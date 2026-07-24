<?php
/**
 * PROM-EN theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'PROMEN_VERSION', '0.32.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
} );

require_once __DIR__ . '/inc/product-data.php';
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
	wp_enqueue_style( 'promen-base', get_theme_file_uri( 'assets/css/base.css' ), [], PROMEN_VERSION );
	wp_enqueue_style( 'promen-catalog', get_theme_file_uri( 'assets/css/catalog.css' ), [ 'promen-base' ], PROMEN_VERSION );
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'promen-product', get_theme_file_uri( 'assets/css/product.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-product', get_theme_file_uri( 'assets/js/product.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}

	$is_cat_page = is_tax( 'product_cat', promen_section_landing_slugs() );
	$is_registry = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_tax( 'norm' ) || is_post_type_archive( 'product' ) );

	if ( $is_cat_page ) {
		wp_enqueue_style( 'promen-category', get_theme_file_uri( 'assets/css/category-sdt.css' ), [ 'promen-base', 'promen-catalog' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-category-sdt', get_theme_file_uri( 'assets/js/category-sdt.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}

	// Главная: страничные стили/скрипты + GSAP ScrollTrigger (self-hosted).
	if ( is_front_page() ) {
		wp_enqueue_style( 'promen-front', get_theme_file_uri( 'assets/css/front.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-gsap', get_theme_file_uri( 'assets/js/vendor/gsap.min.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-scrolltrigger', get_theme_file_uri( 'assets/js/vendor/ScrollTrigger.min.js' ), [ 'promen-gsap' ], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-front', get_theme_file_uri( 'assets/js/front.js' ), [ 'promen-scrolltrigger' ], PROMEN_VERSION, [ 'in_footer' => true ] );

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
		wp_localize_script( 'promen-front', 'promenFront', [
			'assets'     => trailingslashit( get_theme_file_uri( 'assets' ) ),
			'catalogUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			'projects'   => $projects,
		] );
	}

	// Производство: страничные стили/скрипты.
	if ( is_page( 'production' ) ) {
		wp_enqueue_style( 'promen-production', get_theme_file_uri( 'assets/css/production.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-production', get_theme_file_uri( 'assets/js/production.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}

	// Проекты: список и 5 детальных страниц.
	$promen_project_slugs = [ 'kurskaya-aes', 'aes-akkuyu', 'aes-ruppur', 'cherepetskaya-gres', 'teploelektrocentral-tec-3' ];
	if ( is_page( 'proekty' ) ) {
		wp_enqueue_style( 'promen-proekty', get_theme_file_uri( 'assets/css/proekty.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}
	if ( is_page( $promen_project_slugs ) ) {
		wp_enqueue_style( 'promen-proekt', get_theme_file_uri( 'assets/css/proekt.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}

	// Статьи: список (JS-рендер карточек) и 6 детальных.
	if ( is_page( 'stati' ) ) {
		wp_enqueue_style( 'promen-stati', get_theme_file_uri( 'assets/css/stati.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-stati', get_theme_file_uri( 'assets/js/stati.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
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
		wp_enqueue_style( 'promen-statya', get_theme_file_uri( 'assets/css/statya.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-statya', get_theme_file_uri( 'assets/js/statya.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}

	// Нормативная база: реестр ГОСТ/ОСТ/СТО/ТУ (JS-рендер).
	if ( is_page( 'normativnaya-baza' ) && ! is_page( $promen_article_paths ) ) {
		wp_enqueue_style( 'promen-nb', get_theme_file_uri( 'assets/css/nb.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-nb', get_theme_file_uri( 'assets/js/nb.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-projects', get_theme_file_uri( 'assets/js/projects.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
		wp_localize_script( 'promen-nb', 'promenNB', [
			'catalogUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ),
			// Макетные короткие категории реестра → группы каталога.
			'groups'     => [
				'sdt' => 'sdt',
				'op'  => 'opory',
				'zra' => 'armatura',
				'tr'  => 'truby',
				'iz'  => 'izolyatsiya',
				'td'  => 'tochenye',
			],
		] );
	}

	// Контакты / 404 / политика ПДн: страничные стили и скрипты.
	if ( is_page( 'contacts' ) ) {
		wp_enqueue_style( 'promen-contacts', get_theme_file_uri( 'assets/css/contacts.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-footer-pin', get_theme_file_uri( 'assets/js/footer-pin.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}
	if ( is_404() ) {
		wp_enqueue_style( 'promen-404', get_theme_file_uri( 'assets/css/page-404.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-404', get_theme_file_uri( 'assets/js/page-404.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}
	if ( is_page( 'privacy-policy' ) ) {
		wp_enqueue_style( 'promen-privacy', get_theme_file_uri( 'assets/css/privacy.css' ), [ 'promen-base' ], PROMEN_VERSION );
		wp_enqueue_script( 'promen-privacy', get_theme_file_uri( 'assets/js/privacy.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	}

	// Хром сайта: часы, бургер/drawer + модалка запроса — на всех страницах.
	wp_enqueue_script( 'promen-chrome', get_theme_file_uri( 'assets/js/chrome.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	wp_enqueue_script( 'promen-request-modal', get_theme_file_uri( 'assets/js/request-modal.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
	wp_localize_script( 'promen-request-modal', 'promenRM', [
		'ajaxUrl'    => admin_url( 'admin-post.php' ),
		'nonce'      => wp_create_nonce( 'promen_request' ),
		'privacyUrl' => promen_privacy_url(),
		'email'      => 'zakaz@prom-en.com',
	] );

	// Живой реестр: корень каталога и страницы категорий (встроенный partial).
	if ( $is_registry || $is_cat_page ) {
		wp_enqueue_script( 'promen-catalog', get_theme_file_uri( 'assets/js/catalog.js' ), [], PROMEN_VERSION, [ 'in_footer' => true ] );
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
		'stati'              => 'Статьи',
		'contacts'           => 'Контакты',
	];
	foreach ( $pages as $slug => $label ) {
		$page = promen_page( $slug );
		if ( $page ) {
			$items[] = [
				'label'  => $label,
				'url'    => get_permalink( $page ),
				'active' => is_page( $page->ID ),
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
 * ГОСТ 17375 (как в макете product-otvod-90.html); fallback — реестр отводов.
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
		's'              => 'Отвод 90° ГОСТ 17375',
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
