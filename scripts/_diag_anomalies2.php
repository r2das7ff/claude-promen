<?php
/** Диагностика #2: годы в pa_pn, стенка серии ОСТ 34.10.428. */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }

global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

echo "=== pa_pn термы 200..2600 (кандидаты в годы/склейки) ===\n";
foreach ( get_terms( [ 'taxonomy' => 'pa_pn', 'hide_empty' => true, 'number' => 0 ] ) as $term ) {
	if ( is_wp_error( $term ) || ! is_numeric( $term->name ) ) {
		continue;
	}
	$v = (float) $term->name;
	if ( $v >= 200 && $v <= 2600 ) {
		echo "pa_pn {$term->name} (x{$term->count})\n";
	}
}

echo "\n=== Канон: pn в диапазоне годов [1950..2035] ===\n";
foreach ( $wpdb->get_results( "SELECT category, pn, COUNT(*) n FROM {$t} WHERE pn BETWEEN 1950 AND 2035 GROUP BY category, pn", ARRAY_A ) as $r ) {
	echo "{$r['category']}: pn={$r['pn']} x{$r['n']}\n";
}

echo "\n=== Заглушки/тройники: стенка 60..100 — что за серии ===\n";
foreach ( $wpdb->get_results( "SELECT product_id, category, s, norm_slug, JSON_UNQUOTE(JSON_EXTRACT(payload, '$.title')) ti FROM {$t} WHERE s BETWEEN 60 AND 100 AND category IN ('zaglushki','troyniki','dnishcha') ORDER BY s DESC LIMIT 12", ARRAY_A ) as $r ) {
	echo "#{$r['product_id']} [{$r['category']}] s={$r['s']} {$r['norm_slug']} | {$r['ti']}\n";
}

echo "\n=== ОСТ 34.10.428: распределение «стенки» ===\n";
foreach ( $wpdb->get_results( "SELECT s, COUNT(*) n FROM {$t} WHERE norm_slug LIKE 'ost-34-10-428%' GROUP BY s ORDER BY s", ARRAY_A ) as $r ) {
	echo 's=' . ( $r['s'] ?? 'NULL' ) . " x{$r['n']}\n";
}
