<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Соответствие «наружный диаметр → условный проход».
 *
 * Таблица давала для европейского ряда DN на ступень больше (21,3 → DN 20),
 * расходясь с обратной функцией в том же файле и с данными каталога:
 * у 470 строк с Dн 17,2 в источнике стоит DN 10.
 */
final class PipeDnFromOdTest extends TestCase {

	/** Ряд 1 ГОСТ 17375/17376-2001, он же EN 10220. */
	public function test_european_row(): void {
		$row = [
			'10.2'  => '6',
			'13.5'  => '8',
			'17.2'  => '10',
			'21.3'  => '15',
			'26.9'  => '20',
			'33.7'  => '25',
			'42.4'  => '32',
			'48.3'  => '40',
			'60.3'  => '50',
			'76.1'  => '65',
			'88.9'  => '80',
			'114.3' => '100',
			'139.7' => '125',
			'168.3' => '150',
			'219.1' => '200',
		];
		foreach ( $row as $od => $dn ) {
			$this->assertSame( $dn, promen_pipe_dn_from_od( (string) $od ), "Dн {$od}" );
		}
	}

	/** Ряд 2 — российский сортамент. */
	public function test_russian_row(): void {
		$row = [ '14' => '10', '18' => '15', '25' => '20', '32' => '25', '38' => '32', '45' => '40', '57' => '50', '76' => '65', '89' => '80', '108' => '100', '133' => '125', '159' => '150', '219' => '200', '426' => '400' ];
		foreach ( $row as $od => $dn ) {
			$this->assertSame( $dn, promen_pipe_dn_from_od( (string) $od ), "Dн {$od}" );
		}
	}

	/** Таблица не должна противоречить обратной функции. */
	public function test_agrees_with_reverse(): void {
		foreach ( [ '6', '8', '10', '15', '20', '25', '32', '40', '50', '65', '80', '100', '125', '150', '200' ] as $dn ) {
			$od = promen_pipe_od_from_dn( $dn );
			$this->assertNotSame( '', $od, "DN {$dn}: нет наружного диаметра" );
			$this->assertSame( $dn, promen_pipe_dn_from_od( $od ), "DN {$dn} → Dн {$od} → обратно" );
		}
	}

	/** Округление обозначения в источнике: 220 вместо 219, 425 вместо 426. */
	public function test_rounded_designations(): void {
		$this->assertSame( '200', promen_pipe_dn_from_od( '220' ) );
		$this->assertSame( '400', promen_pipe_dn_from_od( '425' ) );
		$this->assertSame( '65', promen_pipe_dn_from_od( '75' ) );
		$this->assertSame( '25', promen_pipe_dn_from_od( '34' ) );
		$this->assertSame( '40', promen_pipe_dn_from_od( '48' ) );
	}

	/** Дальше миллиметра не тянемся: чужой DN хуже пустого. */
	public function test_no_guessing_beyond_one_mm(): void {
		foreach ( [ '50', '100', '125', '150', '460', '700', '94' ] as $od ) {
			$this->assertSame( '', promen_pipe_dn_from_od( $od ), "Dн {$od} не должен получать DN" );
		}
	}

	/** Мусор и пустая строка. */
	public function test_garbage(): void {
		$this->assertSame( '', promen_pipe_dn_from_od( '' ) );
		$this->assertSame( '', promen_pipe_dn_from_od( '0' ) );
		$this->assertSame( '', promen_pipe_dn_from_od( 'ерунда' ) );
	}
}
