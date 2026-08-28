<?php
/** Секция 08 «Производственные возможности»; $promen_s08_weld — текст карточки «Сварные конструкции». */
defined( 'ABSPATH' ) || exit;
$promen_s08_weld = isset( $promen_s08_weld ) ? (string) $promen_s08_weld : '';
?>
<section class="s" id="s08">
    <div class="s-hd">
      <h2 class="s-badge"><span class="s-badge-num">08</span>Производственные возможности</h2>
      <div class="s-meta">MANUFACTURING CAPABILITIES</div>
    </div>
    <div class="prod-layout reveal">
      <div class="prod-main">
        <h2 class="prod-h">Собственное <em>производство</em><br>полного цикла</h2>
        <p class="prod-lead">Завод «Промышленная Энергетика» изготавливает соединительные детали трубопровода методами горячей штамповки, гибки, сварки и механической обработки. Производственная база — Красноярский край, Западная Сибирь.</p>
        <div class="prod-caps">
          <div class="pc"><div class="pc-h">Штамповка и гибка</div><p class="pc-p">Горячая штамповка отводов, тройников, переходов. Гибка из трубных заготовок R = 1,5–5DN. DN 15–1400.</p></div>
          <div class="pc"><div class="pc-h">Сварные конструкции</div><p class="pc-p"><?php echo esc_html( $promen_s08_weld !== '' ? $promen_s08_weld : 'Секторные отводы, крупногабаритные тройники, переходы сварные. ОСТ 34-42-621/622/632 для энергетики.' ); ?></p></div>
          <div class="pc"><div class="pc-h">Термообработка</div><p class="pc-p">Нормализация, отпуск, закалка для теплоустойчивых сталей. Контроль твёрдости и структуры металла.</p></div>
          <div class="pc"><div class="pc-h">Нестандартные изделия</div><p class="pc-p">Изготовление по КД заказчика. Любые марки стали, геометрические параметры, согласование по ТЗ.</p></div>
        </div>
      </div>
      <div class="prod-side">
        <div class="prod-side-lbl">Производственные показатели</div>
        <div class="pm-r"><span class="pm-k">DN диапазон, мм</span><span class="pm-v">15–1400</span></div>
        <div class="pm-r"><span class="pm-k">PN максимум, МПа</span><span class="pm-v">160</span></div>
        <div class="pm-r"><span class="pm-k">Марок стали</span><span class="pm-v">7+</span></div>
        <div class="pm-r"><span class="pm-k">Типов исполнений</span><span class="pm-v">18+</span></div>
        <div class="pm-r"><span class="pm-k">Срок изготовления</span><span class="pm-v">от 14 дн.</span></div>
        <div class="pm-r"><span class="pm-k">Сертификация</span><span class="pm-v">ISO 9001</span></div>
      </div>
    </div>
  </section>
