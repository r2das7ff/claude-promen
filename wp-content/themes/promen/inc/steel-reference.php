<?php
/**
 * Справочник марок стали и динамический рендер секции «Марки стали и материалы».
 * Раньше таблица марок была захардкожена в каждом taxonomy-шаблоне и на карточке
 * (5 фиксированных марок) — не отражала фактический ассортимент. Теперь рендерим
 * по РЕАЛЬНЫМ маркам категории/товара из этого справочника.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Нормализация имени марки к ключу справочника (ст.20/Ст20 → 20 и т.п.).
 */
function promen_steel_key( string $name ): string {
	$raw = trim( $name );
	$n   = str_replace( 'X', 'Х', $raw ); // латинский X → кириллический Х (08X18…)
	// Только явные алиасы — Ст3/Ст3сп НЕ трогаем (это отдельные марки, не «3»).
	$aliases = [
		'Ст20' => '20', 'ст20' => '20', 'ст.20' => '20', 'Ст.20' => '20', 'Сталь 20' => '20', 'сталь 20' => '20',
		'20ХЗМВФ' => '20Х3МВФ', '08X18Н1' => '08Х18Н10Т', '08Х18НЮТ' => '08Х18Н10Т',
	];
	return $aliases[ $raw ] ?? ( $aliases[ $n ] ?? $n );
}

/**
 * Справочник: марка → [desc, temp, pn, std, apps[], mech{sv,st,delta,weld}].
 * Значения — по ГОСТ (1050, 19281/19282, 4543, 20072, 20700, 5632, 380, 14959).
 *
 * @return array<string, array<string, mixed>>
 */
function promen_steel_reference(): array {
	static $ref = null;
	if ( $ref !== null ) {
		return $ref;
	}
	$ref = [
		'Ст3' => [ 'desc' => 'Углеродистая обыкновенного качества. Крепёж и детали общего назначения.', 'temp' => 'до +425°C', 'pn' => '16', 'std' => 'ГОСТ 380-2005', 'apps' => [ 'Крепёж', 'Пром' ], 'mech' => [ 'sv' => '≥ 370', 'st' => '≥ 245', 'delta' => '≥ 26', 'weld' => 'Отличная' ] ],
		'Ст3сп' => [ 'desc' => 'Углеродистая обыкновенного качества, спокойной плавки. Общее назначение, опоры.', 'temp' => 'до +425°C', 'pn' => '16', 'std' => 'ГОСТ 380-2005', 'apps' => [ 'ЖКХ', 'Пром' ], 'mech' => [ 'sv' => '≥ 370', 'st' => '≥ 245', 'delta' => '≥ 26', 'weld' => 'Отличная' ] ],
		'ВСт3сп' => [ 'desc' => 'Углеродистая общего назначения, спокойной плавки.', 'temp' => 'до +425°C', 'pn' => '16', 'std' => 'ГОСТ 380-2005', 'apps' => [ 'ЖКХ', 'Пром' ], 'mech' => [ 'sv' => '≥ 370', 'st' => '≥ 245', 'delta' => '≥ 26', 'weld' => 'Отличная' ] ],
		'20' => [ 'desc' => 'Углеродистая конструкционная. Основной материал стандартных трубопроводов ТЭС/ЖКХ. Хорошая свариваемость.', 'temp' => 'до +425°C', 'pn' => '160', 'std' => 'ГОСТ 1050-2013 / 8731-87', 'apps' => [ 'ТЭС', 'ЖКХ', 'Нефтегаз' ], 'mech' => [ 'sv' => '≥ 410', 'st' => '≥ 245', 'delta' => '≥ 25', 'weld' => 'Отличная' ] ],
		'10' => [ 'desc' => 'Углеродистая конструкционная низкой прочности, высокой пластичности.', 'temp' => 'до +425°C', 'pn' => '100', 'std' => 'ГОСТ 1050-2013', 'apps' => [ 'ЖКХ', 'Пром' ], 'mech' => [ 'sv' => '≥ 340', 'st' => '≥ 205', 'delta' => '≥ 31', 'weld' => 'Отличная' ] ],
		'09Г2С' => [ 'desc' => 'Низколегированная для низких температур, ударная вязкость до −70°C. Нефтегаз, север, сосуды под давлением.', 'temp' => '−70…+475°C', 'pn' => '160', 'std' => 'ГОСТ 19281-2014', 'apps' => [ 'АЭС', 'ТЭС', 'Нефтегаз' ], 'mech' => [ 'sv' => '≥ 470', 'st' => '≥ 265', 'delta' => '≥ 21', 'weld' => 'Хорошая' ] ],
		'17Г1С' => [ 'desc' => 'Низколегированная для магистральных трубопроводов повышенного давления.', 'temp' => 'до +475°C', 'pn' => '100', 'std' => 'ГОСТ 19281-2014', 'apps' => [ 'ТЭС', 'Нефтегаз' ], 'mech' => [ 'sv' => '≥ 490', 'st' => '≥ 345', 'delta' => '≥ 23', 'weld' => 'Хорошая' ] ],
		'17Г1С-У' => [ 'desc' => 'Улучшенная 17Г1С для магистральных газонефтепроводов.', 'temp' => 'до +475°C', 'pn' => '100', 'std' => 'ГОСТ 19281-2014 / ТУ', 'apps' => [ 'Нефтегаз' ] ],
		'17ГС' => [ 'desc' => 'Низколегированная для трубопроводов и сосудов под давлением.', 'temp' => 'до +475°C', 'pn' => '100', 'std' => 'ГОСТ 19281-2014', 'apps' => [ 'ТЭС', 'Нефтегаз' ] ],
		'16ГС' => [ 'desc' => 'Низколегированная для сосудов и аппаратов под давлением.', 'temp' => 'до +475°C', 'pn' => '100', 'std' => 'ГОСТ 19281-2014', 'apps' => [ 'ТЭС', 'Нефтегаз' ] ],
		'15ГС' => [ 'desc' => 'Для трубопроводов повышенного давления и котельных установок.', 'temp' => 'до +500°C', 'pn' => '100', 'std' => 'ГОСТ 19282-73', 'apps' => [ 'ТЭС', 'ГРЭС' ] ],
		'10Г2' => [ 'desc' => 'Низколегированная марганцовистая для низких температур.', 'temp' => '−70…+450°C', 'pn' => '160', 'std' => 'ГОСТ 4543-2016', 'apps' => [ 'Нефтегаз', 'ЖКХ' ] ],
		'10Г2С1' => [ 'desc' => 'Низколегированная кремнемарганцовистая, хладостойкая.', 'temp' => '−70…+475°C', 'pn' => '160', 'std' => 'ГОСТ 19281-2014', 'apps' => [ 'Нефтегаз', 'ЖКХ' ] ],
		'13ХФА' => [ 'desc' => 'Коррозионностойкая низколегированная для нефтепромысловых трубопроводов (H2S/CO2).', 'temp' => 'до +200°C', 'pn' => '160', 'std' => 'ТУ 14-3Р', 'apps' => [ 'Нефтегаз' ] ],
		'35' => [ 'desc' => 'Углеродистая качественная для крепежа и точёных деталей.', 'temp' => 'до +400°C', 'pn' => '—', 'std' => 'ГОСТ 1050-2013', 'apps' => [ 'Крепёж', 'Пром' ] ],
		'45' => [ 'desc' => 'Углеродистая качественная повышенной прочности.', 'temp' => 'до +400°C', 'pn' => '—', 'std' => 'ГОСТ 1050-2013', 'apps' => [ 'Крепёж', 'Пром' ] ],
		'40Х' => [ 'desc' => 'Легированная хромистая для высокопрочного крепежа (кл. 8.8–10.9).', 'temp' => 'до +425°C', 'pn' => '—', 'std' => 'ГОСТ 4543-2016', 'apps' => [ 'Крепёж' ] ],
		'30ХМА' => [ 'desc' => 'Хромомолибденовая для тепло­нагруженного крепежа.', 'temp' => 'до +510°C', 'pn' => '—', 'std' => 'ГОСТ 4543-2016', 'apps' => [ 'ТЭС', 'Крепёж' ] ],
		'35Х' => [ 'desc' => 'Легированная хромистая конструкционная.', 'temp' => 'до +425°C', 'pn' => '—', 'std' => 'ГОСТ 4543-2016', 'apps' => [ 'Крепёж' ] ],
		'25Х1МФ' => [ 'desc' => 'Теплоустойчивая для фланцевого крепежа паропроводов (ЭИ10).', 'temp' => 'до +510°C', 'pn' => '—', 'std' => 'ГОСТ 20700-75', 'apps' => [ 'ТЭС', 'Котлы' ] ],
		'25Х2М1Ф' => [ 'desc' => 'Теплоустойчивая для крепежа высоких параметров (ЭИ723).', 'temp' => 'до +560°C', 'pn' => '—', 'std' => 'ГОСТ 20700-75', 'apps' => [ 'ТЭС', 'Котлы' ] ],
		'20Х1М1Ф1ТР' => [ 'desc' => 'Теплоустойчивая для крепежа сверхвысоких параметров (ЭП182).', 'temp' => 'до +570°C', 'pn' => '—', 'std' => 'ГОСТ 20700-75', 'apps' => [ 'ТЭС', 'Котлы' ] ],
		'12Х1МФ' => [ 'desc' => 'Теплоустойчивая хромомолибденованадиевая для паропроводов.', 'temp' => 'до +585°C', 'pn' => '100', 'std' => 'ГОСТ 20072-74', 'apps' => [ 'ТЭС', 'Котлы' ], 'mech' => [ 'sv' => '≥ 440', 'st' => '≥ 255', 'delta' => '≥ 21', 'weld' => 'Требует ТО' ] ],
		'15Х1М1Ф' => [ 'desc' => 'Теплоустойчивая для паропроводов высокого давления.', 'temp' => 'до +575°C', 'pn' => '100', 'std' => 'ГОСТ 20072-74', 'apps' => [ 'ТЭС', 'Котлы' ] ],
		'12ХМ' => [ 'desc' => 'Теплоустойчивая хромомолибденовая.', 'temp' => 'до +540°C', 'pn' => '100', 'std' => 'ГОСТ 20072-74', 'apps' => [ 'ТЭС' ] ],
		'15ХМ' => [ 'desc' => 'Теплоустойчивая хромомолибденовая для котлов и паропроводов.', 'temp' => 'до +560°C', 'pn' => '100', 'std' => 'ГОСТ 20072-74', 'apps' => [ 'ТЭС', 'Котлы' ] ],
		'20Х3МВФ' => [ 'desc' => 'Теплоустойчивая хромомолибденованадиевая (ЭИ415), высокое давление.', 'temp' => 'до +560°C', 'pn' => '100', 'std' => 'ГОСТ 20072-74', 'apps' => [ 'ТЭС', 'Котлы' ] ],
		'15Х5М' => [ 'desc' => 'Жаропрочная хромистая для нефтехимической аппаратуры.', 'temp' => 'до +650°C', 'pn' => '63', 'std' => 'ГОСТ 20072-74', 'apps' => [ 'Нефтехим' ] ],
		'08Х18Н10Т' => [ 'desc' => 'Коррозионностойкая аустенитная нержавеющая, стабилизированная титаном.', 'temp' => 'до +600°C', 'pn' => '63', 'std' => 'ГОСТ 5632-2014', 'apps' => [ 'АЭС', 'Хим' ], 'mech' => [ 'sv' => '≥ 510', 'st' => '≥ 196', 'delta' => '≥ 40', 'weld' => 'Хорошая' ] ],
		'12Х18Н10Т' => [ 'desc' => 'Коррозионностойкая аустенитная нержавеющая. Агрессивные среды, АЭС, химия.', 'temp' => 'до +600°C', 'pn' => '63', 'std' => 'ГОСТ 5632-2014', 'apps' => [ 'АЭС', 'Хим' ], 'mech' => [ 'sv' => '≥ 510', 'st' => '≥ 196', 'delta' => '≥ 40', 'weld' => 'Хорошая' ] ],
		'12Х18Н12Т' => [ 'desc' => 'Коррозионностойкая аустенитная нержавеющая.', 'temp' => 'до +600°C', 'pn' => '63', 'std' => 'ГОСТ 5632-2014', 'apps' => [ 'АЭС', 'Хим' ] ],
		'08Х18Н12Т' => [ 'desc' => 'Коррозионностойкая аустенитная нержавеющая, низкоуглеродистая.', 'temp' => 'до +600°C', 'pn' => '63', 'std' => 'ГОСТ 5632-2014', 'apps' => [ 'АЭС', 'Хим' ] ],
		'10Х17Н13М2Т' => [ 'desc' => 'Кислотостойкая аустенитная нержавеющая с молибденом.', 'temp' => 'до +600°C', 'pn' => '63', 'std' => 'ГОСТ 5632-2014', 'apps' => [ 'Хим', 'Нефтехим' ] ],
		'30Х13' => [ 'desc' => 'Коррозионностойкая мартенситная (пружинные шайбы, метизы).', 'temp' => 'до +450°C', 'pn' => '—', 'std' => 'ГОСТ 5632-2014', 'apps' => [ 'Крепёж' ] ],
		'65Г' => [ 'desc' => 'Пружинная сталь для пружинных шайб.', 'temp' => 'до +200°C', 'pn' => '—', 'std' => 'ГОСТ 14959-2016', 'apps' => [ 'Крепёж' ] ],
		'08кп' => [ 'desc' => 'Низкоуглеродистая для плоских шайб.', 'temp' => 'до +300°C', 'pn' => '—', 'std' => 'ГОСТ 1050-2013', 'apps' => [ 'Крепёж' ] ],
	];
	return $ref;
}

/**
 * Список фактических марок категории (union по каталогу).
 *
 * @return string[]
 */
function promen_group_steel_list( string $group ): array {
	// Скан steels_json всей группы (до ~7k строк у крепежа) — только по кэш-промаху.
	$ckey   = function_exists( 'promen_filters_cache_key' )
		? promen_filters_cache_key( 'group_steel_list', [ $group ] )
		: 'promen_group_steels_' . md5( $group );
	$cached = get_transient( $ckey );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$table = function_exists( 'promen_catalog_table_name' ) ? promen_catalog_table_name() : $wpdb->prefix . 'promen_catalog_rows';
	$slugs = function_exists( 'promen_catalog_group_slugs' ) ? promen_catalog_group_slugs( $group ) : [ $group ];
	if ( ! $slugs ) {
		return [];
	}
	$ph   = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );
	// phpcs:ignore WordPress.DB.PreparedSQL
	$rows = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT steels_json FROM {$table} WHERE category IN ({$ph}) AND steels_json IS NOT NULL", $slugs ) );
	$seen = [];
	foreach ( $rows as $json ) {
		foreach ( (array) json_decode( (string) $json, true ) as $slug ) {
			$name = function_exists( 'promen_term_label' )
				? promen_term_label( 'pa_steel', (string) $slug )
				: (string) $slug;
			$seen[ promen_steel_key( $name ) ] = true;
		}
	}
	$out = promen_sort_steels( array_keys( $seen ) );
	set_transient( $ckey, $out, 15 * MINUTE_IN_SECONDS );
	return $out;
}

/** Порядок марок: сначала по справочнику (углерод→низколег→теплоуст→нерж), потом прочие. */
function promen_sort_steels( array $steels ): array {
	$order = array_keys( promen_steel_reference() );
	$rank  = array_flip( $order );
	usort( $steels, fn( $a, $b ) => ( $rank[ $a ] ?? 999 ) <=> ( $rank[ $b ] ?? 999 ) ?: strcmp( $a, $b ) );
	return $steels;
}

/**
 * Рендер строк таблицы «Марки стали и материалы» по фактическим маркам.
 * Формат 1:1 с существующей разметкой (mat-r + mat-expand), но динамический.
 *
 * @param string[] $steels имена марок (как в pa_steel / dims).
 * @return int сколько строк отрисовано (после нормализации и дедупа —
 *             входной список может содержать одну марку в разных написаниях).
 */
function promen_render_materials_rows( array $steels, bool $expandable = true ): int {
	$ref  = promen_steel_reference();
	$keys = [];
	foreach ( $steels as $s ) {
		$k = promen_steel_key( (string) $s );
		if ( $k !== '' ) {
			$keys[ $k ] = true;
		}
	}
	$keys = promen_sort_steels( array_keys( $keys ) );
	if ( ! $keys ) {
		echo '<div class="mat-r"><div class="mr-desc" style="opacity:.65;">Марки уточняются под заказ.</div></div>';
		return 0;
	}
	// Метка «сверх телефонного порога»: на десктопе таблица марок — обзор,
	// на 390px её 21 строка занимает больше полутора тысяч пикселей. Строки
	// сверх шестой прячет category-sdt.css, разворот — кнопкой из секции 05.
	$phone_visible = 6;
	$row_i         = 0;
	foreach ( $keys as $grade ) {
		$d    = $ref[ $grade ] ?? [];
		$desc = $d['desc'] ?? 'Марка по нормативу изделия; характеристики — в паспорте поставки.';
		$temp = $d['temp'] ?? '—';
		$pn   = $d['pn'] ?? '—';
		$std  = $d['std'] ?? '';
		$apps = (array) ( $d['apps'] ?? [] );
		$mech = $expandable ? ( $d['mech'] ?? null ) : null;
		$xtra = $row_i >= $phone_visible ? ' mr-extra-m' : '';
		$row_i++;
		// Канонические 6 колонок: Марка | Описание | Темп | PN | Отрасль | ГОСТ.
		echo '<div class="mat-r' . $xtra . '" data-grade="' . esc_attr( $grade ) . '"' . ( $mech ? ' onclick="toggleMat(this)"' : '' ) . '>';
		echo '<div class="mr-g">' . esc_html( $grade ) . '</div>';
		echo '<div class="mr-desc">' . esc_html( $desc ) . '</div>';
		echo '<div class="mr-temp">' . esc_html( $temp ) . '</div>';
		echo '<div class="mr-pn">' . esc_html( $pn !== '—' ? $pn . ' МПа' : '—' ) . '</div>';
		echo '<div class="mr-apps">';
		foreach ( $apps as $i => $a ) {
			echo '<span class="mr-app-t' . ( $i === 0 ? ' hi' : '' ) . '">' . esc_html( $a ) . '</span>';
		}
		echo '</div>';
		echo '<div class="mr-std">' . esc_html( $std !== '' ? $std : '—' ) . '</div>';
		echo '</div>';
		if ( $mech ) {
			echo '<div class="mat-expand' . $xtra . '"><div class="me-grid">'
				. '<div class="me-item"><div class="me-k">σв, МПа</div><div class="me-v">' . esc_html( $mech['sv'] ) . '</div></div>'
				. '<div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">' . esc_html( $mech['st'] ) . '</div></div>'
				. '<div class="me-item"><div class="me-k">δ, %</div><div class="me-v">' . esc_html( $mech['delta'] ) . '</div></div>'
				. '<div class="me-item"><div class="me-k">Темп. max</div><div class="me-v">' . esc_html( $temp ) . '</div></div>'
				. '<div class="me-item"><div class="me-k">Свариваемость</div><div class="me-v">' . esc_html( $mech['weld'] ) . '</div></div>'
				. '</div></div>';
		}
	}
	return count( $keys );
}

/**
 * Полная секция 05 «Марки стали и материалы» для страницы категории —
 * динамически по фактическим маркам категории. Заменяет захардкоженные таблицы.
 */
function promen_render_materials_section( string $group ): void {
	$steels = promen_group_steel_list( $group );
	?>
<section class="s s-alt" id="s05">
    <div class="s-hd">
      <h2 class="s-badge"><span class="s-badge-num">05</span>Марки стали и материалы</h2>
      <div class="s-meta">STEEL GRADES</div>
    </div>
    <div class="s-body">
      <div class="mat-tbl-wrap reveal" data-mat-tbl>
        <div class="mat-tbl-hd"><span>Марка</span><span>Описание</span><span>Темп. среды</span><span>PN макс</span><span>Отрасль</span><span>ГОСТ / ТУ</span></div>
        <?php $mat_rows = promen_render_materials_rows( $steels, true ); ?>
      </div>
      <?php if ( $mat_rows > 6 ) : ?>
        <?php // Кнопка нужна только телефону — на десктопе таблица не урезана. ?>
        <?php // «все N марок» спотыкается на числах на -1 («все 21 марку»), поэтому число отдельно. ?>
        <button type="button" class="norm-more-btn norm-more-btn--m" data-mat-more>
          Все марки стали · <?php echo esc_html( number_format_i18n( $mat_rows ) ); ?>
        </button>
      <?php endif; ?>
    </div>
  </section>
	<?php
}

/**
 * Таблица марок стали для статьи: марка, температура, PN, норматив, применение.
 *
 * Собирается из promen_steel_reference(), а не пишется руками в шаблоне —
 * иначе статья разойдётся с каталогом. Проверка 2026-08-28 нашла ровно это:
 * в тексте стояло «09Г2С, Ст20 — до +450 °C», тогда как в справочнике
 * 09Г2С это −70…+475 °C, а Ст20 — до +425 °C.
 *
 * @param string $group Группа каталога (sdt, flange, fastener, …).
 */
function promen_steel_table( string $group ): void {
	$ref    = promen_steel_reference();
	$grades = promen_group_steel_list( $group );
	if ( ! $grades ) {
		return;
	}
	echo '<div class="ar-tbl-wrap"><table class="ar-tbl">';
	echo '<thead><tr><th>Марка</th><th>Рабочая температура</th><th>PN, не более</th><th>Норматив</th><th>Применение</th></tr></thead><tbody>';
	foreach ( $grades as $name ) {
		$row = $ref[ promen_steel_key( $name ) ] ?? ( $ref[ $name ] ?? null );
		if ( ! $row ) {
			continue;
		}
		printf(
			'<tr><td class="m">%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
			esc_html( $name ),
			esc_html( $row['temp'] ),
			esc_html( '—' === $row['pn'] || '' === $row['pn'] ? '—' : $row['pn'] ),
			esc_html( $row['std'] ),
			esc_html( implode( ', ', $row['apps'] ) )
		);
	}
	echo '</tbody></table></div>';
}

/**
 * Таблица нормативов: документ, изделие, диапазон размеров, позиций в каталоге.
 *
 * Данные живые — из таксономии norm и товаров под ней, поэтому таблица в
 * статье не может разойтись с каталогом. Диапазон и тип изделия считает
 * promen_norm_summary() (см. inc/seo.php), результат там же кэшируется.
 *
 * @param string[] $slugs Слаги нормативов в нужном порядке.
 */
function promen_norm_table( array $slugs ): void {
	$rows = [];
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'norm' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		$summary = function_exists( 'promen_norm_summary' ) ? promen_norm_summary( $term ) : '';
		// «— отводы DN 20–800» → «отводы» + «DN 20–800»
		$kind = $size = '';
		if ( preg_match( '/^\s*—\s*([^D]+?)(?:\s+(DN|M)\s*(.+))?$/u', $summary, $m ) ) {
			$kind = trim( $m[1] );
			$size = isset( $m[2] ) ? trim( $m[2] . ' ' . ( $m[3] ?? '' ) ) : '';
		}
		$rows[] = [
			'name'  => $term->name,
			'url'   => get_term_link( $term ),
			'kind'  => $kind,
			'size'  => $size,
			'count' => (int) $term->count,
		];
	}
	if ( ! $rows ) {
		return;
	}
	echo '<div class="ar-tbl-wrap"><table class="ar-tbl">';
	echo '<thead><tr><th>Норматив</th><th>Изделие</th><th>Типоразмеры</th><th>Позиций</th></tr></thead><tbody>';
	foreach ( $rows as $r ) {
		printf(
			'<tr><td class="m">%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
			is_wp_error( $r['url'] )
				? esc_html( $r['name'] )
				: '<a href="' . esc_url( $r['url'] ) . '">' . esc_html( $r['name'] ) . '</a>',
			esc_html( $r['kind'] ?: '—' ),
			esc_html( $r['size'] ?: '—' ),
			esc_html( number_format_i18n( $r['count'] ) )
		);
	}
	echo '</tbody></table></div>';
}
