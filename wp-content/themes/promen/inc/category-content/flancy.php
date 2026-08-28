<?php
/**
 * Контент категории «flancy»: hero, карта типоисполнений, подбор, знания, модалка.
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
      <div class="hero-eyebrow">ФЛ · Семейство изделий — изготовление под заказ</div>
      <h1 class="hero-h1">Фланцы<br><em>трубопроводные</em><br>стальные</h1>
      <p class="hero-desc">Приварные и плоские фланцы для разъёмных соединений трубопроводов и арматуры: ГОСТ 33259-2015 (типы 01 и 11, PN до 250), плоские по ГОСТ 12820-80, воротниковые по ГОСТ 12821-80, сосудовые по ГОСТ 28759.2-2022. Четыре серии, DN 10–4000. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 10–4000</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">4 серии</span><span class="hp-k">PN 1–250</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <h2 class="hud-label">Технические диапазоны / FLANCY SPECS</h2>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">10 — 4000</span></div>
        <div class="hud-row"><span class="hud-rk">PN, МПа</span><span class="hud-rv">1 — 250</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">01 · 11 · ФП · ФВ</span></div>
        <div class="hud-row"><span class="hud-rk">Марки стали</span><span class="hud-rv">7 марок</span></div>
      </div>
      <div class="hud-block">
        <h2 class="hud-label">Нормативный статус</h2>
        <div class="hud-row"><span class="hud-rk">ГОСТ 33259-2015</span><span class="hud-rv live">Тип 01 / 11</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 12820-80 / 12821-80</span><span class="hud-rv live">ФП / ФВ</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 28759.2-2022</span><span class="hud-rv live">Сосудовые</span></div>
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
        <div class="map-root-label">Фланцы — типоисполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(4,1fr);">
        <div class="mg" data-type="t11">
          <div class="mg-hd"><div class="mg-code">11</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'flancy-11' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Воротниковые тип 11</div>
          <div class="mg-items">
            <div class="mg-item">Приварные встык<span class="mg-norm">ГОСТ 33259-2015</span></div>
            <div class="mg-item">PN до 250<span class="mg-norm">основной ряд</span></div>
            <div class="mg-item">Трубопроводы и арматура<span class="mg-norm">DN 10–2400</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 10–2400</span><span class="mg-ftag">Основной тип</span></div>
        </div>
        <div class="mg" data-type="t01">
          <div class="mg-hd"><div class="mg-code">01</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_catalog_group_count( 'flancy-01' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Плоские тип 01</div>
          <div class="mg-items">
            <div class="mg-item">Плоские по ГОСТ 33259-2015<span class="mg-norm">ГОСТ 33259-2015</span></div>
            <div class="mg-item">PN до 250<span class="mg-norm">типоразмерный ряд</span></div>
            <div class="mg-item">Разъёмные соединения<span class="mg-norm">DN 10–2400</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">Тип 01</span><span class="mg-ftag">PN до 250</span></div>
        </div>
        <div class="mg" data-type="fp">
          <div class="mg-hd"><div class="mg-code">ФП</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'flancy', 'fp' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Плоские приварные</div>
          <div class="mg-items">
            <div class="mg-item">Трубопроводные<span class="mg-norm">ГОСТ 12820-80</span></div>
            <div class="mg-item">Сосудовые<span class="mg-norm">ГОСТ 28759.2-2022</span></div>
            <div class="mg-item">Ру 0,1–2,5 МПа / аппараты<span class="mg-norm">DN 10–4000</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">ФП</span><span class="mg-ftag"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'flancy', 'fp' ) ) ); ?> поз.</span></div>
        </div>
        <div class="mg" data-type="fv">
          <div class="mg-hd"><div class="mg-code">ФВ</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'flancy', 'fv' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Воротниковые 12821</div>
          <div class="mg-items">
            <div class="mg-item">Приварные встык<span class="mg-norm">ГОСТ 12821-80</span></div>
            <div class="mg-item">Ру до 20 МПа<span class="mg-norm">t −253…+600 °C</span></div>
            <div class="mg-item">Трубопроводы и арматура<span class="mg-norm">DN 10–350</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">ФВ</span><span class="mg-ftag">Ру до 20</span></div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <h2 class="s-badge"><span class="s-badge-num">03</span>Подбор фланца</h2>
      <div class="s-meta">FLANCY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача на участке трубопровода</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Разъёмное соединение высокого давления / PN до 250</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Фланцы ГОСТ 33259 — тип 11 (воротниковые) или тип 01 (плоские)</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 33259-2015</span><span class="sg-tag">тип 11 / 01</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'flancy', 'gost-33259-2015' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'flancy', 'gost-33259-2015' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-33259-2015', $ctx['url'] ) ); ?>">К фланцам 33259 в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN и PN</div><div class="sg-param">Тип 01 или 11</div><div class="sg-param">Исполнение уплотнительной поверхности</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Плоский фланец на низкое / среднее давление</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Фланцы плоские приварные ФП по ГОСТ 12820-80</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 12820-80</span><span class="sg-tag">ФП</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'flancy', 'gost-12820-1980' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'flancy', 'gost-12820-1980' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-12820-1980', $ctx['url'] ) ); ?>">К плоским в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN (Dy) и Ру 0,1–2,5 МПа</div><div class="sg-param">Марка стали</div><div class="sg-param">Температура среды</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Воротниковый фланец / приварка встык</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Фланцы ФВ по ГОСТ 12821-80 или тип 11 по ГОСТ 33259-2015</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 12821-80</span><span class="sg-tag">тип 11</span><span class="sg-tag">ФВ</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-12821-1980', $ctx['url'] ) ); ?>">К воротниковым в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN и Ру / PN</div><div class="sg-param">Стыковка с трубой (разделка)</div><div class="sg-param">Марка стали</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Фланец сосуда / аппарата крупного DN</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Сосудовые фланцы по ГОСТ 28759.2-2022</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 28759.2-2022</span><span class="sg-tag">DN 400–4000</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'flancy', 'gost-28759-2-2022' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'flancy', 'gost-28759-2-2022' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-28759-2-2022', $ctx['url'] ) ); ?>">К сосудовым в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">DN аппарата и расчётное давление</div><div class="sg-param">Марка стали</div><div class="sg-param">Объём НК и паспортизация</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 05</div>
            <div class="sg-task-h">Нестандартная геометрия или исполнение по КД</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Изготовление по КД заказчика — фланцы нестандартных размеров</div>
            <div class="sg-tags">
              <span class="sg-tag hi">По чертежу</span><span class="sg-tag">Согласование 1–3 дня</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж фланца / узла</div><div class="sg-param">Среда, давление, температура</div><div class="sg-param">Количество и срок поставки</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-flancy.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на фланцы">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на фланцы</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Фланец" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 33259-2015, ГОСТ 12820-80…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / Dy, мм</label><input id="om-dn" name="dn" type="text" placeholder="DN 100" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Давление, МПа</label><input id="om-pn" name="pn" type="text" placeholder="PN 16 / Ру 2,5" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка стали</label><input id="om-mat" name="material" type="text" placeholder="09Г2С, 12Х18Н10Т…" autocomplete="off"></div>
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
