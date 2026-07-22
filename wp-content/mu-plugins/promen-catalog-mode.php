<?php
/**
 * Plugin Name: PROM-EN Catalog Mode
 * Description: Каталог без цен и корзины — вместо покупки кнопка «Запросить КП».
 */

defined( 'ABSPATH' ) || exit;

/** Цены не выводим нигде. */
add_filter( 'woocommerce_get_price_html', '__return_empty_string', 100 );
add_filter( 'woocommerce_cart_item_price', '__return_empty_string', 100 );

/** Кнопки покупки убираем: и на архивах, и в карточке. */
add_action( 'init', function () {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
} );

/** Товары нельзя положить в корзину даже прямым запросом. */
add_filter( 'woocommerce_is_purchasable', '__return_false', 100 );
add_filter( 'woocommerce_variation_is_purchasable', '__return_false', 100 );

/** Корзина/оплата не нужны: страницы отдают 404, ссылки на них не строим. */
add_action( 'template_redirect', function () {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		wp_safe_redirect( home_url( '/catalog/' ), 302 );
		exit;
	}
} );

/** Скрипты/стили корзины и мини-корзина Woo не грузятся. */
add_filter( 'woocommerce_widget_cart_is_hidden', '__return_true' );
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_script( 'wc-cart-fragments' );
}, 20 );

/** Складские статусы наружу не показываем («под заказ» — всегда). */
add_filter( 'woocommerce_get_stock_html', '__return_empty_string' );
