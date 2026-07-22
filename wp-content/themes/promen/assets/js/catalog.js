/**
 * AJAX-фильтры каталога.
 * Чипсы, пагинация, поиск и сайдбар-группы (?group=) — без перезагрузки.
 * URL через pushState; «назад» работает.
 */
(function () {
  var list = document.getElementById('productList');
  if (!list) return;

  var count = document.getElementById('pCount');
  var pagination = document.querySelector('.cat-pagination');
  var pathSub = document.getElementById('pathSub');
  var mainTitle = document.getElementById('mainTitle');
  var pathCatLink = document.getElementById('pathCatLink');
  var catNav = document.getElementById('catNav');

  function replaceNode(id) {
    var cur = document.getElementById(id);
    var next = window._promenDoc && window._promenDoc.getElementById(id);
    if (cur && next) cur.replaceWith(next);
  }

  function swap(url, push) {
    list.style.opacity = '.35';
    fetch(url, { headers: { 'X-Requested-With': 'fetch' } })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        window._promenDoc = doc;
        var newList = doc.getElementById('productList');
        var newCount = doc.getElementById('pCount');
        var newPag = doc.querySelector('.cat-pagination');
        var newPath = doc.getElementById('pathSub');
        var newTitle = doc.getElementById('mainTitle');
        var newCatLink = doc.getElementById('pathCatLink');
        var newNav = doc.getElementById('catNav');

        if (newList) list.innerHTML = newList.innerHTML;
        if (newCount && count) {
          count.textContent = newCount.textContent;
          count.classList.remove('pop');
          void count.offsetWidth;
          count.classList.add('pop');
        }
        // Шапка колонок реестра меняется с группой (DN/PN у фланцев ≠ угол у отводов).
        replaceNode('tblHd');
        // Панель фильтров и сводку заменяем целиком (счётчики/опции пересчитаны).
        replaceNode('cbFilters');
        replaceNode('cbSummary');
        if (window._promenBindFilterToggle) window._promenBindFilterToggle();
        // Обновить счётчик активных фильтров на кнопке-переключателе.
        var tgl = document.getElementById('cbToggle');
        if (tgl) {
          var n = document.querySelectorAll('#cbSummary .cbs-tag').length;
          var badge = tgl.querySelector('.cb-toggle-n');
          if (n > 0) {
            if (!badge) { badge = document.createElement('span'); badge.className = 'cb-toggle-n'; tgl.appendChild(badge); }
            badge.textContent = n;
          } else if (badge) { badge.remove(); }
        }
        if (newPag && pagination) { pagination.innerHTML = newPag.innerHTML; }
        if (newPath && pathSub) pathSub.textContent = newPath.textContent;
        if (newTitle && mainTitle) mainTitle.textContent = newTitle.textContent;

        if (pathCatLink || newCatLink) {
          var row = document.querySelector('.mh-title-row');
          if (row) {
            var existing = document.getElementById('pathCatLink');
            if (existing) existing.remove();
            if (newCatLink) row.appendChild(newCatLink.cloneNode(true));
            pathCatLink = document.getElementById('pathCatLink');
          }
        }
        if (newNav && catNav) catNav.innerHTML = newNav.innerHTML;

        list.style.opacity = '';
        if (push) history.pushState({ promen: true }, '', url);
        window.scrollTo({ top: document.querySelector('.cmd-bar').offsetTop - 80, behavior: 'smooth' });
      })
      .catch(function () { location.href = url; });
  }

  // Клики: мультичипсы, пагинация, сводка, сброс, сайдбар-группы.
  document.addEventListener('click', function (e) {
    // «+ ещё» — раскрыть скрытые чипсы группы.
    var more = e.target.closest('.c-chip--more');
    if (more) {
      e.preventDefault();
      more.closest('.cbf-multi').querySelectorAll('.c-chip--extra').forEach(function (c) { c.classList.remove('c-chip--extra'); });
      more.remove();
      return;
    }
    var a = e.target.closest('.c-chip, .cat-pagination a, .ce-reset, a.sbn-filter, .cbs-tag, .cbs-reset');
    if (!a || !a.href) return;
    if (a.classList.contains('mh-cat-link')) return;
    e.preventDefault();
    swap(a.href, true);
  });

  // Сворачивание панели фильтров (состояние переживает AJAX-подмену).
  function bindFilterToggle() {
    var toggle = document.getElementById('cbToggle');
    var panel = document.getElementById('cbFilters');
    if (!toggle || !panel) return;
    if (window._promenFiltersOpen) { panel.classList.remove('is-collapsed'); toggle.setAttribute('aria-expanded', 'true'); }
    toggle.onclick = function () {
      var open = panel.classList.toggle('is-collapsed') === false;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      window._promenFiltersOpen = open;
    };
  }
  bindFilterToggle();
  window._promenBindFilterToggle = bindFilterToggle;

  // Диапазоны DN/PN — селекты «от/до».
  document.addEventListener('change', function (e) {
    var sel = e.target.closest('.cbf-range .cbf-sel');
    if (!sel) return;
    var box = sel.closest('.cbf-range');
    var param = box.dataset.param;
    var min = box.querySelector('[data-bound=min]').value;
    var max = box.querySelector('[data-bound=max]').value;
    var filtersEl = document.getElementById('cbFilters');
    var url = new URL(filtersEl.dataset.base, location.origin);
    // сохраняем прочие активные параметры из текущего URL
    new URL(location.href).searchParams.forEach(function (v, k) {
      if (k !== param + '_min' && k !== param + '_max' && k !== 'paged') url.searchParams.set(k, v);
    });
    if (min) url.searchParams.set(param + '_min', min); else url.searchParams.delete(param + '_min');
    if (max) url.searchParams.set(param + '_max', max); else url.searchParams.delete(param + '_max');
    if (filtersEl.dataset.group) url.searchParams.set('group', filtersEl.dataset.group);
    swap(url.toString(), true);
  });

  if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var q = searchForm.querySelector('input[name=q]').value.trim();
      var url = new URL(location.href);
      url.searchParams.delete('paged');
      if (q) url.searchParams.set('q', q); else url.searchParams.delete('q');
      swap(url.toString(), true);
    });
  }

  window.addEventListener('popstate', function () {
    swap(location.href, false);
  });
})();

/* PDP: клик по строке реестра открывает боковой паспорт (как в katalog.html). */
(function () {
  var pdp = document.getElementById('pdp');
  var overlay = document.getElementById('pdpOverlay');
  if (!pdp || !overlay) return;

  var openBtn = document.getElementById('pdpOpen');

  function fill(row) {
    var d = row.dataset;
    document.getElementById('pdpCode').textContent = d.sku || '';
    document.getElementById('pdpTitle').textContent = d.title || '';
    document.getElementById('pdpSub').textContent = d.family || '';
    var params = d.fastener ? [
      ['Резьба / Ø', d.thread], ['Длина L, мм', d.length],
      ['Класс прочности', d.strength], ['Класс точности', d.accuracy],
      ['Тип шайбы', d.washer],
      ['Масса, кг', d.mass],
      ['Материал', d.steel], ['Поднадзорность', d.sup]
    ] : d.flange ? [
      ['DN, мм', d.dn], ['PN', d.pn], ['Тип', d.ftype],
      ['Уплотнение', d.seal], ['D нар., мм', d.d], ['b, мм', d.b],
      ['D1, мм', d.d1], ['n × d болта', (d.n && d.boltd) ? (d.n + '×M' + d.boltd) : (d.n || '')],
      ['Масса, кг', d.mass],
      ['Материал', d.steel], ['Поднадзорность', d.sup]
    ] : [
      ['DN, мм', d.dn], ['PN, МПа', d.pn],
      [d.d2 ? 'D1, мм' : 'D нар., мм', d.d],
      [d.s2 ? 'Стенка s1, мм' : 'Стенка s, мм', d.wall],
      ['D2, мм', d.d2], ['Стенка s2, мм', d.s2],
      ['Исп.', d.exec],
      ['Угол', d.angle ? d.angle + '°' : ''], ['Масса, кг', d.mass],
      ['Материал', d.steel], ['Поднадзорность', d.sup]
    ];
    params = params.filter(function (p) { return p[1]; });
    document.getElementById('pdpParams').innerHTML = params.map(function (p) {
      return '<div class="pdp-prow"><span class="pdp-pk">' + p[0] + '</span><span class="pdp-pv">' + p[1] + '</span></div>';
    }).join('');
    document.getElementById('pdpNorms').innerHTML = d.norm ? '<span class="pdp-tag">' + d.norm + '</span>' : '';
    document.getElementById('pdpSectors').innerHTML = ['ТЭС', 'АЭС', 'Нефтехим', 'ЖКХ'].map(function (s) {
      return '<span class="pdp-sector">' + s + '</span>';
    }).join('');
    document.getElementById('pdpMarks').innerHTML = ['Паспорт изделия', 'Сертификат 3.1', 'ВИК', 'УЗК по запросу'].map(function (m) {
      return '<span class="pdp-mark">' + m + '</span>';
    }).join('');
    if (openBtn) openBtn.href = row.href;
  }

  function open() {
    pdp.classList.add('open');
    overlay.classList.add('show');
  }
  function close() {
    pdp.classList.remove('open');
    overlay.classList.remove('show');
  }

  // Клик по строке — обычный переход на страницу товара (single-product).
  // PDP-препросмотр открывается только по клику на стрелку › в конце строки.
  document.addEventListener('click', function (e) {
    var arrow = e.target.closest('.prod-row .pr-arr');
    if (!arrow) return;
    var row = arrow.closest('.prod-row');
    e.preventDefault();
    e.stopPropagation();
    document.querySelectorAll('.prod-row.sel').forEach(function (r) { r.classList.remove('sel'); });
    row.classList.add('sel');
    fill(row);
    open();
  });

  document.getElementById('pdpClose').addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();

/* Сайдбар: сворачивание/разворачивание семейств (делегирование — переживает AJAX). */
(function () {
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.sbn-toggle');
    if (!btn) return;
    e.preventDefault();
    var group = btn.closest('.sbn-group');
    if (!group) return;
    var open = group.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();

/* База знаний каталога: вкладки + FAQ-аккордеон (как в katalog.html). */
(function () {
  var tabs = document.querySelectorAll('.kb-tab');
  var panels = document.querySelectorAll('.kb-panel');
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('kp-active'); });
      btn.classList.add('active');
      var target = document.getElementById('kp-' + btn.dataset.panel);
      if (target) target.classList.add('kp-active');
    });
  });
  document.querySelectorAll('.fq-q').forEach(function (q) {
    q.addEventListener('click', function () { q.parentElement.classList.toggle('open'); });
  });
})();
