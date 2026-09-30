<?php
/**
 * Выборочный сброс кеша страниц: карточки и серии крепежа.
 *
 * Полный `wp promen cache-purge` под обходом робота бьёт всем потоком в PHP;
 * 30.09.2026 после него общий сервер хостинга полторы минуты отдавал 502.
 * Ключ файла кеша — md5(хост|путь) (wp-content/advanced-cache.php).
 *
 * Запуск на проде: wp eval-file через include-обёртку.
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;
$t    = $wpdb->prefix . 'promen_catalog_rows';
$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
$dir  = WP_CONTENT_DIR . '/cache/promen-page';

$paths = [];
$rows  = $wpdb->get_results( "SELECT category, norm_slug, JSON_UNQUOTE(JSON_EXTRACT(payload, '$.url')) AS url FROM {$t} WHERE category IN ('bolty','gayki','shpilki','shayby','vinty')" );
foreach ( $rows as $r ) {
	$p = (string) wp_parse_url( (string) $r->url, PHP_URL_PATH );
	if ( '' !== $p ) {
		$paths[ $p ] = true;
	}
	$paths[ '/catalog/krepezh/' . $r->category . '/seriya/' . $r->norm_slug . '/' ] = true;
}

$n = 0;
foreach ( array_keys( $paths ) as $p ) {
	$key = md5( $host . '|' . $p );
	foreach ( (array) glob( $dir . '/v*/' . substr( $key, 0, 2 ) . '/' . $key . '.html*' ) as $f ) {
		if ( $f && @unlink( $f ) ) {
			$n++;
		}
	}
}
echo 'адресов ' . count( $paths ) . ', удалено файлов кеша ' . $n . "\n";
