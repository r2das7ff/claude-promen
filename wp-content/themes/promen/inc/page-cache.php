<?php
/**
 * Сброс полностраничного кеша.
 *
 * Сам кеш живёт в wp-content/advanced-cache.php и подключается до загрузки
 * ядра — хуков там ещё нет, поэтому сброс вынесен сюда.
 *
 * Сбрасываем целиком, а не точечно: у каталога любая правка товара меняет
 * и страницу категории, и пагинацию, и реестр на /catalog/. Считать
 * зависимости дороже, чем прогреть кеш заново.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PROMEN_CACHE_DIR' ) ) {
	define( 'PROMEN_CACHE_DIR', WP_CONTENT_DIR . '/cache/promen-page' );
}

/** Рекурсивно удаляет содержимое каталога кеша. Возвращает число файлов. */
function promen_cache_purge(): int {
	if ( ! is_dir( PROMEN_CACHE_DIR ) ) {
		return 0;
	}
	$n = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( PROMEN_CACHE_DIR, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $it as $item ) {
		if ( $item->isDir() ) {
			@rmdir( $item->getPathname() );
		} elseif ( @unlink( $item->getPathname() ) ) {
			$n++;
		}
	}
	return $n;
}

/**
 * Сброс откладываем на конец запроса: при массовом импорте save_post
 * срабатывает тысячи раз, и чистить каталог на каждом — впустую жечь диск.
 */
function promen_cache_purge_later(): void {
	static $queued = false;
	if ( $queued ) {
		return;
	}
	$queued = true;
	add_action( 'shutdown', 'promen_cache_purge', 99 );
}

foreach ( [ 'save_post', 'deleted_post', 'trashed_post', 'untrashed_post' ] as $promen_cache_hook ) {
	add_action( $promen_cache_hook, 'promen_cache_purge_later' );
}
foreach ( [ 'created_term', 'edited_term', 'delete_term' ] as $promen_cache_hook ) {
	add_action( $promen_cache_hook, 'promen_cache_purge_later' );
}
add_action( 'switch_theme', 'promen_cache_purge_later' );
add_action( 'wp_update_nav_menu', 'promen_cache_purge_later' );
add_action( 'permalink_structure_changed', 'promen_cache_purge_later' );
// Обновление темы меняет вёрстку у всех страниц разом.
add_action( 'upgrader_process_complete', 'promen_cache_purge_later' );

/**
 * Кнопка «Сбросить кеш» в админ-панели: коллега правит сайт через панель
 * Timeweb и в админке, и ему нужен способ увидеть правку сразу.
 */
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$bar->add_node( [
		'id'    => 'promen-cache-purge',
		'title' => 'Сбросить кеш страниц',
		'href'  => wp_nonce_url( admin_url( '?promen_cache_purge=1' ), 'promen_cache_purge' ),
	] );
}, 100 );

add_action( 'admin_init', function () {
	if ( empty( $_GET['promen_cache_purge'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'promen_cache_purge' );
	$n = promen_cache_purge();
	wp_safe_redirect( add_query_arg( 'promen_cache_purged', $n, admin_url() ) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['promen_cache_purged'] ) ) {
		printf( '<div class="notice notice-success is-dismissible"><p>Кеш страниц сброшен: удалено файлов — %d.</p></div>',
			(int) $_GET['promen_cache_purged'] );
	}
} );

/** Команда для CLI: wp promen cache-purge */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'promen cache-purge', function () {
		WP_CLI::success( 'Удалено файлов кеша: ' . promen_cache_purge() );
	} );
}
