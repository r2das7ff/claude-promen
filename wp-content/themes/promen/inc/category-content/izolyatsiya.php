<?php
/**
 * Контент категории «izolyatsiya»: hero, карта типоисполнений, подбор, знания, модалка.
 * Извлечено 1:1 из прежнего taxonomy-шаблона; каркас — inc/category-page.php.
 */

defined( 'ABSPATH' ) || exit;

return [
	's08_weld' => 'Изделия в ППУ по ГОСТ 30732: трубы-плети и тройники с оболочкой ПЭ или ОЦ. ГОСТ 30732-2020 · тепловые сети.',
	'hero' => static function ( array $ctx ): void { ?>
<div class="sdt-hero" id="hero">
    <div class="hero-left">
      <nav class="hero-crumb">
        <?php foreach ( $ctx['crumbs'] as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow">ИЗ · Семейство изделий — поставка под заказ</div>
      <h1 class="hero-h1">Изоляция<br><em>и покрытия</em><br>трубы и фасонина в ППУ</h1>
      <p class="hero-desc">Трубы-плети и тройники в пенополиуретановой изоляции по ГОСТ 30732-2020: оболочка полиэтиленовая (ПЭ) или оцинкованная (ОЦ). Для тепловых сетей. Полный пакет документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <?php $izl_dn = promen_canon_range_options( 'dn', 'izolyatsiya' ); ?>
        <div class="hp"><span class="hp-v">DN <?php echo esc_html( $izl_dn ? $izl_dn[0]['name'] . '–' . end( $izl_dn )['name'] : '—' ); ?></span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">ПЭ · ОЦ</span><span class="hp-k">Оболочка</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / IZOLYATSIYA SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv"><?php $izl_dn2 = promen_canon_range_options( 'dn', 'izolyatsiya' ); echo esc_html( $izl_dn2 ? $izl_dn2[0]['name'] . ' — ' . end( $izl_dn2 )['name'] : '—' ); ?></span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">Трубы · Тройники ППУ</span></div>
        <div class="hud-row"><span class="hud-rk">Оболочка</span><span class="hud-rv">ПЭ · ОЦ</span></div>
        <div class="hud-row"><span class="hud-rk">Норматив</span><span class="hud-rv">ГОСТ 30732-2020</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 30732-2020</span><span class="hud-rv live"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'izolyatsiya', 'gost-30732-2020' ) ) ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Оболочка ПЭ</span><span class="hud-rv live">бесканальная</span></div>
        <div class="hud-row"><span class="hud-rk">Оболочка ОЦ</span><span class="hud-rv live">канальная / надзем</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
      </div>
    </div>
  </div>
<?php },
	's02' => static function ( array $ctx ): void { ?>
<section class="s map-outer" id="s02">
    <div class="map-grid"></div>
    <div class="s-hd" style="border-bottom:1px solid rgba(109,140,166,.15);">
      <div class="s-badge s-dark" style="display:flex;"><span class="s-badge-num">02</span><span style="color:rgba(109,140,166,.6);font-family:'DINPro',monospace;font-size:8px;letter-spacing:.28em;text-transform:uppercase;margin-left:14px;">Карта типоисполнений</span></div>
      <div class="s-meta">PRODUCT TYPE MAP</div>
    </div>
    <div class="map-body">
      <div class="map-root"><div class="map-root-label">Изоляция — типоисполнения семейства</div></div>
      <?php $izl = promen_izol_type_counts(); ?>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(3,1fr);">
        <div class="mg"><div class="mg-hd"><div class="mg-code">ТР</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( $izl['truby'] ) ); ?> поз.</div></div><div class="mg-name">Трубы в ППУ</div>
          <div class="mg-items"><div class="mg-item">Плети для теплосетей<span class="mg-norm">ГОСТ 30732</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ТР</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">ПЭ</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( $izl['pe'] ) ); ?> поз.</div></div><div class="mg-name">Тройники · оболочка ПЭ</div>
          <div class="mg-items"><div class="mg-item">Тройники ППУ<span class="mg-norm">бесканальная</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ГОСТ 30732</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">ОЦ</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( $izl['oc'] ) ); ?> поз.</div></div><div class="mg-name">Тройники · оболочка ОЦ</div>
          <div class="mg-items"><div class="mg-item">Тройники ППУ<span class="mg-norm">канальная / надзем</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ГОСТ 30732</span></div></div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор изоляции</div>
      <div class="s-meta">IZOLYATSIYA / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача</div><div class="sg-th">Нужное исполнение</div><div class="sg-th">Что передать</div>
        </div>
        <?php $izl3 = promen_izol_type_counts(); ?>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 01</div><div class="sg-task-h">Труба в ППУ (прямые участки)</div></div>
          <div class="sg-product"><div class="sg-prod-name">Труба ППУ ГОСТ 30732-2020</div>
            <div class="sg-tags"><span class="sg-tag hi">ТР</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( $izl3['truby'] ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'q', 'труба', $ctx['url'] ) ); ?>">К трубам →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">D×s трубы</div><div class="sg-param">Оболочка</div><div class="sg-param">Длина плети</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 02</div><div class="sg-task-h">Тройник ППУ для бесканальной теплосети</div></div>
          <div class="sg-product"><div class="sg-prod-name">Тройник ППУ ПЭ ГОСТ 30732</div>
            <div class="sg-tags"><span class="sg-tag hi">ПЭ</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( $izl3['pe'] ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'q', 'ППУ-ПЭ', $ctx['url'] ) ); ?>">К реестру →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">D×s / d×s</div><div class="sg-param">Оболочка ПЭ</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 03</div><div class="sg-task-h">Тройник ППУ канальная / надземная прокладка</div></div>
          <div class="sg-product"><div class="sg-prod-name">Тройник ППУ ОЦ ГОСТ 30732</div>
            <div class="sg-tags"><span class="sg-tag hi">ОЦ</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( $izl3['oc'] ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'q', 'ППУ-ОЦ', $ctx['url'] ) ); ?>">К реестру →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">D×s / d×s</div><div class="sg-param">Оболочка ОЦ</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 04</div><div class="sg-task-h">Нестандарт / по спецификации</div></div>
          <div class="sg-product"><div class="sg-prod-name">Подбор по КД теплосети</div>
            <div class="sg-tags"><span class="sg-tag">КД</span></div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Форма запроса →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">Спецификация</div><div class="sg-param">ОДК</div><div class="sg-param">Срок</div></div></div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-izolyatsiya.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на изоляцию">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на изоляцию</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Изоляция ППУ" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 30732, оболочка ПЭ/ОЦ…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / D×s, мм</label><input id="om-dn" name="dn" type="text" placeholder="DN 100×80 / 108×4–89×4" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Давление, МПа</label><input id="om-pn" name="pn" type="text" placeholder="PN 16" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка стали</label><input id="om-mat" name="material" type="text" placeholder="09Г2С, 12Х1МФ…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-qty">Количество, шт</label><input id="om-qty" name="qty" type="text" placeholder="100" autocomplete="off"></div>
      <div class="om-field om-field--wide"><label class="om-lbl" for="om-contact">Ваш email / телефон *</label><input id="om-contact" name="contact" type="text" placeholder="Для ответа на запрос" required autocomplete="off"></div>
    </div>
    <div class="om-actions">
      <button type="submit" class="s10-submit">Отправить запрос →</button>
      <span class="om-note">Без обязательств · ответ за 1 рабочий день</span>
    </div>
  </form>
</div>
<?php },
];
