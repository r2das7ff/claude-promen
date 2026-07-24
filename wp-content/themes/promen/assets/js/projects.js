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

/* ── Индикатор прогресса скролла (порт с главной): скроллер может быть
   body ИЛИ documentElement — читаем оба и слушаем оба ── */
(function(){
  var bar=document.createElement('div');
  bar.className='scroll-progress';
  document.body.appendChild(bar);
  var ticking=false;
  function update(){
    var b=document.body,d=document.documentElement;
    var st=b.scrollTop||d.scrollTop||window.pageYOffset||0;
    var h=Math.max(b.scrollHeight,d.scrollHeight)-window.innerHeight;
    bar.style.width=(h>0?Math.min(100,Math.max(0,st/h*100)):0)+'%';
    ticking=false;
  }
  function onScroll(){if(!ticking){ticking=true;requestAnimationFrame(update);}}
  document.body.addEventListener('scroll',onScroll,{passive:true});
  window.addEventListener('scroll',onScroll,{passive:true});
  window.addEventListener('resize',update,{passive:true});
  update();
})();

/* Наезд футера — общий assets/js/footer-pin.js (подключается enqueue'ом). */
