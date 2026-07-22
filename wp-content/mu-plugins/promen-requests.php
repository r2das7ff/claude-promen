<?php
/**
 * Plugin Name: PROM-EN Requests
 * Description: Приём заявок «Запросить КП»: письмо на zakaz@prom-en.com + запись в CPT (подстраховка).
 */

defined( 'ABSPATH' ) || exit;

const PROMEN_REQUEST_EMAIL = 'zakaz@prom-en.com';

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

function promen_handle_request(): void {
	if ( ! isset( $_POST['promen_nonce'] ) || ! wp_verify_nonce( $_POST['promen_nonce'], 'promen_request' ) ) {
		wp_die( 'Сессия устарела — обновите страницу и отправьте форму ещё раз.' );
	}

	// Honeypot: боты заполняют скрытое поле.
	if ( ! empty( $_POST['company_url'] ) ) {
		$back = wp_get_referer() ?: home_url( '/' );
		wp_safe_redirect( add_query_arg( 'sent', '1', remove_query_arg( 'sent', $back ) ) . '#request' );
		exit;
	}

	$contact = sanitize_text_field( wp_unslash( $_POST['contact'] ?? '' ) );
	if ( $contact === '' ) {
		wp_die( 'Укажите email или телефон для ответа на запрос.' );
	}

	// Простейший rate-limit: не чаще 1 заявки / 60 с с одного IP.
	$ip  = $_SERVER['REMOTE_ADDR'] ?? '0';
	$key = 'promen_req_' . md5( $ip );
	if ( get_transient( $key ) ) {
		wp_die( 'Заявка уже принята. Подождите минуту перед повторной отправкой.' );
	}
	set_transient( $key, 1, 60 );

	$fields = [
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
	foreach ( $fields as $key_f => $label ) {
		$val = sanitize_text_field( wp_unslash( $_POST[ $key_f ] ?? '' ) );
		if ( $val !== '' ) {
			$lines[] = "{$label}: {$val}";
		}
	}
	$referer = esc_url_raw( wp_get_referer() ?: '' );
	if ( $referer ) {
		$lines[] = "Страница: {$referer}";
	}
	$body = implode( "\n", $lines );

	$title = 'Запрос КП';
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

	wp_mail( PROMEN_REQUEST_EMAIL, $title, $body );

	$back = $referer ?: home_url( '/' );
	$back = add_query_arg( 'sent', '1', remove_query_arg( 'sent', $back ) ) . '#request';
	wp_safe_redirect( $back );
	exit;
}
add_action( 'admin_post_promen_request', 'promen_handle_request' );
add_action( 'admin_post_nopriv_promen_request', 'promen_handle_request' );
