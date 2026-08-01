/* ════════════════════════════════════════════════════════════
   PROM-EN front.js — главная: sidenav-скроллспай, sfk-карточки,
   аккордеоны s2/s6, параллакс, геокарта s4 (canvas), таймлайн s5
   (GSAP ScrollTrigger), s7/s8/s9, прогресс-бар. Источник —
   инлайн-скрипты html/hero-variant-d.html (2026-07-22); часы и
   бургер живут в chrome.js, форма s10 — серверная (footer.php).
   Конфиг: window.promenFront { assets, catalogUrl, projects{} }.
   ════════════════════════════════════════════════════════════ */
var PF_ASSETS = ((window.promenFront || {}).assets || '');
function PF_PROJECT(slug) {
  var p = (window.promenFront || {}).projects || {};
  return p[slug] || '';
}

document.addEventListener('DOMContentLoaded', function(){
  var items = document.querySelectorAll('.sidenav-item');
  var targets = ['hero','industries','cycle','directions','geography','passport','drawings','parameters','quality','documents','request'];

  var idxMap = {};
  targets.forEach(function(id, i){ idxMap[id] = i; });

  var visibleSet = {};
  var currentActive = null;
  var labelTimer = null;

  function showLabelFor(item){
    if(labelTimer){ clearTimeout(labelTimer); labelTimer = null; }
    items.forEach(function(it){ it.classList.remove('sn-show-label'); });
    if(!item) return;
    item.classList.add('sn-show-label');
    labelTimer = setTimeout(function(){
      item.classList.remove('sn-show-label');
      labelTimer = null;
    }, 1500);
  }

  function updateActive(){
    var active = null;
    for(var i = 0; i < targets.length; i++){
      if(visibleSet[targets[i]]){ active = targets[i]; break; }
    }
    items.forEach(function(it, i){
      var isActive = active !== null && idxMap[active] === i;
      if(isActive){
        it.classList.add('sn-active');
      } else {
        it.classList.remove('sn-active');
      }
    });
    if(active !== currentActive){
      currentActive = active;
      showLabelFor(active !== null ? items[idxMap[active]] : null);
    }
  }

  if(!('IntersectionObserver' in window)) return;

  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){
        visibleSet[e.target.id] = true;
      } else {
        delete visibleSet[e.target.id];
      }
    });
    updateActive();
  }, { rootMargin: '-20% 0px -20% 0px', threshold: 0 });

  targets.forEach(function(id){
    var el = document.getElementById(id);
    if(el) io.observe(el);
  });
});

(function(){
  if(!('IntersectionObserver' in window)) return;
  var cards = document.querySelectorAll('.sfk-card-hd, .sfk-card-bd');
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){
        var idx = Array.from(cards).indexOf(e.target);
        // Правая карточка с задержкой 120ms
        var delay = (idx === 0 || idx === 1) ? idx * 0 : 0;
        if(e.target.classList.contains('sfk-card-hd')){
          // Первая hd сразу, вторая hd с задержкой
          var siblingDelay = e.target.closest('.sfk-card') === document.querySelectorAll('.sfk-card')[1] ? 140 : 0;
          setTimeout(function(){ e.target.classList.add('visible'); }, siblingDelay);
        } else {
          var card = e.target.closest('.sfk-card');
          var isSecond = card === document.querySelectorAll('.sfk-card')[1];
          setTimeout(function(){ e.target.classList.add('visible'); }, isSecond ? 260 : 120);
        }
        io.unobserve(e.target);
      }
    });
  }, {threshold: 0.2});
  cards.forEach(function(el){ io.observe(el); });
})();

// Section 2 — accordion (JS-measured height so stacked mobile content never clips)
(function s2() {
  const rows = document.querySelectorAll('.s2-row');
  function setOpen(row) {
    const panel = row.querySelector('.sr-panel');
    if (panel) panel.style.maxHeight = panel.scrollHeight + 'px';
  }
  function setClosed(row) {
    const panel = row.querySelector('.sr-panel');
    if (panel) panel.style.maxHeight = '';
  }
  rows.forEach(row => {
    row.addEventListener('click', () => {
      if (row.classList.contains('active')) return;
      rows.forEach(r => { r.classList.remove('active'); setClosed(r); });
      row.classList.add('active');
      setOpen(row);
    });
  });
  // Re-measure the open panel on resize/rotate — 2-col desktop panel becomes
  // a taller 1-col stack on mobile/tablet and the height must follow it.
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      const active = document.querySelector('.s2-row.active');
      if (active) setOpen(active);
    }, 120);
  });
  document.querySelectorAll('.s2-row.active').forEach(setOpen);
})();

// 40 вечных циклов пиксельного облака — на паузу вне вьюпорта
// (сами циклы остаются, см. .pixel-cloud.is-off в front.css)
(function pixelCloudGate() {
  const pc = document.querySelector('.pixel-cloud');
  if (!pc || !('IntersectionObserver' in window)) return;
  const io = new IntersectionObserver(entries => {
    pc.classList.toggle('is-off', !entries[0].isIntersecting);
  }, { rootMargin: '120px' });
  io.observe(pc);
})();

// Cursor parallax on the right visual panel
(function parallax() {
  const right = document.querySelector('.right');
  const wrap = document.querySelector('.pe-wrap');
  const cloud = document.querySelector('.pixel-cloud');
  if (!right || !wrap) return;
  document.addEventListener('mousemove', e => {
    const rx = right.getBoundingClientRect();
    if (e.clientX < rx.left) return;
    const cx = (e.clientX - rx.left) / rx.width - .5;
    const cy = (e.clientY - rx.top)  / rx.height - .5;
    wrap.style.transform  = `translate(${cx * -16}px, ${cy * -12}px)`;
    if (cloud) cloud.style.transform = `translate(-50%, -50%) translate(${cx * 8}px, ${cy * 6}px)`;
  });
})();

// ── Section 4 · Geography map ────────────────────────────────
(function geoMap() {
  const canvas = document.getElementById('s4-canvas');
  const container = document.getElementById('s4-wrap');
  if (!canvas || !container) return;

  /* Projection: -5°W–140°E, 5°N–78°N */
  const LON_MIN = -5, LON_MAX = 140, LAT_MIN = 5, LAT_MAX = 78;
  const lonToX = (lon, w) => ((lon - LON_MIN) / (LON_MAX - LON_MIN)) * w;
  const latToY = (lat, h) => ((LAT_MAX - lat) / (LAT_MAX - LAT_MIN)) * h;
  const xToLon = (x, w) => (x / w) * (LON_MAX - LON_MIN) + LON_MIN;
  const yToLat = (y, h) => LAT_MAX - (y / h) * (LAT_MAX - LAT_MIN);

  const HUB = {
    id: 'hub', label: 'Челябинск', sublabel: 'Штаб-квартира',
    lon: 61.4, lat: 55.2, international: false,
    labelSide: 'top', labelOff: 25, labelShiftY: -8, labelShiftX: 0,
  };

  const destinations = [
    { id: 'ruppur',  label: 'АЭС «Руппур»',    sublabel: 'Бангладеш', lon: 89.6, lat: 21.4, international: true,  labelSide: 'right',  labelOff: 30, labelShiftY: 0,   labelShiftX: 0 },
    { id: 'akkuju',  label: 'АЭС «Аккую»',     sublabel: 'Турция',    lon: 33.8, lat: 36.1, international: true,  labelSide: 'bottom', labelOff: 25, labelShiftY: 0,   labelShiftX: 0 },
    { id: 'suvorov', label: 'Черепетская ГРЭС', sublabel: 'Суворов',   lon: 36.6, lat: 54.1, international: false, labelSide: 'top',    labelOff: 22, labelShiftY: -12, labelShiftX: -30 },
    { id: 'omsk',    label: 'ТЭЦ-3',            sublabel: 'Омск',      lon: 73.4, lat: 55.0, international: false, labelSide: 'right',  labelOff: 30, labelShiftY: 0,   labelShiftX: 0 },
    { id: 'kursk',   label: 'Курская АЭС-2',   sublabel: 'Курск',     lon: 36.2, lat: 51.7, international: false, labelSide: 'left',   labelOff: 25, labelShiftY: 8,   labelShiftX: 0 },
  ];

  const routes = [
    { to: destinations[0], delay: 0 },
    { to: destinations[1], delay: 0.4 },
    { to: destinations[2], delay: 0.8 },
    { to: destinations[3], delay: 1.1 },
    { to: destinations[4], delay: 1.4 },
  ];

  const regionPolygons = [
    [[32,70.5],[36,69.5],[40,68.5],[45,68],[50,68.5],[55,69],[60,68],[65,67],[70,67],[73,69],[77,71],[80,72],[84,73.5],[88,75],[93,76],[97,75],[102,77],[107,75],[112,73],[117,73],[122,72],[127,71],[132,68],[136,64],[140,60],[139,56],[135,50],[132,48],[128,47],[125,48],[120,50],[115,50],[110,48],[105,50],[100,50],[95,50],[90,47],[85,48],[80,50],[75,53],[70,52],[65,52],[60,54],[55,50],[50,47],[48,43],[46,42],[44,42],[42,42],[40,43],[38,44.5],[37,46],[36,47],[35,48],[34,49],[33,50],[32,51],[31,52],[30,54],[29,56],[28,58],[29,60],[30,63],[30,67],[32,70.5]],
    [[5,58],[5,62],[7,63.5],[10,65],[13,67],[16,69],[19,70],[23,71],[27,71],[30,70],[28,67],[28,64],[28,61],[28,60],[26,59],[24,58],[20,57],[18,56],[15,55],[12,56],[10,57],[8,57.5],[5,58]],
    [[-5,50.5],[-3.5,50.5],[-3,51.5],[-4,53],[-3.5,54.5],[-4,56.5],[-3,58],[-2,58.5],[0,58],[1.5,56],[2,54],[1.5,52],[1,51],[0,50.5],[-2,50.5],[-5,50.5]],
    [[-10,51.5],[-9.5,52.5],[-9,53.5],[-8,54.5],[-7,55.5],[-6,54.5],[-6,53],[-7,51.5],[-10,51.5]],
    [[-9.5,37],[-8,37.5],[-9,39.5],[-9,42],[-8,43.5],[-2,43.5],[0,43],[3,43],[3,42.5],[1,41],[-1,40],[-3,38],[-5.5,36.5],[-7.5,37],[-9.5,37]],
    [[-4,48.5],[-2,49],[0,49],[2,49.5],[4,49.5],[5,48],[6,47.5],[7,48],[9,48],[11,48],[13,48.5],[15,49],[17,49],[19,49.5],[21,49.5],[23,50],[24,52],[24,54],[23,55.5],[21,56],[19,55.5],[17,54.5],[15,53.5],[13,52.5],[12,51],[10,49],[8,48],[6,47.5],[4,47.5],[2,48],[0,48],[-2,48],[-4,48.5]],
    [[7.5,44],[9,44],[11,44.5],[13,46],[14,46.5],[16,46],[15,44.5],[13.5,43],[13,42],[13,41],[14,40],[15,39],[16,38.5],[16.5,39.5],[15.5,41],[14,42.5],[12.5,44],[10,44.5],[7.5,44]],
    [[20,40.5],[22,40],[24,41.5],[26,42],[28,42],[28,40],[27,39],[26,38],[25,37.5],[24,37],[23,36],[22,36.5],[21,37],[20,38],[20,40.5]],
    [[26,42],[27.5,41.5],[29,41.5],[31,41.5],[33,41.5],[35,41.5],[37,41],[39,41],[40,41],[41,41],[42.5,40.5],[43.5,40],[44,39],[44,38],[43.5,37.5],[42.5,37],[41,36.5],[40,36.5],[38,36.5],[36.5,37],[35,36.5],[34,36.5],[33,36.5],[31.5,36.5],[30,37],[29,37.5],[28,38],[27,39],[26.5,40],[26,42]],
    [[40,43],[41,42.5],[42,42],[43,41.5],[44,41],[46,41.5],[48,41],[50,41],[51,42],[50,43],[48,43],[46,42.5],[44,42],[42,42],[40,43]],
    [[34,42],[36,42],[38,41.5],[40,41],[42,40],[44,39],[46,38],[48,37.5],[50,37],[52,36.5],[54,36],[56,36],[58,36],[60,35.5],[62,35],[63,34],[64,33],[65,32],[66,31],[66,29.5],[65.5,28],[65,27],[64,26],[63,25.5],[62,25.5],[61,25.5],[60,25.5],[59,25.5],[58,26],[57,26.5],[56,27],[55,28],[54,29],[52,30.5],[50,32],[48,34],[46,36],[44,38],[42,39.5],[40,41],[38,41.5],[36,42],[34,42]],
    [[35,30],[36,28.5],[37,27],[38,26],[39,25],[41,23],[43,21],[44.5,19],[45.5,17],[47,15.5],[49,15],[50,16],[52,17],[54,19],[55.5,21],[56,23],[55.5,25],[54,27],[52,29],[50,30],[48,32],[46,34],[44,36],[42,37],[40,38],[38,39],[36,40],[34.5,39],[33,37],[33,34.5],[34,32],[35,30]],
    [[50,42],[52,42],[54,44],[55,47],[55,50],[54,52],[56,53],[58,53],[60,54],[63,54],[66,53],[69,52],[72,51],[75,52],[78,51],[80,50],[83,49],[85,48],[85,46],[84,44],[82,42],[80,40],[78,39],[76,38],[74,37.5],[72,37],[70,37],[68,37],[66,37],[64,36],[63,35],[62,35],[60,35.5],[58,36],[56,36],[54,36],[52,36.5],[50,37],[48,37.5],[46,38],[48,40],[50,42]],
    [[61,37],[63,37.5],[66,37],[69,37],[71,36.5],[74,37.5],[75.5,36],[74.5,34],[73.5,32],[72.5,30],[71.5,28],[70.5,26],[69,25],[67,24.5],[65,25],[63.5,26],[62,28],[61.5,30],[61,32],[61,34],[61,37]],
    [[68,35.5],[72,35],[76,34],[80,32],[84,28],[87,26],[88.5,24],[90,22.5],[92,22],[94,23.5],[96,26.5],[96,22],[94,20],[92,18.5],[90,16.5],[88,14.5],[86,13],[84,12],[82,11],[80,8.5],[78,10],[76,14],[74,18.5],[72,22.5],[70,26.5],[68,30],[68,35.5]],
    [[80,10],[80.5,8],[81,7],[82,6.5],[82,7.5],[81.5,9],[80,10]],
    [[75,40],[78,41],[80,42],[83,45],[87,47],[90,48],[95,49],[100,49],[105,50],[110,48],[115,50],[120,50],[125,48],[128,47],[130,48],[133,48],[135,49],[134,46],[131,43],[128,41],[125,39],[122,37],[120,35],[118,32],[116,29],[114,26],[112,23],[110,21],[108,19],[106,18],[104,17],[102,18],[100,20],[98,22],[96,24],[94,23],[92,22],[90,24],[87,27],[84,29],[82,30],[80,32],[78,34],[76,36],[75,38],[75,40]],
    [[88,47],[90,48],[95,49],[100,49],[105,50],[110,48],[115,47],[112,44],[110,42],[107,42],[104,43],[100,44],[97,46],[93,47],[88,47]],
    [[130,31],[131,33],[132,34],[133,35],[135,36],[137,37],[139,38],[140,40],[141,41.5],[142,43],[141,44],[140,42],[139,39],[137,36],[135,34],[133,33],[132,31],[130,31]],
    [[126,34],[127,36],[128,37],[129,38.5],[130,38],[129,36],[128,35],[127,34],[126,34]],
  ];

  function pointInPoly(px, py, poly) {
    let inside = false;
    for (let i = 0, j = poly.length - 1; i < poly.length; j = i++) {
      const xi = poly[i][0], yi = poly[i][1];
      const xj = poly[j][0], yj = poly[j][1];
      if (((yi > py) !== (yj > py)) && (px < ((xj - xi) * (py - yi)) / (yj - yi) + xi)) inside = !inside;
    }
    return inside;
  }

  function isLand(lon, lat) {
    for (const poly of regionPolygons) if (pointInPoly(lon, lat, poly)) return true;
    return false;
  }

  function quadBez(t, x0, y0, cx, cy, x1, y1) {
    const u = 1 - t;
    return { x: u*u*x0 + 2*u*t*cx + t*t*x1, y: u*u*y0 + 2*u*t*cy + t*t*y1 };
  }
  function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

  function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x+r,y); ctx.lineTo(x+w-r,y); ctx.quadraticCurveTo(x+w,y,x+w,y+r);
    ctx.lineTo(x+w,y+h-r); ctx.quadraticCurveTo(x+w,y+h,x+w-r,y+h);
    ctx.lineTo(x+r,y+h); ctx.quadraticCurveTo(x,y+h,x,y+h-r);
    ctx.lineTo(x,y+r); ctx.quadraticCurveTo(x,y,x+r,y); ctx.closePath();
  }

  function getColors() {
    return {
      dotBase: 'rgba(120,140,170,0.75)',
      dotHover: 'rgba(140,160,190,0.95)',
      accent: '#5A7A94', accentRgb: '90,122,148',
      hubColor: '#6D8CA6',
      intlColor: '#6D8CA6',
      localColor: 'rgba(30,61,92,0.65)',
      labelText: '#0F2A44',
      subLabelText: 'rgba(30,61,92,0.55)',
      labelBg: 'rgba(255,255,255,0.94)',
      routeGlow: 'rgba(109,140,166,0.08)',
      gridLine: 'rgba(109,140,166,0.05)',
      tooltipBg: 'rgba(255,255,255,0.96)',
      tooltipBorder: 'rgba(109,140,166,0.18)',
      tooltipShadow: 'rgba(0,0,0,0.10)',
      connectorLine: 'rgba(109,140,166,0.35)',
    };
  }

  function drawLabel(ctx, dotX, dotY, loc, C, alpha, isHub, isHovered, w, h, blurBuf, dpr) {
    const { label, sublabel, labelSide, labelOff, labelShiftY, labelShiftX } = loc;
    const shiftY = labelShiftY || 0, shiftX = labelShiftX || 0;
    const fontSize = isHub ? (isHovered ? 17 : 16) : (isHovered ? 15 : 14);
    const subFontSize = isHub ? 10 : 9.5;
    const family = 'ui-sans-serif, system-ui, sans-serif';
    const mono = 'ui-monospace, monospace';
    ctx.font = `${isHub ? '700' : '600'} ${fontSize}px ${family}`;
    const labelW = ctx.measureText(label).width;
    ctx.font = `500 ${subFontSize}px ${mono}`;
    const subW = ctx.measureText(sublabel).width;
    const textW = Math.max(labelW, subW);
    const padX = 10, padY = 6;
    const pillW = textW + padX * 2;
    const pillH = fontSize + subFontSize + padY * 2 + 2;
    const gap = labelOff + (isHovered ? 8 : 0);
    let pillX, pillY, lineStartX, lineStartY, lineEndX, lineEndY;
    if (labelSide === 'top') {
      pillX = dotX - pillW/2; pillY = dotY - gap - pillH;
      lineStartX = dotX; lineStartY = dotY - 7; lineEndX = pillX + pillW/2; lineEndY = pillY + pillH;
    } else if (labelSide === 'left') {
      pillX = dotX - gap - pillW; pillY = dotY - pillH/2;
      lineStartX = dotX - 7; lineStartY = dotY; lineEndX = pillX + pillW; lineEndY = pillY + pillH/2;
    } else if (labelSide === 'right') {
      pillX = dotX + gap; pillY = dotY - pillH/2;
      lineStartX = dotX + 7; lineStartY = dotY; lineEndX = pillX; lineEndY = pillY + pillH/2;
    } else {
      pillX = dotX - pillW/2; pillY = dotY + gap;
      lineStartX = dotX; lineStartY = dotY + 7; lineEndX = pillX + pillW/2; lineEndY = pillY;
    }
    pillX = Math.max(4, Math.min(w - pillW - 4, pillX + shiftX));
    pillY = Math.max(4, Math.min(h - pillH - 4, pillY + shiftY));
    if (shiftY) { lineStartY += shiftY; lineEndY += shiftY; }
    if (shiftX) { lineStartX += shiftX; lineEndX += shiftX; }
    ctx.globalAlpha = alpha;
    ctx.beginPath(); ctx.setLineDash([3,3]);
    ctx.moveTo(lineStartX, lineStartY); ctx.lineTo(lineEndX, lineEndY);
    ctx.strokeStyle = C.connectorLine; ctx.lineWidth = 1; ctx.stroke(); ctx.setLineDash([]);
    const bCtx = blurBuf.getContext('2d');
    const expand = 12;
    const srcX = Math.max(0, pillX - expand), srcY = Math.max(0, pillY - expand);
    const srcW = Math.min(w - srcX, pillW + expand*2), srcH = Math.min(h - srcY, pillH + expand*2);
    blurBuf.width = srcW; blurBuf.height = srcH;
    bCtx.filter = 'blur(10px)';
    bCtx.drawImage(ctx.canvas, srcX*dpr, srcY*dpr, srcW*dpr, srcH*dpr, 0, 0, srcW, srcH);
    bCtx.filter = 'none';
    ctx.save();
    roundRect(ctx, pillX, pillY, pillW, pillH, 10); ctx.clip();
    ctx.drawImage(blurBuf, 0, 0, srcW, srcH, srcX, srcY, srcW, srcH);
    ctx.fillStyle = 'rgba(255,255,255,0.55)'; ctx.fillRect(pillX, pillY, pillW, pillH);
    const hlGrad = ctx.createLinearGradient(pillX, pillY, pillX, pillY + pillH);
    hlGrad.addColorStop(0, 'rgba(255,255,255,0.35)'); hlGrad.addColorStop(0.4, 'rgba(255,255,255,0)');
    ctx.fillStyle = hlGrad; ctx.fillRect(pillX, pillY, pillW, pillH);
    ctx.restore();
    roundRect(ctx, pillX, pillY, pillW, pillH, 10);
    ctx.strokeStyle = 'rgba(255,255,255,0.6)'; ctx.lineWidth = 1; ctx.stroke();
    ctx.beginPath(); ctx.moveTo(pillX+4, pillY+8); ctx.lineTo(pillX+4, pillY+pillH-8);
    ctx.strokeStyle = isHub ? C.accent : `rgba(${C.accentRgb},0.55)`;
    ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
    const tx = pillX + padX + 5;
    ctx.font = `${isHub ? '700' : '600'} ${fontSize}px ${family}`;
    ctx.fillStyle = '#0F2A44'; ctx.textBaseline = 'alphabetic';
    ctx.fillText(label, tx, pillY + padY + fontSize);
    ctx.font = `500 ${subFontSize}px ${mono}`;
    ctx.fillStyle = 'rgba(30,61,92,0.7)';
    ctx.fillText(sublabel, tx, pillY + padY + fontSize + 2 + subFontSize);
    ctx.globalAlpha = 1;
  }

  function buildLandMask(w, h, step) {
    const cols = Math.floor(w / step), rows = Math.floor(h / step);
    const mask = [];
    for (let r = 0; r < rows; r++) {
      mask[r] = [];
      for (let c = 0; c < cols; c++) {
        const dx = (c+0.5)*step, dy = (r+0.5)*step;
        mask[r][c] = isLand(xToLon(dx,w), yToLat(dy,h));
      }
    }
    return mask;
  }

  const ctx = canvas.getContext('2d', { alpha: true });
  if (!ctx) return;
  const dpr = Math.min(window.devicePixelRatio || 1, 2);
  let rafId = 0;
  /* Цикл живёт, только пока карта во вьюпорте — см. IntersectionObserver в
     конце модуля. До 2026-07-31 rAF крутился с загрузки до закрытия вкладки
     (полная перерисовка с градиентами ~60 раз/с), cancelAnimationFrame не
     вызывался вовсе. При prefers-reduced-motion постоянного цикла нет:
     кадр рисуется разово — по появлению, ресайзу и взаимодействию. */
  let running = false;
  const reduceMq = window.matchMedia('(prefers-reduced-motion: reduce)');
  const startTime = performance.now();
  let hoveredId = null;
  let mask = null, maskW = 0, maskH = 0, maskStep = 0;
  const blurBuf = document.createElement('canvas');
  const mouse = { x: -1000, y: -1000 };
  const C = getColors();

  /* ── Project info cards (source: prom-en.com/proekty/*) ── */
  const tooltipEl = document.getElementById('s4Tooltip');
  const CATALOG_HREF = (window.promenFront && window.promenFront.catalogUrl) || '/catalog/';
  const icons = {
    nuclear: `<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMax slice">
      <defs><linearGradient id="s4ImgSky" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#E8ECF0"/><stop offset="1" stop-color="#AEBBC9"/>
      </linearGradient></defs>
      <rect x="0" y="0" width="200" height="300" fill="url(#s4ImgSky)"/>
      <ellipse cx="150" cy="72" rx="30" ry="14" fill="#fff" opacity=".55"/>
      <ellipse cx="170" cy="56" rx="22" ry="10" fill="#fff" opacity=".38"/>
      <rect x="28" y="160" width="12" height="110" fill="var(--blue)" opacity=".5"/>
      <rect x="46" y="140" width="12" height="130" fill="var(--blue)" opacity=".68"/>
      <rect x="110" y="210" width="80" height="60" fill="var(--dark)"/>
      <path d="M110,210 A40,40 0 0 1 190,210 Z" fill="var(--dark)"/>
      <rect x="0" y="270" width="200" height="30" fill="var(--dark)"/>
    </svg>`,
    thermal: `<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMax slice">
      <defs><linearGradient id="s4ImgSky2" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#E8ECF0"/><stop offset="1" stop-color="#AEBBC9"/>
      </linearGradient></defs>
      <rect x="0" y="0" width="200" height="300" fill="url(#s4ImgSky2)"/>
      <ellipse cx="100" cy="30" rx="16" ry="8" fill="#fff" opacity=".5"/>
      <ellipse cx="116" cy="18" rx="20" ry="9" fill="#fff" opacity=".38"/>
      <ellipse cx="134" cy="9"  rx="22" ry="8" fill="#fff" opacity=".26"/>
      <polygon points="86,270 102,270 98,40 90,40" fill="var(--blue)" opacity=".65"/>
      <rect x="30" y="190" width="70" height="80" fill="var(--dark)"/>
      <path d="M40,190 L100,270 M100,190 L40,270" stroke="var(--blue)" stroke-width="2" opacity=".35"/>
      <rect x="0" y="270" width="200" height="30" fill="var(--dark)"/>
    </svg>`,
  };
  const projectInfo = {
    kursk: {
      accent: '#1E3D5C', kind: 'nuclear', tag: 'АЭС',
      photo: PF_ASSETS + 'img/projects/kursk2.png',
      sub: 'Курск · Россия',
      status: 'Поставки завершены',
      pulse: false,
      projectHref: PF_PROJECT('proekt-kurskaya-aes'),
      facts: [
        { k: 'Материал', v: 'Сталь 08Х18Н10Т' },
        { k: 'Объём поставки', v: '≈36 т' },
        { k: 'Номенклатура', v: 'Фланцы, колена 45–90°' },
      ],
    },
    suvorov: {
      accent: '#1E3D5C', kind: 'thermal', tag: 'ГРЭС',
      photo: PF_ASSETS + 'img/projects/tec2.png',
      sub: 'Суворов, Тульская обл. · Россия',
      status: 'Поставки завершены',
      pulse: false,
      projectHref: PF_PROJECT('proekt-cherepetskaya-gres'),
      facts: [
        { k: 'Материал', v: 'Сталь 20' },
        { k: 'Объём поставки', v: '≈157 т' },
        { k: 'Диаметр', v: 'Ø25–530 мм' },
      ],
    },
    ruppur: {
      accent: '#6D8CA6', kind: 'nuclear', tag: 'АЭС',
      photo: PF_ASSETS + 'img/projects/rupp.png',
      sub: 'Бангладеш · Международный проект',
      status: 'В стадии строительства',
      pulse: true,
      projectHref: PF_PROJECT('proekt-aes-ruppur'),
      facts: [
        { k: 'Материал', v: 'Сталь 15Х1М1Ф' },
        { k: 'Объём поставки', v: '≈96 т' },
        { k: 'Давление', v: 'До 25 МПа' },
      ],
    },
    akkuju: {
      accent: '#6D8CA6', kind: 'nuclear', tag: 'АЭС',
      photo: PF_ASSETS + 'img/projects/turk2.png',
      sub: 'Мерсин, Турция · Международный проект',
      status: 'В стадии строительства',
      pulse: true,
      projectHref: PF_PROJECT('proekt-aes-akkuyu'),
      facts: [
        { k: 'Материал', v: 'Сталь 20 / 08Х18Н10Т' },
        { k: 'Объём поставки', v: '≈148 т' },
        { k: 'Номенклатура', v: 'Отводы, тройники, переходы' },
      ],
    },
    omsk: {
      accent: '#1E3D5C', kind: 'thermal', tag: 'ТЭЦ',
      photo: PF_ASSETS + 'img/projects/tec3.png',
      sub: 'Омск · Россия',
      status: 'Действующий объект',
      pulse: false,
      projectHref: PF_PROJECT('proekt-teploelektrocentral-tec-3'),
      facts: [
        { k: 'Материал', v: 'Сталь 15Х1М1Ф' },
        { k: 'Объём поставки', v: '≈96 т' },
        { k: 'Давление', v: 'До 25 МПа' },
      ],
    },
  };
  let shownId = null;

  /* Габариты и последняя позиция тултипа: мерить offsetWidth/Height и писать
     left/top каждый кадр rAF-цикла — лишний layout; точка назначения
     статична, пока ховер не сменился. */
  var ttW = 388, ttH = 190, ttLastLeft = null, ttLastTop = null;

  function renderTooltip(dest, info) {
    tooltipEl.style.setProperty('--tt-accent', info.accent);
    const badgeCls = dest.international ? '' : ' local';
    const badgeText = dest.international ? 'Экспорт' : 'РФ';
    tooltipEl.innerHTML =
      `<div class="s4-tt-topbar"></div>` +
      `<div class="s4-tt-body">` +
        `<div class="s4-tt-img">` +
          `<img src="${info.photo}" alt="${dest.label}" loading="eager" referrerpolicy="no-referrer" ` +
            `onerror="this.style.display='none';this.nextElementSibling.style.display='block';">` +
          icons[info.kind].replace('<svg ', '<svg style="display:none" ') +
          `<span class="s4-tt-img-tag">${info.tag}</span>` +
        `</div>` +
        `<div class="s4-tt-content">` +
          `<div class="s4-tt-head">` +
            `<h3 class="s4-tt-label">${dest.label}</h3>` +
            `<span class="s4-tt-badge${badgeCls}">${badgeText}</span>` +
          `</div>` +
          `<span class="s4-tt-sub" title="${info.sub}">${info.sub}</span>` +
          `<div class="s4-tt-status"><span class="s4-tt-dot${info.pulse ? ' pulse' : ''}"></span><span class="s4-tt-status-txt" title="${info.status}">${info.status}</span></div>` +
          `<div class="s4-tt-facts">${info.facts.map(f =>
            `<div class="s4-tt-fact-row"><span class="s4-tt-fact-k" title="${f.k}">${f.k}</span><span class="s4-tt-fact-v" title="${f.v}">${f.v}</span></div>`
          ).join('')}</div>` +
          `<div class="s4-tt-cta">` +
            `<a class="s4-tt-btn" href="${CATALOG_HREF}">Каталог изделий ↗</a>` +
            (info.projectHref ? `<a class="s4-tt-link" href="${info.projectHref}">Страница проекта ↗</a>` : '') +
          `</div>` +
        `</div>` +
      `</div>`;
    ttW = tooltipEl.offsetWidth || 388;
    ttH = tooltipEl.offsetHeight || 190;
    ttLastLeft = null;
    ttLastTop = null;
  }

  function renderMobileList() {
    const wrap = document.getElementById('s4MobileList');
    if (!wrap) return;
    wrap.innerHTML = destinations.map(dest => {
      const info = projectInfo[dest.id];
      const badgeCls = dest.international ? '' : ' local';
      const badgeText = dest.international ? 'Экспорт' : 'РФ';
      return `<div class="s4-ml-card">` +
        `<div class="s4-ml-head">` +
          `<h3 class="s4-ml-title">${dest.label}</h3>` +
          `<span class="s4-ml-badge${badgeCls}">${badgeText}</span>` +
        `</div>` +
        `<span class="s4-ml-sub">${info.sub}</span>` +
        `<div class="s4-ml-status"><span class="s4-ml-dot${info.pulse ? ' pulse' : ''}"></span>${info.status}</div>` +
        `<div class="s4-ml-facts">${info.facts.map(f =>
          `<div class="s4-ml-fact"><span class="k">${f.k}</span><span class="v">${f.v}</span></div>`
        ).join('')}</div>` +
        `<div class="s4-ml-cta">` +
          `<a href="${CATALOG_HREF}">Каталог изделий ↗</a>` +
          (info.projectHref ? `<a href="${info.projectHref}">Страница проекта ↗</a>` : '') +
        `</div>` +
      `</div>`;
    }).join('');
  }
  renderMobileList();

  function positionTooltip(dx, dy, w, h) {
    const tw = ttW, th = ttH;
    let left = dx + 26, top = dy - th / 2;
    if (left + tw > w - 12) left = dx - tw - 26;
    if (left < 12) left = 12;
    if (top < 12) top = 12;
    if (top + th > h - 12) top = h - th - 12;
    if (left === ttLastLeft && top === ttLastTop) return; // точка не сместилась — не пишем layout
    ttLastLeft = left;
    ttLastTop = top;
    tooltipEl.style.left = `${left}px`;
    tooltipEl.style.top = `${top}px`;
  }

  const resize = () => {
    const rect = container.getBoundingClientRect();
    canvas.width  = rect.width  * dpr;
    canvas.height = rect.height * dpr;
    canvas.style.width  = `${rect.width}px`;
    canvas.style.height = `${rect.height}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    /* Маску здесь НЕ сбрасываем: ensureMask сама заметит смену размера.
       Сброс на каждый вызов заставлял window.load (поздние ленивые
       картинки) обнулять валидную маску, и пересборка — isLand по сетке
       ~24k точек × ~500 вершин полигонов, сотни мс — падала в первый же
       rAF-кадр после рестарта цикла: разовый фриз ровно на границе S4/S5. */
  };
  /* Пересборка маски суши — единственная тяжёлая операция модуля.
     Вызывается из draw при фактической смене размера, а при загрузке —
     заранее, в idle, чтобы первый вход карты в вьюпорт её не оплачивал. */
  function ensureMask(w, h) {
    const step = Math.max(4, w / 210);
    if (maskW !== w || maskH !== h || maskStep !== step) {
      mask = buildLandMask(w, h, step); maskW = w; maskH = h; maskStep = step;
    }
    return step;
  }
  (window.requestIdleCallback || function (fn) { setTimeout(fn, 200); })(function () {
    const rect = container.getBoundingClientRect();
    if (rect.width > 0 && rect.height > 0) ensureMask(rect.width, rect.height);
  });
  /* canvas.width= очищает холст, поэтому при reduce после ресайза нужен
     разовый кадр — иначе карта остаётся пустой до взаимодействия.
     resizeAndRedraw/renderOnce — function declarations: вызываются они
     только асинхронно, когда draw ниже уже инициализирован. */
  function resizeAndRedraw() { resize(); if (reduceMq.matches) renderOnce(); }
  resize();
  window.addEventListener('load', resizeAndRedraw);
  const ro = new ResizeObserver(resizeAndRedraw);
  ro.observe(container);

  canvas.addEventListener('mousemove', e => {
    const r = canvas.getBoundingClientRect();
    mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top;
  });
  canvas.addEventListener('mouseleave', () => {
    if (cardHovered) return;
    mouse.x = -1000; mouse.y = -1000; hoveredId = null;
  });

  let cardHovered = false;
  tooltipEl.addEventListener('mouseenter', () => { cardHovered = true; });
  tooltipEl.addEventListener('mouseleave', () => { cardHovered = false; mouse.x = -1000; mouse.y = -1000; hoveredId = null; });

  canvas.addEventListener('touchstart', e => {
    const tp = e.touches[0]; if (!tp) return;
    const r = canvas.getBoundingClientRect();
    mouse.x = tp.clientX - r.left; mouse.y = tp.clientY - r.top;
  }, { passive: true });
  document.addEventListener('touchstart', e => {
    if (!canvas.contains(e.target) && !tooltipEl.contains(e.target)) { mouse.x = -1000; mouse.y = -1000; }
  }, { passive: true });

  const draw = () => {
    const rect = container.getBoundingClientRect();
    const w = rect.width, h = rect.height;
    if (w === 0 || h === 0) { if (running) rafId = requestAnimationFrame(draw); return; }
    const now = performance.now();
    const t = (now - startTime) / 1000;
    const mx = mouse.x, my = mouse.y;
    ctx.clearRect(0, 0, w, h);

    ctx.strokeStyle = C.gridLine; ctx.lineWidth = 0.5;
    for (let i=1; i<10; i++) { ctx.beginPath(); ctx.moveTo(w/10*i,0); ctx.lineTo(w/10*i,h); ctx.stroke(); }
    for (let i=1; i<6; i++)  { ctx.beginPath(); ctx.moveTo(0,h/6*i); ctx.lineTo(w,h/6*i); ctx.stroke(); }

    const step = ensureMask(w, h);
    const cols = Math.floor(w/step), rows = Math.floor(h/step);
    const dotR = (step-1)/2, hoverR = step*8;
    if (mask) {
      ctx.fillStyle = C.dotBase;
      for (let r=0; r<rows; r++) {
        if (!mask[r]) continue;
        for (let c=0; c<cols; c++) {
          if (!mask[r][c]) continue;
          const dx=(c+0.5)*step, dy=(r+0.5)*step;
          ctx.beginPath(); ctx.arc(dx,dy,dotR,0,Math.PI*2); ctx.fill();
        }
      }
      const mouseOnCanvas = mx > -500;
      if (mouseOnCanvas) {
        const spotR = hoverR;
        const glow = ctx.createRadialGradient(mx,my,0,mx,my,spotR);
        glow.addColorStop(0,  `rgba(${C.accentRgb},0.22)`);
        glow.addColorStop(0.25,`rgba(${C.accentRgb},0.14)`);
        glow.addColorStop(0.5, `rgba(${C.accentRgb},0.07)`);
        glow.addColorStop(0.8, `rgba(${C.accentRgb},0.02)`);
        glow.addColorStop(1,   `rgba(${C.accentRgb},0)`);
        ctx.fillStyle = glow; ctx.fillRect(mx-spotR, my-spotR, spotR*2, spotR*2);
        const innerR = spotR*0.6;
        ctx.fillStyle = C.dotHover;
        const sC=Math.max(0,Math.floor((mx-innerR)/step)), eC=Math.min(cols-1,Math.ceil((mx+innerR)/step));
        const sR=Math.max(0,Math.floor((my-innerR)/step)), eR=Math.min(rows-1,Math.ceil((my+innerR)/step));
        for (let r=sR; r<=eR; r++) {
          if (!mask[r]) continue;
          for (let c=sC; c<=eC; c++) {
            if (!mask[r][c]) continue;
            const dx=(c+0.5)*step, dy=(r+0.5)*step;
            const d=Math.hypot(mx-dx,my-dy);
            if (d < innerR) {
              const t2=1-d/innerR, ease=t2*t2*(3-2*t2);
              ctx.globalAlpha=ease*0.7;
              ctx.beginPath(); ctx.arc(dx,dy,dotR*(1+ease*0.3),0,Math.PI*2); ctx.fill();
            }
          }
        }
        ctx.globalAlpha = 1;
      }
    }

    const hubX=lonToX(HUB.lon,w), hubY=latToY(HUB.lat,h);
    routes.forEach(route => {
      const destX=lonToX(route.to.lon,w), destY=latToY(route.to.lat,h);
      const re=Math.max(0,t-route.delay), dp=Math.min(1,re/2.0);
      if (dp<=0) return;
      const midX=(hubX+destX)/2, midY=(hubY+destY)/2;
      const ddx=destX-hubX, ddy=destY-hubY, dist=Math.hypot(ddx,ddy);
      const nx=-ddy/dist, ny=ddx/dist;
      const cx=midX+nx*dist*0.22, cy=midY+ny*dist*0.22-dist*0.06;
      const S=80, pts=[];
      for (let i=0;i<=S;i++) pts.push(quadBez(i/S,hubX,hubY,cx,cy,destX,destY));
      let totalLen=0;
      for (let i=1;i<=S;i++) totalLen+=Math.hypot(pts[i].x-pts[i-1].x,pts[i].y-pts[i-1].y);
      const visLen=totalLen*easeOutCubic(dp);
      const drawP=(lw,col)=>{
        ctx.beginPath(); ctx.moveTo(pts[0].x,pts[0].y);
        let acc=0;
        for (let i=1;i<=S;i++) {
          acc+=Math.hypot(pts[i].x-pts[i-1].x,pts[i].y-pts[i-1].y);
          if (acc>visLen) {
            const over=acc-visLen, seg=Math.hypot(pts[i].x-pts[i-1].x,pts[i].y-pts[i-1].y);
            const f=1-over/seg;
            ctx.lineTo(pts[i-1].x+(pts[i].x-pts[i-1].x)*f,pts[i-1].y+(pts[i].y-pts[i-1].y)*f);
            break;
          }
          ctx.lineTo(pts[i].x,pts[i].y);
        }
        ctx.strokeStyle=col; ctx.lineWidth=lw; ctx.lineCap='round'; ctx.lineJoin='round'; ctx.stroke();
      };
      drawP(5,C.routeGlow);
      drawP(1.6, route.to.international ? C.intlColor : C.localColor);
      if (dp>=1) {
        const tt=((t-route.delay-2.0)%3.5)/3.5;
        const tp=quadBez(tt,hubX,hubY,cx,cy,destX,destY);
        const g=ctx.createRadialGradient(tp.x,tp.y,0,tp.x,tp.y,14);
        g.addColorStop(0,`rgba(${C.accentRgb},0.25)`); g.addColorStop(1,`rgba(${C.accentRgb},0)`);
        ctx.beginPath(); ctx.arc(tp.x,tp.y,14,0,Math.PI*2); ctx.fillStyle=g; ctx.fill();
        ctx.beginPath(); ctx.arc(tp.x,tp.y,2.5,0,Math.PI*2);
        ctx.fillStyle=route.to.international ? C.intlColor : C.localColor; ctx.fill();
      }
    });

    // Two points can sit only ~20px apart on screen (e.g. Kursk/Cherepetskaya
    // GRES, 0.4° of longitude apart) — pick whichever is genuinely nearest
    // the pointer instead of "last one in the array within 24px", which
    // silently made the earlier point unreachable whenever both qualified.
    hoveredId = null;
    { let nearestDist = 24;
      destinations.forEach(dest => {
        const dx=lonToX(dest.lon,w), dy=latToY(dest.lat,h);
        const dist=Math.hypot(mx-dx,my-dy);
        if (dist < nearestDist) { nearestDist = dist; hoveredId = dest.id; }
      });
    }
    destinations.forEach((dest, idx) => {
      const dx=lonToX(dest.lon,w), dy=latToY(dest.lat,h);
      const re=Math.max(0,t-routes[idx].delay);
      const fadeIn=Math.min(1,Math.max(0,(re-1.2)/0.6));
      if (fadeIn<=0) return;
      const hovered = dest.id === hoveredId;
      const pp=((t*0.8+idx*0.5)%2)/2;
      ctx.beginPath(); ctx.arc(dx,dy,4+pp*14,0,Math.PI*2);
      ctx.strokeStyle=`rgba(${C.accentRgb},${(1-pp)*0.35*fadeIn})`;
      ctx.lineWidth=1; ctx.stroke();
      const pulse=1+Math.sin(t*2+idx)*0.12;
      const col=dest.international ? C.intlColor : C.localColor;
      ctx.beginPath(); ctx.arc(dx,dy,(hovered?5:3.5)*pulse,0,Math.PI*2);
      ctx.fillStyle=col; ctx.globalAlpha=fadeIn; ctx.fill(); ctx.globalAlpha=1;
      drawLabel(ctx,dx,dy,dest,C,fadeIn*(hovered?1:0.85),false,hovered,w,h,blurBuf,dpr);
    });

    {
      const ha=Math.min(1,t/0.5);
      for (let ri=0;ri<3;ri++) {
        const ph=(((t*0.5)+ri*1.0)%3.0)/3.0;
        ctx.beginPath(); ctx.arc(hubX,hubY,6+ph*28,0,Math.PI*2);
        ctx.strokeStyle=`rgba(${C.accentRgb},${(1-ph)*(ri===0?0.30:ri===1?0.15:0.08)*ha})`;
        ctx.lineWidth=ri===0?1.5:1; ctx.stroke();
      }
      const g1=ctx.createRadialGradient(hubX,hubY,0,hubX,hubY,22);
      g1.addColorStop(0,`rgba(${C.accentRgb},${0.30*ha})`); g1.addColorStop(1,`rgba(${C.accentRgb},0)`);
      ctx.beginPath(); ctx.arc(hubX,hubY,22,0,Math.PI*2); ctx.fillStyle=g1; ctx.fill();
      ctx.beginPath(); ctx.arc(hubX,hubY,5.5,0,Math.PI*2);
      ctx.fillStyle=C.hubColor; ctx.globalAlpha=ha; ctx.fill(); ctx.globalAlpha=1;
      drawLabel(ctx,hubX,hubY,HUB,C,ha,true,false,w,h,blurBuf,dpr);
    }

    const wantId = hoveredId || (cardHovered ? shownId : null);
    if (wantId !== shownId) {
      shownId = wantId;
      if (shownId) {
        const dest = destinations.find(d => d.id === shownId);
        const info = projectInfo[shownId];
        if (dest && info) { renderTooltip(dest, info); tooltipEl.classList.add('visible'); }
      } else {
        tooltipEl.classList.remove('visible');
      }
    }
    if (shownId && !cardHovered) {
      const dest = destinations.find(d => d.id === shownId);
      if (dest) positionTooltip(lonToX(dest.lon, w), latToY(dest.lat, h), w, h);
    }

    const sp=((t*0.025)%1.0)*h, sh=50;
    const sg=ctx.createLinearGradient(0,sp-sh/2,0,sp+sh/2);
    sg.addColorStop(0,`rgba(${C.accentRgb},0)`); sg.addColorStop(0.5,`rgba(${C.accentRgb},0.025)`); sg.addColorStop(1,`rgba(${C.accentRgb},0)`);
    ctx.fillStyle=sg; ctx.fillRect(0,sp-sh/2,w,sh);

    const vr=Math.max(w,h)*0.72;
    const vg=ctx.createRadialGradient(w/2,h/2,vr*0.5,w/2,h/2,vr);
    vg.addColorStop(0,'rgba(0,0,0,0)'); vg.addColorStop(0.6,'rgba(0,0,0,0)'); vg.addColorStop(1,'rgba(0,0,0,0.06)');
    ctx.fillStyle=vg; ctx.fillRect(0,0,w,h);

    if (running) rafId = requestAnimationFrame(draw);
  };

  function startLoop() {
    if (running || reduceMq.matches) return;
    running = true;
    rafId = requestAnimationFrame(draw);
  }
  function stopLoop() {
    running = false;
    cancelAnimationFrame(rafId);
  }
  /* Разовый кадр: карта — контент (точки, маршруты, подписи), нарисована
     она должна быть и при reduce; двигаться сама — нет. */
  function renderOnce() { if (!running) draw(); }

  const vis = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) { stopLoop(); return; }
      if (reduceMq.matches) renderOnce(); else startLoop();
    });
  }, { rootMargin: '120px' });
  vis.observe(container);

  if (reduceMq.addEventListener) reduceMq.addEventListener('change', function () {
    stopLoop();
    var r = container.getBoundingClientRect();
    if (r.bottom > -120 && r.top < window.innerHeight + 120) {
      if (reduceMq.matches) renderOnce(); else startLoop();
    }
  });

  /* При reduce ховер/тап всё же обновляют кадр — интерактив остаётся,
     фонового движения нет. Слушатели ниже штатных (строки 530+), поэтому
     координаты мыши к моменту перерисовки уже выставлены. */
  canvas.addEventListener('mousemove', function () { if (reduceMq.matches) renderOnce(); });
  canvas.addEventListener('mouseleave', function () { if (reduceMq.matches) renderOnce(); });
  canvas.addEventListener('touchstart', function () { if (reduceMq.matches) renderOnce(); }, { passive: true });
})();

(function() {
  var s5        = document.querySelector('.s5');
  if (!s5) return;

  var sticky    = s5.querySelector('.s5-sticky');
  var hTrack    = document.getElementById('s5HTrack');
  var tlTrack   = document.getElementById('s5TlTrack');
  var tlViewport = document.getElementById('s5TlViewport');
  var panels    = s5.querySelectorAll('.s5-hpanel');
  var tlItems   = s5.querySelectorAll('.s5-tl-item');
  var slides    = s5.querySelectorAll('.s5-slide');
  var progress  = document.getElementById('s5Progress');
  var btnPrev   = document.getElementById('s5Prev');
  var btnNext   = document.getElementById('s5Next');
  var total     = panels.length;
  var current   = 0;
  var NAV_H     = 64;
  var mqDesktop = window.matchMedia('(min-width: 1025px)');   /* hmode только на десктопе; ≤1024 — tap-слайдер (телефон+планшет) */
  /* reduce: скролл-джек с горизонтальным треком — самый вестибулярно-рисковый
     паттерн на сайте; вместо него готовый тап-режим (isHMode → false). */
  var reduceMq = window.matchMedia('(prefers-reduced-motion: reduce)');
  var scrollRoot = null;
  var s5Timeline = null;
  var s5ScrollTrigger = null;
  /* Дискретное листание колесом внутри пина (см. wheel-обработчик ниже) */
  var stepTween = null;
  var wheelAnimating = false;
  var wheelAcc = 0;
  var wheelLast = 0;
  var pendingStep = 0;
  var metrics   = {
    scrollDistance: 0,
    hScrollDistance: 0,
    tlScrollDistance: 0,
    panelWidth: 0,
    stickyHeight: 0
  };
  var LAST = total - 1;

  function clamp(v, min, max) {
    return Math.min(max, Math.max(min, v));
  }

  function isHMode() {
    return mqDesktop.matches && !reduceMq.matches;
  }

  function viewportH() {
    return Math.max(320, window.innerHeight - NAV_H);
  }

  function findScrollRoot() {
    var node = s5.parentElement;
    while (node && node !== document.documentElement) {
      var oy = getComputedStyle(node).overflowY;
      if (/(auto|scroll|overlay)/.test(oy) && node.scrollHeight > node.clientHeight + 2) {
        return node;
      }
      node = node.parentElement;
    }
    return null;
  }

  function getScrollY() {
    if (scrollRoot) return scrollRoot.scrollTop;
    /* скроллер может быть body (responsive-rules §7) */
    return document.body.scrollTop || document.documentElement.scrollTop || window.scrollY || 0;
  }

  function scrollToY(y, smooth) {
    var opts = { top: y, behavior: smooth ? 'smooth' : 'auto' };
    if (scrollRoot) scrollRoot.scrollTo(opts);
    else window.scrollTo(opts);
  }

  function setScrollY(y) {
    if (scrollRoot) { scrollRoot.scrollTop = y; return; }
    document.documentElement.scrollTop = y;
    document.body.scrollTop = y;
  }

  function renderIndex(idx) {
    idx = clamp(idx, 0, LAST);
    current = idx;
    tlItems.forEach(function(it, i) {
      it.classList.toggle('active', i === idx);
    });
    slides.forEach(function(sl, i) {
      sl.classList.toggle('active', i === idx);
    });
    updateProgress(idx);
    updateNavButtons();
    syncContentHeight(idx);
  }

  // Mobile/tablet slide mode: .s5-content had a fixed min-height:520px, but
  // slides vary a lot (facts + title + a one- vs two-sentence description).
  // Slide 1's full paragraph needed more room than that and was getting cut
  // off mid-sentence by .s5-content's overflow:hidden. Measure what the
  // active slide actually needs and size the container to match instead of
  // guessing one fixed number for every slide.
  function syncContentHeight(idx) {
    var contentEl = s5.querySelector('.s5-content');
    if (!contentEl) return;
    if (isHMode()) { contentEl.style.height = ''; return; }
    var slide = slides[idx];
    if (!slide) return;
    // The slide is position:absolute;inset:0, which forces its rendered
    // height to match the container rather than its content — briefly take
    // it out of that constraint to read its true natural height.
    var prevPos = slide.style.position, prevInset = slide.style.inset, prevVis = slide.style.visibility;
    slide.style.position = 'relative';
    slide.style.inset = 'auto';
    slide.style.visibility = 'hidden';
    var naturalHeight = slide.scrollHeight;
    slide.style.position = prevPos;
    slide.style.inset = prevInset;
    slide.style.visibility = prevVis;
    contentEl.style.height = Math.max(naturalHeight, 520) + 'px';
  }

  function updateProgress(idx) {
    if (!progress) return;
    /* scaleX вместо width — см. .s5-tl-progress в front.css */
    if (total <= 1) {
      progress.style.transform = 'scaleX(1)';
      return;
    }
    progress.style.transform = 'scaleX(' + (idx / (total - 1)) + ')';
  }

  function updateNavButtons() {
    if (btnPrev) btnPrev.disabled = current <= 0;
    if (btnNext) btnNext.disabled = current >= total - 1;
  }

  function stepPx() {
    return Math.max(420, Math.round(viewportH() * 0.52));
  }

  function indexFromProgress(p) {
    if (total <= 1) return 0;
    return clamp(Math.round(p * (total - 1)), 0, LAST);
  }

  function syncVisualFromProgress(p) {
    renderIndex(indexFromProgress(p));
  }

  function measure() {
    s5.classList.toggle('s5-hmode', isHMode());

    if (!isHMode()) {
      s5.style.height = '';
      if (hTrack) hTrack.style.transform = '';
      if (tlTrack) tlTrack.style.transform = '';
      panels.forEach(function(p) { p.style.width = ''; });
      metrics.scrollDistance = 0;
      return;
    }

    var stickyHeight = sticky ? sticky.offsetHeight : viewportH();
    var contentViewport = s5.querySelector('.s5-content');
    var panelWidth = contentViewport ? contentViewport.clientWidth : sticky.clientWidth;

    panels.forEach(function(p) {
      p.style.width = panelWidth + 'px';
    });

    var trackWidth = hTrack ? hTrack.scrollWidth : panelWidth * total;
    var viewportWidth = contentViewport ? contentViewport.clientWidth : panelWidth;
    var hScrollDistance = Math.max(0, trackWidth - viewportWidth);

    var tlWidth = tlTrack ? tlTrack.scrollWidth : 0;
    var tlViewW = tlViewport ? tlViewport.clientWidth : viewportWidth;
    var tlScrollDistance = Math.max(0, tlWidth - tlViewW);

    var scrollDistance = stepPx() * Math.max(1, total - 1);

    metrics.hScrollDistance = hScrollDistance;
    metrics.scrollDistance = scrollDistance;
    metrics.tlScrollDistance = tlScrollDistance;
    metrics.panelWidth = panelWidth;
    metrics.stickyHeight = stickyHeight;

    s5.style.height = (stickyHeight + scrollDistance) + 'px';
  }

  function destroyScrollTrigger() {
    if (stepTween) {
      stepTween.kill();
      stepTween = null;
    }
    wheelAnimating = false;
    pendingStep = 0;
    if (s5Timeline) {
      s5Timeline.kill();
      s5Timeline = null;
    }
    s5ScrollTrigger = null;
    if (typeof ScrollTrigger !== 'undefined') {
      ScrollTrigger.getAll().forEach(function(st) {
        if (st.vars && st.vars.id === 's5-timeline') st.kill();
      });
    }
    if (typeof gsap !== 'undefined') {
      gsap.set([hTrack, tlTrack].filter(Boolean), { clearProps: 'transform' });
    }
  }

  function setupScrollerProxy() {
    if (!scrollRoot || typeof ScrollTrigger === 'undefined') return;
    ScrollTrigger.scrollerProxy(scrollRoot, {
      scrollTop: function(value) {
        if (arguments.length) scrollRoot.scrollTop = value;
        return scrollRoot.scrollTop;
      },
      getBoundingClientRect: function() {
        return scrollRoot.getBoundingClientRect();
      }
    });
  }

  function buildScrollTrigger() {
    destroyScrollTrigger();

    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);
    setupScrollerProxy();
    if (!isHMode() || metrics.scrollDistance <= 0) return;

    var hDist = metrics.hScrollDistance;
    var tlDist = metrics.tlScrollDistance;
    var scrollDistance = metrics.scrollDistance;

    s5Timeline = gsap.timeline({
      scrollTrigger: {
        id: 's5-timeline',
        trigger: s5,
        scroller: scrollRoot || undefined,
        start: 'top top+=' + NAV_H,
        end: '+=' + scrollDistance,
        /* 0.15, не true: жёсткий скраб телепортировал трек за каждым тиком
           колеса — ступеньки при честных 60fps. Колесо в пине теперь идёт
           через goToStep-твин (см. wheel ниже), скраб сглаживает остальное:
           скроллбар, клавиатуру, въезд. Больше 0.3 не ставить: трек
           «доплывает» после твина/снапа отдельным вторым движением. */
        scrub: 0.15,
        /* Снап к ближайшему — страховка для не-колёсного скролла.
           Направленным ему быть нельзя: на въезде в секцию с ходу
           остаток инерции (2–3% прогресса) уводил бы с 2017 сразу на
           2019, у нижнего края симметрично. */
        snap: total > 1 ? {
          snapTo: function(value) {
            var step = 1 / (total - 1);
            return Math.round(value / step) * step;
          },
          duration: { min: 0.25, max: 0.5 },
          delay: 0.08,
          ease: 'power1.inOut'
        } : false,
        invalidateOnRefresh: true,
        onUpdate: function(self) {
          syncVisualFromProgress(self.progress);
          s5.classList.toggle('s5-pinned', self.progress > 0.01 && self.progress < 0.99);
        },
        onEnter: function() { s5.classList.add('s5-pinned'); },
        onEnterBack: function() { s5.classList.add('s5-pinned'); },
        onLeave: function() { s5.classList.remove('s5-pinned'); },
        onLeaveBack: function() { s5.classList.remove('s5-pinned'); }
      }
    });

    s5ScrollTrigger = s5Timeline.scrollTrigger;

    s5Timeline.to(hTrack, { x: -hDist, ease: 'none' }, 0);
    if (tlTrack && tlDist > 0) {
      s5Timeline.to(tlTrack, { x: -tlDist, ease: 'none' }, 0);
    }

    if (s5ScrollTrigger) {
      syncVisualFromProgress(s5ScrollTrigger.progress);
    }
  }

  function goToStep(idx) {
    idx = clamp(idx, 0, LAST);
    if (!isHMode()) {
      /* Направление листания — в dir-класс: CSS доводит входящий слайд
         14px со стороны жеста (см. s5SlideNext/Prev в front.css). */
      s5.classList.toggle('s5-dir-prev', idx < current);
      s5.classList.toggle('s5-dir-next', idx >= current);
      renderIndex(idx);
      return;
    }
    if (!s5ScrollTrigger) {
      renderIndex(idx);
      return;
    }
    var p = total > 1 ? idx / (total - 1) : 0;
    var y = s5ScrollTrigger.start + p * (s5ScrollTrigger.end - s5ScrollTrigger.start);
    if (typeof gsap === 'undefined') { scrollToY(y, true); return; }
    /* Листание — ОДИН твин самого таймлайна, ScrollTrigger на это время
       выключен. Если тянуть твином скролл, скраб-твин ST ретаргетится
       вдогонку порциями, и трек едет серией затухающих всплесков
       (покадрово: 881→…→5, 734→…, 583→… — те самые «3 скачка»).
       Пин секции — CSS sticky, ST его не держит, disable безопасен.
       Скролл подводим синхронно, чтобы после enable ST оказался ровно
       в той же точке и пересинхронизация была бесшовной. */
    if (stepTween) stepTween.kill();
    var trig = s5ScrollTrigger;
    var startY = getScrollY();
    var p0 = s5Timeline ? s5Timeline.progress() : 0;
    var pos = { k: 0 };
    wheelAnimating = true;
    trig.disable(false, false);
    stepTween = gsap.to(pos, {
      k: 1,
      /* 0.45, не 0.9: шаг вешается на ArrowLeft/Right и клики — 900мс были
         3× бюджета UI, серия нажатий вставала в очередь заметно надолго. */
      duration: 0.45,
      ease: 'power1.inOut',
      onUpdate: function() {
        var prog = p0 + (p - p0) * pos.k;
        if (s5Timeline) s5Timeline.progress(prog);
        setScrollY(startY + (y - startY) * pos.k);
        syncVisualFromProgress(prog);
        s5.classList.toggle('s5-pinned', prog > 0.01 && prog < 0.99);
      },
      onComplete: finishStep,
      onInterrupt: finishStep
    });
    function finishStep() {
      wheelAnimating = false;
      stepTween = null;
      trig.enable(false, false);
      /* Тик, пришедший во время глайда, не потерян — цепочкой уводим
         дальше: серия тиков листает годы подряд без «едет-стоп-едет». */
      if (pendingStep) {
        var d = pendingStep;
        pendingStep = 0;
        if (!((current === 0 && d < 0) || (current === LAST && d > 0))) {
          goToStep(current + d);
        }
      }
    }
  }

  function onResize() {
    scrollRoot = findScrollRoot();
    var prevProgress = s5ScrollTrigger ? s5ScrollTrigger.progress : 0;
    measure();
    buildScrollTrigger();
    if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
    if (s5ScrollTrigger) {
      syncVisualFromProgress(s5ScrollTrigger.progress);
    } else if (isHMode() && prevProgress > 0) {
      syncVisualFromProgress(prevProgress);
    } else if (!isHMode()) {
      // Rotating/resizing within mobile/tablet mode reflows the slide's
      // text (different wrap points), so its needed height can change too.
      syncContentHeight(current);
    }
  }

  /* ── Event listeners ────────────────────────────────────────── */
  tlItems.forEach(function(item) {
    item.addEventListener('click', function() {
      goToStep(parseInt(item.dataset.idx, 10));
    });
    item.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        goToStep(parseInt(item.dataset.idx, 10));
      }
      if (e.key === 'ArrowRight') { e.preventDefault(); goToStep(current + 1); }
      if (e.key === 'ArrowLeft')  { e.preventDefault(); goToStep(current - 1); }
    });
  });

  if (btnPrev) btnPrev.addEventListener('click', function() { goToStep(current - 1); });
  if (btnNext) btnNext.addEventListener('click', function() { goToStep(current + 1); });

  /* Колесо внутри пина листает этапы дискретно: тик — ровно один год одним
     твином, страница под колесом не скроллится (это убирает двухфазность
     «свой глайд тика → пауза → глайд снапа»). Тики во время твина глотаются.
     На краях (2017 вверх / 2025 вниз) событие не перехватывается — страница
     скроллится дальше. Порог 24px копит трекпадные микродельты; пауза 250мс
     сбрасывает жест. Снап остаётся страховкой для скроллбара и клавиатуры. */
  s5.addEventListener('wheel', function(e) {
    if (!isHMode() || !s5ScrollTrigger || typeof gsap === 'undefined') return;
    /* Не st.isActive: в текущей сборке ScrollTrigger его нет (undefined).
       Считаем «внутри пина» по границам сами. */
    var sy = getScrollY();
    if (sy < s5ScrollTrigger.start - 1 || sy > s5ScrollTrigger.end + 1) return;
    var dir = e.deltaY > 0 ? 1 : -1;
    if (wheelAnimating) { e.preventDefault(); pendingStep = dir; return; }
    if ((current === 0 && dir < 0) || (current === LAST && dir > 0)) return;
    e.preventDefault();
    var now = performance.now();
    if (now - wheelLast > 250) wheelAcc = 0;
    wheelLast = now;
    wheelAcc += (e.deltaMode === 1 ? e.deltaY * 40 : e.deltaY);
    if (Math.abs(wheelAcc) < 24) return;
    goToStep(current + (wheelAcc > 0 ? 1 : -1));
    wheelAcc = 0;
  }, { passive: false });

  /* Swipe support for the mobile/tablet slide-switch mode — a "slide" reads
     as swipeable on touch even with prev/next buttons present; hmode is left
     alone since vertical scroll already drives it there. */
  var s5ContentEl = s5.querySelector('.s5-content');
  if (s5ContentEl) {
    var touchStartX = 0, touchStartY = 0, touchStartT = 0, touching = false;
    s5ContentEl.addEventListener('touchstart', function(e) {
      if (isHMode() || !e.touches[0]) return;
      touching = true;
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
      touchStartT = Date.now();
    }, { passive: true });
    s5ContentEl.addEventListener('touchend', function(e) {
      if (!touching) return;
      touching = false;
      var t = e.changedTouches[0];
      if (!t) return;
      var dx = t.clientX - touchStartX;
      var dy = t.clientY - touchStartY;
      /* Порог по дистанции ИЛИ по скорости: быстрый короткий флик (24-48px,
         >0.3 px/мс) — однозначное намерение, раньше он молча игнорировался. */
      var v = Math.abs(dx) / Math.max(1, Date.now() - touchStartT);
      var horiz = Math.abs(dx) > Math.abs(dy) * 1.5;
      if (horiz && (Math.abs(dx) > 48 || (Math.abs(dx) > 24 && v > 0.3))) {
        goToStep(current + (dx < 0 ? 1 : -1));
      }
    }, { passive: true });
  }

  window.addEventListener('resize', onResize);
  mqDesktop.addEventListener('change', onResize);
  reduceMq.addEventListener('change', onResize);

  if ('ResizeObserver' in window) {
    var ro = new ResizeObserver(function() { onResize(); });
    if (sticky) ro.observe(sticky);
    if (hTrack) ro.observe(hTrack);
    if (tlTrack) ro.observe(tlTrack);
  }

  if ('IntersectionObserver' in window) {
    var revealIO = new IntersectionObserver(function(entries) {
      if (entries[0].isIntersecting) { s5.classList.add('s5-visible'); revealIO.disconnect(); }
    }, { rootMargin: '-64px 0px 0px 0px', threshold: 0.005 });
    revealIO.observe(s5);
  }

  window.addEventListener('load', onResize);

  /* ── Init ───────────────────────────────────────────────────── */
  scrollRoot = findScrollRoot();
  measure();
  renderIndex(0);
  buildScrollTrigger();
  if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
  (function() {
    var rect = s5.getBoundingClientRect();
    if (rect.bottom > NAV_H && rect.top < window.innerHeight) s5.classList.add('s5-visible');
  })();
})();

(function s6() {
  // Accordion (JS-measured height so stacked mobile content never clips)
  const rows = document.querySelectorAll('.s6-row');
  function setOpen(row) {
    const panel = row.querySelector('.sr-panel');
    if (panel) panel.style.maxHeight = panel.scrollHeight + 'px';
  }
  function setClosed(row) {
    const panel = row.querySelector('.sr-panel');
    if (panel) panel.style.maxHeight = '';
  }
  rows.forEach(row => {
    row.addEventListener('click', () => {
      if (row.classList.contains('active')) return;
      rows.forEach(r => { r.classList.remove('active'); setClosed(r); });
      row.classList.add('active');
      setOpen(row);
    });
  });
  let s6ResizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(s6ResizeTimer);
    s6ResizeTimer = setTimeout(() => {
      const active = document.querySelector('.s6-row.active');
      if (active) setOpen(active);
    }, 120);
  });
  document.querySelectorAll('.s6-row.active').forEach(setOpen);

  // SVG metallic activation on scroll-into-view
  const panel = document.querySelector('.s6-drawing-panel');
  const drawing = document.querySelector('.s6-drawing');
  if (!panel || !drawing) return;
  const obs = new IntersectionObserver((entries) => {
    if (!entries[0].isIntersecting) return;
    setTimeout(() => {
      drawing.classList.add('metal');
      panel.classList.add('drawing-active');
    }, 550);
    obs.unobserve(panel);
  }, { threshold: 0.35 });
  obs.observe(panel);
})();

(function(){
  var params = document.querySelectorAll('.s7-param');
  params.forEach(function(p){
    p.addEventListener('click', function(){
      var wasActive = p.classList.contains('highlighted');
      params.forEach(function(x){ x.classList.remove('highlighted'); });
      if(!wasActive) p.classList.add('highlighted');
    });
  });
  if('IntersectionObserver' in window){
    var s7Left = document.querySelector('.s7-left');
    if(s7Left){
      s7Left.style.cssText += 'opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease;';
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
          if(e.isIntersecting){ e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; io.unobserve(e.target); }
        });
      },{threshold:0.2});
      io.observe(s7Left);
    }
    var paramsAnimated = false;
    var ioP = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting && !paramsAnimated){
          paramsAnimated = true;
          params.forEach(function(p,i){
            p.style.opacity='0'; p.style.transform='translateX(12px)';
            p.style.transition='opacity .35s ease '+(i*70)+'ms,transform .35s ease '+(i*70)+'ms';
            setTimeout(function(){ p.style.opacity='1'; p.style.transform='translateX(0)'; }, i*70+30);
          });
          ioP.unobserve(e.target);
        }
      });
    },{threshold:0.15});
    var s7Right = document.querySelector('.s7-right');
    if(s7Right) ioP.observe(s7Right);
  }
})();

(function(){
  var routeItems = document.querySelectorAll('.s8-route-item');
  var passportRows = document.querySelectorAll('.s8-pp-row');

  function highlightStage(item){
    var stage = item.getAttribute('data-stage');
    passportRows.forEach(function(row){
      row.classList.toggle('highlighted', row.getAttribute('data-field') === stage);
    });
    item.classList.add('active');
  }
  function clearStage(){
    passportRows.forEach(function(row){ row.classList.remove('highlighted'); });
    routeItems.forEach(function(r){ r.classList.remove('active'); });
  }
  routeItems.forEach(function(item){
    item.addEventListener('mouseenter', function(){ highlightStage(item); });
    item.addEventListener('mouseleave', clearStage);
    // Route↔passport linking was mouseenter/mouseleave only — invisible on
    // touch, since a route step can never "hover". Tap toggles it instead.
    item.addEventListener('click', function(){
      var wasActive = item.classList.contains('active');
      clearStage();
      if (!wasActive) highlightStage(item);
    });
  });

  if(!('IntersectionObserver' in window)) return;
  var items = document.querySelectorAll('.s8-route-item');
  var animated = false;
  items.forEach(function(el){ el.style.cssText += 'opacity:0;transform:translateX(-8px);transition:opacity .38s ease,transform .38s ease;'; });
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting && !animated){
        animated = true;
        items.forEach(function(el,i){ setTimeout(function(){ el.style.opacity='1'; el.style.transform='translateX(0)'; }, i*75); });
        io.unobserve(e.target);
      }
    });
  },{threshold:0.1});
  var list = document.querySelector('.s8-route-list');
  if(list) io.observe(list);
  var pp = document.querySelector('.s8-passport');
  if(pp){
    pp.style.cssText += 'opacity:0;transform:translateY(14px);transition:opacity .55s ease .15s,transform .55s ease .15s;';
    var io2 = new IntersectionObserver(function(entries){
      entries.forEach(function(e){ if(e.isIntersecting){ e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; io2.unobserve(e.target); } });
    },{threshold:0.15});
    io2.observe(pp);
  }
})();

(function(){
  var btns = document.querySelectorAll('.s9-filter-btn');
  var cards = [].slice.call(document.querySelectorAll('.s9-card'));
  var s9grid = document.querySelector('.s9-grid');
  var reduceMq = window.matchMedia('(prefers-reduced-motion: reduce)');
  var revealTimers = [];

  /* ── FLIP-хореография фильтрации ──
     Один проход, три роли: уходящие гаснут вниз на своём месте (absolute
     на время ухода), выжившие съезжают в новые ячейки (First-Last-Invert-
     Play), новые подлетают снизу лесенкой, полоса категории прочерчивается
     после посадки (CSS: .s9-move/.s9-leave/.s9-enter в front.css).
     У кнопок фильтра ДВА слушателя (фильтр здесь + сброс «Показать все»
     ниже) — их мутации коалесцируются микротаской в один замер, иначе
     хореография гонялась бы дважды за клик. */
  /* Отладка хореографии: ?s9slow замедляет фазы в 6 раз (таймеры + CSS). */
  var S9K = /s9slow/.test(location.search) ? 6 : 1;
  if (S9K > 1) {
    var dbg = document.createElement('style');
    dbg.textContent = '.s9-card.s9-move{transition-duration:' + (0.26*S9K) + 's !important;}'
      + '.s9-card.s9-leave{transition-duration:' + (0.16*S9K) + 's !important;}'
      + '.s9-card.s9-enter{animation-duration:' + (0.28*S9K) + 's !important;}'
      + '.s9-grid.s9-hanim{transition-duration:' + (0.28*S9K) + 's !important;}';
    document.head.appendChild(dbg);
  }
  var gen = 0, pendingFns = [], scheduled = false;
  /* Computed, а не классы: .s9-extra прячет карточку только ≤640px
     (front.css) — на десктопе класс висит, но карточка видима, и проверка
     по классу назначала бы роли неверно (наблюдалось как хаос при фильтре). */
  function visibleNow(c){ return getComputedStyle(c).display !== 'none'; }
  function runFlip(mutate){
    if(!s9grid || reduceMq.matches){ mutate(); return; }
    gen++;
    var myGen = gen;
    revealTimers.forEach(clearTimeout); revealTimers.length = 0;
    /* На время прохода глушим scroll anchoring на документе целиком:
       overflow-anchor:none на сетке не спасает, если браузер выбрал якорь
       ниже неё — страница «доводилась» за анимируемой высотой и прыгала. */
    document.documentElement.style.overflowAnchor = 'none';
    document.body.style.overflowAnchor = 'none';
    /* Срез «до»: чистим хвосты прошлых проходов и скролл-ривила (инлайновые
       transition с задержками заражали бы FLIP), затем замер. Прерывание:
       getBoundingClientRect учитывает transform — карточка в полёте
       ретаргетится из текущей визуальной позиции. */
    cards.forEach(function(c){
      c.classList.remove('s9-move','s9-enter','s9-leave','s9-leave-go');
      c.style.transition='none';
      c.style.transform=''; c.style.opacity='';
      c.style.removeProperty('--s9d');
      /* Прерванный уход (его рестор-таймер заглушён сменой gen) довершаем
         досрочно: вернуть скрытое состояние, иначе карточка-фантом осталась
         бы видимой в потоке и испортила замер «до». */
      if(c._s9restore){
        if(c._s9restore.wasExtra) c.classList.add('s9-extra');
        c.style.display = c._s9restore.wasHidden ? 'none' : '';
        c._s9restore = null;
      }
      if(c._s9abs){ c.style.position=''; c.style.left=''; c.style.top=''; c.style.width=''; c._s9abs=false; }
    });
    var before = [];
    cards.forEach(function(c){ if(visibleNow(c)) before.push({el:c, r:c.getBoundingClientRect()}); });
    var beforeEls = before.map(function(b){ return b.el; });
    var gridH0 = s9grid.offsetHeight;

    mutate();

    var gridRect = s9grid.getBoundingClientRect();
    var gridH1 = s9grid.offsetHeight;
    var moves = [];
    var enterIdx = 0;

    /* Уходящие: вернуть на экран absolute'ом в старой ячейке и погасить. */
    before.forEach(function(b){
      var c = b.el;
      if(visibleNow(c)) return;
      var wasExtra = c.classList.contains('s9-extra');
      var wasHidden = c.style.display === 'none';
      c.classList.remove('s9-extra'); c.style.display='';
      c.classList.add('s9-leave'); c._s9abs = true;
      c._s9restore = { wasExtra: wasExtra, wasHidden: wasHidden };
      c.style.position='absolute';
      c.style.left=(b.r.left-gridRect.left)+'px';
      c.style.top=(b.r.top-gridRect.top)+'px';
      c.style.width=b.r.width+'px';
      setTimeout(function(){
        if(gen !== myGen || !c._s9restore) return;
        c.classList.remove('s9-leave','s9-leave-go');
        c.style.position=''; c.style.left=''; c.style.top=''; c.style.width=''; c._s9abs=false;
        if(c._s9restore.wasExtra) c.classList.add('s9-extra');
        c.style.display = c._s9restore.wasHidden ? 'none' : '';
        c._s9restore = null;
      }, 220*S9K);
    });

    /* Выжившие: инверт без перехода; новые: вход лесенкой (потолок 5). */
    cards.forEach(function(c){
      if(!visibleNow(c)) return;
      var idx = beforeEls.indexOf(c);
      if(idx !== -1){
        var now = c.getBoundingClientRect();
        var dx = before[idx].r.left - now.left;
        var dy = before[idx].r.top - now.top;
        if(dx || dy){
          c.classList.add('s9-move');
          c.style.transform='translate('+dx+'px,'+dy+'px)';
          moves.push(c);
        }
      } else {
        c.style.setProperty('--s9d', (Math.min(enterIdx++, 5) * 40)+'ms');
        c.classList.add('s9-enter');
        setTimeout(function(){
          if(gen !== myGen) return;
          c.classList.remove('s9-enter');
          c.style.removeProperty('--s9d');
        }, 1000*S9K);
      }
    });

    if(gridH0 !== gridH1) s9grid.style.height = gridH0+'px';

    /* Play: инлайновый transition:none снят — классовые переходы едут. */
    requestAnimationFrame(function(){ requestAnimationFrame(function(){
      if(gen !== myGen) return;
      cards.forEach(function(c){ c.style.transition=''; });
      moves.forEach(function(c){ c.style.transform=''; });
      before.forEach(function(b){ if(b.el.classList.contains('s9-leave')) b.el.classList.add('s9-leave-go'); });
      if(gridH0 !== gridH1){
        s9grid.classList.add('s9-hanim');
        s9grid.style.height = gridH1+'px';
      }
    });});
    /* Страховочная уборка — таймером, не в rAF: в скрытой вкладке rAF
       молчит, и без неё остались бы инлайновые transform/height. */
    setTimeout(function(){
      if(gen !== myGen) return;
      moves.forEach(function(c){ c.classList.remove('s9-move'); c.style.transform=''; });
      cards.forEach(function(c){ c.style.transition=''; });
      s9grid.classList.remove('s9-hanim'); s9grid.style.height='';
      document.documentElement.style.overflowAnchor = '';
      document.body.style.overflowAnchor = '';
    }, 400*S9K);
  }
  window._promenS9Mutate = function(fn){
    pendingFns.push(fn);
    if(scheduled) return;
    scheduled = true;
    Promise.resolve().then(function(){
      scheduled = false;
      var fns = pendingFns.slice(); pendingFns.length = 0;
      runFlip(function(){ fns.forEach(function(f){ f(); }); });
    });
  };

  btns.forEach(function(btn){
    btn.addEventListener('click', function(){
      btns.forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var cat = btn.dataset.cat;
      window._promenS9Mutate(function(){
        cards.forEach(function(card){
          card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
        });
      });
    });
  });
  if('IntersectionObserver' in window){
    var animated = false;
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting && !animated){
          animated = true;
          cards.forEach(function(c,i){
            c.style.opacity='0'; c.style.transform='translateY(12px)';
            c.style.transition='opacity .4s ease '+(i*70)+'ms,transform .4s ease '+(i*70)+'ms';
            revealTimers.push(setTimeout(function(){ c.style.opacity='1'; c.style.transform='translateY(0)'; }, i*70+30));
          });
          /* Хвост ривила: инлайновые transition с задержками до ~0.8s
             остаться не должны — их унаследовала бы фильтрация. */
          revealTimers.push(setTimeout(function(){
            cards.forEach(function(c){ c.style.transition=''; c.style.opacity=''; c.style.transform=''; });
          }, cards.length*70+520));
          io.unobserve(e.target);
        }
      });
    },{threshold:0.05});
    if(s9grid) io.observe(s9grid);
  }

  /* Направленная проливка карточек — порт из nb.js (нормативы): белое
     втекает с грани входа курсора и вытекает через грань выхода, подсветка
     читается как проходящая между соседними карточками. Слой — .s9-card::after
     (front.css). Гейт: только мышь — на таче mouseover эмулируется тапом и
     проливка бы залипала. */
  var s9FlowGrid = document.querySelector('.s9-grid');
  if (s9FlowGrid && window.matchMedia('(hover:hover) and (pointer:fine)').matches) {
    var s9FlowEdge = function(e, card){
      var r = card.getBoundingClientRect();
      var w = r.width, h = r.height;
      var x = (e.clientX - r.left - w/2) * (w > h ? h/w : 1);
      var y = (e.clientY - r.top - h/2) * (h > w ? w/h : 1);
      return Math.round((Math.atan2(y,x)*(180/Math.PI)+180)/90+3)%4; /* 0 top,1 right,2 bottom,3 left */
    };
    var S9_FLOW_OFFSET = [{x:'0%',y:'-100%'},{x:'100%',y:'0%'},{x:'0%',y:'100%'},{x:'-100%',y:'0%'}];
    s9FlowGrid.addEventListener('mouseover', function(e){
      var card = e.target.closest('.s9-card');
      if (!card || card.contains(e.relatedTarget)) return;
      /* Повторный вход, пока вытекание не доехало (<500мс): не телепортируем
         слой к грани входа (белая вспышка), а даём транзишену ретаргетнуться
         из текущего положения. */
      if (card._pmFlowT && Date.now() - card._pmFlowT < 500) {
        card.style.setProperty('--flow-x', '0%');
        card.style.setProperty('--flow-y', '0%');
        return;
      }
      var off = S9_FLOW_OFFSET[s9FlowEdge(e, card)];
      /* прыжком ставим слой за грань входа, затем плавно вливаем */
      card.classList.add('flow-instant');
      card.style.setProperty('--flow-x', off.x);
      card.style.setProperty('--flow-y', off.y);
      void card.offsetWidth;
      card.classList.remove('flow-instant');
      card.style.setProperty('--flow-x', '0%');
      card.style.setProperty('--flow-y', '0%');
    });
    s9FlowGrid.addEventListener('mouseout', function(e){
      var card = e.target.closest('.s9-card');
      if (!card || card.contains(e.relatedTarget)) return;
      card._pmFlowT = Date.now();
      var off = S9_FLOW_OFFSET[s9FlowEdge(e, card)];
      card.style.setProperty('--flow-x', off.x);
      card.style.setProperty('--flow-y', off.y);
    });
  }
})();

(function(){
  /* #5 — индикатор прогресса скролла переехал в общий assets/js/chrome.js:
     он нужен всем страницам, а не только главной (2026-07-31). */

  /* #3 — S4 «показать все»: длинный список городов свёрнут до 3 карточек */
  var list = document.getElementById('s4MobileList');
  if(list){
    var cards = list.querySelectorAll('.s4-ml-card');
    if(cards.length > 3 && !list.querySelector('.s4-showall')){
      list.classList.add('collapsed');
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 's4-showall';
      btn.textContent = 'Показать все (' + cards.length + ')';
      btn.addEventListener('click', function(){
        var collapsed = list.classList.toggle('collapsed');
        btn.textContent = collapsed ? ('Показать все (' + cards.length + ')') : 'Свернуть';
      });
      list.appendChild(btn);
    }
  }

  /* #4 — S9 документы: «показать все» (видно 4 карточки, с учётом radio-фильтра) */
  var s9grid = document.querySelector('.s9-grid');
  if(s9grid){
    var s9cards = [].slice.call(s9grid.querySelectorAll('.s9-card'));
    if(s9cards.length){
      var s9expanded = false;
      var s9btn = document.createElement('button');
      s9btn.type = 'button';
      s9btn.className = 's9-showall';
      s9grid.parentNode.insertBefore(s9btn, s9grid.nextSibling);
      var s9apply = function(){
        var visible = s9cards.filter(function(c){ return c.style.display !== 'none'; });
        s9cards.forEach(function(c){ c.classList.remove('s9-extra'); });
        if(!s9expanded){
          visible.forEach(function(c,i){ if(i >= 4) c.classList.add('s9-extra'); });
        }
        if(visible.length > 4){
          s9btn.classList.remove('hide');
          s9btn.classList.toggle('open', s9expanded);
          s9btn.textContent = s9expanded ? 'Свернуть' : ('Показать все (' + visible.length + ')');
        } else {
          s9btn.classList.add('hide');
        }
      };
      /* Через FLIP-коалесцер (см. фильтр выше): клик по чипу дёргает и
         фильтр, и этот сброс — обе мутации играются одним проходом.
         Инициализация — напрямую, без хореографии. */
      var s9run = window._promenS9Mutate || function(f){ f(); };
      s9btn.addEventListener('click', function(){ s9expanded = !s9expanded; s9run(s9apply); });
      [].forEach.call(document.querySelectorAll('.s9-filter-btn'), function(fb){
        fb.addEventListener('click', function(){ s9expanded = false; s9run(s9apply); });
      });
      s9apply();
    }
  }

  /* S9 — маркер статуса «Действует» переносим в правый верхний угол карточки
     (в .s9-card-top напротив бейджа типа). Футер освобождается под скоуп +
     «Скачать», слева текст перестаёт быть поджатым. Применяется на всех ширинах. */
  [].forEach.call(document.querySelectorAll('.s9-card'), function(card){
    var top = card.querySelector('.s9-card-top');
    var status = card.querySelector('.s9-status');
    if(top && status) top.appendChild(status);
  });

  /* #4 — S8 контроль качества: два аккордеона (Маршрут / Паспорт) */
  var s8sec = document.querySelector('.s8');
  if(s8sec){
    var routeBlock  = s8sec.querySelector('.s8-route-block');
    var routeHeader = s8sec.querySelector('.s8-route-header');
    var s8right     = s8sec.querySelector('.s8-right');
    var ppHeader    = s8sec.querySelector('.s8-passport-header');
    if(routeHeader && routeBlock){
      routeHeader.addEventListener('click', function(){ routeBlock.classList.toggle('open'); });
    }
    if(ppHeader && s8right){
      s8right.classList.add('open');   /* паспорт открыт по умолчанию */
      ppHeader.addEventListener('click', function(){ s8right.classList.toggle('open'); });
    }
  }

  /* 2 — SFK АЭС/ТЭС: тап по синей плашке раскрывает карточку.
     Подсказка — реальный span внизу плашки, текст меняется при раскрытии. */
  [].forEach.call(document.querySelectorAll('.sfk-card'), function(card){
    var hd = card.querySelector('.sfk-card-hd');
    if(!hd) return;
    var hint = document.createElement('span');
    hint.className = 'sfk-hint';
    hint.textContent = 'Нажмите, чтобы раскрыть ↓';
    hd.appendChild(hint);
    hd.addEventListener('click', function(){
      var open = card.classList.toggle('open');
      hint.textContent = open ? 'Свернуть ↑' : 'Нажмите, чтобы раскрыть ↓';
    });
  });

  /* 3 — S5: свайп пальцем листает годы (переиспользуем стрелки prev/next) */
  var s5sec = document.querySelector('.s5');
  if(s5sec){
    var tlBtns = s5sec.querySelectorAll('.s5-tl-btn');
    if(tlBtns.length >= 2){
      var prevBtn = tlBtns[0], nextBtn = tlBtns[1];
      var zone = s5sec.querySelector('.s5-inner') || s5sec;
      var sx = null, sy = null, st = 0;
      zone.addEventListener('touchstart', function(e){
        var t = e.changedTouches[0]; sx = t.clientX; sy = t.clientY; st = Date.now();
      }, { passive:true });
      zone.addEventListener('touchend', function(e){
        if(sx === null) return;
        var t = e.changedTouches[0];
        var dx = t.clientX - sx, dy = t.clientY - sy;
        var v = Math.abs(dx) / Math.max(1, Date.now() - st);
        var horiz = Math.abs(dx) > Math.abs(dy) * 1.4;
        /* дистанция ИЛИ скорость — быстрый флик тоже листает */
        if(horiz && (Math.abs(dx) > 45 || (Math.abs(dx) > 24 && v > 0.3))){
          if(dx < 0) nextBtn.click(); else prevBtn.click();
        }
        sx = null; sy = null;
      }, { passive:true });
    }

    /* S5 — сдвигаем ленту так, чтобы активный год стоял у левого края
       (следующий год тем же кеглем выглядывает за правым краем) */
    var tlTrack = s5sec.querySelector('.s5-tl-track');
    if(tlTrack){
      var mqS5 = window.matchMedia('(max-width:1024px)');   /* лента-слайдер на всём tap-диапазоне (телефон+планшет) */
      var positionTicker = function(){
        if(!mqS5.matches){ tlTrack.style.removeProperty('--s5x'); return; }
        var active = tlTrack.querySelector('.s5-tl-item.active');
        if(active) tlTrack.style.setProperty('--s5x', (-active.offsetLeft) + 'px');
      };
      if(window.MutationObserver){
        new MutationObserver(positionTicker).observe(tlTrack, {
          subtree:true, attributes:true, attributeFilter:['class']
        });
      }
      window.addEventListener('resize', positionTicker, { passive:true });
      positionTicker();
    }
  }

  /* Футер · выставляем sticky-top формы S10 так, чтобы её низ (кнопка
     «Отправить») встал у нижнего края экрана — кнопка полностью видна ДО
     того, как футер начнёт наезжать сверху. Форма пиннится top-ом (наезд
     сохраняется), но в точке, где кнопка уже показана. */
  (function(){
    var right = document.querySelector('.footer-zone .s10-right');
    if(!right) return;
    var mqFooter = window.matchMedia('(max-width:640px)');   /* только телефон: там пиннится форма. На планшете пиннится вся .s10 */
    function setStickyTop(){
      if(!mqFooter.matches){ right.style.top = ''; return; }
      var navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h'), 10) || 64;
      var h  = right.offsetHeight;
      var vh = window.innerHeight;
      /* Всегда пиним форму низом к нижнему краю экрана (top = vh − h). Тогда в
         момент, когда форму долистали до конца, её низ уже у нижнего края, а
         футер идёт сразу под ним — наезд начинается СРАЗУ, без пустого прохода.
         (Раньше при коротких формах top:navH оставлял под формой пустое место,
         по которому футер прокручивался «как обычно» до начала наезда.)
         Форма выше экрана → top уходит в минус (верх формы за экраном, но её
         уже пролистали при заполнении). navH-переменная больше не нужна. */
      right.style.top = (vh - h) + 'px';
    }
    setStickyTop();
    window.addEventListener('resize', setStickyTop, { passive:true });
    window.addEventListener('load', setStickyTop);
  })();
})();
