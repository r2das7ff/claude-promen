<?php
/**
 * Перенос труб ППУ из «Трубы → ППУ» в «Изоляция и покрытия» (вариант 2, 2026-07-27).
 * Смена категории 441 товара + пересборка канона/Meili + удаление пустого термина.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

$izl = get_term_by( 'slug', 'izolyatsiya', 'product_cat' );
if ( ! $izl ) { echo "нет термина izolyatsiya!\n"; return; }

$pids = $wpdb->get_col( "SELECT product_id FROM {$t} WHERE category = 'truby-ppu'" );
echo 'к переносу: ' . count( $pids ) . "\n";
$n = 0;
foreach ( $pids as $pid ) {
	$pid = (int) $pid;
	wp_set_object_terms( $pid, [ (int) $izl->term_id ], 'product_cat', false );
	promen_catalog_upsert( $pid );
	$n++;
	if ( $n % 100 === 0 ) { echo "… {$n}\n"; }
}
echo "перенесено: {$n}\n";

// Пустой термин truby-ppu — удалить (из defs уже убран).
$ppu = get_term_by( 'slug', 'truby-ppu', 'product_cat' );
if ( $ppu ) {
	$left = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d", $ppu->term_taxonomy_id ) );
	if ( 0 === $left ) {
		wp_delete_term( $ppu->term_id, 'product_cat' );
		echo "термин truby-ppu удалён\n";
	} else {
		echo "термин truby-ppu НЕ пуст ({$left} связей) — оставлен\n";
	}
}

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient%promen_series%'" );
promen_filters_cache_bump();
flush_rewrite_rules();

echo "\n=== Проверка ===\n";
echo 'изоляция: ', promen_catalog_group_count( 'izolyatsiya' ), ' | трубы: ', promen_catalog_group_count( 'truby' ), ' | truby-ppu: ', promen_catalog_group_count( 'truby-ppu' ), "\n";
$izl_c = promen_izol_type_counts();
echo "типы изоляции: трубы={$izl_c['truby']} ПЭ={$izl_c['pe']} ОЦ={$izl_c['oc']}\n";
$rec = promen_catalog_search_reconcile();
echo "reconcile drift: {$rec['drift']}\n";
$pid = (int) $wpdb->get_var( "SELECT product_id FROM {$t} WHERE category='izolyatsiya' AND JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) LIKE 'Труба%' LIMIT 1" );
echo "пример трубы: #{$pid} → ", get_permalink( $pid ), "\n";
