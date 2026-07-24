<?php
/**
 * Каноническая таблица строк реестра каталога.
 */

defined( 'ABSPATH' ) || exit;

function promen_catalog_table_name(): string {
	global $wpdb;
	return $wpdb->prefix . 'promen_catalog_rows';
}

/** Создать/обновить таблицу канона. */
function promen_catalog_install_table(): void {
	global $wpdb;
	$table   = promen_catalog_table_name();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		product_id BIGINT UNSIGNED NOT NULL,
		sku VARCHAR(191) NOT NULL DEFAULT '',
		category VARCHAR(64) NOT NULL DEFAULT '',
		norm_slug VARCHAR(191) NOT NULL DEFAULT '',
		dn DOUBLE NULL,
		dn2 DOUBLE NULL,
		pn DOUBLE NULL,
		angle DOUBLE NULL,
		steels_json TEXT NOT NULL,
		industries_json TEXT NOT NULL,
		payload LONGTEXT NOT NULL,
		updated_at DATETIME NOT NULL,
		PRIMARY KEY (product_id),
		KEY category (category),
		KEY norm_slug (norm_slug),
		KEY dn (dn),
		KEY pn (pn)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'promen_catalog_db_version', '1.1.0', false );
}

function promen_catalog_maybe_install(): void {
	if ( get_option( 'promen_catalog_db_version' ) !== '1.1.0' ) {
		promen_catalog_install_table();
	}
}

/**
 * Upsert одной строки канона (+ синхронный push в поисковый индекс).
 *
 * @param bool $sync_search false — только канон-таблица: массовые прогоны
 *                          (rebuild) индексируют потом батчами через reindex_all.
 */
function promen_catalog_upsert( int $product_id, bool $sync_search = true ): bool {
	if ( function_exists( 'promen_sync_product_industries' ) ) {
		promen_sync_product_industries( $product_id );
	}
	$doc = promen_build_catalog_document( $product_id );
	if ( ! $doc ) {
		return promen_catalog_delete( $product_id );
	}

	global $wpdb;
	$table = promen_catalog_table_name();
	$now   = current_time( 'mysql', true );

	$wpdb->replace(
		$table,
		[
			'product_id'  => $product_id,
			'sku'         => $doc['sku'],
			'category'    => $doc['category'],
			'norm_slug'   => $doc['norm_slug'],
			'dn'          => $doc['dn'],
			'dn2'         => $doc['dn2'],
			'pn'          => $doc['pn'],
			'angle'       => $doc['angle'],
			'steels_json'     => wp_json_encode( $doc['steels'], JSON_UNESCAPED_UNICODE ),
			'industries_json' => wp_json_encode( $doc['industries'] ?? [], JSON_UNESCAPED_UNICODE ),
			'payload'         => wp_json_encode( $doc, JSON_UNESCAPED_UNICODE ),
			'updated_at'      => $now,
		],
		[ '%d', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s' ]
	);

	if ( $sync_search && function_exists( 'promen_catalog_search_push' ) ) {
		promen_catalog_search_push( $doc );
	}

	return true;
}

function promen_catalog_delete( int $product_id ): bool {
	global $wpdb;
	$wpdb->delete( promen_catalog_table_name(), [ 'product_id' => $product_id ], [ '%d' ] );
	if ( function_exists( 'promen_catalog_search_delete' ) ) {
		promen_catalog_search_delete( $product_id );
	}
	return true;
}

/** Пересобрать канон из всех опубликованных товаров. */
function promen_catalog_rebuild( ?callable $progress = null ): int {
	$ids = get_posts( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	] );

	$n = 0;
	foreach ( $ids as $pid ) {
		promen_sync_variable_parent_steels( (int) $pid );
		promen_catalog_upsert( (int) $pid, false );
		$n++;
		if ( $progress ) {
			$progress( $n, count( $ids ) );
		}
	}
	return $n;
}

/**
 * Синхронизировать pa_steel родителя variable только из опубликованных вариаций.
 */
function promen_sync_variable_parent_steels( int $product_id ): void {
	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_type( 'variable' ) ) {
		return;
	}
	if ( ! function_exists( 'promen_product_steel_slugs' ) ) {
		return;
	}

	$steel_slugs = promen_product_steel_slugs( $product_id );
	$attrs       = $product->get_attributes();
	$tax         = 'pa_steel';
	$tax_id      = wc_attribute_taxonomy_id_by_name( $tax );

	if ( ! $tax_id ) {
		return;
	}

	$term_ids = [];
	foreach ( $steel_slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $tax );
		if ( $term ) {
			$term_ids[] = (int) $term->term_id;
		}
	}

	$found = false;
	foreach ( $attrs as $key => $attr ) {
		if ( ! $attr instanceof WC_Product_Attribute || $attr->get_name() !== $tax ) {
			continue;
		}
		$attr->set_options( $term_ids );
		$attr->set_variation( true );
		$attrs[ $key ] = $attr;
		$found         = true;
		break;
	}

	if ( ! $found ) {
		$a = new WC_Product_Attribute();
		$a->set_id( $tax_id );
		$a->set_name( $tax );
		$a->set_options( $term_ids );
		$a->set_visible( true );
		$a->set_variation( true );
		$attrs[] = $a;
	}

	$product->set_attributes( $attrs );
	$product->save();
}

/** Получить документ из канона. */
function promen_catalog_get( int $product_id ): ?array {
	global $wpdb;
	$row = $wpdb->get_var(
		$wpdb->prepare(
			'SELECT payload FROM ' . promen_catalog_table_name() . ' WHERE product_id = %d',
			$product_id
		)
	);
	if ( ! $row ) {
		return null;
	}
	$doc = json_decode( $row, true );
	return is_array( $doc ) ? $doc : null;
}

/** Количество строк в каноне. */
function promen_catalog_count(): int {
	global $wpdb;
	return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . promen_catalog_table_name() );
}

/**
 * Счётчики всех групп каталога из канона: [ slug => n ] + '' => всего.
 * Один SQL GROUP BY + свёртка по дереву категорий; в transient.
 * (Раньше каждая группа держала свой transient: сайдбар на 33 группах
 * платил ~100 SQL только за чтение кэшей.)
 *
 * @return array<string, int>
 */
function promen_catalog_group_counts(): array {
	static $counts = null;
	if ( null !== $counts ) {
		return $counts;
	}

	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'group_counts' )
		: 'promen_group_counts';
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $counts = $cached;
	}

	global $wpdb;
	$by_cat = [];
	$rows   = $wpdb->get_results( 'SELECT category, COUNT(*) AS cnt FROM ' . promen_catalog_table_name() . ' GROUP BY category', ARRAY_A );
	foreach ( $rows ?: [] as $r ) {
		$by_cat[ (string) $r['category'] ] = (int) $r['cnt'];
	}

	$counts = [ '' => array_sum( $by_cat ) ];
	foreach ( array_keys( promen_term_map( 'product_cat' ) ) as $slug ) {
		$n = 0;
		foreach ( promen_catalog_group_slugs( $slug ) as $s ) {
			$n += $by_cat[ $s ] ?? 0;
		}
		$counts[ $slug ] = $n;
	}
	set_transient( $ckey, $counts, 15 * MINUTE_IN_SECONDS );
	return $counts;
}

/**
 * Каноническое число товаров группы/категории (единый источник counts).
 * Считает по канон-таблице с учётом потомков → согласуется с реестром и REST.
 */
function promen_catalog_group_count( string $group ): int {
	return promen_catalog_group_counts()[ $group ] ?? 0;
}

/**
 * Категории-потомки для фильтра group (включая саму категорию) — из карты
 * терминов, без get_term_children (тот дёргал get_term на каждого потомка).
 *
 * @return string[]
 */
function promen_catalog_group_slugs( string $group ): array {
	if ( $group === '' ) {
		return [];
	}
	$map = promen_term_map( 'product_cat' );
	if ( ! isset( $map[ $group ] ) ) {
		return [ $group ];
	}
	$slugs = [ $group ];
	$queue = [ $map[ $group ]['id'] ];
	while ( $queue ) {
		$pid = array_shift( $queue );
		foreach ( $map as $slug => $d ) {
			if ( $d['parent'] === $pid ) {
				$slugs[] = $slug;
				$queue[] = $d['id'];
			}
		}
	}
	return array_values( array_unique( $slugs ) );
}

/**
 * Счётчики стали в каноне для категории (для verify).
 *
 * @return array<string, int>
 */
function promen_catalog_steel_counts( string $group ): array {
	global $wpdb;
	$table = promen_catalog_table_name();
	$where = '1=1';
	$args  = [];

	$slugs = promen_catalog_group_slugs( $group );
	if ( $slugs ) {
		$ph    = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );
		$where = "category IN ({$ph})";
		$args  = $slugs;
	}

	$sql  = "SELECT steels_json FROM {$table} WHERE {$where}";
	$rows = $args ? $wpdb->get_col( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_col( $sql );

	$counts = [];
	foreach ( $rows as $json ) {
		$steels = json_decode( $json, true );
		if ( ! is_array( $steels ) ) {
			continue;
		}
		foreach ( array_unique( $steels ) as $slug ) {
			$counts[ $slug ] = ( $counts[ $slug ] ?? 0 ) + 1;
		}
	}
	return $counts;
}
