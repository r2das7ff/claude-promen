<?php
/**
 * PHPUnit bootstrap — чистая логика каталога без WordPress.
 */

// inc-файлы темы открываются guard'ом `defined('ABSPATH') || exit;` —
// без этой константы require молча завершает процесс (exit 0),
// и PHPUnit «зеленеет», не выполнив ни одного теста.
defined( 'ABSPATH' ) || define( 'ABSPATH', __DIR__ . '/' );

// selector.php и product-data.php вешают хуки на уровне файла — заглушки
// нужны только чтобы require прошёл; сами хуки в юнит-тестах не участвуют.
if ( ! function_exists( 'add_action' ) ) {
	function add_action( ...$args ) {}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( ...$args ) {}
}

// product-data.php — справочники сортамента (Dн ↔ DN) и разбор размеров.
// Подключается раньше заглушки promen_is_fastener_group(): настоящее
// объявление там безусловное, и заглушка выше него вызвала бы redeclare.
require_once __DIR__ . '/../wp-content/themes/promen/inc/product-data.php';

if ( ! function_exists( 'promen_is_fastener_group' ) ) {
	function promen_is_fastener_group( ?string $group = null ): bool {
		return in_array( (string) $group, [ 'krepezh', 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty' ], true );
	}
}

require_once __DIR__ . '/../wp-content/themes/promen/inc/catalog-schema.php';
require_once __DIR__ . '/../wp-content/themes/promen/inc/catalog-terms.php';
require_once __DIR__ . '/../wp-content/themes/promen/inc/catalog-document.php';
require_once __DIR__ . '/stubs/catalog-search-stubs.php';

// Подборщик: под тест идёт только чистая логика (парсер строки и отбор марок),
// поэтому справочник марок подключается напрямую, без WordPress.
// Антиспам (mu-plugins/promen-antispam.php) подключается тем же приёмом:
// под тест идут только чистые эвристики promen_antispam_reason().
require_once __DIR__ . '/../wp-content/themes/promen/inc/steel-reference.php';
require_once __DIR__ . '/../wp-content/themes/promen/inc/selector.php';
