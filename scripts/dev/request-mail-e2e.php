<?php
/**
 * Локальная сквозная проверка обработчика заявки без SMTP: перехватываем
 * wp_mail фильтром pre_wp_mail, печатаем тему/HTML/текст между маркерами.
 * Запуск (из site/): docker exec -i -e SCENARIO=calc site-wordpress-1 php < scripts/dev/request-mail-e2e.php
 * Сценарии: calc, product, contact, tz, nonce_stale_guest, nonce_stale_foreign, nonce_stale_noheaders.
 * После прогона удалить тестовые записи CPT «Заявки КП» по явным ID (wp post delete ID --force).
 */
$_SERVER['HTTP_HOST']       = 'localhost:8080';
$_SERVER['REQUEST_METHOD']  = 'POST';
$_SERVER['REMOTE_ADDR']     = '10.0.0.7';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 YaBrowser/24.7.0.0 Safari/537.36';
$_SERVER['HTTP_REFERER']    = 'http://localhost:8080/';

require '/var/www/html/wp-load.php';

global $wpdb;
$sku = (string) $wpdb->get_var( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_sku' AND pm.meta_value <> '' AND p.post_type = 'product' AND p.post_status = 'publish' ORDER BY p.ID LIMIT 1" );
$pid = $sku ? (int) wc_get_product_id_by_sku( $sku ) : 0;

$scenario = getenv( 'SCENARIO' ) ?: 'calc';
$base     = [ 'action' => 'promen_request', 'promen_nonce' => wp_create_nonce( 'promen_request' ), 'pd_consent' => '1', 'company_url' => '' ];

switch ( $scenario ) {
	case 'calc': // модалка «Расчёт стоимости» — старый JS шлёт name вместо product
		$_POST = $base + [
			'promen_ajax' => '1', 'preset' => 'calc',
			'name' => 'Отвод 90° крутоизогнутый', 'standard' => 'ГОСТ 17375-2001', 'dn' => 'DN 100 / Ø 108×4',
			'pn' => 'PN 16', 'material' => '09Г2С', 'qty' => '250',
			'contact' => 'ivanov@company.ru, +7 912 345-67-89',
		];
		break;
	case 'product': // карточка товара: форма на странице, sku, доставка
		$_SERVER['HTTP_REFERER'] = $pid ? get_permalink( $pid ) : 'http://localhost:8080/';
		$_POST = $base + [
			'preset' => 'kp', 'sku' => $sku, 'product' => $pid ? get_the_title( $pid ) : 'Тройник',
			'standard' => 'ГОСТ 17376-2001', 'dn' => 'DN 150 / 159×6', 'material' => '12Х1МФ', 'qty' => '40',
			'city' => 'Екатеринбург, до объекта', 'delivery' => 'да; Екатеринбург; 40 шт; ~18 500 ₽',
			'deadline' => '30 календарных дней',
			'contact' => '8 (351) 217-00-99',
		];
		break;
	case 'contact': // страница «Контакты»
		$_SERVER['HTTP_REFERER'] = 'http://localhost:8080/contacts/';
		$_POST = $base + [
			'preset' => 'contact', 'name' => 'ТЕСТ — проверка формы (не заявка)', 'company' => 'ООО «Заказчик»',
			'topic' => 'Техническая консультация', 'contact' => 'no-reply@prom-en.com',
			'task' => "Техническая проверка отправки формы после переезда сайта.\nЗаявкой не является, обрабатывать не нужно.\n\n<b>Проверка</b> экранирования & сущностей.",
		];
		break;
	case 'nonce_stale_guest': // просроченный nonce, но Origin сайта — гостю можно
		$_SERVER['HTTP_ORIGIN'] = 'http://localhost:8080';
		$_POST = [ 'action' => 'promen_request', 'promen_nonce' => 'deadbeef00', 'pd_consent' => '1', 'company_url' => '',
			'promen_ajax' => '1', 'preset' => 'contact', 'name' => 'Nonce stale', 'contact' => 'stale@example.com', 'task' => 'x' ];
		break;
	case 'nonce_stale_foreign': // просроченный nonce и чужой Origin — 403
		$_SERVER['HTTP_ORIGIN']  = 'https://evil.example';
		$_SERVER['HTTP_REFERER'] = 'https://evil.example/';
		$_POST = [ 'action' => 'promen_request', 'promen_nonce' => 'deadbeef00', 'pd_consent' => '1', 'company_url' => '',
			'promen_ajax' => '1', 'preset' => 'contact', 'name' => 'Nonce foreign', 'contact' => 'foreign@example.com', 'task' => 'x' ];
		break;
	case 'nonce_stale_noheaders': // просроченный nonce без Origin/Referer — 403
		unset( $_SERVER['HTTP_REFERER'] );
		$_POST = [ 'action' => 'promen_request', 'promen_nonce' => 'deadbeef00', 'pd_consent' => '1', 'company_url' => '',
			'promen_ajax' => '1', 'preset' => 'contact', 'name' => 'Nonce none', 'contact' => 'none@example.com', 'task' => 'x' ];
		break;
	case 'tz': // модалка «ТЗ» из подборщика
		$_SERVER['HTTP_REFERER'] = 'http://localhost:8080/podbor/';
		$_POST = $base + [
			'promen_ajax' => '1', 'preset' => 'tz', 'name' => 'Петрова Анна Сергеевна', 'company' => 'АО «ТГК-9»',
			'contact' => '+7 (343) 123-45-67',
			'task' => "Объект: Пермская ТЭЦ-9.\nЗапрос: отводы и тройники для паропровода 540 °C, Ру 14 МПа.\nОтрасль: Теплоэнергетика.",
		];
		break;
}

delete_transient( 'promen_req_' . md5( $_SERVER['REMOTE_ADDR'] ) );

// Локально SMTP не настроен и From = wordpress@localhost не проходит валидацию PHPMailer —
// подставляем адрес, как это делает promen-smtp.php на проде.
add_filter( 'wp_mail_from', fn() => 'no-reply@prom-en.com', 30 );

// Перехват на уровне PHPMailer (после всех хуков): видно тему, тип, HTML и AltBody.
// Отправка дальше упрётся в отсутствие sendmail — это ожидаемо и безвредно.
add_action( 'phpmailer_init', function ( $m ) {
	echo "\n===SUBJECT===\n", $m->Subject,
		"\n===META===\n", 'ContentType=', $m->ContentType, ' CharSet=', $m->CharSet, ' ReplyTo=', json_encode( $m->getReplyToAddresses(), JSON_UNESCAPED_UNICODE ), ' Attachments=', count( $m->getAttachments() ),
		"\n===HTML===\n", $m->Body,
		"\n===ALT===\n", $m->AltBody,
		"\n===END===\n";
}, 99 );

promen_handle_request();
