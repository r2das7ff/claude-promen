<?php
/**
 * Verify steel filter options match effective product steels in category.
 * Usage: wp eval-file /scripts/verify-steel-filters.php [category_slug]
 */

$slug = $args[0] ?? 'troyniki';
$term = get_term_by( 'slug', $slug, 'product_cat' );
if ( ! $term ) {
	WP_CLI::error( "Category not found: {$slug}" );
}

// Simulate category archive scope.
global $wp_query;
$wp_query = new WP_Query();
$wp_query->is_tax = true;
$wp_query->queried_object    = $term;
$wp_query->queried_object_id = (int) $term->term_id;

$cat_id = promen_scope_cat_id();
WP_CLI::log( "Category: {$term->name} (id={$cat_id})" );

$opts = promen_multi_options( 'steel', 50 );
WP_CLI::log( 'Steel filter options:' );
foreach ( $opts as $o ) {
	WP_CLI::log( sprintf( '  %3d  %s (%s)', $o['count'], $o['name'], $o['slug'] ) );
}

// Products in category: collect effective steels from listing.
$products = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => [
			[
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => array_merge( [ $cat_id ], get_term_children( $cat_id, 'product_cat' ) ),
				'include_children' => true,
			],
		],
	]
);

$listed = [];
foreach ( $products as $pid ) {
	foreach ( promen_product_steel_slugs( (int) $pid ) as $s ) {
		$listed[ $s ] = ( $listed[ $s ] ?? 0 ) + 1;
	}
}

$filter_slugs = array_column( $opts, 'slug' );
$orphan       = array_diff( $filter_slugs, array_keys( $listed ) );
$missing      = array_diff( array_keys( $listed ), $filter_slugs );

if ( $orphan ) {
	WP_CLI::warning( 'Filter options WITHOUT products in list: ' . implode( ', ', $orphan ) );
} else {
	WP_CLI::success( 'No orphan steel filter options.' );
}

if ( $missing ) {
	WP_CLI::warning( 'Listed steels NOT in filter (top 10): ' . implode( ', ', array_slice( $missing, 0, 10 ) ) );
}

// Test steel=10 filter if present.
if ( in_array( '10', $filter_slugs, true ) ) {
	$_GET['steel'] = '10';
	$q             = new WP_Query(
		[
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'tax_query'      => [
				[
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => [ $cat_id ],
					'include_children' => true,
				],
			],
		]
	);
	// posts_where filter only runs on main_query — sample manually.
	WP_CLI::log( "Note: steel=10 filter query needs main_query; found {$q->found_posts} without steel WHERE." );
	unset( $_GET['steel'] );
}
