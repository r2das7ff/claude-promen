<?php
/**
 * Слаги товаров: числовой хвост от WordPress → PN.
 *
 * Имя по ГОСТу давление не несёт, поэтому фланцы одного DN получали
 * одинаковые слаги, и WordPress разводил их суффиксами: `-2`, `-5`, `-12`.
 * Адрес переставал что-либо значить, а карточки выглядели дублями.
 * Меняем, пока сайт не переехал на боевой домен: сейчас это бесплатно,
 * после переезда та же правка стоила бы сотен редиректов.
 *
 * Запуск (из корня site/):
 *   docker compose run --rm -T wpcli eval-file /scripts/seo/slug_pn_fix.php
 *   docker compose run --rm -T wpcli eval-file /scripts/seo/slug_pn_fix.php --apply
 *   ... --apply --sql=/scripts/seo/slug_pn_fix.sql   # выгрузить UPDATE для сервера
 *
 * Без --apply только показывает план. После --apply нужны
 * `wp promen catalog-rebuild` и `wp promen search-reindex`:
 * в каноне лежат permalink'и.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

// WP-CLI ругается на незнакомые флаги, поэтому параметры позиционные:
//   ... eval-file /scripts/seo/slug_pn_fix.php apply sql=/scripts/seo/plan.sql
$opts     = isset( $args ) && is_array( $args ) ? $args : [];
$apply    = in_array( 'apply', $opts, true );
$sql_path = '';
foreach ( $opts as $a ) {
	if ( 0 === strpos( (string) $a, 'sql=' ) ) {
		$sql_path = substr( (string) $a, 4 );
	}
}

global $wpdb;

/** Все товары со слагом, оканчивающимся числовым хвостом. */
$rows = $wpdb->get_results(
	"SELECT ID, post_name, post_title FROM {$wpdb->posts}
	 WHERE post_type = 'product' AND post_status = 'publish'
	   AND post_name REGEXP '-[0-9]+$'
	 ORDER BY post_name"
);

WP_CLI::log( 'Слагов с числовым хвостом: ' . count( $rows ) );

/** Хвост может быть и частью имени («…-2015»), поэтому группируем по основе. */
$groups = [];
foreach ( $rows as $r ) {
	$base = preg_replace( '/-\d+$/', '', $r->post_name );
	$groups[ $base ][] = $r;
}

// Добираем «нулевой» элемент группы — товар с самой основой, без хвоста.
foreach ( array_keys( $groups ) as $base ) {
	$zero = $wpdb->get_row( $wpdb->prepare(
		"SELECT ID, post_name, post_title FROM {$wpdb->posts}
		 WHERE post_type='product' AND post_status='publish' AND post_name=%s", $base ) );
	if ( $zero ) {
		array_unshift( $groups[ $base ], $zero );
	}
}

$plan = [];
$skip = [ 'нет PN' => 0, 'PN уже в слаге' => 0, 'группа из одного' => 0, 'норматив не найден в слаге' => 0, 'PN совпадают' => 0 ];
$taken = [];

foreach ( $groups as $base => $items ) {
	if ( count( $items ) < 2 ) {
		$skip['группа из одного']++;
		continue;
	}

	// Собираем PN каждого. Если хоть у кого-то нет — группу не трогаем:
	// частичное переименование хуже, чем никакого.
	$pns = [];
	$ok  = true;
	foreach ( $items as $it ) {
		$dims = promen_get_dims( (int) $it->ID );
		$pn   = trim( (string) ( $dims['pn'] ?? '' ) );
		if ( '' === $pn ) {
			$ok = false;
			break;
		}
		$pns[ $it->ID ] = 'pn' . str_replace( '.', '-', promen_fmt_dim( $pn ) );
	}
	if ( ! $ok ) {
		$skip['нет PN']++;
		continue;
	}
	if ( count( array_unique( $pns ) ) !== count( $pns ) ) {
		$skip['PN совпадают']++; // давление не различает позиции — тут нужен другой признак
		continue;
	}
	if ( false !== strpos( $base, 'pn' ) ) {
		$skip['PN уже в слаге']++;
		continue;
	}

	foreach ( $items as $it ) {
		$norm      = (string) get_post_meta( (int) $it->ID, '_promen_norm_key', true );
		$norm_slug = '' !== $norm ? promen_translit( $norm ) : '';
		$pn_token  = $pns[ $it->ID ];

		if ( '' !== $norm_slug && false !== strpos( $base, $norm_slug ) ) {
			$new = str_replace( $norm_slug, $pn_token . '-' . $norm_slug, $base );
		} else {
			$new = $base . '-' . $pn_token;
		}

		if ( $new === $it->post_name ) {
			continue;
		}
		if ( isset( $taken[ $new ] ) ) {
			$skip['норматив не найден в слаге']++;
			continue;
		}
		$taken[ $new ] = true;
		$plan[] = [ 'id' => (int) $it->ID, 'old' => $it->post_name, 'new' => $new, 'title' => $it->post_title ];
	}
}

WP_CLI::log( 'К переименованию: ' . count( $plan ) );
foreach ( $skip as $why => $n ) {
	if ( $n ) {
		WP_CLI::log( sprintf( '  пропущено (%s): %d групп', $why, $n ) );
	}
}

// Проверяем, что новые слаги ни с чем не столкнутся.
$conflicts = 0;
foreach ( $plan as $p ) {
	$busy = $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND ID<>%d AND post_type='product'",
		$p['new'], $p['id'] ) );
	if ( $busy ) {
		WP_CLI::warning( "занят: {$p['new']} (ID {$busy})" );
		$conflicts++;
	}
}
WP_CLI::log( 'Конфликтов: ' . $conflicts );

WP_CLI::log( "\nПримеры:" );
foreach ( array_slice( $plan, 0, 12 ) as $p ) {
	WP_CLI::log( sprintf( '  %s%s     -> %s', $p['old'], str_repeat( ' ', max( 0, 44 - strlen( $p['old'] ) ) ), $p['new'] ) );
}

if ( $sql_path ) {
	$sql = "-- Слаги товаров: числовой хвост → PN. Сгенерировано slug_pn_fix.php\n";
	foreach ( $plan as $p ) {
		$sql .= sprintf( "UPDATE wp_posts SET post_name='%s' WHERE ID=%d AND post_type='product';\n",
			esc_sql( $p['new'] ), $p['id'] );
	}
	file_put_contents( $sql_path, $sql );
	WP_CLI::log( "\nSQL: {$sql_path} (" . count( $plan ) . ' операций)' );
}

if ( ! $apply ) {
	WP_CLI::log( "\nЭто предпросмотр. Для записи добавьте --apply" );
	return;
}
if ( $conflicts ) {
	WP_CLI::error( 'Есть конфликты — ничего не меняю' );
}

// Через wp_update_post, а не прямым UPDATE: тогда ядро само запишет
// _wp_old_slug и старые адреса будут отдавать 301, а не 404. Прямой запрос
// этот хук обходит — на нём я и споткнулся при первом прогоне.
$done = 0;
$fail = 0;
foreach ( $plan as $p ) {
	$res = wp_update_post( [ 'ID' => $p['id'], 'post_name' => $p['new'] ], true );
	if ( is_wp_error( $res ) ) {
		WP_CLI::warning( "ID {$p['id']}: " . $res->get_error_message() );
		$fail++;
		continue;
	}
	$done++;
	if ( 0 === $done % 100 ) {
		WP_CLI::log( "  … {$done}/" . count( $plan ) );
	}
}
if ( $fail ) {
	WP_CLI::warning( "не переименовано: {$fail}" );
}
WP_CLI::success( "Переименовано: {$done}. Дальше: wp promen catalog-rebuild && wp promen search-reindex" );
