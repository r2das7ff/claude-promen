<?php
/**
 * Контент категории «СДТ» — перенос design-reference/sdt.html 1:1.
 * Свои: sidenav, s01 «Суть» (вместо реестра исполнений), s09, s10+s11; без модалки.
 */

defined( 'ABSPATH' ) || exit;

return [
	'sidenav' => static function ( array $ctx ): void { ?>
<nav class="sidenav" aria-label="Навигация по разделам">
  <a class="sidenav-item" href="#hero"><span class="sidenav-dot"></span><span class="sidenav-label">КАТЕГОРИЯ</span></a>
  <a class="sidenav-item" href="#s01"><span class="sidenav-dot"></span><span class="sidenav-label">СУТЬ</span></a>
  <a class="sidenav-item" href="#registry"><span class="sidenav-dot"></span><span class="sidenav-label">РЕЕСТР</span></a>
  
  <a class="sidenav-item" href="#s02"><span class="sidenav-dot"></span><span class="sidenav-label">ТИПЫ</span></a>
  <a class="sidenav-item" href="#s03"><span class="sidenav-dot"></span><span class="sidenav-label">СЕМЕЙСТВА</span></a>
  <a class="sidenav-item" href="#s04"><span class="sidenav-dot"></span><span class="sidenav-label">НОРМЫ</span></a>
  <a class="sidenav-item" href="#s05"><span class="sidenav-dot"></span><span class="sidenav-label">МАТЕРИАЛЫ</span></a>
  <a class="sidenav-item" href="#s06"><span class="sidenav-dot"></span><span class="sidenav-label">ПРИМЕНЕНИЕ</span></a>
  <a class="sidenav-item" href="#s07"><span class="sidenav-dot"></span><span class="sidenav-label">КОНТРОЛЬ</span></a>
  <a class="sidenav-item" href="#s08"><span class="sidenav-dot"></span><span class="sidenav-label">ПРОИЗВОДСТВО</span></a>
  <a class="sidenav-item" href="#s09"><span class="sidenav-dot"></span><span class="sidenav-label">ПАРАМЕТРЫ</span></a>
  <a class="sidenav-item" href="#s10"><span class="sidenav-dot"></span><span class="sidenav-label">ЗАКАЗ</span></a>
  <a class="sidenav-item" href="#s11"><span class="sidenav-dot"></span><span class="sidenav-label">ЗНАНИЯ</span></a>
  <a class="sidenav-item" href="#request"><span class="sidenav-dot"></span><span class="sidenav-label">ЗАПРОС</span></a>
</nav>
<?php },
	'hero' => static function ( array $ctx ): void { ?>
<div class="sdt-hero" id="hero">
    <div class="hero-left">
      <nav class="hero-crumb">
        <?php foreach ( $ctx['crumbs'] as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow">Изготовление под заказ — ТЭС, АЭС, нефтехим</div>
      <h1 class="hero-h1">Соединительные<br><em>детали</em><br>трубопровода</h1>
      <p class="hero-desc">Производство штампованных и сварных деталей для трубопроводных систем ТЭС, АЭС, ГРЭС, нефтегаза и химической промышленности. Исполнение по ГОСТ, ОСТ, ТУ и конструкторской документации заказчика. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">5 семейств</span><span class="hp-k">Отводы · тройники · переходы · днища · заглушки</span></div>
        <div class="hp"><span class="hp-v">DN 6–1600</span><span class="hp-k">Диапазон в каталоге</span></div>
      </div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / SDT SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">6 — 1600</span></div>
        <div class="hud-row"><span class="hud-rk">PN, МПа</span><span class="hud-rv">6 — 160</span></div>
        <div class="hud-row"><span class="hud-rk">Температура среды, °C</span><span class="hud-rv">−70 — +700</span></div>
        <div class="hud-row"><span class="hud-rk">Радиус гиба R / DN</span><span class="hud-rv">1,5 — 5,0</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 17380-2001</span><span class="hud-rv live">Штампованные</span></div>
        <div class="hud-row"><span class="hud-rk">ОСТ 34-42-621/622/632</span><span class="hud-rv live">Сварные</span></div>
        <div class="hud-row"><span class="hud-rk">ТР ТС 032</span><span class="hud-rv live">Сосуды давления</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
      </div>
    </div>
  </div>
<?php },
	'series_custom' => static function ( array $ctx ): void { ?>
<section class="s" id="s01">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">01</span>Подбор изделия</div>
      <div class="s-meta">SDT / PIPE FITTINGS</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача в трубопроводе</div>
          <div class="sg-th">Нужное изделие</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Повернуть трубу на 45°, 90° или 180°</div>
          </div>
          <div class="sg-product" data-label="Нужное изделие">
            <div class="sg-prod-name">Отводы — крутоизогнутые, гнутые, секторные, штампосварные</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17375-2001</span><span class="sg-tag hi">СТО ЦКТИ 321.x</span><span class="sg-tag">ОСТ 36-21-77</span><span class="sg-tag">DN 15–1400</span>
            </div>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN и PN трубопровода</div>
              <div class="sg-param">Угол поворота (45° / 90° / 180° / произвольный)</div>
              <div class="sg-param">Радиус гиба R (1,5DN — 5DN) или тип: крутоизог. / гнутый / секторный</div>
              <div class="sg-param">Марка стали и требования к среде</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Врезать ответвление или разветвить поток</div>
          </div>
          <div class="sg-product" data-label="Нужное изделие">
            <div class="sg-prod-name">Тройники — равнопроходные (d=D), переходные (d&lt;D), сварные крупного DN</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17376-2001</span><span class="sg-tag hi">СТО ЦКТИ 720.x</span><span class="sg-tag">ОСТ 36-24-77</span><span class="sg-tag">DN 15–1000</span>
            </div>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN основной трубы и DN ответвления</div>
              <div class="sg-param">PN, марка стали</div>
              <div class="sg-param">Тип: равнопроходной (d=D) или переходной (d&lt;D)</div>
              <div class="sg-param">Объект: ТЭС / АЭС / нефтехим (влияет на норматив)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Изменить диаметр трубопровода</div>
          </div>
          <div class="sg-product" data-label="Нужное изделие">
            <div class="sg-prod-name">Переходы — концентрические (соосные), эксцентрические (со смещением оси), сварные конусные</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17378-2001</span><span class="sg-tag hi">СТО ЦКТИ 318.x</span><span class="sg-tag">ОСТ 36-22-77</span><span class="sg-tag">DN 15–1400</span>
            </div>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN₁ (большой) и DN₂ (меньший)</div>
              <div class="sg-param">Тип: концентрический (ось сохраняется) или эксцентрический (горизонтальная прокладка, дренаж)</div>
              <div class="sg-param">PN, материал</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Закрыть торец трубы или сосуда давления</div>
          </div>
          <div class="sg-product" data-label="Нужное изделие">
            <div class="sg-prod-name">Днища эллиптические (a/D=0,25), заглушки эллиптические и плоские</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17379-2001</span><span class="sg-tag hi">ГОСТ 6533-78</span><span class="sg-tag">ОСТ 36-25-77</span><span class="sg-tag">DN 25–4000</span>
            </div>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN и PN</div>
              <div class="sg-param">Тип: эллиптическое днище (постоянное закрытие) или плоская заглушка (временное / монтажное)</div>
              <div class="sg-param">Марка стали, температура среды</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 05</div>
            <div class="sg-task-h">Нестандартная деталь или специсполнение</div>
          </div>
          <div class="sg-product" data-label="Нужное изделие">
            <div class="sg-prod-name">По КД / ТУ заказчика — любые геометрия, материал, исполнение для ТЭС и АЭС</div>
            <div class="sg-tags">
              <span class="sg-tag hi">КД заказчика</span><span class="sg-tag hi">НП-045-18</span><span class="sg-tag">НП-089-15</span><span class="sg-tag">Любой DN</span>
            </div>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж или ТЗ с допусками и требованиями к контролю</div>
              <div class="sg-param">Марка стали и требования к сертификации</div>
              <div class="sg-param">Объект (АЭС требует НП-045-18, НП-089-15)</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's02' => static function ( array $ctx ): void { ?>
<section class="s map-outer" id="s02">
    <div class="map-grid"></div>
    <div class="s-hd" style="border-bottom:1px solid rgba(109,140,166,.15);">
      <div class="s-badge s-dark" style="display:flex;"><span class="s-badge-num">02</span><span style="color:rgba(109,140,166,.6);font-family:'DINPro',monospace;font-size:10.5px;letter-spacing:.28em;text-transform:uppercase;margin-left:14px;">Карта типоисполнений</span></div>
      <div class="s-meta">PRODUCT TYPE MAP</div>
    </div>
    <div class="map-body">
      <div class="map-root">
        <div class="map-root-label">СДТ — Соединительные детали трубопровода</div>
      </div>
      <?php
      $map_otv = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( 'otvody' ) : 0;
      $map_tro = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( 'troyniki' ) : 0;
      $map_per = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( 'perekhody' ) : 0;
      $map_dn  = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( 'dnishcha' ) : 0;
      $map_zag = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( 'zaglushki' ) : 0;
      ?>
      <div class="map-groups" id="mapGroups">
        <!-- ОТВОДЫ -->
        <div class="mg" data-type="otv">
          <div class="mg-hd">
            <div class="mg-code">ОТВ</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( $map_otv ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Отводы</div>
          <div class="mg-items">
            <div class="mg-item">Крутоизогнутые 45° / 90° / 180°<span class="mg-norm">ГОСТ 17375-2001</span></div>
            <div class="mg-item">Секторные / сварные<span class="mg-norm">ОСТ 36-21-77</span></div>
            <div class="mg-item">Гнутые из трубных заготовок<span class="mg-norm">СТО ЦКТИ 321.x</span></div>
            <div class="mg-item">Высокое давление / колена с опорой<span class="mg-norm">ГОСТ 22793-83 / 22818-83</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 6–1400</span><span class="mg-ftag">R=1,5–5DN</span><span class="mg-ftag">PN до 160</span></div>
        </div>
        <!-- ТРОЙНИКИ -->
        <div class="mg" data-type="troy">
          <div class="mg-hd">
            <div class="mg-code">ТРО</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( $map_tro ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Тройники</div>
          <div class="mg-items">
            <div class="mg-item">Равнопроходные / переходные<span class="mg-norm">ГОСТ 17376-2001</span></div>
            <div class="mg-item">АЭС — тройники СТО<span class="mg-norm">СТО 95 127-2013</span></div>
            <div class="mg-item">АЭС — врезки и ответвления<span class="mg-norm">СТО 79814898.125-2009</span></div>
            <div class="mg-item">Сварные / ОСТ ТЭС<span class="mg-norm">ОСТ 34-42</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 10–1600</span><span class="mg-ftag">Разветвление</span><span class="mg-ftag">ТЭС / АЭС</span></div>
        </div>
        <!-- ПЕРЕХОДЫ -->
        <div class="mg" data-type="pereh">
          <div class="mg-hd">
            <div class="mg-code">ПЕР</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( $map_per ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Переходы</div>
          <div class="mg-items">
            <div class="mg-item">Концентрические (соосные)<span class="mg-norm">ГОСТ 17378-2001</span></div>
            <div class="mg-item">Эксцентрические (смещение оси)<span class="mg-norm">ГОСТ 17378-2001</span></div>
            <div class="mg-item">Сварные / конусные<span class="mg-norm">ОСТ 36-22-77</span></div>
            <div class="mg-item">ОСТ энергетики<span class="mg-norm">ОСТ 34.10.42x</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 10–1600</span><span class="mg-ftag">Редукция</span><span class="mg-ftag">PN до 160</span></div>
        </div>
      </div>
      <div class="map-groups-2">
        <div class="mg-2" data-type="dn">
          <span class="mg-2-code">ДНЩ</span>
          <span class="mg-2-name">Днища и заглушки</span>
          <span class="mg-2-desc">Днища <?php echo esc_html( number_format_i18n( $map_dn ) ); ?> поз. · заглушки <?php echo esc_html( number_format_i18n( $map_zag ) ); ?> поз. Эллиптические днища и заглушки — DN 6–1600 в каталоге.</span>
          <span class="mg-2-norm">ГОСТ 17379-2001 · ГОСТ 6533-78 · ОСТ 34.10 / 24.125</span>
        </div>
        <div class="mg-2" data-type="ns">
          <span class="mg-2-code">НСТ</span>
          <span class="mg-2-name">Нестандартные детали</span>
          <span class="mg-2-desc">Производство по КД, ТУ, СТО, СТО ЦКТИ заказчика. Специсполнения для объектов ТЭС и АЭС. Любые марки стали, согласование по ТЗ.</span>
          <span class="mg-2-norm">КД заказчика · НП-045-18 · НП-089-15</span>
        </div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void {
		$sdt_family_meta = [
			'otvody'    => [ 'СДТ-01', 'Отводы',   'крутоизогнутые, гнутые, секторные, колена с опорой', '6–1400', 'ГОСТ 17375-2001 · 30753 · 22793 · СТО ЦКТИ 321' ],
			'troyniki'  => [ 'СДТ-02', 'Тройники', 'равнопроходные, переходные, сварные, с опорой',      '10–1600', 'СТО 95 127-2013 · 79814898.125 · ОСТ 34-42' ],
			'perekhody' => [ 'СДТ-03', 'Переходы', 'концентрические, эксцентрические, сварные',           '10–1600', 'ГОСТ 17378-2001 · ОСТ 34.10 · ОСТ 36-22-77' ],
			'dnishcha'  => [ 'СДТ-04', 'Днища',    'эллиптические отбортованные для сосудов и аппаратов',  '10–1500', 'ГОСТ 6533-78 · ОСТ 24.125' ],
			'zaglushki' => [ 'СДТ-05', 'Заглушки', 'эллиптические, плоские, на высокое давление',         '6–1600',  'ГОСТ 17379-2001 · ОСТ 34.10' ],
		];
		$sdt_family_total = 0;
		foreach ( array_keys( $sdt_family_meta ) as $fk ) {
			$sdt_family_total += function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( $fk ) : 0;
		}
		?>
<section class="s s-alt" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Реестр исполнений</div>
      <div class="s-meta">PRODUCT REGISTRY / SDT</div>
    </div>
    <div class="reg-bar" id="regBar">
      <span class="rb-lbl">Семейства СДТ</span>
      <span class="rb-lbl" style="opacity:.55;">Клик — страница семейства и его реестр</span>
      <span class="rb-count" id="regCount">5 семейств · <?php echo esc_html( number_format_i18n( $sdt_family_total ) ); ?> типоразмеров</span>
    </div>
    <div class="reg-hd">
      <span>Код</span><span>Наименование</span><span>DN, мм</span><span>Позиций</span><span>Материалы</span><span>Норматив</span><span>Отрасль</span><span></span>
    </div>
    <div id="regList">
      <?php foreach ( $sdt_family_meta as $fslug => $f ) :
        $ft = get_term_by( 'slug', $fslug, 'product_cat' );
        $furl = promen_product_cat_link( $fslug ) ?: $ctx['shop_url'];
        $fcnt = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( $fslug ) : 0;
      ?>
      <a class="reg-r" data-type="<?php echo esc_attr( $fslug ); ?>" href="<?php echo esc_url( $furl ); ?>">
        <span class="rr-i"><?php echo esc_html( $f[0] ); ?></span>
        <span class="rr-n"><?php echo esc_html( $f[1] ); ?><small><?php echo esc_html( $f[2] ); ?></small></span>
        <span class="rr-dn"><?php echo esc_html( $f[3] ); ?></span>
        <span class="rr-pn"><?php echo esc_html( number_format_i18n( $fcnt ) ); ?> поз.</span>
        <span class="rr-m">по стандарту</span>
        <span class="rr-g"><?php echo esc_html( $f[4] ); ?></span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
      <?php endforeach; ?>
    </div>
</section>
<?php },
	's09' => static function ( array $ctx ): void { ?>
<section class="s" id="s09">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">09</span>Технические параметры</div>
      <div class="s-meta">ENGINEERING SPECIFICATIONS</div>
    </div>
    <div class="s-body">
      <div class="params-wrap reveal">
        <div class="par-left">
          <div class="par-lbl">Фильтр параметров</div>
          <div class="par-grp">
            <div class="par-grp-name">Типоразмер DN, мм</div>
            <div class="dn-grid">
              <button class="dn-b on" data-dn="all">Все</button>
              <button class="dn-b" data-dn="s">15–50</button>
              <button class="dn-b" data-dn="m">65–200</button>
              <button class="dn-b" data-dn="l">250–600</button>
              <button class="dn-b" data-dn="xl">700–1400</button>
            </div>
          </div>
          <div class="par-grp">
            <div class="par-grp-name">Давление PN, МПа</div>
            <div class="pn-flex">
              <button class="pn-b on" data-pn="all">Все</button>
              <button class="pn-b" data-pn="6">6</button>
              <button class="pn-b" data-pn="10">10</button>
              <button class="pn-b" data-pn="16">16</button>
              <button class="pn-b" data-pn="25">25</button>
              <button class="pn-b" data-pn="40">40</button>
              <button class="pn-b" data-pn="63">63</button>
              <button class="pn-b" data-pn="100">100</button>
              <button class="pn-b" data-pn="160">160</button>
            </div>
          </div>
          <div class="par-grp">
            <div class="par-grp-name">Марка стали</div>
            <select class="par-sel" id="matFilter" data-select>
              <option value="all">Все материалы</option>
              <option value="st20">Ст20</option>
              <option value="09g2s">09Г2С</option>
              <option value="15gs">15ГС</option>
              <option value="12x1mf">12Х1МФ</option>
              <option value="15x5m">15Х5М</option>
              <option value="nerj">Нержавеющие</option>
            </select>
          </div>
        </div>
        <div class="par-right">
          <table class="ptbl" id="paramsTable">
            <thead>
              <tr>
                <th>Тип изделия</th>
                <th>DN, мм</th>
                <th>PN, МПа</th>
                <th>Толщ. стенки</th>
                <th>Радиус / угол</th>
                <th>ГОСТ / ОСТ</th>
              </tr>
            </thead>
            <tbody>
              <tr data-dn="s,m" data-pn="25,40,63,100,160" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Отвод крутоизог. 90°</span></td>
                <td><span class="pt-dn">15–500</span></td>
                <td><span class="pt-pn">25–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8734-75 / 8732-78</span></td>
                <td>R = 1,5DN</td>
                <td><span class="pt-norm">ГОСТ 17375-2001</span></td>
              </tr>
              <tr data-dn="s,m" data-pn="25,40,63,100,160" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Отвод крутоизог. 45°</span></td>
                <td><span class="pt-dn">15–500</span></td>
                <td><span class="pt-pn">25–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8734-75 / 8732-78</span></td>
                <td>R = 1,5DN</td>
                <td><span class="pt-norm">ГОСТ 17375-2001</span></td>
              </tr>
              <tr data-dn="s,m" data-pn="25,40,63,100" data-mat="st20,09g2s">
                <td><span class="pt-type">Отвод крутоизог. 180°</span></td>
                <td><span class="pt-dn">15–300</span></td>
                <td><span class="pt-pn">25–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8734-75 / 8732-78</span></td>
                <td>R = 1,5DN</td>
                <td><span class="pt-norm">ГОСТ 17375-2001</span></td>
              </tr>
              <tr data-dn="m,l,xl" data-pn="6,10,16,25,40" data-mat="st20,09g2s,15gs">
                <td><span class="pt-type">Отвод секторный сварной</span></td>
                <td><span class="pt-dn">100–1400</span></td>
                <td><span class="pt-pn">6–40</span></td>
                <td><span class="pt-wall">8–50 мм</span></td>
                <td>15°–90°, R ≥ 1,5DN</td>
                <td><span class="pt-norm">ОСТ 36-21-77 / ОСТ 34-10-752-97</span></td>
              </tr>
              <tr data-dn="s,m" data-pn="40,63,100,160" data-mat="09g2s,12x1mf,15x5m">
                <td><span class="pt-type">Отвод гнутый из трубы</span></td>
                <td><span class="pt-dn">15–500</span></td>
                <td><span class="pt-pn">16–160</span></td>
                <td><span class="pt-wall">По стенке трубы</span></td>
                <td>R = 3,5–5DN</td>
                <td><span class="pt-norm">ГОСТ 24950-81 / СТО ЦКТИ 321.01-06</span></td>
              </tr>
              <tr data-dn="s,m" data-pn="16,25,40,63,100" data-mat="st20,09g2s">
                <td><span class="pt-type">Отвод штампосварной</span></td>
                <td><span class="pt-dn">25–400</span></td>
                <td><span class="pt-pn">16–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8732-78 / 8734-75</span></td>
                <td>R = 1,5DN</td>
                <td><span class="pt-norm">ОСТ 36-20-77</span></td>
              </tr>
              <tr data-dn="s,m" data-pn="16,25,40,63,100" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Тройник равнопроходной</span></td>
                <td><span class="pt-dn">15–500</span></td>
                <td><span class="pt-pn">16–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8732-78 / 8734-75</span></td>
                <td>Угол врезки 90°</td>
                <td><span class="pt-norm">ГОСТ 17376-2001</span></td>
              </tr>
              <tr data-dn="s,m" data-pn="16,25,40,63,100" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Тройник переходной</span></td>
                <td><span class="pt-dn">25–500</span></td>
                <td><span class="pt-pn">16–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8732-78 / 8734-75</span></td>
                <td>d/D = 0,25–0,8</td>
                <td><span class="pt-norm">ГОСТ 17376-2001</span></td>
              </tr>
              <tr data-dn="l,xl" data-pn="6,10,16,25" data-mat="st20,09g2s,15gs">
                <td><span class="pt-type">Тройник сварной</span></td>
                <td><span class="pt-dn">200–1000</span></td>
                <td><span class="pt-pn">6–25</span></td>
                <td><span class="pt-wall">10–60 мм</span></td>
                <td>Угол врезки 90°</td>
                <td><span class="pt-norm">ОСТ 36-24-77 / ОСТ 34-10-762-97</span></td>
              </tr>
              <tr data-dn="s,m,l" data-pn="16,25,40,63,100,160" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Тройник по СТО ЦКТИ</span></td>
                <td><span class="pt-dn">15–1000</span></td>
                <td><span class="pt-pn">6–160</span></td>
                <td><span class="pt-wall">По стенке трубы</span></td>
                <td>Угол врезки 90°</td>
                <td><span class="pt-norm">СТО ЦКТИ 720.01–720.29</span></td>
              </tr>
              <tr data-dn="s,m,l" data-pn="16,25,40,63,100" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Переход концентрический</span></td>
                <td><span class="pt-dn">25–500</span></td>
                <td><span class="pt-pn">16–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8732-78 / 8734-75</span></td>
                <td>Соосный</td>
                <td><span class="pt-norm">ГОСТ 17378-2001</span></td>
              </tr>
              <tr data-dn="s,m,l" data-pn="16,25,40,63,100" data-mat="st20,09g2s,nerj">
                <td><span class="pt-type">Переход эксцентрический</span></td>
                <td><span class="pt-dn">25–500</span></td>
                <td><span class="pt-pn">16–100</span></td>
                <td><span class="pt-wall">По ГОСТ 8732-78 / 8734-75</span></td>
                <td>Смещение (D−d)/2</td>
                <td><span class="pt-norm">ГОСТ 17378-2001</span></td>
              </tr>
              <tr data-dn="l,xl" data-pn="6,10,16,25" data-mat="st20,09g2s">
                <td><span class="pt-type">Переход сварной конусный</span></td>
                <td><span class="pt-dn">300–1400</span></td>
                <td><span class="pt-pn">6–25</span></td>
                <td><span class="pt-wall">10–60 мм</span></td>
                <td>Угол конуса ≤ 30°</td>
                <td><span class="pt-norm">ОСТ 36-22-77 / ОСТ 34-10-753-97</span></td>
              </tr>
              <tr data-dn="s,m,l" data-pn="16,25,40,63,100,160" data-mat="st20,09g2s,12x1mf,nerj">
                <td><span class="pt-type">Переход по СТО ЦКТИ</span></td>
                <td><span class="pt-dn">15–1400</span></td>
                <td><span class="pt-pn">6–160</span></td>
                <td><span class="pt-wall">По стенке трубы</span></td>
                <td>Соосный / смещённый</td>
                <td><span class="pt-norm">СТО ЦКТИ 318.01–318.03</span></td>
              </tr>
              <tr data-dn="s,m,l,xl" data-pn="6,10,16,25,40,63,100" data-mat="st20,09g2s,nerj">
                <td><span class="pt-type">Днище эллиптическое</span></td>
                <td><span class="pt-dn">25–1200</span></td>
                <td><span class="pt-pn">6–100</span></td>
                <td><span class="pt-wall">4–80 мм</span></td>
                <td>a/D = 0,25</td>
                <td><span class="pt-norm">ГОСТ 17379-2001</span></td>
              </tr>
              <tr data-dn="s,m,l" data-pn="6,10,16,25,40,63,100" data-mat="st20,09g2s,12x1mf">
                <td><span class="pt-type">Заглушка плоская</span></td>
                <td><span class="pt-dn">15–600</span></td>
                <td><span class="pt-pn">6–100</span></td>
                <td><span class="pt-wall">4–50 мм</span></td>
                <td>Торцевая</td>
                <td><span class="pt-norm">ГОСТ 17380-2001</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void { ?>
<section class="s" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>Порядок заказа</div>
      <div class="s-meta">ORDERING PROCESS</div>
    </div>
    <div class="flow-grid-wrap">
      <div class="flow-grid reveal">
        <div class="fl-s">
          <div class="fl-n">01</div>
          <div class="fl-h">Техническое задание</div>
          <p class="fl-p">Передаёте спецификацию: тип изделия, DN, PN, марка стали, ГОСТ/ОСТ, количество, объект. Принимаем ТЗ, чертежи, ведомости.</p>
          <span class="fl-tag">ТЗ / КД / Спецификация</span>
        </div>
        <div class="fl-s">
          <div class="fl-n">02</div>
          <div class="fl-h">Инженерная проработка</div>
          <p class="fl-p">Подбор исполнения, проверка допустимости параметров, согласование материала и объёма контроля. Коммерческое предложение в течение 1–3 рабочих дней.</p>
          <span class="fl-tag">КП / Согласование</span>
        </div>
        <div class="fl-s">
          <div class="fl-n">03</div>
          <div class="fl-h">Производство и контроль</div>
          <p class="fl-p">Изготовление на собственных мощностях. Полный маршрут ОТК: входной контроль, НК, гидравлические испытания, маркировка, паспортизация.</p>
          <span class="fl-tag">ОТК / НК / Паспорт</span>
        </div>
        <div class="fl-s">
          <div class="fl-n">04</div>
          <div class="fl-h">Отгрузка и документы</div>
          <p class="fl-p">Комплект сопроводительной документации: паспорт, сертификат металла, протоколы НК, акт испытаний. Доставка на объект заказчика.</p>
          <span class="fl-tag">Отгрузка / Документы</span>
        </div>
      </div>
    </div>
  </section>
<?php },
	'after' => static function ( array $ctx ): void { ?>
<section class="s kb-wrap" id="s11">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">11</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / ENGINEERING REFERENCE</div>
    </div>

    <!-- Tab bar -->
    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="product" role="tab"><span class="kb-tab-n">01</span>О продукции</button>
      <button class="kb-tab" data-panel="select" role="tab"><span class="kb-tab-n">02</span>Подбор изделий</button>
      <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
      <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
      <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
      <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Заказ и стоимость</button>
      <button class="kb-tab" data-panel="faq" role="tab"><span class="kb-tab-n">07</span>Частые вопросы</button>
    </div>

    <!-- Panels -->
    <div class="kb-panels">

      <!-- ─── TAB 1: О ПРОДУКЦИИ ─── -->
      <div class="kb-panel kp-active" id="kp-product">
        <div class="kb-lead">
          <div class="kb-lead-h">Соединительные детали трубопроводов</div>
          <p class="kb-lead-p">СДТ — группа трубопроводных изделий, обеспечивающих изменение направления потока, разветвление, соединение труб разных диаметров и концевое заглушение трубопроводных линий. Применяются во всех видах промышленных трубопроводных систем: паровых, водяных, газовых, нефтяных трактах при любых давлениях и температурах — от криогенных (−196°С) до жаровысокотемпературных (+600°С).</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">АЭС</div>
            <div class="kb-card-title">Атомная энергетика</div>
            <p class="kb-card-body">Трубопроводы <strong>I–IV категорий по НП-045-18</strong>. Расширенный объём НК согласно НП-104-18, прослеживаемость плавки, паспортизация по ГОСТ ISO 10474. Первый контур реакторного отсека, системы аварийного охлаждения, вспомогательные контуры. Изготовление по ТУ предприятия и КД заказчика.</p>
            <div class="kb-card-tags"><span class="kb-tag">НП-045-18</span><span class="kb-tag">НП-068-05</span><span class="kb-tag">НП-089-15</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ТЭС / ГРЭС</div>
            <div class="kb-card-title">Тепловая энергетика</div>
            <p class="kb-card-body">Главные паропроводы, питательные трубопроводы, линии отборов пара. <strong>Рабочие параметры до +600°С, PN до 25 МПа</strong>. Нормативная база — <strong>СТО ЦКТИ серий 321</strong> (гнутые детали) и <strong>720</strong> (тройники, переходы), ОСТ 34 для паровых трактов ТЭС и ГРЭС.</p>
            <div class="kb-card-tags"><span class="kb-tag">СТО ЦКТИ 321</span><span class="kb-tag">СТО ЦКТИ 720</span><span class="kb-tag">ОСТ 34</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">НГК</div>
            <div class="kb-card-title">Нефтегазовый комплекс</div>
            <p class="kb-card-body">Промысловые и магистральные трубопроводы, установки подготовки нефти и газа. Требования по коррозионной стойкости к агрессивным средам (H₂S, CO₂). Изготовление по <strong>ГОСТ 17375-2001, 17376-2001, 17378-2001, 17379-2001</strong>, ОСТ 36 и ТУ предприятия с возможностью учёта требований корпоративных стандартов.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 17380-2001</span><span class="kb-tag">ТР ТС 032</span><span class="kb-tag">09Г2С</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ХИМИЯ / ПП</div>
            <div class="kb-card-title">Химия и промышленность</div>
            <p class="kb-card-body">Реакторы, трубопроводы агрессивных сред, криогенные системы. Материалы — <strong>нержавеющие стали 12Х18Н10Т, 10Х17Н13М2Т</strong> и специальные сплавы. Рабочий диапазон −196…+700°С, среды: кислоты, щёлочи, хлориды, водород, аммиак, перегретый пар.</p>
            <div class="kb-card-tags"><span class="kb-tag">12Х18Н10Т</span><span class="kb-tag">Нержавейка</span><span class="kb-tag">−196°С</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Номенклатура завода — 8 групп продукции</div>
        <div class="kb-groups">
          <div class="kb-grp">
            <span class="kb-grp-code">СДТ</span>
            <span class="kb-grp-name">Соединительные детали трубопровода</span>
            <span class="kb-grp-items">Отводы 45°/90°/180° · Тройники равнопроходные и переходные · Переходы · Днища эллиптические · Заглушки</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ФЛ</span>
            <span class="kb-grp-name">Фланцы трубопроводные</span>
            <span class="kb-grp-items">Воротниковые приварные · Плоские · Свободные на кольце · Глухие · По ГОСТ 33259-2015 и СТО ЦКТИ</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ОП</span>
            <span class="kb-grp-name">Опоры и подвески</span>
            <span class="kb-grp-items">Скользящие · Неподвижные · Пружинные по ОСТ 36-17-85 и СТО ЦКТИ серии 321</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ЗРА</span>
            <span class="kb-grp-name">Запорно-регулирующая арматура</span>
            <span class="kb-grp-items">Задвижки · Клапаны · Краны · По ГОСТ 33257-2015 и НП-068-05 для АЭС</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ТР</span>
            <span class="kb-grp-name">Трубы стальные бесшовные</span>
            <span class="kb-grp-items">Горячедеформированные — ГОСТ 8731-87 / 8732-78, холоднодеформированные — ГОСТ 8733-87 / 8734-75, нержавеющие — ГОСТ 9940-81 / 9941-2022</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">НМ</span>
            <span class="kb-grp-name">Нестандартные металлоизделия</span>
            <span class="kb-grp-items">Детали трубопроводов по чертежам заказчика · Приём КД в форматах DWG / PDF / STEP</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ИЗ</span>
            <span class="kb-grp-name">Изоляция и покрытия</span>
            <span class="kb-grp-items">Тепловая изоляция · Антикоррозионные покрытия · Комплектация для изолированного монтажа</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ТД</span>
            <span class="kb-grp-name">Точёные крепёжные детали</span>
            <span class="kb-grp-items">Шпильки · Гайки · Втулки · Компенсаторы · По ГОСТ и КД заказчика</span>
          </div>
        </div>
      </div><!-- /kp-product -->

      <!-- ─── TAB 2: ПОДБОР ИЗДЕЛИЙ ─── -->
      <div class="kb-panel" id="kp-select">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Ключевые параметры подбора</div>
            <div class="kb-params">
              <div class="kb-param">
                <div class="kb-param-key">DN · Диаметр условный</div>
                <div class="kb-param-val">От <strong>DN 15 до DN 1400</strong> мм. Соответствует условному проходу трубы. Не совпадает с наружным диаметром — DN 50 = Dнар 57 мм по ГОСТ 8732.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">PN · Давление условное</div>
                <div class="kb-param-val">От <strong>PN 0.6 до PN 20.0 МПа</strong> (6–200 кгс/см²). Условное давление при 20°С — при повышенных температурах допустимое давление снижается по таблицам норматива.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Нормативный документ</div>
                <div class="kb-param-val"><strong>ГОСТ, ОСТ, СТО ЦКТИ, НП, ТУ, КД</strong>. Определяет геометрию, допуски на размеры, категорию изделия и обязательный объём контроля качества.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Марка стали</div>
                <div class="kb-param-val">Подбирается по рабочей температуре и среде. <strong>Ст20</strong> — до +425°С; <strong>09Г2С</strong> — до −70°С; <strong>12Х1МФ</strong> — до +570°С; <strong>12Х18Н10Т</strong> — агрессивные среды и АЭС.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Исполнение</div>
                <div class="kb-param-val"><strong>Производство</strong> / <strong>Поставка</strong> / <strong>По чертежу</strong>. Для ответственных объектов предпочтительно производство с полным пакетом собственной документации.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Объём НК</div>
                <div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Расширенный: +<strong>УЗК</strong> / +<strong>РК</strong> / +<strong>МПД</strong> / +<strong>ПВК</strong>. Полный объём для АЭС согласно НП-045-18 и требованиям программы контроля объекта.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Комплект документов</div>
                <div class="kb-param-val">Паспорт 3.1 (ГОСТ ISO 10474) + сертификат на металл + протоколы НК + ГИ (по необходимости). Для АЭС — расширенный пакет с картами прослеживаемости плавки.</div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Как ориентироваться в каталоге</div>
            <div class="kb-steps">
              <div class="kb-step">
                <span class="kb-step-n">01</span>
                <div>
                  <div class="kb-step-title">Выберите группу продукции</div>
                  <div class="kb-step-body">В левой навигационной панели каталога — 9 групп: ВСЕ, СДТ, ФЛ, ОП, ЗРА, ТР, НМ, ИЗ, ТД. Клик сужает реестр до изделий выбранного типа.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">02</span>
                <div>
                  <div class="kb-step-title">Уточните тип: производство или поставка</div>
                  <div class="kb-step-body">Фильтр «Тип» разделяет позиции <strong>собственного производства</strong> (с полным пакетом документов) и торговые поставочные позиции.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">03</span>
                <div>
                  <div class="kb-step-title">Фильтруйте по отрасли</div>
                  <div class="kb-step-body">Фильтр «Отрасль» — <strong>АЭС, ТЭС, НГК</strong> — быстро выбирает изделия, сертифицированные или аттестованные для применения на конкретном типе объектов.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">04</span>
                <div>
                  <div class="kb-step-title">Фильтруйте по нормативу</div>
                  <div class="kb-step-body">Фильтр «Нормы» — <strong>ГОСТ, ОСТ, СТО ЦКТИ, НП, ТУ</strong> — находит позиции, соответствующие конкретному нормативному документу вашего проекта.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">05</span>
                <div>
                  <div class="kb-step-title">Используйте строку поиска</div>
                  <div class="kb-step-body">Поиск работает по <strong>коду, наименованию, ГОСТ, материалу, DN</strong>. Например: «09Г2С» или «ГОСТ 17375-2001» мгновенно фильтрует весь реестр. Горячая клавиша: <strong>⌘K</strong> или <strong>/</strong>.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">06</span>
                <div>
                  <div class="kb-step-title">Откройте карточку и запросите КП</div>
                  <div class="kb-step-body">Каждая позиция открывается в панели с техническими данными, нормативами, объёмом НК и параметрами контроля. Прямая ссылка на форму запроса коммерческого предложения.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /kp-select -->

      <!-- ─── TAB 3: НОРМАТИВНАЯ БАЗА ─── -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор нормативного документа определяет геометрию изделия, допуски на размеры, категорию, объём неразрушающего контроля и состав разрешительной документации. Большинство позиций каталога охвачено одновременно несколькими нормативами — базовым ГОСТ и отраслевым (СТО ЦКТИ, ОСТ, НП) для конкретного типа объектов.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — государственные стандарты</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17380-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные из углеродистой и низколегированной стали. Общие технические условия — головной документ серии</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17375-2001</span><span class="kb-norm-desc">Отводы крутоизогнутые типа 3D (R ≈ 1,5DN). Конструкция. Углы 45°, 60°, 90°, 180°</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30753-2001</span><span class="kb-norm-desc">Отводы крутоизогнутые типа 2D (R ≈ DN). Конструкция — исполнение малого радиуса</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17376-2001</span><span class="kb-norm-desc">Тройники равнопроходные и переходные. Конструкция</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17378-2001</span><span class="kb-norm-desc">Переходы концентрические и эксцентрические. Конструкция</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17379-2001</span><span class="kb-norm-desc">Заглушки эллиптические. Конструкция</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 6533-78</span><span class="kb-norm-desc">Днища эллиптические отбортованные стальные для сосудов, аппаратов и котлов. D 133–4500 мм</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33259-2015</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов на номинальное давление до PN 250</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ОСТ — отраслевые стандарты Минэнерго и Минмаша</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-699-97 / 10.700-97</span><span class="kb-norm-desc">Отводы крутоизогнутые и переходы стальные бесшовные приварные на Рраб &lt; 2,2 МПа для атомных и тепловых электростанций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-747-97 ÷ 10.766-97</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов ТЭС из углеродистой и низколегированной сталей, Рраб &lt; 2,2 МПа, t ≤ 425 °C. Части I–III</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-416-90 ÷ 513-90</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов из коррозионностойкой стали на Рраб ≤ 2,2 МПа, T ≤ 300 °C для АС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-20-77 ÷ 36-25-77</span><span class="kb-norm-desc">Детали трубопроводов Dy 500–1400 мм сварные из углеродистой стали на Ру ≤ 2,5 МПа: отводы штампосварные и секторные, тройники, переходы, заглушки</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-41-81 — 36-49-81</span><span class="kb-norm-desc">Детали трубопроводов из углеродистой стали сварные и гнутые Dy до 500 мм на Ру до 10 МПа</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 24.125.01-89 — 24.125.26-89</span><span class="kb-norm-desc">Детали и сборочные единицы из сталей аустенитного класса для трубопроводов АЭС Dy 14–325 мм</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">СТО — стандарты организаций (ТЭС и АЭС)</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.01–.08-2009</span><span class="kb-norm-desc">Отводы гнутые, крутоизогнутые, штампованные и штампосварные для трубопроводов и паропроводов тепловых станций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 720.01–.24-2009</span><span class="kb-norm-desc">Тройники равнопроходные и переходные (штампованные, сварные, кованые) для трубопроводов и паропроводов ТЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 318.01–.06-2009</span><span class="kb-norm-desc">Переходы точёные, обжатые и штампованные для трубопроводов и паропроводов тепловых станций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 462 / 504 / 530 / 313</span><span class="kb-norm-desc">Штуцера и патрубки, донышки приварные, бобышки, соединения штуцерные. Ресурс 200 000 часов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО 95 133-2013</span><span class="kb-norm-desc">Заглушки плоские приварные для трубопроводов атомных станций из сталей перлитного класса на давление до 2,2 МПа</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО СРО-П 60542948.00010-2013</span><span class="kb-norm-desc">Детали и элементы трубопроводов групп В и С атомных станций. Соединения сварные. Типы и размеры</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">НП — нормы ядерной и радиационной безопасности (АЭС)</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">НП-104-18</span><span class="kb-norm-desc">Сварка и наплавка оборудования и трубопроводов АЭУ. Объём и методы НК</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-068-05</span><span class="kb-norm-desc">Требования к арматуре для атомных станций. Проектирование, изготовление, испытания</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-089-15</span><span class="kb-norm-desc">Общие требования к оборудованию и трубопроводам АЭУ. Категории трубопроводов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ПБ 03-585-03</span><span class="kb-norm-desc">Правила устройства и безопасной эксплуатации технологических трубопроводов</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / ТУ — регламенты и технические условия</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования, работающего под избыточным давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">Технические условия ООО Завод «Промышленная Энергетика» на детали трубопроводов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">КД заказчика</span><span class="kb-norm-desc">Изготовление по индивидуальным чертежам. Согласование материала, технологии и объёма НК до запуска в производство</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ на металл и контроль</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений. Методы и оценка результатов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050-2013</span><span class="kb-norm-desc">Металлопродукция из нелегированных конструкционных качественных сталей (Ст20 и др.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5632-2014</span><span class="kb-norm-desc">Нержавеющие стали и сплавы коррозионно-стойкие, жаростойкие и жаропрочные</span></div>
            </div>
          </div>
        </div>
      </div><!-- /kp-norms -->

      <!-- ─── TAB 4: МАТЕРИАЛЫ ─── -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">Завод работает с полным спектром конструкционных сталей для трубопроводных систем — от углеродистых для общепромышленных применений до жаропрочных перлитных и аустенитных нержавеющих для объектов атомной и тепловой энергетики. <strong>Каждая марка стали поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с указанием плавочных данных, химического состава и механических характеристик. Прослеживаемость металла от плавки завода-поставщика до готового изделия фиксируется документально.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731-87</div>
            <div class="kb-mat-range">до +425°С · PN до 20 МПа</div>
            <div class="kb-mat-apps">Водяные тракты ТЭС · Общепромышленные трубопроводы · НГК низкого давления · Бытовые и отопительные системы</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">09Г2С</div>
            <div class="kb-mat-std">ГОСТ 19281-2014</div>
            <div class="kb-mat-range">−70…+350°С · Хладостойкая</div>
            <div class="kb-mat-apps">Криогенные системы · Северное и арктическое исполнение · НГК при низких температурах · Установки разделения воздуха</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">15ГС</div>
            <div class="kb-mat-std">ОСТ 108.030.118-78</div>
            <div class="kb-mat-range">до +450°С</div>
            <div class="kb-mat-apps">Трубопроводы ТЭС среднего давления · Паровые тракты · Питательные линии</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х1МФ</div>
            <div class="kb-mat-std">ОСТ 108.030.118-78 · ТУ</div>
            <div class="kb-mat-range">до +570°С · Главные паропроводы</div>
            <div class="kb-mat-apps">Паропроводы высокого давления ТЭС и ГРЭС · Главные паровые тракты энергоблоков СКД · Свежий пар 25 МПа / 545°С</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">15Х1М1Ф</div>
            <div class="kb-mat-std">ТУ 14-3-460</div>
            <div class="kb-mat-range">до +580°С · Сверхкритика</div>
            <div class="kb-mat-apps">Сверхкритические параметры пара · Блоки мощностью 300–800 МВт · Повышенные требования к ползучести</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">−196…+600°С · Нержавеющая</div>
            <div class="kb-mat-apps">АЭС (все контуры, все категории) · Агрессивные химические среды · Пищевая и фармацевтическая промышленность</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">10Х17Н13М2Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">до +700°С · Кислотостойкая</div>
            <div class="kb-mat-apps">Сильноагрессивные среды · Серная и фосфорная кислоты · Хлориды · Установки химической переработки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">13Х11Н2В2МФ</div>
            <div class="kb-mat-std">ТУ · Спецназначение</div>
            <div class="kb-mat-range">Мартенситная · Высокопрочная</div>
            <div class="kb-mat-apps">Турбинные диски и детали · Энергетические установки со специальными требованиями по прочности и коррозионной стойкости</div>
          </div>
        </div>
      </div><!-- /kp-materials -->

      <!-- ─── TAB 5: ДОКУМЕНТАЦИЯ ─── -->
      <div class="kb-panel" id="kp-docs">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Стандартный комплект поставки</div>
            <div class="kb-doclist">
              <div class="kb-doc-item">
                <div class="kb-doc-name">Паспорт изделия — сертификат качества 3.1</div>
                <div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Содержит химический состав плавки, механические свойства, результаты приёмочного контроля, маркировку и ссылку на норматив.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл с плавочными данными</div>
                <div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла: номер плавки, химсостав, механические характеристики, стандарт на металл.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК — 100% объём</div>
                <div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Подтверждает геометрическое соответствие и качество поверхности по требованиям норматива.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протоколы УЗК / РК / МПД / ПВК</div>
                <div class="kb-doc-desc">По требованию заказчика или нормативного документа. УЗК по ГОСТ Р 55724-2013. Объём контроля фиксируется в договоре.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Акт гидравлических испытаний</div>
                <div class="kb-doc-desc">При наличии требования в заказе или нормативе. Давление испытания 1.25–1.5 × Pраб, выдержка не менее 5 минут.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Декларация ТР ТС 032/2013 <span class="kb-doc-badge">Обязательно</span></div>
                <div class="kb-doc-desc">RU С-RU.АБ53.В.08323/23, серия RU 0418908. Обязательна при PN&nbsp;&gt;&nbsp;0.05&nbsp;МПа для всей продукции в ЕАЭС.</div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:11px;letter-spacing:.1em;color:var(--g1);">по НП-104-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Программа контроля качества</div>
                <div class="kb-doc-desc">Индивидуальная программа НК для каждой категории трубопровода. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают изделие от заготовки до готовой детали. Содержат номер плавки, номер детали, ссылки на все протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Технологические карты сварки и PWHT</div>
                <div class="kb-doc-desc">По согласованным WPS и PQR. Фиксируют параметры сварочных режимов и результаты послесварочной термообработки.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации сварщиков и специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения и аттестационные свидетельства согласно НП-043-18 и ПБ 03-273-99.</div>
              </div>
            </div>

            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> по проектным спецификациям — от нескольких позиций до полной номенклатуры одного трубопроводного контура. Комплектная поставка включает единую сводную ведомость с позициями, координацию нормативных документов по каждой позиции и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта, обеспечивающий поэтапную приёмку и отчётность.</p>
          </div>
        </div>
      </div><!-- /kp-docs -->

      <!-- ─── TAB 6: ЗАКАЗ И СТОИМОСТЬ ─── -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">

          <div>
            <div class="kb-col-title">Как подготовить заявку</div>
            <div class="kb-checklist">
              <div class="kb-check">
                <span class="kb-check-n">01</span>
                <div>
                  <div class="kb-check-title">Наименование и норматив</div>
                  <div class="kb-check-body">Тип изделия и нормативный документ: отвод 90° по <strong>ГОСТ 17375-2001</strong>, тройник по <strong>СТО ЦКТИ 720.03</strong> и т.д. Если норматив неизвестен — укажите тип объекта / установки.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">02</span>
                <div>
                  <div class="kb-check-title">DN, PN, толщина стенки</div>
                  <div class="kb-check-body">DN (диаметр условный), PN в МПа или кгс/см², толщина стенки (если нестандартная). Для фланцев — дополнительно тип уплотнения (FF / RF / RTJ).</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">03</span>
                <div>
                  <div class="kb-check-title">Марка стали</div>
                  <div class="kb-check-body">Точная марка или рабочие условия (t°С, среда, агрессивность) для подбора. Для АЭС — согласно программе контроля объекта.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">04</span>
                <div>
                  <div class="kb-check-title">Количество и срок</div>
                  <div class="kb-check-body">Количество в штуках. Желаемая дата поставки или срок с момента подтверждения заказа. Для крупных комплектаций — поэтапный график.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">05</span>
                <div>
                  <div class="kb-check-title">Объём НК и документация</div>
                  <div class="kb-check-body">Требуемые методы НК и состав документационного пакета. Для АЭС — ссылка на программу контроля или категорию трубопровода по <strong>НП-045-18</strong>.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">06</span>
                <div>
                  <div class="kb-check-title">Чертёж или КД (для нестандарта)</div>
                  <div class="kb-check-body">DWG, PDF или STEP. Проводим инженерную проработку, согласование материала и технологии. Срок и стоимость — после анализа КД.</div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Марка стали</div>
                  <div class="kb-factor-note">Жаропрочные и нержавеющие стали дороже углеродистых в 3–7 раз. 12Х18Н10Т — примерно в 5–6 раз дороже Ст20.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Объём неразрушающего контроля</div>
                  <div class="kb-factor-note">Каждый дополнительный метод НК увеличивает трудоёмкость. Полный объём для АЭС может в 2–4 раза превышать базовый (ВИК).</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">DN и толщина стенки</div>
                  <div class="kb-factor-note">Масса изделия нелинейно растёт с DN. Крупногабаритные детали DN&nbsp;500+ требуют специальной оснастки и прессового оборудования.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↓</span>
                <div>
                  <div class="kb-factor-name">Тираж заказа</div>
                  <div class="kb-factor-note">Единичные позиции дороже серийных — фиксированные затраты на подготовку производства делятся на весь объём. Серийность от 10 шт. снижает себестоимость.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Срочность</div>
                  <div class="kb-factor-note">Сжатые сроки (менее 10 рабочих дней) требуют приоритетной загрузки производства. Срочные заказы оговариваются индивидуально.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Нестандартная геометрия</div>
                  <div class="kb-factor-note">Изготовление по КД заказчика требует разработки или адаптации технологической оснастки, что увеличивает подготовительные затраты.</div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Частые ошибки при подборе</div>
            <div class="kb-errors">
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">DN ≠ наружный диаметр трубы</div>
                  <div class="kb-err-note">Для DN&nbsp;50 наружный диаметр по ГОСТ&nbsp;8732 составляет 57&nbsp;мм. Всегда уточняйте стандарт и серию трубы при заказе.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Не указана марка стали</div>
                  <div class="kb-err-note">«Сталь» без марки — не спецификация. Для ответственных объектов недопустимо: материал влияет на технологию сварки и объём НК.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Путаница PN и Рабочего давления</div>
                  <div class="kb-err-note">PN — условное (нормативное) давление при 20°С. При повышенных температурах допустимое давление снижается по таблицам норматива — учитывайте это при подборе.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Не учтена категория трубопровода</div>
                  <div class="kb-err-note">Для АЭС объём НК и документации определяется категорией (I–IV). Ошибка в категории — несоответствие программе контроля и срыв сдачи объекта.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">КД вместо ГОСТ для типовых изделий</div>
                  <div class="kb-err-note">Для стандартных позиций (отводы, тройники) заказ «по чертежу» при совпадении с ГОСТ добавляет документооборот и задержку без технического смысла.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Забыть про ТР ТС 032/2013</div>
                  <div class="kb-err-note">Изделия с PN &gt; 0.05 МПа в ЕАЭС требуют декларации ТР ТС. Без неё оборудование не может быть введено в эксплуатацию — заказывайте заблаговременно.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div><!-- /kp-order -->

      <!-- ─── TAB 7: ЧАСТЫЕ ВОПРОСЫ ─── -->
      <div class="kb-panel" id="kp-faq">
        <div class="kb-lead">
          <div class="kb-lead-h">Частые вопросы о стальных деталях трубопроводов</div>
          <p class="kb-lead-p">Ответы на вопросы, которые чаще всего задают инженеры-проектировщики, специалисты отделов снабжения и технического надзора при работе с СДТ для объектов ТЭС, АЭС, НГК и химической промышленности.</p>
        </div>
<?php // Разметка FAQ собирается из этой же вёрстки, см. promen_faq_schema().
  promen_faq_schema( get_theme_file_path( 'inc/category-content/sdt.php' ) ); ?>
        <div class="faq-wrap reveal">
          <div class="fq"><div class="fq-q"><span class="fq-num">01</span><span class="fq-t">Чем отличаются изделия по ОСТ и ГОСТ — можно ли их заменить друг другом?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">ГОСТ и ОСТ — разные нормативные документы с отличающимися допусками, маркировкой и требованиями к контролю. <strong>Взаимозаменяемость — только по письменному согласованию с проектировщиком и представителем надзора.</strong> Для объектов ТЭС/АЭС самовольная замена нормативного документа недопустима.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">02</span><span class="fq-t">Поставляете ли изделия с сертификацией по ТР ТС 032/2013?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Вся продукция завода охвачена декларацией о соответствии <strong>RU С-RU.АБ53.В.08323/23</strong> по ТР ТС 032/2013 «О безопасности оборудования, работающего под давлением». Декларация включается в комплект документов на поставку.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">03</span><span class="fq-t">Какой объём неразрушающего контроля применяется по умолчанию?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Базовый объём — <strong>100% ВИК</strong> (визуально-измерительный контроль) для всех изделий. По требованию заказчика или в соответствии с нормативным документом добавляются:<ul><li>УЗК — по ГОСТ Р 55724-2013</li><li>РК (рентгенографический контроль)</li><li>МПД (магнитопорошковая дефектоскопия)</li><li>ПВК (капиллярный контроль)</li></ul>Для объектов АЭС — полный объём по <strong>НП-045-18</strong> и программе контроля объекта.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">04</span><span class="fq-t">Можно ли заказать нестандартные типоразмеры или исполнение по чертежам заказчика?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Завод изготавливает изделия по конструкторской документации заказчика — в том числе нестандартные диаметры, углы, толщины стенок и специальные исполнения. Для согласования — отправьте КД через форму запроса или на <strong>zakaz@prom-en.com</strong>.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">05</span><span class="fq-t">Как долго хранится прослеживаемость документации после поставки?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Архив производственной документации (паспорта, протоколы НК, сертификаты плавок) хранится на производстве <strong>не менее 10 лет</strong>. Для объектов АЭС — в соответствии с требованиями НП-017-14 и НП-089-15. По запросу возможно предоставление дубликатов документов.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">06</span><span class="fq-t">Какие сроки изготовления для типовых позиций каталога?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Типовые позиции из складской программы (DN 50–200, массовые марки стали) — <strong>от 3–5 рабочих дней</strong>. Серийный заказ с полным НК и паспортизацией — <strong>от 10–15 рабочих дней</strong>. Изделия DN 500+ и спецсплавы — по согласованию. Точный срок указывается в коммерческом предложении.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">07</span><span class="fq-t">Есть ли складская программа или всё производится под заказ?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Часть позиций номенклатуры поддерживается на складе — прежде всего <strong>отводы, тройники и переходы DN 50–200 из Ст20 и 09Г2С</strong> по ГОСТ 17375-2001 / 17376-2001 / 17378-2001. Для уточнения наличия — направьте запрос: мы предоставим актуальный остаток и срок дополнительного выпуска.</div></div></div>
        </div>
      </div><!-- /kp-faq -->

    </div><!-- /kb-panels -->
  </section>
<?php },
];
