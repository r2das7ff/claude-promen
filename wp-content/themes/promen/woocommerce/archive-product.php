<?php
/**
 * Каталог: design-reference/katalog.html
 * Сайдбар фильтрует общую таблицу (?group=), «Страница категории →»
 * ведёт на лендинг активной группы.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$crumbs        = promen_breadcrumbs();
$group         = promen_active_group();
$total         = (int) $GLOBALS['wp_query']->found_posts;
$sdt_term       = get_term_by( 'slug', 'sdt', 'product_cat' );
$otvody_term    = get_term_by( 'slug', 'otvody', 'product_cat' );
$troyniki_term  = get_term_by( 'slug', 'troyniki', 'product_cat' );
$perekhody_term = get_term_by( 'slug', 'perekhody', 'product_cat' );
$dnishcha_term  = get_term_by( 'slug', 'dnishcha', 'product_cat' );
$zaglushki_term = get_term_by( 'slug', 'zaglushki', 'product_cat' );
$flancy_term    = get_term_by( 'slug', 'flancy', 'product_cat' );
$flancy_plosk_term = get_term_by( 'slug', 'flancy-plosk', 'product_cat' );
$flancy_vorot_term = get_term_by( 'slug', 'flancy-vorot', 'product_cat' );
$flancy_01_term = get_term_by( 'slug', 'flancy-01', 'product_cat' );
$flancy_11_term = get_term_by( 'slug', 'flancy-11', 'product_cat' );
$krepezh_term   = get_term_by( 'slug', 'krepezh', 'product_cat' );
$bolty_term     = get_term_by( 'slug', 'bolty', 'product_cat' );
$gayki_term     = get_term_by( 'slug', 'gayki', 'product_cat' );
$shpilki_term   = get_term_by( 'slug', 'shpilki', 'product_cat' );
$shayby_term    = get_term_by( 'slug', 'shayby', 'product_cat' );
$vinty_term     = get_term_by( 'slug', 'vinty', 'product_cat' );
$tochenye_term  = get_term_by( 'slug', 'tochenye', 'product_cat' );
$truby_term     = get_term_by( 'slug', 'truby', 'product_cat' );
$truby_bsh_term = get_term_by( 'slug', 'truby-bsh', 'product_cat' );
$truby_es_term  = get_term_by( 'slug', 'truby-es', 'product_cat' );
$truby_ppu_term = get_term_by( 'slug', 'truby-ppu', 'product_cat' );
$truby_vgp_term = get_term_by( 'slug', 'truby-vgp', 'product_cat' );
$izolyatsiya_term = get_term_by( 'slug', 'izolyatsiya', 'product_cat' );
$opory_term     = get_term_by( 'slug', 'opory', 'product_cat' );
$opory_nepodv_term = get_term_by( 'slug', 'opory-nepodv', 'product_cat' );
$opory_skolz_term = get_term_by( 'slug', 'opory-skolz', 'product_cat' );
$opory_pruzh_term = get_term_by( 'slug', 'opory-pruzh', 'product_cat' );
$armatura_term  = get_term_by( 'slug', 'armatura', 'product_cat' );
$armatura_zadv_term = get_term_by( 'slug', 'armatura-zadvizhki', 'product_cat' );
$armatura_klap_term = get_term_by( 'slug', 'armatura-klapany', 'product_cat' );
$armatura_kran_term = get_term_by( 'slug', 'armatura-krany', 'product_cat' );
$otvody_n       = $otvody_term ? (int) $otvody_term->count : 0;
$troyniki_n     = $troyniki_term ? (int) $troyniki_term->count : 0;
$perekhody_n    = $perekhody_term ? (int) $perekhody_term->count : 0;
$dnishcha_n     = $dnishcha_term ? (int) $dnishcha_term->count : 0;
$zaglushki_n    = $zaglushki_term ? (int) $zaglushki_term->count : 0;
$flancy_plosk_n = $flancy_plosk_term ? (int) $flancy_plosk_term->count : 0;
$flancy_vorot_n = $flancy_vorot_term ? (int) $flancy_vorot_term->count : 0;
$flancy_01_n    = $flancy_01_term ? (int) $flancy_01_term->count : 0;
$flancy_11_n    = $flancy_11_term ? (int) $flancy_11_term->count : 0;
$flancy_n       = $flancy_plosk_n + $flancy_vorot_n + $flancy_01_n + $flancy_11_n;
if ( $flancy_n === 0 && $flancy_term ) {
	$flancy_n = (int) $flancy_term->count;
}
$krepezh_n      = $krepezh_term ? (int) $krepezh_term->count : 0;
if ( $krepezh_term && $krepezh_n === 0 ) {
	$krepezh_children = get_terms( [
		'taxonomy'   => 'product_cat',
		'parent'     => (int) $krepezh_term->term_id,
		'hide_empty' => false,
	] );
	if ( ! is_wp_error( $krepezh_children ) ) {
		foreach ( $krepezh_children as $ch ) {
			$krepezh_n += (int) $ch->count;
		}
	}
}
$bolty_n        = $bolty_term ? (int) $bolty_term->count : 0;
$gayki_n        = $gayki_term ? (int) $gayki_term->count : 0;
$shpilki_n      = $shpilki_term ? (int) $shpilki_term->count : 0;
$shayby_n       = $shayby_term ? (int) $shayby_term->count : 0;
$vinty_n        = $vinty_term ? (int) $vinty_term->count : 0;
$tochenye_n     = $tochenye_term ? (int) $tochenye_term->count : 0;
$truby_bsh_n    = $truby_bsh_term ? (int) $truby_bsh_term->count : 0;
$truby_es_n     = $truby_es_term ? (int) $truby_es_term->count : 0;
$truby_ppu_n    = $truby_ppu_term ? (int) $truby_ppu_term->count : 0;
$truby_vgp_n    = $truby_vgp_term ? (int) $truby_vgp_term->count : 0;
$truby_n        = $truby_bsh_n + $truby_es_n + $truby_ppu_n + $truby_vgp_n;
if ( $truby_n === 0 && $truby_term ) {
	$truby_n = (int) $truby_term->count;
}
$izolyatsiya_n  = $izolyatsiya_term ? (int) $izolyatsiya_term->count : 0;
$opory_nepodv_n = $opory_nepodv_term ? (int) $opory_nepodv_term->count : 0;
$opory_skolz_n  = $opory_skolz_term ? (int) $opory_skolz_term->count : 0;
$opory_pruzh_n  = $opory_pruzh_term ? (int) $opory_pruzh_term->count : 0;
$opory_n        = $opory_nepodv_n + $opory_skolz_n + $opory_pruzh_n;
if ( $opory_n === 0 && $opory_term ) {
	$opory_n = (int) $opory_term->count;
}
$armatura_zadv_n = $armatura_zadv_term ? (int) $armatura_zadv_term->count : 0;
$armatura_klap_n = $armatura_klap_term ? (int) $armatura_klap_term->count : 0;
$armatura_kran_n = $armatura_kran_term ? (int) $armatura_kran_term->count : 0;
$armatura_n     = $armatura_zadv_n + $armatura_klap_n + $armatura_kran_n;
if ( $armatura_n === 0 && $armatura_term ) {
	$armatura_n = (int) $armatura_term->count;
}
$sdt_n          = $otvody_n + $troyniki_n + $perekhody_n + $dnishcha_n + $zaglushki_n;
$is_fastener_ui = promen_is_fastener_group( $group );
$is_branch_ui   = in_array( (string) $group, [ 'troyniki', 'perekhody', 'tochenye' ], true );
// Номинальный PN правдив только у фланцев; у СДТ pn в источнике — вычисленное давление.
// Поэтому 5-я колонка: крепёж → Класс, фланцы → PN, остальные → Масса.
$is_flange_ui   = ( $group && strpos( (string) $group, 'flancy' ) === 0 );

// Колонки характеристик — СВОИ под категорию/подкатегорию (см. product-data.php).
$cat_cols = promen_catalog_columns( (string) $group );
$grid_tpl = '150px minmax(200px,1fr) '
	. implode( ' ', array_map( static fn( $c ) => $c['w'], $cat_cols ) )
	. ' 120px 32px';

$group_views = [
	''          => [ 'title' => 'РЕЕСТР ИЗДЕЛИЙ', 'path' => '/ Весь реестр', 'term' => null ],
	'sdt'       => [ 'title' => 'СДТ', 'path' => '/ Соединительные детали трубопровода', 'term' => $sdt_term ],
	'otvody'    => [ 'title' => 'ОТВОДЫ', 'path' => '/ Отводы', 'term' => $otvody_term ],
	'troyniki'  => [ 'title' => 'ТРОЙНИКИ', 'path' => '/ Тройники', 'term' => $troyniki_term ],
	'perekhody' => [ 'title' => 'ПЕРЕХОДЫ', 'path' => '/ Переходы', 'term' => $perekhody_term ],
	'dnishcha'  => [ 'title' => 'ДНИЩА', 'path' => '/ Днища', 'term' => $dnishcha_term ],
	'zaglushki' => [ 'title' => 'ЗАГЛУШКИ', 'path' => '/ Заглушки', 'term' => $zaglushki_term ],
	'flancy'    => [ 'title' => 'ФЛАНЦЫ', 'path' => '/ Фланцы', 'term' => $flancy_term ],
	'flancy-plosk' => [ 'title' => 'ПЛОСКИЕ ФП', 'path' => '/ Фланцы / Плоские ФП', 'term' => $flancy_plosk_term ],
	'flancy-vorot' => [ 'title' => 'ВОРОТНИКОВЫЕ ФВ', 'path' => '/ Фланцы / Воротниковые ФВ', 'term' => $flancy_vorot_term ],
	'flancy-01' => [ 'title' => 'ТИП 01', 'path' => '/ Фланцы / Тип 01', 'term' => $flancy_01_term ],
	'flancy-11' => [ 'title' => 'ТИП 11', 'path' => '/ Фланцы / Тип 11', 'term' => $flancy_11_term ],
	'krepezh'   => [ 'title' => 'КРЕПЁЖ', 'path' => '/ Крепёж', 'term' => $krepezh_term ],
	'bolty'     => [ 'title' => 'БОЛТЫ', 'path' => '/ Крепёж / Болты', 'term' => $bolty_term ],
	'gayki'     => [ 'title' => 'ГАЙКИ', 'path' => '/ Крепёж / Гайки', 'term' => $gayki_term ],
	'shpilki'   => [ 'title' => 'ШПИЛЬКИ', 'path' => '/ Крепёж / Шпильки', 'term' => $shpilki_term ],
	'shayby'    => [ 'title' => 'ШАЙБЫ', 'path' => '/ Крепёж / Шайбы', 'term' => $shayby_term ],
	'vinty'     => [ 'title' => 'ВИНТЫ', 'path' => '/ Крепёж / Винты', 'term' => $vinty_term ],
	'tochenye'  => [ 'title' => 'ТОЧЕНЫЕ ДЕТАЛИ', 'path' => '/ Точеные детали', 'term' => $tochenye_term ],
	'truby'     => [ 'title' => 'ТРУБЫ', 'path' => '/ Трубы', 'term' => $truby_term ],
	'truby-bsh' => [ 'title' => 'БЕСШОВНЫЕ', 'path' => '/ Трубы / Бесшовные', 'term' => $truby_bsh_term ],
	'truby-es'  => [ 'title' => 'ЭЛЕКТРОСВАРНЫЕ', 'path' => '/ Трубы / Электросварные', 'term' => $truby_es_term ],
	'truby-ppu' => [ 'title' => 'ППУ', 'path' => '/ Трубы / ППУ', 'term' => $truby_ppu_term ],
	'truby-vgp' => [ 'title' => 'ВГП', 'path' => '/ Трубы / ВГП', 'term' => $truby_vgp_term ],
	'izolyatsiya' => [ 'title' => 'ИЗОЛЯЦИЯ', 'path' => '/ Изоляция и покрытия', 'term' => $izolyatsiya_term ],
	'opory'     => [ 'title' => 'ОПОРЫ', 'path' => '/ Опоры трубопроводов', 'term' => $opory_term ],
	'opory-nepodv' => [ 'title' => 'НЕПОДВИЖНЫЕ', 'path' => '/ Опоры / Неподвижные', 'term' => $opory_nepodv_term ],
	'opory-skolz' => [ 'title' => 'СКОЛЬЗЯЩИЕ', 'path' => '/ Опоры / Скользящие', 'term' => $opory_skolz_term ],
	'opory-pruzh' => [ 'title' => 'ПРУЖИННЫЕ', 'path' => '/ Опоры / Пружинные', 'term' => $opory_pruzh_term ],
	'armatura'  => [ 'title' => 'АРМАТУРА', 'path' => '/ Арматура', 'term' => $armatura_term ],
	'armatura-zadvizhki' => [ 'title' => 'ЗАДВИЖКИ', 'path' => '/ Арматура / Задвижки', 'term' => $armatura_zadv_term ],
	'armatura-klapany' => [ 'title' => 'КЛАПАНЫ', 'path' => '/ Арматура / Клапаны', 'term' => $armatura_klap_term ],
	'armatura-krany' => [ 'title' => 'КРАНЫ', 'path' => '/ Арматура / Краны', 'term' => $armatura_kran_term ],
];
$view     = $group_views[ $group ] ?? $group_views[''];
$cat_term = $view['term'];
?>
<script type="application/ld+json"><?php echo promen_breadcrumbs_schema( $crumbs ); ?></script>

<div class="pg">

  <div class="cat-hero">
    <div>
      <nav class="hero-crumb" style="margin-bottom:18px;">
        <?php foreach ( $crumbs as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow">Цифровой инженерный реестр · пилот</div>
      <h1 class="hero-h1">Каталог<br><em>продукции</em></h1>
      <p class="hero-desc">В витрине — СДТ, фланцы, крепёж, точеные детали, трубы, изоляция, опоры и арматура: <?php echo esc_html( number_format_i18n( $sdt_n + $flancy_n + $krepezh_n + $tochenye_n + $truby_n + $izolyatsiya_n + $opory_n + $armatura_n ) ); ?> типоразмеров.</p>
    </div>
    <div class="hero-stats">
      <div class="hs"><span class="hs-v"><?php echo esc_html( number_format_i18n( $total ) ); ?></span><span class="hs-k">В реестре</span></div>
      <div class="hs"><span class="hs-v"><?php echo $is_fastener_ui ? 'M × L' : 'DN 6–1400'; ?></span><span class="hs-k">Диапазон</span></div>
      <div class="hs"><span class="hs-v">ГОСТ / ОСТ</span><span class="hs-k">Нормативная база</span></div>
    </div>
  </div>

  <div class="cat-body">

    <aside class="cat-sb">
      <div class="sb-hd">Группы продукции<span class="sb-hd-count">12</span></div>
      <nav id="catNav">
        <div class="sbn-group open" id="sbnSdt">
          <div class="sbn-item sbn-item--parent<?php echo in_array( $group, [ '', 'sdt' ], true ) ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( 'sdt' ) ); ?>">
              <div class="sbn-code">СДТ</div>
              <div class="sbn-info">
                <span class="sbn-name">Соединительные детали трубопровода</span>
                <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $sdt_n ) ); ?> поз. · 6 семейств</span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="true">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <div class="sbn-children">
            <?php if ( $otvody_term && $otvody_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'otvody' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'otvody' ) ); ?>">
              <span class="sbn-child-name">Отводы</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $otvody_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $troyniki_term && $troyniki_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'troyniki' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'troyniki' ) ); ?>">
              <span class="sbn-child-name">Тройники</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $troyniki_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $perekhody_term && $perekhody_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'perekhody' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'perekhody' ) ); ?>">
              <span class="sbn-child-name">Переходы</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $perekhody_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $dnishcha_term && $dnishcha_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'dnishcha' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'dnishcha' ) ); ?>">
              <span class="sbn-child-name">Днища</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $dnishcha_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $zaglushki_term && $zaglushki_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'zaglushki' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'zaglushki' ) ); ?>">
              <span class="sbn-child-name">Заглушки</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $zaglushki_n ) ); ?></span>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php if ( $flancy_term && $flancy_n > 0 ) :
          $flancy_groups = [ 'flancy', 'flancy-plosk', 'flancy-vorot', 'flancy-01', 'flancy-11' ];
          $flancy_open = in_array( $group, $flancy_groups, true );
        ?>
        <div class="sbn-group<?php echo $flancy_open ? ' open' : ''; ?>" id="sbnFlancy">
          <div class="sbn-item sbn-item--parent<?php echo in_array( $group, [ 'flancy' ], true ) ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( 'flancy' ) ); ?>">
              <div class="sbn-code">ФЛ</div>
              <div class="sbn-info">
                <span class="sbn-name">Фланцы трубопроводные</span>
                <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $flancy_n ) ); ?> поз. · 4 типа</span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="<?php echo $flancy_open ? 'true' : 'false'; ?>">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <div class="sbn-children">
            <?php if ( $flancy_plosk_term && $flancy_plosk_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'flancy-plosk' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'flancy-plosk' ) ); ?>">
              <span class="sbn-child-name">Плоские ФП</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $flancy_plosk_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $flancy_vorot_term && $flancy_vorot_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'flancy-vorot' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'flancy-vorot' ) ); ?>">
              <span class="sbn-child-name">Воротниковые ФВ</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $flancy_vorot_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $flancy_01_term && $flancy_01_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'flancy-01' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'flancy-01' ) ); ?>">
              <span class="sbn-child-name">Тип 01</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $flancy_01_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $flancy_11_term && $flancy_11_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'flancy-11' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'flancy-11' ) ); ?>">
              <span class="sbn-child-name">Тип 11</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $flancy_11_n ) ); ?></span>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $krepezh_term && $krepezh_n > 0 ) :
          $krepezh_groups = [ 'krepezh', 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty' ];
          $krepezh_open = in_array( $group, $krepezh_groups, true );
        ?>
        <div class="sbn-group<?php echo $krepezh_open ? ' open' : ''; ?>" id="sbnKrepezh">
          <div class="sbn-item sbn-item--parent<?php echo in_array( $group, [ 'krepezh' ], true ) ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( 'krepezh' ) ); ?>">
              <div class="sbn-code">КР</div>
              <div class="sbn-info">
                <span class="sbn-name">Крепёж</span>
                <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $krepezh_n ) ); ?> поз. · 5 семейств</span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="<?php echo $krepezh_open ? 'true' : 'false'; ?>">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <div class="sbn-children">
            <?php if ( $bolty_term && $bolty_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'bolty' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'bolty' ) ); ?>">
              <span class="sbn-child-name">Болты</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $bolty_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $shpilki_term && $shpilki_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'shpilki' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'shpilki' ) ); ?>">
              <span class="sbn-child-name">Шпильки</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $shpilki_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $gayki_term && $gayki_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'gayki' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'gayki' ) ); ?>">
              <span class="sbn-child-name">Гайки</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $gayki_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $shayby_term && $shayby_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'shayby' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'shayby' ) ); ?>">
              <span class="sbn-child-name">Шайбы</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $shayby_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $vinty_term && $vinty_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'vinty' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'vinty' ) ); ?>">
              <span class="sbn-child-name">Винты</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $vinty_n ) ); ?></span>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if ( $tochenye_term && $tochenye_n > 0 ) : ?>
        <a class="sbn-item sbn-filter<?php echo $group === 'tochenye' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'tochenye' ) ); ?>">
          <div class="sbn-code">ТД</div>
          <div class="sbn-info">
            <span class="sbn-name">Точеные детали</span>
            <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $tochenye_n ) ); ?> поз. · переходы ПТ</span>
          </div>
        </a>
        <?php endif; ?>
        <?php if ( $truby_term && $truby_n > 0 ) :
          $truby_groups = [ 'truby', 'truby-bsh', 'truby-es', 'truby-ppu', 'truby-vgp' ];
          $truby_open = in_array( $group, $truby_groups, true );
        ?>
        <div class="sbn-group<?php echo $truby_open ? ' open' : ''; ?>" id="sbnTruby">
          <div class="sbn-item sbn-item--parent<?php echo in_array( $group, [ 'truby' ], true ) ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( 'truby' ) ); ?>">
              <div class="sbn-code">ТР</div>
              <div class="sbn-info">
                <span class="sbn-name">Стальные трубы</span>
                <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $truby_n ) ); ?> поз. · 4 типа</span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="<?php echo $truby_open ? 'true' : 'false'; ?>">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <div class="sbn-children">
            <?php if ( $truby_bsh_term && $truby_bsh_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'truby-bsh' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'truby-bsh' ) ); ?>">
              <span class="sbn-child-name">Бесшовные</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $truby_bsh_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $truby_es_term && $truby_es_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'truby-es' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'truby-es' ) ); ?>">
              <span class="sbn-child-name">Электросварные</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $truby_es_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $truby_ppu_term && $truby_ppu_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'truby-ppu' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'truby-ppu' ) ); ?>">
              <span class="sbn-child-name">В ППУ</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $truby_ppu_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $truby_vgp_term && $truby_vgp_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'truby-vgp' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'truby-vgp' ) ); ?>">
              <span class="sbn-child-name">ВГП</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $truby_vgp_n ) ); ?></span>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if ( $izolyatsiya_term && $izolyatsiya_n > 0 ) : ?>
        <a class="sbn-item sbn-filter<?php echo $group === 'izolyatsiya' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'izolyatsiya' ) ); ?>">
          <div class="sbn-code">ИЗ</div>
          <div class="sbn-info">
            <span class="sbn-name">Изоляция и покрытия</span>
            <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $izolyatsiya_n ) ); ?> поз. · тройники ППУ</span>
          </div>
        </a>
        <?php endif; ?>
        <?php if ( $opory_term && $opory_n > 0 ) :
          $opory_groups = [ 'opory', 'opory-nepodv', 'opory-skolz', 'opory-pruzh' ];
          $opory_open = in_array( $group, $opory_groups, true );
        ?>
        <div class="sbn-group<?php echo $opory_open ? ' open' : ''; ?>" id="sbnOpory">
          <div class="sbn-item sbn-item--parent<?php echo in_array( $group, [ 'opory' ], true ) ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( 'opory' ) ); ?>">
              <div class="sbn-code">ОП</div>
              <div class="sbn-info">
                <span class="sbn-name">Опоры трубопроводов</span>
                <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $opory_n ) ); ?> поз. · 3 типа</span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="<?php echo $opory_open ? 'true' : 'false'; ?>">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <div class="sbn-children">
            <?php if ( $opory_nepodv_term && $opory_nepodv_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'opory-nepodv' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'opory-nepodv' ) ); ?>">
              <span class="sbn-child-name">Неподвижные</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $opory_nepodv_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $opory_skolz_term && $opory_skolz_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'opory-skolz' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'opory-skolz' ) ); ?>">
              <span class="sbn-child-name">Скользящие</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $opory_skolz_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $opory_pruzh_term && $opory_pruzh_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'opory-pruzh' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'opory-pruzh' ) ); ?>">
              <span class="sbn-child-name">Пружинные</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $opory_pruzh_n ) ); ?></span>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if ( $armatura_term && $armatura_n > 0 ) :
          $armatura_groups = [ 'armatura', 'armatura-zadvizhki', 'armatura-klapany', 'armatura-krany' ];
          $armatura_open = in_array( $group, $armatura_groups, true );
        ?>
        <div class="sbn-group<?php echo $armatura_open ? ' open' : ''; ?>" id="sbnArmatura">
          <div class="sbn-item sbn-item--parent<?php echo in_array( $group, [ 'armatura' ], true ) ? ' active' : ''; ?>">
            <a class="sbn-parent-link sbn-filter" href="<?php echo esc_url( promen_group_filter_url( 'armatura' ) ); ?>">
              <div class="sbn-code">ЗРА</div>
              <div class="sbn-info">
                <span class="sbn-name">Арматура</span>
                <span class="sbn-meta"><?php echo esc_html( number_format_i18n( $armatura_n ) ); ?> поз. · задвижки · клапаны · краны</span>
              </div>
            </a>
            <button class="sbn-toggle" type="button" aria-label="Свернуть/развернуть семейства" aria-expanded="<?php echo $armatura_open ? 'true' : 'false'; ?>">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </div>
          <div class="sbn-children">
            <?php if ( $armatura_zadv_term && $armatura_zadv_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'armatura-zadvizhki' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'armatura-zadvizhki' ) ); ?>">
              <span class="sbn-child-name">Задвижки</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $armatura_zadv_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $armatura_klap_term && $armatura_klap_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'armatura-klapany' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'armatura-klapany' ) ); ?>">
              <span class="sbn-child-name">Клапаны</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $armatura_klap_n ) ); ?></span>
            </a>
            <?php endif; ?>
            <?php if ( $armatura_kran_term && $armatura_kran_n > 0 ) : ?>
            <a class="sbn-child sbn-filter<?php echo $group === 'armatura-krany' ? ' active' : ''; ?>" href="<?php echo esc_url( promen_group_filter_url( 'armatura-krany' ) ); ?>">
              <span class="sbn-child-name">Краны</span>
              <span class="sbn-child-meta"><?php echo esc_html( number_format_i18n( $armatura_kran_n ) ); ?></span>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php
        $coming = [
          [ 'НМ', 'Нестандартные изделия', 'По КД заказчика' ],
        ];
        foreach ( $coming as [ $code, $name, $meta ] ) : ?>
          <div class="sbn-item" style="opacity:.45;cursor:default;">
            <div class="sbn-code"><?php echo esc_html( $code ); ?></div>
            <div class="sbn-info"><span class="sbn-name"><?php echo esc_html( $name ); ?></span><span class="sbn-meta"><?php echo esc_html( $meta ); ?> · в наполнении</span></div>
          </div>
        <?php endforeach; ?>
      </nav>
    </aside>

    <div class="cat-main">
      <div class="sticky-hd">
        <div class="main-hd">
          <div>
            <div class="mh-path">Каталог <span id="pathSub"><?php echo esc_html( $view['path'] ); ?></span></div>
            <div class="mh-title-row">
              <div class="mh-title" id="mainTitle"><?php echo esc_html( $view['title'] ); ?></div>
              <?php if ( $cat_term && ! is_wp_error( get_term_link( $cat_term ) ) ) : ?>
                <a id="pathCatLink" class="mh-cat-link" href="<?php echo esc_url( get_term_link( $cat_term ) ); ?>" title="<?php echo esc_attr( 'Открыть страницу категории «' . $cat_term->name . '»' ); ?>">Страница категории<span class="gr-go-arr" aria-hidden="true">→</span></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="cmd-bar">
          <div class="cb-search-row">
            <form class="cb-search" method="get" action="">
              <div class="cb-search-ic">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.2"/><line x1="9.5" y1="9.5" x2="13" y2="13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
              </div>
              <input id="searchInput" name="q" type="text" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ) ); ?>" placeholder="Поиск по наименованию, ГОСТ, типоразмеру…">
            </form>
            <?php
            // Модель фильтров: DN/PN — диапазоны, сталь/угол/ГОСТ — мультивыбор.
            $ranges  = $is_fastener_ui
              ? [ 'dn' => 'M, мм' ]
              : [ 'dn' => 'DN, мм', 'pn' => 'PN, МПа' ];
            $multis  = $is_fastener_ui
              ? [ 'steel' => 'Сталь', 'gost' => 'ГОСТ' ]
              : [ 'steel' => 'Сталь', 'angle' => 'Угол', 'gost' => 'ГОСТ' ];
            $act_ranges  = promen_active_ranges();
            $act_multi   = promen_active_multi();
            $summary     = promen_active_summary();
            $active_n    = count( $summary );
            ?>
            <button type="button" class="cb-toggle" id="cbToggle" aria-expanded="false">
              <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M1 3h12M3 7h8M5 11h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
              Фильтры<?php if ( $active_n ) : ?><span class="cb-toggle-n"><?php echo esc_html( $active_n ); ?></span><?php endif; ?>
            </button>
            <div class="chips-count" id="pCount"><?php echo esc_html( number_format_i18n( $total ) ); ?> позиций</div>
          </div>
          <div class="cb-filters is-collapsed" id="cbFilters" data-base="<?php echo esc_url( promen_filters_base_url() ); ?>"<?php echo $group !== '' ? ' data-group="' . esc_attr( $group ) . '"' : ''; ?>>
            <?php foreach ( $ranges as $param => $lbl ) :
              $opts = promen_range_options( $param );
              if ( ! $opts ) { continue; }
              $cur = $act_ranges[ $param ] ?? [ 'min' => null, 'max' => null ]; ?>
              <div class="cbf-range" data-param="<?php echo esc_attr( $param ); ?>">
                <span class="cbf-lbl"><?php echo esc_html( $lbl ); ?></span>
                <select class="cbf-sel" data-bound="min" aria-label="<?php echo esc_attr( $lbl ); ?> от">
                  <option value="">от</option>
                  <?php foreach ( $opts as $o ) : ?>
                    <option value="<?php echo esc_attr( $o['val'] ); ?>"<?php selected( $cur['min'], (float) $o['val'] ); ?>><?php echo esc_html( $o['name'] ); ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="cbf-dash">–</span>
                <select class="cbf-sel" data-bound="max" aria-label="<?php echo esc_attr( $lbl ); ?> до">
                  <option value="">до</option>
                  <?php foreach ( $opts as $o ) : ?>
                    <option value="<?php echo esc_attr( $o['val'] ); ?>"<?php selected( $cur['max'], (float) $o['val'] ); ?>><?php echo esc_html( $o['name'] ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endforeach; ?>

            <?php foreach ( $multis as $param => $lbl ) :
              $opts = promen_multi_options( $param, 40 );
              if ( ! $opts ) { continue; }
              $sel  = $act_multi[ $param ] ?? [];
              $vis  = 6; // остальные — под «ещё» ?>
              <div class="cbf-multi" data-param="<?php echo esc_attr( $param ); ?>">
                <span class="cbf-lbl"><?php echo esc_html( $lbl ); ?></span>
                <?php foreach ( $opts as $i => $o ) : $on = in_array( $o['slug'], $sel, true ); ?>
                  <a class="c-chip<?php echo $on ? ' on' : ''; ?><?php echo $i >= $vis ? ' c-chip--extra' : ''; ?>" href="<?php echo esc_url( promen_multi_toggle_url( $param, $o['slug'] ) ); ?>" data-count="<?php echo esc_attr( $o['count'] ); ?>"><?php echo esc_html( $o['name'] ); ?><span class="c-chip-n"><?php echo esc_html( $o['count'] ); ?></span></a>
                <?php endforeach; ?>
                <?php if ( count( $opts ) > $vis ) : ?>
                  <button type="button" class="c-chip c-chip--more">+ ещё <?php echo count( $opts ) - $vis; ?></button>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cb-summary" id="cbSummary"<?php echo $summary ? '' : ' hidden'; ?>>
            <span class="cbs-lbl">Активные:</span>
            <?php foreach ( $summary as $s ) : ?>
              <a class="cbs-tag" href="<?php echo esc_url( $s['clear_url'] ); ?>"><?php echo esc_html( $s['label'] . ' ' . $s['value'] ); ?><span class="cbs-x" aria-hidden="true">✕</span></a>
            <?php endforeach; ?>
            <a class="cbs-reset" href="<?php echo esc_url( promen_reset_url() ); ?>">Сбросить всё</a>
          </div>
        </div>
        <div class="tbl-hd" id="tblHd" style="grid-template-columns:<?php echo esc_attr( $grid_tpl ); ?>">
          <span>Норматив</span><span>Наименование</span>
          <?php foreach ( $cat_cols as $col ) : ?><span><?php echo esc_html( $col['label'] ); ?></span><?php endforeach; ?>
          <span>Материал</span><span></span>
        </div>
      </div>

      <div id="productList">
        <?php if ( have_posts() ) : ?>
          <?php $i = 0; while ( have_posts() ) : the_post();
            $pid   = get_the_ID();
            $dims  = promen_get_dims( $pid );
            $steel_active = promen_active_multi()['steel'] ?? [];
            $steel_disp   = promen_product_steel_display( $pid, $steel_active ?: null );
            $norm  = promen_product_norm_key( $pid );
            $sku   = get_post_meta( $pid, '_sku', true );
            $sup   = wc_get_product_terms( $pid, 'pa_supervised' );
            $sup_names = wp_list_pluck( $sup ?: [], 'name' );
          ?>
          <?php
            $row_series = promen_series_meta( wc_get_product( $pid ) );
            $row_title  = promen_product_display_title( $pid );
            $row_fast   = promen_fastener_dims( $dims );
            $row_is_fast = $is_fastener_ui || promen_product_is_fastener( $pid );
            $dn1 = trim( (string) ( $dims['dn'] ?? '' ) );
            $dn2 = trim( (string) ( $dims['dn_branch'] ?? ( $dims['dy1'] ?? '' ) ) );
            $d1  = trim( (string) ( $dims['outer_diameter'] ?? '' ) );
            $s1  = trim( (string) ( $dims['wall_thickness'] ?? '' ) );
            $d2  = trim( (string) ( $dims['outer_d_branch'] ?? '' ) );
            $s2  = trim( (string) ( $dims['wall_branch'] ?? '' ) );
          ?>
          <a class="prod-row" href="<?php the_permalink(); ?>" style="grid-template-columns:<?php echo esc_attr( $grid_tpl ); ?>;animation-delay:<?php echo esc_attr( (string) min( $i * 0.025, 0.35 ) ); ?>s"
             data-sku="<?php echo esc_attr( $sku ); ?>"
             data-title="<?php echo esc_attr( $row_title ); ?>"
             data-family="<?php echo esc_attr( get_post_meta( $pid, '_promen_family', true ) ); ?>"
             data-dn="<?php echo esc_attr( $dn1 ); ?>"
             data-pn="<?php echo esc_attr( $dims['pn'] ?? '' ); ?>"
             data-d="<?php echo esc_attr( $d1 ); ?>"
             data-wall="<?php echo esc_attr( $s1 ); ?>"
             data-d2="<?php echo esc_attr( $d2 ); ?>"
             data-s2="<?php echo esc_attr( $s2 ); ?>"
             data-angle="<?php echo esc_attr( $dims['angle'] ?? '' ); ?>"
             data-ftype="<?php echo esc_attr( $dims['flange_type'] ?? ( $dims['product_type'] ?? '' ) ); ?>"
             data-b="<?php echo esc_attr( $dims['flange_thickness'] ?? '' ); ?>"
             data-d1="<?php echo esc_attr( $dims['bolt_circle_d'] ?? '' ); ?>"
             data-n="<?php echo esc_attr( $dims['stud_count'] ?? '' ); ?>"
             data-boltd="<?php echo esc_attr( $dims['bolt_d'] ?? '' ); ?>"
             data-seal="<?php echo esc_attr( $dims['seal_face'] ?? '' ); ?>"
             data-exec="<?php echo esc_attr( $dims['execution'] ?? '' ); ?>"
             data-flange="<?php echo $is_flange_ui ? '1' : ''; ?>"
             data-thread="<?php echo esc_attr( $row_fast['M'] ); ?>"
             data-length="<?php echo esc_attr( $row_fast['length'] ); ?>"
             data-strength="<?php echo esc_attr( $row_fast['strength'] ); ?>"
             data-accuracy="<?php echo esc_attr( $row_fast['accuracy'] ); ?>"
             data-washer="<?php echo esc_attr( $row_fast['washer'] ); ?>"
             data-fastener="<?php echo $row_is_fast ? '1' : ''; ?>"
             data-mass="<?php echo esc_attr( get_post_meta( $pid, '_weight', true ) ); ?>"
             data-steel="<?php echo esc_attr( $steel_disp['text'] !== '' ? $steel_disp['text'] : ( $row_fast['steel'] !== '' ? promen_fmt_steel( $row_fast['steel'] ) : '' ) ); ?>"
             data-sup="<?php echo esc_attr( implode( '/', $sup_names ) ); ?>"
             data-norm="<?php echo esc_attr( $norm ); ?>">
            <span class="pr-norm"><span class="pr-norm-code"><?php echo esc_html( $norm ?: '—' ); ?></span></span>
            <span class="pr-name"><?php
              echo esc_html( $row_title );
              $name_meta = trim( ( $row_series['code'] ?: '' ) . ( get_post_meta( $pid, '_promen_family', true ) ? ' · ' . get_post_meta( $pid, '_promen_family', true ) : '' ), ' ·' );
              if ( $name_meta !== '' ) {
                echo '<small>' . esc_html( $name_meta ) . '</small>';
              }
              if ( $is_branch_ui && ! $row_is_fast ) {
                $compact = promen_dxs_label( $dims );
                if ( $compact !== '' ) {
                  echo '<span class="pr-size-compact">' . esc_html( $compact ) . '</span>';
                }
              }
            ?></span>
            <?php foreach ( $cat_cols as $col ) : ?>
              <span class="pr-<?php echo esc_attr( $col['key'] ); ?>"><?php echo esc_html( promen_catalog_cell( $col['key'], $dims, $row_fast, $pid ) ); ?></span>
            <?php endforeach; ?>
            <span class="pr-mat"<?php echo $steel_disp['title'] !== '' ? ' title="' . esc_attr( $steel_disp['title'] ) . '"' : ''; ?>><?php
              if ( $steel_disp['text'] !== '' ) {
                echo esc_html( $steel_disp['text'] );
              } elseif ( $row_is_fast && $row_fast['steel'] !== '' ) {
                echo esc_html( promen_fmt_steel( $row_fast['steel'] ) );
              } elseif ( $row_is_fast && $row_fast['strength'] !== '' ) {
                echo esc_html( $row_fast['strength'] );
              } else {
                echo '—';
              }
            ?></span>
            <span class="pr-arr">›</span>
          </a>
          <?php $i++; endwhile; ?>
        <?php else : ?>
          <div class="cat-empty">
            <div class="ce-code">—</div>
            <div class="ce-msg">Нет позиций по заданным параметрам</div>
            <a class="ce-reset" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Сбросить фильтры</a>
          </div>
        <?php endif; ?>
      </div>

      <div class="cat-pagination">
        <?php echo paginate_links( [ 'type' => 'plain', 'prev_text' => '← Назад', 'next_text' => 'Вперёд →' ] ); ?>
      </div>
    </div>
  </div>
</div>

<?php
// Технический реестр + база знаний — только на чистом корне каталога
// (как в katalog.html); при фильтрах/поиске/пагинации не дублируем.
$promen_clean_root = ! promen_has_filters() && ! is_paged() && promen_active_group() === '';
if ( $promen_clean_root ) {
	include __DIR__ . '/parts/catalog-seo.php';
	include __DIR__ . '/parts/catalog-kb.php';
}
?>

<!-- PDP: боковой технический паспорт (разметка из katalog.html) -->
<div class="pdp-overlay" id="pdpOverlay"></div>
<div class="pdp" id="pdp">
  <div class="pdp-hd">
    <div class="pdp-hd-lbl">Технический паспорт изделия</div>
    <button class="pdp-close" id="pdpClose">✕</button>
  </div>
  <div class="pdp-scroll">
    <div class="pdp-id">
      <div class="pdp-code" id="pdpCode"></div>
      <div class="pdp-title" id="pdpTitle"></div>
      <div class="pdp-sub" id="pdpSub"></div>
    </div>
    <div class="pdp-sec">
      <div class="pdp-sec-lbl">Технические параметры</div>
      <div id="pdpParams"></div>
    </div>
    <div class="pdp-sec">
      <div class="pdp-sec-lbl">Нормативная документация</div>
      <div class="pdp-tags" id="pdpNorms"></div>
    </div>
    <div class="pdp-sec">
      <div class="pdp-sec-lbl">Области применения</div>
      <div class="pdp-sectors" id="pdpSectors"></div>
    </div>
    <div class="pdp-sec">
      <div class="pdp-sec-lbl">Контроль и документация</div>
      <p class="pdp-pass">Поставка с паспортом изделия, сертификатом на металл 3.1 и протоколами контроля. Для поднадзорных объектов — расширенный объём НК по ТР ТС 032/2013.</p>
      <div class="pdp-marks" id="pdpMarks"></div>
    </div>
  </div>
  <div class="pdp-cta">
    <a class="btn-pdp fill" id="pdpOpen" href="#">Открыть карточку →</a>
    <a class="btn-pdp out" id="pdpRequest" href="#request">Запросить КП</a>
  </div>
</div>
<?php
get_footer();
