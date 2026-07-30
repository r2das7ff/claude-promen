/**
 * Конфигуратор карточки: сталь/поднадзорность = вариации.
 * URL обновляется query-параметрами (?steel=…&nadzor=…) через replaceState —
 * прямые ссылки открывают карточку с предвыбранной опцией, canonical чистый.
 */
/* Reveal-анимация секций — как в оригинальной верстке. */
(function () {
  if ('IntersectionObserver' in window) {
    var rev = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); rev.unobserve(e.target); }
      });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal').forEach(function (el) { rev.observe(el); });
  } else {
    document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
  }
})();

/* Sticky passport bar — появляется после ухода hero из viewport. */
(function () {
  var bar = document.getElementById('stickyPass');
  var hero = document.getElementById('hero');
  if (!bar || !hero) return;
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          bar.classList.remove('vis');
          bar.setAttribute('aria-hidden', 'true');
        } else {
          bar.classList.add('vis');
          bar.setAttribute('aria-hidden', 'false');
        }
      });
    }, { threshold: 0 });
    io.observe(hero);
  }
})();

(function () {
  var data = window.PROMEN_PRODUCT || {};
  var matSel = document.getElementById('matSel');
  var supGrid = document.getElementById('supGrid');
  var state = {
    steel: null,
    sup: null
  };

  function param(name) {
    return new URLSearchParams(location.search).get(name);
  }

  function currentVariation() {
    var v = data.variations || {};
    if (state.steel && v[state.steel]) {
      var bySup = v[state.steel];
      if (state.sup && bySup[state.sup]) return bySup[state.sup];
      var first = Object.keys(bySup)[0];
      return first ? bySup[first] : null;
    }
    return null;
  }

  function renderSteelList() {
    var list = document.getElementById('ppMatList');
    if (!list) return;
    var steels = data.steels || {};
    var slugs = Object.keys(steels);
    if (!slugs.length) return;
    // Активная первая, остальные — ссылки.
    var ordered = slugs.slice();
    if (state.steel && ordered.indexOf(state.steel) >= 0) {
      ordered = [state.steel].concat(ordered.filter(function (s) { return s !== state.steel; }));
    }
    list.innerHTML = ordered.map(function (slug) {
      var active = slug === state.steel;
      return '<button type="button" class="pp-steel' + (active ? ' is-active' : ' is-alt') + '"' +
        ' data-steel="' + slug.replace(/"/g, '') + '"' +
        (active ? ' aria-current="true"' : '') + '>' +
        (steels[slug] || slug) + '</button>';
    }).join('');
  }

  function setSteel(slug) {
    if (!slug || !(data.steels || {})[slug]) return;
    state.steel = slug;
    if (matSel) matSel.value = slug;
    render();
  }

  function syncForm() {
    var steelName = (data.steels || {})[state.steel] || '';
    var variation = currentVariation();
    var nameEl = document.getElementById('f-name');
    var stdEl = document.getElementById('f-std');
    var dnEl = document.getElementById('f-dn');
    var pnEl = document.getElementById('f-pn');
    var matEl = document.getElementById('f-mat');
    var skuInput = document.querySelector('#s10-form input[name="sku"]');

    if (nameEl && !nameEl.dataset.touched) nameEl.value = data.title || '';
    if (stdEl && !stdEl.dataset.touched) stdEl.value = data.norm || '';
    if (dnEl && !dnEl.dataset.touched && data.dn) dnEl.value = 'DN ' + data.dn;
    if (pnEl && !pnEl.dataset.touched && data.pn) pnEl.value = 'PN ' + data.pn;
    if (matEl && !matEl.dataset.touched) matEl.value = steelName;
    if (skuInput) skuInput.value = variation ? variation.sku : (data.sku || '');
  }

  function render() {
    var steelName = (data.steels || {})[state.steel] || '';
    var supName = (data.sups || {})[state.sup] || '';
    var heroMat = document.getElementById('heroMat');
    var ppSup = document.getElementById('ppSup');
    var cfgLine = document.getElementById('cfgLine');
    var cfgSku = document.getElementById('cfgSku');
    var stickyLine = document.getElementById('stickyLine');
    var qcMat = document.getElementById('qcMat');
    var qcMark = document.getElementById('qcMark');

    if (steelName && heroMat) heroMat.textContent = steelName;
    renderSteelList();
    if (supName && ppSup) ppSup.textContent = supName;
    if (steelName && qcMat) qcMat.textContent = steelName + ' · серт. 3.1';
    if (steelName && qcMark) qcMark.textContent = 'ПЭ · ' + steelName + ' · плавка по поставке';

    var variation = currentVariation();
    var sel = window.PROMEN_SEL || { title: data.title, sku: data.sku, own: true };
    var line = sel.title + (steelName ? ' · ' + steelName : '') + (supName ? ' · надзор: ' + supName : '');
    if (cfgLine) cfgLine.textContent = line;
    if (stickyLine) stickyLine.textContent = line;
    // Вариационный артикул применим только к позиции текущей страницы.
    var sku = sel.own && variation ? variation.sku : sel.sku;
    if (cfgSku) {
      cfgSku.textContent = 'Артикул: ' + sku;
    }
    var cfgSub = document.getElementById('cfgSub');
    if (cfgSub && !sel.own && sel.d) {
      var hasBranch = !!(sel.d2 || sel.s2);
      cfgSub.textContent = [
        sel.d ? (hasBranch ? 'D1 ' + sel.d + ' мм' : 'D ' + sel.d + ' мм') : '',
        sel.wall ? (hasBranch ? 's1 ' + sel.wall + ' мм' : 's ' + sel.wall + ' мм') : '',
        sel.d2 ? 'D2 ' + sel.d2 + ' мм' : '',
        sel.s2 ? 's2 ' + sel.s2 + ' мм' : '',
        sel.r ? 'R ' + sel.r + ' мм' : '',
        sel.mass ? 'масса ' + sel.mass + ' кг' : ''
      ].filter(Boolean).join(' · ');
    }

    // Подсветка марок стали товара в таблице материалов.
    var steelNames = Object.keys(data.steels || {}).map(function (k) { return data.steels[k]; });
    document.querySelectorAll('.mat-r[data-grade]').forEach(function (row) {
      var mine = steelNames.indexOf(row.dataset.grade) !== -1;
      row.classList.toggle('mr-on', mine);
      row.classList.toggle('mr-sel', row.dataset.grade === steelName);
    });

    var qs = new URLSearchParams(location.search);
    if (state.steel) qs.set('steel', state.steel); else qs.delete('steel');
    if (state.sup) qs.set('nadzor', state.sup); else qs.delete('nadzor');
    var query = qs.toString();
    history.replaceState(null, '', location.pathname + (query ? '?' + query : '') + location.hash);

    syncForm();
  }

  if (matSel) {
    var urlSteel = param('steel');
    if (urlSteel && (data.steels || {})[urlSteel]) {
      matSel.value = urlSteel;
    }
    state.steel = matSel.value || null;
    matSel.addEventListener('change', function () {
      setSteel(matSel.value);
    });
  } else if (data.steels) {
    var urlSteelOnly = param('steel');
    var steelKeys = Object.keys(data.steels);
    state.steel = (urlSteelOnly && data.steels[urlSteelOnly]) ? urlSteelOnly : (steelKeys[0] || null);
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('#ppMatList .pp-steel');
    if (!btn || btn.classList.contains('is-active')) return;
    e.preventDefault();
    setSteel(btn.getAttribute('data-steel'));
  });

  if (supGrid) {
    var urlSup = param('nadzor');
    var buttons = supGrid.querySelectorAll('.pn-b');
    buttons.forEach(function (b) {
      if (urlSup && b.dataset.sup === urlSup) {
        buttons.forEach(function (x) { x.classList.remove('on'); });
        b.classList.add('on');
      }
      b.addEventListener('click', function () {
        buttons.forEach(function (x) { x.classList.remove('on'); });
        b.classList.add('on');
        state.sup = b.dataset.sup;
        render();
      });
    });
    var on = supGrid.querySelector('.pn-b.on');
    state.sup = on ? on.dataset.sup : null;
  }

  // Конфигуратор: клик по строке ВЫБИРАЕТ позицию (переходы — в секции 03).
  window.PROMEN_SEL = {
    title: data.title, sku: data.sku, dn: data.dn,
    d: '', wall: '', d2: '', s2: '', r: '', mass: '', own: true
  };
  document.querySelectorAll('#specTable tbody tr').forEach(function (tr) {
    tr.style.cursor = 'pointer';
    tr.addEventListener('click', function () {
      document.querySelectorAll('#specTable tbody tr.on').forEach(function (x) { x.classList.remove('on'); });
      tr.classList.add('on');
      window.PROMEN_SEL = {
        title: tr.dataset.title || data.title,
        sku: tr.dataset.sku || data.sku,
        dn: tr.dataset.dn || '',
        d: tr.dataset.d || '',
        wall: tr.dataset.wall || '',
        d2: tr.dataset.d2 || '',
        s2: tr.dataset.s2 || '',
        r: tr.dataset.r || '',
        mass: tr.dataset.mass || '',
        own: (tr.dataset.sku || '') === data.sku
      };
      render();
    });
  });

  // Не перезаписывать поля формы, если пользователь уже правил их вручную.
  ['f-name', 'f-std', 'f-dn', 'f-pn', 'f-mat'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function () { el.dataset.touched = '1'; });
  });

  if (matSel || supGrid || data.title) render();
})();

/* Русское склонение количественных: 1 типоразмер, 2 типоразмера, 5 типоразмеров. */
function promenPlural(n, forms) {
  n = Math.abs(n) % 100;
  var n1 = n % 10;
  if (n > 10 && n < 20) return forms[2];
  if (n1 > 1 && n1 < 5) return forms[1];
  if (n1 === 1) return forms[0];
  return forms[2];
}
var PROMEN_TR = ['типоразмер', 'типоразмера', 'типоразмеров'];

/* Скрыть колонки таблицы, где все значения пусты («—»/пусто) — для категорий
   без части размеров (опоры, арматура, изоляция). Первую колонку не трогаем. */
function promenHideEmptyCols(table) {
  if (!table) return;
  var headRow = table.tHead && table.tHead.rows[0];
  var bodyRows = table.tBodies[0] ? table.tBodies[0].rows : [];
  if (!headRow || !bodyRows.length) return;
  var cols = headRow.cells.length;
  for (var c = 1; c < cols; c++) {
    var hasData = false;
    for (var r = 0; r < bodyRows.length; r++) {
      var cell = bodyRows[r].cells[c];
      var t = cell ? cell.textContent.replace(/\s|—/g, '') : '';
      if (t) { hasData = true; break; }
    }
    if (!hasData) {
      if (headRow.cells[c]) headRow.cells[c].style.display = 'none';
      for (var r2 = 0; r2 < bodyRows.length; r2++) {
        if (bodyRows[r2].cells[c]) bodyRows[r2].cells[c].style.display = 'none';
      }
    }
  }
}
promenHideEmptyCols(document.getElementById('specTable'));
promenHideEmptyCols(document.querySelector('.series-full'));

/* Конфигуратор: DN-кнопки фильтруют таблицу; длинный список свёрнут до N строк. */
(function () {
  var grid = document.getElementById('dnGrid');
  var rows = Array.prototype.slice.call(document.querySelectorAll('#specTable tbody tr'));
  if (!grid || !rows.length) return;
  var note = document.getElementById('cfgTblNote');
  var LIMIT = 12;
  var expanded = false;

  var moreBtn = document.getElementById('cfgMore');

  function applyFilter(dn) {
    var matched = rows.filter(function (tr) { return !dn || tr.dataset.dn === dn; });
    var shown = 0;
    rows.forEach(function (tr) { tr.style.display = 'none'; });
    matched.forEach(function (tr, i) {
      if (expanded || i < LIMIT || tr.classList.contains('on')) { tr.style.display = ''; shown++; }
    });
    var extra = matched.length - shown;
    if (moreBtn) {
      if (!expanded && extra > 0) {
        moreBtn.style.display = '';
        moreBtn.textContent = 'Показать все ' + matched.length + ' ' + promenPlural(matched.length, PROMEN_TR) + ' ↓';
      } else {
        moreBtn.style.display = 'none';
      }
    }
    if (note) note.textContent = dn
      ? 'DN ' + dn + ' — ' + matched.length + ' ' + promenPlural(matched.length, PROMEN_TR) + ' · клик по строке выбирает позицию'
      : 'Вся серия: ' + matched.length + ' ' + promenPlural(matched.length, PROMEN_TR) + ' · клик по строке выбирает позицию';
  }

  var allBtn = grid.querySelector('.dn-b--all');
  var curDn = null;

  function selectBtn(btn) {
    grid.querySelectorAll('.dn-b').forEach(function (x) { x.classList.remove('on'); });
    btn.classList.add('on');
  }

  grid.querySelectorAll('.dn-b').forEach(function (b) {
    b.addEventListener('click', function () {
      expanded = false; // новый фильтр — снова сворачиваем
      if (b.classList.contains('on') && b !== allBtn) {
        selectBtn(allBtn); curDn = null; applyFilter(null); return;
      }
      selectBtn(b); curDn = b.dataset.dn || null; applyFilter(curDn);
    });
  });

  if (moreBtn) {
    moreBtn.addEventListener('click', function () { expanded = true; applyFilter(curDn); });
  }

  applyFilter(null);
})();

/* Реестр размеров серии: сворачиваем длинный список (ссылки остаются в DOM — SEO не страдает). */
(function () {
  var tbl = document.querySelector('.series-full');
  if (!tbl) return;
  var limit = parseInt(tbl.dataset.collapse || '20', 10);
  var rows = tbl.querySelectorAll('tbody tr');
  if (rows.length <= limit) return;
  var hidden = 0;
  rows.forEach(function (tr, i) {
    if (i >= limit && !tr.classList.contains('on')) { tr.style.display = 'none'; hidden++; }
  });
  var btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'series-more';
  btn.textContent = 'Показать все ' + rows.length + ' ' + promenPlural(rows.length, PROMEN_TR) + ' ↓';
  tbl.parentNode.appendChild(btn);
  btn.addEventListener('click', function () {
    rows.forEach(function (tr) { tr.style.display = ''; });
    btn.remove();
  });
})();

/* Модал заявки: «Заказать» и «Рассчитать доставку» (режим с полем города). */
(function () {
  var modal = document.getElementById('orderModal');
  if (!modal) return;
  var overlay = document.getElementById('orderOverlay');
  var cityField = document.getElementById('omCityField');
  var deliveryInput = document.getElementById('omDelivery');
  var title = document.getElementById('omTitle');

  function toggle(show, deliveryMode) {
    modal.classList.toggle('open', show);
    overlay.classList.toggle('show', show);
    // Замок прокрутки фона — как у бокового меню (chrome.js) и глобальной
    // модалки запроса (request-modal.js).
    document.body.classList.toggle('modal-locked', show);
    if (show) {
      if (cityField) cityField.style.display = deliveryMode ? '' : 'none';
      if (deliveryInput) deliveryInput.value = deliveryMode ? 'да' : '';
      if (title) title.textContent = deliveryMode ? 'Расчёт доставки' : 'Заказать позицию';
      var f = modal.querySelector(deliveryMode ? '#om-city' : '#om-qty');
      if (f) setTimeout(function () { f.focus(); }, 220);
    }
  }
  function prefillFromSelection() {
    var sel = window.PROMEN_SEL;
    if (!sel) return;
    var name = document.getElementById('om-name');
    var dn = document.getElementById('om-dn');
    var sku = document.getElementById('omSku');
    if (name) name.value = sel.title;
    if (dn) dn.value = ['DN ' + (sel.dn || '—'), sel.d ? sel.d + '×' + sel.wall : ''].filter(Boolean).join(' / ');
    if (sku) sku.value = sel.sku;
  }

  var orderBtn = document.getElementById('orderOpen');
  var deliveryBtn = document.getElementById('deliveryOpen');
  var cfgBtn = document.getElementById('cfgRequest');
  if (cfgBtn) cfgBtn.addEventListener('click', function () { prefillFromSelection(); toggle(true, false); });
  if (orderBtn) orderBtn.addEventListener('click', function () { prefillFromSelection(); toggle(true, false); });
  if (deliveryBtn) deliveryBtn.addEventListener('click', function () { prefillFromSelection(); toggle(true, true); });
  document.getElementById('orderClose').addEventListener('click', function () { toggle(false); });
  overlay.addEventListener('click', function () { toggle(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') toggle(false); });
})();

/* База знаний: переключение табов (как в оригинальной верстке). */
(function () {
  var tabs = document.querySelectorAll('#s10 .kb-tab');
  var panels = document.querySelectorAll('#s10 .kb-panel');
  if (!tabs.length) return;

  /**
   * Подтянуть ленту так, чтобы соседний таб next стал виден целиком.
   * Скроллим саму .kb-tabrow, а не через scrollIntoView: тот уводит ещё и
   * страницу по вертикали, вырывая пользователя из текущего места.
   * Сдвиг ограничен так, чтобы только что выбранный таб active не ушёл
   * за противоположный край — иначе на узком экране открытая вкладка
   * пропадает из ленты сразу после нажатия.
   */
  function revealTab(active, next) {
    var row = active && active.closest('.kb-tabrow');
    if (!row || row.scrollWidth <= row.clientWidth) return;
    var rr = row.getBoundingClientRect();
    var tr = (next || active).getBoundingClientRect();
    var pad = 16;
    var d = 0;
    if (tr.right > rr.right) { d = tr.right - rr.right + pad; }
    else if (tr.left < rr.left) { d = tr.left - rr.left - pad; }
    if (!d) return;
    var ar = active.getBoundingClientRect();
    if (d > 0) { d = Math.min(d, ar.left - rr.left); }
    else { d = Math.max(d, ar.right - rr.right); }
    if (d) { row.scrollBy({ left: d, behavior: 'smooth' }); }
  }

  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('kp-active'); });
      btn.classList.add('active');
      var target = document.getElementById('kp-' + btn.dataset.panel);
      if (target) target.classList.add('kp-active');
      // Лента табов на телефоне втрое шире экрана, и признак прокрутки —
      // только обрезанный край. Показываем следующий таб: и сам он
      // становится доступен в одно касание, и видно, что лента едет.
      revealTab(btn, btn.nextElementSibling);
    });
  });
})();

/* Марки стали: показать строки сверх телефонного порога (метку mr-extra-m
   ставит inc/steel-reference.php, прячет product.css). */
(function () {
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-mat-more]');
    if (!btn) return;
    var tbl = btn.parentElement ? btn.parentElement.querySelector('[data-mat-tbl]') : null;
    if (tbl) tbl.classList.add('is-expanded');
    btn.remove();
  });
})();

/*
 * Аккордеон только для узких экранов — секция 06 «Области применения».
 * На десктопе это сетка из шести карточек, читается одним взглядом; на
 * 390px она разворачивается в 970px сплошного текста между паспортом
 * качества и комплектом документов. Схлопываем описания, оставляя
 * заголовки секторов: получается оглавление, раскрывается только нужное.
 * Разметку не трогаем — классы и ARIA навешиваем здесь. Инициализируем
 * только на телефоне: на десктопе тела карточек и так видны.
 */
(function () {
  var mqPhone = window.matchMedia('(max-width:640px)');
  var done = false;
  function init() {
    if (done || !mqPhone.matches) return;
    var items = document.querySelectorAll('#s06 .app-c');
    if (items.length < 2) return;
    done = true;
    items.forEach(function (item, i) {
      var head = item.querySelector('.app-h');
      if (!head) return;
      item.classList.add('pm-acc');
      head.classList.add('pm-acc-hd');
      head.setAttribute('role', 'button');
      head.setAttribute('tabindex', '0');
      // Первая карточка раскрыта: пустой аккордеон не показывает, что внутри.
      if (i === 0) item.classList.add('pm-acc-open');
      head.setAttribute('aria-expanded', i === 0 ? 'true' : 'false');
      function toggle() {
        var open = item.classList.toggle('pm-acc-open');
        head.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
      head.addEventListener('click', toggle);
      head.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
          e.preventDefault();
          toggle();
        }
      });
    });
  }
  init();
  if (mqPhone.addEventListener) { mqPhone.addEventListener('change', init); }
  else if (mqPhone.addListener) { mqPhone.addListener(init); }
})();

/* QC: hover по этапу подсвечивает строку цифрового паспорта. */
(function () {
  var items = document.querySelectorAll('#pqRoute .pq-item');
  var rows = document.querySelectorAll('#qcPassport .pq-row');
  if (!items.length) return;
  items.forEach(function (item) {
    item.addEventListener('mouseenter', function () {
      var stage = item.getAttribute('data-stage');
      rows.forEach(function (row) { row.classList.toggle('hl', row.getAttribute('data-field') === stage); });
      item.classList.add('active');
    });
    item.addEventListener('mouseleave', function () {
      rows.forEach(function (row) { row.classList.remove('hl'); });
      item.classList.remove('active');
    });
  });
})();
