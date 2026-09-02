/* Секция «Отдел продаж» (parts/managers.php): оживающие портреты и
   копирование телефона/почты.
   Портреты: ролик играет под курсором и сам, по очереди, когда сетка
   доехала до экрана (см. АВТОПОКАЗ ниже).
   Копирование: делегированный клик по .smgr-copy → Clipboard API с
   execCommand-фолбэком; подтверждение — свап иконки на галочку (is-copied)
   и aria-live статус. */
(function () {
  var sec = document.getElementById('managers');
  if (!sec) return;

  var live = document.createElement('span');
  live.className = 'smgr-sr';
  live.setAttribute('aria-live', 'polite');
  sec.appendChild(live);

  function confirmCopied(btn, txt) {
    btn.classList.add('is-copied');
    live.textContent = 'Скопировано: ' + txt;
    clearTimeout(btn._smgrT);
    btn._smgrT = setTimeout(function () {
      btn.classList.remove('is-copied');
    }, 1500);
  }

  /* Страховка для окружений без Clipboard API (staging по http и т.п.) */
  function legacyCopy(btn, txt) {
    var ta = document.createElement('textarea');
    ta.value = txt;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
      if (document.execCommand('copy')) confirmCopied(btn, txt);
    } catch (e) { /* некритично: остаются tel:/mailto: */ }
    document.body.removeChild(ta);
  }

  /* ── ОЖИВАЮЩИЙ ПОРТРЕТ ──
     Ролик у карточки менеджера играет под курсором и сам по очереди, когда
     секция доехала до экрана (см. АВТОПОКАЗ ниже). Видимость даёт CSS,
     здесь — воспроизведение.
     Ролик проигрывается один раз вперёд: человек оживает, камера отъезжает.
     Возврат отыгрывает CSS-переход, поэтому в покое кадр снова совпадает с
     фотографией и подмены не видно. */
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  var fine   = window.matchMedia('(hover: hover) and (pointer: fine)');
  if (fine.matches && !reduce.matches) {
    var items = Array.prototype.slice.call(sec.querySelectorAll('.smgr-photo.has-video'))
      .map(function (box) {
        return { card: box.closest('.smgr-card') || box, video: box.querySelector('.smgr-video') };
      })
      .filter(function (it) { return it.video; });

    /* ── АВТОПОКАЗ ПО ОЧЕРЕДИ ──
       Оживающий портрет находил только тот, кто вёл курсор по карточке, —
       без наведения секция выглядела набором обычных фотографий. Поэтому,
       когда сетка доезжает до экрана, портреты играют сами: первый по
       порядку, за ним следующий и дальше по кругу.

       Играет строго один: два движущихся портрета рядом спорят за внимание.
       Пауза между ними GAP — за это время предыдущий кадр возвращается к
       фотографии (CSS-переход камеры длиннее, он доигрывает уже под
       следующим портретом, и это незаметно: гаснет он быстро).

       Класс вешаем не на play(), а на событие playing: пока ролик
       буферизуется, показывать нечего, а камера бы уже поехала.

       Курсор главнее очереди: наведение её останавливает и отдаёт карточку
       hover-логике, увод — продолжает со следующего портрета. Файлы грузим
       по одному впереди очереди: preload="none" в разметке остаётся, и
       секция по-прежнему ничего не весит, пока её не увидели. */
    var GAP   = 300;   // пауза между портретами, мс
    var GUARD = 3000;  // запас к длительности, если ended не придёт
    var RETRY = 2000;  // пауза, если браузер отказал в воспроизведении

    var idx = -1, timer = null, guard = null, active = null;
    var running = false;

    /* Наведение читаем из DOM, а не из флага: флаг залипал, если карточка
       уезжала из-под курсора без mouseleave (прокрутка клавишами), и очередь
       после возврата секции больше не стартовала. */
    function isHovered() { return !!sec.querySelector('.smgr-card:hover'); }

    function warm(it) {
      if (it && it.video.preload === 'none') {
        it.video.preload = 'auto';
        it.video.load();
      }
    }

    function stopActive() {
      if (!active) return;
      var it = active;
      active = null;
      clearTimeout(guard);
      it.video.removeEventListener('ended', onEnded);
      it.video.removeEventListener('playing', onPlaying);
      it.card.classList.remove('is-autoplay');
      it.video.pause();
      /* Сброс на первый кадр — он же фотография: следующий запуск начнётся
         чисто, а пауза экономит декодирование. */
      try { it.video.currentTime = 0; } catch (e) {}
    }

    function onPlaying() { if (active) active.card.classList.add('is-autoplay'); }
    function onEnded()   { stopActive(); schedule(GAP); }

    function schedule(delay) {
      clearTimeout(timer);
      if (!running || isHovered()) return;
      timer = setTimeout(playNext, delay);
    }

    function playNext() {
      if (!running || isHovered() || !items.length) return;
      idx = (idx + 1) % items.length;
      var it = active = items[idx];
      warm(items[(idx + 1) % items.length]);
      it.video.addEventListener('playing', onPlaying, { once: true });
      it.video.addEventListener('ended', onEnded, { once: true });
      try { it.video.currentTime = 0; } catch (e) {}
      var p = it.video.play();
      /* Не сыграл — карточка остаётся фотографией, а очередь ждёт дольше
         обычного: браузер глушит беззвучное видео, когда окно потеряло
         фокус («paused to save power»), и на быстрой паузе очередь молотила
         бы play() по кругу вхолостую. RETRY возвращает её к жизни сама,
         когда окно снова активно. */
      if (p && p.catch) p.catch(function () {
        if (active !== it) return;
        stopActive();
        schedule(RETRY);
      });
      guard = setTimeout(function () {
        if (active === it) onEnded();
      }, ((it.video.duration || 6) * 1000) + GUARD);
    }

    Array.prototype.forEach.call(sec.querySelectorAll('.smgr-photo.has-video'), function (box) {
      var v = box.querySelector('.smgr-video');
      if (!v) return;
      var card = box.closest('.smgr-card') || box;
      card.addEventListener('mouseenter', function () {
        clearTimeout(timer);
        stopActive();
        /* Каждое наведение начинает движение заново, а не с середины. */
        try { v.currentTime = 0; } catch (e) {}
        var p = v.play();
        if (p && p.catch) p.catch(function () { /* автоплей отклонён — остаётся фото */ });
      });
      card.addEventListener('mouseleave', function () {
        v.pause();
        try { v.currentTime = 0; } catch (e) {}
        schedule(GAP);
      });
    });

    var grid = sec.querySelector('.smgr-grid');

    /* Запасная проверка геометрией — для случаев, когда наблюдатель молчит:
       страницу открыли в фоновой вкладке (скрытый документ не считает
       пересечения) или IntersectionObserver в браузере нет вовсе. */
    function gridOnScreen() {
      if (!grid) return false;
      var r = grid.getBoundingClientRect();
      return r.bottom > 0 && r.top < (window.innerHeight || 0);
    }

    function startQueue() {
      if (running) return;
      running = true;
      warm(items[0]);
      schedule(GAP);
    }

    function stopQueue() {
      /* Секция ушла с экрана — очередь замирает: играть за кадром незачем,
         а ролики продолжали бы греть процессор. */
      running = false;
      clearTimeout(timer);
      stopActive();
    }

    if (grid && items.length) {
      var ioAnswered = false;

      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
          ioAnswered = true;
          entries.forEach(function (en) {
            if (en.isIntersecting) startQueue();
            else stopQueue();
          });
        }, { threshold: 0.1 }).observe(grid);
      }

      /* Фолбэк вешаем не по отсутствию IntersectionObserver, а по его
         молчанию: он есть везде, но в замороженном рендере (встроенные
         вебвью, панель предпросмотра без фокуса) пересечения не считаются
         и колбэк не приходит вовсе — очередь тогда не стартовала бы никогда.
         Первый колбэк наблюдатель отдаёт сразу после подписки, так что
         секунды ожидания достаточно. */
      setTimeout(function () {
        if (ioAnswered) return;
        var onScroll = function () {
          if (gridOnScreen()) startQueue();
          else stopQueue();
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
      }, 1000);

      if (!document.hidden && gridOnScreen()) startQueue();
    }

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        clearTimeout(timer);
        stopActive();
      } else if (running) {
        schedule(GAP);
      } else if (gridOnScreen()) {
        startQueue();
      }
    });
  }

  sec.addEventListener('click', function (e) {
    var btn = e.target.closest('.smgr-copy');
    if (!btn) return;
    var txt = btn.getAttribute('data-copy') || '';
    if (!txt) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(txt).then(
        function () { confirmCopied(btn, txt); },
        function () { legacyCopy(btn, txt); }
      );
    } else {
      legacyCopy(btn, txt);
    }
  });
})();
