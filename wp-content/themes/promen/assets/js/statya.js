/* ── ПЭ / STATYA — детальная статья: скролл-спай оглавления
   (IntersectionObserver). Порт инлайна html/statya-*.html
   (2026-07-23); часы — chrome.js. ── */
(()=>{
  const links = document.querySelectorAll('#tocList a');
  const map = new Map();
  links.forEach(a=>{ const id=a.getAttribute('href').slice(1); const h=document.getElementById(id); if(h) map.set(h,a); });
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      const a = map.get(entry.target);
      if(!a) return;
      if(entry.isIntersecting) links.forEach(l=>l.classList.remove('active')), a.classList.add('active');
    });
  }, { rootMargin:'-88px 0px -70% 0px', threshold:0 });
  map.forEach((a,h)=>io.observe(h));
})();

