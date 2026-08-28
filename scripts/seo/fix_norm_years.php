<?php
/**
 * Год двух нормативов: 1970 → 1975.
 *
 * В реестре normatives/registry/normatives_master.csv обозначения записаны
 * верно — «ГОСТ 9064-1975» и «ГОСТ 9066-1975», — а нормализатор ключа выдал
 * им 1970. Ошибка разошлась по трём местам: имя термина, его слаг и мета
 * `_promen_norm_key` у товаров. При этом названия самих товаров правильные
 * («Гайка M100 ГОСТ 9064-75»), то есть страница спорит сама с собой.
 *
 * Правим всё сразу: если поменять только мету, слаг серии (translit от ключа)
 * перестанет совпадать со слагом термина, и страницы серий отдадут 404.
 *
 *   docker compose run --rm -T wpcli eval-file /scripts/seo/fix_norm_years.php
 *   ... eval-file /scripts/seo/fix_norm_years.php apply
 *
 * После apply: wp promen catalog-rebuild — в каноне лежат нормативы.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );

$map = [
	'gost-9064-1970' => [ 'ГОСТ 9064-1970', 'ГОСТ 9064-1975', 'gost-9064-1975' ],
	'gost-9066-1970' => [ 'ГОСТ 9066-1970', 'ГОСТ 9066-1975', 'gost-9066-1975' ],
];

foreach ( $map as $old_slug => list( $old_name, $new_name, $new_slug ) ) {
	$term = get_term_by( 'slug', $old_slug, 'norm' );
	if ( ! $term ) {
		WP_CLI::warning( "нет термина {$old_slug}" );
		continue;
	}

	$q = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => [ [ 'taxonomy' => 'norm', 'field' => 'term_id', 'terms' => $term->term_id ] ],
	] );
	$ids = $q->posts;

	WP_CLI::log( sprintf( '%s → %s  (термин + слаг + мета у %d товаров)',
		$old_name, $new_name, count( $ids ) ) );

	if ( ! $apply ) {
		continue;
	}

	// Описание термина тоже содержит обозначение: «ГОСТ 9064-1970 — норматив
	// изготовления изделий…». Без этого на карточке соседствовали два года.
	$desc = str_replace( $old_name, $new_name, (string) $term->description );
	wp_update_term( $term->term_id, 'norm', [
		'name'        => $new_name,
		'slug'        => $new_slug,
		'description' => $desc,
	] );
	$changed = 0;
	foreach ( $ids as $pid ) {
		if ( $old_name === (string) get_post_meta( $pid, '_promen_norm_key', true ) ) {
			update_post_meta( $pid, '_promen_norm_key', $new_name );
			$changed++;
		}
		clean_post_cache( $pid );
	}
	// Слаг серии строится от ключа норматива — прежние кэши указывают на старый.
	delete_transient( 'promen_series_rep_' . md5( promen_translit( $old_name ) ) );
	WP_CLI::success( "  мета обновлена у {$changed}" );
}

if ( ! $apply ) {
	WP_CLI::log( "\nЭто предпросмотр. Для записи добавьте аргумент apply" );
} else {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name REGEXP '^_transient_(timeout_)?promen_'" );
	WP_CLI::success( 'Готово. Дальше: wp promen catalog-rebuild' );
}
