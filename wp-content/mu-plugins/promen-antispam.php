<?php
/**
 * Plugin Name: PROM-EN Antispam
 * Description: Защита форм заявок от ботов. Два независимых слоя:
 *              (1) Яндекс SmartCaptcha в невидимом режиме — проверка токена
 *                  на стороне сервера; включается, как только заданы ключи;
 *              (2) серверные эвристики по ссылкам — работают всегда и ловят
 *                  типовую рассылку («промокод + ссылка», латиница без единой
 *                  кириллической буквы, ссылка в поле «Имя» / «Компания»).
 *              Подключается к promen-requests.php через фильтр
 *              promen_request_verdict.
 *
 * Ключи SmartCaptcha (Yandex Cloud → SmartCaptcha):
 *   — клиентский ключ: константа PROMEN_SMARTCAPTCHA_KEY или опция promen_smartcaptcha_key;
 *   — серверный ключ:  константа PROMEN_SMARTCAPTCHA_SECRET или опция promen_smartcaptcha_secret.
 * Пока хотя бы одного нет — капча не выводится, формы работают как раньше.
 */

defined( 'ABSPATH' ) || exit;

/** Скрипт виджета и эндпоинт проверки токена (документация Yandex Cloud). */
const PROMEN_CAPTCHA_JS       = 'https://smartcaptcha.cloud.yandex.ru/captcha.js';
const PROMEN_CAPTCHA_VALIDATE = 'https://smartcaptcha.cloud.yandex.ru/validate';

/** Уведомление Яндекса об обработке данных — обязательно, раз шильдик скрыт. */
const PROMEN_CAPTCHA_NOTICE = 'https://yandex.ru/legal/smartcaptcha_notice/';

/* -------------------------------------------------------------------------
 * Ключи и состояние
 * ---------------------------------------------------------------------- */

/** Клиентский ключ: константа важнее опции (её проще держать вне БД). */
function promen_captcha_key(): string {
	if ( defined( 'PROMEN_SMARTCAPTCHA_KEY' ) && '' !== (string) PROMEN_SMARTCAPTCHA_KEY ) {
		return (string) PROMEN_SMARTCAPTCHA_KEY;
	}
	return trim( (string) get_option( 'promen_smartcaptcha_key', '' ) );
}

/** Серверный ключ — им подписывается проверка токена, наружу не отдаётся. */
function promen_captcha_secret(): string {
	if ( defined( 'PROMEN_SMARTCAPTCHA_SECRET' ) && '' !== (string) PROMEN_SMARTCAPTCHA_SECRET ) {
		return (string) PROMEN_SMARTCAPTCHA_SECRET;
	}
	return trim( (string) get_option( 'promen_smartcaptcha_secret', '' ) );
}

/** Капча включается только парой ключей: с одним проверка была бы фикцией. */
function promen_captcha_enabled(): bool {
	return '' !== promen_captcha_key() && '' !== promen_captcha_secret();
}

/**
 * Контейнер виджета + уведомление Яндекса. Ставится в форму перед кнопкой;
 * виджет невидимый, места не занимает — видна только строка уведомления.
 * Разметка статична (в ней только публичный ключ), поэтому спокойно живёт
 * в полностраничном кеше.
 */
function promen_captcha_field(): void {
	if ( ! promen_captcha_enabled() ) {
		return;
	}
	printf(
		'<div class="promen-captcha" data-promen-captcha data-sitekey="%s"></div>' .
		'<p class="promen-captcha-note">Форма защищена Яндекс SmartCaptcha — <a href="%s" target="_blank" rel="noopener nofollow">условия обработки данных</a>.</p>',
		esc_attr( promen_captcha_key() ),
		esc_url( PROMEN_CAPTCHA_NOTICE )
	);
}

/* -------------------------------------------------------------------------
 * Проверка токена
 * ---------------------------------------------------------------------- */

/**
 * Проверка токена на серверах Яндекса.
 *
 * Пустой токен — всегда отказ: иначе бот просто не присылал бы поле.
 * А вот недоступность самого сервиса (сеть, 5xx) трактуем как «пропустить» —
 * так советует документация, и терять живую заявку из-за чужой аварии хуже,
 * чем пропустить письмо спамера: за капчей всё равно стоят эвристики ниже.
 */
function promen_captcha_verify( string $token, string $ip ): bool {
	if ( '' === $token ) {
		return false;
	}
	$res = wp_remote_post( PROMEN_CAPTCHA_VALIDATE, [
		'timeout' => 5,
		'body'    => [
			'secret' => promen_captcha_secret(),
			'token'  => $token,
			'ip'     => $ip,
		],
	] );
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		return true;
	}
	$body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	return is_array( $body ) && 'ok' === ( $body['status'] ?? '' );
}

/* -------------------------------------------------------------------------
 * Эвристики: ссылки там, где их не бывает у заказчика
 * ---------------------------------------------------------------------- */

/** Поля, в которых ссылка не появляется ни при каком сценарии заказа. */
const PROMEN_SPAM_PLAIN_FIELDS = [
	'name', 'company', 'topic', 'product', 'sku', 'standard',
	'dn', 'pn', 'material', 'qty', 'deadline', 'city', 'delivery', 'contact',
];

/**
 * Поля, которые заказчик набирает руками, — только по ним судим о языке
 * заявки. «Тема обращения» и «Доставка» приходят из выпадающих списков
 * с русскими значениями: со спамом в них кириллица есть всегда, и проверка
 * «ни одной русской буквы» по всей заявке молча ломалась бы.
 */
const PROMEN_SPAM_TYPED_FIELDS = [
	'name', 'company', 'product', 'standard', 'material', 'city', 'deadline', 'qty',
];

/**
 * Явные признаки ссылки. Домен «на глаз» (mail.ru, prom-en.com) намеренно
 * не ловим: заказчики пишут почту и в «Имя», и в «Компанию» — ложные
 * срабатывания там дороже пропущенного спама.
 */
const PROMEN_SPAM_LINK_RE   = '~(?:https?://|www\.[a-z0-9-]|\[url|\[link|<a\s)~i';
const PROMEN_SPAM_MARKUP_RE = '~(?:\[url|\[link|<a\s)~i';

/**
 * Возвращает причину, по которой заявка похожа на спам, либо '' для чистой.
 *
 * Правила (по убыванию надёжности):
 *   1. разметка ссылки (HTML/BBCode) в любом поле — заказчики так не пишут;
 *   2. ссылка в служебном поле («Имя», «Компания», «Материал» и т. п.);
 *   3. две и более ссылок в сообщении;
 *   4. ссылка в сообщении, и ни одной русской буквы в том, что набирали
 *      руками, — ровно этот профиль у рассылок «$25,000 promo code … https://…».
 */
function promen_antispam_reason( array $post ): string {
	$join = static function ( array $fields ) use ( $post ): string {
		$out = '';
		foreach ( $fields as $field ) {
			$out .= ' ' . (string) ( $post[ $field ] ?? '' );
		}
		return $out;
	};

	$task  = (string) ( $post['task'] ?? '' );
	$plain = $join( PROMEN_SPAM_PLAIN_FIELDS );
	$typed = $join( PROMEN_SPAM_TYPED_FIELDS ) . ' ' . $task;

	if ( preg_match( PROMEN_SPAM_MARKUP_RE, $plain . ' ' . $task ) ) {
		return 'разметка ссылки в тексте';
	}
	if ( preg_match( PROMEN_SPAM_LINK_RE, $plain ) ) {
		return 'ссылка в служебном поле';
	}

	$links = preg_match_all( PROMEN_SPAM_LINK_RE, $task );
	if ( $links >= 2 ) {
		return 'несколько ссылок в сообщении';
	}
	if ( $links >= 1 && ! preg_match( '~\p{Cyrillic}~u', $typed ) ) {
		return 'ссылка и ни одной кириллической буквы';
	}
	return '';
}

/* -------------------------------------------------------------------------
 * Вердикт по заявке
 * ---------------------------------------------------------------------- */

/**
 * Итог проверки для promen-requests.php:
 *   reject     — показать человеку причину (капча не пройдена, можно повторить);
 *   quarantine — молча ответить «успехом»: бот не поймёт, что отсеян, заявка
 *                ляжет в админку с пометкой «СПАМ», письмо не уйдёт.
 */
add_filter( 'promen_request_verdict', function ( array $verdict, array $post ): array {
	if ( 'accept' !== ( $verdict['action'] ?? 'accept' ) ) {
		return $verdict;
	}

	if ( promen_captcha_enabled() ) {
		$token = sanitize_text_field( wp_unslash( (string) ( $post['smart_token'] ?? '' ) ) );
		if ( ! promen_captcha_verify( $token, (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ) ) {
			return [
				'action'  => 'reject',
				'message' => 'Проверка «вы не робот» не пройдена. Обновите страницу и отправьте форму ещё раз или напишите на ' . PROMEN_REQUEST_EMAIL . '.',
			];
		}
	}

	$reason = promen_antispam_reason( $post );
	if ( '' !== $reason ) {
		return [ 'action' => 'quarantine', 'reason' => $reason ];
	}

	return $verdict;
}, 10, 2 );

/* -------------------------------------------------------------------------
 * Админка: видно, что отсеяно и почему
 * ---------------------------------------------------------------------- */

add_filter( 'manage_promen_request_posts_columns', function ( array $cols ): array {
	$cols['promen_spam'] = 'Спам-фильтр';
	return $cols;
} );

add_action( 'manage_promen_request_posts_custom_column', function ( string $col, int $post_id ): void {
	if ( 'promen_spam' !== $col ) {
		return;
	}
	$reason = (string) get_post_meta( $post_id, '_promen_spam', true );
	echo $reason
		? '<span style="color:#b32d2e;">отсеяно: ' . esc_html( $reason ) . '</span>'
		: '—';
}, 10, 2 );
