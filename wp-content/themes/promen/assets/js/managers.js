/* Секция «Отдел продаж» (parts/managers.php): копирование телефона/почты.
   Делегированный клик по .smgr-copy → Clipboard API с execCommand-фолбэком;
   подтверждение — свап иконки на галочку (is-copied) + aria-live статус. */
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
     Ролик у карточки менеджера играет только под курсором. Видимость даёт
     CSS, здесь — воспроизведение: до первого наведения файл вообще не
     скачивается (preload="none"), поэтому секция стоит столько же, сколько
     стоила с одними фотографиями.
     Ролик снят «бумерангом» и заканчивается там же, где начался, так что
     после проигрывания кадр совпадает с фотографией. Уводя курсор, не
     дёргаем currentTime: пусть доигрывает, пока CSS гасит его прозрачностью —
     обрыв на середине движения выглядел бы рывком. */
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  var fine   = window.matchMedia('(hover: hover) and (pointer: fine)');
  if (fine.matches && !reduce.matches) {
    Array.prototype.forEach.call(sec.querySelectorAll('.smgr-photo.has-video'), function (box) {
      var v = box.querySelector('.smgr-video');
      if (!v) return;
      var card = box.closest('.smgr-card') || box;
      card.addEventListener('mouseenter', function () {
        /* Каждое наведение начинает движение заново, а не с середины. */
        try { v.currentTime = 0; } catch (e) {}
        var p = v.play();
        if (p && p.catch) p.catch(function () { /* автоплей отклонён — остаётся фото */ });
      });
      card.addEventListener('mouseleave', function () {
        /* Сброс на первый кадр — он же фотография: следующее наведение
           стартует чисто, а пауза экономит декодирование. */
        v.pause();
        try { v.currentTime = 0; } catch (e) {}
      });
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
