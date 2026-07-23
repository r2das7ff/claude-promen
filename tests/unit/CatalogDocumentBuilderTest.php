<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CatalogDocumentBuilderTest extends TestCase {

	public function test_builds_search_text_with_sku_and_norm(): void {
		$doc = promen_catalog_document_from_fields( [
			'product_id'   => 1,
			'sku'          => 'TR-001',
			'title'        => 'Тройник 150',
			'norm'         => 'ГОСТ 17376-2001',
			'steels'       => [ '09g2s', '20' ],
			'steel_labels' => [ '09Г2С', '20' ],
			'dn'           => 150,
		] );

		$this->assertSame( 'TR-001', $doc['sku'] );
		$this->assertContains( '09g2s', $doc['steels'] );
		$this->assertStringContainsString( 'TR-001', $doc['search_text'] );
		$this->assertStringContainsString( 'ГОСТ 17376-2001', $doc['search_text'] );
		$this->assertStringContainsString( '150', $doc['search_text'] );
	}

	public function test_numeric_cast_ignores_zero(): void {
		$doc = promen_catalog_document_from_fields( [
			'product_id' => 2,
			'dn'         => '0',
			'pn'         => '16',
		] );
		$this->assertNull( $doc['dn'] );
		$this->assertSame( 16.0, $doc['pn'] );
	}
}
