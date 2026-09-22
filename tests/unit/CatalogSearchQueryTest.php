<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Promen_Catalog_Query;

/**
 * Разбор поисковой строки каталога.
 *
 * На проде работает SQL-фолбэк: он искал фразу одним куском, поэтому
 * «тройник 108х4» не находил ни одной из девяти реальных позиций.
 */
final class CatalogSearchQueryTest extends TestCase {

	/** Любой знак умножения приводится к «×» — он стоит в названиях. */
	public function test_multiplication_sign(): void {
		$this->assertSame( 'тройник 108×4', promen_catalog_normalize_q( 'тройник 108х4' ) );
		$this->assertSame( 'тройник 108×4', promen_catalog_normalize_q( 'тройник 108x4' ) );
		$this->assertSame( 'тройник 108×4', promen_catalog_normalize_q( 'тройник 108*4' ) );
		$this->assertSame( 'тройник 108×4', promen_catalog_normalize_q( 'тройник 108 х 4' ) );
		$this->assertSame( '21.3×2', promen_catalog_normalize_q( '21,3х2' ) );
	}

	/** Марку стали трогать нельзя: «12Х18Н10Т» — не типоразмер. */
	public function test_steel_grade_survives(): void {
		$this->assertSame( '12Х18Н10Т', promen_catalog_normalize_q( '12Х18Н10Т' ) );
		$this->assertSame( 'отвод 12х18н10т', promen_catalog_normalize_q( 'отвод 12х18н10т' ) );
		$this->assertSame( '09Г2С', promen_catalog_normalize_q( '09Г2С' ) );
	}

	/** «ду100» → «100»: в данных условный проход записан числом. */
	public function test_dn_prefix_stripped(): void {
		$this->assertSame( 'фланец 100', promen_catalog_normalize_q( 'фланец ду100' ) );
		$this->assertSame( 'фланец 100', promen_catalog_normalize_q( 'фланец ду 100' ) );
		$this->assertSame( 'фланец 100', promen_catalog_normalize_q( 'фланец дн-100' ) );
		// Без числа приставка не значит ничего и остаётся как есть.
		$this->assertSame( 'ду', promen_catalog_normalize_q( 'ду' ) );
	}

	/** Лишние пробелы схлопываются, пустая строка остаётся пустой. */
	public function test_whitespace(): void {
		$this->assertSame( 'тройник 108', promen_catalog_normalize_q( "  тройник   108 " ) );
		$this->assertSame( '', promen_catalog_normalize_q( '   ' ) );
	}

	/** Запрос режется на слова: каждое обязано встретиться в строке. */
	public function test_tokens(): void {
		$this->assertSame( [ 'тройник', '108', '4' ], promen_catalog_q_tokens( 'тройник 108 4' ) );
		$this->assertSame( [ 'гост', '17376' ], promen_catalog_q_tokens( 'гост 17376.' ) );
		$this->assertSame( [ 'отвод', '90°', '108×4' ], promen_catalog_q_tokens( 'отвод 90°, 108×4' ) );
	}

	/** Потолок в шесть слов — каждое добавляет свой LIKE по payload. */
	public function test_tokens_capped(): void {
		$tokens = promen_catalog_q_tokens( 'один два три четыре пять шесть семь восемь' );
		$this->assertCount( 6, $tokens );
	}

	/** Типоразмер для порядка выдачи: и «108×4», и «108 4». */
	public function test_size_token(): void {
		$this->assertSame( '108×4', promen_catalog_q_size_token( 'тройник 108×4' ) );
		$this->assertSame( '108×4', promen_catalog_q_size_token( 'тройник 108 4' ) );
		$this->assertSame( '21.3×2', promen_catalog_q_size_token( 'отвод 21.3×2 исп. 1' ) );
		$this->assertSame( '', promen_catalog_q_size_token( 'тройник гост 17376' ) );
		$this->assertSame( '', promen_catalog_q_size_token( 'отвод' ) );
	}

	/** Запрос нормализуется на разборе — движку уже приходит «×». */
	public function test_query_normalizes_on_parse(): void {
		$query = Promen_Catalog_Query::from_array( [ 'group' => 'troyniki', 'q' => 'тройник 108х4' ] );
		$this->assertSame( 'тройник 108×4', $query->q );
	}
}
