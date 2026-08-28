<?php
/**
 * Plugin Name: PROM-EN Structure
 * Description: Таксономия нормативов, транслит-слаги, загрузка WP-CLI команд проекта.
 */

defined( 'ABSPATH' ) || exit;

/** Таксономия нормативов: страница термина = страница ГОСТ/ОСТ/СТО. */
add_action( 'init', function () {
	register_taxonomy( 'norm', 'product', [
		'labels'            => [
			'name'          => 'Нормативы',
			'singular_name' => 'Норматив',
		],
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'normativy', 'with_front' => false ],
	] );

	register_taxonomy( 'promen_industry', 'product', [
		'labels'            => [
			'name'          => 'Отрасли',
			'singular_name' => 'Отрасль',
		],
		'public'            => false,
		'hierarchical'      => false,
		'show_ui'           => false,
		'show_admin_column' => false,
		'show_in_rest'      => false,
	] );

	if ( function_exists( 'promen_ensure_industry_terms' ) ) {
		promen_ensure_industry_terms();
	}
} );

/**
 * Транслитерация для слагов (кириллица → латиница).
 * Единая точка: её используют импорт товаров, термины атрибутов и категорий.
 */
function promen_translit( string $text ): string {
	static $map = [
		'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
		'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
		'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c',
		'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu',
		'я'=>'ya',
	];
	$text = mb_strtolower( $text, 'UTF-8' );
	$text = strtr( $text, $map );
	$text = preg_replace( '/[^a-z0-9]+/', '-', $text );
	return trim( preg_replace( '/-+/', '-', $text ), '-' );
}

/**
 * URL-схема: /catalog/<кат>/<подкат>/<товар>/ и /catalog/<кат>/<подкат>/ живут
 * на одной базе. Однa группа правил на категории верхнего уровня + резолвер
 * неоднозначных путей: последний сегмент — товар или подкатегория?
 */
// wc_fix_rewrite_rules() удаляет category-правила на общей базе «catalog» —
// возвращаем их ПОСЛЕ чистки Woo (приоритет 20 > 10). «page» исключаем:
// это пагинация архива магазина.
add_filter( 'rewrite_rules_array', function ( array $rules ): array {
	return [
		'catalog/(?!page/)([^/]+)/?$'                 => 'index.php?product_cat=$matches[1]',
		'catalog/(?!page/)([^/]+)/page/([0-9]+)/?$'   => 'index.php?product_cat=$matches[1]&paged=$matches[2]',
	] + $rules;
}, 20 );

add_filter( 'request', function ( array $qv ): array {
	// /catalog/a/b(/…)/ разобран как товар b в категории a. Если товара с таким
	// слагом нет, а термин есть — это страница подкатегории.
	if ( ! empty( $qv['product'] ) && ! empty( $qv['product_cat'] ) ) {
		global $wpdb;
		$product_exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type IN ('product','product_variation') LIMIT 1",
			$qv['product']
		) );
		if ( ! $product_exists && get_term_by( 'slug', $qv['product'], 'product_cat' ) ) {
			$new = [ 'product_cat' => $qv['product'] ];
			if ( ! empty( $qv['paged'] ) ) {
				$new['paged'] = $qv['paged'];
			}
			return $new;
		}
	}
	return $qv;
} );

/**
 * Описания терминов — это полноценный контент категорий (таблицы, заголовки).
 * Штатный kses-фильтр вырезает блочный HTML — отключаем; контент пишет
 * только агент через WP-CLI, пользовательского ввода тут нет.
 */
remove_filter( 'pre_term_description', 'wp_filter_kses' );

/**
 * Страницы СЕРИЙ (дизайн-карточка product-otvod-90.html — это серия:
 * «Отвод крутоизогнутый 90°» с конфигуратором DN).
 * URL: /catalog/sdt/otvody/seriya/<norm-slug>-<angle>/
 * Рендерится репрезентативный товар серии тем же шаблоном single-product,
 * в режиме серии (H1 без типоразмера, canonical на серию).
 */
add_filter( 'query_vars', function ( array $vars ): array {
	$vars[] = 'promen_series';
	$vars[] = 'promen_series_cat';
	return $vars;
} );

add_filter( 'rewrite_rules_array', function ( array $rules ): array {
	return [
		// Серии живут под любой категорией: /catalog/<путь-категории>/seriya/<слаг>/
		// Путь категории захватываем: без него репрезентативный товар искался
		// по одному нормативу, и /flancy-01/seriya/gost-33259-2015/ отдавал
		// страницу типа 11 — тот же норматив, другая подкатегория.
		'catalog/(.+?)/seriya/([^/]+)/?$' => 'index.php?promen_series_cat=$matches[1]&promen_series=$matches[2]',
	] + $rules;
}, 21 );

add_filter( 'request', function ( array $qv ): array {
	if ( empty( $qv['promen_series'] ) ) {
		return $qv;
	}
	$slug = sanitize_title( $qv['promen_series'] );
	// Из пути «flancy/flancy-01» берём последний сегмент — самую глубокую
	// категорию: серия принадлежит именно ей.
	$path = (string) ( $qv['promen_series_cat'] ?? '' );
	$segs = array_values( array_filter( explode( '/', $path ) ) );
	$cat  = $segs ? sanitize_title( end( $segs ) ) : '';

	$rep = function_exists( 'promen_series_representative' ) ? promen_series_representative( $slug, $cat ) : 0;
	if ( ! $rep ) {
		return $qv; // 404 штатно: серии с таким нормативом в этой категории нет
	}
	return [
		'promen_series'     => $slug,
		'promen_series_cat' => $cat,
		'post_type'         => 'product',
		'p'                 => $rep,
	];
}, 20 );

/** WP-CLI команды проекта лежат в /scripts (примонтировано только в wpcli-контейнер). */
if ( defined( 'WP_CLI' ) && WP_CLI && file_exists( '/scripts/promen-cli.php' ) ) {
	require_once '/scripts/promen-cli.php';
}
