/* ── ПЭ / FOOTER-PIN — наезд синего футера на последнюю секцию контента
   (страницы без s10-формы: контакты, проекты, статьи, нормативная база).
   Механика s10-формы каталога, применённая к контенту: контент пинится
   низом к нижнему краю экрана (top = innerHeight − height), футер
   накатывает поверх. Футер приходит из footer.php ПОСЛЕ .pg — переносим
   .footer-zone внутрь .pg (sticky-родителем body с height:100% быть не
   может), двойной отступ гасит base.css (.pg .site-footer{padding-left:0}).
   Обёртка наследует роль flex-колонки (flex:1) — короткий контент
   дотягивается до низа вьюпорта, и наезд начинается ровно тогда,
   когда контент долистан. ── */
(function(){
  var zone=document.querySelector('.footer-zone');
  var pg=document.querySelector('.pg');
  if(!zone||!pg)return;
  pg.appendChild(zone);
  var wrap=document.createElement('div');
  wrap.className='pg-sticky-content';
  while(pg.firstChild!==zone){wrap.appendChild(pg.firstChild);}
  pg.insertBefore(wrap,zone);
  wrap.style.display='flex';
  wrap.style.flexDirection='column';
  wrap.style.flex='1 0 auto';
  wrap.style.position='sticky';
  wrap.style.zIndex='5';
  function setTop(){
    wrap.style.top=(window.innerHeight-wrap.offsetHeight)+'px';
  }
  setTop();
  window.addEventListener('resize',setTop,{passive:true});
  window.addEventListener('load',setTop);
})();
