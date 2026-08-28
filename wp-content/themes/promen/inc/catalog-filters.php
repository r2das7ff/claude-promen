<?php
/**
 * Серверные фильтры каталога (v2).
 *
 * Модель фильтров:
 *   — DN, PN            — числовые ДИАПАЗОНЫ: dn_min/dn_max, pn_min/pn_max;
 *   — Сталь, Угол, ГОСТ — МУЛЬТИВЫБОР (comma-separated слаги): steel, angle, gost;
 *   — group             — категория из сайдбара (product_cat), q — поиск.
 *
 * Нормализация «на показе»: мусорные значения источника (склеенные DN,
 * DN=0, нестандартные углы 681/1021) не попадают в опции фильтров и в
 * границы диапазонов. Товары при этом не меняются.
 */

defined( 'ABSPATH' ) || exit;

/** Числовые фильтры-диапазоны: param => taxonomy (ряды из терминов атрибутов). */
function promen_range_taxonomies(): array {
	return [ 'dn' => 'pa_dn', 'pn' => 'pa_pn' ];
}

/** Все диапазонные параметры (стенка s — без таксономии, ряд из канона). */
function promen_range_params(): array {
	return [ 'dn', 'pn', 's' ];
}

/** Мультивыбор: param => taxonomy. */
function promen_multi_taxonomies(): array {
	return [ 'steel' => 'pa_steel', 'industry' => 'promen_industry', 'angle' => 'pa_angle', 'gost' => 'norm' ];
}

/** Допустимые углы отвода (остальное — мусор источника). */
function promen_valid_angles(): array {
	return [ 15, 30, 45, 60, 90, 180 ];
}

/** Версия кэша фильтров (инвалидируется на изменениях каталога). */
function promen_filters_cache_version(): string {
	$v = get_option( 'promen_filters_cache_version', '' );
	if ( ! is_string( $v ) || $v === '' ) {
		$v = (string) time();
		update_option( 'promen_filters_cache_version', $v, false );
	}
	return $v;
}

/** Инвалидация кэша фильтров. */
function promen_filters_cache_bump(): void {
	update_option( 'promen_filters_cache_version', (string) time(), false );
}

/** Ключ transient для фильтров. */
function promen_filters_cache_key( string $prefix, array $parts = [] ): string {
	$raw = wp_json_encode( [ $prefix, promen_filters_cache_version(), $parts ], JSON_UNESCAPED_UNICODE );
	return 'promen_f_' . md5( $raw ?: $prefix );
}

add_action( 'save_post_product', 'promen_filters_cache_bump', 99 );
add_action( 'deleted_post', function ( int $post_id ) {
	if ( get_post_type( $post_id ) === 'product' ) {
		promen_filters_cache_bump();
	}
}, 99 );
add_action( 'edited_terms', 'promen_filters_cache_bump', 99 );


/** Категории с отдельной страницей категории (taxonomy-product_cat-<slug>.php). */
function promen_section_landing_slugs(): array {
	if ( function_exists( 'promen_catalog_page_slugs' ) ) {
		return promen_catalog_page_slugs();
	}
	return [ 'sdt', 'otvody', 'troyniki', 'perekhody', 'dnishcha', 'zaglushki', 'krepezh', 'flancy', 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty', 'tochenye', 'truby', 'izolyatsiya', 'opory', 'armatura' ];
}

/** Активная группа сайдбара на корне каталога (слаг product_cat или ''). */
function promen_active_group(): string {
	return ! empty( $_GET['group'] ) ? sanitize_title( wp_unslash( $_GET['group'] ) ) : '';
}

/** Валиден ли числовой термин фильтра (не склейка, не ноль). */
function promen_is_valid_numeric_name( string $name ): bool {
	if ( ! preg_match( '/^\d+(\.\d+)?$/', $name ) ) {
		return false; // склейки вида «1.01.21.6…»
	}
	return (float) $name > 0;
}

/**
 * Валидные числовые термины таксономии: [ ['val','name','slug','count'] ],
 * отсортированы по возрастанию. Кэш на запрос.
 */
function promen_numeric_terms( string $tax ): array {
	static $cache = [];
	if ( isset( $cache[ $tax ] ) ) {
		return $cache[ $tax ];
	}
	// Потолки значений: выше — «склейки» импорта (pa_pn «1020100» = 10+20+100).
	$max_val = [ 'pa_dn' => 2600, 'pa_pn' => 2000 ];

	$out = [];
	foreach ( get_terms( [ 'taxonomy' => $tax, 'hide_empty' => true, 'number' => 0 ] ) as $t ) {
		if ( is_wp_error( $t ) || ! promen_is_valid_numeric_name( $t->name ) ) {
			continue;
		}
		$val = (float) $t->name;
		// DN — только целые ≥ 1 (0.63/0.81/… — ошибочно попавший наружный диаметр).
		if ( $tax === 'pa_dn' && ( $val < 1 || floor( $val ) !== $val ) ) {
			continue;
		}
		if ( $val > ( $max_val[ $tax ] ?? INF ) ) {
			continue;
		}
		$out[] = [ 'val' => $val, 'name' => $t->name, 'slug' => $t->slug, 'count' => (int) $t->count ];
	}
	usort( $out, fn( $a, $b ) => $a['val'] <=> $b['val'] );
	return $cache[ $tax ] = $out;
}

/** Активные диапазоны: [ 'dn'=>['min'=>?float,'max'=>?float], … ] (только заданные). */
function promen_active_ranges(): array {
	$out = [];
	foreach ( promen_range_params() as $param ) {
		$min = isset( $_GET[ $param . '_min' ] ) && $_GET[ $param . '_min' ] !== '' ? (float) $_GET[ $param . '_min' ] : null;
		$max = isset( $_GET[ $param . '_max' ] ) && $_GET[ $param . '_max' ] !== '' ? (float) $_GET[ $param . '_max' ] : null;
		if ( null !== $min || null !== $max ) {
			$out[ $param ] = [ 'min' => $min, 'max' => $max ];
		}
	}
	return $out;
}

/** Активный мультивыбор: [ 'steel'=>['09g2s','20'], … ] (только заданные). */
function promen_active_multi(): array {
	$out = [];
	foreach ( promen_multi_taxonomies() as $param => $tax ) {
		if ( empty( $_GET[ $param ] ) ) {
			continue;
		}
		$slugs = array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET[ $param ] ) ) ) );
		if ( $slugs ) {
			$out[ $param ] = array_values( array_unique( $slugs ) );
		}
	}
	return $out;
}

/**
 * Опубликованные product IDs в скоупе категории (кэш на 15 минут).
 *
 * @return int[]
 */
function promen_scope_product_ids( int $cat_id ): array {
	$ckey   = promen_filters_cache_key( 'scope_ids', [ $cat_id ] );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return array_map( 'intval', $cached );
	}

	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	];
	if ( $cat_id ) {
		$args['tax_query'] = [
			[
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => array_merge( [ $cat_id ], get_term_children( $cat_id, 'product_cat' ) ),
				'include_children' => true,
			],
		];
	}
	$ids = array_map( 'intval', get_posts( $args ) );
	set_transient( $ckey, $ids, 15 * MINUTE_IN_SECONDS );
	return $ids;
}

/**
 * IDs товаров по выбранным отраслям в рамках скоупа.
 *
 * @param string[] $industry_slugs
 * @return int[]
 */
function promen_industry_matching_ids( array $industry_slugs, int $cat_id ): array {
	$industry_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', $industry_slugs ) ) ) );
	if ( ! $industry_slugs ) {
		return [];
	}
	sort( $industry_slugs );
	$ckey   = promen_filters_cache_key( 'industry_match_ids', [ $cat_id, implode( ',', $industry_slugs ) ] );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return array_map( 'intval', $cached );
	}

	$ids   = promen_scope_product_ids( $cat_id );
	$match = [];
	foreach ( $ids as $pid ) {
		$pind = function_exists( 'promen_product_industry_slugs' ) ? promen_product_industry_slugs( (int) $pid ) : [];
		if ( array_intersect( $industry_slugs, $pind ) ) {
			$match[] = (int) $pid;
		}
	}
	set_transient( $ckey, $match, 15 * MINUTE_IN_SECONDS );
	return $match;
}

/** Есть ли активные фильтры/поиск (для canonical и сводки). */
function promen_has_filters(): bool {
	return promen_active_ranges() || promen_active_multi() || ! empty( $_GET['q'] );
}

/** Слаги числовых терминов, попадающих в диапазон [min,max]. */
function promen_range_slugs( string $param, ?float $min, ?float $max ): array {
	$tax   = promen_range_taxonomies()[ $param ] ?? '';
	$slugs = [];
	foreach ( promen_numeric_terms( $tax ) as $t ) {
		if ( ( null === $min || $t['val'] >= $min ) && ( null === $max || $t['val'] <= $max ) ) {
			$slugs[] = $t['slug'];
		}
	}
	return $slugs;
}

// ── Применение к запросу ─────────────────────────────────────────

/** Облегчить main query, когда список рендерится из канона. */
add_action( 'pre_get_posts', function ( WP_Query $q ): void {
	if ( is_admin() || ! $q->is_main_query() || ! promen_catalog_uses_layer() ) {
		return;
	}
	if ( ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) || $q->is_tax( 'norm' ) ) ) {
		return;
	}
	if ( $q->is_tax( 'product_cat' ) && ! promen_has_filters() && ! $q->get( 'paged' ) ) {
		$term = get_term( (int) $q->get_queried_object_id(), 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && in_array( $term->slug, promen_section_landing_slugs(), true ) ) {
			return;
		}
	}
	$q->set( 'post__in', [ 0 ] );
	$q->set( 'posts_per_page', 1 );
	$q->set( 'no_found_rows', true );
}, 5 );

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) || $q->is_tax( 'norm' ) ) ) {
		return;
	}
	if ( promen_catalog_uses_layer() ) {
		return;
	}

	// Страница категории раздела (/catalog/sdt/): список не грузим — реестр на корне каталога.
	if ( $q->is_tax( 'product_cat' ) && ! promen_has_filters() && ! $q->get( 'paged' ) ) {
		$term = get_term( (int) $q->get_queried_object_id(), 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && in_array( $term->slug, promen_section_landing_slugs(), true ) ) {
			$q->set( 'posts_per_page', 1 );
			$q->set( 'no_found_rows', true );
			return;
		}
	}

	$q->set( 'posts_per_page', 30 );

	$tax_query = $q->get( 'tax_query' ) ?: [];
	if ( ! is_array( $tax_query ) ) {
		$tax_query = [];
	}

	// Группа сайдбара на корне каталога.
	if ( $q->is_post_type_archive( 'product' ) ) {
		$group = promen_active_group();
		if ( $group !== '' ) {
			$tax_query[] = [ 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => [ $group ], 'include_children' => true ];
		}
	}

	// Диапазоны DN/PN → набор слагов (пустой набор = заведомо ничего, чтобы не «протекало»).
	// Стенка s таксономии не имеет — в legacy-режиме WP_Query не фильтрует по ней.
	foreach ( promen_active_ranges() as $param => $r ) {
		$tax = promen_range_taxonomies()[ $param ] ?? '';
		if ( $tax === '' ) {
			continue;
		}
		$slugs = promen_range_slugs( $param, $r['min'], $r['max'] );
		$tax_query[] = [ 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $slugs ?: [ '__none__' ] ];
	}

	// Мультивыбор → OR внутри группы, AND между группами.
	// Сталь — по «эффективным» маркам (вариации / material_grade), не по всем pa_steel родителя.
	foreach ( promen_active_multi() as $param => $slugs ) {
		if ( $param === 'steel' ) {
			continue;
		}
		$tax_query[] = [ 'taxonomy' => promen_multi_taxonomies()[ $param ], 'field' => 'slug', 'terms' => $slugs, 'operator' => 'IN' ];
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	if ( $tax_query ) {
		$q->set( 'tax_query', $tax_query );
	}

	if ( ! empty( $_GET['q'] ) ) {
		$q->set( 's', sanitize_text_field( wp_unslash( $_GET['q'] ) ) );
	}
} );

/**
 * Фильтр по стали: variable — через attribute_pa_steel вариаций;
 * simple — material_grade в _promen_dims или единственный pa_steel.
 */
add_filter( 'posts_where', function ( string $where, WP_Query $q ): string {
	if ( is_admin() || ! $q->is_main_query() ) {
		return $where;
	}
	if ( ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) || $q->is_tax( 'norm' ) ) ) {
		return $where;
	}

	$steel = promen_active_multi()['steel'] ?? [];
	if ( ! $steel ) {
		return $where;
	}

	global $wpdb;
	$in = implode( ',', array_map( static function ( string $slug ): string {
		return "'" . esc_sql( $slug ) . "'";
	}, $steel ) );

	$json_parts = [];
	foreach ( $steel as $slug ) {
		$label = promen_term_label( 'pa_steel', $slug );
		if ( $label === $slug && ! isset( promen_term_map( 'pa_steel' )[ $slug ] ) ) {
			continue;
		}
		$name = esc_sql( $label );
		$json_parts[] = "pm.meta_value LIKE '%\"material_grade\":\"{$name}\"%'";
		$json_parts[] = "pm.meta_value LIKE '%\"material\":\"{$name}\"%'";
	}
	$json_sql = $json_parts ? implode( ' OR ', $json_parts ) : '0';

	$where .= " AND (
		EXISTS (
			SELECT 1 FROM {$wpdb->posts} v
			INNER JOIN {$wpdb->postmeta} vm ON v.ID = vm.post_id
			  AND vm.meta_key = 'attribute_pa_steel' AND vm.meta_value IN ({$in})
			WHERE v.post_parent = {$wpdb->posts}.ID
			  AND v.post_type = 'product_variation' AND v.post_status = 'publish'
		)
		OR (
			NOT EXISTS (
				SELECT 1 FROM {$wpdb->posts} vx
				WHERE vx.post_parent = {$wpdb->posts}.ID
				  AND vx.post_type = 'product_variation' AND vx.post_status = 'publish'
				LIMIT 1
			)
			AND (
				EXISTS (
					SELECT 1 FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'pa_steel'
					INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id AND t.slug IN ({$in})
					WHERE tr.object_id = {$wpdb->posts}.ID
					GROUP BY tr.object_id
					HAVING COUNT(DISTINCT t.slug) = 1
				)
				OR EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm
					WHERE pm.post_id = {$wpdb->posts}.ID AND pm.meta_key = '_promen_dims'
					  AND ({$json_sql})
				)
			)
		)
	)";

	return $where;
}, 10, 2 );

// ── Опции и счётчики (с учётом текущей категории/группы) ──────────

/** Термин-скоуп текущего вида: id категории или '' для всего каталога. */
function promen_scope_cat_id(): int {
	if ( is_tax( 'product_cat' ) ) {
		return (int) get_queried_object_id();
	}
	if ( is_shop() || is_post_type_archive( 'product' ) ) {
		$group = promen_active_group();
		if ( $group !== '' ) {
			$map = promen_term_map( 'product_cat' );
			if ( isset( $map[ $group ] ) ) {
				return $map[ $group ]['id'];
			}
		}
	}
	return 0;
}

/**
 * Счётчики терминов таксономии в пределах категории-скоупа.
 * Возвращает [ slug => ['name','count'] ]. Без скоупа — глобально.
 */
function promen_scoped_counts( string $tax, int $cat_id ): array {
	$ckey   = promen_filters_cache_key( 'scoped_counts', [ $tax, $cat_id ] );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	if ( $cat_id ) {
		$cat_ids = array_map( 'intval', array_merge( [ $cat_id ], get_term_children( $cat_id, 'product_cat' ) ) );
		$ph      = implode( ',', array_fill( 0, count( $cat_ids ), '%d' ) );
		$sql     = $wpdb->prepare(
			"SELECT t.slug, t.name, COUNT(DISTINCT tr.object_id) AS cnt
			 FROM {$wpdb->terms} t
			 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id AND tt.taxonomy = %s
			 INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_type = 'product' AND p.post_status = 'publish'
			 INNER JOIN {$wpdb->term_relationships} trc ON trc.object_id = p.ID
			 INNER JOIN {$wpdb->term_taxonomy} ttc ON ttc.term_taxonomy_id = trc.term_taxonomy_id
			   AND ttc.taxonomy = 'product_cat' AND ttc.term_id IN ($ph)
			 GROUP BY t.term_id HAVING cnt > 0",
			array_merge( [ $tax ], $cat_ids )
		);
		$rows = $wpdb->get_results( $sql );
	} else {
		$rows = array_map(
			fn( $t ) => (object) [ 'slug' => $t->slug, 'name' => $t->name, 'cnt' => $t->count ],
			get_terms( [ 'taxonomy' => $tax, 'hide_empty' => true, 'number' => 0 ] ) ?: []
		);
	}
	$out = [];
	foreach ( $rows as $r ) {
		$out[ $r->slug ] = [ 'name' => $r->name, 'count' => (int) $r->cnt ];
	}
	set_transient( $ckey, $out, 15 * MINUTE_IN_SECONDS );
	return $out;
}

/**
 * Опции числового диапазона (from/to): отсортированные валидные значения в скоупе.
 *
 * @param string|null $group Слаг группы; null — скоуп из текущего WP-запроса
 *                           (в REST-контексте is_tax/is_shop ложны — там группу
 *                           нужно передавать явно, иначе ряд будет глобальным).
 */
function promen_range_options( string $param, ?string $group = null ): array {
	if ( null === $group ) {
		if ( is_tax( 'product_cat' ) ) {
			$qo    = get_queried_object();
			$group = $qo instanceof WP_Term ? $qo->slug : '';
		} else {
			$group = promen_active_group();
		}
	}
	return promen_canon_range_options( $param, $group );
}

/**
 * Ряд значений диапазона — из КАНОНА (DISTINCT по группе), не из терминов
 * атрибутов: в pa_pn импорт нанёс склейки и годы стандартов (1979, 2456,
 * 1020100…), а канон-колонки валидируются потолками promen_catalog_field_max.
 * Transient с версией фильтров.
 *
 * @return array<int, array{val: float, name: string}>
 */
function promen_canon_range_options( string $param, string $group ): array {
	if ( ! in_array( $param, promen_range_params(), true ) ) {
		return [];
	}
	static $cache = [];
	$mkey = $param . '|' . $group;
	if ( isset( $cache[ $mkey ] ) ) {
		return $cache[ $mkey ];
	}
	$ckey   = promen_filters_cache_key( 'canon_range', [ $param, $group ] );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cache[ $mkey ] = $cached;
	}

	global $wpdb;
	$table = promen_catalog_table_name();
	$col   = $param; // dn|pn|s — колонки канона
	$slugs = $group !== '' ? promen_catalog_group_slugs( $group ) : [];
	if ( $slugs ) {
		$ph   = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$vals = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT {$col} FROM {$table} WHERE {$col} IS NOT NULL AND {$col} > 0 AND category IN ({$ph}) ORDER BY {$col} ASC", $slugs ) );
	} else {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$vals = $wpdb->get_col( "SELECT DISTINCT {$col} FROM {$table} WHERE {$col} IS NOT NULL AND {$col} > 0 ORDER BY {$col} ASC" );
	}

	$opts = [];
	foreach ( $vals ?: [] as $v ) {
		$f = (float) $v;
		// 2 → «2», 2.50 → «2,5» — как в ячейках реестра.
		$name   = rtrim( rtrim( number_format( $f, 2, '.', '' ), '0' ), '.' );
		$opts[] = [ 'val' => $f, 'name' => str_replace( '.', ',', $name ) ];
	}
	// Пустоту не кэшируем: запрос в окне миграции не должен залипать на 15 мин.
	if ( $opts ) {
		set_transient( $ckey, $opts, 15 * MINUTE_IN_SECONDS );
	}
	return $cache[ $mkey ] = $opts;
}

/**
 * Счётчики марок стали в скоупе категории (эффективные марки, не «корзина» родителя).
 *
 * @return array<string, array{name: string, count: int}>
 */
function promen_scoped_steel_counts( int $cat_id ): array {
	static $cache = [];
	if ( isset( $cache[ $cat_id ] ) ) {
		return $cache[ $cat_id ];
	}

	$ckey   = promen_filters_cache_key( 'steel_counts', [ $cat_id ] );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cache[ $cat_id ] = $cached;
	}

	global $wpdb;
	$counts = [];
	$term_names = [];
	$steel_terms = get_terms( [ 'taxonomy' => 'pa_steel', 'hide_empty' => false, 'number' => 0 ] );
	foreach ( $steel_terms ?: [] as $st ) {
		if ( ! is_wp_error( $st ) ) {
			$term_names[ $st->slug ] = $st->name;
		}
	}

	$cat_join    = '';
	$cat_prepare = [];
	if ( $cat_id ) {
		$cat_ids     = array_map( 'intval', array_merge( [ $cat_id ], get_term_children( $cat_id, 'product_cat' ) ) );
		$placeholders = implode( ',', array_fill( 0, count( $cat_ids ), '%d' ) );
		$cat_join    = "INNER JOIN {$wpdb->term_relationships} trc ON trc.object_id = p.ID
			INNER JOIN {$wpdb->term_taxonomy} ttc ON ttc.term_taxonomy_id = trc.term_taxonomy_id
			  AND ttc.taxonomy = 'product_cat' AND ttc.term_id IN ({$placeholders})";
		$cat_prepare = $cat_ids;
	}

	$sql_var = "SELECT vm.meta_value AS slug, COUNT(DISTINCT p.ID) AS cnt
		FROM {$wpdb->posts} v
		INNER JOIN {$wpdb->postmeta} vm ON v.ID = vm.post_id
		  AND vm.meta_key = 'attribute_pa_steel' AND vm.meta_value <> ''
		INNER JOIN {$wpdb->posts} p ON p.ID = v.post_parent
		  AND p.post_type = 'product' AND p.post_status = 'publish'
		{$cat_join}
		WHERE v.post_type = 'product_variation' AND v.post_status = 'publish'
		GROUP BY vm.meta_value";

	$rows_var = $cat_prepare ? $wpdb->get_results( $wpdb->prepare( $sql_var, $cat_prepare ) ) : $wpdb->get_results( $sql_var );
	foreach ( $rows_var ?: [] as $row ) {
		$slug = (string) $row->slug;
		if ( ! isset( $counts[ $slug ] ) ) {
			$counts[ $slug ] = [ 'name' => $term_names[ $slug ] ?? $slug, 'count' => 0 ];
		}
		$counts[ $slug ]['count'] += (int) $row->cnt;
	}

	$sql_simple = "SELECT p.ID
		FROM {$wpdb->posts} p
		{$cat_join}
		WHERE p.post_type = 'product' AND p.post_status = 'publish'
		  AND NOT EXISTS (
			SELECT 1 FROM {$wpdb->posts} vx
			WHERE vx.post_parent = p.ID
			  AND vx.post_type = 'product_variation' AND vx.post_status = 'publish'
			LIMIT 1
		  )";

	$simple_ids = $cat_prepare ? $wpdb->get_col( $wpdb->prepare( $sql_simple, $cat_prepare ) ) : $wpdb->get_col( $sql_simple );
	foreach ( $simple_ids ?: [] as $pid ) {
		foreach ( promen_product_steel_slugs( (int) $pid ) as $slug ) {
			if ( ! isset( $counts[ $slug ] ) ) {
				$counts[ $slug ] = [ 'name' => $term_names[ $slug ] ?? $slug, 'count' => 0 ];
			}
			$counts[ $slug ]['count']++;
		}
	}

	set_transient( $ckey, $counts, 15 * MINUTE_IN_SECONDS );
	return $cache[ $cat_id ] = $counts;
}

/** Опции мультивыбора: [ ['slug','name','count'] ] валидные, в скоупе, по убыв. count. */
function promen_multi_options( string $param, int $limit = 40 ): array {
	if ( $param === 'industry' && promen_catalog_uses_layer() && function_exists( 'promen_catalog_page_result' ) && function_exists( 'promen_industry_facet_options' ) ) {
		return promen_industry_facet_options( promen_catalog_page_result()->facets['industry'] ?? [] );
	}
	if ( $param === 'industry' && function_exists( 'promen_industry_filter_options' ) ) {
		return promen_industry_filter_options( promen_scope_cat_id() );
	}
	if ( promen_catalog_uses_layer() && function_exists( 'promen_catalog_page_result' ) ) {
		return promen_catalog_facet_options( $param, promen_catalog_page_result()->facets, $limit );
	}

	$tax          = promen_multi_taxonomies()[ $param ] ?? '';
	$cat_id       = promen_scope_cat_id();
	if ( $param === 'steel' ) {
		$counts = promen_scoped_steel_counts( $cat_id );
	} else {
		$counts = promen_scoped_counts( $tax, $cat_id );
	}
	$valid_angles = array_map( 'strval', promen_valid_angles() );

	$opts = [];
	foreach ( $counts as $slug => $d ) {
		if ( $param === 'angle' && ! in_array( $d['name'], $valid_angles, true ) ) {
			continue; // мусорные углы
		}
		$opts[] = [ 'slug' => $slug, 'name' => $d['name'], 'count' => $d['count'] ];
	}
	// Стабильный порядок (не по счётчику) — чипы не «скачут» при выборе фильтров.
	$opts = promen_catalog_sort_facet_options( $opts, $param );
	return array_slice( $opts, 0, $limit );
}

// ── URL-хелперы ──────────────────────────────────────────────────

/** Все параметры фильтров (для сброса/сохранения). */
function promen_filter_param_keys(): array {
	$keys = [ 'q', 'paged' ];
	foreach ( promen_range_params() as $p ) {
		$keys[] = $p . '_min';
		$keys[] = $p . '_max';
	}
	foreach ( array_keys( promen_multi_taxonomies() ) as $p ) {
		$keys[] = $p;
	}
	return $keys;
}

/** Базовый URL текущего вида без фильтров, но с сохранением group. */
function promen_filters_base_url(): string {
	$url = remove_query_arg( promen_filter_param_keys() );
	return $url;
}

/** Текущее состояние фильтров как query-массив (для модификации). */
function promen_current_filter_args(): array {
	$args = [];
	foreach ( promen_active_ranges() as $p => $r ) {
		if ( null !== $r['min'] ) {
			$args[ $p . '_min' ] = $r['min'];
		}
		if ( null !== $r['max'] ) {
			$args[ $p . '_max' ] = $r['max'];
		}
	}
	foreach ( promen_active_multi() as $p => $slugs ) {
		$args[ $p ] = implode( ',', $slugs );
	}
	if ( ! empty( $_GET['q'] ) ) {
		$args['q'] = sanitize_text_field( wp_unslash( $_GET['q'] ) );
	}
	return $args;
}

/** URL с переключённым значением мультивыбора (добавить/убрать), paged сброшен. */
function promen_multi_toggle_url( string $param, string $slug ): string {
	$args    = promen_current_filter_args();
	$current = promen_active_multi()[ $param ] ?? [];
	$current = in_array( $slug, $current, true )
		? array_values( array_diff( $current, [ $slug ] ) )
		: array_merge( $current, [ $slug ] );
	if ( $current ) {
		$args[ $param ] = implode( ',', $current );
	} else {
		unset( $args[ $param ] );
	}
	return promen_build_filter_url( $args );
}

/** URL со сброшенным конкретным фильтром (диапазон или мультивыбор). */
function promen_clear_param_url( string $param ): string {
	$args = promen_current_filter_args();
	unset( $args[ $param ], $args[ $param . '_min' ], $args[ $param . '_max' ] );
	return promen_build_filter_url( $args );
}

/** Полный сброс фильтров (group сохраняется). */
function promen_reset_url(): string {
	$args = [];
	if ( ! empty( $_GET['q'] ) ) {
		// поиск оставляем? нет — «сбросить всё» чистит и поиск.
	}
	return promen_build_filter_url( [] );
}

/** Собрать URL из args поверх базового (group подставляется автоматически). */
function promen_build_filter_url( array $args ): string {
	$base  = promen_filters_base_url();
	$group = promen_active_group();
	if ( $group !== '' && ( is_shop() || is_post_type_archive( 'product' ) ) ) {
		$args['group'] = $group;
	}
	if ( ! $args ) {
		return $base;
	}
	return add_query_arg( array_map( 'rawurlencode', $args ), $base );
}

/**
 * Заголовки и хлебные крошки групп каталога (сайдбар + шапка таблицы).
 * term_url/term_name — из карт терминов (никаких get_term_by на группу).
 *
 * @return array<string, array{title: string, path: string, term_url: string, term_name: string}>
 */
function promen_catalog_group_views(): array {
	static $views = null;
	if ( null !== $views ) {
		return $views;
	}
	$views = [
		'' => [ 'title' => 'РЕЕСТР ИЗДЕЛИЙ', 'path' => '/ Весь реестр', 'term_url' => '', 'term_name' => '' ],
	];
	if ( function_exists( 'promen_catalog_taxonomy_defs' ) ) {
		$map   = promen_term_map( 'product_cat' );
		$links = promen_product_cat_links();
		foreach ( promen_catalog_taxonomy_defs() as $slug => $def ) {
			$views[ $slug ] = [
				'title'     => $def['title'],
				'path'      => $def['path'],
				'term_url'  => $links[ $slug ] ?? '',
				'term_name' => $map[ $slug ]['name'] ?? '',
			];
		}
	}
	return $views;
}

/** JSON-вид group_views для catalog.js. */
function promen_catalog_group_views_js(): array {
	$out = [];
	foreach ( promen_catalog_group_views() as $slug => $view ) {
		$out[ $slug ] = [
			'title'    => $view['title'],
			'path'     => $view['path'],
			'termUrl'  => $view['term_url'],
			'termName' => $view['term_name'],
		];
	}
	return $out;
}

/**
 * URL фильтра группы на корне каталога (?group=).
 * Страница категории (ЧПУ) — отдельно через termUrl / «Страница категории →».
 */
function promen_group_filter_url( ?string $group_slug ): string {
	$base = wc_get_page_permalink( 'shop' );
	if ( ! $group_slug ) {
		return $base;
	}
	return add_query_arg( [ 'group' => rawurlencode( $group_slug ) ], $base );
}

/**
 * Нормативы каталога: ключ сопоставления → куда вести фильтр.
 *
 * [ 'ГОСТ 17375' => [ 'g' => 'gost-17375-2001', 'c' => 'otvody', 'n' => 1025 ] ]
 *   g — слаги фасета `gost` (через запятую: у одного документа в каталоге
 *       бывает несколько терминов-дублей — «ost-34-10-432-90» и
 *       «ost-34-10-432-1990»), c — категория, где позиций больше всего,
 *       n — сколько строк канона под этим документом.
 *
 * Считается по канон-таблице, а не по терминам таксономии: фильтр каталога
 * читает именно её, и норматив без строк канона не должен давать ссылку в
 * пустую выдачу. Отдаётся в «Нормативную базу» (promenNB.norms), чтобы
 * кнопка «Открыть в каталоге» открывала каталог сразу с этим документом
 * в фильтре, а не общий вид раздела.
 */
function promen_norm_catalog_index(): array {
	$ckey   = promen_filters_cache_key( 'norm_index' );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$table  = promen_catalog_table_name();
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	$rows   = $exists
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		? (array) $wpdb->get_results( "SELECT norm_slug, category, COUNT(*) AS n FROM {$table} WHERE norm_slug <> '' GROUP BY norm_slug, category", ARRAY_A )
		: [];

	$acc = [];
	foreach ( $rows as $row ) {
		$key = promen_norm_match_key( promen_term_label( 'norm', (string) $row['norm_slug'] ) );
		if ( '' === $key ) {
			continue;
		}
		$n = (int) $row['n'];
		$acc[ $key ]['slugs'][ (string) $row['norm_slug'] ] = true;
		$acc[ $key ]['cats'][ (string) $row['category'] ]   = ( $acc[ $key ]['cats'][ (string) $row['category'] ] ?? 0 ) + $n;
		$acc[ $key ]['n'] = ( $acc[ $key ]['n'] ?? 0 ) + $n;
	}

	$index = [];
	foreach ( $acc as $key => $e ) {
		arsort( $e['cats'] );
		$index[ $key ] = [
			'g' => implode( ',', array_keys( $e['slugs'] ) ),
			'c' => (string) array_key_first( $e['cats'] ),
			'n' => (int) $e['n'],
		];
	}

	set_transient( $ckey, $index, 15 * MINUTE_IN_SECONDS );
	return $index;
}

/**
 * Сводка активных фильтров: [ ['label','value','clear_url'] ] для строки-резюме.
 *
 * У 'gost' подпись пустая намеренно. Значение фасета — полное обозначение
 * документа («ГОСТ 17375-2001», «ОСТ 24.125.03-89», «СТО ЦКТИ 321.03-2009»),
 * и приписка перед ним давала либо дубль («ГОСТ ГОСТ 17375-2001»), либо
 * прямую ошибку: ОСТ и СТО подписывались как ГОСТ.
 */
function promen_active_summary(): array {
	$labels  = [ 'dn' => 'DN', 'pn' => 'PN', 's' => 'Стенка', 'steel' => 'Сталь', 'industry' => 'Отрасль', 'angle' => 'Угол', 'gost' => '' ];
	$out     = [];
	foreach ( promen_active_ranges() as $p => $r ) {
		$val = ( null !== $r['min'] ? (float) $r['min'] : '…' ) . '–' . ( null !== $r['max'] ? (float) $r['max'] : '…' );
		$out[] = [ 'label' => $labels[ $p ], 'value' => $val, 'clear_url' => promen_clear_param_url( $p ) ];
	}
	foreach ( promen_active_multi() as $p => $slugs ) {
		foreach ( $slugs as $slug ) {
			if ( $p === 'industry' ) {
				$imap = function_exists( 'promen_industry_tag_labels' ) ? promen_industry_tag_labels() : [];
				$val  = $imap[ $slug ] ?? strtoupper( $slug );
				$out[] = [ 'label' => $labels[ $p ], 'value' => $val, 'clear_url' => promen_multi_toggle_url( $p, $slug ) ];
				continue;
			}
			$out[] = [ 'label' => $labels[ $p ], 'value' => promen_term_label( promen_multi_taxonomies()[ $p ], $slug ), 'clear_url' => promen_multi_toggle_url( $p, $slug ) ];
		}
	}
	return $out;
}

// ── Canonical / robots на архивах ────────────────────────────────

/*
 * Ядро WP отдаёт canonical только для singular — у товаров он есть, а у
 * архивов (главная, /catalog/, категории, нормативы, пагинация) не было
 * ни одного. Проверено на живом сайте 2026-08-25: 147 страниц без тега,
 * и `?utm_source=…` на категории отдавал 200 без canonical и без noindex,
 * то есть любая метка из рассылки плодила дубль раздела.
 *
 * Поэтому хук выводит self-canonical на всех архивах, а параметрические
 * виды (фильтр, поиск, ?group=) по-прежнему каноникалит на чистый адрес.
 */
add_action( 'wp_head', function () {
	if ( is_singular() ) {
		return; // ядро уже отдало canonical
	}

	$group    = promen_active_group();
	$filtered = promen_has_filters();

	if ( is_post_type_archive( 'product' ) || is_shop() ) {
		// Групповой вид (?group=otvody) каноникалим на страницу категории, а не на голый /catalog/.
		$url = ( $group !== '' ? promen_product_cat_link( $group ) : '' ) ?: get_post_type_archive_link( 'product' );
	} elseif ( is_tax( 'product_cat' ) || is_tax( 'norm' ) ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	} else {
		return;
	}

	if ( ! $url || is_wp_error( $url ) ) {
		return;
	}

	// Пагинация каноникалит на себя. Склейка на первую страницу увела бы
	// из индекса товары со второй и дальше — а их в разделе до 109 страниц.
	// У параметрических видов адрес уже подменён на чистый, туда номер не дописываем.
	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $paged > 1 && ! $filtered && '' === $group ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
	}

	echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	// noindex — только у параметрических (фильтр/поиск) видов; чистая категория индексируется.
	if ( $filtered ) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}, 1 );
