<?php
/**
 * Секция 05 — материалы исполнения (марки стали; строки марок товара подсвечиваются JS).
 * Разметка 1:1 из design-reference/product-otvod-90.html; динамика — PHP.
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="s s-alt" id="s05">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">05</span>Материалы исполнения</div>
      
    </div>
    <div class="s-body">
      <div class="mat-tbl-wrap reveal" data-mat-tbl>
        <div class="mat-tbl-hd"><span>Марка</span><span>Назначение</span><span>t max, °C</span><span>PN макс</span><span>Отрасль</span><span>Стандарт</span></div>
        <?php
        // Динамически — по фактическим маркам товара (см. inc/steel-reference.php).
        $mat_steels = isset( $steels ) ? array_values( (array) $steels ) : [];
        $mat_rows   = promen_render_materials_rows( $mat_steels, false );
        ?>
      </div>
      <?php if ( $mat_rows > 6 ) : ?>
        <?php
        // Кнопка нужна только телефону — на десктопе таблица не урезана.
        // Число отдельно: «все 21 марку» / «все 14 марок» — разные падежи.
        ?>
        <button type="button" class="norm-more-btn norm-more-btn--m" data-mat-more>
          Все марки стали · <?php echo esc_html( number_format_i18n( $mat_rows ) ); ?>
        </button>
      <?php endif; ?>
    </div>
  </section>
