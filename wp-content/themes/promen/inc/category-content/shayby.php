<?php
/**
 * Контент категории «shayby»: hero, карта типоисполнений, подбор, знания, модалка.
 * Извлечено 1:1 из прежнего taxonomy-шаблона; каркас — inc/category-page.php.
 */

defined( 'ABSPATH' ) || exit;

return [
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
      <div class="hero-eyebrow">КР / Шайбы · изготовление под заказ</div>
      <h1 class="hero-h1">Шайбы<br><em>пружинные</em><br>и усиленные</h1>
      <p class="hero-desc">Шайбы ГОСТ 6402 и усиленные ГОСТ 11371 для болтовых и шпилечных соединений. В реестре — 98 позиций. Подбор по номинальному диаметру резьбы M.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">M × L</span><span class="hp-k">Резьба / длина</span></div>
        <div class="hp"><span class="hp-v">Ш</span><span class="hp-k">Тип крепежа</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
        <a class="s10-ghost-link" href="<?php echo esc_url( $ctx['url'] ); ?>">Открыть полный реестр</a>
      </div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / SHAYBY SPECS</div>
        <div class="hud-row"><span class="hud-rk">Резьба M</span><span class="hud-rv">по типоразмеру</span></div>
        <div class="hud-row"><span class="hud-rk">Длина L</span><span class="hud-rv">по карточке</span></div>
        <div class="hud-row"><span class="hud-rk">Семейство</span><span class="hud-rv">6402 · 11371</span></div>
        <div class="hud-row"><span class="hud-rk">В группе КР</span><span class="hud-rv"><?php echo esc_html( $ctx['parent_name'] !== '' ? $ctx['parent_name'] : 'Крепёж' ); ?></span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 6402-1970</span><span class="hud-rv live">80 поз.</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 11371-1978</span><span class="hud-rv live">18 поз.</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
        <div class="hud-row"><span class="hud-rk">Комплект</span><span class="hud-rv live">с болтом / шпилькой</span></div>
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
      <div class="map-root">
        <div class="map-root-label">Шайбы — исполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(1,1fr);max-width:480px;">
        <div class="mg" data-type="main">
          <div class="mg-hd"><div class="mg-code">Ш</div><div class="mg-cnt">98 поз.</div></div>
          <div class="mg-name">Шайбы</div>
          <div class="mg-items">
            <div class="mg-item">Пружинные / плоские<span class="mg-norm">ГОСТ 6402</span></div><div class="mg-item">Усиленные<span class="mg-norm">ГОСТ 11371</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">Ш</span><span class="mg-ftag">98 поз.</span></div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор — шайбы</div>
      <div class="s-meta">SHAYBY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача на участке</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Шайба в комплект фланцевого крепежа</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Шайбы ГОСТ 6402</div>
            <div class="sg-tags">
              <span class="sg-tag hi">Ш</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-6402-1970', $ctx['url'] ) ); ?>">К позициям в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">M под резьбу шпильки/болта</div><div class="sg-param">Количество</div><div class="sg-param">Покрытие</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Усиленная шайба</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Шайбы ГОСТ 11371</div>
            <div class="sg-tags">
              <span class="sg-tag hi">Ш</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-11371-1978', $ctx['url'] ) ); ?>">К позициям в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">M</div><div class="sg-param">Нагрузка узла</div><div class="sg-param">Материал</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Комплект шайб к шпилькам</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Шайбы + шпильки / гайки</div>
            <div class="sg-tags">
              <span class="sg-tag hi">Ш</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-6402-1970', $ctx['url'] ) ); ?>">К позициям в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">M×L шпильки</div><div class="sg-param">Число шайб на соединение</div><div class="sg-param">Срок</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Нестандартная шайба</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Изготовление по КД</div>
            <div class="sg-tags">
              <span class="sg-tag hi">Ш</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж</div><div class="sg-param">Материал</div><div class="sg-param">Количество</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-shayby.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на шайбы">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на шайбы</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Шайбы" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 7798, ГОСТ 9066, ОСТ 26-2040…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">Резьба M</label><input id="om-dn" name="dn" type="text" placeholder="M16" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Длина L, мм</label><input id="om-pn" name="pn" type="text" placeholder="80" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка / класс прочности</label><input id="om-mat" name="material" type="text" placeholder="Ст20 / 5.6 / 8.8…" autocomplete="off"></div>
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
