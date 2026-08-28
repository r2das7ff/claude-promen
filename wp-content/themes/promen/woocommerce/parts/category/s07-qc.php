<?php
/** Секция 07 «Контроль качества» — общая для всех категорий. */
defined( 'ABSPATH' ) || exit;
?>
<section class="s s-dark qc-wrap" id="s07">
    <div class="qc-scanline"></div>
    <div class="s-hd">
      <h2 class="s-badge"><span class="s-badge-num">07</span>Контроль качества</h2>
      <div class="s-meta">QC ROUTE / TRACEABILITY</div>
    </div>
    <div class="qc-grid">
      <div class="qc-stages reveal" id="qcStages">
        <div class="qc-s" data-stage="material">
          <div class="qs-n">01</div>
          <div><div class="qs-h">Входной контроль металла</div><div class="qs-d">Проверка сертификатов 3.1, химического состава, механических свойств. Сверка марки стали с конструкторской документацией.</div></div>
        </div>
        <div class="qc-s" data-stage="standard">
          <div class="qs-n">02</div>
          <div><div class="qs-h">Проверка нормативной базы</div><div class="qs-d">Соответствие ГОСТ, ОСТ, ТУ и КД заказчика. Верификация допусков, радиусов гиба, углов и геометрических параметров.</div></div>
        </div>
        <div class="qc-s" data-stage="heat">
          <div class="qs-n">03</div>
          <div><div class="qs-h">Идентификация плавки</div><div class="qs-d">Присвоение номера плавки, маркировка заготовки. Прослеживаемость от сертификата металла до готового изделия.</div></div>
        </div>
        <div class="qc-s" data-stage="dn">
          <div class="qs-n">04</div>
          <div><div class="qs-h">Операционный контроль</div><div class="qs-d">Контроль на каждой технологической операции: штамповка, гибка, сварка, термообработка. Журнал операций ОТК.</div></div>
        </div>
        <div class="qc-s" data-stage="nk">
          <div class="qs-n">05</div>
          <div><div class="qs-h">Неразрушающий контроль</div><div class="qs-d">УЗК, ВИК, РК сварных швов, капиллярный контроль. Объём НК согласно ПБ 03-585-03 для объектов АЭС.</div></div>
        </div>
        <div class="qc-s" data-stage="geo">
          <div class="qs-n">06</div>
          <div><div class="qs-h">Проверка геометрии</div><div class="qs-d">Контроль размеров на КИМ, проверка радиуса гиба, углов, толщины стенки. Допуск ±0,5 мм на критических участках.</div></div>
        </div>
        <div class="qc-s" data-stage="mark">
          <div class="qs-n">07</div>
          <div><div class="qs-h">Маркировка изделия</div><div class="qs-d">Нанесение маркировки: завод, марка стали, номер плавки, DN/PN, дата изготовления. Клеймение по ГОСТ 4666.</div></div>
        </div>
        <div class="qc-s" data-stage="docs">
          <div class="qs-n">08</div>
          <div><div class="qs-h">Паспорт и отгрузочные документы</div><div class="qs-d">Формирование паспорта изделия, протоколов НК, сертификата металла, акта гидравлических испытаний. Комплект для заказчика.</div></div>
        </div>
      </div>
      <div class="qc-docs reveal">
        <div class="qc-dh">Сопроводительная документация</div>
        <div class="doc-c"><div class="dc-n">01</div><div class="dc-t">Паспорт изделия</div><div class="dc-d">Полные технические характеристики, маркировка, результаты контроля, подпись ОТК. Обязателен для всех позиций СДТ.</div></div>
        <div class="doc-c"><div class="dc-n">02</div><div class="dc-t">Сертификат на металл 3.1</div><div class="dc-d">Химический состав, механические свойства, номер плавки. Прослеживаемость от металлургического завода.</div></div>
        <div class="doc-c"><div class="dc-n">03</div><div class="dc-t">Протоколы неразрушающего контроля</div><div class="dc-d">УЗК, ВИК, РК — по объёму, установленному нормативной базой и требованиями заказчика.</div></div>
        <div class="doc-c"><div class="dc-n">04</div><div class="dc-t">Акт гидравлических испытаний</div><div class="dc-d">Испытание давлением 1,5×PN. Протокол для критических участков паропроводов и сосудов давления.</div></div>
      </div>
    </div>
  </section>
