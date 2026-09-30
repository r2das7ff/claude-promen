<?php
/**
 * Размеры отвода/меньшего конца по таблицам стандартов — 30.09.2026.
 *
 * Поиск «диаметр отвода = DN отвода» нашёл 57 позиций. Разбор по таблицам:
 *
 * ОСТ 24.125.49-89 (тройники штампованные переходные с вытянутой горловиной,
 * таблица на с. 3, скан с meganorm.ru): в Dн отвода записан DN отвода, стенки
 * отвода нет. Исп. 10 записано как «530х28» — по таблице это 630×25 с отводом
 * 530×28, DN 600×500. У исп. 12–13 длина L = 800 и высота H = 323 перепутаны
 * или пропущены, исп. 15 — сталь 20, а не 15ГС. Описание называло все
 * переходные тройники «равнопроходными».
 *
 * ОСТ 34-42-665-84 (переходы сварные листовые, таблицы 2 и 3 — концентрические
 * 01–40 и эксцентрические 41–80, PDF с angd.one): в Dн меньшего конца записан
 * его DN, стенка взята у большего конца; у исп. 19, 29, 31, 77 перепутан и
 * большой конец («820×9» вместо 920×10, «1200×7.5» вместо 1220×11 — 7,5 это
 * S3 из соседней колонки), DN у исп. 19, 31, 77 = 900/1400/15.
 *
 * ОСТ 34-10-764-97, строка 166: 1220×14 с отводом 820×11, в карточке 800.
 *
 * Отводы, колена, фланцы: полей отвода у них нет по определению — туда при
 * импорте попали соседние колонки таблиц. Поля снимаются. У колен
 * СТО СРО-П 60542948.00011 DN записан 40 у всех — ставим по Dн из ряда АЭС
 * (25→20, 32→25, 38→32, 45→40, 57→50, 76→65, 89→80; ряд подтверждён таблицей 1
 * СТО 95 126-2013), у отвода СТО 95 115 25х2 — DN 20, у СТО 79814898.111
 * 220х7 — DN 200.
 *
 * Не трогаем, нужны решения: СТО 95 126-2013 (стандарт на РАВНОПРОХОДНЫЕ
 * тройники, а у нас 17 «переходных» с числом PN/типоразмера вместо отвода),
 * заглушки СТО СРО-П 60542948.00016 исп. 2, серия 4.903-10 (альбома нет),
 * названия фланцев ОСТ 34-10-425-90 («ФП DN22» у фланца 820).
 *
 * Слаги не меняются. Запуск:
 *   wp eval-file _fix_branch_dims_0930.php          — отчёт
 *   wp eval-file _fix_branch_dims_0930.php apply    — применить
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

global $wpdb;

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );

$dims_of = static fn( int $id ): array => json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
$norm_ids = static function ( string $name ): array {
	$t = get_term_by( 'name', $name, 'norm' );
	return $t ? array_map( 'intval', (array) get_objects_in_term( [ $t->term_id ], 'norm' ) ) : [];
};

$plan = []; // id => [ 'set' => [], 'unset' => [], 'title' => ?string, 'content' => ?string, 'note' => string ]

// ── ОСТ 24.125.49-89 ───────────────────────────────────────────────────────
// исп => [ Dy, dy, D, S, d, s1, L, H, масса, сталь ]
$t49 = [
	'01' => [ 125, 100, '133', '8', '108', '8', 400, 95, 29, '15ГС' ],
	'02' => [ 125, 100, '133', '8', '108', '6', 400, 95, 29, '15ГС' ],
	'03' => [ 200, 150, '219', '13', '159', '9', 500, 147, 64, '15ГС' ],
	'05' => [ 400, 300, '426', '24', '325', '19', 800, 310, 364, '15ГС' ],
	'08' => [ 600, 250, '630', '25', '273', '16', 750, 395, 725, '15ГС' ],
	'10' => [ 600, 500, '630', '25', '530', '28', 1100, 440, 980, '15ГС' ],
	'11' => [ 400, 300, '426', '14', '325', '13', 800, 310, 364, '15ГС' ],
	'12' => [ 450, 350, '465', '16', '377', '13', 800, 323, 381, '15ГС' ],
	'13' => [ 450, 400, '465', '16', '426', '14', 800, 323, 385, '15ГС' ],
	'14' => [ 400, 350, '426', '14', '377', '13', 800, 310, 364, '15ГС' ],
	'15' => [ 400, 350, '426', '14', '377', '13', 800, 267, 373, '20' ],
];
foreach ( $norm_ids( 'ОСТ 24.125.49-1989' ) as $id ) {
	$d  = $dims_of( $id );
	$ex = (string) ( $d['execution'] ?? '' );
	if ( ! isset( $t49[ $ex ] ) ) {
		WP_CLI::warning( "24.125.49: исп. «{$ex}» нет в таблице — {$id}" );
		continue;
	}
	list( $dy, $dy1, $D, $S, $d1, $s1, $L, $H, $m, $steel ) = $t49[ $ex ];
	$pn    = (string) ( $d['pn'] ?? $d['pressure_group'] ?? '' );
	$steel_title = '20' === $steel ? 'ст.20' : $steel;
	$plan[ $id ] = [
		'set'     => [
			'outer_diameter' => $D, 'wall_thickness' => $S, 'outer_d_branch' => $d1, 'wall_branch' => $s1,
			'dn' => (string) $dy, 'dn_branch' => (string) $dy1,
			'length' => (string) $L, 'length_mm' => (string) $L, 'height_mm' => (string) $H, 'height_h' => (string) $H,
			'gost_designation' => "{$D}х{$S}-{$d1}х{$s1}", 'material_grade' => $steel,
		],
		'unset'   => [],
		'title'   => "Тройник {$D}х{$S}-{$d1}х{$s1} исп. {$ex} {$steel_title} ОСТ 24.125.49-1989",
		'content' => "Тройник штампованный переходный с вытянутой горловиной по ОСТ 24.125.49-1989. DN {$dy}×{$dy1}. Размеры {$D}х{$S}-{$d1}х{$s1} мм. "
			. ( '' !== $pn ? "PN {$pn} МПа. " : '' ) . "Исполнение {$ex}. Марка стали {$steel_title}. Масса {$m} кг.",
		'note'    => '24.125.49',
	];
}

// ── ОСТ 34-42-665-84 ───────────────────────────────────────────────────────
// исп => [ Dy, dy, D, S, d, s2 ]; 41–80 — эксцентрические с теми же размерами, что 01–40
$t665 = [
	'02' => [ 500, 300, 530, 8, 325, 8 ],    '06' => [ 600, 350, 630, 8, 377, 9 ],
	'10' => [ 700, 400, 720, 8, 426, 9 ],    '14' => [ 800, 500, 820, 9, 530, 8 ],
	'18' => [ 900, 600, 920, 10, 630, 8 ],   '19' => [ 900, 700, 920, 10, 720, 8 ],
	'22' => [ 1000, 600, 1020, 10, 630, 8 ], '24' => [ 1000, 800, 1020, 10, 820, 9 ],
	'27' => [ 1200, 700, 1220, 11, 720, 8 ], '28' => [ 1200, 800, 1220, 11, 820, 9 ],
	'29' => [ 1200, 900, 1220, 11, 920, 10 ], '31' => [ 1400, 700, 1420, 14, 720, 8 ],
	'33' => [ 1400, 900, 1420, 14, 920, 10 ], '36' => [ 1600, 800, 1620, 14, 820, 9 ],
	'37' => [ 1600, 900, 1620, 14, 920, 10 ], '05' => [ 600, 300, 630, 8, 325, 8 ],
];
foreach ( $norm_ids( 'ОСТ 34-42-665-1984' ) as $id ) {
	$d   = $dims_of( $id );
	$ex  = (string) ( $d['execution'] ?? '' );
	$n   = (int) $ex;
	$base = $n > 40 ? $n - 40 : $n;
	$key = sprintf( '%02d', $base );
	if ( ! isset( $t665[ $key ] ) ) {
		WP_CLI::warning( "665: исп. «{$ex}» не разобрано — {$id}" );
		continue;
	}
	list( $dy, $dy1, $D, $S, $d1, $s2 ) = $t665[ $key ];
	$pn   = ( $base <= 25 || ( $base >= 31 && $base <= 34 ) ) ? '1.6' : '1.0';
	$kind = $n > 40 ? 'эксцентрический' : 'концентрический';
	$steel = (string) ( $d['material_grade'] ?? 'ст.20' );
	$plan[ $id ] = [
		'set'     => [
			'outer_diameter' => (string) $D, 'wall_thickness' => (string) $S,
			'outer_d_branch' => (string) $d1, 'wall_branch' => (string) $s2,
			'dn' => (string) $dy, 'dn_branch' => (string) $dy1, 'pn' => $pn,
			'gost_designation' => "{$D}х{$S}-{$d1}х{$s2}",
		],
		'unset'   => [],
		'title'   => "Переход {$D}×{$S}-{$d1}×{$s2} исп. {$ex} ОСТ 34-42-665-1984",
		'content' => "Переход сварной листовой {$kind} по ОСТ 34-42-665-1984. DN {$dy}×{$dy1}. Размеры {$D}×{$S}-{$d1}×{$s2} мм. PN {$pn} МПа. Исполнение {$ex}. Марка стали {$steel}.",
		'note'    => '665',
	];
}

// ── ОСТ 34-10-764-97, строка 166 ───────────────────────────────────────────
$plan[138793] = [
	'set'   => [ 'outer_d_branch' => '820', 'gost_designation' => '1220х14-820х11' ],
	'unset' => [], 'title' => null, 'content' => null, 'note' => '764',
];

// ── Отводы, колена, фланцы: полей отвода быть не должно ────────────────────
$ae_dn = [ '25' => '20', '32' => '25', '38' => '32', '45' => '40', '57' => '50', '76' => '65', '89' => '80' ];
$no_branch = [
	'СТО 79814898.111-2009'          => [ 98493 => '200' ],
	'СТО 95 115-2013'                => [ 97759 => '20' ],
	'СТО СРО-П 60542948.00011-2013'  => 'ae',
	'ОСТ 34-10-425-90'               => [],
];
foreach ( $no_branch as $norm => $dn_fix ) {
	foreach ( $norm_ids( $norm ) as $id ) {
		$d   = $dims_of( $id );
		$has = array_intersect( [ 'outer_d_branch', 'dn_branch', 'wall_branch' ], array_keys( $d ) );
		$set = [];
		if ( 'ae' === $dn_fix ) {
			$od = (string) ( $d['outer_diameter'] ?? '' );
			if ( isset( $ae_dn[ $od ] ) && (string) ( $d['dn'] ?? '' ) !== $ae_dn[ $od ] ) {
				$set['dn'] = $ae_dn[ $od ];
			}
		} elseif ( isset( $dn_fix[ $id ] ) && (string) ( $d['dn'] ?? '' ) !== $dn_fix[ $id ] ) {
			$set['dn'] = $dn_fix[ $id ];
		}
		if ( ! $has && ! $set ) {
			continue;
		}
		$plan[ $id ] = [ 'set' => $set, 'unset' => array_values( $has ), 'title' => null, 'content' => null, 'note' => $norm ];
	}
}

// ── Отчёт ──────────────────────────────────────────────────────────────────
$by_note = [];
foreach ( $plan as $id => $p ) {
	$by_note[ $p['note'] ][] = $id;
}
foreach ( $by_note as $note => $ids ) {
	WP_CLI::log( "## {$note}: " . count( $ids ) );
	foreach ( $ids as $id ) {
		$p   = $plan[ $id ];
		$old = $dims_of( $id );
		$chg = [];
		foreach ( $p['set'] as $k => $v ) {
			if ( (string) ( $old[ $k ] ?? '' ) !== (string) $v ) {
				$chg[] = "{$k} " . ( $old[ $k ] ?? '—' ) . "→{$v}";
			}
		}
		foreach ( $p['unset'] as $k ) {
			$chg[] = "−{$k}(" . $old[ $k ] . ')';
		}
		$t_old = get_the_title( $id );
		$t     = ( null !== $p['title'] && $p['title'] !== $t_old ) ? "  «{$t_old}» → «{$p['title']}»" : '';
		WP_CLI::log( "  {$id} " . implode( ', ', $chg ) . $t );
	}
}

if ( ! $apply ) {
	WP_CLI::log( 'Отчёт. Для правки: ... eval-file _fix_branch_dims_0930.php apply' );
	return;
}

// Слепок: строки постов (с вариациями) и мета.
$ids = array_keys( $plan );
foreach ( array_keys( $plan ) as $id ) {
	$ids = array_merge( $ids, array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'product_variation'", $id ) ) ) );
}
$in   = implode( ',', array_map( 'intval', $ids ) );
$snap = rtrim( (string) getenv( 'HOME' ), '/' ) . '/branch-dims-0930-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $snap, wp_json_encode( [
	'posts' => $wpdb->get_results( "SELECT ID, post_title, post_excerpt, post_content FROM {$wpdb->posts} WHERE ID IN ({$in})", ARRAY_A ),
	'meta'  => $wpdb->get_results( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id IN ({$in}) AND meta_key IN ('_promen_dims','_promen_gost_designation')", ARRAY_A ),
], JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок: ' . $snap );

$done = 0;
foreach ( $plan as $id => $p ) {
	$d = $dims_of( $id );
	foreach ( $p['set'] as $k => $v ) {
		$d[ $k ] = $v;
	}
	foreach ( $p['unset'] as $k ) {
		unset( $d[ $k ] );
	}
	update_post_meta( $id, '_promen_dims', wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
	if ( isset( $p['set']['gost_designation'] ) ) {
		update_post_meta( $id, '_promen_gost_designation', $p['set']['gost_designation'] );
	}

	$fields = [];
	$t_old  = (string) get_post_field( 'post_title', $id );
	if ( null !== $p['title'] && $p['title'] !== $t_old ) {
		$fields['post_title'] = $p['title'];
		// Вариации несут название родителя в начале своего.
		foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'product_variation'", $id ) ) as $v ) {
			if ( 0 === strpos( $v->post_title, $t_old ) ) {
				$wpdb->update( $wpdb->posts, [ 'post_title' => $p['title'] . substr( $v->post_title, strlen( $t_old ) ) ], [ 'ID' => (int) $v->ID ] );
				clean_post_cache( (int) $v->ID );
			}
		}
	}
	if ( null !== $p['content'] ) {
		$fields['post_content'] = $p['content'];
	} elseif ( $p['unset'] || isset( $p['set']['dn'] ) ) {
		// Описание собиралось из тех же полей: убираем «DN отвода N» / «DN1 N», правим DN.
		$c = (string) get_post_field( 'post_content', $id );
		$n = preg_replace( '/\s*(?:DN отвода|DN1) [\d.,]+\./u', '', $c );
		if ( isset( $p['set']['dn'] ) ) {
			$n = preg_replace( '/\bDN [\d.,]+\./u', 'DN ' . $p['set']['dn'] . '.', $n, 1 );
		}
		if ( $n !== $c ) {
			$fields['post_content'] = $n;
		}
	}
	if ( $fields ) {
		// Прямой UPDATE: wp_update_post пересобрал бы слаг и дёрнул save_post у Woo.
		$wpdb->update( $wpdb->posts, $fields, [ 'ID' => $id ] );
	}
	clean_post_cache( $id );
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $id, false );
	}
	$done++;
}

wp_cache_flush();
if ( function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
WP_CLI::success( "Исправлено позиций: {$done}. Дальше: wp promen cache-purge." );
