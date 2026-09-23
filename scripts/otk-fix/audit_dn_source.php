<?php
/**
 * Сверка DN на витрине с DN из источника.
 *
 * Источник — атрибут pa_dn из импорта: его не трогали ни promen_sanitize_dims(),
 * ни второй заход ОТК, а _promen_dims.dn уже перезаписан «по сортаменту».
 * Где значения расходятся, смотрим, какое из двух ближе к фактическому проходу
 * (внутреннему диаметру или D − 2S):
 *
 *   проход — DN источника ближе: так бывает у деталей, чей стандарт задаёт Dу
 *            таблицей по исполнениям (ГОСТ 22790–22826-83, СТО ЦКТИ 321.xx),
 *            а витрина пересчитала DN из наружного диаметра;
 *   иное   — ближе DN витрины: чаще всего в источнике мусор («DN 07» —
 *            номер исполнения, «DN 137» — высота днища), но у тех же
 *            семейств сюда попадают и честные Dу стандарта (76×9 — Dу 50
 *            по ГОСТ 22793, хотя проход 58);
 *   нет d  — прохода не посчитать.
 *
 * Крепёж пропускается: там в dn лежит диаметр резьбы.
 *
 *   docker compose run --rm wpcli eval-file /scripts/otk-fix/audit_dn_source.php
 */

defined( 'ABSPATH' ) || die( "только через wp eval-file\n" );

$ids = get_posts( [ 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] );
$by  = [];
$ex  = [];
$i   = 0;
foreach ( $ids as $id ) {
	if ( ++$i % 500 === 0 ) {
		wp_cache_flush();
	}
	if ( promen_product_is_fastener( $id ) ) {
		continue;
	}
	$terms = wp_get_post_terms( $id, 'pa_dn', [ 'fields' => 'names' ] );
	if ( is_wp_error( $terms ) || ! $terms ) {
		continue;
	}
	$src  = str_replace( ',', '.', (string) $terms[0] );
	$dims = promen_get_dims( $id );
	$cur  = str_replace( ',', '.', (string) ( $dims['dn'] ?? '' ) );
	$od   = (float) str_replace( ',', '.', (string) ( $dims['outer_diameter'] ?? '' ) );
	$s    = (float) str_replace( ',', '.', (string) ( $dims['wall_thickness'] ?? '' ) );
	$din  = (float) str_replace( ',', '.', (string) ( $dims['inner_diameter'] ?? '' ) );
	if ( $din <= 0 && $od > 0 && $s > 0 ) {
		$din = $od - 2 * $s;
	}
	$norm = (string) get_post_meta( $id, '_promen_norm_key', true );
	if ( ! isset( $by[ $norm ] ) ) {
		$by[ $norm ] = [ 'n' => 0, 'same' => 0, 'bore' => 0, 'other' => 0, 'nobore' => 0 ];
	}
	$by[ $norm ]['n']++;
	if ( $src === $cur || ! is_numeric( $src ) ) {
		$by[ $norm ]['same']++;
		continue;
	}
	if ( $din <= 0 || ! is_numeric( $cur ) ) {
		$by[ $norm ]['nobore']++;
		continue;
	}
	$k = ( abs( $din - (float) $src ) < abs( $din - (float) $cur ) && promen_dn_is_standard( $src ) ) ? 'bore' : 'other';
	$by[ $norm ][ $k ]++;
	if ( count( $ex[ $norm . ' — ' . $k ] ?? [] ) < 2 ) {
		$ex[ $norm . ' — ' . $k ][] = sprintf( '%s: Dн %s S %s проход %.0f | источник DN %s, витрина DN %s', get_post_meta( $id, '_sku', true ), $od, $s ?: '—', $din, $src, $cur );
	}
}

uasort( $by, static fn( $a, $b ) => ( $b['bore'] + $b['other'] ) <=> ( $a['bore'] + $a['other'] ) );
printf( "%-28s %6s %6s %6s %6s %6s\n", 'норматив', 'всего', 'равны', 'проход', 'иное', 'нет d' );
$t = [ 'n' => 0, 'same' => 0, 'bore' => 0, 'other' => 0, 'nobore' => 0 ];
foreach ( $by as $norm => $r ) {
	foreach ( $t as $k => $v ) {
		$t[ $k ] += $r[ $k ];
	}
	if ( $r['bore'] + $r['other'] + $r['nobore'] === 0 ) {
		continue;
	}
	printf( "%-28s %6d %6d %6d %6d %6d\n", mb_substr( $norm, 0, 28 ), $r['n'], $r['same'], $r['bore'], $r['other'], $r['nobore'] );
}
printf( "%-28s %6d %6d %6d %6d %6d\n\n", 'ИТОГО', $t['n'], $t['same'], $t['bore'], $t['other'], $t['nobore'] );
foreach ( $ex as $k => $lines ) {
	echo $k, "\n  ", implode( "\n  ", $lines ), "\n";
}
