<?php
/**
 * Снять с публикации строки-фантомы, которых нет ни в одном нормативе.
 *
 * 1. «Заглушки фланцевые» ОСТ 34.10.428-1990 — все 36. Их разобрали из
 *    normatives/Заглушки/ОСТ 34.10.428-1990.pdf, а это копия части 2 сборника
 *    (ОСТ 34-10-508…513-90: ответвления и тройники; постраничный объём текста
 *    совпадает с Тройники/ОСТ 34.10.510-1990.pdf). Размеры взяты из таблиц
 *    тройников и перемешаны: d = 0, 1900, стенка «000». Артикулы «k-…» —
 *    обрезок нормативного ключа из разбора.
 * 2. Тройники ОСТ 34-10-510-90 — 4: в «диаметре» лежит Dу (200, 500, 700,
 *    1200), остальные колонки разъехались.
 * 3. Тройники серии 4.903-10 — 5: 700×2, 460×6, 94×5, 420×10, 980×6 — таких
 *    труб в ряду тепловых сетей нет.
 *
 * Товары уходят в черновик (обратимо), из канона — сразу. Адреса пишутся в
 * gone.tsv, оттуда scripts/krepezh-fix/build_map.py кладёт их в блок gone
 * карты переездов — mu-плагин отвечает на них 410.
 *
 * Запуск:  wp eval-file scripts/junk-rows-fix/fix.php dry|apply
 */

$mode = ( isset( $args[0] ) && 'apply' === $args[0] ) ? 'apply' : 'dry';

$by_sku = [
	'ОСТ 34-10-510-90' => [
		'ост-3410510-90-10-153-200-50--128-1',
		'ост-3410510-90-10-160-500-125--139-1',
		'ост-3410510-90-10-189-700-000---1',
		'ост-3410510-90-10-599-1200-000--585-1',
	],
	'СЕРИЯ 4.903-10'   => [
		'серия-4903-10-0-0-420-10---1',
		'серия-4903-10-1-2-94-5---1',
		'серия-4903-10-2--460-6---1',
		'серия-4903-10-2--700-2---1',
		'серия-4903-10-2-950-980-6---1',
	],
];

global $wpdb;

// Все «заглушки» ОСТ 34.10.428: норматив и артикул «k-…» вместе.
$targets = [];
foreach ( $wpdb->get_results(
	"SELECT p.ID, s.meta_value AS sku FROM {$wpdb->posts} p
	 JOIN {$wpdb->postmeta} n ON n.post_id = p.ID AND n.meta_key = '_promen_norm_key'
	 JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = '_sku'
	 WHERE p.post_type = 'product' AND p.post_status = 'publish'
	   AND n.meta_value = 'ОСТ 34.10.428-1990' AND s.meta_value LIKE 'k-%'
	 ORDER BY p.ID"
) as $r ) {
	$targets[ (int) $r->ID ] = [ 'ОСТ 34.10.428-1990', $r->sku ];
}

$missing = [];
foreach ( $by_sku as $group => $skus ) {
	foreach ( $skus as $sku ) {
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->posts} p
				 JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = '_sku'
				 WHERE p.post_type = 'product' AND p.post_status = 'publish' AND s.meta_value = %s",
				$sku
			)
		);
		if ( ! $ids ) {
			$missing[] = $sku;
		}
		foreach ( $ids as $id ) {
			$targets[ (int) $id ] = [ $group, $sku ];
		}
	}
}

$gone  = [];
$count = [];
foreach ( $targets as $id => [ $group, $sku ] ) {
	$path = (string) wp_parse_url( (string) get_permalink( $id ), PHP_URL_PATH );
	if ( 0 !== strpos( $path, '/catalog/' ) ) {
		printf( "  пропуск %d %s: адрес вне каталога (%s)\n", $id, $sku, $path );
		continue;
	}
	$gone[]            = $path;
	$count[ $group ] = ( $count[ $group ] ?? 0 ) + 1;
	if ( 'apply' === $mode ) {
		wp_update_post( [ 'ID' => $id, 'post_status' => 'draft' ] );
		clean_post_cache( $id );
		promen_catalog_upsert( $id, false ); // не опубликован — уходит из канона
	}
}

// Повторный прогон целей не находит — прежний список не затираем.
if ( $gone ) {
	sort( $gone );
	file_put_contents( __DIR__ . '/gone.tsv', implode( "\n", $gone ) . "\n" );
}

printf( "Режим: %s\n", $mode );
foreach ( $count as $group => $n ) {
	printf( "  %-20s %d\n", $group, $n );
}
printf( "Снимается: %d%s\n", count( $gone ), $missing ? '; не найдено по артикулу: ' . implode( ', ', $missing ) : '' );
if ( 'apply' === $mode && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
	echo "Кеш фильтров сброшен.\n";
}
