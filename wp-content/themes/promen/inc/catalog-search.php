<?php
/**
 * Поисковый слой каталога: Meilisearch + SQL fallback.
 */

defined( 'ABSPATH' ) || exit;

/** Запрос каталога (парсинг из REST/GET). */
class Promen_Catalog_Query {

	public string $group = '';
	public string $q = '';
	public ?float $dn_min = null;
	public ?float $dn_max = null;
	public ?float $pn_min = null;
	public ?float $pn_max = null;
	public ?float $s_min = null;
	public ?float $s_max = null;
	/** @var string[] */
	public array $steel = [];
	/** @var string[] */
	public array $gost = [];
	/** @var string[] */
	public array $angle = [];
	/** @var string[] */
	public array $industry = [];
	public int $page = 1;
	public int $per_page = 50;
	public string $sort_field = 'dn';
	public string $sort_dir = 'asc';
	public bool $sort_explicit = false;
	public string $scope = '';

	public static function from_array( array $params ): self {
		$q = new self();
		$q->group = sanitize_title( (string) ( $params['group'] ?? '' ) );
		$q->q     = sanitize_text_field( (string) ( $params['q'] ?? '' ) );
		$q->scope = ( (string) ( $params['scope'] ?? '' ) === 'all' ) ? 'all' : '';

		foreach ( [ 'dn', 'pn', 's' ] as $p ) {
			$min_key = $p . '_min';
			$max_key = $p . '_max';
			if ( isset( $params[ $min_key ] ) && $params[ $min_key ] !== '' ) {
				$q->{$p . '_min'} = (float) $params[ $min_key ];
			}
			if ( isset( $params[ $max_key ] ) && $params[ $max_key ] !== '' ) {
				$q->{$p . '_max'} = (float) $params[ $max_key ];
			}
		}

		foreach ( [ 'steel', 'gost', 'angle', 'industry' ] as $p ) {
			if ( empty( $params[ $p ] ) ) {
				continue;
			}
			$raw = is_array( $params[ $p ] ) ? $params[ $p ] : explode( ',', (string) $params[ $p ] );
			$q->{$p} = array_values( array_unique( array_filter( array_map( 'sanitize_title', $raw ) ) ) );
		}

		$q->page     = max( 1, (int) ( $params['page'] ?? 1 ) );
		$q->per_page = min( 100, max( 1, (int) ( $params['per_page'] ?? 50 ) ) );

		if ( ! empty( $params['sort'] ) && is_string( $params['sort'] ) ) {
			$parts = explode( ':', $params['sort'] );
			if ( ! empty( $parts[0] ) ) {
				$q->sort_field    = sanitize_key( $parts[0] );
				$q->sort_explicit = true;
			}
			if ( ! empty( $parts[1] ) && in_array( strtolower( $parts[1] ), [ 'asc', 'desc' ], true ) ) {
				$q->sort_dir = strtolower( $parts[1] );
			}
		} elseif ( $q->group !== '' && function_exists( 'promen_catalog_schema_sort' ) ) {
			$def = promen_catalog_schema_sort( $q->group );
			$q->sort_field = $def['field'];
			$q->sort_dir   = $def['dir'];
		}

		return $q;
	}

	/** @return string[] */
	public function category_slugs(): array {
		if ( $this->scope === 'all' ) {
			return []; // поиск по всему каталогу (без категорийного фильтра)
		}
		return $this->group !== '' ? promen_catalog_group_slugs( $this->group ) : [];
	}

	/** @return string[] */
	public function facet_fields(): array {
		if ( $this->group === '' ) {
			return [ 'steel', 'gost', 'angle', 'pn', 'industry' ];
		}
		return function_exists( 'promen_catalog_schema_facets' )
			? promen_catalog_schema_facets( $this->group )
			: [ 'steel', 'gost' ];
	}
}

/** Результат поиска. */
class Promen_Catalog_Search_Result {

	public function __construct(
		public array $hits,
		public int $total,
		public int $page,
		public int $per_page,
		public array $facets,
		public string $engine
	) {}
}

interface Promen_Catalog_Search_Engine {
	public function search( Promen_Catalog_Query $query ): Promen_Catalog_Search_Result;
	public function upsert( array $document ): bool;
	public function delete( int $product_id ): bool;
	public function reindex_all( ?callable $progress = null ): int;
	public function health(): bool;
	public function count_indexed(): int;
	public function engine_name(): string;
}

/**
 * Построить filter-строку Meilisearch из запроса (unit-testable).
 *
 * @param string[] $exclude Группы фильтров, НЕ включаемые в выражение
 *                          ('steel'|'gost'|'angle'|'industry'|'dn'|'pn') —
 *                          для disjunctive-фасетов: опции своей группы
 *                          считаются без её собственного фильтра.
 */
function promen_catalog_meili_filter( Promen_Catalog_Query $query, array $exclude = [] ): string {
	$parts = [];
	$skip  = static fn( string $p ): bool => in_array( $p, $exclude, true );

	$slugs = $query->category_slugs();
	if ( $slugs ) {
		$cat = array_map(
			static fn( string $s ): string => 'category = "' . str_replace( '"', '\\"', $s ) . '"',
			$slugs
		);
		$parts[] = '(' . implode( ' OR ', $cat ) . ')';
	}

	if ( ! $skip( 'dn' ) ) {
		if ( null !== $query->dn_min ) {
			$parts[] = 'dn >= ' . $query->dn_min;
		}
		if ( null !== $query->dn_max ) {
			$parts[] = 'dn <= ' . $query->dn_max;
		}
	}
	if ( ! $skip( 'pn' ) ) {
		if ( null !== $query->pn_min ) {
			$parts[] = 'pn >= ' . $query->pn_min;
		}
		if ( null !== $query->pn_max ) {
			$parts[] = 'pn <= ' . $query->pn_max;
		}
	}
	if ( ! $skip( 's' ) ) {
		if ( null !== $query->s_min ) {
			$parts[] = 's >= ' . $query->s_min;
		}
		if ( null !== $query->s_max ) {
			$parts[] = 's <= ' . $query->s_max;
		}
	}

	if ( $query->steel && ! $skip( 'steel' ) ) {
		$steel = array_map(
			static fn( string $s ): string => 'steels = "' . str_replace( '"', '\\"', $s ) . '"',
			$query->steel
		);
		$parts[] = '(' . implode( ' OR ', $steel ) . ')';
	}
	if ( $query->gost && ! $skip( 'gost' ) ) {
		$gost = array_map(
			static fn( string $s ): string => 'norm_slug = "' . str_replace( '"', '\\"', $s ) . '"',
			$query->gost
		);
		$parts[] = '(' . implode( ' OR ', $gost ) . ')';
	}
	if ( $query->angle && ! $skip( 'angle' ) ) {
		$ang = array_map(
			static function ( string $s ): string {
				$v = is_numeric( $s ) ? (float) $s : $s;
				return 'angle = ' . $v;
			},
			$query->angle
		);
		$parts[] = '(' . implode( ' OR ', $ang ) . ')';
	}
	if ( $query->industry && ! $skip( 'industry' ) ) {
		$ind = array_map(
			static fn( string $s ): string => 'industries = "' . str_replace( '"', '\\"', $s ) . '"',
			$query->industry
		);
		$parts[] = '(' . implode( ' OR ', $ind ) . ')';
	}

	return implode( ' AND ', $parts );
}

/**
 * Активные фасет-группы запроса (у которых есть собственный выбор).
 *
 * @return string[] из 'steel'|'gost'|'angle'|'industry'|'dn'|'pn'
 */
function promen_catalog_query_active_facets( Promen_Catalog_Query $query ): array {
	$out = [];
	foreach ( [ 'steel', 'gost', 'angle', 'industry' ] as $p ) {
		if ( $query->{$p} ) {
			$out[] = $p;
		}
	}
	if ( null !== $query->dn_min || null !== $query->dn_max ) {
		$out[] = 'dn';
	}
	if ( null !== $query->pn_min || null !== $query->pn_max ) {
		$out[] = 'pn';
	}
	if ( null !== $query->s_min || null !== $query->s_max ) {
		$out[] = 's';
	}
	return $out;
}

function promen_meili_url(): string {
	return rtrim( (string) ( getenv( 'PROMEN_MEILI_URL' ) ?: 'http://meilisearch:7700' ), '/' );
}

function promen_meili_key(): string {
	return (string) ( getenv( 'PROMEN_MEILI_KEY' ) ?: 'promen_dev_key' );
}

function promen_meili_index(): string {
	return 'products';
}

/** HTTP к Meilisearch. */
function promen_meili_request( string $method, string $path, ?array $body = null, int $timeout = 30 ): array {
	$args = [
		'method'  => $method,
		'timeout' => $timeout,
		'headers' => [
			'Authorization' => 'Bearer ' . promen_meili_key(),
			'Content-Type'  => 'application/json',
		],
	];
	if ( null !== $body ) {
		$args['body'] = wp_json_encode( $body );
	}
	$res = wp_remote_request( promen_meili_url() . $path, $args );
	if ( is_wp_error( $res ) ) {
		return [ 'ok' => false, 'error' => $res->get_error_message(), 'data' => null ];
	}
	$code = wp_remote_retrieve_response_code( $res );
	$raw  = wp_remote_retrieve_body( $res );
	$data = $raw !== '' ? json_decode( $raw, true ) : [];
	return [
		'ok'    => $code >= 200 && $code < 300,
		'code'  => $code,
		'data'  => is_array( $data ) ? $data : [],
		'error' => $code >= 300 ? ( $raw ?: 'HTTP ' . $code ) : '',
	];
}

class Promen_Meili_Engine implements Promen_Catalog_Search_Engine {

	/** ensure_index достаточно раза на процесс — не дёргаем 2 HTTP на каждый upsert. */
	private static bool $index_ensured = false;

	public function health(): bool {
		$r = promen_meili_request( 'GET', '/health', null, 5 );
		return $r['ok'];
	}

	public function engine_name(): string {
		return 'meili';
	}

	public function ensure_index(): void {
		if ( self::$index_ensured ) {
			return;
		}
		promen_meili_request( 'POST', '/indexes', [
			'uid'        => promen_meili_index(),
			'primaryKey' => 'product_id',
		] );
		promen_meili_request( 'PATCH', '/indexes/' . promen_meili_index() . '/settings', [
			'searchableAttributes' => [ 'search_text', 'sku', 'title', 'norm', 'family' ],
			'filterableAttributes' => [ 'category', 'steels', 'industries', 'norm_slug', 'dn', 'dn2', 'pn', 'angle', 's' ],
			'sortableAttributes'   => [ 'dn', 'pn', 'mass', 'title' ],
			// Иначе estimatedTotalHits капается на 1000 → счётчик не меняется при
			// фильтрации крупных категорий (отводы ~1900). Поднимаем потолок.
			'pagination'           => [ 'maxTotalHits' => 100000 ],
			// DN-ряд крупных групп длиннее дефолтных 100 значений фасета.
			'faceting'             => [ 'maxValuesPerFacet' => 300 ],
		] );
		self::$index_ensured = true;
	}

	/** Удалить индекс целиком (пересборка с нуля при дрейфе — см. cron reconcile). */
	public function drop_index(): void {
		promen_meili_request( 'DELETE', '/indexes/' . promen_meili_index() );
		self::$index_ensured = false;
	}

	public function search( Promen_Catalog_Query $query ): Promen_Catalog_Search_Result {
		$filter = promen_catalog_meili_filter( $query );
		$body   = [
			'q'      => $query->q,
			'limit'  => $query->per_page,
			'offset' => ( $query->page - 1 ) * $query->per_page,
			'facets' => $this->facet_attrs( $query ),
		];
		if ( $filter !== '' ) {
			$body['filter'] = $filter;
		}
		// При текстовом поиске без явной сортировки — отдаём ранжирование Meili
		// по релевантности (иначе форс-сортировка dn затирает качество поиска).
		if ( $query->q === '' || $query->sort_explicit ) {
			$sort_field = in_array( $query->sort_field, [ 'dn', 'pn', 'mass', 'title' ], true )
				? $query->sort_field
				: 'dn';
			$body['sort'] = [ $sort_field . ':' . ( $query->sort_dir === 'desc' ? 'desc' : 'asc' ) ];
		}

		// Фронтовый путь: таймаут короткий, зависший Meili не держит страницу.
		$r = promen_meili_request( 'POST', '/indexes/' . promen_meili_index() . '/search', $body, 5 );
		if ( ! $r['ok'] ) {
			throw new RuntimeException( 'Meilisearch search failed: ' . ( $r['error'] ?? 'unknown' ) );
		}
		$data   = $r['data'];
		$hits   = [];
		foreach ( (array) ( $data['hits'] ?? [] ) as $hit ) {
			$hits[] = $this->normalize_hit( $hit );
		}
		$facets = $this->normalize_facets( (array) ( $data['facetDistribution'] ?? [] ) );

		return new Promen_Catalog_Search_Result(
			$hits,
			(int) ( $data['estimatedTotalHits'] ?? $data['totalHits'] ?? count( $hits ) ),
			$query->page,
			$query->per_page,
			$facets,
			'meili'
		);
	}

	/** @return string[] */
	private function facet_attrs( Promen_Catalog_Query $query ): array {
		$map = [
			'steel'    => 'steels',
			'gost'     => 'norm_slug',
			'angle'    => 'angle',
			'pn'       => 'pn',
			'industry' => 'industries',
		];
		$out = [];
		foreach ( $query->facet_fields() as $f ) {
			if ( isset( $map[ $f ] ) ) {
				$out[] = $map[ $f ];
			}
		}
		// dn/pn/s — всегда: по их распределению сужаются ряды слайдеров диапазонов.
		$out[] = 'dn';
		$out[] = 'pn';
		$out[] = 's';
		return array_values( array_unique( $out ) );
	}

	/** Meili-атрибут фасета для группы фильтра. */
	private function facet_attr_for( string $param ): string {
		$map = [
			'steel'    => 'steels',
			'gost'     => 'norm_slug',
			'angle'    => 'angle',
			'pn'       => 'pn',
			'dn'       => 'dn',
			's'        => 's',
			'industry' => 'industries',
		];
		return $map[ $param ] ?? $param;
	}

	/**
	 * Disjunctive-фасеты одним HTTP /multi-search: для каждой активной группы
	 * распределение считается без её собственного фильтра (иначе в группе
	 * остаётся только выбранное, а «исключать несовместимое» должно работать
	 * лишь между группами). dn/pn включаются для сужения границ слайдеров.
	 *
	 * @param string[] $params Группы, которым нужно «minus-self» распределение.
	 * @return array<string, array<string, int>> param => distribution
	 */
	public function disjunctive_facets( Promen_Catalog_Query $query, array $params ): array {
		if ( ! $params ) {
			return [];
		}
		$queries = [];
		foreach ( $params as $p ) {
			$q = [
				'indexUid' => promen_meili_index(),
				'q'        => $query->q,
				'limit'    => 0,
				'facets'   => [ $this->facet_attr_for( $p ) ],
			];
			$filter = promen_catalog_meili_filter( $query, [ $p ] );
			if ( $filter !== '' ) {
				$q['filter'] = $filter;
			}
			$queries[] = $q;
		}
		$r = promen_meili_request( 'POST', '/multi-search', [ 'queries' => $queries ], 5 );
		if ( ! $r['ok'] ) {
			throw new RuntimeException( 'Meilisearch multi-search failed: ' . ( $r['error'] ?? 'unknown' ) );
		}
		$out     = [];
		$results = (array) ( $r['data']['results'] ?? [] );
		foreach ( $params as $i => $p ) {
			$dist = (array) ( $results[ $i ]['facetDistribution'][ $this->facet_attr_for( $p ) ] ?? [] );
			$out[ $p ] = array_map( 'intval', $dist );
		}
		return $out;
	}

	private function normalize_hit( array $hit ): array {
		unset( $hit['_formatted'] );
		return $hit;
	}

	private function normalize_facets( array $raw ): array {
		$rename = [
			'steels'     => 'steel',
			'norm_slug'  => 'gost',
			'industries' => 'industry',
		];
		$out = [];
		foreach ( $raw as $field => $dist ) {
			$key = $rename[ $field ] ?? $field;
			$out[ $key ] = $dist;
		}
		return $out;
	}

	public function upsert( array $document ): bool {
		$this->ensure_index();
		$r = promen_meili_request(
			'POST',
			'/indexes/' . promen_meili_index() . '/documents',
			[ $document ]
		);
		return $r['ok'];
	}

	public function delete( int $product_id ): bool {
		$r = promen_meili_request( 'DELETE', '/indexes/' . promen_meili_index() . '/documents/' . $product_id );
		return $r['ok'] || ( $r['code'] ?? 0 ) === 404;
	}

	public function reindex_all( ?callable $progress = null ): int {
		$this->ensure_index();
		global $wpdb;
		$table = promen_catalog_table_name();
		$ids   = $wpdb->get_col( "SELECT product_id FROM {$table} ORDER BY product_id ASC" );
		$batch = [];
		$n     = 0;
		foreach ( $ids as $pid ) {
			$doc = promen_catalog_get( (int) $pid );
			if ( ! $doc ) {
				continue;
			}
			$batch[] = $doc;
			if ( count( $batch ) >= 500 ) {
				promen_meili_request( 'POST', '/indexes/' . promen_meili_index() . '/documents', $batch );
				$n += count( $batch );
				$batch = [];
				if ( $progress ) {
					$progress( $n, count( $ids ) );
				}
			}
		}
		if ( $batch ) {
			promen_meili_request( 'POST', '/indexes/' . promen_meili_index() . '/documents', $batch );
			$n += count( $batch );
		}
		return $n;
	}

	public function count_indexed(): int {
		$r = promen_meili_request( 'GET', '/indexes/' . promen_meili_index() . '/stats' );
		return $r['ok'] ? (int) ( $r['data']['numberOfDocuments'] ?? 0 ) : 0;
	}
}

class Promen_Sql_Fallback_Engine implements Promen_Catalog_Search_Engine {

	public function engine_name(): string {
		return 'sql';
	}

	public function health(): bool {
		return true;
	}

	/**
	 * WHERE-условие из запроса (с той же семантикой exclude, что у Meili-билдера).
	 *
	 * @param string[] $exclude
	 * @return array{sql: string, args: array}
	 */
	public function build_where( Promen_Catalog_Query $query, array $exclude = [] ): array {
		global $wpdb;
		$where = [ '1=1' ];
		$args  = [];
		$skip  = static fn( string $p ): bool => in_array( $p, $exclude, true );

		$slugs = $query->category_slugs();
		if ( $slugs ) {
			$ph      = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );
			$where[] = "category IN ({$ph})";
			$args    = array_merge( $args, $slugs );
		}
		if ( ! $skip( 'dn' ) ) {
			if ( null !== $query->dn_min ) {
				$where[] = 'dn >= %f';
				$args[]  = $query->dn_min;
			}
			if ( null !== $query->dn_max ) {
				$where[] = 'dn <= %f';
				$args[]  = $query->dn_max;
			}
		}
		if ( ! $skip( 'pn' ) ) {
			if ( null !== $query->pn_min ) {
				$where[] = 'pn >= %f';
				$args[]  = $query->pn_min;
			}
			if ( null !== $query->pn_max ) {
				$where[] = 'pn <= %f';
				$args[]  = $query->pn_max;
			}
		}
		if ( ! $skip( 's' ) ) {
			if ( null !== $query->s_min ) {
				$where[] = 's >= %f';
				$args[]  = $query->s_min;
			}
			if ( null !== $query->s_max ) {
				$where[] = 's <= %f';
				$args[]  = $query->s_max;
			}
		}
		if ( $query->gost && ! $skip( 'gost' ) ) {
			$ph      = implode( ',', array_fill( 0, count( $query->gost ), '%s' ) );
			$where[] = "norm_slug IN ({$ph})";
			$args    = array_merge( $args, $query->gost );
		}
		if ( $query->angle && ! $skip( 'angle' ) ) {
			$ang = array_map( 'floatval', $query->angle );
			$ph  = implode( ',', array_fill( 0, count( $ang ), '%f' ) );
			$where[] = "angle IN ({$ph})";
			$args    = array_merge( $args, $ang );
		}
		if ( $query->steel && ! $skip( 'steel' ) ) {
			$steel_w = [];
			foreach ( $query->steel as $s ) {
				$steel_w[] = 'steels_json LIKE %s';
				$args[]    = '%"' . $wpdb->esc_like( $s ) . '"%';
			}
			$where[] = '(' . implode( ' OR ', $steel_w ) . ')';
		}
		if ( $query->industry && ! $skip( 'industry' ) ) {
			$ind_w = [];
			foreach ( $query->industry as $s ) {
				$ind_w[] = 'industries_json LIKE %s';
				$args[]  = '%"' . $wpdb->esc_like( $s ) . '"%';
			}
			$where[] = '(' . implode( ' OR ', $ind_w ) . ')';
		}
		if ( $query->q !== '' ) {
			$like    = '%' . $wpdb->esc_like( $query->q ) . '%';
			$where[] = '(payload LIKE %s OR sku LIKE %s)';
			$args[]  = $like;
			$args[]  = $like;
		}

		return [ 'sql' => implode( ' AND ', $where ), 'args' => $args ];
	}

	/**
	 * Disjunctive-фасеты в fallback-режиме: по скану на активную группу.
	 *
	 * @param string[] $params
	 * @return array<string, array<string, int>>
	 */
	public function disjunctive_facets( Promen_Catalog_Query $query, array $params ): array {
		$out = [];
		foreach ( $params as $p ) {
			$w   = $this->build_where( $query, [ $p ] );
			$all = promen_sql_compute_facets( $query, $w['sql'], $w['args'] );
			$key = in_array( $p, [ 'dn' ], true ) ? 'dn' : $p;
			$out[ $p ] = (array) ( $all[ $key ] ?? [] );
		}
		return $out;
	}

	public function search( Promen_Catalog_Query $query ): Promen_Catalog_Search_Result {
		global $wpdb;
		$table = promen_catalog_table_name();
		$w     = $this->build_where( $query );
		$args  = $w['args'];

		$where_sql = $w['sql'];
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) ) : $wpdb->get_var( $count_sql ) );

		$sort_field = in_array( $query->sort_field, [ 'dn', 'pn', 'angle' ], true ) ? $query->sort_field : 'dn';
		$sort_dir   = $query->sort_dir === 'desc' ? 'DESC' : 'ASC';
		$offset     = ( $query->page - 1 ) * $query->per_page;

		$list_sql = "SELECT payload FROM {$table} WHERE {$where_sql} ORDER BY {$sort_field} {$sort_dir} LIMIT %d OFFSET %d";
		$list_args = array_merge( $args, [ $query->per_page, $offset ] );
		$rows      = $wpdb->get_col( $wpdb->prepare( $list_sql, $list_args ) );

		$hits = [];
		foreach ( $rows as $payload ) {
			$doc = json_decode( $payload, true );
			if ( is_array( $doc ) ) {
				$hits[] = $doc;
			}
		}

		$facets = promen_sql_compute_facets( $query, $where_sql, $args );

		return new Promen_Catalog_Search_Result(
			$hits,
			$total,
			$query->page,
			$query->per_page,
			$facets,
			'sql'
		);
	}

	public function upsert( array $document ): bool {
		return true;
	}

	public function delete( int $product_id ): bool {
		return true;
	}

	public function reindex_all( ?callable $progress = null ): int {
		return promen_catalog_count();
	}

	public function count_indexed(): int {
		return promen_catalog_count();
	}
}

/**
 * Подсчёт фасетов для SQL fallback (упрощённо — по всем строкам в скоупе).
 */
function promen_sql_compute_facets( Promen_Catalog_Query $query, string $where_sql, array $args ): array {
	global $wpdb;
	$table = promen_catalog_table_name();
	$sql   = "SELECT steels_json, industries_json, norm_slug, angle, pn, dn, s FROM {$table} WHERE {$where_sql}";
	$rows  = $args ? $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

	$facets = [ 'steel' => [], 'gost' => [], 'angle' => [], 'pn' => [], 'dn' => [], 's' => [], 'industry' => [] ];
	foreach ( $rows ?: [] as $row ) {
		$steels = json_decode( $row['steels_json'] ?? '[]', true );
		if ( is_array( $steels ) ) {
			foreach ( $steels as $s ) {
				$facets['steel'][ $s ] = ( $facets['steel'][ $s ] ?? 0 ) + 1;
			}
		}
		$inds = json_decode( $row['industries_json'] ?? '[]', true );
		if ( is_array( $inds ) ) {
			foreach ( $inds as $s ) {
				$facets['industry'][ $s ] = ( $facets['industry'][ $s ] ?? 0 ) + 1;
			}
		}
		if ( ! empty( $row['norm_slug'] ) ) {
			$facets['gost'][ $row['norm_slug'] ] = ( $facets['gost'][ $row['norm_slug'] ] ?? 0 ) + 1;
		}
		if ( isset( $row['angle'] ) && $row['angle'] !== null && $row['angle'] !== '' ) {
			$key = (string) (float) $row['angle'];
			$facets['angle'][ $key ] = ( $facets['angle'][ $key ] ?? 0 ) + 1;
		}
		if ( isset( $row['pn'] ) && $row['pn'] !== null && $row['pn'] !== '' ) {
			$key = (string) (float) $row['pn'];
			$facets['pn'][ $key ] = ( $facets['pn'][ $key ] ?? 0 ) + 1;
		}
		if ( isset( $row['dn'] ) && $row['dn'] !== null && $row['dn'] !== '' ) {
			$key = (string) (float) $row['dn'];
			$facets['dn'][ $key ] = ( $facets['dn'][ $key ] ?? 0 ) + 1;
		}
		if ( isset( $row['s'] ) && $row['s'] !== null && $row['s'] !== '' ) {
			$key = (string) (float) $row['s'];
			$facets['s'][ $key ] = ( $facets['s'][ $key ] ?? 0 ) + 1;
		}
	}
	return $facets;
}

/**
 * Disjunctive-фасеты для активных групп: у своей группы распределение
 * считается без её собственного фильтра. Meili — одним /multi-search,
 * недоступен — SQL-fallback по скану на группу.
 *
 * @param string[] $params
 * @return array<string, array<string, int>>
 */
function promen_catalog_disjunctive_facets( Promen_Catalog_Query $query, array $params ): array {
	if ( ! $params ) {
		return [];
	}
	if ( ! promen_meili_marked_down() ) {
		try {
			return ( new Promen_Meili_Engine() )->disjunctive_facets( $query, $params );
		} catch ( Throwable $e ) {
			promen_meili_marked_down( true );
		}
	}
	return ( new Promen_Sql_Fallback_Engine() )->disjunctive_facets( $query, $params );
}

/**
 * Meili помечен недоступным? (negative-cache 60с после реального сбоя).
 * Раньше на каждый поиск летел HTTP /health — лишний round-trip: сбой и так
 * ловится try/catch в promen_catalog_search.
 */
function promen_meili_marked_down( ?bool $set = null ): bool {
	static $down = null;
	if ( null !== $set ) {
		$down = $set;
		if ( $set ) {
			set_transient( 'promen_meili_down', 1, MINUTE_IN_SECONDS );
		} else {
			delete_transient( 'promen_meili_down' );
		}
	}
	if ( null === $down ) {
		$down = (bool) get_transient( 'promen_meili_down' );
	}
	return $down;
}

function promen_catalog_search_engine(): Promen_Catalog_Search_Engine {
	if ( promen_meili_marked_down() ) {
		return new Promen_Sql_Fallback_Engine();
	}
	return new Promen_Meili_Engine();
}

function promen_catalog_search( Promen_Catalog_Query $query ): Promen_Catalog_Search_Result {
	try {
		return promen_catalog_search_engine()->search( $query );
	} catch ( Throwable $e ) {
		promen_meili_marked_down( true );
		return ( new Promen_Sql_Fallback_Engine() )->search( $query );
	}
}

/**
 * Push документа в поисковый индекс при сохранении товара.
 * При недоступном Meili — тихо false: дрейф закроет ночной reconcile.
 */
function promen_catalog_search_push( array $doc ): bool {
	if ( promen_meili_marked_down() ) {
		return false;
	}
	try {
		$engine = new Promen_Meili_Engine();
		$engine->ensure_index();
		return $engine->upsert( $doc );
	} catch ( Throwable $e ) {
		promen_meili_marked_down( true );
		return false;
	}
}

/** Удаление из поискового индекса (не через селектор — SQL-движок его «съел» бы noop'ом). */
function promen_catalog_search_delete( int $product_id ): bool {
	if ( promen_meili_marked_down() ) {
		return false;
	}
	try {
		return ( new Promen_Meili_Engine() )->delete( $product_id );
	} catch ( Throwable $e ) {
		promen_meili_marked_down( true );
		return false;
	}
}

function promen_catalog_search_reconcile(): array {
	$canon = promen_catalog_count();
	$meili = new Promen_Meili_Engine();
	if ( ! $meili->health() ) {
		return [ 'ok' => false, 'canon' => $canon, 'indexed' => 0, 'drift' => $canon, 'message' => 'meili unavailable' ];
	}
	$indexed = $meili->count_indexed();
	return [
		'ok'      => $canon === $indexed,
		'canon'   => $canon,
		'indexed' => $indexed,
		'drift'   => abs( $canon - $indexed ),
	];
}
