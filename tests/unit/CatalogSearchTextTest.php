<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CatalogSearchTextTest extends TestCase {

	public function test_search_text_dedupes_whitespace(): void {
		$text = promen_catalog_build_search_text( [
			'title' => 'Тройник',
			'sku'   => 'A-1',
			'norm'  => 'ГОСТ 17376',
		] );
		$this->assertSame( 'Тройник A-1 ГОСТ 17376', $text );
	}
}
