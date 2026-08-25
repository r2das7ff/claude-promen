<?php
/**
 * Калькулятор метизов кг ↔ шт — /kalkulyatory/metizy/.
 * Болты, гайки, шпильки, шайбы, винты: перевод количества в массу и обратно
 * по массе 1000 шт из каталога. Логика — calc.js (модуль metizy).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.03 / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub = promen_calc_url( 'kalkulyatory' );
?>
<div class="pg">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>Метизы: кг ↔ шт</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero">
    <div class="clc-eyebrow">Калькулятор · ПЭ-КЛК/03</div>
    <h1 class="clc-h1">Метизы:<br><em>килограммы ↔ штуки</em></h1>
    <p class="clc-desc">Крепёж поставляется и учитывается на вес, а в спецификациях он в штуках.
      Выберите тип, норматив и размер — калькулятор переведёт партию из штук в килограммы
      и обратно по массе тысячи штук.</p>
  </div>

  <div class="clc-wrap" data-calc="metizy">
    <div>
      <div class="clc-panel">
        <div class="clc-tabs" data-tabs role="tablist"></div>
        <div class="clc-fields" data-fields></div>
      </div>
      <p class="clc-note">Масса — теоретическая по нормативу (за 1000 шт); фактическая зависит
        от покрытия и поля допуска, расхождение обычно в пределах ±3–5%. Для точной массы партии
        запросите паспорт — вышлем вместе с КП.</p>
    </div>
    <div class="clc-out">
      <div class="clc-panel">
        <div class="clc-panel-hd">
          <span class="clc-panel-t">Результат</span>
          <span class="clc-panel-t">ПЭ-КЛК/03</span>
        </div>
        <div data-result><div class="clc-empty">Загружаем данные каталога…</div></div>
      </div>
    </div>
  </div>

  <div class="clc-seo">
    <h2>Сколько болтов в килограмме</h2>
    <p>У каждого типоразмера крепежа в нормативе задана теоретическая масса тысячи штук:
      например, болт M12×50 по ГОСТ 7798-70 весит около 48 кг за 1000 шт — то есть в килограмме
      примерно 20 болтов. Калькулятор использует эти же значения из
      <a href="<?php echo esc_url( promen_product_cat_link( 'krepezh' ) ); ?>">каталога крепежа</a>,
      поэтому расчёт совпадает со счётом склада.</p>
    <ul>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'bolty' ) ); ?>">Болты</a> — ГОСТ 7798-70, 7805-70, 22032-76 и др.;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'gayki' ) ); ?>">Гайки</a> — ГОСТ 5915-70, 5927-70;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'shpilki' ) ); ?>">Шпильки</a> — для фланцевых соединений;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'shayby' ) ); ?>">Шайбы</a> — плоские и пружинные;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'vinty' ) ); ?>">Винты</a>.</li>
    </ul>
  </div>

</div>
<?php
get_footer();
