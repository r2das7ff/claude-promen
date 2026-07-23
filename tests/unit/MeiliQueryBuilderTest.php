<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Promen_Catalog_Query;

final class MeiliQueryBuilderTest extends TestCase {

	public function test_steel_and_dn_range_filter(): void {
		$q = new Promen_Catalog_Query();
		$q->group   = 'troyniki';
		$q->dn_min  = 50;
		$q->dn_max  = 200;
		$q->steel   = [ '10', '09g2s' ];

		$filter = promen_catalog_meili_filter( $q );

		$this->assertStringContainsString( 'category = "troyniki"', $filter );
		$this->assertStringContainsString( 'dn >= 50', $filter );
		$this->assertStringContainsString( 'dn <= 200', $filter );
		$this->assertStringContainsString( 'steels = "10"', $filter );
		$this->assertStringContainsString( 'steels = "09g2s"', $filter );
		$this->assertStringContainsString( ' AND ', $filter );
	}

	public function test_industry_filter(): void {
		$q = new Promen_Catalog_Query();
		$q->industry = [ 'aes', 'tes' ];
		$filter      = promen_catalog_meili_filter( $q );
		$this->assertStringContainsString( 'industries = "aes"', $filter );
		$this->assertStringContainsString( 'industries = "tes"', $filter );
	}

	public function test_gost_or_filter(): void {
		$q = new Promen_Catalog_Query();
		$q->gost = [ 'gost-17376-2001', 'gost-17375-2001' ];
		$filter  = promen_catalog_meili_filter( $q );
		$this->assertStringContainsString( 'norm_slug = "gost-17376-2001"', $filter );
		$this->assertStringContainsString( ' OR ', $filter );
	}
}
