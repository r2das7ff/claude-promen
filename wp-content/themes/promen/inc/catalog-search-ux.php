<?php
/**
 * Обвязка поиска каталога: журнал промахов и разбор запроса в фильтры.
 *
 * Сам поиск живёт в catalog-search.php. Здесь — то, что вокруг него:
 *   — promen_search_log_miss()      — что искали и не нашли (данные для
 *                                     синонимов, минус-слов Директа и
 *                                     понимания, чего в каталоге нет);
 *   — promen_catalog_query_hints()  — «тройник 108х4 09г2с» разобрать
 *                                     в фильтры DN 100 · стенка 4 · 09Г2С.
 */

defined( 'ABSPATH' ) || exit;

/* ═════════════════ ЖУРНАЛ ПРОМАХОВ ═════════════════ */

/** Сколько разных запросов храним. */
function promen_search_log_limit(): int {
	return (int) apply_filters( 'promen_search_log_limit', 300 );
}

/**
 * Записать запрос, который каталог не отработал полностью.
 *
 * Два случая, и оба интересны: не нашлось совсем — и нашлось, но часть слов
 * пришлось отбросить. Второе даже ценнее: «трубы гост 20295» покажет трубы,
 * а в журнале останется, что именно «20295» в каталоге не находится.
 *
 * Считаем повторы, а не пишем строку на каждый заход: сто одинаковых
 * запросов в ленте — это один факт, а не сто.
 *
 * @param string[] $dropped Слова, которые поиск отбросил (пусто — полный промах).
 */
function promen_search_log_miss( string $q, string $group = '', array $dropped = [] ): void {
	$q = trim( preg_replace( '/\s+/u', ' ', $q ) );
	if ( $q === '' || mb_strlen( $q, 'UTF-8' ) > 120 ) {
		return;
	}

	$log = get_option( 'promen_search_misses', [] );
	if ( ! is_array( $log ) ) {
		$log = [];
	}

	$key = mb_strtolower( $q, 'UTF-8' );
	$now = gmdate( 'c' );

	// Живой поиск уходит на сервер на каждой паузе в наборе, поэтому в журнал
	// просятся и недопечатанные префиксы: «тройник 108х» перед «тройник 108х4».
	// Оставляем самое длинное. Частые запросы (n > 2) не трогаем — это уже
	// не обрывок набора, а то, что люди действительно ищут.
	foreach ( array_keys( $log ) as $seen ) {
		$seen = (string) $seen;
		if ( $seen !== $key && str_starts_with( $key, $seen ) && (int) ( $log[ $seen ]['n'] ?? 0 ) <= 2 ) {
			unset( $log[ $seen ] );
		}
	}
	$dropped = array_values( array_unique( array_filter( array_map( 'strval', $dropped ) ) ) );
	if ( isset( $log[ $key ] ) && is_array( $log[ $key ] ) ) {
		$log[ $key ]['n']       = (int) ( $log[ $key ]['n'] ?? 0 ) + 1;
		$log[ $key ]['last']    = $now;
		$log[ $key ]['group']   = $group;
		$log[ $key ]['dropped'] = $dropped;
		$log[ $key ]['zero']    = ! $dropped;
	} else {
		$log[ $key ] = [
			'q'       => $q,
			'n'       => 1,
			'group'   => $group,
			'dropped' => $dropped,
			'zero'    => ! $dropped,
			'first'   => $now,
			'last'    => $now,
		];
	}

	// Переполнение чистим по редким и старым: частые запросы нужнее.
	if ( count( $log ) > promen_search_log_limit() ) {
		uasort(
			$log,
			static fn( array $a, array $b ): int => [ (int) $b['n'], (string) ( $b['last'] ?? '' ) ] <=> [ (int) $a['n'], (string) ( $a['last'] ?? '' ) ]
		);
		$log = array_slice( $log, 0, promen_search_log_limit(), true );
	}

	update_option( 'promen_search_misses', $log, false );
}

/**
 * Журнал промахов, частые сверху.
 *
 * @return list<array{q:string,n:int,group:string,first:string,last:string}>
 */
function promen_search_misses( int $limit = 100 ): array {
	$log = get_option( 'promen_search_misses', [] );
	if ( ! is_array( $log ) ) {
		return [];
	}
	$rows = array_values( array_filter( $log, 'is_array' ) );
	usort(
		$rows,
		static fn( array $a, array $b ): int => [ (int) $b['n'], (string) ( $b['last'] ?? '' ) ] <=> [ (int) $a['n'], (string) ( $a['last'] ?? '' ) ]
	);
	return array_slice( $rows, 0, max( 1, $limit ) );
}

/* ═════════════════ РАЗБОР ЗАПРОСА В ФИЛЬТРЫ ═════════════════ */

/**
 * Что из запроса каталог умеет понять как фильтры.
 *
 * Разбор берём у подборщика (promen_selector_parse) — он уже умеет читать
 * «108х4», «ду100», «ру16», «45°», марки и нормативы. Здесь его результат
 * переводится в параметры реестра и человеческие подписи для строки
 * «Похоже, вы ищете…».
 *
 * Возвращает [] , если понимать нечего или всё уже стоит в фильтрах.
 *
 * @return array{params: array<string,string>, labels: string[], group: string}|array{}
 */
function promen_catalog_query_hints( string $q, string $current_group = '', array $active = [] ): array {
	if ( trim( $q ) === '' || ! function_exists( 'promen_selector_parse' ) ) {
		return [];
	}

	$parsed = promen_selector_parse( $q );
	$params = [];
	$labels = [];

	// Тип изделия — только если раздел ещё не выбран: иначе увели бы человека
	// из раздела, в котором он сознательно стоит.
	$group = (string) ( $parsed['group'] ?? '' );
	if ( $group !== '' && $current_group === '' ) {
		$params['group'] = $group;
		$labels[]        = promen_term_label( 'product_cat', $group );
	}
	$scope_group = $current_group !== '' ? $current_group : $group;

	// Наружный диаметр из «108х4» переводим в условный проход по канону:
	// фильтр реестра работает по DN, а снабженец пишет размер трубы.
	$dn = $parsed['dn'] ?? null;
	if ( null === $dn && ! empty( $parsed['d'] ) && function_exists( 'promen_selector_d_map' ) ) {
		$map = promen_selector_d_map();
		$key = (string) (float) $parsed['d'];
		if ( isset( $map[ $key ] ) ) {
			$dn = $map[ $key ];
		}
	}
	if ( null !== $dn && empty( $active['dn'] ) ) {
		$params['dn_min'] = promen_selector_fmt( (float) $dn );
		$params['dn_max'] = promen_selector_fmt( (float) $dn );
		$labels[]         = 'DN ' . promen_selector_fmt( (float) $dn );
	}

	if ( ! empty( $parsed['s'] ) && empty( $active['s'] ) ) {
		$params['s_min'] = promen_selector_fmt( (float) $parsed['s'] );
		$params['s_max'] = promen_selector_fmt( (float) $parsed['s'] );
		$labels[]        = 'стенка ' . promen_selector_fmt( (float) $parsed['s'] );
	}

	// «отвод 90 градусов»: парсер подборщика читает «градус» как температуру
	// среды — там это верно, а в каталоге температуры нет вовсе. Число из ряда
	// углов у изделия, где угол вообще бывает, считаем углом.
	$angle_raw = $parsed['angle'] ?? null;
	if ( null === $angle_raw && null !== ( $parsed['temp'] ?? null )
		&& function_exists( 'promen_catalog_schema_facets' )
		&& in_array( 'angle', promen_catalog_schema_facets( $scope_group ), true )
		&& in_array( (float) $parsed['temp'], array_map( 'floatval', promen_valid_angles() ), true ) ) {
		$angle_raw = $parsed['temp'];
	}
	if ( null !== $angle_raw && empty( $active['angle'] ) ) {
		$angle = promen_selector_fmt( (float) $angle_raw );
		if ( in_array( (float) $angle, array_map( 'floatval', promen_valid_angles() ), true ) ) {
			$params['angle'] = $angle;
			$labels[]        = $angle . '°';
		}
	}

	// Марки и нормативы сверяем с тем, что реально есть в разделе, —
	// фильтр по отсутствующей марке дал бы пустой экран.
	if ( ! empty( $parsed['steel'] ) && empty( $active['steel'] ) && function_exists( 'promen_selector_group_steel_map' ) ) {
		$steel_map = promen_selector_group_steel_map( $scope_group );
		$slugs     = [];
		foreach ( (array) $parsed['steel'] as $key ) {
			if ( isset( $steel_map[ $key ] ) ) {
				$slugs[] = $steel_map[ $key ];
			}
		}
		$slugs = array_values( array_unique( $slugs ) );
		if ( $slugs ) {
			$params['steel'] = implode( ',', $slugs );
			$labels[]        = implode( ', ', array_map( static fn( string $s ): string => promen_term_label( 'pa_steel', $s ), $slugs ) );
		}
	}

	if ( ! empty( $parsed['gost'] ) && empty( $active['gost'] )
		&& function_exists( 'promen_selector_group_norm_map' ) && function_exists( 'promen_selector_match_norms' ) ) {
		$norm_map = promen_selector_group_norm_map( $scope_group );
		$slugs    = promen_selector_match_norms( (array) $parsed['gost'], $norm_map );
		if ( $slugs ) {
			$params['gost'] = implode( ',', $slugs );
			$labels[]       = promen_term_label( 'norm', (string) $slugs[0] )
				. ( count( $slugs ) > 1 ? ' и ещё ' . ( count( $slugs ) - 1 ) : '' );
		}
	}

	// Один только тип изделия — не подсказка: раздел и так виден в сайдбаре.
	$meaningful = array_diff( array_keys( $params ), [ 'group' ] );
	if ( ! $meaningful ) {
		return [];
	}

	return [
		'params'  => $params,
		'labels'  => $labels,
		'group'   => $params['group'] ?? $current_group,
		// Слова, которые не понял и разбор: только про них честно говорить
		// «не учтены». «Нержавейку» текстовый поиск не нашёл, но разбор понял
		// её как марки — жаловаться на неё было бы странно.
		'unknown' => array_map( static fn( $w ): string => mb_strtolower( (string) $w, 'UTF-8' ), (array) ( $parsed['unknown'] ?? [] ) ),
	];
}

/**
 * Адрес реестра с применёнными подсказками (текстовый запрос снимаем —
 * его смысл уже переехал в фильтры).
 */
function promen_catalog_hints_url( array $hints, string $base = '' ): string {
	if ( empty( $hints['params'] ) ) {
		return '';
	}
	if ( $base === '' ) {
		$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
	}
	$params = $hints['params'];
	// Раздел берём из подсказки целиком: когда человек уже стоит в «Тройниках»,
	// в params его нет, но уводить его на общий реестр нельзя.
	$group  = (string) ( $hints['group'] ?? ( $params['group'] ?? '' ) );
	unset( $params['group'] );

	// Раздел — через страницу категории, как и в остальном каталоге.
	if ( $group !== '' && function_exists( 'promen_product_cat_link' ) ) {
		$term_url = promen_product_cat_link( $group );
		if ( $term_url ) {
			$base = $term_url;
		} else {
			$params['group'] = $group;
		}
	}

	return $params ? add_query_arg( array_map( 'rawurlencode', $params ), $base ) : $base;
}

/* ═════════════════ СВОДКА ДЛЯ ВИТРИНЫ ═════════════════ */

/**
 * Что сказать человеку про его запрос: какие слова не учтены и какие
 * фильтры за ним угадываются. Одна логика для REST и серверного рендера.
 *
 * @return array{dropped: string[], hints: array{labels: string[], url: string}}
 */
function promen_catalog_search_note( Promen_Catalog_Query $query, Promen_Catalog_Search_Result $result ): array {
	$note = [ 'dropped' => $result->dropped, 'hints' => [ 'labels' => [], 'url' => '' ] ];
	if ( $query->q === '' ) {
		return $note;
	}

	$active = [
		'dn'    => ( null !== $query->dn_min || null !== $query->dn_max ),
		's'     => ( null !== $query->s_min || null !== $query->s_max ),
		'angle' => ! empty( $query->angle ),
		'steel' => ! empty( $query->steel ),
		'gost'  => ! empty( $query->gost ),
	];

	$hints = promen_catalog_query_hints( $query->q_raw !== '' ? $query->q_raw : $query->q, $query->group, $active );
	if ( $hints ) {
		$note['hints'] = [
			'labels' => $hints['labels'],
			'url'    => promen_catalog_hints_url( $hints ),
		];
		// Из «не учтены» убираем то, что разбор всё-таки понял (марка, размер,
		// норматив) и служебные слова вроде «мм» — иначе строка противоречит
		// сама себе: слово и не учтено, и понято.
		if ( $note['dropped'] ) {
			$note['dropped'] = array_values(
				array_filter(
					$note['dropped'],
					static fn( string $w ): bool => in_array( mb_strtolower( $w, 'UTF-8' ), $hints['unknown'], true )
				)
			);
		}
	}
	return $note;
}

/* ═════════════════ ЖУРНАЛ В АДМИНКЕ ═════════════════ */

add_action(
	'admin_menu',
	function (): void {
		add_submenu_page(
			'edit.php?post_type=product',
			'Поиск: что искали и не нашли',
			'Поиск: промахи',
			'manage_woocommerce',
			'promen-search-misses',
			'promen_render_search_misses_page'
		);
	}
);

function promen_render_search_misses_page(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'Недостаточно прав.' );
	}

	if ( isset( $_POST['promen_clear_misses'] ) && check_admin_referer( 'promen_clear_misses' ) ) {
		delete_option( 'promen_search_misses' );
		echo '<div class="notice notice-success"><p>Журнал очищен.</p></div>';
	}

	$rows  = promen_search_misses( 200 );
	$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
	echo '<div class="wrap"><h1>Поиск: что искали и не нашли</h1>';
	echo '<p>Запросы, которые каталог отработал не полностью: либо не нашёл ничего, либо нашёл, отбросив часть слов. Второе особенно полезно — в колонке «Не нашлись слова» видно, чего в каталоге нет и какими словами его ищут. Отсюда берутся синонимы, минус-слова для Директа и понимание пробелов ассортимента.</p>';

	if ( ! $rows ) {
		echo '<p><em>Пока пусто — значит, всё, что искали, находилось.</em></p></div>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr>'
		. '<th>Запрос</th><th style="width:70px;">Раз</th><th style="width:200px;">Не нашлись слова</th>'
		. '<th style="width:130px;">Раздел</th><th style="width:150px;">Последний раз</th>'
		. '<th style="width:110px;">Проверить</th>'
		. '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$q       = (string) ( $row['q'] ?? '' );
		$last    = (string) ( $row['last'] ?? '' );
		$dropped = (array) ( $row['dropped'] ?? [] );
		$url     = add_query_arg( [ 'q' => rawurlencode( $q ) ], $shop );
		printf(
			'<tr><td><strong>%s</strong></td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td><a href="%s" target="_blank" rel="noopener">открыть поиск</a></td></tr>',
			esc_html( $q ),
			(int) ( $row['n'] ?? 0 ),
			$dropped ? esc_html( implode( ', ', $dropped ) ) : '<em>не нашлось ничего</em>',
			esc_html( (string) ( $row['group'] ?? '' ) ?: 'весь каталог' ),
			esc_html( $last !== '' ? wp_date( 'd.m.Y H:i', strtotime( $last ) ) : '—' ),
			esc_url( $url )
		);
	}
	echo '</tbody></table>';

	echo '<form method="post" style="margin-top:18px;">';
	wp_nonce_field( 'promen_clear_misses' );
	echo '<button type="submit" name="promen_clear_misses" value="1" class="button">Очистить журнал</button>';
	echo '</form></div>';
}
