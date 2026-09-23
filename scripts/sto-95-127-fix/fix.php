<?php
/**
 * Два тройника СТО 95 127-2013 без DN: размеры по таблице 1 стандарта.
 *
 * Обозначение в стандарте — «DN × DN1», и импорт положил DN в наружный
 * диаметр, а DN1 — в стенку:
 *   «Тройник 100×25-32×2» → строка 102: DN 100 × 25, трубы 108×4,0 и 32×2,0;
 *   «Тройник 150×25-25»   → DN 150 × 20, трубы 159 и 25×2,0. Строк с 150 × 20
 *     две (138: 159×5,0 при PN 40; 158 — другая стенка), давления у позиции
 *     нет — стенку основной трубы не угадываем, оставляем пустой.
 * Источник: СТО 95 127-2013, табл. 1, стр. 16 и 20 (files.stroyinf.ru,
 * 4293726855.pdf). Названия и адреса не меняются.
 *
 * Запуск:  wp eval-file scripts/sto-95-127-fix/fix.php dry|apply
 */

$mode = ( isset( $args[0] ) && 'apply' === $args[0] ) ? 'apply' : 'dry';

$rows = [
	'sto-95-127-2--100-25--32-1-08х18н10т' => [ 'dn' => '100', 'outer_diameter' => '108', 'wall_thickness' => '4', 'dn_branch' => '25', 'outer_d_branch' => '32', 'wall_branch' => '2' ],
	'sto-95-127-2--150-25--25-1-08х18н10т' => [ 'dn' => '150', 'outer_diameter' => '159', 'wall_thickness' => null, 'dn_branch' => '20', 'outer_d_branch' => '25', 'wall_branch' => '2' ],
];

global $wpdb;
foreach ( $rows as $sku => $set ) {
	$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s LIMIT 1", $sku ) );
	if ( ! $id ) {
		echo "  нет товара с артикулом {$sku}\n";
		continue;
	}
	$dims = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
	$was  = sprintf( 'DN %s, %s×%s, штуцер %s×%s (DN %s)', $dims['dn'] ?? '—', $dims['outer_diameter'] ?? '—', $dims['wall_thickness'] ?? '—', $dims['outer_d_branch'] ?? '—', $dims['wall_branch'] ?? '—', $dims['dn_branch'] ?? '—' );
	foreach ( $set as $k => $v ) {
		if ( null === $v ) {
			unset( $dims[ $k ] );
		} else {
			$dims[ $k ] = $v;
		}
	}
	$now = sprintf( 'DN %s, %s×%s, штуцер %s×%s (DN %s)', $dims['dn'] ?? '—', $dims['outer_diameter'] ?? '—', $dims['wall_thickness'] ?? '—', $dims['outer_d_branch'] ?? '—', $dims['wall_branch'] ?? '—', $dims['dn_branch'] ?? '—' );
	printf( "  %d %s\n    было:  %s\n    стало: %s\n", $id, $sku, $was, $now );
	if ( 'apply' === $mode ) {
		update_post_meta( $id, '_promen_dims', wp_slash( wp_json_encode( $dims, JSON_UNESCAPED_UNICODE ) ) );
		clean_post_cache( $id );
		promen_catalog_upsert( $id, false );
	}
}
if ( 'apply' === $mode && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
printf( "Режим: %s\n", $mode );
