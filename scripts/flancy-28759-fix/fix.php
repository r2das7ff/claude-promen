<?php
/**
 * Фланцы сосудов и аппаратов ГОСТ 28759.2-2022: геометрия из таблицы 1.
 *
 * При импорте колонки таблицы съехали у всех 56 позиций:
 *   «DN» в артикуле и адресе — это D1 (наружный диаметр), а не D;
 *   d_inner — это D3, bolt_circle_d — D4, flange_thickness — a1,
 *   bolt_d — диаметр отверстия (23) вместо шпильки (M20),
 *   вес — число шпилек (20…68 «кг» у фланцев от 0,4 до 3,2 м).
 *
 * Источник истины — таблица 1 «Размеры фланцев» самого стандарта
 * (normatives/Фланцы/ГОСТ 28759-2022.pdf, текстовый слой, 96 строк),
 * разобранная в table1.json. Товар находится в таблице по числу из
 * артикула (это D, D1 или D2) и давлению.
 *
 * Правит: размеры, заголовок, слаг (с давлением — иначе три исполнения
 * одного D столкнутся), вес (очищается: массы в таблице нет, а число
 * шпилек под видом массы кормило калькулятор доставки).
 *
 * Запуск:  wp eval-file scripts/flancy-28759-fix/fix.php dry
 *          wp eval-file scripts/flancy-28759-fix/fix.php apply
 */

$mode  = ( isset( $args[0] ) && 'apply' === $args[0] ) ? 'apply' : 'dry';
$table = json_decode( (string) file_get_contents( __DIR__ . '/table1.json' ), true );
if ( ! is_array( $table ) || ! $table ) {
	echo "Нет table1.json рядом со скриптом — остановка.\n";
	return;
}

// «1350 PN3»: 1350 в таблице — D1 только при 1,0 и 1,6 МПа (56 шпилек),
// а у товара 0,3 МПа и 44 шпильки — это D=1200 на 0,3 МПа (D1 там 1330).
// Давление и число шпилек согласны друг с другом против опечатки в D1.
$overrides = [ 'гост-287592-2022-1350-pn3-фп' => [ 'D' => 1200, 'PN' => 0.3 ] ];

$ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'meta_key'       => '_promen_norm_key',
		'meta_value'     => 'ГОСТ 28759.2-2022',
	]
);
printf( "Режим: %s. Позиций ГОСТ 28759.2: %d\n\n", $mode, count( $ids ) );

$fmt = static function ( float $v ): string {
	return rtrim( rtrim( number_format( $v, 2, '.', '' ), '0' ), '.' );
};

$moves = [];
$done  = 0;
$fail  = [];
foreach ( $ids as $id ) {
	$sku = (string) get_post_meta( $id, '_sku', true );
	if ( ! preg_match( '/-(\d{3,4})-pn(\d+)/u', $sku, $m ) ) {
		$fail[] = $sku;
		continue;
	}
	$num    = (int) $m[1];
	$pn_kgs = (int) $m[2];
	$pn_mpa = $pn_kgs / 10;

	$row = null;
	if ( isset( $overrides[ $sku ] ) ) {
		foreach ( $table as $t ) {
			if ( (int) $t['D'] === $overrides[ $sku ]['D'] && abs( (float) $t['PN'] - $overrides[ $sku ]['PN'] ) < 1e-6 ) {
				$row = $t;
				break;
			}
		}
	} else {
		foreach ( [ 'D', 'D1', 'D2' ] as $col ) {
			foreach ( $table as $t ) {
				if ( (float) $t[ $col ] === (float) $num && abs( (float) $t['PN'] - $pn_mpa ) < 1e-6 ) {
					$row = $t;
					break 2;
				}
			}
		}
	}
	if ( ! $row ) {
		$fail[] = $sku;
		continue;
	}

	$post      = get_post( $id );
	$dims      = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
	$d         = (int) $row['D'];
	$url_old   = (string) get_permalink( $id );
	$title_new = sprintf( 'Фланец ФП DN%d PN%d ГОСТ 28759.2-2022', $d, $pn_kgs );
	$slug_new  = sprintf( 'flanec-fp-dn%d-pn%d-gost-28759-2-2022', $d, $pn_kgs );

	$dims_new = array_merge(
		$dims,
		[
			'dn'               => (string) $d,
			'd_inner'          => (string) $d,
			'outer_diameter'   => $fmt( (float) $row['D1'] ),
			'bolt_circle_d'    => $fmt( (float) $row['D2'] ),
			'flange_thickness' => $fmt( (float) $row['b'] ),
			'stud_count'       => (string) (int) $row['bolt_n'],
			'bolt_d'           => $fmt( (float) $row['bolt_d'] ),
			'pn_mpa'           => $fmt( (float) $row['PN'] ),
			'pn'               => $pn_kgs,
		]
	);

	if ( $done < 3 ) {
		printf(
			"  %s\n    было:  %s | Dн %s, окр. %s, b %s, %s×M%s, вес %s\n    стало: %s | Dн %s, окр. %s, b %s, %s×M%s, вес —\n",
			$sku, $post->post_title,
			$dims['outer_diameter'] ?? '—', $dims['bolt_circle_d'] ?? '—', $dims['flange_thickness'] ?? '—',
			$dims['stud_count'] ?? '—', $dims['bolt_d'] ?? '—', get_post_meta( $id, '_weight', true ) ?: '—',
			$title_new, $dims_new['outer_diameter'], $dims_new['bolt_circle_d'], $dims_new['flange_thickness'],
			$dims_new['stud_count'], $dims_new['bolt_d']
		);
	}

	if ( 'apply' === $mode ) {
		wp_update_post( [ 'ID' => $id, 'post_title' => $title_new, 'post_name' => $slug_new ] );
		update_post_meta( $id, '_promen_dims', wp_json_encode( $dims_new, JSON_UNESCAPED_UNICODE ) );
		delete_post_meta( $id, '_weight' );
		clean_post_cache( $id );
		$url_new = (string) get_permalink( $id );
		promen_catalog_upsert( $id, false );
	} else {
		$url_new = str_replace( '/' . $post->post_name . '/', '/' . $slug_new . '/', $url_old );
	}
	if ( $url_new !== $url_old ) {
		$moves[] = [ $url_old, $url_new ];
	}
	$done++;
}

$out = '';
foreach ( $moves as [ $from, $to ] ) {
	$out .= wp_parse_url( $from, PHP_URL_PATH ) . "\t" . wp_parse_url( $to, PHP_URL_PATH ) . "\n";
}
file_put_contents( __DIR__ . '/moves.tsv', $out );

printf( "\nИсправлено: %d, не сопоставлено: %d%s\nПереездов: %d\n", $done, count( $fail ), $fail ? ' (' . implode( ', ', $fail ) . ')' : '', count( $moves ) );
if ( 'apply' === $mode && function_exists( 'promen_filters_cache_bump' ) ) {
	promen_filters_cache_bump();
}
