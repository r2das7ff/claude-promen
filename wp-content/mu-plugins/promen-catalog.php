<?php
/**
 * Plugin Name: PROM-EN Catalog Canon
 * Description: Каноническая таблица реестра каталога и хуки синхронизации.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', function () {
	if ( ! function_exists( 'promen_catalog_maybe_install' ) ) {
		return;
	}
	promen_catalog_maybe_install();
}, 5 );

/** После сохранения товара — обновить канон. */
add_action( 'save_post_product', function ( int $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $post_id );
	}
}, 20 );

add_action( 'before_delete_post', function ( int $post_id ) {
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}
	if ( function_exists( 'promen_catalog_delete' ) ) {
		promen_catalog_delete( $post_id );
	}
} );
