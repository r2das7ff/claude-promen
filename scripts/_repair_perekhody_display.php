<?php
/**
 * Repair weird reducer display titles («Переходы 10/20»).
 *
 * - Persist OD enrichment from DN into _promen_dims
 * - Enrich ОСТ 34.10.423 from SKU …-{D}x{d}
 * - Trash broken ОСТ 34-42-664 duplicates (good copies live in tochenye)
 * - Trash СТО 95.119 junk (reimport separately)
 *
 * Usage: wp eval-file scripts/_repair_perekhody_display.php
 */

defined( 'ABSPATH' ) || exit;

$q = new WP_Query(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'draft', 'private' ],
		'posts_per_page' => -1,
		'tax_query'      => [
			[
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => 'perekhody',
			],
		],
		'fields'         => 'ids',
		'no_found_rows'  => true,
	]
);

$stats = [
	'enriched'   => 0,
	'sku_423'    => 0,
	'trash_664'  => 0,
	'trash_95119'=> 0,
	'skipped'    => 0,
];

foreach ( $q->posts as $id ) {
	$id    = (int) $id;
	$sku   = (string) get_post_meta( $id, '_sku', true );
	$title = (string) get_the_title( $id );
	$raw   = json_decode( (string) get_post_meta( $id, '_promen_dims', true ), true );
	if ( ! is_array( $raw ) ) {
		$raw = [];
	}

	// Broken turned duplicates — real rows are in tochenye.
	if ( str_contains( $sku, 'comp-ост-34-42-664' ) || str_contains( $sku, 'comp-ost-34-42-664' ) ) {
		wp_trash_post( $id );
		if ( function_exists( 'promen_catalog_delete' ) ) {
			promen_catalog_delete( $id );
		}
		$stats['trash_664']++;
		continue;
	}

	// СТО 95.119 — full reimport; drop scrambled rows.
	if ( str_contains( $sku, 'сто-95-119' ) || str_contains( $sku, 'sto-95-119' )
		|| str_contains( $title, 'СТО 95.119' ) || str_contains( $title, 'СТО 95 119' )
	) {
		wp_trash_post( $id );
		if ( function_exists( 'promen_catalog_delete' ) ) {
			promen_catalog_delete( $id );
		}
		$stats['trash_95119']++;
		continue;
	}

	$changed = false;

	// ОСТ 34.10.423: SKU …--{a}-{D}x{d} → торцы D×d.
	if ( str_contains( $sku, 'ост-34-10-423' ) || str_contains( $sku, 'ost-34-10-423' ) ) {
		if ( preg_match( '/--(\d+(?:[.,]\d+)?)-(\d+(?:[.,]\d+)?)x(\d+(?:[.,]\d+)?)$/u', $sku, $m ) ) {
			$od  = str_replace( ',', '.', $m[2] );
			$od2 = str_replace( ',', '.', $m[3] );
			$raw['outer_diameter'] = $od;
			$raw['outer_d_branch'] = $od2;
			unset( $raw['wall_thickness'], $raw['wall_branch'] );
			$dn  = promen_pipe_dn_from_od( $od );
			$dn2 = promen_pipe_dn_from_od( $od2 );
			if ( $dn !== '' ) {
				$raw['dn'] = $dn;
			}
			if ( $dn2 !== '' ) {
				$raw['dn_branch'] = $dn2;
			}
			$new_title = trim( sprintf( 'Переход %s-%s ОСТ 34.10.423-1990', $od, $od2 ) );
			if ( $new_title !== $title ) {
				wp_update_post(
					[
						'ID'         => $id,
						'post_title' => $new_title,
					]
				);
				$title = $new_title;
			}
			$changed = true;
			$stats['sku_423']++;
		}
	}

	// ОСТ 34.10.424: SKU …--{OD?}-{DN}x{DN2}.
	if ( str_contains( $sku, 'ост-34-10-424' ) || str_contains( $sku, 'ost-34-10-424' ) ) {
		if ( preg_match( '/--(\d+(?:[.,]\d+)?)-(\d+(?:[.,]\d+)?)x(\d+(?:[.,]\d+)?)$/u', $sku, $m ) ) {
			$hint = str_replace( ',', '.', $m[1] );
			$dn   = str_replace( ',', '.', $m[2] );
			$dn2  = str_replace( ',', '.', $m[3] );
			$from_hint = promen_pipe_dn_from_od( $hint );
			$od        = ( $from_hint !== '' && abs( (float) $from_hint - (float) $dn ) < 1e-6 )
				? $hint
				: ( promen_pipe_od_from_dn( $dn ) ?: $hint );
			$od2 = promen_pipe_od_from_dn( $dn2 );
			if ( $od2 === '' ) {
				$od2 = $hint;
			}
			$raw['dn']             = $dn;
			$raw['dn_branch']      = $dn2;
			$raw['outer_diameter'] = $od;
			$raw['outer_d_branch'] = $od2;
			if ( isset( $raw['pn'] ) && is_numeric( $raw['pn'] ) && (float) $raw['pn'] > 100 ) {
				unset( $raw['pn'] );
			}
			$new_title = sprintf( 'Переход %s×%s %s-%s ОСТ 34.10.424-1990', $dn, $dn2, $od, $od2 );
			if ( $new_title !== $title ) {
				wp_update_post(
					[
						'ID'         => $id,
						'post_title' => $new_title,
					]
				);
				$title = $new_title;
			}
			$changed = true;
		}
	}

	// Persist sanitize enrichment (DN→OD, clear DN-as-OD2).
	$san = promen_sanitize_dims( $raw );
	foreach ( [ 'outer_diameter', 'outer_d_branch', 'dn', 'dn_branch', 'wall_thickness', 'wall_branch' ] as $key ) {
		$before = (string) ( $raw[ $key ] ?? '' );
		$after  = (string) ( $san[ $key ] ?? '' );
		if ( $after !== '' && $after !== $before ) {
			$raw[ $key ] = $after;
			$changed     = true;
		}
		if ( $after === '' && $before !== '' && in_array( $key, [ 'outer_d_branch' ], true )
			&& isset( $san['outer_diameter'] )
		) {
			// Cleared mistaken DN-as-OD2.
			unset( $raw[ $key ] );
			$changed = true;
		}
	}
	// If sanitize dropped a mistaken OD2 and filled a real one, keep filled.
	if ( isset( $san['outer_d_branch'] ) && (string) ( $raw['outer_d_branch'] ?? '' ) === '' ) {
		$raw['outer_d_branch'] = $san['outer_d_branch'];
		$changed               = true;
	}
	if ( isset( $san['outer_diameter'] ) && (string) ( $raw['outer_diameter'] ?? '' ) === '' ) {
		$raw['outer_diameter'] = $san['outer_diameter'];
		$changed               = true;
	}

	if ( ! $changed ) {
		$stats['skipped']++;
		continue;
	}

	update_post_meta( $id, '_promen_dims', wp_json_encode( $raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	if ( function_exists( 'promen_catalog_upsert' ) ) {
		promen_catalog_upsert( $id );
	}
	$stats['enriched']++;
}

WP_CLI::success(
	sprintf(
		'enriched=%d sku423=%d trash664=%d trash95119=%d skipped=%d',
		$stats['enriched'],
		$stats['sku_423'],
		$stats['trash_664'],
		$stats['trash_95119'],
		$stats['skipped']
	)
);
