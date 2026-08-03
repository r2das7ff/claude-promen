<?php
/**
 * Ремонт крепёжных полей _promen_dims у фланцев ГОСТ 33259/12820/12821.
 *
 * Порча: bolt_circle_d / stud_count / bolt_d / flange_thickness (и местами
 * outer_diameter) — склейки парсера PDF при импорте (пример: DN50 PN16 тип 11
 * с «36×М12, Dб 180 при Dн 185»). Эталон собран из чистой копии ГОСТ 33259-2015
 * (таблицы 3/6) и ГОСТ 12820/12821-80 — scripts/_flange_repair.json,
 * см. _flange_repair_build.py.
 *
 * Правила: ряд по совпадению outer_diameter с D ряда (иначе ряд 1, D чинится);
 * bolt_d = номинал резьбы (не отверстие); b — из таблиц нормы товара;
 * поля без эталона очищаются. После — promen_catalog_upsert по каждому товару.
 *
 * Запуск: wp eval-file /scripts/_fix_flange_dims.php [dry]
 */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }

$dry = in_array( 'dry', $args ?? [], true );
$rep = json_decode( (string) file_get_contents( __DIR__ . '/_flange_repair.json' ), true );
if ( ! is_array( $rep ) ) { echo "нет _flange_repair.json\n"; return; }
$conn = $rep['conn']; $b33 = $rep['b33259']; $b20 = $rep['b12820']; $b21 = $rep['b12821'];

$fmt = static fn( $v ): string => $v === null ? '' : rtrim( rtrim( number_format( (float) $v, 2, '.', '' ), '0' ), '.' );

global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';
$norm_map = [ 'gost-33259-2015' => '33259', 'gost-12820-1980' => '12820', 'gost-12821-1980' => '12821' ];
$rows = $wpdb->get_results( "SELECT product_id, norm_slug, category FROM {$t} WHERE category LIKE 'flancy%' AND norm_slug IN ('gost-33259-2015','gost-12820-1980','gost-12821-1980')" );
$cat_type = [ 'flancy-plosk' => 'ФП', 'flancy-vorot' => 'ФВ', 'flancy-01' => '01', 'flancy-11' => '11' ];

$stat = [ 'fixed' => 0, 'noref' => 0, 'skip' => 0, 'd_fixed' => 0, 'cleared' => 0 ];
$samples = [];
foreach ( $rows as $cr ) {
	$pid  = (int) $cr->product_id;
	$norm = $norm_map[ $cr->norm_slug ];
	$raw  = get_post_meta( $pid, '_promen_dims', true );
	$d    = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $d ) ) { $stat['skip']++; continue; }

	$typ = (string) ( $d['flange_type'] ?? ( $d['product_type'] ?? '' ) );
	if ( $typ === '' ) {
		// Старые импорты без flange_type в dims: тип из категории канона,
		// затем по норме (12820 — плоские ФП, 12821 — воротниковые ФВ).
		$typ = $cat_type[ (string) $cr->category ] ?? ( $norm === '12820' ? 'ФП' : ( $norm === '12821' ? 'ФВ' : '' ) );
	}
	$dn  = $d['dn'] ?? null; $pn = $d['pn'] ?? null;
	if ( $typ === '' || ! is_numeric( $dn ) || ! is_numeric( $pn ) ) { $stat['skip']++; continue; }
	$ck = $fmt( $dn ) . '|' . $fmt( $pn );
	$c  = $conn[ $ck ] ?? null;
	if ( ! $c ) { $stat['noref']++; continue; }

	// выбор ряда по наружному диаметру
	$d_csv = is_numeric( $d['outer_diameter'] ?? null ) ? (float) $d['outer_diameter'] : null;
	$ряд = null; $row = null; $fix_d = false;
	if ( $d_csv !== null ) {
		foreach ( [ 'r1', 'r2' ] as $r ) {
			if ( isset( $c[ $r ]['D'] ) && abs( $c[ $r ]['D'] - $d_csv ) < 0.51 ) { $ряд = $r; $row = $c[ $r ]; break; }
		}
	}
	if ( ! $row ) {
		foreach ( [ 'r1', 'r2' ] as $r ) {
			if ( isset( $c[ $r ]['D'] ) ) { $ряд = $r; $row = $c[ $r ]; $fix_d = ( $d_csv !== null ); break; }
		}
	}
	if ( ! $row ) { $stat['noref']++; continue; }

	// толщина по норме товара
	$bv = null;
	if ( $norm === '33259' ) {
		$t33 = [ 'ФП' => '01', 'ФВ' => '11' ][ $typ ] ?? $typ;
		$bv  = $b33[ $t33 . '|' . $ck ][ $ряд ] ?? null;
	} elseif ( $norm === '12820' ) {
		$bv = $b20[ $ck ] ?? null;
	} else {
		$bv = $b21[ $ck ] ?? null;
		if ( $bv === null && is_numeric( $d['flange_thickness'] ?? null )
			&& (float) $d['flange_thickness'] >= 6 && (float) $d['flange_thickness'] <= 130 ) {
			$bv = (float) $d['flange_thickness']; // fill-значение правдоподобно — оставляем
		}
	}

	$new = [
		'bolt_circle_d'    => $fmt( $row['D2'] ?? null ),
		'stud_count'       => $fmt( $row['n'] ?? null ),
		'bolt_d'           => $fmt( $row['M'] ?? null ),
		'flange_thickness' => $fmt( $bv ),
	];
	if ( $fix_d || $d_csv === null ) {
		$new['outer_diameter'] = $fmt( $row['D'] ?? null );
		if ( $new['outer_diameter'] !== '' ) { $stat['d_fixed']++; }
	}
	if ( empty( $d['flange_type'] ) ) {
		$new['flange_type'] = $typ; // для колонок каталога (роадмап B1)
	}
	$changed = false;
	foreach ( $new as $k => $v ) {
		$old = (string) ( $d[ $k ] ?? '' );
		if ( $v === '' ) {
			if ( $old !== '' ) { unset( $d[ $k ] ); $changed = true; $stat['cleared']++; }
		} elseif ( $old !== $v ) {
			$d[ $k ] = $v; $changed = true;
		}
	}
	if ( ! $changed ) { continue; }
	if ( count( $samples ) < 5 ) {
		$samples[] = "#{$pid} {$norm} {$typ} DN{$fmt($dn)} PN{$fmt($pn)} [{$ряд}]: Dб={$new['bolt_circle_d']} n={$new['stud_count']} M={$new['bolt_d']} b={$new['flange_thickness']}";
	}
	if ( ! $dry ) {
		update_post_meta( $pid, '_promen_dims', is_array( $raw ) ? $d : wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
		promen_catalog_upsert( $pid, false );
	}
	$stat['fixed']++;
}
if ( ! $dry && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
echo ( $dry ? '[DRY] ' : '' ) . "починено {$stat['fixed']}, без эталона {$stat['noref']}, пропущено {$stat['skip']}, D исправлен {$stat['d_fixed']}, полей очищено {$stat['cleared']}\n";
foreach ( $samples as $s ) { echo "  {$s}\n"; }
