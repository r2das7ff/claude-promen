<?php
/**
 * Список «Статьи и материалы» — 1:1 из html/stati.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет).
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-09.ART‑00 / REV.2' );

$promen_catalog_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_stati_url    = ( $p = promen_page( 'stati' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_proekty_url  = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_nb_url       = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : '';

get_header();
?>
<div class="pg">

  <!-- BREADCRUMB -->
  <div class="pd-crumb">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a><span>/</span>
    <b>Статьи</b>
  </div>

  <!-- HEAD -->
  <div class="bl-head" data-reveal>
    <span class="bl-head-ghost">09</span>
    <div>
      <div class="bl-eyebrow"><span class="bl-eyebrow-n">09</span><span class="bl-eyebrow-l">Знания завода</span></div>
      <h1 class="bl-h1">Статьи<br>и материалы</h1>
    </div>
    <div class="bl-head-r">
      <p class="bl-lead">Инженерный взгляд на материаловедение, производство и нормативную базу соединительных деталей трубопровода — от выбора марки стали до паспорта готового изделия.</p>
      <div class="bl-metarow">
        <div class="bl-metaitem"><b id="statCount">6</b><span>Статей</span></div>
        <div class="bl-metaitem"><b>5</b><span>Разделов</span></div>
        <div class="bl-metaitem"><b>2026</b><span>Обновлено</span></div>
      </div>
    </div>
  </div>

  <!-- MEDIA BAND -->
  <div class="bl-media" data-reveal>
    <picture><source srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-2.webp' ) ); ?>" type="image/webp"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-2.jpg' ) ); ?>" alt="Производственная площадка завода Промышленная Энергетика" loading="eager" decoding="async" width="1500" height="2000"></picture>
    <span class="bl-media-idx">ПЭ-09.ART / REV.2</span>
    <span class="bl-media-tag">Производственная площадка · Челябинск</span>
  </div>

  <!-- FILTERS -->
  <div class="bl-filters">
    <span class="bl-filter-lbl">Раздел</span>
    <div class="bl-chips" id="blChips"></div>
    <span class="bl-filter-count" id="blFilterCount"></span>
  </div>

  <!-- FEATURED + GRID -->
  <div class="bl-wrap" data-reveal>
    <div id="blFeatured"></div>
    <div class="bl-grid" id="blGrid" data-reveal-group></div>
  </div>

  <!-- CTA -->
  <div class="pd-cta" data-reveal>
    <div>
      <div class="pd-cta-h">Не нашли ответ<br>на <em>свой вопрос</em>?</div>
      <p class="pd-cta-p">Опишите задачу — инженер завода подберёт материал, нормативный документ и технологию изготовления под ваш проект.</p>
    </div>
    <a class="pd-cta-btn" href="javascript:void(0)" onclick="openRequestModal('solution')">Спросить инженера →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
