<?php
/**
 * Парсер строки спецификации и отбор марок — чистая логика подборщика.
 */

namespace Promen\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SelectorParser extends TestCase {

	public function test_temp_range_upper_only(): void {
		$this->assertSame( [ null, 425 ], promen_selector_temp_range( 'до +425°C' ) );
	}

	public function test_temp_range_with_typographic_minus(): void {
		// Справочник набран типографским минусом и многоточием — ASCII-парсер
		// на такой строке молча вернул бы [70, 475] и пустил бы Ст20 на −70.
		$this->assertSame( [ -70, 475 ], promen_selector_temp_range( "\u{2212}70\u{2026}+475°C" ) );
	}

	public function test_temp_range_empty(): void {
		$this->assertSame( [ null, null ], promen_selector_temp_range( '—' ) );
	}

	public function test_pn_nominal_to_mpa(): void {
		$this->assertSame( 16.0, promen_selector_pn_mpa( '160' ) );
		$this->assertNull( promen_selector_pn_mpa( '—' ) );
	}

	public function test_steel_norm_latin_lookalikes(): void {
		// «12X18H10T» латиницей — самый частый способ набрать нержавейку.
		$this->assertSame(
			promen_selector_steel_norm( '12Х18Н10Т' ),
			promen_selector_steel_norm( '12X18H10T' )
		);
	}

	public function test_steel_norm_strips_st_prefix(): void {
		$this->assertSame( '20', promen_selector_steel_norm( 'ст.20' ) );
		$this->assertSame( '20', promen_selector_steel_norm( 'Сталь 20' ) );
		// ВСт3сп — отдельная марка, префикс срезать нельзя.
		$this->assertSame( 'вст3сп', promen_selector_steel_norm( 'ВСт3сп' ) );
	}

	public function test_parse_full_spec_line(): void {
		$p = promen_selector_parse( 'Отвод 90° 108х4 ст20 ГОСТ 17375-2001' );
		$this->assertSame( 'otvody', $p['group'] );
		$this->assertSame( 108.0, $p['d'] );
		$this->assertSame( 4.0, $p['s'] );
		$this->assertSame( 90.0, $p['angle'] );
		$this->assertSame( [ '20' ], $p['steel'] );
		$this->assertSame( '17375-2001', $p['gost'][0]['number'] );
		$this->assertSame( [], $p['unknown'] );
	}

	public function test_parse_synonym_and_dn(): void {
		$p = promen_selector_parse( 'колено 90 град ду100 09Г2С' );
		$this->assertSame( 'otvody', $p['group'] );
		$this->assertSame( 100.0, $p['dn'] );
		$this->assertSame( 90.0, $p['angle'] );
		$this->assertSame( [ '09Г2С' ], $p['steel'] );
	}

	public function test_parse_steel_synonym_group(): void {
		$p = promen_selector_parse( 'тройник 219х8 нержавейка' );
		$this->assertSame( 'troyniki', $p['group'] );
		$this->assertSame( 219.0, $p['d'] );
		$this->assertContains( '12Х18Н10Т', $p['steel'] );
	}

	public function test_parse_pressure_nominal_and_mpa(): void {
		$this->assertSame( 1.6, promen_selector_parse( 'фланец ду100 ру16' )['pn'] );
		$this->assertSame( 4.0, promen_selector_parse( 'фланец ду100 4 МПа' )['pn'] );
	}

	public function test_parse_temperature_not_confused_with_angle(): void {
		// «540°С» — температура, а не угол: различает буква С после градуса.
		$p = promen_selector_parse( 'паропровод 540°С отвод 90°' );
		$this->assertSame( 540.0, $p['temp'] );
		$this->assertSame( 90.0, $p['angle'] );
	}

	public function test_parse_decimal_comma_inside_number(): void {
		// Запятая внутри числа — десятичная, между словами — разделитель списка.
		$p = promen_selector_parse( 'отвод 21,3х2,5' );
		$this->assertSame( 21.3, $p['d'] );
		$this->assertSame( 2.5, $p['s'] );
	}

	public function test_parse_thread_goes_to_dn(): void {
		// У крепежа резьба лежит в DN каталога: шпилька M20 → DN 20.
		$p = promen_selector_parse( 'шпилька М20' );
		$this->assertSame( 'shpilki', $p['group'] );
		$this->assertSame( 20.0, $p['dn'] );
		$this->assertSame( 'M20', $p['thread'] );
	}

	public function test_parse_thread_with_pitch_is_not_a_size(): void {
		// «M20х1.5» не должно прочитаться как диаметр 20 × стенка 1.5.
		$p = promen_selector_parse( 'болт M20х1.5' );
		$this->assertSame( 20.0, $p['dn'] );
		$this->assertNull( $p['d'] );
		$this->assertNull( $p['s'] );
	}

	public function test_parse_collects_unknown_tokens(): void {
		$p = promen_selector_parse( 'отвод 108х4 бурбулятор' );
		$this->assertSame( [ 'бурбулятор' ], $p['unknown'] );
	}

	public function test_parse_object_query(): void {
		// «Строительство котельной» — объект, а не изделие: типа нет,
		// параметров нет, но отрасль вывести можно.
		$p = promen_selector_parse( 'строительство котельной' );
		$this->assertSame( 'котельн', $p['object'] );
		$this->assertSame( '', $p['group'] );
		$this->assertSame( [], $p['unknown'] );
		$this->assertArrayHasKey( $p['object'], promen_selector_objects() );
	}

	public function test_parse_object_cases_and_synonyms(): void {
		$this->assertSame( 'теплотрасс', promen_selector_parse( 'реконструкция теплотрассы' )['object'] );
		$this->assertSame( 'паропровод', promen_selector_parse( 'монтаж паропровода' )['object'] );
		$this->assertSame( 'цтп', promen_selector_parse( 'ЦТП' )['object'] );
	}

	public function test_object_does_not_override_product_type(): void {
		// В «отводы для котельной» ведущее слово — отвод; котельная лишь
		// уточняет отрасль, тип изделия она перебивать не должна.
		$p = promen_selector_parse( 'отводы ду100 для котельной' );
		$this->assertSame( 'otvody', $p['group'] );
		$this->assertSame( 'котельн', $p['object'] );
		$this->assertSame( 100.0, $p['dn'] );
	}

	public function test_object_industries_are_known_facets(): void {
		// Слаги отраслей в словаре объектов должны существовать в каталоге,
		// иначе фильтр молча не сработает.
		$known = array_keys( promen_selector_industry_apps() );
		foreach ( promen_selector_objects() as $root => $obj ) {
			$this->assertNotSame( '', $obj['label'], "у объекта {$root} нет названия" );
			foreach ( $obj['ind'] as $slug ) {
				$this->assertContains( $slug, $known, "объект {$root} ссылается на несуществующую отрасль {$slug}" );
			}
		}
	}

	public function test_pn_filter_only_where_pn_is_modelled(): void {
		// У фланцев и арматуры PN есть в данных — по нему можно фильтровать.
		$this->assertTrue( promen_selector_group_uses_pn( 'flancy' ) );
		$this->assertTrue( promen_selector_group_uses_pn( 'armatura' ) );
		// У отвода и трубы своего PN нет: фильтр `pn >= X` выбрасывал бы
		// документы с пустым полем и стирал категорию целиком.
		$this->assertFalse( promen_selector_group_uses_pn( 'otvody' ) );
		$this->assertFalse( promen_selector_group_uses_pn( 'truby' ) );
		$this->assertFalse( promen_selector_group_uses_pn( 'shpilki' ) );
		$this->assertFalse( promen_selector_group_uses_pn( '' ) );
	}

	public function test_steel_pick_rejects_by_temperature(): void {
		$r = promen_selector_steel_pick( [ 'temp' => 540, 'available' => [ '20', '12Х1МФ' ] ] );
		$keys = array_column( $r['fit'], 'key' );
		$this->assertSame( [ '12Х1МФ' ], $keys );          // до +585°C — проходит
		$this->assertArrayHasKey( '20', r_reject( $r ) );   // до +425°C — нет
	}

	public function test_steel_pick_rejects_by_pressure(): void {
		$r = promen_selector_steel_pick( [ 'pressure' => 10.0, 'available' => [ '20', '08Х18Н10Т' ] ] );
		$keys = array_column( $r['fit'], 'key' );
		$this->assertSame( [ '20' ], $keys );               // PN 160 = 16 МПа
		$this->assertArrayHasKey( '08Х18Н10Т', r_reject( $r ) ); // PN 63 = 6.3 МПа
	}

	public function test_steel_pick_warns_on_unnormed_low_limit(): void {
		// У Ст20 нижний предел справочником не задан: на −40 марка не должна
		// ни молча пройти, ни молча выпасть — она уходит в warn.
		$r = promen_selector_steel_pick( [ 'temp' => -40, 'available' => [ '20', '09Г2С' ] ] );
		$this->assertSame( [ '09Г2С' ], array_column( $r['fit'], 'key' ) );
		$this->assertSame( [ '20' ], array_column( $r['warn'], 'key' ) );
	}

	public function test_steel_pick_filters_by_industry(): void {
		$r = promen_selector_steel_pick( [ 'industry' => 'aes', 'available' => [ '20', '12Х18Н10Т' ] ] );
		$this->assertSame( [ '12Х18Н10Т' ], array_column( $r['fit'], 'key' ) );
	}

	public function test_steel_pick_keeps_numeric_grades_from_array_keys(): void {
		// available приходит из array_keys() карты марок, и PHP отдаёт «20»
		// как int. Без приведения к строке самые ходовые углеродистые марки
		// молча выпадали из рекомендаций — проверяем именно этот путь.
		$map   = [ '20' => '20', '10' => '10', '09Г2С' => '09g2s' ];
		$r     = promen_selector_steel_pick( [ 'temp' => 130, 'available' => array_keys( $map ) ] );
		$keys  = array_column( $r['fit'], 'key' );
		$this->assertContains( '20', $keys );
		$this->assertContains( '10', $keys );
	}

	public function test_steel_pick_ignores_grades_absent_from_group(): void {
		// Марки нет в ассортименте группы — это не отказ по параметрам,
		// она не должна попасть ни в одну из корзин.
		$r = promen_selector_steel_pick( [ 'temp' => 200, 'available' => [ '20' ] ] );
		$this->assertSame( [ '20' ], array_column( $r['fit'], 'key' ) );
		$this->assertArrayNotHasKey( '09Г2С', $r['reject'] );
	}
}

/** Отказы как есть — вынесено, чтобы не тащить длинный доступ в assert. */
function r_reject( array $r ): array {
	return $r['reject'];
}
