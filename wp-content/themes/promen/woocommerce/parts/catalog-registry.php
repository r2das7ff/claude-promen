<?php
/**
 * Живой реестр каталога: шапка, фильтры, таблица, пагинация, PDP.
 * Используется на /catalog/ и на страницах категорий.
 *
 * Опциональные флаги до include:
 * - $promen_registry_show_cat_link (bool) — кнопка «Страница категории» (default: true на shop, false на tax)
 * - $promen_registry_with_pdp (bool) — рендер PDP в конце (default: true)
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $group ) ) {
	$group = promen_catalog_active_group();
}
if ( ! isset( $catalog ) ) {
	$catalog = promen_catalog_page_result();
}
if ( ! isset( $total ) ) {
	$total = (int) $catalog->total;
}

$is_fastener_ui = promen_is_fastener_group( (string) $group );

if ( ! isset( $cat_cols ) ) {
	$cat_cols = promen_catalog_columns( (string) $group );
}
if ( ! isset( $grid_tpl ) ) {
	$grid_tpl = promen_catalog_grid_template( (string) $group );
}
if ( ! isset( $sort_field ) || ! isset( $sort_dir ) ) {
	$sort_def   = function_exists( 'promen_catalog_schema_sort' ) ? promen_catalog_schema_sort( (string) $group ) : [ 'field' => 'dn', 'dir' => 'asc' ];
	$sort_raw   = isset( $_GET['sort'] ) ? explode( ':', sanitize_text_field( wp_unslash( $_GET['sort'] ) ) ) : [];
	$sort_field = ( $sort_raw[0] ?? '' ) !== '' ? sanitize_key( $sort_raw[0] ) : $sort_def['field'];
	$sort_dir   = ( ( $sort_raw[1] ?? '' ) === 'desc' ) ? 'desc' : ( ( ( $sort_raw[1] ?? '' ) === 'asc' ) ? 'asc' : $sort_def['dir'] );
}
if ( ! isset( $group_views ) ) {
	$group_views = promen_catalog_group_views();
}
if ( ! isset( $view ) ) {
	$view = $group_views[ $group ] ?? $group_views[''];
}
if ( ! isset( $cat_term ) ) {
	$cat_term = $view['term'];
}

$show_cat_link = $promen_registry_show_cat_link ?? ! is_tax( 'product_cat' );
$with_pdp      = $promen_registry_with_pdp ?? true;
?>
    <div class="cat-main" id="registry">
      <div class="sticky-hd">
        <div class="main-hd">
          <div>
            <div class="mh-path">Каталог <span id="pathSub"><?php echo esc_html( $view['path'] ); ?></span></div>
            <div class="mh-title-row">
              <div class="mh-title" id="mainTitle"><?php echo esc_html( $view['title'] ); ?></div>
              <?php if ( $show_cat_link ) :
				$cat_link  = ( $cat_term && ! is_wp_error( get_term_link( $cat_term ) ) ) ? get_term_link( $cat_term ) : '';
				$cat_title = $cat_term ? ( 'Открыть страницу категории «' . $cat_term->name . '»' ) : '';
				?>
              <a id="pathCatLink" class="mh-cat-link" href="<?php echo $cat_link ? esc_url( $cat_link ) : '#'; ?>" title="<?php echo esc_attr( $cat_title ); ?>"<?php echo $cat_link ? '' : ' hidden'; ?>>Страница категории<span class="gr-go-arr" aria-hidden="true">→</span></a>
              <?php else : ?>
              <a id="pathCatLink" class="mh-cat-link" href="#" hidden></a>
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
            $ranges  = $is_fastener_ui
              ? [ 'dn' => 'M, мм' ]
              : [ 'dn' => 'DN, мм', 'pn' => 'PN, МПа' ];
            $multis  = $is_fastener_ui
              ? [ 'industry' => 'Отрасль', 'steel' => 'Сталь', 'gost' => 'ГОСТ' ]
              : [ 'industry' => 'Отрасль', 'steel' => 'Сталь', 'angle' => 'Угол', 'gost' => 'ГОСТ' ];
            $act_ranges = promen_active_ranges();
            $act_multi  = promen_active_multi();
            $summary    = promen_active_summary();
            $active_n   = count( $summary );
            ?>
            <button type="button" class="cb-toggle" id="cbToggle" aria-expanded="false">
              <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M1 3h12M3 7h8M5 11h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
              Фильтры<?php if ( $active_n ) : ?><span class="cb-toggle-n"><?php echo esc_html( $active_n ); ?></span><?php endif; ?>
            </button>
            <div class="chips-count" id="pCount" aria-live="polite"><?php echo esc_html( number_format_i18n( $total ) ); ?> позиций</div>
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
              $sel = $act_multi[ $param ] ?? [];
              $vis = 6; ?>
              <div class="cbf-multi" data-param="<?php echo esc_attr( $param ); ?>">
                <span class="cbf-lbl"><?php echo esc_html( $lbl ); ?></span>
                <?php foreach ( $opts as $i => $o ) : $on = in_array( $o['slug'], $sel, true ); ?>
                  <a class="c-chip<?php echo $on ? ' on' : ''; ?><?php echo (int) $o['count'] === 0 ? ' c-chip--zero' : ''; ?><?php echo $i >= $vis ? ' c-chip--extra' : ''; ?>" href="<?php echo esc_url( promen_multi_toggle_url( $param, $o['slug'] ) ); ?>" data-count="<?php echo esc_attr( $o['count'] ); ?>"><?php echo esc_html( $o['name'] ); ?><span class="c-chip-n"><?php echo esc_html( $o['count'] ); ?></span></a>
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
          <?php echo promen_catalog_header_cells( $cat_cols, $sort_field, $sort_dir ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </div>
      </div>

      <div id="productList" aria-live="polite" aria-busy="false">
        <?php if ( $catalog->hits ) : ?>
          <?php foreach ( $catalog->hits as $i => $hit ) : ?>
            <?php promen_render_catalog_row( $hit, $grid_tpl, (int) $i ); ?>
          <?php endforeach; ?>
        <?php else : ?>
          <div class="cat-empty">
            <div class="ce-code">—</div>
            <div class="ce-msg">Нет позиций по заданным параметрам</div>
            <a class="ce-reset" href="<?php echo esc_url( is_tax( 'product_cat' ) ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' ) ); ?>">Сбросить фильтры</a>
          </div>
        <?php endif; ?>
      </div>

      <div class="cat-pagination">
        <?php echo promen_catalog_pagination_links( $catalog ); ?>
      </div>
    </div>

<?php if ( $with_pdp ) : ?>
<!-- PDP: боковой технический паспорт -->
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
<?php endif; ?>
