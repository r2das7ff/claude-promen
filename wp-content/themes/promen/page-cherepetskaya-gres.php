<?php
/**
 * Проект «Черепетская ГРЭС» — 1:1 из html/proekt-cherepetskaya-gres.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekt.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ‑02 / REV.1' );

$promen_catalog_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_proekty_url  = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_sdt_term     = get_term_by( 'slug', 'sdt', 'product_cat' );
$promen_sdt_url      = ( $promen_sdt_term && ! is_wp_error( $l = get_term_link( $promen_sdt_term ) ) ) ? $l : $promen_catalog_url;
$promen_nb_url       = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : '';

get_header();
?>
<div class="pg">

  <!-- BREADCRUMB -->
  <div class="pd-crumb">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a><span>/</span>
    <a href="<?php echo esc_url( $promen_proekty_url ); ?>">Проекты</a><span>/</span>
    <b>Черепетская ГРЭС</b>
  </div>

  <!-- HERO -->
  <div class="pd-hero">
    <div class="pd-hero-l">
      <div class="pd-badges">
        <span class="pd-badge">ГРЭС</span>
        <span class="pd-badge status"><span class="dot"></span>Поставки завершены</span>
      </div>
      <h1 class="pd-h1">Черепетская ГРЭС</h1>
      <div class="pd-loc">Суворов, Тульская обл. · Россия · на реке Черепеть</div>
      <p class="pd-desc">Черепетская ГРЭС имени Д. Г. Жимерина — первая в СССР мощная электростанция
        сверхвысокого давления (1953). Сегодня в работе два пылеугольных энергоблока по 225 МВт,
        введённые в 2014–2015 годах: котлы ТПЕ‑223 на 13,8 МПа и 565 °C, турбины К‑225. Завод
        «Промышленная Энергетика» поставил трубы и соединительные детали трубопровода четырьмя партиями.</p>
      <div class="pd-stats">
        <div class="hs"><span class="hs-v">Сталь 20</span><span class="hs-k">Марка стали</span></div>
        <div class="hs"><span class="hs-v">≈157 т</span><span class="hs-k">Объём поставки</span></div>
        <div class="hs"><span class="hs-v">Ø25–530</span><span class="hs-k">Диапазон диаметров, мм</span></div>
        <div class="hs"><span class="hs-v">80%</span><span class="hs-k">Деталей — по СТО ЦКТИ</span></div>
      </div>
    </div>
    <div class="pd-hero-r">
      <picture><source srcset="/wp-content/themes/promen/assets/img/projects/tec2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/tec2.png" alt="Черепетская ГРЭС" loading="eager" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';" width="1536" height="1024"></picture>
      <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice"><rect width="400" height="320" fill="#1E3D5C"/><rect x="40" y="160" width="60" height="140" fill="#0F2A44"/><rect x="120" y="100" width="60" height="200" fill="#0F2A44"/><rect x="200" y="180" width="60" height="120" fill="#0F2A44"/><rect x="280" y="140" width="60" height="160" fill="#0F2A44"/></svg>
      <span class="pd-hero-r-tag">Черепетская ГРЭС · Интер РАО</span>
    </div>
  </div>

  <!-- ОБЪЁМ ПОСТАВКИ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">01</span>
      <h2 class="pd-sec-title">Объём поставки</h2>
    </div>
    <div class="pd-phases">
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 1</div>
        <div class="pd-phase-v">≈31 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Трубы стальные</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Конструкционная углеродистая сталь 20</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Диаметр</span><span class="pd-phase-rv">Ø25–219 мм</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 2</div>
        <div class="pd-phase-v">≈14 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Трубы стальные электросварные</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Диаметр</span><span class="pd-phase-rv">Ø219–530 мм</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Назначение</span><span class="pd-phase-rv">Ограждающие и декоративные конструкции</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 3</div>
        <div class="pd-phase-v">≈73 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Трубы котельные</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Сталь 20</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Диаметр</span><span class="pd-phase-rv">Ø28–219 мм</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 4</div>
        <div class="pd-phase-v">≈39 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Соединительные детали трубопровода</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Норматив</span><span class="pd-phase-rv">80% деталей — по СТО ЦКТИ</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Применение</span><span class="pd-phase-rv">Трубопроводы пара и горячей воды</span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ЭТАПЫ ПОСТАВКИ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">02</span>
      <h2 class="pd-sec-title">Этапы поставки</h2>
    </div>
    <div class="pd-tl">
      <div class="pd-step">
        <div class="pd-step-n">01</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Спецификация на четыре партии</div>
          <div class="pd-step-d">Трубы из стали 20 Ø25–219 мм, электросварные трубы Ø219–530 мм, котельные трубы Ø28–219 мм и соединительные детали — 80% из них по СТО ЦКТИ.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">02</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Изготовление и комплектация</div>
          <div class="pd-step-d">Детали по СТО ЦКТИ и чертежам заказчика изготовлены в Челябинске (≈39 т), трубы скомплектованы по сортаменту и марке стали (≈118 т).</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">03</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Контроль качества</div>
          <div class="pd-step-d">Для деталей трубопроводов пара и горячей воды — контроль в объёме СТО ЦКТИ 10.003‑2007: визуально-измерительный, ультразвуковой, проверка геометрии; для труб — сверка сертификатов на металл.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">04</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Упаковка и логистика</div>
          <div class="pd-step-d">Маркировка, увязка труб в пакеты по диаметрам, доставка автотранспортом до площадки станции в Суворове Тульской области.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">05</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Документация и передача заказчику</div>
          <div class="pd-step-d">Паспорта на детали, сертификаты заводов-изготовителей труб, сертификат соответствия ТР ТС 032 RU С‑RU.АБ53.В.08323/23.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ИСПОЛЬЗУЕМАЯ ПРОДУКЦИЯ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">03</span>
      <h2 class="pd-sec-title">Используемая продукция</h2>
    </div>
    <div class="pd-prod-grid">
      <div class="pd-prod">
        <span class="pd-prod-code">ТР</span>
        <div class="pd-prod-name">Стальные трубы</div>
        <p class="pd-prod-desc">Бесшовные из стали 20 для котельных линий и электросварные Ø219–530 мм для ограждающих конструкций.</p>
        <?php if ( $u = promen_project_term_url( 'product_cat', 'truby' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Трубы в каталоге →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">ЦКТИ</span>
        <div class="pd-prod-name">Детали по СТО ЦКТИ</div>
        <p class="pd-prod-desc">Отводы гнутые и крутоизогнутые по СТО ЦКТИ 321, переходы по СТО ЦКТИ 318 для трубопроводов пара и горячей воды.</p>
        <?php if ( $u = promen_project_term_url( 'norm', 'sto-321-01' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Отводы СТО ЦКТИ 321.01 →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">ОП</span>
        <div class="pd-prod-name">Опоры трубопроводов</div>
        <p class="pd-prod-desc">Неподвижные хомутовые и скользящие опоры для протяжённых трубопроводных линий ТЭС.</p>
        <?php if ( $u = promen_project_term_url( 'product_cat', 'opory' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Опоры в каталоге →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">НБ</span>
        <div class="pd-prod-name">Нормативная база</div>
        <p class="pd-prod-desc">СТО ЦКТИ 10.003‑2007, ГОСТ на трубы и сертификат ТР ТС 032 — документы, по которым изготовлены и проверены партии.</p>
        <?php if ( $promen_nb_url ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $promen_nb_url ); ?>">Нормативная база →</a><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ДРУГИЕ ПРОЕКТЫ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">04</span>
      <h2 class="pd-sec-title">Другие проекты</h2>
    </div>
    <div class="pd-rel-grid">
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'kurskaya-aes' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/kursk2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/kursk2.png" alt="Курская АЭС-2" loading="lazy" referrerpolicy="no-referrer" width="1024" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Россия</div><div class="pd-rel-title">Курская АЭС‑2</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-ruppur' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/rupp.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/rupp.png" alt="АЭС Руппур" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Бангладеш</div><div class="pd-rel-title">АЭС «Руппур»</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-akkuyu' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/turk2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/turk2.png" alt="АЭС Аккую" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Турция</div><div class="pd-rel-title">АЭС «Аккую»</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'teploelektrocentral-tec-3' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/tec3.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/tec3.png" alt="Омская ТЭЦ-3" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">ТЭЦ · Россия</div><div class="pd-rel-title">Омская ТЭЦ‑3</div></div>
      </a>
    </div>
  </div>

  <!-- CTA -->
  <div class="pd-cta">
    <div>
      <div class="pd-cta-h">Нужна похожая<br>поставка для <em>вашего объекта</em>?</div>
      <p class="pd-cta-p">Пришлите чертёж или спецификацию — рассчитаем материал, срок изготовления и стоимость партии по аналогии с этим проектом.</p>
    </div>
    <a class="pd-cta-btn" href="<?php echo esc_url( $promen_contacts_url ); ?>">Обсудить поставку →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
