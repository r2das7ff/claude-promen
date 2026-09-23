<?php
/**
 * Слепок перед правкой: _promen_dims и описание всех позиций из changes.tsv.
 * Откат — restore.php из того же слепка.
 *
 * Запуск:  wp eval-file scripts/dn-table-fix/backup.php <changes.tsv> <out.json>
 */

$in  = $args[0] ?? '';
$out = $args[1] ?? '';
if ( '' === $in || '' === $out || ! is_readable( $in ) ) {
	echo "Нужны пути: changes.tsv и файл слепка.\n";
	return;
}

$snap = [];
foreach ( file( $in, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
	$id = (int) strtok( $line, "\t" );
	if ( $id <= 0 ) {
		continue;
	}
	$snap[ $id ] = [
		'dims'    => (string) get_post_meta( $id, '_promen_dims', true ),
		'content' => (string) get_post_field( 'post_content', $id ),
	];
}
file_put_contents( $out, wp_json_encode( $snap, JSON_UNESCAPED_UNICODE ) );
printf( "Слепок: %d позиций → %s (%d байт)\n", count( $snap ), $out, filesize( $out ) );
