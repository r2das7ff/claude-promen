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

/**
 * Что за изделия лежат под нормативом: «— тройники DN 15–200».
 *
 * Заголовок «ГОСТ 9064-1970 — Каталог» не говорит ни человеку, ни поисковику,
 * о чём документ. А это ровно те запросы, по которым старый сайт получает
 * показы без переходов: «сто 95 113-2013» — 49 показов и ноль кликов за
 * 28 дней. Человек ищет норматив, чтобы понять, какие детали по нему делают,
 * и какой у них диапазон размеров.
 *
 * Считаем по товарам норматива и кладём в транзиент: на 127 страницах
 * пересчитывать на каждом запросе незачем.
 */
function promen_norm_summary( WP_Term $term ): string {
	$key    = 'promen_normsum_' . $term->term_id;
	$cached = get_transient( $key );
	if ( false !== $cached ) {
		return (string) $cached;
	}

	// Берём весь норматив, а не первые N: при ограничении выборки диапазон
	// врал — у ГОСТ 17375-2001 выходило «DN 400–800» вместо полного ряда.
	$q = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => [ [ 'taxonomy' => 'norm', 'field' => 'term_id', 'terms' => $term->term_id ] ],
	] );

	$kinds     = [];
	$sizes     = [];
	$fasteners = 0;
	foreach ( $q->posts as $pid ) {
		$cat = promen_deepest_cat( $pid );
		if ( $cat ) {
			$kinds[ $cat->name ] = ( $kinds[ $cat->name ] ?? 0 ) + 1;
		}
		$dims = promen_get_dims( $pid );
		// У крепежа размер — резьба М, а не условный проход: «DN 14» у болта
		// это неверный термин, инженер такое читает как ошибку.
		if ( promen_product_is_fastener( $pid ) ) {
			$fasteners++;
			$thread = promen_fastener_dims( $dims )['thread'];
			if ( '' !== $thread && is_numeric( $thread ) ) {
				$sizes[] = (float) $thread;
			}
		} elseif ( isset( $dims['dn'] ) && is_numeric( $dims['dn'] ) ) {
			$sizes[] = (float) $dims['dn'];
		}
	}
	arsort( $kinds );

	$out = '';
	if ( $kinds ) {
		$name = (string) array_key_first( $kinds );
		$out .= ' — ' . ( function_exists( 'mb_strtolower' ) ? mb_strtolower( $name ) : strtolower( $name ) );
	}
	if ( $sizes ) {
		$prefix = ( $fasteners > count( $q->posts ) / 2 ) ? 'M' : 'DN ';
		$min = promen_fmt_dim( (string) min( $sizes ) );
		$max = promen_fmt_dim( (string) max( $sizes ) );
		$out .= ' ' . $prefix . ( $min === $max ? $min : $min . '–' . $max );
	}

	set_transient( $key, $out, WEEK_IN_SECONDS );
	return $out;
}

/**
 * H1 архива каталога: две части — обычная и акцентная (её шаблон рисует в <em>).
 *
 * Шаблон archive-product.php обслуживает три вида страниц: витрину, категории
 * и нормативы. H1 в нём был зашит строкой «Каталог / продукции», и его получали
 * все три: обход 2026-08-28 нашёл этот заголовок на 15 подкатегориях (те, у
 * которых нет своей страницы, `has_page => false`) и на всех 127 страницах
 * `/normativy/*`. Title у них при этом правильный — расходились только H1.
 *
 * Для подкатегорий текст берём из `h1` в promen_catalog_taxonomy_defs():
 * имя термина само по себе не запрос — «Скользящие» и «Тип 01» человек не ищет,
 * он ищет «опоры скользящие» и «фланцы тип 01».
 */
function promen_archive_h1(): array {
	if ( is_tax( 'norm' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			// promen_norm_summary() отдаёт « — отводы DN 20–800»; в акцент кладём
			// только сам хвост, тире тут разделяет строки вёрстки, а не текст.
			$sum = trim( promen_norm_summary( $term ), " \t\n\r\0\x0B—-" );
			return [ $term->name, $sum ];
		}
	}

	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$defs = function_exists( 'promen_catalog_taxonomy_defs' ) ? promen_catalog_taxonomy_defs() : [];
			$h1   = (string) ( $defs[ $term->slug ]['h1'] ?? '' );
			if ( '' !== $h1 ) {
				$parts = explode( '|', $h1, 2 );
				return [ trim( $parts[0] ), trim( $parts[1] ?? '' ) ];
			}
			return [ $term->name, '' ];
		}
	}

	return [ 'Каталог', 'продукции' ];
}

/** Document title по типу страницы. */
add_filter( 'document_title_parts', function ( array $parts ): array {
	$brand = 'Промышленная Энергетика';

	/*
	 * Главная. Тайтл собирался из blogname и был чистым названием компании —
	 * «PROM-EN — Промышленная Энергетика», 32 символа из ~65 показываемых
	 * Яндексом, без предмета и без региона.
	 *
	 * Данные Вебмастера показывают, что спрос не брендовый: самый частый
	 * запрос домена — «168×5» (типоразмер трубы, 33 показа), дальше
	 * «сто 95 113-2013» и «отводы цпп». Брендовые запросы дают втрое меньше
	 * показов И УЖЕ выиграны — «промышленная энергетика челябинск» стоит на
	 * позиции 1.2. То есть прежний тайтл тратил самую сильную страницу
	 * домена на задачу, которая решена.
	 *
	 * blogname не трогаем: он уходит в Organization-разметку, подвал и RSS,
	 * там нужно именно название организации.
	 */
	if ( is_front_page() ) {
		$parts['title'] = 'Детали трубопроводов для АЭС и ТЭС';
		$parts['site']  = 'Завод «' . $brand . '»';
		unset( $parts['tagline'] );
		return $parts;
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		// Хвост сокращён до бренда латиницей: «— Промышленная Энергетика» это
		// 25 символов из 60 доступных, и у карточек под нож уходил норматив —
		// самая ценная часть заголовка. Обход 2026-08-28: 312 тайтлов из 393
		// длиннее 65 символов. На страницах вне каталога хвост прежний.
		$parts['title'] = promen_product_title_seo( get_queried_object_id() );
		$parts['site']  = 'PROM-EN';
		return $parts;
	}

	if ( is_tax( 'norm' ) ) {
		$term = get_queried_object();
		$parts['title'] = $term->name . promen_norm_summary( $term );
		$parts['site']  = 'PROM-EN';
		return $parts;
	}

	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		// Имя термина само по себе не запрос: «Скользящие», «Тип 01» и
		// «Бесшовные» не содержат предмета, а ищут «опоры скользящие» и
		// «фланцы тип 01». У подкатегорий без своей страницы берём тот же
		// текст, что ушёл в H1.
		$defs = function_exists( 'promen_catalog_taxonomy_defs' ) ? promen_catalog_taxonomy_defs() : [];
		$def  = $defs[ $term->slug ] ?? [];
		// seo_title — у разделов со своей страницей (там H1 нарисован в
		// шаблоне), h1 — у подкатегорий, которым его собирает archive-product.
		$title = (string) ( $def['seo_title'] ?? '' );
		if ( '' === $title && ! empty( $def['h1'] ) ) {
			$title = trim( str_replace( '|', ' ', (string) $def['h1'] ) );
		}
		if ( '' === $title ) {
			$title = $term->name;
		}

		$parts['title'] = $title;
		$parts['site']  = 'PROM-EN';
		return $parts;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$parts['title'] = 'Каталог продукции';
		$parts['site']  = $brand;
		return $parts;
	}

	// Название сайта — «PROM-EN — Промышленная Энергетика», и на внутренних
	// страницах оно даёт хвост в 35 символов: «Контроль качества СДТ — от
	// входного контроля до паспорта изделия – PROM-EN — Промышленная
	// Энергетика» это 100 символов, из которых в выдаче видно 60. Оставляем
	// короткий бренд; полное имя остаётся на главной, где оно и уместно.
	if ( is_singular() && ! is_front_page() ) {
		$parts['site'] = 'PROM-EN';
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
 * XML-карта сайта отдавала 404 вместе с валидным телом.
 *
 * WP::handle_404() выполняется до рендерера карты. Адрес чанка — это
 * ?sitemap=posts&sitemap-subtype=product&paged=N: для ядра это главная с
 * пагинацией, а блог-записей на сайте нет, поэтому любая страница со второй
 * и дальше резолвится в пустой запрос и получает 404. Рендерер карты
 * (WP_Sitemaps::render_sitemaps) статус обратно не поднимает — он умеет
 * только выставить 404 сам, когда список URL пуст.
 *
 * Итог до правки: из восьми чанков товаров индексировался ровно один —
 * wp-sitemap-posts-product-2..8.xml отдавали 404 при 13 407 товарах внутри.
 *
 * Снимаем обработку 404 на маршрутах карты штатным фильтром ядра. Честную
 * 404 на несуществующем чанке (product-9) ядро всё равно поставит само —
 * уже после того, как убедится, что список URL пуст.
 */
add_filter( 'pre_handle_404', function ( $preempt, $wp_query ) {
	if ( $wp_query->get( 'sitemap' ) || $wp_query->get( 'sitemap-stylesheet' ) ) {
		return true;
	}
	return $preempt;
}, 10, 2 );

/*
 * Фавикон.
 *
 * site_icon в базе не задан, файла в корне не было, тема иконку не
 * объявляла — и WordPress в таком случае отдаёт на /favicon.ico редирект
 * на СВОЙ логотип. Во вкладке у завода висела эмблема WordPress.
 *
 * Иконка нарезана из фирменного знака (PE_logo_white.png, левый глиф без
 * текстовой части) и положена на --dark #0F2A44: белый знак на прозрачном
 * фоне пропадал бы во вкладке тёмной темы, а тёмный — в светлой.
 *
 * do_faviconico перехватывает штатный редирект ядра: физический файл в
 * корне сайта отдаётся веб-сервером напрямую и в git не попадает, поэтому
 * на чистом развёртывании иконку должна давать именно тема.
 */
function promen_favicon_uri( string $f ): string {
	return get_theme_file_uri( 'assets/img/favicon/' . $f );
}

add_action( 'wp_head', function () {
	printf( '<link rel="icon" href="%s" sizes="32x32">' . "
", esc_url( promen_favicon_uri( 'icon-32.png' ) ) );
	printf( '<link rel="icon" href="%s" sizes="192x192">' . "
", esc_url( promen_favicon_uri( 'icon-192.png' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "
", esc_url( promen_favicon_uri( 'icon-180.png' ) ) );
	printf( '<meta name="theme-color" content="#0F2A44">' . "
" );
}, 2 );

add_action( 'do_faviconico', function () {
	wp_redirect( promen_favicon_uri( 'favicon.ico' ), 301 );
	exit;
} );

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
		// Любой адрес с параметрами — мимо обхода.
		//
		// Лог доступа 2026-08-26 показал, зачем: GPTBot и безымянный бот
		// перебирали сочетания фасетов на /normativy/…/page/16/?gost=<40
		// нормативов через запятую>&steel=…, адресами по две тысячи символов.
		// Пространство таких URL бесконечно, содержимого своего у них нет,
		// а noindex роботу скачивать не мешает — он мешает только индексировать.
		// Вдобавок параметрические запросы идут мимо полностраничного кеша,
		// то есть каждый такой обход — полная генерация страницы. Похоже,
		// отсюда и два ответа 500 при нашем собственном обходе каталога.
		'Disallow: /*?',
		'',
		'Clean-param: utm_source&utm_medium&utm_campaign&utm_term&utm_content&utm_referrer',
		'Clean-param: yclid&gclid&ymclid&from&openstat&_openstat&roistat&fbclid',
		'Clean-param: add-to-cart&orderby',
		// Фасеты каталога: для Яндекса этого мало (см. Disallow выше), но
		// директива говорит ему склеивать такие адреса с чистыми, а не
		// считать отдельными страницами.
		'Clean-param: gost&steel&dn&pn&s&angle&industry&group&q&paged',
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
	// Архивы автора и рубрик — наследие WordPress: на сайте нет ни блога, ни
	// авторов, а /author/admin/ и /category/uncategorized/ отвечают 200 и
	// светят логин администратора. Обход 2026-08-26 нашёл их единственными
	// страницами без canonical.
	if ( is_author() || is_category() || is_tag() || is_date() ) {
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
 * Картинка страницы для Open Graph и микроразметки.
 *
 * У карточки — фото изделия, у остальных страниц — первое фото из контента,
 * иначе общий снимок производства. Логотип в og:image не ставим: в превью
 * мессенджера он выглядит пустой плашкой.
 */
function promen_og_image(): string {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$photo = promen_product_photo_url( get_queried_object_id() );
		if ( '' !== $photo ) {
			return $photo;
		}
	}
	$post = get_queried_object();
	if ( $post instanceof WP_Post ) {
		$thumb = get_the_post_thumbnail_url( $post, 'full' );
		if ( $thumb ) {
			return $thumb;
		}
		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $post->post_content, $m )
			&& false === stripos( $m[1], 'logo' ) ) {
			return $m[1];
		}
	}
	return get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.jpg' );
}

/**
 * Open Graph и Twitter Card.
 *
 * Обход 2026-08-25: ни одного og-тега на 16 390 страницах. Для B2B это
 * заметно — менеджеры кидают ссылки на позиции в мессенджеры, а превью
 * выходит пустым. Заголовок и описание берём те же, что в <title> и
 * meta description, чтобы тексты не разъезжались.
 */
add_action( 'wp_head', function () {
	if ( is_404() ) {
		return;
	}
	// wp_get_document_title() отдаёт заголовок с HTML-сущностями, а esc_attr
	// кодирует их повторно — в превью уезжало «&#8211;» вместо тире.
	$title = html_entity_decode( wp_get_document_title(), ENT_QUOTES, 'UTF-8' );
	$desc  = function_exists( 'promen_meta_description_text' ) ? promen_meta_description_text() : '';
	$type  = ( function_exists( 'is_product' ) && is_product() ) ? 'product' : ( is_singular() ? 'article' : 'website' );
	$url   = home_url( add_query_arg( [] ) );

	$tags = [
		'og:site_name'   => 'PROM-EN — Промышленная Энергетика',
		'og:locale'      => 'ru_RU',
		'og:type'        => $type,
		'og:title'       => $title,
		'og:url'         => $url,
		'og:image'       => promen_og_image(),
	];
	if ( '' !== $desc ) {
		$tags['og:description'] = $desc;
	}
	foreach ( $tags as $k => $v ) {
		printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $k ), esc_attr( $v ) );
	}
	// Twitter читает свои теги, но подхватывает og:* — дублируем только карту.
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 3 );

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
		// Реквизиты из карты предприятия. Это данные ЕГРЮЛ — они и так
		// открыты, а в разметке помогают поисковику связать сайт с юрлицом.
		'legalName'     => 'Общество с ограниченной ответственностью Завод «Промышленная Энергетика»',
		'taxID'         => '7453307956',
		'vatID'         => '745101001',
		'identifier'    => [
			[ '@type' => 'PropertyValue', 'name' => 'ОГРН', 'value' => '1177456024833' ],
			[ '@type' => 'PropertyValue', 'name' => 'ОКПО', 'value' => '13842829' ],
		],
		'foundingDate'  => '2017-04-05',
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
		// Автор — то же, что стоит подписью в шапке статьи: отдел, а не
		// абстрактная организация. Издатель остаётся заводом.
		'author'           => [
			'@type'          => 'Organization',
			'name'           => 'Инженерный отдел «Промышленная Энергетика»',
			'parentOrganization' => [ '@id' => home_url( '/#organization' ) ],
		],
		'publisher'        => [ '@id' => home_url( '/#organization' ) ],
	];
	$data['image'] = promen_og_image();
	echo '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		. '</script>' . "\n";
}, 6 );

/**
 * /llms.txt — карта сайта для языковых моделей.
 *
 * Соглашение молодое и поисковой выдачи не даёт, но для B2B оно уместно:
 * снабженец всё чаще спрашивает про деталь у ассистента, а не в поиске.
 * Собираем из живых данных, чтобы файл не разошёлся с каталогом.
 */
add_action( 'template_redirect', function () {
	$path = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' );
	if ( 'llms.txt' !== $path ) {
		return;
	}
	$out   = [];
	$out[] = '# PROM-EN — ООО «Завод Промышленная Энергетика»';
	$out[] = '';
	$out[] = '> Завод в Челябинске: детали и сборочные единицы трубопроводов для объектов';
	$out[] = '> атомной и тепловой энергетики. Изготовление по ГОСТ, ОСТ, СТО ЦКТИ, ТУ и';
	$out[] = '> чертежам заказчика. Каталог без цен: коммерческое предложение по запросу.';
	$out[] = '';
	$out[] = '## Каталог';
	$out[] = '';
	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'orderby'    => 'count',
		'order'      => 'DESC',
	] );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$link = get_term_link( $t );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$desc = trim( wp_strip_all_tags( (string) $t->description ) );
			$desc = $desc ? ': ' . wp_html_excerpt( $desc, 120, '…' ) : '';
			$out[] = sprintf( '- [%s](%s)%s — позиций: %d', $t->name, $link, $desc, (int) $t->count );
		}
	}
	$out[] = '';
	$out[] = '## Справочное';
	$out[] = '';
	foreach ( [ 'normativnaya-baza' => 'Нормативная база: реестр ГОСТ, ОСТ, СТО и ТУ',
	            'kalkulyatory'      => 'Калькуляторы подбора и расчёта массы',
	            'stati'             => 'Статьи: выбор стали, контроль качества, изготовление по КД' ] as $slug => $title ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$out[] = sprintf( '- [%s](%s)', $title, get_permalink( $page ) );
		}
	}
	$out[] = '';
	$out[] = '## Компания';
	$out[] = '';
	foreach ( [ 'production' => 'Производство и контроль качества',
	            'proekty'    => 'Реализованные поставки на объекты энергетики',
	            'contacts'   => 'Контакты: 454091, Челябинск, ул. Орджоникидзе, 37' ] as $slug => $title ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$out[] = sprintf( '- [%s](%s)', $title, get_permalink( $page ) );
		}
	}
	$out[] = '';
	$out[] = 'Полный перечень адресов: ' . home_url( '/wp-sitemap.xml' );
	$out[] = '';

	header( 'Content-Type: text/plain; charset=UTF-8' );
	status_header( 200 );
	echo implode( "\n", $out );
	exit;
}, 0 );

/**
 * FAQPage по блокам «Частые вопросы».
 *
 * Расширенных результатов по FAQ Google не даёт с мая 2026, но разметка
 * по-прежнему помогает ИИ-ответам разбирать содержимое и связывать сущности,
 * а для B2B это уже заметный канал: снабженец спрашивает деталь у ассистента.
 *
 * Вопросы лежат статической разметкой в шаблонах, поэтому читаем их из файла
 * и кэшируем. Ставим разметку только там, где FAQ — собственное содержимое
 * страницы: на /catalog/ и на трёх категориях со своими блоками. На 127
 * страницах нормативов показывается тот же общий FAQ каталога — дублировать
 * его разметкой значит плодить одинаковые FAQPage, чего делать нельзя.
 *
 * @param string $file Путь к шаблону с блоком .faq-wrap.
 */
function promen_faq_schema( string $file ): void {
	if ( ! is_readable( $file ) ) {
		return;
	}
	$key  = 'promen_faq_' . md5( $file . (string) @filemtime( $file ) );
	$json = get_transient( $key );

	if ( false === $json ) {
		$html = (string) file_get_contents( $file );
		preg_match_all(
			'#<span class="fq-t">(.*?)</span>.*?<div class="fq-a-in">(.*?)</div>#s',
			$html, $m, PREG_SET_ORDER
		);
		$items = [];
		foreach ( $m as $pair ) {
			$q = trim( html_entity_decode( wp_strip_all_tags( $pair[1] ), ENT_QUOTES, 'UTF-8' ) );
			$a = trim( html_entity_decode( wp_strip_all_tags( $pair[2] ), ENT_QUOTES, 'UTF-8' ) );
			$a = preg_replace( '/\s+/u', ' ', $a );
			if ( '' === $q || '' === $a ) {
				continue;
			}
			$items[] = [
				'@type'          => 'Question',
				'name'           => $q,
				'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $a ],
			];
		}
		$json = $items
			? wp_json_encode(
				[ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items ],
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
			: '';
		set_transient( $key, $json, WEEK_IN_SECONDS );
	}

	if ( $json ) {
		echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
	}
}

/**
 * ` width="…" height="…"` для картинки темы по её URL.
 *
 * Атрибуты нужны браузеру, чтобы зарезервировать место по соотношению сторон:
 * без них половина изображений сайта (427 из 835 при обходе 2026-08-28)
 * вносила вклад в CLS. Вёрстку задаёт CSS, атрибуты на неё не влияют.
 *
 * Там, где путь известен в шаблоне, размеры проставлены прямо в разметке.
 * Эта функция — для мест, где src собирается на лету: фото изделия, карточки
 * проектов, портреты менеджеров. Размер читаем с диска и кладём в транзиент:
 * getimagesize() на каждый запрос карточки — лишний stat на 15 394 страницах.
 */
function promen_img_size_attr( string $url ): string {
	if ( '' === $url ) {
		return '';
	}
	$key    = 'promen_imgsz_' . md5( $url );
	$cached = get_transient( $key );
	if ( is_string( $cached ) ) {
		return $cached;
	}

	$base = get_theme_file_uri( '' );
	$attr = '';
	if ( 0 === strpos( $url, $base ) ) {
		$file = get_theme_file_path( ltrim( substr( $url, strlen( $base ) ), '/' ) );
		if ( is_readable( $file ) ) {
			$size = @getimagesize( $file );
			if ( ! empty( $size[0] ) && ! empty( $size[1] ) ) {
				$attr = ' width="' . (int) $size[0] . '" height="' . (int) $size[1] . '"';
			}
		}
	}

	set_transient( $key, $attr, WEEK_IN_SECONDS );
	return $attr;
}

/** Включаем norm в core XML sitemap. */
add_filter( 'wp_sitemaps_taxonomies', function ( array $taxonomies ): array {
	$taxonomies['norm'] = get_taxonomy( 'norm' );
	return $taxonomies;
} );

/**
 * Родительские разделы каталога — в карту сайта.
 *
 * Ядро отдаёт термины с hide_empty, а у «СДТ», «Крепёж», «Трубы», «Опоры»,
 * «Арматура» и «Изоляции» напрямую назначенных товаров нет — товары висят на
 * подкатегориях. Из 33 разделов в карту попадали 27, причём без самой
 * содержательной страницы сайта: у /catalog/sdt/ 6 988 слов.
 */
add_filter( 'wp_sitemaps_taxonomies_query_args', function ( array $args, string $taxonomy ): array {
	if ( 'product_cat' !== $taxonomy ) {
		return $args;
	}
	$args['hide_empty'] = false;
	// Разделы каталога перечислены в defs — по нему и отбираем. Просто снять
	// hide_empty мало: вместе с родителями в карту приезжает служебная
	// «Uncategorized», а она отдаёт 302.
	if ( function_exists( 'promen_catalog_taxonomy_defs' ) ) {
		$args['slug'] = array_keys( promen_catalog_taxonomy_defs() );
	}
	return $args;
}, 10, 2 );

/**
 * Архив автора — вон из карты сайта.
 *
 * Провайдер users отдавал единственный адрес /author/admin/: 26 слов, H1 от
 * витрины, без canonical и description — и заодно публиковал логин
 * администратора. Сам архив закрываем noindex: ссылок на него нет, но и
 * держать в индексе пустышку незачем.
 */
add_filter( 'wp_sitemaps_add_provider', function ( $provider, string $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

add_action( 'wp_head', function (): void {
	if ( is_author() ) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}, 1 );

/**
 * Страницы серий в карте сайта.
 *
 * Серия — маршрут /catalog/<путь>/seriya/<норматив>[-<угол>]/, не пост и не
 * термин, поэтому ядро о ней не знает. Обнаружить её можно было только через
 * хлебные крошки карточек. При этом серия — готовая посадочная под запрос
 * «фланец ГОСТ 33259» и, в отличие от карточки, сама себе не дубль.
 *
 * Список собираем запросом по связям, а не перебором товаров: 15 394 вызова
 * promen_series_meta() в момент отдачи карты никто не дождётся. Тройка
 * «глубокая категория + норматив + угол» однозначно задаёт слаг серии — тот
 * же ключ, по которому её ищет promen_series_representative().
 */
function promen_series_sitemap_urls(): array {
	$key    = 'promen_series_sitemap';
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$rows = $wpdb->get_results(
		"SELECT DISTINCT c.slug AS cat, n.slug AS norm, COALESCE(a.name, '') AS angle
		   FROM {$wpdb->posts} p
		   JOIN {$wpdb->term_relationships} trn ON trn.object_id = p.ID
		   JOIN {$wpdb->term_taxonomy} ttn ON ttn.term_taxonomy_id = trn.term_taxonomy_id AND ttn.taxonomy = 'norm'
		   JOIN {$wpdb->terms} n ON n.term_id = ttn.term_id
		   JOIN {$wpdb->term_relationships} trc ON trc.object_id = p.ID
		   JOIN {$wpdb->term_taxonomy} ttc ON ttc.term_taxonomy_id = trc.term_taxonomy_id AND ttc.taxonomy = 'product_cat'
		   JOIN {$wpdb->terms} c ON c.term_id = ttc.term_id
		   LEFT JOIN {$wpdb->term_relationships} tra ON tra.object_id = p.ID
		   LEFT JOIN {$wpdb->term_taxonomy} tta ON tta.term_taxonomy_id = tra.term_taxonomy_id AND tta.taxonomy = 'pa_angle'
		   LEFT JOIN {$wpdb->terms} a ON a.term_id = tta.term_id
		  WHERE p.post_type = 'product' AND p.post_status = 'publish'",
		ARRAY_A
	);

	$defs = function_exists( 'promen_catalog_taxonomy_defs' ) ? promen_catalog_taxonomy_defs() : [];
	$urls = [];
	foreach ( (array) $rows as $r ) {
		$cat = (string) $r['cat'];
		// Только самая глубокая категория: серия принадлежит ей, а не родителю.
		if ( ! isset( $defs[ $cat ] ) || ! empty( $defs[ $cat ]['children'] ) ) {
			continue;
		}
		$slug = (string) $r['norm'] . ( '' !== $r['angle'] ? '-' . $r['angle'] : '' );
		$link = function_exists( 'promen_product_cat_link' ) ? promen_product_cat_link( $cat ) : '';
		if ( '' === $link ) {
			continue;
		}
		$urls[ trailingslashit( $link ) . 'seriya/' . $slug . '/' ] = true;
	}

	$urls = array_keys( $urls );
	sort( $urls );
	set_transient( $key, $urls, DAY_IN_SECONDS );
	return $urls;
}

/** Провайдер карты сайта для страниц серий. */
function promen_register_series_sitemap(): void {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) || class_exists( 'Promen_Series_Sitemap_Provider' ) ) {
		return;
	}

	/** Отдаёт адреса серий одной страницей: их порядка сотни. */
	class Promen_Series_Sitemap_Provider extends WP_Sitemaps_Provider {
		public function __construct() {
			$this->name        = 'series';
			$this->object_type = 'series';
		}

		public function get_url_list( $page_num, $object_subtype = '' ) {
			$out = [];
			foreach ( promen_series_sitemap_urls() as $url ) {
				$out[] = [ 'loc' => $url ];
			}
			return $out;
		}

		public function get_max_num_pages( $object_subtype = '' ) {
			return promen_series_sitemap_urls() ? 1 : 0;
		}
	}

	wp_register_sitemap_provider( 'series', new Promen_Series_Sitemap_Provider() );
}
add_action( 'init', 'promen_register_series_sitemap', 20 );

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
