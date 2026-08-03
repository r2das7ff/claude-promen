<?php
/**
 * Калькулятор веса СДТ — /kalkulyatory/ves-sdt/.
 * Отводы, переходы, тройники, заглушки, днища: норматив → типоразмер →
 * масса штуки и партии + доставка. Логика — assets/js/calc.js (модуль sdt).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.01 / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub = promen_calc_url( 'kalkulyatory' );
?>
<div class="pg">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>Вес деталей трубопровода</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero">
    <div class="clc-eyebrow">Калькулятор · ПЭ-КЛК/01</div>
    <h1 class="clc-h1">Вес деталей<br><em>трубопровода</em></h1>
    <p class="clc-desc">Масса отводов, переходов, тройников, заглушек и днищ по ГОСТ и ОСТ.
      Выберите норматив и типоразмер — калькулятор возьмёт массу из каталога завода
      и посчитает партию вместе с доставкой.</p>
  </div>

  <div class="clc-wrap" data-calc="sdt">
    <div>
      <div class="clc-panel">
        <div class="clc-tabs" data-tabs role="tablist"></div>
        <div class="clc-fields" data-fields></div>
      </div>
      <p class="clc-note">Масса — номинальная по нормативу (данные каталога PROM-EN, сталь 20 / 09Г2С);
        фактическая масса партии подтверждается паспортом поставки. Экспорт в Excel не нужен —
        кнопка «Запросить позицию» отправляет расчёт инженеру напрямую.</p>
    </div>
    <div class="clc-out">
      <div class="clc-panel">
        <div class="clc-panel-hd">
          <span class="clc-panel-t">Результат</span>
          <span class="clc-panel-t">ПЭ-КЛК/01</span>
        </div>
        <div data-result><div class="clc-empty">Загружаем данные каталога…</div></div>
        <div class="clc-dlv" data-delivery hidden></div>
      </div>
    </div>
  </div>

  <div class="clc-seo">
    <h2>Как считается вес отвода, тройника или перехода</h2>
    <p>Для деталей по ГОСТ 17375, ГОСТ 17376, ГОСТ 17378, ГОСТ 17379, ГОСТ 30753 и отраслевым ОСТ
      масса каждой позиции нормирована стандартом. Калькулятор не пересчитывает её по формуле,
      а берёт из номенклатуры каталога — той же, по которой завод отгружает продукцию,
      поэтому расчётная масса совпадает с паспортной.</p>
    <ul>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'otvody' ) ); ?>">Отводы стальные</a> — крутоизогнутые 45°/60°/90°/180°;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'perekhody' ) ); ?>">Переходы</a> — концентрические и эксцентрические;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'troyniki' ) ); ?>">Тройники</a> — равнопроходные и переходные;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'zaglushki' ) ); ?>">Заглушки эллиптические</a>;</li>
      <li><a href="<?php echo esc_url( promen_product_cat_link( 'dnishcha' ) ); ?>">Днища</a> по ГОСТ 6533 и ОСТ 26-2040.</li>
    </ul>
    <p>Вес партии нужен не только для сметы: по массе и объёму калькулятор сразу оценивает
      доставку сборным грузом «Деловых Линий» до терминала вашего города.</p>
  </div>

</div>
<?php
get_footer();
