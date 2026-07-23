<?php
/**
 * Дополнить списки сталей по нормативам (≤8) и синхронизировать pa_steel родителя.
 * Запуск: wp eval-file /scripts/_enrich_steel_lists.php
 */

defined( 'ABSPATH' ) || exit;

/**
 * norm_slug (точный слаг из wp_promen_catalog_rows) => марки стали.
 * Списки — по ГОСТ 1759.0-1987, ГОСТ 20700-90 и профильным ГОСТам/ОСТам (кап 8).
 * simple-товары этих нормативов конвертируются в variable с вариациями по pa_steel.
 *
 * @var array<string, list<string>>
 */
$NORM_STEELS = [
	// --- Крепёж: болты общего назначения (ГОСТ 1759.0, кл. 3.6–10.9) ---
	'gost-7798-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-7805-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-7795-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-7796-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-7808-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],

	// --- Крепёж: болты фундаментные (ГОСТ 24379) ---
	'gost-22032-1976'       => [ 'Ст3сп', '20', '09Г2С', '35', '40Х', '45' ],
	'gost-22043-1976'       => [ 'Ст3сп', '20', '09Г2С', '35', '40Х', '45' ],

	// --- Крепёж: болты высокопрочные (кл. 8.8–12.9) ---
	'gost-10602-1994'       => [ '40Х', '35ХФА', '40ХФА', '30Х3МФ', '20Х3МВФ', '45' ],

	// --- Крепёж: шпильки/гайки фланцевые, теплоустойчивые (ГОСТ 20700-90) ---
	'gost-9066-1970'        => [ '35', '40Х', '30ХМА', '25Х1МФ', '25Х2М1Ф', '20Х1М1Ф1ТР', '35Х', '08Х18Н10Т' ],
	'gost-9064-1970'        => [ '35', '40Х', '30ХМА', '25Х1МФ', '25Х2М1Ф', '20Х1М1Ф1ТР', '35Х', '08Х18Н10Т' ],
	'gost-10494-1980'       => [ '35', '40Х', '30ХМА', '25Х1МФ', '25Х2М1Ф', '20Х1М1Ф1ТР', '35Х', '08Х18Н10Т' ],
	'gost-15590-1970'       => [ '35', '40Х', '30ХМА', '25Х1МФ', '25Х2М1Ф', '20Х1М1Ф1ТР', '35Х', '08Х18Н10Т' ],
	'gost-15591-1970'       => [ '35', '40Х', '30ХМА', '25Х1МФ', '25Х2М1Ф', '20Х1М1Ф1ТР', '35Х', '08Х18Н10Т' ],
	'ost-26-2040-96'        => [ '35', '40Х', '30ХМА', '25Х1МФ', '25Х2М1Ф', '20Х1М1Ф1ТР', '35Х', '08Х18Н10Т' ],

	// --- Крепёж: гайки общего назначения ---
	'gost-5915-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-5916-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-5927-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-5929-1970'        => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-10605-1994'       => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],
	'gost-10607-1994'       => [ 'Ст3', '20', '35', '45', '40Х', '09Г2С', '30ХМА', '12Х18Н10Т' ],

	// --- Крепёж: шайбы ---
	'gost-6402-1970'        => [ '65Г', 'Ст3', '30Х13', '12Х18Н10Т' ], // пружинные
	'gost-11371-1978'       => [ 'Ст3', '08кп', '20', '09Г2С', '12Х18Н10Т' ], // плоские усиленные

	// --- Крепёж: винт ---
	'gost-6958-1978'        => [ 'Ст3', '20', '35', '12Х18Н10Т' ],

	// --- Отводы спец. ---
	'sto-321-05'            => [ '12Х1МФ', '15Х1М1Ф', '15ГС', '12Х18Н10Т' ], // теплоустойчивые
	'sto-321-01'            => [ '15ГС', '16ГС', '09Г2С', '20', '17ГС' ],
	'ost-36-21-77'          => [ 'ВСт3сп', '20', '09Г2С' ],
	'sto-79814898-110-2009' => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т' ],

	// --- Переходы ---
	'ost-34-10-424-90'      => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С' ],
	'ost-34-10-417-90'      => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С' ],
	'ost-34-10-423-90'      => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С' ],
	'ost-34-42-664-84'      => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С' ],
	'gost-17378-2001'       => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С' ],

	// --- Заглушки ---
	'ost-24-125-22-89'      => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т' ],
	'ost-24-125-23-89'      => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т' ],

	// Отводы 17376 — уже variable, только синк родителя из вариаций.
	'%17376%'               => [],
];

/*
 * Авто-обогащение СДТ/фланцев/точёных/винтов по СЕМЕЙСТВУ норматива.
 * Списки берём из марок, которые каталог уже утверждает для этой категории
 * (без выдумывания). Норматив с теплоустойчивой/высоколегированной маркой
 * (12Х1МФ, 20Х3МВФ …) НЕ смешиваем с углеродом — своё семейство.
 */
$AUTO_CATS = [
	'troyniki', 'otvody', 'perekhody', 'zaglushki', 'dnishcha',
	'flancy-plosk', 'flancy-11', 'flancy-01', 'flancy-vorot', 'flancy',
	'tochenye', 'vinty',
];
$HEAT_MARKS = [ '12Х1МФ', '15Х1М1Ф', '20Х3МВФ', '20ХЗМВФ', '15Х5М', '12ХМ', '15ХМ' ];
$HEAT_LIST  = [ '12Х1МФ', '15Х1М1Ф', '20Х3МВФ', '15Х5М', '12ХМ', '15ХМ' ];
$CAT_DEFAULT = [
	'troyniki'     => [ '20', '09Г2С', '12Х18Н10Т', '13ХФА', '17Г1С', '08Х18Н10Т', '10', '10Г2С1' ],
	'otvody'       => [ '20', '09Г2С', '12Х18Н10Т', '13ХФА', '17Г1С', '08Х18Н10Т', '10', '10Г2С1' ],
	'perekhody'    => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '13ХФА', '17Г1С', '10Г2С1' ],
	'zaglushki'    => [ '20', '09Г2С', '12Х18Н10Т', '13ХФА', '17Г1С', '08Х18Н10Т' ],
	'dnishcha'     => [ '20', '09Г2С', '12Х18Н10Т', '13ХФА', '17Г1С', '08Х18Н10Т' ],
	'flancy-plosk' => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С', '13ХФА' ],
	'flancy-11'    => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С', '13ХФА' ],
	'flancy-01'    => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С', '13ХФА' ],
	'flancy-vorot' => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С', '13ХФА' ],
	'flancy'       => [ '20', '09Г2С', '12Х18Н10Т', '08Х18Н10Т', '10', '17Г1С', '13ХФА' ],
	'tochenye'     => [ '12Х18Н10Т', '09Г2С', '08Х18Н10Т', '10Г2', '15Х5М', '08Х18Н12Т', '12Х18Н12Т', '10Х17Н13М2Т' ],
	'vinty'        => [ 'Ст3', '20', '35', '40Х', '09Г2С', '12Х18Н10Т' ],
];

// Обработка $AUTO_CATS — per-product, ниже, после определения helper-функций.

function promen_enrich_ensure_steel_term( string $name ): ?WP_Term {
	$name = trim( $name );
	if ( $name === '' ) {
		return null;
	}
	$term = get_term_by( 'name', $name, 'pa_steel' );
	if ( $term && ! is_wp_error( $term ) ) {
		return $term;
	}
	$slug = sanitize_title( $name );
	$term = get_term_by( 'slug', $slug, 'pa_steel' );
	if ( $term && ! is_wp_error( $term ) ) {
		return $term;
	}
	$ins = wp_insert_term( $name, 'pa_steel', [ 'slug' => $slug ] );
	return is_wp_error( $ins ) ? null : get_term( (int) $ins['term_id'], 'pa_steel' );
}

function promen_enrich_ensure_sup_net(): ?WP_Term {
	$sup = get_term_by( 'slug', 'net', 'pa_supervised' );
	if ( $sup && ! is_wp_error( $sup ) ) {
		return $sup;
	}
	$ins = wp_insert_term( 'Нет', 'pa_supervised', [ 'slug' => 'net' ] );
	return is_wp_error( $ins ) ? null : get_term( (int) $ins['term_id'], 'pa_supervised' );
}

/** @param list<string> $steel_names */
function promen_enrich_set_parent_steels( WC_Product $product, array $steel_names ): void {
	$steel_names = array_values( array_unique( array_filter( array_map( 'trim', $steel_names ) ) ) );
	if ( count( $steel_names ) > 8 ) {
		$steel_names = array_slice( $steel_names, 0, 8 );
	}
	$ids = [];
	foreach ( $steel_names as $name ) {
		$t = promen_enrich_ensure_steel_term( $name );
		if ( $t ) {
			$ids[] = (int) $t->term_id;
		}
	}
	if ( ! $ids ) {
		return;
	}
	wp_set_object_terms( $product->get_id(), $ids, 'pa_steel', false );
	$attrs = $product->get_attributes();
	$attr  = new WC_Product_Attribute();
	$attr->set_id( wc_attribute_taxonomy_id_by_name( 'pa_steel' ) );
	$attr->set_name( 'pa_steel' );
	$attr->set_options( $ids );
	$attr->set_visible( true );
	$attr->set_variation( $product->is_type( 'variable' ) || count( $ids ) > 1 );
	$attrs['pa_steel'] = $attr;
	$product->set_attributes( $attrs );
	$product->save();
}

/** @param list<string> $steel_names */
function promen_enrich_expand_simple_to_variable( WC_Product_Simple $product, array $steel_names ): void {
	$steel_names = array_values( array_unique( array_filter( array_map( 'trim', $steel_names ) ) ) );
	if ( count( $steel_names ) > 8 ) {
		$steel_names = array_slice( $steel_names, 0, 8 );
	}
	if ( count( $steel_names ) < 2 ) {
		promen_enrich_set_parent_steels( $product, $steel_names );
		return;
	}

	$parent_id = $product->get_id();
	$sku       = (string) $product->get_sku();
	wp_set_object_terms( $parent_id, 'variable', 'product_type' );
	$variable = new WC_Product_Variable( $parent_id );
	promen_enrich_set_parent_steels( $variable, $steel_names );

	$sup = promen_enrich_ensure_sup_net();
	if ( $sup ) {
		$attrs = $variable->get_attributes();
		$attr  = new WC_Product_Attribute();
		$attr->set_id( wc_attribute_taxonomy_id_by_name( 'pa_supervised' ) );
		$attr->set_name( 'pa_supervised' );
		$attr->set_options( [ (int) $sup->term_id ] );
		$attr->set_visible( true );
		$attr->set_variation( true );
		$attrs['pa_supervised'] = $attr;
		$variable->set_attributes( $attrs );
		$variable->save();
		wp_set_object_terms( $parent_id, [ (int) $sup->term_id ], 'pa_supervised', false );
	}

	foreach ( $steel_names as $name ) {
		$st = promen_enrich_ensure_steel_term( $name );
		if ( ! $st ) {
			continue;
		}
		$vsku = $sku !== '' ? ( $sku . '-' . $st->slug ) : '';
		if ( $vsku && wc_get_product_id_by_sku( $vsku ) ) {
			continue;
		}
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent_id );
		$variation->set_status( 'publish' );
		$variation->set_regular_price( '0' );
		if ( $vsku ) {
			$variation->set_sku( $vsku );
		}
		$variation->set_attributes(
			array_filter(
				[
					'pa_steel'      => $st->slug,
					'pa_supervised' => $sup ? $sup->slug : '',
				]
			)
		);
		$variation->save();
	}
	WC_Product_Variable::sync( $parent_id );
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $parent_id );
	}
}

global $wpdb;
$synced = 0;
$expanded = 0;

foreach ( $NORM_STEELS as $like => $wanted ) {
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT product_id FROM {$wpdb->prefix}promen_catalog_rows WHERE norm_slug LIKE %s",
			$like
		)
	);
	echo "norm {$like} products=" . count( $ids ) . "\n";
	foreach ( $ids as $pid ) {
		$product = wc_get_product( (int) $pid );
		if ( ! $product ) {
			continue;
		}

		// Sync parent terms from variation map.
		if ( $product->is_type( 'variable' ) ) {
			$map = promen_get_variation_map( $product );
			$parent_n = count( wc_get_product_terms( $product->get_id(), 'pa_steel' ) );
			if ( count( $map ) > $parent_n ) {
				$names = [];
				foreach ( array_keys( $map ) as $slug ) {
					if ( $slug === '' ) {
						continue;
					}
					$t = get_term_by( 'slug', $slug, 'pa_steel' );
					$names[] = $t && ! is_wp_error( $t ) ? $t->name : $slug;
				}
				if ( $wanted ) {
					$names = array_values( array_unique( array_merge( $names, $wanted ) ) );
				}
				if ( count( $names ) > 8 ) {
					$names = array_slice( $names, 0, 8 );
				}
				promen_enrich_set_parent_steels( $product, $names );
				$sup = promen_enrich_ensure_sup_net();
				$map2 = promen_get_variation_map( $product );
				$sku  = (string) $product->get_sku();
				foreach ( $names as $name ) {
					$st = promen_enrich_ensure_steel_term( $name );
					if ( ! $st || isset( $map2[ $st->slug ] ) ) {
						continue;
					}
					$vsku = $sku !== '' ? ( $sku . '-' . $st->slug ) : '';
					if ( $vsku && wc_get_product_id_by_sku( $vsku ) ) {
						continue;
					}
					$variation = new WC_Product_Variation();
					$variation->set_parent_id( $product->get_id() );
					$variation->set_status( 'publish' );
					$variation->set_regular_price( '0' );
					if ( $vsku ) {
						$variation->set_sku( $vsku );
					}
					$variation->set_attributes(
						array_filter(
							[
								'pa_steel'      => $st->slug,
								'pa_supervised' => $sup ? $sup->slug : '',
							]
						)
					);
					$variation->save();
				}
				WC_Product_Variable::sync( $product->get_id() );
				if ( function_exists( 'promen_catalog_upsert' ) ) {
					promen_catalog_upsert( $product->get_id() );
				}
				$synced++;
			} elseif ( $wanted ) {
				$have = wp_get_object_terms( $product->get_id(), 'pa_steel', [ 'fields' => 'names' ] );
				if ( is_wp_error( $have ) ) {
					$have = [];
				}
				$merged = array_values( array_unique( array_merge( $have, $wanted ) ) );
				if ( count( $merged ) > 8 ) {
					$merged = array_slice( $merged, 0, 8 );
				}
				sort( $have );
				$chk = $merged;
				sort( $chk );
				if ( $have !== $chk ) {
					promen_enrich_set_parent_steels( $product, $merged );
					$sup  = promen_enrich_ensure_sup_net();
					$map2 = promen_get_variation_map( $product );
					$sku  = (string) $product->get_sku();
					foreach ( $merged as $name ) {
						$st = promen_enrich_ensure_steel_term( $name );
						if ( ! $st || isset( $map2[ $st->slug ] ) ) {
							continue;
						}
						$vsku = $sku !== '' ? ( $sku . '-' . $st->slug ) : '';
						if ( $vsku && wc_get_product_id_by_sku( $vsku ) ) {
							continue;
						}
						$variation = new WC_Product_Variation();
						$variation->set_parent_id( $product->get_id() );
						$variation->set_status( 'publish' );
						$variation->set_regular_price( '0' );
						if ( $vsku ) {
							$variation->set_sku( $vsku );
						}
						$variation->set_attributes(
							array_filter(
								[
									'pa_steel'      => $st->slug,
									'pa_supervised' => $sup ? $sup->slug : '',
								]
							)
						);
						$variation->save();
					}
					WC_Product_Variable::sync( $product->get_id() );
					if ( function_exists( 'promen_catalog_upsert' ) ) {
						promen_catalog_upsert( $product->get_id() );
					}
					$expanded++;
				}
			}
			continue;
		}

		if ( ! $wanted || ! $product->is_type( 'simple' ) ) {
			continue;
		}
		$have = wp_get_object_terms( $product->get_id(), 'pa_steel', [ 'fields' => 'names' ] );
		if ( is_wp_error( $have ) ) {
			$have = [];
		}
		if ( ! $have ) {
			$dims = function_exists( 'promen_get_dims' ) ? promen_get_dims( (int) $pid ) : [];
			if ( ! empty( $dims['material_grade'] ) ) {
				$have[] = (string) $dims['material_grade'];
			}
		}
		$merged = array_values( array_unique( array_merge( $have, $wanted ) ) );
		if ( count( $merged ) > 8 ) {
			$merged = array_slice( $merged, 0, 8 );
		}
		if ( count( $merged ) < 2 ) {
			continue;
		}
		sort( $have );
		$chk = $merged;
		sort( $chk );
		if ( $have === $chk ) {
			continue;
		}
		promen_enrich_expand_simple_to_variable( $product, $merged );
		$expanded++;
		if ( $expanded % 25 === 0 ) {
			echo "  expanded={$expanded}\n";
		}
	}
}

echo "synced_parent_steels={$synced} expanded_norms={$expanded}\n";

/*
 * === Per-product обогащение СДТ/фланцев/точёных/винтов ===
 * Семейство определяем по МАРКЕ каждого товара (нормативы бывают смешанные:
 * в ГОСТ 22793 есть и 20/09Г2С, и 20Х3МВФ). Теплоустойчивые/высоколегированные
 * получают своё семейство, остальные — категорийный микс (углерод+нерж),
 * как каталог уже делает для sto-95-127 и т.п.
 */
$in_cats  = implode( ',', array_map( fn( $c ) => "'" . esc_sql( $c ) . "'", $AUTO_CATS ) );
$auto_rows = $wpdb->get_results(
	"SELECT r.product_id AS pid, r.category AS cat
	 FROM {$wpdb->prefix}promen_catalog_rows r
	 WHERE r.category IN ({$in_cats})
	   AND ( r.steels_json IS NULL OR r.steels_json NOT LIKE '%,%' )"
);
echo 'auto_candidates=' . count( $auto_rows ) . "\n";
$auto_expanded = 0;
foreach ( $auto_rows as $ar ) {
	$product = wc_get_product( (int) $ar->pid );
	if ( ! $product || ! $product->is_type( 'simple' ) ) {
		continue;
	}
	$have = wp_get_object_terms( (int) $ar->pid, 'pa_steel', [ 'fields' => 'names' ] );
	if ( is_wp_error( $have ) ) {
		$have = [];
	}
	if ( ! $have ) {
		$dims = function_exists( 'promen_get_dims' ) ? promen_get_dims( (int) $ar->pid ) : [];
		if ( ! empty( $dims['material_grade'] ) ) {
			$have[] = (string) $dims['material_grade'];
		}
	}
	$is_heat = (bool) array_intersect( $have, $HEAT_MARKS );
	$base    = $is_heat ? $HEAT_LIST : ( $CAT_DEFAULT[ $ar->cat ] ?? [] );
	if ( ! $base ) {
		continue;
	}
	$merged = array_values( array_unique( array_merge( $have, $base ) ) );
	if ( count( $merged ) > 8 ) {
		$merged = array_slice( $merged, 0, 8 );
	}
	if ( count( $merged ) < 2 ) {
		continue;
	}
	sort( $have );
	$chk = $merged;
	sort( $chk );
	if ( $have === $chk ) {
		continue;
	}
	promen_enrich_expand_simple_to_variable( $product, $merged );
	$auto_expanded++;
	if ( $auto_expanded % 50 === 0 ) {
		echo "  auto_expanded={$auto_expanded}\n";
	}
}
echo "auto_expanded={$auto_expanded}\n";
