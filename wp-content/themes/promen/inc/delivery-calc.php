<?php
/**
 * Калькулятор доставки: серверный прокси к API «Деловых Линий».
 *
 * Ключ приложения не должен попадать в браузер, поэтому фронт ходит только
 * в наши REST-эндпоинты, а наружу (api.dellin.ru) ходит сервер:
 *   GET  /wp-json/promen/v1/delivery/cities?q=…   — автодополнение городов
 *   POST /wp-json/promen/v1/delivery/quote        — расчёт стоимости и срока
 *   POST /wp-json/promen/v1/delivery/quote-custom — свободный расчёт: груз
 *        задаёт человек, а не каталог (/kalkulyatory/dostavka/)
 *
 * Ключ: env PROMEN_DELLIN_APPKEY (docker-compose / окружение хостинга)
 * либо константа PROMEN_DELLIN_APPKEY в wp-config.php.
 *
 * Лимиты ДЛ — 1600 запросов/час на приложение, поэтому:
 * подсказки городов кешируются на неделю, расчёты — на 12 часов
 * (по корзинам веса/объёма), запросы посетителей ограничены по IP.
 */

defined( 'ABSPATH' ) || exit;

const PROMEN_DELLIN_API = 'https://api.dellin.ru';

function promen_dellin_appkey(): string {
	$key = (string) getenv( 'PROMEN_DELLIN_APPKEY' );
	if ( $key === '' && defined( 'PROMEN_DELLIN_APPKEY' ) ) {
		$key = (string) PROMEN_DELLIN_APPKEY;
	}
	return trim( $key );
}

/**
 * Точка отправления. Вариант «address» работает без справочника терминалов
 * (цена включает забор груза от площадки — верхняя честная оценка);
 * при желании фильтром можно перейти на ['variant' => 'terminal',
 * 'terminalID' => …] — тогда цена станет «терминал → терминал».
 */
function promen_dellin_derival(): array {
	return apply_filters( 'promen_dellin_derival', [
		'variant' => 'address',
		'address' => [ 'search' => 'Челябинская обл., Челябинск, Орджоникидзе, 37' ],
	] );
}

/**
 * Доставка доступна для товара? Нужен настроенный ключ, а масса должна быть
 * достоверной и «на штуку»: у крепежа масса ненадёжна, у труб хранится
 * кг/м — обе группы мимо. Без ключа модал остаётся обычной формой заявки.
 */
function promen_delivery_available( int $product_id ): bool {
	if ( promen_dellin_appkey() === '' ) {
		return false;
	}
	$group = promen_category_group( $product_id );
	if ( ! promen_mass_is_reliable( $group ) || $group === 'pipe' ) {
		return false;
	}
	$product = wc_get_product( $product_id );
	return $product && (float) $product->get_weight() > 0;
}

/** POST к api.dellin.ru; вернёт массив ответа или WP_Error. */
function promen_dellin_post( string $path, array $body, int $timeout = 12 ) {
	$res = wp_remote_post( PROMEN_DELLIN_API . $path, [
		'timeout' => $timeout,
		'headers' => [ 'Content-Type' => 'application/json' ],
		'body'    => wp_json_encode( $body, JSON_UNESCAPED_UNICODE ),
	] );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$json = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $json ) ) {
		return new WP_Error( 'dellin_bad_json', 'Некорректный ответ API' );
	}
	return $json;
}

/** Текст ошибки из ответа ДЛ — для лога и классификации. */
function promen_dellin_error_text( array $json ): string {
	$parts = [];
	foreach ( (array) ( $json['errors'] ?? [] ) as $e ) {
		$parts[] = trim( ( $e['title'] ?? '' ) . ' ' . ( $e['detail'] ?? '' ) );
	}
	return trim( implode( '; ', array_filter( $parts ) ) );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'promen/v1', '/delivery/cities', [
		'methods'             => 'GET',
		'callback'            => 'promen_rest_delivery_cities',
		'permission_callback' => '__return_true',
		'args'                => [
			'q' => [ 'required' => true, 'type' => 'string' ],
		],
	] );
	register_rest_route( 'promen/v1', '/delivery/quote', [
		'methods'             => 'POST',
		'callback'            => 'promen_rest_delivery_quote',
		'permission_callback' => '__return_true',
	] );
	register_rest_route( 'promen/v1', '/delivery/quote-custom', [
		'methods'             => 'POST',
		'callback'            => 'promen_rest_delivery_quote_custom',
		'permission_callback' => '__return_true',
	] );
} );

/**
 * Автодополнение города: прокси к «Поиску населённых пунктов» ДЛ
 * (v2/public/kladr.json). Ответ кешируется: справочник почти статичен.
 */
function promen_rest_delivery_cities( WP_REST_Request $request ) {
	if ( promen_dellin_appkey() === '' ) {
		return new WP_REST_Response( [ 'error' => 'not_configured' ], 503 );
	}
	$q = mb_strtolower( trim( (string) $request->get_param( 'q' ) ) );
	$q = preg_replace( '/\s+/u', ' ', $q );
	if ( mb_strlen( $q ) < 2 || mb_strlen( $q ) > 64 ) {
		return new WP_REST_Response( [ 'cities' => [] ], 200 );
	}

	$ck     = 'promen_dlc_' . md5( $q );
	$cached = get_transient( $ck );
	if ( is_array( $cached ) ) {
		return new WP_REST_Response( [ 'cities' => $cached ], 200 );
	}

	$json = promen_dellin_post( '/v2/public/kladr.json', [
		'appkey' => promen_dellin_appkey(),
		'q'      => $q,
		'limit'  => 8,
	], 8 );
	if ( is_wp_error( $json ) ) {
		error_log( 'promen delivery cities: ' . $json->get_error_message() );
		return new WP_REST_Response( [ 'error' => 'api_unavailable' ], 502 );
	}

	$cities = [];
	foreach ( (array) ( $json['cities'] ?? [] ) as $c ) {
		if ( empty( $c['code'] ) ) {
			continue;
		}
		$cities[] = [
			'code'     => (string) $c['code'],
			'name'     => (string) ( $c['searchString'] ?? $c['aString'] ?? '' ),
			'full'     => (string) ( $c['aString'] ?? '' ),
			'region'   => (string) ( $c['region_name'] ?? '' ),
			'terminal' => ! empty( $c['isTerminal'] ),
		];
	}
	set_transient( $ck, $cities, WEEK_IN_SECONDS );
	return new WP_REST_Response( [ 'cities' => $cities ], 200 );
}

/**
 * Параметры груза для калькулятора из товара и количества.
 * Габарит места — из размеров изделия (D нар., длина L); если их нет,
 * куб из массы стали с коэффициентом пустоты упаковки ×8. Для стальных
 * изделий тариф почти всегда определяет вес (объёмный вес ДЛ 250 кг/м³),
 * поэтому грубость оценки объёма на цену практически не влияет.
 */
function promen_delivery_cargo( WC_Product $product, int $qty ): ?array {
	$w = (float) $product->get_weight();
	if ( $w <= 0 ) {
		return null;
	}
	$dims = promen_get_dims( $product->get_id() );
	$side = (float) ( $dims['outer_diameter'] ?? 0 ) / 1000;
	$len  = (float) ( $dims['length'] ?? ( $dims['length_mm'] ?? 0 ) ) / 1000;
	if ( $side <= 0 && $len <= 0 ) {
		$side = $len = pow( $w / 7850 * 8, 1 / 3 );
	} elseif ( $side <= 0 ) {
		$side = $len / 4;
	} elseif ( $len <= 0 ) {
		$len = $side;
	}
	$len  = min( max( $len, 0.12, $side ), 6 );
	$side = min( max( $side, 0.12 ), 2.4 );

	// Грузовых мест не больше 20: тариф считается от суммарных веса и
	// объёма, а сотни «мест» по штуке на изделие ломают расчёт упаковки.
	$places  = max( 1, min( $qty, 20 ) );
	$total_w = round( $w * $qty, 1 );
	$total_v = max( round( $len * $side * $side * $qty, 3 ), 0.01 );

	return [
		'quantity'    => $places,
		'length'      => round( $len, 2 ),
		'width'       => round( $side, 2 ),
		'height'      => round( $side, 2 ),
		'weight'      => promen_delivery_weight( $total_w / $places ),
		'totalWeight' => promen_delivery_weight( $total_w ),
		'totalVolume' => $total_v,
		'hazardClass' => 0,
	];
}

/**
 * Дата передачи груза: ближайший рабочий день. Без produceDate калькулятор
 * ДЛ не строит график движения (все даты в ответе — null), а срок доставки
 * для посетителя не менее важен, чем цена. Праздники календарь не знает —
 * на этот случай quote делает повторный запрос без даты.
 */
function promen_delivery_produce_date(): string {
	$ts = current_time( 'timestamp' ) + DAY_IN_SECONDS;
	while ( in_array( (int) wp_date( 'N', $ts ), [ 6, 7 ], true ) ) {
		$ts += DAY_IN_SECONDS;
	}
	return wp_date( 'Y-m-d', $ts );
}

/**
 * Вес для калькулятора ДЛ: округление до 0,1 кг с нижней границей.
 *
 * Без границы мелочь вроде отвода 21,3×2 (0,02 кг) после round() уходила в
 * API нулём, и расчёт падал с «необходимо ввести параметры груза» — на
 * калькуляторе СДТ это ловилось на всех малых DN. Тариф от 0,1 до 1 кг
 * одинаков (минимальный), поэтому граница цену не искажает.
 */
function promen_delivery_weight( float $w ): float {
	return max( 0.1, round( $w, 1 ) );
}

/** Корзина веса для ключа кеша: близкие партии получают один расчёт. */
function promen_delivery_weight_bucket( float $w ): float {
	if ( $w <= 100 ) {
		return ceil( $w );
	}
	if ( $w <= 1000 ) {
		return ceil( $w / 5 ) * 5;
	}
	return ceil( $w / 25 ) * 25;
}

function promen_rest_delivery_quote( WP_REST_Request $request ) {
	if ( promen_dellin_appkey() === '' ) {
		return new WP_REST_Response( [ 'error' => 'not_configured' ], 503 );
	}

	// Не больше 10 расчётов в минуту с одного IP: защита ключа и лимитов ДЛ.
	$ip      = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );
	$rl_key  = 'promen_dlrl_' . md5( $ip );
	$rl_hits = (int) get_transient( $rl_key );
	if ( $rl_hits >= 10 ) {
		return new WP_REST_Response( [ 'error' => 'rate_limited' ], 429 );
	}
	set_transient( $rl_key, $rl_hits + 1, MINUTE_IN_SECONDS );

	$product_id = (int) $request->get_param( 'product_id' );
	$qty        = max( 1, min( 100000, (int) $request->get_param( 'qty' ) ) );
	$city_code  = (string) $request->get_param( 'city_code' );
	if ( ! preg_match( '/^\d{13,25}$/', $city_code ) ) {
		return new WP_REST_Response( [ 'error' => 'bad_city' ], 400 );
	}
	if ( ! $product_id || ! promen_delivery_available( $product_id ) ) {
		return new WP_REST_Response( [ 'error' => 'no_weight' ], 422 );
	}

	$cargo = promen_delivery_cargo( wc_get_product( $product_id ), $qty );
	if ( ! $cargo ) {
		return new WP_REST_Response( [ 'error' => 'no_weight' ], 422 );
	}
	if ( $cargo['totalWeight'] > 20000 ) {
		// Сборным грузом такое не возят — это уже выделенная машина.
		return new WP_REST_Response( [ 'error' => 'too_heavy' ], 422 );
	}

	// Кэш, запрос к ДЛ и разбор ответа — общий хвост с расчётом партии
	// (promen_delivery_quote_for_cargo в inc/calculators.php).
	return promen_delivery_quote_for_cargo( $cargo, $city_code );
}

/**
 * Свободный расчёт доставки — /kalkulyatory/dostavka/.
 *
 * В отличие от расчёта по карточке товара (там вес и габариты берутся из
 * каталога и фронту не доверяются) здесь груз задаёт человек: менеджеру нужно
 * посчитать заказ целиком, паллету или произвольный груз, которого в каталоге
 * нет. Поэтому вся арифметика проходит через жёсткие рамки: габарит места
 * 0,05–6 м, вес места до 20 т, не больше 10 строк и 200 мест в строке,
 * суммарно не больше 20 т — выше сборным грузом не возят.
 *
 * Тариф ДЛ считается от суммарных веса и объёма, а габарит нужен для проверки
 * на негабарит, поэтому разные грузоместа сводятся к одной строке: количество
 * мест, наибольший габарит и точные суммы веса и объёма.
 */
function promen_rest_delivery_quote_custom( WP_REST_Request $request ) {
	if ( promen_dellin_appkey() === '' ) {
		return new WP_REST_Response( [ 'error' => 'not_configured' ], 503 );
	}

	// Лимит общий с расчётом по карточке: 10 расчётов в минуту с одного IP.
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

	$rows = $request->get_param( 'places' );
	if ( ! is_array( $rows ) || ! $rows || count( $rows ) > 10 ) {
		return new WP_REST_Response( [ 'error' => 'bad_places' ], 400 );
	}

	$total_w = 0.0;
	$total_v = 0.0;
	$qty_sum = 0;
	$max_l   = 0.0;
	$max_w   = 0.0;
	$max_h   = 0.0;
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			return new WP_REST_Response( [ 'error' => 'bad_places' ], 400 );
		}
		$l   = (float) ( $row['length'] ?? 0 );
		$wd  = (float) ( $row['width'] ?? 0 );
		$h   = (float) ( $row['height'] ?? 0 );
		$wt  = (float) ( $row['weight'] ?? 0 );
		$qty = (int) ( $row['qty'] ?? 1 );
		if ( $l < 0.05 || $wd < 0.05 || $h < 0.05 || $l > 6 || $wd > 6 || $h > 6 ) {
			return new WP_REST_Response( [ 'error' => 'bad_dims' ], 400 );
		}
		if ( $wt <= 0 || $wt > 20000 ) {
			return new WP_REST_Response( [ 'error' => 'bad_weight' ], 400 );
		}
		if ( $qty < 1 || $qty > 200 ) {
			return new WP_REST_Response( [ 'error' => 'bad_qty' ], 400 );
		}
		$total_w += $wt * $qty;
		$total_v += $l * $wd * $h * $qty;
		$qty_sum += $qty;
		$max_l    = max( $max_l, $l );
		$max_w    = max( $max_w, $wd );
		$max_h    = max( $max_h, $h );
	}
	if ( $total_w > 20000 ) {
		return new WP_REST_Response( [ 'error' => 'too_heavy' ], 422 );
	}

	// Объём можно задать вручную: у сложенной в паллету партии он заметно
	// меньше суммы габаритных ящиков, и honest-цифру знает только отправитель.
	$manual_v = (float) $request->get_param( 'volume' );
	if ( $manual_v > 0 ) {
		$total_v = min( max( $manual_v, 0.01 ), 120 );
	}

	// Мест в тарифе не больше 20 — как в расчёте по карточке.
	$places = max( 1, min( $qty_sum, 20 ) );
	$cargo  = [
		'quantity'    => $places,
		'length'      => round( $max_l, 2 ),
		'width'       => round( $max_w, 2 ),
		'height'      => round( $max_h, 2 ),
		'weight'      => promen_delivery_weight( $total_w / $places ),
		'totalWeight' => promen_delivery_weight( $total_w ),
		'totalVolume' => max( round( $total_v, 3 ), 0.01 ),
		'hazardClass' => 0,
	];

	return promen_delivery_quote_for_cargo( $cargo, $city_code, [
		'type'         => (string) $request->get_param( 'type' ),
		'arrival'      => (string) $request->get_param( 'to' ),
		'address'      => mb_substr( trim( (string) $request->get_param( 'address' ) ), 0, 200 ),
		'stated_value' => min( 50000000, max( 0, (float) $request->get_param( 'stated_value' ) ) ),
	] );
}
