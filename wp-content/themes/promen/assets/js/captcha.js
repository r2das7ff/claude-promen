/* ── ПЭ / CAPTCHA — Яндекс SmartCaptcha, невидимый режим.
   Виджет не показывает чекбокс: токен запрашивается в момент отправки
   формы, окно проверки всплывает только у подозрительных запросов.
   Сам скрипт капчи грузим лениво — при первом касании формы: на сайте
   с пройденным перф-планом сторонний скрипт не должен участвовать
   в первой отрисовке страницы.
   Обслуживает классические формы (footer s10, контакты) сам: находит
   [data-promen-captcha], перехватывает submit, кладёт токен в скрытое
   поле smart_token и отправляет форму нативно. Модалка запроса берёт
   токен через window.promenCaptcha.token(контейнер).
   Проверка токена — на сервере, mu-plugin promen-antispam.php. ── */
(function () {
  var CFG = window.promenCaptchaCfg || {};
  var KEY = CFG.sitekey || '';
  if (!KEY) return;

  var SRC = 'https://smartcaptcha.cloud.yandex.ru/captcha.js?render=onload&onload=promenCaptchaReady';
  var api = null;      /* window.smartCaptcha после загрузки */
  var loading = null;  /* общий Promise загрузки — скрипт нужен один на страницу */
  var slots = [];      /* по контейнеру: {el, id, resolve, reject} */

  function load() {
    if (loading) return loading;
    loading = new Promise(function (ok, fail) {
      window.promenCaptchaReady = function () { api = window.smartCaptcha; ok(api); };
      var s = document.createElement('script');
      s.src = SRC;
      s.defer = true;
      s.onerror = function () { fail(new Error('captcha-script')); };
      document.head.appendChild(s);
    });
    return loading;
  }

  function slot(el) {
    for (var i = 0; i < slots.length; i++) if (slots[i].el === el) return slots[i];
    var s = { el: el, id: null, resolve: null, reject: null };
    slots.push(s);
    return s;
  }

  /*
   * Одно завершение на один execute, и два разных исхода:
   *   settle('токен') / settle('') — отправляем форму. Пустой токен значит
   *     «капча сломалась» (сеть, неверный ключ) — пусть об этом скажет сервер
   *     понятной ошибкой, это честнее намертво зависшей кнопки;
   *   cancel() — человек закрыл окно проверки сам: молча отпускаем кнопку.
   */
  function settle(s, token) {
    var ok = s.resolve;
    s.resolve = s.reject = null;
    if (ok) ok(token || '');
  }

  function cancel(s) {
    var fail = s.reject;
    s.resolve = s.reject = null;
    if (fail) fail(new Error('captcha-cancelled'));
  }

  function render(s) {
    s.id = api.render(s.el, {
      sitekey: KEY,
      invisible: true,
      hideShield: true, /* уведомление Яндекса ставим сами, строкой под кнопкой */
      hl: 'ru',
      callback: function (token) { settle(s, token); }
    });
    /* Задержка — чтобы успешное решение успело прийти в callback раньше,
       чем мы решим, что окно закрыли не решив. */
    api.subscribe(s.id, 'challenge-hidden', function () {
      setTimeout(function () { if (!api.getResponse(s.id)) cancel(s); }, 300);
    });
    api.subscribe(s.id, 'network-error', function () { settle(s, ''); });
    api.subscribe(s.id, 'javascript-error', function () { settle(s, ''); });
  }

  /** Токен для контейнера: рендерим виджет при первом обращении. */
  function token(el) {
    return load().then(function () {
      var s = slot(el);
      if (s.id === null) render(s);
      else api.reset(s.id); /* токен одноразовый — перед новой попыткой сбрасываем */
      return new Promise(function (ok, fail) {
        s.resolve = ok;
        s.reject = fail;
        api.execute(s.id);
      });
    }, function () {
      return ''; /* скрипт капчи не догрузился — см. комментарий к settle() */
    });
  }

  /** Классическая форма: submit → токен → нативная отправка. */
  function hook(form, el) {
    var field = document.createElement('input');
    field.type = 'hidden';
    field.name = 'smart_token';
    form.appendChild(field);

    /* Пока человек заполняет поля, скрипт капчи уже едет. */
    form.addEventListener('focusin', function () { load(); }, { once: true });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('button[type="submit"]');
      var label = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'ПРОВЕРКА…'; }
      token(el).then(function (t) {
        field.value = t;
        /* form.submit() уходит мимо обработчиков — рекурсии не будет. */
        form.submit();
      }).catch(function () {
        if (btn) { btn.disabled = false; btn.textContent = label; }
      });
    });
  }

  var boxes = document.querySelectorAll('[data-promen-captcha]');
  for (var i = 0; i < boxes.length; i++) {
    var form = boxes[i].closest('form');
    if (form) hook(form, boxes[i]);
  }

  window.promenCaptcha = { token: token, warm: load, sitekey: KEY };
}());
