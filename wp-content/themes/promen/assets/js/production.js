/* ════════════════════════════════════════════════════════════
   PROM-EN production.js — «Производство»: reveal-сцены (s2, journal,
   capacity, proof, fleet), галерея drag+inertia, sidenav,
   карта цеха (спарк + видео-попапы). Источник — инлайн-скрипт
   html/production.html (2026-07-22); часы/бургер — chrome.js,
   форма s10 — серверная (footer.php).
   ════════════════════════════════════════════════════════════ */
/* ── Scene 2: Staggered column reveal ── */
(function(){
  const cols = document.querySelectorAll('.s2-col');
  const obs = new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting){
      cols.forEach(function(col, i){
        setTimeout(function(){ col.classList.add('s2-vis'); }, i * 130);
      });
      obs.disconnect();
    }
  },{threshold:0.15});
  obs.observe(document.getElementById('s2'));
})();

/* ── S2 нормирование: колонки ТЭС/АЭС/НГО — аккордеоны на телефоне
   (паттерн SFK главной, front.js). Подсказка — реальный span в шапке;
   CSS показывает её и прячет контент только на ≤640. ── */
(function(){
  [].forEach.call(document.querySelectorAll('.s2-col'), function(col){
    var hd = col.querySelector('.s2-col-hd');
    if(!hd) return;
    var hint = document.createElement('span');
    hint.className = 's2-hint';
    hint.textContent = 'Нажмите, чтобы раскрыть ↓';
    hd.appendChild(hint);
    hd.addEventListener('click', function(){
      var open = col.classList.toggle('open');
      hint.textContent = open ? 'Свернуть ↑' : 'Нажмите, чтобы раскрыть ↓';
    });
  });
})();

/* ── Библиотека материалов: на телефоне видны первые марки, остальные —
   по кнопке «Показать все 8 марок» (паттерн s9-showall главной).
   Кнопка живёт на всех ширинах, CSS показывает её только на ≤640. ── */
(function(){
  var grid = document.querySelector('.grades-grid');
  if(!grid) return;
  var total = grid.querySelectorAll('.ge').length;
  if(!total) return;
  var btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'grades-showall';
  btn.textContent = 'Показать все ' + total + ' марок';
  grid.parentNode.insertBefore(btn, grid.nextSibling);
  btn.addEventListener('click', function(){
    var open = grid.classList.toggle('expanded');
    btn.classList.toggle('open', open);
    btn.textContent = open ? 'Свернуть' : ('Показать все ' + total + ' марок');
  });
})();

/* ── Журнал: reveal checks sequentially, then stamp ── */
(function(){
  var jrnObs=new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting){
      var checks=document.querySelectorAll('.jd-cs');
      checks.forEach(function(el,i){
        setTimeout(function(){el.classList.add('revealed');},400+i*180);
      });
      setTimeout(function(){
        var stamp=document.querySelector('.jd-stamp');
        if(stamp)stamp.classList.add('revealed');
      }, 400 + checks.length * 180 + 400);
      jrnObs.disconnect();
    }
  },{threshold:0.25});
  var jrnEl=document.getElementById('journal');
  if(jrnEl)jrnObs.observe(jrnEl);
})();

/* ── Capacity rows: stagger reveal ── */
(function(){
  var rows=document.querySelectorAll('.cap-row');
  var capObs=new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting){
      rows.forEach(function(r,i){
        setTimeout(function(){r.classList.add('cap-vis');},i*100);
      });
      capObs.disconnect();
    }
  },{threshold:0.12});
  var capEl=document.getElementById('capacity');
  if(capEl)capObs.observe(capEl);
})();

/* ── Proof: scroll-driven clip-path reveal ── */
(function(){
  var section=document.getElementById('proof');
  if(!section)return;
  var clip=document.getElementById('prfClip');
  var intro=document.getElementById('prfIntro');
  var status=document.getElementById('prfStatus');
  var main=document.getElementById('prfMain');
  var params=document.getElementById('prfParams');
  var target=document.getElementById('prfTarget');
  var ann=document.getElementById('prfAnn');

  function clamp(v,lo,hi){return v<lo?lo:v>hi?hi:v;}
  function ease(t){return t<.5?2*t*t:-1+(4-2*t)*t;}
  function lerp(a,b,t){return a+(b-a)*t;}
  function phase(raw,s,e){return ease(clamp((raw-s)/(e-s),0,1));}

  /* Скроллер на этом сайте — body (responsive-rules §7): читаем body И
     documentElement И pageYOffset, слушаем скролл на всех трёх. */
  function scrollY_(){
    return document.body.scrollTop||document.documentElement.scrollTop||window.pageYOffset||0;
  }

  /* Cache absolute doc-coord top — immune to sticky/transform quirks */
  var absTop=0;
  function recache(){
    absTop=section.getBoundingClientRect().top+scrollY_();
  }
  recache();
  window.addEventListener('resize',recache,{passive:true});
  window.addEventListener('load',recache);

  var raf=null;
  function update(){
    var sy=scrollY_();
    var scrolled=Math.max(0,sy-absTop);
    var travel=Math.max(1,section.offsetHeight-window.innerHeight);
    var raw=clamp(scrolled/travel,0,1);

    /* — Phase 0: intro frame fades out 0→0.38 — */
    if(intro)intro.style.opacity=String(1-phase(raw,0,0.38));

    /* — Phase 1: clip iris opens 0→0.52 — */
    /* ≤900: стартовое окно почти во всю ширину (34%/6%), чтобы спеки интро
       не прятались под видео — синхронно с CSS .prf-clip в @media ≤900 */
    var mob=window.matchMedia('(max-width:900px)').matches;
    var clipP=phase(raw,0,0.52);
    var iV=lerp(mob?34:28,0,clipP);
    var iH=lerp(mob?6:20,0,clipP);
    var br=lerp(2,0,clipP);
    if(clip)clip.style.clipPath=
      'inset('+iV.toFixed(2)+'% '+iH.toFixed(2)+'% round '+br.toFixed(1)+'px)';

    /* — Phase 2: status bar fades in 0.38→0.58 — */
    if(status)status.style.opacity=String(phase(raw,0.38,0.58));

    /* — Phase 3: params strip fades in 0.42→0.60 — */
    if(params)params.style.opacity=String(phase(raw,0.42,0.60));

    /* — Phase 4: main copy rises in 0.46→0.74 — */
    var mainP=phase(raw,0.46,0.74);
    if(main){
      main.style.opacity=String(mainP);
      main.style.transform='translateY('+lerp(22,0,mainP).toFixed(1)+'px)';
    }

    /* — Phase 5: measurement target + annotation 0.54→0.78 — */
    if(target)target.style.opacity=String(phase(raw,0.54,0.78));
    if(ann)ann.style.opacity=String(phase(raw,0.58,0.80));

    raf=null;
  }

  function onScroll(){if(!raf)raf=requestAnimationFrame(update);}
  /* Both listeners: window for Chrome/FF, document for Safari overflow-x:hidden edge case */
  window.addEventListener('scroll',onScroll,{passive:true});
  document.addEventListener('scroll',onScroll,{passive:true});
  document.body.addEventListener('scroll',onScroll,{passive:true});
  update();

  /* Running clock — starts at 08:47:00 and counts forward */
  var base=new Date();base.setHours(8,47,0,0);
  var t0=Date.now();
  function pad(n){return n<10?'0'+n:''+n;}
  (function tick(){
    var sec=Math.floor((Date.now()-t0)/1000);
    var d=new Date(base.getTime()+sec*1000);
    var el=document.getElementById('prfClock');
    if(el)el.textContent=pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds());
    setTimeout(tick,1000);
  })();
})();

/* ── Fleet: stagger reveal ── */
(function(){
  var groups=document.querySelectorAll('.fl-group');
  var flObs=new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting){
      groups.forEach(function(g,i){
        setTimeout(function(){g.classList.add('fl-vis');},i*90);
      });
      flObs.disconnect();
    }
  },{threshold:0.1});
  var flEl=document.getElementById('fleet');
  if(flEl)flObs.observe(flEl);
})();

/* ── Fleet: группы оборудования — аккордеоны на телефоне. Шапка группы
   (название + счётчик единиц) служит триггером; стрелку ↓/↑ и скрытие
   списка даёт CSS только на ≤640. ── */
(function(){
  [].forEach.call(document.querySelectorAll('.fl-group'), function(group){
    var hd = group.querySelector('.fl-group-hd');
    if(!hd) return;
    hd.addEventListener('click', function(){ group.classList.toggle('open'); });
  });
})();

/* ── Gallery: drag + inertia ── */
(function(){
  var stage=document.getElementById('galStage');
  var track=document.getElementById('galTrack');
  if(!stage||!track)return;
  var items=Array.from(track.querySelectorAll('.gal-item'));
  var dots=Array.from(document.querySelectorAll('.gal-dot'));
  var x=0,velX=0,isDragging=false,startMouseX=0,startX=0,lastMouseX=0,raf=null,activeIdx=0,targetX=null;
  /* measure actual inter-item stride from DOM to match CSS gap exactly */
  function itemW(){
    if(!items[0])return 400;
    if(items.length>1)return items[1].offsetLeft-items[0].offsetLeft;
    return items[0].offsetWidth+16;
  }
  function maxX(){return -(items.length-1)*itemW();}
  function clamp(v,lo,hi){return v<lo?lo:v>hi?hi:v;}
  function snapTarget(){var iw=itemW();var idx=Math.max(0,Math.min(items.length-1,Math.round(-x/iw)));return -idx*iw;}
  function setActive(idx){
    if(idx===activeIdx)return;
    activeIdx=idx;
    dots.forEach(function(d,i){d.classList.toggle('gal-act',i===idx);});
  }
  /* Телефон: соседний кадр специально выглядывает из-за правого края как
     подсказка «листается» — гашение до 0.42/scale .88 делало его невидимым.
     Кадры равновесные, активный отмечается точками под лентой. */
  var galMob=window.matchMedia('(max-width:640px)');
  function updateScales(){
    /* item.offsetLeft is relative to stage (offsetParent=stage, which has padding).
       cx anchors at the left-edge slot center so item 0 at x=0 gets scale=1.0,
       which visually aligns the active card with the section heading. */
    var padL=parseFloat(getComputedStyle(stage).paddingLeft)||56;
    var iw=itemW();
    var sw=stage.offsetWidth;
    var cx=padL+iw/2;
    var mob=galMob.matches;
    var closestD=Infinity,closestI=0;
    items.forEach(function(item,i){
      var left=item.offsetLeft+x;
      var center=left+item.offsetWidth/2;
      var dist=Math.abs(center-cx);
      if(dist<closestD){closestD=dist;closestI=i;}
      if(mob){item.style.transform='none';item.style.opacity='1';return;}
      var t=Math.max(0,1-dist/(sw*0.55));
      item.style.transform='scale('+(0.88+0.12*t).toFixed(3)+')';
      item.style.opacity=(0.42+0.58*t).toFixed(3);
    });
    setActive(closestI);
  }
  function tick(){
    if(!isDragging){
      if(targetX!==null){
        /* smooth dot-navigation lerp */
        var diff=targetX-x;
        if(Math.abs(diff)<0.5){x=targetX;targetX=null;}
        else x+=diff*0.14;
      } else {
        velX*=0.92;
        if(Math.abs(velX)<0.3){
          velX=0;
          var target=snapTarget();var diff=target-x;
          if(Math.abs(diff)<0.4){x=target;track.style.transform='translateX('+x.toFixed(2)+'px)';updateScales();raf=null;return;}
          x+=diff*0.10;
        } else {x+=velX;x=clamp(x,maxX()-60,60);}
      }
    }
    x=clamp(x,maxX()-40,40);
    track.style.transform='translateX('+x.toFixed(2)+'px)';
    updateScales();
    raf=requestAnimationFrame(tick);
  }
  function startRaf(){if(!raf)raf=requestAnimationFrame(tick);}
  stage.addEventListener('mousedown',function(e){
    targetX=null;
    isDragging=true;startMouseX=e.clientX;startX=x;lastMouseX=e.clientX;velX=0;
    stage.classList.add('is-dragging');document.body.style.userSelect='none';
    e.preventDefault();startRaf();
  });
  window.addEventListener('mousemove',function(e){
    if(!isDragging)return;
    velX=e.clientX-lastMouseX;lastMouseX=e.clientX;
    x=clamp(startX+(e.clientX-startMouseX),maxX()-100,100);
    track.style.transform='translateX('+x.toFixed(2)+'px)';updateScales();
  });
  window.addEventListener('mouseup',function(){
    if(!isDragging)return;
    isDragging=false;stage.classList.remove('is-dragging');document.body.style.userSelect='';startRaf();
  });
  stage.addEventListener('touchstart',function(e){
    targetX=null;
    isDragging=true;startMouseX=e.touches[0].clientX;startX=x;lastMouseX=e.touches[0].clientX;velX=0;startRaf();
  },{passive:true});
  stage.addEventListener('touchmove',function(e){
    if(!isDragging)return;
    var cx=e.touches[0].clientX;
    velX=cx-lastMouseX;lastMouseX=cx;
    x=clamp(startX+(cx-startMouseX),maxX()-60,60);
    track.style.transform='translateX('+x.toFixed(2)+'px)';updateScales();
  },{passive:true});
  stage.addEventListener('touchend',function(){isDragging=false;startRaf();});
  /* wheel: accumulate velocity only — no direct x jump — gives smooth trackpad feel */
  stage.addEventListener('wheel',function(e){
    if(Math.abs(e.deltaY)>Math.abs(e.deltaX))return;
    e.preventDefault();
    targetX=null;
    velX=clamp(velX-e.deltaX*0.28,-22,22);
    startRaf();
  },{passive:false});
  dots.forEach(function(dot,i){
    dot.addEventListener('click',function(){
      velX=0;
      targetX=clamp(-i*itemW(),maxX(),0);
      startRaf();
    });
  });
  /* Тап-стрелки для планшета/телефона (≤1024) — те же снап-точки, что у точек */
  function goStep(d){
    velX=0;
    var idx=Math.max(0,Math.min(items.length-1,activeIdx+d));
    targetX=clamp(-idx*itemW(),maxX(),0);
    startRaf();
  }
  var galPrev=document.getElementById('galPrev');
  var galNext=document.getElementById('galNext');
  if(galPrev)galPrev.addEventListener('click',function(){goStep(-1);});
  if(galNext)galNext.addEventListener('click',function(){goStep(1);});
  /* Ширина кадра зависит от вьюпорта (70vw на телефоне, аспект от высоты
     на десктопе) — при повороте экрана пересобираем позицию активного
     кадра и режим гашения соседей, иначе лента остаётся на старом сдвиге. */
  window.addEventListener('resize',function(){
    velX=0;targetX=null;
    x=clamp(-activeIdx*itemW(),maxX(),0);
    track.style.transform='translateX('+x.toFixed(2)+'px)';
    updateScales();
  },{passive:true});
  requestAnimationFrame(function(){updateScales();});
})();

/* ── Sidenav ── */
const allSections=document.querySelectorAll('#s1,#proof,#s2,#grades,#shopmap,#thermal,#journal,#capacity,#fleet,#gallery,#portal,#order-cta');
const navItems=document.querySelectorAll('.sidenav-item');
let snLabelTimer=null;
let snCurrentActive=null;
function showLabelFor(item){
  if(snLabelTimer){clearTimeout(snLabelTimer);snLabelTimer=null;}
  navItems.forEach(n=>n.classList.remove('sn-show-label'));
  if(!item)return;
  item.classList.add('sn-show-label');
  snLabelTimer=setTimeout(function(){
    item.classList.remove('sn-show-label');
    snLabelTimer=null;
  },1500);
}
const sio=new IntersectionObserver(entries=>{
  entries.forEach(e=>{
    if(e.isIntersecting){
      navItems.forEach(n=>n.classList.remove('sn-active'));
      const a=document.querySelector('.sidenav-item[href="#'+e.target.id+'"]');
      if(a){
        a.classList.add('sn-active');
        if(e.target.id!==snCurrentActive){
          snCurrentActive=e.target.id;
          showLabelFor(a);
        }
      }
    }
  });
},{threshold:0.38});
allSections.forEach(s=>sio.observe(s));

/* ── Карта цеха: спарк вдоль маршрута + видео-попапы на 01/03/05/07 ── */
(function(){
  var plan=document.querySelector('.shm-plan');
  var svg=document.querySelector('.shm-plan-svg');
  var routePath=document.getElementById('shm-route');
  var sparkWrap=document.getElementById('shm-spark-wrap');
  if(!plan||!svg||!routePath||!sparkWrap)return;

  var LOOP_MS=42000;      // полный обход маршрута — в 1.5 раза медленнее прежней версии (28s)
  var HIDE_LEAD_MS=500;   // окно сворачивается за 0.5с до следующей остановки

  var stops=[
    {id:'01',x:87, y:118},
    {id:'03',x:440,y:118},
    {id:'05',x:793,y:118},
    {id:'07',x:440,y:348}
  ];

  var total=routePath.getTotalLength();

  function fractionAt(x,y){
    var samples=4000,best=0,bestD=Infinity;
    for(var i=0;i<=samples;i++){
      var len=total*i/samples;
      var p=routePath.getPointAtLength(len);
      var dx=p.x-x,dy=p.y-y;
      var d=dx*dx+dy*dy;
      if(d<bestD){bestD=d;best=i/samples;}
    }
    return best;
  }
  stops.forEach(function(s){s.frac=fractionAt(s.x,s.y);});

  var popups={};
  stops.forEach(function(s){
    popups[s.id]=plan.querySelector('.shm-popup[data-video="'+s.id+'"]');
  });

  var activeId=null;

  function positionPopup(el,x,y){
    var pt=svg.createSVGPoint();
    pt.x=x;pt.y=y;
    var screenPt=pt.matrixTransform(svg.getScreenCTM());
    var hostRect=plan.getBoundingClientRect();
    var w=el.offsetWidth||420;
    var pad=8;
    var left=screenPt.x-hostRect.left;
    var minL=w/2+pad,maxL=hostRect.width-w/2-pad;
    if(left<minL)left=minL;
    if(left>maxL)left=maxL;
    el.style.left=left+'px';
    el.style.top=(screenPt.y-hostRect.top)+'px';
    el.classList.toggle('shm-popup--below',y<230);
  }

  function openPopup(s){
    var el=popups[s.id];
    if(!el)return;
    positionPopup(el,s.x,s.y);
    el.classList.add('shm-pop-visible');
    var vid=el.querySelector('video');
    if(vid){try{vid.currentTime=0;vid.play();}catch(e){}}
    activeId=s.id;
  }

  function closePopup(id){
    var el=popups[id];
    if(!el)return;
    el.classList.remove('shm-pop-visible');
    var vid=el.querySelector('video');
    if(vid)vid.pause();
    if(activeId===id)activeId=null;
  }

  var startTs=null,lastFrac=0;

  function frame(ts){
    if(startTs===null)startTs=ts;
    var elapsed=(ts-startTs)%LOOP_MS;
    var frac=elapsed/LOOP_MS;

    var p=routePath.getPointAtLength(frac*total);
    sparkWrap.setAttribute('transform','translate('+p.x+','+p.y+')');

    var idx=-1;
    for(var i=0;i<stops.length;i++){
      if(frac>=stops[i].frac)idx=i; else break;
    }
    var current=idx>=0?stops[idx]:null;
    var nextFrac=(idx+1<stops.length)?stops[idx+1].frac:1;

    if(current&&activeId!==current.id){
      if(activeId)closePopup(activeId);
      openPopup(current);
    }
    if(activeId&&(nextFrac-frac)<=(HIDE_LEAD_MS/LOOP_MS)){
      closePopup(activeId);
    }
    if(frac<lastFrac&&activeId){
      closePopup(activeId);
    }
    if(activeId&&idx>=0)positionPopup(popups[activeId],stops[idx].x,stops[idx].y);

    lastFrac=frac;
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
})();
