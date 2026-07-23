<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CatalogSchemaTest extends TestCase {

	public function test_troyniki_columns_and_facets(): void {
		$schema = promen_catalog_group_schema( 'troyniki' );
		$this->assertContains( 'dn', $schema['columns'] );
		$this->assertContains( 'dn2', $schema['columns'] );
		$this->assertContains( 'steel', $schema['facets'] );
		$this->assertContains( 'industry', $schema['facets'] );
		$this->assertContains( 'gost', $schema['facets'] );
	}

	public function test_flancy_has_pn_facet(): void {
		$schema = promen_catalog_group_schema( 'flancy-plosk' );
		$this->assertContains( 'pn', $schema['facets'] );
		$this->assertContains( 'flange_type', $schema['columns'] );
	}

	public function test_krepezh_fastener_columns(): void {
		$cols = promen_catalog_schema_columns( 'bolty' );
		$keys = array_column( $cols, 'key' );
		$this->assertContains( 'thread', $keys );
		$this->assertContains( 'strength', $keys );
	}
}
