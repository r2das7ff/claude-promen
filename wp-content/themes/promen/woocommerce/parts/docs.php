<?php
/**
 * Секция 08 — комплект документов (PDF на сайт не выкладываем — решение от 2026-07-17).
 * Разметка 1:1 из design-reference/product-otvod-90.html; динамика — PHP.
 */
defined( 'ABSPATH' ) || exit;
// Крепёж: гидроиспытаний и DN/PN у болта нет — свои документы партии.
$docs_fx = ! empty( $is_fastener );
?>
<section class="s" id="s08">
    <div class="s-hd">
      <h2 class="s-badge"><span class="s-badge-num">08</span>Комплект документов</h2>
      
    </div>
    <div class="s-body">
      <div class="docs-grid reveal">
        <div class="doc-dl">
          <span class="doc-dl-n">01</span>
          <div><div class="doc-dl-t">Паспорт изделия</div><div class="doc-dl-d"><?php echo $docs_fx ? 'Документ о качестве на партию: марка стали, класс прочности, покрытие, результаты испытаний, подпись ОТК.' : 'Технические характеристики, маркировка, результаты контроля, подпись ОТК. Обязателен для всех позиций СДТ.'; ?></div><span class="doc-dl-fmt">Выдаётся с поставкой</span></div>
        </div>
        <div class="doc-dl">
          <span class="doc-dl-n">02</span>
          <div><div class="doc-dl-t">Бланк паспорта (шаблон)</div><div class="doc-dl-d">Форма для заполнения при отгрузке. Структура полей соответствует внутреннему регламенту завода.</div><span class="doc-dl-fmt">Выдаётся с поставкой</span></div>
        </div>
        <div class="doc-dl">
          <span class="doc-dl-n">03</span>
          <div><div class="doc-dl-t">Сертификат на металл 3.1</div><div class="doc-dl-d">Химический состав, механические свойства, номер плавки. Прослеживаемость от металлургического завода.</div><span class="doc-dl-fmt">По запросу</span></div>
        </div>
        <div class="doc-dl">
          <span class="doc-dl-n">04</span>
          <?php if ( $docs_fx ) : ?>
          <div><div class="doc-dl-t">Протокол контроля резьбы и геометрии</div><div class="doc-dl-d">Резьба — калибрами ПР/НЕ, размеры под ключ и длина — по таблице стандарта изготовления.</div><span class="doc-dl-fmt">С партией</span></div>
          <?php else : ?>
          <div><div class="doc-dl-t">Протоколы неразрушающего контроля</div><div class="doc-dl-d">УЗК, ВИК, РК — по объёму, установленному нормативной базой и требованиями заказчика.</div><span class="doc-dl-fmt">По объёму НК</span></div>
          <?php endif; ?>
        </div>
        <div class="doc-dl">
          <span class="doc-dl-n">05</span>
          <?php if ( $docs_fx ) : ?>
          <div><div class="doc-dl-t">Протокол механических испытаний</div><div class="doc-dl-d">Твёрдость, разрыв, для болтов — испытание на косой шайбе по ГОСТ ISO 898-1; объём — по стандарту и требованиям заказчика.</div><span class="doc-dl-fmt">По согласованию</span></div>
          <?php else : ?>
          <div><div class="doc-dl-t">Акт гидравлических испытаний</div><div class="doc-dl-d">Испытание давлением 1,5×PN. Протокол для критических участков паропроводов и сосудов давления.</div><span class="doc-dl-fmt">По согласованию</span></div>
          <?php endif; ?>
        </div>
        <div class="doc-dl">
          <span class="doc-dl-n">06</span>
          <div><div class="doc-dl-t">Чертёж КД / DXF</div><div class="doc-dl-d">Конструкторская документация для согласования на объекте. Выдаётся по запросу с указанием <?php echo $docs_fx ? 'резьбы и длины' : 'DN и PN'; ?>.</div><span class="doc-dl-fmt">DWG / DXF</span></div>
        </div>
      </div>
    </div>
  </section>
