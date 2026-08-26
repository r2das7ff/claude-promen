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

/**
 * Транзиенты каталога, в которых лежат готовые ссылки.
 *
 * Обход 2026-08-26: после переименования слагов карточки продолжали
 * ссылаться на старые адреса — 521 внутренняя ссылка вела через 301.
 * Виноваты были не канон и не кеш страниц, а транзиенты `promen_series*`
 * с таблицами серий: они пережили и пересборку канона, и переезд базы.
 * Поэтому чистим их вместе со страничным кешем.
 *
 * `promen_dlc` (расчёты доставки) не трогаем — ссылок там нет.
 */
function promen_cache_flush_transients(): int {
	global $wpdb;
	return (int) $wpdb->query(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name REGEXP '^_transient_(timeout_)?promen_(series|f|grpcount)'"
	);
}

/** Рекурсивно удаляет содержимое каталога кеша. Возвращает число файлов. */
function promen_cache_purge(): int {
	promen_cache_flush_transients();
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
 * Уборка каталогов от прежних версий темы.
 *
 * Версия входит в путь кеша (см. advanced-cache.php), поэтому после заливки
 * темы старые файлы становятся недосягаемыми — ни отдать, ни перезаписать.
 * Сами по себе они не исчезнут: TTL их не трогает, читать их никто не будет.
 * Момент удачный: сразу после правки темы каждый запрос всё равно промах,
 * то есть WordPress загружается и этот хук отрабатывает.
 */
add_action( 'init', function () {
	if ( ! is_dir( PROMEN_CACHE_DIR ) || ! function_exists( 'promen_cache_theme_stamp' ) ) {
		return;
	}
	$current = 'v' . promen_cache_theme_stamp();
	foreach ( (array) glob( PROMEN_CACHE_DIR . '/v*', GLOB_ONLYDIR ) as $dir ) {
		if ( basename( $dir ) === $current ) {
			continue;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $it as $item ) {
			$item->isDir() ? @rmdir( $item->getPathname() ) : @unlink( $item->getPathname() );
		}
		@rmdir( $dir );
	}
}, 1 );

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
