<?php
/** Откат dims.pn фланцев к номиналу (×10) — конвертация в МПа теперь в document builder. */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }
global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';
$norms = [ 'gost-33259-2015', 'gost-12820-1980', 'gost-12821-1980', 'gost-28759-2-2022' ];
$ph = implode( ',', array_fill( 0, count( $norms ), '%s' ) );
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$pids = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$t} WHERE category LIKE 'flancy%%' AND norm_slug IN ({$ph})", $norms ) );
$n = 0;
foreach ( $pids as $pid ) {
	$pid = (int) $pid;
	$raw = get_post_meta( $pid, '_promen_dims', true );
	$d   = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $d ) || ! isset( $d['pn'] ) || ! is_numeric( $d['pn'] ) ) { continue; }
	$d['pn'] = (float) rtrim( rtrim( number_format( (float) $d['pn'] * 10, 2, '.', '' ), '0' ), '.' );
	update_post_meta( $pid, '_promen_dims', is_array( $raw ) ? $d : wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
	promen_catalog_upsert( $pid ); // документ теперь делит для канона сам
	$n++;
}
promen_filters_cache_bump();
echo "восстановлен номинал у {$n} фланцев (канон при этом в МПа)\n";
