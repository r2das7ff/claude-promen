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
      <div class="s-meta">MATERIAL GRADES</div>
    </div>
    <div class="s-body">
      <div class="mat-tbl-wrap reveal">
        <div class="mat-tbl-hd"><span>Марка</span><span>Назначение</span><span>t max, °C</span><span>PN макс</span><span>Отрасль</span><span>Стандарт</span></div>
        <?php
        // Динамически — по фактическим маркам товара (см. inc/steel-reference.php).
        $mat_steels = isset( $steels ) ? array_values( (array) $steels ) : [];
        promen_render_materials_rows( $mat_steels, false );
        ?>
      </div>
    </div>
  </section>
