<?php
/** Разово: заглушки/тройники ОСТ 34.10.428 с «стенкой» >= 50 — это высота, не стенка. */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }
global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';
$pids = $wpdb->get_col( "SELECT product_id FROM {$t} WHERE norm_slug LIKE 'ost-34-10-428%' AND s >= 50" );
foreach ( $pids as $pid ) {
	$pid  = (int) $pid;
	$dims = get_post_meta( $pid, '_promen_dims', true );
	$d    = is_array( $dims ) ? $dims : json_decode( (string) $dims, true );
	if ( ! is_array( $d ) || ! isset( $d['wall_thickness'] ) ) { continue; }
	$was = $d['wall_thickness'];
	unset( $d['wall_thickness'] );
	update_post_meta( $pid, '_promen_dims', is_array( $dims ) ? $d : wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
	promen_catalog_upsert( $pid );
	echo "#{$pid}: s={$was} убрана | " . get_the_title( $pid ) . "\n";
}
promen_filters_cache_bump();
echo "готово\n";
