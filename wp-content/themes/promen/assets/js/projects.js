/* ── ПЭ / PROJECTS — раздел «Проекты» (список + детальные) ──
   Порт инлайнов html/proekty.html и html/proekt-*.html (2026-07-23):
   фильтр-чипы списка, индикатор прогресса скролла, наезд футера.
   Часы и бургер — chrome.js. Null-guard'ы: файл общий для всех
   страниц раздела. */

/* Фильтр карточек списка (тип / регион) — порт инлайна proekty.html */
(function(){
  const chips=document.querySelectorAll('.pf-chip');
  const cards=document.querySelectorAll('#prjGrid .p-card');
  if(!chips.length||!cards.length)return;
  chips.forEach(chip=>{
    chip.addEventListener('click',()=>{
      chips.forEach(c=>c.classList.remove('active'));
      chip.classList.add('active');
      const f=chip.dataset.filter;
      cards.forEach(card=>{
        const show = f==='all'
          || card.dataset.type===f
          || card.dataset.region===f;
        card.hidden = !show;
      });
    });
  });
})();

/* Индикатор прогресса скролла — общий assets/js/chrome.js (грузится везде). */

/* Наезд футера — общий assets/js/footer-pin.js (подключается enqueue'ом). */
