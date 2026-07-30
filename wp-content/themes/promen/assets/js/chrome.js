/* ── ПЭ / CHROME — общий хром сайта: часы в наве, бургер + drawer ──
   Порт из html/ (Open Design, ревизия 2026-07-22/23). Активный пункт
   drawer помечает сервер (класс is-active в header.php). */
(function () {
  'use strict';

  /* CLOCK (нав + drawer) */
  function startClock(el) {
    if (!el) return;
    var t = function () {
      var d = new Date(), p = function (n) { return String(n).padStart(2, '0'); };
      el.textContent = p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds()) + ' · ЧЛБ';
    };
    t();
    setInterval(t, 1000);
  }
  startClock(document.getElementById('navClock'));
  startClock(document.getElementById('navDrawerClock'));

  /* S10 · наезд на телефоне: пиним форму низом к нижнему краю экрана
     (top = vh − h) — кнопка «Отправить» видна до наезда, футер стартует
     ровно когда форму долистали. Порт ревизии hero-variant-d (§6);
     на планшете липнет вся секция (base.css), top сбрасываем. */
  var s10Right = document.querySelector('.footer-zone .s10-right');
  if (s10Right) {
    var mqFooter = window.matchMedia('(max-width:640px)');
    var setStickyTop = function () {
      if (!mqFooter.matches) { s10Right.style.top = ''; return; }
      s10Right.style.top = (window.innerHeight - s10Right.offsetHeight) + 'px';
    };
    setStickyTop();
    window.addEventListener('resize', setStickyTop, { passive: true });
    window.addEventListener('load', setStickyTop);
    if (mqFooter.addEventListener) mqFooter.addEventListener('change', setStickyTop);
  }

  /* S10: имя выбранного файла «Чертёж / КД» */
  var s10File = document.getElementById('f-file');
  var s10FileName = document.getElementById('s10-file-name');
  if (s10File && s10FileName) {
    s10File.addEventListener('change', function () {
      s10FileName.textContent = this.files.length ? this.files[0].name : 'PDF, DWG, DXF';
    });
  }

  /* Мобильное меню (бургер + drawer) */
  var burger = document.getElementById('navBurger');
  var drawer = document.getElementById('navDrawer');
  var overlay = document.getElementById('navDrawerOverlay');
  if (!burger || !drawer || !overlay) return;

  function openDrawer() {
    drawer.classList.add('is-open');
    overlay.classList.add('is-open');
    burger.classList.add('is-open');
    burger.setAttribute('aria-expanded', 'true');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('nav-drawer-locked');
  }
  function closeDrawer() {
    drawer.classList.remove('is-open');
    overlay.classList.remove('is-open');
    burger.classList.remove('is-open');
    burger.setAttribute('aria-expanded', 'false');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('nav-drawer-locked');
  }

  burger.addEventListener('click', function () {
    drawer.classList.contains('is-open') ? closeDrawer() : openDrawer();
  });
  overlay.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeDrawer();
  });
  drawer.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', closeDrawer);
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth > 1199) closeDrawer(); /* порог бургера в base.css */
  });
})();
