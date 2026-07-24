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

/**
 * Обновить канон (+ поисковый индекс) один раз за запрос на товар:
 * save_post_product и WC-хуки вариаций пересекаются в одном сохранении.
 */
function promen_catalog_upsert_once( int $product_id ): void {
	static $done = [];
	if ( $product_id <= 0 || isset( $done[ $product_id ] ) ) {
		return;
	}
	if ( ! function_exists( 'promen_catalog_upsert' ) ) {
		return;
	}
	$done[ $product_id ] = true;
	promen_catalog_upsert( $product_id );
}

/** После сохранения товара — обновить канон. */
add_action( 'save_post_product', function ( int $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	promen_catalog_upsert_once( $post_id );
}, 20 );

/** Правка вариации (сталь/поднадзорность) без save родителя — тоже в канон. */
add_action( 'woocommerce_save_product_variation', function ( $variation_id ) {
	promen_catalog_upsert_once( (int) wp_get_post_parent_id( (int) $variation_id ) );
}, 20 );

add_action( 'woocommerce_update_product', function ( $product_id ) {
	promen_catalog_upsert_once( (int) $product_id );
}, 20 );

add_action( 'before_delete_post', function ( int $post_id ) {
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}
	if ( function_exists( 'promen_catalog_delete' ) ) {
		promen_catalog_delete( $post_id );
	}
} );

// ── Ночная сверка канон ↔ поисковый индекс ───────────────────────
// Индекс обновляется синхронно на каждом сохранении, но сбои (Meili лежал,
// упавший импорт) оставляют дрейф. Крон ловит его и пересобирает индекс
// с нуля (drop+reindex: reindex_all не удаляет осиротевшие документы).

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'promen_search_reconcile_daily' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 03:00' ) ?: time() + DAY_IN_SECONDS, 'daily', 'promen_search_reconcile_daily' );
	}
} );

add_action( 'promen_search_reconcile_daily', function () {
	if ( ! function_exists( 'promen_catalog_search_reconcile' ) || ! class_exists( 'Promen_Meili_Engine' ) ) {
		return;
	}
	$r = promen_catalog_search_reconcile();
	if ( ! empty( $r['ok'] ) ) {
		return;
	}
	$meili = new Promen_Meili_Engine();
	if ( ! $meili->health() ) {
		error_log( 'PROM-EN reconcile: Meilisearch недоступен, drift=' . (int) ( $r['drift'] ?? -1 ) );
		return;
	}
	$meili->drop_index();
	$n = $meili->reindex_all();
	error_log( sprintf( 'PROM-EN reconcile: drift=%d, переиндексировано %d документов.', (int) ( $r['drift'] ?? -1 ), $n ) );
} );
