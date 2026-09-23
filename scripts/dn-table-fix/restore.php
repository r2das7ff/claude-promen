<?php
/**
 * Откат правки DN из слепка backup.php: _promen_dims и описание как было.
 *
 * Запуск:  wp eval-file scripts/dn-table-fix/restore.php <snapshot.json>
 */

$in   = $args[0] ?? '';
$snap = is_readable( $in ) ? json_decode( (string) file_get_contents( $in ), true ) : null;
if ( ! is_array( $snap ) ) {
	echo "Нет слепка.\n";
	return;
}
$i = 0;
foreach ( $snap as $id => $row ) {
	update_post_meta( (int) $id, '_promen_dims', wp_slash( $row['dims'] ) );
	wp_update_post( [ 'ID' => (int) $id, 'post_content' => $row['content'] ] );
	clean_post_cache( (int) $id );
	promen_catalog_upsert( (int) $id, false );
	if ( ++$i % 200 === 0 ) {
		wp_cache_flush();
	}
}
printf( "Восстановлено: %d\n", $i );
