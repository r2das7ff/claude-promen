<?php
/**
 * Plugin Name: PROM-EN — проверка живости и заявки при сбое базы
 * Description: /wp-json/promen/v1/health для внешнего мониторинга; разбор заявок, принятых db-error.php.
 *
 * Зачем. 23–24.09.2026 сайт больше десяти часов не работал, а UptimeRobot
 * молчал: он смотрел главную, а её отдавал полностраничный кеш без обращения
 * к базе — 200 на каждой проверке. Этот адрес идёт мимо кеша (/wp-json
 * исключён в advanced-cache.php) и каждый раз спрашивает MySQL. Упала база —
 * db-error.php отвечает 503, монитор это видит.
 *
 * Кроме соединения проверяем канон каталога: пустая таблица при живой базе —
 * «тихая» поломка, когда страницы отдают 200, а каталог пуст (DEPLOY-HOME.md).
 *
 * Мониторинг: UptimeRobot → HTTP(s) → https://prom-en.com/wp-json/promen/v1/health,
 * тревога на любой код, кроме 200 (или keyword `"ok":true`).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Хранилище заявок, принятых при лежащей базе. Тот же выбор, что promen_dbe_spool_dir()
 * в wp-content/db-error.php: рядом с public_html, а если туда не записать — во временный каталог.
 */
function promen_leads_spool_dir(): string {
	$dir = dirname( rtrim( ABSPATH, '/\\' ) ) . '/leads-spool';
	if ( ! is_dir( $dir ) ) {
		$tmp = rtrim( sys_get_temp_dir(), '/\\' ) . '/promen-leads-spool';
		if ( is_dir( $tmp ) ) {
			return $tmp;
		}
	}
	return $dir;
}

/** @return string[] пути к JSON-файлам неразобранных заявок */
function promen_leads_spooled(): array {
	$files = glob( promen_leads_spool_dir() . '/*.json' );
	return is_array( $files ) ? $files : [];
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'promen/v1', '/health', [
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'promen_health_check',
	] );
} );

function promen_health_check(): WP_REST_Response {
	global $wpdb;
	$t0 = microtime( true );

	$db   = 1 === (int) $wpdb->get_var( 'SELECT 1' );
	$rows = null;
	if ( $db ) {
		$table = function_exists( 'promen_catalog_table_name' ) ? promen_catalog_table_name() : $wpdb->prefix . 'promen_catalog_rows';
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
		$rows  = null === $count ? null : (int) $count;
	}
	// Порог с запасом: в каноне ~15 тыс. строк, пустой или обрезанный — поломка.
	$ok = $db && null !== $rows && $rows > 1000;

	$out = [
		'ok'           => $ok,
		'db'           => $db,
		'catalog_rows' => $rows,
		'ms'           => (int) round( ( microtime( true ) - $t0 ) * 1000 ),
		'leads_spool'  => count( promen_leads_spooled() ),
		'time'         => gmdate( 'c' ),
	];
	$down = @filemtime( WP_CONTENT_DIR . '/cache/promen-db-down' );
	if ( $down ) {
		$out['last_db_outage'] = gmdate( 'c', $down );
	}

	$res = new WP_REST_Response( $out, $ok ? 200 : 503 );
	$res->header( 'Cache-Control', 'no-store' );
	return $res;
}

/** Напоминание в админке: заявки из хранилища не видны ни в «Заявках КП», ни в Битриксе. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$n = count( promen_leads_spooled() );
	if ( ! $n ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>Заявки, принятые при сбое базы: %d.</strong> '
		. 'Они лежат в <code>%s</code> и ушли письмом на zakaz@, если почта сработала. '
		. 'В «Заявки КП» переносит команда <code>wp promen leads-spool --import</code>; в Битрикс — вручную.</p></div>',
		(int) $n,
		esc_html( promen_leads_spool_dir() )
	);
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Заявки, принятые db-error.php, пока база лежала.
	 *
	 * ## OPTIONS
	 *
	 * [--import]
	 * : Создать записи в «Заявки КП» и перенести файлы в leads-spool/imported/.
	 *
	 * ## EXAMPLES
	 *
	 *     wp promen leads-spool
	 *     wp promen leads-spool --import
	 */
	WP_CLI::add_command( 'promen leads-spool', function ( $args, $assoc ) {
		$files = promen_leads_spooled();
		if ( ! $files ) {
			WP_CLI::success( 'Хранилище пусто: ' . promen_leads_spool_dir() );
			return;
		}
		$import = ! empty( $assoc['import'] );
		$done   = promen_leads_spool_dir() . '/imported';
		if ( $import && ! is_dir( $done ) && ! wp_mkdir_p( $done ) ) {
			WP_CLI::error( 'Не создать ' . $done );
		}
		foreach ( $files as $file ) {
			$lead = json_decode( (string) file_get_contents( $file ), true );
			if ( ! is_array( $lead ) ) {
				WP_CLI::warning( 'Не JSON: ' . $file );
				continue;
			}
			$contact = (string) ( $lead['fields']['contact'] ?? '' );
			$line    = sprintf( '%s  %s  %s  mail=%s%s', $lead['received_at'] ?? '?', $lead['preset'] ?? '?', $contact,
				$lead['mail'] ?? '?', ! empty( $lead['suspect'] ) ? '  [ПОДОЗРИТЕЛЬНАЯ]' : '' );
			WP_CLI::log( $line );
			if ( ! $import ) {
				continue;
			}

			$text = [ 'Принята при сбое базы данных ' . ( $lead['received_at'] ?? '' ) . ' (db-error.php).' ];
			foreach ( (array) ( $lead['fields'] ?? [] ) as $k => $v ) {
				$text[] = $k . ': ' . $v;
			}
			if ( ! empty( $lead['task'] ) ) {
				$text[] = '';
				$text[] = (string) $lead['task'];
			}
			$text[] = '';
			$text[] = 'Страница: ' . ( $lead['referer'] ?? '' );
			if ( ! empty( $lead['attachment']['file'] ) ) {
				$text[] = 'Вложение «' . $lead['attachment']['original'] . '»: ' . $done . '/' . $lead['attachment']['file'];
			}
			$post_id = wp_insert_post( [
				'post_type'    => 'promen_request',
				'post_status'  => 'private',
				'post_title'   => ( ! empty( $lead['suspect'] ) ? 'СПАМ? · ' : '' ) . 'Сбой БД · ' . $contact . ' — ' . ( $lead['received_at'] ?? '' ),
				'post_content' => implode( "\n", $text ),
			], true );
			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( $post_id->get_error_message() );
				continue;
			}
			update_post_meta( $post_id, '_promen_contact', $contact );
			foreach ( [ 'preset', 'referer', 'ip', 'ua' ] as $meta ) {
				if ( ! empty( $lead[ $meta ] ) ) {
					update_post_meta( $post_id, '_promen_' . $meta, (string) $lead[ $meta ] );
				}
			}
			foreach ( (array) ( $lead['attribution'] ?? [] ) as $k => $v ) {
				if ( '' !== (string) $v ) {
					update_post_meta( $post_id, '_promen_' . $k, (string) $v );
				}
			}
			update_post_meta( $post_id, '_promen_via', 'db-error' );
			if ( ! empty( $lead['attachment']['file'] ) ) {
				@rename( dirname( $file ) . '/' . $lead['attachment']['file'], $done . '/' . $lead['attachment']['file'] );
			}
			rename( $file, $done . '/' . basename( $file ) );
			WP_CLI::log( '  → заявка #' . $post_id );
		}
		WP_CLI::success( ( $import ? 'Перенесено: ' : 'Заявок в хранилище: ' ) . count( $files ) );
	} );
}
