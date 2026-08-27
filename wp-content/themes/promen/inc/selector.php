<?php
/**
 * ПОДБОР ИЗДЕЛИЯ — детерминированный подборщик без ИИ.
 *
 * Модуль ничего не генерирует и не «додумывает»: он переводит параметры
 * задачи (отрасль, температура, давление, типоразмер) либо строку из
 * спецификации в те же параметры запроса, что принимает `promen/v1/catalog`,
 * и отдаёт РЕАЛЬНЫЕ позиции каталога. Марки стали отбираются по справочнику
 * `promen_steel_reference()` — единственному источнику температур и PN.
 *
 * Изоляция и откат: модуль включается константой PROMEN_SELECTOR
 * (functions.php). Выключили — не регистрируется REST-маршрут, не грузятся
 * ассеты, кнопка не появляется. Форма КП работает независимо и не тронута.
 *
 * @see inc/steel-reference.php  справочник марок (temp/pn/apps)
 * @see inc/catalog-api.php      REST каталога — тот же слой поиска
 */

defined( 'ABSPATH' ) || exit;

/* ═════════════════ 01 · ПЕРЕКЛЮЧАТЕЛЬ ═════════════════ */

/** Модуль подбора включён (по умолчанию да; выключается константой). */
function promen_selector_enabled(): bool {
	return defined( 'PROMEN_SELECTOR' ) ? (bool) PROMEN_SELECTOR : true;
}

/**
 * Плавающая кнопка на всех страницах (по умолчанию нет).
 * Пока она выключена, ассеты подбора грузятся только на /podbor/ —
 * иначе 56 КБ CSS+JS ехали бы на каждую страницу сайта ради кнопки.
 */
function promen_selector_launcher_enabled(): bool {
	return defined( 'PROMEN_SELECTOR_LAUNCHER' ) ? (bool) PROMEN_SELECTOR_LAUNCHER : false;
}

/* ═════════════════ 02 · ПРАВИЛА ПО МАРКАМ СТАЛИ ═════════════════ */

/**
 * Разбор строки температуры справочника в пару [мин, макс], °C.
 * Форматы: «до +425°C» → [null, 425]; «−70…+475°C» → [-70, 475].
 * null в нижней границе = справочник её не нормирует (это не «любая»).
 *
 * @return array{0: ?int, 1: ?int}
 */
function promen_selector_temp_range( string $temp ): array {
	// Типографские минус/тире и многоточие — к ASCII, иначе regexp не увидит знак.
	$t = str_replace( [ "\u{2212}", "\u{2013}", "\u{2014}" ], '-', $temp );
	$t = str_replace( "\u{2026}", '...', $t );
	if ( ! preg_match_all( '/-?\d+/', $t, $m ) ) {
		return [ null, null ];
	}
	$nums = array_map( 'intval', $m[0] );
	if ( count( $nums ) === 1 ) {
		return [ null, $nums[0] ]; // «до +425» — только верхний предел
	}
	return [ min( $nums ), max( $nums ) ];
}

/**
 * PN справочника (номинал по ГОСТ 33259, кгс/см²) → МПа.
 * «—» у крепёжных марок = давление к марке неприменимо → null.
 */
function promen_selector_pn_mpa( string $pn ): ?float {
	if ( ! preg_match( '/\d+/', $pn, $m ) ) {
		return null;
	}
	return ( (float) $m[0] ) / 10;
}

/**
 * Справочник марок, разобранный в машиночитаемые пределы.
 *
 * @return array<string, array<string, mixed>>
 */
function promen_selector_steel_specs(): array {
	static $out = null;
	if ( null !== $out ) {
		return $out;
	}
	$out = [];
	foreach ( promen_steel_reference() as $key => $d ) {
		[ $tmin, $tmax ] = promen_selector_temp_range( (string) ( $d['temp'] ?? '' ) );
		$out[ (string) $key ] = [
			'key'      => (string) $key,
			'desc'     => (string) ( $d['desc'] ?? '' ),
			'std'      => (string) ( $d['std'] ?? '' ),
			'apps'     => array_values( (array) ( $d['apps'] ?? [] ) ),
			'temp'     => (string) ( $d['temp'] ?? '' ),
			't_min'    => $tmin,
			't_max'    => $tmax,
			'pn_label' => (string) ( $d['pn'] ?? '' ),
			'pn_mpa'   => promen_selector_pn_mpa( (string) ( $d['pn'] ?? '' ) ),
		];
	}
	return $out;
}

/**
 * Отрасль каталога (слаг фасета) → метки применения из справочника марок.
 *
 * @return array<string, string[]>
 */
function promen_selector_industry_apps(): array {
	return [
		'aes' => [ 'АЭС' ],
		'tes' => [ 'ТЭС', 'ГРЭС', 'Котлы' ],
		'gkh' => [ 'ЖКХ' ],
		'ngk' => [ 'Нефтегаз', 'Нефтехим' ],
	];
}

/**
 * Отбор марок по параметрам среды.
 *
 * Три корзины вместо двух: `warn` — марка проходит по верхнему пределу,
 * но нижний справочником не нормирован, а задана отрицательная температура.
 * Молча включить такую марку нельзя (хладостойкость не подтверждена),
 * молча выкинуть — тоже (справочник просто не содержит нижней границы).
 *
 * @param array{temp?: ?float, pressure?: ?float, industry?: string, available?: string[]} $p
 *        available — фактические марки группы (ключи справочника); пусто = не ограничивать.
 * @return array{fit: array<int, array>, warn: array<int, array>, reject: array<string, string>}
 */
function promen_selector_steel_pick( array $p ): array {
	$temp  = isset( $p['temp'] ) && $p['temp'] !== null ? (float) $p['temp'] : null;
	$pres  = isset( $p['pressure'] ) && $p['pressure'] !== null ? (float) $p['pressure'] : null;
	$ind   = (string) ( $p['industry'] ?? '' );
	// Список приходит из array_keys() карт марок, а PHP превращает ключи
	// «20» и «10» в int — без приведения строгое сравнение ниже выбрасывало
	// из рекомендаций самые ходовые углеродистые марки.
	$avail = array_map( 'strval', array_values( (array) ( $p['available'] ?? [] ) ) );
	$apps  = promen_selector_industry_apps()[ $ind ] ?? [];

	$fit    = [];
	$warn   = [];
	$reject = [];

	foreach ( promen_selector_steel_specs() as $key => $s ) {
		// PHP приводит числовые ключи массива к int: марка «20» приходит сюда
		// как 20, и строгое сравнение со списком строк её теряет.
		$key = (string) $key;
		if ( $avail && ! in_array( $key, $avail, true ) ) {
			continue; // марки нет в ассортименте группы — это не отказ, это не наш товар
		}
		$why  = [];
		$soft = false;

		if ( null !== $temp ) {
			if ( null !== $s['t_max'] && $temp > $s['t_max'] ) {
				$reject[ $key ] = 'рабочая температура выше предела марки (' . $s['temp'] . ')';
				continue;
			}
			if ( null !== $s['t_min'] && $temp < $s['t_min'] ) {
				$reject[ $key ] = 'рабочая температура ниже предела марки (' . $s['temp'] . ')';
				continue;
			}
			if ( null === $s['t_min'] && $temp < 0 ) {
				$soft  = true;
				$why[] = 'нижний предел справочником не нормирован — подтвердить хладостойкость';
			} else {
				$why[] = 'по температуре: ' . $s['temp'];
			}
		}

		if ( null !== $pres ) {
			if ( null === $s['pn_mpa'] ) {
				$reject[ $key ] = 'марка справочника не для деталей под давлением';
				continue;
			}
			if ( $pres > $s['pn_mpa'] + 1e-9 ) {
				$reject[ $key ] = 'рабочее давление выше PN марки (PN ' . $s['pn_label'] . ')';
				continue;
			}
			$why[] = 'по давлению: PN ' . $s['pn_label'];
		}

		if ( $apps ) {
			$hit = array_values( array_intersect( $apps, $s['apps'] ) );
			if ( ! $hit ) {
				$reject[ $key ] = 'справочник не относит марку к этой отрасли';
				continue;
			}
			$why[] = 'отрасль: ' . implode( ', ', $hit );
		}

		$row = [
			'key'  => (string) $key,
			'temp' => $s['temp'],
			'pn'   => $s['pn_label'],
			'desc' => $s['desc'],
			'std'  => $s['std'],
			'why'  => $why,
		];
		if ( $soft ) {
			$warn[] = $row;
		} else {
			$fit[] = $row;
		}
	}

	return [ 'fit' => $fit, 'warn' => $warn, 'reject' => $reject ];
}

/* ═════════════════ 03 · ПАРСЕР СТРОКИ СПЕЦИФИКАЦИИ ═════════════════ */

/**
 * Тип изделия → корни слов, по которым он узнаётся.
 * Порядок = приоритет: «фланцевая заглушка» должна стать заглушкой,
 * а не фланцем, поэтому zaglushki стоит выше flancy.
 *
 * @return array<string, string[]>
 */
function promen_selector_types(): array {
	return [
		'otvody'      => [ 'отвод', 'колен', 'угольник' ],
		'troyniki'    => [ 'тройник' ],
		'perekhody'   => [ 'переход', 'конус' ],
		'dnishcha'    => [ 'днищ' ],
		'zaglushki'   => [ 'заглушк' ],
		'izolyatsiya' => [ 'ппу', 'изоляц', 'пенополиуретан' ],
		'opory'       => [ 'опор' ],
		'armatura'    => [ 'задвижк', 'вентил', 'клапан', 'арматур' ],
		'bolty'       => [ 'болт' ],
		'gayki'       => [ 'гайк' ],
		'shpilki'     => [ 'шпильк' ],
		'shayby'      => [ 'шайб' ],
		'vinty'       => [ 'винт' ],
		'krepezh'     => [ 'крепеж', 'крепёж', 'метиз' ],
		'flancy'      => [ 'фланец', 'фланц', 'воротник' ],
		'tochenye'    => [ 'точен', 'точён' ],
		'truby'       => [ 'труба', 'трубы', 'трубн' ],
	];
}

/**
 * Объекты, а не изделия: «строительство котельной», «реконструкция ЦТП».
 * У такого запроса нет ни типоразмера, ни марки — есть только отрасль,
 * и это ЕДИНСТВЕННОЕ, что из него можно вывести без домыслов. Температуру
 * и давление конкретного объекта справочник не знает, поэтому подбор их
 * не подставляет, а спрашивает.
 *
 * Ключ — корень слова (падежи и «котельная/котельной» ловятся одинаково),
 * `ind` — слаги отрасли каталога в порядке вероятности.
 *
 * @return array<string, array{label: string, ind: string[]}>
 */
function promen_selector_objects(): array {
	return [
		'котельн'       => [ 'label' => 'Котельная', 'ind' => [ 'tes', 'gkh' ] ],
		'цтп'           => [ 'label' => 'ЦТП', 'ind' => [ 'gkh' ] ],
		'итп'           => [ 'label' => 'ИТП', 'ind' => [ 'gkh' ] ],
		'теплотрасс'    => [ 'label' => 'Теплотрасса', 'ind' => [ 'gkh' ] ],
		'теплов'        => [ 'label' => 'Тепловые сети', 'ind' => [ 'gkh' ] ],
		'теплосет'      => [ 'label' => 'Тепловые сети', 'ind' => [ 'gkh' ] ],
		'горяч'         => [ 'label' => 'Сети ГВС', 'ind' => [ 'gkh' ] ],
		'гвс'           => [ 'label' => 'Сети ГВС', 'ind' => [ 'gkh' ] ],
		'паропровод'    => [ 'label' => 'Паропровод', 'ind' => [ 'tes' ] ],
		'турбин'        => [ 'label' => 'Турбинное отделение', 'ind' => [ 'tes' ] ],
		'тэц'           => [ 'label' => 'ТЭЦ', 'ind' => [ 'tes' ] ],
		'грэс'          => [ 'label' => 'ГРЭС', 'ind' => [ 'tes' ] ],
		'тэс'           => [ 'label' => 'ТЭС', 'ind' => [ 'tes' ] ],
		'аэс'           => [ 'label' => 'АЭС', 'ind' => [ 'aes' ] ],
		'атомн'         => [ 'label' => 'Атомная станция', 'ind' => [ 'aes' ] ],
		'нефтепровод'   => [ 'label' => 'Нефтепровод', 'ind' => [ 'ngk' ] ],
		'газопровод'    => [ 'label' => 'Газопровод', 'ind' => [ 'ngk' ] ],
		'нпз'           => [ 'label' => 'НПЗ', 'ind' => [ 'ngk' ] ],
		'нефтехим'      => [ 'label' => 'Нефтехимия', 'ind' => [ 'ngk' ] ],
		'промысл'       => [ 'label' => 'Промысловый трубопровод', 'ind' => [ 'ngk' ] ],
		'насосн'        => [ 'label' => 'Насосная станция', 'ind' => [ 'gkh', 'ngk' ] ],
		'водоподготовк' => [ 'label' => 'Водоподготовка', 'ind' => [ 'gkh' ] ],
	];
}

/**
 * Нормализация обозначения марки для сравнения: латинские двойники →
 * кириллица, «ст.20»/«Сталь 20» → «20», убраны пробелы, точки и дефисы.
 * Одна функция для обеих сторон сравнения — иначе «12X18H10T» не найдётся.
 */
function promen_selector_steel_norm( string $s ): string {
	$s = mb_strtolower( trim( $s ), 'UTF-8' );
	$s = strtr(
		$s,
		[
			'x' => 'х',
			'h' => 'н',
			't' => 'т',
			'c' => 'с',
			'a' => 'а',
			'e' => 'е',
			'o' => 'о',
			'p' => 'р',
			'm' => 'м',
			'k' => 'к',
			'b' => 'в',
			'y' => 'у',
		]
	);
	$s = preg_replace( '/^(?:сталь|ст)\.?\s*/u', '', $s ) ?? $s;
	return (string) preg_replace( '/[\s\.\-_]/u', '', $s );
}

/**
 * Синонимы групп марок: то, что снабженец пишет вместо обозначения.
 *
 * @return array<string, string[]>
 */
function promen_selector_steel_synonyms(): array {
	return [
		'нерж'          => [ '08Х18Н10Т', '12Х18Н10Т', '12Х18Н12Т', '08Х18Н12Т' ],
		'аустенит'      => [ '08Х18Н10Т', '12Х18Н10Т', '12Х18Н12Т', '08Х18Н12Т' ],
		'хладостойк'    => [ '09Г2С', '10Г2', '10Г2С1' ],
		'теплоустойчив' => [ '12Х1МФ', '15Х1М1Ф', '15ХМ', '12ХМ', '20Х3МВФ' ],
	];
}

/**
 * Разбор произвольной строки в параметры запроса.
 *
 * Чистая логика без обращений к БД: наружный диаметр в DN здесь НЕ
 * переводится (для этого нужен каталог) — возвращается как есть в 'd'.
 *
 * @return array<string, mixed>
 */
function promen_selector_parse( string $text ): array {
	$src = trim( $text );
	$out = [
		'text'    => $src,
		'group'   => '',
		'd'       => null,
		's'       => null,
		'dn'      => null,
		'pn'      => null,
		'angle'   => null,
		'temp'    => null,
		'gost'    => [],
		'steel'   => [],
		'thread'  => '',
		'object'  => '',
		'unknown' => [],
	];
	if ( $src === '' ) {
		return $out;
	}

	$low = mb_strtolower( $src, 'UTF-8' );
	// Десятичная запятая → точка ТОЛЬКО внутри чисел: «20, 09Г2С» — это список.
	$low = (string) preg_replace( '/(\d),(\d)/u', '$1.$2', $low );
	// Все варианты знака умножения к одному «х».
	$low = str_replace( [ '×', '*', 'x' ], 'х', $low );

	$rest = $low; // остаток: из него вырезается всё распознанное
	$cut  = static function ( string &$hay, string $needle ): void {
		$pos = mb_strpos( $hay, $needle, 0, 'UTF-8' );
		if ( false !== $pos ) {
			$hay = mb_substr( $hay, 0, $pos, 'UTF-8' ) . ' '
				. mb_substr( $hay, $pos + mb_strlen( $needle, 'UTF-8' ), null, 'UTF-8' );
		}
	};
	// Вырезать слово целиком по его корню: «колено» опознаётся по «колен»,
	// и без этого в остатке зависает «о», а от «нержавейки» — «авейка».
	$cut_word = static function ( string &$hay, string $root ): void {
		$hay = (string) preg_replace( '/\S*' . preg_quote( $root, '/' ) . '\S*/u', ' ', $hay );
	};

	// 1. Норматив: ГОСТ 17375-2001, ОСТ 34.10.418-90, ТУ 1462-…
	if ( preg_match_all( '/\b(гост\s*р|гост|ост|сто|ту)\s*\.?\s*([0-9]+(?:[\.\-][0-9]+)*)/u', $rest, $mm, PREG_SET_ORDER ) ) {
		foreach ( $mm as $m ) {
			$out['gost'][] = [
				'kind'   => trim( (string) $m[1] ),
				'number' => (string) $m[2],
				'raw'    => trim( (string) $m[0] ),
			];
			$cut( $rest, $m[0] );
		}
	}

	// 2. Температура: «540°С», «до 200 градусов». Раньше угла — иначе разбор
	//    угла съест градус Цельсия. Признак температуры — буква С после знака.
	if ( preg_match( '/(-?\d+(?:\.\d+)?)\s*(?:°\s*[сc]\b|градус)/u', $rest, $m ) ) {
		$out['temp'] = (float) $m[1];
		$cut( $rest, $m[0] );
	}

	// 3. Давление: «ру16» (номинал, кгс/см² → МПа) или «1.6 МПа» напрямую.
	if ( preg_match( '/(\d+(?:\.\d+)?)\s*мпа/u', $rest, $m ) ) {
		$out['pn'] = (float) $m[1];
		$cut( $rest, $m[0] );
	} elseif ( preg_match( '/\b(?:ру|pn|рн)\s*\.?\-?\s*(\d+(?:\.\d+)?)/u', $rest, $m ) ) {
		$out['pn'] = ( (float) $m[1] ) / 10;
		$cut( $rest, $m[0] );
	}

	// 4. Резьба крепежа «М20», «M20х1.5» — раньше типоразмера, иначе «20х1.5»
	//    прочитается как диаметр × стенка. В каталоге резьба лежит в DN
	//    (шпилька M8 → DN 8), поэтому пишем её туда же. Буква — и кириллица,
	//    и латиница: набирают и так, и так.
	if ( preg_match( '/(?<![0-9а-яёa-z])[мm]\s*(\d+(?:\.\d+)?)(?:\s*х\s*(\d+(?:\.\d+)?))?(?![0-9а-яё])/u', $rest, $m ) ) {
		$out['dn']     = (float) $m[1];
		$out['thread'] = 'M' . $m[1] . ( isset( $m[2] ) && $m[2] !== '' ? '×' . $m[2] : '' );
		$cut( $rest, $m[0] );
	}

	// 5. Типоразмер «108х4» → наружный диаметр × стенка.
	//    Границы из букв обязательны: без них «12Х18Н10Т» (набранное латиницей
	//    и приведённое к «12х18…») читается как размер 12×18.
	if ( preg_match( '/(?<![0-9а-яёa-z])(\d+(?:\.\d+)?)\s*х\s*(\d+(?:\.\d+)?)(?![0-9а-яёa-z])/u', $rest, $m ) ) {
		$out['d'] = (float) $m[1];
		$out['s'] = (float) $m[2];
		$cut( $rest, $m[0] );
	}

	// 6. Условный проход: «ду100», «dn 100», «ду-100».
	if ( preg_match( '/\b(?:ду|dn|дн)\s*\.?\-?\s*(\d+(?:\.\d+)?)/u', $rest, $m ) ) {
		$out['dn'] = (float) $m[1];
		$cut( $rest, $m[0] );
	}

	// 7. Угол: только со знаком градуса или словом — «90°», «45 град».
	if ( preg_match( '/(\d{1,3})\s*(?:°|град)/u', $rest, $m ) ) {
		$out['angle'] = (float) $m[1];
		$cut( $rest, $m[0] );
	}

	// 8. Тип изделия.
	foreach ( promen_selector_types() as $group => $roots ) {
		foreach ( $roots as $root ) {
			if ( mb_strpos( $rest, $root, 0, 'UTF-8' ) !== false ) {
				$out['group'] = (string) $group;
				$cut_word( $rest, $root );
				break 2;
			}
		}
	}

	// 9. Объект. После типа изделия: в «отводы для котельной» ведущее слово —
	//    отвод, а котельная лишь уточняет отрасль. Одно другому не мешает.
	foreach ( promen_selector_objects() as $root => $obj ) {
		if ( mb_strpos( $rest, (string) $root, 0, 'UTF-8' ) !== false ) {
			$out['object'] = (string) $root;
			$cut_word( $rest, (string) $root );
			break;
		}
	}

	// 10. Марка стали: сначала синонимы, затем сравнение по справочнику.
	foreach ( promen_selector_steel_synonyms() as $root => $keys ) {
		if ( mb_strpos( $rest, (string) $root, 0, 'UTF-8' ) !== false ) {
			$out['steel'] = array_merge( $out['steel'], $keys );
			$cut_word( $rest, (string) $root );
		}
	}
	$ref_norm = [];
	foreach ( array_keys( promen_steel_reference() ) as $key ) {
		$n = promen_selector_steel_norm( (string) $key );
		if ( $n !== '' ) {
			$ref_norm[ $n ] = (string) $key;
		}
	}
	foreach ( preg_split( '/[\s,;]+/u', $rest ) ?: [] as $token ) {
		$token = trim( (string) $token, " \t\n\r\0\x0B.,;:()№" );
		if ( $token === '' ) {
			continue;
		}
		$n = promen_selector_steel_norm( $token );
		if ( $n !== '' && isset( $ref_norm[ $n ] ) ) {
			$out['steel'][] = $ref_norm[ $n ];
			$cut( $rest, $token );
		}
	}
	$out['steel'] = array_values( array_unique( $out['steel'] ) );

	// 11. Остаток — то, что не поняли. Служебные слова непонятыми не считаем.
	$noise = [
		'исп', 'исполнение', 'шт', 'штук', 'мм', 'по', 'для', 'из', 'с', 'и',
		'на', 'ст', 'сталь', 'нужен', 'нужно', 'нужны', 'надо', 'подобрать',
		'гнутый', 'крутоизогнутый', 'бесшовный', 'сварной', 'р',
		// глаголы проектного запроса: «строительство котельной» — объект уже
		// распознан, само слово «строительство» непонятым считать незачем
		'строительство', 'строительства', 'стройка', 'монтаж', 'монтажа',
		'реконструкция', 'реконструкции', 'капремонт', 'ремонт', 'проект',
		'проекта', 'проектирование', 'объект', 'объекта', 'станции', 'станция',
		'сети', 'сеть', 'система', 'системы', 'узел', 'узла',
	];
	foreach ( preg_split( '/[\s,;]+/u', $rest ) ?: [] as $token ) {
		$token = trim( (string) $token, " \t\n\r\0\x0B.,;:()№" );
		if ( $token === '' || in_array( $token, $noise, true ) ) {
			continue;
		}
		$out['unknown'][] = $token;
	}
	$out['unknown'] = array_values( array_unique( $out['unknown'] ) );

	return $out;
}

/* ═════════════════ 04 · СВЯЗЬ С КАТАЛОГОМ ═════════════════ */

/**
 * Карта «наружный диаметр → DN», построенная ПО КАНОНУ, а не по вшитой
 * таблице: таблица рано или поздно разойдётся с ассортиментом, канон — нет.
 * Наружный диаметр лежит в payload (колонки `d` в таблице нет).
 *
 * @return array<string, float> ключ — D как строка ('108'), значение — DN
 */
function promen_selector_d_map(): array {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'selector_d_map' )
		: 'promen_selector_d_map';
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $map = $cached;
	}

	global $wpdb;
	$table = promen_catalog_table_name();
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$rows = $wpdb->get_results(
		"SELECT JSON_UNQUOTE(JSON_EXTRACT(payload, '$.d')) AS d, dn, COUNT(*) AS cnt
		 FROM {$table}
		 WHERE dn IS NOT NULL AND JSON_EXTRACT(payload, '$.d') IS NOT NULL
		 GROUP BY d, dn
		 ORDER BY cnt DESC",
		ARRAY_A
	);

	$map = [];
	foreach ( $rows ?: [] as $r ) {
		$d = (string) ( $r['d'] ?? '' );
		if ( $d === '' || $d === 'null' ) {
			continue;
		}
		$key = (string) (float) $d;
		if ( ! isset( $map[ $key ] ) ) {
			$map[ $key ] = (float) $r['dn']; // первый = самый частый (ORDER BY cnt)
		}
	}
	set_transient( $ckey, $map, 15 * MINUTE_IN_SECONDS );
	return $map;
}

/**
 * Марки группы: ключ справочника → слаг фасета.
 * Источник — тот же универсум фасетов, что у реестра, поэтому подбор
 * не может предложить марку, которой нет в каталоге.
 *
 * @return array<string, string>
 */
function promen_selector_group_steel_map( string $group ): array {
	$universe = promen_catalog_facet_universe( $group );
	$out      = [];
	foreach ( array_keys( (array) ( $universe['steel'] ?? [] ) ) as $slug ) {
		$label = promen_term_label( 'pa_steel', (string) $slug );
		$key   = promen_steel_key( $label );
		if ( isset( promen_steel_reference()[ $key ] ) && ! isset( $out[ $key ] ) ) {
			$out[ $key ] = (string) $slug;
		}
	}
	return $out;
}

/**
 * Нормативы группы: слаг → метка. Нужен, чтобы «гост 17375» без года
 * сошёлся с реальным `gost-17375-2001`.
 *
 * @return array<string, string>
 */
function promen_selector_group_norm_map( string $group ): array {
	$universe = promen_catalog_facet_universe( $group );
	$out      = [];
	foreach ( array_keys( (array) ( $universe['gost'] ?? [] ) ) as $slug ) {
		$out[ (string) $slug ] = promen_term_label( 'norm', (string) $slug );
	}
	return $out;
}

/**
 * Подобрать слаги нормативов по разобранным обозначениям.
 * Сравнение по номеру: «17375» ⊂ «gost-17375-2001».
 *
 * @param array<int, array{kind: string, number: string, raw: string}> $parsed
 * @param array<string, string> $norm_map
 * @return string[]
 */
function promen_selector_match_norms( array $parsed, array $norm_map ): array {
	$out = [];
	foreach ( $parsed as $g ) {
		$num = (string) ( $g['number'] ?? '' );
		if ( $num === '' ) {
			continue;
		}
		$base = explode( '-', $num )[0];
		foreach ( $norm_map as $slug => $label ) {
			if ( str_contains( $slug, $num ) || str_contains( $slug, $base ) ) {
				$out[] = (string) $slug;
			}
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * PN — нормируемый параметр самой детали?
 *
 * У фланцев и арматуры давление указано в обозначении и лежит в данных,
 * поэтому по нему можно фильтровать. У отвода по ГОСТ 17375 своего PN нет:
 * несущая способность определяется стенкой и маркой. Фильтр `pn >= X`
 * в поисковом движке выбрасывает документы с пустым полем — то есть на
 * запрос «1.6 МПа» из выдачи молча исчезали ВСЕ отводы и трубы ЖКХ
 * (проверено: 1641 → 0). Поэтому давление ограничивает выбор марок,
 * а позиции сужает только там, где PN у детали действительно есть.
 */
function promen_selector_group_uses_pn( string $group ): bool {
	if ( $group === '' || ! function_exists( 'promen_catalog_schema_facets' ) ) {
		return false;
	}
	if ( function_exists( 'promen_is_fastener_group' ) && promen_is_fastener_group( $group ) ) {
		return false;
	}
	return in_array( 'pn', promen_catalog_schema_facets( $group ), true );
}

/**
 * Пределы, в которых подбор вообще способен работать — из справочника марок,
 * а не из головы. Нужны, чтобы честно ответить на «температура 900 °C»:
 * это не «ничего не найдено», а «таких марок в справочнике нет».
 *
 * @return array{t_min: int, t_max: int, p_max: float}
 */
function promen_selector_limits(): array {
	static $lim = null;
	if ( null !== $lim ) {
		return $lim;
	}
	$t_min = 0;
	$t_max = 0;
	$p_max = 0.0;
	foreach ( promen_selector_steel_specs() as $s ) {
		if ( null !== $s['t_min'] ) {
			$t_min = min( $t_min, (int) $s['t_min'] );
		}
		if ( null !== $s['t_max'] ) {
			$t_max = max( $t_max, (int) $s['t_max'] );
		}
		if ( null !== $s['pn_mpa'] ) {
			$p_max = max( $p_max, (float) $s['pn_mpa'] );
		}
	}
	$lim = [
		't_min' => $t_min,
		't_max' => $t_max,
		'p_max' => $p_max,
	];
	return $lim;
}

/**
 * Параметры, без которых подбор для объекта невозможен.
 *
 * Список короткий и честный: сюда попадает только то, что реально участвует
 * в отборе. Поле, которое ни на что не влияет, было бы имитацией строгости.
 *
 * @return array<int, array{key: string, label: string, unit: string, why: string}>
 */
function promen_selector_required_params(): array {
	return [
		[
			'key'   => 'temp',
			'label' => 'Температура среды',
			'unit'  => '°C',
			'why'   => 'определяет допустимые марки стали по справочнику материалов',
		],
		[
			'key'   => 'pressure',
			'label' => 'Рабочее давление',
			'unit'  => 'МПа',
			'why'   => 'отсекает марки и исполнения с меньшим PN',
		],
	];
}

/**
 * Марки, встречающиеся в отрасли (по всему каталогу, одним запросом).
 * Нужны, чтобы отбор по условиям среды для объекта опирался на реальный
 * ассортимент, а не на весь справочник: раздел ещё не выбран.
 *
 * @return array<string, string> ключ справочника → слаг фасета
 */
function promen_selector_industry_steel_map( string $ind ): array {
	if ( $ind === '' ) {
		return [];
	}
	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'selector_ind_steels', [ $ind ] )
		: 'promen_selector_ind_steels_' . md5( $ind );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$out = [];
	try {
		$res = promen_catalog_search(
			Promen_Catalog_Query::from_array(
				[
					'group'    => '',
					'industry' => [ $ind ],
					'per_page' => 1,
				]
			)
		);
		foreach ( array_keys( (array) ( $res->facets['steel'] ?? [] ) ) as $slug ) {
			$key = promen_steel_key( promen_term_label( 'pa_steel', (string) $slug ) );
			if ( isset( promen_steel_reference()[ $key ] ) && ! isset( $out[ $key ] ) ) {
				$out[ $key ] = (string) $slug;
			}
		}
	} catch ( \Throwable $e ) {
		$out = [];
	}

	set_transient( $ckey, $out, 15 * MINUTE_IN_SECONDS );
	return $out;
}

/**
 * Разделы каталога, доступные для отрасли, со счётчиками ПО КАТАЛОГУ.
 * Никакого «типового состава котельной» из головы: показываем ровно то,
 * что помечено этой отраслью в данных, и ровно столько, сколько есть.
 * 12 запросов на промах кэша — поэтому результат кладём в transient.
 *
 * @return array<int, array{slug: string, label: string, count: int}>
 */
function promen_selector_industry_groups( string $ind, array $steel_slugs = [], ?float $pres = null ): array {
	if ( $ind === '' ) {
		return [];
	}
	$sig    = md5( $ind . '|' . implode( ',', $steel_slugs ) . '|' . (string) $pres );
	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'selector_ind_groups', [ $sig ] )
		: 'promen_selector_ind_groups_' . $sig;
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$out = [];
	foreach ( promen_selector_type_menu() as $slug => $label ) {
		$params = [
			'group'    => $slug,
			'industry' => [ $ind ],
			'per_page' => 1,
		];
		// Условия среды сужают счётчик: «Отводы 1289» читается как «отводы,
		// у которых есть марка, годная для вашей среды», а не «отводы вообще».
		if ( $steel_slugs ) {
			$params['steel'] = $steel_slugs;
		}
		if ( null !== $pres && promen_selector_group_uses_pn( $slug ) ) {
			$params['pn_min'] = $pres;
		}
		try {
			$res = promen_catalog_search( Promen_Catalog_Query::from_array( $params ) );
		} catch ( \Throwable $e ) {
			continue;
		}
		if ( $res->total > 0 ) {
			$out[] = [
				'slug'  => (string) $slug,
				'label' => (string) $label,
				'count' => (int) $res->total,
			];
		}
	}
	usort( $out, static fn( array $a, array $b ): int => $b['count'] <=> $a['count'] );

	set_transient( $ckey, $out, 15 * MINUTE_IN_SECONDS );
	return $out;
}

/* ═════════════════ 05 · СБОРКА ЗАПРОСА И ПОИСК ═════════════════ */

/**
 * Главный сценарий: вход (строка и/или параметры мастера) → выборка каталога.
 *
 * Ослабление фильтров: если по точному набору ничего не нашлось, условия
 * снимаются по одному в порядке «наименее существенное первым», и каждое
 * снятие попадает в `relaxed` — пользователь должен видеть, что показанное
 * шире запрошенного, а не думать, что нашлось ровно то, что он просил.
 *
 * @param array<string, mixed> $input
 * @return array<string, mixed>
 */
function promen_selector_resolve( array $input ): array {
	$text   = trim( (string) ( $input['q'] ?? '' ) );
	$parsed = promen_selector_parse( $text );

	// Явные параметры мастера сильнее разобранных из строки.
	$num = static function ( $v ): ?float {
		return ( $v === null || $v === '' ) ? null : (float) $v;
	};

	$group = (string) ( $input['group'] ?? '' ) ?: $parsed['group'];
	$group = sanitize_title( $group );
	$dn    = $num( $input['dn'] ?? null ) ?? $parsed['dn'];
	$d     = $num( $input['d'] ?? null ) ?? $parsed['d'];
	$s     = $num( $input['s'] ?? null ) ?? $parsed['s'];
	$angle = $num( $input['angle'] ?? null ) ?? $parsed['angle'];
	$pres  = $num( $input['pressure'] ?? null ) ?? $parsed['pn'];
	$temp  = $num( $input['temp'] ?? null ) ?? $parsed['temp'];
	$ind   = sanitize_title( (string) ( $input['industry'] ?? '' ) );

	// Снятие условия. Часть условий приходит из строки запроса, а не из полей,
	// и обнулить их пустым значением нельзя — оно откатится к разобранному.
	// Поэтому крестик на метке присылает явный список снятого: тогда любое
	// условие снимается там же, где показано, независимо от происхождения.
	$drop = (array) ( $input['drop'] ?? [] );
	$drop = array_filter( array_map( 'sanitize_key', $drop ) );
	if ( $drop ) {
		$dropped = static fn( string $k ): bool => in_array( $k, $drop, true );
		if ( $dropped( 'group' ) ) {
			$group = '';
		}
		if ( $dropped( 'industry' ) ) {
			$ind = '';
		}
		if ( $dropped( 'temp' ) ) {
			$temp = null;
		}
		if ( $dropped( 'pressure' ) ) {
			$pres = null;
		}
		if ( $dropped( 'dn' ) ) {
			$dn = null;
			$d  = null;
		}
		if ( $dropped( 's' ) ) {
			$s = null;
		}
		if ( $dropped( 'angle' ) ) {
			$angle = null;
		}
		if ( $dropped( 'gost' ) ) {
			$parsed['gost'] = [];
		}
		if ( $dropped( 'steel' ) ) {
			$parsed['steel'] = [];
			$input['steel']  = [];
		}
		if ( $dropped( 'object' ) ) {
			$parsed['object'] = '';
		}
	}

	$notes   = [];
	$relaxed = [];

	// Запрос об ОБЪЕКТЕ («строительство котельной»): изделия в нём нет,
	// выводится только отрасль. Параметры среды у каждого объекта свои —
	// подставлять их нельзя, поэтому дальше подбор их спрашивает.
	$object = [
		'label'      => '',
		'industries' => [],
		'groups'     => [],
	];
	if ( $parsed['object'] !== '' ) {
		$def = promen_selector_objects()[ $parsed['object'] ] ?? null;
		if ( $def ) {
			$labels              = promen_industry_tag_labels();
			$object['label']     = (string) $def['label'];
			$object['industries'] = array_map(
				static fn( string $s ): array => [
					'slug'  => $s,
					'label' => $labels[ $s ] ?? strtoupper( $s ),
				],
				$def['ind']
			);
			if ( $ind === '' && $def['ind'] ) {
				$ind = (string) $def['ind'][0];
			}
		}
	}

	// Пустой или совсем непонятый запрос: показать весь каталог значило бы
	// соврать («вот что подобралось»), поэтому честно просим уточнить.
	$has_any = $group !== '' || null !== $dn || null !== $d || null !== $s
		|| null !== $angle || null !== $pres || null !== $temp || $ind !== ''
		|| $parsed['steel'] || $parsed['gost'] || ! empty( $input['steel'] );
	if ( ! $has_any ) {
		promen_selector_log( $text, $parsed, 0 );
		return [
			// Форма ответа одна и та же в любой ветке — клиенту не приходится
			// различать «пустой массив» и «объект с полями».
			'query'   => [
				'group'    => '',
				'dn'       => null,
				'd'        => null,
				's'        => null,
				'angle'    => null,
				'pressure' => null,
				'temp'     => null,
				'industry' => '',
				'steel'    => [],
			],
			'parsed'  => [
				'group'   => '',
				'gost'    => [],
				'steel'   => [],
				'unknown' => $parsed['unknown'],
			],
			'steel'   => [
				'fit'      => [],
				'warn'     => [],
				'rejected' => [],
				'applied'  => [],
			],
			'object'  => $object,
			'hits'    => [],
			'total'   => 0,
			'relaxed' => [],
			'notes'   => [ $text === ''
				? 'Укажите тип изделия и параметры.'
				: 'Не удалось разобрать запрос. Укажите тип изделия — например, «отвод 90° 108х4» — или объект: «котельная», «теплотрасса», «паропровод».' ],
			'catalog' => '',
			'engine'  => '',
		];
	}

	// Отрасль известна, а раздел — нет. Вывалить сюда все позиции отрасли
	// (это тысячи строк) — не подбор, а свалка: показываем разделы каталога
	// со счётчиками, чтобы человек выбрал, и просим параметры среды.
	if ( $group === '' && $ind !== '' ) {
		$labels    = promen_industry_tag_labels();
		$ind_label = $labels[ $ind ] ?? strtoupper( $ind );
		$limits    = promen_selector_limits();
		$notes     = [];

		// Чего не хватает для подбора. Пока список не пуст, счётчики разделов
		// показывают весь ассортимент отрасли и честно об этом говорят.
		$missing = [];
		foreach ( promen_selector_required_params() as $req ) {
			$val = 'temp' === $req['key'] ? $temp : $pres;
			if ( null === $val ) {
				$missing[] = $req;
			}
		}
		$object['required'] = promen_selector_required_params();
		$object['missing']  = array_column( $missing, 'key' );
		$object['limits']   = $limits;

		// Параметры вне пределов справочника — это не «ничего не найдено».
		$out_of_range = false;
		if ( null !== $temp && ( $temp > $limits['t_max'] || $temp < $limits['t_min'] ) ) {
			$out_of_range = true;
			$notes[]      = 'Температура ' . promen_selector_fmt( $temp ) . ' °C вне пределов справочника материалов ('
				. $limits['t_min'] . '…+' . $limits['t_max'] . ' °C). Такие условия — отдельный расчёт, отправьте ТЗ.';
		}
		if ( null !== $pres && $pres > $limits['p_max'] ) {
			$out_of_range = true;
			$notes[]      = 'Давление ' . promen_selector_fmt( $pres ) . ' МПа выше максимума справочника ('
				. promen_selector_fmt( $limits['p_max'] ) . ' МПа). Отправьте ТЗ — подбор считается индивидуально.';
		}

		// Условия заданы — отбираем марки по ассортименту отрасли и сужаем
		// счётчики разделов. Не заданы — показываем отрасль как есть.
		$steel_slugs = [];
		$pick        = [
			'fit'    => [],
			'warn'   => [],
			'reject' => [],
		];
		if ( ! $missing && ! $out_of_range ) {
			$ind_steel = promen_selector_industry_steel_map( $ind );
			$pick      = promen_selector_steel_pick(
				[
					'temp'      => $temp,
					'pressure'  => $pres,
					'industry'  => $ind,
					'available' => array_keys( $ind_steel ),
				]
			);
			foreach ( array_merge( $pick['fit'], $pick['warn'] ) as $row ) {
				if ( isset( $ind_steel[ $row['key'] ] ) ) {
					$steel_slugs[] = $ind_steel[ $row['key'] ];
				}
			}
			$steel_slugs = array_values( array_unique( $steel_slugs ) );
		}

		$object['groups']      = $out_of_range ? [] : promen_selector_industry_groups( $ind, $steel_slugs, $missing ? null : $pres );
		$object['constrained'] = ( ! $missing && ! $out_of_range );

		$head = $object['label'] !== ''
			? 'Объект «' . $object['label'] . '» — отрасль ' . $ind_label . '.'
			: 'Отрасль ' . $ind_label . '.';

		if ( $missing ) {
			$names = array_map(
				static fn( array $r ): string => mb_strtolower( $r['label'], 'UTF-8' ),
				$missing
			);
			$one = count( $names ) === 1;
			array_unshift(
				$notes,
				$head . ( $one ? ' Для подбора нужно ' : ' Для подбора нужны ' ) . implode( ' и ', $names )
					. ( $one ? ' — без него ' : ' — без них ' )
					. 'марку стали определить нельзя. '
					. ( $one ? 'Укажите его в поле ниже.' : 'Укажите их в полях ниже.' )
			);
		} elseif ( ! $out_of_range ) {
			$n = count( $pick['fit'] ) + count( $pick['warn'] );
			if ( 0 === $n ) {
				// Марок нет — значит и разделы показывать нельзя: их счётчики
				// без фильтра по маркам противоречили бы этой же строке.
				$object['groups'] = [];
				array_unshift(
					$notes,
					$head . ' По заданным условиям (' . promen_selector_fmt( (float) $temp ) . ' °C, '
						. promen_selector_fmt( (float) $pres ) . ' МПа) подходящих марок в справочнике нет. '
						. 'Такие параметры считаются индивидуально — отправьте ТЗ.'
				);
			} elseif ( ! $object['groups'] ) {
				array_unshift(
					$notes,
					$head . ' Марки под эти условия есть (' . $n . '), но позиций с ними в каталоге нет. '
						. 'Отправьте ТЗ — изготовим под заказ.'
				);
			} else {
				array_unshift(
					$notes,
					$head . ' По заданным условиям подходит марок: ' . $n
						. '. Счётчики разделов — уже с учётом этих марок. Выберите раздел.'
				);
			}
		}

		promen_selector_log( $text, $parsed, 0 );

		return [
			'query'   => [
				'group'    => '',
				'dn'       => $dn,
				'd'        => $d,
				's'        => $s,
				'angle'    => $angle,
				'pressure' => $pres,
				'temp'     => $temp,
				'industry' => $ind,
				'steel'    => $parsed['steel'],
			],
			'parsed'  => [
				'group'   => $parsed['group'],
				'gost'    => $parsed['gost'],
				'steel'   => $parsed['steel'],
				'unknown' => $parsed['unknown'],
			],
			'steel'   => [
				'fit'      => $pick['fit'],
				'warn'     => $pick['warn'],
				'rejected' => $pick['reject'],
				'applied'  => $steel_slugs,
			],
			'object'  => $object,
			'hits'    => [],
			'total'   => 0,
			'relaxed' => [],
			'notes'   => $notes,
			'catalog' => '',
			'engine'  => '',
		];
	}

	// Наружный диаметр → DN по канону.
	if ( null === $dn && null !== $d ) {
		$dmap = promen_selector_d_map();
		$key  = (string) $d;
		if ( isset( $dmap[ $key ] ) ) {
			$dn      = $dmap[ $key ];
			$notes[] = 'Диаметр Ø' . promen_selector_fmt( $d ) . ' мм соответствует DN ' . promen_selector_fmt( $dn ) . ' — по данным каталога.';
		} else {
			$notes[] = 'Диаметр Ø' . promen_selector_fmt( $d ) . ' мм в каталоге не встречается — подбор идёт без него.';
		}
	}

	// Марки: явный выбор пользователя либо отбор по параметрам среды.
	$steel_map   = $group !== '' ? promen_selector_group_steel_map( $group ) : [];
	$available   = array_keys( $steel_map );
	$user_steels = array_values( array_filter( array_map( 'strval', (array) ( $input['steel'] ?? [] ) ) ) );
	if ( ! $user_steels ) {
		$user_steels = $parsed['steel'];
	}

	// Без параметров среды «подходят» все марки группы — это не подбор,
	// а перечень ассортимента, и показывать его как рекомендацию нельзя.
	$constrained = ( null !== $temp || null !== $pres || $ind !== '' );

	$is_fastener = function_exists( 'promen_is_fastener_group' ) && promen_is_fastener_group( $group );
	$pick        = promen_selector_steel_pick(
		[
			'temp'      => $temp,
			// К крепежу давление среды неприменимо: у его марок PN в справочнике «—».
			'pressure'  => $is_fastener ? null : $pres,
			'industry'  => $ind,
			'available' => $available,
		]
	);

	$steel_slugs = [];
	if ( $user_steels ) {
		$missing = [];
		foreach ( $user_steels as $key ) {
			if ( isset( $steel_map[ $key ] ) ) {
				$steel_slugs[] = $steel_map[ $key ];
			} else {
				$missing[] = $key;
			}
		}
		if ( $missing ) {
			$notes[] = 'В этой группе нет марок: ' . implode( ', ', $missing ) . '. Фильтр по ним не применён.';
		}
	} elseif ( $constrained ) {
		foreach ( array_merge( $pick['fit'], $pick['warn'] ) as $row ) {
			if ( isset( $steel_map[ $row['key'] ] ) ) {
				$steel_slugs[] = $steel_map[ $row['key'] ];
			}
		}
	}
	$steel_slugs = array_values( array_unique( $steel_slugs ) );

	$norm_map   = $group !== '' ? promen_selector_group_norm_map( $group ) : [];
	$norm_slugs = promen_selector_match_norms( $parsed['gost'], $norm_map );

	// Базовый набор параметров запроса каталога.
	$params = [
		'group'    => $group,
		'per_page' => min( 50, max( 1, (int) ( $input['per_page'] ?? 12 ) ) ),
		'page'     => max( 1, (int) ( $input['page'] ?? 1 ) ),
	];
	if ( null !== $dn ) {
		$params['dn_min'] = $dn;
		$params['dn_max'] = $dn;
	}
	if ( null !== $s ) {
		$params['s_min'] = $s;
		$params['s_max'] = $s;
	}
	// Давление сужает позиции только у деталей, у которых PN нормируется
	// (фланцы, арматура). У остальных оно уже отработало на отборе марок.
	$pn_applies = promen_selector_group_uses_pn( $group );
	if ( null !== $pres && $pn_applies ) {
		$params['pn_min'] = $pres; // деталь должна держать не меньше рабочего
	}
	if ( null !== $angle ) {
		$params['angle'] = [ (string) promen_selector_fmt( $angle ) ];
	}
	if ( $ind !== '' ) {
		$params['industry'] = [ $ind ];
	}
	if ( $steel_slugs ) {
		$params['steel'] = $steel_slugs;
	}
	if ( $norm_slugs ) {
		$params['gost'] = $norm_slugs;
	}

	if ( null !== $pres && ! $pn_applies && ! $is_fastener && $group !== '' ) {
		$notes[] = 'Давление ' . promen_selector_fmt( $pres ) . ' МПа учтено при отборе марок. '
			. 'Позиции по нему не отсекались: у деталей этого типа PN в каталоге не нормируется — '
			. 'несущая способность определяется стенкой и маркой.';
	}

	// Лестница ослабления: снимаем по одному, пока не появятся позиции.
	// Порядок — от наименее опасного к наиболее: замена норматива на
	// равноценный видна в строке позиции, а вот марки НЕ снимаются никогда.
	// Показать при 540 °C позицию, у которой нет ни одной допустимой марки,
	// значит выдать за подбор то, что подбором не является.
	$ladder = [
		'gost'  => 'норматив',
		's'     => 'толщина стенки',
		'pn'    => 'рабочее давление',
		'angle' => 'угол',
	];

	$result = promen_selector_run( $params );
	foreach ( $ladder as $drop => $label ) {
		if ( $result->total > 0 ) {
			break;
		}
		$before = $params;
		if ( $drop === 's' && isset( $params['s_min'] ) ) {
			unset( $params['s_min'], $params['s_max'] );
		} elseif ( $drop === 'pn' && isset( $params['pn_min'] ) ) {
			unset( $params['pn_min'] );
		} elseif ( $drop === 'angle' && isset( $params['angle'] ) ) {
			unset( $params['angle'] );
		} elseif ( $drop === 'gost' && isset( $params['gost'] ) ) {
			unset( $params['gost'] );
		} else {
			continue;
		}
		if ( $params === $before ) {
			continue;
		}
		$relaxed[] = $label;
		$result    = promen_selector_run( $params );
	}
	if ( $relaxed ) {
		$notes[] = 'По точному запросу совпадений нет. Снято условие: ' . implode( ', ', $relaxed ) . '.';
	}

	// Пустой результат при действующем фильтре по маркам объясняем прямо:
	// это не «ничего не нашли», а «под эти условия среды позиций нет».
	if ( 0 === $result->total && $steel_slugs && $constrained ) {
		$notes[] = 'Позиций с марками, допустимыми при заданных условиях среды, в этой группе нет. '
			. 'Проверьте параметры или отправьте задачу инженеру.';
	}

	// DN не снимается лестницей (это суть запроса) — но если его нет в группе,
	// пустой результат надо объяснить, а не оставить молча.
	if ( 0 === $result->total && null !== $dn && $group !== '' && function_exists( 'promen_range_options' ) ) {
		$vals = array_map( static fn( array $o ): float => (float) $o['val'], promen_range_options( 'dn', $group ) );
		if ( $vals && ! in_array( $dn, $vals, true ) ) {
			usort( $vals, static fn( float $a, float $b ): int => abs( $a - $dn ) <=> abs( $b - $dn ) );
			$near    = array_map( 'promen_selector_fmt', array_slice( $vals, 0, 3 ) );
			$notes[] = 'DN ' . promen_selector_fmt( $dn ) . ' в этой группе не выпускается. Ближайшие: DN ' . implode( ', ', $near ) . '.';
		}
	}

	// Компактные позиции: панель подбора — не реестр, лишние поля не нужны.
	$hits = [];
	foreach ( $result->hits as $h ) {
		// Показываем те марки позиции, что прошли отбор, а не первые в списке:
		// иначе строка выглядит как «подобрано 09Г2С», хотя подошла 12Х1МФ.
		// Тот же приём, что в promen_rest_catalog() для реестра.
		$steel_txt = (string) ( $h['steel_display'] ?? '' );
		if ( $steel_slugs ) {
			$matched = array_values( array_intersect( (array) ( $h['steels'] ?? [] ), $steel_slugs ) );
			$labels  = array_map(
				static fn( $slug ): string => promen_term_label( 'pa_steel', (string) $slug ),
				$matched
			);
			if ( $labels ) {
				$steel_txt = implode( ', ', $labels );
			}
		}
		if ( $steel_txt === '' ) {
			$steel_txt = implode( ', ', (array) ( $h['steel_labels'] ?? [] ) );
		}
		$hits[] = [
			'sku'        => (string) ( $h['sku'] ?? '' ),
			'title'      => (string) ( $h['title'] ?? '' ),
			'url'        => (string) ( $h['url'] ?? '' ),
			'norm'       => (string) ( $h['norm'] ?? '' ),
			'dn'         => $h['dn'] ?? null,
			'd'          => $h['d'] ?? null,
			's'          => $h['s'] ?? null,
			'pn'         => $h['pn'] ?? null,
			'angle'      => $h['angle'] ?? null,
			'mass'       => $h['mass'] ?? null,
			'steels'     => $steel_txt,
			'industries' => (array) ( $h['industries'] ?? [] ),
		];
	}

	promen_selector_log( $text, $parsed, $result->total );

	return [
		'query'    => [
			'group'    => $group,
			'dn'       => $dn,
			'd'        => $d,
			's'        => $s,
			'angle'    => $angle,
			'pressure' => $pres,
			'temp'     => $temp,
			'industry' => $ind,
			'steel'    => $user_steels,
		],
		'parsed'   => [
			'group'   => $parsed['group'],
			'gost'    => $parsed['gost'],
			'steel'   => $parsed['steel'],
			'unknown' => $parsed['unknown'],
		],
		'steel'    => [
			'fit'      => $constrained ? $pick['fit'] : [],
			'warn'     => $constrained ? $pick['warn'] : [],
			'rejected' => $constrained ? $pick['reject'] : [],
			'applied'  => $steel_slugs,
		],
		'object'   => $object,
		'hits'     => $hits,
		'total'    => $result->total,
		'relaxed'  => $relaxed,
		'notes'    => $notes,
		'catalog'  => promen_selector_catalog_url( $group, $params ),
		'engine'   => $result->engine,
	];
}

/** Выполнить запрос каталога по собранным параметрам. */
function promen_selector_run( array $params ): Promen_Catalog_Search_Result {
	return promen_catalog_search( Promen_Catalog_Query::from_array( $params ) );
}

/** Число без хвостовых нулей: 100.0 → «100», 4.5 → «4.5». */
function promen_selector_fmt( float $v ): string {
	return rtrim( rtrim( number_format( $v, 2, '.', '' ), '0' ), '.' );
}

/**
 * Ссылка «открыть в реестре» — те же фильтры в адресной строке каталога.
 * Реестр читает их из query-строки (см. parsePageUrl в assets/js/catalog.js).
 */
function promen_selector_catalog_url( string $group, array $params ): string {
	$base = $group !== '' ? promen_product_cat_link( $group ) : '';
	if ( $base === '' ) {
		$base = home_url( '/catalog/' );
	}
	$q = [];
	foreach ( [ 'dn_min', 'dn_max', 's_min', 's_max', 'pn_min' ] as $k ) {
		if ( isset( $params[ $k ] ) ) {
			$q[ $k ] = promen_selector_fmt( (float) $params[ $k ] );
		}
	}
	foreach ( [ 'steel', 'gost', 'angle', 'industry' ] as $k ) {
		if ( ! empty( $params[ $k ] ) ) {
			$q[ $k ] = implode( ',', (array) $params[ $k ] );
		}
	}
	return $q ? $base . '?' . http_build_query( $q ) : $base;
}

/* ═════════════════ 06 · ЖУРНАЛ НЕРАЗОБРАННОГО ═════════════════ */

/**
 * Кольцевой журнал запросов, которые парсер не понял или которые не дали
 * результата. Это бесплатный датасет: через месяц он показывает, нужен ли
 * вообще языковой слой и на каких формулировках он споткнётся.
 */
function promen_selector_log( string $text, array $parsed, int $total ): void {
	if ( $text === '' ) {
		return;
	}
	if ( ! $parsed['unknown'] && $total > 0 ) {
		return; // разобрали полностью и нашли — записывать нечего
	}
	$log   = get_option( 'promen_selector_log', [] );
	$log   = is_array( $log ) ? $log : [];
	$log[] = [
		't'       => gmdate( 'c' ),
		'text'    => mb_substr( $text, 0, 300, 'UTF-8' ),
		'unknown' => array_slice( $parsed['unknown'], 0, 12 ),
		'group'   => $parsed['group'],
		'total'   => $total,
	];
	if ( count( $log ) > 300 ) {
		$log = array_slice( $log, -300 );
	}
	update_option( 'promen_selector_log', $log, false );
}

/* ═════════════════ 07 · REST ═════════════════ */

add_action(
	'rest_api_init',
	function (): void {
		if ( ! promen_selector_enabled() ) {
			return;
		}
		register_rest_route(
			'promen/v1',
			'/select',
			[
				'methods'             => 'GET',
				'callback'            => 'promen_rest_select',
				'permission_callback' => '__return_true',
			]
		);
	}
);

function promen_rest_select( WP_REST_Request $request ): WP_REST_Response {
	$steel = $request->get_param( 'steel' );
	$steel = is_array( $steel ) ? $steel : array_filter( explode( ',', (string) $steel ) );
	$drop  = $request->get_param( 'drop' );
	$drop  = is_array( $drop ) ? $drop : array_filter( explode( ',', (string) $drop ) );

	$out = promen_selector_resolve(
		[
			'q'        => (string) $request->get_param( 'q' ),
			'group'    => (string) $request->get_param( 'group' ),
			'industry' => (string) $request->get_param( 'industry' ),
			'temp'     => $request->get_param( 'temp' ),
			'pressure' => $request->get_param( 'pressure' ),
			'dn'       => $request->get_param( 'dn' ),
			'd'        => $request->get_param( 'd' ),
			's'        => $request->get_param( 's' ),
			'angle'    => $request->get_param( 'angle' ),
			'steel'    => $steel,
			'drop'     => $drop,
			'page'     => $request->get_param( 'page' ),
			'per_page' => $request->get_param( 'per_page' ),
		]
	);

	return new WP_REST_Response( $out, 200 );
}

/* ═════════════════ 08 · СПРАВОЧНЫЕ ДАННЫЕ ДЛЯ ИНТЕРФЕЙСА ═════════════════ */

/**
 * Данные для мастера: типы изделий с реальными счётчиками и отрасли.
 * Счётчик берётся из канона — список типов не может разойтись с каталогом.
 *
 * @return array<string, mixed>
 */
function promen_selector_bootstrap(): array {
	$types = [];
	foreach ( promen_selector_type_menu() as $slug => $label ) {
		$count = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( $slug ) : 0;
		if ( $count <= 0 ) {
			continue;
		}
		$types[] = [
			'slug'  => $slug,
			'label' => $label,
			'count' => $count,
		];
	}
	return [
		'types'      => $types,
		'industries' => promen_industry_tag_labels(),
	];
}

/**
 * Порядок и подписи типов в мастере. Отдельно от promen_selector_types()
 * (там корни слов для парсера) — в интерфейсе нужны нормальные названия.
 *
 * @return array<string, string>
 */
function promen_selector_type_menu(): array {
	return [
		'otvody'      => 'Отводы',
		'troyniki'    => 'Тройники',
		'perekhody'   => 'Переходы',
		'dnishcha'    => 'Днища',
		'zaglushki'   => 'Заглушки',
		'flancy'      => 'Фланцы',
		'truby'       => 'Трубы',
		'krepezh'     => 'Крепёж',
		'opory'       => 'Опоры',
		'izolyatsiya' => 'Изоляция ППУ',
		'armatura'    => 'Арматура',
		'tochenye'    => 'Точёные детали',
	];
}
