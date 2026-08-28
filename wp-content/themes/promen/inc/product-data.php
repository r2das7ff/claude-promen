<?php
/**
 * Данные карточки товара: размерный ряд («братья»), вариации, крошки.
 */

defined( 'ABSPATH' ) || exit;

/** Габариты товара из json-меты _promen_dims. */
function promen_get_dims( int $product_id ): array {
	$json = get_post_meta( $product_id, '_promen_dims', true );
	$dims = $json ? json_decode( $json, true ) : [];
	if ( ! is_array( $dims ) ) {
		return [];
	}
	$turned = promen_product_is_turned_reducer( $product_id, $dims );
	$dims   = promen_sanitize_dims( $dims, [ 'turned_reducer' => $turned ] );
	if ( $turned ) {
		$sku  = (string) get_post_meta( $product_id, '_sku', true );
		$dims = promen_enrich_turned_reducer_dims( $dims, $sku );
	}
	if ( promen_product_needs_both_pipe_ends( $product_id ) ) {
		$dims = promen_ensure_both_pipe_ends( $dims );
	}
	return $dims;
}

/**
 * Точёные переходы (ПТ): в стандарте D/D1/d/d1, стенка = (D−d)/2, d≈DN.
 */
function promen_product_is_turned_reducer( int $product_id, array $dims = [] ): bool {
	$tech = (string) ( $dims['technology'] ?? '' );
	if ( preg_match( '/точ[её]н/ui', $tech ) ) {
		return true;
	}
	$slugs = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'slugs' ] );
	if ( ! is_wp_error( $slugs ) && in_array( 'tochenye', $slugs, true ) ) {
		return true;
	}
	$sku = (string) get_post_meta( $product_id, '_sku', true );
	return (bool) preg_match( '/-пт-/ui', $sku );
}

/**
 * Восстанавливает DN из dy/SKU. Стенки точёных НЕ выводим из (D−DN)/2:
 * в ГОСТ 22826-83 d часто ≠ Dy, верный s = (D−d)/2 только при известном d.
 *
 * @param array<string, mixed> $dims
 * @return array<string, mixed>
 */
function promen_enrich_turned_reducer_dims( array $dims, string $sku = '' ): array {
	if ( preg_match( '/пт-(\d+(?:[.,]\d+)?)[xх×](\d+(?:[.,]\d+)?)-/ui', $sku, $m ) ) {
		$dims['dn']        = str_replace( ',', '.', $m[1] );
		$dims['dn_branch'] = str_replace( ',', '.', $m[2] );
		$dims['dy']        = $dims['dn'];
		$dims['dy1']       = $dims['dn_branch'];
	} else {
		$dy  = trim( (string) ( $dims['dy'] ?? '' ) );
		$dy1 = trim( (string) ( $dims['dy1'] ?? '' ) );
		if ( $dy !== '' ) {
			$dims['dn'] = $dy;
		}
		if ( $dy1 !== '' ) {
			$dims['dn_branch'] = $dy1;
		}
	}

	$od  = trim( (string) ( $dims['outer_diameter'] ?? '' ) );
	$od2 = trim( (string) ( $dims['outer_d_branch'] ?? '' ) );
	$s   = trim( (string) ( $dims['wall_thickness'] ?? '' ) );
	$s2  = trim( (string) ( $dims['wall_branch'] ?? '' ) );
	// Внутренние диаметры торцов (d / d1 из таблицы ГОСТ) — единственный надёжный источник s.
	$bore  = trim( (string) ( $dims['inner_diameter'] ?? ( $dims['bore'] ?? '' ) ) );
	$bore2 = trim( (string) ( $dims['inner_d_branch'] ?? ( $dims['bore_branch'] ?? '' ) ) );

	if ( $s === '' && $bore !== '' ) {
		$w = promen_turned_wall_from_bore( $od, $bore );
		if ( $w !== '' ) {
			$dims['wall_thickness'] = $w;
		}
	}
	if ( $s2 === '' && $bore2 !== '' ) {
		$w2 = promen_turned_wall_from_bore( $od2, $bore2 );
		if ( $w2 !== '' ) {
			$dims['wall_branch'] = $w2;
		}
	}
	return $dims;
}

/** s = (Dн − d)/2 для точёных; пусто, если не считается. */
function promen_turned_wall_from_bore( string $od, string $bore ): string {
	$od   = str_replace( ',', '.', trim( $od ) );
	$bore = str_replace( ',', '.', trim( $bore ) );
	if ( $od === '' || $bore === '' || ! is_numeric( $od ) || ! is_numeric( $bore ) ) {
		return '';
	}
	$od_f   = (float) $od;
	$bore_f = (float) $bore;
	if ( $bore_f <= 0 || $od_f <= $bore_f ) {
		return '';
	}
	$w = ( $od_f - $bore_f ) / 2.0;
	if ( $w < 0.5 ) {
		return '';
	}
	if ( abs( $w - round( $w ) ) < 1e-6 ) {
		return (string) (int) round( $w );
	}
	$formatted = number_format( $w, 2, '.', '' );
	return rtrim( rtrim( $formatted, '0' ), '.' );
}

/**
 * Переходы / точёные: всегда два конца (D×s и D2×s2).
 * Равнопроходной — дублируем один известный торец.
 */
function promen_product_needs_both_pipe_ends( int $product_id ): bool {
	$slugs = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'slugs' ] );
	if ( is_wp_error( $slugs ) || ! $slugs ) {
		$family = (string) get_post_meta( $product_id, '_promen_family', true );
		return (bool) preg_match( '/переход|точён|точен/ui', $family );
	}
	return (bool) array_intersect( $slugs, [ 'perekhody', 'tochenye' ] );
}

/**
 * Дополняет второй торец перехода: пустой → копия первого; недостающую стенку зеркалит.
 */
function promen_ensure_both_pipe_ends( array $dims ): array {
	$od  = trim( (string) ( $dims['outer_diameter'] ?? '' ) );
	$s   = trim( (string) ( $dims['wall_thickness'] ?? '' ) );
	$od2 = trim( (string) ( $dims['outer_d_branch'] ?? '' ) );
	$s2  = trim( (string) ( $dims['wall_branch'] ?? '' ) );
	$dn  = trim( (string) ( $dims['dn'] ?? ( $dims['dy'] ?? '' ) ) );
	$dn2 = trim( (string) ( $dims['dn_branch'] ?? ( $dims['dy1'] ?? '' ) ) );

	// Только второй торец — переносим на основной.
	if ( $od === '' && $od2 !== '' ) {
		$dims['outer_diameter'] = $od2;
		$od                     = $od2;
		if ( $s === '' && $s2 !== '' ) {
			$dims['wall_thickness'] = $s2;
			$s                      = $s2;
		}
	}

	// Нет второго торца (равнопроходной / недозаполнено) — дублируем первый.
	if ( $od !== '' && $od2 === '' ) {
		$dims['outer_d_branch'] = $od;
		$od2                    = $od;
		if ( $s !== '' && $s2 === '' ) {
			$dims['wall_branch'] = $s;
			$s2                  = $s;
		}
		if ( $dn !== '' && ( $dn2 === '' || $dn2 === $dn ) ) {
			$dims['dn_branch'] = $dn;
			$dn2               = $dn;
		}
		$dims['equal_pass'] = 'да';
	}

	// Оба OD есть, но стенка только с одной стороны — зеркалим.
	if ( $od !== '' && $od2 !== '' ) {
		if ( $s === '' && $s2 !== '' ) {
			$dims['wall_thickness'] = $s2;
			$s                      = $s2;
		}
		if ( $s2 === '' && $s !== '' ) {
			$dims['wall_branch'] = $s;
			$s2                  = $s;
		}
	}

	// DN2 пуст при равных OD — тоже дублируем.
	if ( $od !== '' && $od2 !== '' && $od === $od2 && $dn !== '' && $dn2 === '' ) {
		$dims['dn_branch'] = $dn;
	}

	return $dims;
}

/**
 * Убирает «DN = номер исполнения» (бобышки ОСТ 24.125.57-89: dn=01…09 = execution).
 * Реальный размер остаётся в outer_diameter / wall_thickness.
 * Также чинит DN, ошибочно взятый из допуска (+0,63) или номера фигуры (2–5).
 *
 * @param array<string, mixed>         $dims
 * @param array{turned_reducer?: bool} $opts
 * @return array<string, mixed>
 */
function promen_sanitize_dims( array $dims, array $opts = [] ): array {
	$turned = ! empty( $opts['turned_reducer'] )
		|| (bool) preg_match( '/точ[её]н/ui', (string) ( $dims['technology'] ?? '' ) );

	$dn = trim( (string) ( $dims['dn'] ?? '' ) );
	$ex = trim( (string) ( $dims['execution'] ?? '' ) );
	$od = trim( (string) ( $dims['outer_diameter'] ?? '' ) );

	// Мусорный dy (13/19/60…) не промоутим в dn при наличии OD —
	// для точёных OD→DN может быть пуст, но dy всё равно ненадёжен.
	if ( $dn === '' && ( $od === '' || $turned ) ) {
		$dy = trim( (string) ( $dims['dy'] ?? '' ) );
		if ( $dy !== '' && ! promen_dn_looks_junk( $dy ) && ( $turned || promen_dn_is_standard( $dy ) ) ) {
			$dn         = $dy;
			$dims['dn'] = $dy;
		}
	}

	// Трубный DN из OD — только для сварных/штампованных. У точёных D≠труба,
	// а DN = внутренний проход (d); подмена ломает стенки (D−DN)/2.
	// У фланцев outer_diameter = D фланца, не трубы — не выводить DN из него.
	$is_flange = promen_dims_look_like_flange( $dims );
	if ( $od !== '' && ! $turned && ! $is_flange ) {
		$inferred = promen_pipe_dn_from_od( $od );
		if ( $inferred !== '' ) {
			$dims['dn'] = $inferred;
			$dn         = $inferred;
		}
	}

	if ( $dn !== '' && $ex !== '' && $dn === $ex && preg_match( '/^0?[1-9]$/', $dn ) && $od !== '' ) {
		unset( $dims['dn'] );
		$dn = '';
	}
	// Мусор n: stud_count совпал с наружным диаметром.
	$n  = trim( (string) ( $dims['stud_count'] ?? '' ) );
	if ( $n !== '' && $od !== '' && $n === $od ) {
		unset( $dims['stud_count'] );
	}

	if ( $od !== '' && ! $turned && ! $is_flange ) {
		$inferred = promen_pipe_dn_from_od( $od );
		$dn_junk  = promen_dn_looks_junk( $dn );
		if ( ! $dn_junk && $inferred !== '' && $dn !== '' && is_numeric( str_replace( ',', '.', $dn ) ) ) {
			// Масса/служебное число вместо DN (2196 при OD 820 → DN 800).
			$dn_val = (float) str_replace( ',', '.', $dn );
			$inf_val = (float) $inferred;
			if ( abs( $dn_val - $inf_val ) >= 1 && ( $dn_val > 1600 || $dn_val > $inf_val * 2 ) ) {
				$dn_junk = true;
			}
		}
		if ( $dn_junk ) {
			if ( $inferred !== '' ) {
				$dims['dn'] = $inferred;
				$dn         = $inferred;
			} elseif ( $dn !== '' ) {
				unset( $dims['dn'] );
				$dn = '';
			}
		}
	}

	$od_branch = trim( (string) ( $dims['outer_d_branch'] ?? '' ) );
	$dn_branch = trim( (string) ( $dims['dn_branch'] ?? '' ) );
	// dy1 — для точёных это реальный DN2; иначе только если dn_branch пуст и нет OD2.
	if ( $dn_branch === '' && ( $od_branch === '' || $turned ) ) {
		$dy1 = trim( (string) ( $dims['dy1'] ?? '' ) );
		if ( $dy1 !== '' && ! promen_dn_looks_junk( $dy1 ) && ( $turned || promen_dn_is_standard( $dy1 ) ) ) {
			$dn_branch         = $dy1;
			$dims['dn_branch'] = $dy1;
		}
	} elseif ( $dn_branch === '' && $od_branch !== '' ) {
		// dn_branch пуст, но OD2 есть — не тащим dy1.
		$dn_branch = '';
	}

	// НЕ промоутим dy в dn здесь: это уже сделано выше с проверкой standard.
	// Блок ниже был источником «Переходы 10/20» и dn=13/19/60.

	// DN2 ошибочно записан в outer_d_branch (пустой OD → витрина «Переходы 10»).
	if ( $od === '' && $od_branch !== '' && $dn_branch !== ''
		&& is_numeric( str_replace( ',', '.', $od_branch ) )
		&& is_numeric( str_replace( ',', '.', $dn_branch ) )
		&& abs( (float) str_replace( ',', '.', $od_branch ) - (float) str_replace( ',', '.', $dn_branch ) ) < 1e-6
	) {
		unset( $dims['outer_d_branch'] );
		$od_branch = '';
	}

	if ( $od_branch !== '' && ! $turned ) {
		$inferred_branch = promen_pipe_dn_from_od( $od_branch );
		if ( $inferred_branch !== '' && ( $dn_branch === '' || promen_dn_looks_junk( $dn_branch ) || ( is_numeric( str_replace( ',', '.', $dn_branch ) ) && abs( (float) str_replace( ',', '.', $dn_branch ) - (float) $inferred_branch ) >= 1 && (float) str_replace( ',', '.', $dn_branch ) > 1600 ) ) ) {
			$dims['dn_branch'] = $inferred_branch;
			$dn_branch         = $inferred_branch;
		} elseif ( $dn_branch !== '' && $inferred_branch !== '' && is_numeric( str_replace( ',', '.', $dn_branch ) ) && abs( (float) str_replace( ',', '.', $dn_branch ) - (float) $od_branch ) < 1e-6 ) {
			// DN2 ошибочно = Dн2 (133 вместо 125).
			$dims['dn_branch'] = $inferred_branch;
			$dn_branch         = $inferred_branch;
		}
	}

	// Обогащение OD из DN, если в мете только условные проходы.
	if ( ! $turned && $od === '' && $dn !== '' && ! promen_dn_looks_junk( $dn ) ) {
		$filled = promen_pipe_od_from_dn( $dn );
		if ( $filled !== '' ) {
			$dims['outer_diameter'] = $filled;
			$od                     = $filled;
		}
	}
	if ( ! $turned && $od_branch === '' && $dn_branch !== '' && ! promen_dn_looks_junk( $dn_branch ) ) {
		$filled_branch = promen_pipe_od_from_dn( $dn_branch );
		if ( $filled_branch !== '' ) {
			$dims['outer_d_branch'] = $filled_branch;
			$od_branch             = $filled_branch;
		}
	}

	$angle = trim( (string) ( $dims['angle'] ?? '' ) );
	if ( $angle !== '' && ! promen_angle_is_plausible( $angle ) ) {
		unset( $dims['angle'] );
	}

	// Физически невозможная стенка (s >= D/2 → внутренний Ø <= 0): не выводим
	// геометрию, чтобы не врать (гейт правдивости на показе/индексации).
	foreach ( [ [ 'outer_diameter', 'wall_thickness' ], [ 'outer_d_branch', 'wall_branch' ] ] as $pair ) {
		$pd = (float) ( $dims[ $pair[0] ] ?? 0 );
		$ps = (float) ( $dims[ $pair[1] ] ?? 0 );
		if ( $pd > 0 && $ps > 0 && $ps >= $pd / 2 ) {
			unset( $dims[ $pair[0] ], $dims[ $pair[1] ] );
		}
	}

	return $dims;
}

/** DN из допуска/фигуры/мусора (0.63, 4, …) — не условный проход. */
function promen_dn_looks_junk( string $dn ): bool {
	if ( $dn === '' ) {
		return true;
	}
	$normalized = str_replace( ',', '.', $dn );
	if ( ! is_numeric( $normalized ) ) {
		return false;
	}
	$value = (float) $normalized;
	if ( $value < 6 ) {
		return true;
	}
	// Дробный DN почти всегда допуск (+0,63), а не Dy.
	if ( abs( $value - round( $value ) ) > 1e-6 ) {
		return true;
	}
	return false;
}

function promen_angle_is_plausible( string $angle ): bool {
	$normalized = str_replace( ',', '.', $angle );
	if ( ! is_numeric( $normalized ) ) {
		return false;
	}
	$value = (float) $normalized;
	return in_array( $value, [ 15.0, 30.0, 45.0, 60.0, 90.0, 180.0 ], true );
}

/** Условный проход по наружному диаметру трубы (мм). */
function promen_pipe_dn_from_od( string $od ): string {
	$raw = str_replace( ',', '.', trim( $od ) );
	if ( $raw === '' ) {
		return '';
	}
	// Целый OD (160, 180, 219) — нельзя rtrim('0'), иначе 160→16→DN15.
	if ( preg_match( '/^\d+$/', $raw ) ) {
		$normalized = $raw;
	} else {
		$normalized = rtrim( rtrim( $raw, '0' ), '.' );
	}
	if ( $normalized === '' ) {
		return '';
	}
	static $map = [
		'10.2'  => '10',
		'13.5'  => '10',
		'14'    => '10',
		'16'    => '15',
		'17'    => '10',
		'17.2'  => '15',
		'18'    => '15',
		'21.3'  => '20',
		'22'    => '20',
		'25'    => '20',
		'26.7'  => '25',
		'26.9'  => '25',
		'27'    => '25',
		'32'    => '25',
		'33.4'  => '32',
		'33.7'  => '32',
		'38'    => '32',
		'42.2'  => '40',
		'42.4'  => '40',
		'45'    => '40',
		'48.3'  => '40',
		'57'    => '50',
		'60.3'  => '50',
		'76'    => '65',
		'76.1'  => '65',
		'88.9'  => '80',
		'89'    => '80',
		'101.6' => '80',
		'108'   => '100',
		'114'   => '100',
		'114.3' => '100',
		'133'   => '125',
		'139.7' => '125',
		'159'   => '150',
		'168'   => '150',
		'168.3' => '150',
		'219'   => '200',
		'219.1' => '200',
		'245'   => '150',
		'273'   => '250',
		'299'   => '300',
		'323.9' => '300',
		'325'   => '300',
		'351'   => '350',
		'355.6' => '350',
		'377'   => '350',
		'402'   => '400',
		'406.4' => '400',
		'426'   => '400',
		'457'   => '450',
		'465'   => '350',
		'480'   => '450',
		'530'   => '500',
		'630'   => '600',
		'720'   => '700',
		'820'   => '800',
		'920'   => '900',
		'1000'  => '1000',
		'1020'  => '1000',
		'1220'  => '1200',
		'1420'  => '1400',
		'1620'  => '1600',
	];
	if ( isset( $map[ $normalized ] ) ) {
		return $map[ $normalized ];
	}
	// Exact float key fallback (159.0 → 159). Целые не трогаем rtrim('0').
	if ( preg_match( '/^\d+$/', $normalized ) ) {
		return $map[ $normalized ] ?? '';
	}
	$key = (string) (float) $normalized;
	$key = rtrim( rtrim( $key, '0' ), '.' );
	return $map[ $key ] ?? $map[ $normalized ] ?? '';
}

/** Наружный диаметр трубы по DN (обратная к promen_pipe_dn_from_od). */
function promen_pipe_od_from_dn( string $dn ): string {
	$raw = str_replace( ',', '.', trim( $dn ) );
	if ( $raw === '' || ! is_numeric( $raw ) ) {
		return '';
	}
	// Целый DN (10, 20, 100, 1200) — нельзя rtrim('0'), иначе 10→1, 1200→12.
	if ( preg_match( '/^\d+$/', $raw ) ) {
		$key = $raw;
	} else {
		$key = rtrim( rtrim( $raw, '0' ), '.' );
	}
	static $map = [
		'6'    => '10.2',
		'8'    => '13.5',
		'10'   => '17.2',
		'15'   => '21.3',
		'20'   => '26.9',
		'25'   => '33.7',
		'32'   => '42.4',
		'40'   => '48.3',
		'50'   => '60.3',
		'65'   => '76.1',
		'80'   => '88.9',
		'100'  => '114.3',
		'125'  => '139.7',
		'150'  => '168.3',
		'200'  => '219.1',
		'250'  => '273',
		'300'  => '325',
		'350'  => '377',
		'400'  => '426',
		'450'  => '480',
		'500'  => '530',
		'600'  => '630',
		'700'  => '720',
		'800'  => '820',
		'900'  => '920',
		'1000' => '1020',
		'1200' => '1220',
		'1400' => '1420',
		'1600' => '1620',
	];
	$int_key = (string) (int) (float) $raw;
	return $map[ $key ] ?? $map[ $int_key ] ?? '';
}

/** Норматив для витрины: meta, иначе разбор title / designation. */
/**
 * Норматив к каноническому русскому виду: латинские слаги (sto-95-127) →
 * «СТО 95 127-2013», пробелы → точка. Пользователь: «на русском, не sto».
 */
function promen_norm_canonical( string $key ): string {
	$key = trim( $key );
	if ( $key === '' || strcasecmp( $key, 'k' ) === 0 ) {
		return '';
	}
	static $slug_map = [
		// СТО ЦКТИ (НПО ЦКТИ): префикс обязателен, серии 318.xx и 321.xx — 2009 г.
		'sto-318-01'            => 'СТО ЦКТИ 318.01-2009',
		'sto-321-01'            => 'СТО ЦКТИ 321.01-2009',
		'sto-321-02'            => 'СТО ЦКТИ 321.02-2009',
		'sto-321-03'            => 'СТО ЦКТИ 321.03-2009',
		'sto-321-04'            => 'СТО ЦКТИ 321.04-2009',
		'sto-321-05'            => 'СТО ЦКТИ 321.05-2009',
		// СТО 95 (Росатом): номер отделяется пробелом
		'sto-95-115'            => 'СТО 95 115-2013',
		'sto-95-115-2013'       => 'СТО 95 115-2013',
		'sto-95-119'            => 'СТО 95 119-2013',
		'sto-95-119-2013'       => 'СТО 95 119-2013',
		'sto-95-126'            => 'СТО 95 126-2013',
		'sto-95-126-2013'       => 'СТО 95 126-2013',
		'sto-95-127'            => 'СТО 95 127-2013',
		'sto-95-127-2013'       => 'СТО 95 127-2013',
		// СТО 79814898: номер отделяется точкой
		'sto-79814898-110-2009' => 'СТО 79814898.110-2009',
		'sto-79814898-111'      => 'СТО 79814898.111-2009',
		'sto-79814898-111-2009' => 'СТО 79814898.111-2009',
		'sto-79814898-113-2009' => 'СТО 79814898.113-2009',
		'sto-79814898-115-2009' => 'СТО 79814898.115-2009',
		'sto-79814898-125'      => 'СТО 79814898.125-2009',
		// типовые серии
		'seriya-4-903-10'       => 'СЕРИЯ 4.903-10',
	];
	$low = strtolower( $key );
	if ( isset( $slug_map[ $low ] ) ) {
		return $slug_map[ $low ];
	}
	// Общий случай латинского слага → русский префикс (год, если неизвестен, опускаем).
	if ( preg_match( '/^(sto|ost|gost|tu|seriya)-(.+)$/i', $key, $m ) ) {
		$pref = [ 'sto' => 'СТО', 'ost' => 'ОСТ', 'gost' => 'ГОСТ', 'tu' => 'ТУ', 'seriya' => 'СЕРИЯ' ][ strtolower( $m[1] ) ];
		$body = str_replace( '-', '.', $m[2] );
		// Год отделяется дефисом, а не точкой: gost-17375-2001 -> ГОСТ 17375-2001.
		// Только четырёхзначный: в «СТО ЦКТИ 321.05» хвост — часть номера, а не год.
		$body = preg_replace( '/\.((?:19|20)\d{2})$/', '-$1', $body );
		// У ГОСТ и ОСТ до 2000 г. год двузначный — как в названиях терминов и товаров.
		if ( 'ГОСТ' === $pref || 'ОСТ' === $pref ) {
			$body = preg_replace( '/-19(\d{2})$/', '-$1', $body );
		}
		return $pref . ' ' . $body;
	}
	return $key;
}

function promen_product_norm_key( int $product_id ): string {
	$nk = trim( (string) get_post_meta( $product_id, '_promen_norm_key', true ) );
	if ( $nk !== '' && strcasecmp( $nk, 'k' ) !== 0 ) {
		return promen_norm_canonical( $nk );
	}
	$title = (string) get_the_title( $product_id );
	$gost  = trim( (string) get_post_meta( $product_id, '_promen_gost_designation', true ) );
	foreach ( [ $gost, $title ] as $text ) {
		if ( $text === '' ) {
			continue;
		}
		if ( preg_match( '/((?:ГОСТ|ОСТ|СТО|ТУ)\s*(?:СРО-П\s*)?[^\s,]{3,}(?:-\d{2,4})?)/ui', $text, $m ) ) {
			return promen_norm_canonical( preg_replace( '/\s+/u', ' ', trim( $m[1] ) ) );
		}
	}
	return promen_norm_canonical( $nk );
}

/**
 * Колонки характеристик реестра каталога — СВОИ под каждую категорию/подкатегорию.
 * Возвращает средние колонки (между «Наименование» и «Материал»); ведущие
 * (Норматив, Наименование) и хвостовые (Материал, стрелка) добавляет шаблон.
 * Каждая колонка: [ 'key' => …, 'label' => …, 'w' => grid-ширина ].
 * key рендерится в archive-product.php (switch по ключу).
 */
function promen_catalog_columns( string $group ): array {
	if ( function_exists( 'promen_catalog_schema_columns' ) ) {
		return promen_catalog_schema_columns( $group );
	}
	// Каждый диаметр и стенка — ОТДЕЛЬНАЯ колонка (как у конкурентов).
	$DN    = [ 'key' => 'dn',     'label' => 'DN',          'w' => '52px' ];
	$D     = [ 'key' => 'd',      'label' => 'Dн, мм',      'w' => '64px' ];
	$S     = [ 'key' => 's',      'label' => 's, мм',       'w' => '54px' ];
	$ANGLE = [ 'key' => 'angle',  'label' => 'Угол',        'w' => '54px' ];
	$RAD   = [ 'key' => 'radius', 'label' => 'R, мм',       'w' => '56px' ];
	$HEIGHT= [ 'key' => 'height', 'label' => 'H, мм',       'w' => '56px' ];
	$MASS  = [ 'key' => 'mass',   'label' => 'Масса, кг',   'w' => '78px' ];
	$MASSM = [ 'key' => 'mass',   'label' => 'Масса, кг/м', 'w' => '100px' ];
	$PN    = [ 'key' => 'pn',     'label' => 'PN',          'w' => '52px' ];
	$TYPE  = [ 'key' => 'flange_type', 'label' => 'Тип',    'w' => '48px' ];
	$B     = [ 'key' => 'b',      'label' => 'b, мм',       'w' => '54px' ];
	$EXEC  = [ 'key' => 'exec',   'label' => 'Исп.',        'w' => '52px' ];

	// Двухдиаметровые (тройник/переход/точёные): 1-й конец Dн/s + 2-й конец Dн2/s2.
	$branch = [
		$DN,
		[ 'key' => 'd',    'label' => 'Dн, мм',  'w' => '60px' ],
		[ 'key' => 's',    'label' => 's, мм',   'w' => '50px' ],
		[ 'key' => 'dn2',  'label' => 'DN2',     'w' => '52px' ],
		[ 'key' => 'd2',   'label' => 'Dн2, мм', 'w' => '62px' ],
		[ 'key' => 's2',   'label' => 's2, мм',  'w' => '54px' ],
		$MASS,
	];
	// Крепёж — резьба/длина/класс.
	$fast = [
		[ 'key' => 'thread',   'label' => 'M',      'w' => '58px' ],
		[ 'key' => 'length',   'label' => 'L, мм',  'w' => '64px' ],
		[ 'key' => 'strength', 'label' => 'Класс',  'w' => '64px' ],
	];

	$g = (string) $group;
	if ( strpos( $g, 'flancy' ) === 0 )   return [ $DN, $PN, $TYPE, $D, $B, $MASS ];
	if ( strpos( $g, 'truby' ) === 0 )    return [ $DN, $D, $S, $MASSM ];
	if ( strpos( $g, 'opory' ) === 0 )    return [ $DN, $MASS ];
	if ( strpos( $g, 'armatura' ) === 0 ) return [ $DN, $PN, $MASS ];
	if ( promen_is_fastener_group( $g ) ) return $fast;

	switch ( $g ) {
		case 'otvody':     return [ $DN, $D, $S, $ANGLE, $RAD, $MASS ];
		case 'dnishcha':   return [ $DN, $D, $S, $HEIGHT, $MASS ];
		case 'zaglushki':  return [ $EXEC, $D, $S, $MASS ];
		case 'izolyatsiya':return [ $DN, $D, $S ];
		case 'troyniki':
		case 'perekhody':
		case 'tochenye':   return $branch;
		default:           return [ $DN, $D, $S, $MASS ]; // '', sdt и прочее
	}
}

/** Значение одной колонки реестра по ключу (см. promen_catalog_columns). */
function promen_catalog_cell( string $key, array $dims, array $fast, int $pid ): string {
	$g = static function ( string $k ) use ( $dims ): string {
		return trim( (string) ( $dims[ $k ] ?? '' ) );
	};
	switch ( $key ) {
		case 'dn':
			$v = $g( 'dn' );
			return $v !== '' ? $v : '—';
		case 'dn2':    $v = $g( 'dn_branch' ) ?: $g( 'dy1' ); return $v !== '' ? $v : '—';
		case 'd':      return $g( 'outer_diameter' ) !== '' ? promen_fmt_dim( $g( 'outer_diameter' ) ) : '—';
		case 's':      return $g( 'wall_thickness' ) !== '' ? promen_fmt_dim( $g( 'wall_thickness' ) ) : '—';
		case 'd2':     return $g( 'outer_d_branch' ) !== '' ? promen_fmt_dim( $g( 'outer_d_branch' ) ) : '—';
		case 's2':     return $g( 'wall_branch' ) !== '' ? promen_fmt_dim( $g( 'wall_branch' ) ) : '—';
		case 'b':
			$v = $g( 'flange_thickness' ) ?: $g( 'b' );
			return $v !== '' ? promen_fmt_dim( $v ) : '—';
		case 'flange_type':
			return promen_flange_type_label( $g( 'flange_type' ) ?: $g( 'product_type' ) );
		case 'dbolt':
			return $g( 'bolt_circle_d' ) !== '' ? promen_fmt_dim( $g( 'bolt_circle_d' ) ) : '—';
		case 'bolts':
			$n = $g( 'stud_count' ); $m = $g( 'bolt_d' );
			if ( $n !== '' && $m !== '' ) return $n . '×M' . promen_fmt_dim( $m );
			if ( $n !== '' ) return $n . ' шт';
			return '—';
		case 'exec':
			$v = $g( 'execution' );
			return $v !== '' ? $v : '—';
		case 'dxs':
			$d = $g( 'outer_diameter' ); $s = $g( 'wall_thickness' );
			if ( $d !== '' && $s !== '' ) return promen_fmt_dim( $d ) . '×' . promen_fmt_dim( $s );
			if ( $d !== '' ) return 'Ø' . promen_fmt_dim( $d );
			return '—';
		case 'angle':  return $g( 'angle' ) !== '' ? promen_fmt_dim( $g( 'angle' ) ) . '°' : '—';
		case 'radius': return $g( 'radius' ) !== '' ? promen_fmt_dim( $g( 'radius' ) ) : '—';
		case 'height':
			$v = $g( 'height_mm' ) ?: ( $g( 'height_h' ) ?: $g( 'height' ) );
			return $v !== '' ? promen_fmt_dim( $v ) : '—';
		case 'pn':     return $g( 'pn' ) !== '' ? promen_fmt_dim( $g( 'pn' ) ) : '—';
		case 'mass':
			$w = get_post_meta( $pid, '_weight', true );
			return ( $w !== '' && (float) $w > 0 ) ? promen_fmt_dim( (string) $w ) : '—';
		case 'thread':
			return $fast['washer'] !== '' ? promen_fmt_dim( $fast['thread'] ) : ( $fast['M'] !== '' ? $fast['M'] : '—' );
		case 'length': return $fast['length'] !== '' ? promen_fmt_dim( $fast['length'] ) : '—';
		case 'strength':
			return $fast['strength'] !== '' ? $fast['strength'] : ( $fast['washer'] !== '' ? 'тип ' . $fast['washer'] : '—' );
	}
	return '—';
}

/** Человекочитаемый тип фланца из кода (01/11/ФП/ФВ). */
function promen_flange_type_label( string $code ): string {
	$code = trim( $code );
	if ( $code === '' ) {
		return '—';
	}
	static $map = [
		'01' => 'Плоский 01',
		'11' => 'Воротник. 11',
		'21' => 'Свободный 21',
		'ФП' => 'Плоский ФП',
		'ФВ' => 'Воротник. ФВ',
	];
	return $map[ $code ] ?? $code;
}

/**
 * Пара(ы) D×s без исполнения: «108×4» или «108×4-89×4» для перехода/тройника.
 */
function promen_dxs_label( array $dims ): string {
	$pair = static function ( $d, $s ): string {
		$d = trim( (string) $d );
		$s = trim( (string) $s );
		$parts = [];
		if ( $d !== '' ) {
			$parts[] = promen_fmt_dim( $d );
		}
		if ( $s !== '' ) {
			$parts[] = promen_fmt_dim( $s );
		}
		return implode( '×', $parts );
	};

	$pair1 = $pair( $dims['outer_diameter'] ?? '', $dims['wall_thickness'] ?? '' );
	$pair2 = $pair( $dims['outer_d_branch'] ?? '', $dims['wall_branch'] ?? '' );

	if ( $pair1 !== '' && $pair2 !== '' ) {
		return $pair1 . '-' . $pair2;
	}
	// Один торец + признак равнопроходного перехода — дублируем D×s-D×s.
	if ( $pair1 !== '' || $pair2 !== '' ) {
		$one = $pair1 !== '' ? $pair1 : $pair2;
		if ( ! empty( $dims['equal_pass'] ) ) {
			return $one . '-' . $one;
		}
		return $one;
	}

	// Fallback: только DN (без D×s).
	$dn  = trim( (string) ( $dims['dn'] ?? ( $dims['dy'] ?? '' ) ) );
	$dn2 = trim( (string) ( $dims['dn_branch'] ?? ( $dims['dy1'] ?? '' ) ) );
	if ( $dn !== '' && $dn2 !== '' ) {
		return 'DN' . promen_fmt_dim( $dn ) . '×DN' . promen_fmt_dim( $dn2 );
	}
	if ( $dn !== '' ) {
		return 'DN' . promen_fmt_dim( $dn );
	}
	return '';
}

/**
 * Типоразмер для H1/крошек/формы.
 * Фланец: DN{dn} PN{pn} [тип] [уплотнение]; СДТ: D×s; переход: D×s-D2×s2; + «исп. N».
 * Крепёж: M{d}×{L} [класс] [сталь]; шайба: {d} тип {T}.
 */
function promen_size_label( array $dims ): string {
	$thread = trim( (string) ( $dims['thread_size'] ?? '' ) );
	$length = trim( (string) ( $dims['length_mm'] ?? ( $dims['length'] ?? '' ) ) );
	$washer = trim( (string) ( $dims['washer_type'] ?? '' ) );
	$strength = trim( (string) ( $dims['strength_class'] ?? '' ) );
	$steel = trim( (string) ( $dims['material_grade'] ?? '' ) );

	// Крепёж: есть резьба / тип шайбы.
	if ( $thread !== '' || $washer !== '' ) {
		// Битый multi-size из экстрактора (винт ГОСТ 6958-78) — не маскируем под M….
		if ( $thread !== '' && substr_count( $thread, '.' ) > 1 ) {
			return '';
		}

		if ( $washer !== '' ) {
			$d = promen_fmt_dim( $thread !== '' ? $thread : (string) ( $dims['dn'] ?? '' ) );
			$size = $d !== '' ? ( $d . ' тип ' . $washer ) : ( 'тип ' . $washer );
		} else {
			$m = promen_thread_label( $thread );
			$size = ( $length !== '' ) ? ( $m . '×' . promen_fmt_dim( $length ) ) : $m;
		}

		if ( $strength !== '' ) {
			$size .= ' ' . $strength;
		}
		if ( $steel !== '' ) {
			$size .= ' ' . promen_fmt_steel( $steel );
		}

		return trim( $size );
	}

	// Фланец: DN/PN (+ тип / уплотнение), не «Dнар исп.».
	if ( promen_dims_look_like_flange( $dims ) ) {
		$parts = [];
		$dn = trim( (string) ( $dims['dn'] ?? '' ) );
		$pn = trim( (string) ( $dims['pn'] ?? '' ) );
		if ( $dn !== '' ) {
			$parts[] = 'DN' . promen_fmt_dim( $dn );
		}
		if ( $pn !== '' ) {
			$parts[] = 'PN' . promen_fmt_dim( $pn );
		}
		$type = trim( (string) ( $dims['flange_type'] ?? ( $dims['product_type'] ?? '' ) ) );
		$seal = trim( (string) ( $dims['seal_face'] ?? '' ) );
		$extra = trim( ( $type !== '' ? 'тип ' . $type : '' ) . ( $seal !== '' ? ' ' . $seal : '' ) );
		if ( $extra !== '' ) {
			$parts[] = $extra;
		}
		if ( $parts ) {
			return implode( ' ', $parts );
		}
	}

	$size = promen_dxs_label( $dims );

	if ( $size !== '' && ! empty( $dims['execution'] ) ) {
		$size .= ' исп. ' . $dims['execution'];
	}

	return $size;
}

/** Геометрия похожа на фланец (тип/уплотнение/болтовая окружность). */
function promen_dims_look_like_flange( array $dims ): bool {
	if ( trim( (string) ( $dims['flange_type'] ?? '' ) ) !== '' ) {
		return true;
	}
	if ( trim( (string) ( $dims['seal_face'] ?? '' ) ) !== '' ) {
		return true;
	}
	if ( trim( (string) ( $dims['flange_thickness'] ?? '' ) ) !== ''
		&& trim( (string) ( $dims['pn'] ?? '' ) ) !== ''
	) {
		return true;
	}
	$pt = trim( (string) ( $dims['product_type'] ?? '' ) );
	return in_array( $pt, [ '01', '11', 'ФП', 'ФВ' ], true );
}

/** Число без хвостовых нулей: «5.0» → «5», «2.5» → «2.5». */
function promen_fmt_dim( string $v ): string {
	$v = trim( str_replace( ',', '.', $v ) );
	if ( $v === '' || ! is_numeric( $v ) ) {
		return $v;
	}
	$f = (float) $v;
	return ( floor( $f ) == $f ) ? (string) (int) $f : rtrim( rtrim( sprintf( '%.3F', $f ), '0' ), '.' );
}

/** Марка стали для названия: «20» → «Ст20». */
function promen_fmt_steel( string $grade ): string {
	$grade = trim( $grade );
	if ( $grade === '' ) {
		return '';
	}
	if ( preg_match( '/^(Ст|ст)/u', $grade ) ) {
		return $grade;
	}
	if ( preg_match( '/^\d/', $grade ) ) {
		return 'Ст' . $grade;
	}
	return $grade;
}

/** Слаг марки стали (как при импорте pa_steel). */
function promen_steel_slug( string $grade ): string {
	return promen_translit( trim( $grade ) );
}

/**
 * Реальные марки стали товара для фильтров и колонки «Материал».
 * Variable — только из опубликованных вариаций; simple — material_grade из dims или один pa_steel.
 * Не используем «корзину» всех марок с родителя variable (attr_steel через |).
 */
function promen_product_steel_slugs( int $product_id ): array {
	static $cache = [];
	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return $cache[ $product_id ] = [];
	}

	if ( $product->is_type( 'variable' ) ) {
		$slugs = [];
		foreach ( $product->get_children() as $vid ) {
			$v = wc_get_product( $vid );
			if ( ! $v || $v->get_status() !== 'publish' ) {
				continue;
			}
			$attrs = $v->get_attributes();
			if ( ! empty( $attrs['pa_steel'] ) ) {
				$slugs[] = (string) $attrs['pa_steel'];
			}
		}
		return $cache[ $product_id ] = array_values( array_unique( array_filter( $slugs ) ) );
	}

	$dims  = promen_get_dims( $product_id );
	$grade = trim( (string) ( $dims['material_grade'] ?? $dims['material'] ?? '' ) );
	if ( $grade !== '' ) {
		return $cache[ $product_id ] = [ promen_steel_slug( $grade ) ];
	}

	$terms = wp_get_post_terms( $product_id, 'pa_steel', [ 'fields' => 'all' ] );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return $cache[ $product_id ] = [];
	}
	if ( count( $terms ) === 1 ) {
		return $cache[ $product_id ] = [ $terms[0]->slug ];
	}

	return $cache[ $product_id ] = [];
}

/** Человекочитаемые марки стали (для реестра). */
function promen_product_steel_labels( int $product_id, ?array $only_slugs = null ): array {
	$slugs = promen_product_steel_slugs( $product_id );
	if ( $only_slugs ) {
		$slugs = array_values( array_intersect( $slugs, $only_slugs ) );
	}
	$labels = [];
	foreach ( $slugs as $slug ) {
		$t = get_term_by( 'slug', $slug, 'pa_steel' );
		$labels[] = $t ? $t->name : $slug;
	}
	return $labels;
}

/**
 * Текст колонки «Материал»: при фильтре — только выбранные марки;
 * без фильтра — до 3 марок, остальное в title.
 *
 * @return array{text: string, title: string}
 */
function promen_product_steel_display( int $product_id, ?array $active_slugs = null ): array {
	$labels = promen_product_steel_labels( $product_id, $active_slugs ?: null );
	if ( ! $labels ) {
		return [ 'text' => '', 'title' => '' ];
	}
	if ( $active_slugs || count( $labels ) <= 3 ) {
		return [ 'text' => implode( ', ', $labels ), 'title' => '' ];
	}
	$full = implode( ', ', $labels );
	return [
		'text'  => implode( ', ', array_slice( $labels, 0, 2 ) ) . ' … +' . ( count( $labels ) - 2 ),
		'title' => $full,
	];
}

/** Короткие подписи для лейблов в таблице реестра. */
function promen_industry_tag_labels(): array {
	return [
		'aes' => 'АЭС',
		'tes' => 'ТЭС',
		'gkh' => 'ЖКХ',
		'ngk' => 'НГК',
	];
}

/**
 * Опции фильтра «Отрасль» из фасетов канонического поиска (скоуп = текущая группа + фильтры).
 *
 * @param array<string, int> $counts_by_slug
 * @return array<int, array{slug: string, name: string, count: int}>
 */
function promen_industry_facet_options( array $counts_by_slug ): array {
	$opts = [];
	foreach ( promen_industry_tag_labels() as $slug => $name ) {
		$opts[] = [
			'slug'  => $slug,
			'name'  => $name,
			'count' => (int) ( $counts_by_slug[ $slug ] ?? 0 ),
		];
	}
	return $opts;
}

/**
 * Опции фильтра «Отрасль» по таксономии (fallback без канонического слоя).
 *
 * @return array<int, array{slug: string, name: string, count: int}>
 */
function promen_industry_filter_options( ?int $cat_id = null ): array {
	$cat_id = $cat_id ?? ( function_exists( 'promen_scope_cat_id' ) ? promen_scope_cat_id() : 0 );
	$scoped = function_exists( 'promen_scoped_counts' )
		? promen_scoped_counts( 'promen_industry', $cat_id )
		: [];
	$counts = [];
	foreach ( $scoped as $slug => $d ) {
		$counts[ $slug ] = (int) ( $d['count'] ?? 0 );
	}
	return promen_industry_facet_options( $counts );
}

/**
 * Отрасли по нормативу изделия (эвристика по реестру норм витрины).
 *
 * @return string[]
 */
function promen_industry_slugs_from_norm( string $norm ): array {
	$norm = mb_strtolower( trim( $norm ), 'UTF-8' );
	if ( $norm === '' ) {
		return [];
	}

	$rules = [
		'aes' => [
			'/(?:нп|пнаэ)[\s.\-]*0?\d/u',
			'/атом|аэс/u',
			'/гост\s*1737[5-9]/u',
			'/гост\s*22793|гост\s*22818/u',
			'/гост\s*33259|гост\s*28759/u',
			'/гост\s*33522/u',
			'/ост\s*36[\-\s]*17/u',
			'/сто\s*(?:цкти\s*)?318\.0[12]/u',
			'/сто\s*(?:цкти\s*)?321\./u',
			'/сто\s*(?:цкти\s*)?504\.02/u',
			'/сто\s*(?:цкти\s*)?720\.01/u',
		],
		'tes' => [
			'/(?:ост\s*34|сто\s*цкти|грес|тэс)/u',
		],
		'ngk' => [
			'/(?:ост\s*36|сро\-п|ту\s|\bapi\b|нефт|газ|нефтехим)/u',
		],
		'gkh' => [
			'/(?:гост\s*3262|гост\s*30732|жкх|теплосет)/u',
			'/гост\s*1737[5-8]/u',
			'/гост\s*17380/u',
			'/гост\s*30753/u',
			'/ост\s*36[\-\s]*25/u',
		],
	];

	$out = [];
	foreach ( $rules as $slug => $patterns ) {
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $norm ) ) {
				$out[] = $slug;
				break;
			}
		}
	}
	return $out;
}

/** Слаги отраслей → подписи для фильтров и подсказок. */
function promen_industry_labels(): array {
	return [
		'aes' => 'АЭС',
		'tes' => 'ТЭС/ГРЭС',
		'gkh' => 'ЖКХ/теплосети',
		'ngk' => 'Нефтегаз/нефтехим',
	];
}

/** Создать термины отраслей (идемпотентно). */
function promen_ensure_industry_terms(): void {
	if ( ! taxonomy_exists( 'promen_industry' ) ) {
		return;
	}
	foreach ( promen_industry_labels() as $slug => $name ) {
		if ( ! term_exists( $slug, 'promen_industry' ) ) {
			wp_insert_term( $name, 'promen_industry', [ 'slug' => $slug ] );
		}
	}
}

/**
 * Эвристика отраслей по нормативу и категории (при импорте / пересборке).
 *
 * @return string[]
 */
function promen_infer_industry_slugs( int $product_id ): array {
	$norm = promen_product_norm_key( $product_id );
	$out  = promen_industry_slugs_from_norm( $norm );

	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$slugs = wp_list_pluck( $terms, 'slug' );
		if ( array_intersect( $slugs, [ 'truby-vgp', 'izolyatsiya', 'opory', 'opory-nepodv', 'opory-skolz', 'opory-pruzh', 'zaglushki', 'flancy-plosk' ] ) ) {
			$out[] = 'gkh';
		}
		if ( array_intersect( $slugs, [ 'flancy', 'flancy-plosk', 'flancy-vorot', 'sdt', 'otvody', 'troyniki', 'perekhody' ] ) ) {
			$out[] = 'ngk';
		}
		if ( array_intersect( $slugs, [ 'truby', 'truby-bsh', 'truby-es', 'truby-ppu' ] ) ) {
			$out[] = 'gkh';
		}
		if ( array_intersect( $slugs, [ 'armatura', 'armatura-zadvizhki', 'armatura-klapany', 'armatura-krany' ] ) ) {
			$out[] = 'ngk';
		}
		if ( array_intersect( $slugs, [ 'sdt', 'otvody', 'troyniki', 'perekhody', 'dnishcha', 'zaglushki', 'tochenye' ] ) ) {
			$out[] = 'tes';
		}
	}

	$out = array_values( array_unique( $out ) );

	// Гарантируем минимум одну отрасль для витрины.
	if ( ! $out ) {
		if ( $terms && ! is_wp_error( $terms ) ) {
			$slugs = wp_list_pluck( $terms, 'slug' );
			if ( array_intersect( $slugs, [ 'truby', 'truby-bsh', 'truby-es', 'truby-ppu', 'truby-vgp', 'izolyatsiya', 'opory', 'opory-nepodv', 'opory-skolz', 'opory-pruzh' ] ) ) {
				$out[] = 'gkh';
			} elseif ( array_intersect( $slugs, [ 'sdt', 'otvody', 'troyniki', 'perekhody', 'dnishcha', 'zaglushki', 'flancy', 'flancy-plosk', 'flancy-vorot', 'armatura' ] ) ) {
				$out[] = 'ngk';
			}
		}
	}

	return array_values( array_unique( $out ) );
}

/** Привязать отрасли к товару (таксономия promen_industry). */
function promen_sync_product_industries( int $product_id ): void {
	if ( ! taxonomy_exists( 'promen_industry' ) ) {
		return;
	}
	promen_ensure_industry_terms();
	$slugs    = promen_infer_industry_slugs( $product_id );
	$term_ids = [];
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'promen_industry' );
		if ( $term ) {
			$term_ids[] = (int) $term->term_id;
		}
	}
	wp_set_object_terms( $product_id, $term_ids, 'promen_industry', false );
}

/**
 * Слаги отраслей товара (из таксономии).
 *
 * @return string[]
 */
function promen_product_industry_slugs( int $product_id ): array {
	static $cache = [];
	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}
	$terms = wp_get_post_terms( $product_id, 'promen_industry', [ 'fields' => 'slugs' ] );
	if ( ! is_wp_error( $terms ) && $terms ) {
		return $cache[ $product_id ] = array_values( $terms );
	}
	return $cache[ $product_id ] = promen_infer_industry_slugs( $product_id );
}

/** Человекочитаемые подписи отраслей товара. */
function promen_product_industry_labels( int $product_id ): array {
	$labels = promen_industry_labels();
	$out    = [];
	foreach ( promen_product_industry_slugs( $product_id ) as $slug ) {
		$out[] = $labels[ $slug ] ?? strtoupper( $slug );
	}
	return $out;
}

/** Строка для колонки «Отрасль» в реестре. */
function promen_product_industry_display( int $product_id ): string {
	$labels = promen_product_industry_labels( $product_id );
	return $labels ? implode( ', ', $labels ) : '';
}

/**
 * HTML лейблов отраслей для ячейки таблицы (как в design-reference/katalog.html).
 *
 * @param string[] $slugs
 */
function promen_industry_tags_html( array $slugs, int $limit = 3 ): string {
	$labels = promen_industry_tag_labels();
	$slugs  = array_values( array_unique( array_filter( $slugs ) ) );
	if ( ! $slugs ) {
		return '—';
	}
	$tags = [];
	foreach ( array_slice( $slugs, 0, $limit ) as $slug ) {
		$text = $labels[ $slug ] ?? strtoupper( $slug );
		$cls  = 'pr-tag' . ( $slug === 'aes' ? ' hi' : '' );
		$tags[] = '<span class="' . esc_attr( $cls ) . '">' . esc_html( $text ) . '</span>';
	}
	$extra = count( $slugs ) - $limit;
	if ( $extra > 0 ) {
		$tags[] = '<span class="pr-tag">+' . (int) $extra . '</span>';
	}
	return '<span class="pr-tags">' . implode( '', $tags ) . '</span>';
}

/** Слаги категорий крепежа (родитель + семейства). */
function promen_fastener_slugs(): array {
	return [ 'krepezh', 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty' ];
}

/** Активная группа каталога — крепёж или его семейство. */
function promen_is_fastener_group( ?string $group = null ): bool {
	$g = $group ?? ( function_exists( 'promen_active_group' ) ? promen_active_group() : '' );
	return in_array( $g, promen_fastener_slugs(), true );
}

/** Товар принадлежит дереву крепежа. */
function promen_product_is_fastener( int $product_id ): bool {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return false;
	}
	foreach ( $terms as $t ) {
		if ( in_array( $t->slug, promen_fastener_slugs(), true ) ) {
			return true;
		}
	}
	return false;
}

/** Короткий вид крепежа из семейства: «Болт высокопрочный» → «Болт». */
function promen_fastener_kind( string $family ): string {
	foreach ( [ 'Болт', 'Гайка', 'Шпилька', 'Шайба', 'Винт' ] as $kind ) {
		if ( str_starts_with( $family, $kind ) ) {
			return $kind;
		}
	}
	return $family !== '' ? $family : 'Крепёж';
}

/** Подпись резьбы: «14» → «M14», «M16» без изменений. */
function promen_thread_label( string $thread ): string {
	$thread = promen_fmt_dim( trim( $thread ) );
	if ( $thread === '' ) {
		return '';
	}
	if ( preg_match( '/^M/i', $thread ) ) {
		return 'M' . substr( $thread, 1 );
	}
	return 'M' . $thread;
}

/** Резьба и длина из dims (с запасным dn / wall для импорта крепежа). */
function promen_fastener_dims( array $dims ): array {
	$thread = (string) ( $dims['thread_size'] ?? '' );
	if ( $thread === '' && ( $dims['dn'] ?? '' ) !== '' ) {
		$thread = (string) $dims['dn'];
	}
	$length = (string) ( $dims['length_mm'] ?? ( $dims['length'] ?? '' ) );
	if ( $length === '' && ( $dims['wall_thickness'] ?? '' ) !== '' && ( $dims['thread_size'] ?? '' ) !== '' && ( $dims['washer_type'] ?? '' ) === '' ) {
		// В CSV крепежа длина часто дублируется в wall_thickness.
		$length = (string) $dims['wall_thickness'];
	}
	return [
		'thread'   => $thread,
		'length'   => $length,
		'M'        => promen_thread_label( $thread ),
		'strength' => (string) ( $dims['strength_class'] ?? '' ),
		'accuracy' => (string) ( $dims['accuracy_class'] ?? '' ),
		'washer'   => (string) ( $dims['washer_type'] ?? '' ),
		'steel'    => (string) ( $dims['material_grade'] ?? '' ),
	];
}

/**
 * Витринное имя: серия + типоразмер (как H1 карточки).
 * Крепёж: короткий вид + M×L/класс (как торговое имя без ГОСТ — норматив в колонке).
 * Fallback — post_title, если геометрии нет.
 */
function promen_product_display_title( int $product_id ): string {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return (string) get_the_title( $product_id );
	}

	$dims = promen_get_dims( $product_id );
	$size = promen_size_label( $dims );
	if ( $size === '' ) {
		return (string) get_the_title( $product_id );
	}

	if ( promen_product_is_fastener( $product_id ) ) {
		$family = (string) get_post_meta( $product_id, '_promen_family', true );
		return trim( promen_fastener_kind( $family ) . ' ' . $size );
	}

	$meta  = promen_series_meta( $product );
	$angle = ! empty( $dims['angle'] ) ? ' ' . $dims['angle'] . '°' : '';

	return trim( $meta['name'] . $angle . ' ' . $size );
}

/**
 * Тип изделия из названия товара: «Тройник ППУ ОЦ 530х10 — …» → «Тройник ППУ-ОЦ»,
 * «Задвижка клиновая стальная DN50 PN…» → «Задвижка клиновая стальная».
 * Текст до первой цифры, без хвостов DN/PN/Ду/Ру, оболочка ППУ через дефис.
 */
function promen_kind_from_title( string $title ): string {
	$kind = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
	$kind = trim( (string) preg_replace( '/[\d].*$/u', '', $kind ) );
	$kind = trim( (string) preg_replace( '/\s+(DN|PN|Dн|Ду|Ру|M|М)\s*$/u', '', $kind ) );
	$kind = trim( (string) preg_replace( '/\s+/u', ' ', $kind ) );
	$kind = str_replace( [ 'ППУ ОЦ', 'ППУ ПЭ' ], [ 'ППУ-ОЦ', 'ППУ-ПЭ' ], $kind );
	return $kind;
}

/**
 * Размерный ряд: товары того же семейства и норматива (+ угла, если есть).
 * Это и таблица типоразмеров, и DN-кнопки конфигуратора.
 * Кэш — в транзиенте на серию.
 */
function promen_get_series( WC_Product $product ): array {
	$norm = trim( (string) get_post_meta( $product->get_id(), '_promen_norm_key', true ) );
	if ( $norm === '' || strcasecmp( $norm, 'k' ) === 0 ) {
		$norm = function_exists( 'promen_product_norm_key' )
			? promen_product_norm_key( $product->get_id() )
			: '';
	}
	$family = get_post_meta( $product->get_id(), '_promen_family', true );
	if ( ! $norm || ! $family ) {
		return [];
	}
	$dims  = promen_get_dims( $product->get_id() );
	$angle = $dims['angle'] ?? '';

	$cache_key = 'promen_series6_' . md5( "{$norm}|{$family}|{$angle}" );
	$series = get_transient( $cache_key );
	if ( false !== $series ) {
		return $series;
	}

	// Братья: family + термин tax=norm (предпочтительно) или meta _promen_norm_key.
	$meta_query = [ [ 'key' => '_promen_family', 'value' => $family ] ];
	$tax_query  = [];
	if ( $angle !== '' ) {
		$tax_query[] = [ 'taxonomy' => 'pa_angle', 'field' => 'name', 'terms' => $angle ];
	}
	$norm_term = get_term_by( 'name', $norm, 'norm' );
	if ( ! $norm_term && function_exists( 'promen_translit' ) ) {
		$norm_term = get_term_by( 'slug', promen_translit( $norm ), 'norm' );
	}
	if ( $norm_term && ! is_wp_error( $norm_term ) ) {
		$tax_query[] = [ 'taxonomy' => 'norm', 'field' => 'term_id', 'terms' => [ (int) $norm_term->term_id ] ];
	} else {
		$meta_query[] = [ 'key' => '_promen_norm_key', 'value' => $norm ];
	}

	$q = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'meta_query'     => $meta_query,
		'tax_query'      => $tax_query,
		'fields'         => 'ids',
	] );

	$series = [];
	foreach ( $q->posts as $id ) {
		$d = promen_get_dims( $id );
		$series[] = [
			'id'       => $id,
			'title'    => get_the_title( $id ),
			'url'      => get_permalink( $id ),
			'dn'       => $d['dn'] ?? '',
			'dn2'      => $d['dn_branch'] ?? ( $d['dy1'] ?? '' ),
			'pn'       => $d['pn'] ?? '',
			'D'        => $d['outer_diameter'] ?? '',
			's'        => $d['wall_thickness'] ?? '',
			'D2'       => $d['outer_d_branch'] ?? '',
			's2'       => $d['wall_branch'] ?? '',
			'b'        => $d['flange_thickness'] ?? '',
			'D1'       => $d['bolt_circle_d'] ?? '',
			'n'        => $d['stud_count'] ?? '',
			'bolt_d'   => $d['bolt_d'] ?? '',
			'd_inner'  => $d['d_inner'] ?? '',
			'flange_type' => $d['flange_type'] ?? ( $d['product_type'] ?? '' ),
			'seal'     => $d['seal_face'] ?? '',
			'series'   => $d['series'] ?? '',
			'h4'       => $d['h4'] ?? '',
			'R'        => $d['radius_mm'] ?? ( $d['radius'] ?? '' ),
			'L'        => $d['length'] ?? ( $d['length_mm'] ?? '' ),
			'thread'   => $d['thread_size'] ?? '',
			'strength' => $d['strength_class'] ?? '',
			'accuracy' => $d['accuracy_class'] ?? '',
			'washer'   => $d['washer_type'] ?? '',
			'exec'     => $d['execution'] ?? '',
			'size'     => promen_size_label( $d ),
			'mass'     => get_post_meta( $id, '_weight', true ),
			'sku'      => get_post_meta( $id, '_sku', true ),
		];
	}
	usort( $series, static function ( $a, $b ) {
		$ta = (float) ( $a['thread'] !== '' ? $a['thread'] : $a['dn'] );
		$tb = (float) ( $b['thread'] !== '' ? $b['thread'] : $b['dn'] );
		if ( $ta !== $tb ) {
			return $ta <=> $tb;
		}
		$la = (float) ( $a['L'] !== '' ? $a['L'] : $a['s'] );
		$lb = (float) ( $b['L'] !== '' ? $b['L'] : $b['s'] );
		if ( $la !== $lb ) {
			return $la <=> $lb;
		}
		return (float) $a['D'] <=> (float) $b['D'];
	} );

	set_transient( $cache_key, $series, DAY_IN_SECONDS );
	return $series;
}

/** Сброс кэша размерных рядов (после импорта / правки нормативов). */
function promen_flush_series_cache(): int {
	global $wpdb;
	return (int) $wpdb->query(
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '\\_transient\\_promen\\_series%'
		    OR option_name LIKE '\\_transient\\_timeout\\_promen\\_series%'"
	);
}

/**
 * Карта вариаций для конфигуратора:
 * [ steel_slug ][ supervised_slug ] => [ sku, variation_id ].
 */
function promen_get_variation_map( WC_Product $product ): array {
	if ( ! $product->is_type( 'variable' ) ) {
		return [];
	}
	$map = [];
	foreach ( $product->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( ! $v ) {
			continue;
		}
		$attrs = $v->get_attributes(); // [ 'pa_steel' => slug, 'pa_supervised' => slug ]
		$steel = $attrs['pa_steel'] ?? '';
		$sup   = $attrs['pa_supervised'] ?? '';
		$map[ $steel ][ $sup ] = [ 'sku' => $v->get_sku(), 'id' => $vid ];
	}
	return $map;
}

/** Названия терминов атрибута товара: [ slug => name ]. */
function promen_attr_options( WC_Product $product, string $tax ): array {
	$out = [];
	foreach ( wc_get_product_terms( $product->get_id(), $tax ) as $t ) {
		$out[ $t->slug ] = $t->name;
	}
	// Variable: стали/надзор часто только на вариациях — подтягиваем из карты.
	if ( ! $out && $product->is_type( 'variable' ) && in_array( $tax, [ 'pa_steel', 'pa_supervised' ], true ) ) {
		$map = promen_get_variation_map( $product );
		if ( $tax === 'pa_steel' ) {
			foreach ( array_keys( $map ) as $slug ) {
				if ( $slug === '' ) {
					continue;
				}
				$term = get_term_by( 'slug', $slug, $tax );
				$out[ $slug ] = $term && ! is_wp_error( $term ) ? $term->name : $slug;
			}
		} else {
			$sups = [];
			foreach ( $map as $by_sup ) {
				foreach ( array_keys( $by_sup ) as $slug ) {
					if ( $slug !== '' ) {
						$sups[ $slug ] = true;
					}
				}
			}
			foreach ( array_keys( $sups ) as $slug ) {
				$term = get_term_by( 'slug', $slug, $tax );
				$out[ $slug ] = $term && ! is_wp_error( $term ) ? $term->name : $slug;
			}
		}
	}
	return $out;
}

/** Хлебные крошки товара/архива: [ [label, url|null], … ]. */
function promen_breadcrumbs(): array {
	$crumbs = [ [ 'Главная', home_url( '/' ) ] ];
	$shop   = wc_get_page_permalink( 'shop' );
	$crumbs[] = [ 'Каталог', $shop ];

	if ( is_product() ) {
		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			usort( $terms, fn( $a, $b ) => count( get_ancestors( $a->term_id, 'product_cat' ) ) <=> count( get_ancestors( $b->term_id, 'product_cat' ) ) );
			$deepest   = end( $terms );
			$ancestors = array_reverse( get_ancestors( $deepest->term_id, 'product_cat' ) );
			foreach ( $ancestors as $aid ) {
				$a = get_term( $aid, 'product_cat' );
				$crumbs[] = [ $a->name, get_term_link( $a ) ];
			}
			$crumbs[] = [ $deepest->name, get_term_link( $deepest ) ];
		}
		$crumbs[] = [ get_the_title(), null ];
	} elseif ( is_product_category() ) {
		$term      = get_queried_object();
		$ancestors = array_reverse( get_ancestors( $term->term_id, 'product_cat' ) );
		foreach ( $ancestors as $aid ) {
			$a = get_term( $aid, 'product_cat' );
			$crumbs[] = [ $a->name, get_term_link( $a ) ];
		}
		$crumbs[] = [ $term->name, null ];
	} elseif ( is_tax( 'norm' ) ) {
		$crumbs[] = [ get_queried_object()->name, null ];
	}
	return apply_filters( 'promen_breadcrumbs', $crumbs );
}

/** BreadcrumbList schema.org. */
function promen_breadcrumbs_schema( array $crumbs ): string {
	$items = [];
	foreach ( $crumbs as $i => [ $label, $url ] ) {
		$item = [ '@type' => 'ListItem', 'position' => $i + 1, 'name' => $label ];
		if ( $url ) {
			$item['item'] = $url;
		}
		$items[] = $item;
	}
	return wp_json_encode( [
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

/**
 * Файл «Базы знаний» под категорию товара.
 *
 * В шаблоне карточки был жёстко подключён kb-otvody.php — и текст про отводы
 * («Классификация отводов», ГОСТ 17375-2001, виды исполнений) выводился на
 * всех 15 407 карточках, включая болты, трубы и фланцы. Для читателя это
 * прямая дезинформация, для поиска — 25 упоминаний отводов на странице болта.
 *
 * Файлы под остальные разделы в теме уже лежали, их подключали только
 * страницы категорий. Идём от самой глубокой категории вверх по дереву:
 * у «болтов» найдётся kb-bolty.php, у «труб э/с» — родительский kb-truby.php.
 * Ничего не нашлось — блок не выводим: пустое место честнее чужого текста.
 */
function promen_kb_part( int $product_id ): string {
	$cat = promen_deepest_cat( $product_id );
	if ( ! $cat ) {
		return '';
	}
	$slugs = [ $cat->slug ];
	foreach ( get_ancestors( $cat->term_id, 'product_cat', 'taxonomy' ) as $anc_id ) {
		$anc = get_term( $anc_id, 'product_cat' );
		if ( $anc && ! is_wp_error( $anc ) ) {
			$slugs[] = $anc->slug;
		}
	}
	foreach ( $slugs as $slug ) {
		$path = get_theme_file_path( 'woocommerce/parts/kb-' . $slug . '.php' );
		if ( file_exists( $path ) ) {
			return $path;
		}
	}
	return '';
}

/**
 * Описание раздела, когда своего текста в базе нет.
 *
 * Обход 2026-08-25 нашёл 15 подразделов с пустым описанием — «Фланцы
 * плоские», «Трубы б/ш», «Опоры скользящие» и другие. Дописывать их
 * текстом в базу нельзя: контент живёт в БД стенда, а правка отсюда туда
 * не уедет. Поэтому собираем из имени раздела и числа позиций.
 */
function promen_term_desc_fallback( WP_Term $term ): string {
	$n = (int) $term->count;
	$forms = [ 'позиция', 'позиции', 'позиций' ];
	$mod100 = $n % 100;
	$mod10  = $n % 10;
	if ( $mod100 >= 11 && $mod100 <= 14 ) {
		$word = $forms[2];
	} elseif ( 1 === $mod10 ) {
		$word = $forms[0];
	} elseif ( $mod10 >= 2 && $mod10 <= 4 ) {
		$word = $forms[1];
	} else {
		$word = $forms[2];
	}

	// Имя подраздела в отрыве от родителя бессодержательно: «Плоские ФП»,
	// «Бесшовные», «Скользящие». В сниппете это выглядит обрывком, поэтому
	// впереди ставим родительский раздел.
	$head   = $term->name;
	$parent = $term->parent ? get_term( $term->parent, $term->taxonomy ) : null;
	if ( $parent instanceof WP_Term ) {
		$head = $parent->name . ' — ' . $term->name;
	}
	if ( $n > 0 ) {
		$head .= ' — ' . number_format_i18n( $n ) . ' ' . $word . ' в каталоге';
	}
	return $head . '. Изготовление по ГОСТ, ОСТ и чертежам заказчика, запрос коммерческого предложения без корзины.';
}

/**
 * Описание карточки, когда своего текста в базе нет.
 *
 * Обход 2026-08-25 нашёл 409 карточек без meta description. Собираем из
 * того, что заведомо заполнено и что различает позиции между собой:
 * типоразмер, норматив, марки стали. Шаблон один, но подстановки разные —
 * одинаковых описаний это не плодит.
 */
function promen_product_desc_fallback( WC_Product $product ): string {
	$id     = $product->get_id();
	$dims   = promen_get_dims( $id );
	$size   = promen_size_label( $dims );
	$norm   = (string) get_post_meta( $id, '_promen_norm_key', true );
	$kind   = promen_kind_from_title( (string) $product->get_name() );
	$steels = (array) wc_get_product_terms( $id, 'pa_steel', [ 'fields' => 'names' ] );

	$head  = trim( ( '' !== $kind ? $kind : (string) $product->get_name() ) . ( '' !== $size ? ' ' . $size : '' ) );
	$parts = [];
	if ( '' !== $head ) {
		$parts[] = '' !== $norm ? $head . ' по ' . $norm : $head;
	}
	if ( $steels ) {
		$parts[] = 'Марки стали ' . implode( ', ', array_slice( $steels, 0, 4 ) );
	}
	$parts[] = 'Изготовление на заказ, запрос коммерческого предложения';

	return implode( '. ', $parts ) . '.';
}

/**
 * Фото изделия: assets/img/products/<slug-категории>.<ext>.
 *
 * Идём от самой глубокой категории вверх по дереву — подкатегория без
 * собственного снимка наследует родительский; нет ни одного — пусто.
 * Вынесено из шаблона карточки, чтобы микроразметка и вёрстка показывали
 * одну и ту же картинку: в schema.org поле image до 2026-08-26 не выводилось.
 */
function promen_product_photo_url( int $product_id ): string {
	$deep_cat = promen_deepest_cat( $product_id );
	if ( ! $deep_cat ) {
		return '';
	}
	$slugs = [ $deep_cat->slug ];
	foreach ( get_ancestors( $deep_cat->term_id, 'product_cat', 'taxonomy' ) as $anc_id ) {
		$anc = get_term( $anc_id, 'product_cat' );
		if ( $anc && ! is_wp_error( $anc ) ) {
			$slugs[] = $anc->slug;
		}
	}
	foreach ( $slugs as $slug ) {
		foreach ( [ 'webp', 'png', 'jpg', 'jpeg' ] as $ext ) {
			$rel = 'assets/img/products/' . $slug . '.' . $ext;
			if ( file_exists( get_theme_file_path( $rel ) ) ) {
				return get_theme_file_uri( $rel );
			}
		}
	}
	return '';
}

/**
 * schema.org Product без цены (catalog mode).
 * sku / name / description / brand / material / category.
 */
function promen_product_schema( WC_Product $product ): string {
	$dims     = promen_get_dims( $product->get_id() );
	$norm_key = get_post_meta( $product->get_id(), '_promen_norm_key', true );
	$steels   = wc_get_product_terms( $product->get_id(), 'pa_steel' );
	$cats     = get_the_terms( $product->get_id(), 'product_cat' );
	$cat_name = '';
	if ( $cats && ! is_wp_error( $cats ) ) {
		usort( $cats, fn( $a, $b ) => count( get_ancestors( $a->term_id, 'product_cat' ) ) <=> count( get_ancestors( $b->term_id, 'product_cat' ) ) );
		$cat_name = end( $cats )->name;
	}

	$data = [
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		// С PN, как и <title>: без давления фланцы одного DN дают одинаковое
		// имя сущности, и поисковик склеивает разные позиции в одну.
		'name'        => function_exists( 'promen_product_title_seo' )
			? promen_product_title_seo( $product->get_id() )
			: $product->get_name(),
		'sku'         => $product->get_sku(),
		'description' => wp_strip_all_tags( promen_sanitize_desc( $product->get_id(), $product->get_description() ) ),
		'url'         => get_permalink( $product->get_id() ),
		'brand'       => [
			'@type' => 'Brand',
			'name'  => 'Промышленная Энергетика',
		],
		// offers здесь больше нет. Оно выводилось без price — а Offer обязан
		// нести price либо priceSpecification, иначе валидатор считает его
		// неполным, и товарный сниппет всё равно не собирается. Для каталога
		// без цен Product без offers — валидная разметка, и это честнее, чем
		// пустой Offer. Подставлять price: 0 нельзя: читается как «бесплатно».
	];
	$photo = promen_product_photo_url( $product->get_id() );
	if ( '' !== $photo ) {
		$data['image'] = $photo;
	}
	if ( $cat_name ) {
		$data['category'] = $cat_name;
	}
	if ( $steels ) {
		$steel_names = array_values( array_map( fn( $t ) => $t->name, $steels ) );
		// material как массив всех доступных марок — отражает вариативность карточки.
		$data['material'] = count( $steel_names ) > 1 ? $steel_names : $steel_names[0];
		if ( count( $steel_names ) > 1 ) {
			$data['additionalProperty'][] = [
				'@type' => 'PropertyValue',
				'name'  => 'Марки стали',
				'value' => implode( ', ', $steel_names ),
			];
		}
	}
	if ( $norm_key ) {
		$data['additionalProperty'][] = [
			'@type' => 'PropertyValue',
			'name'  => 'Норматив',
			'value' => $norm_key,
		];
	}
	if ( ( $dims['dn'] ?? '' ) !== '' ) {
		$data['additionalProperty'][] = [
			'@type' => 'PropertyValue',
			'name'  => 'DN',
			'value' => $dims['dn'],
		];
	}
	if ( ( $dims['pn'] ?? '' ) !== '' ) {
		$data['additionalProperty'][] = [
			'@type' => 'PropertyValue',
			'name'  => 'PN',
			'value' => $dims['pn'],
		];
	}
	return wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

/** Canonical карточки — всегда чистый URL без ?steel=&nadzor=.
 *  Core WP rel_canonical уже отдаёт get_permalink(); при query-параметрах
 *  дополнительно подавляем возможный «грязный» canonical от других плагинов. */
add_filter( 'get_canonical_url', function ( $canonical ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		// Режим серии: canonical на URL серии, не на репрезентативный товар.
		if ( promen_is_series_view() ) {
			$p = wc_get_product( get_the_ID() );
			if ( $p ) {
				return promen_series_meta( $p )['url'];
			}
		}
		return get_permalink();
	}
	return $canonical;
} );

/** Title серии — имя серии, а не репрезентативного типоразмера. */
add_filter( 'document_title_parts', function ( array $parts ): array {
	if ( function_exists( 'is_product' ) && is_product() && promen_is_series_view() ) {
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$m = promen_series_meta( $p );
			$norm = get_post_meta( $p->get_id(), '_promen_norm_key', true );
			$parts['title'] = trim( $m['name'] . ( $m['angle'] !== '' ? ' ' . $m['angle'] . '°' : '' ) . ( $norm ? ' ' . $norm : '' ) );
		}
	}
	return $parts;
}, 20 );

/**
 * Связанные позиции: тот же DN, но другой норматив/серия (в пилоте каталог
 * состоит из отводов, поэтому связываем серии между собой; после расширения
 * каталога сюда вернётся межкатегорийная перелинковка фланцев и крепежа).
 */
function promen_related_by_dn( WC_Product $product, int $limit = 5 ): array {
	$dn_terms = wc_get_product_terms( $product->get_id(), 'pa_dn' );
	if ( ! $dn_terms ) {
		return [];
	}
	$own_norm = get_the_terms( $product->get_id(), 'norm' );
	$own_norm_ids = $own_norm && ! is_wp_error( $own_norm ) ? wp_list_pluck( $own_norm, 'term_id' ) : [];

	$tax_query = [
		[ 'taxonomy' => 'pa_dn', 'field' => 'term_id', 'terms' => [ $dn_terms[0]->term_id ] ],
	];
	if ( $own_norm_ids ) {
		$tax_query[] = [ 'taxonomy' => 'norm', 'field' => 'term_id', 'terms' => $own_norm_ids, 'operator' => 'NOT IN' ];
	}

	$q = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'no_found_rows'  => true,
		'orderby'        => 'ID',
		'post__not_in'   => [ $product->get_id() ],
		'tax_query'      => $tax_query,
		'fields'         => 'ids',
	] );

	$out = [];
	foreach ( $q->posts as $id ) {
		$out[] = [
			'id'    => $id,
			'title' => get_the_title( $id ),
			'url'   => get_permalink( $id ),
			'sku'   => get_post_meta( $id, '_sku', true ),
			'norm'  => get_post_meta( $id, '_promen_norm_key', true ),
		];
	}
	return $out;
}

// ── Серии: «Отвод крутоизогнутый 90°» = норматив × угол ─────────
//
// Дизайн-карточка (product-otvod-90.html) — страница СЕРИИ; типоразмеры —
// её SEO-производные с тем же шаблоном. Серия определяется по товару.

/** Название типа изделия по нормативу (для H1 серии); fallback — семейство. */
function promen_series_type_name( string $norm_key, string $family = '' ): string {
	$map = [
		// Отводы
		'ГОСТ 17375-2001' => 'Отвод крутоизогнутый',
		'ГОСТ 30753-2001' => 'Отвод крутоизогнутый 2D',
		'ГОСТ 22793-83' => 'Отвод гнутый Ру 100',
		'ГОСТ 22818-83' => 'Колено с опорой',
		'ОСТ 36-21-77'    => 'Отвод сварной секторный',
		'СТО ЦКТИ 321.01-2009'      => 'Отвод гнутый ЦКТИ 321.01',
		'СТО ЦКТИ 321.02-2009'      => 'Отвод гнутый ЦКТИ 321.02',
		'СТО ЦКТИ 321.05-2009'      => 'Отвод гнутый ЦКТИ 321.05',
		// Тройники
		'ГОСТ 17376-2001'  => 'Тройник бесшовный приварной',
		'ГОСТ 22801-83'  => 'Тройник на Ру до 100 МПа',
		'ГОСТ 22822-83'  => 'Тройник с опорой',
		'ОСТ 34-10-762-97' => 'Тройник сварной ТЭС',
		'ОСТ 34-10-763-97' => 'Тройник сварной переходный ТЭС',
		'ОСТ 34-10-764-97' => 'Тройник сварной ТЭС',
		'ОСТ 34-10-765-97' => 'Тройник сварной ТЭС',
		'СЕРИЯ 4.903-10'   => 'Тройник тепловых сетей',
		// Переходы
		'ГОСТ 17378-2001'  => 'Переход бесшовный приварной',
		'ГОСТ 22826-83'  => 'Переход на Ру до 100 МПа',
		'ОСТ 36-22-77'     => 'Переход сварной',
		'ОСТ 34-10-753-97' => 'Переход сварной ТЭС',
		'ОСТ 34-10-754-97' => 'Переход сварной ТЭС',
		'СТО ЦКТИ 318.01-2009'       => 'Переход ЦКТИ 318.01',
				// Трубы
		'ГОСТ 8732-78'  => 'Труба бесшовная г/д',
		'ГОСТ 8734-75'  => 'Труба бесшовная х/д',
		'ГОСТ 10704-91' => 'Труба электросварная',
		'ГОСТ 10705-80' => 'Труба электросварная',
		// ГОСТ 30732-2020 покрывает трубы/тройники/отводы в ППУ — тип из названия
		// товара (promen_ppu_kind_from_title); это нейтральный фолбэк серии.
		'ГОСТ 30732-2020' => 'Изделие в ППУ',
		'ГОСТ 3262-75'  => 'Труба ВГП',
		// Опоры и арматура: типов много (неподвижная/скользящая/пружинная;
		// задвижка/клапан/кран) — имя из названия товара через
		// promen_kind_from_title, в карте не фиксируем: одинаковый заголовок
		// на все типы даёт SEO-дубли H1 и неразличимые строки реестра.
		// Днища и заглушки

		'ГОСТ 6533-78'   => 'Днище эллиптическое отбортованное',
		'ГОСТ 17379-2001'  => 'Заглушка эллиптическая',
		'ГОСТ 22815-83'  => 'Заглушка на Ру до 100 МПа',
		// Фланцы
		'ГОСТ 33259-2015'   => 'Фланец трубопроводный',
		'ГОСТ 12820-80'   => 'Фланец плоский приварной',
		'ГОСТ 12821-80'   => 'Фланец приварной встык',
		'ГОСТ 28759.2-2022' => 'Фланец сосудов и аппаратов',
	];
	if ( isset( $map[ $norm_key ] ) ) {
		return $map[ $norm_key ];
	}
	// Крепёж и всё прочее: имя семейства из данных (ед. число не строим).
	return $family !== '' ? $family : 'Изделие';
}

/** Префикс кода позиции по слагу самой глубокой категории товара. */
function promen_series_code_prefix( string $cat_slug ): string {
	$map = [
		'otvody'    => 'ОТВ',
		'troyniki'  => 'ТРО',
		'perekhody' => 'ПЕР',
		'dnishcha'  => 'ДНЩ',
		'zaglushki' => 'ЗГЛ',
		'flancy'    => 'ФЛН',
		'flancy-plosk' => 'ФЛП',
		'flancy-vorot' => 'ФЛВ',
		'flancy-01' => 'Ф01',
		'flancy-11' => 'Ф11',
		'krepezh'   => 'КРП',
		'bolty'     => 'БЛТ',
		'gayki'     => 'ГАЙ',
		'shpilki'   => 'ШПЛ',
		'shayby'    => 'ШАЙ',
		'vinty'     => 'ВНТ',
		'tochenye'  => 'ТЧН',
		'truby'     => 'ТРБ',
		'truby-bsh' => 'ТБШ',
		'truby-es'  => 'ТЭС',
		'truby-ppu' => 'ТПУ',
		'truby-vgp' => 'ТВГ',
		'izolyatsiya' => 'ИЗЛ',
		'opory'     => 'ОПР',
		'opory-nepodv' => 'ОНП',
		'opory-skolz' => 'ОСК',
		'opory-pruzh' => 'ОПРЖ',
		'armatura'  => 'ЗРА',
		'armatura-zadvizhki' => 'ЗДВ',
		'armatura-klapany' => 'КЛП',
		'armatura-krany' => 'КРН',
	];
	return $map[ $cat_slug ] ?? 'ПОЗ';
}

/** Самый глубокий термин категории товара (или null). */
function promen_deepest_cat( int $product_id ): ?WP_Term {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	usort( $terms, fn( $a, $b ) => count( get_ancestors( $a->term_id, 'product_cat' ) ) <=> count( get_ancestors( $b->term_id, 'product_cat' ) ) );
	return end( $terms );
}

/**
 * Товарная группа для правил отображения характеристик.
 * Возвращает: flange | fastener | pipe | support | valve | insulation | sdt | turned | other.
 * От группы зависит, какие характеристики правдивы (напр. номинальный PN — только у фланцев,
 * масса — недостоверна у крепежа, PN у СДТ — вычисленное давление, не класс).
 */
function promen_category_group( int $product_id ): string {
	$term = promen_deepest_cat( $product_id );
	if ( ! $term ) {
		return 'other';
	}
	$slugs = [ $term->slug ];
	foreach ( get_ancestors( $term->term_id, 'product_cat' ) as $aid ) {
		$t = get_term( $aid, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) ) {
			$slugs[] = $t->slug;
		}
	}
	$has = fn( string $s ): bool => in_array( $s, $slugs, true );
	if ( $has( 'flancy' ) || strpos( $term->slug, 'flancy' ) === 0 ) {
		return 'flange';
	}
	if ( promen_product_is_fastener( $product_id ) ) {
		return 'fastener';
	}
	if ( $has( 'truby' ) || strpos( $term->slug, 'truby' ) === 0 ) {
		return 'pipe';
	}
	if ( $has( 'opory' ) || strpos( $term->slug, 'opory' ) === 0 ) {
		return 'support';
	}
	if ( $has( 'armatura' ) ) {
		return 'valve';
	}
	if ( $has( 'izolyatsiya' ) ) {
		return 'insulation';
	}
	if ( $has( 'tochenye' ) ) {
		return 'turned';
	}
	if ( $has( 'sdt' ) || in_array( $term->slug, [ 'otvody', 'troyniki', 'perekhody', 'dnishcha', 'zaglushki' ], true ) ) {
		return 'sdt';
	}
	return 'other';
}

/**
 * Номинальный PN правдив только у фланцев. У СДТ в источнике pn — вычисленное
 * рабочее давление при температуре (напр. 4.02 МПа при 545°C), не номинальный класс.
 */
function promen_pn_is_nominal( string $group ): bool {
	// Фланцы и арматура имеют реальный номинальный класс PN; у СДТ pn — вычисленное
	// давление, поэтому недостоверно.
	return $group === 'flange' || $group === 'valve';
}

/**
 * Масса достоверна у литых/гнутых/трубных изделий; у крепежа в источнике завышена
 * (болт M48 → 2550 кг), поэтому её не показываем.
 */
function promen_mass_is_reliable( string $group ): bool {
	return ! in_array( $group, [ 'fastener' ], true );
}

/**
 * Чистит текст описания от недостоверной массы (крепёж) — для hero и schema.org.
 */
function promen_sanitize_desc( int $product_id, string $text ): string {
	if ( ! promen_mass_is_reliable( promen_category_group( $product_id ) ) ) {
		$text = preg_replace( '/\s*Масса\s+[\d.,]+\s*кг\.?/u', '', $text );
	}
	return $text;
}

/**
 * Метаданные серии товара:
 * name (для H1), code (ОТВ-17375-90), slug, url, pass_id (ПЭ-…-DN).
 */
function promen_series_meta( WC_Product $product ): array {
	$norm_key = get_post_meta( $product->get_id(), '_promen_norm_key', true );
	$family   = get_post_meta( $product->get_id(), '_promen_family', true );
	$dims     = promen_get_dims( $product->get_id() );
	$angle    = $dims['angle'] ?? '';
	$dn       = $dims['dn'] ?? '';

	preg_match( '/([\d][\d.\-]*[\d])/', $norm_key, $m );
	$norm_num = str_replace( '.', '-', $m[1] ?? 'x' );
	// Из «17375-2001» берём номер стандарта без года.
	$norm_short = explode( '-', $norm_num )[0];

	// Категория товара определяет префикс кода и базовый URL серии.
	$cat      = promen_deepest_cat( $product->get_id() );
	$prefix   = promen_series_code_prefix( $cat ? $cat->slug : '' );
	$cat_link = $cat ? get_term_link( $cat ) : wc_get_page_permalink( 'shop' );
	if ( is_wp_error( $cat_link ) ) {
		$cat_link = wc_get_page_permalink( 'shop' );
	}

	$code = $prefix . '-' . $norm_short . ( $angle !== '' ? '-' . $angle : '' );
	$slug = promen_translit( $norm_key ) . ( $angle !== '' ? '-' . $angle : '' );

	// Карта «норматив → тип» промахнулась или дала generic (изоляция ГОСТ 30732-2020,
	// арматура, трубы ВГП со slug-нормой) — тип изделия из названия товара.
	$name = promen_series_type_name( $norm_key, $family );
	if ( $name === $family || 'Изделие' === $name || 'Изделие в ППУ' === $name ) {
		$kind = promen_kind_from_title( (string) $product->get_name() );
		if ( $kind !== '' ) {
			$name = $kind;
		}
	}

	// Уточнение подкатегории, когда один норматив описывает несколько типов.
	// ГОСТ 33259-2015 — это и тип 01, и тип 11: у обеих серий имя выходило
	// «Фланец трубопроводный», а значит одинаковые H1 и title. Проверка по
	// данным (28.08.2026) нашла четыре норматива, живущих больше чем в одной
	// подкатегории; из них имена расходятся сами у трёх — `series_tag` стоит
	// только там, где не расходятся.
	//
	// Только в режиме серии: на карточке типоразмер и так несёт тип
	// («DN40 PN40 тип 11 B»), и уточнение в H1 читалось бы дважды.
	if ( $cat && promen_is_series_view() ) {
		$defs = function_exists( 'promen_catalog_taxonomy_defs' ) ? promen_catalog_taxonomy_defs() : [];
		$tag  = trim( (string) ( $defs[ $cat->slug ]['series_tag'] ?? '' ) );
		if ( '' !== $tag && false === mb_stripos( $name, $tag ) ) {
			$name .= ' ' . $tag;
		}
	}

	return [
		'name'    => $name,
		'angle'   => $angle,
		'code'    => $code,
		'slug'    => $slug,
		'url'     => promen_series_url( $cat_link, $slug, $cat ? $cat->slug : '' ),
		'pass_id' => 'ПЭ-' . $code . ( $dn !== '' ? '-' . $dn : '' ),
	];
}

/**
 * Адрес страницы серии — только если серия открывается.
 *
 * Серия — не отдельный товар, а маршрут `catalog/…/seriya/<слаг>/` из
 * mu-plugins/promen-structure.php: он ищет репрезентативный товар по
 * нормативу и рисует его в режиме серии. Если товара нет, маршрут никуда
 * не ведёт, а крошка ссылку всё равно строила — обход 2026-08-25 нашёл
 * 44 такие битые ссылки. Проверяем ровно тем же способом, что и роутер,
 * иначе адрес и его проверка разойдутся; результат там уже кэшируется
 * в транзиенте, отдельный кэш не нужен.
 */
function promen_series_url( string $cat_link, string $slug, string $cat_slug = '' ): string {
	if ( '' === $slug || ! promen_series_representative( $slug, $cat_slug ) ) {
		return '';
	}
	return trailingslashit( $cat_link ) . 'seriya/' . $slug . '/';
}

/**
 * Репрезентативный товар серии (медианный DN — как DN 100 в дизайне).
 * Кэш в транзиенте; слаг серии = translit(norm)+angle.
 *
 * $cat_slug — категория из адреса серии. Без неё выборка шла по одному
 * нормативу, а один норматив живёт в нескольких подкатегориях: ГОСТ 33259-2015
 * описывает и тип 01, и тип 11. Обход 2026-08-28 нашёл результат — страницы
 * /flancy-01/seriya/gost-33259-2015/ и /flancy-11/seriya/gost-33259-2015/
 * отдавали побайтово одинаковый HTML (различались только og:url и nonce),
 * то есть у плоских фланцев страницы серии не было вовсе, а посетитель на её
 * адресе видел воротниковые. Категорию берём из маршрута, а не из товара.
 */
function promen_series_representative( string $series_slug, string $cat_slug = '' ): int {
	$cache_key = 'promen_series_rep_' . md5( $series_slug . '|' . $cat_slug );
	$id = get_transient( $cache_key );
	if ( false !== $id ) {
		return (int) $id;
	}

	// Разбираем слаг: угол — последний числовой сегмент.
	$angle = '';
	$norm_slug = $series_slug;
	if ( preg_match( '/^(.*)-(\d{2,3})$/', $series_slug, $m ) ) {
		$norm_slug = $m[1];
		$angle     = $m[2];
	}

	$tax_query = [ [ 'taxonomy' => 'norm', 'field' => 'slug', 'terms' => $norm_slug ] ];
	if ( $angle !== '' ) {
		$tax_query[] = [ 'taxonomy' => 'pa_angle', 'field' => 'name', 'terms' => $angle ];
	}
	if ( '' !== $cat_slug ) {
		// include_children по умолчанию true: для родительской категории серия
		// соберётся из всех подкатегорий, для дочерней — только из своей.
		$tax_query[] = [ 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cat_slug ];
	}
	$q = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'no_found_rows'  => true,
		'fields'         => 'ids',
		'tax_query'      => $tax_query,
	] );
	if ( ! $q->posts ) {
		set_transient( $cache_key, 0, HOUR_IN_SECONDS );
		return 0;
	}

	// Медианный по DN товар серии.
	$with_dn = [];
	foreach ( $q->posts as $pid ) {
		$d = promen_get_dims( $pid );
		$with_dn[] = [ 'id' => $pid, 'dn' => (float) ( $d['dn'] ?? 0 ) ];
	}
	usort( $with_dn, fn( $a, $b ) => $a['dn'] <=> $b['dn'] );
	$id = $with_dn[ (int) floor( count( $with_dn ) / 2 ) ]['id'];

	set_transient( $cache_key, $id, DAY_IN_SECONDS );
	return (int) $id;
}

/** Активен ли режим серии на текущем запросе. */
function promen_is_series_view(): bool {
	return (bool) get_query_var( 'promen_series' );
}

/**
 * Авто-нормбаза категории: реальные нормативы, привязанные к товарам
 * категории (с учётом подкатегорий), с числом позиций и кратким описанием.
 * Заменяет скопированные СДТ-вкладки в шаблонах не-СДТ категорий.
 */
function promen_norm_short( string $key ): array {
	static $map = [
		// Фланцы
		'ГОСТ 33259-2015'   => [ 'Фланцы на PN до 250', 'Действующий · заменил ГОСТ 12820-80/12821-80/12836-80', 'Базовый' ],
		'ГОСТ 12820-80'   => [ 'Фланцы плоские приварные', 'Заменён ГОСТ 33259-2015 · для ремонтных программ', 'Заменён' ],
		'ГОСТ 12821-80'   => [ 'Фланцы приварные встык', 'Заменён ГОСТ 33259-2015 · воротниковые', 'Заменён' ],
		'ГОСТ 28759.2-2022' => [ 'Фланцы сосудов и аппаратов', 'Плоские приварные для сосудов под давлением', 'Действует' ],
		// Крепёж
		'ГОСТ 22032-76' => [ 'Шпильки, ввинчиваемый конец 1d', 'Класс точности В', 'Действует' ],
		'ГОСТ 22043-76' => [ 'Шпильки, ввинчиваемый конец 2d', 'Класс точности В', 'Действует' ],
		'ГОСТ 7798-70'  => [ 'Болты с шестигранной головкой', 'Класс точности В', 'Действует' ],
		'ГОСТ 7805-70'  => [ 'Болты с шестигранной головкой', 'Класс точности А', 'Действует' ],
		'ГОСТ 7795-70'  => [ 'Болты с уменьшенной головкой', 'Класс точности В, с подголовком', 'Действует' ],
		'ГОСТ 7808-70'  => [ 'Болты с уменьшенной головкой', 'Класс точности А', 'Действует' ],
		'ГОСТ 15590-70' => [ 'Болты с уменьшенной головкой', 'Класс точности С, с подголовком', 'Действует' ],
		'ГОСТ 15591-70' => [ 'Болты с уменьшенной головкой', 'Класс точности С', 'Действует' ],
		'ГОСТ 7796-70'  => [ 'Болты с уменьшенной головкой', 'Класс точности В', 'Действует' ],
		'ГОСТ 9066-70'  => [ 'Шпильки фланцевых соединений', 'Среда 0…650 °C', 'Действует' ],
		'ГОСТ 10494-80' => [ 'Шпильки фланцевые Ру 10–100', 'Высокое давление', 'Действует' ],
		'ОСТ 26-2040-96'  => [ 'Шпильки фланцевые аппаратов', 'Отраслевой стандарт', 'Действует' ],
		'ГОСТ 10602-94' => [ 'Болты с диаметром резьбы >48', 'Крупный крепёж', 'Действует' ],
		'ГОСТ 5915-70'  => [ 'Гайки шестигранные', 'Класс точности В', 'Действует' ],
		'ГОСТ 5927-70'  => [ 'Гайки шестигранные', 'Класс точности А', 'Действует' ],
		'ГОСТ 11371-78' => [ 'Шайбы', 'Технические условия', 'Действует' ],
		'ГОСТ 6402-70'  => [ 'Шайбы пружинные', 'Стопорение резьбовых соединений', 'Действует' ],
	];
	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}
	return [ promen_series_type_name( $key ), 'Действующий стандарт категории', 'Действует' ];
}

function promen_render_norm_base( string $cat_slug ): void {
	$term = get_term_by( 'slug', $cat_slug, 'product_cat' );
	if ( ! $term ) {
		return;
	}
	$counts = function_exists( 'promen_scoped_counts' )
		? promen_scoped_counts( 'norm', (int) $term->term_id )
		: [];
	$rows = [];
	foreach ( $counts as $slug => $d ) {
		$rows[] = [ 'key' => $d['name'], 'count' => $d['count'] ];
	}
	usort( $rows, fn( $a, $b ) => $b['count'] <=> $a['count'] );
	$rows = array_slice( $rows, 0, 12 );
	?>
  <section class="s s-dark" id="s04">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">04</span>Нормативная база</div>
      <div class="s-meta">STANDARDS REGISTRY</div>
    </div>
    <div class="s-body">
      <div class="norm-grid reveal">
        <?php foreach ( $rows as $r ) :
          [ $title, $desc, $status ] = promen_norm_short( $r['key'] );
          $norm_slug = promen_translit( $r['key'] );
          $nt = get_term_by( 'slug', $norm_slug, 'norm' );
          $href = $nt ? get_term_link( $nt ) : add_query_arg( 'gost', $norm_slug, get_term_link( $term ) );
        ?>
        <a class="nc" href="<?php echo esc_url( is_wp_error( $href ) ? '#' : $href ); ?>">
          <div class="nc-code"><?php echo esc_html( $r['key'] ); ?></div>
          <div class="nc-title"><?php echo esc_html( $title ); ?></div>
          <div class="nc-desc"><?php echo esc_html( $desc ); ?> · <?php echo esc_html( number_format_i18n( $r['count'] ) ); ?> позиций в каталоге.</div>
          <div class="nc-tags"><span class="nc-tag"><?php echo esc_html( $status ); ?></span></div>
          <div class="nc-status"><span class="nc-dot"></span><?php echo $status === 'Заменён' ? 'По запросу' : 'Действует'; ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php
}
