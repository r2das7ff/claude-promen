<?php
/**
 * Сверка схем инженерного чертежа с изделиями.
 *
 * Прогоняет promen_blueprint_type() по всему каталогу и группирует результат по
 * связке «категория + семейство + код типа + норматив». Смотреть надо не на
 * отдельные карточки, а на такие группы: схема выбирается именно по ним.
 *
 *   docker compose run --rm wpcli eval-file /scripts/otk-fix/audit_blueprints.php
 */

defined( 'ABSPATH' ) || die( "только через wp eval-file\n" );

global $wpdb;

$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish'" );

$groups = [];
foreach ( $ids as $pid ) {
	$dims = json_decode( (string) get_post_meta( $pid, '_promen_dims', true ), true );
	$dims = is_array( $dims ) ? $dims : [];
	$fam  = (string) get_post_meta( $pid, '_promen_family', true );
	$norm = (string) get_post_meta( $pid, '_promen_norm_key', true );

	$cats = wp_get_post_terms( $pid, 'product_cat', [ 'fields' => 'slugs' ] );
	$cat  = is_wp_error( $cats ) || ! $cats ? '' : end( $cats );

	$type = promen_blueprint_type( $fam, $dims, $norm, $cat );
	$pt   = (string) ( $dims['product_type'] ?? '' );

	$key = $type . "\t" . $cat . "\t" . $fam . "\t" . $pt . "\t" . $norm;
	if ( ! isset( $groups[ $key ] ) ) {
		$groups[ $key ] = [ 'n' => 0, 'sample' => get_post_field( 'post_title', $pid ) ];
	}
	$groups[ $key ]['n']++;
}

uasort( $groups, fn( $a, $b ) => $b['n'] <=> $a['n'] );

$fh = fopen( __DIR__ . '/blueprint-audit.tsv', 'w' );
fwrite( $fh, "схема\tкатегория\tсемейство\tтип\tнорматив\tпозиций\tпример\n" );
foreach ( $groups as $key => $g ) {
	fwrite( $fh, $key . "\t" . $g['n'] . "\t" . str_replace( "\t", ' ', $g['sample'] ) . "\n" );
}
fclose( $fh );

$by_type = [];
foreach ( $groups as $key => $g ) {
	$t = explode( "\t", $key )[0];
	$by_type[ $t ] = ( $by_type[ $t ] ?? 0 ) + $g['n'];
}
arsort( $by_type );
echo "групп: " . count( $groups ) . "\n";
foreach ( $by_type as $t => $n ) {
	printf( "  %-9s %6d\n", $t, $n );
}
