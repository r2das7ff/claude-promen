<?php
/**
 * Давление в описаниях фланцев: «PN 40 МПа» → «PN 40 (4 МПа)» — 30.09.2026.
 *
 * У номинальных фланцев (ГОСТ 33259, 12820, 12821, 28759.2 — список
 * promen_pn_nominal_norms) импорт писал в описание «PN 40 МПа»: PN 40 — это
 * 4 МПа, так что давление завышено вдесятеро. У части ГОСТ 12821 наоборот:
 * «PN 2.5 МПа» — МПа с подписью PN. Описание видно под заголовком карточки и
 * уходит в meta description.
 *
 * PN берём из _promen_dims.pn (номинал). Правим, только если число в тексте
 * равно PN или PN/10 — иначе в отчёт, без правки.
 *
 * Запуск через include-обёртку: $args = [] (отчёт) / ['apply'].
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Только через WP-CLI\n" );
}

global $wpdb;

$apply = in_array( 'apply', isset( $args ) && is_array( $args ) ? $args : [], true );
$ru    = static fn( float $v ): string => str_replace( '.', ',', promen_fmt_dim( (string) $v ) );

$plan = []; $skip = []; $by = [];
foreach ( promen_pn_nominal_norms() as $slug ) {
	$term = get_term_by( 'slug', $slug, 'norm' );
	foreach ( $term ? (array) get_objects_in_term( [ $term->term_id ], 'norm' ) : [] as $id ) {
		$id   = (int) $id;
		$dims = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true ) ?: [];
		$pn   = (float) str_replace( ',', '.', (string) ( $dims['pn'] ?? '' ) );
		if ( $pn <= 0 ) {
			continue;
		}
		$label = 'PN ' . $ru( $pn ) . ' (' . $ru( $pn / 10 ) . ' МПа)';
		$post  = get_post( $id );
		$new   = [];
		foreach ( [ 'post_content', 'post_excerpt' ] as $f ) {
			$text = (string) $post->$f;
			$out  = preg_replace_callback( '/PN\s*([\d]+(?:[.,]\d+)?)\s*МПа/u', static function ( $m ) use ( $pn, $label, $id, &$skip ) {
				$v = (float) str_replace( ',', '.', $m[1] );
				if ( abs( $v - $pn ) < 1e-6 || abs( $v - $pn / 10 ) < 1e-6 ) {
					return $label;
				}
				$skip[] = "{$id}: «{$m[0]}» при PN {$pn}";
				return $m[0];
			}, $text );
			if ( $out !== $text ) {
				$new[ $f ] = $out;
			}
		}
		if ( $new ) {
			$plan[ $id ] = $new;
			$by[ $slug ] = ( $by[ $slug ] ?? 0 ) + 1;
			if ( ! isset( $shown[ $slug ] ) ) {
				$shown[ $slug ] = true;
				preg_match( '/[^.]*PN[^.]*\./u', (string) $post->post_content, $a );
				preg_match( '/[^.]*PN[^.]*\./u', (string) ( $new['post_content'] ?? '' ), $b );
				WP_CLI::log( "  {$slug}: «" . trim( $a[0] ?? '' ) . '» → «' . trim( $b[0] ?? '' ) . '»' );
			}
		}
	}
}
WP_CLI::log( 'К правке: ' . count( $plan ) . ' ' . wp_json_encode( $by ) . '; пропущено: ' . count( $skip ) );
foreach ( array_slice( $skip, 0, 10 ) as $s ) {
	WP_CLI::log( '  пропуск ' . $s );
}

if ( ! $apply ) {
	WP_CLI::log( 'Отчёт. Для правки: $args = [ "apply" ]' );
	return;
}

$snap = [];
foreach ( array_keys( $plan ) as $id ) {
	$snap[ $id ] = [ 'post_content' => get_post_field( 'post_content', $id ), 'post_excerpt' => get_post_field( 'post_excerpt', $id ) ];
}
$file = rtrim( (string) getenv( 'HOME' ), '/' ) . '/pressure-desc-snapshot-' . gmdate( 'Ymd-His' ) . '.json';
file_put_contents( $file, wp_json_encode( $snap, JSON_UNESCAPED_UNICODE ) );
WP_CLI::log( 'Слепок: ' . $file );

$done = 0;
foreach ( $plan as $id => $fields ) {
	// Прямой UPDATE: wp_update_post пересобрал бы слаг и дёрнул save_post у Woo.
	$wpdb->update( $wpdb->posts, $fields, [ 'ID' => $id ] );
	clean_post_cache( $id );
	if ( 0 === ++$done % 200 ) {
		wp_cache_flush();
	}
}
wp_cache_flush();
WP_CLI::success( "Описаний исправлено: {$done}. Дальше: wp promen cache-purge." );
