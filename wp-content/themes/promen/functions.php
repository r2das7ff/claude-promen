<?php
/**
 * PROM-EN theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'PROMEN_VERSION', '0.99.7' );

// Версия ассетов привязана ко времени правки файлов темы: иначе браузер
// держит старый CSS/JS и правки не видно без ручного сброса кэша.
// Считаем во всех окружениях, не только локально. Раньше на сервере версия
// оставалась фиксированной (PROMEN_VERSION), и после заливки темы посетители
// продолжали получать прежние стили из кэша: файлы на диске новые, адрес
// прежний — ?ver=0.99.7. Проверено на заливке 2026-08-25: свежий product.css
// лежал на сервере, а карточка рисовалась по старому.
// Стоимость — два glob по каталогу темы на запрос, дальше отдаёт кэш ФС.
$promen_asset_stamp = 0;
foreach ( [ '/assets/css/*.css', '/assets/js/*.js' ] as $promen_asset_mask ) {
	foreach ( (array) glob( __DIR__ . $promen_asset_mask ) as $promen_asset_file ) {
		$promen_asset_stamp = max( $promen_asset_stamp, (int) @filemtime( $promen_asset_file ) );
	}
}
if ( $promen_asset_stamp ) {
	define( 'PROMEN_ASSET_VER', PROMEN_VERSION . '.' . $promen_asset_stamp );
}
if ( ! defined( 'PROMEN_ASSET_VER' ) ) {
	// Фолбэк для не-local окружения: там ветка выше не выполняется. Здесь
	// стояло define( 'PROMEN_ASSET_VER', PROMEN_ASSET_VER ) — константа
	// через саму себя, на PHP 8 это фатальная ошибка. Локально не видно:
	// окружение local, константу успевает задать ветка выше.
	define( 'PROMEN_ASSET_VER', PROMEN_VERSION );
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
} );

/**
 * Числа по-русски: «4 598», а не «4,598» — WP-локаль en_US форматирует
 * тысячные запятой во всех живых счётчиках (разделитель — неразрывный пробел).
 */
add_filter( 'number_format_i18n', function ( $formatted, $number, $decimals ) {
	return number_format( (float) $number, (int) $decimals, ',', "\u{00A0}" );
}, 10, 3 );

require_once __DIR__ . '/inc/projects-registry.php';
require_once __DIR__ . '/inc/product-data.php';
require_once __DIR__ . '/inc/blueprint-geometry.php';
require_once __DIR__ . '/inc/catalog-terms.php';
require_once __DIR__ . '/inc/catalog-schema.php';
require_once __DIR__ . '/inc/catalog-document.php';
require_once __DIR__ . '/inc/catalog-store.php';
require_once __DIR__ . '/inc/catalog-search.php';
require_once __DIR__ . '/inc/catalog-render.php';
require_once __DIR__ . '/inc/catalog-taxonomy.php';
require_once __DIR__ . '/inc/steel-reference.php';
require_once __DIR__ . '/inc/catalog-filters.php';
require_once __DIR__ . '/inc/category-page.php';
require_once __DIR__ . '/inc/catalog-api.php';
require_once __DIR__ . '/inc/delivery-calc.php';
require_once __DIR__ . '/inc/calculators.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/pilot-otvody.php';

// Главная — front-page.php (этап 1); прежний 302-редирект в каталог снят.

/**
 * Dev: HTML без кэша — иначе браузер держит страницу со старыми
 * ver-ссылками ассетов и правки «не видны» до жёсткого обновления.
 */
add_action( 'send_headers', function () {
	if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() && ! is_admin() ) {
		nocache_headers();
	}
} );

/**
 * Preload критичных начертаний: CondBlack — LCP-заголовки, Regular — основной текст.
 * URL без ?ver — должен байт-в-байт совпадать с url() из base.css, иначе двойная загрузка.
 */
add_action( 'wp_head', function () {
	$base = get_theme_file_uri( 'assets/fonts' );
	foreach ( [ 'DINPro-CondBlack', 'DINPro' ] as $f ) {
		printf(
			'<link rel="preload" href="%s/%s.woff2" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $base ),
			$f
		);
	}
}, 2 );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'promen-base', get_theme_file_uri( 'assets/css/base.css' ), [], PROMEN_ASSET_VER );
	wp_enqueue_style( 'promen-catalog', get_theme_file_uri( 'assets/css/catalog.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'promen-product', get_theme_file_uri( 'assets/css/product.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-product', get_theme_file_uri( 'assets/js/product.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	$is_cat_page = is_tax( 'product_cat', promen_section_landing_slugs() );
	$is_registry = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_tax( 'norm' ) || is_post_type_archive( 'product' ) );

	if ( $is_cat_page ) {
		wp_enqueue_style( 'promen-category', get_theme_file_uri( 'assets/css/category-sdt.css' ), [ 'promen-base', 'promen-catalog' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-category-sdt', get_theme_file_uri( 'assets/js/category-sdt.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Секция «Отдел продаж» (parts/managers.php) — главная и «Контакты».
	if ( is_front_page() || is_page( 'contacts' ) ) {
		wp_enqueue_style( 'promen-managers', get_theme_file_uri( 'assets/css/managers.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-managers', get_theme_file_uri( 'assets/js/managers.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Главная: страничные стили/скрипты + GSAP ScrollTrigger (self-hosted).
	if ( is_front_page() ) {
		wp_enqueue_style( 'promen-front', get_theme_file_uri( 'assets/css/front.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-gsap', get_theme_file_uri( 'assets/js/vendor/gsap.min.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-scrolltrigger', get_theme_file_uri( 'assets/js/vendor/ScrollTrigger.min.js' ), [ 'promen-gsap' ], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-front', get_theme_file_uri( 'assets/js/front.js' ), [ 'promen-scrolltrigger' ], PROMEN_ASSET_VER, [ 'in_footer' => true ] );

		// Ключи — макетные слаги из front.js; значения — живые /proekty/<slug>/.
		$projects = [];
		foreach ( [
			'proekt-kurskaya-aes'              => 'kurskaya-aes',
			'proekt-cherepetskaya-gres'        => 'cherepetskaya-gres',
			'proekt-aes-ruppur'                => 'aes-ruppur',
			'proekt-aes-akkuyu'                => 'aes-akkuyu',
			'proekt-teploelektrocentral-tec-3' => 'teploelektrocentral-tec-3',
		] as $key => $child ) {
			$url = promen_project_url( $child );
			if ( $url ) {
				$projects[ $key ] = $url;
			}
		}
		// География поставок для карты — из единого реестра, чтобы карта,
		// бегущая строка и страница «Проекты» не расходились между собой.
		$geo = [];
		foreach ( promen_projects_registry() as $promen_geo_item ) {
			if ( 'nomap' === $promen_geo_item['label']['side'] ) {
				continue; // объект есть в списке проектов, но его точка совпадает с соседней
			}
			$geo[] = [
				'id'     => $promen_geo_item['slug'],
				'label'  => $promen_geo_item['map_label'] ?? $promen_geo_item['name'],
				'sub'    => $promen_geo_item['city'],
				'country'=> $promen_geo_item['country'],
				'lon'    => $promen_geo_item['lon'],
				'lat'    => $promen_geo_item['lat'],
				'kind'   => $promen_geo_item['kind'],
				'tag'    => $promen_geo_item['tag'],
				'intl'   => 'intl' === $promen_geo_item['region'],
				'status' => $promen_geo_item['status'],
				'facts'  => $promen_geo_item['facts'],
				'photo'  => isset( $promen_geo_item['photo'] ) ? trailingslashit( get_theme_file_uri( 'assets' ) ) . $promen_geo_item['photo'] : '',
				'href'   => $promen_geo_item['page'] ? promen_project_url( $promen_geo_item['page'] ) : '',
				'label_side' => $promen_geo_item['label']['side'],
				'label_off'  => $promen_geo_item['label']['off'],
				'label_dx'   => $promen_geo_item['label']['dx'],
				'label_dy'   => $promen_geo_item['label']['dy'],
			];
		}

		wp_localize_script( 'promen-front', 'promenFront', [
			'assets'     => trailingslashit( get_theme_file_uri( 'assets' ) ),
			'catalogUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			'projects'   => $projects,
			'geo'        => $geo,
		] );
	}

	// Появление контента при прокрутке на внутренних страницах. Главная и
	// производство ведут своё появление сами, там модуль не нужен.
	if ( ! is_front_page() && ! is_page( 'production' ) ) {
		wp_enqueue_script( 'promen-reveal', get_theme_file_uri( 'assets/js/reveal.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Производство: страничные стили/скрипты.
	if ( is_page( 'production' ) ) {
		wp_enqueue_style( 'promen-production', get_theme_file_uri( 'assets/css/production.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-production', get_theme_file_uri( 'assets/js/production.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Проекты: список и 5 детальных страниц.
	$promen_project_slugs = [ 'kurskaya-aes', 'aes-akkuyu', 'aes-ruppur', 'cherepetskaya-gres', 'teploelektrocentral-tec-3' ];
	if ( is_page( 'proekty' ) ) {
		wp_enqueue_style( 'promen-proekty', get_theme_file_uri( 'assets/css/proekty.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}
	if ( is_page( $promen_project_slugs ) ) {
		wp_enqueue_style( 'promen-proekt', get_theme_file_uri( 'assets/css/proekt.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Статьи: список (JS-рендер карточек) и 6 детальных.
	if ( is_page( 'stati' ) ) {
		wp_enqueue_style( 'promen-stati', get_theme_file_uri( 'assets/css/stati.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-stati', get_theme_file_uri( 'assets/js/stati.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		$articles = [];
		foreach ( [ 'vybor-stali', 'otvod-svarnoy-besshovnyy', 'kontrol-kachestva', 'normativnaya-baza', 'chertezh-zakazchika', 'postavka-aes-tes' ] as $slug ) {
			$url = promen_article_url( $slug );
			if ( $url ) {
				$articles[ $slug ] = $url;
			}
		}
		wp_localize_script( 'promen-stati', 'promenStati', [
			'assets'   => trailingslashit( get_theme_file_uri( 'assets' ) ),
			'articles' => $articles,
		] );
	}
	$promen_article_paths = [
		'stati/statya-vybor-stali',
		'stati/statya-otvod-svarnoy-besshovnyy',
		'stati/statya-kontrol-kachestva',
		'stati/statya-normativnaya-baza',
		'stati/statya-chertezh-zakazchika',
		'stati/statya-postavka-aes-tes',
	];
	if ( is_page( $promen_article_paths ) ) {
		wp_enqueue_style( 'promen-statya', get_theme_file_uri( 'assets/css/statya.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-statya', get_theme_file_uri( 'assets/js/statya.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Нормативная база: реестр ГОСТ/ОСТ/СТО/ТУ (JS-рендер).
	if ( is_page( 'normativnaya-baza' ) && ! is_page( $promen_article_paths ) ) {
		wp_enqueue_style( 'promen-nb', get_theme_file_uri( 'assets/css/nb.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-nb', get_theme_file_uri( 'assets/js/nb.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_localize_script( 'promen-nb', 'promenNB', [
			'catalogUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			// Макетные короткие категории реестра → группы каталога.
			'groups'     => [
				'sdt'  => 'sdt',
				'fl'   => 'flancy',
				'krep' => 'krepezh',
				'op'   => 'opory',
				'zra'  => 'armatura',
				'tr'   => 'truby',
				'iz'   => 'izolyatsiya',
				'td'   => 'tochenye',
			],
		] );
	}

	// Калькуляторы: хаб и все страницы раздела — общие стили/скрипт + REST-конфиг.
	$calc_slug = function_exists( 'promen_calc_current_slug' ) ? promen_calc_current_slug() : '';
	if ( $calc_slug !== '' ) {
		wp_enqueue_style( 'promen-calc', get_theme_file_uri( 'assets/css/calc.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-calc', get_theme_file_uri( 'assets/js/calc.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		// Без footer-pin: он для страниц БЕЗ s10-формы (наезд футера поверх
		// контента), а у калькуляторов s10 есть и высота меняется асинхронно —
		// зона наезжала на форму и поля (скрин 2026-08-03).
		$calc_pages = [];
		foreach ( array_keys( promen_calc_pages() ) as $cslug ) {
			$url = promen_calc_url( $cslug );
			if ( $url ) {
				$calc_pages[ $cslug ] = $url;
			}
		}
		wp_localize_script( 'promen-calc', 'promenCalc', [
			'api'         => rest_url( 'promen/v1/calc' ),
			'deliveryApi' => rest_url( 'promen/v1/delivery' ),
			'delivery'    => function_exists( 'promen_dellin_appkey' ) && promen_dellin_appkey() !== '',
			'catalogUrl'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			'pages'       => $calc_pages,
		] );
	}

	// Контакты / 404 / политика ПДн: страничные стили и скрипты.
	if ( is_page( 'contacts' ) ) {
		wp_enqueue_style( 'promen-contacts', get_theme_file_uri( 'assets/css/contacts.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}
	if ( is_404() ) {
		wp_enqueue_style( 'promen-404', get_theme_file_uri( 'assets/css/page-404.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-404', get_theme_file_uri( 'assets/js/page-404.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}
	if ( is_page( 'privacy-policy' ) ) {
		wp_enqueue_style( 'promen-privacy', get_theme_file_uri( 'assets/css/privacy.css' ), [ 'promen-base' ], PROMEN_ASSET_VER );
		wp_enqueue_script( 'promen-privacy', get_theme_file_uri( 'assets/js/privacy.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	}

	// Хром сайта: часы, бургер/drawer + модалка запроса — на всех страницах.
	wp_enqueue_script( 'promen-chrome', get_theme_file_uri( 'assets/js/chrome.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	// Подменяющий выпадающий список: включается атрибутом data-select у <select>,
	// стили — base.css. Глобально, чтобы новые страницы получали его сами.
	wp_enqueue_script( 'promen-select', get_theme_file_uri( 'assets/js/select.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	wp_enqueue_script( 'promen-request-modal', get_theme_file_uri( 'assets/js/request-modal.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
	wp_localize_script( 'promen-request-modal', 'promenRM', [
		'ajaxUrl'    => admin_url( 'admin-post.php' ),
		'nonce'      => wp_create_nonce( 'promen_request' ),
		'privacyUrl' => promen_privacy_url(),
		'email'      => 'zakaz@prom-en.com',
	] );

	// Живой реестр: корень каталога и страницы категорий (встроенный partial).
	if ( $is_registry || $is_cat_page ) {
		wp_enqueue_script( 'promen-catalog', get_theme_file_uri( 'assets/js/catalog.js' ), [], PROMEN_ASSET_VER, [ 'in_footer' => true ] );
		wp_localize_script( 'promen-catalog', 'promenCatalog', [
			'apiUrl'   => rest_url( 'promen/v1/catalog' ),
			'perPage'  => 30,
			'group'    => function_exists( 'promen_catalog_active_group' ) ? promen_catalog_active_group() : '',
			'labels'   => [
				'industry' => 'Отрасль',
				'steel'    => 'Сталь',
				'angle'    => 'Угол',
				'gost'     => 'ГОСТ',
				'pn'       => 'PN',
			],
			'rangeLbl' => [
				'dn' => 'DN, мм',
				'pn' => 'PN, МПа',
				's'  => 'Стенка s, мм',
			],
			'industryTags' => [
				'aes' => 'АЭС',
				'tes' => 'ТЭС',
				'gkh' => 'ЖКХ',
				'ngk' => 'НГК',
			],
			'views' => function_exists( 'promen_catalog_group_views_js' ) ? promen_catalog_group_views_js() : [],
		] );
	}
} );

/** Дефолтные стили WooCommerce отключаем целиком. */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/** Явно убираем emoji-скрипты и generator — чистый <head>. */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
} );

/**
 * Опубликованная страница по слагу — или null.
 * Пункты навигации появляются по мере публикации страниц (без мёртвых ссылок).
 */
function promen_page( string $slug ): ?WP_Post {
	$page = get_page_by_path( $slug );
	return ( $page instanceof WP_Post && 'publish' === $page->post_status ) ? $page : null;
}

/**
 * Пункты главной навигации — IA по html/ (Open Design):
 * Главная · Каталог · Производство · Проекты · Нормативы · Статьи · Контакты.
 */
function promen_nav_items(): array {
	$items = [
		[ 'label' => 'Главная', 'url' => home_url( '/' ), 'active' => is_front_page() ],
		[
			'label'  => 'Каталог',
			'url'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			'active' => function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_shop() ),
		],
	];
	$pages = [
		'production'         => 'Производство',
		'proekty'            => 'Проекты',
		'normativnaya-baza'  => 'Нормативы',
		'kalkulyatory'       => 'Калькуляторы',
		'stati'              => 'Статьи',
		'contacts'           => 'Контакты',
	];
	foreach ( $pages as $slug => $label ) {
		$page = promen_page( $slug );
		if ( $page ) {
			// Хаб калькуляторов активен и на дочерних страницах раздела.
			$active = is_page( $page->ID )
				|| ( $slug === 'kalkulyatory' && function_exists( 'promen_calc_current_slug' ) && promen_calc_current_slug() !== '' );
			$items[] = [
				'label'  => $label,
				'url'    => get_permalink( $page ),
				'active' => $active,
			];
		}
	}
	return $items;
}

/**
 * Навигация футера: все живые разделы сайта + СДТ.
 * Пункты страниц появляются по мере публикации (мёртвых ссылок нет).
 */
function promen_footer_nav_items(): array {
	$items = [
		[ 'label' => 'Главная', 'url' => home_url( '/' ) ],
		[
			'label' => 'Каталог',
			'url'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
		],
	];
	$sdt_url = function_exists( 'promen_product_cat_link' ) ? promen_product_cat_link( 'sdt' ) : '';
	if ( $sdt_url !== '' ) {
		$items[] = [ 'label' => 'Соединительные детали', 'url' => $sdt_url ];
	}
	foreach ( [
		'production'        => 'Производство',
		'proekty'           => 'Проекты',
		'normativnaya-baza' => 'Нормативы',
		'kalkulyatory'      => 'Калькуляторы',
		'stati'             => 'Статьи',
		'contacts'          => 'Контакты',
	] as $slug => $label ) {
		$page = promen_page( $slug );
		if ( $page ) {
			$items[] = [ 'label' => $label, 'url' => get_permalink( $page ) ];
		}
	}
	return $items;
}

/** URL детальной страницы проекта /proekty/<slug>/ ('' — не опубликована). */
function promen_project_url( string $slug ): string {
	$page = promen_page( 'proekty/' . $slug );
	return $page ? (string) get_permalink( $page ) : '';
}

/** URL статьи /stati/statya-<slug>/ ('' — не опубликована). */
function promen_article_url( string $slug ): string {
	$page = promen_page( 'stati/statya-' . $slug );
	return $page ? (string) get_permalink( $page ) : '';
}

/**
 * «Открыть изделие» на страницах проектов: живая карточка отвода 90°
 * ГОСТ 17375-2001 (как в макете product-otvod-90.html); fallback — реестр отводов.
 */
function promen_demo_product_url(): string {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}
	$found = get_posts( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		's'              => 'Отвод 90° ГОСТ 17375-2001',
	] );
	if ( $found ) {
		$url = (string) get_permalink( $found[0] );
	} else {
		$catalog = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
		$url     = add_query_arg( 'group', 'otvody', $catalog );
	}
	return $url;
}

/**
 * URL опубликованной политики обработки ПДн ('' — пока не опубликована).
 * Как только этап «политика» публикует страницу — ссылка появляется
 * в футере, s10 и модалке автоматически.
 */
function promen_privacy_url(): string {
	$page = promen_page( 'privacy-policy' );
	if ( ! $page ) {
		$id   = (int) get_option( 'wp_page_for_privacy_policy' );
		$page = ( $id && 'publish' === get_post_status( $id ) ) ? get_post( $id ) : null;
	}
	return $page ? (string) get_permalink( $page ) : '';
}

/** Текст левой рельсы: КТЛ–01 в каталоге, имя завода на витрине. */
function promen_strip_text(): string {
	$is_catalog = ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
		|| ( function_exists( 'is_shop' ) && is_shop() )
		|| is_tax( 'norm' );
	$default = $is_catalog ? 'КТЛ–01' : 'Завод Промышленная Энергетика';
	return (string) apply_filters( 'promen_strip_text', $default );
}

/** Индекс страницы в футер-баре (шаблоны задают свой через фильтр). */
function promen_footer_idx(): string {
	$is_catalog = ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
		|| ( function_exists( 'is_shop' ) && is_shop() )
		|| is_tax( 'norm' );
	$default = $is_catalog ? 'ПЭ-КТЛ.FTR / REV.1' : 'ПЭ.FTR / REV.1';
	return (string) apply_filters( 'promen_footer_idx', $default );
}

/**
 * Мета-описание страницы. Без него поисковик берёт случайный фрагмент текста,
 * а у главной это была строка HUD-декора.
 */
function promen_meta_description_text(): string {
	$desc = '';
	if ( is_front_page() ) {
		$desc = 'Завод «Промышленная Энергетика»: детали и сборочные единицы трубопроводов для АЭС и ТЭС. Изготовление по ГОСТ, ОСТ, СТО ЦКТИ и чертежам заказчика.';
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		// У карточки берём очищенное описание — сырой post_content тянет
		// в сниппет таблицы характеристик.
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$desc = wp_strip_all_tags( promen_sanitize_desc( $p->get_id(), $p->get_short_description() ?: $p->get_description() ) );
			if ( '' === trim( $desc ) ) {
				$desc = promen_product_desc_fallback( $p );
			}
		}
	} elseif ( is_tax( 'product_cat' ) || is_tax( 'norm' ) ) {
		// Только первый абзац описания раздела: дальше идут подробности,
		// которые в сниппет всё равно не влезут.
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			if ( $term->description ) {
				$parts = preg_split( '/\n\s*\n/', trim( wp_strip_all_tags( $term->description ) ), 2 );
				$desc  = $parts[0] ?? '';
			}
			if ( '' === trim( (string) $desc ) ) {
				$desc = promen_term_desc_fallback( $term );
			}
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$desc = 'Каталог соединительных деталей трубопроводов, фланцев и крепежа. Запрос коммерческого предложения без корзины.';
	} elseif ( is_singular() ) {
		$post_obj = get_queried_object();
		if ( $post_obj instanceof WP_Post ) {
			$desc = has_excerpt( $post_obj ) ? get_the_excerpt( $post_obj ) : wp_strip_all_tags( $post_obj->post_content );
		}
	} elseif ( is_tax() || is_category() ) {
		$desc = term_description();
	}

	// Страницы-шаблоны рисуют вёрстку в PHP, их post_content пуст —
	// для них описание задаётся здесь, иначе тег просто не выводится.
	if ( '' === trim( wp_strip_all_tags( (string) $desc ) ) && is_page() ) {
		$by_slug = [
			'proekty'     => 'Реализованные поставки завода «Промышленная Энергетика»: объекты атомной и тепловой энергетики в России и за рубежом — АЭС, ТЭЦ, ГРЭС, ГЭС и нефтегазохимия.',
			'production'  => 'Производство деталей трубопроводов: анализ КД, подбор исполнения и марки стали, изготовление, контроль металла, ВИК, УЗК, РК и сопроводительная документация.',
			'contacts'    => 'Контакты завода «Промышленная Энергетика»: Челябинск, ул. Орджоникидзе, 37. Отдел продаж, приём заявок и запрос коммерческого предложения.',
			'kalkulyatory' => 'Калькуляторы завода: подбор и расчёт массы отводов, тройников, переходов, фланцев и труб по ГОСТ, ОСТ и СТО ЦКТИ.',
			'normativnaya-baza' => 'Нормативная база завода «Промышленная Энергетика»: реестр ГОСТ, ОСТ, СТО ЦКТИ, СТО СРО-П и ТУ, применяемых при изготовлении деталей трубопроводов.',
			'stati'       => 'Статьи завода «Промышленная Энергетика»: выбор марки стали, контроль качества СДТ, нормативная база, изготовление по чертежу заказчика.',
		];
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $by_slug[ $slug ] ) ) {
			$desc = $by_slug[ $slug ];
		}
	}

	// Дочерние страницы разделов — конкретный проект, статья, калькулятор —
	// своего описания не имеют: их вёрстка нарисована в PHP, post_content пуст,
	// а карта по слагам выше знает только сами разделы. Собираем из заголовка,
	// подбирая формулировку по родителю.
	if ( '' === trim( wp_strip_all_tags( (string) $desc ) ) && is_singular() ) {
		$obj = get_queried_object();
		if ( $obj instanceof WP_Post ) {
			$parent_slug = $obj->post_parent ? (string) get_post_field( 'post_name', $obj->post_parent ) : '';
			$by_parent   = [
				'proekty'      => '%s — реализованная поставка завода «Промышленная Энергетика»: детали и сборочные единицы трубопроводов для объектов энергетики.',
				'stati'        => '%s — материал завода «Промышленная Энергетика» о деталях трубопроводов, марках стали и нормативной базе.',
				'kalkulyatory' => '%s — калькулятор завода «Промышленная Энергетика»: подбор и расчёт по ГОСТ, ОСТ и СТО ЦКТИ.',
			];
			$tpl  = $by_parent[ $parent_slug ] ?? '%s — завод «Промышленная Энергетика»: изготовление деталей трубопроводов по ГОСТ, ОСТ и чертежам заказчика.';
			$desc = sprintf( $tpl, get_the_title( $obj ) );
		}
	}

	// Переносы и двойные пробелы из post_content схлопываем: иначе они
	// уезжают в атрибут тега как есть.
	$desc = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $desc ) ) );
	if ( '' === $desc ) {
		return '';
	}
	// Пагинация: номер страницы в описании, иначе все страницы раздела несут
	// один и тот же текст — на «Отводах» это 109 одинаковых описаний.
	$paged  = function_exists( 'promen_paged_number' ) ? promen_paged_number() : 1;
	$suffix = $paged > 1 ? ' Страница ' . $paged . '.' : '';

	// 160 символов — предел, после которого сниппет обрезают и Яндекс, и Google.
	// Рвём по границе слова: wp_html_excerpt резал по символу, посреди ГОСТа.
	$limit = 160 - mb_strlen( $suffix );
	if ( mb_strlen( $desc ) > $limit ) {
		$cut   = mb_substr( $desc, 0, $limit - 1 );
		$space = mb_strrpos( $cut, ' ' );
		$desc  = rtrim( false !== $space ? mb_substr( $cut, 0, $space ) : $cut, " ,.;:—-" ) . '…';
	}
	return $desc . $suffix;
}

/** Сам тег. Текст отдаётся отдельно — его же берут Open Graph и Twitter Card. */
function promen_meta_description(): void {
	$desc = promen_meta_description_text();
	if ( '' !== $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'promen_meta_description', 1 );
