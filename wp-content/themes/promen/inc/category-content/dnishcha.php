<?php
/**
 * Контент категории «dnishcha»: hero, карта типоисполнений, подбор, знания, модалка.
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
      <div class="hero-eyebrow">СДТ · Семейство изделий — изготовление под заказ</div>
      <h1 class="hero-h1">Днища<br><em>эллиптические</em><br>отбортованные</h1>
      <p class="hero-desc">Эллиптические отбортованные днища для сосудов, аппаратов и котлов по ГОСТ 6533-78: геометрия и толщина стенки по стандарту, тип ДЭ. В каталоге — DN 100–3800, стенка 4–90 мм, пять марок стали. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 100–3800</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">1 серия</span><span class="hp-k">ГОСТ 6533-78</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / DNISHCHA SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN / D, мм</span><span class="hud-rv">100 — 3800</span></div>
        <div class="hud-row"><span class="hud-rk">Стенка s, мм</span><span class="hud-rv">4 — 90</span></div>
        <div class="hud-row"><span class="hud-rk">Тип</span><span class="hud-rv">ДЭ · эллиптические</span></div>
        <div class="hud-row"><span class="hud-rk">PN</span><span class="hud-rv">по расчёту сосуда</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 6533-78</span><span class="hud-rv live">Действующий</span></div>
        <div class="hud-row"><span class="hud-rk">Область</span><span class="hud-rv live">Сосуды / котлы</span></div>
        <div class="hud-row"><span class="hud-rk">Исполнение</span><span class="hud-rv live">Отбортованные</span></div>
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
      <div class="map-root">
        <div class="map-root-label">Днища — диапазоны диаметра семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(3,1fr);">
        <div class="mg" data-type="ds">
          <div class="mg-hd"><div class="mg-code">ДЭ-С</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_dn_range_count( 'dnishcha', null, 600 ) ) ); ?> поз.</div></div>
          <div class="mg-name">Стандартный DN</div>
          <div class="mg-items">
            <div class="mg-item">Эллиптические отбортованные<span class="mg-norm">ГОСТ 6533-78</span></div>
            <div class="mg-item">Аппараты и коллекторы<span class="mg-norm">DN 100–600</span></div>
            <div class="mg-item">Стенка по ряду стандарта<span class="mg-norm">s от 4 мм</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 100–600</span><span class="mg-ftag">Основной ряд</span></div>
        </div>
        <div class="mg" data-type="dm">
          <div class="mg-hd"><div class="mg-code">ДЭ-М</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_dn_range_count( 'dnishcha', 600, 1400 ) ) ); ?> поз.</div></div>
          <div class="mg-name">Средний DN</div>
          <div class="mg-items">
            <div class="mg-item">Эллиптические отбортованные<span class="mg-norm">ГОСТ 6533-78</span></div>
            <div class="mg-item">Сосуды и котлы<span class="mg-norm">DN 600–1400</span></div>
            <div class="mg-item">Усиленная стенка<span class="mg-norm">по таблице ГОСТ</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 600–1400</span><span class="mg-ftag">Сосуды</span></div>
        </div>
        <div class="mg" data-type="dk">
          <div class="mg-hd"><div class="mg-code">ДЭ-К</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_dn_range_count( 'dnishcha', 1400, null ) ) ); ?> поз.</div></div>
          <div class="mg-name">Крупный DN</div>
          <div class="mg-items">
            <div class="mg-item">Крупногабаритные днища<span class="mg-norm">ГОСТ 6533-78</span></div>
            <div class="mg-item">Аппараты большого диаметра<span class="mg-norm">DN 1400–3800</span></div>
            <div class="mg-item">Транспорт и монтаж<span class="mg-norm">по согласованию</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 1400–3800</span><span class="mg-ftag">Крупный DN</span></div>
        </div>
      </div>
      
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор днища</div>
      <div class="s-meta">DNISHCHA / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача в аппарате / сосуде</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Закрыть цилиндрическую обечайку стандартным днищем</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Днища эллиптические отбортованные — тип ДЭ по ГОСТ 6533</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 6533-78</span><span class="sg-tag">тип ДЭ</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'dnishcha', 'gost-6533-1978' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'dnishcha', 'gost-6533-1978' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-6533-1978', $ctx['url'] ) ); ?>">К днищам в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">D (наружный / внутренний по базе проекта)</div><div class="sg-param">Толщина стенки s</div><div class="sg-param">Марка стали</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Крупный диаметр — DN свыше 1400</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Днища крупного DN — ряд ГОСТ 6533 до 3800 мм</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 6533-78</span><span class="sg-tag">DN 1400–3800</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'dnishcha', 'gost-6533-1978' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'dnishcha', 'gost-6533-1978' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-6533-1978', $ctx['url'] ) ); ?>">К крупным в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">D и s по чертежу сосуда</div><div class="sg-param">Условия монтажа и перевозки</div><div class="sg-param">Объём НК и паспортизация</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Коррозионная среда или повышенные параметры</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Днища из нержавеющих и легированных сталей каталога</div>
            <div class="sg-tags">
              <span class="sg-tag hi">12Х18Н10Т</span><span class="sg-tag">09Г2С · 13ХФА · 17Г1С</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( $ctx['url'] ); ?>">К реестру днищ →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Среда, температура, давление расчёта</div><div class="sg-param">Марка стали и требования к МКК</div><div class="sg-param">Поднадзорность (ТР ТС 032/2013)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Нестандартная геометрия, люк, штуцер или усиление</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Изготовление по КД заказчика — днища со штуцерами, усилениями</div>
            <div class="sg-tags">
              <span class="sg-tag hi">По чертежу</span><span class="sg-tag">Согласование 1–3 дня</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж днища / сосуда</div><div class="sg-param">Среда, давление, температура</div><div class="sg-param">Количество и срок поставки</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-dnishcha.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на днища">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на днища</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Днище" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 6533-78…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / D×s, мм</label><input id="om-dn" name="dn" type="text" placeholder="D 800 / s 10" autocomplete="off"></div>
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
