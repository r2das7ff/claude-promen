<?php
/**
 * Чистка мусорного батча «ДТ» в заглушках (ОСТ 24.125.22-89 / 24.125.23-89).
 *
 * Парсер таблиц положил в product_type заголовок семейства ОСТ 24.125.01…26-89
 * («Детали и сборочные единицы… для трубопроводов АЭС») → «ДТ». Из 15 строк 13 —
 * дубли уже существующих «Заглушка …» по тем же нормативам и размерам, ещё две
 * (33х2) — единственные носители типоразмера, которого в правильном наборе нет.
 *
 * Дубли сносим с 301 на выжившего (карта promen_dedup_redirects), 33х2 —
 * переименовываем в заглушки. Стали не трогаем: у ДТ их 4, у соседей 12, но
 * дотягивать список = выдумывать данные (ОСТ аустенитный, а в соседях 09Г2С и 20).
 *
 * Запуск: docker compose run --rm wpcli eval-file /scripts/_fix_dt_zaglushki.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

/** sku мусорной позиции => sku выжившей заглушки того же размера и норматива. */
$merge = [
	// ОСТ 24.125.22-89
	'comp-ост-24-125-22-89-01--20x1-5' => 'ост-2412522-89-2--20-15---1',
	'comp-ост-24-125-22-89-02--20x1-5' => 'ост-2412522-89-2--20-15---1',
	'comp-ост-24-125-22-89-03--22x1-5' => 'ост-2412522-89-4-03-22-15---1',
	'comp-ост-24-125-22-89-04--22x1-5' => 'ост-2412522-89-4-03-22-15---1',
	'comp-ост-24-125-22-89-05--27x2'   => 'ост-2412522-89-6-05-27-2---1',
	'comp-ост-24-125-22-89-06--27x2'   => 'ост-2412522-89-6-05-27-2---1',
	'comp-ост-24-125-22-89-07--27x1-5' => 'ост-2412522-89-8-07-27-15---1',
	'comp-ост-24-125-22-89-08--27x1-5' => 'ост-2412522-89-8-07-27-15---1',
	// 09 — вторая половина разрезанной строки таблицы 33х2, масса ушла в исп. 10
	'comp-ост-24-125-22-89-09--33x2'   => 'comp-ост-24-125-22-89-10--33x2',
	// ОСТ 24.125.23-89
	'comp-ост-24-125-23-89-01--20x1-5' => 'ост-2412523-89-1-2-20-15---1',
	'comp-ост-24-125-23-89-02--22x1-5' => 'ост-2412523-89-2-36-22-15---1',
	'comp-ост-24-125-23-89-03--27x1-5' => 'ост-2412523-89-3-28-27-15---1',
	'comp-ост-24-125-23-89-04--27x2'   => 'ост-2412523-89-4-46-27-2---1',
];

/** sku => новое имя/слаг/исполнение выживших 33х2. */
$rename = [
	'comp-ост-24-125-22-89-10--33x2' => [
		'title' => 'Заглушка 33х2 ОСТ 24.125.22-1989',
		'slug'  => 'zaglushka-33h2-ost-24-125-22-1989',
		'exec'  => '10',
	],
	'comp-ост-24-125-23-89-05--33x2' => [
		'title' => 'Заглушка 33х2 ОСТ 24.125.23-1989',
		'slug'  => 'zaglushka-33h2-ost-24-125-23-1989',
		'exec'  => '5',
	],
];

$sku_id = static function ( string $sku ): int {
	$id = wc_get_product_id_by_sku( $sku );
	return $id ? (int) $id : 0;
};

$redirects = get_option( 'promen_dedup_redirects', [] );
if ( ! is_array( $redirects ) ) {
	$redirects = [];
}
$deleted = [];
$renamed = [];
$meili_ids = [];

// 1. Переименовать выживших 33х2 (до удаления — они цели редиректов).
foreach ( $rename as $sku => $new ) {
	$id = $sku_id( $sku );
	if ( ! $id ) {
		WP_CLI::warning( "не найден: {$sku}" );
		continue;
	}
	$old_slug = get_post_field( 'post_name', $id );

	wp_update_post( [
		'ID'         => $id,
		'post_title' => $new['title'],
		'post_name'  => $new['slug'],
	] );

	update_post_meta( $id, '_promen_family', 'Заглушки' );
	update_post_meta( $id, '_promen_gost_designation', '33х2' );

	$dims = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true );
	if ( ! is_array( $dims ) ) {
		$dims = [];
	}
	$dims['product_type']      = 'ЗЭ';
	$dims['execution']         = $new['exec'];
	$dims['gost_designation']  = '33х2';
	update_post_meta( $id, '_promen_dims', wp_json_encode( $dims, JSON_UNESCAPED_UNICODE ) );

	if ( $old_slug && $old_slug !== $new['slug'] ) {
		$redirects[ $old_slug ] = $id;
	}

	// Канон-строку переименованного обновляем здесь же. Локально её чинит
	// последующий `wp promen catalog-rebuild`, но на стенде этой команды нет:
	// promen-cli.php живёт в /scripts, который туда не выкладывается. Без
	// апсерта в витрине остался бы старый слаг и старое название.
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $id, false );
	}

	$renamed[ $sku ] = [ 'id' => $id, 'slug' => $new['slug'], 'was' => $old_slug ];
}

// 2. Снести дубли с 301 на выжившего.
foreach ( $merge as $sku => $target_sku ) {
	$id = $sku_id( $sku );
	if ( ! $id ) {
		WP_CLI::warning( "не найден: {$sku}" );
		continue;
	}
	$target = $sku_id( $target_sku );
	if ( ! $target ) {
		WP_CLI::warning( "нет цели редиректа {$target_sku} для {$sku} — пропуск" );
		continue;
	}

	$slug = get_post_field( 'post_name', $id );
	if ( $slug ) {
		$redirects[ $slug ] = $target;
	}

	$product = wc_get_product( $id );
	if ( $product ) {
		foreach ( $product->get_children() as $vid ) {
			wp_delete_post( (int) $vid, true );
		}
		$product->delete( true );
	} else {
		wp_delete_post( $id, true );
	}

	if ( function_exists( 'promen_catalog_delete' ) ) {
		promen_catalog_delete( $id );
	}
	$meili_ids[] = (string) $id;
	$deleted[]   = [ 'sku' => $sku, 'id' => $id, 'slug' => $slug, 'to' => $target ];
}

update_option( 'promen_dedup_redirects', $redirects, false );

// 3. Выкинуть снесённые документы из Meilisearch (reindex_all только upsert'ит).
if ( $meili_ids && function_exists( 'promen_meili_request' ) && function_exists( 'promen_meili_index' ) ) {
	$r = promen_meili_request(
		'POST',
		'/indexes/' . promen_meili_index() . '/documents/delete-batch',
		$meili_ids
	);
	WP_CLI::log( 'Meilisearch delete-batch: ' . ( ! empty( $r['ok'] ) ? 'ok' : 'ошибка' ) );
}

WP_CLI::log( 'Переименовано: ' . wp_json_encode( $renamed, JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Удалено: ' . count( $deleted ) );
WP_CLI::log( 'Карта редиректов: ' . count( $redirects ) . ' записей.' );
WP_CLI::success( 'ДТ-батч разобран. Дальше: wp promen catalog-rebuild.' );
