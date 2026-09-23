<?php

declare(strict_types=1);

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * DN из таблицы стандарта против DN по наружному диаметру.
 *
 * У деталей высокого давления и паропроводов Dу задан таблицей по исполнениям
 * и идёт по внутреннему проходу: ГОСТ 22793-83 — Dу 100 это 127×14 … 180×40.
 * Флаг dn_by_table ставит scripts/dn-table-fix: с ним DN из трубного ряда
 * по наружному диаметру не подставляется.
 */
final class SanitizeDimsTableDnTest extends TestCase {

	public function test_od_row_applies_without_flag(): void {
		$dims = promen_sanitize_dims( [ 'dn' => '100', 'outer_diameter' => '159', 'wall_thickness' => '28' ] );
		$this->assertSame( '150', $dims['dn'] );
	}

	public function test_table_dn_survives_with_flag(): void {
		$dims = promen_sanitize_dims( [ 'dn' => '100', 'outer_diameter' => '159', 'wall_thickness' => '28', 'dn_by_table' => '1' ] );
		$this->assertSame( '100', $dims['dn'] );
	}

	/** Dу 6 при исполнении 6 — не «DN = номер исполнения», раз DN из таблицы. */
	public function test_flag_protects_small_table_dn(): void {
		$dims = promen_sanitize_dims( [ 'dn' => '6', 'execution' => '6', 'outer_diameter' => '15', 'wall_thickness' => '4.5', 'dn_by_table' => '1' ] );
		$this->assertSame( '6', $dims['dn'] );
	}

	/** Флаг без самого DN ничего не держит — DN берётся из ряда как обычно. */
	public function test_flag_without_dn_falls_back_to_od_row(): void {
		$dims = promen_sanitize_dims( [ 'outer_diameter' => '159', 'dn_by_table' => '1' ] );
		$this->assertSame( '150', $dims['dn'] );
	}

	/** 225 есть в ряду ГОСТ 28338: паропроводы СТО ЦКТИ 321.xx, 273×26 → Dу 225. */
	public function test_225_is_standard_dn(): void {
		$this->assertTrue( promen_dn_is_standard( '225' ) );
		$this->assertFalse( promen_dn_is_standard( '120' ) );
	}
}
