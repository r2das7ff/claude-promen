<?php
/**
 * Письмо о заявке (mu-plugins/promen-requests-mail.php): разбор контакта,
 * тема, текстовая и HTML-версии. Чистый PHP, без WordPress.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../wp-content/mu-plugins/promen-requests-mail.php';

final class RequestMailTest extends TestCase {

	private function req( array $over = [] ): array {
		return array_merge( [
			'id'           => 152,
			'preset'       => 'calc',
			'preset_label' => 'Расчёт стоимости',
			'fields'       => [
				'product'  => 'Отвод 90° DN100',
				'standard' => 'ГОСТ 17375-2001',
				'dn'       => 'DN 100 / Ø 108',
				'material' => '09Г2С',
				'qty'      => '100',
				'company'  => 'ООО «Заказчик»',
				'name'     => 'Иванов Иван',
				'contact'  => 'ivanov@company.ru, +7 912 345-67-89',
			],
			'task'         => "Первая строка\nВторая <b>строка</b>",
			'attachment'   => null,
			'referer'      => 'https://prom-en.com/catalog/otvody/',
			'page_title'   => 'Каталог: Отводы',
			'product_url'  => '',
			'time'         => '02.09.2026, 09:59',
			'ip'           => '10.0.0.1',
			'ua'           => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
			'via'          => 'modal',
			'history'      => [ 'count' => 2, 'last' => '12.08.2026' ],
			'admin_url'    => 'https://prom-en.com/wp-admin/post.php?post=152&action=edit',
			'home_url'     => 'https://prom-en.com/',
			'logo_url'     => 'https://prom-en.com/logo.png',
		], $over );
	}

	/* ── разбор контакта ── */

	public function test_contact_email_only(): void {
		$c = promen_request_contact_parse( 'Ivanov@Company.ru' );
		$this->assertSame( 'Ivanov@Company.ru', $c['email'] );
		$this->assertSame( '', $c['tel'] );
	}

	public function test_contact_phone_variants_normalize_to_plus7(): void {
		foreach ( [ '8 (351) 217-00-99', '+7 351 217 00 99', '3512170099', '7-351-217-00-99' ] as $raw ) {
			$c = promen_request_contact_parse( $raw );
			$this->assertSame( '+73512170099', $c['tel'], $raw );
			$this->assertSame( '+7 (351) 217-00-99', $c['phone_pretty'], $raw );
		}
	}

	public function test_contact_email_and_phone_together(): void {
		$c = promen_request_contact_parse( 'ivanov@x.ru, +7 912 345-67-89' );
		$this->assertSame( 'ivanov@x.ru', $c['email'] );
		$this->assertSame( '+79123456789', $c['tel'] );
	}

	public function test_contact_garbage_gives_nothing(): void {
		$c = promen_request_contact_parse( 'позвоните мне' );
		$this->assertSame( '', $c['email'] );
		$this->assertSame( '', $c['tel'] );
	}

	/* ── тема ── */

	public function test_subject_has_number_type_product_and_company(): void {
		$this->assertSame(
			'Заявка №152 · Расчёт стоимости · Отвод 90° DN100 · ООО «Заказчик»',
			promen_request_mail_subject( $this->req() )
		);
	}

	public function test_subject_falls_back_to_topic_and_name(): void {
		$req = $this->req( [
			'preset'       => 'contact',
			'preset_label' => 'Общий запрос',
			'fields'       => [ 'name' => 'ТЕСТ — проверка формы', 'topic' => 'Техническая консультация', 'contact' => 'a@b.ru' ],
		] );
		$this->assertSame( 'Заявка №152 · Общий запрос · Техническая консультация · ТЕСТ — проверка формы', promen_request_mail_subject( $req ) );
	}

	/* ── текст ── */

	public function test_text_keeps_contact_line_for_history_lookup(): void {
		$text = promen_request_mail_text( $this->req() );
		$this->assertStringContainsString( "Контакт: ivanov@company.ru, +7 912 345-67-89", $text );
		$this->assertStringContainsString( "Описание задачи:\nПервая строка\nВторая <b>строка</b>", $text );
		$this->assertStringContainsString( 'Отправлено: 10.0.0.1, Chrome 128, Windows', $text );
	}

	/* ── HTML ── */

	public function test_html_escapes_user_input(): void {
		$html = promen_request_mail_html( $this->req( [ 'fields' => [ 'product' => '<script>x</script>', 'contact' => 'a@b.ru' ] ] ) );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '&lt;script&gt;x&lt;/script&gt;', $html );
		$this->assertStringContainsString( 'Вторая &lt;b&gt;строка&lt;/b&gt;', $html );
	}

	public function test_html_full_grid_for_calc_shows_empty_cells_as_dash(): void {
		$html = promen_request_mail_html( $this->req() );
		foreach ( [ 'Наименование', 'Артикул', 'Стандарт', 'DN / D, мм', 'Давление', 'Материал / марка стали', 'Количество, шт', 'Срок' ] as $label ) {
			$this->assertStringContainsString( promen_mail_esc( $label ), $html, $label );
		}
		// Давление, артикул и срок не заполнены — три прочерка.
		$this->assertSame( 3, substr_count( $html, '>—</div>' ) );
	}

	public function test_html_sparse_grid_for_docs_hides_empty_and_relabels_product(): void {
		$req  = $this->req( [
			'preset'       => 'docs',
			'preset_label' => 'Запрос документации',
			'fields'       => [ 'product' => 'Сертификат ГОСТ 17375-2001', 'contact' => 'a@b.ru' ],
		] );
		$html = promen_request_mail_html( $req );
		$this->assertStringContainsString( 'Документ', $html );
		$this->assertStringNotContainsString( 'Давление', $html );
		$this->assertSame( 0, substr_count( $html, '>—</div>' ) );
	}

	public function test_html_contact_panel_links_and_history(): void {
		$html = promen_request_mail_html( $this->req() );
		$this->assertStringContainsString( 'href="mailto:ivanov@company.ru"', $html );
		$this->assertStringContainsString( 'href="tel:+79123456789"', $html );
		$this->assertStringContainsString( '+7 (912) 345-67-89', $html );
		$this->assertStringContainsString( 'Ранее с этого контакта: 2 заявки, последняя 12.08.2026', $html );
		$this->assertStringContainsString( 'Ответить по почте', $html );
	}

	public function test_html_first_request_and_phone_only(): void {
		$html = promen_request_mail_html( $this->req( [
			'history' => [ 'count' => 0, 'last' => '' ],
			'fields'  => [ 'product' => 'Тройник', 'contact' => '8 912 345 67 89' ],
		] ) );
		$this->assertStringContainsString( 'Первая заявка с этого контакта', $html );
		$this->assertStringNotContainsString( 'Ответить по почте', $html );
		$this->assertStringContainsString( 'связь только по телефону', $html );
	}

	public function test_html_attachment_product_link_and_service_rows(): void {
		$html = promen_request_mail_html( $this->req( [
			'attachment'  => [ 'original' => 'chertezh.pdf', 'url' => 'https://prom-en.com/u/x.pdf', 'size' => 2621440 ],
			'product_url' => 'https://prom-en.com/product/otvod-90/',
		] ) );
		$this->assertStringContainsString( 'chertezh.pdf', $html );
		$this->assertStringContainsString( 'PDF · 2,5 МБ', $html );
		$this->assertStringContainsString( 'href="https://prom-en.com/u/x.pdf"', $html );
		$this->assertStringContainsString( 'href="https://prom-en.com/product/otvod-90/"', $html );
		$this->assertStringContainsString( 'Каталог: Отводы', $html );
		$this->assertStringContainsString( 'модальное окно «Расчёт стоимости»', $html );
		$this->assertStringContainsString( 'Chrome 128, Windows · IP 10.0.0.1', $html );
		$this->assertStringContainsString( 'заявка №152 сохранена', $html );
	}

	public function test_ua_short(): void {
		$this->assertSame( 'Яндекс Браузер 24, Windows', promen_request_ua_short( 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0 YaBrowser/24.1 Safari/537.36' ) );
		$this->assertSame( 'Safari 17, iPhone', promen_request_ua_short( 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Version/17.0 Safari/604.1' ) );
		$this->assertSame( '', promen_request_ua_short( '' ) );
	}

	public function test_plural(): void {
		$this->assertSame( 'заявка', promen_mail_plural( 1, 'заявка', 'заявки', 'заявок' ) );
		$this->assertSame( 'заявки', promen_mail_plural( 3, 'заявка', 'заявки', 'заявок' ) );
		$this->assertSame( 'заявок', promen_mail_plural( 11, 'заявка', 'заявки', 'заявок' ) );
		$this->assertSame( 'заявка', promen_mail_plural( 21, 'заявка', 'заявки', 'заявок' ) );
	}
}
