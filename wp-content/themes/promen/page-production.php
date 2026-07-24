<?php
/**
 * Страница «Производство» — 1:1 из html/production.html (Open Design, 2026-07-22).
 * Хром — header.php, форма s10 и футер — footer.php. Скрипты страницы —
 * assets/js/production.js, стили — assets/css/production.css (functions.php).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-02.PRD / REV.3' );
add_filter( 'promen_s10_eyebrow_num', fn () => '11' );
add_filter( 'promen_strip_text', fn () => 'ПЭ · ЗАВОД' );

get_header();
?>
<nav class="sidenav" id="sidenav">
  <a class="sidenav-item sn-active" href="#s1"><span class="sidenav-dot"></span><span class="sidenav-label">ТОЧНОСТЬ</span></a>
  <a class="sidenav-item" href="#proof"><span class="sidenav-dot"></span><span class="sidenav-label">В ЦЕХЕ</span></a>
  <a class="sidenav-item" href="#s2"><span class="sidenav-dot"></span><span class="sidenav-label">НОРМИРОВАНИЕ</span></a>
  <a class="sidenav-item" href="#grades"><span class="sidenav-dot"></span><span class="sidenav-label">МАТЕРИАЛЫ</span></a>
  <a class="sidenav-item" href="#map"><span class="sidenav-dot"></span><span class="sidenav-label">ИСТОРИЯ ИЗДЕЛИЯ</span></a>
  <a class="sidenav-item" href="#shopmap"><span class="sidenav-dot"></span><span class="sidenav-label">КАРТА ЦЕХА</span></a>
  <a class="sidenav-item" href="#thermal"><span class="sidenav-dot"></span><span class="sidenav-label">ТЕРМООБРАБОТКА</span></a>
  <a class="sidenav-item" href="#journal"><span class="sidenav-dot"></span><span class="sidenav-label">ЖУРНАЛ ОТК</span></a>
  <a class="sidenav-item" href="#capacity"><span class="sidenav-dot"></span><span class="sidenav-label">ВОЗМОЖНОСТИ</span></a>
  <a class="sidenav-item" href="#fleet"><span class="sidenav-dot"></span><span class="sidenav-label">МАШИННЫЙ ПАРК</span></a>
  <a class="sidenav-item" href="#gallery"><span class="sidenav-dot"></span><span class="sidenav-label">ЦЕХ В ОБЪЕКТИВЕ</span></a>
  <a class="sidenav-item" href="#portal"><span class="sidenav-dot"></span><span class="sidenav-label">ДОКУМЕНТЫ</span></a>
</nav>

<main class="pg">

<!-- ══════════════════════════════════════
     СЦЕНА 1 — ТОЧНОСТЬ
     Первое, что видит пользователь: единственный факт,
     который говорит об инженерной культуре больше, чем
     любой список преимуществ.
══════════════════════════════════════ -->
<section id="s1">
  <div class="s1-beam" aria-hidden="true"><div class="s1-beam-line"></div></div>
  <div class="s1-reg tl"></div>
  <div class="s1-reg tr"></div>
  <div class="s1-reg bl"></div>
  <div class="s1-reg br"></div>

  <div class="s1-top">
    <span>ПРОИЗВОДСТВО · ООО ЗАВОД «ПРОМЫШЛЕННАЯ ЭНЕРГЕТИКА» · ЧЕЛЯБИНСК</span>
    <span>N55°10′18″ E61°24′07″</span>
  </div>

  <div class="s1-body">
    <!-- LEFT: the number -->
    <div class="s1-left">
      <div class="s1-eyebrow">ПРОИЗВОДИМ СДТ С ТОЧНОСТЬЮ ДО</div>

      <div class="s1-num-group">
        <span class="s1-num">0.01</span>
        <span class="s1-unit">мм</span>
      </div>

      <div class="s1-caption">
        <div class="s1-label">ДОПУСК НА УПЛОТНИТЕЛЬНЫХ ПОВЕРХНОСТЯХ</div>
        <div class="s1-sub">Трубопроводная арматура АЭС и ТЭС.<br>ГОСТ 24643 · Квалитет IT7 · НАКС II уровень.</div>
      </div>
    </div>

    <!-- RIGHT: engineering annotation callouts -->
    <div class="s1-right">
      <div class="s1-anns">
        <div class="s1-ann">
          <div class="ann-val">Ra 0.4</div>
          <div class="ann-key">Шероховатость<br>поверхности</div>
        </div>
        <div class="s1-ann">
          <div class="ann-val">IT7</div>
          <div class="ann-key">Квалитет<br>точности ISO</div>
        </div>
        <div class="s1-ann">
          <div class="ann-val">±0.01</div>
          <div class="ann-key">Допуск<br>на диаметр, мм</div>
        </div>
        <div class="s1-ann">
          <div class="ann-val">НК II</div>
          <div class="ann-key">Уровень<br>неразруш. контроля</div>
        </div>
      </div>
    </div>
  </div>

  <div class="s1-foot">
    <div class="s1-foot-l">
      <span style="color:rgba(109,140,166,.2)">01</span>
      <span>ТОЧНОСТЬ</span>
    </div>
    <span class="s1-scroll">↓</span>
  </div>
</section>

<!-- ══════════════════════════════════════
     §PROOF — ВИДЕОДОКАЗАТЕЛЬСТВО
     Живое подтверждение точности из первого экрана.
     Кадр из цеха, не реклама.
══════════════════════════════════════ -->
<section id="proof">
  <div class="prf-sticky">

    <!-- Intro: cinematic frame visible before scroll reveal -->
    <div class="prf-intro" id="prfIntro" aria-hidden="true">
      <div class="prf-ib-rule top"></div>
      <div class="prf-ib-rule bottom"></div>
      <div class="prf-ib-top">
        <span class="prf-ib-label-l">ВИДЕОДОКАЗАТЕЛЬСТВО &middot; ТОЧНОСТЬ В ДЕЙСТВИИ</span>
        <span class="prf-ib-label-r">ЦЕХ № 3 &middot; КАРУСЕЛЬНЫЙ СТАНОК</span>
      </div>
      <div class="prf-ib-left">
        <div class="prf-ib-spec">
          <span class="prf-ib-sk">Допуск</span>
          <span class="prf-ib-sv">± 0.01 мм</span>
        </div>
        <div class="prf-ib-spec">
          <span class="prf-ib-sk">Квалитет</span>
          <span class="prf-ib-sv">IT 7</span>
        </div>
      </div>
      <div class="prf-ib-right">
        <div class="prf-ib-spec" style="align-items:flex-end">
          <span class="prf-ib-sk">Шероховатость</span>
          <span class="prf-ib-sv">Ra 0.4</span>
        </div>
        <div class="prf-ib-spec" style="align-items:flex-end">
          <span class="prf-ib-sk">Операция</span>
          <span class="prf-ib-sv">Расточка</span>
        </div>
      </div>
      <div class="prf-ib-corner itl"></div>
      <div class="prf-ib-corner itr"></div>
      <div class="prf-ib-corner ibl"></div>
      <div class="prf-ib-corner ibr"></div>
      <div class="prf-ib-bottom">
        <div class="prf-ib-cue">
          <div class="prf-ib-tick"></div>
          <span>ПРОКРУТИТЕ &middot; ВОЙДИТЕ В ЦЕХ</span>
        </div>
      </div>
    </div>

    <div class="prf-clip" id="prfClip">

      <!-- Video background -->
      <div class="prf-video-wrap">
        <video class="prf-video" autoplay muted loop playsinline preload="auto">
          <source src="<?php echo esc_url( get_theme_file_uri( 'assets/media/tochnost_do_1mm-2.webm' ) ); ?>" type="video/webm">
        </video>
      </div>

      <!-- Gradient overlay -->
      <div class="prf-overlay" aria-hidden="true"></div>

      <!-- Scan line -->
      <div class="prf-scan" aria-hidden="true"></div>

      <!-- Corner brackets -->
      <div class="prf-brk tl" aria-hidden="true"></div>
      <div class="prf-brk tr" aria-hidden="true"></div>
      <div class="prf-brk bl" aria-hidden="true"></div>
      <div class="prf-brk br" aria-hidden="true"></div>

      <!-- Status bar -->
      <div class="prf-status" id="prfStatus">
        <div class="prf-rec">
          <span class="prf-rec-dot"></span>
          ЧИСТОВОЕ РАСТОЧЕНИЕ &middot; ЦЕХ № 3
        </div>
        <div class="prf-status-r">
          <span>ЧПУ КАРУСЕЛЬНЫЙ</span>
          <span class="prf-status-sep">&middot;</span>
          <span>850 ОБ/МИН</span>
          <span class="prf-status-sep">&middot;</span>
          <span id="prfClock">08:47:00</span>
        </div>
      </div>

      <!-- Main content -->
      <div class="prf-main" id="prfMain">
        <div class="prf-kicker">ТОЧНОСТЬ В ДЕЙСТВИИ &middot; СЪЁМКА В ЦЕХЕ</div>
        <div class="prf-num-group">
          <span class="prf-num">0.01</span>
          <span class="prf-unit">мм</span>
        </div>
        <div class="prf-label">ДОПУСК НА УПЛОТНИТЕЛЬНЫХ ПОВЕРХНОСТЯХ</div>
        <p class="prf-text">Этот кадр снят в цехе в рабочую смену — не постановка, не реклама. Чистовое расточение на карусельном станке. Именно эта операция даёт допуск ±&nbsp;0.01&nbsp;мм и шероховатость Ra&nbsp;0.4, о которых говорит первый экран.</p>
      </div>

      <!-- Measurement target (right zone) -->
      <div class="prf-target" id="prfTarget">
        <div class="prf-target-ring"></div>
        <div class="prf-target-dot"></div>
        <div class="prf-target-lbl">ЗАМЕР &middot; IT7</div>
      </div>

      <!-- Annotation callout linked to target -->
      <div class="prf-ann" id="prfAnn">
        <div class="prf-ann-val">Ra 0.4</div>
        <div class="prf-ann-k">Шероховатость</div>
        <div class="prf-ann-line"></div>
      </div>

      <!-- Parameters strip -->
      <div class="prf-params" id="prfParams">
        <div class="prf-param"><span class="prf-pk">МАТЕР.</span>&ensp;<span class="prf-pv">Сталь 20</span></div>
        <div class="prf-param"><span class="prf-pk">ОПЕРАЦИЯ</span>&ensp;<span class="prf-pv">Расточка</span></div>
        <div class="prf-param"><span class="prf-pk">ПОДАЧА</span>&ensp;<span class="prf-pv">0.08 мм/об</span></div>
        <div class="prf-param"><span class="prf-pk">ШЕРОХ.</span>&ensp;<span class="prf-pv">Ra 0.4</span></div>
        <div class="prf-param"><span class="prf-pk">КВАЛИТЕТ</span>&ensp;<span class="prf-pv">IT7</span></div>
        <div class="prf-param"><span class="prf-pk">ДОПУСК</span>&ensp;<span class="prf-pv">±0.01 мм</span></div>
      </div>

    </div><!-- /.prf-clip -->
  </div><!-- /.prf-sticky -->
</section>

<!-- ══════════════════════════════════════
     СЦЕНА 2 — НОРМИРОВАНИЕ
     Три отрасли. Реальные стандарты. Без слов об опыте.
     Инженер видит НП-045-18 — и всё понятно.
══════════════════════════════════════ -->
<section id="s2">
  <div class="s2-top">
    <div class="s2-claim">
      Каждое изделие изготавливается в соответствии<br>
      с нормативными требованиями отрасли, которой служит
    </div>
    <div class="s2-top-r">
      <span style="color:rgba(109,140,166,.2)">02</span>
      <span>НОРМИРОВАНИЕ</span>
    </div>
  </div>

  <div class="s2-cols">
    <!-- ТЭС -->
    <div class="s2-col" data-ind="tes">
      <div class="s2-col-hd">
        <div class="s2-industry">ТЭС</div>
        <div class="s2-industry-full">Тепловая энергетика</div>
      </div>
      <div class="s2-col-stake">Отказ паропровода — остановка блока мощностью 800 МВт</div>
      <div class="s2-cell">
        <div class="s2-std">ГОСТ 17375</div>
        <div class="s2-what">Крутоизогнутые отводы паропроводов тепловых электростанций</div>
      </div>
      <div class="s2-cell">
        <div class="s2-std">ОСТ 108-07</div>
        <div class="s2-what">Детали трубопроводов высокого давления паровых котлов</div>
      </div>
      <div class="s2-cell">
        <div class="s2-std">ГОСТ 17380</div>
        <div class="s2-what">Тройники, переходы, заглушки — детали трубопроводов</div>
      </div>
    </div>

    <!-- АЭС — тёмная колонка, высший регуляторный уровень -->
    <div class="s2-col" data-ind="aes">
      <div class="s2-col-hd">
        <div class="s2-industry">АЭС</div>
        <div class="s2-industry-full">Атомная энергетика</div>
      </div>
      <div class="s2-col-stake">Один дефект — плановый останов реактора и расследование</div>
      <div class="s2-cell">
        <div class="s2-std">НП-045-18</div>
        <div class="s2-what">Правила устройства и безопасной эксплуатации оборудования АС</div>
      </div>
      <div class="s2-cell">
        <div class="s2-std">ТР ТС 032</div>
        <div class="s2-what">Технический регламент — оборудование под давлением</div>
      </div>
      <div class="s2-cell">
        <div class="s2-std">НП-086-11</div>
        <div class="s2-what">Правила ядерной безопасности реакторных установок</div>
      </div>
    </div>

    <!-- НГО -->
    <div class="s2-col" data-ind="ngo">
      <div class="s2-col-hd">
        <div class="s2-industry">НГО</div>
        <div class="s2-industry-full">Нефтегаз и химия</div>
      </div>
      <div class="s2-col-stake">Утечка на скважине — экологическая катастрофа и штраф</div>
      <div class="s2-cell">
        <div class="s2-std">ОСТ 26-07</div>
        <div class="s2-what">Детали трубопроводов нефтяной и газовой промышленности</div>
      </div>
      <div class="s2-cell">
        <div class="s2-std">ГОСТ 9941</div>
        <div class="s2-what">Трубы нержавеющие холоднодеформированные для хим. отрасли</div>
      </div>
      <div class="s2-cell">
        <div class="s2-std">ОСТ 26-18.6</div>
        <div class="s2-what">Сосуды и аппараты нефтехимической и нефтеперерабатывающей промышленности</div>
      </div>
    </div>
  </div>

  <div class="s2-foot">
    <span class="s2-decl">ТР ТС 032/2013 · ДЕКЛАРАЦИЯ RU С-RU.АБ53.В.08323/23 · ДЕЙСТВИТЕЛЬНА</span>
    <span class="s2-continue">↓ БИБЛИОТЕКА МАТЕРИАЛОВ</span>
  </div>
</section>

<!-- ═══════════════════════ §02б БИБЛИОТЕКА МАТЕРИАЛОВ ═══════════════════════ -->
<section id="grades">
  <div class="grades-hd">
    <div class="grades-tag">БИБЛИОТЕКА МАТЕРИАЛОВ</div>
    <span style="font-family:'DINPro',monospace;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--g2);opacity:.5;">STEEL GRADE REFERENCE</span>
  </div>
  <div class="grades-intro">
    <div class="gi-statement">
      Завод работает с <strong>8 марками стали</strong> — от углеродистых Сталь 20 для стандартных паропроводов до высокохромистых сплавов для высокоэффективных ТЭС и аустенитных нержавеющих для объектов АЭС. Выбор марки определяется рабочей температурой, давлением и отраслевым регламентом — мы помогаем с подбором на этапе ТЗ.
    </div>
    <div class="gi-stats">
      <div class="gi-stat">
        <div class="gi-stat-v">8</div>
        <div class="gi-stat-l">Марок стали</div>
      </div>
      <div class="gi-stat">
        <div class="gi-stat-v">+650°</div>
        <div class="gi-stat-l">Макс. рабочая<br>температура</div>
      </div>
      <div class="gi-stat">
        <div class="gi-stat-v">3</div>
        <div class="gi-stat-l">Отрасли<br>ТЭС · АЭС · НГО</div>
      </div>
    </div>
  </div>
  <div class="grades-grid">
    <div class="ge"><span class="ge-idx">01</span><div class="ge-name">ст20</div><div class="ge-std">ГОСТ 1050 · Углеродистая</div><div class="ge-props"><div><div class="ge-pv">412</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+350°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">ТЭС · Паропроводы · Трубопроводы</div></div>
    <div class="ge"><span class="ge-idx">02</span><div class="ge-name">09Г2С</div><div class="ge-std">ГОСТ 19281 · Низколегированная</div><div class="ge-props"><div><div class="ge-pv">490</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">−70°</div><div class="ge-pk">Мин.темп.</div></div></div><div class="ge-app">НГО · Северное исполнение</div></div>
    <div class="ge"><span class="ge-idx">03</span><div class="ge-name" style="font-size:21px;letter-spacing:-.005em;">12Х18Н10Т</div><div class="ge-std">ГОСТ 5632 · Аустенитная</div><div class="ge-props"><div><div class="ge-pv">540</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+600°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">АЭС · Химическая промышленность</div></div>
    <div class="ge"><span class="ge-idx">04</span><div class="ge-name">15ГС</div><div class="ge-std">ОСТ 108 · Теплоустойчивая</div><div class="ge-props"><div><div class="ge-pv">412</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+475°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">ТЭС · Паровые системы</div></div>
    <div class="ge"><span class="ge-idx">05</span><div class="ge-name">15Х5М</div><div class="ge-std">ГОСТ 20072 · Хромомолибд.</div><div class="ge-props"><div><div class="ge-pv">412</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+550°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">НГО · Нефтехимия</div></div>
    <div class="ge"><span class="ge-idx">06</span><div class="ge-name" style="font-size:22px;">10Х9МФБ</div><div class="ge-std">ТУ 14 · Высокохромистая</div><div class="ge-props"><div><div class="ge-pv">620</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+650°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">Высокоэффективные ТЭС · ПГУ</div></div>
    <div class="ge"><span class="ge-idx">07</span><div class="ge-name" style="font-size:21px;letter-spacing:-.005em;">08Х18Н10Т</div><div class="ge-std">ГОСТ 5632 · Нержавеющая</div><div class="ge-props"><div><div class="ge-pv">520</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+600°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">АЭС · Криогенные системы</div></div>
    <div class="ge"><span class="ge-idx">08</span><div class="ge-name">15Х5МУ</div><div class="ge-std">ГОСТ 20072 · Жаропрочная</div><div class="ge-props"><div><div class="ge-pv">540</div><div class="ge-pk">σв, МПа</div></div><div><div class="ge-pv">+600°</div><div class="ge-pk">Макс.темп.</div></div></div><div class="ge-app">ТЭС · Реакторные системы</div></div>
  </div>
</section>

<!-- ═══════════════════════ §03 ИСТОРИЯ ИЗДЕЛИЯ ═══════════════════════ -->
<section id="map">
  <div class="map-scan" aria-hidden="true"></div>
  <div class="map-hd">
    <div class="map-hd-row">
      <div class="map-tag">ИСТОРИЯ ИЗДЕЛИЯ</div>
      <div class="map-spec">ТР. 219×12 · СТАЛЬ 20 · ГОСТ 8732 · ТЭС / АЭС</div>
    </div>
    <div class="map-subtitle">Полный производственный маршрут конкретной трубы — от приёмки проката до передачи изделия с паспортом. Каждый из 6 этапов документируется и не может быть пропущен вне зависимости от объёма партии.</div>
  </div>
  <div class="map-stages">

    <!-- 01 ПРОКАТ -->
    <div class="ms">
      <div class="ms-step">ШАГ 01</div>
      <div class="ms-num">01</div>
      <div class="ms-svg-wrap">
        <svg viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg">
          <rect x="8" y="24" width="74" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="76" width="74" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="24" width="10" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="72" y="24" width="10" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <line x1="4" y1="55" x2="86" y2="55" stroke="rgba(109,140,166,.25)" stroke-width=".4" stroke-dasharray="6 2 2 2"/>
          <line x1="8" y1="17" x2="82" y2="17" stroke="rgba(109,140,166,.35)" stroke-width=".6"/>
          <line x1="8" y1="14" x2="8" y2="20" stroke="rgba(109,140,166,.35)" stroke-width=".6"/>
          <line x1="82" y1="14" x2="82" y2="20" stroke="rgba(109,140,166,.35)" stroke-width=".6"/>
          <text x="45" y="12" text-anchor="middle" font-family="'DINPro',monospace" font-size="6" fill="rgba(109,140,166,.48)">Ø 219 мм</text>
          <path d="M60,87 l2,-5 l2,8 l2,-6 l2,4 l2,-3" fill="none" stroke="rgba(109,140,166,.3)" stroke-width=".8"/>
          <text x="70" y="101" text-anchor="middle" font-family="'DINPro',monospace" font-size="5.5" fill="rgba(109,140,166,.35)">Ra 12.5</text>
        </svg>
      </div>
      <div class="ms-label">ПРОКАТ</div>
      <div class="ms-anns">
        <div class="ms-ann"><span>Ø 219×12</span> ГОСТ 8732-78</div>
        <div class="ms-ann"><span>Сталь 20</span> углеродистая</div>
        <div class="ms-ann"><span>ВК + МК</span> входной контроль</div>
      </div>
      <div class="ms-arrow">→</div>
    </div>

    <!-- 02 РАЗРЕЗКА -->
    <div class="ms">
      <div class="ms-step">ШАГ 02</div>
      <div class="ms-num">02</div>
      <div class="ms-svg-wrap">
        <svg viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg">
          <rect x="14" y="24" width="62" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="14" y="76" width="62" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="14" y="24" width="9" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.5)" stroke-width="1.5"/>
          <rect x="67" y="24" width="9" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.5)" stroke-width="1.5"/>
          <line x1="4" y1="55" x2="86" y2="55" stroke="rgba(109,140,166,.25)" stroke-width=".4" stroke-dasharray="6 2 2 2"/>
          <line x1="14" y1="100" x2="76" y2="100" stroke="rgba(109,140,166,.35)" stroke-width=".6"/>
          <line x1="14" y1="97" x2="14" y2="103" stroke="rgba(109,140,166,.35)" stroke-width=".6"/>
          <line x1="76" y1="97" x2="76" y2="103" stroke="rgba(109,140,166,.35)" stroke-width=".6"/>
          <text x="45" y="108" text-anchor="middle" font-family="'DINPro',monospace" font-size="5.5" fill="rgba(109,140,166,.45)">L = 450 мм</text>
          <text x="23" y="14" text-anchor="middle" font-family="'DINPro',monospace" font-size="7" fill="rgba(109,140,166,.42)">⊥</text>
          <text x="67" y="14" text-anchor="middle" font-family="'DINPro',monospace" font-size="7" fill="rgba(109,140,166,.42)">⊥</text>
        </svg>
      </div>
      <div class="ms-label">РАЗРЕЗКА</div>
      <div class="ms-anns">
        <div class="ms-ann"><span>L = 450</span> мм ± 2</div>
        <div class="ms-ann"><span>Пила / плазма</span> резка</div>
        <div class="ms-ann"><span>⊥ торец</span> перпендикулярность</div>
      </div>
      <div class="ms-arrow">→</div>
    </div>

    <!-- 03 МЕХОБРАБОТКА (wide) -->
    <div class="ms">
      <div class="ms-step">ШАГ 03</div>
      <div class="ms-num">03</div>
      <div class="ms-svg-wrap">
        <svg viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg">
          <rect x="8" y="24" width="74" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="76" width="74" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="5" y="20" width="13" height="70" fill="rgba(109,140,166,.22)" stroke="rgba(169,183,198,.68)" stroke-width="1.7"/>
          <rect x="72" y="24" width="10" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <line x1="4" y1="55" x2="86" y2="55" stroke="rgba(109,140,166,.25)" stroke-width=".4" stroke-dasharray="6 2 2 2"/>
          <line x1="5" y1="42" x2="0" y2="35" stroke="rgba(169,183,198,.38)" stroke-width=".7"/>
          <text x="-1" y="33" font-family="'DINPro',monospace" font-size="5.5" fill="rgba(169,183,198,.58)">Ra 0.4</text>
          <line x1="11" y1="20" x2="20" y2="11" stroke="rgba(109,140,166,.3)" stroke-width=".6"/>
          <text x="22" y="9" font-family="'DINPro',monospace" font-size="6" fill="rgba(109,140,166,.48)">IT7</text>
          <line x1="18" y1="55" x2="29" y2="43" stroke="rgba(109,140,166,.22)" stroke-width=".5"/>
          <text x="31" y="41" font-family="'DINPro',monospace" font-size="5" fill="rgba(109,140,166,.36)">±0.05</text>
          <path d="M72,88 l2,-4 l2,7 l2,-5 l2,4 l2,-2" fill="none" stroke="rgba(109,140,166,.22)" stroke-width=".7"/>
        </svg>
      </div>
      <div class="ms-label">МЕХОБРАБОТКА</div>
      <div class="ms-anns">
        <div class="ms-ann"><span>Ra 0.4</span> уплотнит. поверхность</div>
        <div class="ms-ann"><span>IT7</span> квалитет точности</div>
        <div class="ms-ann"><span>Ø 2500 мм</span> макс. диаметр</div>
        <div class="ms-ann"><span>40+ станков</span> ЧПУ парк</div>
      </div>
      <div class="ms-arrow">→</div>
    </div>

    <!-- 04 СВАРКА -->
    <div class="ms">
      <div class="ms-step">ШАГ 04</div>
      <div class="ms-num">04</div>
      <div class="ms-svg-wrap">
        <svg viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg">
          <rect x="8" y="24" width="32" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="50" y="24" width="32" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="76" width="32" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="50" y="76" width="32" height="10" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="24" width="9" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="73" y="24" width="9" height="62" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <path d="M40,24 L43,18 L47,18 L50,24" fill="rgba(169,183,198,.18)" stroke="rgba(169,183,198,.52)" stroke-width="1.1"/>
          <path d="M40,86 L43,92 L47,92 L50,86" fill="rgba(169,183,198,.18)" stroke="rgba(169,183,198,.52)" stroke-width="1.1"/>
          <line x1="45" y1="18" x2="45" y2="92" stroke="rgba(109,140,166,.25)" stroke-width=".5" stroke-dasharray="3 2"/>
          <line x1="4" y1="55" x2="86" y2="55" stroke="rgba(109,140,166,.25)" stroke-width=".4" stroke-dasharray="6 2 2 2"/>
          <line x1="45" y1="38" x2="60" y2="26" stroke="rgba(109,140,166,.3)" stroke-width=".6"/>
          <line x1="60" y1="26" x2="78" y2="26" stroke="rgba(109,140,166,.3)" stroke-width=".6"/>
          <path d="M67,22 l3,8 l3,-8" fill="none" stroke="rgba(109,140,166,.42)" stroke-width=".8"/>
          <line x1="64" y1="22" x2="76" y2="22" stroke="rgba(109,140,166,.42)" stroke-width=".8"/>
          <text x="45" y="105" text-anchor="middle" font-family="'DINPro',monospace" font-size="5.5" fill="rgba(109,140,166,.36)">НАКС · ГОСТ 55143</text>
        </svg>
      </div>
      <div class="ms-label">СВАРКА</div>
      <div class="ms-anns">
        <div class="ms-ann"><span>30°</span> угол разделки кромки</div>
        <div class="ms-ann"><span>НАКС</span> аттест. сварщиков</div>
        <div class="ms-ann"><span>РДС / РАД / АФ</span></div>
      </div>
      <div class="ms-arrow">→</div>
    </div>

    <!-- 05 НК · КОНТРОЛЬ -->
    <div class="ms">
      <div class="ms-step">ШАГ 05</div>
      <div class="ms-num">05</div>
      <div class="ms-svg-wrap">
        <svg viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg">
          <rect x="8" y="30" width="74" height="9" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="72" width="74" height="9" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="8" y="30" width="9" height="51" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <rect x="73" y="30" width="9" height="51" fill="rgba(109,140,166,.17)" stroke="rgba(169,183,198,.42)" stroke-width="1.2"/>
          <line x1="4" y1="56" x2="86" y2="56" stroke="rgba(109,140,166,.25)" stroke-width=".4" stroke-dasharray="6 2 2 2"/>
          <rect x="34" y="16" width="22" height="14" rx="2" fill="rgba(109,140,166,.12)" stroke="rgba(109,140,166,.48)" stroke-width=".9"/>
          <text x="45" y="25" text-anchor="middle" font-family="'DINPro',monospace" font-size="5.5" fill="rgba(109,140,166,.55)">УЗК</text>
          <line x1="42" y1="30" x2="39" y2="40" stroke="rgba(109,140,166,.22)" stroke-width=".5" stroke-dasharray="2 1"/>
          <line x1="45" y1="30" x2="45" y2="42" stroke="rgba(109,140,166,.22)" stroke-width=".5" stroke-dasharray="2 1"/>
          <line x1="48" y1="30" x2="51" y2="40" stroke="rgba(109,140,166,.22)" stroke-width=".5" stroke-dasharray="2 1"/>
          <text x="84" y="37" text-anchor="end" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(109,140,166,.32)">ВК</text>
          <text x="84" y="44" text-anchor="end" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(109,140,166,.32)">МК</text>
          <text x="84" y="51" text-anchor="end" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(109,140,166,.32)">УЗК</text>
          <text x="84" y="58" text-anchor="end" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(109,140,166,.32)">РК</text>
          <text x="84" y="65" text-anchor="end" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(109,140,166,.32)">ПВК</text>
          <text x="84" y="72" text-anchor="end" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(109,140,166,.32)">ГИ</text>
        </svg>
      </div>
      <div class="ms-label">НК · КОНТРОЛЬ</div>
      <div class="ms-anns">
        <div class="ms-ann"><span>6 методов</span> ВК МК УЗК РК ПВК ГИ</div>
        <div class="ms-ann"><span>1.5 × PN</span> гидроиспытание</div>
        <div class="ms-ann"><span>НП-045-18</span> норматив АЭС</div>
      </div>
      <div class="ms-arrow">→</div>
    </div>

    <!-- 06 ГОТОВОЕ ИЗДЕЛИЕ (wide) -->
    <div class="ms">
      <div class="ms-step">ШАГ 06</div>
      <div class="ms-num">06</div>
      <div class="ms-svg-wrap">
        <svg viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg">
          <rect x="5" y="22" width="80" height="10" fill="rgba(109,140,166,.2)" stroke="rgba(169,183,198,.62)" stroke-width="1.4"/>
          <rect x="5" y="78" width="80" height="10" fill="rgba(109,140,166,.2)" stroke="rgba(169,183,198,.62)" stroke-width="1.4"/>
          <rect x="5" y="22" width="12" height="66" fill="rgba(109,140,166,.2)" stroke="rgba(169,183,198,.62)" stroke-width="1.4"/>
          <rect x="73" y="22" width="12" height="66" fill="rgba(109,140,166,.2)" stroke="rgba(169,183,198,.62)" stroke-width="1.4"/>
          <line x1="2" y1="55" x2="88" y2="55" stroke="rgba(109,140,166,.25)" stroke-width=".4" stroke-dasharray="6 2 2 2"/>
          <circle cx="45" cy="55" r="15" fill="none" stroke="rgba(76,175,80,.25)" stroke-width=".9" stroke-dasharray="3 1.5"/>
          <text x="45" y="52" text-anchor="middle" font-family="'DINPro',monospace" font-size="5.5" fill="rgba(76,175,80,.52)">ОТК</text>
          <text x="45" y="61" text-anchor="middle" font-family="'DINPro',monospace" font-size="5" fill="rgba(76,175,80,.42)">ПРИНЯТО</text>
          <text x="28" y="20" font-family="'DINPro',monospace" font-size="4.5" fill="rgba(169,183,198,.38)">219×12 Ст.20</text>
          <line x1="5" y1="44" x2="0" y2="36" stroke="rgba(109,140,166,.28)" stroke-width=".6"/>
          <text x="-1" y="33" font-family="'DINPro',monospace" font-size="5" fill="rgba(109,140,166,.4)">Ra0.4</text>
          <text x="45" y="104" text-anchor="middle" font-family="'DINPro',monospace" font-size="5" fill="rgba(109,140,166,.3)">ПАСПОРТ · СЕРТ. 3.1</text>
        </svg>
      </div>
      <div class="ms-label">ГОТОВОЕ ИЗДЕЛИЕ</div>
      <div class="ms-anns">
        <div class="ms-ann"><span>Паспорт</span> ГОСТ 2.601</div>
        <div class="ms-ann"><span>Сертификат 3.1</span> на партию</div>
        <div class="ms-ann"><span>ОТК ПРИНЯТО</span> к отгрузке</div>
        <div class="ms-ann"><span>Вся Россия</span> Ж/д и авто</div>
      </div>
    </div>

  </div>
  <div class="map-progress" aria-hidden="true">
    <div class="mp-steps" id="mpSteps">
      <div class="mp-step"><span class="mp-dot"></span><span class="mp-lbl">ПРОКАТ</span></div>
      <div class="mp-step"><span class="mp-dot"></span><span class="mp-lbl">РАЗРЕЗКА</span></div>
      <div class="mp-step"><span class="mp-dot"></span><span class="mp-lbl">МО</span></div>
      <div class="mp-step"><span class="mp-dot"></span><span class="mp-lbl">СВАРКА</span></div>
      <div class="mp-step"><span class="mp-dot"></span><span class="mp-lbl">НК</span></div>
      <div class="mp-step"><span class="mp-dot"></span><span class="mp-lbl">ОТГРУЗКА</span></div>
    </div>
  </div>
  <div class="map-foot">
    <div class="map-foot-kv">МАТЕРИАЛ&nbsp;<strong>Сталь 20 · ГОСТ 1050</strong></div>
    <div class="map-foot-kv">ИЗДЕЛИЕ&nbsp;<strong>Труба 219×12 · L=450 мм</strong></div>
    <div class="map-foot-kv">ЦИКЛ&nbsp;<strong>~7 дней</strong></div>
    <div class="map-foot-kv">ДОКУМЕНТАЦИЯ&nbsp;<strong>Паспорт + Серт. + 6 НК</strong></div>
  </div>
</section>

<!-- ═══════════════════════ §SHOPMAP — КАРТА ЦЕХА ═══════════════════════ -->
<section id="shopmap">
  <div class="shm-hd">
    <div class="shm-tag">КАРТА ЦЕХА</div>
    <span class="sec-hd-en">SHOP FLOOR PLAN</span>
  </div>
  <div class="shm-body">
    <div class="shm-intro">
      <p class="shm-statement">Производственные участки организованы по принципу единого потока — заготовка проходит через все зоны без возврата. Маршрут изготовления определяет планировку цеха площадью 4 200 м².</p>
      <div class="shm-nums">
        <div class="shm-num"><div class="shm-num-v">8</div><div class="shm-num-k">Участков</div></div>
        <div class="shm-num"><div class="shm-num-v">4 200 м²</div><div class="shm-num-k">Площадь</div></div>
        <div class="shm-num"><div class="shm-num-v">43</div><div class="shm-num-k">Ед. оборудования</div></div>
      </div>
    </div>
    <div class="shm-plan">
      <svg class="shm-plan-svg" viewBox="0 0 860 460" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
        <defs>
          <marker id="shm-arr" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto">
            <path d="M0,0 L7,3.5 L0,7 Z" fill="rgba(169,183,198,.65)"/>
          </marker>
          <filter id="shm-spark-glow" x="-300%" y="-300%" width="700%" height="700%">
            <feGaussianBlur stdDeviation="3.2"/>
          </filter>
        </defs>

        <!-- Building outline -->
        <rect x="1" y="1" width="858" height="458" fill="none" stroke="rgba(109,140,166,.22)" stroke-width="1.5"/>

        <!-- Zone fills -->
        <rect x="1" y="1" width="171" height="229" fill="rgba(109,140,166,.045)"/>
        <rect x="172" y="1" width="166" height="229" fill="rgba(109,140,166,.03)"/>
        <rect x="338" y="1" width="205" height="229" fill="rgba(109,140,166,.06)"/>
        <rect x="543" y="1" width="185" height="229" fill="rgba(109,140,166,.03)"/>
        <rect x="728" y="1" width="131" height="458" fill="rgba(160,70,15,.07)"/>
        <rect x="543" y="230" width="185" height="229" fill="rgba(46,125,50,.05)"/>
        <rect x="338" y="230" width="205" height="229" fill="rgba(46,125,50,.035)"/>
        <rect x="1" y="230" width="337" height="229" fill="rgba(109,140,166,.035)"/>

        <!-- Zone dividers -->
        <line x1="172" y1="1" x2="172" y2="230" stroke="rgba(109,140,166,.12)" stroke-width="1"/>
        <line x1="338" y1="1" x2="338" y2="459" stroke="rgba(109,140,166,.12)" stroke-width="1"/>
        <line x1="543" y1="1" x2="543" y2="459" stroke="rgba(109,140,166,.12)" stroke-width="1"/>
        <line x1="728" y1="1" x2="728" y2="459" stroke="rgba(109,140,166,.18)" stroke-width="1"/>
        <line x1="1" y1="230" x2="727" y2="230" stroke="rgba(109,140,166,.14)" stroke-width="1"/>

        <!-- Zone A: СКЛАД МЕТАЛЛА -->
        <text x="87" y="48" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(109,140,166,.09)" letter-spacing="-1">01</text>
        <text x="87" y="152" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2.5" fill="rgba(109,140,166,.75)">СКЛАД</text>
        <text x="87" y="166" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2.5" fill="rgba(109,140,166,.75)">МЕТАЛЛА</text>
        <text x="87" y="184" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.4)">ПРИЁМКА ПРОКАТА</text>
        <text x="87" y="200" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.3)">680 М²</text>

        <!-- Zone B: УЧАСТОК РЕЗКИ -->
        <text x="255" y="48" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(109,140,166,.07)" letter-spacing="-1">02</text>
        <text x="255" y="152" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2.5" fill="rgba(109,140,166,.72)">УЧАСТОК</text>
        <text x="255" y="166" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2.5" fill="rgba(109,140,166,.72)">РЕЗКИ</text>
        <text x="255" y="184" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.4)">ГАЗ · ПЛАЗМА · ПИЛА</text>
        <text x="255" y="200" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.3)">8 МАШИН · 520 М²</text>

        <!-- Zone C: МЕХАНООБРАБОТКА -->
        <text x="440" y="48" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(109,140,166,.1)" letter-spacing="-1">03</text>
        <text x="440" y="152" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(169,183,198,.72)">МЕХАНО-</text>
        <text x="440" y="166" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(169,183,198,.72)">ОБРАБОТКА</text>
        <text x="440" y="184" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.4)">ТОКАРНЫЙ · ФРЕЗЕРНЫЙ</text>
        <text x="440" y="200" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.3)">18 СТ. ЧПУ · 840 М²</text>

        <!-- Zone D: СВАРОЧНЫЙ ЦЕХ -->
        <text x="635" y="48" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(109,140,166,.07)" letter-spacing="-1">04</text>
        <text x="635" y="152" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(109,140,166,.72)">СВАРОЧНЫЙ</text>
        <text x="635" y="166" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(109,140,166,.72)">ЦЕХ</text>
        <text x="635" y="184" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.4)">АВТОМАТ · ОРБИТ. · РУЧНАЯ</text>
        <text x="635" y="200" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.3)">12 ПОСТОВ · 680 М²</text>

        <!-- Zone E: ТЕРМООБРАБОТКА -->
        <line x1="728" y1="230" x2="859" y2="230" stroke="rgba(109,140,166,.07)" stroke-width=".6" stroke-dasharray="4 3"/>
        <text x="793" y="40" text-anchor="middle" font-family="'DINPro',monospace" font-size="22" font-weight="900" fill="rgba(160,70,15,.15)" letter-spacing="-1">05</text>
        <text x="793" y="58" text-anchor="middle" font-family="'DINPro',monospace" font-size="7.5" letter-spacing="2" fill="rgba(180,100,50,.78)">ТЕРМО-</text>
        <text x="793" y="72" text-anchor="middle" font-family="'DINPro',monospace" font-size="7.5" letter-spacing="2" fill="rgba(180,100,50,.78)">ОБРАБОТКА</text>
        <text x="793" y="86" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(160,80,30,.55)">4 ПЕЧИ · ДО 900°C</text>
        <text x="793" y="100" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(160,80,30,.38)">480 М²</text>
        <rect x="754" y="268" width="22" height="26" rx="2" fill="none" stroke="rgba(160,70,15,.2)" stroke-width=".8"/>
        <rect x="782" y="268" width="22" height="26" rx="2" fill="none" stroke="rgba(160,70,15,.2)" stroke-width=".8"/>
        <rect x="754" y="302" width="22" height="26" rx="2" fill="none" stroke="rgba(160,70,15,.2)" stroke-width=".8"/>
        <rect x="782" y="302" width="22" height="26" rx="2" fill="none" stroke="rgba(160,70,15,.2)" stroke-width=".8"/>
        <text x="793" y="368" text-anchor="middle" font-family="'DINPro',monospace" font-size="6" letter-spacing="1" fill="rgba(160,80,30,.3)">КАМЕРНЫЕ ПЕЧИ</text>

        <!-- Zone F: КОНТРОЛЬ НК -->
        <text x="635" y="278" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(46,125,50,.1)" letter-spacing="-1">06</text>
        <text x="635" y="382" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(46,125,50,.72)">КОНТРОЛЬ НК</text>
        <text x="635" y="398" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(46,125,50,.5)">УЗК · РК · МК · ПВК</text>
        <text x="635" y="414" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(46,125,50,.35)">6 МЕТОДОВ · 540 М²</text>

        <!-- Zone G: ОТК -->
        <text x="440" y="278" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(46,125,50,.08)" letter-spacing="-1">07</text>
        <text x="440" y="382" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(46,125,50,.68)">ОТДЕЛ</text>
        <text x="440" y="396" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2" fill="rgba(46,125,50,.68)">КОНТРОЛЯ</text>
        <text x="440" y="412" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(46,125,50,.45)">ОТК · ПАСПОРТИЗАЦИЯ</text>
        <text x="440" y="428" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(46,125,50,.3)">520 М²</text>

        <!-- Zone H: СКЛАД ГП / ОТГРУЗКА -->
        <text x="169" y="278" text-anchor="middle" font-family="'DINPro',monospace" font-size="26" font-weight="900" fill="rgba(109,140,166,.08)" letter-spacing="-1">08</text>
        <text x="169" y="382" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2.5" fill="rgba(109,140,166,.68)">СКЛАД ГП</text>
        <text x="169" y="396" text-anchor="middle" font-family="'DINPro',monospace" font-size="8" letter-spacing="2.5" fill="rgba(109,140,166,.68)">ОТГРУЗКА</text>
        <text x="169" y="412" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.4)">ПАСПОРТ · СЕРТИФИКАТ</text>
        <text x="169" y="428" text-anchor="middle" font-family="'DINPro',monospace" font-size="6.5" letter-spacing="1.5" fill="rgba(109,140,166,.3)">960 М²</text>

        <!-- Entry arrow -->
        <line x1="1" y1="118" x2="38" y2="118" stroke="rgba(169,183,198,.55)" stroke-width="1.2" marker-end="url(#shm-arr)"/>
        <text x="3" y="108" font-family="'DINPro',monospace" font-size="6" letter-spacing="1.5" fill="rgba(109,140,166,.5)">ВХОД</text>
        <!-- Exit arrow -->
        <line x1="38" y1="348" x2="1" y2="348" stroke="rgba(169,183,198,.55)" stroke-width="1.2" marker-end="url(#shm-arr)"/>
        <text x="3" y="338" font-family="'DINPro',monospace" font-size="6" letter-spacing="1.5" fill="rgba(109,140,166,.5)">ВЫХОД</text>

        <!-- Route: static dashed track -->
        <path d="M 1,118 L 87,118 L 255,118 L 440,118 L 635,118 L 793,118 L 793,348 L 635,348 L 440,348 L 169,348 L 1,348" fill="none" stroke="rgba(109,140,166,.2)" stroke-width="1.5" stroke-dasharray="5 4"/>

        <!-- Route: invisible path — geometry source for the spark -->
        <path id="shm-route" d="M 1,118 L 87,118 L 255,118 L 440,118 L 635,118 L 793,118 L 793,348 L 635,348 L 440,348 L 169,348 L 1,348" fill="none" stroke="none"/>

        <!-- Waypoint nodes -->
        <circle cx="87" cy="118" r="3.5" fill="#060E18" stroke="rgba(109,140,166,.5)" stroke-width="1.2"/>
        <circle cx="255" cy="118" r="3.5" fill="#060E18" stroke="rgba(109,140,166,.5)" stroke-width="1.2"/>
        <circle cx="440" cy="118" r="3.5" fill="#060E18" stroke="rgba(169,183,198,.55)" stroke-width="1.2"/>
        <circle cx="635" cy="118" r="3.5" fill="#060E18" stroke="rgba(109,140,166,.5)" stroke-width="1.2"/>
        <circle cx="793" cy="118" r="3.5" fill="#060E18" stroke="rgba(160,70,15,.55)" stroke-width="1.2"/>
        <circle cx="793" cy="348" r="3.5" fill="#060E18" stroke="rgba(160,70,15,.55)" stroke-width="1.2"/>
        <circle cx="635" cy="348" r="3.5" fill="#060E18" stroke="rgba(46,125,50,.55)" stroke-width="1.2"/>
        <circle cx="440" cy="348" r="3.5" fill="#060E18" stroke="rgba(46,125,50,.55)" stroke-width="1.2"/>
        <circle cx="169" cy="348" r="3.5" fill="#060E18" stroke="rgba(109,140,166,.5)" stroke-width="1.2"/>

        <!-- Spark: moving marker, positioned via JS along #shm-route -->
        <g id="shm-spark-wrap" transform="translate(1,118)">
          <circle class="shm-spark-halo" r="8.5" fill="rgba(255,150,70,.4)"/>
          <circle class="shm-spark-core" r="3" fill="#FFF3E2"/>
        </g>
      </svg>
      <div class="shm-popup" data-video="01">
        <div class="shm-popup-hd"><span class="shm-popup-num">01</span><span class="shm-popup-name">СКЛАД МЕТАЛЛА</span></div>
        <video muted playsinline loop preload="none" src="<?php echo esc_url( get_theme_file_uri( 'assets/media/tochnost_do_1mm-2.webm' ) ); ?>"></video>
      </div>
      <div class="shm-popup" data-video="03">
        <div class="shm-popup-hd"><span class="shm-popup-num">03</span><span class="shm-popup-name">МЕХАНООБРАБОТКА</span></div>
        <video muted playsinline loop preload="none" src="<?php echo esc_url( get_theme_file_uri( 'assets/media/tochnost_do_1mm-2.webm' ) ); ?>"></video>
      </div>
      <div class="shm-popup" data-video="05">
        <div class="shm-popup-hd"><span class="shm-popup-num">05</span><span class="shm-popup-name">ТЕРМООБРАБОТКА</span></div>
        <video muted playsinline loop preload="none" src="<?php echo esc_url( get_theme_file_uri( 'assets/media/tochnost_do_1mm-2.webm' ) ); ?>"></video>
      </div>
      <div class="shm-popup" data-video="07">
        <div class="shm-popup-hd"><span class="shm-popup-num">07</span><span class="shm-popup-name">ОТДЕЛ КОНТРОЛЯ</span></div>
        <video muted playsinline loop preload="none" src="<?php echo esc_url( get_theme_file_uri( 'assets/media/tochnost_do_1mm-2.webm' ) ); ?>"></video>
      </div>
    </div>
    <!-- Мобильный/планшетный список зон: SVG-подписи нечитаемы на узких экранах,
         план остаётся мини-картой, данные зон — в этом списке (≤1024) -->
    <div class="shm-list" aria-hidden="true">
      <div class="shm-li"><span class="shm-li-num">01</span><div class="shm-li-body"><div class="shm-li-name">Склад металла</div><div class="shm-li-meta">Приёмка проката · 680 м²</div></div></div>
      <div class="shm-li"><span class="shm-li-num">02</span><div class="shm-li-body"><div class="shm-li-name">Участок резки</div><div class="shm-li-meta">Газ · плазма · пила · 8 машин · 520 м²</div></div></div>
      <div class="shm-li"><span class="shm-li-num">03</span><div class="shm-li-body"><div class="shm-li-name">Механообработка</div><div class="shm-li-meta">Токарный · фрезерный · 18 ст. ЧПУ · 840 м²</div></div></div>
      <div class="shm-li"><span class="shm-li-num">04</span><div class="shm-li-body"><div class="shm-li-name">Сварочный цех</div><div class="shm-li-meta">Автомат · орбит. · ручная · 12 постов · 680 м²</div></div></div>
      <div class="shm-li shm-li--heat"><span class="shm-li-num">05</span><div class="shm-li-body"><div class="shm-li-name">Термообработка</div><div class="shm-li-meta">4 печи · до 900°C · 480 м²</div></div></div>
      <div class="shm-li shm-li--qc"><span class="shm-li-num">06</span><div class="shm-li-body"><div class="shm-li-name">Контроль НК</div><div class="shm-li-meta">УЗК · РК · МК · ПВК · 6 методов · 540 м²</div></div></div>
      <div class="shm-li shm-li--qc"><span class="shm-li-num">07</span><div class="shm-li-body"><div class="shm-li-name">Отдел контроля</div><div class="shm-li-meta">ОТК · паспортизация · 520 м²</div></div></div>
      <div class="shm-li"><span class="shm-li-num">08</span><div class="shm-li-body"><div class="shm-li-name">Склад ГП / отгрузка</div><div class="shm-li-meta">Паспорт · сертификат · 960 м²</div></div></div>
    </div>
    <div class="shm-legend">
      <div class="shm-leg"><span class="shm-leg-dot" style="background:rgba(109,140,166,.5)"></span>Заготовка и обработка</div>
      <div class="shm-leg"><span class="shm-leg-dot" style="background:rgba(160,80,30,.65)"></span>Термический участок</div>
      <div class="shm-leg"><span class="shm-leg-dot" style="background:rgba(46,125,50,.6)"></span>Контроль качества</div>
      <div class="shm-leg"><span class="shm-leg-dot" style="background:rgba(169,183,198,.75)"></span>Маршрут изготовления</div>
    </div>
  </div>
</section>

<!-- ═══════════════════════ §03 THERMAL ═══════════════════════ -->
<section id="thermal">
  <div class="therm-hd">
    <div class="therm-hd-left">
      <div class="therm-tag">ТЕРМООБРАБОТКА</div>
      <div class="therm-headline">Мы меняем<br>структуру<br>металла</div>
      <div class="therm-statement">Термообработка — не финальная операция, а часть технологии. Каждый режим рассчитан под марку стали, геометрию изделия и требования НД. 42 программы в управляющей системе, точность выдержки ±5°C.</div>
    </div>
    <div class="therm-hd-right">
      <div class="therm-stats">
        <div class="therm-stat"><div class="therm-stat-v">+700°C</div><div class="therm-stat-l">Максимальная температура</div></div>
        <div class="therm-stat"><div class="therm-stat-v">42</div><div class="therm-stat-l">Программы термообработки</div></div>
        <div class="therm-stat"><div class="therm-stat-v">±5°C</div><div class="therm-stat-l">Точность выдержки</div></div>
      </div>
    </div>
  </div>
  <div class="therm-body">
    <div class="therm-bar-wrap">
      <div class="therm-bar"></div>
      <div class="therm-cursor" aria-hidden="true"></div>
    </div>
    <div class="therm-ticks">
      <div class="therm-tick"><span class="therm-tk-v" style="color:rgba(109,140,166,.55)">20°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:rgba(109,140,166,.65)">150°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:rgba(120,155,175,.75)">300°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:rgba(140,165,180,.85)">500°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:#a06030">720°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:#c04818">900°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:#d87020">1050°</span><span class="therm-tk-u">°C</span></div>
      <div class="therm-tick"><span class="therm-tk-v" style="color:#f0aa38">1200°</span><span class="therm-tk-u">°C</span></div>
    </div>
    <div class="therm-phases">
      <div class="therm-ph"><div class="therm-ph-r">20 — 100°C</div><div class="therm-ph-n">Исходное состояние</div></div>
      <div class="therm-ph"><div class="therm-ph-r">100 — 300°C</div><div class="therm-ph-n">Отпуск низкий</div></div>
      <div class="therm-ph"><div class="therm-ph-r">300 — 650°C</div><div class="therm-ph-n">Отжиг / Высокий отпуск</div></div>
      <div class="therm-ph"><div class="therm-ph-r">650 — 730°C</div><div class="therm-ph-n">Рекристаллизация</div></div>
      <div class="therm-ph"><div class="therm-ph-r">730 — 900°C</div><div class="therm-ph-n">Нормализация / Аустенитизация</div></div>
      <div class="therm-ph"><div class="therm-ph-r">860 — 950°C</div><div class="therm-ph-n">Закалка</div></div>
      <div class="therm-ph"><div class="therm-ph-r">950 — 1200°C</div><div class="therm-ph-n">Горячая деформация</div></div>
    </div>
  </div>
</section>

<!-- ═══════════════════════ §05 JOURNAL ═══════════════════════ -->
<section id="journal">
  <div class="jrn-hd">
    <div class="jrn-tag">ЖУРНАЛ ВХОДНОГО КОНТРОЛЯ · ФОРМА ВК-001</div>
    <span class="jrn-hd-en">INCOMING MATERIAL INSPECTION LOG</span>
  </div>
  <div class="jrn-context">
    <div class="jc-statement">
      Ни одна труба или заготовка не попадает в производство без прохождения <strong>входного контроля по 6 параметрам</strong>. Сертификат поставщика проверяется, химсостав и механические свойства подтверждаются стилоскопированием, геометрия замеряется. Только после заключения ОТК материал допускается к работе.
    </div>
    <div class="jc-steps">
      <div class="jc-step">
        <div class="jc-step-n">6</div>
        <div class="jc-step-l">параметров<br>контроля</div>
      </div>
      <div class="jc-step">
        <div class="jc-step-n">100%</div>
        <div class="jc-step-l">партий<br>проверяются</div>
      </div>
      <div class="jc-step">
        <div class="jc-step-n">ОТК</div>
        <div class="jc-step-l">подписывает<br>каждую запись</div>
      </div>
    </div>
  </div>
  <div class="jrn-layout" data-od-id="path-3-5-2">
    <div class="jrn-doc">
      <div class="jd-hd">ООО «Завод Промышленная Энергетика» · Форма ВК-001 · Ред.3 от 01.09.2024</div>
      <div class="jd-title">ЖУРНАЛ ВХОДНОГО КОНТРОЛЯ МЕТАЛЛОПРОКАТА И ЗАГОТОВОК</div>
      <hr class="jd-div">
      <div class="jd-meta">
        <span class="jd-rk">Запись №</span><span class="jd-rv">2025/0847</span>
        <span class="jd-rk">Дата</span><span class="jd-rv">14.03.2025</span>
        <span class="jd-rk">Смена</span><span class="jd-rv">2-я</span>
        <span class="jd-rk">Поставщик</span><span class="jd-rv">ПАО «Северсталь»</span>
        <span class="jd-rk">Накладная</span><span class="jd-rv">25С-08472</span>
        <span class="jd-rk">Материал</span><span class="jd-rv jd-span">Труба 219.1×12.0 ГОСТ 8732-78 · Сталь 20</span>
        <span class="jd-rk">№ плавки</span><span class="jd-rv">253847Ж</span>
        <span class="jd-rk">Кол-во</span><span class="jd-rv">12 труб / 14.76 т</span>
      </div>
      <hr class="jd-div">
      <div class="jd-checks">
        <div class="jd-ck"><span class="jd-ci">01</span><span class="jd-cn">Сертификат качества</span><span class="jd-dots"></span><span class="jd-cs ok">✓ ПОЛУЧЕН</span></div>
        <div class="jd-ck"><span class="jd-ci">02</span><span class="jd-cn">Химический состав</span><span class="jd-dots"></span><span class="jd-cs ok">✓ СООТВЕТСТВУЕТ</span></div>
        <div class="jd-ck"><span class="jd-ci">03</span><span class="jd-cn">Механические свойства σв / σт / δ / ψ</span><span class="jd-dots"></span><span class="jd-cs ok">✓ СООТВЕТСТВУЕТ</span></div>
        <div class="jd-ck"><span class="jd-ci">04</span><span class="jd-cn">Геометрические размеры ÷ ±0.3 мм</span><span class="jd-dots"></span><span class="jd-cs ok">✓ В ДОПУСКЕ</span></div>
        <div class="jd-ck"><span class="jd-ci">05</span><span class="jd-cn">Качество поверхности</span><span class="jd-dots"></span><span class="jd-cs ok">✓ БЕЗ ЗАМЕЧАНИЙ</span></div>
        <div class="jd-ck"><span class="jd-ci">06</span><span class="jd-cn">Стилоскопирование (марка стали)</span><span class="jd-dots"></span><span class="jd-cs ok">✓ СТАЛЬ 20 ПОДТВ.</span></div>
      </div>
      <hr class="jd-div">
      <div class="jd-verdict">
        <span class="jd-vl">Заключение ОТК</span>
        <div class="jd-vv-wrap">
          <span class="jd-vv">ПРИНЯТО</span>
          <div class="jd-stamp">ОТК<br>№ 0847<br>ПЭ · 25</div>
        </div>
      </div>
    </div>
    <div class="jrn-side">
      <div class="jrn-slbl">ДРУГИЕ ЗАПИСИ</div>
      <div class="ji act"><div class="ji-code">2025/0847 · 14.03.2025</div><div class="ji-name">Труба 219×12 Сталь 20 — 12 шт.</div><div class="ji-tag ok">● ПРИНЯТО</div></div>
      <div class="ji"><div class="ji-code">2025/0848 · 14.03.2025</div><div class="ji-name">Лист 16×2000 09Г2С — 8 листов</div><div class="ji-tag ok">● ПРИНЯТО</div></div>
      <div class="ji"><div class="ji-code">2025/0849 · 15.03.2025</div><div class="ji-name">Поковка DN300 Сталь 20 — 4 шт.</div><div class="ji-tag ok">● ПРИНЯТО</div></div>
      <div class="ji"><div class="ji-code">2025/0850 · 15.03.2025</div><div class="ji-name">Труба 159×7 12Х18Н10Т — 6 шт.</div><div class="ji-tag ok">● ПРИНЯТО</div></div>
      <div class="ji"><div class="ji-code">2025/0851 · 15.03.2025</div><div class="ji-name">Труба 108×6 09Г2С — 18 шт.</div><div class="ji-tag pend">○ НА ПРОВЕРКЕ</div></div>
      <div class="ji"><div class="ji-code">2025/0852 · 16.03.2025</div><div class="ji-name">Прокат круглый Ø120 Сталь 20</div><div class="ji-tag pend">○ НА ПРОВЕРКЕ</div></div>
    </div>
  </div>
</section>

<!-- ═══════════════════════ §06 МОЩНОСТИ ═══════════════════════ -->
<section id="capacity">
  <div class="cap-hd">
    <div class="cap-tag">ПРОИЗВОДСТВЕННЫЕ ВОЗМОЖНОСТИ</div>
    <span class="sec-hd-en">PRODUCTION CAPACITY</span>
  </div>
  <div class="cap-body">
    <div class="cap-anchor">
      <div class="cap-anchor-kicker">МАКСИМАЛЬНЫЙ ДИАМЕТР ОБРАБОТКИ</div>
      <div class="cap-anchor-num">2500</div>
      <div class="cap-anchor-unit">мм</div>
      <div class="cap-anchor-note">Обрабатываем крупногабаритные изделия без передачи на субподряд — полный цикл внутри одного предприятия</div>
      <div class="cap-anchor-badge">без субподряда · без посредников</div>
    </div>
    <div class="cap-list">
      <div class="cap-row">
        <div class="cap-row-num">DN 15–1400</div>
        <div class="cap-row-info">
          <div class="cap-row-label">Размерный ряд изделий</div>
          <div class="cap-row-note">от мелкой арматуры до крупных патрубков и корпусных деталей</div>
        </div>
      </div>
      <div class="cap-row">
        <div class="cap-row-num">40+</div>
        <div class="cap-row-info">
          <div class="cap-row-label">Станков ЧПУ в парке</div>
          <div class="cap-row-note">токарные · фрезерные · расточные · шлифовальные</div>
        </div>
      </div>
      <div class="cap-row">
        <div class="cap-row-num">PN 250</div>
        <div class="cap-row-info">
          <div class="cap-row-label">Бар — максимальное давление</div>
          <div class="cap-row-note">высоконагруженные системы ТЭС и АЭС</div>
        </div>
      </div>
      <div class="cap-row">
        <div class="cap-row-num">7–35</div>
        <div class="cap-row-info">
          <div class="cap-row-label">Дней — производственный цикл</div>
          <div class="cap-row-note">от принятия ТЗ до отгрузки с документами</div>
        </div>
      </div>
      <div class="cap-row">
        <div class="cap-row-num">6 методов НК</div>
        <div class="cap-row-info">
          <div class="cap-row-label">Неразрушающего контроля</div>
          <div class="cap-row-note">ВК · МК · УЗК · РК · ПВК · Гидроиспытание</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════ §FLEET — МАШИННЫЙ ПАРК ═══════════════════════ -->
<section id="fleet">
  <div class="fl-hd">
    <div class="fl-tag">МАШИННЫЙ ПАРК</div>
    <span class="sec-hd-en">EQUIPMENT FLEET</span>
  </div>
  <div class="fl-body">
    <div class="fl-anchor">
      <div class="fl-anchor-kicker">ПАРК ОБОРУДОВАНИЯ</div>
      <div class="fl-anchor-num">43</div>
      <div class="fl-anchor-unit">единицы</div>
      <div class="fl-anchor-stats">
        <div class="fl-astat"><span class="fl-astat-v">6</span><span class="fl-astat-k">Технологических групп</span></div>
        <div class="fl-astat"><span class="fl-astat-v">2500 мм</span><span class="fl-astat-k">Макс. диаметр</span></div>
        <div class="fl-astat"><span class="fl-astat-v">40 МПа</span><span class="fl-astat-k">Испыт. давление</span></div>
        <div class="fl-astat"><span class="fl-astat-v">900°C</span><span class="fl-astat-k">Темп. термообработки</span></div>
      </div>
    </div>
    <div class="fl-matrix">
      <div class="fl-group">
        <div class="fl-group-hd"><span class="fl-group-cat">Токарная группа</span><span class="fl-group-cnt">8 ед.</span></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Карусельный ДЛА 2500</div><div class="fl-item-spec">Ø до 2500 мм<span class="fl-item-qty">×2</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Токарный ЧПУ DMTG CKD6163</div><div class="fl-item-spec">Ø до 630 мм<span class="fl-item-qty">×4</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Токарно-винторезный 16К20</div><div class="fl-item-spec">Ø до 400 мм<span class="fl-item-qty">×2</span></div></div></div>
      </div>
      <div class="fl-group">
        <div class="fl-group-hd"><span class="fl-group-cat">Фрезерно-расточная</span><span class="fl-group-cnt">5 ед.</span></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Горизонт.-расточной 2А614Ф1</div><div class="fl-item-spec">стол 1600×1000 мм<span class="fl-item-qty">×2</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Вертикальный ОЦ DMTG VMC850</div><div class="fl-item-spec">850×500 мм<span class="fl-item-qty">×3</span></div></div></div>
      </div>
      <div class="fl-group">
        <div class="fl-group-hd"><span class="fl-group-cat">Прессовое оборудование</span><span class="fl-group-cnt">3 ед.</span></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Гидравлический пресс П6330</div><div class="fl-item-spec">усилие 2500 т.с.<span class="fl-item-qty">×1</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Гидравлический пресс П6322</div><div class="fl-item-spec">усилие 630 т.с.<span class="fl-item-qty">×2</span></div></div></div>
      </div>
      <div class="fl-group">
        <div class="fl-group-hd"><span class="fl-group-cat">Сварочные посты</span><span class="fl-group-cnt">12 постов</span></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Сварочный автомат АДФ-1200</div><div class="fl-item-spec">до 1200А · МА-169Б<span class="fl-item-qty">×4</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Орбитальная сварка УОНИИ</div><div class="fl-item-spec">Ø 57–219 мм<span class="fl-item-qty">×2</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Посты НАКС ESAB Aristo</div><div class="fl-item-spec">ручная дуговая<span class="fl-item-qty">×6</span></div></div></div>
      </div>
      <div class="fl-group">
        <div class="fl-group-hd"><span class="fl-group-cat">Термические печи</span><span class="fl-group-cnt">7 ед.</span></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Камерная печь SNOL 900</div><div class="fl-item-spec">до 900°C · объём 2 м³<span class="fl-item-qty">×3</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Печь с кипящим слоем КС-400</div><div class="fl-item-spec">до 700°C<span class="fl-item-qty">×1</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Индукционный нагрев ВЧИ-25</div><div class="fl-item-spec">до 500°C<span class="fl-item-qty">×1</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Закалочный бак масло/вода</div><div class="fl-item-spec">V = 4 м³<span class="fl-item-qty">×2</span></div></div></div>
      </div>
      <div class="fl-group">
        <div class="fl-group-hd"><span class="fl-group-cat">Контроль и испытания</span><span class="fl-group-cnt">8 ед.</span></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">УЗ-дефектоскоп УД3-71</div><div class="fl-item-spec">стенка ≥ 3 мм<span class="fl-item-qty">×3</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Рентгенаппарат РАП-150/300</div><div class="fl-item-spec">Ø до 400 мм<span class="fl-item-qty">×1</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Твердомер ТЭМП-4</div><div class="fl-item-spec">HB 20–450<span class="fl-item-qty">×2</span></div></div></div>
        <div class="fl-item"><div class="fl-item-bar"></div><div class="fl-item-body"><div class="fl-item-name">Стенд гидроиспытаний ГИ-40</div><div class="fl-item-spec">до 40 МПа<span class="fl-item-qty">×2</span></div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════ §GALLERY — ЦЕХ В ОБЪЕКТИВЕ ═══════════════════════ -->
<section id="gallery">
  <div class="gal-hd">
    <div class="gal-tag">ФОТОДОКУМЕНТАЦИЯ</div>
    <span class="sec-hd-en">PRODUCTION IN LENS</span>
  </div>
  <div class="gal-intro">
    <div class="gal-headline">ЦЕХ<br>В ОБЪЕКТИВЕ</div>
    <div class="gal-desc">Съёмка в рабочую смену без постановки. Станки, люди, металл — всё как есть. Так работает завод каждый день.</div>
  </div>
  <div class="gal-stage" id="galStage">
    <div class="gal-track" id="galTrack">
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.jpg' ) ); ?>" alt="Цех механообработки" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 01 / 07</span>
            <span class="gal-zone">ЦЕХ МЕХАНООБРАБОТКИ</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Токарный парк ЧПУ</div>
            <div class="gal-info-detail">КАРУСЕЛЬНЫЙ ДЛА 2500 · ТОЧНОСТЬ ±0.01 ММ</div>
          </div>
        </div>
        <div class="gal-caption">УЧАСТОК ПРЕЦИЗИОННОЙ ОБРАБОТКИ · 8 ЕДИНИЦ ОБОРУДОВАНИЯ</div>
      </div>
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-2.jpg' ) ); ?>" alt="Сварочный участок" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 02 / 07</span>
            <span class="gal-zone">СВАРОЧНЫЙ УЧАСТОК</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Автоматическая дуговая сварка</div>
            <div class="gal-info-detail">АДФ-1200 · ДО 1200А · НАКС II УРОВЕНЬ</div>
          </div>
        </div>
        <div class="gal-caption">12 СВАРОЧНЫХ ПОСТОВ · НЕРЖАВЕЮЩАЯ И ЛЕГИРОВАННАЯ СТАЛЬ</div>
      </div>
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-3.jpg' ) ); ?>" alt="Склад металла" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 03 / 07</span>
            <span class="gal-zone">СКЛАД МЕТАЛЛА</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Склад проката и заготовок</div>
            <div class="gal-info-detail">680 М² · 8 МАРОК СТАЛИ · ГОСТ 8732-78</div>
          </div>
        </div>
        <div class="gal-caption">ВХОДНОЙ КОНТРОЛЬ КАЖДОЙ ПАРТИИ МАТЕРИАЛА</div>
      </div>
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-4.jpg' ) ); ?>" alt="Зона технического контроля" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 04 / 07</span>
            <span class="gal-zone">ОТДЕЛ КОНТРОЛЯ</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Зона технического контроля</div>
            <div class="gal-info-detail">УЗК · РК · МК · ГИДРОИСПЫТАНИЕ ДО 40 МПА</div>
          </div>
        </div>
        <div class="gal-caption">6 МЕТОДОВ НЕРАЗРУШАЮЩЕГО КОНТРОЛЯ · НАКС</div>
      </div>
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-5.jpg' ) ); ?>" alt="Производственный цех" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 05 / 07</span>
            <span class="gal-zone">ПРОИЗВОДСТВЕННЫЙ ЦЕХ</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Рабочая смена</div>
            <div class="gal-info-detail">ПРОИЗВОДСТВО · ЕЖЕДНЕВНЫЙ РЕЖИМ</div>
          </div>
        </div>
      </div>
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-6.jpg' ) ); ?>" alt="Производственный цех" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 06 / 07</span>
            <span class="gal-zone">ПРОИЗВОДСТВЕННЫЙ ЦЕХ</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Металлообработка</div>
            <div class="gal-info-detail">ТОЧНОСТЬ · ОПЫТ · РЕЗУЛЬТАТ</div>
          </div>
        </div>
      </div>
      <div class="gal-item">
        <div class="gal-img-wrap">
          <img class="gal-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-7.jpg' ) ); ?>" alt="Производственный цех" loading="lazy" draggable="false">
          <div class="gal-ov"></div>
          <div class="gal-crn tl"></div><div class="gal-crn tr"></div>
          <div class="gal-crn bl"></div><div class="gal-crn br"></div>
          <div class="gal-meta-top">
            <span class="gal-num">ФОТО 07 / 07</span>
            <span class="gal-zone">ПРОИЗВОДСТВЕННЫЙ ЦЕХ</span>
          </div>
          <div class="gal-info">
            <div class="gal-info-name">Завод в работе</div>
            <div class="gal-info-detail">ПРОМЫШЛЕННАЯ ЭНЕРГЕТИКА · ЧЕЛЯБИНСК</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="gal-footer">
    <div class="gal-dots">
      <div class="gal-dot gal-act"></div>
      <div class="gal-dot"></div>
      <div class="gal-dot"></div>
      <div class="gal-dot"></div>
      <div class="gal-dot"></div>
      <div class="gal-dot"></div>
      <div class="gal-dot"></div>
    </div>
    <div class="gal-arrows">
      <button type="button" class="gal-arrow" id="galPrev" aria-label="Предыдущее фото">←</button>
      <button type="button" class="gal-arrow" id="galNext" aria-label="Следующее фото">→</button>
    </div>
    <div class="gal-hint">← → &nbsp; ПЕРЕТАЩИТЕ ДЛЯ НАВИГАЦИИ</div>
  </div>
</section>

<!-- ═══════════════════════ §07 PORTAL ═══════════════════════ -->
<section id="portal">
  <div class="portal-left">
    <div class="portal-hd">
      <div class="portal-tag">КОМПЛЕКТ СОПРОВОДИТЕЛЬНОЙ ДОКУМЕНТАЦИИ</div>
      <span class="sec-hd-en">НА КАЖДОЕ ИЗДЕЛИЕ</span>
    </div>
    <div class="portal-docs">
      <div class="pdoc">
        <div class="pdoc-code">ПП</div>
        <div><div class="pdoc-name">Паспорт изделия</div><div class="pdoc-desc">Индивидуальный паспорт с данными плавки, результатами НК, термообработки и гидроиспытаний. На каждое изделие без исключения.</div></div>
        <div class="pdoc-badge">ГОСТ 2.601<br>100% изделий</div>
      </div>
      <div class="pdoc">
        <div class="pdoc-code">СК</div>
        <div><div class="pdoc-name">Сертификат качества</div><div class="pdoc-desc">Сертификат на партию продукции — химсостав, механические свойства, применённые нормативные документы. ТР ТС 032.</div></div>
        <div class="pdoc-badge">ТР ТС 032<br>На партию</div>
      </div>
      <div class="pdoc">
        <div class="pdoc-code">НК</div>
        <div><div class="pdoc-name">Протоколы НК</div><div class="pdoc-desc">Протоколы неразрушающего контроля по применённым методам (ВК/МК/УЗК/РК/ПВК) с заключениями специалистов II–III уровня.</div></div>
        <div class="pdoc-badge">НП-045-18<br>НАКС II–III</div>
      </div>
      <div class="pdoc">
        <div class="pdoc-code">ТО</div>
        <div><div class="pdoc-name">Трассировочность плавки</div><div class="pdoc-desc">Сквозная прослеживаемость от плавки заготовки до готового изделия — номер плавки, марка стали, результаты входного контроля.</div></div>
        <div class="pdoc-badge">ГОСТ 7566<br>По плавке</div>
      </div>
      <div class="pdoc">
        <div class="pdoc-code">РК</div>
        <div><div class="pdoc-name">Радиографический контроль</div><div class="pdoc-desc">Рентген сварных швов по ГОСТ 7512, 100% сварных соединений. Заключение специалиста РК II уровня. Архив снимков 5 лет.</div></div>
        <div class="pdoc-badge">ГОСТ 7512<br>100% швов</div>
      </div>
      <div class="pdoc">
        <div class="pdoc-code">ГИ</div>
        <div><div class="pdoc-name">Протокол гидроиспытания</div><div class="pdoc-desc">Испытательное давление 1.5×PN, выдержка 10 минут. Результат — отсутствие течей и деформаций. Протокол с временно́й меткой.</div></div>
        <div class="pdoc-badge">РД 26-15-88<br>1.5 × PN</div>
      </div>
    </div>
  </div>
</section>

</main>

<!-- FOOTER ZONE — sticky CTA + footer slides over -->
<div class="footer-zone" data-od-id="path-4">
<?php get_footer(); ?>
