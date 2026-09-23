<?php
/**
 * DN по таблице стандарта у деталей высокого давления и паропроводов.
 *
 * Эти стандарты задают условный проход таблицей по исполнениям, и он идёт по
 * внутреннему проходу, а не по наружному диаметру трубы:
 *   ГОСТ 22793-83, стр. 2–3: Dу 100 — трубы 127×14, 140×20, 159×28, 180×40;
 *   ГОСТ 22818-83: Dу 100, исп. 2 — 160×26 (в названии «DN 100», в колонке было 150);
 *   СТО ЦКТИ 321.01-2009, табл. 1: исп. 016 — Dу 100 при D 133, dp 98;
 *   ОСТ 24.125.17-89 (та же серия АЭС, что 24.125.01…26): Ду по исполнениям.
 * promen_sanitize_dims() пересчитывала DN из наружного диаметра по обычному
 * трубному ряду, а второй заход ОТК 10.09 записал это в _promen_dims.dn.
 * Значение из источника сохранилось в атрибуте pa_dn (оно же число в артикуле
 * и колонка dn в CSV импорта).
 *
 * Скрипт кладёт pa_dn обратно в _promen_dims.dn и ставит флаг dn_by_table:
 * с ним promen_sanitize_dims() DN не пересчитывает. Берём только значения из
 * ряда ГОСТ 28338 — мусор источника («DN 07», «DN 97», «DN 120») не трогаем,
 * у таких позиций остаётся прежний DN.
 *
 * Описание товара хранит DN текстом («Угол 45°. DN 200. Наружный диаметр…»):
 * единственное «DN n» в нём приводится к таблице.
 *
 * Не трогает: названия, адреса, атрибуты. Идемпотентен.
 *
 * Запуск:  wp eval-file scripts/dn-table-fix/fix.php dry|apply
 * Результат: changes.tsv рядом со скриптом — для сверки прогона на проде.
 */

$mode = ( isset( $args[0] ) && 'apply' === $args[0] ) ? 'apply' : 'dry';

// Нормативы, где Dу задан таблицей стандарта. Ключи _promen_norm_key в базе
// записаны по-разному («gost-22793-1983», «СТО 321.01», «ОСТ 24.125.04-1989»),
// поэтому сверяем по номеру документа.
$families = [
	'ГОСТ 22793-83'       => '/22793/u',
	'ГОСТ 22818-83'       => '/22818/u',
	'СТО ЦКТИ 321.01–.05' => '/(?:^|\D)321[.\-]0[1-5](?:\D|$)/u',
	'ОСТ 24.125.xx-89'    => '/24[.\-]125[.\-]\d{2}(?:\D|$)/u',
];

global $wpdb;
$rows = $wpdb->get_results(
	"SELECT p.ID, m.meta_value AS norm FROM {$wpdb->posts} p
	 JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_promen_norm_key'
	 WHERE p.post_type = 'product' AND p.post_status = 'publish'
	 ORDER BY p.ID"
);

$stat    = [];
$changes = [];
$shown   = [];
$i       = 0;
foreach ( $rows as $r ) {
	$family = '';
	foreach ( $families as $name => $re ) {
		if ( preg_match( $re, (string) $r->norm ) ) {
			$family = $name;
			break;
		}
	}
	if ( '' === $family ) {
		continue;
	}
	if ( ++$i % 200 === 0 ) {
		wp_cache_flush();
	}
	$id = (int) $r->ID;
	$stat[ $family ] = $stat[ $family ] ?? [ 'всего' => 0, 'меняется' => 0, 'только флаг' => 0, 'мусор источника' => 0, 'нет pa_dn' => 0 ];
	$stat[ $family ]['всего']++;

	$terms = wp_get_post_terms( $id, 'pa_dn', [ 'fields' => 'names' ] );
	if ( is_wp_error( $terms ) || ! $terms ) {
		$stat[ $family ]['нет pa_dn']++;
		continue;
	}
	$src = str_replace( ',', '.', trim( (string) $terms[0] ) );
	if ( ! is_numeric( $src ) || ! promen_dn_is_standard( $src ) ) {
		$stat[ $family ]['мусор источника']++;
		continue;
	}
	$src = (string) (int) round( (float) $src );

	$raw = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true );
	if ( ! is_array( $raw ) ) {
		$stat[ $family ]['нет pa_dn']++;
		continue;
	}
	// У фланцев DN из наружного диаметра и так не выводится (там D фланца).
	if ( promen_dims_look_like_flange( $raw ) ) {
		continue;
	}
	// Значение из ряда ещё не значит Dу этой детали: у тройника 530×28
	// ОСТ 24.125.49 в источнике 600 — больше наружного диаметра. Dу лежит
	// между 0,6 прохода и Dн (самый толстостенный случай в таблицах —
	// ГОСТ 22818, 32×8,5 → Dу 10 при проходе 15).
	$od_f = (float) str_replace( ',', '.', (string) ( $raw['outer_diameter'] ?? '' ) );
	$s_f  = (float) str_replace( ',', '.', (string) ( $raw['wall_thickness'] ?? '' ) );
	$bore = ( $od_f > 0 && $s_f > 0 && $s_f < $od_f / 2 ) ? $od_f - 2 * $s_f : 0.0;
	if ( ( $od_f > 0 && (float) $src > $od_f ) || ( $bore > 0 && (float) $src < 0.6 * $bore ) ) {
		$stat[ $family ]['мусор источника']++;
		if ( count( $shown[ $family . ' — отброшено' ] ?? [] ) < 5 ) {
			$shown[ $family . ' — отброшено' ][] = sprintf( '%s: Dн %s×%s, в источнике DN %s', get_post_meta( $id, '_sku', true ), $raw['outer_diameter'] ?? '—', $raw['wall_thickness'] ?? '—', $src );
		}
		continue;
	}
	// Описание товара хранит DN текстом («Угол 45°. DN 200. Наружный диаметр
	// 219 мм») — его писали из того же пересчитанного DN, а у части позиций
	// там третье, мусорное значение («DN 10» у отвода 140×25). В описании
	// ровно одно «DN n» — его и приводим к таблице.
	$content  = (string) get_post_field( 'post_content', $id );
	$fix_text = preg_match_all( '/\bDN\s*(\d+)/u', $content, $dm ) === 1 && $dm[1][0] !== $src;

	$shown_dn  = (string) ( promen_get_dims( $id )['dn'] ?? '' );
	$fix_dims  = ! ( $shown_dn === $src && ! empty( $raw['dn_by_table'] ) && (string) ( $raw['dn'] ?? '' ) === $src );
	if ( ! $fix_dims && ! $fix_text ) {
		continue; // уже исправлено
	}

	if ( $fix_dims ) {
		$key = $shown_dn === $src ? 'только флаг' : 'меняется';
		$stat[ $family ][ $key ]++;
		if ( 'меняется' === $key ) {
			$sku       = (string) get_post_meta( $id, '_sku', true );
			$changes[] = implode( "\t", [ $id, $sku, $r->norm, $raw['outer_diameter'] ?? '', $raw['wall_thickness'] ?? '', $shown_dn, $src ] );
			if ( count( $shown[ $family ] ?? [] ) < 3 ) {
				$shown[ $family ][] = sprintf( '%s: Dн %s×%s — DN %s → %s', $sku, $raw['outer_diameter'] ?? '—', $raw['wall_thickness'] ?? '—', $shown_dn !== '' ? $shown_dn : '—', $src );
			}
		}
	}
	if ( $fix_text ) {
		$stat[ $family ]['описание'] = ( $stat[ $family ]['описание'] ?? 0 ) + 1;
	}

	if ( 'apply' === $mode ) {
		if ( $fix_dims ) {
			$raw['dn']          = $src;
			$raw['dn_by_table'] = '1';
			update_post_meta( $id, '_promen_dims', wp_json_encode( $raw, JSON_UNESCAPED_UNICODE ) );
		}
		if ( $fix_text ) {
			wp_update_post( [ 'ID' => $id, 'post_content' => (string) preg_replace( '/\bDN\s*\d+/u', 'DN ' . $src, $content, 1 ) ] );
		}
		clean_post_cache( $id );
		promen_catalog_upsert( $id, false );
	}
}

// Повторный прогон изменений не находит — прежний список не затираем.
if ( $changes ) {
	sort( $changes );
	file_put_contents( __DIR__ . '/changes.tsv', implode( "\n", $changes ) . "\n" );
}

printf( "Режим: %s\n\n%-22s %6s %9s %12s %16s %10s %9s\n", $mode, 'семейство', 'всего', 'меняется', 'только флаг', 'мусор источника', 'нет pa_dn', 'описание' );
$total = 0;
$texts = 0;
foreach ( $stat as $family => $s ) {
	printf( "%-22s %6d %9d %12d %16d %10d %9d\n", $family, $s['всего'], $s['меняется'], $s['только флаг'], $s['мусор источника'], $s['нет pa_dn'], $s['описание'] ?? 0 );
	$total += $s['меняется'];
	$texts += $s['описание'] ?? 0;
}
printf( "\nDN меняется у %d позиций (changes.tsv), описаний правится: %d\n", $total, $texts );
foreach ( $shown as $family => $lines ) {
	echo "\n", $family, "\n  ", implode( "\n  ", $lines ), "\n";
}
if ( 'apply' === $mode && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
	echo "\nКеш фильтров сброшен.\n";
}
