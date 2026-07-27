<?php
/** Разово: изоляция — нормализация norm_key, пересборка отображаемых имён (тип из названия). */
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

// norm_key к единой slug-форме (у 36 из 37 уже так).
$pids = $wpdb->get_col( "SELECT product_id FROM {$t} WHERE category='izolyatsiya'" );
foreach ( $pids as $pid ) {
	$pid = (int) $pid;
	$nk  = (string) get_post_meta( $pid, '_promen_norm_key', true );
	if ( $nk !== '' && $nk !== 'gost-30732-2020' ) {
		update_post_meta( $pid, '_promen_norm_key', 'gost-30732-2020' );
		echo "#{$pid}: norm_key «{$nk}» → gost-30732-2020\n";
	}
}

// Кэши серий (не versioned транзиенты) — сбросить.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient%promen_series%'" );

// Пересборка канона (display_title теперь из названия товара).
$n = 0;
foreach ( $pids as $pid ) {
	promen_catalog_upsert( (int) $pid );
	$n++;
}
promen_filters_cache_bump();
echo "пересобрано {$n} позиций изоляции\n";

echo "\n=== Проверка: имена в реестре ===\n";
foreach ( $wpdb->get_results( "SELECT product_id, JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) ti, JSON_UNQUOTE(JSON_EXTRACT(payload,'$.norm')) nrm FROM {$t} WHERE category='izolyatsiya' ORDER BY RAND() LIMIT 6", ARRAY_A ) as $r ) {
	echo "#{$r['product_id']} | {$r['ti']} | норматив: {$r['nrm']}\n";
}
$bad = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE category='izolyatsiya' AND JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) LIKE 'Изоляция и покрытия%'" );
$tr  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE category='izolyatsiya' AND JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) LIKE 'Труба в ППУ%'" );
echo "строк с «Изоляция и покрытия…»: {$bad} | с «Труба в ППУ…»: {$tr}\n";
