<?php
/**
 * Проект «АЭС «Аккую»» — 1:1 из html/proekt-aes-akkuyu.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekt.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ‑04 / REV.1' );

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
    <b>АЭС «Аккую»</b>
  </div>

  <!-- HERO -->
  <div class="pd-hero">
    <div class="pd-hero-l">
      <div class="pd-badges">
        <span class="pd-badge">АЭС</span>
        <span class="pd-badge status building"><span class="dot"></span>В стадии строительства</span>
        <span class="pd-badge intl">Экспорт</span>
      </div>
      <h1 class="pd-h1">АЭС «Аккую»</h1>
      <div class="pd-loc">Гюльнар, Мерсин · Турция · побережье Средиземного моря</div>
      <p class="pd-desc">Первая турецкая АЭС из четырёх энергоблоков ВВЭР‑1200 (4800 МВт суммарно),
        реализуемая по схеме build‑own‑operate. Генеральный подрядчик — «Атомстройэкспорт». Завод
        «Промышленная Энергетика» поставил соединительные детали трубопровода двумя партиями.</p>
      <div class="pd-stats">
        <div class="hs"><span class="hs-v">Ст20 / 08Х18Н10Т</span><span class="hs-k">Материалы</span></div>
        <div class="hs"><span class="hs-v">≈148 т</span><span class="hs-k">Объём поставки</span></div>
        <div class="hs"><span class="hs-v">4×1200 МВт</span><span class="hs-k">Мощность энергоблоков</span></div>
        <div class="hs"><span class="hs-v">Кат. I–IV</span><span class="hs-k">Категории давления</span></div>
      </div>
    </div>
    <div class="pd-hero-r">
      <picture><source srcset="/wp-content/themes/promen/assets/img/projects/turk2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/turk2.png" alt="АЭС Аккую" loading="eager" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';" width="1536" height="1024"></picture>
      <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice"><rect width="400" height="320" fill="#2EA8BA" opacity=".22"/><rect x="40" y="150" width="80" height="150" fill="#0F2A44"/><rect x="140" y="130" width="80" height="170" fill="#0F2A44"/><rect x="240" y="150" width="80" height="150" fill="#0F2A44"/></svg>
      <span class="pd-hero-r-tag">АЭС «Аккую» · ВВЭР‑1200 ×4</span>
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
        <div class="pd-phase-v">≈92 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Отводы, переходы</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Углеродистая сталь (Сталь 20)</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Диаметр</span><span class="pd-phase-rv">Ø273–426 мм</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 2</div>
        <div class="pd-phase-v">≈56 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Тройники, переходы, отводы</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Аустенитная нержавеющая сталь 08Х18Н10Т</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Класс</span><span class="pd-phase-rv">АЭС-класс, кат. давления ≥2.2 МПа</span></div>
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
          <div class="pd-step-t">Техническое задание и чертежи</div>
          <div class="pd-step-d">Приём КД генподрядчика, согласование марок стали (Ст20 и 08Х18Н10Т) и диаметров под категории давления I–IV.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">02</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Производство</div>
          <div class="pd-step-d">Изготовление партии 1 (углеродистая сталь, ≈92 т, Ø273–426 мм) и партии 2 (аустенитная сталь 08Х18Н10Т, ≈56 т).</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">03</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Контроль качества</div>
          <div class="pd-step-d">Визуально-измерительный и неразрушающий контроль, идентификация плавки, сертификация по требованиям АЭС-класса.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">04</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Упаковка и международная логистика</div>
          <div class="pd-step-d">Маркировка, экспортная упаковка и доставка на строительную площадку АЭС «Аккую» в Турции.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">05</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Документация и передача заказчику</div>
          <div class="pd-step-d">Комплект паспортов изделий, сертификаты соответствия, декларация ТР ТС 032 RU С‑RU.АБ53.В.08323/23.</div>
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
        <span class="pd-prod-code">СДТ</span>
        <div class="pd-prod-name">Отводы и переходы</div>
        <p class="pd-prod-desc">Соединительные детали трубопровода АЭС-класса, углеродистые и аустенитные стали.</p>
        <a class="pd-prod-link" href="<?php echo esc_url( $promen_sdt_url ); ?>">Страница СДТ →</a>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">СДТ‑003</span>
        <div class="pd-prod-name">Отвод 90°</div>
        <p class="pd-prod-desc">Пример изделия из партии — карточка отвода 90° с полным техническим паспортом.</p>
        <a class="pd-prod-link" href="<?php echo esc_url( promen_demo_product_url() ); ?>">Открыть изделие →</a>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">ФЛ</span>
        <div class="pd-prod-name">Фланцы трубопроводные</div>
        <p class="pd-prod-desc">Приварные и свободные фланцы для узлов АЭС, DN 10–1600.</p>
        <a class="pd-prod-link" href="<?php echo esc_url( add_query_arg( 'group', 'flancy', $promen_catalog_url ) ); ?>">В каталоге →</a>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">НБ</span>
        <div class="pd-prod-name">Нормативная база</div>
        <p class="pd-prod-desc">ГОСТ, ОСТ и декларация ТР ТС 032, применённые при изготовлении партии.</p>
        <?php if ( $promen_nb_url ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $promen_nb_url ); ?>"></a><?php endif; ?>
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
