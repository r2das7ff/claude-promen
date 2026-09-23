<?php
/**
 * Днища ГОСТ 6533-78: D и DN по таблице стандарта.
 *
 * 1. «Днище эллиптическое D137 …» (25 позиций) — это днища D = 550 мм.
 *    В таблице ГОСТ 6533 строка «(550) 25 137 …»: 137 — высота выпуклой части
 *    hв = 0,25·D, импорт положил её в диаметр. Других днищ D = 550 в каталоге
 *    нет, так что это не двойники, а та же серия с неверным числом.
 * 2. D 1500 (4 позиции) — диаметр верный, но DN пустой: пересчёт идёт через
 *    таблицу наружных диаметров труб, а 1 500 — внутренний диаметр аппарата,
 *    в той таблице его нет.
 * 3. D 1400 (5 позиций) — DN = 10, мусор источника.
 *
 * Для днищ по внутреннему диаметру DN = D — так уже стоит у D 500, 600, 1600.
 *
 * Запуск:  wp eval-file scripts/dnishcha-6533-fix/fix.php dry|apply
 */

$mode = ( isset( $args[0] ) && 'apply' === $args[0] ) ? 'apply' : 'dry';

$ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => '_promen_norm_key',
		'meta_value'     => 'ГОСТ 6533-1978',
	]
);

$moves = [];
$stat  = [ 'd137' => 0, 'dn_fix' => 0 ];
foreach ( $ids as $id ) {
	$dims = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
	$od   = (string) ( $dims['outer_diameter'] ?? '' );
	$dn   = (string) ( $dims['dn'] ?? '' );

	$new_d  = null;
	$new_dn = null;
	if ( '137' === $od ) {
		$new_d  = '550';
		$new_dn = '550';
	} elseif ( in_array( $od, [ '1400', '1500' ], true ) && $dn !== $od ) {
		$new_dn = $od;
	}
	if ( null === $new_d && null === $new_dn ) {
		continue;
	}

	$post    = get_post( $id );
	$url_old = (string) get_permalink( $id );

	if ( null !== $new_d ) {
		$stat['d137']++;
		$title_new = str_replace( 'D137', 'D' . $new_d, $post->post_title );
		$slug_new  = str_replace( '-d137-', '-d' . $new_d . '-', $post->post_name );
		if ( $stat['d137'] <= 2 ) {
			printf( "  %s → %s\n    %s → %s\n", $post->post_title, $title_new, $post->post_name, $slug_new );
		}
	} else {
		$stat['dn_fix']++;
		$title_new = $post->post_title;
		$slug_new  = $post->post_name;
		if ( $stat['dn_fix'] <= 2 ) {
			printf( "  %s: DN %s → %s\n", $post->post_title, $dn !== '' ? $dn : '—', $new_dn );
		}
	}

	if ( 'apply' === $mode ) {
		if ( null !== $new_d ) {
			$dims['outer_diameter'] = $new_d;
		}
		$dims['dn'] = $new_dn;
		update_post_meta( $id, '_promen_dims', wp_json_encode( $dims, JSON_UNESCAPED_UNICODE ) );
		if ( $title_new !== $post->post_title || $slug_new !== $post->post_name ) {
			wp_update_post( [ 'ID' => $id, 'post_title' => $title_new, 'post_name' => $slug_new ] );
		}
		clean_post_cache( $id );
		$url_new = (string) get_permalink( $id );
		promen_catalog_upsert( $id, false );
	} else {
		$url_new = str_replace( '/' . $post->post_name . '/', '/' . $slug_new . '/', $url_old );
	}
	if ( $url_new !== $url_old ) {
		$moves[] = wp_parse_url( $url_old, PHP_URL_PATH ) . "\t" . wp_parse_url( $url_new, PHP_URL_PATH );
	}
}

file_put_contents( __DIR__ . '/moves.tsv', $moves ? implode( "\n", $moves ) . "\n" : '' );
printf( "\nРежим: %s. D137 → D550: %d, DN исправлен: %d, переездов: %d\n", $mode, $stat['d137'], $stat['dn_fix'], count( $moves ) );
if ( 'apply' === $mode && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
