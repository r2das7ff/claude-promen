<?php
/**
 * Присоединительные размеры фланцев по таблицам стандартов — 30.09.2026.
 *
 * Калькулятор КОФ собирает комплект крепежа из D, D1, числа отверстий,
 * резьбы и толщины фланца. У 2/3 фланцев ГОСТ 33259 и у всех ГОСТ 12820
 * эти поля были сдвинуты импортом (29 или 40 отверстий, D1 почти равен D),
 * у ГОСТ 12821 в «резьбе» лежал диаметр отверстия (18 вместо M16) —
 * калькулятор такие строки отбраковывал и писал «уточняется».
 *
 * Источники (разбор — work-0930/*.py):
 *  - ГОСТ 33259-2015, таблицы 3 (тип 01) и 6 (тип 11), с поправкой ИУС
 *    №11-2016; ряд 1 — обозначение каталога «100-11-1-…» (ряд 1 по
 *    приложению Г); где у ряда 1 прочерк — ряд 2;
 *  - ГОСТ 12821-80 и 12820-80: D, D1, отверстия — ряд 1 ГОСТ 33259
 *    (= ГОСТ 12815; сошлось с 69 из 75 карточек 12821 до миллиметра),
 *    толщина b — таблицы стандартов (base.garant.ru; PN 25 у 12820 — скан
 *    архива, сверен с массами каталога и изображением страницы).
 *
 * Не трогаем: DN 32 PN 25 ГОСТ 12820 (113457) — в карточке данные строки
 * DN 350; ОСТ 34-10-425 (в таблице стандарта нет толщины фланца) и
 * ОСТ 24.125.24 (документа нет).
 *
 * Запуск через include-обёртку: $args = [] (отчёт) / ['apply'];
 * данные — /tmp/flange_set.json { product_id: { поле: значение } }.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

global $wpdb;

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );
$set   = json_decode( (string) file_get_contents( '/tmp/flange_set.json' ), true );
if ( ! is_array( $set ) || ! $set ) {
	WP_CLI::error( 'Нет /tmp/flange_set.json' );
}

$changes = [];
foreach ( $set as $id => $fields ) {
	$id   = (int) $id;
	$dims = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
	$diff = [];
	foreach ( $fields as $k => $v ) {
		if ( (string) ( $dims[ $k ] ?? '' ) !== (string) $v ) {
			$diff[ $k ] = [ $dims[ $k ] ?? null, $v ];
		}
	}
	if ( $diff ) {
		$changes[ $id ] = $diff;
	}
}
$cnt = [];
foreach ( $changes as $diff ) {
	foreach ( array_keys( $diff ) as $k ) {
		$cnt[ $k ] = ( $cnt[ $k ] ?? 0 ) + 1;
	}
}
WP_CLI::log( sprintf( 'Карточек в наборе: %d, меняется: %d; по полям: %s', count( $set ), count( $changes ), wp_json_encode( $cnt ) ) );
foreach ( array_slice( $changes, 0, 5, true ) as $id => $diff ) {
	WP_CLI::log( "  {$id} " . get_the_title( $id ) . ' ' . wp_json_encode( $diff, JSON_UNESCAPED_UNICODE ) );
}

if ( ! $apply ) {
	WP_CLI::log( 'Отчёт. Для правки: $args = [ "apply" ]' );
	return;
}

$snap = [];
foreach ( array_keys( $changes ) as $id ) {
	$snap[ $id ] = get_post_meta( $id, '_promen_dims', true );
}
$file = rtrim( (string) getenv( 'HOME' ), '/' ) . '/flange-bolts-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $file, wp_json_encode( $snap, JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок: ' . $file );

$done = 0;
foreach ( $changes as $id => $diff ) {
	$dims = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
	foreach ( $diff as $k => $pair ) {
		$dims[ $k ] = $pair[1];
	}
	update_post_meta( $id, '_promen_dims', wp_json_encode( $dims, JSON_UNESCAPED_UNICODE ) );
	promen_catalog_upsert( $id, false );
	if ( 0 === ++$done % 200 ) {
		wp_cache_flush();
		WP_CLI::log( "  … {$done}" );
	}
}
wp_cache_flush();
if ( function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump(); // заодно сбрасывает кеш ответов калькулятора
}
WP_CLI::success( "Обновлено карточек: {$done}. Дальше: wp promen cache-purge." );
