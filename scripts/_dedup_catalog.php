<?php
/**
 * Дедуп каталога: схлопывает позиции, задублированные по марке стали, в ОДИН
 * вариативный товар (union марок как вариации). Плюс канонизация норм-дрейфа
 * (gost-r-…, -YYYY варианты). Плюс транслит-ЧПУ для товаров с product-N.
 *
 * DRY-RUN по умолчанию. Применение: PROMEN_DEDUP_APPLY=1 wp eval-file … (через env контейнера).
 * Ключ группировки: category | base_norm | payload.title (title НЕ содержит марку).
 */

defined( 'ABSPATH' ) || exit;

$APPLY = getenv( 'PROMEN_DEDUP_APPLY' ) === '1';
echo $APPLY ? "=== РЕЖИМ: APPLY (запись) ===\n" : "=== РЕЖИМ: DRY-RUN (только отчёт) ===\n";

global $wpdb;
$table = $wpdb->prefix . 'promen_catalog_rows';

/** База норматива: убрать gost-r- и хвостовой год. */
function dd_base_norm( string $slug ): string {
	$s = preg_replace( '/^gost-r-/', 'gost-', $slug );
	$s = preg_replace( '/-(19|20)\d{2}$/', '', (string) $s );
	return (string) $s;
}

/** Канонический слаг норматива для выжившего: без gost-r-, предпочтительно с годом. */
function dd_canon_norm( array $slugs ): string {
	$clean = array_map( fn( $s ) => preg_replace( '/^gost-r-/', 'gost-', $s ), $slugs );
	$clean = array_values( array_unique( $clean ) );
	// Предпочитаем вариант с годом (более точный).
	usort( $clean, function ( $a, $b ) {
		$ay = preg_match( '/-(19|20)\d{2}$/', $a ) ? 1 : 0;
		$by = preg_match( '/-(19|20)\d{2}$/', $b ) ? 1 : 0;
		if ( $ay !== $by ) { return $by <=> $ay; }
		return strlen( $b ) <=> strlen( $a );
	} );
	return $clean[0];
}

/** Транслитерация RU→lat для ЧПУ. */
function dd_translit( string $t ): string {
	$map = [
		'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i',
		'й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t',
		'у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'',
		'э'=>'e','ю'=>'yu','я'=>'ya','×'=>'h','°'=>'',
	];
	$t = mb_strtolower( trim( $t ), 'UTF-8' );
	$t = strtr( $t, $map );
	$t = preg_replace( '/[^a-z0-9]+/u', '-', $t );
	$t = trim( (string) $t, '-' );
	return $t !== '' ? $t : '';
}

/** Уникальный slug в пределах post_type=product. */
function dd_unique_slug( string $base, int $pid ): string {
	global $wpdb;
	if ( $base === '' ) { $base = 'pozitsiya'; }
	$base = mb_substr( $base, 0, 180 );
	$slug = $base;
	$i    = 2;
	while ( true ) {
		$exists = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type IN ('product','product_variation') AND ID<>%d LIMIT 1",
			$slug, $pid
		) );
		if ( ! $exists ) { return $slug; }
		$slug = $base . '-' . $i;
		$i++;
	}
}

/** pa_steel термин по имени (создать при отсутствии). */
function dd_steel_term( string $name ): ?WP_Term {
	$name = trim( $name );
	if ( $name === '' ) { return null; }
	$t = get_term_by( 'name', $name, 'pa_steel' ) ?: get_term_by( 'slug', sanitize_title( $name ), 'pa_steel' );
	if ( $t && ! is_wp_error( $t ) ) { return $t; }
	$ins = wp_insert_term( $name, 'pa_steel', [ 'slug' => sanitize_title( $name ) ] );
	return is_wp_error( $ins ) ? null : get_term( (int) $ins['term_id'], 'pa_steel' );
}

/** Сделать товар variable c union марок как вариациями (идемпотентно). */
function dd_ensure_variable( int $pid, array $steel_names ): void {
	$product = wc_get_product( $pid );
	if ( ! $product ) { return; }
	$steel_names = array_values( array_unique( array_filter( array_map( 'trim', $steel_names ) ) ) );
	if ( ! $steel_names ) { return; }

	$ids = [];
	foreach ( $steel_names as $n ) {
		$t = dd_steel_term( $n );
		if ( $t ) { $ids[] = (int) $t->term_id; }
	}
	if ( ! $ids ) { return; }

	if ( ! $product->is_type( 'variable' ) ) {
		wp_set_object_terms( $pid, 'variable', 'product_type' );
		$product = new WC_Product_Variable( $pid );
	}
	wp_set_object_terms( $pid, $ids, 'pa_steel', false );

	$sup = get_term_by( 'slug', 'net', 'pa_supervised' );
	if ( ! $sup ) {
		$ins = wp_insert_term( 'Нет', 'pa_supervised', [ 'slug' => 'net' ] );
		$sup = is_wp_error( $ins ) ? null : get_term( (int) $ins['term_id'], 'pa_supervised' );
	}

	$attrs = $product->get_attributes();
	$a1 = new WC_Product_Attribute();
	$a1->set_id( wc_attribute_taxonomy_id_by_name( 'pa_steel' ) );
	$a1->set_name( 'pa_steel' );
	$a1->set_options( $ids );
	$a1->set_visible( true );
	$a1->set_variation( true );
	$attrs['pa_steel'] = $a1;
	if ( $sup ) {
		$a2 = new WC_Product_Attribute();
		$a2->set_id( wc_attribute_taxonomy_id_by_name( 'pa_supervised' ) );
		$a2->set_name( 'pa_supervised' );
		$a2->set_options( [ (int) $sup->term_id ] );
		$a2->set_visible( true );
		$a2->set_variation( true );
		$attrs['pa_supervised'] = $a2;
		wp_set_object_terms( $pid, [ (int) $sup->term_id ], 'pa_supervised', false );
	}
	$product->set_attributes( $attrs );
	$product->save();

	$existing = function_exists( 'promen_get_variation_map' ) ? promen_get_variation_map( $product ) : [];
	$sku = (string) $product->get_sku();
	foreach ( $steel_names as $n ) {
		$t = dd_steel_term( $n );
		if ( ! $t || isset( $existing[ $t->slug ] ) ) { continue; }
		$vsku = $sku !== '' ? $sku . '-' . $t->slug : '';
		if ( $vsku && wc_get_product_id_by_sku( $vsku ) ) { continue; }
		$v = new WC_Product_Variation();
		$v->set_parent_id( $pid );
		$v->set_status( 'publish' );
		$v->set_regular_price( '0' );
		if ( $vsku ) { $v->set_sku( $vsku ); }
		$v->set_attributes( array_filter( [ 'pa_steel' => $t->slug, 'pa_supervised' => $sup ? $sup->slug : '' ] ) );
		$v->save();
	}
	WC_Product_Variable::sync( $pid );
}

// ── Сбор групп ─────────────────────────────────────────────
$rows = $wpdb->get_results(
	"SELECT product_id AS pid, category AS cat, norm_slug AS ns,
	        JSON_UNQUOTE(JSON_EXTRACT(payload,'$.title')) AS title
	 FROM {$table}"
);
$groups = [];
foreach ( $rows as $r ) {
	$title = trim( (string) $r->title );
	if ( $title === '' || $title === 'null' ) { continue; } // без заголовка не группируем
	$key = $r->cat . '|' . dd_base_norm( (string) $r->ns ) . '|' . $title;
	$groups[ $key ][] = $r;
}

$stat_del = 0; $stat_merged = 0; $per_cat = []; $examples = []; $del_ids = []; $redir = [];
$slug_fixed = 0;

foreach ( $groups as $key => $members ) {
	usort( $members, fn( $a, $b ) => (int) $a->pid <=> (int) $b->pid );
	$survivor = (int) $members[0]->pid;
	$cat      = $members[0]->cat;
	$title    = trim( (string) $members[0]->title );
	$dupes    = count( $members ) - 1;

	// union марок по всей группе
	$steels = [];
	foreach ( $members as $m ) {
		$names = wp_get_object_terms( (int) $m->pid, 'pa_steel', [ 'fields' => 'names' ] );
		if ( ! is_wp_error( $names ) ) { foreach ( $names as $n ) { $steels[ $n ] = 1; } }
	}
	$steel_names = array_keys( $steels );
	$canon_norm  = dd_canon_norm( array_map( fn( $m ) => (string) $m->ns, $members ) );

	if ( $dupes > 0 ) {
		$per_cat[ $cat ] = ( $per_cat[ $cat ] ?? 0 ) + $dupes;
		if ( count( $examples ) < 12 ) {
			$examples[] = sprintf( '[%s] "%s" ×%d → survivor %d, марок union=%d, norm=%s',
				$cat, mb_substr( $title, 0, 40 ), count( $members ), $survivor, count( $steel_names ), $canon_norm );
		}
	}

	// Транслит-ЧПУ выжившего (даже одиночки с product-N)
	$sname = get_post_field( 'post_name', $survivor );
	$need_slug = ( strpos( (string) $sname, 'product' ) === 0 );

	if ( ! $APPLY ) {
		if ( $need_slug ) { $slug_fixed++; }
		continue;
	}

	// Одиночка без дублей: только чиним product-N slug (без тяжёлой перезаписи вариаций).
	if ( $dupes === 0 ) {
		if ( $need_slug ) {
			$upd = [ 'ID' => $survivor ];
			if ( get_post_field( 'post_title', $survivor ) === '' ) { $upd['post_title'] = $title; }
			$slug_src = dd_translit( $title . ' ' . str_replace( [ 'gost-', 'ost-', 'sto-' ], [ 'gost ', 'ost ', 'sto ' ], $canon_norm ) );
			$upd['post_name'] = dd_unique_slug( $slug_src, $survivor );
			wp_update_post( $upd );
			$slug_fixed++;
			if ( $slug_fixed % 100 === 0 ) { echo "  slug_fixed={$slug_fixed}\n"; }
		}
		continue;
	}

	// APPLY (дубль-группа): канон-норм на выжившего
	if ( $canon_norm !== '' ) {
		$nt = get_term_by( 'slug', $canon_norm, 'norm' );
		if ( $nt && ! is_wp_error( $nt ) ) { wp_set_object_terms( $survivor, [ (int) $nt->term_id ], 'norm', false ); }
		update_post_meta( $survivor, '_promen_norm_key', $canon_norm );
		$wpdb->update( $table, [ 'norm_slug' => $canon_norm ], [ 'product_id' => $survivor ] );
	}

	// union марок в выжившего
	if ( count( $steel_names ) >= 2 ) { dd_ensure_variable( $survivor, $steel_names ); }

	// заголовок + ЧПУ выжившего
	$upd = [ 'ID' => $survivor ];
	if ( get_post_field( 'post_title', $survivor ) === '' ) { $upd['post_title'] = $title; }
	if ( $need_slug ) {
		$slug_src = dd_translit( $title . ' ' . str_replace( [ 'gost-', 'ost-', 'sto-' ], [ 'gost ', 'ost ', 'sto ' ], $canon_norm ) );
		$upd['post_name'] = dd_unique_slug( $slug_src, $survivor );
		$slug_fixed++;
	}
	if ( count( $upd ) > 1 ) { wp_update_post( $upd ); }

	// удалить дубли
	foreach ( array_slice( $members, 1 ) as $m ) {
		$pid = (int) $m->pid;
		$old = get_post_field( 'post_name', $pid );
		if ( $old ) { $redir[ $old ] = $survivor; }
		foreach ( get_children( [ 'post_parent' => $pid, 'post_type' => 'product_variation', 'numberposts' => -1, 'post_status' => 'any' ] ) as $v ) {
			wp_delete_post( $v->ID, true );
		}
		$wpdb->delete( $table, [ 'product_id' => $pid ] );
		$del_ids[] = $pid;
		wp_delete_post( $pid, true );
		$stat_del++;
	}
	if ( function_exists( 'promen_catalog_upsert' ) ) { promen_catalog_upsert( $survivor ); }
	$stat_merged++;
	if ( $stat_merged % 100 === 0 ) { echo "  merged={$stat_merged} deleted={$stat_del}\n"; }
}

echo "\n--- Итог по категориям (избыточных дублей) ---\n";
arsort( $per_cat );
foreach ( $per_cat as $c => $n ) { echo sprintf( "  %-14s %d\n", $c, $n ); }
echo 'ВСЕГО избыточных: ' . array_sum( $per_cat ) . "\n";
echo "product-N slug к правке: {$slug_fixed}\n\n";
echo "--- Примеры групп ---\n";
foreach ( $examples as $e ) { echo "  $e\n"; }

if ( $APPLY ) {
	// сохранить карту редиректов
	$old = get_option( 'promen_dedup_redirects', [] );
	if ( ! is_array( $old ) ) { $old = []; }
	update_option( 'promen_dedup_redirects', $old + $redir, false );
	// почистить Meili от удалённых
	if ( $del_ids && function_exists( 'promen_meili_index' ) && function_exists( 'promen_meili_request' ) ) {
		foreach ( array_chunk( $del_ids, 500 ) as $chunk ) {
			promen_meili_request( 'POST', '/indexes/' . promen_meili_index() . '/documents/delete-batch', array_map( 'intval', $chunk ) );
		}
	}
	echo "\nПРИМЕНЕНО: merged={$stat_merged} deleted={$stat_del} redirects=" . count( $redir ) . "\n";
} else {
	echo "\nDRY-RUN: изменений нет. Для применения — PROMEN_DEDUP_APPLY=1.\n";
}
