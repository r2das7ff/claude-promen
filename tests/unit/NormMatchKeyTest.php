<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Ключ сопоставления обозначений нормативов (реестр «Нормативной базы» ↔ каталог).
 *
 * Тот же ключ считает assets/js/nb.js — расхождение здесь означает, что
 * кнопка «Открыть в каталоге» перестанет ставить документ в фильтр.
 */
final class NormMatchKeyTest extends TestCase {

	/** Год пишут и двумя, и четырьмя цифрами — ключ обязан совпасть. */
	public function test_short_and_full_year_give_same_key(): void {
		$this->assertSame(
			promen_norm_match_key( 'ГОСТ 22793-1983' ),
			promen_norm_match_key( 'ГОСТ 22793-83' )
		);
		$this->assertSame( 'ГОСТ 22793', promen_norm_match_key( 'ГОСТ 22793-83' ) );
	}

	/** Разделители номера у реестра и каталога разные: дефис против точки. */
	public function test_separators_are_normalised(): void {
		$this->assertSame(
			promen_norm_match_key( 'ОСТ 34.10.763-97' ),
			promen_norm_match_key( 'ОСТ 34-10-763-97' )
		);
		$this->assertSame( 'ОСТ 34.10.763', promen_norm_match_key( 'ОСТ 34-10-763-97' ) );
	}

	/** «ЦКТИ» и хвост в скобках в каталог не попадают. */
	public function test_cktu_and_parenthetical_are_dropped(): void {
		$this->assertSame( 'СТО 321.02', promen_norm_match_key( 'СТО ЦКТИ 321.02-2009' ) );
		$this->assertSame( 'СТО 321.02', promen_norm_match_key( 'СТО 321.02' ) );
		$this->assertSame( 'ОСТ 34.10.761', promen_norm_match_key( 'ОСТ 34-10-761-97 (часть III)' ) );
	}

	/** У «СТО 321.01» «.01» — часть номера, а не год: точка его защищает. */
	public function test_dotted_tail_is_not_a_year(): void {
		$this->assertSame( 'СТО 318.01', promen_norm_match_key( 'СТО 318.01' ) );
		$this->assertNotSame(
			promen_norm_match_key( 'СТО 318.01' ),
			promen_norm_match_key( 'СТО 318.03' )
		);
	}

	/** «ГОСТ Р» — отдельный тип, склейки с «ГОСТ» быть не должно. */
	public function test_gost_r_is_a_separate_type(): void {
		$this->assertSame( 'ГОСТР 54432', promen_norm_match_key( 'ГОСТ Р 54432-2011' ) );
		$this->assertNotSame(
			promen_norm_match_key( 'ГОСТ 54432-2011' ),
			promen_norm_match_key( 'ГОСТ Р 54432-2011' )
		);
	}

	/** Не обозначение документа — пустой ключ, ссылка уходит в фолбэк. */
	public function test_non_designation_gives_empty_key(): void {
		$this->assertSame( '', promen_norm_match_key( 'RU С-RU.АБ53.В.08323/23' ) );
		$this->assertSame( '', promen_norm_match_key( '' ) );
	}
}
