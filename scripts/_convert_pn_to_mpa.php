<?php
/**
 * Приведение давлений к МПа (2026-07-24).
 *
 * Правила (по диагностике _diag_pn_units*.php):
 *  1. Фланцы ГОСТ 33259 / 12820 / 12821 / 28759.2 — PN хранился кгс/см²-номиналом
 *     (title «PN250» ↔ pn=250): номинальное соответствие МПа = PN/10.
 *     Проверка: ряды после конвертации совпадают с рядами стандартов
 *     (12820: 0,1–2,5 МПа; 28759.2: 0,3/0,6/1,0/1,6 МПа; 33259: до 25 МПа).
 *  2. Тройники ОСТ 34.10.511-1990 — норма ТЭС низкого давления (≤ 2,5 МПа);
 *     значения pn > 2.5 — мусор импорта (DN магистрали: pn==dn у 56 строк,
 *     масса 36.1/52, склейки 293.2/379.4) → обнуляются.
 *  3. Остальные группы уже в МПа (точные пересчёты 3.92/11.77/37.27 и
 *     МПа-ряды ГОСТ 22815/22822 до 100 МПа) — не трогаются.
 *
 * Запуск: docker compose run --rm wpcli eval-file /scripts/_convert_pn_to_mpa.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit( "wp eval-file only\n" ); }

global $wpdb;
$t = $wpdb->prefix . 'promen_catalog_rows';

$read_dims = static function ( int $pid, &$raw ) {
	$raw = get_post_meta( $pid, '_promen_dims', true );
	$d   = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	return is_array( $d ) ? $d : null;
};
$write_dims = static function ( int $pid, array $d, $raw ) {
	update_post_meta( $pid, '_promen_dims', is_array( $raw ) ? $d : wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
};

// ── 1. Фланцы: кгс-номинал → МПа (/10) ───────────────────────────
$flancy_norms = [ 'gost-33259-2015', 'gost-12820-1980', 'gost-12821-1980', 'gost-28759-2-2022' ];
$ph   = implode( ',', array_fill( 0, count( $flancy_norms ), '%s' ) );
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$pids = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$t} WHERE category LIKE 'flancy%%' AND norm_slug IN ({$ph}) AND pn IS NOT NULL", $flancy_norms ) );
echo 'фланцев к конвертации: ' . count( $pids ) . "\n";
$conv = 0;
foreach ( $pids as $pid ) {
	$pid = (int) $pid;
	$d   = $read_dims( $pid, $raw );
	if ( null === $d || ! isset( $d['pn'] ) || ! is_numeric( $d['pn'] ) ) {
		continue;
	}
	$old = (float) $d['pn'];
	if ( $old <= 0 ) {
		continue;
	}
	$new = $old / 10;
	// Красивый номинал: 0.25, 6.3, 25 — без длинных хвостов.
	$d['pn'] = (float) rtrim( rtrim( number_format( $new, 3, '.', '' ), '0' ), '.' );
	$write_dims( $pid, $d, $raw );
	promen_catalog_upsert( $pid );
	$conv++;
}
echo "фланцы: конвертировано {$conv}\n";

// ── 2. Тройники ОСТ 34.10.511-1990: pn > 2.5 — мусор → null ──────
$pids = $wpdb->get_col( "SELECT product_id FROM {$t} WHERE norm_slug = 'ost-34-10-511-1990' AND pn > 2.5" );
echo 'тройников 511-1990 с мусорным pn: ' . count( $pids ) . "\n";
$nulled = 0;
foreach ( $pids as $pid ) {
	$pid = (int) $pid;
	$d   = $read_dims( $pid, $raw );
	if ( null === $d || ! isset( $d['pn'] ) ) {
		continue;
	}
	$was = $d['pn'];
	unset( $d['pn'] );
	$write_dims( $pid, $d, $raw );
	promen_catalog_upsert( $pid );
	$nulled++;
}
echo "тройники: pn обнулён у {$nulled}\n";

promen_filters_cache_bump();

// ── 3. Проверка достоверности ────────────────────────────────────
echo "\n=== ПРОВЕРКА: ряды PN после конвертации ===\n";
foreach ( [
	"flancy%|gost-33259-2015",
	"flancy%|gost-12820-1980",
	"flancy%|gost-12821-1980",
	"flancy%|gost-28759-2-2022",
	"troyniki|ost-34-10-511-1990",
] as $probe ) {
	list( $cat, $norm ) = explode( '|', $probe );
	$v = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT pn FROM {$t} WHERE category LIKE %s AND norm_slug = %s AND pn IS NOT NULL ORDER BY pn", $cat, $norm ) );
	echo "{$norm}: " . ( $v ? implode( ', ', $v ) : '—' ) . "\n";
}
$mx = $wpdb->get_row( "SELECT MAX(pn) mx, COUNT(*) n FROM {$t} WHERE pn IS NOT NULL", ARRAY_A );
echo "максимум PN по каталогу: {$mx['mx']} МПа ({$mx['n']} строк с pn)\n";
echo 'строк с pn > 160: ' . (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE pn > 160" ) . "\n";
$spot = $wpdb->get_row( "SELECT pn, JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) ti FROM {$t} WHERE product_id = 116809", ARRAY_A );
echo "спот-чек #116809: pn={$spot['pn']} | {$spot['ti']}\n";
