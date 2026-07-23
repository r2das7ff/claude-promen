<?php
/**
 * Страница категории «Опоры трубопроводов».
 * Ветка /catalog/opory/. Факты — products_опоры.csv (пилот).
 */

defined( 'ABSPATH' ) || exit;

get_header();

$term     = get_queried_object();
$crumbs   = promen_breadcrumbs();
$shop_url = wc_get_page_permalink( 'shop' );
$otv      = get_term_by( 'slug', 'opory', 'product_cat' );
$otv_link = ( $otv && ! is_wp_error( get_term_link( $otv ) ) ) ? get_term_link( $otv ) : $shop_url;
$otv_url  = $otv_link;
$otv_cnt  = function_exists( 'promen_catalog_group_count' ) ? promen_catalog_group_count( 'opory' ) : ( $otv ? (int) $otv->count : 0 );
$n_np = $_opory_nepodv ? (int) $_opory_nepodv->count : 0;
$n_sk = $_opory_skolz ? (int) $_opory_skolz->count : 0;
$n_pr = $_opory_pruzh ? (int) $_opory_pruzh->count : 0;
?>
<script type="application/ld+json"><?php echo promen_breadcrumbs_schema( $crumbs ); ?></script>

<nav class="sidenav" aria-label="Навигация по разделам">
  <a class="sidenav-item" href="#hero"><span class="sidenav-dot"></span><span class="sidenav-label">КАТЕГОРИЯ</span></a>
  <a class="sidenav-item" href="#s01"><span class="sidenav-dot"></span><span class="sidenav-label">СЕРИИ</span></a>
  <a class="sidenav-item" href="#registry"><span class="sidenav-dot"></span><span class="sidenav-label">РЕЕСТР</span></a>
  
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
      <div class="hero-eyebrow">ОП · Семейство изделий — поставка под заказ</div>
      <h1 class="hero-h1">Опоры<br><em>трубопроводов</em><br>неподвижные и скользящие</h1>
      <p class="hero-desc">Неподвижные хомутовые, скользящие и пружинные опоры по ОСТ 36-17-85. DN от 50. Фиксация трассы и компенсация температурных перемещений. Полный пакет документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $otv_cnt ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 50–1000</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">3 типа</span><span class="hp-k">НП · СК · ПР</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / OPORY SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">50 — 1000</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">НП · СК · ПР</span></div>
        <div class="hud-row"><span class="hud-rk">Норматив</span><span class="hud-rv">ОСТ 36-17-85</span></div>
        <div class="hud-row"><span class="hud-rk">Материал</span><span class="hud-rv">Ст3сп · Ст20</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Состав реестра</div>
        <div class="hud-row"><span class="hud-rk">Неподвижные</span><span class="hud-rv live"><?php echo esc_html( (string) $n_np ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Скользящие</span><span class="hud-rv live"><?php echo esc_html( (string) $n_sk ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Пружинные</span><span class="hud-rv live"><?php echo esc_html( (string) $n_pr ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
      </div>
    </div>
  </div>



<?php promen_render_category_series_registry( 'opory' ); ?>

<?php promen_render_category_catalog_embed( 'opory', (int) $otv_cnt ); ?>


<section class="s map-outer" id="s02">
    <div class="map-grid"></div>
    <div class="s-hd" style="border-bottom:1px solid rgba(109,140,166,.15);">
      <div class="s-badge s-dark" style="display:flex;"><span class="s-badge-num">02</span><span style="color:rgba(109,140,166,.6);font-family:'DINPro',monospace;font-size:8px;letter-spacing:.28em;text-transform:uppercase;margin-left:14px;">Карта типоисполнений</span></div>
      <div class="s-meta">PRODUCT TYPE MAP</div>
    </div>
    <div class="map-body">
      <div class="map-root"><div class="map-root-label">Опоры — типоисполнения семейства</div></div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(3,1fr);">
        <div class="mg"><div class="mg-hd"><div class="mg-code">НП</div><div class="mg-cnt"><?php echo esc_html( (string) $n_np ); ?> поз.</div></div><div class="mg-name">Неподвижные</div>
          <div class="mg-items"><div class="mg-item">Хомутовые<span class="mg-norm">фиксация</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ОСТ 36-17</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">СК</div><div class="mg-cnt"><?php echo esc_html( (string) $n_sk ); ?> поз.</div></div><div class="mg-name">Скользящие</div>
          <div class="mg-items"><div class="mg-item">Перемещения<span class="mg-norm">температура</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ОСТ 36-17</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">ПР</div><div class="mg-cnt"><?php echo esc_html( (string) $n_pr ); ?> поз.</div></div><div class="mg-name">Пружинные</div>
          <div class="mg-items"><div class="mg-item">Подвески<span class="mg-norm">нагрузка</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ОСТ 36-17</span></div></div>
      </div>
    </div>
  </section>

<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор опоры</div>
      <div class="s-meta">OPORY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead"><div class="sg-th">Задача</div><div class="sg-th">Нужное исполнение</div><div class="sg-th">Что передать</div></div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 01</div><div class="sg-task-h">Зафиксировать участок трубопровода</div></div>
          <div class="sg-product"><div class="sg-prod-name">Опора неподвижная хомутовая</div>
            <div class="sg-tags"><span class="sg-tag hi">НП</span><span class="sg-tag"><?php echo esc_html( (string) $n_np ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'group', 'opory-nepodv', $shop_url ) ); ?>">К неподвижным →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">DN трубы</div><div class="sg-param">Нагрузка</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 02</div><div class="sg-task-h">Компенсировать температурные перемещения</div></div>
          <div class="sg-product"><div class="sg-prod-name">Опора скользящая</div>
            <div class="sg-tags"><span class="sg-tag hi">СК</span><span class="sg-tag"><?php echo esc_html( (string) $n_sk ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'group', 'opory-skolz', $shop_url ) ); ?>">К скользящим →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">DN</div><div class="sg-param">Ход / покрытие</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 03</div><div class="sg-task-h">Вертикальная нагрузка / подвеска</div></div>
          <div class="sg-product"><div class="sg-prod-name">Опора пружинная</div>
            <div class="sg-tags"><span class="sg-tag hi">ПР</span><span class="sg-tag"><?php echo esc_html( (string) $n_pr ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'group', 'opory-pruzh', $shop_url ) ); ?>">К пружинным →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">DN</div><div class="sg-param">Нагрузка, кН</div><div class="sg-param">Ход пружины</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 04</div><div class="sg-task-h">По КД / нестандарт</div></div>
          <div class="sg-product"><div class="sg-prod-name">Подбор по спецификации</div>
            <div class="sg-tags"><span class="sg-tag">КД</span></div>
            <a class="sg-link" href="#request">Форма запроса →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">Чертёж</div><div class="sg-param">Нагрузки</div><div class="sg-param">Срок</div></div></div>
        </div>
      </div>
    </div>
  </section>


<?php promen_render_category_norms_section( 'opory' ); ?>


<?php promen_render_materials_section( 'opory' ); ?>

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
          <div class="pc"><div class="pc-h">Сварные конструкции</div><p class="pc-p">Опоры и подвески трассы; арматура — по спецификации объекта. ОСТ 36-17 / ГОСТ 33257.</p></div>
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

<?php include __DIR__ . '/parts/kb-opory.php'; ?>

</div><!-- /.pg -->


<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на опоры">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на опоры</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Опора" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ОСТ 36-17, DN, тип НП/СК/ПР…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / D×s, мм</label><input id="om-dn" name="dn" type="text" placeholder="DN 100×80 / 108×4–89×4" autocomplete="off"></div>
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
