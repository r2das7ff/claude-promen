<?php
/**
 * Рендер реестра каталога из канонического слоя (SQL / Meilisearch).
 */

defined( 'ABSPATH' ) || exit;

/** Использовать канонический слой вместо тяжёлого WP_Query на витрине. */
function promen_catalog_uses_layer(): bool {
	return (bool) apply_filters( 'promen_catalog_uses_layer', true );
}

/** Активная группа для запроса каталога (GET group или страница категории). */
function promen_catalog_active_group(): string {
	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return $term->slug;
		}
	}
	return promen_active_group();
}

/** Собрать Promen_Catalog_Query из текущего HTTP-запроса. */
function promen_catalog_query_from_request(): Promen_Catalog_Query {
	$params = [
		'group'    => promen_catalog_active_group(),
		'q'        => isset( $_GET['q'] ) ? wp_unslash( $_GET['q'] ) : '',
		'page'     => get_query_var( 'paged' ) ?: ( $_GET['paged'] ?? 1 ),
		'per_page' => 30,
		'sort'     => isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : '',
		'scope'    => isset( $_GET['scope'] ) ? sanitize_key( wp_unslash( $_GET['scope'] ) ) : '',
	];

	foreach ( [ 'dn', 'pn' ] as $p ) {
		if ( isset( $_GET[ $p . '_min' ] ) && $_GET[ $p . '_min' ] !== '' ) {
			$params[ $p . '_min' ] = $_GET[ $p . '_min' ];
		}
		if ( isset( $_GET[ $p . '_max' ] ) && $_GET[ $p . '_max' ] !== '' ) {
			$params[ $p . '_max' ] = $_GET[ $p . '_max' ];
		}
	}

	foreach ( array_keys( promen_multi_taxonomies() ) as $p ) {
		if ( ! empty( $_GET[ $p ] ) ) {
			$params[ $p ] = wp_unslash( $_GET[ $p ] );
		}
	}

	if ( is_tax( 'norm' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$params['gost'] = $term->slug;
		}
	}

	return Promen_Catalog_Query::from_array( $params );
}

/** Результат поиска для текущей страницы каталога (один раз за запрос). */
function promen_catalog_page_result(): Promen_Catalog_Search_Result {
	static $result = null;
	if ( null === $result ) {
		$result = promen_catalog_search( promen_catalog_query_from_request() );
	}
	return $result;
}

/**
 * Опции мультивыбора из фасетов канонического поиска.
 *
 * @param array<string, array<string, int>> $facets
 * @return array<int, array{slug: string, name: string, count: int}>
 */
function promen_catalog_facet_options( string $param, array $facets, int $limit = 40 ): array {
	$dist = $facets[ $param ] ?? [];
	if ( ! $dist ) {
		return [];
	}

	$opts = [];
	foreach ( $dist as $slug => $count ) {
		$name = promen_catalog_facet_label( $param, (string) $slug );
		$opts[] = [
			'slug'  => (string) $slug,
			'name'  => $name,
			'count' => (int) $count,
		];
	}

	$opts = promen_catalog_sort_facet_options( $opts, $param );

	return array_slice( $opts, 0, $limit );
}

function promen_catalog_facet_label( string $param, string $slug ): string {
	if ( $param === 'industry' ) {
		$labels = promen_industry_tag_labels();
		return $labels[ $slug ] ?? strtoupper( $slug );
	}
	if ( $param === 'steel' ) {
		$t = get_term_by( 'slug', $slug, 'pa_steel' );
		return $t ? $t->name : $slug;
	}
	if ( $param === 'gost' ) {
		$t = get_term_by( 'slug', $slug, 'norm' );
		return $t ? $t->name : $slug;
	}
	return $slug;
}

/** Шаблон grid для строк реестра. */
function promen_catalog_grid_template( string $group ): string {
	$cols = promen_catalog_columns( $group );
	return '150px minmax(200px,1fr) '
		. implode( ' ', array_map( static fn( $c ) => $c['w'], $cols ) )
		. ' 120px 96px 32px';
}

/** Колонка → поле сортировки Meili (пусто = не сортируется). */
function promen_catalog_sortable_field( string $key ): string {
	static $map = [ 'dn' => 'dn', 'mass' => 'mass', 'massm' => 'mass', 'pn' => 'pn' ];
	return $map[ $key ] ?? '';
}

/**
 * Внутренние ячейки шапки реестра с кликабельной сортировкой (DN/PN/Масса).
 * Используется и на сервере (archive-product.php), и мьорится в catalog.js.
 */
function promen_catalog_header_cells( array $cols, string $sort_field, string $sort_dir ): string {
	$html = '<span>Норматив</span><span>Наименование</span>';
	foreach ( $cols as $col ) {
		$sf    = promen_catalog_sortable_field( (string) $col['key'] );
		$label = esc_html( (string) $col['label'] );
		if ( $sf === '' ) {
			$html .= '<span>' . $label . '</span>';
			continue;
		}
		$active = ( $sf === $sort_field );
		$arr    = $active ? ( $sort_dir === 'desc' ? '↓' : '↑' ) : '⇅';
		$html  .= '<span class="th-sort' . ( $active ? ' is-active' : '' ) . '" role="button" tabindex="0"'
			. ' data-sort-field="' . esc_attr( $sf ) . '" title="Сортировать">' . $label
			. '<i class="th-arr">' . $arr . '</i></span>';
	}
	$html .= '<span>Материал</span><span>Отрасль</span><span></span>';
	return $html;
}

/** HTML одной строки реестра из канон-документа. */
function promen_render_catalog_row( array $hit, string $grid_tpl, int $index = 0 ): void {
	$url    = esc_url( (string) ( $hit['url'] ?? '#' ) );
	$norm   = esc_html( (string) ( $hit['norm'] ?? '—' ) );
	$title  = esc_html( (string) ( $hit['title'] ?? '' ) );
	$family = esc_html( (string) ( $hit['family'] ?? '' ) );
	$sku    = esc_attr( (string) ( $hit['sku'] ?? '' ) );
	$steel  = esc_html( (string) ( $hit['steel_display'] ?? '—' ) );
	$industries = (array) ( $hit['industries'] ?? [] );
	$ind_html = function_exists( 'promen_industry_tags_html' )
		? promen_industry_tags_html( $industries )
		: esc_html( (string) ( $hit['industry_display'] ?? '—' ) );
	$delay  = esc_attr( (string) min( $index * 0.025, 0.35 ) );
	$cells  = (array) ( $hit['cells'] ?? [] );

	echo '<a class="prod-row" href="' . $url . '" style="grid-template-columns:' . esc_attr( $grid_tpl ) . ';animation-delay:' . $delay . 's"';
	echo ' data-sku="' . $sku . '" data-title="' . esc_attr( (string) ( $hit['title'] ?? '' ) ) . '"';
	echo ' data-norm="' . esc_attr( (string) ( $hit['norm'] ?? '' ) ) . '"';
	echo ' data-steel="' . esc_attr( (string) ( $hit['steel_display'] ?? '' ) ) . '"';
	echo ' data-industry="' . esc_attr( (string) ( $hit['industry_display'] ?? '' ) ) . '">';
	echo '<span class="pr-norm"><span class="pr-norm-code">' . $norm . '</span></span>';
	echo '<span class="pr-name">' . $title;
	if ( $family !== '' ) {
		echo '<small>' . $family . '</small>';
	}
	echo '</span>';
	foreach ( promen_catalog_columns( (string) ( $hit['category'] ?? '' ) ) as $col ) {
		$val   = $cells[ $col['key'] ] ?? '—';
		$empty = ( $val === '—' || $val === '' );
		echo '<span class="pr-' . esc_attr( $col['key'] ) . ( $empty ? ' is-empty' : '' ) . '">' . esc_html( (string) $val ) . '</span>';
	}
	echo '<span class="pr-mat">' . $steel . '</span>';
	echo '<span class="pr-ind">' . $ind_html . '</span>';
	echo '<span class="pr-arr">›</span></a>';
}

/** Пагинация для канонического результата. */
function promen_catalog_pagination_links( Promen_Catalog_Search_Result $result ): string {
	$pages = (int) ceil( $result->total / max( 1, $result->per_page ) );
	if ( $pages <= 1 ) {
		return '';
	}
	return (string) paginate_links( [
		'total'     => $pages,
		'current'   => $result->page,
		'type'      => 'plain',
		'prev_text' => '← Назад',
		'next_text' => 'Вперёд →',
	] );
}
