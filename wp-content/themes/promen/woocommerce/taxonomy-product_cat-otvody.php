<?php
/**
 * Лендинг категории «Отводы» — шаблон sdt.html второго уровня вложенности.
 * Сгенерирован из taxonomy-product_cat-sdt.php: hero, «Подбор» и «Карта
 * типоисполнений» адаптированы под отводы, остальные секции сохранены.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$term     = get_queried_object();
$crumbs   = promen_breadcrumbs();
$shop_url = wc_get_page_permalink( 'shop' );
$otv      = get_term_by( 'slug', 'otvody', 'product_cat' );
$otv_url  = add_query_arg( 'group', 'otvody', $shop_url ); // реестр отводов на корне каталога
$otv_cnt  = $otv ? (int) $otv->count : 0;
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
      <div class="hero-eyebrow">СДТ · Семейство изделий — изготовление под заказ</div>
      <h1 class="hero-h1">Отводы<br><em>стальные</em><br>приварные</h1>
      <p class="hero-desc">Крутоизогнутые, гнутые, секторные отводы и колена с опорой для поворота трубопроводов ТЭС, АЭС, нефтегаза и химической промышленности. Восемь серий по ГОСТ, ОСТ и СТО ЦКТИ, углы 15–180°, семь марок стали. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v">DN 6–1400</span><span class="hp-k">Типоразмеры</span></div>
        <div class="hp"><span class="hp-v">15–180°</span><span class="hp-k">Углы поворота</span></div>
        <div class="hp"><span class="hp-v">7 марок</span><span class="hp-k">Сталей</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
        <a class="s10-ghost-link" href="<?php echo esc_url( $otv_url ); ?>">Открыть полный реестр</a>
      </div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / OTVODY SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">6 — 1400</span></div>
        <div class="hud-row"><span class="hud-rk">Углы поворота</span><span class="hud-rv">15° — 180°</span></div>
        <div class="hud-row"><span class="hud-rk">Температура среды, °C</span><span class="hud-rv">−70 — +700</span></div>
        <div class="hud-row"><span class="hud-rk">Радиус гиба R / DN</span><span class="hud-rv">1,5 — 5,0</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 17375 / 30753</span><span class="hud-rv live">Крутоизогнутые</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 22793 / 22818</span><span class="hud-rv live">Ру до 100 МПа</span></div>
        <div class="hud-row"><span class="hud-rk">СТО ЦКТИ 321.01–.05</span><span class="hud-rv live">Гнутые для ТЭС</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
      </div>
    </div>
  </div>


<section class="s s-alt" id="s01">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">01</span>Реестр исполнений</div>
      <div class="s-meta">PRODUCT REGISTRY / OTVODY</div>
    </div>
    <div class="reg-bar" id="regBar">
      <span class="rb-lbl">Типоисполнения</span>
      <span class="rb-lbl" style="opacity:.55;">4 группы · клик по заголовку сворачивает группу</span>
      <span class="rb-count" id="regCount">8 серий · 3 120 позиций</span>
    </div>
    <div class="reg-hd">
      <span>Норматив</span><span>Наименование</span><span>DN</span><span>Позиций</span><span>Материал</span><span>Код</span><span>Отрасль</span><span></span>
    </div>
    <div id="regList">
      <div class="reg-group open" data-group="ok">
        <button class="reg-group-hd" type="button" aria-expanded="true">
          <span class="rg-code">ОК</span>
          <span class="rg-name">Крутоизогнутые<small>штампованные · R = 1,5DN (3D) и R ≈ DN (2D) · 45–180°</small></span>
          <span class="rg-params">DN 15–800 · PN по стенке</span>
          <span class="rg-cnt">1 641 поз.</span>
          <span class="rg-chev"><svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </button>
        <div class="reg-group-body">
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-17375-2001', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-001</span>
        <span class="rr-n">Отвод крутоизогнутый штампованный<small>R = 1,5DN · тип 3D</small></span>
        <span class="rr-dn">15–800</span>
        <span class="rr-pn">1025 поз.</span>
        <span class="rr-m">09Г2С, 12Х18Н10Т, 20</span>
        <span class="rr-g">ГОСТ 17375-2001</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-30753-2001', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-002</span>
        <span class="rr-n">Отвод крутоизогнутый типа 2D<small>R ≈ DN · штампованный</small></span>
        <span class="rr-dn">50–800</span>
        <span class="rr-pn">616 поз.</span>
        <span class="rr-m">09Г2С, 12Х18Н10Т, 20</span>
        <span class="rr-g">ГОСТ 30753-2001</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
        </div>
      </div>
      <div class="reg-group open" data-group="og">
        <button class="reg-group-hd" type="button" aria-expanded="true">
          <span class="rg-code">ОГ</span>
          <span class="rg-name">Гнутые<small>из трубной заготовки · R = 1,5–5DN · углы от 15°</small></span>
          <span class="rg-params">DN 6–300 · Ру до 100 МПа</span>
          <span class="rg-cnt">1 290 поз.</span>
          <span class="rg-chev"><svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </button>
        <div class="reg-group-body">
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22793-1983', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-003</span>
        <span class="rr-n">Отвод гнутый на Ру до 100 МПа<small>высокое давление</small></span>
        <span class="rr-dn">6–200</span>
        <span class="rr-pn">579 поз.</span>
        <span class="rr-m">09Г2С, 20Х3МВФ, 20</span>
        <span class="rr-g">ГОСТ 22793-1983</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'sto-321-01', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-005</span>
        <span class="rr-n">Отвод гнутый СТО ЦКТИ 321.01<small>для трубопроводов ТЭС</small></span>
        <span class="rr-dn">10–300</span>
        <span class="rr-pn">100 поз.</span>
        <span class="rr-m">15ГС</span>
        <span class="rr-g">СТО 321.01</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'sto-321-02', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-006</span>
        <span class="rr-n">Отвод гнутый СТО ЦКТИ 321.02<small>для трубопроводов ТЭС</small></span>
        <span class="rr-dn">10–300</span>
        <span class="rr-pn">100 поз.</span>
        <span class="rr-m">15ГС, 20</span>
        <span class="rr-g">СТО 321.02</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'sto-321-05', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-007</span>
        <span class="rr-n">Отвод гнутый СТО ЦКТИ 321.05<small>для трубопроводов ТЭС</small></span>
        <span class="rr-dn">0.63–300</span>
        <span class="rr-pn">511 поз.</span>
        <span class="rr-m">12Х1МФ</span>
        <span class="rr-g">СТО 321.05</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
        </div>
      </div>
      <div class="reg-group open" data-group="ko">
        <button class="reg-group-hd" type="button" aria-expanded="true">
          <span class="rg-code">КО</span>
          <span class="rg-name">Колена с опорой<small>опорная пята · высокое давление</small></span>
          <span class="rg-params">DN 6–200 · Ру до 100 МПа</span>
          <span class="rg-cnt">109 поз.</span>
          <span class="rg-chev"><svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </button>
        <div class="reg-group-body">
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22818-1983', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-004</span>
        <span class="rr-n">Колено с опорой<small>для трубопроводов высокого давления</small></span>
        <span class="rr-dn">6–200</span>
        <span class="rr-pn">109 поз.</span>
        <span class="rr-m">09Г2С, 20Х3МВФ, 20</span>
        <span class="rr-g">ГОСТ 22818-1983</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
        </div>
      </div>
      <div class="reg-group open" data-group="oss">
        <button class="reg-group-hd" type="button" aria-expanded="true">
          <span class="rg-code">ОСС</span>
          <span class="rg-name">Сварные секторные<small>сборка из сегментов · крупный DN</small></span>
          <span class="rg-params">DN 500–1400 · R = 1,5DN</span>
          <span class="rg-cnt">80 поз.</span>
          <span class="rg-chev"><svg width="10" height="10" viewBox="0 0 10 10" fill="none"><path d="M2 3.5 5 6.5 8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </button>
        <div class="reg-group-body">
<a class="reg-r" data-type="otv" href="<?php echo esc_url( add_query_arg( 'gost', 'ost-36-21-77', $otv_url ) ); ?>">
        <span class="rr-i">ОТВ-008</span>
        <span class="rr-n">Отвод сварной секторный<small>крупные диаметры</small></span>
        <span class="rr-dn">500–1400</span>
        <span class="rr-pn">80 поз.</span>
        <span class="rr-m">ВСт3сп</span>
        <span class="rr-g">ОСТ 36-21-77</span>
        <span class="rr-t"><span class="rr-tag hi">АЭС</span><span class="rr-tag">ТЭС</span></span>
        <span class="rr-arr">›</span>
      </a>
        </div>
      </div>
    </div>
    <div class="reg-cta">
      <a class="s10-submit" href="<?php echo esc_url( $otv_url ); ?>" style="display:inline-flex;">Открыть полный реестр отводов →</a>
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
        <div class="map-root-label">Отводы — типоисполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(4,1fr);">
        <!-- КРУТОИЗОГНУТЫЕ -->
        <div class="mg" data-type="ok">
          <div class="mg-hd">
            <div class="mg-code">ОК</div>
            <div class="mg-cnt">1 641 поз.</div>
          </div>
          <div class="mg-name">Крутоизогнутые</div>
          <div class="mg-items">
            <div class="mg-item">Тип 3D · R = 1,5DN · 45/60/90/180°<span class="mg-norm">ГОСТ 17375-2001</span></div>
            <div class="mg-item">Тип 2D · R ≈ DN<span class="mg-norm">ГОСТ 30753-2001</span></div>
            <div class="mg-item">Штамповка / протяжка, бесшовные<span class="mg-norm">приварные</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 15–800</span><span class="mg-ftag">Основной тип</span></div>
        </div>
        <!-- ГНУТЫЕ -->
        <div class="mg" data-type="og">
          <div class="mg-hd">
            <div class="mg-code">ОГ</div>
            <div class="mg-cnt">1 290 поз.</div>
          </div>
          <div class="mg-name">Гнутые</div>
          <div class="mg-items">
            <div class="mg-item">R = 3,5–5DN, углы от 15°<span class="mg-norm">СТО ЦКТИ 321.01/.02/.05</span></div>
            <div class="mg-item">На давление до 100 МПа<span class="mg-norm">ГОСТ 22793-83</span></div>
            <div class="mg-item">Главные паропроводы ТЭС<span class="mg-norm">15ГС · 12Х1МФ</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 6–300</span><span class="mg-ftag">Мин. потери</span></div>
        </div>
        <!-- КОЛЕНА -->
        <div class="mg" data-type="ko">
          <div class="mg-hd">
            <div class="mg-code">КО</div>
            <div class="mg-cnt">109 поз.</div>
          </div>
          <div class="mg-name">Колена с опорой</div>
          <div class="mg-items">
            <div class="mg-item">Опорная пята, высокое давление<span class="mg-norm">ГОСТ 22818-83</span></div>
            <div class="mg-item">Исполнения 1–4<span class="mg-norm">Ру до 100 МПа</span></div>
            <div class="mg-item">20 · 09Г2С · 20Х3МВФ<span class="mg-norm">поднадзорные объекты</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 6–200</span><span class="mg-ftag">ТР ТС 032</span></div>
        </div>
        <!-- СЕКТОРНЫЕ -->
        <div class="mg" data-type="oss">
          <div class="mg-hd">
            <div class="mg-code">ОСС</div>
            <div class="mg-cnt">80 поз.</div>
          </div>
          <div class="mg-name">Сварные секторные</div>
          <div class="mg-items">
            <div class="mg-item">Сборка из сегментов, R = 1,5DN<span class="mg-norm">ОСТ 36-21-77</span></div>
            <div class="mg-item">Крупные диаметры<span class="mg-norm">DN 500–1400</span></div>
            <div class="mg-item">НК сварных швов: ВИК / УЗК / РК<span class="mg-norm">по объёму заказа</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 500–1400</span><span class="mg-ftag">ТЭС / ГРЭС</span></div>
        </div>

      </div>
      
    </div>
  </section>

<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор отвода</div>
      <div class="s-meta">OTVODY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача в трубопроводе</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Повернуть трубу на стандартный угол 45° / 60° / 90° / 180°</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы крутоизогнутые штампованные — R = 1,5DN (тип 3D) или R ≈ DN (тип 2D)</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17375-2001</span><span class="sg-tag hi">ГОСТ 30753-2001</span><span class="sg-tag">DN 15–800</span><span class="sg-tag">1 641 позиция</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-17375-2001', $otv_url ) ); ?>">К крутоизогнутым в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN и PN трубопровода</div>
              <div class="sg-param">Угол поворота и радиус: 1,5DN (3D) или DN (2D)</div>
              <div class="sg-param">Наружный диаметр × толщина стенки (напр. 108×4)</div>
              <div class="sg-param">Марка стали и требования к среде</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Снизить сопротивление и эрозию в паропроводе ТЭС</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы гнутые с увеличенным радиусом — R = 3,5–5DN, углы от 15°</div>
            <div class="sg-tags">
              <span class="sg-tag hi">СТО ЦКТИ 321.01/321.02/321.05</span><span class="sg-tag">DN 10–300</span><span class="sg-tag">711 позиций</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'sto-321-05', $otv_url ) ); ?>">К гнутым СТО ЦКТИ в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN, PN и температура среды</div>
              <div class="sg-param">Угол поворота (в т.ч. нестандартный 15–30°)</div>
              <div class="sg-param">Марка стали: 15ГС, 12Х1МФ — для паропроводов</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Трубопровод высокого давления — свыше 10 до 100 МПа</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы гнутые Ру 100 и колена с опорой — усиленная стенка, опорная пята</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 22793-83</span><span class="sg-tag hi">ГОСТ 22818-83</span><span class="sg-tag">DN 6–200</span><span class="sg-tag">688 позиций</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22793-1983', $otv_url ) ); ?>">К отводам Ру 100 в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Рабочее давление Ру и температура</div>
              <div class="sg-param">Исполнение по стандарту (1–4)</div>
              <div class="sg-param">Наличие опоры, марка стали (20, 09Г2С, 20Х3МВФ)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Крупный диаметр — DN 500–1400, где штамповка недоступна</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы сварные секторные — сборка из сегментов, R = 1,5DN</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ОСТ 36-21-77</span><span class="sg-tag">DN 500–1400</span><span class="sg-tag">80 позиций</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'ost-36-21-77', $otv_url ) ); ?>">К секторным в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN, толщина стенки, угол</div>
              <div class="sg-param">Число секторов / допуски по проекту</div>
              <div class="sg-param">Объём НК сварных швов (ВИК / УЗК / РК)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 05</div>
            <div class="sg-task-h">Нестандартный угол, радиус или сталь вне каталога</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Изготовление по КД заказчика — гибка под произвольный угол, спецстали</div>
            <div class="sg-tags">
              <span class="sg-tag hi">По чертежу</span><span class="sg-tag">ТУ 24.20.40</span><span class="sg-tag">Согласование 1–3 дня</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж или эскиз с размерами</div>
              <div class="sg-param">Среда, давление, температура</div>
              <div class="sg-param">Количество и срок поставки</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<section class="s s-dark" id="s04">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">04</span>Нормативная база</div>
      <div class="s-meta">REGULATORY REGISTRY</div>
    </div>
    <div class="s-body" style="padding-top:28px;">
      <div class="norm-tab-bar reveal" id="normTabs">
        <button class="nt on" data-ng="otv">Отводы</button>
        <button class="nt" data-ng="troy">Тройники</button>
        <button class="nt" data-ng="pereh">Переходы</button>
        <button class="nt" data-ng="dn">Днища / Заглушки</button>
        <button class="nt" data-ng="aes">АЭС</button>
        <button class="nt" data-ng="gen">Общие</button>
      </div>
      <!-- ОТВОДЫ -->
      <div class="norm-group nactive" id="ng-otv">
        <div class="ng-label">Нормативная база — Отводы</div>
        <div class="norm-grid2">
          <div class="nc reveal">
            <div class="nc-code">ГОСТ 17375-2001</div>
            <div class="nc-title">Отводы крутоизогнутые бесшовные приварные</div>
            <div class="nc-desc">Штампованные отводы из углеродистой и низколегированной стали. Углы 45°, 90°, 180°. Радиус R = 1,5DN. DN 15–500.</div>
            <div class="nc-tags"><span class="nc-tag">Крутоизогнутые</span><span class="nc-tag">DN 15–500</span><span class="nc-tag">Действующий</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ ещё нормативы</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ГОСТ 30753 — общие ТУ на детали трубопроводов</div>
                <div class="nc-full-item">СТО ЦКТИ 321.03-2009 — для трубопроводов ТЭС</div>
                <div class="nc-full-item">СТО ЦКТИ 321.14-2009 — теплоэнергетические применения</div>
                <div class="nc-full-item">СТО 79814898-111-2009 — энергетические системы</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
                <div class="nc-full-item">ТР ТС 032/2013 — обязателен при PN &gt; 0,05 МПа</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2001</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ОСТ 36-21-77</div>
            <div class="nc-title">Отводы секторные / сварные для трубопроводов</div>
            <div class="nc-desc">Сварные секторные отводы больших диаметров. DN 100–1400, PN 6–40 МПа. Паровые и водяные тракты ТЭС и ГРЭС.</div>
            <div class="nc-tags"><span class="nc-tag">Секторные</span><span class="nc-tag">DN 100–1400</span><span class="nc-tag">ТЭС / ГРЭС</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ ещё нормативы</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ОСТ 34.10.752-97 — трубопроводы тепловых электростанций</div>
                <div class="nc-full-item">ОСТ 36-41-81 — сварные детали трубопроводов</div>
                <div class="nc-full-item">ТС-583.000 — конкретное исполнение</div>
                <div class="nc-full-item">СТО 79814898 112-2009 — энергетические системы</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 1977</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">СТО ЦКТИ 321.01–321.06</div>
            <div class="nc-title">Отводы гнутые для трубопроводов ТЭС</div>
            <div class="nc-desc">Серия стандартов ЦКТИ для гнутых отводов: R = 3,5–5DN. Применение в главных паропроводах и тепловых трактах ТЭС. 6 документов серии 2009 г.</div>
            <div class="nc-tags"><span class="nc-tag">Гнутые</span><span class="nc-tag">ТЭС</span><span class="nc-tag">6 СТО ЦКТИ</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ полный список</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ГОСТ 22793-83 — гнутые трубы и отводы</div>
                <div class="nc-full-item">ГОСТ 24950-81 — отводы гнутые из труб</div>
                <div class="nc-full-item">ОСТ 36-42-81 — гнутые отводы</div>
                <div class="nc-full-item">СТО ЦКТИ 321.01-2009 — тип 01</div>
                <div class="nc-full-item">СТО ЦКТИ 321.02-2009 — тип 02</div>
                <div class="nc-full-item">СТО ЦКТИ 321.03-2009 — тип 03</div>
                <div class="nc-full-item">СТО ЦКТИ 321.04-2009 — тип 04</div>
                <div class="nc-full-item">СТО ЦКТИ 321.05-2009 — тип 05</div>
                <div class="nc-full-item">СТО ЦКТИ 321.06-2009 — тип 06</div>
                <div class="nc-full-item">СТО 79814898 113-2009 — энерг. системы</div>
                <div class="nc-full-item">СТО 79814898 750-2014 — обновлённая редакция</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2009</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ОСТ 36-20-77</div>
            <div class="nc-title">Отводы штампосварные</div>
            <div class="nc-desc">Штампосварные отводы методом горячей штамповки. DN 25–400, R = 1,5DN. Применение в трубопроводах ТЭС и нефтехима.</div>
            <div class="nc-tags"><span class="nc-tag">Штампосварные</span><span class="nc-tag">DN 25–400</span><span class="nc-tag">Действующий</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ ещё нормативы</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ГОСТ 17375-2001 — базовый стандарт на форму</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
                <div class="nc-full-item">ТР ТС 032/2013 — обязателен при PN &gt; 0,05 МПа</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 1977</div>
          </div>
        </div>
      </div>
      <!-- ТРОЙНИКИ -->
      <div class="norm-group" id="ng-troy">
        <div class="ng-label">Нормативная база — Тройники</div>
        <div class="norm-grid2 cols-1">
          <div class="nc reveal">
            <div class="nc-code">ГОСТ 17376-2001 + СТО ЦКТИ 720.01–720.29</div>
            <div class="nc-title">Тройники бесшовные и сварные, в т.ч. для ТЭС по СТО ЦКТИ</div>
            <div class="nc-desc">ГОСТ 17376-2001 — равнопроходные и переходные тройники DN 15–500. ОСТ 36-24-77 — сварные большого диаметра для ТЭС. Серия СТО ЦКТИ 720 содержит 29 стандартов (720.01–720.29, 2009–2011) для специсполнений на объектах тепловой энергетики.</div>
            <div class="nc-tags"><span class="nc-tag">Равнопроход.</span><span class="nc-tag">Переходные</span><span class="nc-tag">Сварные</span><span class="nc-tag">ТЭС</span><span class="nc-tag">29 СТО ЦКТИ</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ все нормативы (35 документов)</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ГОСТ 17376-2001 — тройники бесшовные приварные</div>
                <div class="nc-full-item">ОСТ 36-24-77 — тройники сварные</div>
                <div class="nc-full-item">ОСТ 34.10.762-97 — тройники для ТЭС (равнопр.)</div>
                <div class="nc-full-item">ОСТ 34.10.763-97 — тройники для ТЭС (переходные)</div>
                <div class="nc-full-item">ОСТ 34.10.764-97 — тройники для ТЭС (тип 3)</div>
                <div class="nc-full-item">ТС-588.000, ТС-589.000, ТС-590.000 — конкретные исполнения</div>
                <div class="nc-full-item">СТО ЦКТИ 720.01-2009 — тип 01 (равнопроходной)</div>
                <div class="nc-full-item">СТО ЦКТИ 720.02-2009 — тип 02</div>
                <div class="nc-full-item">СТО ЦКТИ 720.03-2009 — тип 03</div>
                <div class="nc-full-item">СТО ЦКТИ 720.04-2009 — тип 04</div>
                <div class="nc-full-item">СТО ЦКТИ 720.05-2009 — тип 05</div>
                <div class="nc-full-item">СТО ЦКТИ 720.06-2009 — тип 06</div>
                <div class="nc-full-item">СТО ЦКТИ 720.07-2009 — тип 07</div>
                <div class="nc-full-item">СТО ЦКТИ 720.08-2009 — тип 08</div>
                <div class="nc-full-item">СТО ЦКТИ 720.09-2009 — тип 09</div>
                <div class="nc-full-item">СТО ЦКТИ 720.10-2009 — тип 10</div>
                <div class="nc-full-item">СТО ЦКТИ 720.11-2009 — тип 11</div>
                <div class="nc-full-item">СТО ЦКТИ 720.12-2009 — тип 12</div>
                <div class="nc-full-item">СТО ЦКТИ 720.13-2009 — тип 13</div>
                <div class="nc-full-item">СТО ЦКТИ 720.14-2009 — тип 14</div>
                <div class="nc-full-item">СТО ЦКТИ 720.15-2009 — тип 15</div>
                <div class="nc-full-item">СТО ЦКТИ 720.16-2009 — тип 16</div>
                <div class="nc-full-item">СТО ЦКТИ 720.17-2009 — тип 17</div>
                <div class="nc-full-item">СТО ЦКТИ 720.18-2009 — тип 18</div>
                <div class="nc-full-item">СТО ЦКТИ 720.19-2009 — тип 19</div>
                <div class="nc-full-item">СТО ЦКТИ 720.20-2009 — тип 20</div>
                <div class="nc-full-item">СТО ЦКТИ 720.21-2009 — тип 21</div>
                <div class="nc-full-item">СТО ЦКТИ 720.22-2009 — тип 22</div>
                <div class="nc-full-item">СТО ЦКТИ 720.23-2009 — тип 23</div>
                <div class="nc-full-item">СТО ЦКТИ 720.24-2009 — тип 24</div>
                <div class="nc-full-item">СТО ЦКТИ 720.25-2011 — тип 25</div>
                <div class="nc-full-item">СТО ЦКТИ 720.26-2011 — тип 26</div>
                <div class="nc-full-item">СТО ЦКТИ 720.27-2011 — тип 27</div>
                <div class="nc-full-item">СТО ЦКТИ 720.28-2011 — тип 28</div>
                <div class="nc-full-item">СТО ЦКТИ 720.29-2011 — тип 29</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2001–2011</div>
          </div>
        </div>
      </div>
      <!-- ПЕРЕХОДЫ -->
      <div class="norm-group" id="ng-pereh">
        <div class="ng-label">Нормативная база — Переходы</div>
        <div class="norm-grid2 cols-1">
          <div class="nc reveal">
            <div class="nc-code">ГОСТ 17378-2001 + СТО ЦКТИ 318.01–318.03</div>
            <div class="nc-title">Переходы концентрические и эксцентрические; по СТО ЦКТИ для ТЭС</div>
            <div class="nc-desc">ГОСТ 17378-2001 — штампованные переходы DN 25–500. ОСТ 36-22-77 — сварные конусные. СТО ЦКТИ 318.01/318.02/318.03 — специсполнения для котельных и трубопроводов ТЭС. СТО СРО-П 60542948 00015-2013 — промышленные трубопроводы.</div>
            <div class="nc-tags"><span class="nc-tag">Концентр.</span><span class="nc-tag">Эксцентр.</span><span class="nc-tag">Сварные</span><span class="nc-tag">ТЭС</span><span class="nc-tag">СТО ЦКТИ</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ все нормативы</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ГОСТ 17378-2001 — переходы бесшовные приварные</div>
                <div class="nc-full-item">ОСТ 36-22-77 — переходы сварные</div>
                <div class="nc-full-item">ОСТ 34-10-753-97 — переходы для ТЭС</div>
                <div class="nc-full-item">ТС 585, ТС 586 — конкретные исполнения</div>
                <div class="nc-full-item">СТО 79814898 115-2009 — энергетические системы</div>
                <div class="nc-full-item">СТО СРО-П 60542948 00015-2013 — промышленные трубопроводы</div>
                <div class="nc-full-item">СТО ЦКТИ 318.01-2009 — концентрические для ТЭС</div>
                <div class="nc-full-item">СТО ЦКТИ 318.02-2009 — эксцентрические для ТЭС</div>
                <div class="nc-full-item">СТО ЦКТИ 318.03-2009 — сварные конусные для ТЭС</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
                <div class="nc-full-item">ТР ТС 032/2013 — обязателен при PN &gt; 0,05 МПа</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2001–2013</div>
          </div>
        </div>
      </div>
      <!-- ДНИЩА / ЗАГЛУШКИ -->
      <div class="norm-group" id="ng-dn">
        <div class="ng-label">Нормативная база — Днища и заглушки</div>
        <div class="norm-grid2 cols-1">
          <div class="nc reveal">
            <div class="nc-code">ГОСТ 17379-2001 · ГОСТ 6533-78 · ОСТ 36-25-77</div>
            <div class="nc-title">Днища эллиптические, заглушки — для трубопроводов и сосудов давления</div>
            <div class="nc-desc">ГОСТ 17379-2001 — эллиптические заглушки DN 25–1200. ГОСТ 6533-78 — отбортованные днища. ОСТ 36-25-77 — для трубопроводов тепловых сетей и ТЭС. СТО ЦКТИ 504.02-2009 — котельные объекты ТЭС.</div>
            <div class="nc-tags"><span class="nc-tag">Эллиптические</span><span class="nc-tag">DN 25–4000</span><span class="nc-tag">Сосуды давления</span><span class="nc-tag">ТЭС</span></div>
            <button class="nc-expand-btn" onclick="toggleNc(this)">+ все нормативы</button>
            <div class="nc-full">
              <div class="nc-full-items">
                <div class="nc-full-item">ГОСТ 17379-2001 — эллиптические заглушки бесшовные</div>
                <div class="nc-full-item">ГОСТ 6533-78 — днища отбортованные эллиптические</div>
                <div class="nc-full-item">ОСТ 36-25-77 — днища для тепловых сетей и ТЭС</div>
                <div class="nc-full-item">СТО ЦКТИ 504.02-2009 — котельные объекты ТЭС</div>
                <div class="nc-full-item">ТУ 24.20.40-001-13842829-2023 — ТУ предприятия</div>
                <div class="nc-full-item">ТР ТС 032/2013 — обязателен при PN &gt; 0,05 МПа</div>
              </div>
            </div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 1978–2009</div>
          </div>
        </div>
      </div>
      <!-- АЭС -->
      <div class="norm-group" id="ng-aes">
        <div class="ng-label">Нормативная база — Атомная энергетика</div>
        <div class="norm-grid2">
          <div class="nc reveal">
            <div class="nc-code">НП-045-18</div>
            <div class="nc-title">Правила устройства и безопасной эксплуатации трубопроводов пара и горячей воды для объектов атомной энергии</div>
            <div class="nc-desc">Основной действующий норматив Ростехнадзора для трубопроводов на объектах атомной энергии. Заменил устаревшие редакции. Обязателен при проектировании и приёмке СДТ для АЭС.</div>
            <div class="nc-tags"><span class="nc-tag">АЭС</span><span class="nc-tag">Действующий</span><span class="nc-tag">Ростехнадзор</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2018</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">НП-089-15</div>
            <div class="nc-title">Правила ядерной безопасности реакторных установок атомных станций</div>
            <div class="nc-desc">Нормативный документ для реакторных установок. Применяется совместно с НП-045-18 при определении требований к СДТ первого и второго контура АЭС.</div>
            <div class="nc-tags"><span class="nc-tag">АЭС</span><span class="nc-tag">Реакторные уст.</span><span class="nc-tag">Действующий</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2015</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ТР ТС 032/2013</div>
            <div class="nc-title">Технический регламент ЕАЭС о безопасности оборудования под давлением</div>
            <div class="nc-desc">Обязателен для всех изделий с PN &gt; 0,05 МПа на территории ЕАЭС, включая объекты АЭС. Требует декларации соответствия, паспорта и протоколов контроля.</div>
            <div class="nc-tags"><span class="nc-tag">ЕАЭС</span><span class="nc-tag">Декларация</span><span class="nc-tag">Обязательный</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2013</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ПНАЭ Г-7-008-89 / Г-7-010-89</div>
            <div class="nc-title">Исторические документы — только как ссылочные, не основные</div>
            <div class="nc-desc">ПНАЭ Г-7-008-89 — сварка в атомной энергетике. ПНАЭ Г-7-010-89 — контроль сварных соединений. Частично заменены актуальными НП. Используются как ссылочные в проектной документации старых АЭС.</div>
            <div class="nc-tags"><span class="nc-tag">Исторический</span><span class="nc-tag">Ссылочный</span></div>
            <div class="nc-note"><strong>Внимание:</strong> применять только если прямо указаны в КД заказчика. Актуальные требования — в НП-045-18 и НП-089-15.</div>
            <div class="nc-status" style="margin-top:8px;"><div class="nc-dot" style="background:rgba(169,183,198,.35);"></div>Ссылочный / 1989</div>
          </div>
        </div>
      </div>
      <!-- ОБЩИЕ -->
      <div class="norm-group" id="ng-gen">
        <div class="ng-label">Нормативная база — Общие документы</div>
        <div class="norm-grid2">
          <div class="nc reveal">
            <div class="nc-code">ТУ 24.20.40-001-13842829-2023</div>
            <div class="nc-title">Технические условия предприятия — соединительные детали трубопроводов</div>
            <div class="nc-desc">Внутренние ТУ ООО Завод «Промышленная Энергетика». Охватывают все типы СДТ собственного производства. Устанавливают дополнительные требования к контролю, испытаниям и документированию.</div>
            <div class="nc-tags"><span class="nc-tag">ТУ предприятия</span><span class="nc-tag">Все типы СДТ</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2023</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ТР ТС 032/2013</div>
            <div class="nc-title">Технический регламент о безопасности оборудования под давлением (ЕАЭС)</div>
            <div class="nc-desc">Обязательный регламент для всех изделий с PN &gt; 0,05 МПа на территории ЕАЭС. Требует декларирования, сертификации, паспорта изделия. Распространяется на все типы СДТ.</div>
            <div class="nc-tags"><span class="nc-tag">ЕАЭС</span><span class="nc-tag">Обязательный</span><span class="nc-tag">Все типы</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2013</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">КД заказчика</div>
            <div class="nc-title">Конструкторская документация — нестандартные и специальные исполнения</div>
            <div class="nc-desc">Для нестандартных деталей и специсполнений изготовление ведётся по согласованным чертежам и ТУ заказчика. При необходимости предприятие разрабатывает ТУ для конкретного изделия.</div>
            <div class="nc-tags"><span class="nc-tag">Нестандартные</span><span class="nc-tag">По ТЗ</span><span class="nc-tag">АЭС / ТЭС</span></div>
            <div class="nc-status"><div class="nc-dot"></div>По проекту заказчика</div>
          </div>
          <div class="nc reveal">
            <div class="nc-code">ГОСТ серия 17375–17380</div>
            <div class="nc-title">Базовые ГОСТы 2001 года — детали трубопроводов бесшовные приварные</div>
            <div class="nc-desc">Базовая серия для стандартных СДТ: ГОСТ 17375 (отводы), 17376 (тройники), 17378 (переходы), 17379 (заглушки эллипт.), 17380 (заглушки плоские). Действующие, изд. 2001.</div>
            <div class="nc-tags"><span class="nc-tag">Базовые</span><span class="nc-tag">Действующие</span><span class="nc-tag">5 ГОСТ</span></div>
            <div class="nc-status"><div class="nc-dot"></div>Действующий / 2001</div>
          </div>
        </div>
      </div>
    </div>
  </section>

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

<section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
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
            <p class="kb-card-body">Трубопроводы <strong>I–IV категорий по НП-089-15</strong>. Расширенный объём НК согласно НП-045-18, прослеживаемость плавки, паспортизация по ГОСТ ISO 10474. Первый контур реакторного отсека, системы аварийного охлаждения, вспомогательные контуры. Изготовление по ТУ предприятия и КД заказчика.</p>
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
            <p class="kb-card-body">Промысловые и магистральные трубопроводы, установки подготовки нефти и газа. Требования по коррозионной стойкости к агрессивным средам (H₂S, CO₂). Изготовление по <strong>ГОСТ 17375–17380</strong>, ОСТ 36 и ТУ предприятия с возможностью учёта требований корпоративных стандартов.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 17375–17380</span><span class="kb-tag">ТР ТС 032</span><span class="kb-tag">09Г2С</span></div>
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
            <span class="kb-grp-items">Горячедеформированные и холоднодеформированные по ГОСТ 8731–8734 и ГОСТ 9940–9941</span>
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
                  <div class="kb-step-body">Поиск работает по <strong>коду, наименованию, ГОСТ, материалу, DN</strong>. Например: «09Г2С» или «ГОСТ 17375» мгновенно фильтрует весь реестр. Горячая клавиша: <strong>⌘K</strong> или <strong>/</strong>.</div>
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
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17375-2001</span><span class="kb-norm-desc">Отводы крутоизогнутые бесшовные приварные. DN 15–500, R = 1.5DN, углы 45°, 90°, 180°</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17376-2001</span><span class="kb-norm-desc">Тройники равнопроходные и переходные бесшовные приварные. DN 15–500</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17378-2001</span><span class="kb-norm-desc">Переходы концентрические и эксцентрические бесшовные приварные. DN 15–500</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17379-2001</span><span class="kb-norm-desc">Заглушки эллиптические приварные. DN 15–500</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30753-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные из углеродистой стали. Общие ТУ</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33259-2015</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов. DN 10–4000, PN 1–250</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ОСТ — отраслевые стандарты Минэнерго и Минмаша</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-20-77</span><span class="kb-norm-desc">Отводы штампосварные. DN 25–400, для трубопроводов ТЭС и нефтехима</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-21-77</span><span class="kb-norm-desc">Отводы секторные/сварные. DN 100–1400, паровые и водяные тракты ТЭС/ГРЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-22-77</span><span class="kb-norm-desc">Тройники сварные. DN 100–1400 для трубопроводов ТЭС и ГРЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-24-77</span><span class="kb-norm-desc">Переходы сварные. DN 100–1400 для трубопроводов высокого давления</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34.10.752-97</span><span class="kb-norm-desc">Отводы для трубопроводов тепловых электростанций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34.10.762-97</span><span class="kb-norm-desc">Тройники равнопроходные для трубопроводов ТЭС</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">СТО ЦКТИ — стандарты организации (ТЭС, ГРЭС, ТЭЦ)</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.01–.06</span><span class="kb-norm-desc">Отводы гнутые для трубопроводов ТЭС. 6 типоисполнений, серия 2009 г.</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.14</span><span class="kb-norm-desc">Отводы для теплоэнергетических применений специального исполнения</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 720.01–.29</span><span class="kb-norm-desc">Тройники и переходы для трубопроводов ТЭС. 29 типоисполнений 2009–2011 гг.</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 101.18-2015</span><span class="kb-norm-desc">Фланцы трубопроводные специального исполнения для объектов ТЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО 79814898-111-2009</span><span class="kb-norm-desc">Энергетические системы — стандарт организации</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">НП — нормы ядерной и радиационной безопасности (АЭС)</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">НП-045-18</span><span class="kb-norm-desc">Правила контроля сварных соединений оборудования и трубопроводов АЭУ. Объём и методы НК</span></div>
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
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:7px;letter-spacing:.1em;color:var(--g1);">по НП-045-18</span></div>
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
                  <div class="kb-check-body">Тип изделия и нормативный документ: отвод 90° по <strong>ГОСТ 17375</strong>, тройник по <strong>СТО ЦКТИ 720.03</strong> и т.д. Если норматив неизвестен — укажите тип объекта / установки.</div>
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
        <div class="faq-wrap reveal">
          <div class="fq"><div class="fq-q"><span class="fq-num">01</span><span class="fq-t">Чем отличаются изделия по ОСТ и ГОСТ — можно ли их заменить друг другом?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">ГОСТ и ОСТ — разные нормативные документы с отличающимися допусками, маркировкой и требованиями к контролю. <strong>Взаимозаменяемость — только по письменному согласованию с проектировщиком и представителем надзора.</strong> Для объектов ТЭС/АЭС самовольная замена нормативного документа недопустима.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">02</span><span class="fq-t">Поставляете ли изделия с сертификацией по ТР ТС 032/2013?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Вся продукция завода охвачена декларацией о соответствии <strong>RU С-RU.АБ53.В.08323/23</strong> по ТР ТС 032/2013 «О безопасности оборудования, работающего под давлением». Декларация включается в комплект документов на поставку.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">03</span><span class="fq-t">Какой объём неразрушающего контроля применяется по умолчанию?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Базовый объём — <strong>100% ВИК</strong> (визуально-измерительный контроль) для всех изделий. По требованию заказчика или в соответствии с нормативным документом добавляются:<ul><li>УЗК — по ГОСТ Р 55724-2013</li><li>РК (рентгенографический контроль)</li><li>МПД (магнитопорошковая дефектоскопия)</li><li>ПВК (капиллярный контроль)</li></ul>Для объектов АЭС — полный объём по <strong>НП-045-18</strong> и программе контроля объекта.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">04</span><span class="fq-t">Можно ли заказать нестандартные типоразмеры или исполнение по чертежам заказчика?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Завод изготавливает изделия по конструкторской документации заказчика — в том числе нестандартные диаметры, углы, толщины стенок и специальные исполнения. Для согласования — отправьте КД через форму запроса или на <strong>zakaz@prom-en.com</strong>.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">05</span><span class="fq-t">Как долго хранится прослеживаемость документации после поставки?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Архив производственной документации (паспорта, протоколы НК, сертификаты плавок) хранится на производстве <strong>не менее 10 лет</strong>. Для объектов АЭС — в соответствии с требованиями НП-017-14 и НП-089-15. По запросу возможно предоставление дубликатов документов.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">06</span><span class="fq-t">Какие сроки изготовления для типовых позиций каталога?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Типовые позиции из складской программы (DN 50–200, массовые марки стали) — <strong>от 3–5 рабочих дней</strong>. Серийный заказ с полным НК и паспортизацией — <strong>от 10–15 рабочих дней</strong>. Изделия DN 500+ и спецсплавы — по согласованию. Точный срок указывается в коммерческом предложении.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">07</span><span class="fq-t">Есть ли складская программа или всё производится под заказ?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Часть позиций номенклатуры поддерживается на складе — прежде всего <strong>отводы, тройники и переходы DN 50–200 из Ст20 и 09Г2С</strong> по ГОСТ 17375 / 17376 / 17378. Для уточнения наличия — направьте запрос: мы предоставим актуальный остаток и срок дополнительного выпуска.</div></div></div>
        </div>
      </div><!-- /kp-faq -->

    </div><!-- /kb-panels -->
  </section>

</div><!-- /.pg -->


<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на отводы">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на отводы</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Отвод" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 17375, СТО ЦКТИ 321…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / D×s, мм</label><input id="om-dn" name="dn" type="text" placeholder="DN 100 / 108×4" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Давление, МПа</label><input id="om-pn" name="pn" type="text" placeholder="PN 16" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка стали</label><input id="om-mat" name="material" type="text" placeholder="09Г2С, 12Х1МФ…" autocomplete="off"></div>
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
