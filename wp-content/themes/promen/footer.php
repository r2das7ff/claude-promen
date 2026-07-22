<?php
/**
 * Футер-зона: форма «Запросить КП» (s10) + тёмный футер.
 */
$catalog_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
?>
<div class="footer-zone">

<section class="s10" id="request">
  <div class="s10-inner">
    <div class="s10-left">
      <div class="s10-eyebrow">
        <span class="s10-eye-num">ЗАК</span>
        <span class="s10-eye-label">ЗАПРОС</span>
      </div>
      <h2 class="s10-h2">Передайте<br>нам<br>исходные<br>данные</h2>
      <p class="s10-sub">Укажите параметры изделия или прикрепите чертёж — мы подберём нормативный документ, материал и срок изготовления. Ответ в течение одного рабочего дня.</p>
      <div class="s10-contacts">
        <div class="s10-contact-row">
          <span class="s10-contact-k">ПОЧТА</span>
          <a class="s10-contact-v" href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a>
        </div>
        <div class="s10-contact-row">
          <span class="s10-contact-k">ТЕЛЕФОН</span>
          <a class="s10-contact-v" href="tel:+73512170099">+7 (351) 217-00-99</a>
        </div>
        <div class="s10-contact-row">
          <span class="s10-contact-k">АДРЕС</span>
          <span class="s10-contact-v">454091, Челябинск, ул. Орджоникидзе, 37</span>
        </div>
        <div class="s10-contact-row">
          <span class="s10-contact-k">ВРЕМЯ</span>
          <span class="s10-contact-v">Пн–Пт, 09:00–18:00 МСК</span>
        </div>
      </div>
    </div>
    <div class="s10-right">
      <div class="s10-form-label">ФОРМА ЗАПРОСА — ПЭ-ФОРМА/КТЛ</div>
      <?php $promen_sent = isset( $_GET['sent'] ); ?>
      <form id="s10-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo $promen_sent ? ' style="display:none;"' : ''; ?>>
        <?php if ( function_exists( 'is_product' ) && is_product() ) : ?>
          <input type="hidden" name="sku" value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_sku', true ) ); ?>">
        <?php endif; ?>
        <input type="hidden" name="action" value="promen_request">
        <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
        <div style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;" aria-hidden="true">
          <label for="f-company">Company</label>
          <input id="f-company" name="company_url" type="text" tabindex="-1" autocomplete="off">
        </div>
        <div class="s10-form">
          <div class="s10-field">
            <label class="s10-field-label" for="f-name">НАИМЕНОВАНИЕ</label>
            <input id="f-name" name="product" type="text" placeholder="Отвод 90°, тройник, переход, фланец…" autocomplete="off">
          </div>
          <div class="s10-field">
            <label class="s10-field-label" for="f-std">СТАНДАРТ</label>
            <input id="f-std" name="standard" type="text" placeholder="ГОСТ 17375, ОСТ 36, СТО ЦКТИ…" autocomplete="off">
          </div>
          <div class="s10-field">
            <label class="s10-field-label" for="f-dn">DN / D, мм</label>
            <input id="f-dn" name="dn" type="text" placeholder="DN 100 / Ø 108" autocomplete="off">
          </div>
          <div class="s10-field">
            <label class="s10-field-label" for="f-pn">ДАВЛЕНИЕ, МПа</label>
            <input id="f-pn" name="pn" type="text" placeholder="PN 160 / 16 МПа" autocomplete="off">
          </div>
          <div class="s10-field">
            <label class="s10-field-label" for="f-mat">МАТЕРИАЛ</label>
            <input id="f-mat" name="material" type="text" placeholder="09Г2С, 12Х1МФ, 12Х18Н10Т…" autocomplete="off">
          </div>
          <div class="s10-field">
            <label class="s10-field-label" for="f-qty">КОЛИЧЕСТВО, шт</label>
            <input id="f-qty" name="qty" type="text" placeholder="100" autocomplete="off">
          </div>
          <div class="s10-field s10-field--wide">
            <label class="s10-field-label" for="f-deadline">СРОК</label>
            <input id="f-deadline" name="deadline" type="text" placeholder="30 календарных дней" autocomplete="off">
          </div>
          <div class="s10-field s10-field--wide">
            <label class="s10-field-label" for="f-contact">ВАШ EMAIL / ТЕЛЕФОН *</label>
            <input id="f-contact" name="contact" type="text" required placeholder="Для ответа на запрос" autocomplete="email">
          </div>
        </div>
        <div class="s10-actions">
          <button type="submit" class="s10-submit">ОТПРАВИТЬ ЗАПРОС →</button>
          <a class="s10-ghost-link" href="mailto:zakaz@prom-en.com">Написать напрямую</a>
        </div>
        <p class="s10-note">Ответ в течение 1 рабочего дня · Запрос без обязательств</p>
      </form>
      <div class="s10-success" id="s10-success"<?php echo $promen_sent ? ' style="display:block;"' : ''; ?>>
        ✓ ЗАПРОС ПРИНЯТ. Наш инженер свяжется с вами в течение рабочего дня.
      </div>
    </div>
  </div>
</section>

<footer class="site-footer" id="footer">
  <div class="ft-statement">
    <div class="ft-kicker">ПРОМЫШЛЕННАЯ ЭНЕРГЕТИКА · КАТАЛОГ ПРОДУКЦИИ</div>
    <h2 class="ft-headline">Готовы<br>к работе</h2>
    <p class="ft-tagline">Производство деталей и сборочных единиц трубопроводов для объектов атомной и тепловой энергетики. Единичные и серийные партии. Сертификат 3.1.</p>
    <a class="ft-cta" href="mailto:zakaz@prom-en.com">Отправить запрос →</a>
  </div>
  <div class="ft-grid">
    <div class="ft-cell">
      <div class="ft-cell-label">КОНТАКТЫ</div>
      <div class="ft-cell-val mono">
        <a href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a><br>
        <a href="tel:+73512170099">+7 (351) 217-00-99</a><br>
        Пн–Пт, 09:00–18:00 МСК
      </div>
    </div>
    <div class="ft-cell">
      <div class="ft-cell-label">ПАРАМЕТРЫ</div>
      <div class="ft-cell-val mono">
        DN 15 — DN 1400<br>
        PN 0.6 — 20.0 МПа<br>
        ГОСТ · ОСТ · СТО · НП
      </div>
    </div>
    <div class="ft-cell">
      <div class="ft-cell-label">СПЕЦИАЛИЗАЦИЯ</div>
      <div class="ft-cell-val">ТЭС<br>АЭС</div>
    </div>
    <div class="ft-cell">
      <div class="ft-cell-label">НАВИГАЦИЯ</div>
      <ul class="ft-nav-list">
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a></li>
        <li><a href="<?php echo esc_url( $catalog_url ); ?>">Каталог</a></li>
        <li><a href="#request">Форма запроса</a></li>
      </ul>
    </div>
  </div>
  <div class="ft-bar">
    <span class="ft-copy">© 2004–<?php echo esc_html( date_i18n( 'Y' ) ); ?> ООО Завод «Промышленная Энергетика». Все права защищены.</span>
    <span class="ft-idx">ПЭ-КТЛ.FTR / REV.1</span>
  </div>
</footer>

</div><!-- /.footer-zone -->
<?php wp_footer(); ?>
</body>
</html>
