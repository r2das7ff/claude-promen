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
 * Что делает. Отдаёт письма на smtp.yandex.ru от имени ящика, который
 * этому домену принадлежит — тогда и SPF, и DKIM проставляет сам Яндекс.
 *
 * Настройка (в wp-config.php, выше require wp-settings.php):
 *
 *     define( 'PROMEN_SMTP_USER', 'no-reply@prom-en.com' );
 *     define( 'PROMEN_SMTP_PASS', 'пароль-приложения' );
 *
 * Пароль — именно пароль ПРИЛОЖЕНИЯ из Яндекс ID, а не пароль от почты:
 * при включённой двухфакторной аутентификации обычный не подойдёт.
 *
 * Без этих констант плагин молчит и письма идут прежним путём — то есть
 * незаполненный конфиг ничего не ломает, просто не чинит.
 */

defined( 'ABSPATH' ) || exit;

const PROMEN_SMTP_HOST = 'smtp.yandex.ru';
const PROMEN_SMTP_PORT = 465;

function promen_smtp_ready(): bool {
	return defined( 'PROMEN_SMTP_USER' ) && defined( 'PROMEN_SMTP_PASS' )
		&& '' !== (string) PROMEN_SMTP_USER && '' !== (string) PROMEN_SMTP_PASS;
}

/*
 * Отправитель обязан совпадать с ящиком, под которым авторизуемся: Яндекс
 * отвергает письма с чужим From, а если бы и пропустил — подпись DKIM легла
 * бы не на тот домен и проверку у получателя письмо не прошло бы.
 */
add_filter( 'wp_mail_from', function ( $from ) {
	return promen_smtp_ready() ? (string) PROMEN_SMTP_USER : $from;
}, 20 );

add_filter( 'wp_mail_from_name', function ( $name ) {
	return promen_smtp_ready() ? 'Промышленная Энергетика' : $name;
}, 20 );

add_action( 'phpmailer_init', function ( $mailer ) {
	if ( ! promen_smtp_ready() ) {
		return;
	}
	$mailer->isSMTP();
	$mailer->Host       = PROMEN_SMTP_HOST;
	$mailer->Port       = PROMEN_SMTP_PORT;
	$mailer->SMTPAuth   = true;
	$mailer->SMTPSecure = 'ssl';           // 465 — implicit TLS, не STARTTLS
	$mailer->Username   = PROMEN_SMTP_USER;
	$mailer->Password   = PROMEN_SMTP_PASS;
	$mailer->CharSet    = 'UTF-8';
	$mailer->Timeout    = 15;              // шаред-хостинг: не висим на форме

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
