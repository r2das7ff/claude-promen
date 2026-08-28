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

/** Кешируем ли этот запрос вообще. */
function promen_cache_eligible(): bool {
	// HEAD пускаем только на чтение готового файла (см. низ файла): начиная
	// с WP 6.8 ядро умеет обрывать рендер шаблона на HEAD, и такой ответ
	// нельзя класть в кеш — под ключом GET-страницы окажется пустое тело.
	if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', [ 'GET', 'HEAD' ], true ) ) {
		return false;
	}
	// Любые параметры мимо кеша: фильтры каталога, поиск, utm, add-to-cart.
	// Они и так закрыты от индексации, а кешировать их — плодить мусор на диске.
	if ( ! empty( $_GET ) || ! empty( $_SERVER['QUERY_STRING'] ) ) {
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

/** Путь к файлу кеша. Хост в ключе — при переезде на боевой домен чужой кеш не подхватится. */
function promen_cache_file(): string {
	$key = md5( ( $_SERVER['HTTP_HOST'] ?? '' ) . '|' . ( $_SERVER['REQUEST_URI'] ?? '/' ) );
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
	// Закрытые от индексации страницы кешировать незачем: это поиск,
	// личный кабинет и параметрические виды.
	if ( false !== stripos( $buffer, 'noindex' ) ) {
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

if ( ! promen_cache_eligible() ) {
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

// Промах на HEAD не кешируем: тело такого ответа ядро может не построить.
if ( $promen_head ) {
	return;
}

header( 'X-Promen-Cache: MISS' );
ob_start( 'promen_cache_store' );
