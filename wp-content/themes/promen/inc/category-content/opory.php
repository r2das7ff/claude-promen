<?php
/**
 * Контент категории «opory»: hero, карта типоисполнений, подбор, знания, модалка.
 * Извлечено 1:1 из прежнего taxonomy-шаблона; каркас — inc/category-page.php.
 */

defined( 'ABSPATH' ) || exit;

return [
	's08_weld' => 'Опоры и подвески трассы; арматура — по спецификации объекта. ОСТ 36-17 / ГОСТ 33257.',
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
      <div class="hero-eyebrow">ОП · Семейство изделий — поставка под заказ</div>
      <h1 class="hero-h1">Опоры<br><em>трубопроводов</em><br>неподвижные и скользящие</h1>
      <p class="hero-desc">Неподвижные хомутовые, скользящие и пружинные опоры по ОСТ 36-17-85. DN от 50. Фиксация трассы и компенсация температурных перемещений. Полный пакет документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 50–1000</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">3 типа</span><span class="hp-k">НП · СК · ПР</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / OPORY SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">50 — 1000</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">НП · СК · ПР</span></div>
        <div class="hud-row"><span class="hud-rk">Норматив</span><span class="hud-rv">ОСТ 36-17-85</span></div>
        <div class="hud-row"><span class="hud-rk">Материал</span><span class="hud-rv">Ст3сп · Ст20</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Состав реестра</div>
        <div class="hud-row"><span class="hud-rk">Неподвижные</span><span class="hud-rv live"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-nepodv' ) ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Скользящие</span><span class="hud-rv live"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-skolz' ) ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Пружинные</span><span class="hud-rv live"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-pruzh' ) ); ?> поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
      </div>
    </div>
  </div>
<?php },
	's02' => static function ( array $ctx ): void { ?>
<section class="s map-outer" id="s02">
    <div class="map-grid"></div>
    <div class="s-hd" style="border-bottom:1px solid rgba(109,140,166,.15);">
      <div class="s-badge s-dark" style="display:flex;"><span class="s-badge-num">02</span><span style="color:rgba(109,140,166,.6);font-family:'DINPro',monospace;font-size:10.5px;letter-spacing:.28em;text-transform:uppercase;margin-left:14px;">Карта типоисполнений</span></div>
      <div class="s-meta">PRODUCT TYPE MAP</div>
    </div>
    <div class="map-body">
      <div class="map-root"><div class="map-root-label">Опоры — типоисполнения семейства</div></div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(3,1fr);">
        <div class="mg"><div class="mg-hd"><div class="mg-code">НП</div><div class="mg-cnt"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-nepodv' ) ); ?> поз.</div></div><div class="mg-name">Неподвижные</div>
          <div class="mg-items"><div class="mg-item">Хомутовые<span class="mg-norm">фиксация</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ОСТ 36-17</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">СК</div><div class="mg-cnt"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-skolz' ) ); ?> поз.</div></div><div class="mg-name">Скользящие</div>
          <div class="mg-items"><div class="mg-item">Перемещения<span class="mg-norm">температура</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ОСТ 36-17</span></div></div>
        <div class="mg"><div class="mg-hd"><div class="mg-code">ПР</div><div class="mg-cnt"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-pruzh' ) ); ?> поз.</div></div><div class="mg-name">Пружинные</div>
          <div class="mg-items"><div class="mg-item">Подвески<span class="mg-norm">нагрузка</span></div></div>
          <div class="mg-footer"><span class="mg-ftag">ОСТ 36-17</span></div></div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор опоры</div>
      <div class="s-meta">OPORY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead"><div class="sg-th">Задача</div><div class="sg-th">Нужное исполнение</div><div class="sg-th">Что передать</div></div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 01</div><div class="sg-task-h">Зафиксировать участок трубопровода</div></div>
          <div class="sg-product" data-label="Нужное исполнение"><div class="sg-prod-name">Опора неподвижная хомутовая</div>
            <div class="sg-tags"><span class="sg-tag hi">НП</span><span class="sg-tag"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-nepodv' ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'group', 'opory-nepodv', $ctx['shop_url'] ) ); ?>">К неподвижным →</a></div>
          <div class="sg-params" data-label="Что передать"><div class="sg-param-list"><div class="sg-param">DN трубы</div><div class="sg-param">Нагрузка</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 02</div><div class="sg-task-h">Компенсировать температурные перемещения</div></div>
          <div class="sg-product" data-label="Нужное исполнение"><div class="sg-prod-name">Опора скользящая</div>
            <div class="sg-tags"><span class="sg-tag hi">СК</span><span class="sg-tag"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-skolz' ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'group', 'opory-skolz', $ctx['shop_url'] ) ); ?>">К скользящим →</a></div>
          <div class="sg-params" data-label="Что передать"><div class="sg-param-list"><div class="sg-param">DN</div><div class="sg-param">Ход / покрытие</div><div class="sg-param">Количество</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 03</div><div class="sg-task-h">Вертикальная нагрузка / подвеска</div></div>
          <div class="sg-product" data-label="Нужное исполнение"><div class="sg-prod-name">Опора пружинная</div>
            <div class="sg-tags"><span class="sg-tag hi">ПР</span><span class="sg-tag"><?php echo esc_html( (string) promen_catalog_group_count( 'opory-pruzh' ) ); ?> поз.</span></div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'group', 'opory-pruzh', $ctx['shop_url'] ) ); ?>">К пружинным →</a></div>
          <div class="sg-params" data-label="Что передать"><div class="sg-param-list"><div class="sg-param">DN</div><div class="sg-param">Нагрузка, кН</div><div class="sg-param">Ход пружины</div></div></div>
        </div>
        <div class="sg-row">
          <div class="sg-task"><div class="sg-task-code">Задача 04</div><div class="sg-task-h">По КД / нестандарт</div></div>
          <div class="sg-product" data-label="Нужное исполнение"><div class="sg-prod-name">Подбор по спецификации</div>
            <div class="sg-tags"><span class="sg-tag">КД</span></div>
            <a class="sg-link" href="#request">Форма запроса →</a></div>
          <div class="sg-params" data-label="Что передать"><div class="sg-param-list"><div class="sg-param">Чертёж</div><div class="sg-param">Нагрузки</div><div class="sg-param">Срок</div></div></div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-opory.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на опоры">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на опоры</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Опора" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ОСТ 36-17, DN, тип НП/СК/ПР…" autocomplete="off"></div>
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
