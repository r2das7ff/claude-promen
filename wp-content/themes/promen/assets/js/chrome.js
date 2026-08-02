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

  /* ПОЛОСА ПРОГРЕССА ЧТЕНИЯ (стили — base.css, видна ≤1100px, где скрыта
     боковая точечная навигация). Живёт здесь, а не в страничных скриптах:
     до 2026-07-31 копии лежали в front.js и projects.js, и полосы не было на
     карточке товара, в каталоге, на категориях, контактах, производстве,
     политике и 404.
     Скроллером может быть body ИЛИ documentElement — на страницах с
     html,body{height:100%} прокручивается body, и window.scrollY всегда 0,
     поэтому читаем оба и слушаем оба. */
  (function () {
    var bar = document.createElement('div');
    bar.className = 'scroll-progress';
    document.body.appendChild(bar);
    var nav = document.querySelector('.nav');
    var ticking = false;
    function update() {
      var b = document.body, d = document.documentElement;
      var st = b.scrollTop || d.scrollTop || window.pageYOffset || 0;
      var h = Math.max(b.scrollHeight, d.scrollHeight) - window.innerHeight;
      /* scaleX вместо width — см. .scroll-progress в base.css */
      bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, Math.max(0, st / h)) : 0) + ')';
      /* Тень шапки: контент «скользит под» неё (см. .nav.is-scrolled) */
      if (nav) nav.classList.toggle('is-scrolled', st > 8);
      ticking = false;
    }
    function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
    document.body.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  })();

  /* «БЕГУЩЕЕ» ПОДЧЁРКИВАНИЕ МЕНЮ — один индикатор под активным пунктом:
     при ховере/фокусе едет к цели (240мс, --ease-out), при уходе
     возвращается домой. При загрузке прочерчивается из нуля на месте
     активного пункта («регистрация», как полосы карточек s9 главной).
     Без JS класс nav--line не ставится — работает старый per-link ::after.
     Reduce-motion снапает через CSS (.nav-line{transition:none}). */
  (function () {
    var navEl = document.querySelector('.nav');
    var ul = navEl && navEl.querySelector('.nav-links');
    if (!navEl || !ul) return;
    var links = [].slice.call(ul.querySelectorAll('a'));
    if (!links.length) return;
    var line = document.createElement('span');
    line.className = 'nav-line';
    ul.appendChild(line);
    navEl.classList.add('nav--line');
    var activeLink = ul.querySelector('a.is-active');

    function place(link, instant) {
      var w = link.offsetWidth, x = link.offsetLeft;
      if (!w) { line.style.opacity = '0'; return; } /* меню скрыто (бургер-тир) */
      if (instant) line.classList.add('nav-line--snap');
      line.style.opacity = '1';
      line.style.transform = 'translateX(' + x + 'px) scaleX(' + (w / 100) + ')';
      if (instant) { void line.offsetWidth; line.classList.remove('nav-line--snap'); }
    }
    function home(instant) {
      if (activeLink) place(activeLink, instant);
      else line.style.opacity = '0'; /* нет активного пункта — индикатор спит */
    }

    links.forEach(function (a) {
      a.addEventListener('mouseenter', function () { place(a); });
      a.addEventListener('focus', function () { place(a); });
    });
    ul.addEventListener('mouseleave', function () { home(); });
    ul.addEventListener('focusout', function (e) { if (!ul.contains(e.relatedTarget)) home(); });

    if (activeLink && activeLink.offsetWidth) {
      /* прочерчивание при загрузке: со scaleX(0) на месте активного пункта */
      line.classList.add('nav-line--snap');
      line.style.opacity = '1';
      line.style.transform = 'translateX(' + activeLink.offsetLeft + 'px) scaleX(0)';
      void line.offsetWidth;
      line.classList.remove('nav-line--snap');
      setTimeout(function () { home(); }, 120);
    } else {
      home(true);
    }

    window.addEventListener('resize', function () { home(true); }, { passive: true });
    window.addEventListener('load', function () { home(true); });
  })();

  /* S10 · наезд на телефоне и планшете: пиним форму так, чтобы её низ совпал
     с нижним краем экрана (top = vh − h) — кнопка «Отправить» видна до наезда,
     футер стартует ровно когда форму долистали. Порт ревизии hero-variant-d
     (§6). Порог 1024, а не 640: на планшете секция тоже распадается и пиннится
     только форма (base.css), иначе низ формы уходил бы за экран.

     Но при высоком экране (iPad 820×1180) форма ниже вьюпорта, и «низом к
     краю» означало бы top≈380: форма зависала в середине, а над ней зияла
     пустота — контактный блок уже уехал, футер ещё не пришёл. Поэтому берём
     минимум из двух: либо низом к краю (форма выше экрана), либо шапкой под
     навигацию (форма помещается целиком). */
  var s10Right = document.querySelector('.footer-zone .s10-right');
  if (s10Right) {
    var mqFooter = window.matchMedia('(max-width:1024px)');
    var navH = function () {
      var v = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h'), 10);
      return isNaN(v) ? 64 : v;
    };
    var setStickyTop = function () {
      if (!mqFooter.matches) { s10Right.style.top = ''; return; }
      s10Right.style.top = Math.min(window.innerHeight - s10Right.offsetHeight, navH()) + 'px';
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
