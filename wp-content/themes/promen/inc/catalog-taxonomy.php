<?php
/**
 * Единый реестр категорий каталога (навигация, страницы категорий, group views).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Каноническое дерево категорий.
 *
 * @return array<string, array{
 *   label: string,
 *   title: string,
 *   path: string,
 *   code?: string,
 *   nav_name?: string,
 *   meta_suffix?: string,
 *   has_page: bool,
 *   nav: bool,
 *   parent?: string,
 *   children?: string[]
 * }>
 */
function promen_catalog_taxonomy_defs(): array {
	static $defs = null;
	if ( null !== $defs ) {
		return $defs;
	}

	$defs = [
		'sdt' => [
			'label'       => 'СДТ',
			'title'       => 'СДТ',
			'path'        => '/ Соединительные детали трубопровода',
			'code'        => 'СДТ',
			'nav_name'    => 'Соединительные детали трубопровода',
			'meta_suffix' => '5 семейств',
			'has_page'    => true,
			'nav'         => true,
			'children'    => [ 'otvody', 'troyniki', 'perekhody', 'dnishcha', 'zaglushki' ],
		],
		'otvody' => [
			'label'    => 'Отводы',
			'title'    => 'ОТВОДЫ',
			'path'     => '/ Отводы',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'sdt',
		],
		'troyniki' => [
			'label'    => 'Тройники',
			'title'    => 'ТРОЙНИКИ',
			'path'     => '/ Тройники',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'sdt',
		],
		'perekhody' => [
			'label'    => 'Переходы',
			'title'    => 'ПЕРЕХОДЫ',
			'path'     => '/ Переходы',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'sdt',
		],
		'dnishcha' => [
			'label'    => 'Днища',
			'title'    => 'ДНИЩА',
			'path'     => '/ Днища',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'sdt',
		],
		'zaglushki' => [
			'label'    => 'Заглушки',
			'title'    => 'ЗАГЛУШКИ',
			'path'     => '/ Заглушки',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'sdt',
		],
		'flancy' => [
			'label'       => 'Фланцы',
			'title'       => 'ФЛАНЦЫ',
			'path'        => '/ Фланцы',
			'code'        => 'ФЛ',
			'nav_name'    => 'Фланцы трубопроводные',
			'meta_suffix' => '4 типа',
			'has_page'    => true,
			'nav'         => true,
			'children'    => [ 'flancy-plosk', 'flancy-vorot', 'flancy-01', 'flancy-11' ],
		],
		'flancy-plosk' => [
			'label'    => 'Плоские ФП',
			'title'    => 'ПЛОСКИЕ ФП',
			'path'     => '/ Фланцы / Плоские ФП',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'flancy',
		],
		'flancy-vorot' => [
			'label'    => 'Воротниковые ФВ',
			'title'    => 'ВОРОТНИКОВЫЕ ФВ',
			'path'     => '/ Фланцы / Воротниковые ФВ',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'flancy',
		],
		'flancy-01' => [
			'label'    => 'Тип 01',
			'title'    => 'ТИП 01',
			'path'     => '/ Фланцы / Тип 01',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'flancy',
		],
		'flancy-11' => [
			'label'    => 'Тип 11',
			'title'    => 'ТИП 11',
			'path'     => '/ Фланцы / Тип 11',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'flancy',
		],
		'krepezh' => [
			'label'       => 'Крепёж',
			'title'       => 'КРЕПЁЖ',
			'path'        => '/ Крепёж',
			'code'        => 'КР',
			'nav_name'    => 'Крепёж',
			'meta_suffix' => '5 семейств',
			'has_page'    => true,
			'nav'         => true,
			'children'    => [ 'bolty', 'shpilki', 'gayki', 'shayby', 'vinty' ],
		],
		'bolty' => [
			'label'    => 'Болты',
			'title'    => 'БОЛТЫ',
			'path'     => '/ Крепёж / Болты',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'krepezh',
		],
		'shpilki' => [
			'label'    => 'Шпильки',
			'title'    => 'ШПИЛЬКИ',
			'path'     => '/ Крепёж / Шпильки',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'krepezh',
		],
		'gayki' => [
			'label'    => 'Гайки',
			'title'    => 'ГАЙКИ',
			'path'     => '/ Крепёж / Гайки',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'krepezh',
		],
		'shayby' => [
			'label'    => 'Шайбы',
			'title'    => 'ШАЙБЫ',
			'path'     => '/ Крепёж / Шайбы',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'krepezh',
		],
		'vinty' => [
			'label'    => 'Винты',
			'title'    => 'ВИНТЫ',
			'path'     => '/ Крепёж / Винты',
			'has_page' => true,
			'nav'      => true,
			'parent'   => 'krepezh',
		],
		'tochenye' => [
			'label'       => 'Точеные детали',
			'title'       => 'ТОЧЕНЫЕ ДЕТАЛИ',
			'path'        => '/ Точеные детали',
			'code'        => 'ТД',
			'nav_name'    => 'Точеные детали',
			'meta_suffix' => 'переходы ПТ',
			'has_page'    => true,
			'nav'         => true,
		],
		'truby' => [
			'label'       => 'Трубы',
			'title'       => 'ТРУБЫ',
			'path'        => '/ Трубы',
			'code'        => 'ТР',
			'nav_name'    => 'Стальные трубы',
			'meta_suffix' => '4 типа',
			'has_page'    => true,
			'nav'         => true,
			'children'    => [ 'truby-bsh', 'truby-es', 'truby-ppu', 'truby-vgp' ],
		],
		'truby-bsh' => [
			'label'    => 'Бесшовные',
			'title'    => 'БЕСШОВНЫЕ',
			'path'     => '/ Трубы / Бесшовные',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'truby',
		],
		'truby-es' => [
			'label'    => 'Электросварные',
			'title'    => 'ЭЛЕКТРОСВАРНЫЕ',
			'path'     => '/ Трубы / Электросварные',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'truby',
		],
		'truby-ppu' => [
			'label'    => 'В ППУ',
			'title'    => 'ППУ',
			'path'     => '/ Трубы / ППУ',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'truby',
		],
		'truby-vgp' => [
			'label'    => 'ВГП',
			'title'    => 'ВГП',
			'path'     => '/ Трубы / ВГП',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'truby',
		],
		'izolyatsiya' => [
			'label'       => 'Изоляция',
			'title'       => 'ИЗОЛЯЦИЯ',
			'path'        => '/ Изоляция и покрытия',
			'code'        => 'ИЗ',
			'nav_name'    => 'Изоляция и покрытия',
			'meta_suffix' => 'тройники ППУ',
			'has_page'    => true,
			'nav'         => true,
		],
		'opory' => [
			'label'       => 'Опоры',
			'title'       => 'ОПОРЫ',
			'path'        => '/ Опоры трубопроводов',
			'code'        => 'ОП',
			'nav_name'    => 'Опоры трубопроводов',
			'meta_suffix' => '3 типа',
			'has_page'    => true,
			'nav'         => true,
			'children'    => [ 'opory-nepodv', 'opory-skolz', 'opory-pruzh' ],
		],
		'opory-nepodv' => [
			'label'    => 'Неподвижные',
			'title'    => 'НЕПОДВИЖНЫЕ',
			'path'     => '/ Опоры / Неподвижные',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'opory',
		],
		'opory-skolz' => [
			'label'    => 'Скользящие',
			'title'    => 'СКОЛЬЗЯЩИЕ',
			'path'     => '/ Опоры / Скользящие',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'opory',
		],
		'opory-pruzh' => [
			'label'    => 'Пружинные',
			'title'    => 'ПРУЖИННЫЕ',
			'path'     => '/ Опоры / Пружинные',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'opory',
		],
		'armatura' => [
			'label'       => 'Арматура',
			'title'       => 'АРМАТУРА',
			'path'        => '/ Арматура',
			'code'        => 'ЗРА',
			'nav_name'    => 'Арматура',
			'meta_suffix' => 'задвижки · клапаны · краны',
			'has_page'    => true,
			'nav'         => true,
			'children'    => [ 'armatura-zadvizhki', 'armatura-klapany', 'armatura-krany' ],
		],
		'armatura-zadvizhki' => [
			'label'    => 'Задвижки',
			'title'    => 'ЗАДВИЖКИ',
			'path'     => '/ Арматура / Задвижки',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'armatura',
		],
		'armatura-klapany' => [
			'label'    => 'Клапаны',
			'title'    => 'КЛАПАНЫ',
			'path'     => '/ Арматура / Клапаны',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'armatura',
		],
		'armatura-krany' => [
			'label'    => 'Краны',
			'title'    => 'КРАНЫ',
			'path'     => '/ Арматура / Краны',
			'has_page' => false,
			'nav'      => true,
			'parent'   => 'armatura',
		],
	];

	return $defs;
}

/** Слаги верхнего уровня сайдбара (порядок навигации). */
function promen_catalog_nav_roots(): array {
	return [ 'sdt', 'flancy', 'krepezh', 'tochenye', 'truby', 'izolyatsiya', 'opory', 'armatura' ];
}

/** Категории со своим taxonomy-шаблоном страницы категории. */
function promen_catalog_page_slugs(): array {
	$out = [];
	foreach ( promen_catalog_taxonomy_defs() as $slug => $def ) {
		if ( ! empty( $def['has_page'] ) ) {
			$out[] = $slug;
		}
	}
	return $out;
}

/** Определение категории или null. */
function promen_catalog_taxonomy_def( string $slug ): ?array {
	$defs = promen_catalog_taxonomy_defs();
	return $defs[ $slug ] ?? null;
}

/**
 * Счётчик группы из канона (с кэшем на запрос).
 */
function promen_catalog_nav_count( string $slug ): int {
	static $cache = [];
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}
	if ( ! function_exists( 'promen_catalog_group_count' ) ) {
		return $cache[ $slug ] = 0;
	}
	return $cache[ $slug ] = promen_catalog_group_count( $slug );
}

/**
 * Рендер сайдбара групп каталога.
 *
 * @param string $active_group Текущий slug группы.
 */
function promen_render_catalog_sidebar( string $active_group ): void {
	$defs  = promen_catalog_taxonomy_defs();
	$roots = promen_catalog_nav_roots();
	$chev  = '<svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	?>
      <div class="sb-hd">Группы продукции<span class="sb-hd-count"><?php echo esc_html( (string) count( $roots ) ); ?></span></div>
      <nav id="catNav">
	<?php
	foreach ( $roots as $root ) {
		$def = $defs[ $root ] ?? null;
		if ( ! $def || empty( $def['nav'] ) ) {
			continue;
		}
		$count = promen_catalog_nav_count( $root );
		if ( $count <= 0 && ! get_term_by( 'slug', $root, 'product_cat' ) ) {
			continue;
		}
		$children = $def['children'] ?? [];
		$id_map   = [
			'sdt'      => 'sbnSdt',
			'flancy'   => 'sbnFlancy',
			'krepezh'  => 'sbnKrepezh',
			'truby'    => 'sbnTruby',
			'opory'    => 'sbnOpory',
			'armatura' => 'sbnArmatura',
		];
		$nav_id = $id_map[ $root ] ?? ( 'sbn' . ucfirst( $root ) );

		if ( $children ) {
			$family_slugs = array_merge( [ $root ], $children );
			$open         = in_array( $active_group, $family_slugs, true ) || ( $root === 'sdt' && in_array( $active_group, [ '', 'sdt' ], true ) );
			if ( $root === 'sdt' ) {
				$open = true; // СДТ всегда раскрыт, как раньше.
			}
			$parent_active = ( $root === 'sdt' )
				? in_array( $active_group, [ '', 'sdt' ], true )
				: ( $active_group === $root );
			$code      = $def['code'] ?? '';
			$nav_name  = $def['nav_name'] ?? $def['label'];
			$meta_suf  = $def['meta_suffix'] ?? '';
			$meta_line = number_format_i18n( $count ) . ' поз.' . ( $meta_suf !== '' ? ' · ' . $meta_suf : '' );
			?>
        <div class="sbn-group<?php echo $open ? ' open' : ''; ?>" id="<?php echo esc_attr( $nav_id ); ?>">
          <div class="sbn-item sbn-item--parent<?php echo $parent_active ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( $root ) ); ?>">
              <div class="sbn-code"><?php echo esc_html( $code ); ?></div>
              <div class="sbn-info">
                <span class="sbn-name"><?php echo esc_html( $nav_name ); ?></span>
                <span class="sbn-meta"><?php echo esc_html( $meta_line ); ?></span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
              <?php echo $chev; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            </button>
          </div>
          <div class="sbn-children">
			<?php
			foreach ( $children as $child ) {
				$cdef = $defs[ $child ] ?? null;
				if ( ! $cdef ) {
					continue;
				}
				$ccount = promen_catalog_nav_count( $child );
				if ( $ccount <= 0 && ! get_term_by( 'slug', $child, 'product_cat' ) ) {
					continue;
				}
				if ( $ccount <= 0 ) {
					continue;
				}
				?>
            <a class="sbn-child sbn-filter<?php echo $active_group === $child ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( $child ) ); ?>">
              <span class="sbn-child-name"><?php echo esc_html( $cdef['label'] ); ?></span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $ccount ) ); ?></span>
            </a>
				<?php
			}
			?>
          </div>
        </div>
			<?php
		} else {
			if ( $count <= 0 ) {
				continue;
			}
			$code     = $def['code'] ?? '';
			$nav_name = $def['nav_name'] ?? $def['label'];
			$meta_suf = $def['meta_suffix'] ?? '';
			$meta     = number_format_i18n( $count ) . ' поз.' . ( $meta_suf !== '' ? ' · ' . $meta_suf : '' );
			?>
        <a class="sbn-item sbn-filter<?php echo $active_group === $root ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( $root ) ); ?>">
          <div class="sbn-code"><?php echo esc_html( $code ); ?></div>
          <div class="sbn-info">
            <span class="sbn-name"><?php echo esc_html( $nav_name ); ?></span>
            <span class="sbn-meta"><?php echo esc_html( $meta ); ?></span>
          </div>
        </a>
			<?php
		}
	}
	?>
        <div class="sbn-item" style="opacity:.45;cursor:default;">
          <div class="sbn-code">НМ</div>
          <div class="sbn-info"><span class="sbn-name">Нестандартные изделия</span><span class="sbn-meta">По КД заказчика · в наполнении</span></div>
        </div>
      </nav>
	<?php
}

/**
 * Встроить живой реестр на страницу категории (только таблица + фильтры + PDP).
 * Без сайдбара групп — иначе страница категории превращается в гибрид /catalog/.
 */
function promen_render_category_catalog_embed( string $group_slug, int $count = 0 ): void {
	if ( $count <= 0 && function_exists( 'promen_catalog_group_count' ) ) {
		$count = promen_catalog_group_count( $group_slug );
	}
	$def   = promen_catalog_taxonomy_def( $group_slug );
	$label = $def['title'] ?? strtoupper( $group_slug );
	$group = $group_slug;
	$promen_registry_show_cat_link = false;
	$promen_registry_with_pdp      = true;
	?>
<section class="s catalog-embed" id="registry">
  <div class="s-hd">
    <div class="s-badge"><span class="s-badge-num">02</span>Реестр изделий</div>
    <div class="s-meta">LIVE CATALOG / <?php echo esc_html( $label ); ?> · <?php echo esc_html( number_format_i18n( $count ) ); ?> позиций</div>
  </div>
  <div class="cat-body catalog-embed__body">
    <?php include get_theme_file_path( 'woocommerce/parts/catalog-registry.php' ); ?>
  </div>
</section>
	<?php
}

/**
 * Топ нормативов категории из канона: [ ['slug','name','count','dn','steels'], … ].
 *
 * @param bool $merge_variants Схлопывать year/gost-r варианты одного норматива.
 * @return list<array{slug:string,name:string,count:int,dn?:string,steels?:string}>
 */
function promen_catalog_group_norm_stats( string $group, int $limit = 0, bool $merge_variants = true ): array {
	global $wpdb;
	$table = promen_catalog_table_name();
	$slugs = function_exists( 'promen_catalog_group_slugs' ) ? promen_catalog_group_slugs( $group ) : [ $group ];
	if ( ! $slugs ) {
		return [];
	}
	$ph  = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );
	$sql = "SELECT norm_slug AS slug,
		MAX(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.norm'))) AS name,
		MAX(JSON_UNQUOTE(JSON_EXTRACT(payload, '$.steel_display'))) AS steels,
		MIN(NULLIF(dn, 0)) AS dn_min,
		MAX(NULLIF(dn, 0)) AS dn_max,
		COUNT(*) AS cnt
		FROM {$table}
		WHERE category IN ({$ph}) AND norm_slug <> ''
		GROUP BY norm_slug
		ORDER BY cnt DESC";
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $slugs ), ARRAY_A );
	$out  = [];
	$seen = [];
	foreach ( $rows ?: [] as $row ) {
		$slug = (string) ( $row['slug'] ?? '' );
		if ( $slug === '' ) {
			continue;
		}
		$dn = '';
		if ( $row['dn_min'] !== null && $row['dn_max'] !== null ) {
			// Хвостовые нули срезаем только у дробей (21.30→21.3); целые не трогаем,
			// иначе 800→8, 300→3, 20→2 (тот же баг, что и в product-data.php:361/452).
			$fmt  = static function ( $v ): string {
				$s = (string) (float) $v;
				return strpos( $s, '.' ) !== false ? rtrim( rtrim( $s, '0' ), '.' ) : $s;
			};
			$dmin = $fmt( $row['dn_min'] );
			$dmax = $fmt( $row['dn_max'] );
			$dn   = $dmin === $dmax ? $dmin : ( $dmin . '–' . $dmax );
		}
		$steels = trim( (string) ( $row['steels'] ?? '' ) );
		$name   = trim( (string) ( $row['name'] ?? '' ) );
		if ( $name === '' ) {
			$term = get_term_by( 'slug', $slug, 'norm' );
			$name = ( $term && ! is_wp_error( $term ) ) ? $term->name : $slug;
		}
		$cnt = (int) $row['cnt'];

		if ( $merge_variants ) {
			$base = preg_replace( '/^gost-r-/', 'gost-', $slug );
			$base = preg_replace( '/-(19|20)\d{2}$/', '', (string) $base );
			if ( isset( $seen[ $base ] ) ) {
				$i = $seen[ $base ];
				// Не суммируем тройной импорт: берём max и предпочитаем slug с годом.
				if ( $cnt > $out[ $i ]['count'] ) {
					$out[ $i ]['count']  = $cnt;
					$out[ $i ]['slug']   = $slug;
					$out[ $i ]['name']   = $name;
					$out[ $i ]['dn']     = $dn;
					$out[ $i ]['steels'] = $steels !== '' ? $steels : $out[ $i ]['steels'];
				} elseif ( strlen( $slug ) > strlen( $out[ $i ]['slug'] ) ) {
					$out[ $i ]['slug'] = $slug;
					$out[ $i ]['name'] = $name;
				}
				continue;
			}
			$seen[ $base ] = count( $out );
		}

		$out[] = [
			'slug'   => $slug,
			'name'   => $name,
			'count'  => $cnt,
			'dn'     => $dn,
			'steels' => $steels,
		];
	}
	usort( $out, static fn( $a, $b ) => $b['count'] <=> $a['count'] );
	if ( $limit > 0 ) {
		return array_slice( $out, 0, $limit );
	}
	return $out;
}

/**
 * Группы типоисполнений для реестра s01 (код / название / match-префиксы slug).
 *
 * @return list<array{key:string,code:string,name:string,small:string,match:list<string>}>
 */
function promen_catalog_series_groups( string $group ): array {
	$defs = [
		'otvody' => [
			[ 'key' => 'ok', 'code' => 'ОК', 'name' => 'Крутоизогнутые', 'small' => 'штампованные · R = 1,5DN (3D) и R ≈ DN (2D) · 45–180°', 'match' => [ 'gost-17375', 'gost-30753' ] ],
			[ 'key' => 'og', 'code' => 'ОГ', 'name' => 'Гнутые', 'small' => 'из трубной заготовки · R = 1,5–5DN · углы от 15°', 'match' => [ 'gost-22793', 'sto-321' ] ],
			[ 'key' => 'ko', 'code' => 'КО', 'name' => 'Колена с опорой', 'small' => 'опорная пята · высокое давление', 'match' => [ 'gost-22818' ] ],
			[ 'key' => 'oss', 'code' => 'ОСС', 'name' => 'Сварные секторные', 'small' => 'сборка из сегментов · крупный DN', 'match' => [ 'ost-36-21' ] ],
			[ 'key' => 'o24', 'code' => 'О24', 'name' => 'ОСТ 24.125', 'small' => 'детали трубопроводов энергетики', 'match' => [ 'ost-24-125' ] ],
			[ 'key' => 'o34', 'code' => 'О34', 'name' => 'ОСТ 34', 'small' => 'трубопроводы ТЭС', 'match' => [ 'ost-34' ] ],
			[ 'key' => 'aes', 'code' => 'АЭС', 'name' => 'СТО АЭС', 'small' => 'атомная энергетика', 'match' => [ 'sto-95', 'sto-79814898', 'sto-sro' ] ],
		],
		'troyniki' => [
			[ 'key' => 'tsh', 'code' => 'ТШ', 'name' => 'Штампованные приварные', 'small' => 'бесшовные · равнопроходные и переходные', 'match' => [ 'gost-17376' ] ],
			[ 'key' => 'tr1', 'code' => 'Т-100', 'name' => 'На Ру до 100 МПа', 'small' => 'высокое давление · с опорой и без', 'match' => [ 'gost-22801', 'gost-22822' ] ],
			[ 'key' => 'tsv', 'code' => 'ТСВ', 'name' => 'Сварные для энергетики', 'small' => 'ОСТ 34 · трубопроводы ТЭС', 'match' => [ 'ost-34' ] ],
			[ 'key' => 't24', 'code' => 'О24', 'name' => 'ОСТ 24.125', 'small' => 'детали трубопроводов энергетики', 'match' => [ 'ost-24' ] ],
			[ 'key' => 'tts', 'code' => 'ТТС', 'name' => 'Тепловые сети', 'small' => 'типовая серия', 'match' => [ 'seriya-4-903', 'seriya-4' ] ],
			[ 'key' => 'aes', 'code' => 'АЭС', 'name' => 'СТО АЭС', 'small' => 'атомная энергетика', 'match' => [ 'sto-95', 'sto-79814898', 'sto-sro' ] ],
		],
		'perekhody' => [
			[ 'key' => 'psh', 'code' => 'ПШ', 'name' => 'Бесшовные приварные', 'small' => 'концентрические и эксцентрические', 'match' => [ 'gost-17378' ] ],
			[ 'key' => 'p100', 'code' => 'П-100', 'name' => 'На Ру до 100 МПа', 'small' => 'высокое давление · нефтехимия', 'match' => [ 'gost-22826' ] ],
			[ 'key' => 'psv', 'code' => 'ПСВ', 'name' => 'Сварные крупный DN', 'small' => 'ОСТ 36 / ОСТ 34 · ТЭС', 'match' => [ 'ost-36-22', 'ost-34' ] ],
			[ 'key' => 'pt', 'code' => 'ПТ', 'name' => 'Точёные и мелкий DN', 'small' => 'СТО ЦКТИ / точёные', 'match' => [ 'sto-318' ] ],
			[ 'key' => 'p24', 'code' => 'О24', 'name' => 'ОСТ 24.125', 'small' => 'детали трубопроводов энергетики', 'match' => [ 'ost-24' ] ],
			[ 'key' => 'aes', 'code' => 'АЭС', 'name' => 'СТО АЭС', 'small' => 'атомная энергетика', 'match' => [ 'sto-95', 'sto-79814898', 'sto-sro' ] ],
		],
		'dnishcha' => [
			[ 'key' => 'de', 'code' => 'ДЭ', 'name' => 'Эллиптические отбортованные', 'small' => 'для сосудов, аппаратов и котлов', 'match' => [ 'gost-6533' ] ],
			[ 'key' => 'do', 'code' => 'ДО', 'name' => 'ОСТ энергетики', 'small' => 'ОСТ 24.125', 'match' => [ 'ost-24' ] ],
		],
		'zaglushki' => [
			[ 'key' => 'ze', 'code' => 'ЗЭ', 'name' => 'Эллиптические приварные', 'small' => 'бесшовные · ГОСТ 17379', 'match' => [ 'gost-17379' ] ],
			[ 'key' => 'zf', 'code' => 'ЗФ', 'name' => 'Фланцевые / высокое давление', 'small' => 'ГОСТ 22815 и ОСТ', 'match' => [ 'gost-22815' ] ],
			[ 'key' => 'zo', 'code' => 'ЗО', 'name' => 'ОСТ энергетики', 'small' => 'ОСТ 34 / 24', 'match' => [ 'ost-34', 'ost-24' ] ],
			[ 'key' => 'aes', 'code' => 'АЭС', 'name' => 'СТО АЭС', 'small' => 'атомная энергетика', 'match' => [ 'sto-95', 'sto-79814898', 'sto-sro' ] ],
		],
		'flancy' => [
			[ 'key' => 't11', 'code' => '11', 'name' => 'Воротниковые тип 11', 'small' => 'приварные встык · ГОСТ 33259', 'match' => [ 'gost-33259' ] ],
			[ 'key' => 'fp', 'code' => 'ФП', 'name' => 'Плоские приварные', 'small' => 'трубопроводные и сосудовые', 'match' => [ 'gost-12820', 'gost-28759' ] ],
			[ 'key' => 'fv', 'code' => 'ФВ', 'name' => 'Воротниковые приварные встык', 'small' => 'ГОСТ 12821', 'match' => [ 'gost-12821' ] ],
			[ 'key' => 'fo', 'code' => 'ФО', 'name' => 'ОСТ / прочие', 'small' => 'энергетика и спец.', 'match' => [ 'ost-' ] ],
		],
		'truby' => [
			[ 'key' => 'bs', 'code' => 'БШ', 'name' => 'Бесшовные', 'small' => 'горячедеформированные / холоднодеформированные', 'match' => [ 'gost-8732', 'gost-8734' ] ],
			[ 'key' => 'es', 'code' => 'ЭС', 'name' => 'Электросварные', 'small' => 'прямошовные', 'match' => [ 'gost-10704', 'gost-10705' ] ],
			[ 'key' => 'ppu', 'code' => 'ППУ', 'name' => 'В ППУ-изоляции', 'small' => 'тепловые сети', 'match' => [ 'gost-30732' ] ],
			[ 'key' => 'vgp', 'code' => 'ВГП', 'name' => 'Водогазопроводные', 'small' => 'ГОСТ 3262', 'match' => [ 'gost-3262' ] ],
		],
		'krepezh' => [
			[ 'key' => 'b', 'code' => 'Б', 'name' => 'Болты', 'small' => 'фундаментные · шестигранные · высокопрочные', 'match' => [ 'gost-22032', 'gost-7805', 'gost-22043', 'gost-7798', 'gost-7795', 'gost-10602', 'gost-7796', 'gost-7808', 'gost-7811', 'gost-7817', 'gost-7783', 'gost-15591' ] ],
			[ 'key' => 'shp', 'code' => 'ШП', 'name' => 'Шпильки', 'small' => 'фланцевые · общепромышленные · ОСТ', 'match' => [ 'gost-15590', 'gost-9066', 'ost-26-2040', 'gost-10494' ] ],
			[ 'key' => 'g', 'code' => 'Г', 'name' => 'Гайки', 'small' => 'шестигранные · фланцевые · колпачковые', 'match' => [ 'gost-9064', 'gost-5915', 'gost-5916', 'gost-5927', 'gost-5929', 'gost-5935', 'gost-10605', 'gost-10607' ] ],
			[ 'key' => 'sh', 'code' => 'Ш', 'name' => 'Шайбы', 'small' => 'пружинные · усиленные', 'match' => [ 'gost-6402', 'gost-11371' ] ],
			[ 'key' => 'v', 'code' => 'В', 'name' => 'Винты', 'small' => 'с метрической резьбой', 'match' => [ 'gost-11738', 'gost-6958' ] ],
		],
		'bolty' => [
			[ 'key' => 'main', 'code' => 'Б', 'name' => 'Болты', 'small' => 'семейство крепежа', 'match' => [ '' ] ],
		],
		'gayki' => [
			[ 'key' => 'main', 'code' => 'Г', 'name' => 'Гайки', 'small' => 'семейство крепежа', 'match' => [ '' ] ],
		],
		'shpilki' => [
			[ 'key' => 'main', 'code' => 'ШП', 'name' => 'Шпильки', 'small' => 'семейство крепежа', 'match' => [ '' ] ],
		],
		'shayby' => [
			[ 'key' => 'main', 'code' => 'Ш', 'name' => 'Шайбы', 'small' => 'семейство крепежа', 'match' => [ '' ] ],
		],
		'vinty' => [
			[ 'key' => 'main', 'code' => 'В', 'name' => 'Винты', 'small' => 'с метрической резьбой', 'match' => [ '' ] ],
		],
		'tochenye' => [
			[ 'key' => 'pt', 'code' => 'ПТ', 'name' => 'Точёные детали', 'small' => 'механическая обработка', 'match' => [ '' ] ],
		],
		'opory' => [
			[ 'key' => 'main', 'code' => 'ОПР', 'name' => 'Опоры трубопроводов', 'small' => 'неподвижные · скользящие · пружинные', 'match' => [ '' ] ],
		],
		'armatura' => [
			[ 'key' => 'main', 'code' => 'ЗРА', 'name' => 'Арматура', 'small' => 'задвижки · клапаны · краны', 'match' => [ '' ] ],
		],
		'izolyatsiya' => [
			[ 'key' => 'main', 'code' => 'ИЗЛ', 'name' => 'Изоляция', 'small' => 'оболочки ППУ', 'match' => [ '' ] ],
		],
		'sdt' => [
			[ 'key' => 'otv', 'code' => 'ОТВ', 'name' => 'Отводы', 'small' => 'крутоизогнутые · гнутые · секторные', 'match' => [ 'gost-17375', 'gost-30753', 'gost-22793', 'gost-22818', 'sto-321', 'ost-36-21', 'ost-24-125' ] ],
			[ 'key' => 'tro', 'code' => 'ТРО', 'name' => 'Тройники', 'small' => 'штампованные · сварные · СТО АЭС', 'match' => [ 'gost-17376', 'gost-22801', 'gost-22822', 'sto-95-127', 'sto-79814898-125', 'ost-34-42-676', 'seriya-4' ] ],
			[ 'key' => 'per', 'code' => 'ПЕР', 'name' => 'Переходы', 'small' => 'бесшовные · сварные · точёные', 'match' => [ 'gost-17378', 'gost-22826', 'ost-36-22', 'ost-34-10-42', 'sto-318' ] ],
			[ 'key' => 'dn', 'code' => 'ДНЩ', 'name' => 'Днища', 'small' => 'эллиптические', 'match' => [ 'gost-6533', 'ost-24-125-53', 'ost-24-125-21' ] ],
			[ 'key' => 'zag', 'code' => 'ЗГЛ', 'name' => 'Заглушки', 'small' => 'эллиптические · фланцевые', 'match' => [ 'gost-17379', 'gost-22815', 'ost-34-10' ] ],
		],
	];

	if ( isset( $defs[ $group ] ) ) {
		return $defs[ $group ];
	}
	$def = promen_catalog_taxonomy_def( $group );
	$code = strtoupper( mb_substr( (string) ( $def['code'] ?? $group ), 0, 3 ) );
	$name = (string) ( $def['label'] ?? $def['title'] ?? $group );
	return [
		[ 'key' => 'main', 'code' => $code, 'name' => $name, 'small' => 'нормативы раздела', 'match' => [ '' ] ],
	];
}

/**
 * Разложить нормативы по группам типоисполнений. Невошедшие — в «Прочие».
 *
 * @param list<array{slug:string,name:string,count:int,dn?:string,steels?:string}> $norms
 * @return list<array{key:string,code:string,name:string,small:string,items:list<array>,count:int}>
 */
function promen_catalog_bucket_series_norms( string $group, array $norms ): array {
	$groups = promen_catalog_series_groups( $group );
	$buckets = [];
	foreach ( $groups as $g ) {
		$buckets[ $g['key'] ] = $g + [ 'items' => [], 'count' => 0 ];
	}
	$other_key = 'other';
	$buckets[ $other_key ] = [
		'key'   => $other_key,
		'code'  => 'ПР',
		'name'  => 'Прочие нормативы',
		'small' => 'есть в живом реестре · без отдельной группы',
		'items' => [],
		'count' => 0,
		'match' => [],
	];

	foreach ( $norms as $norm ) {
		$slug = (string) $norm['slug'];
		$base = preg_replace( '/^gost-r-/', 'gost-', $slug );
		$base = (string) preg_replace( '/-(19|20)\d{2}$/', '', (string) $base );
		$placed = false;
		foreach ( $groups as $g ) {
			foreach ( $g['match'] as $prefix ) {
				if ( $prefix === '' ) {
					$buckets[ $g['key'] ]['items'][] = $norm;
					$buckets[ $g['key'] ]['count']  += (int) $norm['count'];
					$placed = true;
					break 2;
				}
				if ( str_starts_with( $slug, $prefix ) || str_starts_with( $base, $prefix ) ) {
					$buckets[ $g['key'] ]['items'][] = $norm;
					$buckets[ $g['key'] ]['count']  += (int) $norm['count'];
					$placed = true;
					break 2;
				}
			}
		}
		if ( ! $placed ) {
			$buckets[ $other_key ]['items'][] = $norm;
			$buckets[ $other_key ]['count']  += (int) $norm['count'];
		}
	}

	$out = [];
	foreach ( $buckets as $b ) {
		if ( ! $b['items'] ) {
			continue;
		}
		$out[] = $b;
	}
	return $out;
}

/**
 * Краткое имя серии для строки реестра исполнений.
 */
function promen_catalog_series_row_title( array $norm ): string {
	$name = (string) ( $norm['name'] ?? '' );
	if ( function_exists( 'promen_series_type_name' ) ) {
		$typed = promen_series_type_name( $name );
		if ( $typed !== '' && $typed !== 'Изделие' ) {
			return $typed;
		}
	}
	return $name !== '' ? $name : (string) ( $norm['slug'] ?? '' );
}

/**
 * Русское склонение для счётчиков (1 / 2–4 / 5+).
 */
function promen_ru_plural( int $n, string $one, string $few, string $many ): string {
	$n = abs( $n ) % 100;
	$n1 = $n % 10;
	if ( $n > 10 && $n < 20 ) {
		return $many;
	}
	if ( $n1 > 1 && $n1 < 5 ) {
		return $few;
	}
	if ( $n1 === 1 ) {
		return $one;
	}
	return $many;
}

/**
 * Секция 04 «Нормативная база» — только нормативы этой категории (из канона).
 * Показываем максимум 6 карточек; остальные — по кнопке «ещё».
 */
function promen_render_category_norms_section( string $group_slug ): void {
	$def   = promen_catalog_taxonomy_def( $group_slug );
	$label = $def['label'] ?? ( $def['title'] ?? $group_slug );
	$meta  = strtoupper( preg_replace( '/[^a-z0-9]+/i', '-', $group_slug ) ?: $group_slug );
	$norms = promen_catalog_group_norm_stats( $group_slug, 0, true );
	$term  = get_term_by( 'slug', $group_slug, 'product_cat' );
	$base  = ( $term && ! is_wp_error( get_term_link( $term ) ) ) ? get_term_link( $term ) : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ) );
	$visible = 6;
	$hidden  = max( 0, count( $norms ) - $visible );
	?>
<section class="s s-dark" id="s04">
  <div class="s-hd">
    <div class="s-badge"><span class="s-badge-num">04</span>Нормативная база</div>
    <div class="s-meta">REGULATORY REGISTRY / <?php echo esc_html( $meta ); ?></div>
  </div>
  <div class="s-body" style="padding-top:28px;">
    <?php if ( ! $norms ) : ?>
      <p class="reveal" style="opacity:.7;padding:0 40px 40px;">Нормативы раздела наполняются вместе с каталогом.</p>
    <?php else : ?>
      <div class="norm-group nactive" id="ng-main">
        <div class="ng-label">Нормативная база — <?php echo esc_html( $label ); ?></div>
        <div class="norm-grid2" data-norm-grid>
          <?php foreach ( $norms as $i => $n ) :
				$href    = add_query_arg( 'gost', $n['slug'], $base );
				$classes = 'nc reveal sg-link' . ( $i >= $visible ? ' nc-extra' : '' );
				?>
          <a class="<?php echo esc_attr( $classes ); ?>" href="<?php echo esc_url( $href ); ?>">
            <div class="nc-code"><?php echo esc_html( $n['name'] ); ?></div>
            <div class="nc-title"><?php echo esc_html( number_format_i18n( $n['count'] ) ); ?> типоразмеров в реестре</div>
            <div class="nc-desc">Клик откроет живой реестр раздела с фильтром по этому нормативу.</div>
            <div class="nc-tags">
              <span class="nc-tag"><?php echo esc_html( number_format_i18n( $n['count'] ) ); ?> поз.</span>
              <span class="nc-tag">Фильтр →</span>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>В каталоге</div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php if ( $hidden > 0 ) : ?>
          <button type="button" class="norm-more-btn" data-norm-more>
            Показать ещё <?php echo esc_html( (string) $hidden ); ?> <?php echo esc_html( promen_ru_plural( $hidden, 'норматив', 'норматива', 'нормативов' ) ); ?>
          </button>
        <?php endif; ?>
      </div>
      <div class="norm-group nactive" id="ng-gen" style="margin-top:28px;">
        <div class="ng-label">Нормативная база — Общие документы</div>
        <div class="norm-grid2">
          <div class="nc reveal">
            <div class="nc-code">ТР ТС 032/2013</div>
            <div class="nc-title">О безопасности оборудования под избыточным давлением</div>
            <div class="nc-desc">Обязателен при рабочем давлении свыше 0,05 МПа — декларация / сертификат в комплекте поставки.</div>
            <div class="nc-tags"><span class="nc-tag">ТР ТС</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ТУ 24.20.40-001</div>
            <div class="nc-title">Технические условия предприятия</div>
            <div class="nc-desc">Комплектность, маркировка, объём НК и паспорт изделия по регламенту завода.</div>
            <div class="nc-tags"><span class="nc-tag">ТУ</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2023</div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
	<?php
}

/**
 * Секция «Реестр исполнений» (s01) — группы + все нормативы из канона (как в живом реестре).
 */
function promen_render_category_series_registry( string $group_slug ): void {
	$code   = strtoupper( preg_replace( '/[^a-z0-9]+/i', '-', $group_slug ) ?: $group_slug );
	$total  = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( $group_slug ) : 0;
	// Без merge: тот же набор slug, что и фасет ГОСТ в живом реестре.
	$norms  = promen_catalog_group_norm_stats( $group_slug, 0, false );
	$groups = promen_catalog_bucket_series_norms( $group_slug, $norms );
	$term   = get_term_by( 'slug', $group_slug, 'product_cat' );
	$base   = ( $term && ! is_wp_error( get_term_link( $term ) ) ) ? get_term_link( $term ) : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' ) );
	$n_ser  = count( $norms );
	$n_grp  = count( $groups );
	$prefix = function_exists( 'promen_series_code_prefix' ) ? promen_series_code_prefix( $group_slug ) : mb_substr( $code, 0, 3 );
	$row_i  = 0;
	?>
<section class="s s-alt" id="s01">
  <div class="s-hd">
    <div class="s-badge"><span class="s-badge-num">01</span>Реестр исполнений</div>
    <div class="s-meta">PRODUCT REGISTRY / <?php echo esc_html( $code ); ?></div>
  </div>
  <div class="reg-bar" id="regBar">
    <span class="rb-lbl">Типоисполнения</span>
    <span class="rb-lbl" style="opacity:.55;"><?php echo esc_html( number_format_i18n( $n_grp ) ); ?> <?php echo esc_html( promen_ru_plural( $n_grp, 'группа', 'группы', 'групп' ) ); ?> · клик по заголовку сворачивает</span>
    <span class="rb-count" id="regCount"><?php echo esc_html( number_format_i18n( $n_ser ) ); ?> <?php echo esc_html( promen_ru_plural( $n_ser, 'серия', 'серии', 'серий' ) ); ?> · <?php echo esc_html( number_format_i18n( $total ) ); ?> позиций</span>
  </div>
  <div class="reg-hd">
    <span>Норматив</span><span>Наименование</span><span>DN</span><span>Позиций</span><span>Материал</span><span>Отрасль</span><span></span>
  </div>
  <div id="regList">
    <?php if ( ! $groups ) : ?>
      <p style="padding:24px 40px;opacity:.65;">Серии появятся после наполнения каталога.</p>
    <?php else : ?>
      <?php foreach ( $groups as $g ) :
			$n_items = count( $g['items'] );
			?>
      <div class="reg-group open" data-group="<?php echo esc_attr( $g['key'] ); ?>">
        <button class="reg-group-hd" type="button" aria-expanded="true">
          <span class="rg-code"><?php echo esc_html( $g['code'] ); ?></span>
          <span class="rg-name"><?php echo esc_html( $g['name'] ); ?><small><?php echo esc_html( $g['small'] ); ?></small></span>
          <span class="rg-params"><?php echo esc_html( number_format_i18n( $n_items ) ); ?> <?php echo esc_html( promen_ru_plural( $n_items, 'серия', 'серии', 'серий' ) ); ?></span>
          <span class="rg-cnt"><?php echo esc_html( number_format_i18n( (int) $g['count'] ) ); ?> поз.</span>
          <span class="rg-chev"><svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </button>
        <div class="reg-group-body">
          <?php
			$ind_short = [ 'aes' => 'АЭС', 'tes' => 'ТЭС', 'gkh' => 'ЖКХ', 'ngk' => 'НГК' ];
			foreach ( $g['items'] as $n ) :
				$row_i++;
				$href  = add_query_arg( 'gost', $n['slug'], $base );
				$title = promen_catalog_series_row_title( $n );
				$dn    = ( $n['dn'] ?? '' ) !== '' ? $n['dn'] : '—';
				$mat   = ( $n['steels'] ?? '' ) !== '' ? $n['steels'] : 'по стандарту';
				// Отрасли по нормативу; если эвристика молчит — общепром. по умолчанию.
				$inds  = function_exists( 'promen_industry_slugs_from_norm' ) ? promen_industry_slugs_from_norm( $n['name'] ) : [];
				if ( ! $inds ) {
					$inds = [ 'tes', 'gkh', 'ngk' ];
				}
				?>
          <a class="reg-r" data-type="<?php echo esc_attr( $g['key'] ); ?>" href="<?php echo esc_url( $href ); ?>">
            <span class="rr-g"><?php echo esc_html( $n['name'] ); ?></span>
            <span class="rr-n"><?php echo esc_html( $title ); ?><small>фильтр в реестре изделий</small></span>
            <span class="rr-dn"><?php echo esc_html( $dn ); ?></span>
            <span class="rr-pn"><?php echo esc_html( number_format_i18n( $n['count'] ) ); ?> поз.</span>
            <span class="rr-m"><?php echo esc_html( $mat ); ?></span>
            <span class="rr-t"><?php foreach ( $inds as $isl ) : ?><span class="rr-ind"><?php echo esc_html( $ind_short[ $isl ] ?? $isl ); ?></span><?php endforeach; ?></span>
            <span class="rr-arr">›</span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
	<?php
}
