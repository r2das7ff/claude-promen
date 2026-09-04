<?php
/**
 * Эвристики антиспама (mu-plugins/promen-antispam.php): что считается
 * рассылкой, а что живой заявкой. Проверка токена SmartCaptcha сюда не
 * входит — она ходит в сеть, её место в ручной проверке с боевыми ключами.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../wp-content/mu-plugins/promen-antispam.php';

final class AntispamTest extends TestCase {

	/** @dataProvider spamCases */
	public function test_spam_is_caught( string $label, array $post ): void {
		$this->assertNotSame( '', promen_antispam_reason( $post ), $label );
	}

	/** @dataProvider cleanCases */
	public function test_real_request_passes( string $label, array $post ): void {
		$this->assertSame( '', promen_antispam_reason( $post ), $label );
	}

	public static function spamCases(): array {
		return [
			'промокод и ссылка, латиница' => [ 'рассылка со скрина 03.09.2026', [
				'name'    => 'RobertHoisp',
				'company' => 'google',
				'topic'   => 'Сотрудничество / поставщикам',
				'contact' => 'wjip@mraedlh.af',
				'task'    => 'A $25,000 promo code for the bold https://telegra.ph/Win-the-jackpot-today',
			] ],
			'ссылка в поле «Компания»' => [ 'ссылка в служебном поле', [
				'name'    => 'Иван',
				'company' => 'https://spam.example/promo',
				'contact' => 'ivan@zavod.ru',
				'task'    => 'Нужны отводы 90 градусов',
			] ],
			'www без схемы в поле «Имя»' => [ 'домен вместо имени', [
				'name'    => 'www.spam-shop.example',
				'contact' => '+7 999 000 00 00',
			] ],
			'две ссылки в сообщении' => [ 'сообщение-каталог ссылок', [
				'name'    => 'Иван',
				'contact' => 'ivan@zavod.ru',
				'task'    => 'Смотрите http://a.example и http://b.example',
			] ],
			'BBCode-ссылка' => [ 'разметка форумного спама', [
				'name'    => 'Иван',
				'contact' => 'ivan@zavod.ru',
				'task'    => 'Отводы [url=http://spam.example]тут[/url]',
			] ],
			'англоязычный спам с русской темой из списка' => [ 'тема из select не считается языком заявки', [
				'name'    => 'Angela',
				'company' => 'SEO Boost',
				'topic'   => 'Сотрудничество / поставщикам',
				'contact' => 'angela@promo.example',
				'task'    => 'We can boost your traffic, details here https://promo.example/offer',
			] ],
		];
	}

	public static function cleanCases(): array {
		return [
			'обычная заявка КП' => [ 'форма каталога целиком', [
				'product'  => 'Отвод 90°',
				'standard' => 'ГОСТ 17375-2001',
				'dn'       => 'DN 100',
				'pn'       => 'PN 16',
				'material' => '09Г2С',
				'qty'      => '120',
				'deadline' => '30 календарных дней',
				'contact'  => 'snab@teplo.ru',
				'task'     => 'Нужен расчёт с доставкой в Челябинск',
			] ],
			'почта прямо в поле «Имя»' => [ 'частая привычка заказчиков — не ссылка', [
				'name'    => 'Иван Петров, ivan.petrov@mail.ru',
				'company' => 'ООО «Ромашка», romashka.ru',
				'contact' => 'ivan.petrov@mail.ru',
				'task'    => 'Пришлите КП на фланцы',
			] ],
			'ссылка на чертёж в русском тексте' => [ 'живой заказчик со ссылкой на облако', [
				'name'    => 'Сергей',
				'topic'   => 'Коммерческий запрос / расчёт',
				'contact' => '+7 912 345-67-89',
				'task'    => 'Чертёж лежит тут: https://disk.yandex.ru/d/abc123 — посчитайте, пожалуйста',
			] ],
			'английский текст без ссылок' => [ 'иностранный заказчик', [
				'name'    => 'John Smith',
				'company' => 'Steel Trade Ltd',
				'contact' => 'john@steeltrade.com',
				'task'    => 'Please send your quotation for 200 elbows DN 100',
			] ],
			'только контакт' => [ 'минимальная заявка', [ 'contact' => 'a@b.ru' ] ],
		];
	}
}
