<?php
/**
 * Каталог: design-reference/katalog.html
 * Сайдбар фильтрует общую таблицу (?group=), «Страница категории →»
 * ведёт на страница категории активной группы.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$crumbs  = promen_breadcrumbs();
$group   = promen_catalog_active_group();
$catalog = promen_catalog_page_result();
$total   = (int) $catalog->total;

$is_fastener_ui = promen_is_fastener_group( $group );
$is_branch_ui   = in_array( (string) $group, [ 'troyniki', 'perekhody', 'tochenye' ], true );
$is_flange_ui   = ( $group && strpos( (string) $group, 'flancy' ) === 0 );

// Колонки характеристик — СВОИ под категорию/подкатегорию (см. product-data.php).
$cat_cols = promen_catalog_columns( (string) $group );
$grid_tpl = promen_catalog_grid_template( (string) $group );

// Текущая сортировка (для кликабельной шапки).
$sort_def   = function_exists( 'promen_catalog_schema_sort' ) ? promen_catalog_schema_sort( (string) $group ) : [ 'field' => 'dn', 'dir' => 'asc' ];
$sort_raw   = isset( $_GET['sort'] ) ? explode( ':', sanitize_text_field( wp_unslash( $_GET['sort'] ) ) ) : [];
$sort_field = ( $sort_raw[0] ?? '' ) !== '' ? sanitize_key( $sort_raw[0] ) : $sort_def['field'];
$sort_dir   = ( ( $sort_raw[1] ?? '' ) === 'desc' ) ? 'desc' : ( ( ( $sort_raw[1] ?? '' ) === 'asc' ) ? 'asc' : $sort_def['dir'] );

$group_views = promen_catalog_group_views();
$view        = $group_views[ $group ] ?? $group_views[''];
$cat_term    = $view['term'];

$catalog_total_n = 0;
foreach ( promen_catalog_nav_roots() as $root_slug ) {
	$catalog_total_n += promen_catalog_nav_count( $root_slug );
}
?>
<script type="application/ld+json"><?php echo promen_breadcrumbs_schema( $crumbs ); ?></script>

<div class="pg">

  <div class="cat-hero">
    <div>
      <nav class="hero-crumb" style="margin-bottom:18px;">
        <?php foreach ( $crumbs as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow">Цифровой инженерный реестр · пилот</div>
      <h1 class="hero-h1">Каталог<br><em>продукции</em></h1>
      <p class="hero-desc">В витрине — СДТ, фланцы, крепёж, точеные детали, трубы, изоляция, опоры и арматура: <?php echo esc_html( number_format_i18n( $catalog_total_n ) ); ?> типоразмеров.</p>
    </div>
    <div class="hero-stats">
      <div class="hs"><span class="hs-v"><?php echo esc_html( number_format_i18n( $total ) ); ?></span><span class="hs-k">В реестре</span></div>
      <div class="hs"><span class="hs-v"><?php echo $is_fastener_ui ? 'M × L' : 'DN 6–1400'; ?></span><span class="hs-k">Диапазон</span></div>
      <div class="hs"><span class="hs-v">ГОСТ / ОСТ</span><span class="hs-k">Нормативная база</span></div>
    </div>
  </div>

  <div class="cat-body">

    <aside class="cat-sb">
      <?php promen_render_catalog_sidebar( (string) $group ); ?>
    </aside>

    <?php
    $promen_registry_show_cat_link = true;
    $promen_registry_with_pdp      = true;
    include __DIR__ . '/parts/catalog-registry.php';
    ?>
  </div>
</div>

<?php
// Технический реестр + база знаний — только на чистом корне каталога
// (как в katalog.html); при фильтрах/поиске/пагинации не дублируем.
$promen_clean_root = ! promen_has_filters() && ! is_paged() && promen_active_group() === '';
if ( $promen_clean_root ) {
	include __DIR__ . '/parts/catalog-seo.php';
	include __DIR__ . '/parts/catalog-kb.php';
}

get_footer();
