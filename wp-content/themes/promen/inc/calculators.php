<?php
/**
 * Калькуляторы: реестр страниц + REST-данные из канон-таблицы каталога.
 *
 * Все расчёты опираются на СОБСТВЕННЫЕ данные канона (wp_promen_catalog_rows),
 * а не на переписанные таблицы ГОСТ: масса и геометрия у позиций уже есть,
 * поэтому каждый результат ведёт на конкретный товар и в заявку.
 *
 * Эндпоинты (GET, публичные, кэш в transient):
 *   /promen/v1/calc/sdt-norms?type=…      — нормативы типа СДТ (с массой)
 *   /promen/v1/calc/sdt-rows?type=…&norm=…— типоразмеры: D, s, [D2,s2], [угол], масса
 *   /promen/v1/calc/flange-norms          — нормативы×типы фланцев
 *   /promen/v1/calc/flange-rows?norm=…    — DN, PN, тип, Dб, n×M, b, масса
 *   /promen/v1/calc/fastener-norms?kind=… — нормативы вида крепежа
 *   /promen/v1/calc/fastener-rows?kind=…&norm=… — M, L, класс, масса 1000 шт
 *   /promen/v1/calc/pipe-rows             — трубы: Dн, s, кг/м
 *   POST /promen/v1/delivery/quote-batch  — доставка партии из нескольких позиций
 *
 * ВАЖНО про массу крепежа: в каноне у метизов масса хранится «за 1000 шт»
 * (стандарт метизных прайсов; поэтому карточка её и не показывает «на штуку»).
 * Калькуляторы используют это значение как кг/1000 шт — деление на 1000 даёт
 * достоверную штучную массу.
 */

defined( 'ABSPATH' ) || exit;

/** Слаги категорий канона по типу СДТ-калькулятора. */
function promen_calc_sdt_types(): array {
	return [
		'otvody'    => 'Отводы',
		'perekhody' => 'Переходы',
		'troyniki'  => 'Тройники',
		'zaglushki' => 'Заглушки',
		'dnishcha'  => 'Днища',
	];
}

/** Виды крепежа → категория канона. */
function promen_calc_fastener_kinds(): array {
	return [
		'bolty'   => 'Болты',
		'gayki'   => 'Гайки',
		'shpilki' => 'Шпильки',
		'shayby'  => 'Шайбы',
		'vinty'   => 'Винты',
	];
}

/**
 * Реестр страниц калькуляторов: slug → карточка для хаба/меню/SEO.
 * Страницы появляются на хабе по мере публикации (как promen_nav_items).
 */
function promen_calc_pages(): array {
	return [
		'ves-sdt' => [
			'num'   => '01',
			'title' => 'Вес деталей трубопровода',
			'desc'  => 'Масса отводов, переходов, тройников, заглушек и днищ по ГОСТ/ОСТ — из живого каталога, с партией и доставкой.',
			'tag'   => 'ОТВОДЫ · ПЕРЕХОДЫ · ТРОЙНИКИ · ЗАГЛУШКИ · ДНИЩА',
		],
		'flancevyy-krepezh' => [
			'num'   => '02',
			'title' => 'Фланцы и крепёж (КОФ)',
			'desc'  => 'Вес фланца и полный комплект крепежа к соединению: шпильки или болты, гайки, шайбы — количество, длина, масса.',
			'tag'   => 'ГОСТ 33259 · КОФ · ШПИЛЬКИ · ГАЙКИ',
		],
		'metizy' => [
			'num'   => '03',
			'title' => 'Метизы: кг ↔ шт',
			'desc'  => 'Перевод количества крепежа в килограммы и обратно по теоретической массе норматива.',
			'tag'   => 'БОЛТЫ · ГАЙКИ · ШПИЛЬКИ · ШАЙБЫ',
		],
		'truby-ves' => [
			'num'   => '04',
			'title' => 'Трубы: метры ↔ тонны',
			'desc'  => 'Вес метра трубы, перевод длины в тоннаж и обратно, площадь окраски и вместимость трубопровода.',
			'tag'   => 'ГОСТ 8732 · 10704 · ВГП',
		],
		'dn-dyuym' => [
			'num'   => '05',
			'title' => 'DN · дюймы · диаметры',
			'desc'  => 'Соответствие условного прохода DN, трубной резьбы в дюймах и наружных диаметров по ГОСТ и EN/ASME.',
			'tag'   => 'DN · NPS · Dн',
		],
		'analogi-staley' => [
			'num'   => '06',
			'title' => 'Аналоги марок стали',
			'desc'  => 'Ближайшие зарубежные аналоги российских марок: EN, ASTM/AISI, DIN — и обратный подбор.',
			'tag'   => 'ГОСТ · EN · ASTM · DIN',
		],
	];
}

/** Слаг страницы калькулятора текущего запроса ('' — не калькулятор). */
function promen_calc_current_slug(): string {
	if ( ! is_page() ) {
		return '';
	}
	$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
	if ( $slug === 'kalkulyatory' || isset( promen_calc_pages()[ $slug ] ) ) {
		return $slug;
	}
	return '';
}

/** URL страницы калькулятора ('' — не опубликована). */
function promen_calc_url( string $slug ): string {
	$page = promen_page( $slug === 'kalkulyatory' ? 'kalkulyatory' : 'kalkulyatory/' . $slug );
	return $page ? (string) get_permalink( $page ) : '';
}

/* ── Кэш ── */

/** Transient-ключ эндпоинта (с версией фильтров — сбрасывается при ребилде канона). */
function promen_calc_cache_key( string $name, array $args = [] ): string {
	if ( function_exists( 'promen_filters_cache_key' ) ) {
		return promen_filters_cache_key( 'calc_' . $name, $args );
	}
	return 'promen_calc_' . $name . '_' . md5( wp_json_encode( $args ) );
}

/* ── Выборки из канона ── */

/**
 * Нормативы категории с числом позиций (учитываются только строки с массой,
 * когда $with_mass — калькулятору без массы считать нечего).
 *
 * @return array<int, array{slug:string,label:string,n:int}>
 */
function promen_calc_norms_for( array $categories, bool $with_mass = true ): array {
	global $wpdb;
	$table = promen_catalog_table_name();
	$ph    = implode( ',', array_fill( 0, count( $categories ), '%s' ) );
	$mass  = $with_mass ? "AND JSON_EXTRACT(payload,'$.mass') IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(payload,'$.mass')) != 'null'" : '';
	// phpcs:ignore WordPress.DB.PreparedSQL
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT norm_slug, COUNT(*) AS n FROM {$table} WHERE category IN ({$ph}) AND norm_slug != '' {$mass} GROUP BY norm_slug ORDER BY n DESC",
		$categories
	), ARRAY_A );

	$out = [];
	foreach ( $rows ?: [] as $r ) {
		$slug = (string) $r['norm_slug'];
		$out[] = [
			'slug'  => $slug,
			'label' => function_exists( 'promen_term_label' ) ? promen_term_label( 'norm', $slug ) : $slug,
			'n'     => (int) $r['n'],
		];
	}
	return $out;
}

/**
 * Payload-строки категории/норматива одним запросом.
 *
 * @return array<int, array<string, mixed>>
 */
function promen_calc_payloads( array $categories, string $norm = '' ): array {
	global $wpdb;
	$table = promen_catalog_table_name();
	$ph    = implode( ',', array_fill( 0, count( $categories ), '%s' ) );
	$args  = $categories;
	$where = "category IN ({$ph})";
	if ( $norm !== '' ) {
		$where .= ' AND norm_slug = %s';
		$args[] = $norm;
	}
	// phpcs:ignore WordPress.DB.PreparedSQL
	$json = $wpdb->get_col( $wpdb->prepare( "SELECT payload FROM {$table} WHERE {$where}", $args ) );

	$out = [];
	foreach ( $json ?: [] as $p ) {
		$doc = json_decode( (string) $p, true );
		if ( is_array( $doc ) ) {
			$out[] = $doc;
		}
	}
	return $out;
}

/** float|null из payload-значения. */
function promen_calc_num( $v ): ?float {
	if ( $v === null || $v === '' || ! is_numeric( $v ) ) {
		return null;
	}
	return (float) $v;
}

/** Число из форматированной ячейки реестра («21,3» / «45°» / «—»). */
function promen_calc_cell_num( array $doc, string $key ): ?float {
	$raw = str_replace( [ ',', '°', ' ' ], [ '.', '', '' ], trim( (string) ( $doc['cells'][ $key ] ?? '' ) ) );
	return is_numeric( $raw ) ? (float) $raw : null;
}

/* ── REST ── */

add_action( 'rest_api_init', function () {
	$get = static function ( string $route, callable $cb, array $args = [] ): void {
		register_rest_route( 'promen/v1', $route, [
			'methods'             => 'GET',
			'callback'            => $cb,
			'permission_callback' => '__return_true',
			'args'                => $args,
		] );
	};

	$get( '/calc/sdt-norms', 'promen_rest_calc_sdt_norms', [ 'type' => [ 'required' => true ] ] );
	$get( '/calc/sdt-rows', 'promen_rest_calc_sdt_rows', [
		'type' => [ 'required' => true ],
		'norm' => [ 'required' => true ],
	] );
	$get( '/calc/flange-norms', 'promen_rest_calc_flange_norms' );
	$get( '/calc/flange-rows', 'promen_rest_calc_flange_rows', [ 'norm' => [ 'required' => true ] ] );
	$get( '/calc/fastener-norms', 'promen_rest_calc_fastener_norms', [ 'kind' => [ 'required' => true ] ] );
	$get( '/calc/fastener-rows', 'promen_rest_calc_fastener_rows', [
		'kind' => [ 'required' => true ],
		'norm' => [ 'required' => true ],
	] );
	$get( '/calc/pipe-rows', 'promen_rest_calc_pipe_rows' );

	register_rest_route( 'promen/v1', '/delivery/quote-batch', [
		'methods'             => 'POST',
		'callback'            => 'promen_rest_delivery_quote_batch',
		'permission_callback' => '__return_true',
	] );
} );

/** Ответ с браузерным кэшем: данные меняются только при ребилде канона. */
function promen_calc_response( array $data ): WP_REST_Response {
	$res = new WP_REST_Response( $data, 200 );
	$res->set_headers( [ 'Cache-Control' => 'public, max-age=900' ] );
	return $res;
}

function promen_rest_calc_sdt_norms( WP_REST_Request $request ) {
	$type = (string) $request->get_param( 'type' );
	if ( ! isset( promen_calc_sdt_types()[ $type ] ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_type' ], 400 );
	}
	$ck     = promen_calc_cache_key( 'sdt_norms', [ $type ] );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$cached = promen_calc_norms_for( [ $type ] );
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'norms' => $cached ] );
}

/**
 * Типоразмеры СДТ: дедуп по геометрии (сталь на массу канона не влияет),
 * приоритет строкам с массой. Ключи компактные — списки бывают до ~1000 строк.
 */
function promen_rest_calc_sdt_rows( WP_REST_Request $request ) {
	$type = (string) $request->get_param( 'type' );
	$norm = sanitize_title( (string) $request->get_param( 'norm' ) );
	if ( ! isset( promen_calc_sdt_types()[ $type ] ) || $norm === '' ) {
		return new WP_REST_Response( [ 'error' => 'bad_request' ], 400 );
	}

	$ck     = promen_calc_cache_key( 'sdt_rows', [ $type, $norm ] );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$rows = [];
		foreach ( promen_calc_payloads( [ $type ], $norm ) as $doc ) {
			$d = promen_calc_num( $doc['d'] ?? null );
			$s = promen_calc_num( $doc['s'] ?? null );
			if ( $d === null && $type !== 'dnishcha' ) {
				continue;
			}
			$row = [
				'd'   => $d,
				's'   => $s,
				'd2'  => promen_calc_num( $doc['d2'] ?? null ),
				's2'  => promen_calc_num( $doc['s2'] ?? null ),
				'a'   => promen_calc_num( $doc['angle'] ?? null ),
				'h'   => promen_calc_cell_num( $doc, 'height' ),
				'r'   => promen_calc_cell_num( $doc, 'radius' ),
				'e'   => trim( (string) ( $doc['cells']['exec'] ?? '' ) ),
				'dn'  => promen_calc_num( $doc['dn'] ?? null ),
				'm'   => promen_calc_num( $doc['mass'] ?? null ),
				'pid' => (int) ( $doc['product_id'] ?? 0 ),
				'u'   => (string) ( $doc['url'] ?? '' ),
				't'   => (string) ( $doc['title'] ?? '' ),
				'sku' => (string) ( $doc['sku'] ?? '' ),
			];
			$key = implode( '|', [ $row['d'], $row['s'], $row['d2'], $row['s2'], $row['a'], $row['e'], $row['h'] ] );
			// Дубли размеров между сталями: оставляем строку с массой.
			if ( ! isset( $rows[ $key ] ) || ( $rows[ $key ]['m'] === null && $row['m'] !== null ) ) {
				$rows[ $key ] = $row;
			}
		}
		$rows = array_values( $rows );
		usort( $rows, static fn( $a, $b ) => [ $a['d'], $a['s'], $a['d2'], $a['a'] ] <=> [ $b['d'], $b['s'], $b['d2'], $b['a'] ] );
		$cached = $rows;
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'rows' => $cached ] );
}

/** Категории фланцев в каноне. */
function promen_calc_flange_categories(): array {
	return [ 'flancy', 'flancy-01', 'flancy-11', 'flancy-plosk', 'flancy-vorot' ];
}

function promen_rest_calc_flange_norms() {
	$ck     = promen_calc_cache_key( 'flange_norms' );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$cached = promen_calc_norms_for( promen_calc_flange_categories() );
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'norms' => $cached ] );
}

/**
 * Строки фланцев: DN, PN (МПа), тип, геометрия и разобранный крепёж n×M.
 * Дедуп по (тип, DN, PN, b): исполнения с одинаковой геометрией схлопываются.
 */
function promen_rest_calc_flange_rows( WP_REST_Request $request ) {
	$norm = sanitize_title( (string) $request->get_param( 'norm' ) );
	if ( $norm === '' ) {
		return new WP_REST_Response( [ 'error' => 'bad_request' ], 400 );
	}

	$ck     = promen_calc_cache_key( 'flange_rows_v3', [ $norm ] );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$rows = [];
		foreach ( promen_calc_payloads( promen_calc_flange_categories(), $norm ) as $doc ) {
			$dn = promen_calc_num( $doc['dn'] ?? null );
			$pn = promen_calc_num( $doc['pn'] ?? null );
			if ( $dn === null || $pn === null ) {
				continue;
			}
			$bolts = trim( (string) ( $doc['cells']['bolts'] ?? '' ) );
			$n     = null;
			$m     = null;
			if ( preg_match( '/^(\d+)\s*[×x]\s*M([\d.,]+)$/u', $bolts, $mm ) ) {
				$n = (int) $mm[1];
				$m = (float) str_replace( ',', '.', $mm[2] );
			}
			$row = [
				'dn'   => $dn,
				'pn'   => $pn,
				'type' => trim( (string) ( $doc['cells']['flange_type'] ?? '' ) ),
				'd'    => promen_calc_num( $doc['d'] ?? null ),
				'db'   => promen_calc_cell_num( $doc, 'dbolt' ),
				'n'    => $n,
				'M'    => $m,
				'b'    => promen_calc_cell_num( $doc, 'b' ),
				'm'    => promen_calc_num( $doc['mass'] ?? null ),
				'pid'  => (int) ( $doc['product_id'] ?? 0 ),
				'u'    => (string) ( $doc['url'] ?? '' ),
				't'    => (string) ( $doc['title'] ?? '' ),
				'sku'  => (string) ( $doc['sku'] ?? '' ),
			];

			// Санити крепёжной геометрии — склейки импорта (36×M12 на DN50,
			// Dб вплотную к Dн) не годятся для подбора комплекта:
			//  1) шаг болтов π·Dб/n не меньше 2,2 резьбы (иначе гайки перекрываются);
			//  2) болтовые отверстия внутри тела фланца: Dб + M + 8 ≤ Dн;
			//  3) разумные диапазоны толщины/числа/резьбы.
			// У бракованной строки геометрию гасим, фланец с массой оставляем —
			// комплект тогда честно «уточняется у инженера».
			$sane = ( $row['b'] === null || ( $row['b'] >= 6 && $row['b'] <= 120 ) )
				&& ( $row['n'] === null || ( $row['n'] >= 4 && $row['n'] <= 64 ) )
				&& ( $row['M'] === null || ( $row['M'] >= 6 && $row['M'] <= 64 ) );
			if ( $sane && $row['db'] !== null && $row['n'] !== null && $row['M'] !== null ) {
				$sane = ( M_PI * $row['db'] / $row['n'] ) >= 2.2 * $row['M'];
			}
			if ( $sane && $row['db'] !== null && $row['d'] !== null ) {
				$sane = ( $row['db'] + ( $row['M'] ?? 0 ) + 8 ) <= $row['d'];
			}
			if ( ! $sane ) {
				$row['db'] = null;
				$row['n']  = null;
				$row['M']  = null;
				$row['b']  = null;
			}

			// Один типоразмер = (тип, DN, PN); из дублей берём строку с полным
			// комплектом данных: геометрия крепежа (2 балла) + масса (1 балл).
			$score = static fn( array $r ): int =>
				( $r['n'] !== null && $r['M'] !== null && $r['b'] !== null ? 2 : 0 ) + ( $r['m'] !== null ? 1 : 0 );
			$key = implode( '|', [ $row['type'], $row['dn'], $row['pn'] ] );
			if ( ! isset( $rows[ $key ] ) || $score( $row ) > $score( $rows[ $key ] ) ) {
				$rows[ $key ] = $row;
			}
		}
		$rows = array_values( $rows );
		usort( $rows, static fn( $a, $b ) => [ $a['type'], $a['dn'], $a['pn'] ] <=> [ $b['type'], $b['dn'], $b['pn'] ] );
		$cached = $rows;
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'rows' => $cached ] );
}

function promen_rest_calc_fastener_norms( WP_REST_Request $request ) {
	$kind = (string) $request->get_param( 'kind' );
	if ( ! isset( promen_calc_fastener_kinds()[ $kind ] ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_kind' ], 400 );
	}
	$ck     = promen_calc_cache_key( 'fastener_norms', [ $kind ] );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$cached = promen_calc_norms_for( [ $kind ] );
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'norms' => $cached ] );
}

/**
 * Теоретическая масса ОДНОГО метиза, кг — геометрическая модель по ГОСТ
 * (стержень πd²/4·L, гайка/шайба через d³). Точность ±10% — достаточно,
 * чтобы различать интерпретации единиц (разрыв 1000×), см. ниже.
 */
function promen_calc_fastener_theory_kg( string $kind, float $m, ?float $l ): float {
	$len = $l !== null && $l > 0 ? $l : 10 * $m; // без длины (гайки/шайбы) — не используется
	switch ( $kind ) {
		case 'gayki':
			return 8.505e-6 * $m * $m * $m;
		case 'shayby':
			return 2.367e-6 * $m * $m * $m;
		case 'bolty':
		case 'vinty':
			// Стержень + шестигранная головка (~половина гайки).
			return M_PI * $m * $m / 4 * $len * 7.85e-6 + 4.25e-6 * $m * $m * $m;
		default: // shpilki
			return M_PI * $m * $m / 4 * $len * 7.85e-6 * 0.96;
	}
}

/**
 * Масса метиза в каноне приезжала из РАЗНЫХ прайсов в разных единицах:
 * у болтов/гаек «кг за 1000 шт» (она же «г/шт»), у шпилек ГОСТ 9066 —
 * «кг за штуку». Нормализуем к канону «кг за 1000 шт»: выбираем ту
 * интерпретацию, которая попадает в полосу 0,2–5× геометрической модели.
 * Вне обеих полос (мусор вроде «гайки M160») — null.
 */
function promen_calc_fastener_kg1000( string $kind, float $m, ?float $l, float $value ): ?float {
	if ( $value <= 0 || $m <= 0 ) {
		return null;
	}
	$theory1000 = promen_calc_fastener_theory_kg( $kind, $m, $l ) * 1000;
	if ( $theory1000 <= 0 ) {
		return $value;
	}
	$as1000 = $value / $theory1000;         // значение — кг/1000 шт?
	$asPiece = $value * 1000 / $theory1000; // значение — кг/шт?
	if ( $as1000 >= 0.2 && $as1000 <= 5 ) {
		return $value;
	}
	if ( $asPiece >= 0.2 && $asPiece <= 5 ) {
		return $value * 1000;
	}
	return null;
}

/**
 * Метизы: резьба M, длина L, класс и масса 1000 шт (см. шапку файла).
 * Дедуп по (M, L, класс) — сталь/покрытие на теоретическую массу не влияют.
 */
function promen_rest_calc_fastener_rows( WP_REST_Request $request ) {
	$kind = (string) $request->get_param( 'kind' );
	$norm = sanitize_title( (string) $request->get_param( 'norm' ) );
	if ( ! isset( promen_calc_fastener_kinds()[ $kind ] ) || $norm === '' ) {
		return new WP_REST_Response( [ 'error' => 'bad_request' ], 400 );
	}

	$ck     = promen_calc_cache_key( 'fastener_rows_v2', [ $kind, $norm ] );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$rows = [];
		foreach ( promen_calc_payloads( [ $kind ], $norm ) as $doc ) {
			$thread = trim( (string) ( $doc['cells']['thread'] ?? '' ) );
			$m      = null;
			if ( preg_match( '/M?([\d.,]+)/u', $thread, $mm ) ) {
				$m = (float) str_replace( ',', '.', $mm[1] );
			}
			if ( $m === null ) {
				continue;
			}
			$mass = promen_calc_num( $doc['mass'] ?? null );
			$l    = promen_calc_cell_num( $doc, 'length' );
			$row  = [
				'M'   => $m,
				'l'   => $l,
				'cls' => trim( (string) ( $doc['cells']['strength'] ?? '' ) ),
				'kg'  => $mass === null ? null : promen_calc_fastener_kg1000( $kind, $m, $l, $mass ), // кг за 1000 шт
				'pid' => (int) ( $doc['product_id'] ?? 0 ),
				'u'   => (string) ( $doc['url'] ?? '' ),
				't'   => (string) ( $doc['title'] ?? '' ),
				'sku' => (string) ( $doc['sku'] ?? '' ),
			];
			$key = implode( '|', [ $row['M'], $row['l'], $row['cls'] ] );
			if ( ! isset( $rows[ $key ] ) || ( $rows[ $key ]['kg'] === null && $row['kg'] !== null ) ) {
				$rows[ $key ] = $row;
			}
		}
		$rows = array_values( $rows );
		usort( $rows, static fn( $a, $b ) => [ $a['M'], (float) $a['l'], $a['cls'] ] <=> [ $b['M'], (float) $b['l'], $b['cls'] ] );
		$cached = $rows;
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'rows' => $cached ] );
}

/** Трубы: Dн × s → кг/м. Один компактный список по всем трубным категориям. */
function promen_rest_calc_pipe_rows() {
	$ck     = promen_calc_cache_key( 'pipe_rows' );
	$cached = get_transient( $ck );
	if ( ! is_array( $cached ) ) {
		$rows = [];
		foreach ( promen_calc_payloads( [ 'truby-es', 'truby-bsh', 'truby-vgp' ] ) as $doc ) {
			$d  = promen_calc_num( $doc['d'] ?? null );
			$s  = promen_calc_num( $doc['s'] ?? null );
			$kg = promen_calc_num( $doc['mass'] ?? null ); // кг/м
			if ( $d === null || $s === null || $kg === null ) {
				continue;
			}
			$key = $d . '|' . $s . '|' . (string) ( $doc['norm_slug'] ?? '' );
			if ( isset( $rows[ $key ] ) ) {
				continue;
			}
			$rows[ $key ] = [
				'd'    => $d,
				's'    => $s,
				'kg'   => $kg,
				'norm' => (string) ( $doc['norm'] ?? '' ),
				'pid'  => (int) ( $doc['product_id'] ?? 0 ),
				'u'    => (string) ( $doc['url'] ?? '' ),
			];
		}
		$rows = array_values( $rows );
		usort( $rows, static fn( $a, $b ) => [ $a['d'], $a['s'] ] <=> [ $b['d'], $b['s'] ] );
		$cached = $rows;
		set_transient( $ck, $cached, 15 * MINUTE_IN_SECONDS );
	}
	return promen_calc_response( [ 'rows' => $cached ] );
}

/**
 * Доставка партии: несколько позиций одним расчётом «Деловых Линий».
 * Груз собирается сервером из товаров (вес/габариты не принимаются с фронта).
 */
function promen_rest_delivery_quote_batch( WP_REST_Request $request ) {
	if ( promen_dellin_appkey() === '' ) {
		return new WP_REST_Response( [ 'error' => 'not_configured' ], 503 );
	}

	// Общий с одиночным расчётом лимит: не больше 10 расчётов в минуту с IP.
	$ip      = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );
	$rl_key  = 'promen_dlrl_' . md5( $ip );
	$rl_hits = (int) get_transient( $rl_key );
	if ( $rl_hits >= 10 ) {
		return new WP_REST_Response( [ 'error' => 'rate_limited' ], 429 );
	}
	set_transient( $rl_key, $rl_hits + 1, MINUTE_IN_SECONDS );

	$city_code = (string) $request->get_param( 'city_code' );
	if ( ! preg_match( '/^\d{13,25}$/', $city_code ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_city' ], 400 );
	}

	$items = $request->get_param( 'items' );
	if ( ! is_array( $items ) || ! $items || count( $items ) > 40 ) {
		return new WP_REST_Response( [ 'error' => 'bad_items' ], 400 );
	}

	// Суммарный груз: вес и объём складываются, габарит места — максимум по позициям.
	$total_w = 0.0;
	$total_v = 0.0;
	$qty_sum = 0;
	$max_len = 0.0;
	$max_sid = 0.0;
	foreach ( $items as $item ) {
		$pid = (int) ( $item['product_id'] ?? 0 );
		$qty = max( 1, min( 100000, (int) ( $item['qty'] ?? 1 ) ) );
		$product = $pid ? wc_get_product( $pid ) : null;
		if ( ! $product || (float) $product->get_weight() <= 0 ) {
			return new WP_REST_Response( [ 'error' => 'no_weight', 'product_id' => $pid ], 422 );
		}
		$cargo = promen_delivery_cargo( $product, $qty );
		if ( ! $cargo ) {
			return new WP_REST_Response( [ 'error' => 'no_weight', 'product_id' => $pid ], 422 );
		}
		$total_w += (float) $cargo['totalWeight'];
		$total_v += (float) $cargo['totalVolume'];
		$qty_sum += $qty;
		$max_len  = max( $max_len, (float) $cargo['length'] );
		$max_sid  = max( $max_sid, (float) $cargo['width'] );
	}
	if ( $total_w > 20000 ) {
		return new WP_REST_Response( [ 'error' => 'too_heavy' ], 422 );
	}

	$places = max( 1, min( $qty_sum, 20 ) );
	$cargo  = [
		'quantity'    => $places,
		'length'      => round( $max_len, 2 ),
		'width'       => round( $max_sid, 2 ),
		'height'      => round( $max_sid, 2 ),
		'weight'      => round( $total_w / $places, 1 ),
		'totalWeight' => round( $total_w, 1 ),
		'totalVolume' => max( round( $total_v, 3 ), 0.01 ),
		'hazardClass' => 0,
	];

	return promen_delivery_quote_for_cargo( $cargo, $city_code );
}

/**
 * Общий хвост расчёта доставки: кэш по корзине веса/объёма, запрос к ДЛ,
 * разбор цены и срока. Вынесен из promen_rest_delivery_quote 1:1.
 */
function promen_delivery_quote_for_cargo( array $cargo, string $city_code ): WP_REST_Response {
	$ck     = 'promen_dlq_' . md5( implode( '|', [
		$city_code,
		promen_delivery_weight_bucket( $cargo['totalWeight'] ),
		ceil( $cargo['totalVolume'] * 20 ) / 20,
		$cargo['quantity'],
	] ) );
	$cached = get_transient( $ck );
	if ( is_array( $cached ) ) {
		return new WP_REST_Response( $cached, 200 );
	}

	$derival = promen_dellin_derival();
	$dated   = $derival;
	$dated['produceDate'] = promen_delivery_produce_date();
	if ( ( $dated['variant'] ?? '' ) === 'address' ) {
		$dated['time'] = [ 'worktimeStart' => '9:00', 'worktimeEnd' => '18:00' ];
	}

	$body = static function ( array $derival ) use ( $city_code, $cargo ): array {
		return [
			'appkey'   => promen_dellin_appkey(),
			'delivery' => [
				'deliveryType' => [ 'type' => 'auto' ],
				'derival'      => $derival,
				'arrival'      => [
					'variant' => 'terminal',
					'city'    => $city_code,
				],
			],
			'cargo'    => $cargo,
		];
	};

	$json = promen_dellin_post( '/v2/calculator.json', $body( $dated ) );

	if ( ! is_wp_error( $json )
		&& (int) ( $json['metadata']['status'] ?? 0 ) !== 200
		&& preg_match( '/дат|время|produceDate/ui', promen_dellin_error_text( $json ) ) ) {
		$json = promen_dellin_post( '/v2/calculator.json', $body( $derival ) );
	}

	if ( is_wp_error( $json ) ) {
		error_log( 'promen delivery quote: ' . $json->get_error_message() );
		return new WP_REST_Response( [ 'error' => 'api_unavailable' ], 502 );
	}

	$status = (int) ( $json['metadata']['status'] ?? 0 );
	if ( $status !== 200 || empty( $json['data'] ) ) {
		$err = promen_dellin_error_text( $json );
		error_log( 'promen delivery quote [' . $status . ']: ' . $err );
		$code = preg_match( '/терминал|населённ|населен|город/ui', $err ) ? 'no_terminal' : 'api_error';
		return new WP_REST_Response( [ 'error' => $code, 'detail' => $err ], 502 );
	}

	$data  = $json['data'];
	$price = (float) ( $data['price'] ?? 0 );
	if ( $price <= 0 ) {
		$price = (float) ( $data['intercity']['price'] ?? 0 );
	}
	if ( $price <= 0 ) {
		error_log( 'promen delivery quote: пустая цена в ответе' );
		return new WP_REST_Response( [ 'error' => 'api_error' ], 502 );
	}

	$eta_raw = (string) ( $data['orderDates']['giveoutFromOspReceiver']
		?? ( $data['orderDates']['arrivalToOspReceiver'] ?? '' ) );
	$eta_ts  = $eta_raw !== '' ? strtotime( $eta_raw ) : false;

	$out = [
		'ok'       => true,
		'price'    => round( $price ),
		'terminal' => (string) ( $data['arrival']['terminal'] ?? '' ),
		'eta'      => $eta_ts ? wp_date( 'd.m', $eta_ts ) : '',
		'weight'   => $cargo['totalWeight'],
		'volume'   => $cargo['totalVolume'],
	];
	set_transient( $ck, $out, 12 * HOUR_IN_SECONDS );
	return new WP_REST_Response( $out, 200 );
}
