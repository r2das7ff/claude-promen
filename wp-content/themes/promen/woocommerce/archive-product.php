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

    <?php
    // Кнопка видна только на ≤900px, где сайдбар свёрнут: без неё на телефоне
    // не было ни одной ссылки на категорию — каталог превращался в плоский
    // список без навигации по разделам.
    $promen_defs      = promen_catalog_taxonomy_defs();
    $promen_cur_group = '';
    if ( $group !== '' && isset( $promen_defs[ $group ] ) ) {
    	$promen_cur_group = (string) ( $promen_defs[ $group ]['nav_name'] ?? $promen_defs[ $group ]['label'] ?? '' );
    }
    ?>
    <button type="button" class="cat-sb-toggle" id="catSbToggle" aria-expanded="false" aria-controls="catSb">
      <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M1.5 2.5h11M1.5 7h11M1.5 11.5h11" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
      <span class="cat-sb-toggle-t">Категории</span>
      <?php if ( $promen_cur_group !== '' ) : ?>
        <span class="cat-sb-toggle-cur"><?php echo esc_html( $promen_cur_group ); ?></span>
      <?php endif; ?>
      <span class="cat-sb-toggle-arr" aria-hidden="true"></span>
    </button>

    <aside class="cat-sb" id="catSb">
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
// Технический реестр + база знаний — на первой странице любого вида каталога.
//
// Прячем только на пагинации: страницы /page/N/ индексируются (canonical и
// noindex на них не выставляются), и один и тот же большой блок на 514
// страницах — настоящие дубли.
//
// Под фильтром и группой секции остаются: такие виды уже отдают
// noindex,follow + canonical на чистый URL (inc/catalog-filters.php), дублей
// там не возникает, а исчезновение половины страницы по клику на фильтр
// читается как поломка.
$promen_clean_root = ! is_paged();
if ( $promen_clean_root ) {
	include __DIR__ . '/parts/catalog-seo.php';
	include __DIR__ . '/parts/catalog-kb.php';
}

get_footer();
