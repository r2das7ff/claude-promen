<?php
/** Подкатегории изоляции: «Трубы в ППУ» и «Тройники ППУ» (2026-07-27). */
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

$parent = get_term_by( 'slug', 'izolyatsiya', 'product_cat' );
if ( ! $parent ) { echo "нет термина izolyatsiya\n"; return; }

$kids = [
	'izolyatsiya-truby'    => 'Трубы в ППУ',
	'izolyatsiya-troyniki' => 'Тройники ППУ',
];
$ids = [];
foreach ( $kids as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term ) {
		$r = wp_insert_term( $name, 'product_cat', [ 'slug' => $slug, 'parent' => (int) $parent->term_id ] );
		if ( is_wp_error( $r ) ) { echo "ошибка терма {$slug}: " . $r->get_error_message() . "\n"; return; }
		$ids[ $slug ] = (int) $r['term_id'];
		echo "создан терм {$slug} (#{$ids[$slug]})\n";
	} else {
		$ids[ $slug ] = (int) $term->term_id;
		echo "терм {$slug} уже есть (#{$ids[$slug]})\n";
	}
}

$rows = $wpdb->get_results( "SELECT product_id, JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) ti FROM {$t} WHERE category IN ('izolyatsiya','izolyatsiya-truby','izolyatsiya-troyniki')", ARRAY_A );
echo 'товаров к распределению: ' . count( $rows ) . "\n";
$n = [ 'izolyatsiya-truby' => 0, 'izolyatsiya-troyniki' => 0 ];
foreach ( $rows as $r ) {
	$pid  = (int) $r['product_id'];
	$slug = ( 0 === mb_strpos( (string) $r['ti'], 'Труба' ) ) ? 'izolyatsiya-truby' : 'izolyatsiya-troyniki';
	wp_set_object_terms( $pid, [ $ids[ $slug ] ], 'product_cat', false );
	promen_catalog_upsert( $pid );
	$n[ $slug ]++;
	if ( 0 === ( $n['izolyatsiya-truby'] + $n['izolyatsiya-troyniki'] ) % 100 ) { echo "…\n"; }
}
echo "трубы: {$n['izolyatsiya-truby']} | тройники: {$n['izolyatsiya-troyniki']}\n";

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient%promen_series%'" );
promen_filters_cache_bump();
flush_rewrite_rules();

echo "\n=== Проверка ===\n";
echo 'изоляция: ', promen_catalog_group_count( 'izolyatsiya' ),
	' | трубы ППУ: ', promen_catalog_group_count( 'izolyatsiya-truby' ),
	' | тройники ППУ: ', promen_catalog_group_count( 'izolyatsiya-troyniki' ), "\n";
$c = promen_izol_type_counts();
echo "type_counts: трубы={$c['truby']} ПЭ={$c['pe']} ОЦ={$c['oc']}\n";
$rec = promen_catalog_search_reconcile();
echo "drift: {$rec['drift']}\n";
$pid = (int) $wpdb->get_var( "SELECT product_id FROM {$t} WHERE category='izolyatsiya-truby' LIMIT 1" );
echo "URL трубы: ", get_permalink( $pid ), "\n";
$pid2 = (int) $wpdb->get_var( "SELECT product_id FROM {$t} WHERE category='izolyatsiya-troyniki' LIMIT 1" );
echo "URL тройника: ", get_permalink( $pid2 ), "\n";
