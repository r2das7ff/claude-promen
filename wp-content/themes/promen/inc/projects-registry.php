<?php
/**
 * Реестр объектов поставок — единый источник для карты на главной,
 * бегущей строки и списка на странице «Проекты».
 *
 * ВНИМАНИЕ. Состав поставки, объём и марки стали у объектов без внутренней
 * страницы («page» => null) заполнены ПРЕДВАРИТЕЛЬНО, по типу объекта и
 * профилю завода. Перед публикацией на прод их должен подтвердить менеджер —
 * см. флаг 'draft' => true. Проверенные объекты флага не имеют.
 *
 * kind:   nuclear | thermal | hydro | mining | chemical
 * region: ru | intl   (intl — экспорт, к таким объектам ведётся дуга на карте)
 * label:  сторона подписи на карте и смещение, чтобы не наезжала на соседей
 *
 * @package promen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Полный реестр объектов.
 *
 * @return array<int,array<string,mixed>>
 */
function promen_projects_registry(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$r = [
		// ── Проверенные объекты с детальными страницами ──────────────────
		[
			'slug' => 'kurskaya-aes', 'page' => 'kurskaya-aes',
			'name' => 'Курская АЭС‑2', 'city' => 'Курчатов', 'country' => 'Россия',
			'lon' => 36.2, 'lat' => 51.7, 'kind' => 'nuclear', 'tag' => 'АЭС', 'region' => 'ru',
			'status' => 'Поставки завершены', 'photo' => 'img/projects/kursk2.png',
			'facts' => [ [ 'Материал', 'Сталь 08Х18Н10Т' ], [ 'Объём поставки', '≈36 т' ], [ 'Номенклатура', 'Фланцы, колена 45–90°' ] ],
			'label' => [ 'side' => 'left', 'off' => 25, 'dx' => 0, 'dy' => 8 ],
		],
		[
			'slug' => 'cherepetskaya-gres', 'page' => 'cherepetskaya-gres',
			'name' => 'Черепетская ГРЭС', 'city' => 'Суворов', 'country' => 'Россия',
			'lon' => 36.6, 'lat' => 54.1, 'kind' => 'thermal', 'tag' => 'ГРЭС', 'region' => 'ru',
			'status' => 'Поставки завершены', 'photo' => 'img/projects/tec2.png',
			'facts' => [ [ 'Материал', 'Сталь 20' ], [ 'Объём поставки', '≈157 т' ], [ 'Диаметр', 'Ø25–530 мм' ] ],
			'label' => [ 'side' => 'top', 'off' => 22, 'dx' => -30, 'dy' => -12 ],
		],
		[
			'slug' => 'teploelektrocentral-tec-3', 'page' => 'teploelektrocentral-tec-3',
			'name' => 'Омская ТЭЦ‑3', 'city' => 'Омск', 'country' => 'Россия',
			'lon' => 73.4, 'lat' => 55.0, 'kind' => 'thermal', 'tag' => 'ТЭЦ', 'region' => 'ru',
			'status' => 'Поставки завершены', 'photo' => 'img/projects/tec3.png',
			'facts' => [ [ 'Материал', 'Сталь 20, 09Г2С' ], [ 'Номенклатура', 'Отводы, тройники, переходы' ], [ 'Документация', 'Паспорт, сертификат, ПТД' ] ],
			'label' => [ 'side' => 'right', 'off' => 30, 'dx' => 0, 'dy' => 0 ],
			// ТЭЦ-3, ТЭЦ-4 и ТЭЦ-5 стоят в одной точке карты — показываем одну плашку.
			'map_label' => 'Омские ТЭЦ‑3/4/5',
		],
		[
			'slug' => 'aes-akkuyu', 'page' => 'aes-akkuyu',
			'name' => 'АЭС «Аккую»', 'city' => 'Мерсин', 'country' => 'Турция',
			'lon' => 33.8, 'lat' => 36.1, 'kind' => 'nuclear', 'tag' => 'АЭС', 'region' => 'intl',
			'status' => 'В стадии строительства', 'photo' => 'img/projects/turk2.png',
			'facts' => [ [ 'Материал', '08Х18Н10Т, 09Г2С' ], [ 'Класс', 'АЭС‑класс, ПНАЭ Г‑7' ], [ 'Контроль', 'ВИК · УЗК · РК' ] ],
			'label' => [ 'side' => 'bottom', 'off' => 25, 'dx' => 0, 'dy' => 0 ],
		],
		[
			'slug' => 'aes-ruppur', 'page' => 'aes-ruppur',
			'name' => 'АЭС «Руппур»', 'city' => 'Пабна', 'country' => 'Бангладеш',
			'lon' => 89.6, 'lat' => 24.1, 'kind' => 'nuclear', 'tag' => 'АЭС', 'region' => 'intl',
			'status' => 'В стадии строительства', 'photo' => 'img/projects/rupp.png',
			'facts' => [ [ 'Материал', '08Х18Н10Т' ], [ 'Класс', 'АЭС‑класс, ПНАЭ Г‑7' ], [ 'Прослеживаемость', 'Плавка · паспорт' ] ],
			'label' => [ 'side' => 'right', 'off' => 30, 'dx' => 0, 'dy' => 0 ],
		],
	];

	// ── Атомная энергетика ───────────────────────────────────────────────
	$nuclear = [
		[ 'leningradskaya-aes',   'Ленинградская АЭС',   'Сосновый Бор',      'Россия', 29.04, 59.85, 'ru',   [ 'side' => 'left',   'dx' => 0,  'dy' => -6 ] ],
		[ 'smolenskaya-aes',      'Смоленская АЭС',      'Десногорск',        'Россия', 33.24, 54.17, 'ru',   [ 'side' => 'left',   'dx' => 0,  'dy' => 0  ] ],
		[ 'novovoronezhskaya-aes','Нововоронежская АЭС', 'Нововоронеж',       'Россия', 39.20, 51.28, 'ru',   [ 'side' => 'bottom', 'dx' => 0,  'dy' => 0  ] ],
		[ 'zaporozhskaya-aes',    'Запорожская АЭС',     'Энергодар',         '',       34.58, 47.51, 'ru',   [ 'side' => 'bottom', 'dx' => 0,  'dy' => 0  ] ],
		[ 'aes-el-dabaa',         'АЭС «Эль‑Дабаа»',     'Эль‑Дабаа',         'Египет', 28.47, 31.03, 'intl', [ 'side' => 'bottom', 'dx' => 0,  'dy' => 0  ] ],
		[ 'aes-kudankulam',       'АЭС «Куданкулам»',    'Тамилнад',          'Индия',  77.71,  8.60, 'intl', [ 'side' => 'right',  'dx' => 0,  'dy' => 0  ] ],
		[ 'aes-syuydapu',         'АЭС «Сюйдапу»',       'Ляонин',            'Китай',  120.3, 40.30, 'intl', [ 'side' => 'right',  'dx' => 0,  'dy' => 0  ] ],
	];
	foreach ( $nuclear as [ $slug, $name, $city, $country, $lon, $lat, $region, $label ] ) {
		$r[] = [
			'slug' => $slug, 'page' => null, 'draft' => true,
			'name' => $name, 'city' => $city, 'country' => $country,
			'lon' => $lon, 'lat' => $lat, 'kind' => 'nuclear', 'tag' => 'АЭС', 'region' => $region,
			'status' => 'Поставки выполнены',
			'facts' => [
				[ 'Материал', '08Х18Н10Т, 10Х17Н13М2Т' ],
				[ 'Номенклатура', 'Отводы, тройники, переходы, заглушки' ],
				[ 'Документация', 'Паспорт · сертификат на металл · протоколы НК' ],
			],
			'label' => array_merge( [ 'off' => 26 ], $label ),
		];
	}

	// ── Тепловая энергетика ──────────────────────────────────────────────
	$thermal = [
		[ 'surgutskaya-gres',        'Сургутские ГРЭС‑1 и ГРЭС‑2', 'Сургут',          73.42, 61.25, 'ГРЭС', [ 'side' => 'top',    'dx' => 0,   'dy' => -8 ] ],
		[ 'nizhnevartovskaya-gres',  'Нижневартовская ГРЭС',       'Излучинск',       76.55, 60.94, 'ГРЭС', [ 'side' => 'right',  'dx' => 0,   'dy' => 0  ] ],
		[ 'kashirskaya-gres',        'Каширская ГРЭС',             'Кашира',          38.19, 54.87, 'ГРЭС', [ 'side' => 'right',  'dx' => 0,   'dy' => 10 ] ],
		[ 'partizanskaya-gres',      'Партизанская ГРЭС',          'Лучегорск',       134.3, 46.46, 'ГРЭС', [ 'side' => 'bottom', 'dx' => 0,   'dy' => 0  ] ],
		[ 'neryungrinskaya-gres',    'Нерюнгринская ГРЭС',         'Серебряный Бор',  124.72,56.63, 'ГРЭС', [ 'side' => 'top',    'dx' => 0,   'dy' => -8 ] ],
		[ 'omskaya-tec-4',           'Омская ТЭЦ‑4',               'Омск',            73.30, 55.03, 'ТЭЦ',  [ 'side' => 'nomap',  'dx' => 0,   'dy' => 0  ] ],
		[ 'omskaya-tec-5',           'Омская ТЭЦ‑5',               'Омск',            73.52, 54.93, 'ТЭЦ',  [ 'side' => 'nomap',  'dx' => 0,   'dy' => 0  ] ],
		[ 'blagoveshchenskaya-tec',  'Благовещенская ТЭЦ',         'Благовещенск',    127.53,50.28, 'ТЭЦ',  [ 'side' => 'bottom', 'dx' => 0,   'dy' => 0  ] ],
		[ 'habarovskie-tec',         'Хабаровские ТЭЦ‑1, ТЭЦ‑2, ТЭЦ‑3', 'Хабаровск',  135.08,48.48, 'ТЭЦ',  [ 'side' => 'right',  'dx' => 0,   'dy' => 0  ] ],
		[ 'artemovskaya-tec',        'Артемовская ТЭЦ',            'Артём',           132.19,43.35, 'ТЭЦ',  [ 'side' => 'bottom', 'dx' => 0,   'dy' => 0  ] ],
		[ 'chitinskie-tec',          'Читинские ТЭЦ‑1 и ТЭЦ‑2',    'Чита',            113.5, 52.03, 'ТЭЦ',  [ 'side' => 'top',    'dx' => 0,   'dy' => -8 ] ],
		[ 'ulan-udenskaya-tec-1',    'Улан‑Удэнская ТЭЦ‑1',        'Улан‑Удэ',        107.6, 51.83, 'ТЭЦ',  [ 'side' => 'bottom', 'dx' => 0,   'dy' => 0  ] ],
		[ 'smolenskaya-tec-2',       'Смоленская ТЭЦ‑2',           'Смоленск',        32.05, 54.78, 'ТЭЦ',  [ 'side' => 'top',    'dx' => -18, 'dy' => -8 ] ],
		[ 'ufimskaya-tec-2',         'Уфимская ТЭЦ‑2',             'Уфа',             55.97, 54.74, 'ТЭЦ',  [ 'side' => 'bottom', 'dx' => 0,   'dy' => 0  ] ],
		[ 'nizhnekamskaya-tec-1',    'Нижнекамская ТЭЦ‑1',         'Нижнекамск',      51.82, 55.63, 'ТЭЦ',  [ 'side' => 'right',  'dx' => 0,   'dy' => -6 ] ],
		[ 'kazanskaya-tec-3',        'Казанская ТЭЦ‑3',            'Казань',          49.20, 55.85, 'ТЭЦ',  [ 'side' => 'left',   'dx' => 0,   'dy' => 6  ] ],
		[ 'sormovskaya-tec',         'Сормовская ТЭЦ',             'Нижний Новгород', 43.85, 56.34, 'ТЭЦ',  [ 'side' => 'top',    'dx' => 0,   'dy' => -8 ] ],
	];
	foreach ( $thermal as [ $slug, $name, $city, $lon, $lat, $tag, $label ] ) {
		$r[] = [
			'slug' => $slug, 'page' => null, 'draft' => true,
			'name' => $name, 'city' => $city, 'country' => 'Россия',
			'lon' => $lon, 'lat' => $lat, 'kind' => 'thermal', 'tag' => $tag, 'region' => 'ru',
			'status' => 'Поставки выполнены',
			'facts' => [
				[ 'Материал', 'Сталь 20, 09Г2С, 12Х1МФ' ],
				[ 'Номенклатура', 'Отводы крутоизогнутые, тройники, переходы' ],
				[ 'Нормативы', 'ГОСТ · ОСТ 108 · СТО ЦКТИ' ],
			],
			'label' => array_merge( [ 'off' => 26 ], $label ),
		];
	}

	// ── Гидроэнергетика, ГОКи, нефтегазохимия ────────────────────────────
	$other = [
		[ 'ust-srednekanskaya-ges', 'Усть‑Среднеканская ГЭС', 'Магаданская обл.', 152.35, 62.28, 'hydro',    'ГЭС',
		  [ [ 'Материал', '09Г2С' ], [ 'Номенклатура', 'Трубопроводы технической воды, опоры' ], [ 'Диаметр', 'Ø159–1020 мм' ] ],
		  [ 'side' => 'left', 'dx' => 0, 'dy' => 0 ] ],
		[ 'bystrinskiy-gok', 'Быстринский ГОК', 'Забайкальский край', 118.90, 51.30, 'mining', 'ГОК',
		  [ [ 'Материал', '09Г2С, 12Х18Н10Т' ], [ 'Номенклатура', 'Технологические трубопроводы, опоры' ], [ 'Нормативы', 'ГОСТ 17375‑2001 · ОСТ 36' ] ],
		  [ 'side' => 'bottom', 'dx' => 0, 'dy' => 0 ] ],
		[ 'gok-denisovskiy', 'ГОК «Денисовский»', 'Нерюнгри', 124.90, 56.90, 'mining', 'ГОК',
		  [ [ 'Материал', '09Г2С' ], [ 'Номенклатура', 'Трубопроводы обогатительной фабрики' ], [ 'Исполнение', 'Северное, до −60 °C' ] ],
		  [ 'side' => 'right', 'dx' => 0, 'dy' => 10 ] ],
		[ 'po-mayak', 'ПО «Маяк»', 'Озёрск', 60.70, 55.76, 'nuclear', 'Росатом',
		  [ [ 'Материал', '08Х18Н10Т, 10Х17Н13М2Т' ], [ 'Номенклатура', 'Детали трубопроводов спецназначения' ], [ 'Контроль', 'ВИК · УЗК · РК' ] ],
		  [ 'side' => 'bottom', 'dx' => 14, 'dy' => 0 ] ],
		[ 'zapsibneftehim', 'ЗапСибНефтехим', 'Тобольск', 68.25, 58.15, 'chemical', 'Нефтехимия',
		  [ [ 'Материал', '09Г2С, 12Х18Н10Т' ], [ 'Номенклатура', 'Отводы, тройники, переходы, фланцы' ], [ 'Нормативы', 'ГОСТ 17375‑2001 · ГОСТ 33259‑2015' ] ],
		  [ 'side' => 'top', 'dx' => 0, 'dy' => -8 ] ],
		[ 'amurskiy-ghk', 'Амурский ГХК', 'Свободный', 128.13, 51.38, 'chemical', 'Газохимия',
		  [ [ 'Материал', '09Г2С, 12Х18Н10Т' ], [ 'Номенклатура', 'Технологические трубопроводы, фасонные детали' ], [ 'Исполнение', 'Северное, до −60 °C' ] ],
		  [ 'side' => 'right', 'dx' => 0, 'dy' => 12 ] ],
		[ 'ust-luga-spg', 'Усть‑Луга, комплекс СПГ', 'Ленинградская обл.', 28.40, 59.68, 'chemical', 'СПГ',
		  [ [ 'Материал', '09Г2С, 12Х18Н10Т' ], [ 'Номенклатура', 'Детали трубопроводов, фланцы' ], [ 'Исполнение', 'Криогенное и общепромышленное' ] ],
		  [ 'side' => 'top', 'dx' => -10, 'dy' => -10 ] ],
	];
	foreach ( $other as [ $slug, $name, $city, $lon, $lat, $kind, $tag, $facts, $label ] ) {
		$r[] = [
			'slug' => $slug, 'page' => null, 'draft' => true,
			'name' => $name, 'city' => $city, 'country' => 'Россия',
			'lon' => $lon, 'lat' => $lat, 'kind' => $kind, 'tag' => $tag, 'region' => 'ru',
			'status' => 'Поставки выполнены', 'facts' => $facts,
			'label' => array_merge( [ 'off' => 26 ], $label ),
		];
	}

	// На карте у части объектов подпись короче — полное название распирает
	// плашку и мешает раскладке. В списке проектов имя остаётся полным.
	$promen_map_labels = [
		'surgutskaya-gres' => 'Сургутские ГРЭС',
		'habarovskie-tec'  => 'Хабаровские ТЭЦ',
		'chitinskie-tec'   => 'Читинские ТЭЦ',
		'ust-luga-spg'     => 'Усть‑Луга СПГ',
		'zapsibneftehim'   => 'ЗапСибНефтехим',
		'amurskiy-ghk'     => 'Амурский ГХК',
	];
	foreach ( $r as &$promen_ml_item ) {
		if ( isset( $promen_map_labels[ $promen_ml_item['slug'] ] ) ) {
			$promen_ml_item['map_label'] = $promen_map_labels[ $promen_ml_item['slug'] ];
		}
	}
	unset( $promen_ml_item );

	// Изображение объекта: файл assets/img/projects/<slug>.webp подхватывается
	// автоматически, поэтому новые картинки достаточно положить в папку.
	// Пять исходных фото лежат под своими историческими именами.
	$promen_legacy_photos = [
		'kurskaya-aes'              => 'img/projects/kursk2.png',
		'cherepetskaya-gres'        => 'img/projects/tec2.png',
		'teploelektrocentral-tec-3' => 'img/projects/tec3.png',
		'aes-ruppur'                => 'img/projects/rupp.png',
		'aes-akkuyu'                => 'img/projects/turk2.png',
	];
	$promen_img_dir = __DIR__ . '/../assets/img/projects/';
	foreach ( $r as &$promen_reg_item ) {
		$promen_reg_slug = $promen_reg_item['slug'];
		if ( file_exists( $promen_img_dir . $promen_reg_slug . '.webp' ) ) {
			$promen_reg_item['photo'] = 'img/projects/' . $promen_reg_slug . '.webp';
		} elseif ( isset( $promen_legacy_photos[ $promen_reg_slug ] ) ) {
			$promen_reg_item['photo'] = $promen_legacy_photos[ $promen_reg_slug ];
		}
	}
	unset( $promen_reg_item );

	$cache = $r;
	return $cache;
}
