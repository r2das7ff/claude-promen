<?php
/**
 * Plugin Name: PROM-EN Requests — письмо менеджеру
 * Description: Тема, текстовая и HTML-версии письма о заявке с сайта. Чистый PHP
 *              без обращений к WordPress — данные готовит promen-requests.php,
 *              здесь только вёрстка; поэтому файл покрыт юнит-тестами
 *              (tests/unit/RequestMailTest.php).
 *
 * Зачем HTML. Письмо на zakaz@ читает менеджер: ему нужно с одного взгляда
 * понять, ЧТО просят, КТО и КАК с ним связаться, какие параметры изделия и
 * откуда пришла заявка. Плоский список «Поле: значение» этого не даёт.
 * Вёрстка повторяет форму s10 сайта: сетка полей с тонкими линиями, моно-
 * подписи капителью, тёмная панель контакта — те же токены, что в base.css.
 *
 * Ограничения почтовых клиентов: таблицы вместо grid/flex, все стили inline,
 * без скруглений (их и на сайте нет), без веб-шрифтов — стек с запасом на
 * Arial; у кого DINPro стоит локально, тот увидит его.
 */

defined( 'ABSPATH' ) || exit;

/** Подписи полей — общие для текста и HTML. */
const PROMEN_MAIL_LABELS = [
	'name'     => 'ФИО / Контактное лицо',
	'company'  => 'Организация',
	'topic'    => 'Тема обращения',
	'product'  => 'Наименование',
	'sku'      => 'Артикул',
	'standard' => 'Стандарт',
	'dn'       => 'DN / D, мм',
	'pn'       => 'Давление',
	'material' => 'Материал / марка стали',
	'qty'      => 'Количество, шт',
	'deadline' => 'Срок',
	'city'     => 'Город доставки',
	'delivery' => 'Расчёт доставки',
	'contact'  => 'Контакт',
];

/** Пресеты, у которых в письме нужна полная сетка параметров изделия (пустые ячейки — прочерком). */
const PROMEN_MAIL_FULL_GRID = [ 'kp', 'calc', 'product' ];

/** Экранирование для HTML-письма: без WP, чтобы файл жил и в тестах. */
function promen_mail_esc( $s ): string {
	return htmlspecialchars( (string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

/**
 * Разбор поля «email / телефон»: заявитель пишет туда что угодно —
 * «ivanov@x.ru», «+7 912 345-67-89», «8(351)2170099, ivanov@x.ru».
 * Возвращает email, телефон как написан, tel:-форму и красивую запись.
 */
function promen_request_contact_parse( string $contact ): array {
	$out = [ 'email' => '', 'phone' => '', 'tel' => '', 'phone_pretty' => '' ];

	if ( preg_match( '/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/u', $contact, $m ) ) {
		$out['email'] = $m[0];
		$contact      = str_replace( $m[0], ' ', $contact );
	}

	// Телефон: от 10 цифр, между ними допустимы пробелы, скобки, дефисы, точки.
	if ( preg_match( '/\+?\d[\d\s\-().]{8,}\d/u', $contact, $m ) ) {
		$digits = preg_replace( '/\D+/', '', $m[0] );
		if ( strlen( $digits ) >= 10 ) {
			$out['phone'] = trim( $m[0] );
			if ( 11 === strlen( $digits ) && in_array( $digits[0], [ '7', '8' ], true ) ) {
				$digits = '7' . substr( $digits, 1 );
			} elseif ( 10 === strlen( $digits ) ) {
				$digits = '7' . $digits;
			}
			$out['tel'] = '+' . $digits;
			if ( 11 === strlen( $digits ) && '7' === $digits[0] ) {
				$out['phone_pretty'] = sprintf(
					'+7 (%s) %s-%s-%s',
					substr( $digits, 1, 3 ),
					substr( $digits, 4, 3 ),
					substr( $digits, 7, 2 ),
					substr( $digits, 9, 2 )
				);
			} else {
				$out['phone_pretty'] = $out['tel'];
			}
		}
	}
	return $out;
}

/** Браузер и ОС из User-Agent — коротко, для служебной строки письма. */
function promen_request_ua_short( string $ua ): string {
	if ( '' === $ua ) {
		return '';
	}
	$browser = '';
	// Порядок важен: Chromium-браузеры несут и «Chrome/», и «Safari/»;
	// у настоящего Safari номер версии стоит в «Version/».
	foreach ( [
		'#YaBrowser/(\d+)#'           => 'Яндекс Браузер',
		'#Edg/(\d+)#'                 => 'Edge',
		'#OPR/(\d+)#'                 => 'Opera',
		'#Firefox/(\d+)#'             => 'Firefox',
		'#Chrome/(\d+)#'              => 'Chrome',
		'#Version/(\d+)[^ ]* Safari#' => 'Safari',
	] as $re => $label ) {
		if ( preg_match( $re, $ua, $m ) ) {
			$browser = $label . ' ' . $m[1];
			break;
		}
	}
	$os = '';
	foreach ( [
		'Windows'   => 'Windows',
		'Android'   => 'Android',
		'iPhone'    => 'iPhone',
		'iPad'      => 'iPad',
		'Mac OS X'  => 'macOS',
		'Linux'     => 'Linux',
	] as $needle => $label ) {
		if ( false !== strpos( $ua, $needle ) ) {
			$os = $label;
			break;
		}
	}
	return trim( $browser . ( $browser && $os ? ', ' : '' ) . $os );
}

/** Размер файла по-человечески. */
function promen_request_filesize( int $bytes ): string {
	if ( $bytes >= 1048576 ) {
		return str_replace( '.', ',', (string) round( $bytes / 1048576, 1 ) ) . ' МБ';
	}
	if ( $bytes >= 1024 ) {
		return (string) round( $bytes / 1024 ) . ' КБ';
	}
	return $bytes . ' Б';
}

/** Обрезка для темы письма и превью. */
function promen_mail_trim( string $s, int $len ): string {
	$s = trim( preg_replace( '/\s+/u', ' ', $s ) );
	return mb_strlen( $s ) > $len ? rtrim( mb_substr( $s, 0, $len - 1 ) ) . '…' : $s;
}

/** Что именно просят — коротко: изделие, иначе тема, иначе артикул. */
function promen_request_mail_what( array $req ): string {
	$f = $req['fields'];
	return $f['product'] ?? $f['topic'] ?? $f['sku'] ?? '';
}

/** Заявитель для темы: организация, иначе имя. */
function promen_request_mail_who( array $req ): string {
	$f = $req['fields'];
	return $f['company'] ?? $f['name'] ?? '';
}

/**
 * Тема письма. Менеджер сортирует ящик по теме, поэтому в ней сразу
 * номер, тип, изделие и от кого:
 *   «Заявка №152 · Расчёт стоимости · Отвод 90° DN100 · ООО «Заказчик»».
 */
function promen_request_mail_subject( array $req ): string {
	$parts = [ 'Заявка' . ( ! empty( $req['id'] ) ? ' №' . $req['id'] : '' ), $req['preset_label'] ];
	$what  = promen_request_mail_what( $req );
	if ( '' !== $what ) {
		$parts[] = promen_mail_trim( $what, 60 );
	}
	$who = promen_request_mail_who( $req );
	if ( '' !== $who ) {
		$parts[] = promen_mail_trim( $who, 40 );
	}
	return implode( ' · ', $parts );
}

/**
 * Текстовая версия: идёт в запись CPT (админка) и в AltBody письма для
 * клиентов без HTML. Формат прежний, построчный: по строке «Контакт: …»
 * ищется история заявок, менять её нельзя.
 */
function promen_request_mail_text( array $req ): string {
	$lines = [];
	if ( ! empty( $req['id'] ) ) {
		$lines[] = 'Заявка №' . $req['id'] . ( ! empty( $req['time'] ) ? ' от ' . $req['time'] . ' МСК' : '' );
	}
	$lines[] = 'Тип запроса: ' . $req['preset_label'];
	foreach ( PROMEN_MAIL_LABELS as $key => $label ) {
		if ( isset( $req['fields'][ $key ] ) && '' !== $req['fields'][ $key ] ) {
			$lines[] = $label . ': ' . $req['fields'][ $key ];
		}
	}
	if ( ! empty( $req['product_url'] ) ) {
		$lines[] = 'Карточка товара: ' . $req['product_url'];
	}
	if ( '' !== ( $req['task'] ?? '' ) ) {
		$lines[] = "Описание задачи:\n" . $req['task'];
	}
	if ( ! empty( $req['attachment'] ) ) {
		$lines[] = 'Вложение: ' . $req['attachment']['original'] . "\n" . $req['attachment']['url'];
	}
	if ( ! empty( $req['history']['count'] ) ) {
		$lines[] = 'Ранее с этого контакта: ' . $req['history']['count'] . ' (последняя ' . $req['history']['last'] . ')';
	}
	if ( ! empty( $req['referer'] ) ) {
		$lines[] = 'Страница: ' . $req['referer'];
	}
	$service = array_filter( [ $req['ip'] ?? '', promen_request_ua_short( $req['ua'] ?? '' ) ] );
	if ( $service ) {
		$lines[] = 'Отправлено: ' . implode( ', ', $service );
	}
	return implode( "\n", $lines );
}

/* ───────────────────────── HTML ───────────────────────── */

/** Токены сайта (base.css :root). */
const PROMEN_MAIL_C = [
	'bg'     => '#E8ECF0',
	'bg2'    => '#DDE3EA',
	'dark'   => '#0F2A44',
	'blue'   => '#1E3D5C',
	'g1'     => '#6D8CA6',
	'g1t'    => '#4E6B84',
	'g2'     => '#A9B7C6',
	'white'  => '#FFFFFF',
	'ln'     => '#C8D0D8',
	'ln2'    => '#A0B0BC',
];

const PROMEN_MAIL_FONT      = "'DINPro','Arial Narrow',Arial,Helvetica,sans-serif";
const PROMEN_MAIL_FONT_COND = "'DINProCond','DINPro','Arial Narrow',Arial,Helvetica,sans-serif";

/** Моно-подпись капителью — как .s10-field-label на сайте. */
function promen_mail_label( string $text, string $color = '' ): string {
	$color = $color ?: PROMEN_MAIL_C['g1t'];
	return '<div style="font-family:' . PROMEN_MAIL_FONT . ';font-size:10px;line-height:12px;letter-spacing:2px;text-transform:uppercase;color:' . $color . ';margin:0 0 8px;">' . promen_mail_esc( $text ) . '</div>';
}

/** Заголовок блока: тонкая линия + подпись, как .s10-form-label. */
function promen_mail_section_title( string $text ): string {
	return '<tr><td style="padding:26px 32px 12px;">'
		. '<div style="font-family:' . PROMEN_MAIL_FONT . ';font-size:10.5px;line-height:13px;letter-spacing:2.5px;text-transform:uppercase;color:' . PROMEN_MAIL_C['g1t'] . ';">'
		. '<span style="display:inline-block;width:22px;height:1px;background:' . PROMEN_MAIL_C['g1'] . ';vertical-align:middle;margin-right:10px;"></span>'
		. promen_mail_esc( $text ) . '</div></td></tr>';
}

/** Ячейка сетки параметров: подпись + значение; пустое — прочерком. */
function promen_mail_cell( string $label, string $value_html, bool $wide ): string {
	$empty = '' === $value_html;
	return '<td' . ( $wide ? ' colspan="2"' : ' width="50%"' ) . ' valign="top" bgcolor="' . PROMEN_MAIL_C['bg'] . '" style="padding:14px 16px 12px;background:' . PROMEN_MAIL_C['bg'] . ';">'
		. promen_mail_label( $label )
		. '<div style="font-family:' . PROMEN_MAIL_FONT . ';font-size:15px;line-height:20px;font-weight:' . ( $empty ? '400' : '700' ) . ';color:' . ( $empty ? PROMEN_MAIL_C['g2'] : PROMEN_MAIL_C['dark'] ) . ';word-break:break-word;overflow-wrap:anywhere;">'
		. ( $empty ? '—' : $value_html ) . '</div></td>';
}

/**
 * Сетка «Изделие»: для форм КП/расчёта/позиции — фиксированная раскладка со
 * всеми ячейками (менеджер привыкает к месту каждого параметра), для
 * остальных пресетов — только заполненные, парами.
 */
function promen_mail_product_grid( array $req ): string {
	$f    = $req['fields'];
	$keys = [ 'product', 'sku', 'standard', 'dn', 'pn', 'material', 'qty', 'deadline' ];
	$full = in_array( $req['preset'], PROMEN_MAIL_FULL_GRID, true );

	$has = false;
	foreach ( $keys as $k ) {
		if ( isset( $f[ $k ] ) && '' !== $f[ $k ] ) {
			$has = true;
			break;
		}
	}
	if ( ! $has ) {
		return '';
	}

	if ( $full ) {
		$rows = [ [ 'product' ], [ 'sku', 'standard' ], [ 'dn', 'pn' ], [ 'material', 'qty' ], [ 'deadline' ] ];
	} else {
		$present = array_values( array_filter( $keys, fn( $k ) => isset( $f[ $k ] ) && '' !== $f[ $k ] ) );
		$rows    = array_chunk( $present, 2 );
	}

	$labels = PROMEN_MAIL_LABELS;
	if ( 'docs' === $req['preset'] ) {
		$labels['product'] = 'Документ';
	}

	$html = '<tr><td style="padding:0 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="1" border="0" bgcolor="' . PROMEN_MAIL_C['ln2'] . '" style="border-collapse:separate;background:' . PROMEN_MAIL_C['ln2'] . ';">';
	foreach ( $rows as $row ) {
		$html .= '<tr>';
		$wide  = 1 === count( $row );
		foreach ( $row as $k ) {
			$val = isset( $f[ $k ] ) ? promen_mail_esc( $f[ $k ] ) : '';
			if ( 'product' === $k && '' !== $val && ! empty( $req['product_url'] ) ) {
				$val .= '<div style="margin-top:6px;"><a href="' . promen_mail_esc( $req['product_url'] ) . '" style="font-family:' . PROMEN_MAIL_FONT . ';font-size:11px;line-height:14px;letter-spacing:1.2px;text-transform:uppercase;font-weight:400;color:' . PROMEN_MAIL_C['g1t'] . ';text-decoration:underline;">Открыть карточку товара →</a></div>';
			}
			$html .= promen_mail_cell( $labels[ $k ], $val, $wide );
		}
		$html .= '</tr>';
	}
	return $html . '</table></td></tr>';
}

/** Кнопка в стиле .s10-submit: залитая или контурная. */
function promen_mail_button( string $href, string $text, bool $filled ): string {
	$bg    = $filled ? PROMEN_MAIL_C['white'] : 'transparent';
	$color = $filled ? PROMEN_MAIL_C['dark'] : PROMEN_MAIL_C['white'];
	$brd   = $filled ? PROMEN_MAIL_C['white'] : PROMEN_MAIL_C['g1'];
	return '<td style="padding:0 12px 0 0;"><a href="' . promen_mail_esc( $href ) . '" style="display:inline-block;padding:12px 20px;border:1px solid ' . $brd . ';background:' . $bg . ';color:' . $color . ';font-family:' . PROMEN_MAIL_FONT . ';font-size:12px;line-height:14px;font-weight:700;letter-spacing:1px;text-transform:uppercase;text-decoration:none;">' . promen_mail_esc( $text ) . '</a></td>';
}

/** Тёмная панель контакта — кто написал и как ответить. */
function promen_mail_contact_panel( array $req ): string {
	$f  = $req['fields'];
	$c  = promen_request_contact_parse( $f['contact'] ?? '' );
	$C  = PROMEN_MAIL_C;
	$fn = PROMEN_MAIL_FONT;

	$name    = $f['name'] ?? '';
	$company = $f['company'] ?? '';

	$html = '<tr><td style="padding:0 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="' . $C['dark'] . '" style="background:' . $C['dark'] . ';"><tr><td style="padding:24px 28px 26px;">';

	/*
	 * Заголовок панели — имя; без имени — организация; без обоих (формы
	 * расчёта и позиции имя не спрашивают) заголовком становится сам контакт.
	 */
	$headline = '' !== $name ? $name : $company;
	if ( '' !== $headline ) {
		$html .= promen_mail_label( '' !== $name ? 'Контактное лицо' : 'Организация', $C['g1'] );
		$html .= '<div style="font-family:' . $fn . ';font-size:20px;line-height:26px;font-weight:700;color:' . $C['white'] . ';">' . promen_mail_esc( $headline ) . '</div>';
		if ( '' !== $name && '' !== $company ) {
			$html .= '<div style="font-family:' . $fn . ';font-size:14px;line-height:20px;color:' . $C['g2'] . ';margin-top:2px;">' . promen_mail_esc( $company ) . '</div>';
		}
	}

	// Контакт: email и телефон ссылками; если разобрать не удалось — как написали.
	$contact_html = [];
	if ( '' !== $c['email'] ) {
		$contact_html[] = '<a href="mailto:' . promen_mail_esc( $c['email'] ) . '" style="color:' . $C['white'] . ';text-decoration:underline;">' . promen_mail_esc( $c['email'] ) . '</a>';
	}
	if ( '' !== $c['tel'] ) {
		$contact_html[] = '<a href="tel:' . promen_mail_esc( $c['tel'] ) . '" style="color:' . $C['white'] . ';text-decoration:underline;">' . promen_mail_esc( $c['phone_pretty'] ) . '</a>';
	}
	if ( ! $contact_html && '' !== ( $f['contact'] ?? '' ) ) {
		$contact_html[] = '<span style="color:' . $C['white'] . ';">' . promen_mail_esc( $f['contact'] ) . '</span>';
	}
	if ( $contact_html ) {
		$size = '' !== $headline ? 'font-size:16px;line-height:22px;' : 'font-size:20px;line-height:26px;';
		$html .= '<div style="' . ( '' !== $headline ? 'margin-top:16px;' : '' ) . '">' . promen_mail_label( 'Контакт для ответа', $C['g1'] )
			. '<div style="font-family:' . $fn . ';' . $size . 'font-weight:700;word-break:break-word;overflow-wrap:anywhere;">' . implode( '<span style="color:' . $C['g1'] . ';padding:0 8px;">·</span>', $contact_html ) . '</div></div>';
	}

	// История: повторный клиент — важный сигнал для менеджера.
	if ( isset( $req['history'] ) ) {
		$n    = (int) ( $req['history']['count'] ?? 0 );
		$note = $n > 0
			? 'Ранее с этого контакта: ' . $n . ' ' . promen_mail_plural( $n, 'заявка', 'заявки', 'заявок' ) . ', последняя ' . $req['history']['last']
			: 'Первая заявка с этого контакта';
		$html .= '<div style="font-family:' . $fn . ';font-size:12px;line-height:16px;color:' . $C['g2'] . ';margin-top:12px;">' . promen_mail_esc( $note ) . '</div>';
	}

	$buttons = '';
	if ( '' !== $c['email'] ) {
		$buttons .= promen_mail_button( 'mailto:' . $c['email'] . '?subject=' . rawurlencode( 'Re: ' . promen_request_mail_subject( $req ) ), 'Ответить по почте →', true );
	}
	if ( '' !== $c['tel'] ) {
		$buttons .= promen_mail_button( 'tel:' . $c['tel'], 'Позвонить →', false );
	}
	if ( '' !== $buttons ) {
		$html .= '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;"><tr>' . $buttons . '</tr></table>';
	}

	return $html . '</td></tr></table></td></tr>';
}

function promen_mail_plural( int $n, string $one, string $few, string $many ): string {
	$n10 = $n % 10;
	$n100 = $n % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		return $one;
	}
	if ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 12 || $n100 > 14 ) ) {
		return $few;
	}
	return $many;
}

/** Блок с одним текстовым полем в ячейке сетки (задача, доставка). */
function promen_mail_text_block( string $label, string $value_html ): string {
	return '<tr><td style="padding:0 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="1" border="0" bgcolor="' . PROMEN_MAIL_C['ln2'] . '" style="border-collapse:separate;background:' . PROMEN_MAIL_C['ln2'] . ';"><tr>'
		. promen_mail_cell( $label, $value_html, true )
		. '</tr></table></td></tr>';
}

/**
 * HTML-письмо целиком. $req — см. promen_request_collect() в promen-requests.php.
 */
function promen_request_mail_html( array $req ): string {
	$C   = PROMEN_MAIL_C;
	$fn  = PROMEN_MAIL_FONT;
	$f   = $req['fields'];
	$esc = 'promen_mail_esc';

	$what = promen_request_mail_what( $req );
	$c    = promen_request_contact_parse( $f['contact'] ?? '' );

	// Превью в списке писем: то, чего нет в теме — имя и контакт.
	$preheader = promen_mail_trim( implode( ' · ', array_filter( [
		$f['name'] ?? '',
		$f['company'] ?? '',
		$c['email'] ?: ( $c['phone_pretty'] ?: ( $f['contact'] ?? '' ) ),
		'' !== ( $req['task'] ?? '' ) ? $req['task'] : '',
	] ) ), 150 );

	$home     = $req['home_url'] ?? 'https://prom-en.com';
	$home_txt = preg_replace( '#^https?://#', '', $home );

	$h  = '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">';
	$h .= '<meta name="viewport" content="width=device-width,initial-scale=1">';
	$h .= '<meta name="color-scheme" content="light"><meta name="supported-color-schemes" content="light">';
	$h .= '<title>' . $esc( promen_request_mail_subject( $req ) ) . '</title></head>';
	$h .= '<body style="margin:0;padding:0;background:' . $C['bg'] . ';" bgcolor="' . $C['bg'] . '">';
	$h .= '<div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:' . $C['bg'] . ';">' . $esc( $preheader ) . '</div>';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="' . $C['bg'] . '" style="background:' . $C['bg'] . ';"><tr><td align="center" style="padding:28px 12px;">';
	$h .= '<table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="' . $C['white'] . '" style="width:640px;max-width:100%;background:' . $C['white'] . ';border:1px solid ' . $C['ln'] . ';">';

	// ── Шапка: логотип + метка «заявка с сайта» ──
	$h .= '<tr><td style="padding:24px 32px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>';
	$h .= '<td valign="middle" style="padding:0;">';
	if ( ! empty( $req['logo_url'] ) ) {
		$h .= '<a href="' . $esc( $home ) . '" style="text-decoration:none;"><img src="' . $esc( $req['logo_url'] ) . '" width="170" height="35" alt="Промышленная Энергетика" style="display:block;width:170px;height:auto;border:0;font-family:' . $fn . ';font-size:14px;font-weight:700;color:' . $C['dark'] . ';"></a>';
	} else {
		$h .= '<div style="font-family:' . $fn . ';font-size:14px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:' . $C['dark'] . ';">Промышленная Энергетика</div>';
	}
	$h .= '</td><td valign="middle" align="right" style="padding:0;">';
	$h .= '<span style="display:inline-block;font-family:' . $fn . ';font-size:11px;line-height:11px;letter-spacing:2px;text-transform:uppercase;color:' . $C['g1t'] . ';border:1px solid ' . $C['g1'] . ';padding:5px 9px 4px;">Заявка с сайта</span>';
	$h .= '</td></tr></table></td></tr>';

	// ── Линия ──
	$h .= '<tr><td style="padding:20px 32px 0;"><div style="height:1px;background:' . $C['ln'] . ';font-size:0;line-height:0;">&nbsp;</div></td></tr>';

	// ── Заголовок: номер и время, тип, изделие ──
	$meta = ( ! empty( $req['id'] ) ? '№ ' . $req['id'] : '' ) . ( ! empty( $req['time'] ) ? ( ! empty( $req['id'] ) ? ' · ' : '' ) . $req['time'] . ' МСК' : '' );
	$h   .= '<tr><td style="padding:22px 32px 0;">';
	if ( '' !== $meta ) {
		$h .= '<div style="font-family:' . $fn . ';font-size:11px;line-height:14px;letter-spacing:2px;text-transform:uppercase;color:' . $C['g1t'] . ';margin:0 0 10px;">' . $esc( $meta ) . '</div>';
	}
	$h .= '<div style="font-family:' . PROMEN_MAIL_FONT_COND . ';font-size:30px;line-height:32px;font-weight:900;letter-spacing:-0.3px;text-transform:uppercase;color:' . $C['dark'] . ';">' . $esc( $req['preset_label'] ) . '</div>';
	if ( '' !== $what ) {
		$h .= '<div style="font-family:' . $fn . ';font-size:16px;line-height:22px;font-weight:300;color:' . $C['blue'] . ';margin-top:8px;">' . $esc( $what ) . '</div>';
	}
	$h .= '</td></tr>';

	// ── Контакт ──
	$h .= '<tr><td style="height:22px;font-size:0;line-height:0;">&nbsp;</td></tr>';
	$h .= promen_mail_contact_panel( $req );

	// ── Изделие ──
	$grid = promen_mail_product_grid( $req );
	if ( '' !== $grid ) {
		$h .= promen_mail_section_title( 'Изделие и параметры' ) . $grid;
	}

	// ── Доставка ──
	$delivery = [];
	foreach ( [ 'city', 'delivery' ] as $k ) {
		if ( isset( $f[ $k ] ) && '' !== $f[ $k ] ) {
			$delivery[ $k ] = $f[ $k ];
		}
	}
	if ( $delivery ) {
		$h .= promen_mail_section_title( 'Доставка' );
		$h .= '<tr><td style="padding:0 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="1" border="0" bgcolor="' . $C['ln2'] . '" style="border-collapse:separate;background:' . $C['ln2'] . ';"><tr>';
		$wide = 1 === count( $delivery );
		foreach ( $delivery as $k => $v ) {
			if ( 'delivery' === $k ) {
				// product.js пишет «да; терминал; N шт; ~цена ₽» — убираем служебное «да» и точки с запятой.
				$v = str_replace( '; ', ' · ', preg_replace( '/^да;\s*/u', '', $v ) );
			}
			$h .= promen_mail_cell( PROMEN_MAIL_LABELS[ $k ], $esc( $v ), $wide );
		}
		$h .= '</tr></table></td></tr>';
	}

	// ── Задача / сообщение ──
	if ( '' !== ( $req['task'] ?? '' ) || ( isset( $f['topic'] ) && '' !== $f['topic'] ) ) {
		$h .= promen_mail_section_title( 'contact' === $req['preset'] ? 'Сообщение' : 'Описание задачи' );
		if ( isset( $f['topic'] ) && '' !== $f['topic'] ) {
			$h .= promen_mail_text_block( PROMEN_MAIL_LABELS['topic'], $esc( $f['topic'] ) );
			$h .= '<tr><td style="height:1px;font-size:0;line-height:0;">&nbsp;</td></tr>';
		}
		if ( '' !== ( $req['task'] ?? '' ) ) {
			$h .= promen_mail_text_block( 'Текст', '<span style="font-weight:400;">' . nl2br( $esc( $req['task'] ) ) . '</span>' );
		}
	}

	// ── Вложение ──
	if ( ! empty( $req['attachment'] ) ) {
		$a  = $req['attachment'];
		$h .= promen_mail_section_title( 'Вложение' );
		$h .= '<tr><td style="padding:0 32px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid ' . $C['ln2'] . ';"><tr>';
		$h .= '<td valign="middle" style="padding:14px 16px;">';
		$h .= '<div style="font-family:' . $fn . ';font-size:15px;line-height:20px;font-weight:700;color:' . $C['dark'] . ';">' . $esc( $a['original'] ) . '</div>';
		$h .= '<div style="font-family:' . $fn . ';font-size:12px;line-height:16px;color:' . $C['g1t'] . ';margin-top:3px;">' . $esc( strtoupper( pathinfo( $a['original'], PATHINFO_EXTENSION ) ) ) . ( ! empty( $a['size'] ) ? ' · ' . promen_request_filesize( (int) $a['size'] ) : '' ) . ' · файл приложен к письму</div>';
		$h .= '</td><td valign="middle" align="right" style="padding:14px 16px;white-space:nowrap;">';
		$h .= '<a href="' . $esc( $a['url'] ) . '" style="display:inline-block;padding:9px 14px;border:1px solid ' . $C['ln2'] . ';font-family:' . $fn . ';font-size:11px;line-height:13px;letter-spacing:1.2px;text-transform:uppercase;color:' . $C['g1t'] . ';text-decoration:none;">↓ Скачать</a>';
		$h .= '</td></tr></table></td></tr>';
	}

	// ── Служебное: откуда, чем, куда сохранено ──
	$h .= '<tr><td style="height:28px;font-size:0;line-height:0;">&nbsp;</td></tr>';
	$h .= '<tr><td bgcolor="' . $C['bg2'] . '" style="padding:18px 32px 20px;background:' . $C['bg2'] . ';border-top:1px solid ' . $C['ln'] . ';">';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-family:' . $fn . ';font-size:12px;line-height:18px;color:' . $C['g1t'] . ';">';

	$row = fn( string $k, string $v_html ) => '<tr><td valign="top" width="120" style="padding:2px 12px 2px 0;font-size:10px;line-height:18px;letter-spacing:1.5px;text-transform:uppercase;color:' . $C['g1t'] . ';">' . $esc( $k ) . '</td><td valign="top" style="padding:2px 0;color:' . $C['blue'] . ';">' . $v_html . '</td></tr>';

	if ( ! empty( $req['referer'] ) ) {
		$page_txt = ! empty( $req['page_title'] ) ? $req['page_title'] : preg_replace( '#^https?://[^/]+#', '', $req['referer'] );
		$h .= $row( 'Страница', '<a href="' . $esc( $req['referer'] ) . '" style="color:' . $C['blue'] . ';text-decoration:underline;">' . $esc( promen_mail_trim( $page_txt, 90 ) ) . '</a>' );
	}
	$via = ( $req['via'] ?? '' ) === 'modal' ? 'модальное окно «' . $req['preset_label'] . '»' : 'форма на странице';
	$h  .= $row( 'Форма', $esc( $via ) );
	$sent = array_filter( [ promen_request_ua_short( $req['ua'] ?? '' ), $req['ip'] ?? '' ] );
	if ( $sent ) {
		$h .= $row( 'Отправлено', $esc( implode( ' · IP ', $sent ) ) );
	}
	if ( ! empty( $req['admin_url'] ) ) {
		$h .= $row( 'В админке', '<a href="' . $esc( $req['admin_url'] ) . '" style="color:' . $C['blue'] . ';text-decoration:underline;">заявка №' . $esc( (string) $req['id'] ) . ' сохранена в «Заявки КП»</a>' );
	}
	$h .= '</table>';

	$reply_note = '' !== $c['email']
		? 'Кнопка «Ответить» в почтовом клиенте пишет сразу заявителю: его адрес подставлен в Reply-To.'
		: 'Email заявитель не оставил — связь только по телефону.';
	$h .= '<div style="font-family:' . $fn . ';font-size:11px;line-height:16px;color:' . $C['g1t'] . ';margin-top:14px;padding-top:12px;border-top:1px solid ' . $C['ln'] . ';">' . $esc( $reply_note ) . ' Письмо сформировано автоматически сайтом <a href="' . $esc( $home ) . '" style="color:' . $C['g1t'] . ';">' . $esc( $home_txt ) . '</a>.</div>';
	$h .= '</td></tr>';

	$h .= '</table></td></tr></table></body></html>';
	return $h;
}
