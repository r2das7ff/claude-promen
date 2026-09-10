<?php
/**
 * Plugin Name: PROM-EN — переезды внутри каталога
 * Description: 301 для товаров, сменивших слаг, и 410 для удалённых типоразмеров.
 *
 * Почему отдельно от promen-redirects: та карта покрывает адреса СТАРОГО сайта
 * (/products/, /rubric-products/) и по префиксу отсекает всё остальное, чтобы не
 * тянуть мегабайтный массив на каждый запрос. Здесь адреса живого каталога
 * (/catalog/…), карта маленькая, и нужен ещё 410, которого там нет вовсе.
 *
 * Почему 410, а не 404: типоразмеров нет в нормативе, возвращать их не будем
 * никогда. 410 — сигнал «удалено насовсем», поисковик выбрасывает адрес после
 * первого же обхода, тогда как 404 он перепроверяет неделями.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return;
	}
	if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', [ 'GET', 'HEAD' ], true ) ) {
		return;
	}

	$uri  = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( '' === $path || 0 !== strpos( $path, '/catalog/' ) ) {
		return;
	}
	$key = '/' . trim( rawurldecode( $path ), '/' ) . '/';

	static $map = null;
	if ( null === $map ) {
		$file = __DIR__ . '/promen-catalog-moves-map.php';
		$map  = is_readable( $file ) ? (array) require $file : [ 'moved' => [], 'gone' => [] ];
	}

	if ( isset( $map['moved'][ $key ] ) ) {
		$target = home_url( $map['moved'][ $key ] );
		$qs     = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		if ( '' !== $qs ) {
			$target .= '?' . $qs;
		}
		wp_redirect( $target, 301 );
		exit;
	}

	if ( isset( $map['gone'][ $key ] ) ) {
		// Шаблон 404 темы, но со статусом 410: страница оформлена как обычная
		// «не найдено», а роботу уходит верный код.
		add_filter( 'wp_headers', function ( $h ) {
			return $h;
		} );
		status_header( 410 );
		nocache_headers();
		global $wp_query;
		$wp_query->set_404();
		include get_query_template( '404' );
		exit;
	}
}, 1 );
