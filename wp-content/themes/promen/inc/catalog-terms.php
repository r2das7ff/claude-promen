<?php
/**
 * Карты терминов каталога: slug → name/id/parent/url одним запросом на таксономию.
 *
 * Лечит «терм-шторм»: построчные get_term_by()/get_term_link() на каждую опцию
 * фасета, строку реестра и пункт сайдбара давали сотни SQL на страницу.
 * Карта строится одним get_terms(), живёт в static на запрос и в transient
 * между запросами (ключ версионирован promen_filters_cache_version —
 * инвалидация та же, что у остальных кэшей фильтров).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Карта терминов таксономии: [ slug => ['id','name','parent'] ].
 */
function promen_term_map( string $tax ): array {
	static $maps = [];
	if ( isset( $maps[ $tax ] ) ) {
		return $maps[ $tax ];
	}

	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'term_map', [ $tax ] )
		: 'promen_term_map_' . md5( $tax );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $maps[ $tax ] = $cached;
	}

	$out   = [];
	$terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => false, 'number' => 0 ] );
	foreach ( is_wp_error( $terms ) ? [] : $terms as $t ) {
		$out[ $t->slug ] = [
			'id'     => (int) $t->term_id,
			'name'   => $t->name,
			'parent' => (int) $t->parent,
		];
	}
	set_transient( $ckey, $out, 15 * MINUTE_IN_SECONDS );
	return $maps[ $tax ] = $out;
}

/** Человекочитаемое имя термина по слагу (фолбэк — сам слаг). */
function promen_term_label( string $tax, string $slug ): string {
	$map = promen_term_map( $tax );
	return $map[ $slug ]['name'] ?? $slug;
}

/**
 * URL страниц категорий каталога: [ slug => url ].
 * get_term_link() на иерархической таксономии дорог (цепочка предков) —
 * считаем один раз и держим в transient.
 */
function promen_product_cat_links(): array {
	static $links = null;
	if ( null !== $links ) {
		return $links;
	}

	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'cat_links' )
		: 'promen_cat_links';
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $links = $cached;
	}

	$links = [];
	foreach ( promen_term_map( 'product_cat' ) as $slug => $d ) {
		$url = get_term_link( (int) $d['id'], 'product_cat' );
		if ( ! is_wp_error( $url ) ) {
			$links[ $slug ] = (string) $url;
		}
	}
	set_transient( $ckey, $links, 15 * MINUTE_IN_SECONDS );
	return $links;
}

/** URL страницы категории по слагу ('' — категории нет). */
function promen_product_cat_link( string $slug ): string {
	return promen_product_cat_links()[ $slug ] ?? '';
}
