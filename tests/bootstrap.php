<?php
/**
 * PHPUnit bootstrap — чистая логика каталога без WordPress.
 */

// inc-файлы темы открываются guard'ом `defined('ABSPATH') || exit;` —
// без этой константы require молча завершает процесс (exit 0),
// и PHPUnit «зеленеет», не выполнив ни одного теста.
defined( 'ABSPATH' ) || define( 'ABSPATH', __DIR__ . '/' );

if ( ! function_exists( 'promen_is_fastener_group' ) ) {
	function promen_is_fastener_group( ?string $group = null ): bool {
		return in_array( (string) $group, [ 'krepezh', 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty' ], true );
	}
}

require_once __DIR__ . '/../wp-content/themes/promen/inc/catalog-schema.php';
require_once __DIR__ . '/../wp-content/themes/promen/inc/catalog-document.php';
require_once __DIR__ . '/stubs/catalog-search-stubs.php';
