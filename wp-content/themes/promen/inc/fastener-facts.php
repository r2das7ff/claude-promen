<?php
/**
 * Описание карточки крепежа — текст из фактов самой позиции и стандартов.
 *
 * Зачем. Яндекс снимал карточки болтов как «малоценные» (59 из 64 снятий
 * нового каталога за июль–сентябрь 2026): соседние болты совпадали на 97,9% —
 * таблица серии дважды, база знаний раздела целиком, а своего текста у
 * позиции не было ни строчки. Здесь — абзацы, которые у M16×60 и M20×60
 * действительно разные: резьба и её шаг, класс прочности с пределами,
 * на каких фланцах стоит эта резьба, с какими гайками и шайбами идёт.
 *
 * Источники:
 * - названия стандартов — реестр нормативов (inc/data/fastener-facts.php);
 * - фланцы — таблица 6 ГОСТ 33259-2015, ряд 1; ограничение болтов — п. 7.9.5;
 * - крупный шаг — ГОСТ 24705-2004 (ГОСТ 8724-2002);
 * - класс прочности — ГОСТ ISO 898-1-2014, пары с гайками — ГОСТ ISO 898-2-2015.
 *
 * Чего нет в данных — того нет и в тексте: у ГОСТ 15590/15591 в каталоге
 * «шпильки», хотя по стандарту это болты, а у 15591 вместо резьбы мусор
 * импорта (M8.458) — им описание не собираем, пока данные не исправлены.
 */

defined( 'ABSPATH' ) || exit;

/** Данные генератора: названия стандартов и резьбы фланцев. */
function promen_fastener_facts_data(): array {
	static $data = null;
	if ( null === $data ) {
		$file = __DIR__ . '/data/fastener-facts.php';
		$data = is_readable( $file ) ? (array) require $file : [];
	}
	return $data;
}

/** «ГОСТ 7798-70» → «ГОСТ 7798», «ОСТ 26-2040-96» → «ОСТ 26-2040». */
function promen_fastener_norm_base( string $norm_key ): string {
	return trim( (string) preg_replace( '/-(\d{2}|\d{4})$/', '', trim( $norm_key ) ) );
}

/** Крупный шаг метрической резьбы, мм (ГОСТ 24705-2004). null — нет в ряду. */
function promen_thread_coarse_pitch( float $d ): ?float {
	static $p = [
		'1' => 0.25, '1.2' => 0.25, '1.4' => 0.3, '1.6' => 0.35, '2' => 0.4, '2.5' => 0.45, '3' => 0.5,
		'3.5' => 0.6, '4' => 0.7, '5' => 0.8, '6' => 1, '7' => 1, '8' => 1.25, '10' => 1.5, '12' => 1.75,
		'14' => 2, '16' => 2, '18' => 2.5, '20' => 2.5, '22' => 2.5, '24' => 3, '27' => 3, '30' => 3.5,
		'33' => 3.5, '36' => 4, '39' => 4, '42' => 4.5, '45' => 4.5, '48' => 5,
	];
	return $p[ promen_fmt_dim( (string) $d ) ] ?? null;
}

/** Мелкий шаг для примера обозначения (самый ходовой из ГОСТ 8724). */
function promen_thread_fine_pitch( float $d ): ?float {
	static $p = [
		'3' => 0.35, '4' => 0.5, '5' => 0.5, '6' => 0.75, '8' => 1, '10' => 1.25, '12' => 1.25, '14' => 1.5, '16' => 1.5,
		'18' => 1.5, '20' => 1.5, '22' => 1.5, '24' => 2, '27' => 2, '30' => 2, '33' => 2, '36' => 3, '39' => 3,
		'42' => 3, '45' => 3, '48' => 3,
	];
	return $p[ promen_fmt_dim( (string) $d ) ] ?? null;
}

/** Высота гайки ГОСТ 5915-70, мм. */
function promen_nut_height_5915( float $d ): ?float {
	static $m = [
		'6' => 5, '8' => 6.5, '10' => 8, '12' => 10, '14' => 11, '16' => 13, '18' => 15, '20' => 16, '22' => 18,
		'24' => 19, '27' => 22, '30' => 24, '36' => 29, '42' => 34, '48' => 38,
	];
	return $m[ promen_fmt_dim( (string) $d ) ] ?? null;
}

/**
 * Что зависит от длины: длина резьбы b болта ГОСТ 7798/7805 (2d+6 при l ≤ 125,
 * 2d+12 до 200, 2d+25 длиннее) и толщина пакета под гайку ГОСТ 5915 с выходом
 * конца на два шага. Шпилька ГОСТ 22043 — две гайки.
 */
function promen_fastener_length_text( string $base, float $d, float $l ): string {
	$pitch = promen_thread_coarse_pitch( $d );
	$nut   = promen_nut_height_5915( $d );
	if ( ! $pitch || ! $nut || $l <= 0 ) {
		return '';
	}
	$txt = '';
	if ( in_array( $base, [ 'ГОСТ 7798', 'ГОСТ 7805' ], true ) ) {
		$b = 2 * $d + ( $l <= 125 ? 6 : ( $l <= 200 ? 12 : 25 ) );
		if ( $l - $b <= 2 * $pitch ) {
			$txt = 'При длине ' . promen_fx_num( $l ) . ' мм резьба идёт практически по всему стержню (расчётная длина резьбы b = ' . promen_fx_num( $b ) . ' мм не меньше длины болта).';
		} else {
			$txt = 'Длина резьбы для этой длины — b = ' . promen_fx_num( $b ) . ' мм (2d + ' . promen_fx_num( $b - 2 * $d ) . '), под головкой остаётся гладкая часть стержня около ' . promen_fx_num( $l - $b ) . ' мм.';
		}
		$grip = $l - $nut - 2 * $pitch;
	} elseif ( 'ГОСТ 22043' === $base ) {
		$grip = $l - 2 * ( $nut + 2 * $pitch );
	} else {
		return '';
	}
	if ( $grip >= 2 ) {
		$txt .= ( $txt !== '' ? ' ' : '' ) . 'С ' . ( 'ГОСТ 22043' === $base ? 'двумя гайками' : 'гайкой' ) . ' ГОСТ 5915 (высота ' . promen_fx_num( $nut ) . ' мм) и выходом конца на два шага резьбы '
			. ( 'ГОСТ 22043' === $base ? 'шпилька' : 'болт' ) . ' стягивает пакет деталей толщиной до ~' . promen_fx_num( floor( $grip ) ) . ' мм; каждая шайба забирает свою толщину из этого запаса.';
	}
	return $txt;
}

/** Число по-русски: 2.5 → «2,5». */
function promen_fx_num( $v ): string {
	return str_replace( '.', ',', promen_fmt_dim( (string) $v ) );
}

/**
 * Где стоит резьба M во фланцах ГОСТ 33259 (тип 11, ряд 1):
 * группы PN с одинаковым набором DN → «PN 10, 16 — DN 32–125».
 */
function promen_fastener_flange_usage( float $m, float $max_pn = 0 ): array {
	$use = promen_fastener_facts_data()['flange_usage'][ (int) $m ] ?? [];
	if ( ! $use || (float) (int) $m !== $m ) {
		return [];
	}
	static $dn_row = [ 10, 15, 20, 25, 32, 40, 50, 65, 80, 100, 125, 150, 200, 250, 300, 350, 400, 450, 500, 600, 700, 800, 900, 1000, 1200, 1400, 1600 ];
	$groups = [];
	foreach ( $use as $pn => $dns ) {
		if ( $max_pn > 0 && (float) $pn > $max_pn ) {
			continue;
		}
		$groups[ implode( ',', $dns ) ][] = (string) $pn;
	}
	$out = [];
	foreach ( $groups as $dns => $pns ) {
		$dns = array_map( 'intval', explode( ',', $dns ) );
		// Подряд идущие по ряду DN — диапазоном, иначе перечнем.
		$idx  = array_map( fn( $d ) => array_search( $d, $dn_row, true ), $dns );
		$cont = count( $dns ) > 2 && ! in_array( false, $idx, true ) && ( end( $idx ) - $idx[0] ) === count( $idx ) - 1;
		$dn_txt = $cont ? 'DN ' . $dns[0] . '–' . end( $dns ) : 'DN ' . implode( ', ', $dns );
		$out[] = 'PN ' . implode( ', ', array_map( 'promen_fx_num', $pns ) ) . ' — ' . $dn_txt;
	}
	return $out;
}

/**
 * Ответные детали той же резьбы из канона: [ [ 'title', 'url', 'note' ], … ].
 * Точная позиция — ссылкой на карточку, серия без однозначной пары — на норматив.
 */
function promen_fastener_mates( string $kind, string $base, float $m ): array {
	$big = $m > 48;
	$plan = [];
	switch ( $kind ) {
		case 'bolty':
		case 'shpilki':
			if ( 'ГОСТ 9066' === $base ) {
				$plan[] = [ 'gost-9064-1975', 'гайка к шпильке' ];
			} elseif ( 'ГОСТ 10494' === $base ) {
				return [];
			} else {
				$plan[] = [ $big ? 'gost-10605-1994' : 'gost-5915-1970', 'гайка' ];
				if ( ! $big ) {
					$plan[] = [ 'gost-11371-1978', 'шайба плоская' ];
					$plan[] = [ 'gost-6402-1970', 'шайба пружинная' ];
				}
			}
			break;
		case 'gayki':
			if ( 'ГОСТ 9064' === $base ) {
				$plan[] = [ 'norm:gost-9066-1975', 'шпильки к гайке' ];
			} else {
				$plan[] = [ $big ? 'norm:gost-10602-1994' : 'norm:gost-7798-1970', 'болты этой резьбы' ];
				if ( ! $big ) {
					$plan[] = [ 'gost-11371-1978', 'шайба плоская' ];
					$plan[] = [ 'gost-6402-1970', 'шайба пружинная' ];
				}
			}
			break;
		case 'shayby':
			$plan[] = [ 'gost-5915-1970', 'гайка' ];
			$plan[] = [ 'norm:gost-7798-1970', 'болты этой резьбы' ];
			break;
		case 'vinty':
			$plan[] = [ 'gost-6402-1970', 'шайба пружинная' ];
			break;
	}
	if ( ! $plan ) {
		return [];
	}

	$ck  = 'promen_series_fxm2_' . md5( $kind . '|' . $base . '|' . $m );
	$hit = get_transient( $ck );
	if ( is_array( $hit ) ) {
		return $hit;
	}

	global $wpdb;
	$t   = $wpdb->prefix . 'promen_catalog_rows';
	$out = [];
	foreach ( $plan as [ $norm, $note ] ) {
		$as_norm = str_starts_with( $norm, 'norm:' );
		$slug    = $as_norm ? substr( $norm, 5 ) : $norm;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, payload FROM {$t} WHERE norm_slug = %s AND ABS(dn - %f) < 0.01 ORDER BY product_id LIMIT 40", $slug, $m ) );
		if ( ! $rows ) {
			continue;
		}
		if ( $as_norm ) {
			$terms = get_the_terms( (int) $rows[0]->product_id, 'norm' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$link = get_term_link( $terms[0] );
				if ( ! is_wp_error( $link ) ) {
					$out[] = [ 'title' => $terms[0]->name . ' ' . promen_thread_label( (string) $m ), 'url' => $link, 'note' => $note ];
				}
			}
			continue;
		}
		// Пружинная шайба — нормальная (тип Н), остальное — первая позиция.
		$pick = $rows[0];
		foreach ( $rows as $r ) {
			$p = json_decode( (string) $r->payload, true );
			if ( 'тип Н' === ( $p['cells']['strength'] ?? '' ) ) {
				$pick = $r;
				break;
			}
		}
		$p = json_decode( (string) $pick->payload, true );
		$out[] = [
			'title' => trim( ( $p['title'] ?? '' ) . ' ' . ( $p['norm'] ?? '' ) ),
			'url'   => get_permalink( (int) $pick->product_id ),
			'note'  => $note,
		];
	}
	set_transient( $ck, $out, DAY_IN_SECONDS );
	return $out;
}

/**
 * Вся серия крепежа из канона, лёгкими строками: [ id, t (резьба), l, cls, url, title ].
 * promen_get_series() берёт не больше 500 позиций, а у ГОСТ 7805, 22043, 22032,
 * 9066, 7795 и 7798 их больше — на карточке это давало «500 типоразмеров»,
 * неполный список резьб и пропавшую из таблицы строку самой позиции.
 * Серия — тот же норматив и то же семейство. Кэш на сутки; префикс promen_series
 * — чтобы его сбрасывал promen_flush_series_cache().
 */
function promen_fastener_series_rows( int $product_id ): array {
	global $wpdb;
	$t   = $wpdb->prefix . 'promen_catalog_rows';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$own = $wpdb->get_row( $wpdb->prepare( "SELECT norm_slug, payload FROM {$t} WHERE product_id = %d", $product_id ) );
	if ( ! $own || '' === (string) $own->norm_slug ) {
		return [];
	}
	$fam = (string) ( json_decode( (string) $own->payload, true )['family'] ?? '' );
	$ck  = 'promen_series_fx1_' . md5( $own->norm_slug . '|' . $fam );
	$hit = get_transient( $ck );
	if ( is_array( $hit ) ) {
		return $hit;
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, dn, payload FROM {$t} WHERE norm_slug = %s", $own->norm_slug ) );
	$out  = [];
	foreach ( $rows as $r ) {
		$p = json_decode( (string) $r->payload, true );
		if ( ! is_array( $p ) || (string) ( $p['family'] ?? '' ) !== $fam ) {
			continue;
		}
		$len = (string) ( $p['cells']['length'] ?? '' );
		$out[] = [
			'id'    => (int) $r->product_id,
			't'     => (float) $r->dn,
			'l'     => is_numeric( str_replace( ',', '.', $len ) ) ? (float) str_replace( ',', '.', $len ) : 0.0,
			'cls'   => (string) ( $p['cells']['strength'] ?? '' ),
			'url'   => (string) ( $p['url'] ?? '' ),
			'title' => (string) ( $p['title'] ?? '' ),
		];
	}
	usort( $out, static fn( $a, $b ) => [ $a['t'], $a['l'], $a['cls'], $a['id'] ] <=> [ $b['t'], $b['l'], $b['cls'], $b['id'] ] );
	set_transient( $ck, $out, DAY_IN_SECONDS );
	return $out;
}

/**
 * Всё для карточки крепежа: окно своей резьбы (полные строки для таблицы),
 * ссылки на другие резьбы, сводка серии и соседи. [] — канона нет, шаблон
 * откатывается на promen_get_series().
 */
function promen_fastener_card( int $product_id, int $window = 24 ): array {
	$all = promen_fastener_series_rows( $product_id );
	if ( count( $all ) < 2 ) {
		return [];
	}
	$ids   = array_column( $all, 'id' );
	$cur_i = array_search( $product_id, $ids, true );
	if ( false === $cur_i ) {
		return [];
	}
	$cur  = $all[ $cur_i ];
	$same = array_values( array_filter( $all, static fn( $r ) => $r['t'] === $cur['t'] ) );
	$si   = (int) array_search( $product_id, array_column( $same, 'id' ), true );
	$win  = count( $same ) > $window ? array_slice( $same, max( 0, min( $si - intdiv( $window, 2 ), count( $same ) - $window ) ), $window ) : $same;

	// Другие резьбы: та же длина и класс, иначе та же длина, иначе первая позиция.
	$threads = [];
	foreach ( $all as $r ) {
		$k = promen_fmt_dim( (string) $r['t'] );
		$score = ( $r['l'] === $cur['l'] ? 2 : 0 ) + ( $r['cls'] === $cur['cls'] ? 1 : 0 );
		if ( ! isset( $threads[ $k ] ) || $score > $threads[ $k ]['score'] ) {
			$threads[ $k ] = [ 'url' => $r['url'], 'score' => $score, 'row' => $r ];
		}
	}

	// Соседи: две длины вниз и вверх по своей резьбе, та же длина у соседних резьб.
	$nb = [];
	foreach ( [ -2, -1, 1, 2 ] as $off ) {
		if ( isset( $same[ $si + $off ] ) ) {
			$nb[] = $same[ $si + $off ] + [ 'same' => true ];
		}
	}
	$tk = array_map( 'strval', array_keys( $threads ) ); // ключ «16» PHP хранит числом
	$ti = array_search( promen_fmt_dim( (string) $cur['t'] ), $tk, true );
	foreach ( [ $ti - 1, $ti + 1 ] as $j ) {
		if ( false !== $ti && isset( $tk[ $j ] ) && 3 === $threads[ $tk[ $j ] ]['score'] ) {
			$nb[] = $threads[ $tk[ $j ] ]['row'] + [ 'same' => false ];
		}
	}

	$ts = array_column( $all, 't' );
	$ls = array_filter( array_column( $all, 'l' ) );
	$ol = array_filter( array_column( $same, 'l' ) );
	return [
		'rows'    => array_map( static fn( $r ) => promen_series_row( $r['id'] ), $win ),
		'threads' => array_map( static fn( $x ) => $x['url'], $threads ),
		'nb'      => $nb,
		'n'       => count( $all ),
		'n_own'   => count( $same ),
		't_range' => [ min( $ts ), max( $ts ) ],
		'l_range' => $ls ? [ min( $ls ), max( $ls ) ] : [],
		'l_own'   => count( $ol ) > 1 ? [ min( $ol ), max( $ol ) ] : [],
	];
}

/**
 * Абзацы описания. Возвращает [ 'h' => заголовок, 'p' => [ html, … ], 'mates' => […], 'calc' => url ]
 * или [] — если данных на честный текст не хватает.
 */
function promen_fastener_description( int $product_id, string $norm_key, string $size_label ): array {
	$cat  = promen_deepest_cat( $product_id );
	$kind = $cat ? $cat->slug : '';
	if ( ! in_array( $kind, [ 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty' ], true ) ) {
		return [];
	}
	$base = promen_fastener_norm_base( $norm_key );
	if ( in_array( $base, [ 'ГОСТ 15590', 'ГОСТ 15591' ], true ) ) {
		return [];
	}
	$dims = promen_get_dims( $product_id );
	$f    = promen_fastener_dims( $dims );
	$m    = (float) str_replace( ',', '.', $f['thread'] );
	if ( $m <= 0 ) {
		return [];
	}
	$M      = promen_thread_label( $f['thread'] );
	$L      = $f['length'] !== '' ? promen_fx_num( $f['length'] ) : '';
	$class  = trim( $f['strength'] );
	$titles = promen_fastener_facts_data()['titles'] ?? [];
	$std_t  = $titles[ $base ] ?? '';
	$e      = 'esc_html';

	$word = [ 'bolty' => 'Болт', 'gayki' => 'Гайка', 'shpilki' => 'Шпилька', 'shayby' => 'Шайба', 'vinty' => 'Винт' ][ $kind ];
	$flange_std = in_array( $base, [ 'ГОСТ 9064', 'ГОСТ 9066', 'ГОСТ 10494', 'ОСТ 26-2040' ], true );
	$p = [];

	// 1. Что это по стандарту.
	$intro = $word . ' ' . $e( $size_label !== '' ? $size_label : $M ) . ' изготавливается по <strong>' . $e( $norm_key ) . '</strong>'
		. ( $std_t !== '' ? ' «' . $e( $std_t ) . '»' : '' ) . '.';
	switch ( $kind ) {
		case 'bolty':
			$intro .= $L !== ''
				? ' Длина болта ' . $L . ' мм считается от опорной поверхности головки до торца стержня.'
				: '';
			break;
		case 'vinty':
			$intro .= ' Головка цилиндрическая, под внутренний шестигранник — винт утапливают в отверстие с цековкой, когда выступающая головка мешает.'
				. ( $L !== '' ? ' Длина ' . $L . ' мм — от опорной поверхности головки до торца.' : '' );
			break;
		case 'shpilki':
			if ( 'ГОСТ 22032' === $base ) {
				$intro .= ' Один конец длиной около d ввинчивается в резьбовое отверстие стальной детали, на второй навинчивается гайка.'
					. ( $L !== '' ? ' Длина ' . $L . ' мм по стандарту считается без ввинчиваемого конца.' : '' );
			} elseif ( 'ГОСТ 22043' === $base ) {
				$intro .= ' Резьба на обоих концах, гайки ставят с двух сторон: шпилька проходит сквозь гладкие отверстия соединяемых деталей.'
					. ( $L !== '' ? ' Длина ' . $L . ' мм — полная длина шпильки.' : '' );
			} elseif ( 'ГОСТ 9066' === $base ) {
				$intro .= ' Шпилька фланцевого соединения трубопроводов и арматуры; технические требования, материалы и приёмка — по ГОСТ 20700-75, ответные гайки — ГОСТ 9064-75.'
					. ( $L !== '' ? ' Длина ' . $L . ' мм — полная длина шпильки.' : '' );
			} elseif ( 'ГОСТ 10494' === $base ) {
				$intro .= ' Шпилька для соединений высокого давления; ответные гайки — ГОСТ 10495-80.'
					. ( $L !== '' ? ' Длина ' . $L . ' мм — полная длина шпильки.' : '' );
			} elseif ( 'ОСТ 26-2040' === $base ) {
				$intro .= ' Отраслевой стандарт химического и нефтяного машиностроения: шпильки фланцевых соединений сосудов, аппаратов и трубопроводов.'
					. ( $L !== '' ? ' Длина ' . $L . ' мм — полная длина шпильки.' : '' );
			}
			break;
		case 'gayki':
			if ( 'ГОСТ 9064' === $base ) {
				$intro .= ' Гайка фланцевого соединения в паре со шпильками ГОСТ 9066-75; технические требования — ГОСТ 20700-75.';
			} elseif ( in_array( $base, [ 'ГОСТ 5916', 'ГОСТ 5929', 'ГОСТ 10607' ], true ) ) {
				$intro .= ' Низкая гайка — заметно ниже обычной: ставится контргайкой или там, где нет большой осевой нагрузки.';
			} else {
				$intro .= ' Гайка нормальной высоты для болтов и шпилек той же резьбы.';
			}
			break;
		case 'shayby':
			if ( 'ГОСТ 6402' === $base ) {
				$types = [ 'Л' => 'лёгкая', 'Н' => 'нормальная', 'Т' => 'тяжёлая', 'ОТ' => 'особо тяжёлая' ];
				$wt    = trim( $f['washer'] );
				$intro .= ' Пружинная шайба (гровер) — разрезное кольцо, которое упругостью держит гайку или болт от самоотвинчивания при вибрации.'
					. ( isset( $types[ $wt ] ) ? ' Тип ' . $e( $wt ) . ' — ' . $types[ $wt ] . ': чем тяжелее тип, тем толще сечение кольца и сильнее прижим.' : '' );
			} else {
				$intro .= ' Плоская шайба под гайку или головку болта: увеличивает опорную площадь и бережёт поверхность детали при затяжке.';
			}
			$intro .= ' Размер ' . $e( promen_fmt_dim( $f['thread'] ) ) . ' — номинальный диаметр резьбы крепежа, под который идёт шайба; внутренний диаметр чуть больше — по таблице стандарта.';
			break;
	}
	$p[] = $intro;

	// 2. Резьба.
	if ( 'shayby' !== $kind ) {
		$pitch = ( ! $flange_std && $m <= 48 && promen_thread_fine_pitch( $m ) ) ? promen_thread_coarse_pitch( $m ) : null;
		if ( $pitch ) {
			$p[] = 'Резьба ' . $e( $M ) . ' — метрическая с крупным шагом ' . promen_fx_num( $pitch ) . ' мм по ГОСТ 24705-2004. В обозначении крупный шаг не пишут, мелкий указывают явно: '
				. $e( $M ) . '×' . promen_fx_num( promen_thread_fine_pitch( $m ) ) . '. Поле допуска резьбы — 6g у стержня и 6H у гайки, если в заказе не оговорено иное.';
		} elseif ( $flange_std || $m > 48 ) {
			$p[] = 'Резьба ' . $e( $M ) . ' — метрическая; шаг для этого диаметра задаёт таблица ' . $e( $norm_key ) . ', при заказе он входит в условное обозначение.';
		}
	}

	// 2б. Длина: резьба и пакет под гайку.
	if ( $L !== '' ) {
		$lt = promen_fastener_length_text( $base, $m, (float) str_replace( ',', '.', $f['length'] ) );
		if ( $lt !== '' ) {
			$p[] = $lt;
		}
	}

	// 3. Класс прочности (болты, винты, шпильки).
	if ( preg_match( '/^(\d{1,2})\.(\d)$/', $class, $cm ) && in_array( $kind, [ 'bolty', 'vinty', 'shpilki' ], true ) ) {
		$a  = (int) $cm[1];
		$b  = (int) $cm[2];
		$uts = $a * 100;
		$ys  = $a * $b * 10;
		$mat = $a >= 8
			? 'Такие классы получают закалкой с отпуском: среднеуглеродистые стали 35, 45 или легированные 40Х, 30ХГСА.'
			: 'Этот класс не требует закалки: крепёж делают из углеродистых сталей Ст3, 10, 20, 35.';
		$p[] = 'Класс прочности ' . $e( $class ) . ' по ГОСТ ISO 898-1-2014: первая цифра × 100 — номинальный предел прочности <strong>' . $uts . ' МПа</strong>, '
			. 'произведение цифр × 10 — предел текучести <strong>' . $ys . ' МПа</strong> (' . $b . '0% от прочности). ' . $mat
			. ' Гайку в пару берут класса не ниже ' . $a . ' (ГОСТ ISO 898-2), иначе при затяжке раньше сорвёт резьбу гайки.';
	}

	// 4. Фланцы.
	$usage = [];
	if ( in_array( $kind, [ 'bolty', 'shpilki', 'gayki' ], true ) && 'ГОСТ 22032' !== $base && 'ГОСТ 10494' !== $base ) {
		$usage = promen_fastener_flange_usage( $m, 'bolty' === $kind ? 25 : 0 );
	}
	if ( $usage ) {
		$p[] = 'Во фланцевых соединениях по ГОСТ 33259-2015 (фланцы приварные встык, ряд 1) резьба ' . $e( $M ) . ' стоит на фланцах: '
			. $e( implode( '; ', $usage ) ) . '.'
			. ( 'bolty' === $kind ? ' Болты во фланцах допускаются до PN 25 (2,5 МПа) и температуры от −40 до 300 °C (п. 7.9.5 стандарта) — выше ставят только шпильки.' : '' )
			. ' Крепёж делают из стали того же структурного класса, что и фланцы (п. 7.9.3).';
	}

	return [
		'h'     => trim( $word . ' ' . ( $size_label !== '' ? $size_label : $M ) . ' ' . $norm_key ),
		'p'     => $p,
		'mates' => promen_fastener_mates( $kind, $base, $m ),
		'calc'  => $usage && function_exists( 'promen_calc_url' ) ? promen_calc_url( 'flancevyy-krepezh' ) : '',
	];
}

/**
 * Meta description карточки крепежа: те же факты, что в описании, по
 * убыванию важности, пока влезает в 160 символов. Раньше было
 * «Крепёж по ГОСТ …. Резьба M16. Длина 60 мм.» — одно на тысячу карточек.
 */
function promen_fastener_meta_desc( int $product_id, bool $series = false ): string {
	$cat  = promen_deepest_cat( $product_id );
	$kind = $cat ? $cat->slug : '';
	$word = [ 'bolty' => 'Болт', 'gayki' => 'Гайка', 'shpilki' => 'Шпилька', 'shayby' => 'Шайба', 'vinty' => 'Винт' ][ $kind ] ?? '';
	$norm = function_exists( 'promen_product_norm_key' ) ? (string) promen_product_norm_key( $product_id ) : '';
	$base = promen_fastener_norm_base( $norm );
	if ( '' === $word || '' === $norm || in_array( $base, [ 'ГОСТ 15590', 'ГОСТ 15591' ], true ) ) {
		return '';
	}
	if ( $series ) {
		// Страница серии: описание ряда, а не той позиции, что открыла серию.
		$all = promen_fastener_series_rows( $product_id );
		if ( count( $all ) < 2 ) {
			return '';
		}
		$ts   = array_column( $all, 't' );
		$ls   = array_filter( array_column( $all, 'l' ) );
		$cls  = array_values( array_unique( array_filter( array_column( $all, 'cls' ), static fn( $c ) => (bool) preg_match( '/^\d{1,2}\.\d$/', $c ) ) ) );
		sort( $cls );
		$plural = [ 'Болт' => 'Болты', 'Гайка' => 'Гайки', 'Шпилька' => 'Шпильки', 'Шайба' => 'Шайбы', 'Винт' => 'Винты' ][ $word ];
		$desc   = $plural . ' ' . $norm . ': ' . count( $all ) . ' ' . promen_ru_plural( count( $all ), 'типоразмер', 'типоразмера', 'типоразмеров' )
			. ', резьба ' . promen_thread_label( (string) min( $ts ) ) . '–' . promen_thread_label( (string) max( $ts ) )
			. ( $ls ? ', длина ' . promen_fx_num( min( $ls ) ) . '–' . promen_fx_num( max( $ls ) ) . ' мм' : '' )
			. ( $cls ? ( count( $cls ) > 1 ? ', классы прочности ' : ', класс прочности ' ) . implode( ', ', $cls ) : '' ) . '.';
		$tail = ' Реестр размеров и КП за 1 рабочий день.';
		return mb_strlen( $desc . $tail ) <= 160 ? $desc . $tail : $desc;
	}
	$dims = promen_get_dims( $product_id );
	$f    = promen_fastener_dims( $dims );
	$m    = (float) str_replace( ',', '.', $f['thread'] );
	$size = promen_size_label( $dims, $product_id );
	$head = $word . ' ' . ( $size !== '' ? $size : $f['M'] ) . ' по ' . $norm;

	$facts = [];
	$pitch = ! in_array( $base, [ 'ГОСТ 9064', 'ГОСТ 9066', 'ГОСТ 10494', 'ОСТ 26-2040' ], true ) && $m <= 48 && 'shayby' !== $kind ? promen_thread_coarse_pitch( $m ) : null;
	if ( $pitch ) {
		$facts[] = 'резьба ' . $f['M'] . ' с шагом ' . promen_fx_num( $pitch ) . ' мм';
	}
	if ( preg_match( '/^(\d{1,2})\.(\d)$/', trim( $f['strength'] ), $cm ) && 'gayki' !== $kind ) {
		$facts[] = 'прочность ' . ( (int) $cm[1] * 100 ) . ' МПа, текучесть ' . ( (int) $cm[1] * (int) $cm[2] * 10 ) . ' МПа';
	}
	$l = (float) str_replace( ',', '.', $f['length'] );
	if ( in_array( $base, [ 'ГОСТ 7798', 'ГОСТ 7805' ], true ) && $l > 0 && $pitch ) {
		$b = 2 * $m + ( $l <= 125 ? 6 : ( $l <= 200 ? 12 : 25 ) );
		$facts[] = $l - $b <= 2 * $pitch ? 'резьба по всей длине' : 'длина резьбы ' . promen_fx_num( $b ) . ' мм';
	}
	if ( 'ГОСТ 6402' === $base ) {
		$types = [ 'Л' => 'лёгкая', 'Н' => 'нормальная', 'Т' => 'тяжёлая', 'ОТ' => 'особо тяжёлая' ];
		$wt    = trim( $f['washer'] );
		$facts[] = 'пружинная' . ( isset( $types[ $wt ] ) ? ', ' . $types[ $wt ] : '' ) . ', под резьбу M' . promen_fmt_dim( $f['thread'] );
	} elseif ( 'shayby' === $kind ) {
		$facts[] = 'под резьбу M' . promen_fmt_dim( $f['thread'] );
	}
	if ( in_array( $base, [ 'ГОСТ 9064', 'ГОСТ 9066' ], true ) ) {
		$facts[] = 'фланцевые соединения до 650 °C';
	}
	$tail = ' Сертификат 3.1, КП за 1 рабочий день.';

	$desc = $head;
	foreach ( $facts as $i => $fact ) {
		$try = $desc . ( 0 === $i ? ': ' : ', ' ) . $fact;
		if ( mb_strlen( $try . '.' . $tail ) > 160 ) {
			break;
		}
		$desc = $try;
	}
	$desc .= '.';
	return mb_strlen( $desc . $tail ) <= 160 ? $desc . $tail : $desc;
}

