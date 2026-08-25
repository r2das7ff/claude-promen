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
    var t=(window.innerHeight-wrap.offsetHeight)+'px';
    /* Лишняя запись — лишняя инвалидация стиля на обёртке в 20 000px. */
    if(wrap.style.top!==t)wrap.style.top=t;
  }
  setTop();

  /* На телефоне высота вьюпорта меняется сама, когда браузер прячет и
     показывает адресную строку. Пересчёт по такому resize сдвигал точку
     пиннинга прямо во время прокрутки — контент дёргался на ровном месте.
     Реагируем на смену ширины (поворот, ресайз окна) и на крупные скачки
     высоты, мелкие игнорируем: неточность в сотню пикселей незаметна,
     рывок заметен. */
  var lastW=window.innerWidth,lastH=window.innerHeight;
  window.addEventListener('resize',function(){
    if(window.innerWidth===lastW&&Math.abs(window.innerHeight-lastH)<140)return;
    lastW=window.innerWidth;lastH=window.innerHeight;
    setTop();
  },{passive:true});
  window.addEventListener('load',setTop);

  /* top считается от высоты контента, а она меняется без изменения окна:
     «Показать ещё» в реестре нормативов, фильтры, аккордеоны, догрузка
     картинок. Со старым top обёртка пиннилась раньше времени — контент
     замирал на пол-экрана прокрутки, а последние карточки так и оставались
     под футером. Пересчитываем по фактическому изменению размера.

     Синхронно, без rAF. Наблюдатель будит нас после раскладки, но ДО
     отрисовки — правка top успевает в тот же кадр. Через rAF она уезжала
     в следующий, и один кадр обёртка стояла со старым значением: на первом
     же «Показать ещё» это 1754px расхождения, то есть видимый скачок.
     Зацикливания нет: top у sticky-элемента не меняет его собственную
     высоту, а значит и повторного срабатывания наблюдателя не вызывает.
     Отложенный вызов был вреден и вторым концом: пока вкладка в фоне, rAF
     не выполняется, флаг pending залипал, и все изменения размера до
     возвращения на вкладку терялись. */
  if('ResizeObserver' in window){
    new ResizeObserver(setTop).observe(wrap);
  }
})();
