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

/* ── Оглавление на ≤1024: колонка TOC в одноколоночной сетке падала под
   статью, где навигация уже бесполезна. Переносим бокс первым элементом
   .ar-split и сворачиваем в аккордеон; после перехода по ссылке список
   сворачивается. CTA-бокс остаётся в aside после статьи. Разметка статей
   живёт в БД — весь перенос делается здесь, контент не меняется. ── */
(()=>{
  const split = document.querySelector('.ar-split');
  const box = document.querySelector('.ar-toc-box');
  if(!split || !box) return;
  const lbl = box.querySelector('.ar-toc-lbl');
  const list = box.querySelector('.ar-toc-list');
  if(!lbl || !list) return;
  const count = document.createElement('span');
  count.className = 'ar-toc-count';
  count.textContent = String(list.querySelectorAll('a').length).padStart(2,'0');
  const mq = window.matchMedia('(max-width:1024px)');
  const home = { parent: box.parentElement, next: box.nextElementSibling };
  let wrap = null;
  const collapse = (v)=>{
    box.classList.toggle('is-collapsed', v);
    lbl.setAttribute('aria-expanded', v ? 'false' : 'true');
  };
  const toggle = ()=>{ if(wrap) collapse(!box.classList.contains('is-collapsed')); };
  function apply(){
    if(mq.matches && !wrap){
      wrap = document.createElement('div');
      wrap.className = 'ar-toc-top';
      wrap.appendChild(box);
      split.insertBefore(wrap, split.firstChild);
      lbl.appendChild(count);
      lbl.setAttribute('role','button');
      lbl.setAttribute('tabindex','0');
      if(list.id) lbl.setAttribute('aria-controls', list.id);
      collapse(true);
    } else if(!mq.matches && wrap){
      home.parent.insertBefore(box, home.next);
      wrap.remove(); wrap = null;
      count.remove();
      ['role','tabindex','aria-controls','aria-expanded'].forEach(a=>lbl.removeAttribute(a));
      box.classList.remove('is-collapsed');
    }
  }
  lbl.addEventListener('click', toggle);
  lbl.addEventListener('keydown', (e)=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); toggle(); } });
  list.addEventListener('click', (e)=>{ if(wrap && e.target.closest('a')) collapse(true); });
  apply();
  if(mq.addEventListener) mq.addEventListener('change', apply);
})();

