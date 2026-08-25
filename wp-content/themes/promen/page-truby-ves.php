<?php
/**
 * Трубный калькулятор — /kalkulyatory/truby-ves/.
 * Метры ↔ тонны по кг/м из каталога (или по формуле для своих размеров),
 * площадь окраски и вместимость. Логика — calc.js (модуль pipes).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.04 / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub = promen_calc_url( 'kalkulyatory' );
?>
<div class="pg">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>Трубы: метры ↔ тонны</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero">
    <div class="clc-eyebrow">Калькулятор · ПЭ-КЛК/04</div>
    <h1 class="clc-h1">Трубы:<br><em>метры ↔ тонны</em></h1>
    <p class="clc-desc">Вес метра трубы, перевод длины в тоннаж и обратно, площадь наружной
      поверхности под окраску и вместимость трубопровода. Размеры — из сортамента каталога
      или свои.</p>
  </div>

  <div class="clc-wrap" data-calc="pipes">
    <div>
      <div class="clc-panel">
        <div class="clc-tabs" data-tabs role="tablist"></div>
        <div class="clc-fields" data-fields></div>
      </div>
      <p class="clc-note">Для позиций каталога кг/м — по сортаменту ГОСТ; для своих размеров —
        формула (D − s) × s × 0,02466 при плотности стали 7,85 г/см³. Заполните «Длина» или
        «Масса» — второе поле пересчитается само.</p>
    </div>
    <div class="clc-out">
      <div class="clc-panel">
        <div class="clc-panel-hd">
          <span class="clc-panel-t">Результат</span>
          <span class="clc-panel-t">ПЭ-КЛК/04</span>
        </div>
        <div data-result><div class="clc-empty">Загружаем сортамент…</div></div>
      </div>
    </div>
  </div>

  <div class="clc-seo">
    <h2>Вес метра трубы и что из него следует</h2>
    <p>Масса метра стальной трубы зависит только от наружного диаметра и стенки:
      m = (D − s) × s × 0,02466. Для труб из
      <a href="<?php echo esc_url( promen_product_cat_link( 'truby' ) ); ?>">каталога PROM-EN</a>
      калькулятор берёт нормативное значение кг/м напрямую из сортамента — электросварные
      по ГОСТ 10704-91, бесшовные по ГОСТ 8732-78 и водогазопроводные по ГОСТ 3262.</p>
    <p>Из веса метра сразу считаются практические величины: сколько метров в тонне закупки,
      сколько квадратных метров красить (наружная поверхность π·D·L) и сколько литров воды
      уйдёт на гидроиспытание участка (внутренний объём).</p>
  </div>

</div>
<?php
get_footer();
