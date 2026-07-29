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

/* ── Карта цеха: автотур по участкам; план и лента — два вида одного
      состояния, видео идёт непрерывно и не перезапускается при смене зоны ── */
(function(){
  var section=document.getElementById('shopmap');
  var split=document.querySelector('.shm-split');
  var reel=document.querySelector('.shm-reel');
  if(!section||!split||!reel)return;

  var rows=Array.prototype.slice.call(reel.querySelectorAll('.shm-row'));
  var zones=Array.prototype.slice.call(split.querySelectorAll('.shm-zone'));
  var nodes=Array.prototype.slice.call(split.querySelectorAll('.shm-node'));
  var route=document.getElementById('shm-route');
  var spark=document.getElementById('shm-spark-wrap');
  var vids=Array.prototype.slice.call(reel.querySelectorAll('video'));
  if(!rows.length||!route||!spark)return;

  var DWELL=4200;   // стоянка на участке — столько показывается его кадр
  var TRAVEL=800;   // перегон между участками
  var SEG=DWELL+TRAVEL;
  var LOOP=SEG*rows.length+TRAVEL;   // +выезд к отгрузке в конце круга
  var reduce=window.matchMedia('(prefers-reduced-motion: reduce)');

  /* Остановки в координатах viewBox плана (860×460), по одной на участок.
     05 стоит на середине вертикального прогона — термоучасток занимает
     всю высоту корпуса, и метка должна попадать в его центр. */
  var STOPS=[[87,118],[255,118],[440,118],[635,118],[793,233],[635,348],[440,348],[169,348]];
  var total=route.getTotalLength();

  function fractionAt(x,y){
    var samples=1400,best=0,bestD=Infinity;
    for(var i=0;i<=samples;i++){
      var p=route.getPointAtLength(total*i/samples);
      var dx=p.x-x,dy=p.y-y,d=dx*dx+dy*dy;
      if(d<bestD){bestD=d;best=i/samples;}
    }
    return best;
  }
  var fracs=STOPS.map(function(s){return fractionAt(s[0],s[1]);});

  var active=-1;
  function setActive(i){
    if(i===active)return;
    active=i;
    reel.style.setProperty('--shm-i',i);
    reel.setAttribute('data-active',i);
    rows.forEach(function(el,n){el.classList.toggle('is-open',n===i);});
    zones.forEach(function(el,n){el.classList.toggle('is-on',n===i);});
    nodes.forEach(function(el,n){el.classList.toggle('is-on',n===i);});
  }

  function moveSpark(f){
    var p=route.getPointAtLength(f*total);
    spark.setAttribute('transform','translate('+p.x.toFixed(2)+','+p.y.toFixed(2)+')');
  }
  function ease(x){return x<.5?2*x*x:1-Math.pow(-2*x+2,2)/2;}

  var raf=0,t0=null,visible=false,hold=false,resumeTimer=0;

  function tick(ts){
    raf=requestAnimationFrame(tick);
    /* Точка входа в таймлайн — начало стоянки текущего участка: после
       перехвата курсором тур продолжается с той зоны, где его оставили. */
    if(t0===null)t0=ts-(Math.max(active,0)*SEG+TRAVEL);
    var t=(ts-t0)%LOOP;
    if(t<0)t+=LOOP;
    var i=Math.floor(t/SEG);
    if(i>=rows.length){                       // выезд: докатываем метку к отгрузке
      var le=Math.min((t-rows.length*SEG)/TRAVEL,1);
      moveSpark(fracs[rows.length-1]+(1-fracs[rows.length-1])*ease(le));
      return;
    }
    var local=t-i*SEG;
    var from=i===0?0:fracs[i-1];
    moveSpark(local<TRAVEL?from+(fracs[i]-from)*ease(local/TRAVEL):fracs[i]);
    setActive(i);
  }

  function start(){if(raf||reduce.matches)return;t0=null;raf=requestAnimationFrame(tick);}
  function stop(){if(raf){cancelAnimationFrame(raf);raf=0;}t0=null;}

  function pick(i){setActive(i);moveSpark(fracs[i]);}
  function takeOver(i,sticky){
    hold=true;stop();pick(i);
    if(resumeTimer){clearTimeout(resumeTimer);resumeTimer=0;}
    if(sticky)resumeTimer=setTimeout(release,9000);
  }
  function release(){
    if(resumeTimer){clearTimeout(resumeTimer);resumeTimer=0;}
    hold=false;
    if(visible)start();
  }

  function bind(el,i){
    el.addEventListener('pointerenter',function(e){
      if(e.pointerType==='touch')return;      // тапу отвечает click — со своим удержанием
      takeOver(i,false);
    });
    el.addEventListener('click',function(){takeOver(i,true);});
    el.addEventListener('focus',function(){takeOver(i,false);});
  }
  rows.forEach(bind);
  zones.forEach(bind);
  split.addEventListener('pointerleave',function(e){
    if(e.pointerType==='touch')return;
    release();
  });
  split.addEventListener('focusout',function(e){
    if(!split.contains(e.relatedTarget))release();
  });

  function playAll(){vids.forEach(function(v){var p=v.play();if(p&&p.catch)p.catch(function(){});});}
  function pauseAll(){vids.forEach(function(v){v.pause();});}

  /* Два <video> крутятся постоянно — вне экрана их надо глушить, иначе
     секция жжёт декодер на всей остальной странице. */
  var io=new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      visible=e.isIntersecting;
      if(visible){playAll();if(!hold)start();}
      else{pauseAll();stop();}
    });
  },{threshold:.06});
  io.observe(section);

  /* Возврат на вкладку: браузер сам глушит видео в фоне и не поднимает его
     обратно — IntersectionObserver тут не сработает, секция не двигалась.
     Таймлайн сбрасываем, иначе метка прыгает на случайный участок. */
  document.addEventListener('visibilitychange',function(){
    if(document.hidden){pauseAll();stop();return;}
    if(!visible)return;
    playAll();
    if(!hold){stop();start();}
  });

  setActive(0);
  moveSpark(fracs[0]);
})();
