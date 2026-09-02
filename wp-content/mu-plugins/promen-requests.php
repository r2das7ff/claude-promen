<?php
/**
 * Plugin Name: PROM-EN Requests
 * Description: Приём заявок «Запросить КП»: письмо на zakaz@prom-en.com + запись в CPT (подстраховка).
 *              Принимает и классическую форму s10 (POST → redirect ?sent=1),
 *              и модалку request-modal.js (promen_ajax=1 → JSON), с вложением чертежа.
 *              Письмо — HTML в дизайне сайта (promen-requests-mail.php) с текстовой
 *              альтернативой; в теме сразу номер, тип, изделие и заявитель.
 */

defined( 'ABSPATH' ) || exit;

const PROMEN_REQUEST_EMAIL = 'zakaz@prom-en.com';

/** Разрешённые расширения вложения (чертёж / КД) и лимит размера. */
const PROMEN_REQUEST_FILE_EXT  = [ 'pdf', 'dwg', 'dxf', 'png', 'jpg', 'jpeg' ];
const PROMEN_REQUEST_FILE_SIZE = 15 * 1024 * 1024; // 15 MB

/** Подписи типов запроса (значение hidden-поля preset). */
const PROMEN_REQUEST_PRESETS = [
	'kp'       => 'Запрос КП',
	'tz'       => 'Техническое задание',
	'calc'     => 'Расчёт стоимости',
	'solution' => 'Подбор решения',
	'product'  => 'Запрос позиции',
	'docs'     => 'Запрос документации',
	'project'  => 'Обсуждение проекта',
	'contact'  => 'Общий запрос',
];

/** Поля формы, которые попадают в письмо и запись (кроме textarea task). */
const PROMEN_REQUEST_FIELDS = [
	'name', 'company', 'topic', 'product', 'sku', 'standard', 'dn', 'pn',
	'material', 'qty', 'deadline', 'city', 'delivery', 'contact',
];

add_action( 'init', function () {
	register_post_type( 'promen_request', [
		'labels'       => [ 'name' => 'Заявки КП', 'singular_name' => 'Заявка КП' ],
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-email-alt',
		'supports'     => [ 'title', 'editor' ],
		'capabilities' => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap' => true,
	] );
} );

/** Ошибка: JSON для модалки, wp_die для классической формы. */
function promen_request_fail( string $message, int $code = 400 ): void {
	if ( ! empty( $_POST['promen_ajax'] ) ) {
		wp_send_json_error( [ 'message' => $message ], $code );
	}
	wp_die( esc_html( $message ) );
}

/** Успех: JSON для модалки, redirect ?sent=1 для классической формы. */
function promen_request_done(): void {
	if ( ! empty( $_POST['promen_ajax'] ) ) {
		wp_send_json_success( [ 'message' => 'ok' ] );
	}
	$back = wp_get_referer() ?: home_url( '/' );
	$back = add_query_arg( 'sent', '1', remove_query_arg( 'sent', $back ) ) . '#request';
	wp_safe_redirect( $back );
	exit;
}

/**
 * Вложение «Чертёж / КД»: строгий whitelist расширений, лимит 15 MB,
 * имя файла генерируется сервером. Возвращает [path, url, original, size] или null.
 * Бросает promen_request_fail при явной ошибке загрузки (молча терять чертёж нельзя).
 */
function promen_request_attachment(): ?array {
	if ( empty( $_FILES['attachment'] ) || ! is_array( $_FILES['attachment'] ) ) {
		return null;
	}
	$file = $_FILES['attachment'];
	if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
		return null;
	}
	if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
		promen_request_fail( 'Не удалось загрузить файл. Попробуйте ещё раз или отправьте без вложения.' );
	}
	if ( (int) $file['size'] > PROMEN_REQUEST_FILE_SIZE ) {
		promen_request_fail( 'Файл больше 15 МБ. Сожмите его или отправьте напрямую на ' . PROMEN_REQUEST_EMAIL . '.' );
	}
	$original = sanitize_file_name( (string) $file['name'] );
	$ext      = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, PROMEN_REQUEST_FILE_EXT, true ) ) {
		promen_request_fail( 'Допустимые форматы вложения: PDF, DWG, DXF, PNG, JPG.' );
	}
	if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
		promen_request_fail( 'Не удалось загрузить файл.' );
	}

	$upload = wp_upload_bits(
		'promen-request-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 8, false ) . '.' . $ext,
		null,
		(string) file_get_contents( $file['tmp_name'] )
	);
	if ( ! empty( $upload['error'] ) ) {
		promen_request_fail( 'Не удалось сохранить файл: ' . $upload['error'] );
	}
	return [
		'path'     => $upload['file'],
		'url'      => $upload['url'],
		'original' => $original,
		'size'     => (int) $file['size'],
	];
}

/**
 * Заголовок страницы-источника по её адресу; пусто, если не распознали.
 * Карточки товара и страницы отдают ID через url_to_postid, категории
 * каталога — через get_term_by по последнему сегменту пути.
 */
function promen_request_page_title( string $url ): string {
	if ( '' === $url ) {
		return '';
	}
	$id = (int) url_to_postid( $url );
	if ( $id > 0 ) {
		return wp_strip_all_tags( (string) get_the_title( $id ) );
	}
	if ( untrailingslashit( $url ) === untrailingslashit( home_url( '/' ) ) ) {
		return 'Главная';
	}
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$slug = $path !== '' ? basename( $path ) : '';
	if ( '' !== $slug && taxonomy_exists( 'product_cat' ) ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			return 'Каталог: ' . $term->name;
		}
	}
	return '';
}

/**
 * Сколько заявок уже приходило с этого контакта (по строке «Контакт: …»
 * в записях CPT) и когда была последняя. Текущая запись исключается.
 * Ищем по email, если он есть, иначе по контакту как написан.
 */
function promen_request_history( string $contact, int $exclude_id ): ?array {
	global $wpdb;
	$parsed = promen_request_contact_parse( $contact );
	$needle = '' !== $parsed['email'] ? $parsed['email'] : trim( $contact );
	if ( '' === $needle || ! isset( $wpdb ) ) {
		return null;
	}
	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT COUNT(*) AS n, MAX(post_date) AS last FROM {$wpdb->posts}
		 WHERE post_type = 'promen_request' AND ID <> %d AND post_content LIKE %s",
		$exclude_id,
		'%' . $wpdb->esc_like( 'Контакт: ' ) . '%' . $wpdb->esc_like( $needle ) . '%'
	) );
	if ( ! $row ) {
		return null;
	}
	return [
		'count' => (int) $row->n,
		'last'  => $row->last ? wp_date( 'd.m.Y', strtotime( $row->last ) ) : '',
	];
}

/**
 * Данные заявки в одном массиве — из него собираются тема, текст и HTML
 * (promen-requests-mail.php). Сюда же кладём всё, что помогает менеджеру:
 * страницу-источник, ссылку на карточку по артикулу, историю контакта.
 */
function promen_request_collect( string $preset, ?array $attachment ): array {
	$fields = [];
	foreach ( PROMEN_REQUEST_FIELDS as $key ) {
		$val = sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) );
		if ( '' !== $val ) {
			$fields[ $key ] = $val;
		}
	}

	/*
	 * В модалке пресетов «Расчёт», «Позиция», «Документы» поле НАИМЕНОВАНИЕ
	 * исторически уходило под именем name (то есть как ФИО). Скрипт
	 * исправлен (request-modal.js шлёт product), но страницы из кеша ещё
	 * какое-то время присылают старый вариант — переносим сами.
	 */
	if ( in_array( $preset, [ 'calc', 'product', 'docs' ], true ) && empty( $fields['product'] ) && ! empty( $fields['name'] ) ) {
		$fields['product'] = $fields['name'];
		unset( $fields['name'] );
	}

	$referer = esc_url_raw( wp_get_referer() ?: '' );

	$product_url = '';
	if ( ! empty( $fields['sku'] ) && function_exists( 'wc_get_product_id_by_sku' ) ) {
		$pid = (int) wc_get_product_id_by_sku( $fields['sku'] );
		if ( $pid > 0 ) {
			$product_url = (string) get_permalink( $pid );
		}
	}

	return [
		'id'           => 0,
		'preset'       => $preset,
		'preset_label' => PROMEN_REQUEST_PRESETS[ $preset ] ?? PROMEN_REQUEST_PRESETS['kp'],
		'fields'       => $fields,
		'task'         => sanitize_textarea_field( wp_unslash( $_POST['task'] ?? '' ) ),
		'attachment'   => $attachment,
		'referer'      => $referer,
		'page_title'   => promen_request_page_title( $referer ),
		'product_url'  => $product_url,
		'time'         => wp_date( 'd.m.Y, H:i', null, new DateTimeZone( 'Europe/Moscow' ) ),
		'ip'           => sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
		'ua'           => sanitize_text_field( (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
		'via'          => ! empty( $_POST['promen_ajax'] ) ? 'modal' : 'page',
		'history'      => null,
		'admin_url'    => '',
		'home_url'     => home_url( '/' ),
		'logo_url'     => get_theme_file_uri( 'assets/img/PE_logo_black.png' ),
	];
}

/**
 * Запрос пришёл с самого сайта: хост Origin (или Referer, если Origin нет)
 * совпадает с хостом сайта. Без обоих заголовков — нет.
 */
function promen_request_same_origin(): bool {
	$site = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	foreach ( [ 'HTTP_ORIGIN', 'HTTP_REFERER' ] as $header ) {
		$val = (string) ( $_SERVER[ $header ] ?? '' );
		if ( '' === $val || 'null' === $val ) {
			continue;
		}
		return strtolower( (string) wp_parse_url( $val, PHP_URL_HOST ) ) === $site && '' !== $site;
	}
	return false;
}

function promen_handle_request(): void {
	/*
	 * Nonce живёт 12–24 часа, а страницы отдаются из полностраничного кеша
	 * до 7 дней (advanced-cache.php): гость со «старой» копией страницы
	 * приносил просроченный nonce и получал «Сессия устарела», причём
	 * обновление страницы не помогало — кеш отдавал ту же копию. У гостя
	 * nonce и так не привязан к сессии (uid 0, пустой токен), поэтому его
	 * просрочка — не признак подделки. Гостю достаточно same-origin;
	 * для залогиненных проверка nonce остаётся строгой. От ботов защищают
	 * honeypot, rate-limit и обязательное согласие ниже.
	 */
	$nonce_ok = isset( $_POST['promen_nonce'] ) && wp_verify_nonce( $_POST['promen_nonce'], 'promen_request' );
	if ( ! $nonce_ok && ( is_user_logged_in() || ! promen_request_same_origin() ) ) {
		promen_request_fail( 'Сессия устарела — обновите страницу и отправьте форму ещё раз.', 403 );
	}

	// Honeypot: боты заполняют скрытое поле. Отвечаем «успехом» без обработки.
	if ( ! empty( $_POST['company_url'] ) ) {
		promen_request_done();
	}

	$contact = sanitize_text_field( wp_unslash( $_POST['contact'] ?? '' ) );
	if ( $contact === '' ) {
		promen_request_fail( 'Укажите email или телефон для ответа на запрос.' );
	}

	// Согласие на обработку ПДн (152-ФЗ) — обязательно для всех форм.
	if ( empty( $_POST['pd_consent'] ) ) {
		promen_request_fail( 'Для отправки запроса требуется согласие на обработку персональных данных.' );
	}

	// Простейший rate-limit: не чаще 1 заявки / 60 с с одного IP.
	$ip  = $_SERVER['REMOTE_ADDR'] ?? '0';
	$key = 'promen_req_' . md5( $ip );
	if ( get_transient( $key ) ) {
		promen_request_fail( 'Заявка уже принята. Подождите минуту перед повторной отправкой.', 429 );
	}
	set_transient( $key, 1, 60 );

	$preset = sanitize_key( $_POST['preset'] ?? '' );
	if ( ! isset( PROMEN_REQUEST_PRESETS[ $preset ] ) ) {
		$preset = 'kp';
	}

	$req = promen_request_collect( $preset, promen_request_attachment() );

	/*
	 * Сначала запись в CPT: её ID становится номером заявки в теме письма,
	 * и даже если SMTP откажет, заявка останется в админке.
	 */
	$post_id = wp_insert_post( [
		'post_type'    => 'promen_request',
		'post_status'  => 'private',
		'post_title'   => promen_request_mail_subject( $req ) . ' — ' . $req['time'],
		'post_content' => promen_request_mail_text( $req ),
	] );
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		$req['id']        = (int) $post_id;
		$req['admin_url'] = admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' );
		$req['history']   = promen_request_history( $contact, (int) $post_id );
		foreach ( [ 'preset', 'referer', 'ip', 'ua' ] as $meta ) {
			if ( '' !== (string) $req[ $meta ] ) {
				update_post_meta( (int) $post_id, '_promen_' . $meta, $req[ $meta ] );
			}
		}
		update_post_meta( (int) $post_id, '_promen_contact', $contact );
		// Заголовок с номером — чтобы в списке админки совпадал с темой письма.
		wp_update_post( [
			'ID'         => (int) $post_id,
			'post_title' => promen_request_mail_subject( $req ) . ' — ' . $req['time'],
		] );
	}

	/*
	 * Reply-To на адрес заявителя (promen-smtp.php). Письмо уходит от
	 * no-reply@, и без этого «Ответить» в почтовом клиенте вело бы в никуда,
	 * а другого канала связи по заявке нет.
	 */
	$reply_to = promen_request_contact_parse( $contact )['email'];
	add_filter( 'promen_mail_reply_to', function () use ( $reply_to ) {
		return $reply_to;
	} );

	// Текстовая альтернатива для клиентов без HTML: wp_mail её не умеет, ставим через PHPMailer.
	$alt_body = promen_request_mail_text( $req );
	$set_alt  = function ( $mailer ) use ( $alt_body ) {
		$mailer->AltBody = $alt_body;
	};
	add_action( 'phpmailer_init', $set_alt, 30 );

	wp_mail(
		PROMEN_REQUEST_EMAIL,
		promen_request_mail_subject( $req ),
		promen_request_mail_html( $req ),
		[ 'Content-Type: text/html; charset=UTF-8' ],
		$req['attachment'] ? [ $req['attachment']['path'] ] : []
	);

	remove_action( 'phpmailer_init', $set_alt, 30 );

	promen_request_done();
}
add_action( 'admin_post_promen_request', 'promen_handle_request' );
add_action( 'admin_post_nopriv_promen_request', 'promen_handle_request' );
