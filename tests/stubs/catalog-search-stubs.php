<?php
/**
 * Stubs for catalog-search unit tests (no WordPress).
 */

if ( ! function_exists( 'promen_catalog_group_slugs' ) ) {
	function promen_catalog_group_slugs( string $group ): array {
		if ( $group === '' ) {
			return [];
		}
		if ( $group === 'flancy' ) {
			return [ 'flancy', 'flancy-plosk', 'flancy-vorot' ];
		}
		return [ $group ];
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $s ) {
		return strtolower( trim( preg_replace( '/[^a-z0-9\-]+/i', '-', (string) $s ), '-' ) );
	}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $s ) {
		return trim( preg_replace( '/[
	 ]+/', ' ', (string) $s ) );
	}
}
if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $s ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) );
	}
}

require_once __DIR__ . '/../../wp-content/themes/promen/inc/catalog-search.php';
