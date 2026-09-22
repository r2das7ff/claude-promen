<?php
/**
 * Schema registry: колонки реестра, фасеты и сортировка по группе каталога.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Базовые колонки реестра (ключ => определение).
 *
 * label — простой текст, html — вариант с индексом для шапки: она набрана
 * заглавными (CSS .tbl-hd), и «Dн» превращалось в «DН», которое читается
 * как латинское «DH». Индекс живёт в span.th-ix, где uppercase отключён.
 * hint — расшифровка колонки в подсказке при наведении.
 */
function promen_catalog_column_defs(): array {
	return [
		'dn'          => [ 'key' => 'dn', 'label' => 'DN', 'w' => '52px', 'hint' => 'Условный проход — номер из стандартного ряда, примерно равен внутреннему диаметру' ],
		'd'           => [ 'key' => 'd', 'label' => 'Dн, мм', 'html' => 'D<span class="th-ix">н</span>, мм', 'w' => '64px', 'hint' => 'Наружный диаметр трубы, мм' ],
		's'           => [ 'key' => 's', 'label' => 's, мм', 'w' => '54px', 'hint' => 'Толщина стенки, мм' ],
		'angle'       => [ 'key' => 'angle', 'label' => 'Угол', 'w' => '54px', 'hint' => 'Угол поворота, градусы' ],
		'radius'      => [ 'key' => 'radius', 'label' => 'R, мм', 'w' => '56px', 'hint' => 'Радиус гиба, мм' ],
		'height'      => [ 'key' => 'height', 'label' => 'H, мм', 'w' => '56px', 'hint' => 'Высота, мм' ],
		'mass'        => [ 'key' => 'mass', 'label' => 'Масса, кг', 'w' => '78px', 'hint' => 'Масса одного изделия, кг' ],
		// 100px, не 90: подпись «Масса, кг/м» + стрелка сортировки на 11px шапки
		// упиралась в край колонки и срывалась в перенос.
		'massm'       => [ 'key' => 'mass', 'label' => 'Масса, кг/м', 'w' => '100px', 'hint' => 'Масса погонного метра, кг/м' ],
		'pn'          => [ 'key' => 'pn', 'label' => 'PN', 'w' => '52px', 'hint' => 'Условное давление, МПа' ],
		'flange_type' => [ 'key' => 'flange_type', 'label' => 'Тип', 'w' => '124px', 'hint' => 'Тип фланца по нормативу' ],
		'b'           => [ 'key' => 'b', 'label' => 'b, мм', 'w' => '54px', 'hint' => 'Толщина фланца, мм' ],
		'dbolt'       => [ 'key' => 'dbolt', 'label' => 'Dб, мм', 'html' => 'D<span class="th-ix">б</span>, мм', 'w' => '62px', 'hint' => 'Диаметр окружности болтов, мм' ],
		'bolts'       => [ 'key' => 'bolts', 'label' => 'Болты', 'w' => '76px', 'hint' => 'Количество и резьба болтов' ],
		'exec'        => [ 'key' => 'exec', 'label' => 'Исп.', 'w' => '52px', 'hint' => 'Исполнение по нормативу' ],
		'dn2'         => [ 'key' => 'dn2', 'label' => 'DN2', 'w' => '52px', 'hint' => 'Условный проход ответвления (у переходов — меньшего конца)' ],
		'd2'          => [ 'key' => 'd2', 'label' => 'Dн2, мм', 'html' => 'D<span class="th-ix">н2</span>, мм', 'w' => '62px', 'hint' => 'Наружный диаметр ответвления (у переходов — меньшего конца), мм' ],
		's2'          => [ 'key' => 's2', 'label' => 's2, мм', 'w' => '54px', 'hint' => 'Толщина стенки ответвления (у переходов — меньшего конца), мм' ],
		'thread'      => [ 'key' => 'thread', 'label' => 'M', 'w' => '58px', 'hint' => 'Метрическая резьба' ],
		'length'      => [ 'key' => 'length', 'label' => 'L, мм', 'w' => '64px', 'hint' => 'Длина, мм' ],
		'strength'    => [ 'key' => 'strength', 'label' => 'Класс', 'w' => '64px', 'hint' => 'Класс прочности' ],
	];
}

/**
 * Конфигурация группы: columns, facets, ranges, default_sort.
 *
 * @return array{columns: string[], facets: string[], ranges: string[], sort: array{field: string, dir: string}}
 */
function promen_catalog_group_schema( string $group ): array {
	$g = (string) $group;
	$is_fast = function_exists( 'promen_is_fastener_group' ) && promen_is_fastener_group( $g );

	if ( $is_fast ) {
		return [
			'columns' => [ 'thread', 'length', 'strength' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		];
	}
	if ( str_starts_with( $g, 'flancy' ) ) {
		return [
			'columns' => [ 'dn', 'pn', 'flange_type', 'd', 'dbolt', 'bolts', 'b', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'gost', 'pn' ],
			'ranges'  => [ 'dn', 'pn' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		];
	}
	if ( str_starts_with( $g, 'truby' ) ) {
		return [
			'columns' => [ 'dn', 'd', 's', 'massm' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn', 's' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		];
	}
	if ( str_starts_with( $g, 'izolyatsiya' ) ) {
		return [
			'columns' => [ 'dn', 'd', 's' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn', 's' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		];
	}
	if ( str_starts_with( $g, 'opory' ) ) {
		return [
			'columns' => [ 'dn', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		];
	}
	if ( str_starts_with( $g, 'armatura' ) ) {
		return [
			'columns' => [ 'dn', 'pn', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'gost', 'pn' ],
			'ranges'  => [ 'dn', 'pn' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		];
	}

	return match ( $g ) {
		'otvody'      => [
			'columns' => [ 'dn', 'd', 's', 'angle', 'radius', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'angle', 'gost' ],
			'ranges'  => [ 'dn', 'pn', 's' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		],
		'dnishcha'    => [
			'columns' => [ 'dn', 'd', 's', 'height', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn', 'pn', 's' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		],
		'zaglushki'   => [
			'columns' => [ 'exec', 'd', 's', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn', 'pn', 's' ],
			'sort'    => [ 'field' => 'd', 'dir' => 'asc' ],
		],
		'troyniki', 'perekhody', 'tochenye' => [
			'columns' => [ 'dn', 'd', 's', 'dn2', 'd2', 's2', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'gost' ],
			'ranges'  => [ 'dn', 'pn', 's' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		],
		default => [
			'columns' => [ 'dn', 'd', 's', 'mass' ],
			'facets'  => [ 'industry', 'steel', 'angle', 'gost' ],
			'ranges'  => [ 'dn', 'pn', 's' ],
			'sort'    => [ 'field' => 'dn', 'dir' => 'asc' ],
		],
	};
}

/** Колонки реестра для группы (массив key/label/w/hint). */
function promen_catalog_schema_columns( string $group ): array {
	$defs = promen_catalog_column_defs();
	$keys = promen_catalog_group_schema( $group )['columns'];
	$out  = [];
	foreach ( $keys as $key ) {
		if ( isset( $defs[ $key ] ) ) {
			$out[] = $defs[ $key ];
		}
	}
	return $out;
}

/** Фасеты, доступные для группы. */
function promen_catalog_schema_facets( string $group ): array {
	return promen_catalog_group_schema( $group )['facets'];
}

/** Диапазонные фильтры для группы. */
function promen_catalog_schema_ranges( string $group ): array {
	return promen_catalog_group_schema( $group )['ranges'];
}

/** Сортировка по умолчанию. */
function promen_catalog_schema_sort( string $group ): array {
	return promen_catalog_group_schema( $group )['sort'];
}

/**
 * СТАБИЛЬНЫЙ порядок опций фасета — НЕ по счётчику, чтобы чипы не «скакали»
 * при выборе других фильтров (пользователь: «параметры всегда в одном месте»).
 * Числовые (angle/pn/dn) — по величине; угол дополнительно фильтруется белым
 * списком promen_valid_angles(); остальные (steel/gost/industry) — натуральный
 * порядок по имени. Порядок не зависит от текущего выбора → позиции фиксированы.
 *
 * @param array<int, array{slug?:string,name:string,count?:int,val?:mixed}> $opts
 * @return array<int, array{slug?:string,name:string,count?:int,val?:mixed}>
 */
function promen_catalog_sort_facet_options( array $opts, string $param ): array {
	if ( $param === 'angle' ) {
		if ( function_exists( 'promen_valid_angles' ) ) {
			$valid = promen_valid_angles();
			$opts  = array_values( array_filter(
				$opts,
				static fn( $o ) => in_array( (int) round( (float) $o['name'] ), $valid, true )
			) );
		}
		usort( $opts, static fn( $a, $b ) => (float) $a['name'] <=> (float) $b['name'] );
		return $opts;
	}
	if ( $param === 'pn' || $param === 'dn' ) {
		usort( $opts, static fn( $a, $b ) => (float) ( $a['val'] ?? $a['name'] ) <=> (float) ( $b['val'] ?? $b['name'] ) );
		return $opts;
	}
	usort( $opts, static fn( $a, $b ) => strnatcasecmp( (string) $a['name'], (string) $b['name'] ) );
	return $opts;
}
