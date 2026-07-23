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

require_once __DIR__ . '/../../wp-content/themes/promen/inc/catalog-search.php';
