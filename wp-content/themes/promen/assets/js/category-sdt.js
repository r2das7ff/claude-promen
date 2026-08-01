/**
 * Лендинг СДТ: reveal, табы базы знаний, FAQ-аккордеон,
 * фильтр реестра исполнений — поведение из design-reference/sdt.html.
 */
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

  var tabs = document.querySelectorAll('.kb-tab');
  var panels = document.querySelectorAll('.kb-panel');

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
    var pad = 16; // чтобы таб не прилипал к самому краю
    var d = 0;
    if (tr.right > rr.right) { d = tr.right - rr.right + pad; }
    else if (tr.left < rr.left) { d = tr.left - rr.left - pad; }
    if (!d) return;
    var ar = active.getBoundingClientRect();
    if (d > 0) { d = Math.min(d, ar.left - rr.left); }
    else { d = Math.max(d, ar.right - rr.right); }
    if (d) {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      row.scrollBy({ left: d, behavior: reduce ? 'auto' : 'smooth' });
    }
  }

  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('kp-active'); });
      btn.classList.add('active');
      var target = document.getElementById('kp-' + btn.dataset.panel);
      if (target) target.classList.add('kp-active');
      // Лента из семи табов на телефоне втрое шире экрана, и признак
      // прокрутки — только обрезанный край. Показываем следующий таб: и
      // сам он становится доступен в одно касание, и видно, что лента едет.
      revealTab(btn, btn.nextElementSibling);
    });
  });

  document.querySelectorAll('.fq-q').forEach(function (q) {
    q.addEventListener('click', function () { q.parentElement.classList.toggle('open'); });
  });

  /*
   * Аккордеон только для узких экранов.
   * Секции «Подбор» (s03, пять задач) и «Области применения» (s06, шесть
   * секторов) на десктопе — таблица и сетка, которые читаются одним взглядом.
   * На 390px они разворачиваются в 2000px и 1900px сплошного текста, через
   * который надо пролистать, чтобы дойти до нужной строки. Схлопываем тело
   * карточек, оставляя заголовки: получается оглавление, по которому видно
   * все варианты сразу, и раскрывается только нужный.
   * Разметку не трогаем (s03 живёт в 18 конфигах категорий) — навешиваем
   * классы и ARIA здесь. Инициализируем только на телефоне: на десктопе
   * тела карточек всё равно видны, а лишние role=button ни к чему.
   */
  var mqPhone = window.matchMedia('(max-width:640px)');
  var accDone = false;
  var initAccordions = function () {
    if (accDone || !mqPhone.matches) return;
    accDone = true;
    [
      { item: '#s03 .sg-row', head: '.sg-task' },
      { item: '#s06 .app-c', head: '.app-h' }
    ].forEach(function (cfg) {
      var items = document.querySelectorAll(cfg.item);
      if (items.length < 2) return;
      items.forEach(function (item, i) {
        var head = item.querySelector(cfg.head);
        if (!head) return;
        item.classList.add('pm-acc');
        head.classList.add('pm-acc-hd');
        head.setAttribute('role', 'button');
        head.setAttribute('tabindex', '0');
        // Первая карточка раскрыта: пустой аккордеон не показывает, что внутри.
        if (i === 0) item.classList.add('pm-acc-open');
        head.setAttribute('aria-expanded', i === 0 ? 'true' : 'false');
        var toggle = function () {
          var open = item.classList.toggle('pm-acc-open');
          head.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        head.addEventListener('click', toggle);
        head.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
            e.preventDefault();
            toggle();
          }
        });
      });
    });
  };
  initAccordions();
  if (mqPhone.addEventListener) {
    mqPhone.addEventListener('change', initAccordions);
  } else if (mqPhone.addListener) {
    mqPhone.addListener(initAccordions);
  }

  // Группы реестра: клик по заголовку сворачивает/разворачивает.
  document.querySelectorAll('.reg-group-hd').forEach(function (hd) {
    hd.addEventListener('click', function () {
      var g = hd.closest('.reg-group');
      var open = g.classList.toggle('open');
      hd.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  /*
   * Телефон и планшет: сервер отдаёт все группы раскрытыми (.open в
   * catalog-taxonomy.php). На десктопе это обзорная таблица, а на 390px —
   * около 2000px однотипных строк «фильтр в реестре изделий» между шапкой
   * категории и живым реестром. Оставляем раскрытой первую группу,
   * остальные сворачиваем; заголовки с шевроном остаются оглавлением.
   * Порог 900px — тот же, что у компакт-строки .reg-r в category-sdt.css.
   * Флаг regCollapsed: сворачиваем один раз за загрузку, чтобы поворот
   * экрана не схлопывал то, что пользователь уже открыл руками.
   */
  var mqCompact = window.matchMedia('(max-width:900px)');
  var regGroups = document.querySelectorAll('#regList .reg-group');
  var regCollapsed = false;
  var collapseRegGroups = function () {
    if (regCollapsed || !mqCompact.matches || regGroups.length < 2) return;
    regCollapsed = true;
    regGroups.forEach(function (g, i) {
      if (i === 0) return;
      // max-height анимируется 0.3s — на первой отрисовке это лишнее мельтешение.
      var body = g.querySelector('.reg-group-body');
      if (body) body.style.transition = 'none';
      g.classList.remove('open');
      var hd = g.querySelector('.reg-group-hd');
      if (hd) hd.setAttribute('aria-expanded', 'false');
      // Принудительный пересчёт обязателен: без него браузер схлопывает оба
      // изменения стиля в одно и max-height всё равно анимируется. Помимо
      // мельтешения это ломало прокрутку к #registry по ?gost=… — она
      // считалась, пока высота секции ещё старая, и промахивалась на 1265px.
      if (body) { void body.offsetHeight; body.style.transition = ''; }
    });
  };
  collapseRegGroups();
  if (mqCompact.addEventListener) {
    mqCompact.addEventListener('change', collapseRegGroups);
  } else if (mqCompact.addListener) {
    mqCompact.addListener(collapseRegGroups);
  }

  // Марки стали: клик по строке раскрывает панель деталей (как в sdt.html).
  document.querySelectorAll('.mat-r').forEach(function (row) {
    row.addEventListener('click', function () {
      var wasOpen = row.classList.contains('exp');
      document.querySelectorAll('.mat-r.exp').forEach(function (r) { r.classList.remove('exp'); });
      if (!wasOpen) row.classList.add('exp');
    });
  });

  // Модал заявки из hero.
  var orderModal = document.getElementById('orderModal');
  var orderOverlay = document.getElementById('orderOverlay');
  var orderOpen = document.getElementById('orderOpen');
  if (orderModal && orderOpen) {
    var toggleOrder = function (show) {
      orderModal.classList.toggle('open', show);
      orderOverlay.classList.toggle('show', show);
      // Замок прокрутки фона — как у бокового меню (chrome.js) и глобальной
      // модалки запроса (request-modal.js).
      document.body.classList.toggle('modal-locked', show);
      if (show) {
        var f = orderModal.querySelector('input[name="standard"]');
        if (f) setTimeout(function () { f.focus(); }, 220);
      }
    };
    orderOpen.addEventListener('click', function () { toggleOrder(true); });
    document.getElementById('orderClose').addEventListener('click', function () { toggleOrder(false); });
    orderOverlay.addEventListener('click', function () { toggleOrder(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') toggleOrder(false); });
  }

  var chips = document.querySelectorAll('#regBar .r-chip');
  var rows = document.querySelectorAll('#regList .reg-r');
  var count = document.getElementById('regCount');
  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      chips.forEach(function (c) { c.classList.remove('on'); });
      chip.classList.add('on');
      var reg = chip.dataset.reg;
      var n = 0;
      rows.forEach(function (r) {
        var show = reg === 'all' || (r.dataset.type || '').indexOf(reg) !== -1;
        r.style.display = show ? '' : 'none';
        if (show) n++;
      });
      if (count) count.textContent = n + ' серий';
    });
  });

  // Нормативная база: показать скрытые карточки (>6 на десктопе, >3 на телефоне).
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-norm-more]');
    if (!btn) return;
    var wrap = btn.closest('.norm-group');
    var grid = wrap ? wrap.querySelector('[data-norm-grid]') : null;
    if (grid) grid.classList.add('is-expanded');
    btn.remove();
  });

  // Марки стали: показать строки сверх телефонного порога (>6).
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-mat-more]');
    if (!btn) return;
    var tbl = btn.parentElement ? btn.parentElement.querySelector('[data-mat-tbl]') : null;
    if (tbl) tbl.classList.add('is-expanded');
    btn.remove();
  });

  /*
   * Переход по фильтру: прокрутить к живому реестру.
   * Каждая строка «Реестра исполнений», каждая карточка норматива и ссылки
   * «К … в реестре» ведут на эту же страницу с ?gost=… — то есть страница
   * перезагружается и открывается СВЕРХУ, а отфильтрованная выдача, ради
   * которой переходили, лежит на 1870px ниже (два экрана на телефоне).
   * Пользователь нажимал «Открыть в реестре» и видел ту же шапку категории.
   * Скроллим к #registry, если в адресе есть любой фильтрующий параметр.
   *
   * Стоит в самом конце и ещё через два кадра: выше по файлу сворачиваются
   * группы реестра исполнений — это снимает со страницы ~1290px НАД #registry,
   * и скролл, посчитанный до сворачивания, промахивался ровно на эту величину.
   *
   * Не вмешиваемся, если пользователь задал якорь сам или это переход «назад»
   * (там позицию восстанавливает браузер — перебивать её нельзя).
   */
  var FILTER_PARAMS = ['gost', 'steel', 'industry', 'q', 'sort', 'scope', 'paged',
    'dn_min', 'dn_max', 'pn_min', 'pn_max', 's_min', 's_max'];
  (function () {
    if (location.hash || !location.search) return;
    var nav = (performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || {};
    if (nav.type === 'back_forward') return;
    var qp = new URLSearchParams(location.search);
    if (!FILTER_PARAMS.some(function (p) { return qp.has(p); })) return;
    var reg = document.getElementById('registry');
    if (!reg) return;
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        // scroll-margin-top у #registry уже учитывает фиксированную шапку.
        reg.scrollIntoView({ behavior: 'auto', block: 'start' });
      });
    });
  })();
})();
