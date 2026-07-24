/* ── ПЭ / 404 — echo запрошенного пути. Порт из html/404.html:
   показываем только реальный путь (?path=/?from= от rewrite-правила
   или same-origin referrer) — не выдумываем ничего для убедительности.
   Поиск и чипы — нативные ссылки/GET-форма в каталог (без JS). ── */
(function () {
  var el = document.getElementById('reqPath');
  if (!el) return;
  var params = new URLSearchParams(location.search);
  var attempted = params.get('path') || params.get('from') || '';
  if (!attempted && location.pathname && location.pathname !== '/') {
    attempted = location.pathname; /* в WP 404 сам URL и есть запрошенный путь */
  }
  if (!attempted && document.referrer) {
    try {
      var refUrl = new URL(document.referrer);
      if (refUrl.origin === location.origin) attempted = refUrl.pathname;
    } catch (e) { /* некорректный referrer игнорируем */ }
  }
  el.textContent = attempted || 'не передан сервером';
})();
