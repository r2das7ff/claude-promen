<?php
/**
 * Крепёж: ГОСТ 22032-76 и ГОСТ 22043-76 — это ШПИЛЬКИ, а не «болты фундаментные».
 *
 * Официальные названия документов (normatives/registry/normatives_master.csv):
 *   ГОСТ 22032-1976 — «Шпильки с ввинчиваемым концом длиной 1d. Класс точности В»
 *   ГОСТ 22043-1976 — «Шпильки для деталей с гладкими отверстиями. Класс точности А»
 *
 * В каталоге 1 561 такая позиция лежала в «Болтах» с семейством «Болт
 * фундаментный», названием «Болт M…», кодом «…-Б-…» и адресом /bolty/bolt-….
 * Фундаментные болты — это вообще другой предмет (ГОСТ 24379), и снабженец
 * видит ошибку с первого взгляда.
 *
 * Правит: семейство, заголовок, слаг, артикул, категорию. Канон пересобирает
 * по затронутым товарам. Старые адреса пишет в moves.tsv — оттуда они идут
 * в карту 301 (wp-content/mu-plugins/promen-catalog-moves-map.php).
 *
 * Запуск:  wp eval-file scripts/krepezh-fix/fix.php dry
 *          wp eval-file scripts/krepezh-fix/fix.php apply
 */

$mode = ( isset( $args[0] ) && 'apply' === $args[0] ) ? 'apply' : 'dry';

$plan = [
	'ГОСТ 22032-1976' => 'Шпилька с ввинчиваемым концом',
	'ГОСТ 22043-1976' => 'Шпилька для гладких отверстий',
];

$target_term = get_term_by( 'slug', 'shpilki', 'product_cat' );
if ( ! $target_term ) {
	echo "Нет категории «shpilki» — остановка.\n";
	return;
}

$ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_query'     => [
			[
				'key'     => '_promen_norm_key',
				'value'   => array_keys( $plan ),
				'compare' => 'IN',
			],
		],
	]
);

printf( "Режим: %s. Позиций под правку: %d\n\n", $mode, count( $ids ) );

$moves   = [];
$touched = 0;
$skipped = 0;

foreach ( $ids as $id ) {
	$post   = get_post( $id );
	$norm   = (string) get_post_meta( $id, '_promen_norm_key', true );
	$family = $plan[ $norm ] ?? '';
	if ( '' === $family || ! $post ) {
		$skipped++;
		continue;
	}

	$url_old   = (string) get_permalink( $id );
	$title_old = (string) $post->post_title;
	$slug_old  = (string) $post->post_name;
	$sku_old   = (string) get_post_meta( $id, '_sku', true );

	// «Болт M2.5х10 10.9 ГОСТ 22032-76» → «Шпилька M2.5х10 10.9 ГОСТ 22032-76»
	$title_new = preg_replace( '/^Болт\b/u', 'Шпилька', $title_old );
	$slug_new  = preg_replace( '/^bolt-/', 'shpilka-', $slug_old );
	// Код изделия: «22032-Б-2.5-10-10.9» → «22032-ШП-2.5-10-10.9», как у
	// остальных шпилек (15590-ШП-8-35-4.6).
	$sku_new = preg_replace( '/-Б-/u', '-ШП-', $sku_old );

	if ( $touched < 3 ) {
		printf( "  %s\n    название: %s\n              → %s\n    слаг:     %s → %s\n    артикул:  %s → %s\n    семейство: %s\n",
			$norm, $title_old, $title_new, $slug_old, $slug_new, $sku_old, $sku_new, $family );
	}

	if ( 'apply' === $mode ) {
		wp_update_post(
			[
				'ID'         => $id,
				'post_title' => $title_new,
				'post_name'  => $slug_new,
			]
		);
		update_post_meta( $id, '_promen_family', $family );
		if ( $sku_new !== $sku_old ) {
			update_post_meta( $id, '_sku', $sku_new );
		}
		wp_set_object_terms( $id, [ (int) $target_term->term_id ], 'product_cat', false );
		clean_post_cache( $id );

		$url_new = (string) get_permalink( $id );
		if ( $url_old !== $url_new ) {
			$moves[] = [ $url_old, $url_new ];
		}
		promen_catalog_upsert( $id, false );
	} else {
		$moves[] = [ $url_old, str_replace( [ '/bolty/', '/' . $slug_old . '/' ], [ '/shpilki/', '/' . $slug_new . '/' ], $url_old ) ];
	}

	$touched++;
	if ( 0 === $touched % 300 ) {
		wp_cache_flush();
		printf( "  … %d/%d\n", $touched, count( $ids ) );
	}
}

$path = __DIR__ . '/moves.tsv';
$out  = '';
foreach ( $moves as [ $from, $to ] ) {
	$out .= wp_parse_url( $from, PHP_URL_PATH ) . "\t" . wp_parse_url( $to, PHP_URL_PATH ) . "\n";
}
file_put_contents( $path, $out );

printf( "\nОбработано: %d, пропущено: %d, переездов: %d\nКарта адресов: %s\n", $touched, $skipped, count( $moves ), $path );
if ( 'apply' === $mode && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
	echo "Кеш фильтров сброшен.\n";
}
