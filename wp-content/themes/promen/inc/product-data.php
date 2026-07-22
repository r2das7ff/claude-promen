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
	return promen_sanitize_dims( $dims );
}

/**
 * Убирает «DN = номер исполнения» (бобышки ОСТ 24.125.57: dn=01…09 = execution).
 * Реальный размер остаётся в outer_diameter / wall_thickness.
 */
function promen_sanitize_dims( array $dims ): array {
	$dn = trim( (string) ( $dims['dn'] ?? '' ) );
	$ex = trim( (string) ( $dims['execution'] ?? '' ) );
	$od = trim( (string) ( $dims['outer_diameter'] ?? '' ) );
	if ( $dn !== '' && $ex !== '' && $dn === $ex && preg_match( '/^0?[1-9]$/', $dn ) && $od !== '' ) {
		unset( $dims['dn'] );
	}
	// Мусор n: stud_count совпал с наружным диаметром.
	$n  = trim( (string) ( $dims['stud_count'] ?? '' ) );
	if ( $n !== '' && $od !== '' && $n === $od ) {
		unset( $dims['stud_count'] );
	}
	return $dims;
}

/** Норматив для витрины: meta, иначе разбор title / designation. */
function promen_product_norm_key( int $product_id ): string {
	$nk = trim( (string) get_post_meta( $product_id, '_promen_norm_key', true ) );
	if ( $nk !== '' && strcasecmp( $nk, 'k' ) !== 0 ) {
		return $nk;
	}
	$title = (string) get_the_title( $product_id );
	$gost  = trim( (string) get_post_meta( $product_id, '_promen_gost_designation', true ) );
	foreach ( [ $gost, $title ] as $text ) {
		if ( $text === '' ) {
			continue;
		}
		if ( preg_match( '/((?:ГОСТ|ОСТ|СТО|ТУ)\s*(?:СРО-П\s*)?[^\s,]{3,}(?:-\d{2,4})?)/ui', $text, $m ) ) {
			return preg_replace( '/\s+/u', ' ', trim( $m[1] ) );
		}
	}
	return ( $nk !== '' && strcasecmp( $nk, 'k' ) !== 0 ) ? $nk : '';
}

/**
 * Колонки характеристик реестра каталога — СВОИ под каждую категорию/подкатегорию.
 * Возвращает средние колонки (между «Наименование» и «Материал»); ведущие
 * (Норматив, Наименование) и хвостовые (Материал, стрелка) добавляет шаблон.
 * Каждая колонка: [ 'key' => …, 'label' => …, 'w' => grid-ширина ].
 * key рендерится в archive-product.php (switch по ключу).
 */
function promen_catalog_columns( string $group ): array {
	// Каждый диаметр и стенка — ОТДЕЛЬНАЯ колонка (как у конкурентов).
	$DN    = [ 'key' => 'dn',     'label' => 'DN',          'w' => '52px' ];
	$D     = [ 'key' => 'd',      'label' => 'Dн, мм',      'w' => '64px' ];
	$S     = [ 'key' => 's',      'label' => 's, мм',       'w' => '54px' ];
	$ANGLE = [ 'key' => 'angle',  'label' => 'Угол',        'w' => '54px' ];
	$RAD   = [ 'key' => 'radius', 'label' => 'R, мм',       'w' => '56px' ];
	$HEIGHT= [ 'key' => 'height', 'label' => 'H, мм',       'w' => '56px' ];
	$MASS  = [ 'key' => 'mass',   'label' => 'Масса, кг',   'w' => '78px' ];
	$MASSM = [ 'key' => 'mass',   'label' => 'Масса, кг/м', 'w' => '90px' ];
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
			$v = $g( 'flange_type' ) ?: $g( 'product_type' );
			return $v !== '' ? $v : '—';
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
	return $pair1 !== '' ? $pair1 : $pair2;
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
		// Битый multi-size из экстрактора (винт ГОСТ 6958) — не маскируем под M….
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
 * Размерный ряд: товары того же семейства и норматива (+ угла, если есть).
 * Это и таблица типоразмеров, и DN-кнопки конфигуратора.
 * Кэш — в транзиенте на серию.
 */
function promen_get_series( WC_Product $product ): array {
	$norm   = get_post_meta( $product->get_id(), '_promen_norm_key', true );
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

	$q = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'meta_query'     => [
			[ 'key' => '_promen_norm_key', 'value' => $norm ],
			[ 'key' => '_promen_family', 'value' => $family ],
		],
		'tax_query'      => $angle !== '' ? [
			[ 'taxonomy' => 'pa_angle', 'field' => 'name', 'terms' => $angle ],
		] : [],
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
		'name'        => $product->get_name(),
		'sku'         => $product->get_sku(),
		'description' => wp_strip_all_tags( promen_sanitize_desc( $product->get_id(), $product->get_description() ) ),
		'url'         => get_permalink( $product->get_id() ),
		'brand'       => [
			'@type' => 'Brand',
			'name'  => 'Промышленная Энергетика',
		],
		'offers'      => [
			'@type'         => 'Offer',
			'availability'  => 'https://schema.org/InStock',
			'url'           => get_permalink( $product->get_id() ),
			'priceCurrency' => 'RUB',
			// Цены нет — catalog mode; указываем только availability.
		],
	];
	if ( $cat_name ) {
		$data['category'] = $cat_name;
	}
	if ( $steels ) {
		$data['material'] = $steels[0]->name;
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
		'ГОСТ 22793-1983' => 'Отвод гнутый Ру 100',
		'ГОСТ 22818-1983' => 'Колено с опорой',
		'ОСТ 36-21-77'    => 'Отвод сварной секторный',
		'СТО 321.01'      => 'Отвод гнутый ЦКТИ 321.01',
		'СТО 321.02'      => 'Отвод гнутый ЦКТИ 321.02',
		'СТО 321.05'      => 'Отвод гнутый ЦКТИ 321.05',
		// Тройники
		'ГОСТ 17376-2001'  => 'Тройник бесшовный приварной',
		'ГОСТ 22801-1983'  => 'Тройник на Ру до 100 МПа',
		'ГОСТ 22822-1983'  => 'Тройник с опорой',
		'ОСТ 34-10-762-97' => 'Тройник сварной ТЭС',
		'ОСТ 34.10.762-97' => 'Тройник сварной ТЭС',
		'ОСТ 34-10-763-97' => 'Тройник сварной переходный ТЭС',
		'ОСТ 34-10-764-97' => 'Тройник сварной ТЭС',
		'ОСТ 34-10-765-97' => 'Тройник сварной ТЭС',
		'СЕРИЯ 4.903-10'   => 'Тройник тепловых сетей',
		// Переходы
		'ГОСТ 17378-2001'  => 'Переход бесшовный приварной',
		'ГОСТ 22826-1983'  => 'Переход на Ру до 100 МПа',
		'ОСТ 36-22-77'     => 'Переход сварной',
		'ОСТ 34-10-753-97' => 'Переход сварной ТЭС',
		'ОСТ 34.10.754-97' => 'Переход сварной ТЭС',
		'СТО 318.01'       => 'Переход ЦКТИ 318.01',
				// Трубы
		'ГОСТ 8732-1978'  => 'Труба бесшовная г/д',
		'ГОСТ 8734-1975'  => 'Труба бесшовная х/д',
		'ГОСТ 10704-1991' => 'Труба электросварная',
		'ГОСТ 10705-1980' => 'Труба электросварная',
		'ГОСТ 30732-2020' => 'Труба в ППУ',
		'ГОСТ 3262-1975'  => 'Труба ВГП',
		// Опоры
		'ОСТ 36-17-85'    => 'Опора трубопровода',
		// Арматура
		'ГОСТ 33257-2015' => 'Арматура трубопроводная',
		// Днища и заглушки

		'ГОСТ 6533-1978'   => 'Днище эллиптическое отбортованное',
		'ГОСТ 17379-2001'  => 'Заглушка эллиптическая',
		'ГОСТ 22815-1983'  => 'Заглушка на Ру до 100 МПа',
		// Фланцы
		'ГОСТ 33259-2015'   => 'Фланец трубопроводный',
		'ГОСТ 12820-1980'   => 'Фланец плоский приварной',
		'ГОСТ 12821-1980'   => 'Фланец приварной встык',
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
	return $group === 'flange';
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

	return [
		'name'    => promen_series_type_name( $norm_key, $family ),
		'angle'   => $angle,
		'code'    => $code,
		'slug'    => $slug,
		'url'     => trailingslashit( $cat_link ) . 'seriya/' . $slug . '/',
		'pass_id' => 'ПЭ-' . $code . ( $dn !== '' ? '-' . $dn : '' ),
	];
}

/**
 * Репрезентативный товар серии (медианный DN — как DN 100 в дизайне).
 * Кэш в транзиенте; слаг серии = translit(norm)+angle.
 */
function promen_series_representative( string $series_slug ): int {
	$cache_key = 'promen_series_rep_' . md5( $series_slug );
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
		'ГОСТ 33259-2015'   => [ 'Фланцы на PN до 250', 'Действующий · заменил ГОСТ 12820/12821/12836-80', 'Базовый' ],
		'ГОСТ 12820-1980'   => [ 'Фланцы плоские приварные', 'Заменён ГОСТ 33259-2015 · для ремонтных программ', 'Заменён' ],
		'ГОСТ 12821-1980'   => [ 'Фланцы приварные встык', 'Заменён ГОСТ 33259-2015 · воротниковые', 'Заменён' ],
		'ГОСТ 28759.2-2022' => [ 'Фланцы сосудов и аппаратов', 'Плоские приварные для сосудов под давлением', 'Действует' ],
		// Крепёж
		'ГОСТ 22032-1976' => [ 'Шпильки, ввинчиваемый конец 1d', 'Класс точности В', 'Действует' ],
		'ГОСТ 22043-1976' => [ 'Шпильки, ввинчиваемый конец 2d', 'Класс точности В', 'Действует' ],
		'ГОСТ 7798-1970'  => [ 'Болты с шестигранной головкой', 'Класс точности В', 'Действует' ],
		'ГОСТ 7805-1970'  => [ 'Болты с шестигранной головкой', 'Класс точности А', 'Действует' ],
		'ГОСТ 7795-1970'  => [ 'Болты с уменьшенной головкой', 'Класс точности В, с подголовком', 'Действует' ],
		'ГОСТ 7808-1970'  => [ 'Болты с уменьшенной головкой', 'Класс точности А', 'Действует' ],
		'ГОСТ 15590-1970' => [ 'Болты с уменьшенной головкой', 'Класс точности С, с подголовком', 'Действует' ],
		'ГОСТ 15591-1970' => [ 'Болты с уменьшенной головкой', 'Класс точности С', 'Действует' ],
		'ГОСТ 7796-1970'  => [ 'Болты с уменьшенной головкой', 'Класс точности В', 'Действует' ],
		'ГОСТ 9066-1970'  => [ 'Шпильки фланцевых соединений', 'Среда 0…650 °C', 'Действует' ],
		'ГОСТ 10494-1980' => [ 'Шпильки фланцевые Ру 10–100', 'Высокое давление', 'Действует' ],
		'ОСТ 26-2040-96'  => [ 'Шпильки фланцевые аппаратов', 'Отраслевой стандарт', 'Действует' ],
		'ГОСТ 10602-1994' => [ 'Болты с диаметром резьбы >48', 'Крупный крепёж', 'Действует' ],
		'ГОСТ 5915-1970'  => [ 'Гайки шестигранные', 'Класс точности В', 'Действует' ],
		'ГОСТ 5927-1970'  => [ 'Гайки шестигранные', 'Класс точности А', 'Действует' ],
		'ГОСТ 11371-1978' => [ 'Шайбы', 'Технические условия', 'Действует' ],
		'ГОСТ 6402-1970'  => [ 'Шайбы пружинные', 'Стопорение резьбовых соединений', 'Действует' ],
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
