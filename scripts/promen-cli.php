<?php
/**
 * WP-CLI команды PROM-EN.
 *
 *   wp promen import [--file=…] [--category=отводы] [--limit=N] [--dry-run]
 *   wp promen verify [--file=…] [--sample=N]
 *
 * CSV: /data/products-csv/products_variable.csv (см. docker-compose.yml).
 * Однопроходный потоковый импорт: variable-родители в CSV всегда идут
 * раньше своих вариаций (проверено). Идемпотентен по SKU.
 */

defined( 'WP_CLI' ) || exit;

class Promen_Import_Command {

	private const DEFAULT_FILE = '/data/products-csv/products_variable.csv';

	/** Категория CSV → путь в product_cat (родитель/ребёнок, слаг => название). */
	private const CATEGORY_TREE = [
		'отводы'   => [ [ 'sdt', 'СДТ — Соединительные детали трубопровода' ], [ 'otvody', 'Отводы' ] ],
		'переходы' => [ [ 'sdt', 'СДТ — Соединительные детали трубопровода' ], [ 'perekhody', 'Переходы' ] ],
		'тройники' => [ [ 'sdt', 'СДТ — Соединительные детали трубопровода' ], [ 'troyniki', 'Тройники' ] ],
		'днища'    => [ [ 'sdt', 'СДТ — Соединительные детали трубопровода' ], [ 'dnishcha', 'Днища' ] ],
		'заглушки' => [ [ 'sdt', 'СДТ — Соединительные детали трубопровода' ], [ 'zaglushki', 'Заглушки' ] ],
		'фланцы'   => [ [ 'flancy', 'Фланцы' ] ],
		'крепеж'   => [ [ 'krepezh', 'Крепёж' ] ],
		'точеные'  => [ [ 'tochenye', 'Точеные детали' ] ],
		'трубы'    => [ [ 'truby', 'Трубы' ] ],
		'изоляция' => [ [ 'izolyatsiya', 'Изоляция и покрытия' ] ],
		'опоры'    => [ [ 'opory', 'Опоры трубопроводов' ] ],
		'арматура' => [ [ 'armatura', 'Арматура' ] ],
	];

	/** Семейства крепежа → подкатегория внутри «Крепёж». */
	private const FASTENER_FAMILIES = [
		'Болт'    => [ 'bolty', 'Болты' ],
		'Гайка'   => [ 'gayki', 'Гайки' ],
		'Шпилька' => [ 'shpilki', 'Шпильки' ],
		'Шайба'   => [ 'shayby', 'Шайбы' ],
		'Винт'    => [ 'vinty', 'Винты' ],
	];

	/** Типы фланцев (product_type) → подкатегория. */
	private const FLANGE_TYPES = [
		'ФП' => [ 'flancy-plosk', 'Плоские ФП' ],
		'ФВ' => [ 'flancy-vorot', 'Воротниковые ФВ' ],
		'01' => [ 'flancy-01', 'Тип 01' ],
		'11' => [ 'flancy-11', 'Тип 11' ],
	];

	/** Типы труб (product_type) → подкатегория. */
	private const TUBE_TYPES = [
		'БШ'  => [ 'truby-bsh', 'Бесшовные' ],
		'ЭС'  => [ 'truby-es', 'Электросварные' ],
		'ППУ' => [ 'truby-ppu', 'В ППУ-изоляции' ],
		'ВГП' => [ 'truby-vgp', 'Водогазопроводные' ],
	];

	/** Типы опор (product_type) → подкатегория. */
	private const SUPPORT_TYPES = [
		'НП' => [ 'opory-nepodv', 'Неподвижные' ],
		'СК' => [ 'opory-skolz', 'Скользящие' ],
		'ПР' => [ 'opory-pruzh', 'Пружинные' ],
	];

	/** Типы арматуры (product_type) → подкатегория. */
	private const VALVE_TYPES = [
		'ЗД' => [ 'armatura-zadvizhki', 'Задвижки' ],
		'КО' => [ 'armatura-klapany', 'Клапаны' ],
		'КР' => [ 'armatura-krany', 'Краны' ],
	];

	private const ATTRIBUTES = [
		'steel'      => 'Марка стали',
		'supervised' => 'Поднадзорность',
		'dn'         => 'DN',
		'pn'         => 'PN',
		'angle'      => 'Угол',
	];

	private bool $dry = false;
	private array $report = [ 'created' => 0, 'updated' => 0, 'variations' => 0, 'skipped_dup' => 0, 'errors' => 0 ];
	private array $seen_skus = [];
	/** sku родителя => product_id (для привязки вариаций). */
	private array $parent_ids = [];
	private array $term_cache = [];
	private array $norm_cache = [];

	public function import( array $args, array $assoc ): void {
		$file     = $assoc['file'] ?? self::DEFAULT_FILE;
		$only_cat = $assoc['category'] ?? null;
		$limit    = isset( $assoc['limit'] ) ? (int) $assoc['limit'] : 0;
		$this->dry = ! empty( $assoc['dry-run'] );

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV не найден: {$file}" );
		}

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
		wp_suspend_cache_invalidation( true );
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$this->ensure_attributes();

		$fh     = fopen( $file, 'r' );
		$header = fgetcsv( $fh );
		$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] ); // BOM
		$idx = array_flip( $header );

		$n = 0;
		$processed = 0;
		$started = microtime( true );

		while ( ( $line = fgetcsv( $fh ) ) !== false ) {
			$n++;
			$row = [];
			foreach ( $idx as $col => $i ) {
				$row[ $col ] = isset( $line[ $i ] ) ? trim( (string) $line[ $i ] ) : '';
			}

			$row = $this->normalize_row( $row );

			if ( $only_cat && $row['category'] !== $only_cat ) {
				continue;
			}

			$sku = $row['sku'];
			if ( isset( $this->seen_skus[ $sku ] ) ) {
				$this->report['skipped_dup']++;
				WP_CLI::debug( "дубль SKU, строка {$n}: {$sku}" );
				continue;
			}
			$this->seen_skus[ $sku ] = true;

			try {
				$this->import_row( $row );
			} catch ( Throwable $e ) {
				$this->report['errors']++;
				WP_CLI::warning( "строка {$n} ({$sku}): " . $e->getMessage() );
			}

			$processed++;
			if ( $processed % 500 === 0 ) {
				$rate = round( $processed / ( microtime( true ) - $started ), 1 );
				WP_CLI::log( "… {$processed} строк ({$rate}/с)" );
				// Не даём кэшу объектов разрастаться на длинном прогоне.
				wp_cache_flush();
			}
			if ( $limit && $processed >= $limit ) {
				break;
			}
		}
		fclose( $fh );

		wp_suspend_cache_invalidation( false );
		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		if ( ! $this->dry ) {
			WP_CLI::log( 'Обновляю lookup-таблицы WooCommerce…' );
			wc_update_product_lookup_tables();
			// Иерархия и счётчики терминов после отложенного импорта.
			foreach ( [ 'product_cat', 'norm', 'pa_steel', 'pa_supervised', 'pa_dn', 'pa_pn', 'pa_angle' ] as $tax ) {
				clean_taxonomy_cache( $tax );
			}
			WP_CLI::runcommand( 'term recount product_cat norm pa_steel pa_supervised pa_dn pa_pn pa_angle', [ 'launch' => false, 'exit_error' => false ] );
			WP_CLI::runcommand( 'cache flush', [ 'launch' => false, 'exit_error' => false ] );
		}

		$r = $this->report;
		WP_CLI::success( sprintf(
			'Импорт завершён: создано %d, обновлено %d, вариаций %d, дублей пропущено %d, ошибок %d.',
			$r['created'], $r['updated'], $r['variations'], $r['skipped_dup'], $r['errors']
		) );
	}

	private function import_row( array $row ): void {
		switch ( $row['woo_type'] ) {
			case 'variable':
				$this->import_product( $row, true );
				break;
			case 'simple':
				$this->import_product( $row, false );
				break;
			case 'variation':
				$this->import_variation( $row );
				break;
		}
	}

	/**
	 * Нормализация строки до импорта:
	 * — отводы, ошибочно попавшие в «переходы»;
	 * — пустой/мусорный normative_key → ГОСТ/ОСТ/СТО из designation/title;
	 * — у бобышек DN≠номер исполнения.
	 */
	private function normalize_row( array $row ): array {
		$fam   = (string) ( $row['product_family'] ?? '' );
		$title = (string) ( $row['title'] ?? '' );

		if ( ( $row['category'] ?? '' ) === 'переходы'
			&& ( str_starts_with( $fam, 'Отвод' ) || str_starts_with( $title, 'Отвод' ) )
		) {
			WP_CLI::warning( sprintf(
				'категория remapped переходы→отводы: %s (%s)',
				$row['sku'] ?? '',
				mb_substr( $title, 0, 60 )
			) );
			$row['category'] = 'отводы';
		}

		$nk = trim( (string) ( $row['normative_key'] ?? '' ) );
		if ( $nk === '' || strcasecmp( $nk, 'k' ) === 0 ) {
			$resolved = $this->resolve_normative_key( $row );
			if ( $resolved !== '' ) {
				$row['normative_key'] = $resolved;
			}
		}

		$row = $this->sanitize_execution_as_dn( $row );
		return $row;
	}

	/** ГОСТ/ОСТ/СТО/ТУ из full_designation или title. */
	private function resolve_normative_key( array $row ): string {
		$fd = trim( (string) ( $row['full_designation'] ?? '' ) );
		if ( $fd !== '' && strcasecmp( $fd, 'k' ) !== 0 ) {
			$from_fd = $this->extract_normative( $fd );
			if ( $from_fd !== '' ) {
				return $from_fd;
			}
			// designation уже и есть норматив (напр. «ОСТ 24.125.57-1989»).
			if ( preg_match( '/^(ГОСТ|ОСТ|СТО|ТУ)\b/ui', $fd ) ) {
				return preg_replace( '/\s+/u', ' ', $fd );
			}
		}
		return $this->extract_normative( (string) ( $row['title'] ?? '' ) );
	}

	private function extract_normative( string $text ): string {
		if ( $text === '' ) {
			return '';
		}
		if ( preg_match( '/((?:ГОСТ|ОСТ|СТО|ТУ)\s*(?:СРО-П\s*)?[^\s,]{3,}(?:-\d{2,4})?)/ui', $text, $m ) ) {
			return preg_replace( '/\s+/u', ' ', trim( $m[1] ) );
		}
		return '';
	}

	/** DN = 01…09 при совпадении с execution и наличии Dн — это исполнение, не проход. */
	private function sanitize_execution_as_dn( array $row ): array {
		$dn = trim( (string) ( $row['dn'] ?? '' ) );
		$ex = trim( (string) ( $row['execution'] ?? ( $row['dim_execution'] ?? '' ) ) );
		$od = trim( (string) ( $row['outer_diameter'] ?? '' ) );
		$title = (string) ( $row['title'] ?? '' );
		$is_bob = (bool) preg_match( '/бобыш/ui', $title )
			|| str_contains( (string) ( $row['normative_key'] ?? '' ), '24.125.57' )
			|| str_contains( $title, '24.125.57' );

		if ( $is_bob && $dn !== '' && $ex !== '' && $dn === $ex
			&& preg_match( '/^0?[1-9]$/', $dn ) && $od !== ''
		) {
			$row['dn'] = '';
			if ( isset( $row['dim_dn'] ) ) {
				$row['dim_dn'] = '';
			}
			if ( trim( (string) ( $row['execution'] ?? '' ) ) === '' ) {
				$row['execution'] = $ex;
			}
		}
		return $row;
	}

	private function import_product( array $row, bool $variable ): void {
		if ( $this->dry ) {
			$this->report['created']++;
			if ( $variable ) {
				$this->parent_ids[ $row['sku'] ] = -1;
			}
			return;
		}

		$existing_id = wc_get_product_id_by_sku( $row['sku'] );
		$product = $existing_id
			? wc_get_product( $existing_id )
			: ( $variable ? new WC_Product_Variable() : new WC_Product_Simple() );

		$product->set_name( $row['title'] );
		$product->set_slug( $this->unique_slug( $row['title'], $row['sku'], $existing_id ) );
		$product->set_sku( $row['sku'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_description( $row['description'] );
		$product->set_stock_status( 'onbackorder' );
		// Пустая масса в CSV — сбросить вес (иначе старый мусор из прошлого импорта остаётся).
		$product->set_weight( $row['mass_kg'] !== '' ? $row['mass_kg'] : '' );

		$product->set_attributes( $this->build_attributes( $row, $variable ) );

		$product->update_meta_data( '_promen_dims', wp_json_encode( $this->collect_dims( $row ), JSON_UNESCAPED_UNICODE ) );
		$product->update_meta_data( '_promen_norm_key', $row['normative_key'] );
		if ( $row['gost_designation'] !== '' ) {
			$product->update_meta_data( '_promen_gost_designation', $row['gost_designation'] );
		}
		if ( $row['product_family'] !== '' ) {
			$product->update_meta_data( '_promen_family', $row['product_family'] );
		}

		$id = $product->save();

		wp_set_object_terms( $id, $this->category_terms( $row ), 'product_cat' );
		wp_set_object_terms( $id, $this->norm_term( $row['normative_key'] ), 'norm' );

		if ( $variable ) {
			$this->parent_ids[ $row['sku'] ] = $id;
		}
		$this->report[ $existing_id ? 'updated' : 'created' ]++;
	}

	private function import_variation( array $row ): void {
		if ( $this->dry ) {
			$this->report['variations']++;
			return;
		}

		$parent_id = $this->parent_ids[ $row['parent_sku'] ] ?? wc_get_product_id_by_sku( $row['parent_sku'] );
		if ( ! $parent_id ) {
			throw new RuntimeException( "родитель {$row['parent_sku']} не найден" );
		}

		$existing_id = wc_get_product_id_by_sku( $row['sku'] );
		$variation = $existing_id ? wc_get_product( $existing_id ) : new WC_Product_Variation();
		$variation->set_parent_id( $parent_id );
		$variation->set_sku( $row['sku'] );
		$variation->set_status( 'publish' );
		$variation->set_stock_status( 'onbackorder' );
		$variation->set_weight( $row['mass_kg'] !== '' ? $row['mass_kg'] : '' );

		$attrs = [];
		if ( $row['attr_steel'] !== '' ) {
			$attrs['pa_steel'] = $this->attr_term_slug( 'steel', $row['attr_steel'] );
		}
		if ( $row['attr_supervised'] !== '' ) {
			$attrs['pa_supervised'] = $this->attr_term_slug( 'supervised', $row['attr_supervised'] );
		}
		$variation->set_attributes( $attrs );

		$variation->save();
		$this->report['variations']++;
	}

	/** Атрибуты товара: вариационные (сталь, надзор) + фильтровые (DN, PN, угол). */
	private function build_attributes( array $row, bool $variable ): array {
		$attrs = [];
		$position = 0;

		$add = function ( string $key, array $values, bool $is_variation ) use ( &$attrs, &$position ) {
			$values = array_filter( array_map( 'trim', $values ), 'strlen' );
			if ( ! $values ) {
				return;
			}
			$tax = 'pa_' . $key;
			$ids = [];
			foreach ( $values as $v ) {
				$ids[] = $this->attr_term_id( $key, $v );
			}
			$a = new WC_Product_Attribute();
			$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
			$a->set_name( $tax );
			$a->set_options( $ids );
			$a->set_position( $position++ );
			$a->set_visible( true );
			$a->set_variation( $is_variation );
			$attrs[] = $a;
		};

		if ( $variable ) {
			$add( 'steel', explode( '|', $row['attr_steel'] ), true );
			$add( 'supervised', explode( '|', $row['attr_supervised'] ), true );
		} else {
			// У simple-товаров сталь и надзор — обычные (невариационные) атрибуты.
			$add( 'steel', [ $row['attr_steel'] ?: $row['material'] ], false );
			$add( 'supervised', [ $row['attr_supervised'] ], false );
		}

		$add( 'dn', [ $row['dn'] ], false );
		$add( 'pn', [ $row['pn'] ], false );
		$add( 'angle', [ $row['angle'] ], false );

		return $attrs;
	}

	/** Все непустые dim_* + базовая геометрия — одним JSON. */
	private function collect_dims( array $row ): array {
		$dims = [];
		foreach ( $row as $col => $val ) {
			if ( $val !== '' && ( str_starts_with( $col, 'dim_' ) ) ) {
				$dims[ substr( $col, 4 ) ] = $val;
			}
		}
		foreach ( [ 'outer_diameter', 'wall_thickness', 'outer_d_branch', 'wall_branch', 'dn_branch',
			'radius', 'execution', 'angle', 'dn', 'pn', 'gost_designation',
			'thread_size', 'length_mm', 'strength_class', 'accuracy_class', 'washer_type',
			'material_grade', 'coating', 'thread_pitch', 'nominal_d_mm', 'standard_page',
			'product_type' ] as $col ) {
			if ( isset( $row[ $col ] ) && $row[ $col ] !== '' ) {
				$dims[ $col ] = $row[ $col ];
			}
		}
		// Тип фланца: CSV product_type (01/11/ФП/ФВ) или dim_flange_type.
		if ( empty( $dims['flange_type'] ) && ! empty( $row['product_type'] )
			&& isset( self::FLANGE_TYPES[ $row['product_type'] ] )
		) {
			$dims['flange_type'] = $row['product_type'];
		}
		// Не оставляем DN=исполнение у бобышек в dims.
		$dn = trim( (string) ( $dims['dn'] ?? '' ) );
		$ex = trim( (string) ( $dims['execution'] ?? '' ) );
		$od = trim( (string) ( $dims['outer_diameter'] ?? '' ) );
		if ( $dn !== '' && $ex !== '' && $dn === $ex && preg_match( '/^0?[1-9]$/', $dn ) && $od !== '' ) {
			unset( $dims['dn'] );
		}
		return $dims;
	}

	// ── Категории ────────────────────────────────────────────────

	private function category_terms( array $row ): array {
		$path = self::CATEGORY_TREE[ $row['category'] ] ?? null;
		if ( ! $path ) {
			return [];
		}
		if ( $row['category'] === 'крепеж' ) {
			foreach ( self::FASTENER_FAMILIES as $prefix => $sub ) {
				if ( str_starts_with( $row['product_family'], $prefix ) ) {
					$path = array_merge( $path, [ $sub ] );
					break;
				}
			}
		}
		if ( $row['category'] === 'фланцы' ) {
			$type = $row['product_type'] ?? '';
			if ( isset( self::FLANGE_TYPES[ $type ] ) ) {
				$path = array_merge( $path, [ self::FLANGE_TYPES[ $type ] ] );
			}
		}
		if ( $row['category'] === 'трубы' ) {
			$type = $row['product_type'] ?? '';
			if ( isset( self::TUBE_TYPES[ $type ] ) ) {
				$path = array_merge( $path, [ self::TUBE_TYPES[ $type ] ] );
			}
		}
		if ( $row['category'] === 'опоры' ) {
			$type = $row['product_type'] ?? '';
			if ( isset( self::SUPPORT_TYPES[ $type ] ) ) {
				$path = array_merge( $path, [ self::SUPPORT_TYPES[ $type ] ] );
			}
		}
		if ( $row['category'] === 'арматура' ) {
			$type = $row['product_type'] ?? '';
			if ( isset( self::VALVE_TYPES[ $type ] ) ) {
				$path = array_merge( $path, [ self::VALVE_TYPES[ $type ] ] );
			}
		}

		$parent  = 0;
		$ids     = [];
		foreach ( $path as [ $slug, $name ] ) {
			$parent = $this->ensure_term( $slug, $name, $parent );
			$ids[]  = $parent;
		}
		// Привязываем только самый глубокий термин — предки достраиваются иерархией.
		return [ end( $ids ) ];
	}

	private function ensure_term( string $slug, string $name, int $parent ): int {
		$key = "cat:{$slug}:{$parent}";
		if ( isset( $this->term_cache[ $key ] ) ) {
			return $this->term_cache[ $key ];
		}
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term ) {
			$id = (int) $term->term_id;
		} else {
			$res = wp_insert_term( $name, 'product_cat', [ 'slug' => $slug, 'parent' => $parent ] );
			if ( is_wp_error( $res ) ) {
				throw new RuntimeException( $res->get_error_message() );
			}
			$id = (int) $res['term_id'];
		}
		return $this->term_cache[ $key ] = $id;
	}

	private function norm_term( string $norm_key ): array {
		if ( $norm_key === '' ) {
			return [];
		}
		if ( ! isset( $this->norm_cache[ $norm_key ] ) ) {
			$slug = promen_translit( $norm_key );
			$term = get_term_by( 'slug', $slug, 'norm' );
			if ( $term ) {
				$this->norm_cache[ $norm_key ] = (int) $term->term_id;
			} else {
				$res = wp_insert_term( $norm_key, 'norm', [ 'slug' => $slug ] );
				if ( is_wp_error( $res ) ) {
					throw new RuntimeException( $res->get_error_message() );
				}
				$this->norm_cache[ $norm_key ] = (int) $res['term_id'];
			}
		}
		return [ $this->norm_cache[ $norm_key ] ];
	}

	// ── Атрибуты ─────────────────────────────────────────────────

	private function ensure_attributes(): void {
		foreach ( self::ATTRIBUTES as $key => $label ) {
			if ( ! wc_attribute_taxonomy_id_by_name( 'pa_' . $key ) ) {
				$res = wc_create_attribute( [ 'name' => $label, 'slug' => $key ] );
				if ( is_wp_error( $res ) ) {
					WP_CLI::error( "атрибут {$key}: " . $res->get_error_message() );
				}
			}
			$tax = 'pa_' . $key;
			if ( ! taxonomy_exists( $tax ) ) {
				register_taxonomy( $tax, 'product', [ 'hierarchical' => false, 'show_ui' => false ] );
			}
		}
	}

	private function attr_term_slug( string $key, string $value ): string {
		return promen_translit( $value );
	}

	private function attr_term_id( string $key, string $value ): int {
		$tax  = 'pa_' . $key;
		$slug = $this->attr_term_slug( $key, $value );
		$ck   = "attr:{$tax}:{$slug}";
		if ( isset( $this->term_cache[ $ck ] ) ) {
			return $this->term_cache[ $ck ];
		}
		$term = get_term_by( 'slug', $slug, $tax );
		if ( $term ) {
			$id = (int) $term->term_id;
		} else {
			$res = wp_insert_term( $value, $tax, [ 'slug' => $slug ] );
			if ( is_wp_error( $res ) ) {
				throw new RuntimeException( "термин {$value} ({$tax}): " . $res->get_error_message() );
			}
			$id = (int) $res['term_id'];
		}
		return $this->term_cache[ $ck ] = $id;
	}

	// ── Слаги ────────────────────────────────────────────────────

	private function unique_slug( string $title, string $sku, int $existing_id ): string {
		$slug = promen_translit( $title );
		// При обновлении оставляем текущий слаг, чтобы не плодить редиректы.
		if ( $existing_id ) {
			$current = get_post_field( 'post_name', $existing_id );
			if ( $current ) {
				return $current;
			}
		}
		return $slug;
	}

	// ── Контент терминов ─────────────────────────────────────────

	/**
	 * Заливка контента категорий/нормативов из markdown-файлов.
	 *
	 *   wp promen content [--dir=/data/content]
	 *
	 * Файл: frontmatter (taxonomy, slug, name?) + HTML-тело → описание термина.
	 */
	public function content( array $args, array $assoc ): void {
		$dir = rtrim( $assoc['dir'] ?? '/data/content', '/' );
		if ( ! is_dir( $dir ) ) {
			WP_CLI::error( "Каталог не найден: {$dir}" );
		}
		$files = new RegexIterator(
			new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) ),
			'/\.md$/'
		);
		$done = 0;
		foreach ( $files as $file ) {
			$raw = file_get_contents( (string) $file );
			if ( ! preg_match( '/^---\n(.*?)\n---\n(.*)$/s', $raw, $m ) ) {
				WP_CLI::warning( "нет frontmatter: {$file}" );
				continue;
			}
			$meta = [];
			foreach ( explode( "\n", $m[1] ) as $line ) {
				if ( str_contains( $line, ':' ) ) {
					[ $k, $v ] = explode( ':', $line, 2 );
					$meta[ trim( $k ) ] = trim( $v );
				}
			}
			if ( empty( $meta['taxonomy'] ) || empty( $meta['slug'] ) ) {
				WP_CLI::warning( "нет taxonomy/slug: {$file}" );
				continue;
			}
			$term = get_term_by( 'slug', $meta['slug'], $meta['taxonomy'] );
			if ( ! $term ) {
				WP_CLI::warning( "термин не найден: {$meta['taxonomy']}/{$meta['slug']}" );
				continue;
			}
			$update = [ 'description' => trim( $m[2] ) ];
			if ( ! empty( $meta['name'] ) ) {
				$update['name'] = $meta['name'];
			}
			$res = wp_update_term( $term->term_id, $meta['taxonomy'], $update );
			if ( is_wp_error( $res ) ) {
				WP_CLI::warning( "{$meta['slug']}: " . $res->get_error_message() );
				continue;
			}
			$done++;
			WP_CLI::log( "✓ {$meta['taxonomy']}/{$meta['slug']}" );
		}
		WP_CLI::success( "Контент загружен: {$done} терминов." );
	}

	// ── Сверка ───────────────────────────────────────────────────

	public function verify( array $args, array $assoc ): void {
		$file     = $assoc['file'] ?? self::DEFAULT_FILE;
		$sample   = isset( $assoc['sample'] ) ? (int) $assoc['sample'] : 20;
		$only_cat = $assoc['category'] ?? null;

		$fh     = fopen( $file, 'r' );
		$header = fgetcsv( $fh );
		$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] );
		$idx = array_flip( $header );

		$rows = [];
		while ( ( $line = fgetcsv( $fh ) ) !== false ) {
			if ( $line[ $idx['woo_type'] ] === 'variation' ) {
				continue;
			}
			if ( $only_cat && $line[ $idx['category'] ] !== $only_cat ) {
				continue;
			}
			$rows[] = $line;
		}
		fclose( $fh );

		shuffle( $rows );
		$rows = array_slice( $rows, 0, $sample );
		$fail = 0;

		foreach ( $rows as $line ) {
			$sku   = $line[ $idx['sku'] ];
			$title = $line[ $idx['title'] ];
			$id    = wc_get_product_id_by_sku( $sku );
			if ( ! $id ) {
				WP_CLI::warning( "нет товара: {$sku}" );
				$fail++;
				continue;
			}
			$p = wc_get_product( $id );
			if ( $p->get_name() !== $title ) {
				WP_CLI::warning( "title не совпал: {$sku}" );
				$fail++;
			}
		}
		$ok = count( $rows ) - $fail;
		WP_CLI::success( "Сверка: {$ok}/" . count( $rows ) . ' совпали.' );
	}
}

WP_CLI::add_command( 'promen import', [ new Promen_Import_Command(), 'import' ] );
WP_CLI::add_command( 'promen verify', [ new Promen_Import_Command(), 'verify' ] );
WP_CLI::add_command( 'promen content', [ new Promen_Import_Command(), 'content' ] );
