<?php
/**
 * Секция 01 — инженерный чертёж (параметрический SVG по типу изделия).
 * Тип определяется по семейству/углу; SVG-схема и выноски используют реальные
 * размеры товара (D, s, d₂, s₂, R, угол, H, M, L, толщина фланца и т.д.).
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'promen_blueprint_type' ) ) {
	function promen_blueprint_type( string $family, array $dims ): string {
		if ( ! empty( $dims['angle'] ) ) {
			return 'bend';
		}
		$f = function_exists( 'mb_strtolower' ) ? mb_strtolower( $family ) : strtolower( $family );
		$has = fn( $s ) => mb_strpos( $f, $s ) !== false;
		if ( $has( 'тройник' ) )                    return 'tee';
		if ( $has( 'переход' ) )                    return 'reducer';
		if ( $has( 'днищ' ) || $has( 'заглушк' ) )  return 'head';
		if ( $has( 'фланец' ) || $has( 'фланц' ) )  return 'flange';
		if ( $has( 'гайка' ) )                      return 'nut';
		if ( $has( 'шайба' ) )                      return 'washer';
		if ( $has( 'болт' ) || $has( 'винт' ) )     return 'bolt';
		if ( $has( 'шпильк' ) )                     return 'stud';
		if ( $has( 'труб' ) )                       return 'pipe';
		return 'section';
	}
}

$bp_type = promen_blueprint_type( (string) $family, $dims );
$fmt = fn( $v ) => promen_fmt_dim( (string) $v );

// Значения-выноски (только реальные; пустые не показываем).
$D   = $d_out !== '' ? $fmt( $d_out ) : '';
$S   = $wall !== '' ? $fmt( $wall ) : '';
$D2  = ! empty( $dims['outer_d_branch'] ) ? $fmt( $dims['outer_d_branch'] ) : '';
$S2  = ! empty( $dims['wall_branch'] ) ? $fmt( $dims['wall_branch'] ) : '';
$L   = ! empty( $dims['length'] ) ? $fmt( $dims['length'] ) : ( ! empty( $dims['length_mm'] ) ? $fmt( $dims['length_mm'] ) : '' );
$H   = ! empty( $dims['height_mm'] ) ? $fmt( $dims['height_mm'] ) : '';
$R   = ! empty( $dims['radius_mm'] ) ? $fmt( $dims['radius_mm'] ) : ( ! empty( $dims['radius'] ) ? $fmt( $dims['radius'] ) : '' );
$Dn  = ! empty( $dims['dn'] ) ? $fmt( $dims['dn'] ) : '';
$M   = ! empty( $dims['thread_size'] ) ? promen_thread_label( (string) $dims['thread_size'] ) : '';
$Dbolt = ! empty( $dims['bolt_circle_d'] ) ? $fmt( $dims['bolt_circle_d'] ) : '';
$Th    = ! empty( $dims['flange_thickness'] ) ? $fmt( $dims['flange_thickness'] ) : '';
$studs = $dims['stud_count'] ?? '';
$boltD = ! empty( $dims['bolt_d'] ) ? $fmt( $dims['bolt_d'] ) : '';
$wd    = ! empty( $dims['nominal_d_mm'] ) ? $fmt( $dims['nominal_d_mm'] ) : $Dn;
$wtype = $dims['washer_type'] ?? '';

// SVG-обёртки (единый стиль).
$svg_open = '<svg viewBox="0 0 400 240" preserveAspectRatio="xMidYMid meet" width="100%" aria-hidden="true"><defs><pattern id="bpF' . $bp_type . '" width="16" height="16" patternUnits="userSpaceOnUse"><path d="M 16 0 L 0 0 0 16" fill="none" stroke="rgba(109,140,166,.1)" stroke-width=".5"></path></pattern></defs><rect x="0" y="0" width="400" height="240" fill="url(#bpF' . $bp_type . ')"></rect><g fill="none" stroke-linecap="round" stroke-linejoin="round">';
$svg_close = '</g></svg>';
$fill  = 'rgba(109,140,166,.1)';
$fill2 = 'rgba(15,42,68,.35)';
$stroke = 'rgba(169,183,198,.75)';
$dim   = 'var(--g1)';
$axis  = 'rgba(255,255,255,.25)';
$lbl   = 'rgba(109,140,166,.75)';
$e = fn( $s ) => esc_html( $s );

$schem_labels = [
	'bend' => 'Схема геометрии · вид в плоскости изгиба',
	'tee' => 'Схема · магистраль и ответвление',
	'reducer' => 'Схема · переход диаметров',
	'head' => 'Схема · эллиптическое днище (осевое сечение)',
	'flange' => 'Схема · фланец (вид и сечение)',
	'bolt' => 'Схема · болт (резьба и длина)',
	'stud' => 'Схема · шпилька',
	'nut' => 'Схема · гайка шестигранная',
	'washer' => 'Схема · шайба (сечение)',
	'pipe' => 'Схема · труба (наружный диаметр и стенка)',
	'section' => 'Схема сечения · наружный диаметр и стенка',
];
?>
<section class="s s-dark" id="s01">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">01</span>Инженерный чертёж</div>
      <div class="s-meta">BLUEPRINT<?php echo $norm_key ? ' / ' . esc_html( $norm_key ) : ''; ?></div>
    </div>
    <div class="bp-panel">
      <div class="bp-grid-bg"></div>
      <div class="bp-dual reveal">
        <div class="bp-photo-wrap">
          <div class="bp-schematic">
            <div class="bp-schematic-lbl"><?php echo esc_html( $schem_labels[ $bp_type ] ?? $schem_labels['section'] ); ?></div>
            <?php echo $svg_open; // phpcs:ignore ?>
            <?php if ( $bp_type === 'bend' ) : ?>
              <path d="M 232 130 L 232 176 A 148 148 0 0 0 132 30 L 72 30 L 72 2 L 148 2 A 92 92 0 0 1 260 118 L 288 118 L 288 150 L 260 150 A 120 120 0 0 0 380 30 L 430 30 L 430 58 L 380 58 A 148 148 0 0 1 288 176 Z" transform="translate(0,40)" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></path>
              <text x="90" y="90" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" transform="rotate(-90 90 90)">D<?php echo $D ? ' ' . $e( $D ) : ''; ?></text>
              <text x="300" y="120" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11"><?php echo $e( $angle_txt ); ?></text>
              <?php if ( $R ) : ?><text x="300" y="200" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10">R <?php echo $e( $R ); ?></text><?php endif; ?>

            <?php elseif ( $bp_type === 'tee' ) : ?>
              <rect x="40" y="110" width="320" height="60" rx="4" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></rect>
              <rect x="170" y="30" width="60" height="90" rx="4" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></rect>
              <line x1="40" y1="140" x2="360" y2="140" stroke="<?php echo $axis; ?>" stroke-width="1" stroke-dasharray="6 5"></line>
              <line x1="200" y1="30" x2="200" y2="140" stroke="<?php echo $axis; ?>" stroke-width="1" stroke-dasharray="6 5"></line>
              <line x1="40" y1="182" x2="360" y2="182" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="200" y="176" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle">D<?php echo ( $D && $S ) ? ' ' . $e( $D . '×' . $S ) : ''; ?></text>
              <text x="245" y="70" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10">d<?php echo ( $D2 && $S2 ) ? ' ' . $e( $D2 . '×' . $S2 ) : ' (ответвление)'; ?></text>

            <?php elseif ( $bp_type === 'reducer' ) : ?>
              <path d="M 60 70 L 200 95 L 340 110 L 340 130 L 200 145 L 60 170 Z" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></path>
              <line x1="60" y1="120" x2="360" y2="120" stroke="<?php echo $axis; ?>" stroke-width="1" stroke-dasharray="6 5"></line>
              <line x1="48" y1="70" x2="48" y2="170" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="30" y="124" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" transform="rotate(-90 30 124)">D₁<?php echo $D ? ' ' . $e( $D ) : ''; ?></text>
              <line x1="356" y1="110" x2="356" y2="130" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="372" y="124" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" transform="rotate(-90 372 124)">D₂<?php echo $D2 ? ' ' . $e( $D2 ) : ''; ?></text>
              <?php if ( $L ) : ?><text x="200" y="200" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle">L <?php echo $e( $L ); ?></text><?php endif; ?>

            <?php elseif ( $bp_type === 'head' ) : ?>
              <path d="M 90 150 A 110 90 0 0 1 310 150 L 310 190 L 90 190 Z" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></path>
              <path d="M 100 150 A 100 82 0 0 1 300 150" fill="none" stroke="<?php echo $fill2; ?>" stroke-width="1.5"></path>
              <line x1="90" y1="196" x2="310" y2="196" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="200" y="212" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle">D<?php echo $D ? ' ' . $e( $D ) : ''; ?></text>
              <line x1="330" y1="60" x2="330" y2="150" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="346" y="110" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" transform="rotate(-90 346 110)">H<?php echo $H ? ' ' . $e( $H ) : ''; ?></text>

            <?php elseif ( $bp_type === 'flange' ) : ?>
              <circle cx="120" cy="120" r="82" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></circle>
              <circle cx="120" cy="120" r="34" fill="<?php echo $fill2; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.5"></circle>
              <circle cx="120" cy="120" r="62" fill="none" stroke="<?php echo $axis; ?>" stroke-width="1" stroke-dasharray="4 4"></circle>
              <?php for ( $i = 0; $i < 8; $i++ ) { $a = $i * M_PI / 4; printf( '<circle cx="%.1f" cy="%.1f" r="5" fill="none" stroke="%s" stroke-width="1"></circle>', 120 + 62 * cos( $a ), 120 + 62 * sin( $a ), $stroke ); } ?>
              <text x="120" y="118" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="9" text-anchor="middle">Dб<?php echo $Dbolt ? ' ' . $e( $Dbolt ) : ''; ?></text>
              <text x="120" y="220" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle">D нар<?php echo $D ? ' ' . $e( $D ) : ''; ?></text>
              <rect x="250" y="70" width="26" height="100" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></rect>
              <rect x="276" y="95" width="60" height="50" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></rect>
              <text x="263" y="200" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="9" text-anchor="middle">b<?php echo $Th ? ' ' . $e( $Th ) : ''; ?></text>

            <?php elseif ( $bp_type === 'bolt' ) : ?>
              <polygon points="40,95 52,80 76,80 88,95 76,110 52,110" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></polygon>
              <rect x="88" y="88" width="230" height="14" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></rect>
              <?php for ( $x = 96; $x < 318; $x += 10 ) { printf( '<line x1="%d" y1="86" x2="%d" y2="104" stroke="%s" stroke-width=".5"></line>', $x, $x - 4, $lbl ); } ?>
              <line x1="88" y1="130" x2="318" y2="130" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="203" y="148" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle">L<?php echo $L ? ' ' . $e( $L ) : ''; ?></text>
              <text x="64" y="70" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle"><?php echo $M ? $e( $M ) : 'резьба'; ?></text>

            <?php elseif ( $bp_type === 'stud' ) : ?>
              <rect x="60" y="105" width="280" height="14" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></rect>
              <?php for ( $x = 68; $x < 130; $x += 9 ) { printf( '<line x1="%d" y1="103" x2="%d" y2="121" stroke="%s" stroke-width=".5"></line>', $x, $x - 4, $lbl ); }
              for ( $x = 275; $x < 340; $x += 9 ) { printf( '<line x1="%d" y1="103" x2="%d" y2="121" stroke="%s" stroke-width=".5"></line>', $x, $x - 4, $lbl ); } ?>
              <line x1="60" y1="145" x2="340" y2="145" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="200" y="163" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle">L<?php echo $L ? ' ' . $e( $L ) : ''; ?></text>
              <text x="200" y="88" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle"><?php echo $M ? $e( $M ) : 'резьба'; ?></text>

            <?php elseif ( $bp_type === 'nut' ) : ?>
              <?php $cx = 200; $cy = 120; $r = 70; $pts = [];
              for ( $i = 0; $i < 6; $i++ ) { $a = M_PI / 6 + $i * M_PI / 3; $pts[] = sprintf( '%.1f,%.1f', $cx + $r * cos( $a ), $cy + $r * sin( $a ) ); } ?>
              <polygon points="<?php echo implode( ' ', $pts ); ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></polygon>
              <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="34" fill="<?php echo $fill2; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.5"></circle>
              <text x="<?php echo $cx; ?>" y="<?php echo $cy + 4; ?>" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="12" text-anchor="middle"><?php echo $M ? $e( $M ) : ''; ?></text>

            <?php elseif ( $bp_type === 'washer' ) : ?>
              <circle cx="200" cy="120" r="78" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></circle>
              <circle cx="200" cy="120" r="40" fill="<?php echo $fill2; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.5"></circle>
              <line x1="122" y1="120" x2="278" y2="120" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="200" y="112" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle">d<?php echo $wd ? ' ' . $e( $wd ) : ''; ?></text>

            <?php elseif ( $bp_type === 'pipe' ) : ?>
              <rect x="40" y="90" width="320" height="60" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></rect>
              <line x1="40" y1="98" x2="360" y2="98" stroke="<?php echo $fill2; ?>" stroke-width="1"></line>
              <line x1="40" y1="142" x2="360" y2="142" stroke="<?php echo $fill2; ?>" stroke-width="1"></line>
              <line x1="26" y1="90" x2="26" y2="150" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="14" y="124" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" transform="rotate(-90 14 124)">D<?php echo $D ? ' ' . $e( $D ) : ''; ?></text>
              <line x1="360" y1="90" x2="386" y2="98" stroke="<?php echo $dim; ?>" stroke-width=".6"></line>
              <text x="374" y="80" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10">s<?php echo $S ? ' ' . $e( $S ) : ''; ?></text>

            <?php else : /* section (fallback) */ ?>
              <circle cx="150" cy="120" r="78" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2.5"></circle>
              <circle cx="150" cy="120" r="58" fill="<?php echo $fill2; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.5"></circle>
              <line x1="72" y1="120" x2="228" y2="120" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
              <text x="150" y="112" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle">D<?php echo $D ? ' ' . $e( $D ) : ''; ?></text>
              <text x="300" y="70" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10">s<?php echo $S ? ' ' . $e( $S ) : ''; ?></text>
            <?php endif; ?>
            <?php echo $svg_close; // phpcs:ignore ?>
          </div>
        </div>
        <div class="bp-side">
          <div class="bp-tag">Позиция<strong><?php echo esc_html( $product->get_sku() ); ?></strong></div>
          <?php
          // Type-aware выноски.
          $tags = [];
          if ( $bp_type === 'bend' ) {
            $tags['Угол изгиба'] = $angle_txt;
            if ( $R ) { $tags['Радиус гиба'] = 'R = ' . $R . ' мм'; }
            if ( $D ) { $tags['Наружный диаметр'] = 'D = ' . $D . ' мм'; }
            if ( $S ) { $tags['Толщина стенки'] = 's = ' . $S . ' мм'; }
          } elseif ( $bp_type === 'tee' ) {
            if ( $D )  { $tags['Магистраль D×s'] = trim( $D . ( $S ? '×' . $S : '' ) ) . ' мм'; }
            if ( $D2 ) { $tags['Ответвление d×s'] = trim( $D2 . ( $S2 ? '×' . $S2 : '' ) ) . ' мм'; }
          } elseif ( $bp_type === 'reducer' ) {
            if ( $D )  { $tags['Больший D₁×s'] = trim( $D . ( $S ? '×' . $S : '' ) ) . ' мм'; }
            if ( $D2 ) { $tags['Меньший D₂×s'] = trim( $D2 . ( $S2 ? '×' . $S2 : '' ) ) . ' мм'; }
            if ( $L )  { $tags['Строит. длина'] = 'L = ' . $L . ' мм'; }
          } elseif ( $bp_type === 'head' ) {
            if ( $D ) { $tags['Наружный диаметр'] = 'D = ' . $D . ' мм'; }
            if ( $H ) { $tags['Высота борта'] = 'H = ' . $H . ' мм'; }
            if ( $S ) { $tags['Толщина стенки'] = 's = ' . $S . ' мм'; }
          } elseif ( $bp_type === 'flange' ) {
            if ( $D )     { $tags['Наружный диаметр'] = $D . ' мм'; }
            if ( $Dbolt ) { $tags['Диаметр окружности болтов'] = $Dbolt . ' мм'; }
            if ( $Dn )    { $tags['Проход DN'] = $Dn; }
            if ( $Th )    { $tags['Толщина фланца'] = $Th . ' мм'; }
            if ( $studs !== '' ) { $tags['Отверстий под шпильки'] = $studs; }
            if ( $boltD ) { $tags['Диаметр отверстия'] = $boltD . ' мм'; }
          } elseif ( $bp_type === 'bolt' || $bp_type === 'stud' ) {
            if ( $M ) { $tags['Резьба'] = $M; }
            if ( $L ) { $tags['Длина'] = 'L = ' . $L . ' мм'; }
            if ( ! empty( $dims['strength_class'] ) ) { $tags['Класс прочности'] = $dims['strength_class']; }
          } elseif ( $bp_type === 'nut' ) {
            if ( $M ) { $tags['Резьба'] = $M; }
          } elseif ( $bp_type === 'washer' ) {
            if ( $wd )    { $tags['Внутренний диаметр'] = 'd = ' . $wd . ' мм'; }
            if ( $wtype ) { $tags['Тип'] = $wtype; }
          } elseif ( $bp_type === 'pipe' ) {
            if ( $D ) { $tags['Наружный диаметр'] = 'D = ' . $D . ' мм'; }
            if ( $S ) { $tags['Толщина стенки'] = 's = ' . $S . ' мм'; }
            if ( $Dn ) { $tags['Условный проход'] = 'DN ' . $Dn; }
          } else {
            if ( $D ) { $tags['Наружный диаметр'] = 'D = ' . $D . ' мм'; }
            if ( $S ) { $tags['Толщина стенки'] = 's = ' . $S . ' мм'; }
          }
          foreach ( $tags as $k => $v ) : ?>
            <div class="bp-tag"><?php echo esc_html( $k ); ?><strong><?php echo esc_html( $v ); ?></strong></div>
          <?php endforeach; ?>
          <?php if ( $mass ) : ?><div class="bp-tag">Масса изделия<strong><?php echo esc_html( $mass ); ?> кг</strong></div><?php endif; ?>
          <?php if ( $norm_key ) : ?><div class="bp-tag">Стандарт<strong><?php echo esc_html( $norm_key ); ?></strong></div><?php endif; ?>
        </div>
      </div>
    </div>
  </section>
