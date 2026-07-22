<?php
/**
 * SEO: title/description шаблоны и sitemap для taxonomy norm.
 * Без Yoast — лёгкий слой поверх title-tag + wp_sitemaps.
 */

defined( 'ABSPATH' ) || exit;

/** Document title по типу страницы. */
add_filter( 'document_title_parts', function ( array $parts ): array {
	$brand = 'Промышленная Энергетика';

	if ( function_exists( 'is_product' ) && is_product() ) {
		$parts['title'] = get_the_title();
		$parts['site']  = $brand;
		return $parts;
	}

	if ( is_tax( 'product_cat' ) || is_tax( 'norm' ) ) {
		$term = get_queried_object();
		$parts['title'] = $term->name;
		$parts['site']  = 'Каталог · ' . $brand;
		return $parts;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$parts['title'] = 'Каталог продукции';
		$parts['site']  = $brand;
	}

	return $parts;
} );

/** Meta description из первого абзаца описания термина / товара. */
add_action( 'wp_head', function () {
	$desc = '';

	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$desc = wp_strip_all_tags( promen_sanitize_desc( $p->get_id(), $p->get_short_description() ?: $p->get_description() ) );
		}
	} elseif ( is_tax( 'product_cat' ) || is_tax( 'norm' ) ) {
		$term = get_queried_object();
		if ( $term && $term->description ) {
			$parts = preg_split( '/\n\s*\n/', trim( wp_strip_all_tags( $term->description ) ), 2 );
			$desc  = $parts[0] ?? '';
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$desc = 'Каталог соединительных деталей трубопроводов, фланцев и крепежа. Запрос коммерческого предложения без корзины.';
	}

	$desc = trim( preg_replace( '/\s+/', ' ', $desc ) );
	if ( $desc === '' ) {
		return;
	}
	if ( mb_strlen( $desc ) > 160 ) {
		$desc = mb_substr( $desc, 0, 157 ) . '…';
	}
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
}, 2 );

/** Включаем norm в core XML sitemap. */
add_filter( 'wp_sitemaps_taxonomies', function ( array $taxonomies ): array {
	$taxonomies['norm'] = get_taxonomy( 'norm' );
	return $taxonomies;
} );

/** Параметрические URL каталога не должны попадать в sitemap (их и нет — core sitemap чистый). */
add_filter( 'wp_sitemaps_posts_entry', function ( $entry, $post ) {
	if ( $post->post_type === 'product' ) {
		$entry['loc'] = get_permalink( $post );
	}
	return $entry;
}, 10, 2 );
