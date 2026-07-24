<?php
/**
 * Контент категории «zaglushki»: hero, карта типоисполнений, подбор, знания, модалка.
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
      <h1 class="hero-h1">Заглушки<br><em>эллиптические</em><br>и фланцевые</h1>
      <p class="hero-desc">Эллиптические приварные заглушки по ГОСТ 17379-2001 и фланцевые на Ру свыше 10 до 100 МПа по ГОСТ 22815-83 — для глухого закрытия торца трубы или штуцера при монтаже, испытаниях и эксплуатации. Две серии, DN 6–600, шесть марок стали. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v"><?php echo esc_html( number_format_i18n( $ctx['count'] ) ); ?></span><span class="hp-k">Типоразмеров</span></div>
        <div class="hp"><span class="hp-v">DN 6–600</span><span class="hp-k">Диапазон</span></div>
        <div class="hp"><span class="hp-v">2 серии</span><span class="hp-k">ГОСТ 17379 / 22815</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / ZAGLUSHKI SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">6 — 600</span></div>
        <div class="hud-row"><span class="hud-rk">Типы</span><span class="hud-rv">ЗЭ · ЗФ</span></div>
        <div class="hud-row"><span class="hud-rk">Ру (фланцевые)</span><span class="hud-rv">св. 10 до 100 МПа</span></div>
        <div class="hud-row"><span class="hud-rk">Марки стали</span><span class="hud-rv">6 марок</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 17379-2001</span><span class="hud-rv live">Эллиптические</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 22815-83</span><span class="hud-rv live">Фланцевые Ру 100</span></div>
        <div class="hud-row"><span class="hud-rk">Область</span><span class="hud-rv live">Трубопроводы / НГК</span></div>
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
        <div class="map-root-label">Заглушки — типоисполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(2,1fr);">
        <div class="mg" data-type="ze">
          <div class="mg-hd"><div class="mg-code">ЗЭ</div><div class="mg-cnt">93 поз.</div></div>
          <div class="mg-name">Эллиптические приварные</div>
          <div class="mg-items">
            <div class="mg-item">Бесшовные приварные встык<span class="mg-norm">ГОСТ 17379-2001</span></div>
            <div class="mg-item">Эллиптическая форма<span class="mg-norm">ИСО 3419-81</span></div>
            <div class="mg-item">Исполнения 1 и 2 по таблицам<span class="mg-norm">DN 15–600</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 15–600</span><span class="mg-ftag">Основной тип</span></div>
        </div>
        <div class="mg" data-type="zf">
          <div class="mg-hd"><div class="mg-code">ЗФ</div><div class="mg-cnt">35 поз.</div></div>
          <div class="mg-name">Фланцевые Ру до 100 МПа</div>
          <div class="mg-items">
            <div class="mg-item">Фланцевое присоединение<span class="mg-norm">ГОСТ 22815-83</span></div>
            <div class="mg-item">Высокое давление<span class="mg-norm">Ру св. 10 до 100 МПа</span></div>
            <div class="mg-item">Нефтехимия / удобрения<span class="mg-norm">DN 6–200</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">Ру до 100 МПа</span><span class="mg-ftag">Поднадзорные</span></div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор заглушки</div>
      <div class="s-meta">ZAGLUSHKI / SELECTION GUIDE</div>
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
            <div class="sg-task-h">Глухое закрытие торца трубы приваркой</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Заглушки эллиптические приварные — тип ЗЭ по ГОСТ 17379</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17379-2001</span><span class="sg-tag">тип ЗЭ</span><span class="sg-tag">93 позиции</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-17379-2001', $ctx['url'] ) ); ?>">К эллиптическим в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">D×s трубы (или DN и стенка)</div><div class="sg-param">Марка стали</div><div class="sg-param">Исполнение 1 или 2</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Временное / разъёмное закрытие на высоком давлении</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Заглушки фланцевые на Ру св. 10 до 100 МПа — ГОСТ 22815</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 22815-83</span><span class="sg-tag">тип ЗФ</span><span class="sg-tag">35 позиций</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22815-1983', $ctx['url'] ) ); ?>">К фланцевым в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN и Ру (или рабочее давление)</div><div class="sg-param">Исполнение фланца (1–4)</div><div class="sg-param">Марка стали (в т.ч. 20Х3МВФ)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Коррозионная среда или северное исполнение</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Заглушки из нержавеющих и хладостойких сталей каталога</div>
            <div class="sg-tags">
              <span class="sg-tag hi">12Х18Н10Т</span><span class="sg-tag">09Г2С · 13ХФА · 17Г1С</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( $ctx['url'] ); ?>">К реестру заглушек →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Среда, температура, давление</div><div class="sg-param">Марка стали и требования к МКК</div><div class="sg-param">Поднадзорность (ТР ТС 032/2013)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Нестандартная геометрия или исполнение по КД</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Изготовление по КД заказчика — заглушки нестандартных размеров</div>
            <div class="sg-tags">
              <span class="sg-tag hi">По чертежу</span><span class="sg-tag">Согласование 1–3 дня</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж заглушки / узла</div><div class="sg-param">Среда, давление, температура</div><div class="sg-param">Количество и срок поставки</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void {
		include get_theme_file_path( 'woocommerce/parts/kb-zaglushki.php' );
	},
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на заглушки">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на заглушки</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Заглушка" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 17379, ГОСТ 22815…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / D×s, мм</label><input id="om-dn" name="dn" type="text" placeholder="DN 100 / 108×4" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Давление, МПа</label><input id="om-pn" name="pn" type="text" placeholder="PN 16 / Ру 100" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка стали</label><input id="om-mat" name="material" type="text" placeholder="09Г2С, 20Х3МВФ…" autocomplete="off"></div>
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
