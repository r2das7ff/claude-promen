<?php
/**
 * Отказ базы данных: что получает посетитель, когда MySQL недоступен.
 *
 * WordPress подключает этот файл сам (wpdb::db_connect → db-error.php), когда
 * не может соединиться с MySQL: до плагинов, темы и хуков. Функций WordPress
 * здесь нет — только константы wp-config и то, что загружено до базы, в том
 * числе advanced-cache.php.
 *
 * Инцидент 23–24.09.2026: больше десяти часов сайт отвечал голой страницей
 * «Error establishing a database connection» с кодом 500 на всех адресах,
 * которых не было в кеше, — включая robots.txt и отправку заявок. Теперь:
 *
 *  1. Заявка с формы (admin-post.php, action=promen_request) сохраняется в
 *     файл вне public_html и уходит письмом. Заявки при сбое не теряются.
 *  2. Страница есть в полностраничном кеше — отдаём её, даже устаревшую,
 *     с плашкой «ограниченный режим» и кодом 200.
 *  3. API и AJAX — JSON 503 с Retry-After.
 *  4. Остальное — страница техработ с телефоном и формой обратной связи,
 *     503 + Retry-After: поисковики считают это временной недоступностью и
 *     не выкидывают страницы из индекса, в отличие от 500.
 *
 * Разбор заявок из хранилища после восстановления базы — `wp promen leads-spool`
 * (mu-plugins/promen-health.php), в админке о них напоминает уведомление.
 */

defined( 'ABSPATH' ) || exit;

if ( 'cli' === PHP_SAPI ) {
	fwrite( STDERR, "Error establishing a database connection\n" );
	exit( 1 );
}

/*
 * Ни один ответ отсюда не должен попасть в полностраничный кеш. advanced-cache.php
 * уже открыл буфер promen_cache_store, и без запрета устаревшая копия с плашкой
 * «ограниченный режим» легла бы в кеш с кодом 200 и жила бы там неделю после
 * того, как база поднимется. Поймано на стенде 24.09.2026.
 */
if ( ! defined( 'PROMEN_CACHE_SKIP' ) ) {
	define( 'PROMEN_CACHE_SKIP', true );
}
while ( ob_get_level() > 0 ) {
	ob_end_clean();
}

const PROMEN_DBE_PHONE      = '+7 (351) 217-00-99';
const PROMEN_DBE_PHONE_HREF = '+73512170099';
const PROMEN_DBE_EMAIL      = 'zakaz@prom-en.com';

/** Поля формы заявки — те же, что PROMEN_REQUEST_FIELDS в mu-plugins/promen-requests.php. */
const PROMEN_DBE_FIELDS = [
	'name', 'company', 'topic', 'product', 'sku', 'standard', 'dn', 'pn',
	'material', 'qty', 'deadline', 'city', 'delivery', 'contact',
];

const PROMEN_DBE_PRESETS = [
	'kp'       => 'Запрос КП',
	'tz'       => 'Техническое задание',
	'calc'     => 'Расчёт стоимости',
	'solution' => 'Подбор решения',
	'product'  => 'Запрос позиции',
	'docs'     => 'Запрос документации',
	'delivery' => 'Заявка на доставку',
	'project'  => 'Обсуждение проекта',
	'contact'  => 'Общий запрос',
];

const PROMEN_DBE_FILE_EXT  = [ 'pdf', 'dwg', 'dxf', 'png', 'jpg', 'jpeg' ];
const PROMEN_DBE_FILE_SIZE = 15 * 1024 * 1024;

/** Метка «когда база отказала последний раз» — её показывает /wp-json/promen/v1/health. */
@touch( WP_CONTENT_DIR . '/cache/promen-db-down' );

/**
 * Хранилище заявок: рядом с public_html, не внутри — из веба недоступно.
 * Тот же путь вычисляет promen_leads_spool_dir() в mu-plugins/promen-health.php.
 */
function promen_dbe_spool_dir(): string {
	$dir = dirname( rtrim( ABSPATH, '/\\' ) ) . '/leads-spool';
	if ( is_dir( $dir ) || @mkdir( $dir, 0700, true ) ) {
		return $dir;
	}
	$dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/promen-leads-spool';
	return ( is_dir( $dir ) || @mkdir( $dir, 0700, true ) ) ? $dir : '';
}

function promen_dbe_path(): string {
	$path = (string) strtok( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), '?' );
	return '' !== $path ? $path : '/';
}

function promen_dbe_esc( string $s ): string {
	return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' );
}

function promen_dbe_is_bot(): bool {
	return (bool) preg_match( '/bot|crawl|spider|slurp|yandex|google|bing|baidu|petal|mail\.ru|http/i', (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
}

function promen_dbe_json( int $code, array $body ): void {
	http_response_code( $code );
	header( 'Content-Type: application/json; charset=UTF-8' );
	header( 'Cache-Control: no-store, private' );
	if ( 503 === $code ) {
		header( 'Retry-After: 300' );
	}
	echo json_encode( $body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
}

/** Страница-сообщение в стиле сайта, без внешних ресурсов. */
function promen_dbe_page( int $code, string $title, string $body_html ): void {
	http_response_code( $code );
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'Cache-Control: no-store, private' );
	header( 'X-Robots-Tag: noindex' );
	if ( 503 === $code ) {
		header( 'Retry-After: 300' );
	}
	if ( 'HEAD' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		exit;
	}
	echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">'
		. '<meta name="viewport" content="width=device-width,initial-scale=1">'
		. '<meta name="robots" content="noindex"><title>' . promen_dbe_esc( $title ) . ' — Промышленная Энергетика</title>'
		. '<style>'
		. ':root{--bg:#f4f5f7;--card:#fff;--ink:#1c2330;--mute:#5b6472;--line:#dde1e6;--acc:#0b5cad}'
		. '*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
		. 'background:var(--bg);color:var(--ink);font:16px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}'
		. 'main{width:100%;max-width:600px;margin:16px;padding:32px;background:var(--card);border:1px solid var(--line)}'
		. '.brand{font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-size:13px;color:var(--mute);margin:0 0 20px}'
		. 'h1{font-size:24px;line-height:1.25;margin:0 0 12px}p{margin:0 0 12px}a{color:var(--acc)}'
		. '.contacts{margin:20px 0;padding:16px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line)}'
		. '.contacts a{font-size:20px;font-weight:600;text-decoration:none}.mute{color:var(--mute);font-size:14px}'
		. 'form{display:grid;gap:10px;margin-top:8px}input,textarea{font:inherit;padding:10px 12px;border:1px solid var(--line);width:100%}'
		. 'textarea{min-height:90px}button{font:inherit;font-weight:600;padding:12px;border:0;background:var(--ink);color:#fff;cursor:pointer}'
		. 'label.c{display:flex;gap:8px;align-items:flex-start;font-size:13px;color:var(--mute)}label.c input{width:auto;margin-top:3px}'
		. '.hp{position:absolute;left:-9999px}'
		. '</style></head><body><main><p class="brand">Завод «Промышленная Энергетика»</p>'
		. '<h1>' . promen_dbe_esc( $title ) . '</h1>' . $body_html . '</main></body></html>';
	exit;
}

function promen_dbe_contacts_html(): string {
	return '<div class="contacts"><p><a href="tel:' . PROMEN_DBE_PHONE_HREF . '">' . PROMEN_DBE_PHONE . '</a></p>'
		. '<p><a href="mailto:' . PROMEN_DBE_EMAIL . '">' . PROMEN_DBE_EMAIL . '</a></p>'
		. '<p class="mute">Пн–Пт, 08:00–17:00 (Челябинск, МСК+2)</p></div>';
}

/** Страница техработ с короткой формой: заявка уйдёт в хранилище, как с любой формы сайта. */
function promen_dbe_maintenance(): void {
	$back = promen_dbe_esc( promen_dbe_path() );
	promen_dbe_page(
		503,
		'Сайт временно работает с перебоями',
		'<p>Мы уже восстанавливаем работу. Попробуйте <a href="' . $back . '">обновить страницу</a> через несколько минут.</p>'
		. '<p>Отдел продаж на связи — звоните или пишите:</p>'
		. promen_dbe_contacts_html()
		. '<p><strong>Или оставьте контакт — перезвоним:</strong></p>'
		. '<form method="post" action="/wp-admin/admin-post.php">'
		. '<input type="hidden" name="action" value="promen_request"><input type="hidden" name="preset" value="contact">'
		. '<input class="hp" type="text" name="company_url" tabindex="-1" autocomplete="off" aria-hidden="true">'
		. '<input type="text" name="name" placeholder="Имя" autocomplete="name">'
		. '<input type="text" name="contact" placeholder="Телефон или email" required autocomplete="tel">'
		. '<textarea name="task" placeholder="Что нужно: изделие, стандарт, количество"></textarea>'
		. '<label class="c"><input type="checkbox" name="pd_consent" value="1" required>'
		. '<span>Согласен на обработку персональных данных в соответствии с <a href="/privacy-policy/">политикой</a></span></label>'
		. '<button type="submit">Отправить</button></form>'
	);
}

/**
 * Готовая страница из полностраничного кеша (advanced-cache.php), даже
 * протухшая: при лежащей базе устаревший каталог лучше пустого экрана.
 * Ищем во всех версиях темы — после выкладки каталог v<отпечаток> меняется,
 * а прежние убирает только WordPress на init, до которого сейчас не дойти.
 */
function promen_dbe_stale_file(): string {
	$dir  = defined( 'PROMEN_CACHE_DIR' ) ? PROMEN_CACHE_DIR : WP_CONTENT_DIR . '/cache/promen-page';
	$host = (string) ( $_SERVER['HTTP_HOST'] ?? '' );
	$uris = [];
	if ( function_exists( 'promen_cache_target' ) ) {
		$target = promen_cache_target();
		if ( $target ) {
			$uris[] = $target[0];
		}
	}
	$uris[] = (string) ( $_SERVER['REQUEST_URI'] ?? '/' );
	$uris[] = promen_dbe_path(); // фильтр/поиск — отдаём сам раздел
	foreach ( array_unique( $uris ) as $uri ) {
		$key   = md5( $host . '|' . $uri );
		$files = glob( $dir . '/v*/' . substr( $key, 0, 2 ) . '/' . $key . '.html' );
		if ( ! $files ) {
			continue;
		}
		usort( $files, static function ( $a, $b ) {
			return (int) @filemtime( $b ) <=> (int) @filemtime( $a );
		} );
		foreach ( $files as $file ) {
			if ( is_readable( $file ) && (int) @filesize( $file ) > 512 ) {
				return $file;
			}
		}
	}
	return '';
}

function promen_dbe_serve_stale( string $file ): void {
	$html = (string) @file_get_contents( $file );
	if ( '' === $html ) {
		return;
	}
	// Роботу плашку не показываем: попадёт в сниппет.
	if ( ! promen_dbe_is_bot() ) {
		$bar  = '<div role="status" style="position:relative;z-index:2147483000;background:#1c2330;color:#fff;'
			. 'font:14px/1.45 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;padding:10px 16px;text-align:center">'
			. 'Сайт работает в ограниченном режиме: поиск и фильтры каталога временно недоступны. Заявки принимаются, '
			. 'телефон <a href="tel:' . PROMEN_DBE_PHONE_HREF . '" style="color:#fff;text-decoration:underline">' . PROMEN_DBE_PHONE . '</a>, '
			. '<a href="mailto:' . PROMEN_DBE_EMAIL . '" style="color:#fff;text-decoration:underline">' . PROMEN_DBE_EMAIL . '</a></div>';
		$out = preg_replace_callback( '/<body\b[^>]*>/i', static function ( $m ) use ( $bar ) {
			return $m[0] . $bar;
		}, $html, 1 );
		if ( is_string( $out ) ) {
			$html = $out;
		}
	}
	http_response_code( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'Cache-Control: no-store, private' );
	header( 'X-Promen-Cache: STALE-DB-DOWN' );
	if ( 'HEAD' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		echo $html;
	}
	exit;
}

/* ─── Заявки ─────────────────────────────────────────────────────────── */

function promen_dbe_clean( $val, int $max ): string {
	$val = is_scalar( $val ) ? (string) $val : '';
	// Не-UTF-8 (старые скрипты шлют cp1251) под флагом /u превратил бы поле в пустую строку.
	if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $val, 'UTF-8' ) ) {
		$val = (string) mb_convert_encoding( $val, 'UTF-8', 'Windows-1251' );
	}
	$val = strip_tags( $val );
	$val = (string) preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $val );
	$val = trim( $val );
	return function_exists( 'mb_substr' ) ? mb_substr( $val, 0, $max, 'UTF-8' ) : substr( $val, 0, $max );
}

/** Origin (или Referer) с того же хоста — как promen_request_same_origin(). */
function promen_dbe_same_origin(): bool {
	$site = strtolower( preg_replace( '/^www\.|:\d+$/i', '', (string) ( $_SERVER['HTTP_HOST'] ?? '' ) ) );
	foreach ( [ 'HTTP_ORIGIN', 'HTTP_REFERER' ] as $header ) {
		$val = (string) ( $_SERVER[ $header ] ?? '' );
		if ( '' === $val || 'null' === $val ) {
			continue;
		}
		$host = strtolower( preg_replace( '/^www\./i', '', (string) parse_url( $val, PHP_URL_HOST ) ) );
		return '' !== $site && $host === $site;
	}
	return false;
}

/** Проверка токена SmartCaptcha, если серверный ключ задан константой в wp-config. */
function promen_dbe_captcha(): string {
	if ( ! defined( 'PROMEN_SMARTCAPTCHA_SECRET' ) || '' === (string) PROMEN_SMARTCAPTCHA_SECRET ) {
		return 'unverified';
	}
	$token = (string) ( $_POST['smart_token'] ?? '' );
	if ( '' === $token ) {
		return 'failed';
	}
	if ( ! function_exists( 'curl_init' ) ) {
		return 'unverified';
	}
	$ch = curl_init( 'https://smartcaptcha.cloud.yandex.ru/validate' );
	curl_setopt_array( $ch, [
		CURLOPT_POST           => true,
		CURLOPT_POSTFIELDS     => http_build_query( [
			'secret' => (string) PROMEN_SMARTCAPTCHA_SECRET,
			'token'  => $token,
			'ip'     => (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ),
		] ),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 4,
		CURLOPT_CONNECTTIMEOUT => 3,
	] );
	$raw = curl_exec( $ch );
	curl_close( $ch );
	$res = is_string( $raw ) ? json_decode( $raw, true ) : null;
	if ( ! is_array( $res ) ) {
		return 'unverified';
	}
	return 'ok' === ( $res['status'] ?? '' ) ? 'ok' : 'failed';
}

/**
 * Письмо о заявке. SMTP Яндекса — если его ящик задан константами в wp-config
 * (PROMEN_SMTP_USER / PROMEN_SMTP_PASS, как в promen-smtp.php). Основные
 * настройки SMTP лежат в базе и сейчас недоступны. Без констант — mail():
 * IP хостинга нет в SPF домена, письмо может уйти в спам, поэтому заявка в
 * любом случае сначала записывается в хранилище.
 */
function promen_dbe_mail( string $subject, string $text, string $reply_to ): string {
	if ( defined( 'PROMEN_SMTP_USER' ) && defined( 'PROMEN_SMTP_PASS' )
		&& '' !== (string) PROMEN_SMTP_USER && '' !== (string) PROMEN_SMTP_PASS
		&& is_readable( ABSPATH . WPINC . '/PHPMailer/PHPMailer.php' ) ) {
		try {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$m = new PHPMailer\PHPMailer\PHPMailer( true );
			$m->isSMTP();
			$m->Host       = 'smtp.yandex.ru';
			$m->Port       = 465;
			$m->SMTPAuth   = true;
			$m->SMTPSecure = 'ssl';
			$m->Username   = (string) PROMEN_SMTP_USER;
			$m->Password   = (string) PROMEN_SMTP_PASS;
			$m->CharSet    = 'UTF-8';
			$m->Timeout    = 15;
			$m->setFrom( (string) PROMEN_SMTP_USER, 'Промышленная Энергетика' );
			$m->addAddress( PROMEN_DBE_EMAIL );
			if ( '' !== $reply_to ) {
				$m->addReplyTo( $reply_to );
			}
			$m->Subject = $subject;
			$m->Body    = $text;
			$m->send();
			return 'smtp';
		} catch ( \Throwable $e ) {
			// падаем на mail()
		}
	}
	$headers = "From: =?UTF-8?B?" . base64_encode( 'Промышленная Энергетика' ) . "?= <no-reply@prom-en.com>\r\n"
		. ( '' !== $reply_to ? "Reply-To: {$reply_to}\r\n" : '' )
		. "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit";
	$ok = @mail( PROMEN_DBE_EMAIL, '=?UTF-8?B?' . base64_encode( $subject ) . '?=', $text, $headers );
	return $ok ? 'mail' : 'failed';
}

function promen_dbe_handle_lead(): void {
	$ajax = ! empty( $_POST['promen_ajax'] );
	$fail = static function ( string $msg, int $code = 400 ) use ( $ajax ): void {
		if ( $ajax ) {
			promen_dbe_json( $code, [ 'success' => false, 'data' => [ 'message' => $msg ] ] );
		}
		promen_dbe_page( $code, 'Заявка не отправлена', '<p>' . promen_dbe_esc( $msg ) . '</p>' . promen_dbe_contacts_html() );
	};
	$done = static function () use ( $ajax ): void {
		if ( $ajax ) {
			promen_dbe_json( 200, [ 'success' => true, 'data' => [ 'message' => 'ok' ] ] );
		}
		$back = (string) ( $_SERVER['HTTP_REFERER'] ?? '/' );
		$back = promen_dbe_same_origin() ? $back : '/';
		promen_dbe_page( 200, 'Заявка принята', '<p>Спасибо! Менеджер свяжется с вами в рабочее время.</p>'
			. '<p>Если вопрос срочный — позвоните:</p>' . promen_dbe_contacts_html()
			. '<p><a href="' . promen_dbe_esc( $back ) . '">← Вернуться на сайт</a></p>' );
	};

	if ( ! promen_dbe_same_origin() ) {
		$fail( 'Сессия устарела — обновите страницу и отправьте форму ещё раз.', 403 );
	}
	// Honeypot: бот получает «успех», в хранилище ничего не пишем.
	if ( ! empty( $_POST['company_url'] ) ) {
		$done();
	}
	$contact = promen_dbe_clean( $_POST['contact'] ?? '', 200 );
	if ( '' === $contact ) {
		$fail( 'Укажите email или телефон для ответа на запрос.' );
	}
	if ( empty( $_POST['pd_consent'] ) ) {
		$fail( 'Для отправки запроса требуется согласие на обработку персональных данных.' );
	}

	$spool = promen_dbe_spool_dir();
	if ( '' === $spool ) {
		$fail( 'Сайт временно не принимает заявки. Напишите нам на ' . PROMEN_DBE_EMAIL . ' или позвоните ' . PROMEN_DBE_PHONE . '.', 503 );
	}

	// Не чаще одной заявки в минуту с IP и не больше 2000 файлов: хранилище не должно съесть диск.
	$ip    = (string) ( $_SERVER['REMOTE_ADDR'] ?? '0' );
	$rl    = $spool . '/.rl-' . md5( $ip );
	$rl_mt = @filemtime( $rl );
	if ( $rl_mt && time() - $rl_mt < 60 ) {
		$fail( 'Заявка уже принята. Подождите минуту перед повторной отправкой.', 429 );
	}
	if ( count( (array) glob( $spool . '/*.json' ) ) >= 2000 ) {
		$fail( 'Сайт временно не принимает заявки. Напишите нам на ' . PROMEN_DBE_EMAIL . ' или позвоните ' . PROMEN_DBE_PHONE . '.', 503 );
	}
	@touch( $rl );

	$fields = [];
	foreach ( PROMEN_DBE_FIELDS as $key ) {
		$val = promen_dbe_clean( $_POST[ $key ] ?? '', 500 );
		if ( '' !== $val ) {
			$fields[ $key ] = $val;
		}
	}
	$task   = promen_dbe_clean( $_POST['task'] ?? '', 5000 );
	$preset = preg_replace( '/[^a-z]/', '', strtolower( (string) ( $_POST['preset'] ?? '' ) ) );
	if ( ! isset( PROMEN_DBE_PRESETS[ $preset ] ) ) {
		$preset = 'kp';
	}

	$id = gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) );

	// Вложение: тот же whitelist и лимит, что в promen_request_attachment(). Молча терять чертёж нельзя.
	$attachment = null;
	if ( ! empty( $_FILES['attachment'] ) && is_array( $_FILES['attachment'] ) && UPLOAD_ERR_NO_FILE !== (int) $_FILES['attachment']['error'] ) {
		$file = $_FILES['attachment'];
		if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
			$fail( 'Не удалось загрузить файл. Попробуйте ещё раз или отправьте без вложения.' );
		}
		if ( (int) $file['size'] > PROMEN_DBE_FILE_SIZE ) {
			$fail( 'Файл больше 15 МБ. Сожмите его или отправьте напрямую на ' . PROMEN_DBE_EMAIL . '.' );
		}
		$original = preg_replace( '/[^\w.\-() ]+/u', '_', (string) $file['name'] );
		$ext      = strtolower( pathinfo( (string) $original, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, PROMEN_DBE_FILE_EXT, true ) ) {
			$fail( 'Допустимые форматы вложения: PDF, DWG, DXF, PNG, JPG.' );
		}
		$stored = $id . '.' . $ext;
		if ( ! @move_uploaded_file( (string) $file['tmp_name'], $spool . '/' . $stored ) ) {
			$fail( 'Не удалось сохранить файл. Отправьте его напрямую на ' . PROMEN_DBE_EMAIL . '.' );
		}
		@chmod( $spool . '/' . $stored, 0600 );
		$attachment = [ 'file' => $stored, 'original' => (string) $original, 'size' => (int) $file['size'] ];
	}

	$pick = static function ( string $post_key, string $cookie_key ): string {
		$val = (string) ( $_POST[ $post_key ] ?? '' );
		return '' !== $val ? $val : (string) ( $_COOKIE[ $cookie_key ] ?? '' );
	};
	$captcha = promen_dbe_captcha();
	$linky   = (bool) preg_match( '~(?:https?://|www\.[a-z0-9-]|\[url|\[link|<a\s)~i', implode( "\n", $fields ) . "\n" . $task );

	$lead = [
		'id'          => $id,
		'received_at' => gmdate( 'c' ),
		'reason'      => 'db-down',
		'preset'      => $preset,
		'fields'      => $fields,
		'task'        => $task,
		'attribution' => [
			'ym_client_id' => substr( (string) preg_replace( '/\D/', '', $pick( 'ym_client_id', '_ym_uid' ) ), 0, 40 ),
			'yclid'        => substr( (string) preg_replace( '/[^A-Za-z0-9_-]/', '', $pick( 'yclid', 'promen_yclid' ) ), 0, 64 ),
		],
		'referer'     => promen_dbe_clean( $_SERVER['HTTP_REFERER'] ?? '', 1000 ),
		'ip'          => promen_dbe_clean( $ip, 64 ),
		'ua'          => promen_dbe_clean( $_SERVER['HTTP_USER_AGENT'] ?? '', 500 ),
		'via'         => $ajax ? 'modal' : 'page',
		'captcha'     => $captcha,
		'suspect'     => 'failed' === $captcha || $linky,
		'attachment'  => $attachment,
		'mail'        => 'pending',
	];

	$path = $spool . '/' . $id . '.json';
	$json = json_encode( $lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	if ( ! is_string( $json ) || false === @file_put_contents( $path, $json, LOCK_EX ) ) {
		$fail( 'Сайт временно не принимает заявки. Напишите нам на ' . PROMEN_DBE_EMAIL . ' или позвоните ' . PROMEN_DBE_PHONE . '.', 503 );
	}
	@chmod( $path, 0600 );

	// Подозрительные (капча, ссылки) лежат в хранилище, но письмом не идут — как карантин promen-antispam.
	if ( ! $lead['suspect'] ) {
		$label = PROMEN_DBE_PRESETS[ $preset ];
		$lines = [ 'Заявка принята во время сбоя базы данных сайта.', 'Тип: ' . $label ];
		foreach ( $fields as $key => $val ) {
			$lines[] = $key . ': ' . $val;
		}
		if ( '' !== $task ) {
			$lines[] = '';
			$lines[] = $task;
		}
		$lines[] = '';
		$lines[] = 'Страница: ' . $lead['referer'];
		if ( $attachment ) {
			$lines[] = 'Вложение «' . $attachment['original'] . '» сохранено на сервере: leads-spool/' . $attachment['file'];
		}
		$lines[] = 'Запись заявки: leads-spool/' . $id . '.json (в «Заявки КП» и Битрикс перенести после восстановления: wp promen leads-spool).';
		$reply   = filter_var( $contact, FILTER_VALIDATE_EMAIL ) ? $contact : '';
		$lead['mail'] = promen_dbe_mail( '[Сбой сайта] ' . $label . ' — ' . $contact, implode( "\n", $lines ), $reply );
		@file_put_contents( $path, json_encode( $lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ), LOCK_EX );
	}

	$done();
}

/* ─── Разбор запроса ─────────────────────────────────────────────────── */

$promen_dbe_method = strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) );
$promen_dbe_uri    = (string) ( $_SERVER['REQUEST_URI'] ?? '/' );

try {
	if ( 'POST' === $promen_dbe_method && 'promen_request' === ( $_POST['action'] ?? '' ) ) {
		promen_dbe_handle_lead();
	}

	// Зеркало www → основной домен: обычно это делает WordPress, но без базы он не дойдёт.
	$promen_dbe_host = (string) ( $_SERVER['HTTP_HOST'] ?? '' );
	if ( 0 === stripos( $promen_dbe_host, 'www.' ) && in_array( $promen_dbe_method, [ 'GET', 'HEAD' ], true ) ) {
		header( 'Location: https://' . substr( $promen_dbe_host, 4 ) . $promen_dbe_uri, true, 301 );
		exit;
	}

	$promen_dbe_path = promen_dbe_path();
	if ( 0 === strpos( $promen_dbe_path, '/wp-json' ) || isset( $_GET['rest_route'] )
		|| false !== strpos( $promen_dbe_path, 'admin-ajax.php' ) || false !== strpos( $promen_dbe_path, 'admin-post.php' ) ) {
		promen_dbe_json( 503, [
			'code'    => 'db_unavailable',
			'message' => 'Сервис временно недоступен. Позвоните ' . PROMEN_DBE_PHONE . ' или напишите на ' . PROMEN_DBE_EMAIL . '.',
			'data'    => [ 'status' => 503 ],
		] );
	}

	if ( in_array( $promen_dbe_method, [ 'GET', 'HEAD' ], true )
		&& 0 !== strpos( $promen_dbe_path, '/wp-admin' ) && 0 !== strpos( $promen_dbe_path, '/wp-login' ) ) {
		$promen_dbe_file = promen_dbe_stale_file();
		if ( '' !== $promen_dbe_file ) {
			promen_dbe_serve_stale( $promen_dbe_file );
		}
	}
} catch ( \Throwable $promen_dbe_e ) {
	// Что бы ни сломалось выше — посетитель получает страницу с телефоном, а не пустой экран.
	error_log( 'PROM-EN db-error.php: ' . $promen_dbe_e->getMessage() );
}

promen_dbe_maintenance();
