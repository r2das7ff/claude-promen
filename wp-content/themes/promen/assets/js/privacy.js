/* ── ПЭ / PRIVACY — «Политика обработки ПДн»: закрытие мобильного TOC,
   его тумблер и скролл-спай с прогрессом чтения. Порт инлайна
   html/privacy-policy.html (2026-07-23); часы — chrome.js. ── */
/* TOC NAVIGATION — rely on native <a href="#id"> anchor jump + CSS scroll-margin-top
   (set on .pol-chapter / .pol-h3 / .pol-purpose) for the fixed-nav offset, and CSS
   html{scroll-behavior:smooth} for the animation. No manual scrollTo/rAF/History-API
   calls here — those depend on browser features that can be unreliable inside a
   sandboxed preview iframe; native anchor navigation always works. JS below only
   closes the mobile TOC drawer after a tap — it does not touch scrolling at all. */
(()=>{
  document.querySelectorAll('.pol-toc a[data-target]').forEach(a=>{
    a.addEventListener('click',function(){
      if(window.innerWidth<=1024){
        const col = document.getElementById('tocCol');
        if(col) col.classList.remove('is-open');
      }
    });
  });
})();

/* MOBILE TOC TOGGLE */
(()=>{
  const head = document.getElementById('tocHead');
  const col = document.getElementById('tocCol');
  head.addEventListener('click',()=>{
    if(window.innerWidth<=1024){ col.classList.toggle('is-open'); }
  });
})();

/* SCROLL-SPY + PROGRESS */
(()=>{
  const chapters = Array.from(document.querySelectorAll('.pol-chapter'));
  const subHeads = Array.from(document.querySelectorAll('.pol-h3, .pol-purpose'));
  const tocItems = Array.from(document.querySelectorAll('.pol-toc-item'));
  const tocSubLinks = Array.from(document.querySelectorAll('.pol-toc-sub a'));
  const readFill = document.getElementById('readBarFill');
  const tocProgress = document.getElementById('tocProgress');
  const tocHeadLabel = document.getElementById('tocHeadLabel');

  function setActiveChapter(chId){
    tocItems.forEach(li=>li.classList.toggle('is-active', li.dataset.ch===chId));
    const active = tocItems.find(li=>li.dataset.ch===chId);
    if(active){ tocHeadLabel.textContent = active.querySelector('.pol-toc-txt').textContent; }
  }
  function setActiveSub(id){
    tocSubLinks.forEach(a=>a.classList.toggle('is-active', a.getAttribute('data-target')===id));
  }

  /* rAF-гейт + чтения до записей: хендлер висит на трёх целях (window/
     document/body — скроллер зависит от responsive-правил), без гейта одно
     событие обрабатывалось дважды, а offsetTop читался после записи width —
     forced layout. Паттерн — chrome.js (.scroll-progress). */
  let ticking = false;
  function update(){
    ticking = false;
    /* скроллер может быть body (responsive-rules §7) — читаем оба */
    const scrollY = document.body.scrollTop || document.documentElement.scrollTop || window.scrollY || 0;
    const docH = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight) - window.innerHeight;
    const pct = docH>0 ? Math.min(1, Math.max(0, scrollY/docH)) : 0;

    /* Сначала все чтения… */
    const probeY = scrollY + 140;
    let currentCh = chapters[0] ? chapters[0].id : null;
    for(const ch of chapters){
      if(ch.offsetTop <= probeY) currentCh = ch.id; else break;
    }
    let currentSub = null;
    for(const h of subHeads){
      if(h.offsetTop <= probeY) currentSub = h.id; else break;
    }

    /* …потом записи. scaleX вместо width — см. .read-bar-fill в privacy.css */
    readFill.style.transform = 'scaleX('+pct+')';
    tocProgress.style.transform = 'scaleX('+pct+')';
    if(currentCh) setActiveChapter(currentCh);
    if(currentSub) setActiveSub(currentSub);
  }
  function onScroll(){ if(!ticking){ ticking = true; requestAnimationFrame(update); } }
  window.addEventListener('scroll', onScroll, {passive:true});
  document.addEventListener('scroll', onScroll, {passive:true});
  document.body.addEventListener('scroll', onScroll, {passive:true});
  window.addEventListener('resize', onScroll);
  update();
})();
