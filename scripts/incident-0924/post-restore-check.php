<?php
/**
 * Проверка базы после восстановления прав (инцидент 24.09.2026).
 *
 * Только чтение и только лёгкие запросы: счётчики по индексам, метаданные
 * таблиц (SHOW TABLE STATUS — без сканирования), опции. Один проход, одна
 * загрузка WordPress — база сразу после разблокировки не должна увидеть всплеск.
 *
 *   ssh promen-prod 'cd ~/prom-en.com/public_html && wp eval-file ~/incident-0924/post-restore-check.php'
 *
 * Персональных данных не печатает: по заявкам — только количество и даты.
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;
$t0  = microtime( true );
$out = [];

$out['mysql']   = $wpdb->get_var( 'SELECT VERSION()' );
$out['prefix']  = $wpdb->prefix;
$out['home']    = get_option( 'home' );
$out['siteurl'] = get_option( 'siteurl' );
$out['wc_db']   = get_option( 'woocommerce_db_version' );
$out['canon_v'] = get_option( 'promen_catalog_db_version' );

$c = wp_count_posts( 'product' );
$out['products'] = [
	'publish' => (int) $c->publish,
	'draft'   => (int) $c->draft,
	'private' => (int) $c->private,
	'trash'   => (int) $c->trash,
];

// Канон реестра против опубликованных товаров: расхождение — «тихая» поломка каталога.
$canon = $wpdb->prefix . 'promen_catalog_rows';
$out['canon'] = [
	'rows'                 => (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$canon}`" ),
	'published_no_row'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} p LEFT JOIN `{$canon}` c ON c.product_id = p.ID WHERE p.post_type = 'product' AND p.post_status = 'publish' AND c.product_id IS NULL" ),
	'row_not_published'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$canon}` c LEFT JOIN {$wpdb->posts} p ON p.ID = c.product_id AND p.post_type = 'product' AND p.post_status = 'publish' WHERE p.ID IS NULL" ),
	'last_update'          => $wpdb->get_var( "SELECT MAX(updated_at) FROM `{$canon}`" ),
	'by_category_top'      => $wpdb->get_results( "SELECT category, COUNT(*) n FROM `{$canon}` GROUP BY category ORDER BY n DESC LIMIT 25", ARRAY_A ),
];
if ( $out['canon']['row_not_published'] > 0 ) {
	$out['canon']['row_not_published_sample'] = $wpdb->get_col( "SELECT c.product_id FROM `{$canon}` c LEFT JOIN {$wpdb->posts} p ON p.ID = c.product_id AND p.post_type = 'product' AND p.post_status = 'publish' WHERE p.ID IS NULL LIMIT 60" );
}
if ( $out['canon']['published_no_row'] > 0 ) {
	$out['canon']['published_no_row_sample'] = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN `{$canon}` c ON c.product_id = p.ID WHERE p.post_type = 'product' AND p.post_status = 'publish' AND c.product_id IS NULL LIMIT 60" );
}

// Таблицы: движок, строки, признаки повреждения — по метаданным, без CHECK TABLE.
$tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
$bad    = [];
$size   = 0;
foreach ( (array) $tables as $row ) {
	$size += (int) $row['Data_length'] + (int) $row['Index_length'];
	if ( empty( $row['Engine'] ) || false !== stripos( (string) $row['Comment'], 'crash' ) || false !== stripos( (string) $row['Comment'], 'corrupt' ) ) {
		$bad[] = [ $row['Name'], $row['Engine'], $row['Comment'] ];
	}
}
$out['tables'] = [ 'count' => count( (array) $tables ), 'size_mb' => round( $size / 1048576, 1 ), 'suspicious' => $bad ];

$out['options'] = [
	'autoload_kb' => (int) round( (int) $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload IN ('yes','on','auto-on','auto')" ) / 1024 ),
	'transients'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_%'" ),
];

$out['requests_cpt'] = $wpdb->get_row( "SELECT COUNT(*) AS last_48h, MAX(post_date) AS latest FROM {$wpdb->posts} WHERE post_type = 'promen_request' AND post_date > NOW() - INTERVAL 2 DAY", ARRAY_A );

// Просроченные задачи WP-Cron: после простоя их может скопиться пачка.
$overdue = [];
foreach ( (array) _get_cron_array() as $ts => $hooks ) {
	if ( $ts > time() ) {
		continue;
	}
	foreach ( (array) $hooks as $hook => $events ) {
		$overdue[ $hook ] = ( $overdue[ $hook ] ?? 0 ) + count( (array) $events );
	}
}
arsort( $overdue );
$out['cron_overdue'] = array_slice( $overdue, 0, 15, true );

$spool = dirname( rtrim( ABSPATH, '/' ) ) . '/leads-spool';
$out['leads_spool'] = is_dir( $spool ) ? count( (array) glob( $spool . '/*.json' ) ) : 0;

$out['db_error'] = $wpdb->last_error;
$out['ms']       = (int) round( ( microtime( true ) - $t0 ) * 1000 );

echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
