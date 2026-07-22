<?php
/**
 * Страница категории «Винты» — семейство крепежа.
 * Ветка /catalog/krepezh/vinty/. Факты — aggregates / content/category/vinty.md.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$term     = get_queried_object();
$crumbs   = promen_breadcrumbs();
$shop_url = wc_get_page_permalink( 'shop' );
$otv      = get_term_by( 'slug', 'vinty', 'product_cat' );
$otv_url  = add_query_arg( 'group', 'vinty', $shop_url );
$otv_cnt  = $otv ? (int) $otv->count : 0;
$krep     = get_term_by( 'slug', 'krepezh', 'product_cat' );
?>
<script type="application/ld+json"><?php echo promen_breadcrumbs_schema( $crumbs ); ?></script>

<nav class="sidenav" aria-label="Навигация по разделам">
  <a class="sidenav-item" href="#hero"><span class="sidenav-dot"></span><span class="sidenav-label">КАТЕГОРИЯ</span></a>
  <a class="sidenav-item" href="#s01"><span class="sidenav-dot"></span><span class="sidenav-label">РЕЕСТР</span></a>
  <a class="sidenav-item" href="#s02"><span class="sidenav-dot"></span><span class="sidenav-label">ТИПЫ</span></a>
  <a class="sidenav-item" href="#s03"><span class="sidenav-dot"></span><span class="sidenav-label">ПОДБОР</span></a>
  <a class="sidenav-item" href="#s04"><span class="sidenav-dot"></span><span class="sidenav-label">НОРМЫ</span></a>
  <a class="sidenav-item" href="#s05"><span class="sidenav-dot"></span><span class="sidenav-label">МАТЕРИАЛЫ</span></a>
  <a class="sidenav-item" href="#s06"><span class="sidenav-dot"></span><span class="sidenav-label">ПРИМЕНЕНИЕ</span></a>
  <a class="sidenav-item" href="#s07"><span class="sidenav-dot"></span><span class="sidenav-label">КОНТРОЛЬ</span></a>
  <a class="sidenav-item" href="#s08"><span class="sidenav-dot"></span><span class="sidenav-label">ПРОИЗВОДСТВО</span></a>
  <a class="sidenav-item" href="#s09"><span class="sidenav-dot"></span><span class="sidenav-label">ЗАКАЗ</span></a>
  <a class="sidenav-item" href="#s10"><span class="sidenav-dot"></span><span class="sidenav-label">ЗНАНИЯ</span></a>
  <a class="sidenav-item" href="#request"><span class="sidenav-dot"></span><span class="sidenav-label">ЗАПРОС</span></a>
</nav>

<div class="pg">

  <div class="sdt-hero" id="hero">
    <div class="hero-left">
      <nav class="hero-crumb">
        <?php foreach ( $crumbs as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow">КР / Винты · изготовление под заказ</div>
      <h1 class="hero-h1">Винты<br><em>по ГОСТ</em><br>6958</h1>
      <p class="hero-desc">Винты по ГОСТ 6958-1978. В реестре — 1 позиция. Подбор и уточнение исполнения — по карточке и заявке.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $otv_cnt ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">M × L</span><span class="hp-k">Резьба / длина</span></div>
        <div class="hp"><span class="hp-v">В</span><span class="hp-k">Тип крепежа</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
        <a class="s10-ghost-link" href="<?php echo esc_url( $otv_url ); ?>">Открыть полный реестр</a>
      </div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / VINTY SPECS</div>
        <div class="hud-row"><span class="hud-rk">Резьба M</span><span class="hud-rv">по типоразмеру</span></div>
        <div class="hud-row"><span class="hud-rk">Длина L</span><span class="hud-rv">по карточке</span></div>
        <div class="hud-row"><span class="hud-rk">Семейство</span><span class="hud-rv">ГОСТ 6958</span></div>
        <div class="hud-row"><span class="hud-rk">В группе КР</span><span class="hud-rv"><?php echo $krep ? esc_html( $krep->name ) : 'Крепёж'; ?></span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 6958-1978</span><span class="hud-rv live">1 поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
        <div class="hud-row"><span class="hud-rk">Документы</span><span class="hud-rv live">Паспорт 3.1</span></div>
        <div class="hud-row"><span class="hud-rk">Заказ</span><span class="hud-rv live">по заявке</span></div>
      </div>
    </div>
  </div>


<section class="s s-alt" id="s01">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">01</span>Реестр исполнений</div>
      <div class="s-meta">PRODUCT REGISTRY / VINTY</div>
    </div>
    <div class="reg-bar" id="regBar">
      <span class="rb-lbl">Серии нормативов</span>
      <span class="rb-lbl" style="opacity:.55;">клик по строке открывает фильтр реестра</span>
      <span class="rb-count" id="regCount"><?php echo esc_html( number_format_i18n( $otv_cnt ) ); ?> позиций</span>
    </div>
    <div class="reg-hd">
      <span>Норматив</span><span>Наименование</span><span>M / L</span><span>Позиций</span><span>Материал</span><span>Код</span><span>Отрасль</span><span></span>
    </div>
    <div id="regList">
      <div class="reg-group open" data-group="main">
        <button class="reg-group-hd" type="button" aria-expanded="true">
          <span class="rg-code">В</span>
          <span class="rg-name">Винты<small>семейство крепежа</small></span>
          <span class="rg-params">M × L · 1 поз.</span>
          <span class="rg-cnt">1 поз.</span>
          <span class="rg-chev"><svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </button>
        <div class="reg-group-body">
      <a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-6958-1978', $otv_url ) ); ?>">
        <span class="rr-i">ВНТ-6958</span>
        <span class="rr-n">Винт<small>ГОСТ 6958</small></span>
        <span class="rr-dn">M×L</span>
        <span class="rr-pn">1 поз.</span>
        <span class="rr-m">по стандарту</span>
        <span class="rr-g">ГОСТ 6958-1978</span>
        <span class="rr-t"><span class="rr-tag hi">КР</span></span>
        <span class="rr-arr">›</span>
      </a>
        </div>
      </div>
    </div>
    <div class="reg-cta">
      <a class="s10-submit" href="<?php echo esc_url( $otv_url ); ?>" style="display:inline-flex;">Открыть полный реестр — винты →</a>
      <?php if ( $krep && ! is_wp_error( get_term_link( $krep ) ) ) : ?>
        <a class="s10-ghost-link" href="<?php echo esc_url( get_term_link( $krep ) ); ?>" style="margin-left:16px;">← Все семейства крепежа</a>
      <?php endif; ?>
    </div>
  </section>

<section class="s map-outer" id="s02">
    <div class="map-grid"></div>
    <div class="s-hd" style="border-bottom:1px solid rgba(109,140,166,.15);">
      <div class="s-badge s-dark" style="display:flex;"><span class="s-badge-num">02</span><span style="color:rgba(109,140,166,.6);font-family:'DINPro',monospace;font-size:8px;letter-spacing:.28em;text-transform:uppercase;margin-left:14px;">Карта типоисполнений</span></div>
      <div class="s-meta">PRODUCT TYPE MAP</div>
    </div>
    <div class="map-body">
      <div class="map-root">
        <div class="map-root-label">Винты — исполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(1,1fr);max-width:480px;">
        <div class="mg" data-type="main">
          <div class="mg-hd"><div class="mg-code">В</div><div class="mg-cnt">1 поз.</div></div>
          <div class="mg-name">Винты</div>
          <div class="mg-items">
            <div class="mg-item">По ГОСТ 6958<span class="mg-norm">ГОСТ 6958-1978</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">В</span><span class="mg-ftag">1 поз.</span></div>
        </div>
      </div>
    </div>
  </section>

<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор — винты</div>
      <div class="s-meta">VINTY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача на участке</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Винт по ГОСТ 6958</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Винт ГОСТ 6958-1978</div>
            <div class="sg-tags">
              <span class="sg-tag hi">В</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-6958-1978', $otv_url ) ); ?>">К позициям в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Параметры из карточки</div><div class="sg-param">Количество</div><div class="sg-param">Срок</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Нестандартный винт</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Изготовление по КД</div>
            <div class="sg-tags">
              <span class="sg-tag hi">В</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж</div><div class="sg-param">Материал</div><div class="sg-param">Количество</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php promen_render_norm_base( is_tax() ? get_queried_object()->slug : 'vinty' ); ?>

<section class="s s-alt" id="s05">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">05</span>Марки стали и материалы</div>
      <div class="s-meta">STEEL GRADES</div>
    </div>
    <div class="s-body">
      <div class="mat-tbl-wrap reveal">
        <div class="mat-tbl-hd">
          <span>Марка</span>
          <span>Описание</span>
          <span>Темп. среды</span>
          <span>PN макс, МПа</span>
          <span>ГОСТ / ТУ</span>
          <span>Применение</span>
        </div>
        <!-- ROW: Ст20 -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">Ст20</div><div class="mr-std">ГОСТ 1050 / 8734 / 8732</div></div>
          <div class="mr-desc">Углеродистая конструкционная сталь. Основной материал для стандартных трубопроводов ТЭС. Хорошая свариваемость, доступность, широкая нормативная база.</div>
          <div class="mr-temp">до +425°C</div>
          <div class="mr-pn">100 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ГОСТ 8734/8732</div>
          <div class="mr-apps"><span class="mr-app-t hi">ТЭС</span><span class="mr-app-t hi">ГРЭС</span><span class="mr-app-t">Пром</span><span class="mr-app-t">Нефтегаз</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">σв, МПа</div><div class="me-v">≥ 410</div></div>
          <div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">≥ 245</div></div>
          <div class="me-item"><div class="me-k">δ, %</div><div class="me-v">≥ 25</div></div>
          <div class="me-item"><div class="me-k">Темп. max</div><div class="me-v">+425°C</div></div>
          <div class="me-item"><div class="me-k">Свариваемость</div><div class="me-v">Отличная</div></div>
        </div></div>
        <!-- ROW: 09Г2С -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">09Г2С</div><div class="mr-std">ГОСТ 19281 / 19282</div></div>
          <div class="mr-desc">Низколегированная сталь для низких температур. Сохраняет ударную вязкость до −70°C. Применяется в системах нефтегаза, хранилищах СПГ, арктических условиях.</div>
          <div class="mr-temp">−70 / +475°C</div>
          <div class="mr-pn">100 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ГОСТ 19281</div>
          <div class="mr-apps"><span class="mr-app-t hi">Нефтегаз</span><span class="mr-app-t hi">ТЭС</span><span class="mr-app-t">Хим</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">σв, МПа</div><div class="me-v">≥ 490</div></div>
          <div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">≥ 345</div></div>
          <div class="me-item"><div class="me-k">KCV при −70°C</div><div class="me-v">≥ 34 Дж/см²</div></div>
          <div class="me-item"><div class="me-k">Темп. min</div><div class="me-v">−70°C</div></div>
          <div class="me-item"><div class="me-k">Свариваемость</div><div class="me-v">Хорошая</div></div>
        </div></div>
        <!-- ROW: 15ГС -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">15ГС</div><div class="mr-std">ТУ 14-3-460 / ГОСТ 8733</div></div>
          <div class="mr-desc">Низколегированная сталь с марганцем и кремнием. Применяется в трубопроводах ТЭС при давлениях до 16 МПа, в т.ч. в сварных отводах и переходах большого DN.</div>
          <div class="mr-temp">до +475°C</div>
          <div class="mr-pn">100 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ТУ 14-3-460</div>
          <div class="mr-apps"><span class="mr-app-t hi">ТЭС</span><span class="mr-app-t hi">ГРЭС</span><span class="mr-app-t">Нефтехим</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">σв, МПа</div><div class="me-v">≥ 450</div></div>
          <div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">≥ 275</div></div>
          <div class="me-item"><div class="me-k">δ, %</div><div class="me-v">≥ 22</div></div>
          <div class="me-item"><div class="me-k">Темп. max</div><div class="me-v">+475°C</div></div>
          <div class="me-item"><div class="me-k">Свариваемость</div><div class="me-v">Хорошая</div></div>
        </div></div>
        <!-- ROW: 12Х1МФ -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">12Х1МФ</div><div class="mr-std">ТУ 14-3-460 / ТУ 14-3Р-55</div></div>
          <div class="mr-desc">Теплоустойчивая сталь для паровых трубопроводов высокого давления. Основной материал главных паропроводов ТЭС. Устойчива к ползучести при длительных нагрузках.</div>
          <div class="mr-temp">до +570°C</div>
          <div class="mr-pn">160 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ТУ 14-3-460</div>
          <div class="mr-apps"><span class="mr-app-t hi">ТЭС</span><span class="mr-app-t hi">ГРЭС</span><span class="mr-app-t">Главн. паропр.</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">σ при 550°C</div><div class="me-v">≥ 118 МПа</div></div>
          <div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">≥ 275</div></div>
          <div class="me-item"><div class="me-k">Жаростойкость</div><div class="me-v">до 570°C</div></div>
          <div class="me-item"><div class="me-k">Термообработка</div><div class="me-v">Обязательно</div></div>
          <div class="me-item"><div class="me-k">Контроль</div><div class="me-v">УЗК + ВИК</div></div>
        </div></div>
        <!-- ROW: 15Х5М -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">15Х5М</div><div class="mr-std">ГОСТ 550 / ТУ 14-3-561</div></div>
          <div class="mr-desc">Жаропрочная хромомолибденовая сталь. Применяется в нефтепереработке при температурах до 650°C в сероводородсодержащих средах. Высокая коррозионная стойкость.</div>
          <div class="mr-temp">до +650°C</div>
          <div class="mr-pn">100 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ГОСТ 550</div>
          <div class="mr-apps"><span class="mr-app-t hi">Нефтегаз</span><span class="mr-app-t hi">Нефтепер.</span><span class="mr-app-t">Хим</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">Cr, %</div><div class="me-v">4,0–6,0</div></div>
          <div class="me-item"><div class="me-k">Mo, %</div><div class="me-v">0,45–0,60</div></div>
          <div class="me-item"><div class="me-k">Стойк. H₂S</div><div class="me-v">Высокая</div></div>
          <div class="me-item"><div class="me-k">Темп. max</div><div class="me-v">650°C</div></div>
          <div class="me-item"><div class="me-k">Применение</div><div class="me-v">Нефтепер.</div></div>
        </div></div>
        <!-- ROW: 12Х18Н10Т -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">12Х18Н10Т</div><div class="mr-std">ГОСТ 5632 / 9940 / 9941</div></div>
          <div class="mr-desc">Аустенитная нержавеющая сталь с Ti-стабилизацией. Стандартный материал для АЭС и агрессивных сред. Устойчива к МКК, хлоридам и кислотам. Применяется в первом и втором контуре АЭС.</div>
          <div class="mr-temp">до +700°C</div>
          <div class="mr-pn">100 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ГОСТ 5632</div>
          <div class="mr-apps"><span class="mr-app-t hi">АЭС</span><span class="mr-app-t hi">Хим</span><span class="mr-app-t">ТЭС</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">σв, МПа</div><div class="me-v">≥ 540</div></div>
          <div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">≥ 196</div></div>
          <div class="me-item"><div class="me-k">МКК стойкость</div><div class="me-v">Высокая (Ti)</div></div>
          <div class="me-item"><div class="me-k">Темп. max</div><div class="me-v">700°C</div></div>
          <div class="me-item"><div class="me-k">Контроль АЭС</div><div class="me-v">РК + УЗК + ВИК</div></div>
        </div></div>
        <!-- ROW: 08Х18Н10Т -->
        <div class="mat-r" onclick="toggleMat(this)">
          <div><div class="mr-g">08Х18Н10Т</div><div class="mr-std">ГОСТ 5632 / 9940</div></div>
          <div class="mr-desc">Аустенитная нержавеющая сталь с пониженным содержанием углерода. Улучшенная стойкость к МКК по сравнению с 12Х18Н10Т. Применяется в химической и нефтехимической промышленности.</div>
          <div class="mr-temp">до +600°C</div>
          <div class="mr-pn">100 МПа</div>
          <div style="font-family:'DINPro',monospace;font-size:8.5px;color:var(--g1);">ГОСТ 5632</div>
          <div class="mr-apps"><span class="mr-app-t hi">Хим</span><span class="mr-app-t hi">Нефтехим</span><span class="mr-app-t">АЭС</span></div>
        </div>
        <div class="mat-expand"><div class="me-grid">
          <div class="me-item"><div class="me-k">σв, МПа</div><div class="me-v">≥ 510</div></div>
          <div class="me-item"><div class="me-k">σт, МПа</div><div class="me-v">≥ 196</div></div>
          <div class="me-item"><div class="me-k">C, %</div><div class="me-v">≤ 0,08</div></div>
          <div class="me-item"><div class="me-k">МКК стойкость</div><div class="me-v">Высокая</div></div>
          <div class="me-item"><div class="me-k">Свариваемость</div><div class="me-v">Отличная</div></div>
        </div></div>
      </div>
    </div>
  </section>

<section class="s" id="s06">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">06</span>Области применения</div>
      <div class="s-meta">APPLICATION SECTORS</div>
    </div>
    <div class="s-body">
      <div class="app-grid reveal">
        <div class="app-c">
          <div class="app-bg">ТЭС</div>
          <div class="app-code">Сектор 01</div>
          <div class="app-h">Тепловые электростанции</div>
          <p class="app-p">Главные паропроводы, питательные и дренажные тракты. Пар высокого давления, критические участки с PN до 160 МПа и T до 570°C.</p>
          <div class="app-params">
            <div class="app-pr"><span class="app-pk">Давление, МПа</span><span class="app-pv">до 160</span></div>
            <div class="app-pr"><span class="app-pk">Температура, °C</span><span class="app-pv">до +570</span></div>
            <div class="app-pr"><span class="app-pk">Типичный материал</span><span class="app-pv">Ст20, 12Х1МФ</span></div>
          </div>
        </div>
        <div class="app-c">
          <div class="app-bg">АЭС</div>
          <div class="app-code">Сектор 02</div>
          <div class="app-h">Атомные электростанции</div>
          <p class="app-p">Системы первого и второго контура. Повышенные требования к трассируемости плавки, ультразвуковому контролю, документации. Нержавеющие стали класса 12Х18Н10Т.</p>
          <div class="app-params">
            <div class="app-pr"><span class="app-pk">Давление, МПа</span><span class="app-pv">6–160</span></div>
            <div class="app-pr"><span class="app-pk">Материал</span><span class="app-pv">12Х18Н10Т</span></div>
            <div class="app-pr"><span class="app-pk">Контроль</span><span class="app-pv">РК + УЗК + ВИК</span></div>
          </div>
        </div>
        <div class="app-c">
          <div class="app-bg">НГ</div>
          <div class="app-code">Сектор 03</div>
          <div class="app-h">Нефтегаз</div>
          <p class="app-p">Промысловые и технологические трубопроводы, сепарационные системы, газоперерабатывающие установки. Хладостойкие стали 09Г2С для условий Сибири и арктики.</p>
          <div class="app-params">
            <div class="app-pr"><span class="app-pk">Темп. мин, °C</span><span class="app-pv">−70</span></div>
            <div class="app-pr"><span class="app-pk">Материал</span><span class="app-pv">09Г2С, 15Х5М</span></div>
            <div class="app-pr"><span class="app-pk">Среда</span><span class="app-pv">Нефть, газ, H₂S</span></div>
          </div>
        </div>
        <div class="app-c">
          <div class="app-bg">ХП</div>
          <div class="app-code">Сектор 04</div>
          <div class="app-h">Химическая промышленность</div>
          <p class="app-p">Трубопроводы агрессивных сред: кислоты, щёлочи, растворители, технологические жидкости. Аустенитные нержавеющие стали с высокой стойкостью к МКК.</p>
          <div class="app-params">
            <div class="app-pr"><span class="app-pk">Материал</span><span class="app-pv">12Х18Н10Т, 08Х18Н10</span></div>
            <div class="app-pr"><span class="app-pk">Контроль</span><span class="app-pv">РК + ВИК + МКК</span></div>
          </div>
        </div>
        <div class="app-c">
          <div class="app-bg">ГР</div>
          <div class="app-code">Сектор 05</div>
          <div class="app-h">ГРЭС и крупные ТЭС</div>
          <p class="app-p">Главные паропроводы и распределительные коллекторы крупных энергоблоков. Теплоустойчивые стали 12Х1МФ, повышенные требования к гидравлическим испытаниям и протоколам НК.</p>
          <div class="app-params">
            <div class="app-pr"><span class="app-pk">Давление, МПа</span><span class="app-pv">до 160</span></div>
            <div class="app-pr"><span class="app-pk">Температура, °C</span><span class="app-pv">до +570</span></div>
            <div class="app-pr"><span class="app-pk">Материал</span><span class="app-pv">12Х1МФ, Ст20</span></div>
          </div>
        </div>
        <div class="app-c">
          <div class="app-bg">ЖКХ</div>
          <div class="app-code">Сектор 06</div>
          <div class="app-h">ЖКХ и теплосети</div>
          <p class="app-p">Тепловые сети, котельные установки, системы горячего водоснабжения. Стандартные исполнения по ГОСТ 17375–17380, DN 15–600, PN 6–25 МПа.</p>
          <div class="app-params">
            <div class="app-pr"><span class="app-pk">Давление, МПа</span><span class="app-pv">6–25</span></div>
            <div class="app-pr"><span class="app-pk">Температура, °C</span><span class="app-pv">до +200</span></div>
            <div class="app-pr"><span class="app-pk">Материал</span><span class="app-pv">Ст20, 09Г2С</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

<section class="s s-dark qc-wrap" id="s07">
    <div class="qc-scanline"></div>
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">07</span>Контроль качества</div>
      <div class="s-meta">QC ROUTE / TRACEABILITY</div>
    </div>
    <div class="qc-grid">
      <div class="qc-stages reveal" id="qcStages">
        <div class="qc-s" data-stage="material">
          <div class="qs-n">01</div>
          <div><div class="qs-h">Входной контроль металла</div><div class="qs-d">Проверка сертификатов 3.1, химического состава, механических свойств. Сверка марки стали с конструкторской документацией.</div></div>
        </div>
        <div class="qc-s" data-stage="standard">
          <div class="qs-n">02</div>
          <div><div class="qs-h">Проверка нормативной базы</div><div class="qs-d">Соответствие ГОСТ, ОСТ, ТУ и КД заказчика. Верификация допусков, радиусов гиба, углов и геометрических параметров.</div></div>
        </div>
        <div class="qc-s" data-stage="heat">
          <div class="qs-n">03</div>
          <div><div class="qs-h">Идентификация плавки</div><div class="qs-d">Присвоение номера плавки, маркировка заготовки. Прослеживаемость от сертификата металла до готового изделия.</div></div>
        </div>
        <div class="qc-s" data-stage="dn">
          <div class="qs-n">04</div>
          <div><div class="qs-h">Операционный контроль</div><div class="qs-d">Контроль на каждой технологической операции: штамповка, гибка, сварка, термообработка. Журнал операций ОТК.</div></div>
        </div>
        <div class="qc-s" data-stage="nk">
          <div class="qs-n">05</div>
          <div><div class="qs-h">Неразрушающий контроль</div><div class="qs-d">УЗК, ВИК, РК сварных швов, капиллярный контроль. Объём НК согласно ПБ 03-585-03 для объектов АЭС.</div></div>
        </div>
        <div class="qc-s" data-stage="geo">
          <div class="qs-n">06</div>
          <div><div class="qs-h">Проверка геометрии</div><div class="qs-d">Контроль размеров на КИМ, проверка радиуса гиба, углов, толщины стенки. Допуск ±0,5 мм на критических участках.</div></div>
        </div>
        <div class="qc-s" data-stage="mark">
          <div class="qs-n">07</div>
          <div><div class="qs-h">Маркировка изделия</div><div class="qs-d">Нанесение маркировки: завод, марка стали, номер плавки, DN/PN, дата изготовления. Клеймение по ГОСТ 4666.</div></div>
        </div>
        <div class="qc-s" data-stage="docs">
          <div class="qs-n">08</div>
          <div><div class="qs-h">Паспорт и отгрузочные документы</div><div class="qs-d">Формирование паспорта изделия, протоколов НК, сертификата металла, акта гидравлических испытаний. Комплект для заказчика.</div></div>
        </div>
      </div>
      <div class="qc-docs reveal">
        <div class="qc-dh">Сопроводительная документация</div>
        <div class="doc-c"><div class="dc-n">01</div><div class="dc-t">Паспорт изделия</div><div class="dc-d">Полные технические характеристики, маркировка, результаты контроля, подпись ОТК. Обязателен для всех позиций СДТ.</div></div>
        <div class="doc-c"><div class="dc-n">02</div><div class="dc-t">Сертификат на металл 3.1</div><div class="dc-d">Химический состав, механические свойства, номер плавки. Прослеживаемость от металлургического завода.</div></div>
        <div class="doc-c"><div class="dc-n">03</div><div class="dc-t">Протоколы неразрушающего контроля</div><div class="dc-d">УЗК, ВИК, РК — по объёму, установленному нормативной базой и требованиями заказчика.</div></div>
        <div class="doc-c"><div class="dc-n">04</div><div class="dc-t">Акт гидравлических испытаний</div><div class="dc-d">Испытание давлением 1,5×PN. Протокол для критических участков паропроводов и сосудов давления.</div></div>
      </div>
    </div>
  </section>

<section class="s" id="s08">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">08</span>Производственные возможности</div>
      <div class="s-meta">MANUFACTURING CAPABILITIES</div>
    </div>
    <div class="prod-layout reveal">
      <div class="prod-main">
        <h2 class="prod-h">Собственное <em>производство</em><br>полного цикла</h2>
        <p class="prod-lead">Завод «Промышленная Энергетика» изготавливает соединительные детали трубопровода методами горячей штамповки, гибки, сварки и механической обработки. Производственная база — Красноярский край, Западная Сибирь.</p>
        <div class="prod-caps">
          <div class="pc"><div class="pc-h">Штамповка и гибка</div><p class="pc-p">Горячая штамповка отводов, тройников, переходов. Гибка из трубных заготовок R = 1,5–5DN. DN 15–1400.</p></div>
          <div class="pc"><div class="pc-h">Сварные конструкции</div><p class="pc-p">Секторные отводы, крупногабаритные тройники, переходы сварные. ОСТ 34-42-621/622/632 для энергетики.</p></div>
          <div class="pc"><div class="pc-h">Термообработка</div><p class="pc-p">Нормализация, отпуск, закалка для теплоустойчивых сталей. Контроль твёрдости и структуры металла.</p></div>
          <div class="pc"><div class="pc-h">Нестандартные изделия</div><p class="pc-p">Изготовление по КД заказчика. Любые марки стали, геометрические параметры, согласование по ТЗ.</p></div>
        </div>
      </div>
      <div class="prod-side">
        <div class="prod-side-lbl">Производственные показатели</div>
        <div class="pm-r"><span class="pm-k">DN диапазон, мм</span><span class="pm-v">15–1400</span></div>
        <div class="pm-r"><span class="pm-k">PN максимум, МПа</span><span class="pm-v">160</span></div>
        <div class="pm-r"><span class="pm-k">Марок стали</span><span class="pm-v">7+</span></div>
        <div class="pm-r"><span class="pm-k">Типов исполнений</span><span class="pm-v">18+</span></div>
        <div class="pm-r"><span class="pm-k">Срок изготовления</span><span class="pm-v">от 14 дн.</span></div>
        <div class="pm-r"><span class="pm-k">Сертификация</span><span class="pm-v">ISO 9001</span></div>
      </div>
    </div>
  </section>


<section class="s" id="s09">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">09</span>Порядок заказа</div>
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

<?php include __DIR__ . '/parts/kb-vinty.php'; ?>

</div><!-- /.pg -->


<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на винты">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на винты</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Винты" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 7798, ГОСТ 9066, ОСТ 26-2040…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">Резьба M</label><input id="om-dn" name="dn" type="text" placeholder="M16" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Длина L, мм</label><input id="om-pn" name="pn" type="text" placeholder="80" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка / класс прочности</label><input id="om-mat" name="material" type="text" placeholder="Ст20 / 5.6 / 8.8…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-qty">Количество, шт</label><input id="om-qty" name="qty" type="text" placeholder="100" autocomplete="off"></div>
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
