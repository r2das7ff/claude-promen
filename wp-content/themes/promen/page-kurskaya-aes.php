<?php
/**
 * Проект «Курская АЭС‑2» — 1:1 из html/proekt-kurskaya-aes.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekt.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ‑01 / REV.1' );

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
    <b>Курская АЭС‑2</b>
  </div>

  <!-- HERO -->
  <div class="pd-hero">
    <div class="pd-hero-l">
      <div class="pd-badges">
        <span class="pd-badge">АЭС</span>
        <span class="pd-badge status"><span class="dot"></span>Поставки завершены</span>
      </div>
      <h1 class="pd-h1">Курская АЭС‑2</h1>
      <div class="pd-loc">Курчатов, Курская обл. · Россия · 40 км от г. Курска</div>
      <p class="pd-desc">Строящаяся атомная электростанция с реакторами ВВЭР — замена выводимой из
        эксплуатации Курской АЭС. Завод «Промышленная Энергетика» поставил соединительные детали
        трубопровода двумя партиями по чертежам и техническим условиям заказчика.</p>
      <div class="pd-stats">
        <div class="hs"><span class="hs-v">08Х18Н10Т</span><span class="hs-k">Марка стали</span></div>
        <div class="hs"><span class="hs-v">≈36 т</span><span class="hs-k">Объём поставки</span></div>
        <div class="hs"><span class="hs-v">≤45 дней</span><span class="hs-k">Срок изготовления партии</span></div>
        <div class="hs"><span class="hs-v">ISO 9001</span><span class="hs-k">Система менеджмента качества</span></div>
      </div>
    </div>
    <div class="pd-hero-r">
      <img src="/wp-content/themes/promen/assets/img/projects/kursk2.png" alt="Курская АЭС-2" loading="eager" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
      <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice"><rect width="400" height="320" fill="#1E3D5C"/><rect x="60" y="140" width="280" height="160" fill="#0F2A44"/><circle cx="200" cy="130" r="80" fill="none" stroke="#6D8CA6" stroke-width="2.5" opacity=".5"/><circle cx="200" cy="130" r="55" fill="none" stroke="#6D8CA6" stroke-width="1.5" opacity=".35"/></svg>
      <span class="pd-hero-r-tag">Курская АЭС‑2 · ВВЭР</span>
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
        <div class="pd-phase-v">≈10 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Фланцы трубопроводные</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Жаропрочная нержавеющая сталь 08Х18Н10Т</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Класс</span><span class="pd-phase-rv">Аустенитная сталь</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Партия 2</div>
        <div class="pd-phase-v">≈26 т</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Колена (отводы) 45° / 60° / 90°</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Жаропрочная нержавеющая сталь 08Х18Н10Т</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Класс</span><span class="pd-phase-rv">Аустенитная сталь</span></div>
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
          <div class="pd-step-d">Приём КД заказчика, согласование марки стали, параметров DN/PN и нормативной базы изготовления.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">02</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Производство</div>
          <div class="pd-step-d">Изготовление партии 1 (фланцы, ≈10 т) и партии 2 (колена 45–90°, ≈26 т) из стали 08Х18Н10Т на производственной площадке в Челябинске.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">03</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Контроль качества</div>
          <div class="pd-step-d">Визуально-измерительный контроль, неразрушающий контроль (УЗК/РК), идентификация плавки, проверка сертификатов на металл.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">04</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Упаковка и логистика</div>
          <div class="pd-step-d">Маркировка изделий, упаковка для транспортировки, доставка до строительной площадки Курской АЭС‑2.</div>
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
        <span class="pd-prod-code">ФЛ</span>
        <div class="pd-prod-name">Фланцы трубопроводные</div>
        <p class="pd-prod-desc">Приварные и свободные, DN 10–1600, сталь 08Х18Н10Т и аналоги по ГОСТ 12820-80‑80.</p>
        <a class="pd-prod-link" href="<?php echo esc_url( add_query_arg( 'group', 'flancy', $promen_catalog_url ) ); ?>">В каталоге →</a>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">СДТ</span>
        <div class="pd-prod-name">Колена (отводы) 45–90°</div>
        <p class="pd-prod-desc">Соединительные детали трубопровода по чертежам заказчика для АЭС‑класса, бесшовное и сварное исполнение.</p>
        <a class="pd-prod-link" href="<?php echo esc_url( $promen_sdt_url ); ?>">Страница СДТ →</a>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">СДТ‑003</span>
        <div class="pd-prod-name">Отвод 90°</div>
        <p class="pd-prod-desc">Пример изделия из партии — карточка отвода 90° с полным техническим паспортом.</p>
        <a class="pd-prod-link" href="<?php echo esc_url( promen_demo_product_url() ); ?>">Открыть изделие →</a>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">НБ</span>
        <div class="pd-prod-name">Нормативная база</div>
        <p class="pd-prod-desc">ГОСТ, ОСТ, декларация ТР ТС 032 и сертификаты, применённые при изготовлении партии.</p>
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
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'cherepetskaya-gres' ) ); ?>">
        <div class="pd-rel-media"><img src="/wp-content/themes/promen/assets/img/projects/tec2.png" alt="Черепетская ГРЭС" loading="lazy" referrerpolicy="no-referrer"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">ГРЭС · Россия</div><div class="pd-rel-title">Черепетская ГРЭС</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-ruppur' ) ); ?>">
        <div class="pd-rel-media"><img src="/wp-content/themes/promen/assets/img/projects/rupp.png" alt="АЭС Руппур" loading="lazy" referrerpolicy="no-referrer"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Бангладеш</div><div class="pd-rel-title">АЭС «Руппур»</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-akkuyu' ) ); ?>">
        <div class="pd-rel-media"><img src="/wp-content/themes/promen/assets/img/projects/turk2.png" alt="АЭС Аккую" loading="lazy" referrerpolicy="no-referrer"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Турция</div><div class="pd-rel-title">АЭС «Аккую»</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'teploelektrocentral-tec-3' ) ); ?>">
        <div class="pd-rel-media"><img src="/wp-content/themes/promen/assets/img/projects/tec3.png" alt="Омская ТЭЦ-3" loading="lazy" referrerpolicy="no-referrer"></div>
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
