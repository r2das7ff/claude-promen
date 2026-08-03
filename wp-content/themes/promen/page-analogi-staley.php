<?php
/**
 * Справочник аналогов марок стали — /kalkulyatory/analogi-staley/.
 * ГОСТ ↔ EN ↔ ASTM/AISI ↔ DIN с поиском в обе стороны.
 * Логика — calc.js (модуль steels).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.06 / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub = promen_calc_url( 'kalkulyatory' );
?>
<div class="pg">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>Аналоги марок стали</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero">
    <div class="clc-eyebrow">Справочник · ПЭ-КЛК/06</div>
    <h1 class="clc-h1">Аналоги<br><em>марок стали</em></h1>
    <p class="clc-desc">Ближайшие соответствия марок по ГОСТ и зарубежных стандартов EN, ASTM/AISI
      и DIN — для чтения импортной документации и подбора замен. Поиск работает в обе стороны:
      введите «09Г2С» или «321».</p>
  </div>

  <div class="clc-wrap" data-calc="steels" style="grid-template-columns:1fr;">
    <div class="clc-panel">
      <div class="clc-search">
        <span class="clc-search-ic">ПОИСК</span>
        <input type="text" data-search placeholder="20 · P265GH · A106 · 321 · 09Г2С…" autocomplete="off">
      </div>
      <div data-table></div>
      <div class="clc-dlv">
        <p class="clc-note" style="margin:0 0 12px;">Соответствия справочные: химический состав и
          механика «аналогов» совпадают не полностью, а для ответственных объектов (АЭС, сосуды
          под давлением) замена марки требует согласования. Пришлите требования проекта —
          подберём материал по нормативу.</p>
        <button type="button" class="clc-btn" data-consult>Подобрать материал →</button>
      </div>
    </div>
  </div>

  <div class="clc-seo">
    <h2>Как пользоваться таблицей аналогов</h2>
    <p>Марка-аналог — это ближайшая по составу и назначению сталь другого стандарта, а не полная
      копия. Сталь 20 близка к EN P265GH и ASTM A106 Gr.B; 09Г2С — к P355NH и A516 Gr.70;
      нержавеющая 12Х18Н10Т — к AISI 321. При работе по зарубежной документации аналог помогает
      быстро понять класс материала, но рабочие параметры всегда проверяются по исходному
      стандарту.</p>
    <p>Детали из перечисленных марок — углеродистых, низколегированных, теплоустойчивых и
      нержавеющих — завод производит серийно:
      <a href="<?php echo esc_url( promen_product_cat_link( 'sdt' ) ); ?>">СДТ</a>,
      <a href="<?php echo esc_url( promen_product_cat_link( 'flancy' ) ); ?>">фланцы</a>,
      <a href="<?php echo esc_url( promen_product_cat_link( 'krepezh' ) ); ?>">крепёж</a>.
      Марки в наличии по каждой категории видны в фильтре «Сталь» каталога.</p>
  </div>

</div>
<?php
get_footer();
