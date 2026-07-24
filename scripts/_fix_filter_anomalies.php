<?php
/**
 * Разовая чистка аномалий данных фильтров (2026-07-24):
 *  - dims со «склейками»: pn > 2000 (273325 = склеенные диаметры) и
 *    wall_thickness > 100 (125/200/250/300 у серий «исп. 10») — обнуляются;
 *  - имена 3 СТО-терминов нормативов — на кириллицу;
 *  - точечный re-upsert затронутых товаров (канон + Meili) + сброс кэшей.
 *
 * Запуск: docker compose run --rm wpcli eval-file /scripts/_fix_filter_anomalies.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }

global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

// 1. Товары с мусорными dims: ищем по канону (там уже собраны значения).
$pids = $wpdb->get_col( "SELECT product_id FROM {$t} WHERE pn > 2000 OR s > 100" );
echo 'товаров с аномалиями: ' . count( $pids ) . "\n";

$fixed = 0;
foreach ( $pids as $pid ) {
	$pid  = (int) $pid;
	$dims = get_post_meta( $pid, '_promen_dims', true );
	$d    = is_array( $dims ) ? $dims : json_decode( (string) $dims, true );
	if ( ! is_array( $d ) ) {
		echo "#{$pid}: dims не массив — пропуск\n";
		continue;
	}
	$was = [];
	if ( isset( $d['pn'] ) && (float) $d['pn'] > 2000 ) {
		$was[] = 'pn=' . $d['pn'];
		unset( $d['pn'] );
	}
	if ( isset( $d['wall_thickness'] ) && (float) $d['wall_thickness'] > 100 ) {
		$was[] = 's=' . $d['wall_thickness'];
		unset( $d['wall_thickness'] );
	}
	if ( ! $was ) {
		continue;
	}
	update_post_meta( $pid, '_promen_dims', is_array( $dims ) ? $d : wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
	promen_catalog_upsert( $pid ); // канон + Meili сразу
	echo "#{$pid}: убрано " . implode( ', ', $was ) . ' | ' . get_the_title( $pid ) . "\n";
	$fixed++;
}

// 2. СТО-термины без кириллических имён.
foreach ( [ 'sto-79814898-125', 'sto-95-119', 'sto-95-127' ] as $slug ) {
	$term = get_term_by( 'slug', $slug, 'norm' );
	if ( ! $term || is_wp_error( $term ) ) {
		echo "терм не найден: {$slug}\n";
		continue;
	}
	if ( preg_match( '/[А-Яа-яЁё]/u', $term->name ) ) {
		echo "уже кириллица: {$slug} => {$term->name}\n";
		continue;
	}
	$new = promen_norm_label_from_slug( $slug );
	wp_update_term( $term->term_id, 'norm', [ 'name' => $new ] );
	echo "терм переименован: {$slug} => «{$new}»\n";
}

promen_filters_cache_bump();
echo "готово: dims исправлено у {$fixed}, кэши сброшены\n";
