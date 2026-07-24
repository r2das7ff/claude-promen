/* ── ПЭ / STATI — список статей: массив ARTICLES, рендер карточек,
   фильтры по рубрикам. Порт инлайна html/stati.html (2026-07-23);
   часы — chrome.js; URL и ассеты — window.promenStati. ── */
var PS_ASSETS = ((window.promenStati || {}).assets || '');
function PS_URL(slug) {
  var u = (window.promenStati || {}).articles || {};
  return u[slug] || '#';
}
const ARTICLES = [
  {
    id:'vybor-stali', cat:'materials', catLabel:'Материаловедение',
    title:'Как выбрать сталь для соединительных деталей трубопровода',
    excerpt:'Углеродистые, жаропрочные и нержавеющие стали: какая марка выдержит параметры вашего трубопровода и почему её нельзя выбрать «на глаз».',
    date:'14.07.2026', read:'8 мин', img:PS_ASSETS+'img/photos/promen-photo-hor-1.jpg',
    href:PS_URL('vybor-stali')
  },
  {
    id:'otvod-svarnoy-besshovnyy', cat:'production', catLabel:'Производство',
    title:'Бесшовные и сварные отводы: в чём разница и когда что выбирать',
    excerpt:'Два способа изготовления одной и той же детали дают разную прочность, цену и срок поставки. Разбираем, когда оправдан каждый.',
    date:'11.07.2026', read:'7 мин', img:PS_ASSETS+'img/photos/promen-photo-7.jpg',
    href:PS_URL('otvod-svarnoy-besshovnyy'), featured:true
  },
  {
    id:'kontrol-kachestva', cat:'quality', catLabel:'Контроль качества',
    title:'Контроль качества СДТ: от входного контроля до паспорта изделия',
    excerpt:'Восемь этапов, через которые проходит каждая деталь на заводе — от идентификации плавки металла до финального пакета документов.',
    date:'08.07.2026', read:'9 мин', img:PS_ASSETS+'img/photos/promen-photo-6.jpg',
    href:PS_URL('kontrol-kachestva')
  },
  {
    id:'normativnaya-baza', cat:'standards', catLabel:'Нормативы',
    title:'ГОСТ, ОСТ и ТУ на соединительные детали трубопровода',
    excerpt:'Как устроена многоуровневая система стандартов в трубопроводной арматуре и почему для АЭС и ТЭС одного ГОСТа часто недостаточно.',
    date:'03.07.2026', read:'6 мин', img:PS_ASSETS+'img/photos/promen-photo-hor-4.jpg',
    href:PS_URL('normativnaya-baza')
  },
  {
    id:'chertezh-zakazchika', cat:'production', catLabel:'Производство',
    title:'Изготовление по чертежам заказчика: что нужно передать заводу',
    excerpt:'Минимальный комплект конструкторской документации, без которого завод не сможет посчитать срок и стоимость единичной партии.',
    date:'28.06.2026', read:'6 мин', img:PS_ASSETS+'img/photos/promen-photo-5.jpg',
    href:PS_URL('chertezh-zakazchika')
  },
  {
    id:'postavka-aes-tes', cat:'projects', catLabel:'Проекты',
    title:'Поставки для АЭС и ТЭС: требования и особенности',
    excerpt:'Чем поставка для атомной и тепловой энергетики отличается от промышленного заказа — прослеживаемость, НК и сроки крупных партий.',
    date:'22.06.2026', read:'8 мин', img:PS_ASSETS+'img/photos/promen-photo-hor-3.jpg',
    href:PS_URL('postavka-aes-tes')
  }
];

const CATEGORIES = [
  {key:'all', label:'Все'},
  {key:'materials', label:'Материаловедение'},
  {key:'production', label:'Производство'},
  {key:'quality', label:'Контроль качества'},
  {key:'standards', label:'Нормативы'},
  {key:'projects', label:'Проекты'}
];

let activeCat = 'all';

function countFor(key){
  if(key==='all') return ARTICLES.length;
  return ARTICLES.filter(a=>a.cat===key).length;
}

function renderChips(){
  const el = document.getElementById('blChips');
  el.innerHTML = CATEGORIES.map(c=>
    `<button class="bl-chip${activeCat===c.key?' active':''}" data-cat="${c.key}">${c.label}<span class="bl-chip-n">${countFor(c.key)}</span></button>`
  ).join('');
  el.querySelectorAll('.bl-chip').forEach(btn=>{
    btn.addEventListener('click', ()=>{ activeCat = btn.dataset.cat; renderChips(); renderList(); });
  });
}

function renderList(){
  const list = activeCat==='all' ? ARTICLES : ARTICLES.filter(a=>a.cat===activeCat);
  const featured = list.find(a=>a.featured) || list[0];
  const rest = list.filter(a=>a!==featured);

  document.getElementById('blFilterCount').textContent = `${String(list.length).padStart(2,'0')} ${list.length===1?'МАТЕРИАЛ':'МАТЕРИАЛОВ'}`;

  const featuredEl = document.getElementById('blFeatured');
  if(featured){
    featuredEl.innerHTML = `
      <a class="bl-featured" href="${featured.href}">
        <div class="bl-featured-media">
          <span class="bl-featured-badge">Рекомендуем</span>
          <img src="${featured.img}" alt="${featured.title}" loading="lazy">
        </div>
        <div class="bl-featured-body">
          <span class="bl-featured-tag">${featured.catLabel}</span>
          <h2 class="bl-featured-title">${featured.title}</h2>
          <p class="bl-featured-excerpt">${featured.excerpt}</p>
          <div class="bl-featured-meta"><span>${featured.date}</span><span>Чтение · ${featured.read}</span></div>
          <span class="bl-featured-link">Читать статью →</span>
        </div>
      </a>`;
  } else {
    featuredEl.innerHTML = '';
  }

  document.getElementById('blGrid').innerHTML = rest.map(a=>`
    <a class="bl-card" href="${a.href}">
      <div class="bl-card-media">
        <span class="bl-card-cat">${a.catLabel}</span>
        <img src="${a.img}" alt="${a.title}" loading="lazy">
      </div>
      <div class="bl-card-body">
        <h3 class="bl-card-title">${a.title}</h3>
        <p class="bl-card-excerpt">${a.excerpt}</p>
        <div class="bl-card-meta"><span>${a.date}</span><span>${a.read}</span></div>
      </div>
    </a>
  `).join('');
}

renderChips();
renderList();
