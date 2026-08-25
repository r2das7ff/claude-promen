<?php
/**
 * Секция 07 — маршрут контроля качества + цифровой паспорт (hover-подсветка).
 * Разметка 1:1 из design-reference/product-otvod-90.html; динамика — PHP.
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="s s-dark qc-wrap" id="s07">
    <div class="qc-scanline"></div>
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">07</span>Маршрут контроля качества</div>
      
    </div>
    <div class="prod-qc-grid">
      <div class="prod-qc-left reveal">
        <div>
          <p class="prod-qc-lead">От сертификата металла и номера плавки до ОТК, неразрушающего контроля и комплекта сопроводительной документации. Выберите этап — подсветится строка цифрового паспорта.</p>
        </div>
        <div>
          <div class="prod-qc-route-hd">Маршрут контроля качества</div>
          <div id="pqRoute">
            <div class="pq-item" data-stage="material"><span class="pq-dot"></span><span class="pq-num">01</span><span class="pq-name">Входной контроль металла</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="standard"><span class="pq-dot"></span><span class="pq-num">02</span><span class="pq-name">Проверка нормативной базы</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="heat"><span class="pq-dot"></span><span class="pq-num">03</span><span class="pq-name">Идентификация плавки</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="dn"><span class="pq-dot"></span><span class="pq-num">04</span><span class="pq-name">Операционный контроль</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="nk"><span class="pq-dot"></span><span class="pq-num">05</span><span class="pq-name">Неразрушающий контроль</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="geo"><span class="pq-dot"></span><span class="pq-num">06</span><span class="pq-name">Проверка геометрии</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="mark"><span class="pq-dot"></span><span class="pq-num">07</span><span class="pq-name">Маркировка изделия</span><span class="pq-ok">ОК</span></div>
            <div class="pq-item" data-stage="docs"><span class="pq-dot"></span><span class="pq-num">08</span><span class="pq-name">Паспорт и отгрузочные документы</span><span class="pq-ok">ОК</span></div>
          </div>
        </div>
      </div>
      <div class="prod-qc-right reveal">
        <div class="pq-pass-hd">
          <span class="pq-pass-lbl">Цифровой паспорт изделия</span>
        </div>
        <div class="pq-pass" id="qcPassport">
          <div class="pq-bracket tl"></div><div class="pq-bracket tr"></div><div class="pq-bracket bl"></div><div class="pq-bracket br"></div>
          <div class="pq-scan"></div>
          <div class="pq-head">
            <span class="pq-prod" id="qcProdTitle"><?php echo esc_html( $qc_title ); ?></span>
            <span class="pq-id" id="qcPassId">ПЭ-<?php echo esc_html( wp_date( 'Y' ) . '-' . ( $dn !== '' ? $dn : '000' ) ); ?></span>
          </div>
          <div class="pq-body">
            <div class="pq-row" data-field="material"><span class="pq-k">Материал</span><span class="pq-v" id="qcMat"><?php echo esc_html( $first_steel ); ?> · сертификат качества</span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="standard"><span class="pq-k">Стандарт</span><span class="pq-v"><?php echo esc_html( $norm_key ?: 'по нормативу' ); ?></span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="heat"><span class="pq-k">Плавка</span><span class="pq-v">по плавке поставки</span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="dn"><span class="pq-k">DN / PN</span><span class="pq-v" id="qcDnPn">DN <?php echo esc_html( $dn !== '' ? $dn : '—' ); ?><?php echo $pn !== '' ? ' / PN ' . esc_html( $pn ) : ''; ?></span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="nk"><span class="pq-k">Контроль НК</span><span class="pq-v">ВИК / УЗК / ОТК</span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="geo"><span class="pq-k">Геометрия</span><span class="pq-v">±0,5 мм · ПРИНЯТО</span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="mark"><span class="pq-k">Маркировка</span><span class="pq-v" id="qcMark">ПЭ · <?php echo esc_html( $first_steel ); ?> · плавка по поставке</span><span class="pq-chk">✓</span></div>
            <div class="pq-row" data-field="docs"><span class="pq-k">Документы</span><span class="pq-v">Сертификат / Паспорт / НК</span><span class="pq-chk">✓</span></div>
          </div>
          <div class="pq-ft">
            <div class="pq-status"><div class="pq-pulse"></div><span class="pq-accepted">Маршрут приёмки ОТК</span></div>
            <div class="pq-meta">Формируется для каждой партии при отгрузке</div>
          </div>
        </div>
        <div class="pq-ann">
          <div class="pq-ann-i"><span class="pq-ann-k">Партия</span><span class="pq-ann-v">24 шт.</span></div>
          <div class="pq-ann-i"><span class="pq-ann-k">Объект приёмки</span><span class="pq-ann-v">ТЭС-2</span></div>
          <div class="pq-ann-i"><span class="pq-ann-k">Регистрация ОТК</span><span class="pq-ann-v">ПЭ/ОТК/26-471</span></div>
          <div class="pq-ann-i"><span class="pq-ann-k">Срок архива</span><span class="pq-ann-v">20 лет</span></div>
        </div>
      </div>
    </div>
  </section>
