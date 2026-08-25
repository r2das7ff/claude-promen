<?php
/**
 * Хром сайта: nav (+бургер и мобильный drawer) и левая рельса .strip.
 * Разметка — html/ (Open Design, ревизия 2026-07-22/23).
 * Пункты меню — promen_nav_items(): появляются по мере публикации страниц.
 */
$promen_nav = promen_nav_items();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Пропуск навигации: первая остановка Tab, видима только при фокусе. -->
<a class="skip-link" href="#main">Перейти к содержимому</a>

<!-- NAV -->
<nav class="nav">
  <div class="nav-brand">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-logo">
      <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/PE_logo_black.png' ) ); ?>" alt="Промышленная Энергетика" width="2000" height="414" fetchpriority="high" decoding="async">
    </a>
  </div>
  <ul class="nav-links">
    <?php foreach ( $promen_nav as $item ) : ?>
      <li><a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['active'] ? ' class="is-active"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="nav-meta" id="navClock"></div>
  <button type="button" class="nav-cta cta-grow" onclick="openRequestModal('tz')">Отправить ТЗ <span class="nav-cta-arr" aria-hidden="true">→</span></button>
  <button type="button" class="nav-burger" id="navBurger" aria-label="Открыть меню" aria-expanded="false" aria-controls="navDrawer">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- MOBILE DRAWER -->
<div class="nav-drawer-overlay" id="navDrawerOverlay"></div>
<aside class="nav-drawer" id="navDrawer" aria-hidden="true">
  <div class="nav-drawer-head"><span class="nav-drawer-lbl">Меню</span></div>
  <ul class="nav-drawer-links">
    <?php foreach ( $promen_nav as $i => $item ) : ?>
      <li><a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['active'] ? ' class="is-active"' : ''; ?>><span class="n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( $item['label'] ); ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="nav-drawer-foot">
    <div class="nav-drawer-clock" id="navDrawerClock"></div>
    <button type="button" class="nav-drawer-cta" onclick="openRequestModal('tz')">Отправить ТЗ →</button>
  </div>
</aside>

<!-- STRIP -->
<div class="strip">
  <span class="strip-yr"><?php echo esc_html( date_i18n( 'Y' ) ); ?></span>
  <div class="strip-pulse"></div>
  <span class="strip-txt"><?php echo esc_html( promen_strip_text() ); ?></span>
</div>

<main id="main" class="site-main">
