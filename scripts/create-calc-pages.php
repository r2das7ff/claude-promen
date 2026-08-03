<?php
/**
 * Создание страниц раздела «Калькуляторы» (идемпотентно).
 * Запуск: docker compose run --rm wpcli eval-file /scripts/create-calc-pages.php
 *
 * Слаги детей должны совпадать с page-<slug>.php темы и ключами
 * promen_calc_pages() (inc/calculators.php) — шаблон и карточка хаба
 * подхватываются по слагу.
 */

defined( 'ABSPATH' ) || exit;

$pages = [
	'kalkulyatory' => [
		'title'   => 'Калькуляторы',
		'excerpt' => 'Калькуляторы веса деталей трубопровода, фланцевого крепежа, метизов и труб — по данным каталога завода «Промышленная Энергетика». Расчёт массы партии и доставки онлайн.',
		'parent'  => 0,
	],
	'ves-sdt' => [
		'title'   => 'Калькулятор веса отводов, переходов, тройников, заглушек и днищ',
		'excerpt' => 'Онлайн-расчёт массы отводов, переходов, тройников, заглушек и днищ по ГОСТ 17375, 17376, 17378, 17379, 30753, 6533 и ОСТ. Вес штуки и партии + доставка.',
		'parent'  => 'kalkulyatory',
	],
	'flancevyy-krepezh' => [
		'title'   => 'Калькулятор фланцев и крепежа (КОФ)',
		'excerpt' => 'Вес фланца по ГОСТ 33259 и комплект крепежа к фланцевому соединению: количество и длина шпилек или болтов, гайки, шайбы, масса комплекта КОФ.',
		'parent'  => 'kalkulyatory',
	],
	'metizy' => [
		'title'   => 'Калькулятор метизов: перевод кг в штуки',
		'excerpt' => 'Сколько болтов, гаек, шпилек или шайб в килограмме: перевод количества крепежа в массу и обратно по теоретической массе ГОСТ (за 1000 шт).',
		'parent'  => 'kalkulyatory',
	],
	'truby-ves' => [
		'title'   => 'Трубный калькулятор: вес метра, метры в тонны',
		'excerpt' => 'Вес метра стальной трубы по ГОСТ 10704, 8732, 3262, перевод метров в тонны и обратно, площадь окраски и вместимость трубопровода — онлайн.',
		'parent'  => 'kalkulyatory',
	],
	'dn-dyuym' => [
		'title'   => 'Таблица DN, дюймов и наружных диаметров труб',
		'excerpt' => 'Соответствие условного прохода DN, дюймов (NPS) и наружных диаметров труб по ГОСТ и EN/ASME: DN 50 = 2″ = Ø57 (ГОСТ) = Ø60,3 (EN). Полная таблица с поиском.',
		'parent'  => 'kalkulyatory',
	],
	'analogi-staley' => [
		'title'   => 'Аналоги марок стали: ГОСТ, EN, ASTM, DIN',
		'excerpt' => 'Таблица соответствия марок стали: 20 — P265GH/A106B, 09Г2С — P355NH/A516, 12Х18Н10Т — AISI 321. Подбор российских и зарубежных аналогов с поиском.',
		'parent'  => 'kalkulyatory',
	],
];

$ids = [];
foreach ( $pages as $slug => $p ) {
	$parent_id = $p['parent'] === 0 ? 0 : (int) ( $ids[ $p['parent'] ] ?? 0 );
	$path      = $parent_id ? $pages[ $p['parent'] ]['title'] : '';
	$existing  = get_page_by_path( $parent_id ? 'kalkulyatory/' . $slug : $slug );

	if ( $existing instanceof WP_Post ) {
		$ids[ $slug ] = $existing->ID;
		// Обновляем заголовок/экцерпт/статус — скрипт можно гонять повторно.
		wp_update_post( [
			'ID'           => $existing->ID,
			'post_title'   => $p['title'],
			'post_excerpt' => $p['excerpt'],
			'post_status'  => 'publish',
			'post_parent'  => $parent_id,
		] );
		WP_CLI::log( "= {$slug}: обновлена (#{$existing->ID})" );
		continue;
	}

	$id = wp_insert_post( [
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $p['title'],
		'post_name'    => $slug,
		'post_excerpt' => $p['excerpt'],
		'post_parent'  => $parent_id,
		'post_content' => '',
	], true );

	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "{$slug}: " . $id->get_error_message(), false );
		continue;
	}
	$ids[ $slug ] = (int) $id;
	WP_CLI::log( "+ {$slug}: создана (#{$id})" );
}

flush_rewrite_rules();
WP_CLI::success( 'Страницы калькуляторов готовы.' );
