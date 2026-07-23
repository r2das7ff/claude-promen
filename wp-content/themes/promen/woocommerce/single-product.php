<?php
/**
 * Карточка товара — макет из design-reference/product-otvod-90.html.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );

	$dims     = promen_get_dims( $product->get_id() );
	$norm_key = promen_product_norm_key( $product->get_id() );
	if ( $norm_key === '' ) {
		$norm_key = get_post_meta( $product->get_id(), '_promen_norm_key', true );
	}
	$family   = get_post_meta( $product->get_id(), '_promen_family', true );
	$series   = promen_get_series( $product );
	$steels   = promen_attr_options( $product, 'pa_steel' );
	$sups     = promen_attr_options( $product, 'pa_supervised' );
	$var_map  = promen_get_variation_map( $product );
	$related  = promen_related_by_dn( $product );
	$crumbs   = promen_breadcrumbs();

	$dn    = $dims['dn'] ?? '';
	$dn2   = $dims['dn_branch'] ?? ( $dims['dy1'] ?? '' );
	$pn    = $dims['pn'] ?? '';
	$d_out = $dims['outer_diameter'] ?? '';
	$wall  = $dims['wall_thickness'] ?? '';
	$d2    = $dims['outer_d_branch'] ?? '';
	$s2    = $dims['wall_branch'] ?? '';
	$length = $dims['length'] ?? ( $dims['length_mm'] ?? '' );
	$mass  = $product->get_weight();
	$has_branch = $d2 !== '' || $s2 !== '';
	// Переходы / точёные: в карточке всегда показываем оба торца (равнопроходной — дубликат).
	if ( function_exists( 'promen_product_needs_both_pipe_ends' ) && promen_product_needs_both_pipe_ends( $product->get_id() ) ) {
		$has_branch = true;
	}
	$is_fastener = promen_product_is_fastener( $product->get_id() );
	$fast        = promen_fastener_dims( $dims );
	$thread_m    = $fast['M'];
	$thread_raw  = $fast['thread'];
	$strength    = $fast['strength'];
	$accuracy    = $fast['accuracy'];
	$washer_type = $fast['washer'];
	$mat_grade   = $fast['steel'];
	if ( $mat_grade === '' && ! empty( $dims['material_grade'] ) ) {
		$mat_grade = (string) $dims['material_grade'];
	}
	if ( $mat_grade === '' && ! empty( $dims['material'] ) ) {
		$mat_grade = (string) $dims['material'];
	}
	// Нет атрибута, но марка есть в dims — показать в паспорте.
	if ( ! $steels && $mat_grade !== '' ) {
		$term = get_term_by( 'name', $mat_grade, 'pa_steel' );
		if ( ! $term ) {
			$term = get_term_by( 'slug', sanitize_title( $mat_grade ), 'pa_steel' );
		}
		$slug = ( $term && ! is_wp_error( $term ) ) ? $term->slug : sanitize_title( $mat_grade );
		$steels = [ $slug => function_exists( 'promen_fmt_steel' ) ? promen_fmt_steel( $mat_grade ) : $mat_grade ];
	}
	if ( $is_fastener && $length === '' && $fast['length'] !== '' ) {
		$length = $fast['length'];
	}
	$radius = $dims['radius_mm'] ?? ( $dims['radius'] ?? '' );
	$flange_type = trim( (string) ( $dims['flange_type'] ?? ( $dims['product_type'] ?? '' ) ) );
	$seal_face   = trim( (string) ( $dims['seal_face'] ?? '' ) );
	$flange_b    = trim( (string) ( $dims['flange_thickness'] ?? '' ) );
	$bolt_circle = trim( (string) ( $dims['bolt_circle_d'] ?? '' ) );
	$stud_count  = trim( (string) ( $dims['stud_count'] ?? '' ) );
	$bolt_d      = trim( (string) ( $dims['bolt_d'] ?? '' ) );
	$d_inner     = trim( (string) ( $dims['d_inner'] ?? '' ) );
	$flange_series = trim( (string) ( $dims['series'] ?? '' ) );
	$flange_h4     = trim( (string) ( $dims['h4'] ?? '' ) );
	// Ряд «I/II/…» — ок; мусор вроде числа = D нар. скрываем.
	if ( $flange_series !== '' && is_numeric( $flange_series ) && $d_out !== '' && $flange_series === $d_out ) {
		$flange_series = '';
	}

	// Правила правдивости характеристик (см. inc/product-data.php).
	$cat_group = promen_category_group( $product->get_id() );
	$is_flange = $cat_group === 'flange';
	$pn_ok     = promen_pn_is_nominal( $cat_group ) && $pn !== '';
	$mass_ok   = $mass && promen_mass_is_reliable( $cat_group );
	$mass_col  = promen_mass_is_reliable( $cat_group ); // показывать колонку «Масса» в таблицах серии

	// Фото изделия: пока только отводы; на остальных категориях — заглушка.
	// Файл кладётся в themes/promen/assets/img/products/otvod.<ext> (рендер img только если файл есть).
	$deep_cat  = promen_deepest_cat( $product->get_id() );
	$photo_url = '';
	if ( $deep_cat && $deep_cat->slug === 'otvody' ) {
		foreach ( [ 'png', 'jpg', 'jpeg', 'webp' ] as $ext ) {
			$rel = 'assets/img/products/otvod.' . $ext;
			if ( file_exists( get_theme_file_path( $rel ) ) ) {
				$photo_url = get_theme_file_uri( $rel );
				break;
			}
		}
	}

	$norm_term = null;
	$norm_terms = get_the_terms( $product->get_id(), 'norm' );
	if ( $norm_terms && ! is_wp_error( $norm_terms ) ) {
		$norm_term = $norm_terms[0];
	}

	// Уникальные DN / M серии для кнопок конфигуратора.
	$dn_options = [];
	foreach ( $series as $s ) {
		$key = $is_fastener
			? ( ( $s['thread'] !== '' ? $s['thread'] : $s['dn'] ) )
			: $s['dn'];
		if ( $key === '' || isset( $dn_options[ $key ] ) ) {
			continue;
		}
		if ( ! $is_fastener && function_exists( 'promen_dn_looks_junk' ) && promen_dn_looks_junk( (string) $key ) ) {
			continue;
		}
		$dn_options[ $key ] = $s['url'];
	}
	uksort( $dn_options, fn( $a, $b ) => (float) $a <=> (float) $b );

	// Производные значения для секций из дизайна (чертёж, QC, база знаний).
	$angle_txt   = ! empty( $dims['angle'] ) ? $dims['angle'] . '°' : '—';
	$angle_sp    = ! empty( $dims['angle'] ) ? ' ' . $dims['angle'] . '°' : '';
	$first_steel = $steels ? reset( $steels ) : '—';
	$bp_r        = ! empty( $dims['radius_mm'] ) ? $dims['radius_mm'] . ' мм' : ( ! empty( $dims['radius'] ) ? $dims['radius'] . ' мм' : '—' );
	$bp_f        = ! empty( $dims['f_mm'] ) ? 'F = ' . $dims['f_mm'] . ' мм' : ( ! empty( $dims['c_mm'] ) ? 'C = ' . $dims['c_mm'] . ' мм' : '—' );

	// Серия: дизайн-карточка = страница серии; типоразмер — её производная.
	$series_meta = promen_series_meta( $product );
	$is_series   = promen_is_series_view();
	$size_label  = promen_size_label( $dims );
	$qc_title    = trim( $series_meta['name'] . $angle_sp . ( $is_fastener
		? ( $thread_m !== '' ? ' ' . $thread_m : '' ) . ( $length !== '' ? '×' . $length : '' )
		: ( $dn !== '' ? ' DN ' . $dn : '' ) ) );

	// H1 дизайна: серия крупно; типоразмер — подстрокой (в series-режиме крошки/H1 без размера).
	$h1_series = $series_meta['name'] . $angle_sp;

	// Крошки: последний уровень — серия (ссылкой) и компактный типоразмер.
	if ( $crumbs && null === end( $crumbs )[1] ) {
		array_pop( $crumbs );
	}
	$crumbs[] = [ $h1_series, $is_series ? null : $series_meta['url'] ];
	if ( ! $is_series && $size_label !== '' ) {
		$crumbs[] = [ $size_label, null ];
	}
?>
<script type="application/ld+json"><?php echo promen_breadcrumbs_schema( $crumbs ); ?></script>
<script type="application/ld+json"><?php echo promen_product_schema( $product ); ?></script>

<?php include __DIR__ . '/parts/sidenav.php'; ?>

<div class="sticky-pass" id="stickyPass" aria-hidden="true">
  <span class="sticky-pass-k">Конфигурация</span>
  <span class="sticky-pass-v" id="stickyLine"><?php the_title(); ?></span>
  <span class="sticky-pass-sep"></span>
  <span class="sticky-pass-k">Масса</span>
  <span class="sticky-pass-v" id="stickyMass"><?php echo esc_html( $mass_ok ? $mass . ' кг' : '—' ); ?></span>
  <a class="sticky-pass-cta" href="#request">Запросить расчёт →</a>
</div>

<div class="pg">

  <div class="prod-hero" id="hero">
    <div class="hero-left">
      <nav class="hero-crumb">
        <?php foreach ( $crumbs as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?>
            <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?>
            <span><?php echo esc_html( $label ); ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow"><?php echo esc_html( $series_meta['code'] . ( $norm_key ? ' / ' . $norm_key : '' ) ); ?></div>
      <?php if ( $is_series ) :
        // H1 как в дизайне: «Отвод / <em>крутоизогнутый</em> / 90°».
        $h1_words = explode( ' ', $series_meta['name'] );
        $h1_first = array_shift( $h1_words );
      ?>
        <h1 class="hero-h1"><?php echo esc_html( $h1_first ); ?><?php echo $h1_words ? '<br><em>' . esc_html( implode( ' ', $h1_words ) ) . '</em>' : ''; ?><?php echo $angle_sp !== '' ? '<br>' . esc_html( trim( $angle_sp ) ) : ''; ?></h1>
      <?php else : ?>
        <h1 class="hero-h1 hero-h1--split">
          <span class="hero-h1-series"><?php echo esc_html( $h1_series ); ?></span>
          <span class="hero-h1-size hero-size-badge">
            <span class="hsb-k">Типоразмер</span>
            <span class="hsb-v"><?php echo esc_html( $size_label ); ?></span>
            <?php if ( $norm_key ) : ?><span class="hsb-norm"><?php echo esc_html( $norm_key ); ?></span><?php endif; ?>
          </span>
        </h1>
      <?php endif; ?>
      <div class="hero-desc"><?php echo wp_kses_post( wpautop( promen_sanitize_desc( $product->get_id(), $product->get_description() ) ) ); ?></div>
      <div class="hero-media <?php echo $photo_url ? 'has-photo' : 'is-ph'; ?>">
        <?php if ( $photo_url ) : ?>
          <img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $qc_title ); ?>" loading="lazy" decoding="async">
        <?php else : ?>
          <div class="pm-ph">
            <svg viewBox="0 0 48 48" width="40" height="40" aria-hidden="true"><path d="M6 34l10-12 7 8 6-7 13 16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><circle cx="16" cy="15" r="4" fill="none" stroke="currentColor" stroke-width="2.5"/><rect x="4" y="8" width="40" height="32" rx="3" fill="none" stroke="currentColor" stroke-width="2.5"/></svg>
            <span class="pm-t">Фото изделия готовится</span>
            <span class="pm-s">Инженерный чертёж — в блоке 01</span>
          </div>
        <?php endif; ?>
      </div>
      <div class="hero-params">
        <?php if ( $is_fastener ) : ?>
          <?php if ( $thread_m !== '' || $washer_type !== '' ) : ?>
            <div class="hp"><span class="hp-v" id="heroDn"><?php echo esc_html( $washer_type !== '' ? promen_fmt_dim( $thread_raw ) : $thread_m ); ?></span><span class="hp-k"><?php echo $washer_type !== '' ? 'Диаметр' : 'Резьба'; ?></span></div>
          <?php endif; ?>
          <?php if ( $length !== '' ) : ?>
            <div class="hp"><span class="hp-v" id="heroPn"><?php echo esc_html( $length ); ?> мм</span><span class="hp-k">Длина L</span></div>
          <?php elseif ( $strength !== '' ) : ?>
            <div class="hp"><span class="hp-v" id="heroPn"><?php echo esc_html( $strength ); ?></span><span class="hp-k">Класс прочности</span></div>
          <?php elseif ( $washer_type !== '' ) : ?>
            <div class="hp"><span class="hp-v" id="heroPn">тип <?php echo esc_html( $washer_type ); ?></span><span class="hp-k">Исполнение</span></div>
          <?php endif; ?>
        <?php else : ?>
          <?php if ( $dn !== '' ) : ?>
            <div class="hp"><span class="hp-v" id="heroDn">DN <?php echo esc_html( $dn ); ?></span><span class="hp-k">Типоразмер</span></div>
          <?php endif; ?>
          <?php if ( $pn_ok ) : ?>
            <div class="hp"><span class="hp-v" id="heroPn">PN <?php echo esc_html( $pn ); ?></span><span class="hp-k">Давление</span></div>
          <?php elseif ( $radius !== '' ) : ?>
            <div class="hp"><span class="hp-v" id="heroPn">R <?php echo esc_html( $radius ); ?></span><span class="hp-k">Радиус гиба</span></div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ( $steels ) : ?>
          <div class="hp"><span class="hp-v" id="heroMat"><?php echo esc_html( reset( $steels ) ); ?></span><span class="hp-k">Марка стали</span></div>
        <?php elseif ( $is_fastener && $mat_grade !== '' ) : ?>
          <div class="hp"><span class="hp-v" id="heroMat"><?php echo esc_html( promen_fmt_steel( $mat_grade ) ); ?></span><span class="hp-k">Марка стали</span></div>
        <?php elseif ( $is_fastener && $strength !== '' && $length !== '' ) : ?>
          <div class="hp"><span class="hp-v" id="heroMat"><?php echo esc_html( $strength ); ?></span><span class="hp-k">Класс прочности</span></div>
        <?php endif; ?>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Заказать →</button>
        <button class="s10-ghost-link" type="button" id="deliveryOpen">Рассчитать стоимость доставки</button>
      </div>
    </div>
    <div class="hero-right">
      <div class="pass-card" id="passCard">
        <div class="pass-hd">
          <span class="pass-sys">SYS://PRODUCT.PASSPORT.v1</span>
          <span class="pass-id" id="passId"><?php echo esc_html( $series_meta['pass_id'] ); ?></span>
        </div>
        <div class="pass-body">
          <?php if ( $norm_key ) : ?>
            <div class="pp-row" data-field="standard"><span class="pp-k">Стандарт</span><span class="pp-v"><?php echo esc_html( $norm_key ); ?></span><span class="pp-chk">✓</span></div>
          <?php endif; ?>
          <?php if ( $is_flange && $flange_type !== '' ) : ?>
            <div class="pp-row" data-field="type"><span class="pp-k">Тип</span><span class="pp-v"><?php echo esc_html( $flange_type ); ?></span><span class="pp-chk">✓</span></div>
          <?php elseif ( $family && ! $is_flange ) : ?>
            <div class="pp-row" data-field="type"><span class="pp-k">Тип</span><span class="pp-v"><?php echo esc_html( $family . ( ! empty( $dims['angle'] ) ? ' · ' . $dims['angle'] . '°' : '' ) ); ?></span><span class="pp-chk">✓</span></div>
          <?php endif; ?>
          <?php if ( $is_fastener ) : ?>
            <?php if ( $washer_type !== '' ) : ?>
              <div class="pp-row" data-field="dn"><span class="pp-k">Диаметр</span><span class="pp-v" id="ppDnPn"><?php echo esc_html( promen_fmt_dim( $thread_raw ) ); ?> мм</span><span class="pp-chk">✓</span></div>
              <div class="pp-row" data-field="washer"><span class="pp-k">Тип шайбы</span><span class="pp-v"><?php echo esc_html( $washer_type ); ?></span><span class="pp-chk">✓</span></div>
            <?php else : ?>
              <?php if ( $thread_m !== '' ) : ?>
                <div class="pp-row" data-field="dn"><span class="pp-k">Резьба</span><span class="pp-v" id="ppDnPn"><?php echo esc_html( $thread_m ); ?></span><span class="pp-chk">✓</span></div>
              <?php endif; ?>
              <?php if ( $length !== '' ) : ?>
                <div class="pp-row" data-field="length"><span class="pp-k">Длина L</span><span class="pp-v"><?php echo esc_html( $length ); ?> мм</span><span class="pp-chk">✓</span></div>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ( $strength !== '' ) : ?>
              <div class="pp-row" data-field="strength"><span class="pp-k">Класс прочности</span><span class="pp-v"><?php echo esc_html( $strength ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $accuracy !== '' ) : ?>
              <div class="pp-row" data-field="accuracy"><span class="pp-k">Класс точности</span><span class="pp-v"><?php echo esc_html( $accuracy ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $mat_grade !== '' && ! $steels ) : ?>
              <div class="pp-row" data-field="material"><span class="pp-k">Материал</span><span class="pp-v" id="ppMat"><?php echo esc_html( promen_fmt_steel( $mat_grade ) ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
          <?php elseif ( $is_flange ) : ?>
            <?php if ( $dn !== '' ) : ?>
              <div class="pp-row" data-field="dn"><span class="pp-k">DN</span><span class="pp-v">DN <?php echo esc_html( $dn ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $pn_ok ) : ?>
              <div class="pp-row" data-field="pn"><span class="pp-k">PN</span><span class="pp-v">PN <?php echo esc_html( $pn ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $flange_type !== '' ) : ?>
              <div class="pp-row" data-field="ftype"><span class="pp-k">Тип</span><span class="pp-v"><?php echo esc_html( promen_flange_type_label( $flange_type ) ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $seal_face !== '' ) : ?>
              <div class="pp-row" data-field="seal"><span class="pp-k">Уплотн. поверхность</span><span class="pp-v"><?php echo esc_html( $seal_face ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $flange_series !== '' ) : ?>
              <div class="pp-row" data-field="series"><span class="pp-k">Ряд</span><span class="pp-v"><?php echo esc_html( $flange_series ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php
              // Единый набор геометрии фланца — по строке на размер, чёткие подписи.
              $flange_geo = [
                'D наружный'   => $d_out !== '' ? promen_fmt_dim( $d_out ) . ' мм' : '',
                'D болт. окр.' => $bolt_circle !== '' ? promen_fmt_dim( $bolt_circle ) . ' мм' : '',
                'd внутренний' => $d_inner !== '' ? promen_fmt_dim( $d_inner ) . ' мм' : '',
                'Толщина b'    => $flange_b !== '' ? promen_fmt_dim( $flange_b ) . ' мм' : '',
              ];
            ?>
            <?php foreach ( $flange_geo as $lbl => $val ) : if ( $val === '' ) continue; ?>
              <div class="pp-row" data-field="geo"><span class="pp-k"><?php echo esc_html( $lbl ); ?></span><span class="pp-v"><?php echo esc_html( $val ); ?></span><span class="pp-chk">✓</span></div>
            <?php endforeach; ?>
            <?php if ( $stud_count !== '' || $bolt_d !== '' ) : ?>
              <div class="pp-row" data-field="bolts"><span class="pp-k">Крепёж</span><span class="pp-v"><?php
                $bolt_lbl = trim(
                  ( $stud_count !== '' ? 'n ' . $stud_count : '' )
                  . ( $bolt_d !== '' ? ( $stud_count !== '' ? ' × ' : '' ) . 'M' . promen_fmt_dim( $bolt_d ) : '' )
                );
                echo esc_html( $bolt_lbl );
              ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $flange_h4 !== '' && ( $flange_type === 'ФВ' || $flange_type === '11' ) ) : ?>
              <div class="pp-row" data-field="h4"><span class="pp-k">h4</span><span class="pp-v"><?php echo esc_html( promen_fmt_dim( $flange_h4 ) ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
          <?php else : ?>
            <?php if ( $dn !== '' ) : ?>
              <div class="pp-row" data-field="dn"><span class="pp-k">DN<?php echo $dn2 !== '' ? ' / DN2' : ''; ?></span><span class="pp-v" id="ppDnPn">DN <?php echo esc_html( $dn ); ?><?php echo $dn2 !== '' ? ' / DN ' . esc_html( $dn2 ) : ''; ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $pn_ok ) : ?>
              <div class="pp-row" data-field="pn"><span class="pp-k">PN</span><span class="pp-v">PN <?php echo esc_html( $pn ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $d_out !== '' ) : ?>
              <div class="pp-row" data-field="d1"><span class="pp-k"><?php echo $has_branch ? 'D1' : 'D нар.'; ?></span><span class="pp-v"><?php echo esc_html( $d_out ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $wall !== '' ) : ?>
              <div class="pp-row" data-field="wall"><span class="pp-k"><?php echo $has_branch ? 'Стенка s1' : 'Стенка'; ?></span><span class="pp-v" id="ppWall"><?php echo esc_html( $wall ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( ! empty( $dims['execution'] ) ) : ?>
              <div class="pp-row" data-field="exec"><span class="pp-k">Исполнение</span><span class="pp-v"><?php echo esc_html( $dims['execution'] ); ?></span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $d2 !== '' ) : ?>
              <div class="pp-row" data-field="d2"><span class="pp-k">D2</span><span class="pp-v"><?php echo esc_html( $d2 ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $s2 !== '' ) : ?>
              <div class="pp-row" data-field="wall2"><span class="pp-k">Стенка s2</span><span class="pp-v"><?php echo esc_html( $s2 ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $radius !== '' ) : ?>
              <div class="pp-row" data-field="radius"><span class="pp-k">Радиус гиба R</span><span class="pp-v"><?php echo esc_html( $radius ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( ! empty( $dims['angle'] ) ) : ?>
              <div class="pp-row" data-field="angle"><span class="pp-k">Угол</span><span class="pp-v"><?php echo esc_html( $dims['angle'] ); ?>°</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
            <?php if ( $length !== '' ) : ?>
              <div class="pp-row" data-field="length"><span class="pp-k">Длина L</span><span class="pp-v"><?php echo esc_html( $length ); ?> мм</span><span class="pp-chk">✓</span></div>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ( $steels ) : ?>
            <div class="pp-row" data-field="material">
              <span class="pp-k">Материал</span>
              <span class="pp-v pp-mat-wrap" id="ppMat">
                <span class="pp-mat-list" id="ppMatList" role="list">
                  <?php
                  $first_steel = true;
                  foreach ( $steels as $st_slug => $st_name ) :
                    ?>
                    <button type="button" class="pp-steel<?php echo $first_steel ? ' is-active' : ' is-alt'; ?>" role="listitem" data-steel="<?php echo esc_attr( $st_slug ); ?>"<?php echo $first_steel ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $st_name ); ?></button><?php
                    $first_steel = false;
                  endforeach;
                  ?>
                </span>
                <span class="pp-mat-cert"> · серт. 3.1</span>
              </span>
              <span class="pp-chk">✓</span>
            </div>
          <?php endif; ?>
          <?php if ( $mass_ok ) : ?>
            <div class="pp-row" data-field="mass"><span class="pp-k">Масса<?php echo $cat_group === 'pipe' ? ', кг/м' : ', кг'; ?></span><span class="pp-v" id="ppMass"><?php echo esc_html( $mass ); ?></span><span class="pp-chk">≈</span></div>
          <?php endif; ?>
          <?php if ( $sups ) : ?>
            <div class="pp-row" data-field="supervised"><span class="pp-k">Поднадзорность</span><span class="pp-v" id="ppSup"><?php echo esc_html( reset( $sups ) ); ?></span><span class="pp-chk">✓</span></div>
          <?php endif; ?>
        </div>
        <div class="pass-ft">
          <span class="pass-status">Конфигурация активна</span>
          <span class="pass-meta">Обновляется конфигуратором</span>
        </div>
      </div>
    </div>
  </div>

  <section class="s prod-essence" id="s00">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">00</span>Суть изделия</div>
      <div class="s-meta">PRODUCT ESSENCE</div>
    </div>
    <div class="s-body">
      <div class="essence-grid reveal">
        <div class="ec">
          <div class="ec-code">01 / Геометрия</div>
          <div class="ec-h"><?php echo esc_html( $family ?: 'Отвод' ); ?><?php echo ! empty( $dims['angle'] ) ? ' ' . esc_html( $dims['angle'] ) . '°' : ''; ?></div>
          <p class="ec-p"><?php if ( $norm_key ) : ?>Изделие по <strong><?php echo esc_html( $norm_key ); ?></strong>. <?php endif; ?><?php if ( $is_fastener ) : ?>Типоразмер <?php echo esc_html( $size_label !== '' ? $size_label : ( $thread_m !== '' ? $thread_m : '—' ) ); ?><?php echo $length !== '' && $size_label === '' ? ' × ' . esc_html( $length ) . ' мм' : ''; ?>.<?php else : ?>Типоразмер DN <?php echo esc_html( $dn !== '' ? $dn : '—' ); ?><?php echo $dn2 !== '' ? '/' . esc_html( $dn2 ) : ''; ?><?php echo $size_label !== '' ? ', ' . esc_html( preg_replace( '/\s*исп\..*$/u', '', $size_label ) ) . ' мм' : ''; ?>.<?php endif; ?></p>
          <div class="ec-tags">
            <?php if ( ! empty( $dims['angle'] ) ) : ?><span class="ec-tag"><?php echo esc_html( $dims['angle'] ); ?>°</span><?php endif; ?>
            <?php if ( $norm_key ) : ?><span class="ec-tag"><?php echo esc_html( $norm_key ); ?></span><?php endif; ?>
          </div>
        </div>
        <div class="ec">
          <div class="ec-code">02 / Соединение</div>
          <div class="ec-h"><?php
            if ( $is_fastener ) {
              echo 'Резьбовое соединение';
            } elseif ( $is_flange ) {
              echo in_array( $flange_type, [ '11', 'ФВ' ], true ) ? 'Воротниковый фланец' : 'Плоский фланец';
            } else {
              echo 'Под приварку';
            }
          ?></div>
          <p class="ec-p"><?php
            if ( $is_fastener ) {
              echo 'Метрическая резьба, длина и класс прочности — по выбранному ГОСТ/ОСТ. Допуски — по таблице стандарта изготовления.';
            } elseif ( $is_flange ) {
              echo in_array( $flange_type, [ '11', 'ФВ' ], true )
                ? 'Присоединение к трубопроводу приваркой встык (воротник). Уплотнение и ряд — по выбранному исполнению стандарта.'
                : 'Присоединение к трубопроводу на приварке / свободной посадке (плоский). Уплотнение и ряд — по выбранному исполнению стандарта.';
            } else {
              echo 'Концы под сварку встык. Подготовка кромок по ГОСТ 16037. Допуски — по таблице стандарта изготовления.';
            }
          ?></p>
          <div class="ec-tags"><?php
            if ( $is_fastener ) :
              ?><span class="ec-tag">M × L</span><span class="ec-tag">ГОСТ / ОСТ</span><?php
            elseif ( $is_flange ) :
              ?><span class="ec-tag">DN / PN</span><?php if ( $flange_type !== '' ) : ?><span class="ec-tag">тип <?php echo esc_html( $flange_type ); ?></span><?php endif;
            else :
              ?><span class="ec-tag">Встык</span><span class="ec-tag">ГОСТ 16037</span><?php
            endif;
          ?></div>
        </div>
        <div class="ec">
          <div class="ec-code">03 / Параметры</div>
          <div class="ec-h"><?php echo $is_fastener
            ? esc_html( $size_label !== '' ? $size_label : ( $thread_m ?: 'Типоразмер' ) )
            : ( $dn !== '' ? 'DN ' . esc_html( $dn ) : 'Типоразмер' ) . ( $pn_ok ? ' / PN ' . esc_html( $pn ) : '' ); ?></div>
          <p class="ec-p"><?php echo $is_fastener
            ? 'Рабочие параметры зависят от класса прочности, покрытия и условий узла. Точный режим — в запросе КП.'
            : 'Рабочие параметры зависят от толщины стенки, марки стали и температуры среды. Точный режим — в запросе КП.'; ?></p>
          <div class="ec-tags">
            <?php if ( $is_fastener ) : ?>
              <?php if ( $thread_m !== '' ) : ?><span class="ec-tag"><?php echo esc_html( $thread_m ); ?></span><?php endif; ?>
              <?php if ( $length !== '' ) : ?><span class="ec-tag">L <?php echo esc_html( $length ); ?></span><?php endif; ?>
            <?php else : ?>
              <?php if ( $dn !== '' ) : ?><span class="ec-tag">DN <?php echo esc_html( $dn ); ?></span><?php endif; ?>
              <?php if ( $pn_ok ) : ?><span class="ec-tag">PN <?php echo esc_html( $pn ); ?></span><?php endif; ?>
              <?php if ( ! $pn_ok && $radius !== '' ) : ?><span class="ec-tag">R <?php echo esc_html( $radius ); ?></span><?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="ec">
          <div class="ec-code">04 / Прослеживаемость</div>
          <div class="ec-h">Сертификат 3.1</div>
          <p class="ec-p">Поставка с сертификатом на металл, номером плавки и паспортом изделия. Для поднадзорных объектов — расширенный объём НК.</p>
          <div class="ec-tags"><span class="ec-tag">3.1</span><span class="ec-tag">ОТК</span></div>
        </div>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/parts/blueprint.php'; ?>

  <?php if ( $dn_options || $steels ) : ?>
  <section class="s" id="s02">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">02</span>Конфигуратор</div>
      <div class="s-meta"><?php echo $is_fastener ? 'M · L · MATERIAL' : 'DN · MATERIAL · SUPERVISION'; ?></div>
    </div>
    <div class="s-body">
      <div class="params-wrap reveal">
        <div class="par-left">
          <div class="par-lbl">Параметры исполнения</div>
          <?php if ( count( $dn_options ) > 1 ) : ?>
          <div class="par-grp">
            <div class="par-grp-name"><?php echo $is_fastener ? 'Резьба M — фильтр типоразмеров серии' : 'DN, мм — фильтр типоразмеров серии'; ?></div>
            <div class="dn-grid" id="dnGrid">
              <button type="button" class="dn-b dn-b--all on" data-dn="">ВСЕ</button>
              <?php foreach ( $dn_options as $dn_val => $dn_url ) : ?>
                <button type="button" class="dn-b<?php echo (string) $dn_val === (string) ( $is_fastener ? $thread_raw : $dn ) ? ' cur' : ''; ?>" data-dn="<?php echo esc_attr( $dn_val ); ?>"><?php echo esc_html( $is_fastener ? promen_thread_label( (string) $dn_val ) : $dn_val ); ?></button>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php if ( $steels ) : ?>
          <div class="par-grp">
            <div class="par-grp-name">Марка стали</div>
            <select class="par-sel" id="matSel">
              <?php foreach ( $steels as $slug => $name ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <?php if ( count( $sups ) > 1 ) : ?>
          <div class="par-grp">
            <div class="par-grp-name">Поднадзорность</div>
            <div class="pn-flex" id="supGrid">
              <?php $first = true; foreach ( $sups as $slug => $name ) : ?>
                <button class="pn-b<?php echo $first ? ' on' : ''; ?>" data-sup="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></button>
              <?php $first = false; endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <div class="cfg-summary">
            <div class="cfg-sum-lbl">Выбранная конфигурация</div>
            <div class="cfg-sum-val" id="cfgLine"><?php the_title(); ?></div>
            <div class="cfg-sum-sub" id="cfgSub">
              <?php echo esc_html( implode( ' · ', array_filter( $is_fastener ? [
                $size_label !== '' ? $size_label : '',
                $mass ? "масса {$mass} кг" : '',
              ] : [
                $d_out !== '' ? ( $has_branch ? "D1 {$d_out} мм" : "D {$d_out} мм" ) : '',
                $wall !== '' ? ( $has_branch ? "s1 {$wall} мм" : "s {$wall} мм" ) : '',
                $d2 !== '' ? "D2 {$d2} мм" : '',
                $s2 !== '' ? "s2 {$s2} мм" : '',
                ! empty( $series[0]['R'] ) && ! empty( $dims['radius_mm'] ) ? 'R ' . $dims['radius_mm'] . ' мм' : '',
                $length !== '' ? "L {$length} мм" : '',
                $mass ? "масса {$mass} кг" : '',
              ] ) ) ); ?>
            </div>
            <div class="cfg-sum-sku" id="cfgSku">Артикул: <?php echo esc_html( $product->get_sku() ); ?></div>
          </div>
          <button type="button" class="s10-submit" style="margin-top:22px;" id="cfgRequest">Запросить КП →</button>
        </div>
        <div class="par-right">
          <?php if ( $series ) :
            $series_has_branch = ! empty( array_filter( array_column( $series, 'D2' ) ) );
            $series_has_strength = $is_fastener && ! empty( array_filter( array_column( $series, 'strength' ) ) );
            $series_has_pn = $is_flange && ! empty( array_filter( array_column( $series, 'pn' ) ) );
            $series_has_b  = $is_flange && ! empty( array_filter( array_column( $series, 'b' ) ) );
            $series_has_d1 = $is_flange && ! empty( array_filter( array_column( $series, 'D1' ) ) );
            $series_has_n  = $is_flange && ! empty( array_filter( array_column( $series, 'n' ) ) );
          ?>
          <table class="ptbl" id="specTable">
            <thead>
              <tr>
                <?php if ( $is_fastener ) : ?>
                  <th>M</th>
                  <th>L, мм</th>
                  <?php if ( $series_has_strength ) : ?><th>Класс</th><?php endif; ?>
                <?php elseif ( $is_flange ) : ?>
                  <th>DN</th>
                  <?php if ( $series_has_pn ) : ?><th>PN</th><?php endif; ?>
                  <th>D, мм</th>
                  <?php if ( $series_has_b ) : ?><th>b, мм</th><?php endif; ?>
                  <?php if ( $series_has_d1 ) : ?><th>D1, мм</th><?php endif; ?>
                  <?php if ( $series_has_n ) : ?><th>n</th><?php endif; ?>
                <?php else : ?>
                  <th>DN<?php echo $series_has_branch ? ' / DN2' : ''; ?></th>
                  <th><?php echo $series_has_branch ? 'D1×s1' : 'D, мм'; ?></th>
                  <?php if ( $series_has_branch ) : ?><th>D2×s2</th><?php else : ?><th>s, мм</th><?php endif; ?>
                <?php endif; ?>
                <?php if ( ! $is_flange && ! empty( $series[0]['exec'] ) ) : ?><th>Исп.</th><?php endif; ?>
                <?php if ( $mass_col ) : ?><th>Масса<?php echo $cat_group === 'pipe' ? ', кг/м' : ', кг'; ?></th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $series as $s ) :
                $s_thread = $s['thread'] !== '' ? $s['thread'] : $s['dn'];
                $s_len    = $s['L'] !== '' ? $s['L'] : $s['s'];
              ?>
              <tr<?php echo $s['id'] === $product->get_id() ? ' class="on"' : ''; ?> data-href="<?php echo esc_url( $s['url'] ); ?>" data-dn="<?php echo esc_attr( $is_fastener ? $s_thread : $s['dn'] ); ?>"
                  data-title="<?php echo esc_attr( $s['size'] ?: $s['title'] ); ?>" data-sku="<?php echo esc_attr( $s['sku'] ?? '' ); ?>"
                  data-d="<?php echo esc_attr( $s['D'] ); ?>" data-wall="<?php echo esc_attr( $is_fastener ? $s_len : ( $is_flange ? ( $s['b'] ?? '' ) : $s['s'] ) ); ?>"
                  data-d2="<?php echo esc_attr( $s['D2'] ?? '' ); ?>" data-s2="<?php echo esc_attr( $s['s2'] ?? '' ); ?>"
                  data-r="<?php echo esc_attr( $s['R'] ); ?>" data-mass="<?php echo esc_attr( $s['mass'] ); ?>">
                <?php if ( $is_fastener ) : ?>
                  <td><?php echo esc_html( ! empty( $s['washer'] ) ? promen_fmt_dim( (string) $s_thread ) : promen_thread_label( (string) $s_thread ) ); ?></td>
                  <td><?php echo esc_html( $s_len !== '' ? $s_len : ( ! empty( $s['washer'] ) ? 'тип ' . $s['washer'] : '—' ) ); ?></td>
                  <?php if ( $series_has_strength ) : ?><td><?php echo esc_html( $s['strength'] !== '' ? $s['strength'] : '—' ); ?></td><?php endif; ?>
                <?php elseif ( $is_flange ) : ?>
                  <td><?php echo esc_html( $s['dn'] !== '' ? $s['dn'] : '—' ); ?></td>
                  <?php if ( $series_has_pn ) : ?><td><?php echo esc_html( $s['pn'] !== '' ? $s['pn'] : '—' ); ?></td><?php endif; ?>
                  <td><?php echo esc_html( $s['D'] !== '' ? $s['D'] : '—' ); ?></td>
                  <?php if ( $series_has_b ) : ?><td><?php echo esc_html( $s['b'] !== '' ? $s['b'] : '—' ); ?></td><?php endif; ?>
                  <?php if ( $series_has_d1 ) : ?><td><?php echo esc_html( $s['D1'] !== '' ? $s['D1'] : '—' ); ?></td><?php endif; ?>
                  <?php if ( $series_has_n ) : ?><td><?php echo esc_html( $s['n'] !== '' ? $s['n'] : '—' ); ?></td><?php endif; ?>
                <?php else : ?>
                  <td><?php echo esc_html( $s['dn'] ); ?><?php echo ! empty( $s['dn2'] ) ? ' / ' . esc_html( $s['dn2'] ) : ''; ?></td>
                  <?php if ( $series_has_branch ) : ?>
                    <td><?php echo esc_html( trim( implode( '×', array_filter( [ $s['D'], $s['s'] ] ) ) ) ); ?></td>
                    <td><?php echo esc_html( trim( implode( '×', array_filter( [ $s['D2'] ?? '', $s['s2'] ?? '' ] ) ) ) ); ?></td>
                  <?php else : ?>
                    <td><?php echo esc_html( $s['D'] ); ?></td>
                    <td><?php echo esc_html( $s['s'] ); ?></td>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ( ! $is_flange && ! empty( $series[0]['exec'] ) ) : ?><td><?php echo esc_html( $s['exec'] ); ?></td><?php endif; ?>
                <?php if ( $mass_col ) : ?><td><?php echo esc_html( $s['mass'] ); ?></td><?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <p class="cfg-tbl-note" id="cfgTblNote">Клик по строке выбирает позицию для запроса КП</p>
          <button type="button" class="series-more" id="cfgMore" style="display:none;">Показать все ↓</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( count( $series ) > 1 ) : ?>
  <section class="s s-alt" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Реестр размеров серии</div>
      <div class="s-meta"><?php echo esc_html( $norm_key ); ?></div>
    </div>
    <div class="s-body">
      <div class="mat-tbl-wrap reveal" style="overflow-x:auto;">
        <?php
          $series_has_branch   = ! empty( array_filter( array_column( $series, 'D2' ) ) );
          $series_has_strength = $is_fastener && ! empty( array_filter( array_column( $series, 'strength' ) ) );
          $series_has_pn = $is_flange && ! empty( array_filter( array_column( $series, 'pn' ) ) );
          $series_has_b  = $is_flange && ! empty( array_filter( array_column( $series, 'b' ) ) );
          $series_has_d1 = $is_flange && ! empty( array_filter( array_column( $series, 'D1' ) ) );
          $series_has_n  = $is_flange && ! empty( array_filter( array_column( $series, 'n' ) ) );
        ?>
        <table class="ptbl series-full" style="width:100%;" data-collapse="20">
          <thead>
            <tr>
              <th>Типоразмер</th>
              <?php if ( $is_fastener ) : ?>
                <th>M</th>
                <th>L, мм</th>
                <?php if ( $series_has_strength ) : ?><th>Класс</th><?php endif; ?>
              <?php elseif ( $is_flange ) : ?>
                <th>DN</th>
                <?php if ( $series_has_pn ) : ?><th>PN</th><?php endif; ?>
                <th>D, мм</th>
                <?php if ( $series_has_b ) : ?><th>b, мм</th><?php endif; ?>
                <?php if ( $series_has_d1 ) : ?><th>D1, мм</th><?php endif; ?>
                <?php if ( $series_has_n ) : ?><th>n</th><?php endif; ?>
              <?php else : ?>
                <th>DN<?php echo $series_has_branch ? ' / DN2' : ''; ?></th>
                <th><?php echo $series_has_branch ? 'D1×s1' : 'D нар., мм'; ?></th>
                <?php if ( $series_has_branch ) : ?><th>D2×s2</th><?php else : ?><th>s, мм</th><?php endif; ?>
              <?php endif; ?>
              <?php if ( ! $is_flange && ! empty( $series[0]['exec'] ) ) : ?><th>Исп.</th><?php endif; ?>
              <?php if ( $mass_col ) : ?><th>Масса<?php echo $cat_group === 'pipe' ? ', кг/м' : ', кг'; ?></th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $series as $s ) :
              $s_thread = $s['thread'] !== '' ? $s['thread'] : $s['dn'];
              $s_len    = $s['L'] !== '' ? $s['L'] : $s['s'];
            ?>
            <tr<?php echo $s['id'] === $product->get_id() ? ' class="on"' : ''; ?>>
              <td><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['size'] ?: $s['title'] ); ?></a></td>
              <?php if ( $is_fastener ) : ?>
                <td><?php echo esc_html( ! empty( $s['washer'] ) ? promen_fmt_dim( (string) $s_thread ) : promen_thread_label( (string) $s_thread ) ); ?></td>
                <td><?php echo esc_html( $s_len !== '' ? $s_len : ( ! empty( $s['washer'] ) ? 'тип ' . $s['washer'] : '—' ) ); ?></td>
                <?php if ( $series_has_strength ) : ?><td><?php echo esc_html( $s['strength'] !== '' ? $s['strength'] : '—' ); ?></td><?php endif; ?>
              <?php elseif ( $is_flange ) : ?>
                <td><?php echo esc_html( $s['dn'] !== '' ? $s['dn'] : '—' ); ?></td>
                <?php if ( $series_has_pn ) : ?><td><?php echo esc_html( $s['pn'] !== '' ? $s['pn'] : '—' ); ?></td><?php endif; ?>
                <td><?php echo esc_html( $s['D'] !== '' ? $s['D'] : '—' ); ?></td>
                <?php if ( $series_has_b ) : ?><td><?php echo esc_html( $s['b'] !== '' ? $s['b'] : '—' ); ?></td><?php endif; ?>
                <?php if ( $series_has_d1 ) : ?><td><?php echo esc_html( $s['D1'] !== '' ? $s['D1'] : '—' ); ?></td><?php endif; ?>
                <?php if ( $series_has_n ) : ?><td><?php echo esc_html( $s['n'] !== '' ? $s['n'] : '—' ); ?></td><?php endif; ?>
              <?php else : ?>
                <td><?php echo esc_html( $s['dn'] ); ?><?php echo ! empty( $s['dn2'] ) ? ' / ' . esc_html( $s['dn2'] ) : ''; ?></td>
                <?php if ( $series_has_branch ) : ?>
                  <td><?php echo esc_html( trim( implode( '×', array_filter( [ $s['D'], $s['s'] ] ) ) ) ); ?></td>
                  <td><?php echo esc_html( trim( implode( '×', array_filter( [ $s['D2'] ?? '', $s['s2'] ?? '' ] ) ) ) ); ?></td>
                <?php else : ?>
                  <td><?php echo esc_html( $s['D'] ); ?></td>
                  <td><?php echo esc_html( $s['s'] ); ?></td>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ( ! $is_flange && ! empty( $series[0]['exec'] ) ) : ?><td><?php echo esc_html( $s['exec'] ); ?></td><?php endif; ?>
              <?php if ( $mass_col ) : ?><td><?php echo esc_html( $s['mass'] ); ?></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="reg-note">Значения массы — ориентировочные. Точная масса уточняется по КД и фактической толщине стенки.</p>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $norm_term ) : ?>
  <section class="s s-dark" id="s04">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">04</span>Нормативная база</div>
      <div class="s-meta">STANDARDS REGISTRY</div>
    </div>
    <div class="s-body">
      <div class="norm-grid reveal">
        <a class="nc" href="<?php echo esc_url( get_term_link( $norm_term ) ); ?>">
          <div class="nc-code"><?php echo esc_html( $norm_term->name ); ?></div>
          <div class="nc-title"><?php echo esc_html( $family ?: 'Норматив изделия' ); ?></div>
          <div class="nc-desc"><?php echo esc_html( $norm_term->description ? wp_trim_words( $norm_term->description, 24 ) : 'Основной стандарт изделия. Все типоразмеры и требования — на странице норматива.' ); ?></div>
          <div class="nc-tags"><span class="nc-tag">Базовый</span></div>
          <div class="nc-status"><span class="nc-dot"></span>Действует</div>
        </a>
        <div class="nc">
          <div class="nc-code">ТР ТС 032/2013</div>
          <div class="nc-title">О безопасности оборудования под давлением</div>
          <div class="nc-desc">Требования к сосудам и трубопроводам. Декларация соответствия для изделий завода.</div>
          <div class="nc-tags"><span class="nc-tag">Сертификация</span></div>
          <div class="nc-status"><span class="nc-dot"></span>Действует</div>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php include __DIR__ . '/parts/materials.php'; ?>

  <?php include __DIR__ . '/parts/applications.php'; ?>

  <?php include __DIR__ . '/parts/qc.php'; ?>

  <?php include __DIR__ . '/parts/docs.php'; ?>

  <?php if ( $related ) : ?>
  <section class="s s-alt" id="s09">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">09</span>Связанные позиции<?php echo $is_fastener ? ( $thread_m !== '' ? ' · ' . esc_html( $thread_m ) : '' ) : ( $dn !== '' ? ' · DN ' . esc_html( $dn ) : '' ); ?></div>
      <div class="s-meta">RELATED PRODUCTS</div>
    </div>
    <div class="s-body">
      <div class="rel-grid reveal">
        <?php foreach ( $related as $r ) : ?>
          <a class="rel-c" href="<?php echo esc_url( $r['url'] ); ?>">
            <span class="rel-code"><?php echo esc_html( $r['norm'] ); ?></span>
            <span class="rel-h"><?php echo esc_html( $r['title'] ); ?></span>
            <span class="rel-norm">Тот же DN — другое исполнение / норматив</span>
          </a>
        <?php endforeach; ?>
        <?php
        $cats = get_the_terms( $product->get_id(), 'product_cat' );
        if ( $cats && ! is_wp_error( $cats ) ) :
          $deepest = end( $cats ); ?>
          <a class="rel-c" href="<?php echo esc_url( get_term_link( $deepest ) ); ?>">
            <span class="rel-code">КАТЕГОРИЯ</span>
            <span class="rel-h"><?php echo esc_html( $deepest->name ); ?></span>
            <span class="rel-norm">Все позиции категории</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

<?php include __DIR__ . '/parts/kb-otvody.php'; ?>

</div><!-- /.pg -->

<?php
	// Данные конфигуратора для JS: вариации, выбранная опция из URL.
	wp_localize_script( 'promen-product', 'PROMEN_PRODUCT', [
		'sku'        => $product->get_sku(),
		'title'      => get_the_title(),
		'norm'       => $norm_key,
		'dn'         => $dn,
		'pn'         => $pn,
		'variations' => $var_map,
		'steels'     => $steels,
		'sups'       => $sups,
	] );
endwhile;
?>
<!-- Модал заявки (hero CTA карточки) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title" id="omTitle">Заказать позицию</div>
  <p class="om-sub" id="omSub">Проверьте параметры позиции и оставьте контакт — инженер подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <input type="hidden" name="sku" id="omSku" value="<?php echo esc_attr( $product->get_sku() ); ?>">
    <input type="hidden" name="delivery" id="omDelivery" value="">
    <div class="om-grid">
      <div class="om-field om-field--wide"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="<?php echo esc_attr( get_the_title() ); ?>" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" value="<?php echo esc_attr( $norm_key ); ?>" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn"><?php echo $is_fastener ? 'Резьба M / длина L' : 'DN / D×s, мм'; ?></label><input id="om-dn" name="dn" type="text" value="<?php echo esc_attr( $is_fastener ? ( $size_label !== '' ? $size_label : trim( $thread_m . ( $length !== '' ? '×' . $length : '' ) ) ) : trim( ( $dn !== '' ? 'DN ' . $dn : '' ) . ( $size_label !== '' ? ' / ' . $size_label : '' ) ) ); ?>" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка стали</label><input id="om-mat" name="material" type="text" value="<?php echo esc_attr( $first_steel !== '—' ? $first_steel : '' ); ?>" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-qty">Количество, шт</label><input id="om-qty" name="qty" type="text" placeholder="100" autocomplete="off"></div>
      <div class="om-field om-field--wide" id="omCityField" style="display:none;"><label class="om-lbl" for="om-city">Город / регион доставки</label><input id="om-city" name="city" type="text" placeholder="Например: Екатеринбург, до объекта" autocomplete="off"></div>
      <div class="om-field om-field--wide"><label class="om-lbl" for="om-contact">Ваш email / телефон *</label><input id="om-contact" name="contact" type="text" placeholder="Для ответа на запрос" required autocomplete="off"></div>
    </div>
    <div class="om-actions">
      <button type="submit" class="s10-submit">Отправить запрос →</button>
      <span class="om-note">Без обязательств · ответ за 1 рабочий день</span>
    </div>
  </form>
</div>

<?php
get_footer();
