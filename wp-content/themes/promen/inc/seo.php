<?php
/**
 * SEO: title/description шаблоны и sitemap для taxonomy norm.
 * Без Yoast — лёгкий слой поверх title-tag + wp_sitemaps.
 */

defined( 'ABSPATH' ) || exit;

/** Document title по типу страницы. */
add_filter( 'document_title_parts', function ( array $parts ): array {
	$brand = 'Промышленная Энергетика';

	if ( function_exists( 'is_product' ) && is_product() ) {
		$parts['title'] = get_the_title();
		$parts['site']  = $brand;
		return $parts;
	}

	if ( is_tax( 'product_cat' ) || is_tax( 'norm' ) ) {
		$term = get_queried_object();
		$parts['title'] = $term->name;
		$parts['site']  = 'Каталог · ' . $brand;
		return $parts;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$parts['title'] = 'Каталог продукции';
		$parts['site']  = $brand;
	}

	return $parts;
} );

/*
 * Meta description раньше выводился и здесь, и в promen_meta_description()
 * из functions.php — два независимых хука wp_head, то есть тег на каждой
 * категории и карточке печатался дважды (проверено 2026-08-25, 8 из 8
 * страниц каждого типа). Логика этого хука перенесена в functions.php,
 * он остался единственным источником тега.
 */

/*
 * ВРЕМЕННО: подтверждение прав на тестовый домен в Яндекс.Вебмастере.
 * Нужно, чтобы увидеть, попал ли стенд в индекс — если попал, перед
 * переездом его закрываем, иначе после переключения домена получим
 * два одинаковых сайта и склейку не в ту сторону.
 * Снять вместе с самим доменом после переезда.
 */
add_action( 'wp_head', function () {
	if ( 'prom-en.forgotaboutdre.ru' !== ( $_SERVER['HTTP_HOST'] ?? '' ) ) {
		return;
	}
	echo '<meta name="yandex-verification" content="fadaca41a33d7a26" />' . "\n";
}, 1 );

/** Включаем norm в core XML sitemap. */
add_filter( 'wp_sitemaps_taxonomies', function ( array $taxonomies ): array {
	$taxonomies['norm'] = get_taxonomy( 'norm' );
	return $taxonomies;
} );

/** Параметрические URL каталога не должны попадать в sitemap (их и нет — core sitemap чистый). */
add_filter( 'wp_sitemaps_posts_entry', function ( $entry, $post ) {
	if ( $post->post_type === 'product' ) {
		$entry['loc'] = get_permalink( $post );
	}
	return $entry;
}, 10, 2 );

/**
 * 301 со схлопнутых при дедупе позиций на выжившего.
 * Карта promen_dedup_redirects: старый post_name → ID выжившего.
 */
add_action( 'template_redirect', function (): void {
	if ( ! is_404() ) {
		return;
	}
	$map = get_option( 'promen_dedup_redirects', [] );
	if ( ! is_array( $map ) || ! $map ) {
		return;
	}
	$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$path = trim( $path, '/' );
	if ( $path === '' ) {
		return;
	}
	$seg = ( $pos = strrpos( $path, '/' ) ) !== false ? substr( $path, $pos + 1 ) : $path;
	$seg = sanitize_title( rawurldecode( $seg ) );
	if ( $seg === '' || empty( $map[ $seg ] ) ) {
		return;
	}
	$url = get_permalink( (int) $map[ $seg ] );
	if ( $url ) {
		wp_safe_redirect( $url, 301 );
		exit;
	}
}, 1 );
