<?php
/**
 * Проект «Омская ТЭЦ‑3» — 1:1 из html/proekt-teploelektrocentral-tec-3.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekt.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ‑05 / REV.1' );

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
    <b>Омская ТЭЦ‑3</b>
  </div>

  <!-- HERO -->
  <div class="pd-hero">
    <div class="pd-hero-l">
      <div class="pd-badges">
        <span class="pd-badge">ТЭЦ</span>
        <span class="pd-badge status active"><span class="dot"></span>Действующий объект</span>
      </div>
      <h1 class="pd-h1">Омская ТЭЦ‑3</h1>
      <div class="pd-loc">Омск · Россия · АО «ТГК‑11», группа «Интер РАО»</div>
      <p class="pd-desc">Одна из старейших ТЭЦ Омска (1954) и единственная газовая из трёх станций «ТГК‑11»
        в городе: турбины ПТ‑60 и Т‑120, с 2013 года — парогазовая установка ПГУ‑90. Станция снабжает
        паром нефтехимические предприятия и теплом Советский округ. Завод «Промышленная Энергетика»
        поставил трубы, соединительные детали трубопровода и арматуру для перевооружения
        паропроводов высокого давления.</p>
      <div class="pd-stats">
        <div class="hs"><span class="hs-v">15Х1М1Ф</span><span class="hs-k">Марка стали</span></div>
        <div class="hs"><span class="hs-v">≈96 т</span><span class="hs-k">Объём поставки</span></div>
        <div class="hs"><span class="hs-v">25 МПа</span><span class="hs-k">Рабочее давление, до</span></div>
        <div class="hs"><span class="hs-v">3 партии</span><span class="hs-k">Трубы · СДТ · арматура</span></div>
      </div>
    </div>
    <div class="pd-hero-r">
      <picture><source srcset="/wp-content/themes/promen/assets/img/projects/tec3.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/tec3.png" alt="Омская ТЭЦ-3" loading="eager" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';" width="1536" height="1024"></picture>
      <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice"><rect width="400" height="320" fill="#1E3D5C"/><rect x="40" y="180" width="60" height="120" fill="#0F2A44"/><rect x="120" y="120" width="60" height="180" fill="#0F2A44"/><rect x="200" y="160" width="60" height="140" fill="#0F2A44"/><rect x="280" y="100" width="60" height="200" fill="#0F2A44"/></svg>
      <span class="pd-hero-r-tag">Омская ТЭЦ‑3 · ТГК‑11</span>
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
        <div class="pd-phase-v">≈20 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Трубы стальные бесшовные</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Теплоустойчивая сталь 15Х1М1Ф</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Назначение</span><span class="pd-phase-rv">Перевооружение паропроводов высокого давления</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 2</div>
        <div class="pd-phase-v">≈65 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Соединительные детали трубопровода</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Давление</span><span class="pd-phase-rv">До 25 МПа</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Изготовление</span><span class="pd-phase-rv">Серийные и по чертежам заказчика</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 3</div>
        <div class="pd-phase-v">≈11 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Запорная арматура и крепёж</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Состав</span><span class="pd-phase-rv">Штуцеры, вентили и клапаны высокого давления</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Испытания</span><span class="pd-phase-rv">Гидравлические, ГОСТ 33257‑2015</span></div>
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
          <div class="pd-step-t">Техническое задание</div>
          <div class="pd-step-d">Перевооружение паропроводов высокого давления: трубы из 15Х1М1Ф, соединительные детали — серийные и по чертежам заказчика, арматура и крепёж высокого давления.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">02</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Производство и комплектация</div>
          <div class="pd-step-d">Три партии: трубы (≈20 т), соединительные детали на давление до 25 МПа (≈65 т), арматура и крепёж (≈11 т).</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">03</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Контроль теплоустойчивой стали</div>
          <div class="pd-step-d">Визуально-измерительный и ультразвуковой контроль, стилоскопирование марки 15Х1М1Ф, контроль твёрдости гнутых деталей после термообработки, гидравлические испытания арматуры по ГОСТ 33257‑2015.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">04</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Упаковка и логистика</div>
          <div class="pd-step-d">Маркировка по спецификации, защита кромок под сварку, доставка автотранспортом из Челябинска до площадки ТЭЦ‑3 в Омске.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">05</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Документация и передача заказчику</div>
          <div class="pd-step-d">Паспорта изделий, сертификаты на металл с номерами плавок, протоколы контроля и испытаний, сертификат соответствия ТР ТС 032 RU С‑RU.АБ53.В.08323/23.</div>
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
        <div class="pd-prod-name">Трубы из 15Х1М1Ф</div>
        <p class="pd-prod-desc">Бесшовные трубы из теплоустойчивой хромомолибденованадиевой стали для паропроводов перегретого пара высокого давления.</p>
        <?php if ( $u = promen_project_term_url( 'product_cat', 'truby-bsh' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Бесшовные трубы →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">ЦКТИ</span>
        <div class="pd-prod-name">Отводы для паропроводов</div>
        <p class="pd-prod-desc">Гнутые, крутоизогнутые и штампованные отводы по СТО ЦКТИ 321.05 для паропроводов тепловых станций.</p>
        <?php if ( $u = promen_project_term_url( 'norm', 'sto-321-05' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Отводы СТО ЦКТИ 321.05 →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">ЗРА</span>
        <div class="pd-prod-name">Арматура высокого давления</div>
        <p class="pd-prod-desc">Вентили, клапаны и штуцеры для паровых трактов ТЭС с испытаниями по ГОСТ 33257‑2015.</p>
        <?php if ( $u = promen_project_term_url( 'product_cat', 'armatura' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Арматура в каталоге →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">НБ</span>
        <div class="pd-prod-name">Нормативная база</div>
        <p class="pd-prod-desc">СТО ЦКТИ 10.003‑2007, ГОСТ 33257‑2015 и сертификат ТР ТС 032 — документы, по которым изготовлены и проверены партии.</p>
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
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'cherepetskaya-gres' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/tec2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/tec2.png" alt="Черепетская ГРЭС" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">ГРЭС · Россия</div><div class="pd-rel-title">Черепетская ГРЭС</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-ruppur' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/rupp.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/rupp.png" alt="АЭС Руппур" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Бангладеш</div><div class="pd-rel-title">АЭС «Руппур»</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-akkuyu' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/turk2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/turk2.png" alt="АЭС Аккую" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Турция</div><div class="pd-rel-title">АЭС «Аккую»</div></div>
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
