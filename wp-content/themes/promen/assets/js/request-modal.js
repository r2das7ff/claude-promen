/* ── ПЭ / REQUEST-MODAL — единый попап-компонент форм запроса ──
   Порт из html/request-modal.js (Open Design). Стиль наследует токены :root
   (--dark,--blue,--g1,--g2,--white,--bg) и паттерн секции S10.
   Отправка: POST на admin-post.php (mu-plugin promen-requests), конфиг
   через window.promenRM = { ajaxUrl, nonce, privacyUrl, email }.
   Использование: openRequestModal('preset', { name:'...', sku:'...' }) */
(function () {
  if (window.openRequestModal) return;

  var CFG = window.promenRM || {};

  /* поле пресета → имя поля бэкенда (promen-requests.php) */
  var FIELD_NAMES = {
    name: 'name', company: 'company', contact: 'contact', task: 'task',
    std: 'standard', dn: 'dn', pn: 'pn', mat: 'material',
    qty: 'qty', deadline: 'deadline'
  };

  var PRESETS = {
    tz: {
      eyebrow: 'ТЗ',
      title: 'Отправить\nтехническое задание',
      sub: 'Прикрепите чертёж или опишите задачу — инженер оценит возможность изготовления и сроки.',
      fields: [
        { id: 'name', label: 'ФИО / КОНТАКТНОЕ ЛИЦО', placeholder: 'Иванов Иван Иванович' },
        { id: 'company', label: 'ОРГАНИЗАЦИЯ', placeholder: 'ООО «Заказчик»' },
        { id: 'contact', label: 'EMAIL / ТЕЛЕФОН', placeholder: 'ivanov@company.ru', wide: true },
        { id: 'task', label: 'ОПИСАНИЕ ЗАДАЧИ', placeholder: 'Изделие, параметры, партия, объект применения…', wide: true, textarea: true }
      ],
      file: true,
      submitLabel: 'ОТПРАВИТЬ ТЗ →',
      successText: '✓ ТЗ ПОЛУЧЕНО. Инженер свяжется с вами в течение рабочего дня.'
    },
    calc: {
      eyebrow: 'РАСЧЁТ',
      title: 'Запросить расчёт\nстоимости',
      sub: 'Укажите параметры изделия — подготовим коммерческое предложение и срок изготовления.',
      fields: [
        { id: 'name', label: 'НАИМЕНОВАНИЕ', placeholder: 'Отвод 90°, тройник, переход…' },
        { id: 'std', label: 'СТАНДАРТ', placeholder: 'ГОСТ 17375, ОСТ 108, СТО…' },
        { id: 'dn', label: 'DN / D, мм', placeholder: 'DN 100 / Ø 108' },
        { id: 'pn', label: 'ДАВЛЕНИЕ, МПа', placeholder: 'PN 160 / 16 МПа' },
        { id: 'mat', label: 'МАТЕРИАЛ', placeholder: '09Г2С, 12Х1МФ, 08Х18Н10Т…' },
        { id: 'qty', label: 'КОЛИЧЕСТВО, шт', placeholder: '100' },
        { id: 'contact', label: 'EMAIL / ТЕЛЕФОН', placeholder: 'ivanov@company.ru', wide: true }
      ],
      file: true,
      submitLabel: 'ОТПРАВИТЬ ЗАПРОС →',
      successText: '✓ ЗАПРОС ПРИНЯТ. Расчёт направим в течение одного рабочего дня.'
    },
    solution: {
      eyebrow: 'ПОДБОР',
      title: 'Подобрать решение\nпо ТЗ',
      sub: 'Опишите задачу — подберём нормативный документ, материал и технологию изготовления.',
      fields: [
        { id: 'name', label: 'ФИО / КОНТАКТНОЕ ЛИЦО', placeholder: 'Иванов Иван Иванович' },
        { id: 'contact', label: 'EMAIL / ТЕЛЕФОН', placeholder: 'ivanov@company.ru' },
        { id: 'task', label: 'ЗАДАЧА', placeholder: 'Тип изделия, параметры, объект применения…', wide: true, textarea: true }
      ],
      file: true,
      submitLabel: 'ОТПРАВИТЬ →',
      successText: '✓ ЗАЯВКА ПРИНЯТА. Подберём решение и свяжемся в течение рабочего дня.'
    },
    product: {
      eyebrow: 'ПОЗИЦИЯ',
      title: 'Запросить\nпозицию',
      sub: 'Укажите количество и срок — подготовим коммерческое предложение по выбранной позиции.',
      fields: [
        { id: 'name', label: 'НАИМЕНОВАНИЕ', placeholder: '', readonly: true, wide: true },
        { id: 'qty', label: 'КОЛИЧЕСТВО, шт', placeholder: '100' },
        { id: 'deadline', label: 'СРОК', placeholder: '30 календарных дней' },
        { id: 'contact', label: 'EMAIL / ТЕЛЕФОН', placeholder: 'ivanov@company.ru', wide: true }
      ],
      submitLabel: 'ЗАПРОСИТЬ →',
      successText: '✓ ЗАПРОС ПРИНЯТ. Коммерческое предложение направим в течение рабочего дня.'
    },
    docs: {
      eyebrow: 'ДОКУМЕНТЫ',
      title: 'Запросить\nдокументацию',
      sub: 'Укажите email — вышлем технический паспорт изделия и технические условия (ТУ).',
      fields: [
        { id: 'name', label: 'НАИМЕНОВАНИЕ', placeholder: '', readonly: true, wide: true },
        { id: 'contact', label: 'EMAIL', placeholder: 'ivanov@company.ru', wide: true }
      ],
      submitLabel: 'ЗАПРОСИТЬ ДОКУМЕНТЫ →',
      successText: '✓ ЗАПРОС ПРИНЯТ. Документы направим на указанный email.'
    },
    project: {
      eyebrow: 'ПРОЕКТ',
      title: 'Обсудить\nпохожий проект',
      sub: 'Опишите объект и задачу — подготовим предложение по аналогии с этим проектом.',
      fields: [
        { id: 'name', label: 'ФИО / КОНТАКТНОЕ ЛИЦО', placeholder: 'Иванов Иван Иванович' },
        { id: 'contact', label: 'EMAIL / ТЕЛЕФОН', placeholder: 'ivanov@company.ru' },
        { id: 'task', label: 'ОБЪЕКТ / ЗАДАЧА', placeholder: 'Тип объекта, изделия, объём поставки…', wide: true, textarea: true }
      ],
      file: true,
      submitLabel: 'ОТПРАВИТЬ →',
      successText: '✓ ЗАЯВКА ПРИНЯТА. Свяжемся в течение рабочего дня.'
    },
    contact: {
      eyebrow: 'ЗАПРОС',
      title: 'Отправить\nзапрос',
      sub: 'Оставьте контакты — свяжемся и уточним детали в течение рабочего дня.',
      fields: [
        { id: 'name', label: 'ФИО', placeholder: 'Иванов Иван Иванович' },
        { id: 'contact', label: 'EMAIL / ТЕЛЕФОН', placeholder: 'ivanov@company.ru' },
        { id: 'task', label: 'СООБЩЕНИЕ', placeholder: 'Кратко опишите запрос…', wide: true, textarea: true }
      ],
      submitLabel: 'ОТПРАВИТЬ →',
      successText: '✓ ЗАПРОС ОТПРАВЛЕН. Свяжемся в течение рабочего дня.'
    }
  };

  /* Вход панели — var(--ease-out) (токен из base.css, есть на каждой
     странице): встроенный ease для главной конверсионной поверхности слишком
     вялый — начало почти линейное, останов размазан. Сильный ease-out
     стартует быстро и мягко доводит — модалка «отвечает» на клик. */
  var CSS = ''
    + '.rm-overlay{position:fixed;inset:0;z-index:9990;background:rgba(15,42,68,.72);'
    + 'display:flex;align-items:center;justify-content:center;padding:32px;'
    /* visibility с задержкой = длительности фейда: без неё авторская
       анимация закрытия не рендерилась вовсе (оверлей скрывался в тот же
       кадр). Образец — .pm-select-menu в base.css. */
    + 'opacity:0;visibility:hidden;transition:opacity .2s ease,visibility 0s linear .2s;}'
    + '.rm-overlay.show{opacity:1;visibility:visible;transition-delay:0s;}'
    + '.rm-modal{position:relative;width:100%;max-width:640px;max-height:88vh;overflow-y:auto;'
    + 'background:var(--white);border:1px solid var(--g1);padding:48px 44px 36px;'
    + 'transform:translateY(16px);transition:transform .22s var(--ease-out,ease);}'
    + '.rm-overlay.show .rm-modal{transform:translateY(0);}'
    /* reduce: появление остаётся (fade помогает понять смену контекста),
       сдвиг убираем. */
    + '@media (prefers-reduced-motion:reduce){'
    + '.rm-modal{transform:none;transition:none;}'
    /* оверлей: фейд остаётся — как и обещает комментарий выше про reduce */
    /* исход заявки: fade остаётся (понять смену контекста), подъём — нет */
    + '.rm-success{transform:none;}'
    + '}'
    + '.rm-close{position:absolute;top:20px;right:20px;width:32px;height:32px;'
    + 'display:flex;align-items:center;justify-content:center;border:1px solid var(--g1);'
    + 'background:none;color:var(--dark);font-size:14px;cursor:pointer;transition:background .12s,color .12s,border-color .12s,transform var(--dur-press,160ms) var(--ease-out,ease);}'
    + '.rm-close:hover{background:var(--dark);color:var(--white);}'
    + '.rm-eyebrow{display:flex;align-items:center;gap:12px;margin-bottom:22px;}'
    + '.rm-eye-num{font-family:"DINPro",monospace;font-size:11px;letter-spacing:.2em;'
    + 'color:var(--g1);border:1px solid var(--g1);padding:3px 8px;line-height:1;text-transform:uppercase;}'
    + '.rm-eye-label{font-family:"DINPro",monospace;font-size:11px;letter-spacing:.25em;'
    + 'text-transform:uppercase;color:var(--g1);opacity:.6;display:flex;align-items:center;gap:10px;}'
    + '.rm-eye-label::before{content:"";width:22px;height:1px;background:var(--g1);flex-shrink:0;}'
    + '.rm-title{font-family:"DINProCond","DINPro",sans-serif;font-weight:900;'
    + 'font-size:clamp(24px,3.4vw,32px);line-height:1.02;letter-spacing:-.01em;'
    + 'text-transform:uppercase;color:var(--dark);margin:0 0 14px;white-space:pre-line;}'
    + '.rm-sub{font-family:"DINPro",sans-serif;font-size:14px;font-weight:300;'
    + 'line-height:1.6;color:var(--blue);margin:0 0 28px;max-width:480px;}'
    + '.rm-fields{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--g1);'
    + 'border:1px solid var(--g1);}'
    + '.rm-field{display:flex;flex-direction:column;background:var(--bg);padding:14px 16px 12px;'
    + 'position:relative;transition:background .12s;}'
    + '.rm-field:focus-within{background:var(--white);z-index:1;}'
    + '.rm-field-label{font-family:"DINPro",monospace;font-size:10px;letter-spacing:.2em;'
    + 'text-transform:uppercase;color:var(--g1);margin-bottom:8px;}'
    + '.rm-field input,.rm-field textarea{border:none;background:transparent;'
    + 'font-family:"DINPro",sans-serif;font-size:14px;font-weight:500;color:var(--dark);'
    + 'outline:none;padding:0;resize:none;font-variant-numeric:tabular-nums;}'
    + '.rm-field textarea{min-height:52px;line-height:1.5;font-weight:400;}'
    + '.rm-field input::placeholder,.rm-field textarea::placeholder{color:var(--g2);font-weight:300;font-size:13.5px;}'
    + '.rm-field--wide{grid-column:span 2;}'
    + '.rm-file-row{margin-top:14px;display:flex;align-items:center;flex-wrap:wrap;gap:16px;}'
    + '.rm-file-label-txt{font-family:"DINPro",monospace;font-size:11px;letter-spacing:.2em;'
    + 'text-transform:uppercase;color:var(--g1);opacity:.6;}'
    + '.rm-file-btn{display:inline-flex;align-items:center;gap:8px;font-family:"DINPro",monospace;'
    + 'font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--g1);'
    + 'border:1px solid var(--g1);padding:8px 14px;cursor:pointer;transition:background .12s,color .12s,border-color .12s,transform var(--dur-press,160ms) var(--ease-out,ease);background:var(--white);}'
    + '.rm-file-btn:hover{background:var(--bg);color:var(--dark);border-color:var(--g1);}'
    + '.rm-file-name{font-family:"DINPro",monospace;font-size:12px;color:var(--g1);opacity:.7;}'
    + '.rm-actions{display:flex;align-items:center;gap:16px;margin-top:24px;flex-wrap:wrap;}'
    + '.rm-submit{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;'
    + 'background:var(--dark);color:var(--white);border:1px solid var(--dark);'
    + 'font-family:"DINPro",sans-serif;font-weight:700;font-size:13.5px;letter-spacing:.08em;'
    + 'text-transform:uppercase;cursor:pointer;transition:background .15s,color .15s,border-color .15s,opacity .15s,transform var(--dur-press,160ms) var(--ease-out,ease);}'
    + '.rm-submit:hover{background:var(--blue);border-color:var(--blue);}'
    /* Press feedback: см. блок в base.css */
    + '.rm-submit:active,.rm-file-btn:active,.rm-close:active{transform:scale(.97);}'
    + '.rm-submit[disabled]{opacity:.55;cursor:default;}'
    + '.rm-ghost-link{font-family:"DINPro",monospace;font-size:12px;letter-spacing:.1em;'
    + 'text-transform:uppercase;color:var(--g1);text-decoration:none;border-bottom:1px solid transparent;}'
    + '.rm-ghost-link:hover{color:var(--dark);border-color:var(--g1);}'
    + '.rm-note{font-family:"DINPro",monospace;font-size:10.5px;letter-spacing:.1em;color:var(--g1);'
    + 'opacity:.6;margin:12px 0 0;}'
    /* Исходы показываются классом .show, вход — через @starting-style:
       display переключился, транзишен стартует из первого кадра. Без
       поддержки — мгновенно, как раньше. Ошибка — только fade, без
       подъёма: подъём празднует, ошибке он не к лицу. */
    + '.rm-error{display:none;margin-top:14px;padding:12px 16px;border:1px solid var(--g1);'
    + 'font-family:"DINPro",monospace;font-size:12px;letter-spacing:.04em;line-height:1.5;color:var(--dark);'
    + 'background:rgba(109,140,166,.12);opacity:0;transition:opacity .2s ease;}'
    + '.rm-error.show{display:block;opacity:1;}'
    + '@starting-style{.rm-error.show{opacity:0;}}'
    + '.rm-success{display:none;padding:22px 24px;background:rgba(109,140,166,.08);'
    + 'border:1px solid var(--g1);font-family:"DINPro",monospace;font-size:13px;'
    + 'letter-spacing:.03em;line-height:1.6;color:var(--dark);'
    + 'opacity:0;transform:translateY(8px);'
    + 'transition:opacity .35s var(--ease-out,ease),transform .35s var(--ease-out,ease);}'
    + '.rm-success.show{display:block;opacity:1;transform:none;}'
    + '@starting-style{.rm-success.show{opacity:0;transform:translateY(8px);}}'
    + '.rm-consent{display:flex;align-items:flex-start;gap:10px;margin-top:16px;cursor:pointer;}'
    + '.rm-consent input{width:18px;height:18px;margin-top:1px;flex-shrink:0;accent-color:var(--dark);cursor:pointer;}'
    + '.rm-consent-txt{font-family:"DINPro",sans-serif;font-size:13px;line-height:1.5;color:var(--blue);}'
    + '.rm-consent-txt a{color:var(--dark);text-decoration:underline;text-underline-offset:2px;}'
    + '.rm-consent.err input{outline:2px solid var(--g1);outline-offset:2px;}'
    /* ≤640px: пол шрифта 10px + тач-цели 44px */
    + '@media(max-width:640px){.rm-modal{padding:40px 24px 28px;}'
    + '.rm-fields{grid-template-columns:1fr;}.rm-field--wide{grid-column:span 1;}'
    + '.rm-actions{flex-direction:column;align-items:flex-start;}'
    + '.rm-close{width:44px;height:44px;}'
    + '.rm-eye-num,.rm-eye-label{font-size:12px;}'
    + '.rm-field-label{font-size:13px;}'
    + '.rm-field input,.rm-field textarea{font-size:16px;}' /* 16px — не даёт iOS зумить при фокусе */
    + '.rm-field input::placeholder,.rm-field textarea::placeholder{font-size:15px;}'
    + '.rm-file-label-txt{font-size:13px;}'
    + '.rm-file-btn{font-size:13px;min-height:44px;padding:12px 16px;}'
    + '.rm-file-name{font-size:13px;}'
    + '.rm-ghost-link{font-size:13px;min-height:44px;display:inline-flex;align-items:center;}'
    + '.rm-note{font-size:13px;}'
    + '.rm-submit{min-height:48px;}'
    + '.rm-consent{align-items:center;min-height:44px;}'
    + '.rm-consent input{width:22px;height:22px;}'
    + '.rm-consent-txt{font-size:14px;}}';

  function injectStyle() {
    if (document.getElementById('rm-styles')) return;
    var s = document.createElement('style');
    s.id = 'rm-styles';
    s.textContent = CSS;
    document.head.appendChild(s);
  }

  function consentHtml() {
    var link = CFG.privacyUrl
      ? '<a href="' + CFG.privacyUrl + '" target="_blank" rel="noopener">Политике обработки ПДн</a>'
      : 'Политике обработки ПДн';
    return '<span class="rm-consent-txt">Соглашаюсь на обработку персональных данных согласно ' + link + '.</span>';
  }

  function buildDom() {
    if (document.getElementById('rmOverlay')) return;
    var email = CFG.email || 'zakaz@prom-en.com';
    var wrap = document.createElement('div');
    wrap.innerHTML =
      '<div class="rm-overlay" id="rmOverlay">' +
        '<div class="rm-modal" id="rmModal" role="dialog" aria-modal="true" aria-labelledby="rmTitle">' +
          '<button type="button" class="rm-close" id="rmClose" aria-label="Закрыть">✕</button>' +
          '<div class="rm-eyebrow"><span class="rm-eye-num" id="rmEyeNum">01</span>' +
          '<span class="rm-eye-label" id="rmEyeLabel">ЗАПРОС</span></div>' +
          '<h3 class="rm-title" id="rmTitle"></h3>' +
          '<p class="rm-sub" id="rmSub"></p>' +
          '<form id="rmForm" novalidate>' +
            '<div class="rm-fields" id="rmFields"></div>' +
            '<div class="rm-file-row" id="rmFileRow" style="display:none">' +
              '<span class="rm-file-label-txt">ЧЕРТЁЖ / ФАЙЛ</span>' +
              '<div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;">' +
                '<label class="rm-file-btn" for="rmFile">↑ ПРИКРЕПИТЬ ФАЙЛ' +
                  '<input id="rmFile" type="file" accept=".pdf,.dwg,.dxf,.png,.jpg" style="display:none">' +
                '</label>' +
                '<span class="rm-file-name" id="rmFileName">PDF, DWG, DXF</span>' +
              '</div>' +
            '</div>' +
            '<label class="rm-consent" id="rmConsentLab">' +
              '<input type="checkbox" id="rmConsent">' +
              consentHtml() +
            '</label>' +
            '<div class="rm-actions">' +
              '<button type="submit" class="rm-submit" id="rmSubmitBtn">ОТПРАВИТЬ →</button>' +
              '<a class="rm-ghost-link" href="mailto:' + email + '">Написать напрямую</a>' +
            '</div>' +
            '<div class="rm-error" id="rmError"></div>' +
            '<p class="rm-note">Ответ в течение 1 рабочего дня · Запрос без обязательств</p>' +
          '</form>' +
          '<div class="rm-success" id="rmSuccess"></div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap.firstChild);

    document.getElementById('rmClose').addEventListener('click', closeRequestModal);
    document.getElementById('rmOverlay').addEventListener('click', function (e) {
      if (e.target.id === 'rmOverlay') closeRequestModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.getElementById('rmOverlay').classList.contains('show')) {
        closeRequestModal();
      }
    });
    document.getElementById('rmFile').addEventListener('change', function () {
      var el = document.getElementById('rmFileName');
      el.textContent = this.files.length ? this.files[0].name : 'PDF, DWG, DXF';
    });
    document.getElementById('rmConsent').addEventListener('change', function () {
      var lab = this.closest('.rm-consent');
      if (lab) lab.classList.remove('err');
    });
    document.getElementById('rmForm').addEventListener('submit', submitForm);
  }

  var currentPreset = 'contact';
  var currentCtx = {};

  function showError(msg) {
    var el = document.getElementById('rmError');
    el.textContent = msg;
    el.classList.add('show');
  }

  function submitForm(e) {
    e.preventDefault();
    var consent = document.getElementById('rmConsent');
    if (consent && !consent.checked) {
      var lab = consent.closest('.rm-consent');
      if (lab) lab.classList.add('err');
      consent.focus();
      return;
    }
    document.getElementById('rmError').classList.remove('show');

    var preset = PRESETS[currentPreset] || PRESETS.contact;
    var fd = new FormData();
    fd.append('action', 'promen_request');
    fd.append('promen_nonce', CFG.nonce || '');
    fd.append('promen_ajax', '1');
    fd.append('preset', currentPreset);
    fd.append('pd_consent', '1');
    fd.append('company_url', ''); /* honeypot — пустой у людей */
    if (currentCtx.sku) fd.append('sku', currentCtx.sku);
    preset.fields.forEach(function (f) {
      var input = document.getElementById('rm-f-' + f.id);
      if (!input) return;
      fd.append(FIELD_NAMES[f.id] || f.id, input.value || '');
    });
    var fileInput = document.getElementById('rmFile');
    if (preset.file && fileInput.files.length) {
      fd.append('attachment', fileInput.files[0]);
    }

    var btn = document.getElementById('rmSubmitBtn');
    var btnLabel = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'ОТПРАВКА…';

    fetch(CFG.ajaxUrl || '/wp-admin/admin-post.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { success: r.ok }; }); })
      .then(function (json) {
        if (json && json.success) {
          document.getElementById('rmForm').style.display = 'none';
          document.getElementById('rmSuccess').classList.add('show');
        } else {
          showError((json && json.data && json.data.message) || 'Не удалось отправить запрос. Напишите нам напрямую: ' + (CFG.email || 'zakaz@prom-en.com'));
        }
      })
      .catch(function () {
        showError('Сеть недоступна. Напишите нам напрямую: ' + (CFG.email || 'zakaz@prom-en.com'));
      })
      .finally(function () {
        btn.disabled = false;
        btn.textContent = btnLabel;
      });
  }

  var eyeCounter = 0;

  window.openRequestModal = function (presetKey, ctx) {
    var preset = PRESETS[presetKey] || PRESETS.contact;
    currentPreset = PRESETS[presetKey] ? presetKey : 'contact';
    currentCtx = ctx || {};
    injectStyle();
    buildDom();

    eyeCounter++;
    document.getElementById('rmEyeNum').textContent = String(eyeCounter).padStart(2, '0');
    document.getElementById('rmEyeLabel').textContent = preset.eyebrow;
    document.getElementById('rmTitle').textContent = currentCtx.title || preset.title;
    document.getElementById('rmSub').textContent = currentCtx.sub || preset.sub;
    document.getElementById('rmSubmitBtn').textContent = preset.submitLabel;
    document.getElementById('rmSuccess').textContent = preset.successText;

    var fieldsEl = document.getElementById('rmFields');
    fieldsEl.innerHTML = '';
    preset.fields.forEach(function (f) {
      var field = document.createElement('div');
      field.className = 'rm-field' + (f.wide ? ' rm-field--wide' : '');
      var label = document.createElement('label');
      label.className = 'rm-field-label';
      label.setAttribute('for', 'rm-f-' + f.id);
      label.textContent = f.label;
      field.appendChild(label);
      var input;
      if (f.textarea) {
        input = document.createElement('textarea');
      } else {
        input = document.createElement('input');
        input.type = 'text';
      }
      input.id = 'rm-f-' + f.id;
      input.autocomplete = 'off';
      if (f.placeholder) input.placeholder = f.placeholder;
      if (f.readonly) input.readOnly = true;
      var prefillVal = currentCtx[f.id];
      if (prefillVal) input.value = prefillVal;
      field.appendChild(input);
      fieldsEl.appendChild(field);
    });

    var fileRow = document.getElementById('rmFileRow');
    fileRow.style.display = preset.file ? 'flex' : 'none';
    document.getElementById('rmFileName').textContent = 'PDF, DWG, DXF';
    document.getElementById('rmFile').value = '';

    var form = document.getElementById('rmForm');
    form.style.display = 'block';
    document.getElementById('rmSuccess').classList.remove('show');
    document.getElementById('rmError').classList.remove('show');
    var consent = document.getElementById('rmConsent');
    if (consent) { consent.checked = false; var cl = consent.closest('.rm-consent'); if (cl) cl.classList.remove('err'); }

    document.getElementById('rmOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
    var firstInput = fieldsEl.querySelector('input:not([readonly]), textarea');
    /* Сразу, не по таймеру: 200мс ожидания входа модалки запирали клавиатуру
       и скринридер; preventScroll гасит прыжок прокрутки при фокусе. */
    if (firstInput) firstInput.focus({ preventScroll: true });
  };

  window.closeRequestModal = function () {
    var overlay = document.getElementById('rmOverlay');
    if (overlay) overlay.classList.remove('show');
    document.body.style.overflow = '';
  };
})();
