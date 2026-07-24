<?php
/** Диагностика аномалий данных: PN/стенка со «склейками», нормативы без кириллицы. */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }

global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

echo "=== PN > 200 по категориям ===\n";
foreach ( $wpdb->get_results( "SELECT category, pn, COUNT(*) n FROM {$t} WHERE pn > 200 GROUP BY category, pn ORDER BY category, pn", ARRAY_A ) as $r ) {
	echo "{$r['category']}: pn={$r['pn']} x{$r['n']}\n";
}

echo "\n=== Стенка s > 100 ===\n";
foreach ( $wpdb->get_results( "SELECT category, s, COUNT(*) n FROM {$t} WHERE s > 100 GROUP BY category, s ORDER BY category, s", ARRAY_A ) as $r ) {
	echo "{$r['category']}: s={$r['s']} x{$r['n']}\n";
}

echo "\n=== Примеры строк (payload) ===\n";
$rows = $wpdb->get_results( "SELECT product_id, category, pn, s, JSON_UNQUOTE(JSON_EXTRACT(payload, '$.title')) t FROM {$t} WHERE pn > 200 OR s > 100 LIMIT 10", ARRAY_A );
foreach ( $rows as $r ) {
	echo "#{$r['product_id']} [{$r['category']}] pn={$r['pn']} s={$r['s']} | {$r['t']}\n";
}

echo "\n=== dims у первых трёх ===\n";
foreach ( array_slice( $rows, 0, 3 ) as $r ) {
	$dims = get_post_meta( (int) $r['product_id'], '_promen_dims', true );
	$d    = is_array( $dims ) ? $dims : json_decode( (string) $dims, true );
	$keys = [ 'pn', 'wall_thickness', 'outer_diameter', 'dn' ];
	$out  = [];
	foreach ( $keys as $k ) { $out[] = "$k=" . var_export( $d[ $k ] ?? null, true ); }
	echo "#{$r['product_id']}: " . implode( ' ', $out ) . "\n";
}

echo "\n=== Нормативы фильтра без кириллицы ===\n";
$slugs = $wpdb->get_col( "SELECT DISTINCT norm_slug FROM {$t} WHERE norm_slug != ''" );
$map   = promen_term_map( 'norm' );
$miss  = 0;
foreach ( $slugs as $slug ) {
	$name = $map[ $slug ]['name'] ?? null;
	if ( null === $name ) {
		echo "НЕТ ТЕРМИНА: {$slug}\n";
		$miss++;
	} elseif ( ! preg_match( '/[А-Яа-яЁё]/u', $name ) ) {
		echo "НЕ КИРИЛЛИЦА: {$slug} => «{$name}»\n";
		$miss++;
	}
}
echo "итого проблемных: {$miss} из " . count( $slugs ) . "\n";
