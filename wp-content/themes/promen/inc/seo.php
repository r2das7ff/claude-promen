<?php
/**
 * SEO: title/description шаблоны и sitemap для taxonomy norm.
 * Без Yoast — лёгкий слой поверх title-tag + wp_sitemaps.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Заголовок карточки для <title>: к имени из базы добавляем PN, если его там нет.
 *
 * Обозначение по ГОСТу давление не несёт, поэтому фланцы одного DN получали
 * одинаковые названия: «Фланец 80-11-1-B-IV ГОСТ 33259-2015» — двенадцать
 * штук на PN 25, 40, 200 и так далее. Обход 2026-08-25 нашёл 298 таких групп
 * на 1 023 страницы. Данные при этом верные: sku и H1 у них различаются,
 * страдал только <title>, а по нему поисковик и склеивает дубли.
 *
 * PN форматируем как promen_size_label() в H1 — иначе заголовок и H1 разъедутся.
 */
function promen_product_title_seo( int $product_id ): string {
	$title = (string) get_the_title( $product_id );
	if ( preg_match( '/\bPN\b/iu', $title ) ) {
		return $title; // давление уже в имени
	}
	$dims = function_exists( 'promen_get_dims' ) ? promen_get_dims( $product_id ) : [];
	$pn   = trim( (string) ( $dims['pn'] ?? '' ) );
	if ( '' === $pn ) {
		return $title;
	}
	$label = 'PN' . promen_fmt_dim( $pn );

	// Ставим перед нормативом: «Фланец 80-11-1-B-IV PN40 ГОСТ 33259-2015»
	// читается лучше, чем давление в самом хвосте.
	$norm = (string) get_post_meta( $product_id, '_promen_norm_key', true );
	if ( '' !== $norm && false !== mb_strpos( $title, $norm ) ) {
		return str_replace( $norm, $label . ' ' . $norm, $title );
	}
	return $title . ' ' . $label;
}

/** Document title по типу страницы. */
add_filter( 'document_title_parts', function ( array $parts ): array {
	$brand = 'Промышленная Энергетика';

	if ( function_exists( 'is_product' ) && is_product() ) {
		$parts['title'] = promen_product_title_seo( get_queried_object_id() );
		$parts['site']  = $brand;
		return $parts;
	}

	if ( is_tax( 'product_cat' ) || is_tax( 'norm' ) ) {
		$term = get_queried_object();
		$parts['title'] = $term->name;
		$parts['site']  = 'Каталог · ' . $brand;
		return $parts;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$parts['title'] = 'Каталог продукции';
		$parts['site']  = $brand;
	}

	return $parts;
} );

/*
 * Meta description раньше выводился и здесь, и в promen_meta_description()
 * из functions.php — два независимых хука wp_head, то есть тег на каждой
 * категории и карточке печатался дважды (проверено 2026-08-25, 8 из 8
 * страниц каждого типа). Логика этого хука перенесена в functions.php,
 * он остался единственным источником тега.
 */

/** Номер страницы пагинации: 1, если её нет. */
function promen_paged_number(): int {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

/*
 * Пагинация: номер страницы в title.
 *
 * Без него все 109 страниц раздела «Отводы» несут один заголовок. Обход
 * 2026-08-25 насчитал 288 групп дублей description — почти все из пагинации.
 * Приоритет 30: позже фильтра серий (20), иначе номер затрётся.
 */
add_filter( 'document_title_parts', function ( array $parts ): array {
	$paged = promen_paged_number();
	if ( $paged > 1 && ! empty( $parts['title'] ) ) {
		$parts['title'] .= ' — страница ' . $paged;
		// Ядро кладёт свой «Page N» в отдельную часть — иначе номер задваивается,
		// да ещё и по-английски: строки перевода на этой установке не подхватываются.
		unset( $parts['page'] );
	}
	return $parts;
}, 30 );

/*
 * Защита от soft-404.
 *
 * Адреса вида /catalog/<раздел>/seriya/<что-угодно>/ не совпадают ни с одним
 * правилом, и WordPress молча отдаёт блог-главную с кодом 200. Обход
 * 2026-08-25 нашёл 44 такие страницы: для поисковика это 44 дубля главной,
 * а Яндекс за такое ставит диагноз SOFT_404. Ссылки на них ведут из «хлебных
 * крошек» карточек — там строится URL серии, которой может не существовать.
 *
 * Правило простое: если запрос разрешился в главную, а путь при этом не
 * корень сайта — это промах маршрутизации, отдаём честную 404.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || is_feed() || is_robots() || is_404() || ! is_home() ) {
		return;
	}
	// XML-карта тоже разрешается в главную, пока её не перехватил рендерер
	// ядра, — без этой проверки /wp-sitemap*.xml начинает отдавать 404.
	if ( get_query_var( 'sitemap' ) || get_query_var( 'sitemap-stylesheet' ) ) {
		return;
	}
	$path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		return; // настоящая главная
	}
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	$tpl = get_query_template( '404' );
	if ( $tpl ) {
		include $tpl;
	}
	exit;
} );

/*
 * ВРЕМЕННО: подтверждение прав на тестовый домен в Яндекс.Вебмастере.
 * Нужно, чтобы увидеть, попал ли стенд в индекс — если попал, перед
 * переездом его закрываем, иначе после переключения домена получим
 * два одинаковых сайта и склейку не в ту сторону.
 * Снять вместе с самим доменом после переезда.
 */
add_action( 'wp_head', function () {
	if ( 'prom-en.forgotaboutdre.ru' !== ( $_SERVER['HTTP_HOST'] ?? '' ) ) {
		return;
	}
	echo '<meta name="yandex-verification" content="fadaca41a33d7a26" />' . "\n";
}, 1 );

/**
 * robots.txt.
 *
 * Clean-param — яндексовская директива, и для нас она важнее canonical:
 * Яндекс по ней сразу склеивает параметрические копии, не тратя на них обход.
 * Метрика показала, что на старом сайте ходят с `?utm_referrer=` — такие
 * метки создают дубль каждого раздела, поэтому список меток закрываем целиком.
 */
add_filter( 'robots_txt', function ( string $output, $public ): string {
	if ( ! $public ) {
		return $output; // сайт закрыт настройкой — не переопределяем
	}
	// Своя группа User-agent: ядро дописывает Sitemap: в конец, и правила
	// после него оказались бы вне группы — Clean-param Яндекс тогда не прочтёт.
	$extra = [
		'',
		'User-agent: *',
		'Disallow: /wp-json/',
		'Disallow: /xmlrpc.php',
		'Disallow: */feed/',
		'Disallow: /comments/feed/',
		'Disallow: /?s=',
		'Disallow: /search/',
		'Disallow: /cart/',
		'Disallow: /checkout/',
		'Disallow: /my-account/',
		'',
		'Clean-param: utm_source&utm_medium&utm_campaign&utm_term&utm_content&utm_referrer',
		'Clean-param: yclid&gclid&ymclid&from&openstat&_openstat&roistat&fbclid',
		'Clean-param: add-to-cart&orderby',
		'',
	];
	return $output . implode( "\n", $extra ) . "\n";
}, 10, 2 );

/**
 * Служебные страницы WooCommerce — вон из индекса.
 *
 * Обход 2026-08-25 нашёл /sample-page/, /cart/ и /my-account/ в sitemap,
 * а /feed/, /wp-json/ и /comments/feed/ открытыми для индексации. Для
 * каталога без корзины это чистый мусор в выдаче.
 */
function promen_is_service_page(): bool {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return true;
	}
	return is_page( [ 'sample-page', 'cart', 'checkout', 'my-account' ] ) || is_search();
}

add_action( 'wp_head', function () {
	if ( promen_is_service_page() ) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}, 1 );

/** Те же страницы не должны попадать в карту сайта. */
add_filter( 'wp_sitemaps_posts_query_args', function ( array $args, string $post_type ): array {
	if ( 'page' !== $post_type ) {
		return $args;
	}
	$exclude = [];
	foreach ( [ 'sample-page', 'cart', 'checkout', 'my-account', 'privacy-policy' ] as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$exclude[] = $page->ID;
		}
	}
	foreach ( [ 'cart', 'checkout', 'myaccount' ] as $wc ) {
		$id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( $wc ) : 0;
		if ( $id > 0 ) {
			$exclude[] = $id;
		}
	}
	if ( $exclude ) {
		$args['post__not_in'] = array_unique( array_merge( $args['post__not_in'] ?? [], $exclude ) );
	}
	return $args;
}, 10, 2 );

/**
 * Заголовки безопасности.
 *
 * На стенде не отдавался ни один: ни HSTS, ни X-Content-Type-Options.
 * Ставим из PHP, а не из конфига веб-сервера — на шаред-хостинге доступа
 * к нему нет, а переезд на боевой домен настройки бы не унёс.
 * CSP не трогаем: у темы инлайновые стили и скрипты, политику надо
 * подбирать отдельно, иначе положим вёрстку.
 */
add_action( 'send_headers', function () {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
} );

/**
 * IndexNow: ключ подтверждения.
 *
 * Протокол поддерживают Яндекс и Bing: после правок можно не ждать обхода,
 * а сообщить об изменившихся адресах — на каталоге в 15 тысяч страниц это
 * заметно быстрее. Для подтверждения владения по адресу /<ключ>.txt должен
 * лежать сам ключ. Отдаём его из PHP: класть файл в docroot на шаред-хостинге
 * неудобно, а при переезде домена он бы туда не уехал.
 *
 * Ключ не секрет — он публичен по устройству протокола.
 * Отправка адресов делается отдельно: scripts/seo/yandex.py indexnow.
 */
const PROMEN_INDEXNOW_KEY = 'fd3fb7d5c88be5d7a2307f99298b6e60';

add_action( 'template_redirect', function () {
	$path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' );
	if ( $path !== PROMEN_INDEXNOW_KEY . '.txt' ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=UTF-8' );
	status_header( 200 );
	echo PROMEN_INDEXNOW_KEY;
	exit;
}, 0 );

/**
 * Organization — единая карточка компании на всех страницах.
 *
 * Аудит разметки 2026-08-26: Product и BreadcrumbList на карточках были,
 * а Organization не было нигде. Для завода это главный пробел — именно по
 * ней поисковик связывает сайт, юрлицо, адрес и телефоны в одну сущность.
 * Данные — со страницы «Контакты», выдумывать тут нечего.
 */
add_action( 'wp_head', function () {
	if ( is_404() ) {
		return;
	}
	$org = [
		'@context'      => 'https://schema.org',
		'@type'         => 'Organization',
		'@id'           => home_url( '/#organization' ),
		'name'          => 'ООО «Завод Промышленная Энергетика»',
		'alternateName' => 'PROM-EN',
		'url'           => home_url( '/' ),
		'logo'          => get_theme_file_uri( 'assets/img/PE_logo_color.png' ),
		'email'         => 'zakaz@prom-en.com',
		'telephone'     => '+7 (351) 217-00-99',
		'address'       => [
			'@type'           => 'PostalAddress',
			'postalCode'      => '454091',
			'addressLocality' => 'Челябинск',
			'streetAddress'   => 'ул. Орджоникидзе, 37',
			'addressCountry'  => 'RU',
		],
		'contactPoint'  => [
			[
				'@type'             => 'ContactPoint',
				'contactType'       => 'sales',
				'telephone'         => '+7 (351) 217-00-99',
				'email'             => 'zakaz@prom-en.com',
				'areaServed'        => 'RU',
				'availableLanguage' => 'Russian',
			],
		],
		// geo — свойство места, а не организации, поэтому через location.
		'location'      => [
			'@type'   => 'Place',
			'name'    => 'Производственная площадка',
			'address' => [
				'@type'           => 'PostalAddress',
				'postalCode'      => '454091',
				'addressLocality' => 'Челябинск',
				'streetAddress'   => 'ул. Орджоникидзе, 37',
				'addressCountry'  => 'RU',
			],
			'geo'     => [
				'@type'     => 'GeoCoordinates',
				'latitude'  => 55.1644,
				'longitude' => 61.4368,
			],
		],
	];
	echo '<script type="application/ld+json">'
		. wp_json_encode( $org, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		. '</script>' . "\n";
}, 5 );

/**
 * Article на страницах «Статей».
 *
 * Их четыре, и разметки у них не было вовсе. Автор — организация: статьи
 * пишутся от лица завода, выдумывать физлицо неправильно.
 */
add_action( 'wp_head', function () {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || ! $post->post_parent ) {
		return;
	}
	if ( 'stati' !== get_post_field( 'post_name', $post->post_parent ) ) {
		return;
	}
	$data = [
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'headline'         => get_the_title( $post ),
		'mainEntityOfPage' => get_permalink( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'inLanguage'       => 'ru-RU',
		'author'           => [ '@id' => home_url( '/#organization' ) ],
		'publisher'        => [ '@id' => home_url( '/#organization' ) ],
	];
	$img = get_the_post_thumbnail_url( $post, 'full' );
	if ( $img ) {
		$data['image'] = $img;
	}
	echo '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		. '</script>' . "\n";
}, 6 );

/** Включаем norm в core XML sitemap. */
add_filter( 'wp_sitemaps_taxonomies', function ( array $taxonomies ): array {
	$taxonomies['norm'] = get_taxonomy( 'norm' );
	return $taxonomies;
} );

/** Параметрические URL каталога не должны попадать в sitemap (их и нет — core sitemap чистый). */
add_filter( 'wp_sitemaps_posts_entry', function ( $entry, $post ) {
	if ( $post->post_type === 'product' ) {
		$entry['loc'] = get_permalink( $post );
	}
	return $entry;
}, 10, 2 );

/**
 * 301 со схлопнутых при дедупе позиций на выжившего.
 * Карта promen_dedup_redirects: старый post_name → ID выжившего.
 */
add_action( 'template_redirect', function (): void {
	if ( ! is_404() ) {
		return;
	}
	$map = get_option( 'promen_dedup_redirects', [] );
	if ( ! is_array( $map ) || ! $map ) {
		return;
	}
	$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$path = trim( $path, '/' );
	if ( $path === '' ) {
		return;
	}
	$seg = ( $pos = strrpos( $path, '/' ) ) !== false ? substr( $path, $pos + 1 ) : $path;
	$seg = sanitize_title( rawurldecode( $seg ) );
	if ( $seg === '' || empty( $map[ $seg ] ) ) {
		return;
	}
	$url = get_permalink( (int) $map[ $seg ] );
	if ( $url ) {
		wp_safe_redirect( $url, 301 );
		exit;
	}
}, 1 );
