<?php
/**
 * «Нормативная база» — 1:1 из html/normativnaya-baza.html (Open Design,
 * 2026-07-23): реестр ГОСТ/ОСТ/СТО/ТУ с фильтрами и панелью документа.
 * «Скачать PDF» — честный тост (файлов нормативов в проекте пока нет,
 * появятся с папкой normatives от заказчика). Хром — header.php.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-12.НБ / REV.1' );
add_filter( 'promen_strip_text', fn () => 'НБ–12' );

$promen_catalog_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_stati_url      = ( $p = promen_page( 'stati' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_proekty_url    = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url   = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_production_url = ( $p = promen_page( 'production' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_sdt_term       = get_term_by( 'slug', 'sdt', 'product_cat' );
$promen_sdt_url        = ( $promen_sdt_term && ! is_wp_error( $l = get_term_link( $promen_sdt_term ) ) ) ? $l : $promen_catalog_url;

get_header();
?>
<div class="pg">

  <!-- HERO -->
  <div class="nb-hero">
    <div>
      <div class="nb-eyebrow">Цифровой реестр документов</div>
      <h1 class="nb-h1">Нормативная<br><em>база</em></h1>
      <p class="nb-desc">Действующие ГОСТ, ОСТ, ТУ и правила безопасности, по которым завод изготавливает и поставляет продукцию для ТЭС, АЭС и промышленных объектов. Открывайте карточку документа, смотрите область применения или скачивайте файл для проектной документации.</p>
    </div>
    <div class="nb-stats">
      <div class="hs"><span class="hs-v" id="hsTotal">—</span><span class="hs-k">Документов в базе</span></div>
      <div class="hs"><span class="hs-v" id="hsCats">—</span><span class="hs-k">Групп нормативов</span></div>
      <div class="hs"><span class="hs-v">ГОСТ / ОСТ / ТУ</span><span class="hs-k">Основные типы</span></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <div class="nb-toolbar">
    <div class="nb-search">
      <svg width="15" height="15" viewBox="0 0 13 13" fill="none"><circle cx="5.5" cy="5.5" r="4" stroke="currentColor"/><line x1="8.8" y1="8.8" x2="12" y2="12" stroke="currentColor"/></svg>
      <input id="nbSearch" type="text" placeholder="Поиск по номеру ГОСТ, названию, категории…">
    </div>
    <div class="nb-filter-row">
      <span class="nb-filter-lbl">Категория</span>
      <div class="nb-chips" id="nbChips"></div>
    </div>
    <div class="nb-filter-row" id="nbSubRow" style="display:none">
      <span class="nb-filter-lbl">Тип детали</span>
      <div class="nb-chips" id="nbSubChips"></div>
    </div>
    <div class="nb-filter-row nb-filter-row--sec">
      <span class="nb-filter-lbl">Тип документа</span>
      <div class="nb-chips" id="nbTypeChips"></div>
    </div>
    <div class="nb-active" id="nbActive"></div>
    <div class="nb-count" id="nbCount"></div>
  </div>

  <!-- GRID -->
  <div class="nb-grid-wrap">
    <div class="nb-grid" id="nbGrid"></div>
    <div class="nb-more-wrap" id="nbMoreWrap">
      <button class="nb-more" id="nbMore">Показать ещё →</button>
    </div>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->

<!-- ПАНЕЛЬ ДОКУМЕНТА (в макете — после футера; fixed-оверлей) -->
</div>

<!-- DETAIL PANEL -->
<div class="nb-overlay" id="nbOverlay"></div>
<div class="nb-panel" id="nbPanel">
  <div class="nb-panel-hd">
    <span class="nb-panel-lbl">Карточка документа</span>
    <div class="nb-panel-close" id="nbPanelClose">✕</div>
  </div>
  <div class="nb-panel-scroll">
    <div class="nb-panel-id">
      <div class="nb-panel-type"><span class="nb-type" id="pType">—</span></div>
      <div class="nb-panel-code" id="pCode">—</div>
      <div class="nb-panel-title" id="pTitle">—</div>
    </div>
    <div class="nb-panel-sec">
      <div class="nb-panel-sec-lbl">Параметры документа</div>
      <div class="nb-panel-row"><span class="nb-panel-k">Категория</span><span class="nb-panel-v" id="pCat">—</span></div>
      <div class="nb-panel-row" id="pSubRow" style="display:none"><span class="nb-panel-k">Тип детали</span><span class="nb-panel-v" id="pSub">—</span></div>
      <div class="nb-panel-row"><span class="nb-panel-k">Статус</span><span class="nb-panel-v" id="pStatus">● Действует</span></div>
      <div class="nb-panel-row"><span class="nb-panel-k">Тип</span><span class="nb-panel-v" id="pTypeFull">—</span></div>
    </div>
    <div class="nb-panel-sec" id="pSuperSec" style="display:none">
      <div class="nb-panel-sec-lbl">Статус документа</div>
      <p class="nb-panel-desc" id="pSuperText">—</p>
    </div>
    <div class="nb-panel-sec">
      <div class="nb-panel-sec-lbl">Область применения</div>
      <p class="nb-panel-desc" id="pDesc">—</p>
    </div>
  </div>
  <div class="nb-panel-cta">
    <button class="btn-pnl fill" id="pDownload">Скачать PDF</button>
    <a class="btn-pnl out" id="pCatalogLink" href="#" style="display:none">Открыть в каталоге →</a>
  </div>
</div>

<div class="nb-toast" id="nbToast"></div>
<?php get_footer(); ?>
