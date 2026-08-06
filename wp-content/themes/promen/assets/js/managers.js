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
