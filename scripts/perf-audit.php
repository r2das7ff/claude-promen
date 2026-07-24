<?php
/**
 * Замер бэкенда каталога: SQL-запросы и время по компонентам.
 * Запуск: docker compose run --rm wpcli eval-file /scripts/perf-audit.php
 *
 * Печатает таблицу по компонентам + JSON-строку PERF_JSON для сравнения
 * baseline/after скриптом perf-audit.sh.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "wp eval-file only\n" );
}

/**
 * @param callable $fn
 * @return array{queries:int,ms:float}
 */
function promen_perf_measure( callable $fn ): array {
	global $wpdb;
	$q0 = (int) $wpdb->num_queries;
	$t0 = microtime( true );
	ob_start();
	$fn();
	ob_end_clean();
	return [
		'queries' => (int) $wpdb->num_queries - $q0,
		'ms'      => round( ( microtime( true ) - $t0 ) * 1000, 1 ),
	];
}

$group = 'otvody';

$components = [
	// Чистый поиск (Meili или SQL fallback) — без рендера.
	'search_only'    => static function () use ( $group ) {
		$q = Promen_Catalog_Query::from_array( [ 'group' => $group, 'per_page' => 30 ] );
		promen_catalog_search( $q );
	},
	// Живой реестр (embed страницы категории): фильтры + опции + 30 строк.
	'registry_embed' => static function () use ( $group ) {
		$_GET['group'] = $group;
		promen_render_category_catalog_embed( $group );
		unset( $_GET['group'] );
	},
	// REST /promen/v1/catalog — как его зовёт каждый клик фильтра.
	'rest_catalog'   => static function () use ( $group ) {
		$req = new WP_REST_Request( 'GET', '/promen/v1/catalog' );
		$req->set_param( 'group', $group );
		rest_do_request( $req );
	},
	// Секции страницы категории и сайдбар корня.
	's01_series'     => static function () use ( $group ) {
		promen_render_category_series_registry( $group );
	},
	's04_norms'      => static function () use ( $group ) {
		promen_render_category_norms_section( $group );
	},
	'sidebar'        => static function () {
		promen_render_catalog_sidebar( '' );
	},
	// Полная страница категории = сайдбар не нужен, но embed+s01+s04 вместе,
	// как их платит один HTTP-запрос (внутренние кэши греются между собой).
	'category_page'  => static function () use ( $group ) {
		$_GET['group'] = $group;
		promen_render_category_series_registry( $group );
		promen_render_category_catalog_embed( $group );
		promen_render_category_norms_section( $group );
		unset( $_GET['group'] );
	},
];

// Прогрев автозагрузки опций, чтобы не шуметь в замере.
promen_perf_measure( static function () {
	get_option( 'promen_filters_cache_version' );
} );

$engine = promen_catalog_search_engine()->engine_name();

// Один компонент на процесс: wp eval-file /scripts/perf-audit.php <component>
// (без аргумента — все подряд; тогда поздние греются кэшами ранних).
$only = isset( $args[0] ) && isset( $components[ $args[0] ] ) ? (string) $args[0] : '';

$out = [ 'engine' => $engine, 'group' => $group ];
foreach ( $components as $name => $fn ) {
	if ( $only !== '' && $name !== $only ) {
		continue;
	}
	$out[ $name ] = promen_perf_measure( $fn );
}

printf( "%-22s %10s %10s\n", 'component', 'queries', 'ms' );
foreach ( $out as $k => $v ) {
	if ( ! is_array( $v ) ) {
		continue;
	}
	printf( "%-22s %10d %10.1f\n", $k, $v['queries'], $v['ms'] );
}
printf( "engine: %s\n", $engine );
echo 'PERF_JSON ' . wp_json_encode( $out ) . "\n";
