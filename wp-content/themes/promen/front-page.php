<?php
/**
 * Главная страница — 1:1 из html/hero-variant-d.html (Open Design, 2026-07-22).
 * Хром (nav/drawer/strip) — header.php, форма s10 и футер — footer.php.
 * Скрипты страницы — assets/js/front.js (+ GSAP/ScrollTrigger вендором),
 * стили — assets/css/front.css. Подключение — functions.php (is_front_page()).
 */
$promen_catalog_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
// S9 «Нормативная база» — карточки стандартов ведут в полный реестр документов.
$promen_nb_url      = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : home_url( '/normativnaya-baza/' );

add_filter( 'promen_footer_idx', fn () => 'ПЭ-00.FTR / REV.1' );
add_filter( 'promen_s10_eyebrow_num', fn () => '10' );

get_header();
?>
<nav class="sidenav" aria-label="Навигация по разделам">
  <a class="sidenav-item" href="#hero"><span class="sidenav-dot"></span><span class="sidenav-label">ГЛАВНАЯ</span></a>
  <a class="sidenav-item" href="#industries"><span class="sidenav-dot"></span><span class="sidenav-label">ОТРАСЛИ</span></a>
  <a class="sidenav-item" href="#cycle"><span class="sidenav-dot"></span><span class="sidenav-label">ЦИКЛ</span></a>
  <a class="sidenav-item" href="#directions"><span class="sidenav-dot"></span><span class="sidenav-label">ПРОДУКЦИЯ</span></a>
  <a class="sidenav-item" href="#geography"><span class="sidenav-dot"></span><span class="sidenav-label">КАРТА</span></a>
  <a class="sidenav-item" href="#passport"><span class="sidenav-dot"></span><span class="sidenav-label">ЗАВОД</span></a>
  <a class="sidenav-item" href="#drawings"><span class="sidenav-dot"></span><span class="sidenav-label">ПРОИЗВОДСТВО</span></a>
  <a class="sidenav-item" href="#parameters"><span class="sidenav-dot"></span><span class="sidenav-label">ПАРАМЕТРЫ</span></a>
  <a class="sidenav-item" href="#quality"><span class="sidenav-dot"></span><span class="sidenav-label">КОНТРОЛЬ</span></a>
  <a class="sidenav-item" href="#documents"><span class="sidenav-dot"></span><span class="sidenav-label">ДОКУМЕНТЫ</span></a>
  <a class="sidenav-item" href="#request"><span class="sidenav-dot"></span><span class="sidenav-label">ЗАПРОС</span></a>
</nav>

<!-- HERO -->
<div class="hero" id="hero">

  <!-- LEFT: Content block -->
  <div class="left">

    <div class="eyebrow">
      <span class="ey-num">01</span>
      <span class="ey-label">АЭС · ТЭС</span>
    </div>

    <!-- Headline — категорийная формула: что делает компания -->
    <h1 class="h1">
      ПРОИЗВОДСТВО<br>
      <span class="h1-l2">ДСЕ<br>ТРУБОПРОВОДОВ</span>
    </h1>

    <!-- Tagline — "FUNCTIONAL. DIGITAL. UNAPOLOGETIC." format -->
    <p class="tagline">
      ТОЧНО<span class="sep">.</span>
      НАДЁЖНО<span class="sep">.</span>
      ПО ЧЕРТЕЖУ.
    </p>

    <div class="rule" data-label="ПЭ-00.001 / ТР ТС 032 / СЕРИЯ RU 0418908"></div>

    <p class="body">
      Производство деталей и сборочных единиц трубопроводов
      для объектов атомной и тепловой энергетики. Изготовление по ГОСТ, ОСТ, СТО, ТУ
      и конструкторской документации заказчика.
    </p>

    <!-- CTAs — "EXPLORE WORK ↗  [VIEW MANIFESTO]" structure -->
    <div class="ctas">
      <a href="#" class="btn-fill" onclick="openRequestModal('calc');return false;">Запросить расчёт <span class="ic">↗</span></a>
      <a href="<?php echo esc_url( $promen_catalog_url ); ?>" class="btn-ghost">
        <span class="bk">[</span>&nbsp;Каталог продукции&nbsp;<span class="bk">]</span>
      </a>
    </div>

    <!-- Bottom technical data row -->
    <div class="data-row" aria-hidden="true">
      <span>ТР ТС 032</span>
      <span>ГОСТ 5520</span>
      <span>PN 250 / 25 МПа</span>
    </div>
  </div>

  <!-- RIGHT: Visual element -->
  <div class="right">
    <div class="right-glow"></div>

    <!-- Structural cross-hair lines -->
    <div class="cross-h"></div>
    <div class="cross-v"></div>

    <!-- Frame corners -->
    <div class="rf">
      <div class="c c-tl"></div>
      <div class="c c-tr"></div>
      <div class="c c-bl"></div>
      <div class="c c-br"></div>
    </div>

    <!-- Pixel cloud — brand blues as "color dust". Every point below is placed
         (or mirrored) so the whole cloud's vertical extent is exactly [40,560] —
         symmetric around y=300, the same point the logo sits on (its own center,
         via .pe-wrap's flex-centering in .right). No corrective transform needed:
         since the SVG's default xMidYMid-meet scaling always centers y=300 of the
         viewBox on .right's center, this symmetry keeps the logo mid-way between
         the squares on every device/resolution, not just this one. -->
    <svg class="pixel-cloud" viewBox="0 0 600 600" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
      <!-- Top scatter -->
      <rect x="50" y="55" width="6" height="6" fill="#1E3D5C" opacity=".9" style="animation:pf1 3.2s ease-in-out infinite"></rect>
      <rect x="68" y="40" width="4" height="4" fill="#6D8CA6" opacity=".7" style="animation:pf2 4.0s ease-in-out infinite"></rect>
      <rect x="90" y="72" width="10" height="10" fill="#1E3D5C" opacity=".6" style="animation:pf3 2.8s ease-in-out infinite"></rect>
      <rect x="130" y="48" width="4" height="4" fill="#A9B7C6" opacity=".4" style="animation:pf4 3.5s ease-in-out infinite"></rect>
      <rect x="160" y="65" width="6" height="6" fill="#6D8CA6" opacity=".35" style="animation:pf1 4.4s ease-in-out .3s infinite"></rect>
      <rect x="510" y="55" width="6" height="6" fill="#1E3D5C" opacity=".8" style="animation:pf2 3.1s ease-in-out .5s infinite"></rect>
      <rect x="535" y="40" width="4" height="4" fill="#6D8CA6" opacity=".6" style="animation:pf4 4.2s ease-in-out .2s infinite"></rect>
      <rect x="556" y="75" width="10" height="10" fill="#1E3D5C" opacity=".5" style="animation:pf1 2.9s ease-in-out .7s infinite"></rect>
      <rect x="475" y="50" width="4" height="4" fill="#A9B7C6" opacity=".3" style="animation:pf3 3.8s ease-in-out .1s infinite"></rect>

      <!-- Bottom scatter — exact mirror of the top scatter (y' = 600 − y − h) -->
      <rect x="50" y="539" width="6" height="6" fill="#1E3D5C" opacity=".8" style="animation:pf3 3.4s ease-in-out .6s infinite"></rect>
      <rect x="68" y="556" width="4" height="4" fill="#6D8CA6" opacity=".6" style="animation:pf1 4.3s ease-in-out .1s infinite"></rect>
      <rect x="90" y="518" width="10" height="10" fill="#1E3D5C" opacity=".55" style="animation:pf4 3.0s ease-in-out .4s infinite"></rect>
      <rect x="130" y="548" width="4" height="4" fill="#A9B7C6" opacity=".35" style="animation:pf2 3.7s ease-in-out .9s infinite"></rect>
      <rect x="160" y="529" width="6" height="6" fill="#6D8CA6" opacity=".3" style="animation:pf3 4.6s ease-in-out .2s infinite"></rect>
      <rect x="510" y="539" width="6" height="6" fill="#1E3D5C" opacity=".75" style="animation:pf1 3.3s ease-in-out .7s infinite"></rect>
      <rect x="535" y="556" width="4" height="4" fill="#6D8CA6" opacity=".55" style="animation:pf3 4.5s ease-in-out .3s infinite"></rect>
      <rect x="556" y="515" width="10" height="10" fill="#1E3D5C" opacity=".45" style="animation:pf2 3.1s ease-in-out .8s infinite"></rect>
      <rect x="475" y="546" width="4" height="4" fill="#A9B7C6" opacity=".28" style="animation:pf4 4.1s ease-in-out .5s infinite"></rect>

      <!-- Left side column — recentred so its 5 rows sit symmetric around y=300 -->
      <rect x="38" y="210" width="4" height="4" fill="#6D8CA6" opacity=".5" style="animation:pf2 3.0s ease-in-out .4s infinite"></rect>
      <rect x="56" y="255" width="8" height="8" fill="#1E3D5C" opacity=".8" style="animation:pf4 3.6s ease-in-out .2s infinite"></rect>
      <rect x="40" y="300" width="4" height="4" fill="#6D8CA6" opacity=".45" style="animation:pf1 4.1s ease-in-out .8s infinite"></rect>
      <rect x="62" y="345" width="6" height="6" fill="#1E3D5C" opacity=".6" style="animation:pf3 3.3s ease-in-out .5s infinite"></rect>
      <rect x="44" y="390" width="4" height="4" fill="#A9B7C6" opacity=".3" style="animation:pf2 4.5s ease-in-out .1s infinite"></rect>
      <!-- Right side column — mirrors the left column's y-rows -->
      <rect x="546" y="210" width="4" height="4" fill="#6D8CA6" opacity=".55" style="animation:pf4 3.0s ease-in-out .6s infinite"></rect>
      <rect x="530" y="255" width="8" height="8" fill="#1E3D5C" opacity=".9" style="animation:pf1 2.7s ease-in-out .3s infinite"></rect>
      <rect x="548" y="300" width="4" height="4" fill="#A9B7C6" opacity=".4" style="animation:pf3 3.9s ease-in-out .9s infinite"></rect>
      <rect x="534" y="345" width="6" height="6" fill="#1E3D5C" opacity=".65" style="animation:pf2 3.4s ease-in-out .4s infinite"></rect>
      <rect x="550" y="390" width="4" height="4" fill="#6D8CA6" opacity=".45" style="animation:pf1 4.2s ease-in-out .7s infinite"></rect>

      <!-- Large "chunk" pixels — top pair + exact mirror as the bottom pair -->
      <rect x="68" y="80" width="14" height="14" fill="#1E3D5C" opacity=".5" style="animation:pf2 5.2s ease-in-out infinite"></rect>
      <rect x="510" y="80" width="14" height="14" fill="#1E3D5C" opacity=".6" style="animation:pf4 5.8s ease-in-out .5s infinite"></rect>
      <rect x="68" y="506" width="14" height="14" fill="#1E3D5C" opacity=".45" style="animation:pf1 4.9s ease-in-out 1s infinite"></rect>
      <rect x="510" y="506" width="14" height="14" fill="#6D8CA6" opacity=".4" style="animation:pf3 5.5s ease-in-out .3s infinite"></rect>

      <!-- Mid field sparse pixels — top pair + exact mirror -->
      <rect x="120" y="100" width="4" height="4" fill="#6D8CA6" opacity=".3" style="animation:pf4 4.3s ease-in-out .7s infinite"></rect>
      <rect x="460" y="100" width="4" height="4" fill="#6D8CA6" opacity=".25" style="animation:pf2 3.7s ease-in-out .2s infinite"></rect>
      <rect x="120" y="496" width="4" height="4" fill="#1E3D5C" opacity=".35" style="animation:pf1 3.5s ease-in-out .9s infinite"></rect>
      <rect x="460" y="496" width="4" height="4" fill="#1E3D5C" opacity=".3" style="animation:pf3 4.0s ease-in-out .1s infinite"></rect>

      <!-- Horizontal "glitch lines" — top pair + exact mirror as the bottom pair -->
      <rect x="52" y="185" width="28" height="2" fill="#6D8CA6" opacity=".15" style="animation:pf2 6s ease-in-out .8s infinite"></rect>
      <rect x="520" y="195" width="22" height="2" fill="#6D8CA6" opacity=".12" style="animation:pf4 5.5s ease-in-out .4s infinite"></rect>
      <rect x="52" y="413" width="28" height="2" fill="#1E3D5C" opacity=".2" style="animation:pf1 6.2s ease-in-out .2s infinite"></rect>
      <rect x="520" y="403" width="22" height="2" fill="#1E3D5C" opacity=".18" style="animation:pf3 5.8s ease-in-out 1.1s infinite"></rect>
    </svg>

    <!-- PE SIGN — main visual element with CSS glitch -->
    <div class="pe-wrap">
      <img class="pe-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/PE_sign_blue.png' ) ); ?>" alt="Промышленная Энергетика — знак">
      <div class="pe-g1"></div>
      <div class="pe-g2"></div>
    </div>

    <!-- HUD: "РЕНДЕР 01" — matches reference "RENDERING 01" -->
    <div class="hud hud-render">РЕНДЕР 01</div>

    <!-- HUD: Coordinates — Chelyabinsk (real) -->
    <div class="hud hud-coords">
      <p>X_<span>54.7431</span></p>
      <p>Y_<span>61.2344</span></p>
      <p>Z_<span>+320.0</span></p>
    </div>

    <!-- HUD: Top-right info -->
    <div class="hud hud-info">
      <p>ТЭС / АЭС</p>
      <p>PN 250 / 25 МПа</p>
    </div>

    <!-- HUD: Scene label — matches "//SCN_01" -->
    <div class="hud hud-scene">//ПЭ_01</div>
  </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     SFK · ОТРАСЛЕВЫЕ ЗАДАЧИ — АЭС / ТЭС
════════════════════════════════════════════════════════════ -->
<section class="sfk" id="industries" data-od-id="sfk-industries">

  <!-- Шапка секции -->
  <div class="sfk-head">
    <div>
      <div class="sfk-eyebrow">
        <span class="sfk-eye-num">→</span>
        <span class="sfk-eye-label">ОТРАСЛЕВЫЕ ЗАДАЧИ</span>
      </div>
      <h2 class="sfk-h2">Детали трубопроводов под<br>требования АЭС и ТЭС</h2>
    </div>
    <p class="sfk-lead">Исполнение изделия определяется не только типоразмером, но и условиями эксплуатации: средой, давлением, температурой, маркой стали, нормативным документом и требованиями к контролю.</p>
  </div>

  <!-- Карточки -->
  <div class="sfk-cards">

    <!-- АЭС -->
    <div class="sfk-card">
      <div class="sfk-card-hd">
        <div class="sfk-sector">АЭС</div>
        <div class="sfk-sector-full">Атомная энергетика</div>
      </div>
      <div class="sfk-card-bd">
        <p class="sfk-card-desc">Объекты атомной энергетики предъявляют наивысшие требования к прослеживаемости материала и полноте сопроводительной документации. Каждая деталь сопровождается паспортом изделия, сертификатом на материал и протоколами контроля.</p>
        <div class="sfk-params">
          <div class="sfk-param">
            <span class="sfk-pk">НОРМАТИВЫ</span>
            <span class="sfk-pv">ПНАЭ · ОСТ 24 · СТО · КД заказчика</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">ДОКУМЕНТАЦИЯ</span>
            <span class="sfk-pv">Паспорт изделия · Серт. 3.1 · Протоколы НК</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">КОНТРОЛЬ</span>
            <span class="sfk-pv">ВИК · УЗК · РК · Гидроиспытания</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">ПРОСЛЕЖИВАЕМОСТЬ</span>
            <span class="sfk-pv">Плавка · Серт. материала · Паспорт</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">МАТЕРИАЛ</span>
            <span class="sfk-pv">12Х1МФ · 08Х18Н10Т · по КД</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ТЭС -->
    <div class="sfk-card">
      <div class="sfk-card-hd">
        <div class="sfk-sector">ТЭС</div>
        <div class="sfk-sector-full">Тепловая энергетика</div>
      </div>
      <div class="sfk-card-bd">
        <p class="sfk-card-desc">Тепловые электростанции требуют деталей под высокотемпературные паровые и водяные среды. Изготовление единичных и серийных партий, нестандартных деталей по чертежам заказчика, ремонтных вставок и узлов трубопровода.</p>
        <div class="sfk-params">
          <div class="sfk-param">
            <span class="sfk-pk">НОРМАТИВЫ</span>
            <span class="sfk-pv">ГОСТ · ОСТ 108 · СТО ЦКТИ · ТУ · КД</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">СРЕДА / УСЛОВИЯ</span>
            <span class="sfk-pv">Пар · Вода · T до 570°C · PN до 250</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">ЗАДАЧИ</span>
            <span class="sfk-pv">Ремонт · СМР · Модернизация · Новое строительство</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">ПАРТИИ</span>
            <span class="sfk-pv">Серийные и единичные · По чертежу</span>
          </div>
          <div class="sfk-param">
            <span class="sfk-pk">МАТЕРИАЛ</span>
            <span class="sfk-pv">09Г2С · Ст20 · 12Х1МФ · 15Х5М</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Технический вывод -->
  <div class="sfk-conclusion-wrap">
    <p class="sfk-conclusion">Для каждого объекта подбираем исполнение по DN, PN, температуре, среде, материалу, нормативной документации и комплекту сопроводительных документов.</p>
  </div>

  <!-- Trust strip — ключевые нормативы/допуски сразу на виду -->
  <div class="trust-strip">
    <span class="ts-label">РАЗРЕШИТЕЛЬНАЯ ДОКУМЕНТАЦИЯ</span>
    <div class="ts-items">
      <span class="ts-item">ПНАЭ Г-7</span>
      <span class="ts-sep">·</span>
      <span class="ts-item">РТН</span>
      <span class="ts-sep">·</span>
      <span class="ts-item">ОСТ 108</span>
      <span class="ts-sep">·</span>
      <span class="ts-item">ИСО 9001</span>
      <span class="ts-sep">·</span>
      <span class="ts-item">СЕРТИФИКАТ 3.1</span>
      <span class="ts-sep">·</span>
      <span class="ts-item">ВИК · УЗК · РК</span>
    </div>
  </div>

</section>

<!-- ═══════════════════════════════════════════════════════════
     SECTION 2 · ОТ ЧЕРТЕЖА ДО ПОСТАВКИ
════════════════════════════════════════════════════════════ -->
<section class="s2" id="cycle">

  <header class="s2-head">
    <p class="s2-kicker">Комплексный цикл</p>
    <h2 class="s2-h2">Берём на себя путь от чертежа<br>до готовой поставки</h2>
    <p class="s2-lead">От анализа технического задания и подбора исполнения до изготовления, контроля, комплектации документации и отгрузки продукции на объект.</p>
  </header>

  <div class="s2-acc">

    <!-- ── Row 01: Анализ задачи ── -->
    <div class="s2-row active" data-stage="1">
      <div class="sr-num">01</div>
      <div class="sr-content">
        <div class="sr-bar">
          <div class="sr-bar-text">
            <span class="sr-title">Анализ задачи</span>
            <span class="sr-meta">чертёж · спецификация · ТЗ</span>
          </div>
          <span class="sr-plus">+</span>
        </div>
        <div class="sr-panel">
          <div class="sr-left">Изучаем чертёж, спецификацию, параметры среды, давление, температуру, материал и требования объекта.</div>
          <div class="sr-sep"></div>
          <div class="sr-right">
            <div class="s2-param"><span class="s2-param-k">Документ</span><span class="s2-param-v">чертёж / ТЗ / ОСТ / ТУ</span></div>
            <div class="s2-param"><span class="s2-param-k">DN</span><span class="s2-param-v">15 — 1400 мм</span></div>
            <div class="s2-param"><span class="s2-param-k">PN</span><span class="s2-param-v">до 400 кгс/см²</span></div>
            <div class="s2-param"><span class="s2-param-k">Температура</span><span class="s2-param-v">−70 … +600 °C</span></div>
            <div class="s2-param"><span class="s2-param-k">Среда</span><span class="s2-param-v">нефть · газ · пар · вода</span></div>
            <div class="s2-param"><span class="s2-param-k">Стандарт</span><span class="s2-param-v">ГОСТ · ОСТ · СТО · ТУ · DIN · ANSI</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 02: Подбор исполнения ── -->
    <div class="s2-row" data-stage="2">
      <div class="sr-num">02</div>
      <div class="sr-content">
        <div class="sr-bar">
          <div class="sr-bar-text">
            <span class="sr-title">Подбор исполнения</span>
            <span class="sr-meta">материал · стандарт · тип</span>
          </div>
          <span class="sr-plus">+</span>
        </div>
        <div class="sr-panel">
          <div class="sr-left">Определяем стандарт, марку стали, тип детали, способ изготовления и требования к контролю.</div>
          <div class="sr-sep"></div>
          <div class="sr-right">
            <div class="s2-param"><span class="s2-param-k">Стандарт</span><span class="s2-param-v">ГОСТ 17375</span></div>
            <div class="s2-param"><span class="s2-param-k">Материал</span><span class="s2-param-v">Сталь 09Г2С</span></div>
            <div class="s2-param"><span class="s2-param-k">Тип</span><span class="s2-param-v">штампосварной</span></div>
            <div class="s2-param"><span class="s2-param-k">Давление</span><span class="s2-param-v">PN 25 · 2,5 МПа</span></div>
            <div class="s2-param"><span class="s2-param-k">Темп.</span><span class="s2-param-v">до +300 °C</span></div>
            <div class="s2-param"><span class="s2-param-k">Контроль</span><span class="s2-param-v">УЗК + ВИК</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 03: Производство и комплектация ── -->
    <div class="s2-row" data-stage="3">
      <div class="sr-num">03</div>
      <div class="sr-content">
        <div class="sr-bar">
          <div class="sr-bar-text">
            <span class="sr-title">Производство и комплектация</span>
            <span class="sr-meta">изготовление · подбор · фасонные детали</span>
          </div>
          <span class="sr-plus">+</span>
        </div>
        <div class="sr-panel">
          <div class="sr-left">Изготавливаем или комплектуем отводы, тройники, переходы, фланцы, трубы и арматуру под параметры объекта.</div>
          <div class="sr-sep"></div>
          <div class="sr-right sr-right-fill">
            <div class="s2-parts">
              <div class="s2-part">
                <span class="s2-part-name">Отвод 90°</span>
                <span class="s2-part-gost">ГОСТ 17375</span>
              </div>
              <div class="s2-part">
                <span class="s2-part-name">Тройник</span>
                <span class="s2-part-gost">ГОСТ 17376</span>
              </div>
              <div class="s2-part">
                <span class="s2-part-name">Фланец</span>
                <span class="s2-part-gost">ГОСТ 33259</span>
              </div>
              <div class="s2-part">
                <span class="s2-part-name">Переход</span>
                <span class="s2-part-gost">ГОСТ 17378</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Row 04: Контроль и поставка ── -->
    <div class="s2-row" data-stage="4">
      <div class="sr-num">04</div>
      <div class="sr-content">
        <div class="sr-bar">
          <div class="sr-bar-text">
            <span class="sr-title">Контроль и поставка</span>
            <span class="sr-meta">ОТК · документация · отгрузка</span>
          </div>
          <span class="sr-plus">+</span>
        </div>
        <div class="sr-panel">
          <div class="sr-left">Проверяем геометрию, комплектность, маркировку, сопроводительные документы и организуем отгрузку на объект.</div>
          <div class="sr-sep"></div>
          <div class="sr-right">
            <ul class="s2-checklist">
              <li><span class="s2-chk">✓</span>Геометрия изделий</li>
              <li><span class="s2-chk">✓</span>Маркировка плавки</li>
              <li><span class="s2-chk">✓</span>Сварные швы</li>
              <li><span class="s2-chk">✓</span>Комплектность</li>
              <li><span class="s2-chk">✓</span>Паспорт · Сертификат · Протоколы</li>
            </ul>
            <div class="s2-status">Статус · Готово к отгрузке</div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- .s2-acc -->

  <!-- Trust callout -->
  <div class="s2-trust">
    <div class="trust-mark">// ИНЖЕНЕРНАЯ ЗАМЕТКА</div>
    <p class="trust-text">Каждая поставка рассчитывается под реальные параметры трубопровода: <strong>давление, температуру, рабочую среду, материал, стандарт</strong> и условия эксплуатации.</p>
  </div>

</section>

<!-- ════════════════════════════════════════════════════════════
     SECTION 3 · НАПРАВЛЕНИЯ И РЕШЕНИЯ
════════════════════════════════════════════════════════════ -->
<section class="s3" id="directions">

  <header class="s3-head">
    <p class="s3-kicker">Производственные направления</p>
    <h2 class="s3-h2">Изделия и комплектация<br>по техническому заданию</h2>
    <p class="s3-lead">Производим и комплектуем трубопроводные изделия под проектные требования — от стандартных деталей до нестандартных элементов по чертежам заказчика, с учётом давления, температуры, среды, материала и нормативной базы.</p>
  </header>

  <div class="s3-grid">

    <!-- ── D1: Dominant block · ТЭС / АЭС ────────────────── -->
    <div class="s3-card s3-d1">

      <!-- Dark visual area -->
      <div class="s3-visual">

        <!-- Pipe cross-section schematic -->
        <svg class="s3-schema" viewBox="0 0 280 220" width="260" height="204" style="bottom:0;right:-16px;" aria-hidden="true" fill="none">
          <!-- Outer pipe circle (OD) -->
          <circle cx="138" cy="118" r="76" fill="rgba(30,61,92,.45)" stroke="rgba(169,183,198,.3)" stroke-width="1.2" stroke-dasharray="5 4"></circle>
          <!-- Inner bore circle -->
          <circle cx="138" cy="118" r="59" fill="rgba(15,42,68,.7)" stroke="rgba(169,183,198,.6)" stroke-width="1.5"></circle>
          <!-- Center crosshairs -->
          <line x1="40" y1="118" x2="236" y2="118" stroke="rgba(109,140,166,.35)" stroke-width=".7" stroke-dasharray="9 4"></line>
          <line x1="138" y1="28" x2="138" y2="208" stroke="rgba(109,140,166,.35)" stroke-width=".7" stroke-dasharray="9 4"></line>
          <circle cx="138" cy="118" r="2.5" fill="rgba(109,140,166,.4)"></circle>
          <!-- DN dimension (bore radius) -->
          <line x1="138" y1="22" x2="197" y2="22" stroke="rgba(109,140,166,.5)" stroke-width=".8"></line>
          <line x1="138" y1="17" x2="138" y2="27" stroke="rgba(109,140,166,.5)" stroke-width=".8"></line>
          <line x1="197" y1="17" x2="197" y2="27" stroke="rgba(109,140,166,.5)" stroke-width=".8"></line>
          <text x="170" y="17" text-anchor="middle" font-family="DINPro,monospace" font-size="9" fill="rgba(169,183,198,.55)" letter-spacing=".5">DN</text>
          <!-- S callout (wall thickness) -->
          <line x1="222" y1="48" x2="204" y2="66" stroke="rgba(109,140,166,.4)" stroke-width=".7"></line>
          <text x="227" y="46" font-family="DINPro,monospace" font-size="8.5" fill="rgba(169,183,198,.5)" letter-spacing=".4">S=14</text>
          <!-- PN inset -->
          <rect x="106" y="97" width="64" height="42" fill="rgba(109,140,166,.1)" stroke="rgba(109,140,166,.2)" stroke-width=".7"></rect>
          <text x="138" y="114" text-anchor="middle" font-family="DINPro,monospace" font-size="7.5" fill="rgba(109,140,166,.65)" letter-spacing="1">PN 25</text>
          <text x="138" y="130" text-anchor="middle" font-family="DINProCond,monospace" font-size="15" fill="rgba(255,255,255,.4)" font-weight="900" letter-spacing=".02em">09Г2С</text>
        </svg>

        <!-- Top: number + tags -->
        <div>
          <div class="s3-num-inv">01</div>
          <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;">
            <span class="s3-tag-inv">ТЭС / АЭС</span>
            <span class="s3-tag-inv">ГОСТ / ОСТ</span>
            <span class="s3-tag-inv">СТО ЦКТИ</span>
            <span class="s3-tag-inv">ТУ</span>
          </div>
        </div>

        <!-- Bottom: title + sub -->
        <div>
          <h3 class="s3-title-inv">Детали трубопроводов<br>для ТЭС и АЭС</h3>
          <p class="s3-sub-inv">Объекты тепловой и атомной энергетики</p>
        </div>

        <!-- Hover reveal -->
        <div class="s3-vis-hover">Материалы: ст. 09Г2С · 12Х18Н10Т · 15ГС · 15Х1М1Ф · Документация: паспорт, сертификат, ПТД</div>
      </div><!-- /.s3-visual -->

      <!-- Light content area -->
      <div class="s3-body">
        <p class="s3-desc">Изготавливаем детали трубопроводов для объектов тепловой и атомной энергетики по ГОСТ, ОСТ, ТУ, СТО, СТО ЦКТИ и СТО СРО-П. Полный пакет сопроводительной документации.</p>
        <div class="s3-tags">
          <span class="s3-tag">DN 25–1400</span>
          <span class="s3-tag">PN до 250</span>
          <span class="s3-tag">Паспорт</span>
          <span class="s3-tag">Легир. стали</span>
          <span class="s3-tag">ГОСТ 17375–17379</span>
        </div>
      </div>

    </div><!-- /.s3-d1 -->

    <!-- ── D2: Wide card · Отводы / тройники / переходы ─── -->
    <div class="s3-card s3-d2">

      <div class="s3-num">02</div>
      <h3 class="s3-title">Отводы, тройники,<br>переходы и заглушки</h3>
      <p class="s3-sub">Соединительные детали трубопроводов</p>

      <!-- 4 fitting symbols -->
      <div class="s3-fittings">

        <div class="s3-fitting">
          <svg viewBox="0 0 48 52" width="44" height="48" fill="none" aria-hidden="true">
            <line x1="0" y1="16" x2="22" y2="16" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="0" y1="28" x2="22" y2="28" stroke="#6D8CA6" stroke-width="1.3"></line>
            <path d="M 22,16 A 16,16 0 0,1 38,32" stroke="#6D8CA6" stroke-width="1.3"></path>
            <path d="M 22,28 A  4, 4 0 0,1 26,32" stroke="#6D8CA6" stroke-width="1.3"></path>
            <line x1="26" y1="32" x2="26" y2="52" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="38" y1="32" x2="38" y2="52" stroke="#6D8CA6" stroke-width="1.3"></line>
          </svg>
          <span class="s3-fitting-lbl">Отвод</span>
        </div>

        <div class="s3-fitting">
          <svg viewBox="0 0 48 48" width="44" height="44" fill="none" aria-hidden="true">
            <line x1="0" y1="20" x2="48" y2="20" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="0" y1="32" x2="18" y2="32" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="30" y1="32" x2="48" y2="32" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="18" y1="32" x2="18" y2="48" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="30" y1="32" x2="30" y2="48" stroke="#6D8CA6" stroke-width="1.3"></line>
          </svg>
          <span class="s3-fitting-lbl">Тройник</span>
        </div>

        <div class="s3-fitting">
          <svg viewBox="0 0 52 48" width="48" height="44" fill="none" aria-hidden="true">
            <line x1="0" y1="10" x2="52" y2="17" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="0" y1="34" x2="52" y2="27" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="0" y1="10" x2="0" y2="34" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="52" y1="17" x2="52" y2="27" stroke="#6D8CA6" stroke-width="1.3"></line>
          </svg>
          <span class="s3-fitting-lbl">Переход</span>
        </div>

        <div class="s3-fitting">
          <svg viewBox="0 0 48 44" width="44" height="40" fill="none" aria-hidden="true">
            <line x1="0" y1="14" x2="32" y2="14" stroke="#6D8CA6" stroke-width="1.3"></line>
            <line x1="0" y1="30" x2="32" y2="30" stroke="#6D8CA6" stroke-width="1.3"></line>
            <path d="M 32,14 A 8,8 0 0,1 32,30" fill="rgba(109,140,166,.07)" stroke="#6D8CA6" stroke-width="1.3"></path>
          </svg>
          <span class="s3-fitting-lbl">Заглушка</span>
        </div>

      </div><!-- /.s3-fittings -->

      <p class="s3-desc">Производим соединительные детали для изменения направления, разветвления, сужения трубопровода и его закрытия — под давление, температуру и марку стали заказчика.</p>
      <div class="s3-tags">
        <span class="s3-tag">DN 25–1200</span>
        <span class="s3-tag">PN до 250</span>
        <span class="s3-tag">ГОСТ 17375–17379</span>
        <span class="s3-tag">Бесшовные</span>
        <span class="s3-tag">Сварные</span>
      </div>

      <div class="s3-hover-bar">Угловые и секционные · Штамповка · Сегментная сварка · Конические и соосные переходы</div>
    </div><!-- /.s3-d2 -->

    <!-- ── D3: Compact · Трубы / изоляция ────────────────── -->
    <div class="s3-card s3-d3">

      <!-- Insulated pipe cross-section (abs) -->
      <svg class="s3-schema" viewBox="0 0 120 120" width="108" height="108" style="bottom:-10px;right:-10px;" aria-hidden="true" fill="none">
        <circle cx="60" cy="60" r="52" fill="rgba(109,140,166,.05)" stroke="#A0B0BC" stroke-width="1" stroke-dasharray="4 3"></circle>
        <circle cx="60" cy="60" r="38" fill="rgba(30,61,92,.08)" stroke="#6D8CA6" stroke-width="1.2"></circle>
        <circle cx="60" cy="60" r="28" fill="rgba(15,42,68,.06)" stroke="#6D8CA6" stroke-width="1" stroke-dasharray="3 2"></circle>
        <circle cx="60" cy="60" r="3" fill="rgba(109,140,166,.35)"></circle>
        <line x1="12" y1="60" x2="108" y2="60" stroke="#A0B0BC" stroke-width=".6" stroke-dasharray="6 3"></line>
        <line x1="60" y1="12" x2="60" y2="108" stroke="#A0B0BC" stroke-width=".6" stroke-dasharray="6 3"></line>
        <text x="68" y="34" font-family="DINPro,monospace" font-size="8" fill="rgba(109,140,166,.55)" letter-spacing=".3">ВУС</text>
        <text x="68" y="52" font-family="DINPro,monospace" font-size="7" fill="rgba(109,140,166,.4)" letter-spacing=".3">S</text>
      </svg>

      <div class="s3-num">03</div>
      <h3 class="s3-title-sm">Трубы, изоляция<br>и покрытия</h3>
      <p class="s3-sub">Комплектация под условия эксплуатации</p>
      <p class="s3-desc-sm">Поставляем трубы с антикоррозионным и тепловым покрытием — ВУС, ППУ, ЦПП, ЭП, СЭП — под конкретные условия прокладки и среды.</p>
      <div class="s3-tags">
        <span class="s3-tag">ВУС</span>
        <span class="s3-tag">ППУ</span>
        <span class="s3-tag">ЦПП</span>
        <span class="s3-tag">ЭП</span>
        <span class="s3-tag">СЭП</span>
      </div>

      <div class="s3-hover-bar">Бесшовные · ГОСТ 8731 / 8733 · Сварные · ГОСТ 10705 / 20295</div>
    </div><!-- /.s3-d3 -->

    <!-- ── D4: Compact · Нестандартные узлы ──────────────── -->
    <div class="s3-card s3-d4">

      <!-- Custom part drawing fragment (abs) -->
      <svg class="s3-schema" viewBox="0 0 120 100" width="108" height="90" style="bottom:-8px;right:-8px;" aria-hidden="true" fill="none">
        <path d="M 22,82 L 22,28 L 52,16 L 82,28 L 94,50 L 82,82 Z" fill="rgba(109,140,166,.05)" stroke="#6D8CA6" stroke-width="1" stroke-dasharray="4 3"></path>
        <line x1="13" y1="28" x2="13" y2="82" stroke="#A0B0BC" stroke-width=".7"></line>
        <line x1="8" y1="28" x2="18" y2="28" stroke="#A0B0BC" stroke-width=".7"></line>
        <line x1="8" y1="82" x2="18" y2="82" stroke="#A0B0BC" stroke-width=".7"></line>
        <line x1="22" y1="90" x2="82" y2="90" stroke="#A0B0BC" stroke-width=".7"></line>
        <line x1="22" y1="85" x2="22" y2="95" stroke="#A0B0BC" stroke-width=".7"></line>
        <line x1="82" y1="85" x2="82" y2="95" stroke="#A0B0BC" stroke-width=".7"></line>
        <circle cx="57" cy="50" r="4" fill="none" stroke="#A0B0BC" stroke-width=".7"></circle>
        <line x1="45" y1="50" x2="69" y2="50" stroke="#A0B0BC" stroke-width=".6" stroke-dasharray="3 2"></line>
        <line x1="57" y1="38" x2="57" y2="62" stroke="#A0B0BC" stroke-width=".6" stroke-dasharray="3 2"></line>
      </svg>

      <div class="s3-num">04</div>
      <h3 class="s3-title-sm">Нестандартные<br>узлы и изделия</h3>
      <p class="s3-sub">По КД, чертежам и ТЗ заказчика</p>
      <p class="s3-desc-sm">Изготавливаем фасонные части, переходники и специальные элементы по конструкторской документации заказчика — от единичного до серийного.</p>
      <div class="s3-tags">
        <span class="s3-tag">Чертёж</span>
        <span class="s3-tag">ТЗ</span>
        <span class="s3-tag">ОСТ / ТУ</span>
        <span class="s3-tag">от 1 шт.</span>
      </div>

      <div class="s3-hover-bar">Узлы · Переходники · Фасонные части · Нестандартные конфигурации</div>
    </div><!-- /.s3-d4 -->

    <!-- ── CTA panel ──────────────────────────────────────── -->
    <div class="s3-cta">
      <div class="s3-cta-inner">
        <p class="s3-cta-text">Готовы обсудить параметры вашего трубопровода. Подготовим технико-коммерческое предложение на основе технического задания, чертежей или проектной документации — в рабочие сроки.</p>
        <div class="s3-cta-actions">
          <button class="s3-btn" onclick="openRequestModal('solution')">Подобрать решение по ТЗ ↗</button>
          <span class="s3-btn-alt">Все направления производства</span>
        </div>
      </div>
    </div><!-- /.s3-cta -->

  </div><!-- /.s3-grid -->

</section>

<!-- ════════════════════════════════════════════════════════════
     SECTION 4 · ГЕОГРАФИЯ ПОСТАВОК
════════════════════════════════════════════════════════════ -->
<section class="s4" id="geography">

  <header class="s4-head">
    <p class="s4-kicker">География поставок</p>
    <h2 class="s4-h2">Подтверждённые<br>объекты поставок</h2>
    <div class="s4-right">
      <p class="s4-lead">Производим и отгружаем детали трубопроводов для российских и зарубежных энергетических объектов. Работаем по требованиям объекта: нормативная база, материал, комплект документов.</p>
      <div class="s4-stats">
        <div class="s4-stat">
          <span class="s4-stat-num">5</span>
          <span class="s4-stat-label">Объектов поставки</span>
        </div>
        <div class="s4-stat">
          <span class="s4-stat-num">2</span>
          <span class="s4-stat-label">Международных проекта</span>
        </div>
        <div class="s4-stat">
          <span class="s4-stat-num">3</span>
          <span class="s4-stat-label">Страны</span>
        </div>
      </div>
    </div>
  </header>

  <div class="s4-map">
    <div id="s4-wrap">
      <canvas id="s4-canvas"></canvas>
      <div class="s4-tooltip" id="s4Tooltip"></div>
    </div>
  </div>

  <!-- Mobile: canvas hit-testing can't reliably separate two points ~20px
       apart on a phone screen with a fingertip — below 640px this list
       (built from the same destinations/projectInfo data as the map tooltip)
       replaces the interactive map instead of trying to nurse it smaller. -->
  <div class="s4-mobile-list" id="s4MobileList"></div>

</section>

<!-- ══════════════════════════════════════════════════════════════
     S5 — ЗАВОД В ЦИФРАХ
     ══════════════════════════════════════════════════════════════ -->
<section class="s5 history-section" id="passport" data-od-id="s5-passport">

  <div class="s5-sticky history-sticky">

  <div class="s5-eyebrow">
    <span class="s5-eye-num">05</span>
    <span class="s5-eye-label">ИСТОРИЯ ЗАВОДА</span>
  </div>

  <div class="s5-inner">

    <!-- ── TOP: Horizontal timeline ── -->
    <div class="s5-tl-wrap">
      <div class="s5-tl-head">
        <p class="s5-scroll-hint">Прокрутите — листайте этапы</p>
        <div class="s5-tl-nav" aria-hidden="true">
          <button type="button" class="s5-tl-btn" id="s5Prev" aria-label="Предыдущий этап">←</button>
          <button type="button" class="s5-tl-btn" id="s5Next" aria-label="Следующий этап">→</button>
        </div>
      </div>
      <div class="s5-tl-viewport" id="s5TlViewport">
        <nav class="s5-tl" aria-label="Этапы истории завода">
          <div class="s5-tl-track history-track" id="s5TlTrack">
            <div class="s5-tl-progress" id="s5Progress"></div>

            <div class="s5-tl-item active" data-idx="0" role="button" tabindex="0">
              <span class="s5-tl-year">2017</span>
              <span class="s5-tl-cat">Основание</span>
              <div class="s5-tl-node"></div>
            </div>

            <div class="s5-tl-item" data-idx="1" role="button" tabindex="0">
              <span class="s5-tl-year">2019</span>
              <span class="s5-tl-cat">Сертификация</span>
              <div class="s5-tl-node"></div>
            </div>

            <div class="s5-tl-item" data-idx="2" role="button" tabindex="0">
              <span class="s5-tl-year">2021</span>
              <span class="s5-tl-cat">Атомная отрасль</span>
              <div class="s5-tl-node"></div>
            </div>

            <div class="s5-tl-item" data-idx="3" role="button" tabindex="0">
              <span class="s5-tl-year">2023</span>
              <span class="s5-tl-cat">Масштабирование</span>
              <div class="s5-tl-node"></div>
            </div>

            <div class="s5-tl-item" data-idx="4" role="button" tabindex="0">
              <span class="s5-tl-year">2025</span>
              <span class="s5-tl-cat">Сегодня</span>
              <div class="s5-tl-node"></div>
            </div>

          </div>
        </nav>
      </div>
    </div>

    <!-- ── BOTTOM: Content slides — horizontal track ── -->
    <div class="s5-content" aria-live="polite">
      <div class="s5-htrack history-track" id="s5HTrack">

      <!-- 0 · 2017 -->
      <div class="s5-hpanel history-panel" data-idx="0">
      <div class="s5-slide active" data-idx="0">
        <div class="s5-slide-bg-year" aria-hidden="true">2017</div>
        <div class="s5-slide-main">
          <div class="s5-slide-head">
            <p class="s5-slide-period">2017 — Запуск</p>
            <h2 class="s5-slide-title">Основание<br>завода</h2>
            <div class="s5-slide-facts">
              <div class="s5-fact">
                <span class="s5-fact-v">2017</span>
                <span class="s5-fact-k">Год основания</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">ТЭС</span>
                <span class="s5-fact-k">Первая отрасль</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">ГОСТ</span>
                <span class="s5-fact-k">Нормативная база</span>
              </div>
            </div>
          </div>
          <div class="s5-slide-desc-panel">
            <span class="s5-desc-corner tl"></span>
            <span class="s5-desc-corner tr"></span>
            <span class="s5-desc-corner bl"></span>
            <span class="s5-desc-corner br"></span>
            <div class="s5-desc-bg-num" aria-hidden="true">01</div>
            <header class="s5-desc-top">
              <span class="s5-desc-top-label">Описание этапа</span>
              <span class="s5-desc-top-num">01</span>
            </header>
            <div class="s5-desc-inner">
              <div class="s5-slide-desc-body">
                <p class="s5-slide-desc">Завод основан как специализированное производство деталей трубопроводов для объектов тепловой энергетики. Первые производственные мощности, запуск комплектации по ГОСТ и начало сертификации продукции.</p>
              </div>
            </div>
            <footer class="s5-desc-meta">
              <span class="s5-desc-meta-tag">Этап истории</span>
              <span>01 / 05</span>
            </footer>
          </div>
        </div>
      </div>
      </div><!-- .s5-hpanel 2017 -->

      <!-- 1 · 2019 -->
      <div class="s5-hpanel history-panel" data-idx="1">
      <div class="s5-slide" data-idx="1">
        <div class="s5-slide-bg-year" aria-hidden="true">2019</div>
        <div class="s5-slide-main">
          <div class="s5-slide-head">
            <p class="s5-slide-period">2019 — Рост</p>
            <h2 class="s5-slide-title">Первые поставки<br>и сертификация</h2>
            <div class="s5-slide-facts">
              <div class="s5-fact">
                <span class="s5-fact-v">DN 800</span>
                <span class="s5-fact-k">Макс. диаметр</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">ГОСТ · ОСТ</span>
                <span class="s5-fact-k">Стандарты</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">ВИК + УЗК</span>
                <span class="s5-fact-k">Контроль</span>
              </div>
            </div>
          </div>
          <div class="s5-slide-desc-panel">
            <span class="s5-desc-corner tl"></span>
            <span class="s5-desc-corner tr"></span>
            <span class="s5-desc-corner bl"></span>
            <span class="s5-desc-corner br"></span>
            <div class="s5-desc-bg-num" aria-hidden="true">02</div>
            <header class="s5-desc-top">
              <span class="s5-desc-top-label">Описание этапа</span>
              <span class="s5-desc-top-num">02</span>
            </header>
            <div class="s5-desc-inner">
              <div class="s5-slide-desc-body">
                <p class="s5-slide-desc">Получение первых отраслевых сертификатов соответствия. Поставки на действующие объекты ТЭС. Запуск штамповочного участка и расширение номенклатуры — штампосварные отводы и тройники до DN 800.</p>
              </div>
            </div>
            <footer class="s5-desc-meta">
              <span class="s5-desc-meta-tag">Этап истории</span>
              <span>02 / 05</span>
            </footer>
          </div>
        </div>
      </div>
      </div><!-- .s5-hpanel 2019 -->

      <!-- 2 · 2021 -->
      <div class="s5-hpanel history-panel" data-idx="2">
      <div class="s5-slide" data-idx="2">
        <div class="s5-slide-bg-year" aria-hidden="true">2021</div>
        <div class="s5-slide-main">
          <div class="s5-slide-head">
            <p class="s5-slide-period">2021 — Аттестация</p>
            <h2 class="s5-slide-title">Выход на атомную<br>энергетику</h2>
            <div class="s5-slide-facts">
              <div class="s5-fact">
                <span class="s5-fact-v">АЭС · ТЭС</span>
                <span class="s5-fact-k">Отрасли</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">СТО ЦКТИ</span>
                <span class="s5-fact-k">Аттестация</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">+600 °C</span>
                <span class="s5-fact-k">Темп. среды</span>
              </div>
            </div>
          </div>
          <div class="s5-slide-desc-panel">
            <span class="s5-desc-corner tl"></span>
            <span class="s5-desc-corner tr"></span>
            <span class="s5-desc-corner bl"></span>
            <span class="s5-desc-corner br"></span>
            <div class="s5-desc-bg-num" aria-hidden="true">03</div>
            <header class="s5-desc-top">
              <span class="s5-desc-top-label">Описание этапа</span>
              <span class="s5-desc-top-num">03</span>
            </header>
            <div class="s5-desc-inner">
              <div class="s5-slide-desc-body">
                <p class="s5-slide-desc">Прохождение аттестации по СТО ЦКТИ и СТО СРО-П. Первые поставки на объекты АЭС. Освоение производства из жаропрочных и коррозионностойких марок стали: 09Г2С, 12Х18Н10Т, 15Х1М1Ф.</p>
              </div>
            </div>
            <footer class="s5-desc-meta">
              <span class="s5-desc-meta-tag">Этап истории</span>
              <span>03 / 05</span>
            </footer>
          </div>
        </div>
      </div>
      </div><!-- .s5-hpanel 2021 -->

      <!-- 3 · 2023 -->
      <div class="s5-hpanel history-panel" data-idx="3">
      <div class="s5-slide" data-idx="3">
        <div class="s5-slide-bg-year" aria-hidden="true">2023</div>
        <div class="s5-slide-main">
          <div class="s5-slide-head">
            <p class="s5-slide-period">2023 — Экспорт</p>
            <h2 class="s5-slide-title">Масштабирование<br>и новые рынки</h2>
            <div class="s5-slide-facts">
              <div class="s5-fact">
                <span class="s5-fact-v">DN 1400</span>
                <span class="s5-fact-k">Макс. диаметр</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">Экспорт</span>
                <span class="s5-fact-k">География</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">300+</span>
                <span class="s5-fact-k">Объектов</span>
              </div>
            </div>
          </div>
          <div class="s5-slide-desc-panel">
            <span class="s5-desc-corner tl"></span>
            <span class="s5-desc-corner tr"></span>
            <span class="s5-desc-corner bl"></span>
            <span class="s5-desc-corner br"></span>
            <div class="s5-desc-bg-num" aria-hidden="true">04</div>
            <header class="s5-desc-top">
              <span class="s5-desc-top-label">Описание этапа</span>
              <span class="s5-desc-top-num">04</span>
            </header>
            <div class="s5-desc-inner">
              <div class="s5-slide-desc-body">
                <p class="s5-slide-desc">Выход на экспортные поставки. Запуск системы комплектной поставки «от чертежа до объекта» — от анализа ТЗ и подбора исполнения до отгрузки с полным пакетом сопроводительной документации.</p>
              </div>
            </div>
            <footer class="s5-desc-meta">
              <span class="s5-desc-meta-tag">Этап истории</span>
              <span>04 / 05</span>
            </footer>
          </div>
        </div>
      </div>
      </div><!-- .s5-hpanel 2023 -->

      <!-- 4 · 2025 -->
      <div class="s5-hpanel history-panel" data-idx="4">
      <div class="s5-slide" data-idx="4">
        <div class="s5-slide-bg-year" aria-hidden="true">2025</div>
        <div class="s5-slide-main">
          <div class="s5-slide-head">
            <p class="s5-slide-period">2025 — Сегодня</p>
            <h2 class="s5-slide-title">Полный цикл<br>производства</h2>
            <div class="s5-slide-facts">
              <div class="s5-fact">
                <span class="s5-fact-v">500+</span>
                <span class="s5-fact-k">Объектов</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">PN 400</span>
                <span class="s5-fact-k">Давление</span>
              </div>
              <div class="s5-fact">
                <span class="s5-fact-v">−70…+600</span>
                <span class="s5-fact-k">Диапазон °C</span>
              </div>
            </div>
          </div>
          <div class="s5-slide-desc-panel">
            <span class="s5-desc-corner tl"></span>
            <span class="s5-desc-corner tr"></span>
            <span class="s5-desc-corner bl"></span>
            <span class="s5-desc-corner br"></span>
            <div class="s5-desc-bg-num" aria-hidden="true">05</div>
            <header class="s5-desc-top">
              <span class="s5-desc-top-label">Описание этапа</span>
              <span class="s5-desc-top-num">05</span>
            </header>
            <div class="s5-desc-inner">
              <div class="s5-slide-desc-body">
                <p class="s5-slide-desc">Завод работает как единый производственный цикл: анализ чертежа и ТЗ, подбор материала и стандарта, изготовление, контроль ОТК, комплектация документации и отгрузка на объект точно в срок.</p>
              </div>
            </div>
            <footer class="s5-desc-meta">
              <span class="s5-desc-meta-tag">Этап истории</span>
              <span>05 / 05</span>
            </footer>
          </div>
        </div>
      </div>
      </div><!-- .s5-hpanel 2025 -->

      </div><!-- .s5-htrack -->
    </div><!-- .s5-content -->

  </div><!-- .s5-inner -->

  </div><!-- .s5-sticky -->

</section>

<!-- CHAPTER BAND: ПРОИЗВОДСТВЕННЫЕ ВОЗМОЖНОСТИ (S6 · S7 · S8) -->
<div class="chapter-band">
  <span class="chapter-band-title">ПРОИЗВОДСТВЕННЫЕ ВОЗМОЖНОСТИ</span>
  <span class="chapter-band-line"></span>
  <span class="chapter-band-tag">S6 · S7 · S8</span>
</div>

<!-- ══════════════════════════════════════════════════════════════
     S6 — ИЗ ЧЕРТЕЖА В МЕТАЛЛ
     ══════════════════════════════════════════════════════════════ -->
<section class="s6" id="drawings" data-od-id="s6-drawings">

  <header class="s6-head">
    <p class="s6-kicker">Изготовление по документации</p>
    <h2 class="s6-h2">Из чертежа<br>в металл</h2>
    <p class="s6-lead">Работаем по конструкторской документации заказчика — от единичного изделия до серийной партии.</p>
  </header>

  <div class="s6-body">

    <!-- LEFT: SVG технический чертёж отвода 90° -->
    <div class="s6-drawing-panel">
      <div class="s6-c tl"></div><div class="s6-c tr"></div>
      <div class="s6-c bl"></div><div class="s6-c br"></div>
      <div class="s6-shimmer"></div>

      <svg class="s6-drawing" viewBox="0 0 300 290" xmlns="http://www.w3.org/2000/svg" aria-label="Технический чертёж отвода 90°">
        <defs>
          <linearGradient id="s6metal" x1="10%" y1="0%" x2="90%" y2="100%">
            <stop offset="0%" stop-color="#7A9AB0"></stop>
            <stop offset="35%" stop-color="#B4CAD8"></stop>
            <stop offset="58%" stop-color="#D2E0E8"></stop>
            <stop offset="100%" stop-color="#7A9AB0"></stop>
          </linearGradient>
          <linearGradient id="s6highlight" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="rgba(255,255,255,0)"></stop>
            <stop offset="50%" stop-color="rgba(255,255,255,0.18)"></stop>
            <stop offset="100%" stop-color="rgba(255,255,255,0)"></stop>
          </linearGradient>
          <marker id="s6arr" markerWidth="5" markerHeight="5" refX="4" refY="2.5" orient="auto">
            <path d="M0,0 L5,2.5 L0,5 Z" fill="#6D8CA6"></path>
          </marker>
          <marker id="s6arrR" markerWidth="5" markerHeight="5" refX="1" refY="2.5" orient="auto">
            <path d="M5,0 L0,2.5 L5,5 Z" fill="#6D8CA6"></path>
          </marker>
        </defs>

        <!-- Тело отвода: путь описывает профиль 90° изгиба -->
        <!-- Внешний радиус дуги: 106, внутренний: 54, центр дуги: (120,140) -->
        <path d="M 14 265 L 14 140 A 106 106 0 0 1 120 34 L 265 34 L 265 86 L 120 86 A 54 54 0 0 0 66 140 L 66 265 Z" fill="url(#s6metal)" stroke="#0F2A44" stroke-width="1.5" stroke-linejoin="round"></path>

        <!-- Металлический блик поверх тела -->
        <path d="M 14 265 L 14 140 A 106 106 0 0 1 120 34 L 265 34 L 265 86 L 120 86 A 54 54 0 0 0 66 140 L 66 265 Z" fill="url(#s6highlight)" stroke="none"></path>

        <!-- Внутренняя линия глубины (металлический рельеф) -->
        <path d="M 19 260 L 19 144 A 101 101 0 0 1 124 39 L 260 39" fill="none" stroke="#D2E0E8" stroke-width="0.8" opacity="0.55"></path>

        <!-- Торцевая штриховка — низ вертикальной трубы -->
        <line x1="14" y1="265" x2="66" y2="265" stroke="#0F2A44" stroke-width="1.5"></line>
        <line x1="21" y1="265" x2="21" y2="257" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="29" y1="265" x2="29" y2="257" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="37" y1="265" x2="37" y2="257" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="45" y1="265" x2="45" y2="257" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="53" y1="265" x2="53" y2="257" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="61" y1="265" x2="61" y2="257" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>

        <!-- Торцевая штриховка — правый торец горизонтальной трубы -->
        <line x1="265" y1="34" x2="265" y2="86" stroke="#0F2A44" stroke-width="1.5"></line>
        <line x1="265" y1="41" x2="257" y2="41" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="265" y1="51" x2="257" y2="51" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="265" y1="61" x2="257" y2="61" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="265" y1="71" x2="257" y2="71" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>
        <line x1="265" y1="79" x2="257" y2="79" stroke="#6D8CA6" stroke-width="0.8" opacity="0.7"></line>

        <!-- Осевые линии (штрихпунктир) — вертикаль -->
        <line x1="40" y1="278" x2="40" y2="143" stroke="#6D8CA6" stroke-width="0.75" stroke-dasharray="8,3,2,3" opacity="0.55"></line>
        <!-- Осевая — дуга перехода -->
        <path d="M 40 140 A 80 80 0 0 1 120 60" fill="none" stroke="#6D8CA6" stroke-width="0.75" stroke-dasharray="8,3,2,3" opacity="0.55"></path>
        <!-- Осевая — горизонталь -->
        <line x1="120" y1="60" x2="278" y2="60" stroke="#6D8CA6" stroke-width="0.75" stroke-dasharray="8,3,2,3" opacity="0.55"></line>

        <!-- Размер: DN (диаметр трубы) -->
        <line x1="14" y1="210" x2="66" y2="210" stroke="#6D8CA6" stroke-width="0.9" marker-start="url(#s6arrR)" marker-end="url(#s6arr)"></line>
        <line x1="14" y1="200" x2="14" y2="216" stroke="#6D8CA6" stroke-width="0.7" opacity="0.6"></line>
        <line x1="66" y1="200" x2="66" y2="216" stroke="#6D8CA6" stroke-width="0.7" opacity="0.6"></line>
        <text x="40" y="224" text-anchor="middle" font-family="DINPro,monospace" font-size="8.5" fill="#6D8CA6" letter-spacing="0.8">DN 100</text>

        <!-- Размер: радиус гиба R -->
        <line x1="40" y1="146" x2="120" y2="146" stroke="#6D8CA6" stroke-width="0.9" marker-start="url(#s6arrR)" marker-end="url(#s6arr)" opacity="0.9"></line>
        <text x="80" y="160" text-anchor="middle" font-family="DINPro,monospace" font-size="8" fill="#6D8CA6" letter-spacing="0.6">R = 1,5DN</text>

        <!-- Угол 90° -->
        <path d="M 40 108 A 32 32 0 0 1 72 60" fill="none" stroke="#6D8CA6" stroke-width="0.85" opacity="0.75"></path>
        <text x="62" y="96" text-anchor="middle" font-family="DINProCond,DINPro,monospace" font-size="10" fill="#6D8CA6" font-weight="700" letter-spacing="0.4">90°</text>

        <!-- Технический паспорт детали (title block) -->
        <rect x="142" y="235" width="120" height="44" fill="rgba(232,236,240,0.85)" stroke="#A0B0BC" stroke-width="0.8"></rect>
        <line x1="142" y1="245" x2="262" y2="245" stroke="#A0B0BC" stroke-width="0.7"></line>
        <text x="147" y="243" font-family="DINPro,monospace" font-size="7" fill="#6D8CA6" letter-spacing="1.2">МАТЕРИАЛ</text>
        <text x="147" y="257" font-family="DINProCond,DINPro,monospace" font-size="10" fill="#0F2A44" font-weight="700" letter-spacing="0.3">09Г2С / 12Х1МФ</text>
        <text x="147" y="268" font-family="DINPro,monospace" font-size="7" fill="#6D8CA6" letter-spacing="0.6">ГОСТ 17375 / ТУ</text>
        <text x="147" y="276" font-family="DINPro,monospace" font-size="7" fill="#6D8CA6" letter-spacing="0.4" opacity="0.65">Р ≤ 250 / Т ≤ +600°C</text>

        <!-- Номер чертежа -->
        <text x="290" y="16" text-anchor="end" font-family="DINPro,monospace" font-size="7" fill="#A0B0BC" letter-spacing="0.8" opacity="0.65">ОТВОД 90° ГОСТ 17375</text>
        <text x="290" y="26" text-anchor="end" font-family="DINPro,monospace" font-size="7" fill="#A0B0BC" letter-spacing="0.6">ПЭ-01-100-90</text>
      </svg>

      <span class="s6-dwg-label">ПКД / ОТВОД 90° / DN 100 / М 1:2</span>
    </div>

    <!-- RIGHT: Аккордеон -->
    <div class="s6-acc-wrap">

      <div class="s6-row active">
        <div class="sr-num">01</div>
        <div class="sr-content">
          <div class="sr-bar">
            <div class="sr-bar-text">
              <span class="sr-title">Работа по КД заказчика</span>
              <span class="sr-meta">чертёж · ТЗ · согласование</span>
            </div>
            <span class="sr-plus">+</span>
          </div>
          <div class="sr-panel">
            <div class="sr-left">Принимаем рабочие чертежи, спецификации и ТЗ заказчика. Изготавливаем строго по предоставленной документации без самовольных отступлений.</div>
            <div class="sr-sep"></div>
            <div class="sr-right">
              <div class="s2-param"><span class="s2-param-k">Формат КД</span><span class="s2-param-v">PDF · DWG · STEP</span></div>
              <div class="s2-param"><span class="s2-param-k">Нормативы</span><span class="s2-param-v">ГОСТ / ОСТ / СТО / ТУ</span></div>
              <div class="s2-param"><span class="s2-param-k">Согласование</span><span class="s2-param-v">До запуска производства</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="s6-row">
        <div class="sr-num">02</div>
        <div class="sr-content">
          <div class="sr-bar">
            <div class="sr-bar-text">
              <span class="sr-title">Анализ и адаптация документации</span>
              <span class="sr-meta">конструктив · допуски · применимость</span>
            </div>
            <span class="sr-plus">+</span>
          </div>
          <div class="sr-panel">
            <div class="sr-left">Проверяем чертёж на технологичность, уточняем допуски и посадки. При необходимости — согласуем адаптацию с заказчиком и фиксируем письменно.</div>
            <div class="sr-sep"></div>
            <div class="sr-right">
              <div class="s2-param"><span class="s2-param-k">Допуски</span><span class="s2-param-v">По чертежу / ГОСТ</span></div>
              <div class="s2-param"><span class="s2-param-k">Адаптация</span><span class="s2-param-v">По согласованию</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="s6-row">
        <div class="sr-num">03</div>
        <div class="sr-content">
          <div class="sr-bar">
            <div class="sr-bar-text">
              <span class="sr-title">Подбор материала и технологии</span>
              <span class="sr-meta">марка стали · метод · режим</span>
            </div>
            <span class="sr-plus">+</span>
          </div>
          <div class="sr-panel">
            <div class="sr-left">Подбираем марку стали и метод изготовления (штамповка, сварка, точение) в соответствии с требованиями КД и условиями эксплуатации.</div>
            <div class="sr-sep"></div>
            <div class="sr-right">
              <div class="s2-param"><span class="s2-param-k">Стали</span><span class="s2-param-v">Угл. / Лег. / Нержавеющие</span></div>
              <div class="s2-param"><span class="s2-param-k">Методы</span><span class="s2-param-v">Штамповка / Сварка / Точение</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="s6-row">
        <div class="sr-num">04</div>
        <div class="sr-content">
          <div class="sr-bar">
            <div class="sr-bar-text">
              <span class="sr-title">Единичные и серийные партии</span>
              <span class="sr-meta">1 шт. → серия → документация</span>
            </div>
            <span class="sr-plus">+</span>
          </div>
          <div class="sr-panel">
            <div class="sr-left">От одного изделия до серийных партий. Для объектов АЭС и ТЭС — с полным комплектом сопроводительной документации на каждую единицу.</div>
            <div class="sr-sep"></div>
            <div class="sr-right">
              <div class="s2-param"><span class="s2-param-k">Партии</span><span class="s2-param-v">Единичные → Серийные</span></div>
              <div class="s2-param"><span class="s2-param-k">Документы</span><span class="s2-param-v">Паспорт · Сертификат · ВИК</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="s6-row">
        <div class="sr-num">05</div>
        <div class="sr-content">
          <div class="sr-bar">
            <div class="sr-bar-text">
              <span class="sr-title">Сложные трубопроводные узлы</span>
              <span class="sr-meta">коллекторы · тройниковые сборки</span>
            </div>
            <span class="sr-plus">+</span>
          </div>
          <div class="sr-panel">
            <div class="sr-left">Изготавливаем коллекторы, тройниковые блоки, сложные сварные узлы по КД заказчика. Контроль сварных соединений — ВИК, УЗК или РК.</div>
            <div class="sr-sep"></div>
            <div class="sr-right">
              <div class="s2-param"><span class="s2-param-k">Сварка</span><span class="s2-param-v">РД / АДС / ТИГ</span></div>
              <div class="s2-param"><span class="s2-param-k">Контроль швов</span><span class="s2-param-v">ВИК / УЗК / РК</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="s6-row">
        <div class="sr-num">06</div>
        <div class="sr-content">
          <div class="sr-bar">
            <div class="sr-bar-text">
              <span class="sr-title">Установка бобышек и штуцеров</span>
              <span class="sr-meta">бобышки · штуцеры · отборники давления</span>
            </div>
            <span class="sr-plus">+</span>
          </div>
          <div class="sr-panel">
            <div class="sr-left">Устанавливаем бобышки, штуцеры и отборники давления на трубопроводные детали по чертежу заказчика. Сварные швы проходят обязательный ВИК.</div>
            <div class="sr-sep"></div>
            <div class="sr-right">
              <div class="s2-param"><span class="s2-param-k">Тип</span><span class="s2-param-v">Бобышка / Штуцер / Отборник</span></div>
              <div class="s2-param"><span class="s2-param-k">Исполнение</span><span class="s2-param-v">По КД заказчика</span></div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- .s6-acc-wrap -->

  </div><!-- .s6-body -->

</section>

<!-- ═══════════════════════════════════════════════════════════════
     S7 — ПАРАМЕТРЫ ПРОИЗВОДСТВА
     ════════════════════════════════════════════════════════════ -->
<section class="s7" id="parameters" data-od-id="s7-parameters">
  <div class="s7-inner">
    <div class="s7-left">
      <div class="s7-eyebrow">
        <span class="s7-eye-num">07</span>
        <span class="s7-eye-label">ВОЗМОЖНОСТИ</span>
      </div>
      <h2 class="s7-h2">Параметры<br>производства</h2>
      <p class="s7-desc">Полный диапазон диаметров и давлений для объектов
        атомной и тепловой энергетики. Изготовление в единичном
        и серийном исполнении по ГОСТ, ОСТ, СТО, ТУ и КД заказчика.</p>
    </div>
    <div class="s7-right">
      <div class="s7-param" data-tip="Условный диаметр от DN 15 до DN 1400">
        <span class="s7-pk">ДИАМЕТРЫ</span>
        <span class="s7-pv">DN 15 — DN 1400</span>
        <div class="s7-tt">Охватывает все типовые типоразмеры<br>для ТЭС и АЭС. Нестандарт — по чертежу.</div>
      </div>
      <div class="s7-param">
        <span class="s7-pk">ТОЛЩИНА СТЕНКИ</span>
        <span class="s7-pv">S 2 — S 100 мм</span>
        <div class="s7-tt">Тонкостенные и толстостенные изделия;<br>классы точности по ГОСТ.</div>
      </div>
      <div class="s7-param">
        <span class="s7-pk">СТАЛИ</span>
        <span class="s7-pv">Углерод. / Легир. / Нерж.</span>
        <div class="s7-tt">Ст20, 09Г2С, 12Х1МФ, 08Х18Н10Т,<br>15Х5М и другие по согласованию.</div>
      </div>
      <div class="s7-param">
        <span class="s7-pk">ИЗГОТОВЛЕНИЕ</span>
        <span class="s7-pv">Штамповка / Сварка / Точение</span>
        <div class="s7-tt">Выбор метода зависит от DN, S и<br>требований нормативного документа.</div>
      </div>
      <div class="s7-param">
        <span class="s7-pk">ИСПОЛНЕНИЕ</span>
        <span class="s7-pv">Бесшовное / Сварное</span>
        <div class="s7-tt">Бесшовное — штамповкой или точением.<br>Сварное — с контролем швов РК/УЗК.</div>
      </div>
      <div class="s7-param">
        <span class="s7-pk">ПАРТИИ</span>
        <span class="s7-pv">Единичные → Серийные</span>
        <div class="s7-tt">Минимальная партия — 1 шт.<br>Серийное производство — без ограничений.</div>
      </div>
      <div class="s7-param">
        <span class="s7-pk">КОНТРОЛЬ</span>
        <span class="s7-pv">ВИК / УЗК / РК / КК</span>
        <div class="s7-tt">ВИК — визуально-измерительный;<br>УЗК — ультразвуковой; РК — радиографический;<br>КК — капиллярный. Согласно НД и КД.</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     S8 — КОНТРОЛЬ КАЧЕСТВА / ПАСПОРТ ИЗДЕЛИЯ
     ════════════════════════════════════════════════════════════ -->
<section class="s8" id="quality" data-od-id="s8-quality">
  <div class="s8-grid-bg"></div>
  <div class="s8-hud-top">
    <span class="s8-hud-tl">56°12′N / 092°47′E · ЗАПАДНАЯ СИБИРЬ · ИЗДЕЛИЯ ДЛЯ ОБЪЕКТОВ ТЭС И АЭС</span>
    <span class="s8-hud-tr">SYS://QC.PASSPORT.v2.4 · СТАТУС: АКТИВЕН · 14.08.2024</span>
  </div>
  <div class="s8-inner">

    <!-- LEFT: headline + control route -->
    <div class="s8-left">
      <div class="s8-head-block">
        <div class="s8-eyebrow">
          <span class="s8-eye-num">08</span>
          <span class="s8-eye-label">ПРОСЛЕЖИВАЕМОСТЬ</span>
        </div>
        <h2 class="s8-h2">КАЖДОЕ ИЗДЕЛИЕ<br>ИМЕЕТ ИСТОРИЮ</h2>
        <p class="s8-lead">От сертификата металла и номера плавки до ОТК, неразрушающего контроля и комплекта сопроводительной документации.</p>
      </div>
      <div class="s8-route-block">
        <div class="s8-route-header">МАРШРУТ КОНТРОЛЯ КАЧЕСТВА</div>
        <div class="s8-route-list">
          <div class="s8-route-item" data-stage="material">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">01</span>
            <span class="s8-ri-name">Входной контроль металла</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="standard">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">02</span>
            <span class="s8-ri-name">Проверка сертификатов</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="heat">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">03</span>
            <span class="s8-ri-name">Идентификация плавки</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="dn">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">04</span>
            <span class="s8-ri-name">Операционный контроль</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="nk">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">05</span>
            <span class="s8-ri-name">Неразрушающий контроль</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="geo">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">06</span>
            <span class="s8-ri-name">Проверка геометрии</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="mark">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">07</span>
            <span class="s8-ri-name">Маркировка изделия</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
          <div class="s8-route-item" data-stage="docs">
            <span class="s8-ri-dot"></span>
            <span class="s8-ri-num">08</span>
            <span class="s8-ri-name">Паспорт и отгрузочные документы</span>
            <span class="s8-ri-ok">ОК</span>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Digital passport -->
    <div class="s8-right">
      <div class="s8-passport-header">
        <span class="s8-passport-label">ЦИФРОВОЙ ПАСПОРТ ИЗДЕЛИЯ</span>
        <span class="s8-passport-sys">SYS://QC.PASSPORT.v2.4</span>
      </div>
      <div class="s8-passport">
        <div class="s8-bracket tl"></div>
        <div class="s8-bracket tr"></div>
        <div class="s8-bracket bl"></div>
        <div class="s8-bracket br"></div>
        <div class="s8-scanline"></div>
        <div class="s8-pp-head">
          <span class="s8-pp-product">Отвод 90° DN 100</span>
          <span class="s8-pp-id">ПЭ-2024-08-0471</span>
        </div>
        <div class="s8-pp-body">
          <div class="s8-pp-row" data-field="standard">
            <span class="s8-pp-key">СТАНДАРТ</span>
            <span class="s8-pp-val">ГОСТ 17375-2001</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="material">
            <span class="s8-pp-key">МАТЕРИАЛ</span>
            <span class="s8-pp-val">09Г2С</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="heat">
            <span class="s8-pp-key">ПЛАВКА</span>
            <span class="s8-pp-val">К-2024-117</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="dn">
            <span class="s8-pp-key">DN / PN</span>
            <span class="s8-pp-val">DN 100 / PN 160</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="nk">
            <span class="s8-pp-key">КОНТРОЛЬ НК</span>
            <span class="s8-pp-val">ВИК / УЗК / ОТК</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="geo">
            <span class="s8-pp-key">ГЕОМЕТРИЯ</span>
            <span class="s8-pp-val">Допуск ±0.5 мм · ПРИНЯТО</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="mark">
            <span class="s8-pp-key">МАРКИРОВКА</span>
            <span class="s8-pp-val">ПЭ · 09Г2С · К-2024-117</span>
            <span class="s8-pp-chk">✓</span>
          </div>
          <div class="s8-pp-row" data-field="docs">
            <span class="s8-pp-key">ДОКУМЕНТЫ</span>
            <span class="s8-pp-val">Серт. 3.1 / Паспорт / Протоколы НК</span>
            <span class="s8-pp-chk">✓</span>
          </div>
        </div>
        <div class="s8-pp-footer">
          <div class="s8-pp-status-bar">
            <div class="s8-pulse"></div>
            <span class="s8-pp-accepted">ПРИНЯТО ОТК · 14.08.2024</span>
          </div>
          <div class="s8-pp-meta">Партия: 24 шт. · Объект: ТЭС-2<br>Рег.: ПЭ/ОТК/24-471</div>
        </div>
      </div>
      <div class="s8-annotations">
        <div class="s8-ann-item">
          <span class="s8-ann-key">Партия</span>
          <span class="s8-ann-val">24 шт.</span>
        </div>
        <div class="s8-ann-item">
          <span class="s8-ann-key">Объект приёмки</span>
          <span class="s8-ann-val">ТЭС-2</span>
        </div>
        <div class="s8-ann-item">
          <span class="s8-ann-key">Регистрация ОТК</span>
          <span class="s8-ann-val">ПЭ/ОТК/24-471</span>
        </div>
        <div class="s8-ann-item">
          <span class="s8-ann-key">Срок архива</span>
          <span class="s8-ann-val">20 лет</span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     S9 — НОРМАТИВНАЯ БАЗА И ДОКУМЕНТЫ
     ════════════════════════════════════════════════════════════ -->
<section class="s9" id="documents" data-od-id="s9-documents">
  <div class="s9-head">
    <div class="s9-title-block">
      <div class="s9-eyebrow">
        <span class="s9-eye-num">09</span>
        <span class="s9-eye-label">ДОКУМЕНТЫ</span>
      </div>
      <h2 class="s9-h2">Нормативная<br>база</h2>
    </div>
    <div class="s9-filter" role="group" aria-label="Фильтр документов">
      <button class="s9-filter-btn active" data-cat="all">ВСЕ</button>
      <button class="s9-filter-btn" data-cat="std">СТАНДАРТЫ</button>
      <button class="s9-filter-btn" data-cat="cert">СЕРТИФИКАТЫ</button>
      <button class="s9-filter-btn" data-cat="smk">СМК</button>
      <button class="s9-filter-btn" data-cat="decl">ДЕКЛАРАЦИИ</button>
      <button class="s9-filter-btn" data-cat="tu">ТУ</button>
    </div>
  </div>
  <div class="s9-grid">

    <div class="s9-card" data-cat="std">
      <div class="s9-card-top">
        <span class="s9-type">ГОСТ</span>
      </div>
      <p class="s9-card-name">ГОСТ 17375–17380-2001 · соединительные детали</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Отводы, тройники, переходы, заглушки. DN 15–800</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="<?php echo esc_url( $promen_nb_url ); ?>">В РЕЕСТР</a>
      </div>
    </div>

    <div class="s9-card" data-cat="std">
      <div class="s9-card-top">
        <span class="s9-type">ГОСТ</span>
      </div>
      <p class="s9-card-name">ГОСТ 33259-2015 · фланцы трубопроводов и арматуры</p>
      <div class="s9-card-foot">
        <span class="s9-scope">DN 10–2400, до PN 250</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="<?php echo esc_url( $promen_nb_url ); ?>">В РЕЕСТР</a>
      </div>
    </div>

    <div class="s9-card" data-cat="std">
      <div class="s9-card-top">
        <span class="s9-type">ОСТ</span>
      </div>
      <p class="s9-card-name">ОСТ 34 и ОСТ 36 · детали трубопроводов ТЭС и АС</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Рраб &lt; 2,2 МПа, t ≤ 425 °C; Dy до 1400</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="<?php echo esc_url( $promen_nb_url ); ?>">В РЕЕСТР</a>
      </div>
    </div>

    <div class="s9-card" data-cat="std">
      <div class="s9-card-top">
        <span class="s9-type">СТО</span>
      </div>
      <p class="s9-card-name">СТО ЦКТИ 2009 · детали трубопроводов тепловых станций</p>
      <div class="s9-card-foot">
        <span class="s9-scope">p ≥ 4,0 МПа, ресурс 200 000 часов</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="<?php echo esc_url( $promen_nb_url ); ?>">В РЕЕСТР</a>
      </div>
    </div>

    <div class="s9-card" data-cat="cert">
      <div class="s9-card-top">
        <span class="s9-type">СЕРТ.</span>
      </div>
      <p class="s9-card-name">Сертификат соответствия ГОСТ 17375-2001</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Отводы крутоизогнутые. DN 15–800</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="#" onclick="openRequestModal('docs',{name:'Сертификат соответствия ГОСТ 17375-2001'});return false;">СКАЧАТЬ</a>
      </div>
    </div>

    <div class="s9-card" data-cat="cert">
      <div class="s9-card-top">
        <span class="s9-type">СЕРТ.</span>
      </div>
      <p class="s9-card-name">Сертификат соответствия ГОСТ 17376-2001</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Тройники. DN 15–600</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="#" onclick="openRequestModal('docs',{name:'Сертификат соответствия ГОСТ 17376-2001'});return false;">СКАЧАТЬ</a>
      </div>
    </div>

    <div class="s9-card" data-cat="smk">
      <div class="s9-card-top">
        <span class="s9-type">СМК</span>
      </div>
      <p class="s9-card-name">Свидетельство ISO 9001:2015</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Производство ДСЕ</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="#" onclick="openRequestModal('docs',{name:'Свидетельство ISO 9001:2015'});return false;">СКАЧАТЬ</a>
      </div>
    </div>

    <div class="s9-card" data-cat="smk">
      <div class="s9-card-top">
        <span class="s9-type">СМК</span>
      </div>
      <p class="s9-card-name">Лицензия Ростехнадзора</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Оборудование АЭС</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="#" onclick="openRequestModal('docs',{name:'Лицензия Ростехнадзора'});return false;">СКАЧАТЬ</a>
      </div>
    </div>

    <div class="s9-card" data-cat="decl">
      <div class="s9-card-top">
        <span class="s9-type">ДЕКЛ.</span>
      </div>
      <p class="s9-card-name">Декларация о соответствии ТР ТС 032/2013</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Оборудование под давлением</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="#" onclick="openRequestModal('docs',{name:'Декларация о соответствии ТР ТС 032/2013'});return false;">СКАЧАТЬ</a>
      </div>
    </div>

    <div class="s9-card" data-cat="tu">
      <div class="s9-card-top">
        <span class="s9-type">ТУ</span>
      </div>
      <p class="s9-card-name">ТУ 24.20.40-001-13842829-2023 Детали трубопроводов</p>
      <div class="s9-card-foot">
        <span class="s9-scope">Изготовление по КД заказчика и вне сортамента ГОСТ</span>
        <span class="s9-status"><span class="s9-status-dot"></span>Действует</span>
        <a class="s9-link" href="#" onclick="openRequestModal('docs',{name:'ТУ 24.20.40-001-13842829-2023 Детали трубопроводов'});return false;">ЗАПРОСИТЬ</a>
      </div>
    </div>

  </div>
  <p class="s9-note">Полный реестр действующих ГОСТ, ОСТ, СТО и ТУ с фильтрами по типу детали и виду документа — на странице <a href="<?php echo esc_url( $promen_nb_url ); ?>">«Нормативная база»</a>.</p>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     FOOTER ZONE — S10 sticky + footer slides over it
     ════════════════════════════════════════════════════════════ -->
<?php get_footer(); ?>
