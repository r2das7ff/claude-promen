<?php
/**
 * Plugin Name: PROM-EN Requests
 * Description: Приём заявок «Запросить КП»: письмо на zakaz@prom-en.com + запись в CPT (подстраховка).
 *              Принимает и классическую форму s10 (POST → redirect ?sent=1),
 *              и модалку request-modal.js (promen_ajax=1 → JSON), с вложением чертежа.
 */

defined( 'ABSPATH' ) || exit;

const PROMEN_REQUEST_EMAIL = 'zakaz@prom-en.com';

/** Разрешённые расширения вложения (чертёж / КД) и лимит размера. */
const PROMEN_REQUEST_FILE_EXT  = [ 'pdf', 'dwg', 'dxf', 'png', 'jpg', 'jpeg' ];
const PROMEN_REQUEST_FILE_SIZE = 15 * 1024 * 1024; // 15 MB

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
 * имя файла генерируется сервером. Возвращает [path, url, original] или null.
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
	];
}

function promen_handle_request(): void {
	if ( ! isset( $_POST['promen_nonce'] ) || ! wp_verify_nonce( $_POST['promen_nonce'], 'promen_request' ) ) {
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

	$preset_labels = [
		'kp'       => 'Форма КП (s10)',
		'tz'       => 'Техническое задание',
		'calc'     => 'Расчёт стоимости',
		'solution' => 'Подбор решения',
		'product'  => 'Запрос позиции',
		'docs'     => 'Запрос документации',
		'project'  => 'Обсуждение проекта',
		'contact'  => 'Общий запрос',
	];
	$preset = sanitize_key( $_POST['preset'] ?? '' );

	$fields = [
		'name'     => 'ФИО / Контактное лицо',
		'company'  => 'Организация',
		'topic'    => 'Тема запроса',
		'product'  => 'Наименование',
		'standard' => 'Стандарт',
		'dn'       => 'DN / D',
		'pn'       => 'Давление',
		'material' => 'Материал',
		'qty'      => 'Количество',
		'deadline' => 'Срок',
		'city'     => 'Город доставки',
		'delivery' => 'Расчёт доставки',
		'contact'  => 'Контакт',
		'sku'      => 'Артикул',
	];

	$lines = [];
	if ( isset( $preset_labels[ $preset ] ) ) {
		$lines[] = 'Тип запроса: ' . $preset_labels[ $preset ];
	}
	foreach ( $fields as $key_f => $label ) {
		$val = sanitize_text_field( wp_unslash( $_POST[ $key_f ] ?? '' ) );
		if ( $val !== '' ) {
			$lines[] = "{$label}: {$val}";
		}
	}
	// Многострочное описание задачи — отдельно (textarea).
	$task = sanitize_textarea_field( wp_unslash( $_POST['task'] ?? '' ) );
	if ( $task !== '' ) {
		$lines[] = "Описание задачи:\n{$task}";
	}

	$attachment = promen_request_attachment();
	if ( $attachment ) {
		$lines[] = 'Вложение: ' . $attachment['original'] . "\n" . $attachment['url'];
	}

	$referer = esc_url_raw( wp_get_referer() ?: '' );
	if ( $referer ) {
		$lines[] = "Страница: {$referer}";
	}
	$body = implode( "\n", $lines );

	$title = isset( $preset_labels[ $preset ] ) && 'kp' !== $preset ? $preset_labels[ $preset ] : 'Запрос КП';
	if ( ! empty( $_POST['product'] ) ) {
		$title .= ': ' . sanitize_text_field( wp_unslash( $_POST['product'] ) );
	} elseif ( ! empty( $_POST['sku'] ) ) {
		$title .= ': ' . sanitize_text_field( wp_unslash( $_POST['sku'] ) );
	}

	wp_insert_post( [
		'post_type'    => 'promen_request',
		'post_status'  => 'private',
		'post_title'   => $title . ' — ' . wp_date( 'd.m.Y H:i' ),
		'post_content' => $body,
	] );

	/*
	 * Reply-To на адрес заявителя (promen-smtp.php). Письмо уходит от
	 * no-reply@, и без этого «Ответить» в почтовом клиенте вело бы в никуда,
	 * а другого канала связи по заявке нет.
	 */
	$reply_to = is_email( $contact ) ? $contact : '';
	add_filter( 'promen_mail_reply_to', function () use ( $reply_to ) {
		return $reply_to;
	} );

	wp_mail(
		PROMEN_REQUEST_EMAIL,
		$title,
		$body,
		[],
		$attachment ? [ $attachment['path'] ] : []
	);

	promen_request_done();
}
add_action( 'admin_post_promen_request', 'promen_handle_request' );
add_action( 'admin_post_nopriv_promen_request', 'promen_handle_request' );
