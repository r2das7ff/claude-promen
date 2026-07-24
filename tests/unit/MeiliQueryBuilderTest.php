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

	public function test_exclude_drops_own_group_but_keeps_others(): void {
		$q = new Promen_Catalog_Query();
		$q->group  = 'otvody';
		$q->gost   = [ 'gost-17375-2001' ];
		$q->steel  = [ '20' ];
		$q->dn_min = 50;

		$filter = promen_catalog_meili_filter( $q, [ 'gost' ] );
		$this->assertStringNotContainsString( 'norm_slug', $filter, 'исключённая группа не фильтрует' );
		$this->assertStringContainsString( 'steels = "20"', $filter );
		$this->assertStringContainsString( 'dn >= 50', $filter );
		$this->assertStringContainsString( 'category = "otvody"', $filter, 'категория остаётся всегда' );

		$no_dn = promen_catalog_meili_filter( $q, [ 'dn' ] );
		$this->assertStringNotContainsString( 'dn >=', $no_dn );
		$this->assertStringContainsString( 'norm_slug = "gost-17375-2001"', $no_dn );
	}

	public function test_active_facets_lists_only_groups_with_selection(): void {
		$q = new Promen_Catalog_Query();
		$q->gost   = [ 'gost-17375-2001' ];
		$q->pn_max = 16;
		$this->assertSame( [ 'gost', 'pn' ], promen_catalog_query_active_facets( $q ) );

		$empty = new Promen_Catalog_Query();
		$this->assertSame( [], promen_catalog_query_active_facets( $empty ) );
	}

	public function test_wall_range_filter_and_exclude(): void {
		$q = Promen_Catalog_Query::from_array( [ 'group' => 'truby', 's_min' => '2.5', 's_max' => '8' ] );
		$this->assertSame( 2.5, $q->s_min );

		$filter = promen_catalog_meili_filter( $q );
		$this->assertStringContainsString( 's >= 2.5', $filter );
		$this->assertStringContainsString( 's <= 8', $filter );
		$this->assertSame( [ 's' ], promen_catalog_query_active_facets( $q ) );

		$no_s = promen_catalog_meili_filter( $q, [ 's' ] );
		$this->assertStringNotContainsString( 's >=', $no_s );
	}
}
