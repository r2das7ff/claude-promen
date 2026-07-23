<?php
/**
 * Витрина пилота. В базе — только подключённые категории (отводы, тройники…),
 * поэтому корень каталога показывает всё без принудительного фильтра;
 * ограничение по группе задаётся ?group= из сайдбара.
 */

defined( 'ABSPATH' ) || exit;

const PROMEN_PILOT_CAT = 'otvody';

/**
 * Структура пилота повторяет дизайн: /catalog/ (реестр) → /catalog/sdt/
 * (страница категории sdt.html) → /catalog/sdt/otvody/ (реестр отводов) → товар.
 * Крошки строятся штатно (Главная → Каталог → СДТ → Отводы → товар).
 * Пустые категории вне пилота — в реестр каталога.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || ! is_tax( 'product_cat' ) ) {
		return;
	}
	$term = get_queried_object();
	if ( ! $term ) {
		return;
	}
	// Категории с товарами (свои или у детей) живут своей жизнью;
	// пустые — в каталог, пока не наполнены.
	$count = (int) $term->count;
	foreach ( get_term_children( $term->term_id, 'product_cat' ) as $child_id ) {
		$child  = get_term( $child_id, 'product_cat' );
		$count += $child ? (int) $child->count : 0;
	}
	if ( $count > 0 ) {
		return;
	}
	wp_safe_redirect( wc_get_page_permalink( 'shop' ), 302 );
	exit;
}, 5 );

function promen_otvody_term() {
	return get_term_by( 'slug', PROMEN_PILOT_CAT, 'product_cat' );
}

function promen_otvody_url(): string {
	$t = promen_otvody_term();
	return $t ? get_term_link( $t ) : home_url( '/catalog/sdt/otvody/' );
}
