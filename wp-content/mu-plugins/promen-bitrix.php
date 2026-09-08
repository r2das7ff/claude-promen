<?php
/**
 * Plugin Name: PROM-EN — лид в Битрикс24
 * Description: Заявка с сайта создаёт лид в CRM с идентификаторами визита.
 *
 * Зачем. К порталу привязаны ящики zakaz*, и письмо само становится лидом —
 * но письмо с сайта уходит ОТ АДРЕСА САЙТА, заявитель только в Reply-To.
 * Поэтому все сайтовые лиды выглядят письмами от одного отправителя, и
 * дописать в нужный ClientID постфактум нельзя: непонятно, в какой именно.
 * Отсюда решение — лид создаёт сам сайт, сразу с полями.
 *
 * Без ClientID в CRM штатная связка Метрика ↔ Битрикс24 работает вхолостую:
 * данные она грузит ежечасно, но сопоставить заказ с визитом ей нечем, и
 * цели CRM стоят на нуле весь год.
 *
 * Доступ: константа PROMEN_BITRIX_WEBHOOK или опция promen_bitrix_webhook —
 * входящий вебхук с правом crm. Пока его нет, плагин молчит и сайт работает
 * ровно как раньше: письмо на zakaz@ и запись в «Заявки КП».
 *
 * Письмо на zakaz@ остаётся намеренно: на каждую заявку будет два лида —
 * наш и почтовый. Чтобы РОП видел пару, номер заявки идёт и в тему письма,
 * и в название лида.
 */

defined( 'ABSPATH' ) || exit;

/** Ответственный за лиды с сайта: Виктор Саитгареев (основной аккаунт, не 351). */
const PROMEN_BITRIX_ASSIGNED = 53;

/** «Заявка с сайта» — то самое поле, которое менеджеры ставили руками. */
const PROMEN_BITRIX_UF_FROM_SITE = 'UF_CRM_1718694011';

/** Источник «Веб-сайт» — отделяет наши лиды от почтовых с zakaz@prom-en.com. */
const PROMEN_BITRIX_SOURCE = 'WEB';

/**
 * Дольше ждать нельзя: вызов синхронный, за ним стоит живой человек с
 * открытой формой. Не ответил за это время — заявка всё равно цела
 * в письме и в CPT, а ошибка ляжет в мету.
 */
const PROMEN_BITRIX_TIMEOUT = 6;


function promen_bitrix_webhook(): string {
	if ( defined( 'PROMEN_BITRIX_WEBHOOK' ) && '' !== (string) PROMEN_BITRIX_WEBHOOK ) {
		return rtrim( (string) PROMEN_BITRIX_WEBHOOK, '/' );
	}
	return rtrim( trim( (string) get_option( 'promen_bitrix_webhook', '' ) ), '/' );
}


function promen_bitrix_enabled(): bool {
	return '' !== promen_bitrix_webhook();
}


/**
 * Контакт заявителя приходит одной строкой: почта или телефон, как ввели.
 * Битриксу нужны разные поля, поэтому разбираем по тому же правилу, что и
 * Reply-To в письме.
 */
function promen_bitrix_contact_fields( string $contact ): array {
	$parsed = function_exists( 'promen_request_contact_parse' )
		? promen_request_contact_parse( $contact )
		: [ 'email' => is_email( $contact ) ? $contact : '', 'phone' => '' ];

	$fields = [];
	if ( ! empty( $parsed['email'] ) ) {
		$fields['EMAIL'] = [ [ 'VALUE' => $parsed['email'], 'VALUE_TYPE' => 'WORK' ] ];
	}
	$phone = $parsed['phone'] ?? '';
	if ( '' === $phone && empty( $parsed['email'] ) ) {
		$phone = $contact; // не почта и не разобралось — считаем телефоном
	}
	if ( '' !== $phone ) {
		$fields['PHONE'] = [ [ 'VALUE' => $phone, 'VALUE_TYPE' => 'WORK' ] ];
	}
	return $fields;
}


/**
 * Тело лида: то же, что менеджер видит в письме, но разложенное по полям.
 * Комментарий оставляем текстом — в нём параметры изделия и задача, которые
 * в отдельные поля CRM не ложатся.
 */
function promen_bitrix_lead_fields( array $req, int $post_id, array $attribution ): array {
	$fields = $req['fields'] ?? [];

	$title = sprintf(
		'Заявка №%d · %s — prom-en.com',
		$post_id,
		$req['preset_label'] ?? 'Запрос'
	);

	$lines = [];
	foreach ( [
		'product'  => 'Изделие',
		'standard' => 'Стандарт',
		'dn'       => 'Ду',
		'pn'       => 'Ру',
		'material' => 'Материал',
		'qty'      => 'Количество',
		'deadline' => 'Срок',
		'city'     => 'Город',
		'delivery' => 'Доставка',
		'sku'      => 'Артикул',
	] as $key => $label ) {
		if ( ! empty( $fields[ $key ] ) ) {
			$lines[] = $label . ': ' . $fields[ $key ];
		}
	}
	if ( ! empty( $req['task'] ) ) {
		$lines[] = 'Задача: ' . $req['task'];
	}
	if ( ! empty( $req['product_url'] ) ) {
		$lines[] = 'Карточка товара: ' . $req['product_url'];
	}
	if ( ! empty( $req['referer'] ) ) {
		$lines[] = 'Страница обращения: ' . $req['referer'];
	}
	if ( ! empty( $req['attachment']['url'] ) ) {
		$lines[] = 'Вложение: ' . $req['attachment']['url'];
	}
	$lines[] = 'Заявка в админке сайта: ' . ( $req['admin_url'] ?? '' );

	$lead = [
		'TITLE'                        => $title,
		'NAME'                         => $fields['name'] ?? '',
		'COMPANY_TITLE'                => $fields['company'] ?? '',
		'SOURCE_ID'                    => PROMEN_BITRIX_SOURCE,
		'SOURCE_DESCRIPTION'           => 'Форма на сайте prom-en.com',
		'STATUS_ID'                    => 'NEW',
		'ASSIGNED_BY_ID'               => PROMEN_BITRIX_ASSIGNED,
		'OPENED'                       => 'Y',
		'COMMENTS'                     => implode( "\n", $lines ),
		PROMEN_BITRIX_UF_FROM_SITE     => 1,
		'UF_CRM_METRIKA_CLIENT_ID'     => $attribution['ym_client_id'] ?? '',
		'UF_CRM_YCLID'                 => $attribution['yclid'] ?? '',
	];

	return $lead + promen_bitrix_contact_fields( (string) ( $fields['contact'] ?? '' ) );
}


/**
 * Создание лида. Результат пишем в мету заявки: по ней потом видно, дошла
 * ли она до CRM, и не нужно лезть в логи.
 */
function promen_bitrix_create_lead( array $req, int $post_id, array $attribution ): void {
	if ( ! promen_bitrix_enabled() || $post_id <= 0 ) {
		return;
	}

	$response = wp_remote_post( promen_bitrix_webhook() . '/crm.lead.add.json', [
		'timeout' => PROMEN_BITRIX_TIMEOUT,
		'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
		'body'    => wp_json_encode( [
			'fields' => promen_bitrix_lead_fields( $req, $post_id, $attribution ),
			// Лид должен пройти обычным путём: уведомления ответственному,
			// запись в ленту, роботы — всё как у лида из почты.
			'params' => [ 'REGISTER_SONET_EVENT' => 'Y' ],
		] ),
	] );

	if ( is_wp_error( $response ) ) {
		update_post_meta( $post_id, '_promen_bitrix_error', $response->get_error_message() );
		return;
	}

	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( isset( $body['result'] ) && (int) $body['result'] > 0 ) {
		update_post_meta( $post_id, '_promen_bitrix_lead', (int) $body['result'] );
		delete_post_meta( $post_id, '_promen_bitrix_error' );
		return;
	}

	update_post_meta( $post_id, '_promen_bitrix_error', substr( (string) wp_remote_retrieve_body( $response ), 0, 500 ) );
}


/**
 * Заявка принята и прошла антиспам — карантинные сюда не доходят, в CRM
 * должен попадать только живой поток.
 */
add_action( 'promen_request_accepted', 'promen_bitrix_create_lead', 10, 3 );
