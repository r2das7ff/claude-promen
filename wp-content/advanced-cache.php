<?php
/**
 * Полностраничный кеш PROM-EN.
 *
 * Подключается из wp-settings.php при `define( 'WP_CACHE', true )` — раньше,
 * чем загружается ядро, плагины и тема. Поэтому здесь нет ни одной функции
 * WordPress: авторизацию определяем по кукам, а не через is_user_logged_in().
 *
 * Зачем: TTFB стенда 1.1–1.8 с, и это генерация HTML. Каталог на 15 407
 * карточек, при обходе в четыре потока сервер дважды ответил 500. Отдача
 * готового файла снимает и то, и другое.
 *
 * Сброс живёт в теме — inc/page-cache.php, здесь его быть не может:
 * хуки WordPress на этом этапе ещё не существуют.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'PROMEN_CACHE_DIR' ) ) {
	define( 'PROMEN_CACHE_DIR', WP_CONTENT_DIR . '/cache/promen-page' );
}
if ( ! defined( 'PROMEN_CACHE_TTL' ) ) {
	// Каталог меняется редко, а сброс делается по событию — потолок нужен
	// только чтобы кеш не залёживался, если событие вдруг потерялось.
	//
	// Неделя, а не 12 часов: за сутки истекал весь каталог сразу, и первый же
	// обход после этого генерировал 15 394 страницы заново. Разбор лога
	// 28.08.2026 показал, что все 5xx на стенде — именно холодная генерация
	// тяжёлых карточек под параллелью. Сброс по событию никуда не делся, он и
	// остаётся основным способом инвалидации.
	define( 'PROMEN_CACHE_TTL', 7 * DAY_IN_SECONDS );
}
if ( ! defined( 'PROMEN_CACHE_STALE' ) ) {
	// Сколько ещё отдавать протухшую страницу, пока её пересобирает другой
	// процесс: без этого в момент истечения TTL все одновременные запросы к
	// одному адресу уходят в генерацию разом.
	define( 'PROMEN_CACHE_STALE', 10 * MINUTE_IN_SECONDS );
}

/**
 * Сторож перебора фасетов: сколько значений выбрано в мультивыборе.
 *
 * Инцидент 23–24.09.2026. С 19:30 ботнет (33 тыс. IP, браузерные UA, без
 * Referer и cookie) перебирал сочетания фасетов вида
 * `/catalog/?gost=a%2Cb%2Cc%2Cd` — 90 703 запроса из 99 671, до пяти в
 * секунду. Такие адреса бесконечны, мимо кеша и каждый — полная генерация
 * с фасетными запросами к канону. MySQL шаред-хостинга захлебнулся, с 20:00
 * сайт отвечал «Error establishing a database connection», в 23:20 хостинг
 * отрезал пользователю БД доступ целиком. robots.txt (`Disallow: /*?`) такие
 * боты не читают.
 *
 * Живому посетителю сочетание из двух и более значений достаётся только от
 * catalog.js (фишки переключаются через API без перезагрузки), то есть
 * браузер у него исполняет JS. Боты перебора JS не исполняют: к API за те же
 * сутки было 37 обращений против 90 тысяч к страницам.
 *
 * Считаем только мультивыбор. Диапазоны и поиск (`dn_min`, `s_max`, `q`)
 * ведут из объявлений Директа — их не трогаем.
 */
function promen_guard_facet_values(): int {
	$n = 0;
	foreach ( [ 'gost', 'steel', 'angle', 'industry' ] as $param ) {
		if ( ! isset( $_GET[ $param ] ) ) {
			continue;
		}
		$vals = is_array( $_GET[ $param ] ) ? $_GET[ $param ] : explode( ',', (string) $_GET[ $param ] );
		foreach ( $vals as $val ) {
			// Вложенный массив наш интерфейс не строит — считаем за сочетание.
			$n += is_array( $val ) ? 2 : ( '' !== trim( (string) $val ) ? 1 : 0 );
		}
	}
	return $n;
}

/** Нужна ли проверка браузера: сочетание фасетов от клиента без нашей cookie. */
function promen_guard_needs_challenge(): bool {
	if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', [ 'GET', 'HEAD' ], true ) ) {
		return false;
	}
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	// API каталога не трогаем: его зовёт catalog.js, а cookie у посетителя,
	// пришедшего на чистую страницу, ещё нет.
	foreach ( [ '/wp-admin', '/wp-json', '/wp-login.php', '/wp-cron.php' ] as $prefix ) {
		if ( 0 === strpos( $uri, $prefix ) ) {
			return false;
		}
	}
	if ( isset( $_GET['rest_route'] ) || promen_guard_facet_values() < 2 ) {
		return false;
	}
	if ( isset( $_COOKIE['pe_js'] ) ) {
		return false;
	}
	foreach ( array_keys( $_COOKIE ) as $name ) {
		if ( 0 === strpos( (string) $name, 'wordpress_logged_in_' ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Проверка браузера без базы и без WordPress: страница ставит cookie
 * скриптом и перезагружается. Человек видит мелькание на долю секунды,
 * бот без JS — 403 на полкилобайта вместо генерации каталога.
 * Защита от петли: без cookie страница не перезагружается, а объясняет.
 */
function promen_guard_challenge(): void {
	$path = (string) strtok( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), '?' );
	$path = htmlspecialchars( '' !== $path ? $path : '/', ENT_QUOTES, 'UTF-8' );

	http_response_code( 403 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'Cache-Control: no-store, private' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'X-Promen-Guard: challenge' );
	if ( 'HEAD' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		exit;
	}

	$help = '<h1>Фильтры каталога работают с включённым JavaScript</h1>'
		. '<p>Включите JavaScript и cookie или откройте <a href="' . $path . '">раздел без фильтров</a>.</p>'
		. '<p>Отдел продаж: <a href="tel:+73512170099">+7 (351) 217-00-99</a>, '
		. '<a href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a></p>';

	echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">'
		. '<meta name="viewport" content="width=device-width,initial-scale=1">'
		. '<meta name="robots" content="noindex,nofollow"><title>Каталог — PROM-EN</title>'
		. '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
		. 'background:#f4f5f7;color:#1c2330;font:16px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}'
		. 'main{max-width:520px;margin:16px;padding:28px;background:#fff;border:1px solid #dde1e6}'
		. 'h1{font-size:20px;margin:0 0 12px}a{color:#0b5cad}</style></head><body><main>'
		. '<p id="pe-wait">Открываем подборку каталога…</p>'
		. '<div id="pe-help" hidden>' . $help . '</div>'
		. '<noscript><style>#pe-wait{display:none}</style>' . $help . '</noscript>'
		. '<script>(function(){var ok=false,n=0;try{'
		. 'document.cookie="pe_js=1; path=/; max-age=2592000; SameSite=Lax"+(location.protocol==="https:"?"; Secure":"");'
		. 'ok=document.cookie.indexOf("pe_js=1")>-1;'
		. 'n=+(sessionStorage.getItem("pe_js_n")||0);sessionStorage.setItem("pe_js_n",n+1);'
		. '}catch(e){}'
		. 'if(ok&&n<2){location.replace(location.href);return;}'
		. 'document.getElementById("pe-wait").hidden=true;document.getElementById("pe-help").hidden=false;'
		. '})();</script></main></body></html>';
	exit;
}

/**
 * Предохранитель базы: не больше PROMEN_RENDER_SLOTS генераций WordPress
 * одновременно.
 *
 * Проверка браузера закрывает приём ночи 23.09, но кеш можно обойти и иначе —
 * любым лишним параметром в адресе. Каждый обход кеша — полная загрузка
 * WordPress и запросы к MySQL, а база на виртуальном хостинге общая: при
 * перегрузке Timeweb сбрасывает права пользователя БД (тикет №12709309 от
 * 23.09.2026). Поэтому число одновременных генераций ограничено сверху при
 * любом трафике: лишние запросы получают сохранённую копию или 503.
 *
 * Слоты — файлы под flock(): замок снимается сам, когда процесс завершается,
 * даже аварийно, — зависших слотов не бывает. Лежат вне каталога страниц,
 * иначе их удалял бы сброс кеша. Не открылся каталог или файл — пропускаем
 * без ограничения: предохранитель не должен сам ронять сайт.
 *
 * Мимо предохранителя: POST (заявки, админка и WP-Cron — их терять нельзя),
 * залогиненные, расчёт доставки (ждёт внешний API, базу почти не трогает).
 * Браузер, уже исполнявший JS сайта (cookie pe_js или _ym_uid Метрики), ждёт
 * слот до 8 секунд, остальные — полторы.
 */
function promen_guard_render_gate(): void {
	if ( 'cli' === PHP_SAPI || 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		return;
	}
	$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '/' );
	foreach ( [ '/wp-admin', '/wp-login.php', '/wp-json/promen/v1/delivery' ] as $prefix ) {
		if ( 0 === strpos( $uri, $prefix ) ) {
			return;
		}
	}
	foreach ( array_keys( $_COOKIE ) as $name ) {
		if ( 0 === strpos( (string) $name, 'wordpress_logged_in_' ) ) {
			return;
		}
	}
	$human = isset( $_COOKIE['pe_js'] ) || isset( $_COOKIE['_ym_uid'] );

	$slots = defined( 'PROMEN_RENDER_SLOTS' ) ? max( 1, (int) PROMEN_RENDER_SLOTS ) : 4;
	$dir   = WP_CONTENT_DIR . '/cache/promen-slots';
	if ( ! is_dir( $dir ) && ! @mkdir( $dir, 0755, true ) && ! is_dir( $dir ) ) {
		return;
	}

	$deadline = microtime( true ) + ( $human ? 8.0 : 1.5 );
	$first    = mt_rand( 0, $slots - 1 );
	do {
		for ( $k = 0; $k < $slots; $k++ ) {
			$fh = @fopen( $dir . '/slot-' . ( ( $first + $k ) % $slots ), 'c' );
			if ( false === $fh ) {
				return;
			}
			if ( flock( $fh, LOCK_EX | LOCK_NB ) ) {
				$GLOBALS['promen_render_slot'] = $fh; // держим до конца запроса
				return;
			}
			fclose( $fh );
		}
		usleep( 200000 );
	} while ( microtime( true ) < $deadline );

	promen_guard_busy();
}

/** Все слоты заняты: сохранённая копия страницы (любой версии темы) или 503. */
function promen_guard_busy(): void {
	$uri  = (string) ( $_SERVER['REQUEST_URI'] ?? '/' );
	$head = 'HEAD' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' );
	header( 'Cache-Control: no-store, private' );
	header( 'X-Promen-Guard: busy' );

	if ( 0 === strpos( $uri, '/wp-json' ) || isset( $_GET['rest_route'] ) ) {
		http_response_code( 503 );
		header( 'Retry-After: 10' );
		header( 'Content-Type: application/json; charset=UTF-8' );
		echo '{"code":"busy","message":"Сервер занят, повторите через несколько секунд.","data":{"status":503}}';
		exit;
	}

	$target = promen_cache_target();
	$path   = (string) strtok( $uri, '?' );
	$key    = md5( ( $_SERVER['HTTP_HOST'] ?? '' ) . '|' . ( $target ? $target[0] : ( '' !== $path ? $path : '/' ) ) );
	$files  = glob( PROMEN_CACHE_DIR . '/v*/' . substr( $key, 0, 2 ) . '/' . $key . '.html' );
	if ( $files ) {
		usort( $files, static function ( $a, $b ) {
			return (int) @filemtime( $b ) <=> (int) @filemtime( $a );
		} );
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-Promen-Cache: STALE-BUSY' );
		if ( ! $head ) {
			readfile( $files[0] );
		}
		exit;
	}

	http_response_code( 503 );
	header( 'Retry-After: 30' );
	header( 'Content-Type: text/html; charset=UTF-8' );
	if ( $head ) {
		exit;
	}
	echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">'
		. '<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">'
		. '<meta http-equiv="refresh" content="20"><title>Сайт перегружен — PROM-EN</title>'
		. '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f4f5f7;'
		. 'color:#1c2330;font:16px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}main{max-width:520px;margin:16px;'
		. 'padding:28px;background:#fff;border:1px solid #dde1e6}h1{font-size:20px;margin:0 0 12px}a{color:#0b5cad}</style></head>'
		. '<body><main><h1>Сайт сейчас перегружен</h1><p>Страница откроется сама через несколько секунд.</p>'
		. '<p>Отдел продаж: <a href="tel:+73512170099">+7 (351) 217-00-99</a>, <a href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a></p>'
		. '</main></body></html>';
	exit;
}

/**
 * Метки рекламы и аналитики. На разметку они не влияют: yclid и ClientID
 * request-modal.js берёт из адреса и cookie сам, на сервере их не читает
 * никто. Поэтому переход по объявлению отдаётся из кеша чистой страницы —
 * раньше каждый клик из Директа был полной генерацией.
 */
function promen_cache_is_tracking_param( string $key ): bool {
	$key = strtolower( $key );
	return 0 === strpos( $key, 'utm_' )
		|| in_array( $key, [ 'yclid', 'ysclid', 'ymclid', 'gclid', 'fbclid', 'etext', 'from', 'openstat', '_openstat', 'roistat' ], true );
}

/**
 * Адрес, под которым страница лежит в кеше, и можно ли её туда класть.
 *
 * — без параметров или только с метками: чистый путь;
 * — ровно один фасет с одним значением (`?gost=gost-17375-2001`,
 *   `?group=otvody`): путь с этим параметром. Таких адресов конечное число
 *   (сотни), а ведут на них ссылки «в реестре» и посадочные Директа;
 * — всё остальное (сочетания, диапазоны, поиск, пагинация с фильтром) —
 *   null, мимо кеша: пространство таких адресов не ограничено.
 *
 * Класть в кеш ответ на адрес с метками нельзя: разметка могла подхватить
 * адрес запроса (og:url, canonical параметрических видов) и под ключом
 * чистой страницы оказались бы чужие метки. Отдавать готовую — можно.
 *
 * @return array{0: string, 1: bool}|null [ адрес ключа, можно ли сохранять ]
 */
function promen_cache_target(): ?array {
	// Считаем один раз, до WordPress: ядро потом прогоняет $_GET через
	// wp_magic_quotes, и при записи в кеш ключ должен совпасть с чтением.
	static $memo = false;
	if ( false !== $memo ) {
		return $memo;
	}
	$memo = promen_cache_target_compute();
	return $memo;
}

/** @return array{0: string, 1: bool}|null */
function promen_cache_target_compute(): ?array {
	$path = (string) strtok( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), '?' );
	if ( '' === $path ) {
		$path = '/';
	}

	$params  = $_GET;
	$tracked = false;
	foreach ( array_keys( $params ) as $key ) {
		if ( promen_cache_is_tracking_param( (string) $key ) ) {
			unset( $params[ $key ] );
			$tracked = true;
		}
	}
	if ( ! $params ) {
		return [ $path, ! $tracked ];
	}

	if ( 1 === count( $params ) ) {
		$key = (string) key( $params );
		$val = reset( $params );
		if ( in_array( $key, [ 'gost', 'steel', 'angle', 'industry', 'group' ], true )
			&& is_string( $val ) && preg_match( '/^[a-z0-9][a-z0-9-]{0,79}$/', $val ) ) {
			return [ $path . '?' . $key . '=' . $val, ! $tracked ];
		}
	}
	return null;
}

/** Кешируем ли этот запрос вообще. */
function promen_cache_eligible(): bool {
	// HEAD пускаем только на чтение готового файла (см. низ файла): начиная
	// с WP 6.8 ядро умеет обрывать рендер шаблона на HEAD, и такой ответ
	// нельзя класть в кеш — под ключом GET-страницы окажется пустое тело.
	if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', [ 'GET', 'HEAD' ], true ) ) {
		return false;
	}
	// Параметры — см. promen_cache_target(): метки и одиночный фасет в кеш,
	// сочетания, поиск и add-to-cart мимо.
	if ( null === promen_cache_target() ) {
		return false;
	}
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	foreach ( [ '/wp-admin', '/wp-login.php', '/wp-json', '/wp-cron.php', '/xmlrpc.php', '/wp-content/' ] as $prefix ) {
		if ( 0 === strpos( $uri, $prefix ) ) {
			return false;
		}
	}
	// Залогиненные, покупатели с корзиной, авторы комментариев — всегда мимо.
	foreach ( array_keys( $_COOKIE ) as $name ) {
		foreach ( [ 'wordpress_logged_in_', 'wp-postpass_', 'comment_author_', 'woocommerce_items_in_cart', 'woocommerce_cart_hash', 'wp_woocommerce_session_' ] as $mark ) {
			if ( 0 === strpos( $name, $mark ) ) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Отпечаток версии темы.
 *
 * Заливка файлов темы кеш не сбрасывает: хуки WordPress при этом не
 * срабатывают, и посетители до истечения TTL видели бы старую вёрстку.
 * Поэтому версия входит в путь кеша — после правки любого PHP темы все
 * запросы автоматически становятся промахами. Стоимость: один glob по inc/
 * и пара stat на запрос, доли миллисекунды против секунды генерации.
 */
function promen_cache_theme_stamp(): int {
	// Сам обход маски запоминаем на минуту в файле-отметке: 122 файла — это
	// сто с лишним stat на каждый запрос, а меняются они только при выкладке.
	// Минута задержки роли не играет: после заливки кеш всё равно сбрасывают.
	$memo = PROMEN_CACHE_DIR . '/.stamp';
	$memo_mt = @filemtime( $memo );
	if ( $memo_mt && time() - $memo_mt < 60 ) {
		$cached = (int) @file_get_contents( $memo );
		if ( $cached > 0 ) {
			return $cached;
		}
	}

	$dir   = WP_CONTENT_DIR . '/themes/promen';
	$stamp = (int) @filemtime( $dir . '/functions.php' );
	// Маска покрывает всё, что рисует разметку. Раньше в ней были только inc/
	// и woocommerce/: правка page-*.php, parts/*.php или front-page.php
	// отпечаток не меняла, и после заливки такого файла страницы отдавали
	// старую вёрстку до истечения TTL. Ловушка выстрелила 28.08.2026 на
	// page-stati.php — картинки не переключались на webp.
	foreach ( [
		'/*.php',
		'/inc/*.php',
		'/inc/category-content/*.php',
		'/parts/*.php',
		'/woocommerce/*.php',
		'/woocommerce/parts/*.php',
		'/woocommerce/parts/category/*.php',
	] as $mask ) {
		foreach ( (array) glob( $dir . $mask ) as $file ) {
			$mtime = (int) @filemtime( $file );
			if ( $mtime > $stamp ) {
				$stamp = $mtime;
			}
		}
	}

	if ( is_dir( PROMEN_CACHE_DIR ) || @mkdir( PROMEN_CACHE_DIR, 0755, true ) || is_dir( PROMEN_CACHE_DIR ) ) {
		@file_put_contents( $memo, (string) $stamp, LOCK_EX );
	}
	return $stamp;
}

/**
 * Путь к файлу кеша. Хост в ключе — при переезде на боевой домен чужой кеш не подхватится.
 * Для чистого адреса ключ тот же, что был до promen_cache_target(): md5(хост|путь).
 */
function promen_cache_file(): string {
	$target = promen_cache_target();
	$key    = md5( ( $_SERVER['HTTP_HOST'] ?? '' ) . '|' . ( $target ? $target[0] : ( $_SERVER['REQUEST_URI'] ?? '/' ) ) );
	return PROMEN_CACHE_DIR . '/v' . promen_cache_theme_stamp() . '/' . substr( $key, 0, 2 ) . '/' . $key . '.html';
}

/**
 * Валидаторы кеша: отметка времени и ETag страницы.
 *
 * Зачем отдельный файл рядом с кешем, а не mtime самого файла: TTL сбрасывает
 * страницу раз в 12 часов, и после перегенерации mtime меняется даже когда
 * HTML остался прежним. Краулер в ответ на новый Last-Modified качает те же
 * 500 КБ заново. Поэтому метка времени переставляется только при изменении
 * содержимого — ключ здесь хеш, а не время записи.
 *
 * Обход 2026-08-28: HTML отдавался вообще без Cache-Control, Last-Modified и
 * ETag, то есть на 15 407 страницах каталога условный запрос был невозможен
 * в принципе и каждый обход перекачивал весь сайт.
 */
function promen_cache_meta_file( string $file ): string {
	return $file . '.meta';
}

/** [ метка времени, ETag ] сохранённой страницы; ETag пустой, если файла нет. */
function promen_cache_meta_read( string $file ): array {
	$raw = @file_get_contents( promen_cache_meta_file( $file ) );
	if ( ! is_string( $raw ) || ! preg_match( '/^(\d+) ([0-9a-f]{32})$/', trim( $raw ), $m ) ) {
		return [ 0, '' ];
	}
	return [ (int) $m[1], $m[2] ];
}

/** Заголовки условного запроса. Возвращает true, если клиенту хватит 304. */
function promen_cache_send_validators( int $ts, string $etag ): bool {
	if ( $ts <= 0 || '' === $etag ) {
		return false;
	}
	header( 'Cache-Control: public, max-age=0, s-maxage=600, stale-while-revalidate=60' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $ts ) . ' GMT' );
	header( 'ETag: "' . $etag . '"' );

	$inm = trim( (string) ( $_SERVER['HTTP_IF_NONE_MATCH'] ?? '' ) );
	if ( '' !== $inm ) {
		// Прокси и браузеры присылают ETag со слабым префиксом и в списке.
		foreach ( explode( ',', $inm ) as $candidate ) {
			if ( trim( $candidate, " \t\"'W/" ) === $etag ) {
				return true;
			}
		}
		// ETag прислали, но он чужой — страница изменилась, дату не смотрим.
		return false;
	}

	$ims = trim( (string) ( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '' ) );
	if ( '' !== $ims ) {
		$since = strtotime( $ims );
		return false !== $since && $since >= $ts;
	}
	return false;
}

/** Обработчик буфера: решает, можно ли сохранить готовую страницу. */
function promen_cache_store( string $buffer ): string {
	// Короткий ответ — почти наверняка ошибка или редирект-заглушка.
	if ( strlen( $buffer ) < 512 ) {
		return $buffer;
	}
	if ( 200 !== http_response_code() ) {
		return $buffer;
	}
	foreach ( headers_list() as $header ) {
		$low = strtolower( $header );
		// Страница выдала куку (сессия, корзина) — она персональная.
		if ( 0 === strpos( $low, 'set-cookie:' ) ) {
			return $buffer;
		}
		if ( 0 === strpos( $low, 'content-type:' ) && false === strpos( $low, 'text/html' ) ) {
			return $buffer;
		}
	}
	$target = promen_cache_target();
	if ( ! $target || ! $target[1] ) {
		return $buffer; // адрес с метками: отдать готовую можно, класть свою — нет
	}
	// Закрытые от индексации страницы кешировать незачем: это поиск и личный
	// кабинет. Исключение — вид с одним фасетом: он под noindex намеренно
	// (canonical на чистый раздел), но адресов таких конечное число, а без
	// кеша каждый заход на них — генерация с фасетными запросами к базе.
	$param_view = false !== strpos( $target[0], '?' );
	if ( ! $param_view && false !== stripos( $buffer, 'noindex' ) ) {
		return $buffer;
	}
	if ( defined( 'PROMEN_CACHE_SKIP' ) && PROMEN_CACHE_SKIP ) {
		return $buffer;
	}

	$file = promen_cache_file();
	$dir  = dirname( $file );
	if ( ! is_dir( $dir ) && ! @mkdir( $dir, 0755, true ) && ! is_dir( $dir ) ) {
		return $buffer;
	}
	// Пишем через временный файл: иначе параллельный запрос прочитает половину.
	$tmp = $file . '.' . getmypid() . '.tmp';
	if ( false !== @file_put_contents( $tmp, $buffer, LOCK_EX ) ) {
		if ( ! @rename( $tmp, $file ) ) {
			@unlink( $tmp );
			return $buffer;
		}
	}

	// Метку времени переставляем только когда изменился HTML: перегенерация
	// по TTL не должна отбирать у краулера право на 304.
	$etag             = md5( $buffer );
	[ $old_ts, $old ] = promen_cache_meta_read( $file );
	$ts               = ( $old === $etag && $old_ts > 0 ) ? $old_ts : time();
	@file_put_contents( promen_cache_meta_file( $file ), $ts . ' ' . $etag, LOCK_EX );
	promen_cache_send_validators( $ts, $etag );

	return $buffer;
}

if ( promen_guard_needs_challenge() ) {
	promen_guard_challenge();
}

if ( ! promen_cache_eligible() ) {
	promen_guard_render_gate();
	return;
}

$promen_head = 'HEAD' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' );
$promen_file = promen_cache_file();
if ( is_readable( $promen_file ) ) {
	$age = time() - (int) @filemtime( $promen_file );

	// Страница протухла, но ещё свежая в пределах окна: пересобирает её один
	// процесс — тот, кто первым поставил замок, — остальные получают прежнюю
	// копию. Иначе в момент истечения TTL к генерации уходят все разом.
	if ( $age >= PROMEN_CACHE_TTL && $age < PROMEN_CACHE_TTL + PROMEN_CACHE_STALE ) {
		$promen_lock = $promen_file . '.lock';
		$promen_lock_mt = @filemtime( $promen_lock );
		if ( $promen_lock_mt && time() - $promen_lock_mt < PROMEN_CACHE_STALE ) {
			$age = 0; // замок держит кто-то другой — отдаём прежнюю копию
		} elseif ( @touch( $promen_lock ) ) {
			// Замок наш: пересобираем ниже как обычный промах.
			$age = PHP_INT_MAX;
		} else {
			$age = 0;
		}
	}

	if ( $age < PROMEN_CACHE_TTL ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-Promen-Cache: HIT' );
		// Возраст отдаём для диагностики: видно, свежая ли отдача.
		header( 'X-Promen-Cache-Age: ' . $age );

		[ $promen_ts, $promen_etag ] = promen_cache_meta_read( $promen_file );
		if ( '' === $promen_etag ) {
			// Файл от прежней версии кеша — метку заводим на лету.
			$promen_etag = (string) @md5_file( $promen_file );
			$promen_ts   = (int) @filemtime( $promen_file );
			if ( '' !== $promen_etag ) {
				@file_put_contents( promen_cache_meta_file( $promen_file ), $promen_ts . ' ' . $promen_etag, LOCK_EX );
			}
		}
		if ( promen_cache_send_validators( $promen_ts, $promen_etag ) ) {
			http_response_code( 304 );
			exit;
		}

		if ( ! $promen_head ) {
			readfile( $promen_file );
		}
		exit;
	}
}

// Дальше — генерация WordPress, то есть запросы к базе: сначала слот.
promen_guard_render_gate();

// Промах на HEAD не кешируем: тело такого ответа ядро может не построить.
if ( $promen_head ) {
	return;
}

header( 'X-Promen-Cache: MISS' );
ob_start( 'promen_cache_store' );
