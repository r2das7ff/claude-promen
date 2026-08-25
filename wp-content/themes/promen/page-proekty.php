<?php
/**
 * Список проектов «Реализованные поставки» — 1:1 из html/proekty.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekty.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ / REV.1' );

$promen_catalog_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_proekty_url  = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_sdt_term     = get_term_by( 'slug', 'sdt', 'product_cat' );
$promen_sdt_url      = ( $promen_sdt_term && ! is_wp_error( $l = get_term_link( $promen_sdt_term ) ) ) ? $l : $promen_catalog_url;
$promen_nb_url       = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : '';

get_header();
?>
<div class="pg">

  <!-- HERO -->
  <div class="prj-hero">
    <div>
      <div class="prj-eyebrow">Реестр реализованных поставок</div>
      <h1 class="prj-h1">Проекты<br><em>завода</em></h1>
      <p class="prj-desc">Поставки соединительных деталей трубопровода, труб и запорной арматуры для объектов
        атомной и тепловой энергетики — в России и на международных стройках. Каждая позиция изготовлена
        по чертежам заказчика с полным пакетом сопроводительной документации.</p>
    </div>
    <div class="prj-stats" data-reveal-group>
      <?php
      $promen_prj_all      = promen_projects_registry();
      $promen_prj_countries = array_values( array_unique( array_filter( array_column( $promen_prj_all, 'country' ) ) ) );
      $promen_prj_cnt = [
        'all'  => count( $promen_prj_all ),
        'aes'  => count( array_filter( $promen_prj_all, fn( $x ) => 'nuclear' === $x['kind'] ) ),
        'tes'  => count( array_filter( $promen_prj_all, fn( $x ) => 'thermal' === $x['kind'] ) ),
        'prom' => count( array_filter( $promen_prj_all, fn( $x ) => ! in_array( $x['kind'], [ 'nuclear', 'thermal' ], true ) ) ),
        'ru'   => count( array_filter( $promen_prj_all, fn( $x ) => 'ru' === $x['region'] ) ),
        'intl' => count( array_filter( $promen_prj_all, fn( $x ) => 'intl' === $x['region'] ) ),
      ];
      ?>
      <div class="hs"><span class="hs-v"><?php echo (int) $promen_prj_cnt['all']; ?></span><span class="hs-k">Объектов поставки</span></div>
      <div class="hs"><span class="hs-v"><?php echo (int) $promen_prj_cnt['aes']; ?></span><span class="hs-k">Объектов атомной энергетики</span></div>
      <div class="hs"><span class="hs-v"><?php echo count( $promen_prj_countries ); ?></span><span class="hs-k">Страны — <?php echo esc_html( implode( ', ', $promen_prj_countries ) ); ?></span></div>
      <div class="hs"><span class="hs-v">45&nbsp;дней</span><span class="hs-k">Средний срок изготовления партии</span></div>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="prj-filters" id="prjFilters" data-reveal>
    <span class="pf-label">Фильтр</span>
    <span class="pf-chip active" data-filter="all">Все проекты<span class="pf-count"><?php echo esc_html( sprintf( '%02d', $promen_prj_cnt['all'] ) ); ?></span></span>
    <span class="pf-chip" data-filter="aes">АЭС<span class="pf-count"><?php echo esc_html( sprintf( '%02d', $promen_prj_cnt['aes'] ) ); ?></span></span>
    <span class="pf-chip" data-filter="tes">ТЭС · ГРЭС<span class="pf-count"><?php echo esc_html( sprintf( '%02d', $promen_prj_cnt['tes'] ) ); ?></span></span>
    <span class="pf-chip" data-filter="prom">Промышленность<span class="pf-count"><?php echo esc_html( sprintf( '%02d', $promen_prj_cnt['prom'] ) ); ?></span></span>
    <span class="pf-chip" data-filter="ru">Россия<span class="pf-count"><?php echo esc_html( sprintf( '%02d', $promen_prj_cnt['ru'] ) ); ?></span></span>
    <span class="pf-chip" data-filter="intl">Экспорт<span class="pf-count"><?php echo esc_html( sprintf( '%02d', $promen_prj_cnt['intl'] ) ); ?></span></span>
  </div>

  <!-- GRID -->
  <div class="prj-body">
    <div class="prj-grid" id="prjGrid" data-reveal-group>

<?php
      // Карточки объектов — из общего реестра (inc/projects-registry.php),
      // он же питает карту на главной и бегущую строку.
      // У объектов без детальной страницы карточка пока не кликабельна.
      $promen_prj_svg = [
        'nuclear'  => '<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="30" y="120" width="140" height="150" fill="#0F2A44"/><circle cx="100" cy="110" r="46" fill="none" stroke="#6D8CA6" stroke-width="2" opacity=".5"/></svg>',
        'thermal'  => '<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="20" y="150" width="30" height="120" fill="#0F2A44"/><rect x="60" y="100" width="30" height="170" fill="#0F2A44"/><rect x="100" y="170" width="30" height="100" fill="#0F2A44"/></svg>',
        'hydro'    => '<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="0" y="170" width="200" height="100" fill="#0F2A44"/><path d="M0 175 Q50 160 100 175 T200 175" fill="none" stroke="#6D8CA6" stroke-width="2" opacity=".5"/></svg>',
        'mining'   => '<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><path d="M20 270 L80 150 L140 270 Z" fill="#0F2A44"/><path d="M110 270 L155 190 L195 270 Z" fill="#0F2A44" opacity=".8"/></svg>',
        'chemical' => '<svg viewBox="0 0 200 300" preserveAspectRatio="xMidYMid slice"><rect width="200" height="300" fill="#1E3D5C"/><rect x="35" y="120" width="45" height="150" rx="22" fill="#0F2A44"/><rect x="105" y="90" width="45" height="180" rx="22" fill="#0F2A44"/></svg>',
      ];
      foreach ( promen_projects_registry() as $promen_prj ) :
        $promen_prj_url  = $promen_prj['page'] ? promen_project_url( $promen_prj['page'] ) : '';
        $promen_prj_type = 'nuclear' === $promen_prj['kind'] ? 'aes' : ( 'thermal' === $promen_prj['kind'] ? 'tes' : 'prom' );
        $promen_prj_tag  = $promen_prj['tag'];
        $promen_prj_loc  = $promen_prj['city'] . ( $promen_prj['country'] ? ' · ' . $promen_prj['country'] : '' );
        $promen_prj_soon = ! $promen_prj_url;
        $promen_prj_el   = $promen_prj_soon ? 'div' : 'a';
        $promen_prj_done = false === stripos( $promen_prj['status'], 'строительств' );
        ?>
      <<?php echo $promen_prj_el; ?> class="p-card<?php echo $promen_prj_soon ? ' p-card-soon' : ''; ?>"
        id="<?php echo esc_attr( $promen_prj['slug'] ); ?>"
        data-type="<?php echo esc_attr( $promen_prj_type ); ?>"
        data-region="<?php echo esc_attr( $promen_prj['region'] ); ?>"
        <?php echo $promen_prj_soon ? '' : 'href="' . esc_url( $promen_prj_url ) . '"'; ?>>
        <div class="p-media">
          <?php if ( ! empty( $promen_prj['photo'] ) ) : ?>
          <img src="<?php echo esc_url( get_theme_file_uri( 'assets/' . $promen_prj['photo'] ) ); ?>" alt="<?php echo esc_attr( $promen_prj['name'] ); ?>" loading="lazy" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <?php endif; ?>
          <?php echo $promen_prj_svg[ $promen_prj['kind'] ] ?? $promen_prj_svg['thermal']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <span class="p-tag"><?php echo esc_html( $promen_prj_tag ); ?></span>
          <span class="p-status"><span class="p-status-dot<?php echo $promen_prj_done ? ' done' : ''; ?>"></span><?php echo esc_html( $promen_prj_done ? 'Завершено' : 'В работе' ); ?></span>
        </div>
        <div class="p-body">
          <div>
            <div class="p-title"><?php echo esc_html( $promen_prj['name'] ); ?></div>
            <div class="p-loc"><?php echo esc_html( $promen_prj_loc ); ?></div>
          </div>
          <div class="p-facts">
            <?php foreach ( $promen_prj['facts'] as [ $promen_prj_fk, $promen_prj_fv ] ) : ?>
            <div class="p-fact"><span class="p-fact-k"><?php echo esc_html( $promen_prj_fk ); ?></span><span class="p-fact-v"><?php echo esc_html( $promen_prj_fv ); ?></span></div>
            <?php endforeach; ?>
          </div>
          <div class="p-foot">
            <span class="p-link"><?php echo $promen_prj_soon ? 'История поставки готовится' : 'История поставки →'; ?></span>
          </div>
        </div>
      </<?php echo $promen_prj_el; ?>>
      <?php endforeach; ?>
</div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
