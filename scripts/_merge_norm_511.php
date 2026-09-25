<?php
/**
 * Склейка двух терминов norm на один документ ОСТ 34.10.511-90.
 *
 * В базе два термина с одинаковым именем «ОСТ 34-10-511-90»:
 * ost-34-10-511-90 (717, 56 тройников, импорт 22.07) и ost-34-10-511-1990
 * (723, 95 тройников, импорт 21.07). 56 названий совпадают попарно.
 *
 * Какой импорт верный — видно по данным. У старого (723) в диаметр отвода
 * записан его DN (у 57х3-32х2.5 «outer_d_branch» = 25), стенки отвода и
 * обозначения нет, а «PN» в описании подставлен из DN («PN 1200 МПа» у
 * DN 1200) — в 47 из 56 пар. Давление в размерах у старого верное там, где
 * оно есть (9 из 9 совпали). Новый (717) заполнен правильно, но оборван: нет
 * исполнения 30 (220х7-159х6) и 58–95 (630–1220 мм) — они есть только в
 * старом.
 *
 * Поэтому:
 *  - в 56 парах выживает новая карточка, старая удаляется; новая забирает
 *    её адрес без «-2» (он старше на день), а «-2» уходит в карту 301;
 *  - 39 уникальных старых карточек переезжают в термин 717, размеры отвода,
 *    обозначение и тип правятся по названию, мусорное описание снимается
 *    (у новых карточек описания нет — страница собирается из размеров);
 *  - термин 723 удаляется, его адреса ведёт promen_norm_slug_redirects().
 *
 * Запуск:
 *   wp eval-file scripts/_merge_norm_511.php          — отчёт
 *   wp eval-file scripts/_merge_norm_511.php apply    — применить
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

global $wpdb;

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );

const KEEP_SLUG = 'ost-34-10-511-90';
const DROP_SLUG = 'ost-34-10-511-1990';

$keep = get_term_by( 'slug', KEEP_SLUG, 'norm' );
$drop = get_term_by( 'slug', DROP_SLUG, 'norm' );
if ( ! $keep || ! $drop ) {
	WP_CLI::error( 'Термины не найдены: ' . ( $keep ? '' : KEEP_SLUG . ' ' ) . ( $drop ? '' : DROP_SLUG ) );
}

$keep_by_title = [];
foreach ( (array) get_objects_in_term( [ $keep->term_id ], 'norm' ) as $id ) {
	$keep_by_title[ get_the_title( (int) $id ) ] = (int) $id;
}

$pairs  = [];   // старый ID => новый ID
$extras = [];   // старый ID => новые размеры
foreach ( (array) get_objects_in_term( [ $drop->term_id ], 'norm' ) as $id ) {
	$id    = (int) $id;
	$title = get_the_title( $id );
	if ( isset( $keep_by_title[ $title ] ) ) {
		$pairs[ $id ] = $keep_by_title[ $title ];
		continue;
	}

	// «Тройник 630х8-325х12 исп. 58 …» → магистраль 630х8, отвод 325х12.
	if ( ! preg_match( '/(\d+(?:\.\d+)?)х(\d+(?:\.\d+)?)-(\d+(?:\.\d+)?)х(\d+(?:\.\d+)?)/u', $title, $m ) ) {
		WP_CLI::warning( "Размер не разобран: {$id} {$title}" );
		continue;
	}
	$old = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
	if ( (string) ( $old['outer_diameter'] ?? '' ) !== $m[1] || (string) ( $old['wall_thickness'] ?? '' ) !== $m[2] ) {
		WP_CLI::warning( "Магистраль в размерах не сходится с названием: {$id} {$title}" );
		continue;
	}
	$dn = (string) ( $old['dn'] ?? '' );
	if ( '' === $dn && '220' === $m[1] ) {
		$dn = '200'; // так же, как у исп. 25–29 в новом импорте
	}
	// Порядок ключей — как у нового импорта.
	$new = [
		'outer_diameter' => $m[1],
		'wall_thickness' => $m[2],
		'outer_d_branch' => $m[3],
		'wall_branch'    => $m[4],
		'dn_branch'      => (string) ( $old['dn_branch'] ?? '' ),
		'execution'      => (string) ( $old['execution'] ?? '' ),
		'dn'             => $dn,
		'pn'             => (string) ( $old['pn'] ?? '' ),
		'gost_designation' => "{$m[1]}х{$m[2]}-{$m[3]}х{$m[4]}",
		'material_grade' => (string) ( $old['material_grade'] ?? '' ),
		'product_type'   => 'ТР',
	];
	$extras[ $id ] = array_filter( $new, static fn( $v ) => '' !== $v );
}

WP_CLI::log( sprintf( 'Пар (старая удаляется): %d; уникальных старых (переезжают): %d', count( $pairs ), count( $extras ) ) );
$first = array_key_first( $pairs );
if ( $first ) {
	WP_CLI::log( sprintf( '  пара: %d «%s» → %d', $first, get_post_field( 'post_name', $first ), $pairs[ $first ] ) );
}
$first = array_key_first( $extras );
if ( $first ) {
	WP_CLI::log( sprintf( '  уникальная: %d %s', $first, wp_json_encode( $extras[ $first ], JSON_UNESCAPED_UNICODE ) ) );
}

if ( ! $apply ) {
	WP_CLI::log( 'Отчёт. Для правки: ... eval-file _merge_norm_511.php apply' );
	return;
}

// Слепок всего, что удаляется или меняется: строки постов, мета, связи с терминами.
$snap_ids = array_merge( array_keys( $pairs ), array_values( $pairs ), array_keys( $extras ) );
foreach ( array_keys( $pairs ) as $id ) {
	$snap_ids = array_merge( $snap_ids, array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d", $id ) ) ) );
}
$in       = implode( ',', array_map( 'intval', $snap_ids ) );
$snapshot = [
	'posts'    => $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID IN ({$in})", ARRAY_A ),
	'meta'     => $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE post_id IN ({$in})", ARRAY_A ),
	'terms'    => $wpdb->get_results( "SELECT * FROM {$wpdb->term_relationships} WHERE object_id IN ({$in})", ARRAY_A ),
	'drop_term' => (array) $drop,
	'redirects' => get_option( 'promen_dedup_redirects', [] ),
];
$snap_file = rtrim( (string) getenv( 'HOME' ), '/' ) . '/merge-norm-511-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $snap_file, wp_json_encode( $snapshot, JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок: ' . $snap_file . ' (' . size_format( (int) filesize( $snap_file ) ) . ')' );

$redirects = get_option( 'promen_dedup_redirects', [] );
if ( ! is_array( $redirects ) ) {
	$redirects = [];
}

// 1. Пары: удаляем старую, новая забирает её адрес, «-2» — в карту 301.
foreach ( $pairs as $old_id => $new_id ) {
	$old_slug = (string) get_post_field( 'post_name', $old_id );
	$new_slug = (string) get_post_field( 'post_name', $new_id );

	$product = wc_get_product( $old_id );
	if ( $product ) {
		foreach ( $product->get_children() as $vid ) {
			wp_delete_post( (int) $vid, true );
		}
		$product->delete( true );
	} else {
		wp_delete_post( $old_id, true );
	}
	if ( function_exists( 'promen_catalog_delete' ) ) {
		promen_catalog_delete( $old_id );
	}

	// Прежние склейки, что вели на удалённую карточку, — на выжившую.
	foreach ( $redirects as $k => $v ) {
		if ( (int) $v === $old_id ) {
			$redirects[ $k ] = $new_id;
		}
	}

	if ( '' !== $old_slug && $old_slug !== $new_slug ) {
		// Прямой UPDATE: wp_update_post дёрнул бы save_post у Woo.
		$wpdb->update( $wpdb->posts, [ 'post_name' => $old_slug ], [ 'ID' => $new_id ] );
		clean_post_cache( $new_id );
		$redirects[ $new_slug ] = $new_id;
		unset( $redirects[ $old_slug ] );
	}
	update_post_meta( $new_id, '_promen_norm_key', KEEP_SLUG );
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $new_id, false );
	}
}
WP_CLI::log( 'Пары склеены: ' . count( $pairs ) );

// 2. Уникальные старые: в верный термин, размеры по названию, без мусорного описания.
foreach ( $extras as $id => $dims ) {
	wp_set_object_terms( $id, [ (int) $keep->term_id ], 'norm', false );
	update_post_meta( $id, '_promen_dims', wp_json_encode( $dims, JSON_UNESCAPED_UNICODE ) );
	update_post_meta( $id, '_promen_gost_designation', $dims['gost_designation'] );
	update_post_meta( $id, '_promen_norm_key', KEEP_SLUG );
	$wpdb->update( $wpdb->posts, [ 'post_content' => '' ], [ 'ID' => $id ] );
	clean_post_cache( $id );
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $id, false );
	}
}
WP_CLI::log( 'Уникальные перенесены: ' . count( $extras ) );

update_option( 'promen_dedup_redirects', $redirects, false );

$left = (array) get_objects_in_term( [ $drop->term_id ], 'norm' );
if ( $left ) {
	WP_CLI::warning( 'В термине ' . DROP_SLUG . ' осталось объектов: ' . count( $left ) . ' — термин не удалён' );
} else {
	wp_delete_term( $drop->term_id, 'norm' );
	WP_CLI::log( 'Термин ' . DROP_SLUG . ' удалён' );
}

delete_transient( 'promen_series_sitemap' );
wp_cache_flush();

WP_CLI::success( sprintf( 'Готово. В термине %s: %d. Дальше: wp promen cache-purge.', KEEP_SLUG,
	count( (array) get_objects_in_term( [ $keep->term_id ], 'norm' ) ) ) );
