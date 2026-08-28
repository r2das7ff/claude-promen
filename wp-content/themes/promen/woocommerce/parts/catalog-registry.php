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

$show_cat_link = $promen_registry_show_cat_link ?? ! is_tax( 'product_cat' );
$with_pdp      = $promen_registry_with_pdp ?? true;
// На странице категории якорь #registry уже на секции-обёртке
// (promen_render_category_catalog_embed) — второй такой id здесь дал бы
// дубль. На /catalog/ обёртки нет, и id остаётся за этим блоком.
$embedded      = ! empty( $promen_registry_embedded );
?>
    <div class="cat-main"<?php echo $embedded ? '' : ' id="registry"'; ?>>
      <div class="sticky-hd">
        <div class="main-hd">
          <div>
            <div class="mh-path">Каталог <span id="pathSub"><?php echo esc_html( $view['path'] ); ?></span></div>
            <div class="mh-title-row">
              <div class="mh-title" id="mainTitle"><?php echo esc_html( $view['title'] ); ?></div>
              <?php if ( $show_cat_link ) :
				$cat_link  = (string) ( $view['term_url'] ?? '' );
				$cat_title = $view['term_name'] !== '' ? ( 'Открыть страницу категории «' . $view['term_name'] . '»' ) : '';
				?>
              <a id="pathCatLink" class="mh-cat-link" href="<?php echo $cat_link ? esc_url( $cat_link ) : '#'; ?>" title="<?php echo esc_attr( $cat_title ); ?>"<?php echo $cat_link ? '' : ' hidden'; ?>>Страница категории<span class="gr-go-arr" aria-hidden="true">→</span></a>
              <?php else : ?>
              <a id="pathCatLink" class="mh-cat-link" href="#" hidden></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="cmd-bar">
          <?php
          // Единая логика опций с REST: сужение несовместимого при активных
          // фильтрах, полный универсум без них (promen_catalog_filter_state).
          $promen_fq    = promen_catalog_query_from_request();
          $filter_state = promen_catalog_filter_state( $promen_fq, $catalog );
          $facet_opts   = $filter_state['facet_options'];
          $range_opts   = $filter_state['range_options'];

          // Диапазоны — из схемы группы (у труб/СДТ есть стенка s, у крепежа только M).
          $range_lbl_map = [
            'dn' => $is_fastener_ui ? 'M, мм' : 'DN, мм',
            'pn' => 'PN, МПа',
            's'  => 'Стенка s, мм',
          ];
          $range_lbls = [];
          foreach ( promen_catalog_schema_ranges( (string) $group ) as $rp ) {
            $range_lbls[ $rp ] = $range_lbl_map[ $rp ] ?? $rp;
          }
          $multis = $is_fastener_ui
            ? [ 'gost' => 'ГОСТ', 'steel' => 'Сталь' ]
            : [ 'gost' => 'ГОСТ', 'steel' => 'Сталь', 'angle' => 'Угол' ];
          $act_ranges = promen_active_ranges();
          $act_multi  = promen_active_multi();
          $summary    = promen_active_summary();
          $active_n   = count( $summary );
          $sel_ind    = $act_multi['industry'][0] ?? '';
          ?>
          <div class="cb-search-row">
            <form class="cb-search" method="get" action="">
              <div class="cb-search-ic">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.2"/><line x1="9.5" y1="9.5" x2="13" y2="13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
              </div>
              <?php // data-ph-sm — короткий плейсхолдер для телефонов, подставляет catalog.js ?>
              <input id="searchInput" name="q" type="text" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ) ); ?>" placeholder="Поиск по наименованию, ГОСТ, типоразмеру…" data-ph-sm="Поиск по ГОСТ" autocomplete="off">
            </form>
            <div class="cb-tabs" id="cbTabs" aria-label="Фильтр по отрасли">
              <a class="cb-tab<?php echo $sel_ind === '' ? ' on' : ''; ?>" href="<?php echo esc_url( promen_clear_param_url( 'industry' ) ); ?>" data-industry="">Все отрасли</a>
              <?php foreach ( (array) ( $facet_opts['industry'] ?? [] ) as $o ) :
                $on   = $sel_ind === $o['slug'];
                // Single-select: клик по активному — сброс на «Все отрасли».
                $href = $on ? promen_clear_param_url( 'industry' ) : promen_build_filter_url( array_merge( promen_current_filter_args(), [ 'industry' => $o['slug'] ] ) );
                ?>
                <a class="cb-tab<?php echo $on ? ' on' : ''; ?>" href="<?php echo esc_url( $href ); ?>" data-industry="<?php echo esc_attr( $o['slug'] ); ?>"><?php echo esc_html( $o['name'] ); ?><span class="cb-tab-n"><?php echo esc_html( number_format_i18n( (int) $o['count'] ) ); ?></span></a>
              <?php endforeach; ?>
            </div>
            <button type="button" class="cb-toggle" id="cbToggle" aria-expanded="false">
              <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M1 3h12M3 7h8M5 11h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
              Фильтры<?php if ( $active_n ) : ?><span class="cb-toggle-n"><?php echo esc_html( $active_n ); ?></span><?php endif; ?>
            </button>
            <a class="cb-reset" id="cbReset" href="<?php echo esc_url( promen_reset_url() ); ?>"<?php echo $active_n ? '' : ' hidden'; ?>><span class="cb-reset-x" aria-hidden="true">✕</span>Сбросить<span class="cb-reset-n"><?php echo esc_html( $active_n ); ?></span></a>
            <div class="chips-count" id="pCount" aria-live="polite"><?php echo esc_html( number_format_i18n( $total ) ); ?> позиций</div>
          </div>
          <div class="cb-filters is-collapsed" id="cbFilters" data-base="<?php echo esc_url( promen_filters_base_url() ); ?>"<?php echo $group !== '' ? ' data-group="' . esc_attr( $group ) . '"' : ''; ?>>
            <div class="cbf-sliders">
            <?php foreach ( $range_lbls as $param => $lbl ) :
              $opts = (array) ( $range_opts[ $param ] ?? [] );
              if ( ! $opts ) { continue; }
              $cur   = $act_ranges[ $param ] ?? [ 'min' => null, 'max' => null ];
              $last  = count( $opts ) - 1;
              $i_min = 0;
              $i_max = $last;
              foreach ( $opts as $i => $o ) {
                if ( null !== $cur['min'] && (float) $o['val'] <= (float) $cur['min'] ) { $i_min = $i; }
                if ( null !== $cur['max'] && (float) $o['val'] <= (float) $cur['max'] ) { $i_max = $i; }
              }
              if ( $i_max < $i_min ) { $i_max = $i_min; }
              $vals = wp_json_encode( array_map( static fn( $o ) => [ 'val' => (float) $o['val'], 'name' => (string) $o['name'] ], $opts ) );
              ?>
              <div class="cbf-slider" data-param="<?php echo esc_attr( $param ); ?>" data-values="<?php echo esc_attr( $vals ); ?>">
                <span class="cbf-lbl"><?php echo esc_html( $lbl ); ?></span>
                <div class="cbf-track">
                  <div class="cbf-fill"></div>
                  <input type="range" class="cbf-r" data-bound="min" min="0" max="<?php echo esc_attr( $last ); ?>" step="1" value="<?php echo esc_attr( $i_min ); ?>" aria-label="<?php echo esc_attr( $lbl ); ?> от">
                  <input type="range" class="cbf-r" data-bound="max" min="0" max="<?php echo esc_attr( $last ); ?>" step="1" value="<?php echo esc_attr( $i_max ); ?>" aria-label="<?php echo esc_attr( $lbl ); ?> до">
                </div>
                <span class="cbf-io">
                  <input type="text" class="cbf-in" data-bound="min" inputmode="decimal" value="<?php echo esc_attr( $opts[ $i_min ]['name'] ); ?>" aria-label="<?php echo esc_attr( $lbl ); ?> от, ручной ввод">
                  <span class="cbf-dash">–</span>
                  <input type="text" class="cbf-in" data-bound="max" inputmode="decimal" value="<?php echo esc_attr( $opts[ $i_max ]['name'] ); ?>" aria-label="<?php echo esc_attr( $lbl ); ?> до, ручной ввод">
                </span>
              </div>
            <?php endforeach; ?>
            </div>

            <?php foreach ( $multis as $param => $lbl ) :
              $opts = (array) ( $facet_opts[ $param ] ?? [] );
              if ( ! $opts ) { continue; }
              $sel = $act_multi[ $param ] ?? [];
              $vis = 8; ?>
              <div class="cbf-multi" data-param="<?php echo esc_attr( $param ); ?>">
                <span class="cbf-lbl"><?php echo esc_html( $lbl ); ?></span>
                <div class="cbf-chips">
                <?php foreach ( $opts as $i => $o ) : $on = in_array( $o['slug'], $sel, true ); ?>
                  <a class="c-chip<?php echo $on ? ' on' : ''; ?><?php echo (int) $o['count'] === 0 ? ' c-chip--zero' : ''; ?><?php echo $i >= $vis ? ' c-chip--extra' : ''; ?>" href="<?php echo esc_url( promen_multi_toggle_url( $param, $o['slug'] ) ); ?>" data-count="<?php echo esc_attr( $o['count'] ); ?>"><?php echo esc_html( $o['name'] ); ?><span class="c-chip-n"><?php echo esc_html( $o['count'] ); ?></span></a>
                <?php endforeach; ?>
                <?php if ( count( $opts ) > $vis ) : ?>
                  <button type="button" class="c-chip c-chip--more">+ ещё <?php echo count( $opts ) - $vis; ?></button>
                <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cb-summary" id="cbSummary"<?php echo $summary ? '' : ' hidden'; ?>>
            <span class="cbs-lbl">Активные:</span>
            <?php foreach ( $summary as $s ) : ?>
              <?php // trim — у 'gost' подпись пустая (см. promen_active_summary). ?>
              <a class="cbs-tag" href="<?php echo esc_url( $s['clear_url'] ); ?>"><?php echo esc_html( trim( $s['label'] . ' ' . $s['value'] ) ); ?><span class="cbs-x" aria-hidden="true">✕</span></a>
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
            <?php promen_render_catalog_row( $hit, $grid_tpl, (int) $i, $cat_cols ); ?>
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
      <h3 class="pdp-sec-lbl">Технические параметры</h3>
      <div id="pdpParams"></div>
    </div>
    <div class="pdp-sec">
      <h3 class="pdp-sec-lbl">Нормативная документация</h3>
      <div class="pdp-tags" id="pdpNorms"></div>
    </div>
    <div class="pdp-sec">
      <h3 class="pdp-sec-lbl">Области применения</h3>
      <div class="pdp-sectors" id="pdpSectors"></div>
    </div>
    <div class="pdp-sec">
      <h3 class="pdp-sec-lbl">Контроль и документация</h3>
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
