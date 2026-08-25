<?php
/**
 * Геометрия инженерных чертежей (секция 01 карточки товара).
 *
 * Раньше контуры были захардкоженными path: у отвода 30°, 60° и 90° путь
 * совпадал побайтово, менялась только подпись угла — на карточке колена 30°
 * был нарисован отвод примерно на 90°. Для завода это прямая потеря
 * экспертности: инженер сверяет чертёж с паспортом.
 *
 * Здесь контур считается из реальных размеров изделия. Ничего не выдумываем:
 * если размера нет в данных, берём типовое соотношение по нормативу и НЕ
 * подписываем его цифрой — рисунок остаётся честным, а выноска не врёт.
 *
 * Все построители работают в миллиметрах и возвращают массив путей, которые
 * promen_bp_fit() вписывает в холст 400×240.
 */

defined( 'ABSPATH' ) || exit;

/** Холст SVG. */
const PROMEN_BP_W   = 400;
const PROMEN_BP_H   = 240;
const PROMEN_BP_PAD = 44;

/**
 * Вписать набор точек в холст: возвращает функцию-преобразователь мм → пиксели.
 *
 * @param array $pts Точки [[x,y], …] в мм, задающие габарит фигуры.
 * @return array{0:callable,1:float} Преобразователь и масштаб.
 */
function promen_bp_fit( array $pts ): array {
	$xs = array_column( $pts, 0 );
	$ys = array_column( $pts, 1 );
	$minx = min( $xs ); $maxx = max( $xs );
	$miny = min( $ys ); $maxy = max( $ys );
	$w = max( 0.001, $maxx - $minx );
	$h = max( 0.001, $maxy - $miny );

	$k = min( ( PROMEN_BP_W - 2 * PROMEN_BP_PAD ) / $w, ( PROMEN_BP_H - 2 * PROMEN_BP_PAD ) / $h );

	$ox = ( PROMEN_BP_W - $w * $k ) / 2 - $minx * $k;
	$oy = ( PROMEN_BP_H - $h * $k ) / 2 - $miny * $k;

	$tr = static fn( float $x, float $y ): array => [ $x * $k + $ox, $y * $k + $oy ];
	return [ $tr, $k ];
}

/** Точки дуги окружности — для габарита и для осевой линии. */
function promen_bp_arc_pts( float $cx, float $cy, float $r, float $a0, float $a1, int $steps = 24 ): array {
	$out = [];
	for ( $i = 0; $i <= $steps; $i++ ) {
		$a = $a0 + ( $a1 - $a0 ) * $i / $steps;
		$out[] = [ $cx + $r * cos( $a ), $cy + $r * sin( $a ) ];
	}
	return $out;
}

/** `x,y` с двумя знаками — чтобы path не разрастался. */
function promen_bp_p( array $pt ): string {
	return sprintf( '%.2f %.2f', $pt[0], $pt[1] );
}

/**
 * ОТВОД. Осевая — дуга радиуса R на угол $ang; тело трубы между R±D/2.
 *
 * Вход направлен вправо, дуга уводит его вверх: при 90° получается привычный
 * вид колена, при 15° — пологий загиб, при 180° — калач. Именно это и должно
 * быть видно на карточке.
 *
 * @param float $d_out Наружный диаметр, мм.
 * @param float $r_bend Радиус гиба по осевой, мм.
 * @param float $ang Угол изгиба, градусы.
 * @return array{path:string,axis:string,center:array,ends:array,r_known:bool}
 */
function promen_bp_bend_geometry( float $d_out, float $r_bend, float $ang ): array {
	$ang = max( 5.0, min( 180.0, $ang ) );
	$d   = $d_out > 0 ? $d_out : 100.0;

	// Радиуса гиба может не быть в данных. Берём 1.5 DN — типовой 3D по
	// ГОСТ 17375. Флагом сообщаем наружу, что цифру подписывать нельзя.
	$r_known = $r_bend > 0;
	$r = $r_known ? $r_bend : 1.5 * $d;
	// Слишком тугой радиус физически невозможен: осевая должна оставаться
	// снаружи внутренней стенки.
	$r = max( $r, 0.6 * $d );

	$e = max( 0.3 * $d, 0.08 * $r );   // прямые участки у торцов

	$a0 = M_PI / 2;                     // старт: точка под центром, ход вправо
	$a1 = $a0 - deg2rad( $ang );

	$ro = $r + $d / 2;
	$ri = $r - $d / 2;

	// Касательная в конце дуги (движение при убывающем угле).
	$t1 = [ sin( $a1 ), -cos( $a1 ) ];
	// Радиальный орт в конце — вдоль него уходим с внешней стороны на внутреннюю.
	$n1 = [ cos( $a1 ), sin( $a1 ) ];

	$o_start = [ $ro * cos( $a0 ), $ro * sin( $a0 ) ];
	$i_start = [ $ri * cos( $a0 ), $ri * sin( $a0 ) ];
	$o_end   = [ $ro * cos( $a1 ), $ro * sin( $a1 ) ];
	$i_end   = [ $ri * cos( $a1 ), $ri * sin( $a1 ) ];

	// Прямые участки: вход уходит влево от старта, выход — вперёд по касательной.
	$o_start_e = [ $o_start[0] - $e, $o_start[1] ];
	$i_start_e = [ $i_start[0] - $e, $i_start[1] ];
	$o_end_e   = [ $o_end[0] + $e * $t1[0], $o_end[1] + $e * $t1[1] ];
	$i_end_e   = [ $i_end[0] + $e * $t1[0], $i_end[1] + $e * $t1[1] ];

	// Габарит: концы плюс сама дуга (её экстремумы прямыми не описать).
	$bbox = array_merge(
		[ $o_start_e, $i_start_e, $o_end_e, $i_end_e ],
		promen_bp_arc_pts( 0, 0, $ro, $a1, $a0 ),
		promen_bp_arc_pts( 0, 0, $ri, $a1, $a0 )
	);

	[ $tr ] = promen_bp_fit( $bbox );

	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );
	// Масштабированные радиусы: fit сохраняет пропорции, берём по любой оси.
	$k   = ( $tr( 1, 0 )[0] - $tr( 0, 0 )[0] );
	$sro = $ro * $k;
	$sri = $ri * $k;

	// sweep=0 — против часовой на экране (y вниз): низ → право → верх.
	$large = $ang > 180 ? 1 : 0;
	$path = 'M ' . $P( $o_start_e )
		. ' L ' . $P( $o_start )
		. sprintf( ' A %.2f %.2f 0 %d 0 ', $sro, $sro, $large ) . $P( $o_end )
		. ' L ' . $P( $o_end_e )
		. ' L ' . $P( $i_end_e )
		. ' L ' . $P( $i_end )
		. sprintf( ' A %.2f %.2f 0 %d 1 ', $sri, $sri, $large ) . $P( $i_start )
		. ' L ' . $P( $i_start_e )
		. ' Z';

	$ax_start = [ $r * cos( $a0 ) - $e * 1.6, $r * sin( $a0 ) ];
	$ax_end   = [ $r * cos( $a1 ) + $e * 1.6 * $t1[0], $r * sin( $a1 ) + $e * 1.6 * $t1[1] ];
	$sr = $r * $k;
	$axis = 'M ' . $P( $ax_start )
		. ' L ' . $P( [ $r * cos( $a0 ), $r * sin( $a0 ) ] )
		. sprintf( ' A %.2f %.2f 0 %d 0 ', $sr, $sr, $large ) . $P( [ $r * cos( $a1 ), $r * sin( $a1 ) ] )
		. ' L ' . $P( $ax_end );

	return [
		'path'    => $path,
		'axis'    => $axis,
		'center'  => $tr( 0, 0 ),
		'r_known' => $r_known,
		'ends'    => [
			'in_out'  => $tr( $o_start_e[0], $o_start_e[1] ),
			'in_in'   => $tr( $i_start_e[0], $i_start_e[1] ),
			'mid_arc' => $tr( $r * cos( ( $a0 + $a1 ) / 2 ), $r * sin( ( $a0 + $a1 ) / 2 ) ),
		],
		// Точки осевой на дуге и метка угла между радиусами из центра.
		'arc0'    => $tr( $r * cos( $a0 ), $r * sin( $a0 ) ),
		'arc1'    => $tr( $r * cos( $a1 ), $r * sin( $a1 ) ),
		'mark'    => [
			'r'  => min( 0.42 * $sr, 46.0 ),
			'p0' => $tr( 0.42 * $r * cos( $a0 ), 0.42 * $r * sin( $a0 ) ),
			'p1' => $tr( 0.42 * $r * cos( $a1 ), 0.42 * $r * sin( $a1 ) ),
			'txt' => $tr( 0.46 * $r * cos( ( $a0 + $a1 ) / 2 ), 0.46 * $r * sin( ( $a0 + $a1 ) / 2 ) ),
		],
	];
}

/**
 * ТРОЙНИК. Магистраль D×L, ответвление d₂ высотой h.
 * Переходный тройник обязан выглядеть переходным: у ответвления свой диаметр.
 */
function promen_bp_tee_geometry( float $d_run, float $d_br, float $len, float $h_br ): array {
	$d  = $d_run > 0 ? $d_run : 100.0;
	$d2 = $d_br > 0 ? min( $d_br, $d ) : $d;          // равнопроходный по умолчанию
	$l  = $len > 0 ? $len : 2.6 * $d;
	$h  = $h_br > 0 ? $h_br : ( $d / 2 + max( 0.7 * $d, 0.5 * $d2 ) );

	$half  = $d / 2;
	$half2 = $d2 / 2;

	// Магистраль лежит горизонтально, ось по y=0; ответвление вверх.
	$run  = [ [ -$l / 2, -$half ], [ $l / 2, -$half ], [ $l / 2, $half ], [ -$l / 2, $half ] ];
	$br   = [ [ -$half2, -$h ], [ $half2, -$h ], [ $half2, -$half ], [ -$half2, -$half ] ];

	[ $tr ] = promen_bp_fit( array_merge( $run, $br ) );
	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );

	// Единый контур: у настоящего тройника стенка магистрали в месте врезки
	// отсутствует. Двумя прямоугольниками получалась коробка, приставленная
	// к трубе, и линия магистрали шла сквозь ответвление.
	$body = 'M ' . $P( [ -$l / 2, -$half ] )
		. ' L ' . $P( [ -$half2, -$half ] )
		. ' L ' . $P( [ -$half2, -$h ] )
		. ' L ' . $P( [ $half2, -$h ] )
		. ' L ' . $P( [ $half2, -$half ] )
		. ' L ' . $P( [ $l / 2, -$half ] )
		. ' L ' . $P( [ $l / 2, $half ] )
		. ' L ' . $P( [ -$l / 2, $half ] )
		. ' Z';

	return [
		'body'     => $body,
		'axis_h'   => [ $tr( -$l / 2 - 6, 0 ), $tr( $l / 2 + 6, 0 ) ],
		'axis_v'   => [ $tr( 0, -$h - 6 ), $tr( 0, $half + 6 ) ],
		'run_dim'  => [ $tr( -$l / 2, $half ), $tr( $l / 2, $half ) ],
		'br_top'   => $tr( 0, -$h ),
		'equal'    => abs( $d2 - $d ) < 0.01,
	];
}

/**
 * ПЕРЕХОД концентрический: D₁ → D₂ за длину L, с короткими цилиндрами по краям.
 */
function promen_bp_reducer_geometry( float $d1, float $d2, float $len ): array {
	$a = $d1 > 0 ? $d1 : 100.0;
	$b = $d2 > 0 ? min( $d2, $a ) : 0.6 * $a;
	if ( $b >= $a ) { $b = 0.6 * $a; }
	$l = $len > 0 ? $len : 1.2 * $a;

	$cyl = max( 0.08 * $l, 0.05 * $a );   // прямые участки у торцов
	$x0  = -$l / 2;
	$x1  = $x0 + $cyl;
	$x3  = $l / 2;
	$x2  = $x3 - $cyl;

	$pts = [
		[ $x0, -$a / 2 ], [ $x1, -$a / 2 ], [ $x2, -$b / 2 ], [ $x3, -$b / 2 ],
		[ $x3, $b / 2 ], [ $x2, $b / 2 ], [ $x1, $a / 2 ], [ $x0, $a / 2 ],
	];
	[ $tr ] = promen_bp_fit( $pts );
	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );

	$path = 'M ' . implode( ' L ', array_map( $P, $pts ) ) . ' Z';

	return [
		'path'   => $path,
		'axis'   => [ $tr( $x0 - 6, 0 ), $tr( $x3 + 6, 0 ) ],
		'big'    => [ $tr( $x0, -$a / 2 ), $tr( $x0, $a / 2 ) ],
		'small'  => [ $tr( $x3, -$b / 2 ), $tr( $x3, $b / 2 ) ],
		'len'    => [ $tr( $x0, $a / 2 ), $tr( $x3, $a / 2 ) ],
	];
}

/**
 * ДНИЩЕ эллиптическое, осевое сечение: купол высотой H и цилиндрический борт.
 */
function promen_bp_head_geometry( float $d_out, float $h_dome, float $skirt, float $wall ): array {
	$d = $d_out > 0 ? $d_out : 400.0;
	// По ГОСТ 6533 высота выпуклой части эллиптического днища — четверть диаметра.
	$h = $h_dome > 0 ? $h_dome : 0.25 * $d;
	$b = $skirt > 0 ? $skirt : max( 0.06 * $d, 0.4 * $h );
	$s = $wall > 0 ? min( $wall, 0.12 * $d ) : 0.03 * $d;

	$rx = $d / 2;
	$pts = [ [ -$rx, 0 ], [ $rx, 0 ], [ -$rx, -$h ], [ $rx, -$h ], [ -$rx, $b ], [ $rx, $b ] ];
	[ $tr ] = promen_bp_fit( $pts );
	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );
	$k = $tr( 1, 0 )[0] - $tr( 0, 0 )[0];

	// Наружный контур: купол-полуэллипс + борт вниз.
	$outer = 'M ' . $P( [ -$rx, $b ] ) . ' L ' . $P( [ -$rx, 0 ] )
		. sprintf( ' A %.2f %.2f 0 0 1 ', $rx * $k, $h * $k ) . $P( [ $rx, 0 ] )
		. ' L ' . $P( [ $rx, $b ] ) . ' Z';
	// Внутренний обвод — стенка постоянной толщины.
	$inner = 'M ' . $P( [ -$rx + $s, $b ] ) . ' L ' . $P( [ -$rx + $s, 0 ] )
		. sprintf( ' A %.2f %.2f 0 0 1 ', ( $rx - $s ) * $k, max( 1.0, ( $h - $s ) ) * $k ) . $P( [ $rx - $s, 0 ] )
		. ' L ' . $P( [ $rx - $s, $b ] );

	return [
		'outer'  => $outer,
		'inner'  => $inner,
		'axis'   => [ $tr( 0, -$h - 8 ), $tr( 0, $b + 8 ) ],
		'd_dim'  => [ $tr( -$rx, $b ), $tr( $rx, $b ) ],
		'h_dim'  => [ $tr( $rx, -$h ), $tr( $rx, 0 ) ],
		'h_known' => $h_dome > 0,
	];
}

/**
 * ФЛАНЕЦ: вид с торца (наружный диаметр, окружность и реальное число отверстий)
 * плюс сечение справа (толщина, шейка у воротникового).
 */
function promen_bp_flange_geometry( float $d_out, float $d_bolt, float $d_hole, int $holes, float $thick, float $d_bore, bool $neck ): array {
	$d  = $d_out > 0 ? $d_out : 200.0;
	$db = $d_bolt > 0 ? min( $d_bolt, 0.92 * $d ) : 0.75 * $d;
	$dh = $d_hole > 0 ? $d_hole : max( 0.05 * $d, 0.09 * ( $d - $db ) );
	$n  = $holes > 0 ? $holes : 8;
	$b  = $thick > 0 ? $thick : 0.1 * $d;
	$bore = $d_bore > 0 ? min( $d_bore, 0.7 * $d ) : 0.42 * $d;

	$r  = $d / 2;
	// Вид и сечение стоят рядом: общий габарит по обоим.
	$gap = 0.18 * $d;
	$sec_x0 = $r + $gap;
	$sec_x1 = $sec_x0 + $b;
	$hub_l  = $neck ? max( 1.6 * $b, 0.22 * $d ) : 0.0;

	$pts = [ [ -$r, -$r ], [ $r, $r ], [ $sec_x1 + $hub_l, -$r ], [ $sec_x1 + $hub_l, $r ] ];
	[ $tr ] = promen_bp_fit( $pts );
	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );
	$k = $tr( 1, 0 )[0] - $tr( 0, 0 )[0];

	$hole_pts = [];
	for ( $i = 0; $i < $n; $i++ ) {
		$a = -M_PI / 2 + 2 * M_PI * $i / $n;
		$hole_pts[] = [ $tr( ( $db / 2 ) * cos( $a ), ( $db / 2 ) * sin( $a ) ), $dh / 2 * $k ];
	}

	// Сечение: полка сверху и снизу, между ними проход; у воротникового — шейка.
	$sec = [];
	$sec[] = 'M ' . $P( [ $sec_x0, -$r ] ) . ' L ' . $P( [ $sec_x1, -$r ] )
		. ' L ' . $P( [ $sec_x1, -$bore / 2 ] ) . ' L ' . $P( [ $sec_x0, -$bore / 2 ] ) . ' Z';
	$sec[] = 'M ' . $P( [ $sec_x0, $r ] ) . ' L ' . $P( [ $sec_x1, $r ] )
		. ' L ' . $P( [ $sec_x1, $bore / 2 ] ) . ' L ' . $P( [ $sec_x0, $bore / 2 ] ) . ' Z';
	if ( $neck ) {
		$hub_r = $bore / 2 + max( 0.035 * $d, 0.35 * $b );
		$sec[] = 'M ' . $P( [ $sec_x1, -$hub_r * 1.55 ] ) . ' L ' . $P( [ $sec_x1 + $hub_l, -$hub_r ] )
			. ' L ' . $P( [ $sec_x1 + $hub_l, -$bore / 2 ] ) . ' L ' . $P( [ $sec_x1, -$bore / 2 ] ) . ' Z';
		$sec[] = 'M ' . $P( [ $sec_x1, $hub_r * 1.55 ] ) . ' L ' . $P( [ $sec_x1 + $hub_l, $hub_r ] )
			. ' L ' . $P( [ $sec_x1 + $hub_l, $bore / 2 ] ) . ' L ' . $P( [ $sec_x1, $bore / 2 ] ) . ' Z';
	}

	return [
		'face_c'   => $tr( 0, 0 ),
		'face_r'   => $r * $k,
		'bore_r'   => $bore / 2 * $k,
		'bolt_r'   => $db / 2 * $k,
		'holes'    => $hole_pts,
		'holes_n'  => $n,
		'section'  => $sec,
		'sec_dim'  => [ $tr( $sec_x0, $r ), $tr( $sec_x1, $r ) ],
		'd_dim'    => [ $tr( -$r, $r ), $tr( $r, $r ) ],
		// Осевая разреза: без неё две половины полки читались как две детали.
		'sec_axis' => [ $tr( $sec_x0 - 0.04 * $d, 0 ), $tr( $sec_x1 + $hub_l + 0.04 * $d, 0 ) ],
		'face_axis_h' => [ $tr( -$r * 1.12, 0 ), $tr( $r * 1.12, 0 ) ],
		'face_axis_v' => [ $tr( 0, -$r * 1.12 ), $tr( 0, $r * 1.12 ) ],
	];
}

/**
 * ТРУБА: наружный диаметр и стенка в реальном соотношении.
 * Тонкостенная и толстостенная должны отличаться на глаз.
 */
function promen_bp_pipe_geometry( float $d_out, float $wall, float $len ): array {
	$d = $d_out > 0 ? $d_out : 100.0;
	$s = $wall > 0 ? min( $wall, 0.45 * $d ) : 0.04 * $d;
	$l = $len > 0 ? $len : 3.2 * $d;

	$pts = [ [ -$l / 2, -$d / 2 ], [ $l / 2, $d / 2 ] ];
	[ $tr ] = promen_bp_fit( $pts );
	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );

	return [
		'body'  => 'M ' . $P( [ -$l / 2, -$d / 2 ] ) . ' L ' . $P( [ $l / 2, -$d / 2 ] )
			. ' L ' . $P( [ $l / 2, $d / 2 ] ) . ' L ' . $P( [ -$l / 2, $d / 2 ] ) . ' Z',
		'wall_top'    => [ $tr( -$l / 2, -$d / 2 + $s ), $tr( $l / 2, -$d / 2 + $s ) ],
		'wall_bottom' => [ $tr( -$l / 2, $d / 2 - $s ), $tr( $l / 2, $d / 2 - $s ) ],
		'd_dim'       => [ $tr( -$l / 2, -$d / 2 ), $tr( -$l / 2, $d / 2 ) ],
		's_dim'       => [ $tr( $l / 2, -$d / 2 ), $tr( $l / 2, -$d / 2 + $s ) ],
	];
}

/**
 * ШАЙБА: наружный и внутренний диаметры в реальном соотношении.
 */
function promen_bp_washer_geometry( float $d_out, float $d_in, float $thick ): array {
	$d  = $d_out > 0 ? $d_out : 40.0;
	$di = $d_in > 0 ? min( $d_in, 0.85 * $d ) : 0.45 * $d;
	$t  = $thick > 0 ? $thick : 0.08 * $d;

	$gap = 0.3 * $d;
	$pts = [ [ -$d / 2, -$d / 2 ], [ $d / 2, $d / 2 ], [ $d / 2 + $gap + $t, 0 ] ];
	[ $tr ] = promen_bp_fit( $pts );
	$k = $tr( 1, 0 )[0] - $tr( 0, 0 )[0];
	$P = static fn( array $pt ): string => promen_bp_p( $tr( $pt[0], $pt[1] ) );

	$x0 = $d / 2 + $gap;
	return [
		'c'      => $tr( 0, 0 ),
		'r_out'  => $d / 2 * $k,
		'r_in'   => $di / 2 * $k,
		'sec'    => [
			'M ' . $P( [ $x0, -$d / 2 ] ) . ' L ' . $P( [ $x0 + $t, -$d / 2 ] )
				. ' L ' . $P( [ $x0 + $t, -$di / 2 ] ) . ' L ' . $P( [ $x0, -$di / 2 ] ) . ' Z',
			'M ' . $P( [ $x0, $d / 2 ] ) . ' L ' . $P( [ $x0 + $t, $d / 2 ] )
				. ' L ' . $P( [ $x0 + $t, $di / 2 ] ) . ' L ' . $P( [ $x0, $di / 2 ] ) . ' Z',
		],
		'd_dim'  => [ $tr( -$d / 2, 0 ), $tr( $d / 2, 0 ) ],
	];
}

/** Число из данных: в каталоге размеры лежат строками, иногда с запятой. */
function promen_bp_num( $v ): float {
	if ( is_numeric( $v ) ) {
		return (float) $v;
	}
	$s = str_replace( [ ' ', ' ', ',' ], [ '', '', '.' ], (string) $v );
	return is_numeric( $s ) ? (float) $s : 0.0;
}

/**
 * Размерная линия с засечками и подписью — по ГОСТ 2.307 засечки, не стрелки.
 *
 * @param array  $p1    Начало, экранные координаты.
 * @param array  $p2    Конец.
 * @param string $label Текст; пустой — линия без подписи.
 * @param string $color Цвет линии.
 * @param string $lbl   Цвет подписи.
 * @param int    $off   Отступ подписи от линии.
 */
function promen_bp_dim( array $p1, array $p2, string $label, string $color, string $lbl, int $off = -6 ): string {
	$dx = $p2[0] - $p1[0];
	$dy = $p2[1] - $p1[1];
	$len = sqrt( $dx * $dx + $dy * $dy );
	if ( $len < 1 ) {
		return '';
	}
	$ux = $dx / $len; $uy = $dy / $len;
	$nx = -$uy; $ny = $ux;              // нормаль
	$t  = 3.5;                           // половина засечки

	$out = sprintf(
		'<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width=".75"></line>',
		$p1[0], $p1[1], $p2[0], $p2[1], $color
	);
	foreach ( [ $p1, $p2 ] as $p ) {
		$out .= sprintf(
			'<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width=".75"></line>',
			$p[0] - $nx * $t, $p[1] - $ny * $t, $p[0] + $nx * $t, $p[1] + $ny * $t, $color
		);
	}
	if ( $label !== '' ) {
		$mx = ( $p1[0] + $p2[0] ) / 2 + $nx * $off;
		$my = ( $p1[1] + $p2[1] ) / 2 + $ny * $off;
		$rot = abs( $ux ) < 0.3 ? sprintf( ' transform="rotate(-90 %.1f %.1f)"', $mx, $my ) : '';
		$out .= sprintf(
			'<text x="%.1f" y="%.1f" fill="%s" font-family="monospace" font-size="10" text-anchor="middle"%s>%s</text>',
			$mx, $my + 3, $lbl, $rot, esc_html( $label )
		);
	}
	return $out;
}

/** Осевая линия — штрихпунктир по ГОСТ. */
function promen_bp_axis( array $p1, array $p2, string $color ): string {
	return sprintf(
		'<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke="%s" stroke-width="1" stroke-dasharray="10 3 2 3"></line>',
		$p1[0], $p1[1], $p2[0], $p2[1], $color
	);
}
