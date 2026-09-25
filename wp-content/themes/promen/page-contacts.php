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

  <div class="cnt-wrap" data-reveal-group>
    <!-- LEFT: лист реквизитов + расположение -->
    <div class="cnt-left">
      <?php
      /* Лист реквизитов (2026-09-25). Три зоны, как в основной надписи
         чертежа: прямые линии связи крупно — со страницы «Контакты» прежде
         всего звонят и пишут; ниже справочные реквизиты; замыкает лист
         карта предприятия — её просят для договора и счёта, поэтому это
         плитка с кнопкой, а не последняя строка таблицы. */
      ?>
      <section class="cnt-block" aria-labelledby="cntRegTtl">
        <h2 class="cnt-label" id="cntRegTtl">Реквизиты и контакты</h2>
        <div class="cnt-sheet">
          <dl class="cnt-lines">
            <div class="cnt-line">
              <dt class="cnt-line-k">Телефон</dt>
              <dd><a class="cnt-line-v" href="tel:+73512170099">+7 (351) 217-00-99</a></dd>
            </div>
            <div class="cnt-line">
              <dt class="cnt-line-k">Почта</dt>
              <dd><a class="cnt-line-v" href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a></dd>
            </div>
            <div class="cnt-line cnt-line--hours">
              <dt class="cnt-line-k">Режим</dt>
              <dd class="cnt-line-v cnt-line-v--txt">Пн–Пт, 08:00–17:00</dd>
            </div>
          </dl>
          <dl class="cnt-reg">
            <div class="cnt-row">
              <dt class="cnt-row-k">Юрлицо</dt>
              <dd class="cnt-row-v">ООО&nbsp;Завод «Промышленная Энергетика»</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">Адрес</dt>
              <dd class="cnt-row-v">454091, г.&nbsp;Челябинск, ул.&nbsp;Орджоникидзе, д.&nbsp;37, <span class="nw">помещ. 4, офис 301</span></dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">ИНН / КПП</dt>
              <dd class="cnt-row-v mono">7453307956 / 745101001</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">ОГРН</dt>
              <dd class="cnt-row-v mono">1177456024833 от 05.04.2017</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">ОКПО</dt>
              <dd class="cnt-row-v mono">13842829</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">Руководитель</dt>
              <dd class="cnt-row-v">Генеральный директор Агапкин Александр Дмитриевич, действует на основании Устава</dd>
            </div>
          </dl>
          <?php /* Банковские реквизиты — дословно из карты предприятия
                   (assets/docs/karta-predpriyatiya-prom-en.pdf). Сменится банк —
                   править и здесь, и в PDF. Счета без пробелов между группами
                   цифр: их копируют в 1С и платёжки. */ ?>
          <dl class="cnt-reg">
            <div class="cnt-row">
              <dt class="cnt-row-k">Расч. счёт</dt>
              <dd class="cnt-row-v mono">40702810672000052062</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">Банк</dt>
              <dd class="cnt-row-v">ПАО&nbsp;«Сбербанк» Челябинское отделение №&nbsp;8597</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">Корр. счёт</dt>
              <dd class="cnt-row-v mono">30101810700000000602</dd>
            </div>
            <div class="cnt-row">
              <dt class="cnt-row-k">БИК</dt>
              <dd class="cnt-row-v mono">047501602</dd>
            </div>
          </dl>
          <dl class="cnt-reg">
            <div class="cnt-row">
              <dt class="cnt-row-k">Сертификат</dt>
              <?php /* Перенос только по пробелам между частями номера. Дефис в «С-RU…»
                       остаётся обычным: с неразрывным скопированный номер не найдётся
                       в реестре ФСА. */ ?>
              <dd class="cnt-row-v mono"><span class="nw">ТР ТС 032</span> <span class="nw">RU С-RU.АБ53.В.08323/23</span> <span class="nw">серия RU 0418908</span></dd>
            </div>
          </dl>
          <a class="cnt-card" href="<?php echo esc_url( get_theme_file_uri( 'assets/docs/karta-predpriyatiya-prom-en.pdf' ) ); ?>" download
             aria-label="Скачать карту предприятия, PDF, 155 КБ" aria-describedby="cntCardDesc">
            <svg class="cnt-card-ic" viewBox="0 0 34 44" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M1.5 1.5h21l10 10v31h-31z"/><path d="M22.5 1.5v10h10"/><path d="M7.5 20h19M7.5 25.5h19M7.5 31h12"/></svg>
            <span class="cnt-card-body">
              <span class="cnt-card-t">Карта предприятия</span>
              <span class="cnt-card-d" id="cntCardDesc">Все реквизиты на фирменном бланке, включая коды ОКВЭД, ОКТМО и ОКАТО</span>
              <span class="cnt-card-meta">PDF · 155&nbsp;КБ</span>
            </span>
            <span class="cnt-card-btn">
              <svg class="cnt-card-dl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><g class="cnt-card-dl-arr"><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></g></svg>
              Скачать
            </span>
          </a>
        </div>
      </section>

      <section class="cnt-block" aria-labelledby="cntLocTtl">
        <h2 class="cnt-label" id="cntLocTtl">Расположение</h2>
        <div class="cnt-loc" data-reveal>
          <div class="cnt-loc-grid" aria-hidden="true"></div>
          <div class="cnt-loc-body">
            <div class="cnt-loc-mark" aria-hidden="true">
              <div class="cnt-loc-ring"></div>
              <div class="cnt-loc-dot"></div>
            </div>
            <div>
              <div class="cnt-loc-city">г. Челябинск</div>
              <div class="cnt-loc-coord">55.1644° N · 61.4368° E</div>
              <div class="cnt-loc-addr">ул. Орджоникидзе, 37 — офис<br>и департамент управления</div>
              <?php /* Поиск по адресу, а не по координатам: геокодер Яндекса сам
                       поставит метку на здание, даже если цифры выше округлены. */ ?>
              <a class="cnt-loc-link" href="https://yandex.ru/maps/?text=<?php echo rawurlencode( 'Челябинск, улица Орджоникидзе, 37' ); ?>" target="_blank" rel="noopener">Открыть в Яндекс Картах<span class="sr-only"> (в новой вкладке)</span><svg class="cnt-loc-link-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg></a>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- RIGHT: request form -->
    <?php /* #request — якорь, на который возвращает promen_request_done()
             (?sent=1#request). Без него после отправки страница открывалась
             сверху, и на телефоне «Отправлено» не было видно. */ ?>
    <div class="cnt-right" id="request">
      <?php /* .cnt-right-in — липкий на десктопе: лист реквизитов вдвое выше
               формы, и без этого под ней стояла пустая панель, а сама форма
               уезжала из кадра, пока читают реквизиты. */ ?>
      <div class="cnt-right-in">
      <?php /* Текст — одним span: .cnt-label — flex, и голый .nw стал бы отдельной колонкой. */ ?>
      <h2 class="cnt-label"><span>Форма обратной связи — <span class="nw">ПЭ-ФОРМА/11</span></span></h2>
      <ul class="s10-promise">
        <li class="s10-promise-item"><svg class="s10-promise-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>Ответ в течение 1 рабочего дня</li>
        <li class="s10-promise-item"><svg class="s10-promise-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>Обращение без обязательств</li>
      </ul>
      <form id="cntForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo $promen_sent ? ' style="display:none;"' : ''; ?>>
        <input type="hidden" name="action" value="promen_request">
        <input type="hidden" name="preset" value="contact">
        <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
        <div style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;" aria-hidden="true">
          <label for="c-company-url">Company URL</label>
          <input id="c-company-url" name="company_url" type="text" tabindex="-1" autocomplete="off">
        </div>
        <?php /* autocomplete по WCAG 1.3.5: браузер подставит имя, почту и компанию
                 из профиля. Поле «Телефон или почта» — email, как в форме футера. */ ?>
        <div class="cnt-form">
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-name">Имя <span class="cnt-required">*</span></label>
            <input id="c-name" name="name" type="text" placeholder="Как к вам обращаться" required autocomplete="name">
          </div>
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-contact">Телефон или почта <span class="cnt-required">*</span></label>
            <input id="c-contact" name="contact" type="text" placeholder="+7 … / mail@company.ru" required autocomplete="email">
          </div>
          <div class="cnt-field">
            <label class="cnt-field-label" for="c-company">Компания</label>
            <input id="c-company" name="company" type="text" placeholder="ООО «…»" autocomplete="organization">
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
          <?php // Невидимая SmartCaptcha (mu-plugin promen-antispam): без ключей ничего не печатает. ?>
          <?php if ( function_exists( 'promen_captcha_field' ) ) { promen_captcha_field(); } ?>
          <div class="cnt-actions">
            <button type="submit" class="cnt-submit">Отправить сообщение →</button>
            <a class="cnt-ghost" href="mailto:zakaz@prom-en.com">Написать напрямую</a>
          </div>
        </div>
      </form>
      <div class="cnt-success" id="cntSuccess"<?php echo $promen_sent ? ' style="display:block;"' : ''; ?>>
        ✓ <b>СООБЩЕНИЕ ОТПРАВЛЕНО.</b> Инженер завода свяжется с вами в течение рабочего дня.
      </div>
      </div><!-- /.cnt-right-in -->
    </div>
  </div>

  <?php // Менеджеры направлений — общая часть с главной; ось rail даёт сам .pg. ?>
  <?php get_template_part( 'parts/managers', null, [ 'num' => 'ОП', 'flush' => true ] ); ?>
</div><!-- /.pg -->
<?php get_footer(); ?>
