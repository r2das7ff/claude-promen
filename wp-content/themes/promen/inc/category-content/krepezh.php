<?php
/**
 * Контент категории «krepezh»: hero, карта типоисполнений, подбор, знания, модалка.
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
      <div class="hero-eyebrow">КР · Семейство изделий — изготовление под заказ</div>
      <h1 class="hero-h1">Крепёж<br><em>фланцевый</em><br>и монтажный</h1>
      <p class="hero-desc">Болты, шпильки, гайки, шайбы и винты для фланцевых соединений и общепромышленного монтажа: ГОСТ 7798-70 / 7805-70 / 22032-76, шпильки ГОСТ 15590-70 / 9066-75 и ОСТ 26-2040-96, гайки и шайбы по ряду ГОСТ. Пять типов, <?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?> <?php echo esc_html( promen_ru_plural( (int) $ctx['count'], 'позиция', 'позиции', 'позиций' ) ); ?>. Подбор по резьбе M и длине L.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">M × L</span><span class="hp-k">Резьба / длина</span></div>
        <div class="hp"><span class="hp-v">5 типов</span><span class="hp-k">Б · ШП · Г · Ш · В</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <h2 class="hud-label">Технические диапазоны / KREPEZH SPECS</h2>
        <div class="hud-row"><span class="hud-rk">Резьба M</span><span class="hud-rv">по типоразмеру</span></div>
        <div class="hud-row"><span class="hud-rk">Длина L, мм</span><span class="hud-rv">8 — 900</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">Б · ШП · Г · Ш · В</span></div>
        <div class="hud-row"><span class="hud-rk">Нормативы</span><span class="hud-rv">23 серии</span></div>
      </div>
      <div class="hud-block">
        <h2 class="hud-label">Нормативный статус</h2>
        <div class="hud-row"><span class="hud-rk">ГОСТ 7798-70 / 7805-70</span><span class="hud-rv live">Болты</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 15590-70 / 9066-75</span><span class="hud-rv live">Шпильки</span></div>
        <div class="hud-row"><span class="hud-rk">ОСТ 26-2040-96</span><span class="hud-rv live">Шпильки ОСТ</span></div>
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
      <div class="map-root">
        <div class="map-root-label">Крепёж — типоисполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(5,1fr);">
        <a class="mg" data-type="b" href="<?php echo esc_url( promen_product_cat_link( 'bolty' ) ); ?>">
          <div class="mg-hd"><div class="mg-code">Б</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'bolty' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Болты</div>
          <div class="mg-items">
            <div class="mg-item">Фундаментные<span class="mg-norm">ГОСТ 22032-76 / 22043-76</span></div>
            <div class="mg-item">Шестигранные<span class="mg-norm">ГОСТ 7798-70 / 7795-70</span></div>
            <div class="mg-item">Высокопрочные<span class="mg-norm">ГОСТ 10602-94</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">M × L</span><span class="mg-ftag">Страница семейства →</span></div>
        </a>
        <a class="mg" data-type="shp" href="<?php echo esc_url( promen_product_cat_link( 'shpilki' ) ); ?>">
          <div class="mg-hd"><div class="mg-code">ШП</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'shpilki' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Шпильки</div>
          <div class="mg-items">
            <div class="mg-item">Общепромышленные<span class="mg-norm">ГОСТ 15590-70</span></div>
            <div class="mg-item">Фланцевые<span class="mg-norm">ГОСТ 9066-75</span></div>
            <div class="mg-item">ОСТ для аппаратов<span class="mg-norm">ОСТ 26-2040-96</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">ШП</span><span class="mg-ftag">Страница семейства →</span></div>
        </a>
        <a class="mg" data-type="g" href="<?php echo esc_url( promen_product_cat_link( 'gayki' ) ); ?>">
          <div class="mg-hd"><div class="mg-code">Г</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'gayki' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Гайки</div>
          <div class="mg-items">
            <div class="mg-item">Фланцевые<span class="mg-norm">ГОСТ 9064-75</span></div>
            <div class="mg-item">Шестигранные<span class="mg-norm">ГОСТ 5915-70 / 10605-94</span></div>
            <div class="mg-item">Низкие / колпачковые<span class="mg-norm">ряд ГОСТ</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">Г</span><span class="mg-ftag">Страница семейства →</span></div>
        </a>
        <a class="mg" data-type="sh" href="<?php echo esc_url( promen_product_cat_link( 'shayby' ) ); ?>">
          <div class="mg-hd"><div class="mg-code">Ш</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'shayby' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Шайбы</div>
          <div class="mg-items">
            <div class="mg-item">Пружинные / плоские<span class="mg-norm">ГОСТ 6402-70</span></div>
            <div class="mg-item">Усиленные<span class="mg-norm">ГОСТ 11371-78</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">Ш</span><span class="mg-ftag">Страница семейства →</span></div>
        </a>
        <a class="mg" data-type="v" href="<?php echo esc_url( promen_product_cat_link( 'vinty' ) ); ?>">
          <div class="mg-hd"><div class="mg-code">В</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'vinty' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Винты</div>
          <div class="mg-items">
            <div class="mg-item">По ГОСТ 6958-78<span class="mg-norm">ГОСТ 6958-78</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">В</span><span class="mg-ftag">Страница семейства →</span></div>
        </a>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <h2 class="s-badge"><span class="s-badge-num">03</span>Подбор крепежа</h2>
      <div class="s-meta">KREPEZH / SELECTION GUIDE</div>
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
            <div class="sg-task-h">Крепёж фланцевой пары (шпилька + гайка)</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Шпильки ГОСТ 9066-75 / 15590-70 + гайки ГОСТ 9064-75</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ШП</span><span class="sg-tag">Г</span><span class="sg-tag">фланцы</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-9066-1970', $ctx['url'] ) ); ?>">К шпилькам 9066 в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN / PN фланца</div><div class="sg-param">Резьба M и длина L</div><div class="sg-param">Исполнение уплотнения</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Болтовое соединение / общепромышленный монтаж</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Болты с шестигранной головкой ГОСТ 7798-70 / 7795-70 / 7805-70</div>
            <div class="sg-tags">
              <span class="sg-tag hi">Б</span><span class="sg-tag">ГОСТ 7798-70</span><span class="sg-tag">M × L</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-7798-1970', $ctx['url'] ) ); ?>">К болтам 7798 в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Резьба M</div><div class="sg-param">Длина L, мм</div><div class="sg-param">Класс прочности / покрытие</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Фундаментный болт / анкерное крепление</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Болты фундаментные ГОСТ 22032-76 / 22043-76</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 22032-76</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'krepezh', 'gost-22032-1976' ) + promen_category_norm_count( 'krepezh', 'gost-22043-1976' ) ) ); ?> поз. семейства</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22032-1976', $ctx['url'] ) ); ?>">К фундаментным в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Тип исполнения по ГОСТ</div><div class="sg-param">M и L</div><div class="sg-param">Условия заделки</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Шпильки для сосудов и аппаратов (ОСТ)</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Шпильки ОСТ 26-2040-96</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ОСТ 26-2040-96</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'krepezh', 'ost-26-2040-96' ) ) ); ?> поз.</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'ost-26-2040-96', $ctx['url'] ) ); ?>">К шпилькам ОСТ в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">M и L по КД аппарата</div><div class="sg-param">Марка / класс</div><div class="sg-param">Объём НК</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 05</div>
            <div class="sg-task-h">Нестандартный крепёж / по КД</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Изготовление по чертежу заказчика</div>
            <div class="sg-tags">
              <span class="sg-tag">КД</span><span class="sg-tag">ТУ завода</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж / спецификация</div><div class="sg-param">Материал и покрытие</div><div class="sg-param">Количество и срок</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-krepezh.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на крепёж">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на крепёж</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Крепёж" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 7798-70, ГОСТ 9066-75, ОСТ 26-2040-96…" autocomplete="off"></div>
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
