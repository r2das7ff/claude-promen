<?php
/**
 * Список проектов «Реализованные поставки» — 1:1 из html/proekty.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekty.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ / REV.1' );

$promen_catalog_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_proekty_url  = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_sdt_term     = get_term_by( 'slug', 'sdt', 'product_cat' );
$promen_sdt_url      = ( $promen_sdt_term && ! is_wp_error( $l = get_term_link( $promen_sdt_term ) ) ) ? $l : $promen_catalog_url;
$promen_nb_url       = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : '';

get_header();
?>
<div class="pg">

  <!-- HERO -->
  <div class="prj-hero">
    <div>
      <div class="prj-eyebrow">Реестр реализованных поставок</div>
      <h1 class="prj-h1">Проекты<br><em>завода</em></h1>
      <p class="prj-desc">Поставки соединительных деталей трубопровода, труб и запорной арматуры для объектов
        атомной и тепловой энергетики — в России и на международных стройках. Каждая позиция изготовлена
        по чертежам заказчика с полным пакетом сопроводительной документации.</p>
    </div>
    <div class="prj-stats">
      <div class="hs"><span class="hs-v">5</span><span class="hs-k">Реализованных проекта</span></div>
      <div class="hs"><span class="hs-v">≈500&nbsp;т</span><span class="hs-k">Совокупный объём поставки</span></div>
      <div class="hs"><span class="hs-v">3</span><span class="hs-k">Страны — Россия, Турция, Бангладеш</span></div>
      <div class="hs"><span class="hs-v">45&nbsp;дней</span><span class="hs-k">Средний срок изготовления партии</span></div>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="prj-filters" id="prjFilters">
    <span class="pf-label">Фильтр</span>
    <span class="pf-chip active" data-filter="all">Все проекты<span class="pf-count">05</span></span>
    <span class="pf-chip" data-filter="aes">АЭС<span class="pf-count">03</span></span>
    <span class="pf-chip" data-filter="tes">ТЭС · ГРЭС<span class="pf-count">02</span></span>
    <span class="pf-chip" data-filter="ru">Россия<span class="pf-count">03</span></span>
    <span class="pf-chip" data-filter="intl">Экспорт<span class="pf-count">02</span></span>
  </div>

  <!-- GRID -->
  <div class="prj-body">
    <div class="prj-grid" id="prjGrid">

      <!-- Курская АЭС-2 -->
      <a class="p-card" data-type="aes" data-region="ru" href="<?php echo esc_url( promen_project_url( 'kurskaya-aes' ) ); ?>">
        <div class="p-media">
          <img src="/wp-content/themes/promen/assets/img/projects/kursk2.png" alt="Курская АЭС-2" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="30" y="120" width="140" height="150" fill="#0F2A44"/><circle cx="100" cy="110" r="46" fill="none" stroke="#6D8CA6" stroke-width="2" opacity=".5"/></svg>
          <span class="p-tag">АЭС</span>
          <span class="p-status"><span class="p-status-dot done"></span>Завершено</span>
        </div>
        <div class="p-body">
          <div>
            <div class="p-title">Курская АЭС‑2</div>
            <div class="p-loc">Курчатов, Курская обл. · Россия</div>
          </div>
          <div class="p-facts">
            <div class="p-fact"><span class="p-fact-k">Материал</span><span class="p-fact-v">Сталь 08Х18Н10Т</span></div>
            <div class="p-fact"><span class="p-fact-k">Объём</span><span class="p-fact-v">≈36 т</span></div>
            <div class="p-fact"><span class="p-fact-k">Номенклатура</span><span class="p-fact-v">Фланцы, колена 45–90°</span></div>
          </div>
          <div class="p-foot">
            <span class="p-link">История поставки →</span>
          </div>
        </div>
      </a>

      <!-- Черепетская ГРЭС -->
      <a class="p-card" data-type="tes" data-region="ru" href="<?php echo esc_url( promen_project_url( 'cherepetskaya-gres' ) ); ?>">
        <div class="p-media">
          <img src="/wp-content/themes/promen/assets/img/projects/tec2.png" alt="Черепетская ГРЭС" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="20" y="150" width="30" height="120" fill="#0F2A44"/><rect x="60" y="100" width="30" height="170" fill="#0F2A44"/><rect x="100" y="170" width="30" height="100" fill="#0F2A44"/></svg>
          <span class="p-tag">ГРЭС</span>
          <span class="p-status"><span class="p-status-dot done"></span>Завершено</span>
        </div>
        <div class="p-body">
          <div>
            <div class="p-title">Черепетская ГРЭС</div>
            <div class="p-loc">Суворов, Тульская обл. · Россия</div>
          </div>
          <div class="p-facts">
            <div class="p-fact"><span class="p-fact-k">Материал</span><span class="p-fact-v">Сталь 20</span></div>
            <div class="p-fact"><span class="p-fact-k">Объём</span><span class="p-fact-v">≈157 т</span></div>
            <div class="p-fact"><span class="p-fact-k">Диаметр</span><span class="p-fact-v">Ø25–530 мм</span></div>
          </div>
          <div class="p-foot">
            <span class="p-link">История поставки →</span>
          </div>
        </div>
      </a>

      <!-- АЭС Руппур -->
      <a class="p-card" data-type="aes" data-region="intl" href="<?php echo esc_url( promen_project_url( 'aes-ruppur' ) ); ?>">
        <div class="p-media">
          <img src="/wp-content/themes/promen/assets/img/projects/rupp.png" alt="АЭС «Руппур»" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#2EA8BA" opacity=".25"/><rect x="30" y="120" width="140" height="150" fill="#0F2A44"/><circle cx="100" cy="110" r="46" fill="none" stroke="#2EA8BA" stroke-width="2" opacity=".6"/></svg>
          <span class="p-tag">АЭС</span>
          <span class="p-status"><span class="p-status-dot on"></span>Строительство</span>
          <span class="p-badge-intl">Экспорт</span>
        </div>
        <div class="p-body">
          <div>
            <div class="p-title">АЭС «Руппур»</div>
            <div class="p-loc">Пабна · Бангладеш</div>
          </div>
          <div class="p-facts">
            <div class="p-fact"><span class="p-fact-k">Материал</span><span class="p-fact-v">Сталь 15Х1М1Ф</span></div>
            <div class="p-fact"><span class="p-fact-k">Объём</span><span class="p-fact-v">≈96 т</span></div>
            <div class="p-fact"><span class="p-fact-k">Давление</span><span class="p-fact-v">До 25 МПа</span></div>
          </div>
          <div class="p-foot">
            <span class="p-link">История поставки →</span>
          </div>
        </div>
      </a>

      <!-- АЭС Аккую -->
      <a class="p-card" data-type="aes" data-region="intl" href="<?php echo esc_url( promen_project_url( 'aes-akkuyu' ) ); ?>">
        <div class="p-media">
          <img src="/wp-content/themes/promen/assets/img/projects/turk2.png" alt="АЭС «Аккую»" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#2EA8BA" opacity=".25"/><rect x="30" y="120" width="140" height="150" fill="#0F2A44"/><circle cx="100" cy="110" r="46" fill="none" stroke="#2EA8BA" stroke-width="2" opacity=".6"/></svg>
          <span class="p-tag">АЭС</span>
          <span class="p-status"><span class="p-status-dot on"></span>Строительство</span>
          <span class="p-badge-intl">Экспорт</span>
        </div>
        <div class="p-body">
          <div>
            <div class="p-title">АЭС «Аккую»</div>
            <div class="p-loc">Гюльнар, Мерсин · Турция</div>
          </div>
          <div class="p-facts">
            <div class="p-fact"><span class="p-fact-k">Материал</span><span class="p-fact-v">Сталь 20 / 08Х18Н10Т</span></div>
            <div class="p-fact"><span class="p-fact-k">Объём</span><span class="p-fact-v">≈148 т</span></div>
            <div class="p-fact"><span class="p-fact-k">Номенклатура</span><span class="p-fact-v">Отводы, тройники, переходы</span></div>
          </div>
          <div class="p-foot">
            <span class="p-link">История поставки →</span>
          </div>
        </div>
      </a>

      <!-- ТЭЦ-3 Омск -->
      <a class="p-card" data-type="tes" data-region="ru" href="<?php echo esc_url( promen_project_url( 'teploelektrocentral-tec-3' ) ); ?>">
        <div class="p-media">
          <img src="/wp-content/themes/promen/assets/img/projects/tec3.png" alt="Омская ТЭЦ-3" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="20" y="150" width="30" height="120" fill="#0F2A44"/><rect x="60" y="100" width="30" height="170" fill="#0F2A44"/><rect x="100" y="170" width="30" height="100" fill="#0F2A44"/></svg>
          <span class="p-tag">ТЭЦ</span>
          <span class="p-status"><span class="p-status-dot"></span>Действующий</span>
        </div>
        <div class="p-body">
          <div>
            <div class="p-title">Омская ТЭЦ‑3</div>
            <div class="p-loc">Омск · Россия</div>
          </div>
          <div class="p-facts">
            <div class="p-fact"><span class="p-fact-k">Материал</span><span class="p-fact-v">Сталь 15Х1М1Ф</span></div>
            <div class="p-fact"><span class="p-fact-k">Объём</span><span class="p-fact-v">≈96 т</span></div>
            <div class="p-fact"><span class="p-fact-k">Давление</span><span class="p-fact-v">До 25 МПа</span></div>
          </div>
          <div class="p-foot">
            <span class="p-link">История поставки →</span>
          </div>
        </div>
      </a>

    </div>
  </div>

  <!-- CTA -->
  <div class="prj-cta">
    <div>
      <div class="prj-cta-h">Готовите поставку<br>для <em>своего объекта</em>?</div>
      <p class="prj-cta-p">Пришлите чертёж, спецификацию или техническое задание — инженер завода рассчитает
        материал, срок изготовления и стоимость партии.</p>
    </div>
    <a class="prj-cta-btn" href="<?php echo esc_url( $promen_contacts_url ); ?>">Обсудить поставку →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
