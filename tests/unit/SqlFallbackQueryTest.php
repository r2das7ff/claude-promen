<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Promen_Catalog_Query;

final class SqlFallbackQueryTest extends TestCase {

	public function test_category_filter_in_meili_string(): void {
		$q = new Promen_Catalog_Query();
		$q->group = 'flancy';
		$filter   = promen_catalog_meili_filter( $q );
		$this->assertStringContainsString( 'category = "flancy"', $filter );
		$this->assertStringContainsString( ' OR ', $filter );
	}
}
