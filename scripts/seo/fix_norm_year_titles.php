<?php
/**
 * Год норматива в названиях и описаниях товаров — по официальному обозначению.
 *
 * Повторный обход 25.09.2026: у 41 норматива термин и реестр записывают год
 * двумя цифрами («ОСТ 24.125.03-89», «ОСТ 34-10-432-90» — так он напечатан на
 * самом стандарте), а в названиях 1 412 товаров стоит развёрнутый импортом год
 * «-1989», «-1990». Страница спорит сама с собой: крошка и норматив говорят
 * «-89», H1 и title карточки — «-1989». Поиск тоже на стороне двузначного:
 * «ост 24.125.03-89» — форма со спецификаций и чертежей.
 *
 * Правило: год пишется так, как он напечатан на стандарте. До 2000 года
 * обозначения давались с двузначным годом, с 2000-го — с четырёхзначным.
 * Меняем только год внутри обозначения, разделители (точки или дефисы)
 * оставляем как есть в названии — на поиск они не влияют, а правка
 * получается минимальной.
 *
 * Трогаем только нормативы, у которых ТЕРМИН уже с двузначным годом, а
 * названия — с четырёхзначным. Обратный случай (термин «ГОСТ 10494-1980»,
 * названия «ГОСТ 10494-80») здесь не правится: там верны названия, а менять
 * нужно термин — это отдельное решение.
 *
 * Слаги товаров не трогаем: адреса проиндексированы, год в URL ни на что не
 * влияет, а смена слагов стоила бы переобхода тысяч карточек.
 *
 * Запуск:
 *   wp eval-file fix_norm_year_titles.php          — отчёт, ничего не меняет
 *   wp eval-file fix_norm_year_titles.php apply    — применить
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );

/** Ядро обозначения без разделителей и года: «ост 24 125 03». */
$core = static function ( string $s ): string {
	$s = mb_strtolower( trim( $s ) );
	$s = preg_replace( '/[.\-\s]+/u', ' ', $s );
	$s = preg_replace( '/\s(\d{2}|\d{4})$/u', '', $s );
	return trim( $s );
};

$designation_re = '/\b(?:ГОСТ(?:\s+Р)?|ОСТ|СТО(?:\s+ЦКТИ)?)\s+\d[\d.\-\s]*?[.\-\s](\d{4})\b/u';

$plan  = [];   // term_id => [ name, [ id => [ old_title, new_title, fields ] ] ]
$total = 0;

foreach ( get_terms( [ 'taxonomy' => 'norm', 'hide_empty' => true ] ) as $term ) {
	// Только термины с двузначным годом на конце.
	if ( ! preg_match( '/[.\-\s](\d{2})$/u', trim( $term->name ), $ty ) ) {
		continue;
	}
	$term_core = $core( $term->name );
	$yy        = $ty[1];

	$changes = [];
	foreach ( (array) get_objects_in_term( [ $term->term_id ], 'norm' ) as $id ) {
		$id   = (int) $id;
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}

		// Одна функция замены на все поля: только обозначения этого норматива,
		// у которых четырёхзначный год оканчивается теми же двумя цифрами.
		$fix = static function ( string $text ) use ( $designation_re, $core, $term_core, $yy ): string {
			return (string) preg_replace_callback( $designation_re, static function ( $m ) use ( $core, $term_core, $yy ) {
				$full = $m[0];
				$year = $m[1];
				if ( $core( $full ) !== $term_core || substr( $year, 2 ) !== $yy ) {
					return $full;
				}
				return substr( $full, 0, -4 ) . $yy;
			}, $text );
		};

		$new_title   = $fix( $post->post_title );
		$new_excerpt = $fix( $post->post_excerpt );
		$new_content = $fix( $post->post_content );

		$fields = [];
		if ( $new_title !== $post->post_title ) {
			$fields['post_title'] = $new_title;
		}
		if ( $new_excerpt !== $post->post_excerpt ) {
			$fields['post_excerpt'] = $new_excerpt;
		}
		if ( $new_content !== $post->post_content ) {
			$fields['post_content'] = $new_content;
		}
		if ( $fields ) {
			$changes[ $id ] = [ $post->post_title, $new_title, $fields ];
		}
	}

	if ( $changes ) {
		$plan[ $term->term_id ] = [ $term->name, $changes ];
		$total += count( $changes );
	}
}

WP_CLI::log( sprintf( 'Нормативов к правке: %d, товаров: %d', count( $plan ), $total ) );
foreach ( $plan as $tid => list( $name, $changes ) ) {
	$first = reset( $changes );
	$fields_count = [];
	foreach ( $changes as $c ) {
		foreach ( array_keys( $c[2] ) as $f ) {
			$fields_count[ $f ] = ( $fields_count[ $f ] ?? 0 ) + 1;
		}
	}
	WP_CLI::log( sprintf( '  %-26s %4d тов.  [%s]', $name, count( $changes ),
		implode( ', ', array_map( static fn( $k, $v ) => "$k $v", array_keys( $fields_count ), $fields_count ) ) ) );
	WP_CLI::log( sprintf( '      «%s» → «%s»', $first[0], $first[1] ) );
}

if ( ! $apply ) {
	WP_CLI::log( '' );
	WP_CLI::log( 'Отчёт. Для правки: ... eval-file fix_norm_year_titles.php apply' );
	return;
}

// Слепок исходных полей — откатывать на боевой базе иначе неоткуда.
$snapshot = [];
foreach ( $plan as $tid => list( $name, $changes ) ) {
	foreach ( $changes as $id => $c ) {
		$post = get_post( $id );
		$snapshot[ $id ] = [
			'post_title'   => $post->post_title,
			'post_excerpt' => $post->post_excerpt,
			'post_content' => $post->post_content,
		];
	}
}
$snap_file = rtrim( (string) getenv( 'HOME' ), '/' ) . '/norm-year-titles-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $snap_file, wp_json_encode( $snapshot, JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок: ' . $snap_file . ' (' . size_format( (int) filesize( $snap_file ) ) . ')' );

$done = 0;
foreach ( $plan as $tid => list( $name, $changes ) ) {
	foreach ( $changes as $id => list( $old, $new, $fields ) ) {
		// Прямой UPDATE вместо wp_update_post: он дёргает save_post у Woo и
		// пересобирает слаг — а слаги менять не собираемся.
		global $wpdb;
		$wpdb->update( $wpdb->posts, $fields, [ 'ID' => $id ] );
		clean_post_cache( $id );
		if ( function_exists( 'promen_catalog_upsert' ) ) {
			promen_catalog_upsert( $id, false );
		}
		$done++;
		if ( 0 === $done % 200 ) {
			wp_cache_flush(); // память на шареде: канон тянет кеш объектов
			WP_CLI::log( "  … {$done}" );
		}
	}
}

WP_CLI::success( "Исправлено товаров: {$done}. Дальше: wp promen cache-purge." );
