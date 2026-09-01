<?php
/**
 * Plugin Name: PROM-EN — отправка писем через SMTP
 * Description: Заявки уходят через smtp.yandex.ru, а не через системный mail().
 *
 * Зачем. Почта домена живёт на Яндексе (MX 10 mx.yandex.net), а SPF домена
 * перечисляет только Яндекс, Unisender, cheapsender и rsndr:
 *
 *     v=spf1 include:_spf.yandex.net include:spf.unisender.com
 *            include:_spf.cheapsender.email include:rsndr.ru ~all
 *
 * IP хостинга (92.53.96.169) в этом списке отсутствует. Значит письмо,
 * отправленное системным mail() прямо с сервера, SPF не проходит, DKIM у
 * него тоже нет — и Яндекс, куда адресован zakaz@prom-en.com, кладёт такое
 * в спам или молча отбрасывает. Заявка при этом сохраняется в базе
 * (post_type promen_request), то есть данные не теряются, но отдел продаж
 * о ней не узнаёт. Старый сайт эту проблему решал плагином wp-mail-smtp,
 * при переезде плагин не поехал.
 *
 * Откуда настройки. Заводить новую учётную запись не потребовалось: ящик
 * no-reply@prom-en.com уже был настроен на старом сайте. Его параметры
 * перенесены в опцию promen_smtp, пароль — в том же виде, в каком его
 * хранил wp-mail-smtp: sodium_crypto_secretbox, ключ в promen_smtp_key.
 * В открытом виде пароль существует только внутри этого файла в момент
 * отправки письма и никуда не записывается.
 *
 * Аварийный путь: если опции нет, читаются константы PROMEN_SMTP_USER и
 * PROMEN_SMTP_PASS из wp-config. Нет ни того, ни другого — плагин молчит
 * и письма идут прежним путём, то есть незаполненный конфиг ничего не
 * ломает, просто не чинит.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Параметры подключения. Возвращает [] , если настроить нечем.
 */
function promen_smtp_config(): array {
	static $cfg = null;
	if ( null !== $cfg ) {
		return $cfg;
	}
	$cfg = [];
	$o   = get_option( 'promen_smtp' );

	if ( is_array( $o ) && ! empty( $o['user'] ) && ! empty( $o['pass_enc'] ) ) {
		$pass = promen_smtp_decrypt( (string) $o['pass_enc'], (string) get_option( 'promen_smtp_key' ) );
		if ( '' !== $pass ) {
			$cfg = [
				'host'       => $o['host'] ?: 'smtp.yandex.ru',
				'port'       => (int) ( $o['port'] ?: 465 ),
				'encryption' => $o['encryption'] ?: 'ssl',
				'user'       => (string) $o['user'],
				'pass'       => $pass,
				'from'       => (string) ( $o['from'] ?: $o['user'] ),
				'from_name'  => (string) ( $o['from_name'] ?: 'Промышленная Энергетика' ),
			];
			return $cfg;
		}
	}

	if ( defined( 'PROMEN_SMTP_USER' ) && defined( 'PROMEN_SMTP_PASS' )
		&& '' !== (string) PROMEN_SMTP_USER && '' !== (string) PROMEN_SMTP_PASS ) {
		$cfg = [
			'host'       => 'smtp.yandex.ru',
			'port'       => 465,
			'encryption' => 'ssl',
			'user'       => (string) PROMEN_SMTP_USER,
			'pass'       => (string) PROMEN_SMTP_PASS,
			'from'       => (string) PROMEN_SMTP_USER,
			'from_name'  => 'Промышленная Энергетика',
		];
	}
	return $cfg;
}

/**
 * Расшифровка пароля. Формат тот же, что у wp-mail-smtp: base64 от
 * «nonce + secretbox», ключ хранится base64-строкой.
 */
function promen_smtp_decrypt( string $enc, string $key_b64 ): string {
	if ( '' === $enc || '' === $key_b64 || ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
		return '';
	}
	$key  = base64_decode( $key_b64, true );
	$blob = base64_decode( $enc, true );
	if ( false === $key || false === $blob
		|| strlen( $key ) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES
		|| strlen( $blob ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return '';
	}
	$plain = @sodium_crypto_secretbox_open(
		substr( $blob, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ),
		substr( $blob, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ),
		$key
	);
	return is_string( $plain ) ? $plain : '';
}

/*
 * Отправитель обязан совпадать с ящиком, под которым авторизуемся: Яндекс
 * отвергает письма с чужим From, а если бы и пропустил — подпись DKIM легла
 * бы не на тот домен и проверку у получателя письмо не прошло бы.
 */
add_filter( 'wp_mail_from', function ( $from ) {
	$c = promen_smtp_config();
	return $c ? $c['from'] : $from;
}, 20 );

add_filter( 'wp_mail_from_name', function ( $name ) {
	$c = promen_smtp_config();
	return $c ? $c['from_name'] : $name;
}, 20 );

add_action( 'phpmailer_init', function ( $mailer ) {
	$c = promen_smtp_config();
	if ( ! $c ) {
		return;
	}
	$mailer->isSMTP();
	$mailer->Host       = $c['host'];
	$mailer->Port       = $c['port'];
	$mailer->SMTPAuth   = true;
	$mailer->SMTPSecure = $c['encryption'];   // 465 — implicit TLS, не STARTTLS
	$mailer->Username   = $c['user'];
	$mailer->Password   = $c['pass'];
	$mailer->CharSet    = 'UTF-8';
	$mailer->Timeout    = 15;                 // шаред-хостинг: не висим на форме

	/*
	 * Reply-To на адрес заявителя, если он оставил email. Иначе менеджер
	 * отвечает на no-reply@ и ответ уходит в никуда — а это единственный
	 * канал связи по заявке.
	 */
	$reply = apply_filters( 'promen_mail_reply_to', '' );
	if ( is_email( $reply ) ) {
		$mailer->clearReplyTos();
		$mailer->addReplyTo( $reply );
	}
} );

/*
 * Отказ SMTP не должен теряться: заявка уже записана в базу, но без следа
 * в логе никто не узнает, что письмо не ушло. Пишем в error_log хостинга —
 * его видно по SSH и в панели.
 */
add_action( 'wp_mail_failed', function ( $error ) {
	if ( $error instanceof WP_Error ) {
		error_log( 'PROM-EN: письмо не отправлено — ' . $error->get_error_message() );
	}
} );
