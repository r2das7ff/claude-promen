<?php
/**
 * PROM-EN theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'PROMEN_VERSION', '0.17.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
} );

require_once __DIR__ . '/inc/product-data.php';
require_once __DIR__ . '/inc/catalog-filters.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/pilot-otvody.php';

/**
 * Главная вне скоупа каталога — ведём сразу в реестр,
 * иначе / выглядит как пустой «каталог» без товаров.
 */
add_action( 'template_redirect', function () {
	if ( is_front_page() && ! is_admin() ) {
		$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
		if ( $shop ) {
			wp_safe_redirect( $shop, 302 );
			exit;
		}
	}
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'promen-base', get_theme_file_uri( 'assets/css/base.css' ), [], PROMEN_VERSION );
	wp_enqueue_style( 'promen-catalog', get_theme_file_uri( 'assets/css/catalog.css' ), [ 'promen-base' ], PROMEN_VERSION );
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'promen-product', get_theme_file_uri( 'assets/css/product.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-product', get_theme_file_uri( 'assets/js/product.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}
	// Лендинги разделов (порт sdt.html): свой CSS/JS, без AJAX-реестра.
	if ( is_tax( 'product_cat', promen_section_landing_slugs() ) ) {
		wp_enqueue_style( 'promen-category', get_theme_file_uri( 'assets/css/category-sdt.css' ), [ 'promen-base', 'promen-catalog' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-category-sdt', get_theme_file_uri( 'assets/js/category-sdt.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	} elseif ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_tax( 'norm' ) || is_post_type_archive( 'product' ) ) ) {
		wp_enqueue_script( 'promen-catalog', get_theme_file_uri( 'assets/js/catalog.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}
} );

/** Дефолтные стили WooCommerce отключаем целиком. */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/** Явно убираем emoji-скрипты и generator — чистый <head>. */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
} );

/**
 * Пункты главной навигации.
 * Разделы вне скоупа каталога остаются ссылками-заглушками (#).
 */
function promen_nav_items(): array {
	return [
		[ 'label' => 'Главная', 'url' => home_url( '/' ), 'active' => is_front_page() ],
		[
			'label'  => 'Каталог',
			'url'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			'active' => function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_shop() ),
		],
		[ 'label' => 'Производство', 'url' => '#', 'active' => false ],
		[ 'label' => 'Контроль качества', 'url' => '#', 'active' => false ],
		[ 'label' => 'О заводе', 'url' => '#', 'active' => false ],
		[ 'label' => 'Контакты', 'url' => '#footer', 'active' => false ],
	];
}
