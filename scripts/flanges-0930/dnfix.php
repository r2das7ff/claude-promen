<?php
global $wpdb;
$set = json_decode( (string) file_get_contents( '/tmp/dnfix_set.json' ), true );
$snap = []; $done = 0;
foreach ( $set as $id => $f ) {
	$id = (int) $id; $old = $f['_old_dn']; unset( $f['_old_dn'] );
	$post = get_post( $id );
	$snap[ $id ] = [ 'dims' => get_post_meta( $id, '_promen_dims', true ), 'content' => $post->post_content ];
	$d = array_merge( json_decode( (string) $snap[ $id ]['dims'], true ) ?: [], $f );
	update_post_meta( $id, '_promen_dims', wp_json_encode( $d, JSON_UNESCAPED_UNICODE ) );
	$c = preg_replace( '/\bDN ' . preg_quote( $old, '/' ) . '\./u', 'DN ' . $f['dn'] . '.', $post->post_content, 1 );
	if ( $c !== $post->post_content ) $wpdb->update( $wpdb->posts, [ 'post_content' => $c ], [ 'ID' => $id ] );
	clean_post_cache( $id );
	promen_catalog_upsert( $id, false );
	$done++;
}
file_put_contents( getenv( 'HOME' ) . '/dnfix-flanges-snapshot-' . gmdate( 'Ymd-His' ) . '.json', wp_json_encode( $snap, JSON_UNESCAPED_UNICODE ) );
wp_cache_flush(); promen_filters_cache_bump();
echo "исправлено $done\n";
foreach ( [ 114793, 117321, 146948 ] as $id ) echo "$id " . get_the_title( $id ) . ' | ' . get_post_meta( $id, '_promen_dims', true ) . ' | ' . wp_strip_all_tags( get_post_field( 'post_content', $id ) ) . "\n";
