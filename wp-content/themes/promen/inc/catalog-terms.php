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
	$map  = promen_term_map( $tax );
	$name = $map[ $slug ]['name'] ?? $slug;
	// Нормативы всегда по-русски: термин без названия/со слагом вместо имени
	// («sto-95-127») превращаем в «СТО 95 127-2013».
	if ( 'norm' === $tax && ! preg_match( '/[А-Яа-яЁё]/u', $name ) ) {
		return promen_norm_label_from_slug( $name );
	}
	return $name;
}

/** «gost-17375-2001» → «ГОСТ 17375-2001», «sto-95-127» → «СТО 95 127-2013». */
function promen_norm_label_from_slug( string $slug ): string {
	$prefixes = [
		'gost-r-' => 'ГОСТ Р ',
		'gost-'   => 'ГОСТ ',
		'ost-'    => 'ОСТ ',
		'sto-'    => 'СТО ',
		'tu-'     => 'ТУ ',
		'seriya-' => 'Серия ',
		'np-'     => 'НП ',
	];
	foreach ( $prefixes as $p => $label ) {
		if ( str_starts_with( $slug, $p ) ) {
			return $label . substr( $slug, strlen( $p ) ); // дефисы номера сохраняются
		}
	}
	return $slug;
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

/**
 * Ключ сопоставления обозначений нормативов: «тип + номер без года».
 *
 * Реестр «Нормативной базы» и каталог записывают один документ по-разному:
 * «ГОСТ 22793-83» против «ГОСТ 22793-1983», «ОСТ 34-10-763-97» против
 * «ОСТ 34.10.763-97», «СТО ЦКТИ 321.02-2009» против «СТО 321.02». Ключ
 * снимает расхождение: год отбрасывается, разделители сводятся к точке,
 * приписка «ЦКТИ» и хвост в скобках («(часть III)») убираются.
 *
 * Тот же ключ считает assets/js/nb.js — при правке править обе стороны.
 */
function promen_norm_match_key( string $label ): string {
	$s = mb_strtoupper( trim( $label ), 'UTF-8' );
	$s = str_replace( 'Ё', 'Е', $s );
	$s = preg_replace( '/\([^)]*\)/u', ' ', $s );
	$s = preg_replace( '/ЦКТИ/u', ' ', (string) $s );
	$s = trim( (string) preg_replace( '/\s+/u', ' ', (string) $s ) );

	if ( ! preg_match( '/^(ГОСТ Р|ГОСТ|ОСТ|СТО|ТУ|СЕРИЯ|НП|ПБ|ПНАЭ|АТК|РД|СП|СНИП)\s*(.+)$/u', $s, $m ) ) {
		return '';
	}
	$num = promen_norm_number_key( $m[2] );
	return '' === $num ? '' : str_replace( ' ', '', $m[1] ) . ' ' . $num;
}

/**
 * Номер норматива без года: «34-10-763-97» → «34.10.763».
 *
 * Год отсекается только за дефисом, слэшем или пробелом: у «СТО 321.01»
 * «.01» — часть номера документа, а не год, и точка его защищает.
 */
function promen_norm_number_key( string $num ): string {
	$num = preg_replace( '/[-–—\/\s](?:19\d{2}|20\d{2}|\d{2})$/u', '', trim( $num ) );
	$num = preg_replace( '/[^0-9A-ZА-Я]+/u', '.', mb_strtoupper( (string) $num, 'UTF-8' ) );
	return trim( (string) $num, '.' );
}
