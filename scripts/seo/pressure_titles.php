<?php
/**
 * Давление в названиях товаров: «PN 13.73» → «13,73 МПа» — 30.09.2026.
 *
 * Импорт вписал в названия давление в МПа с подписью PN: «Отвод гнутый …
 * PN 13.73 12Х1МФ СТО 321.05-2009» (рабочее давление, 13,73 МПа), «Колено …
 * PN 32 ГОСТ 22818-1983» (32 МПа — стандарт на Ру до 100 МПа). PN — это
 * номинальный класс в кгс/см², так что «PN 13.73» занижает давление вдесятеро.
 *
 * Правим только названия, где число после PN совпадает с pn товара в МПа.
 * Настоящий PN не трогаем: «PN2,5» у фланцев ОСТ 34-10-425 (0,25 МПа) и
 * «PN16» у тройников СТО 95 126 (1,6 МПа) — это номинальный класс, он в десять
 * раз больше МПа. Номинальные фланцы (promen_pressure_is_pn) не трогаем вовсе.
 *
 * Второй проход — канон каталога у фланцев с давлением в МПа: их подпись
 * размера теперь «DN800 0,25 МПа», а в строке каталога лежала «PN0.25».
 *
 * Слаги не меняются. Запуск через include-обёртку ($args = [] / ['apply']).
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

global $wpdb;

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );

$rows = $wpdb->get_results( "SELECT p.ID, p.post_title, m.meta_value FROM {$wpdb->posts} p
	JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_promen_dims'
	WHERE p.post_type = 'product' AND p.post_status = 'publish' AND m.meta_value LIKE '%\"pn\"%'" );

$titles = [];  // id => [ old, new ]
$canon  = [];  // id => true
$skip   = [];
foreach ( $rows as $r ) {
	$id   = (int) $r->ID;
	$dims = json_decode( (string) $r->meta_value, true ) ?: [];
	$pn   = trim( (string) ( $dims['pn'] ?? '' ) );
	if ( '' === $pn || ! is_numeric( $pn ) || promen_pressure_is_pn( $id ) ) {
		continue;
	}
	if ( promen_dims_look_like_flange( $dims ) ) {
		$canon[ $id ] = true;
	}
	if ( ! preg_match( '/\bPN\s*(\d+(?:[.,]\d+)?)/u', $r->post_title, $m ) ) {
		continue;
	}
	$v = (float) str_replace( ',', '.', $m[1] );
	if ( abs( $v - (float) $pn ) > 1e-6 ) {
		$skip[ $m[0] ] = ( $skip[ $m[0] ] ?? 0 ) + 1; // настоящий PN (×10) или расхождение — не трогаем
		continue;
	}
	$label = str_replace( '.', ',', promen_fmt_dim( $pn ) ) . ' МПа';
	$titles[ $id ] = [ $r->post_title, preg_replace( '/\bPN\s*' . preg_quote( $m[1], '/' ) . '(?![\d.,])(?:\s*МПа)?/u', $label, $r->post_title, 1 ) ];
	$canon[ $id ]  = true;
}

$by_norm = [];
foreach ( array_keys( $titles ) as $id ) {
	$t = wp_get_post_terms( $id, 'norm', [ 'fields' => 'names' ] );
	$by_norm[ $t[0] ?? '—' ][] = $id;
}
WP_CLI::log( sprintf( 'Названий к правке: %d; строк канона к пересборке: %d', count( $titles ), count( $canon ) ) );
foreach ( $by_norm as $norm => $ids ) {
	WP_CLI::log( sprintf( '  %-32s %4d  «%s» → «%s»', $norm, count( $ids ), $titles[ $ids[0] ][0], $titles[ $ids[0] ][1] ) );
}
WP_CLI::log( 'Не тронуты (PN не равен МПа): ' . wp_json_encode( array_slice( $skip, 0, 8, true ), JSON_UNESCAPED_UNICODE ) );

if ( ! $apply ) {
	WP_CLI::log( 'Отчёт. Для правки: $args = [ "apply" ]' );
	return;
}

$snap = rtrim( (string) getenv( 'HOME' ), '/' ) . '/pressure-titles-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $snap, wp_json_encode( array_map( static fn( $x ) => $x[0], $titles ), JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок названий: ' . $snap );

foreach ( $titles as $id => list( $old, $new ) ) {
	// Прямой UPDATE: wp_update_post пересобрал бы слаг и дёрнул save_post у Woo.
	$wpdb->update( $wpdb->posts, [ 'post_title' => $new ], [ 'ID' => $id ] );
	foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'product_variation'", $id ) ) as $v ) {
		if ( 0 === strpos( $v->post_title, $old ) ) {
			$wpdb->update( $wpdb->posts, [ 'post_title' => $new . substr( $v->post_title, strlen( $old ) ) ], [ 'ID' => (int) $v->ID ] );
			clean_post_cache( (int) $v->ID );
		}
	}
	clean_post_cache( $id );
}

$done = 0;
foreach ( array_keys( $canon ) as $id ) {
	promen_catalog_upsert( $id, false );
	if ( 0 === ++$done % 200 ) {
		wp_cache_flush(); // память на шареде
		WP_CLI::log( "  канон … {$done}" );
	}
}
wp_cache_flush();
if ( function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
WP_CLI::success( sprintf( 'Названий исправлено: %d, строк канона пересобрано: %d. Дальше: wp promen cache-purge.', count( $titles ), $done ) );
