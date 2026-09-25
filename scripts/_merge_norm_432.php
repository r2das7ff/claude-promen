<?php
/**
 * Склейка двух терминов norm на один документ ОСТ 34.10.432-90.
 *
 * В базе два термина с одинаковым именем «ОСТ 34-10-432-90» и разными
 * слагами — ost-34-10-432-90 (716) и ost-34-10-432-1990 (722), по пять
 * тройников в каждом. Типоразмеры совпадают попарно: 14х2, 18х2.5, 25х3,
 * 32х2.5, 38х3.
 *
 * Верным признан слаг с двузначным годом: реестр
 * normatives/registry/normatives_master.csv записывает семейство как
 * «ОСТ 34-10-416-90 ÷ ОСТ 34-10-433-90 (часть 1)», и у выжившего термина
 * карточки полнее — с исполнением (01–05) и маркой 08Х18Н10Т, что сходится
 * с описанием документа: детали из коррозионностойкой стали для АС.
 *
 * Дубли сносим с 301 на выжившего (карта promen_dedup_redirects), адреса
 * самого норматива и серии переезжают через promen_norm_slug_redirects().
 *
 * Запуск: wp eval-file scripts/_merge_norm_432.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

const KEEP_SLUG = 'ost-34-10-432-90';
const DROP_SLUG = 'ost-34-10-432-1990';

$keep = get_term_by( 'slug', KEEP_SLUG, 'norm' );
$drop = get_term_by( 'slug', DROP_SLUG, 'norm' );

if ( ! $keep || ! $drop ) {
	WP_CLI::error( 'Термины не найдены: ' . ( $keep ? '' : KEEP_SLUG . ' ' ) . ( $drop ? '' : DROP_SLUG ) );
}

/** Ключ типоразмера из названия: «Тройник 18х2.5 …» → «18х2.5». */
$size_of = static function ( int $id ): string {
	$title = (string) get_the_title( $id );
	return preg_match( '/(\d+(?:[.,]\d+)?х\d+(?:[.,]\d+)?)/u', $title, $m )
		? str_replace( ',', '.', $m[1] )
		: '';
};

$keep_by_size = [];
foreach ( (array) get_objects_in_term( [ $keep->term_id ], 'norm' ) as $id ) {
	$s = $size_of( (int) $id );
	if ( '' !== $s ) {
		$keep_by_size[ $s ] = (int) $id;
	}
}
WP_CLI::log( 'Выживающие типоразмеры: ' . implode( ', ', array_keys( $keep_by_size ) ) );

$redirects = get_option( 'promen_dedup_redirects', [] );
if ( ! is_array( $redirects ) ) {
	$redirects = [];
}

$deleted = 0;
$skipped = [];
foreach ( (array) get_objects_in_term( [ $drop->term_id ], 'norm' ) as $id ) {
	$id   = (int) $id;
	$size = $size_of( $id );
	$target = $keep_by_size[ $size ] ?? 0;
	if ( ! $target ) {
		$skipped[] = $id . ' (' . get_the_title( $id ) . ')';
		continue;
	}

	$slug = (string) get_post_field( 'post_name', $id );
	if ( '' !== $slug ) {
		$redirects[ $slug ] = $target;
	}

	$product = wc_get_product( $id );
	if ( $product ) {
		foreach ( $product->get_children() as $vid ) {
			wp_delete_post( (int) $vid, true );
		}
		$product->delete( true );
	} else {
		wp_delete_post( $id, true );
	}
	if ( function_exists( 'promen_catalog_delete' ) ) {
		promen_catalog_delete( $id );
	}
	WP_CLI::log( sprintf( '  %d (%s) → %d', $id, $size, $target ) );
	$deleted++;
}

if ( $skipped ) {
	WP_CLI::warning( 'Без пары, НЕ удалены: ' . implode( '; ', $skipped ) );
}

update_option( 'promen_dedup_redirects', $redirects, false );

// Термин сносим только когда за ним ничего не осталось.
$left = (array) get_objects_in_term( [ $drop->term_id ], 'norm' );
if ( $left ) {
	WP_CLI::warning( 'В термине ' . DROP_SLUG . ' осталось объектов: ' . count( $left ) . ' — термин не удалён' );
} else {
	wp_delete_term( $drop->term_id, 'norm' );
	WP_CLI::log( 'Термин ' . DROP_SLUG . ' удалён' );
}

delete_transient( 'promen_series_sitemap' );

WP_CLI::success( sprintf( 'Удалено карточек: %d. Карта редиректов: %d записей.', $deleted, count( $redirects ) ) );
