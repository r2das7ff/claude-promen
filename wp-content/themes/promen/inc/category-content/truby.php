<?php
/**
 * Контент категории «truby»: hero, карта типоисполнений, подбор, знания, модалка.
 * Извлечено 1:1 из прежнего taxonomy-шаблона; каркас — inc/category-page.php.
 */

defined( 'ABSPATH' ) || exit;

return [
	's08_weld' => 'Трубы БШ и ЭС, ВГП для внутренних сетей. ГОСТ 8732 / 10704 / 3262; плети в ППУ — раздел «Изоляция и покрытия».',
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
      <div class="hero-eyebrow">ТР · Семейство изделий — поставка под заказ</div>
      <h1 class="hero-h1">Трубы<br><em>стальные</em><br>бесшовные и сварные</h1>
      <p class="hero-desc">Бесшовные (БШ), электросварные (ЭС) и водогазопроводные (ВГП): ГОСТ 8732 / 8734, ГОСТ 10704 / 10705, ГОСТ 3262. Три типа, DN от 15; трубы в ППУ-изоляции — в разделе «Изоляция и покрытия». Полный пакет документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 15–1400</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">3 типа</span><span class="hp-k">ЭС · БШ · ВГП</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / TRUBY SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN / D, мм</span><span class="hud-rv">15 — 1400</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">ЭС · БШ · ВГП</span></div>
        <div class="hud-row"><span class="hud-rk">Нормативы</span><span class="hud-rv">6 серий</span></div>
        <div class="hud-row"><span class="hud-rk">Плети в ППУ</span><span class="hud-rv">раздел «Изоляция»</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 8732-1978</span><span class="hud-rv live">БШ · 595</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 10704-1991</span><span class="hud-rv live">ЭС · 522</span></div>
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
      <div class="map-root"><div class="map-root-label">Трубы — типоисполнения семейства</div></div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(3,1fr);">
        <div class="mg"><div class="mg-hd"><div class="mg-code">БШ</div><div class="mg-cnt">700 поз.</div></div><div class="mg-name">Бесшовные</div>
          <div class="mg-items"><div class="mg-item">Гор. деформ.<span class="mg-norm">ГОСТ 8732</span></div><div class="mg-item">Хол. деформ.<span class="mg-norm">ГОСТ 8734</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">БШ</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">ЭС</div><div class="mg-cnt">733 поз.</div></div><div class="mg-name">Электросварные</div>
          <div class="mg-items"><div class="mg-item">Сортамент<span class="mg-norm">ГОСТ 10704</span></div><div class="mg-item">ТУ<span class="mg-norm">ГОСТ 10705</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ЭС</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">ВГП</div><div class="mg-cnt">70 поз.</div></div><div class="mg-name">Водогазопроводные</div>
          <div class="mg-items"><div class="mg-item">ГОСТ 3262<span class="mg-norm">70 поз.</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ВГП</span></div></div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор трубы</div>
      <div class="s-meta">TRUBY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача</div><div class="sg-th">Нужное исполнение</div><div class="sg-th">Что передать</div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 01</div><div class="sg-task-h">Бесшовная труба под давление / пар</div></div>
          <div class="sg-product"><div class="sg-prod-name">Трубы БШ ГОСТ 8732 / 8734</div>
            <div class="sg-tags"><span class="sg-tag hi">БШ</span><span class="sg-tag">700 поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-8732-1978', $ctx['url'] ) ); ?>">К бесшовным →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">D×s или DN</div><div class="sg-param">Марка стали</div><div class="sg-param">Длина / партия</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 02</div><div class="sg-task-h">Электросварная труба / теплосеть</div></div>
          <div class="sg-product"><div class="sg-prod-name">Трубы ЭС ГОСТ 10704 / 10705</div>
            <div class="sg-tags"><span class="sg-tag hi">ЭС</span><span class="sg-tag">733 поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-10704-1991', $ctx['url'] ) ); ?>">К электросварным →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">D×s</div><div class="sg-param">Сталь</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 03</div><div class="sg-task-h">Труба в ППУ для тепловых сетей</div></div>
          <div class="sg-product"><div class="sg-prod-name">Раздел «Изоляция и покрытия»</div>
            <div class="sg-tags"><span class="sg-tag hi">ППУ</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_izol_type_counts()['truby'] ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( promen_product_cat_link( 'izolyatsiya' ) ?: $ctx['shop_url'] ); ?>">К трубам в ППУ →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">D×s трубы</div><div class="sg-param">Оболочка ПЭ/ОЦ</div><div class="sg-param">Длина плети</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 04</div><div class="sg-task-h">Водогазопроводная труба</div></div>
          <div class="sg-product"><div class="sg-prod-name">Трубы ВГП ГОСТ 3262-1975</div>
            <div class="sg-tags"><span class="sg-tag hi">ВГП</span><span class="sg-tag">70 поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-3262-1975', $ctx['url'] ) ); ?>">К ВГП →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">Условный проход</div><div class="sg-param">Лёгкая/обычная/усиленная</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 05</div><div class="sg-task-h">Нестандарт / по спецификации</div></div>
          <div class="sg-product"><div class="sg-prod-name">Подбор и поставка по КД</div>
            <div class="sg-tags"><span class="sg-tag">КД</span></div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Форма запроса →</a></div>
          <div class="sg-params"><div class="sg-param-list"><div class="sg-param">Спецификация</div><div class="sg-param">Сталь / покрытие</div><div class="sg-param">Срок</div></div></div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-truby.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на трубы">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на трубы</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Труба" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 8732, ГОСТ 10704…" autocomplete="off"></div>
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
