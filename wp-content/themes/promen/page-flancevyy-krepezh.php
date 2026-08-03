<?php
/**
 * Калькулятор фланцев и крепежа (КОФ) — /kalkulyatory/flancevyy-krepezh/.
 * Тип × PN × DN → вес фланца + полный комплект: шпильки/болты, гайки, шайбы
 * с длиной и массой; всё — позиции каталога. Логика — calc.js (модуль flange).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.02 / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub = promen_calc_url( 'kalkulyatory' );
?>
<div class="pg">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>Фланцы и крепёж (КОФ)</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero">
    <div class="clc-eyebrow">Калькулятор · ПЭ-КЛК/02</div>
    <h1 class="clc-h1">Фланцы и крепёж<br><em>комплект КОФ</em></h1>
    <p class="clc-desc">Выберите тип фланца, давление и проход — калькулятор покажет вес фланца
      и соберёт комплект ответных фланцев: сколько шпилек или болтов нужно, какой длины,
      сколько гаек и шайб, и сколько весь комплект весит.</p>
  </div>

  <div class="clc-wrap" data-calc="flange">
    <div>
      <div class="clc-panel">
        <div class="clc-tabs" data-tabs role="tablist"></div>
        <div class="clc-fields" data-fields></div>
      </div>
      <p class="clc-note">PN указано в МПа (как в каталоге) с подсказкой в кгс/см² (Ру).
        Количество и резьба крепежа — из геометрии фланца по нормативу; длина шпильки —
        расчётная под пару фланцев с прокладкой. КОФ = комплект ответных фланцев (пара).</p>
    </div>
    <div class="clc-out">
      <div class="clc-panel">
        <div class="clc-panel-hd">
          <span class="clc-panel-t">Комплект</span>
          <span class="clc-panel-t">ПЭ-КЛК/02</span>
        </div>
        <div data-result><div class="clc-empty">Загружаем данные каталога…</div></div>
        <div class="clc-dlv" data-delivery hidden></div>
      </div>
    </div>
  </div>

  <div class="clc-seo">
    <h2>Что входит в комплект фланцевого соединения</h2>
    <p>На каждое соединение нужны два фланца, прокладка и крепёж по числу отверстий: шпильки
      с двумя гайками (или болты с гайкой) и шайбы. Число отверстий n и резьба M определяются
      нормативом фланца — например, у воротникового фланца DN 50 PN 1,6 МПа по ГОСТ 33259 это
      4 отверстия M16. Калькулятор берёт эти данные из каталога и сразу показывает подходящие
      позиции: <a href="<?php echo esc_url( promen_product_cat_link( 'flancy' ) ); ?>">фланцы</a>,
      <a href="<?php echo esc_url( promen_product_cat_link( 'shpilki' ) ); ?>">шпильки</a>,
      <a href="<?php echo esc_url( promen_product_cat_link( 'gayki' ) ); ?>">гайки</a> и
      <a href="<?php echo esc_url( promen_product_cat_link( 'shayby' ) ); ?>">шайбы</a>
      можно запросить одним комплектом.</p>
    <p>Длина шпильки считается по классической схеме: две толщины фланца, прокладка, две гайки
      по 0,8d, шайбы и выступ резьбы — с округлением вверх до стандартного ряда длин.
      Для рабочих сред с высокой температурой марку крепежа (35, 40Х, 25Х1МФ…) подбирает
      инженер — укажите параметры в заявке.</p>
  </div>

</div>
<?php
get_footer();
