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

  // Группы реестра: клик по заголовку сворачивает/разворачивает.
  document.querySelectorAll('.reg-group-hd').forEach(function (hd) {
    hd.addEventListener('click', function () {
      var g = hd.closest('.reg-group');
      var open = g.classList.toggle('open');
      hd.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

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
})();
