<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- NAV -->
<nav class="nav">
  <div class="nav-brand">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-logo">
      <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/PE_logo_black.png' ) ); ?>" alt="Промышленная Энергетика">
    </a>
  </div>
  <ul class="nav-links">
    <?php foreach ( promen_nav_items() as $item ) : ?>
      <li><a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['active'] ? ' class="is-active"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="nav-meta" id="navClock"></div>
  <a class="nav-cta" href="#request">Отправить ТЗ →</a>
</nav>

<!-- STRIP -->
<div class="strip">
  <span class="strip-yr"><?php echo esc_html( date_i18n( 'Y' ) ); ?></span>
  <div class="strip-pulse"></div>
  <span class="strip-txt">КТЛ–01</span>
</div>
