<?php
/**
 * Канонический документ строки реестра каталога.
 */

defined( 'ABSPATH' ) || exit;

/** Привести значение к float или null. */
function promen_catalog_float( $value ): ?float {
	if ( $value === null || $value === '' ) {
		return null;
	}
	if ( ! is_numeric( $value ) ) {
		return null;
	}
	$f = (float) $value;
	return $f > 0 ? $f : null;
}

/**
 * Собрать search_text из частей (для unit-тестов без WP).
 *
 * @param array<string, mixed> $parts
 */
function promen_catalog_build_search_text( array $parts ): string {
	$chunks = [];
	foreach ( [ 'title', 'sku', 'norm', 'family', 'steels_text' ] as $k ) {
		if ( ! empty( $parts[ $k ] ) ) {
			$chunks[] = (string) $parts[ $k ];
		}
	}
	foreach ( [ 'dn', 'dn2', 'pn', 'd', 's', 'angle' ] as $k ) {
		if ( isset( $parts[ $k ] ) && $parts[ $k ] !== null && $parts[ $k ] !== '' ) {
			$chunks[] = (string) $parts[ $k ];
		}
	}
	return trim( preg_replace( '/\s+/u', ' ', implode( ' ', $chunks ) ) );
}

/**
 * Построить канон-документ из сырых полей (unit-testable).
 *
 * @param array<string, mixed> $input
 * @return array<string, mixed>
 */
function promen_catalog_document_from_fields( array $input ): array {
	$steels = array_values( array_unique( array_filter( (array) ( $input['steels'] ?? [] ) ) ) );
	$steel_labels = (array) ( $input['steel_labels'] ?? [] );
	$norm_slug    = (string) ( $input['norm_slug'] ?? '' );
	$updated_at   = (string) ( $input['updated_at'] ?? gmdate( 'c' ) );

	$doc = [
		'id'           => (int) ( $input['product_id'] ?? 0 ),
		'product_id'   => (int) ( $input['product_id'] ?? 0 ),
		'sku'          => (string) ( $input['sku'] ?? '' ),
		'title'        => (string) ( $input['title'] ?? '' ),
		'url'          => (string) ( $input['url'] ?? '' ),
		'category'     => (string) ( $input['category'] ?? '' ),
		'family'       => (string) ( $input['family'] ?? '' ),
		'norm'         => (string) ( $input['norm'] ?? '' ),
		'norm_slug'    => $norm_slug,
		'dn'           => promen_catalog_float( $input['dn'] ?? null ),
		'dn2'          => promen_catalog_float( $input['dn2'] ?? null ),
		'pn'           => promen_catalog_float( $input['pn'] ?? null ),
		'd'            => promen_catalog_float( $input['d'] ?? null ),
		's'            => promen_catalog_float( $input['s'] ?? null ),
		'd2'           => promen_catalog_float( $input['d2'] ?? null ),
		's2'           => promen_catalog_float( $input['s2'] ?? null ),
		'angle'        => promen_catalog_float( $input['angle'] ?? null ),
		'mass'         => promen_catalog_float( $input['mass'] ?? null ),
		'steels'       => $steels,
		'steel_labels' => $steel_labels,
		'cells'        => (array) ( $input['cells'] ?? [] ),
		'steel_display'=> (string) ( $input['steel_display'] ?? '' ),
		'industries'       => array_values( array_unique( array_filter( (array) ( $input['industries'] ?? [] ) ) ) ),
		'industry_labels'  => (array) ( $input['industry_labels'] ?? [] ),
		'industry_display' => (string) ( $input['industry_display'] ?? '' ),
		'updated_at'   => $updated_at,
	];

	$doc['search_text'] = promen_catalog_build_search_text( [
		'title'       => $doc['title'],
		'sku'         => $doc['sku'],
		'norm'        => $doc['norm'],
		'family'      => $doc['family'],
		'steels_text' => implode( ' ', $steel_labels ),
		'dn'          => $doc['dn'],
		'dn2'         => $doc['dn2'],
		'pn'          => $doc['pn'],
		'd'           => $doc['d'],
		's'           => $doc['s'],
		'angle'       => $doc['angle'],
	] );

	return $doc;
}

/** Построить канон-документ из опубликованного товара WC. */
function promen_build_catalog_document( int $product_id ): ?array {
	$product = wc_get_product( $product_id );
	if ( ! $product || $product->get_status() !== 'publish' ) {
		return null;
	}
	if ( $product->is_type( 'variation' ) ) {
		return null;
	}

	$dims  = promen_get_dims( $product_id );
	$term  = promen_deepest_cat( $product_id );
	$group = $term ? $term->slug : '';

	$steels = function_exists( 'promen_product_steel_slugs' )
		? promen_product_steel_slugs( $product_id )
		: [];
	$steel_labels = function_exists( 'promen_product_steel_labels' )
		? promen_product_steel_labels( $product_id )
		: [];
	$steel_disp = function_exists( 'promen_product_steel_display' )
		? promen_product_steel_display( $product_id )
		: [ 'text' => implode( ', ', $steel_labels ), 'title' => '' ];

	$industries = function_exists( 'promen_product_industry_slugs' )
		? promen_product_industry_slugs( $product_id )
		: [];
	$industry_labels = function_exists( 'promen_product_industry_labels' )
		? promen_product_industry_labels( $product_id )
		: [];
	$industry_display = function_exists( 'promen_product_industry_display' )
		? promen_product_industry_display( $product_id )
		: implode( ', ', $industry_labels );

	$norm = promen_product_norm_key( $product_id );
	$norm_terms = wp_get_post_terms( $product_id, 'norm', [ 'fields' => 'all' ] );
	$norm_slug  = ( ! is_wp_error( $norm_terms ) && $norm_terms ) ? $norm_terms[0]->slug : '';

	$row_fast = promen_fastener_dims( $dims );
	$cells    = [];
	foreach ( promen_catalog_schema_columns( $group ) as $col ) {
		$cells[ $col['key'] ] = promen_catalog_cell( $col['key'], $dims, $row_fast, $product_id );
	}

	$mass = get_post_meta( $product_id, '_weight', true );
	$mass = promen_catalog_float( $mass );

	return promen_catalog_document_from_fields( [
		'product_id'    => $product_id,
		'sku'           => (string) $product->get_sku(),
		'title'         => promen_product_display_title( $product_id ),
		'url'           => get_permalink( $product_id ) ?: '',
		'category'      => $group,
		'family'        => (string) get_post_meta( $product_id, '_promen_family', true ),
		'norm'          => $norm,
		'norm_slug'     => $norm_slug,
		'dn'            => $dims['dn'] ?? null,
		'dn2'           => $dims['dn_branch'] ?? ( $dims['dy1'] ?? null ),
		'pn'            => $dims['pn'] ?? null,
		'd'             => $dims['outer_diameter'] ?? null,
		's'             => $dims['wall_thickness'] ?? null,
		'd2'            => $dims['outer_d_branch'] ?? null,
		's2'            => $dims['wall_branch'] ?? null,
		'angle'         => $dims['angle'] ?? null,
		'mass'          => $mass,
		'steels'        => $steels,
		'steel_labels'  => $steel_labels,
		'steel_display' => $steel_disp['text'],
		'industries'        => $industries,
		'industry_labels'   => $industry_labels,
		'industry_display'  => $industry_display,
		'cells'         => $cells,
		'updated_at'    => get_post_modified_time( 'c', true, $product_id ) ?: gmdate( 'c' ),
	] );
}
