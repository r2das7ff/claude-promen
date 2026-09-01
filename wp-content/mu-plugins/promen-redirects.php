<?php
/**
 * Plugin Name: PROM-EN — карта 301 со старого сайта
 * Description: Старые адреса prom-en.com (/products/, /rubric-products/, статика) → новые.
 *
 * Почему mu-plugin, а не .htaccess: правил 10 173, и Apache перебирал бы их
 * регулярками на КАЖДОМ запросе, включая картинки и CSS. Здесь — один хеш-поиск
 * по массиву, который opcache держит скомпилированным в памяти.
 *
 * Почему на `init`, а не на 404: до 404 WordPress успевает отработать полный
 * цикл запроса — разбор правил перезаписи, запрос к базе, загрузку темы. Старых
 * адресов на новом сайте нет ни одного, ловить их поздно незачем.
 *
 * Один хоп: карта строится так, что цель всегда отдаёт 200 (скрипт
 * scripts/seo/build_redirects.py). Цепочек 301 → 301 нет, на главную не ведёт
 * ни один адрес — только товар, страница норматива или категория.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) || wp_doing_ajax() ) {
		return;
	}
	if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'GET' ) {
		return;
	}

	$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( '' === $path || '/' === $path ) {
		return;
	}

	// Карта хранит адреса со слешем на конце — приводим к той же форме.
	$key = '/' . trim( rawurldecode( $path ), '/' ) . '/';

	// Быстрый отсев: старые разделы известны наперёд, и на новом сайте их нет.
	// Без этой проверки каждый запрос тянул бы с диска мегабайтный массив.
	static $prefixes = [ '/products/', '/rubric-products/', '/uslugi/' ];
	$interesting = false;
	foreach ( $prefixes as $p ) {
		if ( 0 === strpos( $key, $p ) ) {
			$interesting = true;
			break;
		}
	}
	if ( ! $interesting ) {
		static $singles = [ '/kontakty/', '/trust-us/', '/prajs-list-truby/', '/prajs-list-detali/' ];
		if ( ! in_array( $key, $singles, true ) ) {
			return;
		}
	}

	static $map = null;
	if ( null === $map ) {
		$file = __DIR__ . '/promen-redirects-map.php';
		$map  = is_readable( $file ) ? (array) require $file : [];
	}

	$target = $map[ $key ] ?? '';
	if ( '' === $target ) {
		// Раздел старый, а адрес неизвестен — отдаём честную 404 темы,
		// а не редирект «куда-нибудь». Ошибку видно в Вебмастере, и адрес
		// можно добрать в карту.
		return;
	}

	// Запрос с параметрами (utm и прочее) — хвост сохраняем.
	$qs = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	if ( '' !== $qs ) {
		$target .= ( strpos( $target, '?' ) === false ? '?' : '&' ) . $qs;
	}

	wp_redirect( $target, 301 );
	exit;
}, 1 );
