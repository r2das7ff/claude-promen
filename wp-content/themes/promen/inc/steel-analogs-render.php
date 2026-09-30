<?php
/**
 * Подборщик аналогов марок стали — серверная отрисовка паспорта и таблицы.
 *
 * Разметка паспорта повторяет render() в assets/js/steel-analogs.js: сервер
 * рисует первый паспорт (пример или ?marka=…), скрипт перерисовывает его при
 * выборе другой марки. Меняете разметку здесь — меняйте и там.
 */

defined( 'ABSPATH' ) || exit;

/** Элементы химсостава в порядке показа; C, Si, Mn показываются всегда. */
const PROMEN_SA_ELEMENTS = [ 'C', 'Si', 'Mn', 'Cr', 'Ni', 'Mo', 'V', 'Ti', 'W', 'Nb', 'B', 'Cu', 'S', 'P' ];

/** Число по-русски: 0,17 · 0,035 · 1,06 · 16,8 · 17. */
function promen_sa_num( float $x ): string {
	if ( $x >= 1 ) {
		// 1,06 · 16,8 · 17: до сотых, без хвостовых нулей.
		return rtrim( rtrim( number_format( $x, 2, ',', '' ), '0' ), ',' );
	}
	// 0,17 · 0,035 · 0,02: тысячные, но не короче сотых.
	$s = number_format( $x, 3, ',', '' );
	if ( str_ends_with( $s, '0' ) ) {
		$s = substr( $s, 0, -1 );
	}
	return $s;
}

/** Диапазон элемента: «0,17–0,24», «≤ 0,12», «≥ 0,10» или готовая запись. */
function promen_sa_range( ?array $r ): string {
	if ( ! $r ) {
		return '—';
	}
	if ( ! empty( $r[2] ) ) {
		return (string) $r[2];
	}
	if ( null !== $r[0] && null !== $r[1] ) {
		return promen_sa_num( (float) $r[0] ) . '–' . promen_sa_num( (float) $r[1] );
	}
	if ( null !== $r[1] ) {
		return '≤ ' . promen_sa_num( (float) $r[1] );
	}
	return '≥ ' . promen_sa_num( (float) $r[0] );
}

/** Форма слова после числа: 1 марка, 2 марки, 42 марки, 45 марок. */
function promen_sa_plural( int $n, string $one, string $few, string $many ): string {
	$m10  = $n % 10;
	$m100 = $n % 100;
	if ( 1 === $m10 && 11 !== $m100 ) {
		return $one;
	}
	return ( $m10 >= 2 && $m10 <= 4 && ( $m100 < 12 || $m100 > 14 ) ) ? $few : $many;
}

/** Целое с неразрывным пробелом в тысячах: 9 549. */
function promen_sa_int( int $n ): string {
	return number_format( $n, 0, ',', "\u{00A0}" );
}

/**
 * Строки сравнения химсостава: легирующие элементы — полосами на общей
 * для строки шкале; остаточные (только верхняя граница) и S, P — строкой
 * под графиком, чтобы не забивать его «≤ 0,30» у каждой марки.
 *
 * @return array{rows: array<int, array>, rest: array<int, array>}
 */
function promen_sa_chem_rows( array $ru, ?array $an ): array {
	$alloy = static fn( $r ) => $r && ( ( null !== $r[0] && $r[0] > 0 ) || ! empty( $r[2] ) );
	$rows  = [];
	$rest  = [];
	foreach ( PROMEN_SA_ELEMENTS as $el ) {
		$a = $ru[ $el ] ?? null;
		$b = $an[ $el ] ?? null;
		if ( ! $a && ! $b ) {
			continue;
		}
		if ( 'S' === $el || 'P' === $el || ! ( in_array( $el, [ 'C', 'Si', 'Mn' ], true ) || $alloy( $a ) || $alloy( $b ) ) ) {
			$rest[] = [ 'el' => $el, 'a' => $a, 'b' => $b ];
			continue;
		}
		$hi = 0.0;
		foreach ( [ $a, $b ] as $r ) {
			if ( $r ) {
				$hi = max( $hi, (float) ( $r[1] ?? ( (float) $r[0] * 1.6 ) ) );
			}
		}
		$scale = promen_sa_nice( $hi * 1.12 );
		$bar   = static function ( $r ) use ( $scale ): ?array {
			if ( ! $r ) {
				return null;
			}
			$lo = null === $r[0] ? 0.0 : (float) $r[0];
			$up = null === $r[1] ? $scale : (float) $r[1];
			return [
				'l'  => round( $lo / $scale * 100, 2 ),
				'w'  => max( 1.2, round( ( $up - $lo ) / $scale * 100, 2 ) ),
				'ol' => null === $r[0],
				'or' => null === $r[1],
			];
		};
		$diff = false;
		if ( $a && $b ) {
			$la   = (float) ( $a[0] ?? 0 );
			$ha   = null === $a[1] ? INF : (float) $a[1];
			$lb   = (float) ( $b[0] ?? 0 );
			$hb   = null === $b[1] ? INF : (float) $b[1];
			$diff = $ha < $lb || $hb < $la;
		}
		$rows[] = [ 'el' => $el, 'a' => $a, 'b' => $b, 'ba' => $bar( $a ), 'bb' => $bar( $b ), 'diff' => $diff ];
	}
	return [ 'rows' => $rows, 'rest' => $rest ];
}

/** Ближайшая «круглая» верхняя граница шкалы. */
function promen_sa_nice( float $v ): float {
	foreach ( [ 0.01, 0.02, 0.05, 0.1, 0.2, 0.25, 0.5, 1, 2, 2.5, 5, 10, 20, 25, 50 ] as $s ) {
		if ( $v <= $s ) {
			return (float) $s;
		}
	}
	return 100.0;
}

/** Метка степени соответствия: три риски + слово. */
function promen_sa_q( int $q, bool $word = true ): string {
	$labels = promen_steel_analog_q();
	$label  = $labels[ $q ]['label'] ?? '';
	$html   = '<span class="sa-q" data-q="' . $q . '" title="' . esc_attr( $labels[ $q ]['hint'] ?? '' ) . '">'
		. '<span class="sa-q-t" aria-hidden="true"><i></i><i></i><i></i></span>';
	$html  .= $word
		? '<span class="sa-q-w">' . esc_html( $label ) . '</span>'
		: '<span class="sr-only">' . esc_html( $label ) . '</span>';
	return $html . '</span>';
}

/** Иконки (обводка 1.5, currentColor). */
function promen_sa_icon( string $name ): string {
	$paths = [
		'copy'  => '<rect x="8" y="8" width="12" height="12" rx="1"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
		'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'link'  => '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/>',
	];
	return '<svg class="sa-ic" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

/** Текст для копирования аналога: «EN X6CrNiTi18-10 (1.4541)». */
function promen_sa_copy_text( string $sys, array $a ): string {
	$prefix = [ 'EN' => 'EN', 'ASTM' => '', 'AISI' => 'AISI', 'DIN' => 'DIN', 'JIS' => 'JIS', 'GB' => 'GB' ][ $sys ] ?? $sys;
	$n      = ! empty( $a['n'] ) ? ' (' . $a['n'] . ')' : '';
	return trim( $prefix . ' ' . $a['g'] ) . $n;
}

/** Ключ химсостава аналога для сравнения по умолчанию. */
function promen_sa_default_cmp( array $g ): string {
	foreach ( array_keys( promen_steel_analog_systems() ) as $sys ) {
		if ( ! empty( $g['an'][ $sys ]['chem'] ) ) {
			return (string) $g['an'][ $sys ]['chem'];
		}
	}
	return '';
}

/** Блок химсостава паспорта. */
function promen_sa_chem_html( array $g, string $cmp ): string {
	$foreign = promen_steel_foreign_chem();
	if ( ! $g['chem'] ) {
		return '<p class="sa-chem-empty">Химсостав марки задают технические условия поставщика — в подборщике его нет. Требования к составу для вашей задачи подтвердит инженер завода.</p>';
	}
	$an   = $foreign[ $cmp ] ?? null;
	$data = promen_sa_chem_rows( $g['chem'], $an ? $an['c'] : null );

	// Кнопки выбора аналога — только у тех, чей химсостав есть в данных.
	$chips = '';
	$seen  = [];
	foreach ( promen_steel_analog_systems() as $sys => $meta ) {
		$key = $g['an'][ $sys ]['chem'] ?? '';
		if ( '' === $key || isset( $seen[ $key ] ) || ! isset( $foreign[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$chips       .= '<button type="button" class="sa-chip" data-cmp="' . esc_attr( $key ) . '" aria-pressed="' . ( $key === $cmp ? 'true' : 'false' ) . '">'
			. ( str_starts_with( $foreign[ $key ]['label'], 'AISI' ) ? '' : '<span class="sa-chip-sys">' . esc_html( $sys ) . '</span>' )
			. esc_html( $foreign[ $key ]['label'] ) . '</button>';
	}

	$h  = '<div class="sa-chem-hd">';
	$h .= '<div class="sa-chem-key"><span class="sa-sw sa-sw--ru" aria-hidden="true"></span><b>' . esc_html( $g['name'] ) . '</b><span>' . esc_html( $g['std'] ) . '</span></div>';
	if ( $an ) {
		$h .= '<div class="sa-chem-key"><span class="sa-sw sa-sw--an" aria-hidden="true"></span><b>' . esc_html( $an['label'] ) . '</b><span>' . esc_html( $an['doc'] ) . '</span></div>';
	}
	if ( '' !== $chips ) {
		$h .= '<div class="sa-chips" role="group" aria-label="Сравнить с аналогом">' . $chips . '</div>';
	}
	$h .= '</div>';

	$h .= '<div class="sa-els" role="table" aria-label="Химический состав, %">';
	$h .= '<div class="sa-el sa-el--hd" role="row"><span role="columnheader"><span class="sr-only">Элемент</span></span><span role="columnheader" class="sa-el-scale">Диапазон, % массы</span><span role="columnheader" class="sa-el-v"><b>' . esc_html( $g['name'] ) . '</b>' . ( $an ? '<span>' . esc_html( $an['label'] ) . '</span>' : '' ) . '</span></div>';
	foreach ( $data['rows'] as $i => $r ) {
		$h .= '<div class="sa-el' . ( $r['diff'] ? ' is-diff' : '' ) . '" role="row" style="--i:' . (int) $i . '">';
		$h .= '<span class="sa-el-k" role="rowheader">' . esc_html( $r['el'] ) . '</span>';
		$h .= '<span class="sa-el-bars" role="cell">';
		foreach ( [ 'ba' => 'ru', 'bb' => 'an' ] as $k => $cls ) {
			if ( $r[ $k ] ) {
				$b  = $r[ $k ];
				$h .= '<span class="sa-bar sa-bar--' . $cls . ( $b['ol'] ? ' is-ol' : '' ) . ( $b['or'] ? ' is-or' : '' ) . '" style="left:' . $b['l'] . '%;width:' . $b['w'] . '%"></span>';
			} elseif ( 'bb' === $k && $an ) {
				$h .= '<span class="sa-bar sa-bar--nil"></span>';
			}
		}
		$h .= '</span>';
		$h .= '<span class="sa-el-v" role="cell"><b>' . esc_html( promen_sa_range( $r['a'] ) ) . '</b>' . ( $an ? '<span>' . esc_html( promen_sa_range( $r['b'] ) ) . '</span>' : '' ) . '</span>';
		$h .= '</div>';
	}
	$h .= '</div>';

	if ( $data['rest'] ) {
		$parts = [];
		foreach ( $data['rest'] as $r ) {
			$parts[] = '<span><b>' . esc_html( $r['el'] ) . '</b> ' . esc_html( promen_sa_range( $r['a'] ) ) . ( $an ? ' <i>|</i> ' . esc_html( promen_sa_range( $r['b'] ) ) : '' ) . '</span>';
		}
		$h .= '<p class="sa-rest"><span class="sa-lbl">Остаточные и примеси</span>' . implode( '', $parts ) . '</p>';
	}
	if ( array_filter( $data['rows'], static fn( $r ) => $r['diff'] ) ) {
		$h .= '<p class="sa-chem-diff"><span class="sa-diff-mark" aria-hidden="true"></span>Диапазоны не пересекаются — по этому элементу марки различаются по составу.</p>';
	}
	return $h;
}

/** Строка аналога в паспорте. */
function promen_sa_analog_row( string $sys, array $meta, ?array $a ): string {
	if ( ! $a ) {
		return '<li class="sa-an-row is-nil" data-sys="' . esc_attr( $sys ) . '"><span class="sa-sys">' . esc_html( $meta['label'] ) . '<small>' . esc_html( $meta['name'] ) . '</small></span><div class="sa-an-main"><span class="sa-nil">Аналога нет</span></div></li>';
	}
	$h  = '<li class="sa-an-row" data-sys="' . esc_attr( $sys ) . '">';
	$h .= '<span class="sa-sys">' . esc_html( $meta['label'] ) . '<small>' . esc_html( $meta['name'] ) . '</small></span>';
	$h .= '<div class="sa-an-main">';
	$h .= '<div class="sa-an-g"><b>' . esc_html( $a['g'] ) . '</b>' . ( ! empty( $a['n'] ) ? '<span class="sa-num">' . esc_html( $a['n'] ) . '</span>' : '' ) . '</div>';
	if ( ! empty( $a['forms'] ) ) {
		$h .= '<ul class="sa-forms">';
		foreach ( $a['forms'] as $f ) {
			$h .= '<li><span>' . esc_html( $f[0] ) . '</span><b>' . esc_html( $f[1] ) . '</b></li>';
		}
		$h .= '</ul>';
	} elseif ( ! empty( $a['doc'] ) ) {
		$h .= '<div class="sa-an-doc">' . esc_html( $a['doc'] ) . '</div>';
	}
	if ( ! empty( $a['note'] ) ) {
		$h .= '<p class="sa-note">' . esc_html( $a['note'] ) . '</p>';
	}
	if ( ! empty( $a['alt'] ) ) {
		$h .= '<p class="sa-alt"><span>Также</span> ' . esc_html( $a['alt'] ) . '</p>';
	}
	$h .= '</div>';
	$h .= promen_sa_q( (int) $a['q'] );
	$h .= '<button type="button" class="sa-copy" data-copy="' . esc_attr( promen_sa_copy_text( $sys, $a ) ) . '" aria-label="' . esc_attr( 'Скопировать ' . promen_sa_copy_text( $sys, $a ) ) . '">' . promen_sa_icon( 'copy' ) . '</button>';
	return $h . '</li>';
}

/** Паспорт марки целиком; $example — первый показ без запроса, с подсказкой. */
function promen_sa_passport( array $g, bool $example = false ): void {
	$groups  = promen_steel_analog_groups();
	$systems = promen_steel_analog_systems();
	$cmp     = promen_sa_default_cmp( $g );
	?>
	<?php if ( $example ) : ?>
		<div class="sa-ctx" data-ctx><span><span class="sa-lbl">Пример</span> Так выглядит паспорт марки. Введите свою марку в поле выше или выберите из частых.</span></div>
	<?php else : ?>
		<div class="sa-ctx" data-ctx hidden></div>
	<?php endif; ?>
	<div class="sa-side">
		<div class="sa-id">
			<div class="sa-id-top"><span class="sa-lbl">Марка по ГОСТ</span><span class="sa-grp"><?php echo esc_html( $groups[ $g['g'] ] ?? '' ); ?></span></div>
			<h2 class="sa-name"><?php echo esc_html( $g['name'] ); ?></h2>
			<?php if ( $g['desc'] ) : ?><p class="sa-desc"><?php echo esc_html( $g['desc'] ); ?></p><?php endif; ?>
			<dl class="sa-facts">
				<?php if ( $g['std'] ) : ?><div><dt>Норматив</dt><dd><?php echo esc_html( $g['std'] ); ?></dd></div><?php endif; ?>
				<?php if ( $g['temp'] ) : ?><div><dt>Температура среды</dt><dd><?php echo esc_html( $g['temp'] ); ?></dd></div><?php endif; ?>
				<?php if ( $g['mech'] ) : ?><div><dt class="sa-nc">σт · σв, МПа · δ, %</dt><dd><?php echo esc_html( $g['mech']['st'] . ' · ' . $g['mech']['sv'] . ' · ' . $g['mech']['delta'] ); ?></dd></div><?php endif; ?>
				<?php if ( $g['apps'] ) : ?><div><dt>Отрасли</dt><dd><?php echo esc_html( implode( ' · ', $g['apps'] ) ); ?></dd></div><?php endif; ?>
			</dl>
		</div>
		<div class="sa-buy">
			<?php promen_sa_catalog_block( $g ); ?>
			<div class="sa-acts">
				<button type="button" class="clc-btn" data-act="kp">Запросить КП <?php echo promen_sa_icon( 'arrow' ); // phpcs:ignore ?></button>
				<button type="button" class="clc-btn clc-btn--ghost" data-act="link"><?php echo promen_sa_icon( 'link' ); // phpcs:ignore ?> Ссылка на марку</button>
			</div>
		</div>
	</div>
	<div class="sa-an">
			<div class="sa-an-hd">
				<span class="sa-lbl">Аналоги по системам</span>
				<a class="sa-legend" href="#stepen"><?php echo promen_sa_q( 3, false ); // phpcs:ignore ?>прямой<?php echo promen_sa_q( 2, false ); // phpcs:ignore ?>близкий<?php echo promen_sa_q( 1, false ); // phpcs:ignore ?>условный</a>
			</div>
			<?php if ( $g['none'] ) : ?>
				<p class="sa-none"><?php echo esc_html( $g['none'] ); ?></p>
			<?php else : ?>
				<ol class="sa-an-list">
					<?php
					foreach ( $systems as $sys => $meta ) {
						echo promen_sa_analog_row( $sys, $meta, $g['an'][ $sys ] ?? null ); // phpcs:ignore
					}
					?>
				</ol>
			<?php endif; ?>
	</div>
	<section class="sa-chem" data-chem aria-label="Химический состав">
		<?php echo promen_sa_chem_html( $g, $cmp ); // phpcs:ignore ?>
	</section>
	<?php
}

/** Блок «в каталоге завода» или «под заказ». */
function promen_sa_catalog_block( array $g ): void {
	$links = $g['links'];
	if ( ! $links ) {
		?>
		<div class="sa-cat is-off">
			<span class="sa-lbl">В каталоге завода</span>
			<p>Серийных позиций из <?php echo esc_html( $g['name'] ); ?> в каталоге нет. Детали по чертежу из этой марки — по запросу: инженер ответит, возьмёт ли завод заказ и в какой срок.</p>
		</div>
		<?php
		return;
	}
	$main = $links[0];
	?>
	<div class="sa-cat">
		<div class="sa-cat-hd">
			<span class="sa-lbl">В каталоге завода</span>
			<a class="sa-cat-all" href="<?php echo esc_url( $main['url'] ); ?>"><?php echo esc_html( promen_sa_int( $main['n'] ) . ' ' . promen_sa_plural( $main['n'], 'позиция', 'позиции', 'позиций' ) ); ?> <?php echo promen_sa_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<ul class="sa-cat-list">
			<?php foreach ( array_slice( $main['by'], 0, 8 ) as $b ) : ?>
				<li><a href="<?php echo esc_url( $b['url'] ); ?>"><?php echo esc_html( $b['label'] ); ?><b><?php echo esc_html( promen_sa_int( $b['n'] ) ); ?></b></a></li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( array_slice( $links, 1 ) as $l ) : ?>
			<a class="sa-cat-also" href="<?php echo esc_url( $l['url'] ); ?>">Отдельно в каталоге: <?php echo esc_html( $l['label'] ); ?> — <?php echo esc_html( promen_sa_int( $l['n'] ) ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}

/** Полная таблица соответствия. */
function promen_sa_table( array $grades ): void {
	$systems = promen_steel_analog_systems();
	$groups  = promen_steel_analog_groups();
	$page    = get_permalink();
	?>
	<table class="sa-tbl">
		<thead>
			<tr>
				<th scope="col">ГОСТ</th>
				<?php foreach ( $systems as $meta ) : ?><th scope="col"><?php echo esc_html( $meta['label'] ); ?></th><?php endforeach; ?>
			</tr>
		</thead>
		<?php foreach ( $groups as $gk => $glabel ) : ?>
			<?php $rows = array_filter( $grades, static fn( $g ) => $g['g'] === $gk ); ?>
			<?php if ( ! $rows ) { continue; } ?>
			<tbody data-g="<?php echo esc_attr( $gk ); ?>">
				<tr class="sa-tbl-grp"><th colspan="<?php echo count( $systems ) + 1; ?>" scope="rowgroup"><?php echo esc_html( $glabel ); ?> <span><?php echo count( $rows ); ?></span></th></tr>
				<?php foreach ( $rows as $g ) : ?>
					<tr data-id="<?php echo esc_attr( $g['id'] ); ?>">
						<th scope="row"><a href="<?php echo esc_url( add_query_arg( 'marka', $g['id'], $page ) . '#podbor' ); ?>"><?php echo esc_html( $g['name'] ); ?></a></th>
						<?php foreach ( $systems as $sys => $meta ) : ?>
							<?php $a = $g['an'][ $sys ] ?? null; ?>
							<?php if ( $a ) : ?>
								<td><?php echo promen_sa_q( (int) $a['q'], false ); // phpcs:ignore ?><span class="sa-td-g"><?php echo esc_html( $a['g'] ); ?></span><?php echo ( 'EN' === $sys && ! empty( $a['n'] ) ) ? '<span class="sa-td-n">' . esc_html( $a['n'] ) . '</span>' : ''; ?></td>
							<?php else : ?>
								<td class="is-nil">—</td>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		<?php endforeach; ?>
	</table>
	<?php
}
