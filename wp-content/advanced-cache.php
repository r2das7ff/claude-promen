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
	define( 'PROMEN_CACHE_TTL', 12 * HOUR_IN_SECONDS );
}

/** Кешируем ли этот запрос вообще. */
function promen_cache_eligible(): bool {
	if ( 'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
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

/** Путь к файлу кеша. Хост в ключе — при переезде на боевой домен чужой кеш не подхватится. */
function promen_cache_file(): string {
	$key = md5( ( $_SERVER['HTTP_HOST'] ?? '' ) . '|' . ( $_SERVER['REQUEST_URI'] ?? '/' ) );
	return PROMEN_CACHE_DIR . '/' . substr( $key, 0, 2 ) . '/' . $key . '.html';
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
		}
	}
	return $buffer;
}

if ( ! promen_cache_eligible() ) {
	return;
}

$promen_file = promen_cache_file();
if ( is_readable( $promen_file ) ) {
	$age = time() - (int) @filemtime( $promen_file );
	if ( $age < PROMEN_CACHE_TTL ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-Promen-Cache: HIT' );
		// Возраст отдаём для диагностики: видно, свежая ли отдача.
		header( 'X-Promen-Cache-Age: ' . $age );
		readfile( $promen_file );
		exit;
	}
}

header( 'X-Promen-Cache: MISS' );
ob_start( 'promen_cache_store' );
