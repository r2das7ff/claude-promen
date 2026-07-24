<?php
/**
 * REST API каталога (hits + facets + columns).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
	register_rest_route( 'promen/v1', '/catalog', [
		'methods'             => 'GET',
		'callback'            => 'promen_rest_catalog',
		'permission_callback' => '__return_true',
	] );
} );

function promen_rest_catalog( WP_REST_Request $request ): WP_REST_Response {
	$params = [
		'group'    => $request->get_param( 'group' ),
		'q'        => $request->get_param( 'q' ),
		'dn_min'   => $request->get_param( 'dn_min' ),
		'dn_max'   => $request->get_param( 'dn_max' ),
		'pn_min'   => $request->get_param( 'pn_min' ),
		'pn_max'   => $request->get_param( 'pn_max' ),
		's_min'    => $request->get_param( 's_min' ),
		's_max'    => $request->get_param( 's_max' ),
		'steel'    => $request->get_param( 'steel' ),
		'industry' => $request->get_param( 'industry' ),
		'gost'     => $request->get_param( 'gost' ),
		'angle'    => $request->get_param( 'angle' ),
		'page'     => $request->get_param( 'page' ),
		'per_page' => $request->get_param( 'per_page' ),
		'sort'     => $request->get_param( 'sort' ),
		'scope'    => $request->get_param( 'scope' ),
	];

	$query  = Promen_Catalog_Query::from_array( $params );
	$result = promen_catalog_search( $query );

	$group   = $query->group;
	$columns = promen_catalog_schema_columns( $group );
	$ranges  = promen_catalog_schema_ranges( $group );
	$facets  = promen_catalog_schema_facets( $group );

	$hits = $result->hits;
	if ( $query->steel ) {
		foreach ( $hits as $i => $hit ) {
			$slugs = array_values( array_intersect( (array) ( $hit['steels'] ?? [] ), $query->steel ) );
			$labels = [];
			foreach ( $slugs as $slug ) {
				$labels[] = promen_term_label( 'pa_steel', (string) $slug );
			}
			if ( $labels ) {
				$hits[ $i ]['steel_display'] = implode( ', ', $labels );
			}
		}
	}
	$result->hits = $hits;

	$state = promen_catalog_filter_state( $query, $result );

	return new WP_REST_Response( [
		'hits'          => $result->hits,
		'total'         => $result->total,
		'page'          => $result->page,
		'per_page'      => $result->per_page,
		'pages'         => (int) ceil( $result->total / max( 1, $result->per_page ) ),
		'facets'        => $state['facet_options'],
		'range_options' => $state['range_options'],
		'columns'       => $columns,
		'ranges'        => $ranges,
		'facet_params'  => $facets,
		'engine'        => $result->engine,
		'group'         => $group,
	], 200 );
}

/**
 * Опции фасетов и ряды диапазонов для запроса — общая логика REST и SSR-партиала.
 *
 * Без активных фильтров — полный универсум группы (позиции опций стабильны,
 * нули серым). С активными фильтрами — режим сужения: опции, несовместимые
 * с выбором в ДРУГИХ группах, скрываются; распределение собственной группы
 * считается без её фильтра (disjunctive), чтобы внутри группы можно было
 * расширять выбор. Ряды DN/PN сужаются к реально доступным значениям —
 * слайдеры не предлагают пустых диапазонов.
 *
 * @return array{facet_options: array<string, array>, range_options: array<string, array>}
 */
function promen_catalog_filter_state( Promen_Catalog_Query $query, Promen_Catalog_Search_Result $result ): array {
	$group  = $query->group;
	$facets = promen_catalog_schema_facets( $group );
	$ranges = promen_catalog_schema_ranges( $group );

	$active      = promen_catalog_query_active_facets( $query );
	$has_filters = $active || $query->q !== '';
	$dj          = $active ? promen_catalog_disjunctive_facets( $query, $active ) : [];

	$merged = [];
	if ( $has_filters ) {
		foreach ( $facets as $param ) {
			$dist  = (array) ( $dj[ $param ] ?? $result->facets[ $param ] ?? [] );
			$sel   = in_array( $param, [ 'steel', 'gost', 'angle', 'industry' ], true ) ? (array) $query->{$param} : [];
			$clean = [];
			foreach ( $dist as $slug => $c ) {
				if ( (int) $c > 0 ) {
					$clean[ (string) $slug ] = (int) $c;
				}
			}
			// Выбранное не исчезает даже при нулевом счёте — иначе фильтр не снять.
			foreach ( $sel as $s ) {
				if ( ! isset( $clean[ $s ] ) ) {
					$clean[ $s ] = (int) ( $dist[ $s ] ?? 0 );
				}
			}
			$merged[ $param ] = $clean;
		}
	} else {
		// Полный универсум опций группы с наложением текущих счётчиков.
		$universe = promen_catalog_facet_universe( $group );
		foreach ( $facets as $param ) {
			$univ = (array) ( $universe[ $param ] ?? [] );
			$live = (array) ( $result->facets[ $param ] ?? [] );
			if ( ! $univ ) {
				$merged[ $param ] = $live;
				continue;
			}
			$dist = [];
			foreach ( $univ as $slug => $c ) {
				$dist[ $slug ] = (int) ( $live[ $slug ] ?? 0 );
			}
			foreach ( $live as $slug => $c ) {
				if ( ! isset( $dist[ $slug ] ) ) {
					$dist[ $slug ] = (int) $c;
				}
			}
			$merged[ $param ] = $dist;
		}
	}

	$facet_options = promen_rest_build_facet_options( $merged, $facets );
	if ( in_array( 'industry', $facets, true ) && function_exists( 'promen_industry_facet_options' ) ) {
		$ind_dist = (array) ( $dj['industry'] ?? $result->facets['industry'] ?? [] );
		$facet_options['industry'] = promen_industry_facet_options( array_map( 'intval', $ind_dist ) );
	}

	$range_options = [];
	foreach ( $ranges as $r ) {
		$full = function_exists( 'promen_range_options' ) ? promen_range_options( $r, $group ) : [];
		if ( $has_filters && $full ) {
			$dist = (array) ( $dj[ $r ] ?? $result->facets[ $r ] ?? [] );
			if ( $dist ) {
				$avail = [];
				foreach ( array_keys( $dist ) as $v ) {
					$avail[ (string) (float) $v ] = true;
				}
				$narrow = array_values( array_filter(
					$full,
					static fn( array $o ): bool => isset( $avail[ (string) (float) $o['val'] ] )
				) );
				if ( $narrow ) {
					$full = $narrow;
				}
			}
		}
		$range_options[ $r ] = $full;
	}

	return [ 'facet_options' => $facet_options, 'range_options' => $range_options ];
}

/**
 * Полный набор опций фасетов группы БЕЗ мультивыбора (стабильный универсум).
 * Кэшируется на 15 мин с версией фильтров. Нужен, чтобы опции steel/gost/angle
 * не исчезали при выборе других фильтров (позиции фиксированы).
 *
 * @return array<string, array<string, int>>
 */
function promen_catalog_facet_universe( string $group ): array {
	$ckey = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'facet_universe', [ $group ] )
		: 'promen_facet_universe_' . md5( $group );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	try {
		$q   = Promen_Catalog_Query::from_array( [ 'group' => $group, 'per_page' => 1 ] );
		$res = promen_catalog_search( $q );
		$out = $res->facets;
	} catch ( \Throwable $e ) {
		$out = [];
	}
	set_transient( $ckey, $out, 15 * MINUTE_IN_SECONDS );
	return $out;
}

/**
 * @param array<string, array<string, int>> $raw
 * @param string[] $allowed
 */
function promen_rest_build_facet_options( array $raw, array $allowed ): array {
	$out = [];
	foreach ( $allowed as $param ) {
		$dist = $raw[ $param ] ?? [];
		if ( ! $dist ) {
			$out[ $param ] = [];
			continue;
		}
		$opts = [];
		foreach ( $dist as $slug => $count ) {
			$name = promen_rest_facet_label( $param, (string) $slug );
			$opts[] = [
				'slug'  => (string) $slug,
				'name'  => $name,
				'count' => (int) $count,
			];
		}
		$out[ $param ] = promen_catalog_sort_facet_options( $opts, $param );
	}
	return $out;
}

function promen_rest_facet_label( string $param, string $slug ): string {
	if ( $param === 'steel' ) {
		return promen_term_label( 'pa_steel', $slug );
	}
	if ( $param === 'gost' ) {
		return promen_term_label( 'norm', $slug );
	}
	if ( $param === 'industry' ) {
		$labels = promen_industry_tag_labels();
		return $labels[ $slug ] ?? strtoupper( $slug );
	}
	return $slug;
}
