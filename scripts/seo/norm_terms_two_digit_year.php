<?php
/**
 * Нормативы до 2000 года — к официальному обозначению с двузначным годом.
 *
 * Обратная сторона fix_norm_year_titles.php. У 22 нормативов (6 339 товаров,
 * почти весь крепёж) названия товаров уже написаны верно — «ГОСТ 7798-70»,
 * а термин, ключ норматива в товарах и реестр держали развёрнутый год
 * «ГОСТ 7798-1970». Страница снова спорила сама с собой: H1 карточки
 * «-70», крошка и колонка «Норматив» каталога «-1970».
 *
 * Правило то же: год пишется так, как напечатан на стандарте, до 2000 года —
 * двумя цифрами. Решение заказчика 30.09.2026: термины привести к
 * официальной форме, названия товаров не трогать.
 *
 * Что меняется:
 *  - имя термина norm (слаг остаётся: адреса /normativy/gost-7798-1970/ и
 *    серий проиндексированы, год в URL ни на что не влияет);
 *  - служебный ключ _promen_norm_key у товаров — из него берётся норматив
 *    для колонки каталога и для места PN в <title> карточки;
 *  - то же обозначение внутри payload канона каталога (одним REPLACE по
 *    norm_slug вместо пересборки 6 339 строк на шареде).
 *
 * Запуск:
 *   wp eval-file norm_terms_two_digit_year.php          — отчёт
 *   wp eval-file norm_terms_two_digit_year.php apply    — применить
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

global $wpdb;

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );

$names = [
	'ГОСТ 10494-1980', 'ГОСТ 10602-1994', 'ГОСТ 10605-1994', 'ГОСТ 10607-1994',
	'ГОСТ 11371-1978', 'ГОСТ 11738-1984', 'ГОСТ 15590-1970', 'ГОСТ 15591-1970',
	'ГОСТ 22032-1976', 'ГОСТ 22043-1976', 'ГОСТ 5915-1970',  'ГОСТ 5916-1970',
	'ГОСТ 5927-1970',  'ГОСТ 5929-1970',  'ГОСТ 6402-1970',  'ГОСТ 7795-1970',
	'ГОСТ 7796-1970',  'ГОСТ 7798-1970',  'ГОСТ 7805-1970',  'ГОСТ 7808-1970',
	'ГОСТ 9064-1975',  'ГОСТ 9066-1975',
];

$table = promen_catalog_table_name();
$plan  = [];
foreach ( $names as $old ) {
	$term = get_term_by( 'name', $old, 'norm' );
	if ( ! $term ) {
		WP_CLI::warning( "Термин не найден: {$old}" );
		continue;
	}
	$new = preg_replace( '/-19(\d{2})$/u', '-$1', $old );
	$dup = get_term_by( 'name', $new, 'norm' );
	if ( $dup && (int) $dup->term_id !== (int) $term->term_id ) {
		WP_CLI::warning( "Уже есть термин «{$new}» (id {$dup->term_id}) — {$old} пропущен" );
		continue;
	}
	$meta  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_promen_norm_key' AND meta_value = %s", $old ) );
	$canon = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE norm_slug = %s AND payload LIKE %s", $term->slug, '%' . $wpdb->esc_like( $old ) . '%' ) );
	$plan[] = [ $term, $old, $new, $meta, $canon ];
	WP_CLI::log( sprintf( '  %-16s → %-13s слаг %-18s товаров %4d, ключей %4d, строк канона %4d', $old, $new, $term->slug, (int) $term->count, $meta, $canon ) );
}

if ( ! $apply ) {
	WP_CLI::log( 'Отчёт. Для правки: ... eval-file norm_terms_two_digit_year.php apply' );
	return;
}

$snap = rtrim( (string) getenv( 'HOME' ), '/' ) . '/norm-terms-two-digit-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $snap, wp_json_encode( array_map( static fn( $p ) => [ 'term_id' => $p[0]->term_id, 'slug' => $p[0]->slug, 'old' => $p[1], 'new' => $p[2] ], $plan ), JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок: ' . $snap . ' (откат — те же замены в обратную сторону)' );

foreach ( $plan as list( $term, $old, $new ) ) {
	$r = wp_update_term( $term->term_id, 'norm', [ 'name' => $new, 'slug' => $term->slug ] );
	if ( is_wp_error( $r ) ) {
		WP_CLI::warning( "{$old}: " . $r->get_error_message() );
		continue;
	}
	$m = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = '_promen_norm_key' AND meta_value = %s", $new, $old ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$c = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET payload = REPLACE(payload, %s, %s) WHERE norm_slug = %s", $old, $new, $term->slug ) );
	WP_CLI::log( sprintf( '  %s → %s: ключей %d, строк канона %d', $old, $new, (int) $m, (int) $c ) );
}

wp_cache_flush();
if ( function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
delete_transient( 'promen_series_sitemap' );

WP_CLI::success( 'Готово. Дальше: wp promen cache-purge.' );
