<?php
/**
 * Страница «Контакты» — 1:1 из html/contacts.html (Open Design, 2026-07-23).
 * Форма страницы серверная (mu-plugin promen-requests, preset=contact);
 * футер без s10 (у страницы своя форма) — фильтр promen_footer_form.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-11.CNT / REV.1' );

$promen_privacy_url = promen_privacy_url();
$promen_sent        = isset( $_GET['sent'] );

get_header();
?>
<div class="pg">

  <!-- HERO -->
  <div class="cnt-hero">
    <div>
      <div class="cnt-eyebrow">Прямая линия с заводом</div>
      <h1 class="cnt-h1">Свяжитесь<br><em>с нами</em></h1>
      <p class="cnt-desc">Передайте параметры изделия, чертёж или задачу — инженер завода свяжется с вами
        для уточнения нормативной базы, материала и срока изготовления.</p>
    </div>
    <div class="cnt-stats">
      <div class="hs"><span class="hs-v">2017</span><span class="hs-k">Основание завода</span></div>
      <div class="hs"><span class="hs-v">Челябинск</span><span class="hs-k">Производственная площадка</span></div>
      <div class="hs"><span class="hs-v">1 день</span><span class="hs-k">Срок ответа на запрос</span></div>
    </div>
  </div>

  <div class="cnt-wrap">
    <!-- LEFT: registry + location -->
    <div class="cnt-left">
      <div>
        <div class="cnt-label">Реквизиты и контакты</div>
        <div class="cnt-reg">
          <div class="cnt-row">
            <span class="cnt-row-k">Юрлицо</span>
            <span class="cnt-row-v">ООО «Завод Промышленная Энергетика»</span>
          </div>
          <div class="cnt-row">
            <span class="cnt-row-k">Адрес</span>
            <span class="cnt-row-v">454091, г. Челябинск, ул. Орджоникидзе, 37</span>
          </div>
          <div class="cnt-row">
            <span class="cnt-row-k">Телефон</span>
            <span class="cnt-row-v mono"><a href="tel:+73512170099">+7 (351) 217-00-99</a></span>
          </div>
          <div class="cnt-row">
            <span class="cnt-row-k">Почта</span>
            <span class="cnt-row-v mono"><a href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a></span>
          </div>
          <div class="cnt-row">
            <span class="cnt-row-k">Режим</span>
            <span class="cnt-row-v">Пн–Пт, 09:00–18:00 МСК</span>
          </div>
          <div class="cnt-row">
            <span class="cnt-row-k">Декларация</span>
            <span class="cnt-row-v mono">ТР ТС 032 RU С-RU.АБ53.В.08323/23 серия RU 0418908</span>
          </div>
        </div>
      </div>

      <div>
        <div class="cnt-label">Расположение</div>
        <div class="cnt-loc">
          <div class="cnt-loc-grid" aria-hidden="true"></div>
          <div class="cnt-loc-body">
            <div class="cnt-loc-mark" aria-hidden="true">
              <div class="cnt-loc-ring"></div>
              <div class="cnt-loc-dot"></div>
            </div>
            <div>
              <div class="cnt-loc-city">г. Челябинск</div>
              <div class="cnt-loc-coord">55.1644° N · 61.4368° E</div>
              <div class="cnt-loc-addr">ул. Орджоникидзе, 37 — производственная<br>площадка завода</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: request form -->
    <div class="cnt-right">
      <div class="cnt-label">Форма обратной связи — ПЭ-ФОРМА/11</div>
      <div class="s10-promise">
        <span class="s10-promise-item"><span class="s10-promise-dot"></span>Ответ в течение 1 рабочего дня</span>
        <span class="s10-promise-item"><span class="s10-promise-dot"></span>Обращение без обязательств</span>
      </div>
      <form id="cntForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo $promen_sent ? ' style="display:none;"' : ''; ?>>
        <input type="hidden" name="action" value="promen_request">
        <input type="hidden" name="preset" value="contact">
        <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
        <div style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;" aria-hidden="true">
          <label for="c-company-url">Company URL</label>
          <input id="c-company-url" name="company_url" type="text" tabindex="-1" autocomplete="off">
        </div>
        <div class="cnt-form">
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-name">Имя <span class="cnt-required">*</span></label>
            <input id="c-name" name="name" type="text" placeholder="Как к вам обращаться" required autocomplete="off">
          </div>
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-contact">Телефон или почта <span class="cnt-required">*</span></label>
            <input id="c-contact" name="contact" type="text" placeholder="+7 … / mail@company.ru" required autocomplete="off">
          </div>
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-company">Компания</label>
            <input id="c-company" name="company" type="text" placeholder="ООО «…»" autocomplete="off">
          </div>
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-topic">Тема обращения</label>
            <?php /* data-select — включает подменяющий список из assets/js/select.js. */ ?>
            <select id="c-topic" name="topic" data-select>
              <option>Коммерческий запрос / расчёт</option>
              <option>Техническая консультация</option>
              <option>Статус текущего заказа</option>
              <option>Сотрудничество / поставщикам</option>
              <option>Другое</option>
            </select>
          </div>
          <div class="cnt-field cnt-field--wide">
            <label class="cnt-field-label" for="c-msg">Сообщение</label>
            <textarea id="c-msg" name="task" placeholder="Параметры изделия, объём партии, срок — либо любой другой вопрос"></textarea>
          </div>
          <label class="cnt-consent" id="cntConsent">
            <input type="checkbox" id="c-consent" name="pd_consent" value="1" required>
            <span>Соглашаюсь на обработку персональных данных согласно<?php if ( $promen_privacy_url ) : ?> <a href="<?php echo esc_url( $promen_privacy_url ); ?>">Политике обработки ПДн</a><?php else : ?> Политике обработки ПДн<?php endif; ?></span>
          </label>
          <div class="cnt-actions">
            <button type="submit" class="cnt-submit">Отправить сообщение →</button>
            <a class="cnt-ghost" href="mailto:zakaz@prom-en.com">Написать напрямую</a>
          </div>
        </div>
      </form>
      <div class="cnt-success" id="cntSuccess"<?php echo $promen_sent ? ' style="display:block;"' : ''; ?>>
        ✓ <b>СООБЩЕНИЕ ОТПРАВЛЕНО.</b> Инженер завода свяжется с вами в течение рабочего дня.
      </div>
    </div>
  </div>

  <?php // Менеджеры направлений — общая часть с главной; ось rail даёт сам .pg. ?>
  <?php get_template_part( 'parts/managers', null, [ 'num' => 'ОП', 'flush' => true ] ); ?>
</div><!-- /.pg -->
<?php get_footer(); ?>
