<?php
/**
 * Хаб «Калькуляторы» — /kalkulyatory/.
 * Карточки инструментов из promen_calc_pages(); появляются по мере
 * публикации дочерних страниц (мёртвых ссылок нет).
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.HUB / REV.1' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
?>
<div class="pg">

  <div class="clc-hero">
    <div class="clc-eyebrow">Инструменты снабженца · ПЭ-КЛК</div>
    <h1 class="clc-h1">Калькуляторы<br><em>и справочники</em></h1>
    <p class="clc-desc">Масса деталей, комплекты фланцевого крепежа, перевод килограммов в штуки
      и метров в тонны — по данным живого каталога завода. Каждый расчёт заканчивается
      конкретной позицией и заявкой, а не таблицей в Excel.</p>
  </div>

  <div class="clc-hub">
    <?php foreach ( promen_calc_pages() as $slug => $card ) : ?>
      <?php
      $url = promen_calc_url( $slug );
      if ( $url === '' ) {
        continue;
      }
      ?>
      <a class="clc-card" href="<?php echo esc_url( $url ); ?>">
        <span class="clc-card-num"><?php echo esc_html( $card['num'] ); ?></span>
        <span class="clc-card-t"><?php echo esc_html( $card['title'] ); ?></span>
        <span class="clc-card-d"><?php echo esc_html( $card['desc'] ); ?></span>
        <span class="clc-card-tag"><?php echo esc_html( $card['tag'] ); ?></span>
        <span class="clc-card-arr" aria-hidden="true">→</span>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="clc-seo">
    <h2>Зачем эти инструменты</h2>
    <p>Калькуляторы построены на данных каталога PROM-EN: масса и геометрия каждой позиции —
      те же, что в паспорте изделия. Посчитали партию — сразу видно, какая это позиция каталога,
      сколько весит партия и во что обойдётся доставка «Деловыми Линиями» до вашего терминала.</p>
    <p>Если задача нестандартная — <a href="#request">отправьте исходные данные</a>, инженер
      подберёт норматив, материал и срок изготовления в течение рабочего дня.</p>
  </div>

</div>
<?php
get_footer();
