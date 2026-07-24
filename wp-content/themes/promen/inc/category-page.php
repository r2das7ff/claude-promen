<?php
/**
 * Общий каркас страницы категории каталога.
 *
 * До рефакторинга каждый taxonomy-шаблон нёс полную копию каркаса
 * (~400–1200 строк, 18 файлов, ~85% дублирования). Теперь:
 *   - каркас и порядок секций — здесь;
 *   - статичные секции s06–s09 — woocommerce/parts/category/*.php;
 *   - уникальный контент (hero, s02, s03, s10, модалка) —
 *     конфиги inc/category-content/<slug>.php (возвращают массив колбэков);
 *   - taxonomy-product_cat-<slug>.php — тонкая обёртка в 3 строки
 *     (сам файл нужен: по нему WP выбирает шаблон категории).
 *
 * Каждый колбэк получает $ctx:
 *   slug, count (позиций в группе), url (страница категории),
 *   shop_url, crumbs, parent_name (родительская группа, '' если корневая).
 */

defined( 'ABSPATH' ) || exit;

/** Контекст, доступный контент-колбэкам категории. */
function promen_category_page_ctx( string $slug ): array {
	$defs   = function_exists( 'promen_catalog_taxonomy_defs' ) ? promen_catalog_taxonomy_defs() : [];
	$parent = (string) ( $defs[ $slug ]['parent'] ?? '' );

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );

	return [
		'slug'        => $slug,
		'count'       => function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( $slug ) : 0,
		'url'         => promen_product_cat_link( $slug ) ?: $shop_url,
		'shop_url'    => $shop_url,
		'crumbs'      => function_exists( 'promen_breadcrumbs' ) ? promen_breadcrumbs() : [],
		'parent_name' => $parent !== '' ? promen_term_label( 'product_cat', $parent ) : '',
	];
}

/**
 * Отрисовать страницу категории по конфигу inc/category-content/<slug>.php.
 */
function promen_render_category_page( string $slug ): void {
	$file = get_theme_file_path( 'inc/category-content/' . $slug . '.php' );
	if ( ! file_exists( $file ) ) {
		// Конфиг не подключён — честная 404 вместо белой страницы.
		status_header( 404 );
		get_header();
		echo '<div class="pg"><p style="padding:80px 40px;">Раздел в наполнении.</p></div>';
		get_footer();
		return;
	}
	$config = require $file;
	$ctx    = promen_category_page_ctx( $slug );
	$parts  = static function ( string $name ) {
		return get_theme_file_path( 'woocommerce/parts/category/' . $name . '.php' );
	};

	get_header();
	?>
<script type="application/ld+json"><?php echo promen_breadcrumbs_schema( $ctx['crumbs'] ); ?></script>

	<?php
	if ( isset( $config['sidenav'] ) ) {
		$config['sidenav']( $ctx );
	} else {
		include $parts( 'sidenav' );
	}
	?>

<div class="pg">

	<?php
	$config['hero']( $ctx );

	if ( isset( $config['series_custom'] ) ) {
		$config['series_custom']( $ctx );
	} elseif ( $config['series'] ?? true ) {
		promen_render_category_series_registry( $slug );
	}

	promen_render_category_catalog_embed( $slug, (int) $ctx['count'] );

	if ( isset( $config['s02'] ) ) {
		$config['s02']( $ctx );
	}
	if ( isset( $config['s03'] ) ) {
		$config['s03']( $ctx );
	}

	promen_render_category_norms_section( $slug );
	promen_render_materials_section( $slug );

	include $parts( 's06-applications' );
	include $parts( 's07-qc' );

	$promen_s08_weld = $config['s08_weld'] ?? '';
	include $parts( 's08-production' );

	if ( isset( $config['s09'] ) ) {
		$config['s09']( $ctx );
	} else {
		include $parts( 's09-ordering' );
	}

	if ( isset( $config['s10'] ) ) {
		$config['s10']( $ctx );
	}
	if ( isset( $config['after'] ) ) {
		$config['after']( $ctx );
	}
	?>

</div><!-- /.pg -->

	<?php
	if ( isset( $config['modal'] ) ) {
		$config['modal']( $ctx );
	}
	get_footer();
}
