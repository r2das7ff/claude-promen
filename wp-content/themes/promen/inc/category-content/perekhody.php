<?php
/**
 * Контент категории «perekhody»: hero, карта типоисполнений, подбор, знания, модалка.
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
      <h1 class="hero-h1">Переходы<br><em>стальные</em><br>приварные</h1>
      <p class="hero-desc">Концентрические и эксцентрические переходы для стыковки участков трубопровода разных диаметров: бесшовные по ГОСТ 17378-2001, на Ру до 100 МПа по ГОСТ 22826-83, сварные по ОСТ 36-22-77 и ОСТ 34-10-753/754-97, точёные по СТО 318.01. Шесть серий, DN 15–1600. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 15–1600</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">6 серий</span><span class="hp-k">По ГОСТ / ОСТ / СТО</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / PEREKHODY SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">15 — 1600</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">ПК · ПЭ · ПТ</span></div>
        <div class="hud-row"><span class="hud-rk">Ру (высокое давление)</span><span class="hud-rv">до 100 МПа</span></div>
        <div class="hud-row"><span class="hud-rk">Температура среды, °C</span><span class="hud-rv">−50 — +510</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 17378-2001</span><span class="hud-rv live">Бесшовные</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 22826-83</span><span class="hud-rv live">Ру до 100 МПа</span></div>
        <div class="hud-row"><span class="hud-rk">ОСТ 36-22 / 34-10</span><span class="hud-rv live">Сварные</span></div>
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
        <div class="map-root-label">Переходы — типоисполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(4,1fr);">
        <div class="mg" data-type="psh">
          <div class="mg-hd"><div class="mg-code">ПШ</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'perekhody', 'psh' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Бесшовные приварные</div>
          <div class="mg-items">
            <div class="mg-item">Концентрические (ПК)<span class="mg-norm">ГОСТ 17378-2001</span></div>
            <div class="mg-item">Эксцентрические (ПЭ)<span class="mg-norm">ГОСТ 17378-2001</span></div>
            <div class="mg-item">Штамповка из трубы<span class="mg-norm">DN 32–600</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 32–600</span><span class="mg-ftag">Основной тип</span></div>
        </div>
        <div class="mg" data-type="p100">
          <div class="mg-hd"><div class="mg-code">П-100</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'tochenye', 'gost-22826-1983' ) ) ); ?> поз.</div></div>
          <div class="mg-name">На Ру до 100 МПа</div>
          <div class="mg-items">
            <div class="mg-item">Высокое давление<span class="mg-norm">ГОСТ 22826-83</span></div>
            <div class="mg-item">Нефтехимия / удобрения<span class="mg-norm">Ру св. 10 до 100</span></div>
            <div class="mg-item">Dy×dy от 10×6 до 200×150<span class="mg-norm">DN 25–200</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">Ру до 100 МПа</span><span class="mg-ftag">Поднадзорные</span></div>
        </div>
        <div class="mg" data-type="psv">
          <div class="mg-hd"><div class="mg-code">ПСВ</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'perekhody', 'psv' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Сварные крупный DN</div>
          <div class="mg-items">
            <div class="mg-item">Сварные конусные<span class="mg-norm">ОСТ 36-22-77</span></div>
            <div class="mg-item">Листовые для ТЭС<span class="mg-norm">ОСТ 34-10-753-97</span></div>
            <div class="mg-item">НК сварных швов<span class="mg-norm">ВИК / УЗК / РК</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 300–1600</span><span class="mg-ftag">ТЭС / ГРЭС</span></div>
        </div>
        <div class="mg" data-type="pt">
          <div class="mg-hd"><div class="mg-code">ПТ</div><div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'perekhody', 'pt' ) ) ); ?> поз.</div></div>
          <div class="mg-name">Точёные и мелкий DN</div>
          <div class="mg-items">
            <div class="mg-item">Точёные ЦКТИ<span class="mg-norm">СТО 318.01</span></div>
            <div class="mg-item">Сварные мелкий DN<span class="mg-norm">ОСТ 34.10.754-97</span></div>
            <div class="mg-item">Котельные и обвязка<span class="mg-norm">DN 15–65</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 15–65</span><span class="mg-ftag">ТЭС</span></div>
        </div>
      </div>
      
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор перехода</div>
      <div class="s-meta">PEREKHODY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача в трубопроводе</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Состыковать трубы разного DN с сохранением оси</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Переходы концентрические бесшовные — штампованные приварные</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17378-2001</span><span class="sg-tag">тип ПК</span><span class="sg-tag">DN 32–600</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-17378-2001', $ctx['url'] ) ); ?>">К бесшовным в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">D1×s1 и D2×s2 (напр. 219×8 — 159×6)</div><div class="sg-param">DN / PN участка</div><div class="sg-param">Марка стали и среда</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Нужен дренаж / смещение оси потока (эксцентрик)</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Переходы эксцентрические — бесшовные по ГОСТ 17378</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17378-2001</span><span class="sg-tag">тип ПЭ</span><span class="sg-tag">эксцентрические</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-17378-2001', $ctx['url'] ) ); ?>">К эксцентрическим в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">D1×s1 и D2×s2, ориентация «плоской» стороны</div><div class="sg-param">PN, марка стали</div><div class="sg-param">Объект: ТЭС / АЭС / нефтехим</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Трубопровод высокого давления — свыше 10 до 100 МПа</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Переходы на Ру до 100 МПа — ГОСТ 22826-83</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 22826-83</span><span class="sg-tag">DN 25–200</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'tochenye', 'gost-22826-1983' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'tochenye', 'gost-22826-1983' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22826-1983', $ctx['url'] ) ); ?>">К переходам Ру 100 в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Рабочее давление Ру и температура (−50…+510 °C)</div><div class="sg-param">Dy×dy и исполнение по стандарту</div><div class="sg-param">Марка стали (в т.ч. 20ХЗМВФ)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Крупный DN / трубопровод ТЭС — штамповка недоступна</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Переходы сварные — ОСТ 36-22-77 и ОСТ 34-10-753-97</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ОСТ 36-22-77</span><span class="sg-tag hi">ОСТ 34-10-753</span><span class="sg-tag">DN 300–1600</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'ost-36-22-77', $ctx['url'] ) ); ?>">К сварным в реестре →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">D1×s1 и D2×s2 магистрали</div><div class="sg-param">Объём НК сварных швов (ВИК / УЗК / РК)</div><div class="sg-param">Категория трубопровода ТЭС</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 05</div>
            <div class="sg-task-h">Нестандартная геометрия, сталь или усиление</div>
          </div>
          <div class="sg-product" data-label="Нужное исполнение">
            <div class="sg-prod-name">Изготовление по КД заказчика — спецпереходы, усиленные стенки</div>
            <div class="sg-tags">
              <span class="sg-tag hi">По чертежу</span><span class="sg-tag">Согласование 1–3 дня</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params" data-label="Что передать для расчёта">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж или эскиз с D1×D2 и длиной</div><div class="sg-param">Среда, давление, температура</div><div class="sg-param">Количество и срок поставки</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-perekhody.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на переходы">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на переходы</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Переход" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 17378, ГОСТ 22826…" autocomplete="off"></div>
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
