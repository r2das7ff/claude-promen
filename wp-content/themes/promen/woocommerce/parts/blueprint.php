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
      <div class="s-meta"><?php echo $norm_key ? esc_html( $norm_key ) : ''; ?></div>
    </div>
    <div class="bp-panel">
      <div class="bp-grid-bg"></div>
      <div class="bp-dual reveal">
        <div class="bp-photo-wrap">
          <div class="bp-schematic">
            <div class="bp-schematic-lbl"><?php echo esc_html( $schem_labels[ $bp_type ] ?? $schem_labels['section'] ); ?></div>
            <?php echo $svg_open; // phpcs:ignore ?>
            <?php
            // Контуры считаются из реальных размеров изделия — inc/blueprint-geometry.php.
            $n  = fn( $k, $alt = '' ) => promen_bp_num( $dims[ $k ] ?? ( $alt !== '' ? ( $dims[ $alt ] ?? 0 ) : 0 ) );
            $dO = $n( 'outer_diameter' );
            $sW = $n( 'wall_thickness' );
            ?>
            <?php if ( $bp_type === 'bend' ) :
              $g = promen_bp_bend_geometry( $dO, $n( 'radius_mm', 'radius' ), $n( 'angle' ) ?: 90 );
              ?>
              <path d="<?php echo $g['path']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></path>
              <path d="<?php echo $g['axis']; ?>" fill="none" stroke="<?php echo $axis; ?>" stroke-width="1" stroke-dasharray="10 3 2 3"></path>
              <line x1="<?php echo $g['center'][0]; ?>" y1="<?php echo $g['center'][1]; ?>" x2="<?php echo $g['arc0'][0]; ?>" y2="<?php echo $g['arc0'][1]; ?>" stroke="<?php echo $axis; ?>" stroke-width=".75" stroke-dasharray="4 4"></line>
              <line x1="<?php echo $g['center'][0]; ?>" y1="<?php echo $g['center'][1]; ?>" x2="<?php echo $g['arc1'][0]; ?>" y2="<?php echo $g['arc1'][1]; ?>" stroke="<?php echo $axis; ?>" stroke-width=".75" stroke-dasharray="4 4"></line>
              <path d="M <?php echo $g['mark']['p0'][0] . ' ' . $g['mark']['p0'][1]; ?> A <?php printf( '%.1f %.1f 0 0 0 ', $g['mark']['r'], $g['mark']['r'] ); echo $g['mark']['p1'][0] . ' ' . $g['mark']['p1'][1]; ?>" fill="none" stroke="<?php echo $dim; ?>" stroke-width=".75"></path>
              <text x="<?php echo $g['mark']['txt'][0]; ?>" y="<?php echo $g['mark']['txt'][1]; ?>" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="11" text-anchor="middle"><?php echo $e( $angle_txt ); ?></text>
              <?php echo promen_bp_dim( $g['ends']['in_out'], $g['ends']['in_in'], $D ? 'D ' . $D : 'D', $dim, $lbl, -10 ); ?>
              <?php if ( $g['r_known'] && $R ) : ?>
                <line x1="<?php echo $g['center'][0]; ?>" y1="<?php echo $g['center'][1]; ?>" x2="<?php echo $g['ends']['mid_arc'][0]; ?>" y2="<?php echo $g['ends']['mid_arc'][1]; ?>" stroke="<?php echo $dim; ?>" stroke-width=".75"></line>
                <?php // Подпись радиуса — у самой дуги: рядом с центром она сталкивалась с меткой угла. ?>
                <text x="<?php echo $g['center'][0] + ( $g['ends']['mid_arc'][0] - $g['center'][0] ) * 0.84; ?>" y="<?php echo $g['center'][1] + ( $g['ends']['mid_arc'][1] - $g['center'][1] ) * 0.84 - 6; ?>" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle">R <?php echo $e( $R ); ?></text>
              <?php endif; ?>

            <?php elseif ( $bp_type === 'tee' ) :
              $g = promen_bp_tee_geometry( $dO, $n( 'outer_d_branch' ), $n( 'length_mm', 'length' ), 0 );
              ?>
              <path d="<?php echo $g['body']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></path>
              <?php echo promen_bp_axis( $g['axis_h'][0], $g['axis_h'][1], $axis ); ?>
              <?php echo promen_bp_axis( $g['axis_v'][0], $g['axis_v'][1], $axis ); ?>
              <?php echo promen_bp_dim( $g['run_dim'][0], $g['run_dim'][1], $L ? 'L ' . $L : '', $dim, $lbl, 14 ); ?>
              <text x="<?php echo $g['br_top'][0]; ?>" y="<?php echo $g['br_top'][1] - 8; ?>" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle">d<?php echo ( $D2 && $S2 ) ? ' ' . $e( $D2 . '×' . $S2 ) : ''; ?></text>
              <text x="<?php echo $g['axis_h'][0][0] + 4; ?>" y="<?php echo $g['axis_h'][0][1] - 8; ?>" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10">D<?php echo ( $D && $S ) ? ' ' . $e( $D . '×' . $S ) : ''; ?></text>

            <?php elseif ( $bp_type === 'reducer' ) :
              $g = promen_bp_reducer_geometry( $dO, $n( 'outer_d_branch' ), $n( 'length_mm', 'length' ) );
              ?>
              <path d="<?php echo $g['path']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></path>
              <?php echo promen_bp_axis( $g['axis'][0], $g['axis'][1], $axis ); ?>
              <?php echo promen_bp_dim( $g['big'][0], $g['big'][1], $D ? 'D₁ ' . $D : 'D₁', $dim, $lbl, -12 ); ?>
              <?php echo promen_bp_dim( $g['small'][0], $g['small'][1], $D2 ? 'D₂ ' . $D2 : 'D₂', $dim, $lbl, 12 ); ?>
              <?php echo promen_bp_dim( $g['len'][0], $g['len'][1], $L ? 'L ' . $L : '', $dim, $lbl, 16 ); ?>

            <?php elseif ( $bp_type === 'head' ) :
              $g = promen_bp_head_geometry( $dO, $n( 'height_mm', 'h_mm' ), $n( 'skirt_mm' ), $sW );
              ?>
              <path d="<?php echo $g['outer']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></path>
              <path d="<?php echo $g['inner']; ?>" fill="none" stroke="<?php echo $fill2; ?>" stroke-width="1.2"></path>
              <?php echo promen_bp_axis( $g['axis'][0], $g['axis'][1], $axis ); ?>
              <?php echo promen_bp_dim( $g['d_dim'][0], $g['d_dim'][1], $D ? 'D ' . $D : 'D', $dim, $lbl, 16 ); ?>
              <?php if ( $g['h_known'] && $H ) { echo promen_bp_dim( $g['h_dim'][0], $g['h_dim'][1], 'H ' . $H, $dim, $lbl, -12 ); } ?>

            <?php elseif ( $bp_type === 'flange' ) :
              $g = promen_bp_flange_geometry(
                $dO, $n( 'bolt_circle_d' ), $n( 'bolt_d' ), (int) ( $dims['stud_count'] ?? 0 ),
                $n( 'flange_thickness' ), $n( 'bore_d' ) ?: $n( 'dn' ), (string) ( $dims['flange_type'] ?? '' ) === '11'
              );
              ?>
              <circle cx="<?php echo $g['face_c'][0]; ?>" cy="<?php echo $g['face_c'][1]; ?>" r="<?php echo $g['face_r']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></circle>
              <circle cx="<?php echo $g['face_c'][0]; ?>" cy="<?php echo $g['face_c'][1]; ?>" r="<?php echo $g['bolt_r']; ?>" fill="none" stroke="<?php echo $axis; ?>" stroke-width="1" stroke-dasharray="6 4"></circle>
              <circle cx="<?php echo $g['face_c'][0]; ?>" cy="<?php echo $g['face_c'][1]; ?>" r="<?php echo $g['bore_r']; ?>" fill="<?php echo $fill2; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.5"></circle>
              <?php foreach ( $g['holes'] as $h ) : ?>
                <circle cx="<?php echo $h[0][0]; ?>" cy="<?php echo $h[0][1]; ?>" r="<?php echo max( 2.0, $h[1] ); ?>" fill="none" stroke="<?php echo $stroke; ?>" stroke-width="1.2"></circle>
              <?php endforeach; ?>
              <?php foreach ( $g['section'] as $sp ) : ?>
                <path d="<?php echo $sp; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.8"></path>
              <?php endforeach; ?>
              <?php echo promen_bp_axis( $g['sec_axis'][0], $g['sec_axis'][1], $axis ); ?>
              <?php echo promen_bp_axis( $g['face_axis_h'][0], $g['face_axis_h'][1], $axis ); ?>
              <?php echo promen_bp_axis( $g['face_axis_v'][0], $g['face_axis_v'][1], $axis ); ?>
              <?php echo promen_bp_dim( $g['d_dim'][0], $g['d_dim'][1], $D ? 'D ' . $D : 'D', $dim, $lbl, 16 ); ?>
              <?php echo promen_bp_dim( $g['sec_dim'][0], $g['sec_dim'][1], $Th ? 'b ' . $Th : 'b', $dim, $lbl, 16 ); ?>
              <text x="<?php echo $g['face_c'][0]; ?>" y="<?php echo $g['face_c'][1] - $g['face_r'] - 8; ?>" fill="<?php echo $lbl; ?>" font-family="monospace" font-size="10" text-anchor="middle"><?php echo (int) $g['holes_n']; ?> отв.<?php echo $boltD ? ' ⌀' . $e( $boltD ) : ''; ?></text>

            <?php elseif ( $bp_type === 'pipe' ) :
              $g = promen_bp_pipe_geometry( $dO, $sW, $n( 'length_mm', 'length' ) );
              ?>
              <path d="<?php echo $g['body']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></path>
              <line x1="<?php echo $g['wall_top'][0][0]; ?>" y1="<?php echo $g['wall_top'][0][1]; ?>" x2="<?php echo $g['wall_top'][1][0]; ?>" y2="<?php echo $g['wall_top'][1][1]; ?>" stroke="<?php echo $fill2; ?>" stroke-width="1.2"></line>
              <line x1="<?php echo $g['wall_bottom'][0][0]; ?>" y1="<?php echo $g['wall_bottom'][0][1]; ?>" x2="<?php echo $g['wall_bottom'][1][0]; ?>" y2="<?php echo $g['wall_bottom'][1][1]; ?>" stroke="<?php echo $fill2; ?>" stroke-width="1.2"></line>
              <?php echo promen_bp_dim( $g['d_dim'][0], $g['d_dim'][1], $D ? 'D ' . $D : 'D', $dim, $lbl, -12 ); ?>
              <?php echo promen_bp_dim( $g['s_dim'][0], $g['s_dim'][1], $S ? 's ' . $S : 's', $dim, $lbl, 14 ); ?>

            <?php elseif ( $bp_type === 'washer' ) :
              $g = promen_bp_washer_geometry( $dO ?: promen_bp_num( $dims['outer_d'] ?? 0 ), promen_bp_num( $dims['nominal_d_mm'] ?? $dims['dn'] ?? 0 ), $sW );
              ?>
              <circle cx="<?php echo $g['c'][0]; ?>" cy="<?php echo $g['c'][1]; ?>" r="<?php echo $g['r_out']; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="2"></circle>
              <circle cx="<?php echo $g['c'][0]; ?>" cy="<?php echo $g['c'][1]; ?>" r="<?php echo $g['r_in']; ?>" fill="<?php echo $fill2; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.5"></circle>
              <?php foreach ( $g['sec'] as $sp ) : ?>
                <path d="<?php echo $sp; ?>" fill="<?php echo $fill; ?>" stroke="<?php echo $stroke; ?>" stroke-width="1.8"></path>
              <?php endforeach; ?>
              <?php // Подпись уводим за окружность: в центре она ложилась на отверстие. ?>
              <?php echo promen_bp_dim( $g['d_dim'][0], $g['d_dim'][1], $wd ? 'd ' . $wd : 'd', $dim, $lbl, -( (int) $g['r_out'] + 16 ) ); ?>

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
