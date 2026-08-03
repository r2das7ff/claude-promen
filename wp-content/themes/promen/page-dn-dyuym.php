<?php
/**
 * Справочник DN · дюймы · диаметры — /kalkulyatory/dn-dyuym/.
 * Таблица соответствия DN/NPS/Дн(ГОСТ)/OD(EN·ASME) с поиском.
 * Логика — calc.js (модуль dn).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.05 / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub = promen_calc_url( 'kalkulyatory' );
?>
<div class="pg">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>DN · дюймы · диаметры</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero">
    <div class="clc-eyebrow">Справочник · ПЭ-КЛК/05</div>
    <h1 class="clc-h1">DN, дюймы<br><em>и наружные диаметры</em></h1>
    <p class="clc-desc">Условный проход DN, трубный размер в дюймах и фактические наружные
      диаметры: по российским ГОСТ и по EN/ASME они отличаются — например, DN 50 это Ø57 у нас
      и Ø60,3 за рубежом. Введите любой из размеров — таблица подсветит строку.</p>
  </div>

  <div class="clc-wrap" data-calc="dn" style="grid-template-columns:1fr;">
    <div class="clc-panel">
      <div class="clc-search">
        <span class="clc-search-ic">ПОИСК</span>
        <input type="text" data-search placeholder="DN 50 · 2 · 57 · 60,3…" autocomplete="off">
      </div>
      <div data-table></div>
    </div>
  </div>

  <div class="clc-seo">
    <h2>Почему диаметры «не сходятся»</h2>
    <p>DN — безразмерное обозначение прохода, а не диаметр. Российские сортаменты исторически
      используют свои наружные диаметры (57, 76, 89, 108, 159, 219…), европейско-американская
      система — свои (60,3; 76,1; 88,9; 114,3; 168,3; 219,1…). При стыковке импортного
      оборудования с трубопроводом по ГОСТ это даёт разницу до сантиметра на диаметр —
      её закрывают переходами или деталями по чертежу заказчика.</p>
    <p>Под оба ряда диаметров завод изготавливает
      <a href="<?php echo esc_url( promen_product_cat_link( 'perekhody' ) ); ?>">переходы</a> и
      <a href="<?php echo esc_url( promen_product_cat_link( 'sdt' ) ); ?>">соединительные детали</a> —
      в том числе нестандартные размеры по ТЗ.</p>
  </div>

</div>
<?php
get_footer();
