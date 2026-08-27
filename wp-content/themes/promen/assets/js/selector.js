/* ── ПЭ / SELECTOR — подбор изделия ───────────────────────────────────────
   Плавающая кнопка + панель подбора. Логика подбора живёт на сервере
   (inc/selector.php, REST promen/v1/select): здесь только ввод, отрисовка
   и передача выбранного в форму КП через openRequestModal('solution', …).

   Раскладка — три шага: «что подбираем» → «условия» → «результат».
   Условия показаны метками, и они же ими управляют: крестик снимает условие
   там же, где оно видно. Поля ввода одни на всю панель (сетка «уточнить
   параметры») — вторых полей для тех же данных быть не должно.

   Конфиг — window.promenSelector = { api, types[], industries{} }.
   Встроенный режим (страница /podbor/): в разметке есть #promenSelectorMount,
   панель строится прямо в нём, без оверлея и кнопки. ── */
(function () {
  'use strict';

  var CFG = window.promenSelector || {};
  var API = CFG.api || '/wp-json/promen/v1/select';

  /* Поля сетки: ключ условия → id инпута. Единственный источник значений. */
  var FIELDS = {
    group: 'pselType',
    industry: 'pselInd',
    temp: 'pselTemp',
    pressure: 'pselPres',
    dn: 'pselDn',
    s: 'pselS'
  };

  var EXAMPLES = [
    'Отвод 90° 108х4 ст20 ГОСТ 17375',
    'шпилька М20',
    'строительство котельной'
  ];

  var state = {
    built: false,
    inline: false,
    picked: {},    /* sku → позиция */
    last: null,    /* последний ответ сервера */
    drop: [],      /* снятые условия — уходят на сервер списком */
    groupsBack: 0, /* сколько разделов было в списке, чтобы вернуться к нему */
    seq: 0         /* номер запроса: ответы приходят не по порядку */
  };

  /* ── утилиты ── */

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function num(v) {
    if (v === null || v === undefined || v === '') return '';
    var n = Number(v);
    return isNaN(n) ? String(v) : String(Math.round(n * 1000) / 1000);
  }

  function el(tag, cls, html) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (html != null) e.innerHTML = html;
    return e;
  }

  function $(id) { return document.getElementById(id); }

  /* ── разметка ── */

  function panelHtml() {
    var types = (CFG.types || []).map(function (t) {
      return '<option value="' + esc(t.slug) + '">' + esc(t.label) + ' · ' + t.count + '</option>';
    }).join('');

    var inds = Object.keys(CFG.industries || {}).map(function (k) {
      return '<option value="' + esc(k) + '">' + esc(CFG.industries[k]) + '</option>';
    }).join('');

    var examples = EXAMPLES.map(function (x) {
      return '<button type="button" class="psel-ex" data-ex="' + esc(x) + '">' + esc(x) + '</button>';
    }).join('<span class="psel-ex-sep">·</span>');

    return '' +
      '<div class="psel-panel" role="dialog" aria-modal="true" aria-labelledby="pselTitle">' +
        '<div class="psel-head">' +
          '<button type="button" class="psel-close" id="pselClose" aria-label="Закрыть">✕</button>' +
          '<div class="psel-eyebrow">Подбор</div>' +
          '<h2 class="psel-title" id="pselTitle">Подобрать изделие</h2>' +
          '<p class="psel-sub">Вставьте строку из спецификации или опишите задачу — ' +
            'найдём позиции в каталоге и марки стали, допустимые для ваших условий.</p>' +
        '</div>' +

        '<div class="psel-body" id="pselBody">' +

          '<section class="psel-step" id="pselStep1">' +
            '<h3 class="psel-step-h"><span class="psel-step-n">1</span> Что подбираем</h3>' +
            '<div class="psel-ask">' +
              '<input id="pselQ" type="text" autocomplete="off" placeholder="Отвод 90° 108х4 ст20 ГОСТ 17375">' +
              '<button type="button" id="pselGo">Найти</button>' +
            '</div>' +
            '<p class="psel-hint">Например: ' + examples + '</p>' +
          '</section>' +

          '<section class="psel-step" id="pselCond">' +
            '<h3 class="psel-step-h"><span class="psel-step-n">2</span> Условия</h3>' +
            '<div id="pselBanner"></div>' +
            '<div class="psel-chips" id="pselChips">' +
              '<span class="psel-chips-empty">Условия не заданы</span>' +
            '</div>' +
            '<button type="button" class="psel-more" id="pselMore" aria-expanded="false" aria-controls="pselGrid">' +
              'Уточнить параметры' +
            '</button>' +
            '<div class="psel-grid" id="pselGrid" hidden>' +
              '<div class="psel-cell" data-cell="group">' +
                '<label class="psel-cell-lab" for="pselType">Тип изделия</label>' +
                '<select id="pselType" data-select><option value="">— любой —</option>' + types + '</select>' +
              '</div>' +
              '<div class="psel-cell" data-cell="industry">' +
                '<label class="psel-cell-lab" for="pselInd">Отрасль</label>' +
                '<select id="pselInd" data-select><option value="">— любая —</option>' + inds + '</select>' +
              '</div>' +
              '<div class="psel-cell" data-cell="temp">' +
                '<label class="psel-cell-lab" for="pselTemp">Температура среды, °C</label>' +
                '<input id="pselTemp" type="text" inputmode="numeric" autocomplete="off" placeholder="150">' +
              '</div>' +
              '<div class="psel-cell" data-cell="pressure">' +
                '<label class="psel-cell-lab" for="pselPres">Рабочее давление, МПа</label>' +
                '<input id="pselPres" type="text" inputmode="decimal" autocomplete="off" placeholder="1.6">' +
              '</div>' +
              '<div class="psel-cell" data-cell="dn">' +
                '<label class="psel-cell-lab" for="pselDn">DN (Ду)</label>' +
                '<input id="pselDn" type="text" inputmode="numeric" autocomplete="off" placeholder="100">' +
              '</div>' +
              '<div class="psel-cell" data-cell="s">' +
                '<label class="psel-cell-lab" for="pselS">Стенка, мм</label>' +
                '<input id="pselS" type="text" inputmode="decimal" autocomplete="off" placeholder="4">' +
              '</div>' +
            '</div>' +
            '<button type="button" class="psel-cta psel-run" id="pselRun">Подобрать →</button>' +
          '</section>' +

          '<section class="psel-step" id="pselResSec">' +
            '<h3 class="psel-step-h"><span class="psel-step-n">3</span> Результат</h3>' +
            '<div id="pselOut"></div>' +
          '</section>' +

        '</div>' +

        '<div class="psel-foot">' +
          '<span class="psel-picked" id="pselPicked">Ничего не выбрано</span>' +
          '<a class="psel-ghost" id="pselCatalog" href="#" hidden>Открыть в реестре</a>' +
          '<button type="button" class="psel-cta" id="pselReq" disabled>Запросить КП →</button>' +
        '</div>' +
      '</div>';
  }

  function build() {
    if (state.built) return;

    var mount = $('promenSelectorMount');
    state.inline = !!mount;

    var host;
    if (state.inline) {
      mount.className = (mount.className + ' psel-inline').trim();
      mount.innerHTML = panelHtml();
      host = mount;
      /* Встроенная панель — не диалог: она не перекрывает страницу и
         не запирает фокус. И её заголовок скрыт, ссылаться на него нельзя. */
      var p = mount.querySelector('.psel-panel');
      p.removeAttribute('role');
      p.removeAttribute('aria-modal');
      p.removeAttribute('aria-labelledby');
    } else {
      var overlay = el('div', 'psel-overlay');
      overlay.id = 'pselOverlay';
      overlay.innerHTML = panelHtml();
      document.body.appendChild(overlay);
      host = overlay;

      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) close();
      });
      $('pselClose').addEventListener('click', close);
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('show')) close();
      });
    }

    state.built = true;
    state.host = host;

    $('pselOut').innerHTML = '<p class="psel-placeholder">Здесь появятся позиции каталога ' +
      'и марки стали, допустимые для заданных условий. Отмеченные позиции ' +
      'уйдут в запрос КП одним списком.</p>';

    /* Кастомный выпадающий список: разметка создана только что, поэтому
       select.js нужно позвать повторно (см. window.promenSelectInit). */
    if (typeof window.promenSelectInit === 'function') window.promenSelectInit();

    $('pselGo').addEventListener('click', function () { fresh(); });
    $('pselRun').addEventListener('click', function () { run(); });
    $('pselQ').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); fresh(); }
    });
    $('pselMore').addEventListener('click', toggleGrid);
    $('pselReq').addEventListener('click', request);

    Object.keys(FIELDS).forEach(function (key) {
      var input = $(FIELDS[key]);
      input.addEventListener('change', function () {
        if (input.value.trim() !== '') undrop(key);
        run();
      });
      input.addEventListener('input', function () {
        if (input.value.trim() !== '') undrop(key);
        syncRun();
      });
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); run(); }
      });
    });

    host.addEventListener('change', function (e) {
      if (e.target && e.target.matches('input[type="checkbox"][data-sku]')) pick(e.target);
    });

    /* Метки, разделы и примеры рисуются заново на каждый ответ, поэтому
       слушаем на контейнере, а не на самих элементах. */
    host.addEventListener('click', function (e) {
      var t = e.target;
      var ex = t.closest && t.closest('[data-ex]');
      if (ex) { $('pselQ').value = ex.getAttribute('data-ex'); fresh(); return; }
      var x = t.closest && t.closest('[data-drop]');
      if (x) { dropCondition(x.getAttribute('data-drop')); return; }
      var g = t.closest && t.closest('[data-group]');
      if (g) { setField('group', g.getAttribute('data-group')); run(); return; }
      var i = t.closest && t.closest('[data-ind]');
      if (i) { setField('industry', i.getAttribute('data-ind')); run(); return; }
      if (t.id === 'pselTz') sendTz();
    });
  }

  /* ── состояние условий ── */

  /* Условие снова задано — значит оно больше не снято. Без этого выбор
     раздела после возврата к списку не срабатывал бы: сервер продолжал бы
     обнулять его по списку снятых. */
  function undrop(key) {
    var i = state.drop.indexOf(key);
    if (i !== -1) state.drop.splice(i, 1);
  }

  function setField(key, value) {
    var input = $(FIELDS[key]);
    if (!input) return;
    input.value = value;
    if (value !== '') undrop(key);
    /* Кастомная кнопка select.js перерисовывается по событию change. */
    if (input.tagName === 'SELECT') input.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function fieldValue(key) {
    var input = $(FIELDS[key]);
    return input ? input.value.trim() : '';
  }

  /* Снять условие: чистим поле (если оно у него есть) и говорим серверу,
     что условие снято, — часть условий приходит из строки запроса, и пустым
     полем их не убрать. */
  function dropCondition(key) {
    if (FIELDS[key]) {
      var input = $(FIELDS[key]);
      input.value = '';
      if (input.tagName === 'SELECT') input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (state.drop.indexOf(key) === -1) state.drop.push(key);
    run();
  }

  /* Новый запрос из строки — прежние снятия забываем. */
  function fresh() {
    state.drop = [];
    state.groupsBack = 0;
    run();
  }

  function toggleGrid() {
    var grid = $('pselGrid');
    var btn = $('pselMore');
    var open = grid.hidden;
    grid.hidden = !open;
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.textContent = open ? 'Свернуть параметры' : 'Уточнить параметры';
  }

  function openGrid() {
    if ($('pselGrid').hidden) toggleGrid();
  }

  /* ── запрос ── */

  function run() {
    build();
    var p = { q: $('pselQ').value.trim(), per_page: 20 };
    Object.keys(FIELDS).forEach(function (key) {
      var v = fieldValue(key);
      if (v !== '') p[key] = v;
    });
    if (state.drop.length) p.drop = state.drop.join(',');

    var qs = Object.keys(p).filter(function (k) { return p[k] !== ''; })
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]); })
      .join('&');

    var busy = $('pselOut');
    busy.classList.add('psel-busy');

    var seq = ++state.seq;
    fetch(API + '?' + qs, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        /* Ответ на устаревший запрос игнорируем: пользователь успел
           изменить условия, и старый результат перетёр бы новый. */
        if (seq !== state.seq) return;
        state.last = json;
        render(json);
      })
      .catch(function () {
        if (seq !== state.seq) return;
        $('pselResSec').hidden = false;
        $('pselOut').innerHTML = '<p class="psel-empty">Не удалось выполнить подбор. ' +
          'Попробуйте ещё раз или напишите нам напрямую.</p>';
      })
      .finally(function () {
        if (seq === state.seq) busy.classList.remove('psel-busy');
      });
  }

  /* ── шаг 2: метки условий, плашка, кнопка ── */

  function conditionList(d) {
    var q = d.query || {};
    var p = d.parsed || {};
    var o = d.object || {};
    var out = [];

    if (o.label) out.push({ key: 'object', name: 'Объект', val: o.label });
    if (q.group) {
      var label = q.group;
      (CFG.types || []).forEach(function (t) { if (t.slug === q.group) label = t.label; });
      out.push({ key: 'group', name: 'Тип', val: label });
    }
    if (q.industry) {
      out.push({ key: 'industry', name: 'Отрасль', val: (CFG.industries || {})[q.industry] || q.industry });
    }
    if (q.temp !== null && q.temp !== undefined && q.temp !== '') {
      out.push({ key: 'temp', name: 'Температура', val: num(q.temp) + ' °C' });
    }
    if (q.pressure) out.push({ key: 'pressure', name: 'Давление', val: num(q.pressure) + ' МПа' });
    if (q.dn) out.push({ key: 'dn', name: 'DN', val: num(q.dn) + (q.d ? ' (Ø' + num(q.d) + ')' : '') });
    if (q.s) out.push({ key: 's', name: 'Стенка', val: num(q.s) + ' мм' });
    if (q.angle) out.push({ key: 'angle', name: 'Угол', val: num(q.angle) + '°' });
    if ((p.gost || []).length) {
      out.push({ key: 'gost', name: 'Норматив', val: p.gost.map(function (g) { return g.raw; }).join(', ') });
    }
    if ((q.steel || []).length) out.push({ key: 'steel', name: 'Марка', val: q.steel.join(', ') });

    return out;
  }

  function renderConditions(d) {
    var o = d.object || {};
    var missing = o.missing || [];
    var conds = conditionList(d);

    /* Плашка держится на экране, пока условие не выполнено: инструкция
       должна быть видна в тот момент, когда по ней действуют. */
    var banner = '';
    if (missing.length) {
      var req = (o.required || []).filter(function (r) { return missing.indexOf(r.key) !== -1; });
      var names = req.map(function (r) { return r.label.toLowerCase(); });
      var one = names.length === 1;
      /* Перечень через двоеточие, а не «нужна температура»: иначе фраза
         требует согласования по роду и падежу для каждого поля. */
      banner = '<div class="psel-banner">' +
        '<span class="psel-banner-ico" aria-hidden="true">!</span>' +
        '<span>' +
          (o.label ? 'Для объекта «' + esc(o.label) + '» не хватает данных: ' : 'Не хватает данных: ') +
          esc(names.join(', ')) + '. ' +
          'Без ' + (one ? 'этого' : 'них') + ' не определить марку стали — ' +
          'заполните ' + (one ? 'поле' : 'поля') + ' ниже.' +
        '</span>' +
      '</div>';
    }
    $('pselBanner').innerHTML = banner;

    $('pselChips').innerHTML = conds.map(function (c) {
      return '<span class="psel-chip">' + esc(c.name) + ' <b>' + esc(c.val) + '</b>' +
        '<button type="button" class="psel-chip-x" data-drop="' + esc(c.key) + '" ' +
          'aria-label="Снять условие: ' + esc(c.name) + '">✕</button></span>';
    }).join('') || '<span class="psel-chips-empty">Условия не заданы</span>';

    /* Обязательные поля подсвечиваются в единственной сетке параметров,
       а не дублируются отдельной формой. */
    var grid = $('pselGrid');
    grid.querySelectorAll('.psel-cell').forEach(function (cell) {
      var need = missing.indexOf(cell.getAttribute('data-cell')) !== -1;
      cell.classList.toggle('psel-cell--need', need);
      var lab = cell.querySelector('.psel-cell-lab');
      var mark = lab.querySelector('.psel-req-mark');
      if (need && !mark) {
        lab.insertAdjacentHTML('beforeend', ' <span class="psel-req-mark">обязательно</span>');
      } else if (!need && mark) {
        mark.remove();
      }
    });

    if (missing.length) openGrid();
    syncRun();
  }

  /* Кнопка подбора мертва, пока не заполнены все обязательные условия:
     наполовину заданные условия дали бы подбор, который выглядит
     обоснованным, но таковым не является. */
  function syncRun() {
    var btn = $('pselRun');
    if (!btn) return;
    var missing = ((state.last || {}).object || {}).missing || [];
    var pending = missing.filter(function (k) { return fieldValue(k) === ''; });
    btn.disabled = pending.length > 0;
    btn.title = pending.length ? 'Заполните обязательные условия' : '';
  }

  /* ── шаг 3: результат ── */

  function backLinkHtml(d) {
    var q = d.query || {};
    if (!q.group || !state.groupsBack) return '';
    return '<button type="button" class="psel-back" data-drop="group">← Все разделы (' +
      state.groupsBack + ')</button>';
  }

  function groupsHtml(d) {
    var o = d.object || {};
    if (!(o.groups || []).length) return '';
    state.groupsBack = o.groups.length;
    return '<div class="psel-res-sec">' +
      '<span class="psel-lab">' +
        (o.constrained ? 'Разделы каталога под ваши условия' : 'Разделы каталога по отрасли — весь ассортимент') +
      '</span>' +
      '<div class="psel-groups">' +
      o.groups.map(function (g) {
        return '<button type="button" class="psel-group" data-group="' + esc(g.slug) + '">' +
          '<span class="psel-group-t">' + esc(g.label) + '</span>' +
          '<span class="psel-group-n">' + g.count + '</span>' +
        '</button>';
      }).join('') + '</div></div>';
  }

  function steelHtml(d) {
    var s = d.steel || {};
    var fit = s.fit || [];
    var warn = s.warn || [];
    if (!fit.length && !warn.length) return '';

    var mark = function (row, cls) {
      var why = (row.why || []).join('; ');
      var title = row.key + ' — ' + (row.desc || '') + (why ? ' (' + why + ')' : '');
      return '<span class="psel-mark' + cls + '" title="' + esc(title) + '">' + esc(row.key) + '</span>';
    };

    var html = '<div class="psel-res-sec"><span class="psel-lab">Марки, допустимые для ваших условий</span>' +
      '<div class="psel-steel">' +
      fit.map(function (r) { return mark(r, ''); }).join('') +
      warn.map(function (r) { return mark(r, ' psel-mark--warn'); }).join('') +
      '</div>';
    if (warn.length) {
      html += '<p class="psel-hint">Пунктиром — марки, у которых справочник не нормирует нижний ' +
        'предел температуры: хладостойкость подтверждается отдельно.</p>';
    }
    var rej = Object.keys(s.rejected || {}).length;
    if (rej) html += '<p class="psel-hint">Отсеяно по параметрам: ' + rej + '.</p>';
    html += '</div>';
    return html;
  }

  function rowHtml(h) {
    var meta = [];
    if (h.norm) meta.push(esc(h.norm));
    if (h.dn !== null && h.dn !== undefined) meta.push('DN ' + num(h.dn));
    if (h.d) meta.push('Ø' + num(h.d) + (h.s ? '×' + num(h.s) : '') + ' мм');
    if (h.angle) meta.push(num(h.angle) + '°');
    if (h.mass) meta.push(num(h.mass) + ' кг');
    if (h.steels) meta.push(esc(h.steels));

    var checked = state.picked[h.sku] ? ' checked' : '';
    return '<div class="psel-row">' +
      '<input type="checkbox" data-sku="' + esc(h.sku) + '" aria-label="Добавить в запрос"' + checked + '>' +
      '<div>' +
        '<a class="psel-row-t" href="' + esc(h.url) + '">' + esc(h.title) + '</a>' +
        '<div class="psel-row-m">' + meta.map(function (m) { return '<span>' + m + '</span>'; }).join('') + '</div>' +
      '</div>' +
    '</div>';
  }

  function render(d) {
    renderConditions(d);

    var out = $('pselOut');
    var html = backLinkHtml(d);

    html += groupsHtml(d);
    html += steelHtml(d);

    (d.notes || []).forEach(function (n) {
      html += '<p class="psel-note">' + esc(n) + '</p>';
    });

    if (d.total > 0) {
      html += '<div class="psel-res-sec">' +
        '<div class="psel-count"><span class="psel-lab">Позиции каталога</span>' +
        '<span class="psel-count-n">' + d.total + '</span></div>' +
        '<div class="psel-list">' + (d.hits || []).map(rowHtml).join('') + '</div>';
      if (d.total > (d.hits || []).length) {
        html += '<p class="psel-hint">Показаны первые ' + d.hits.length +
          '. Остальные — в реестре каталога.</p>';
      }
      html += '</div>';
    }

    /* Запрос об объекте: позиций ещё нет, подбирать нечего — уходит ТЗ.
       Когда разделы показаны, главное действие — выбрать раздел, и ТЗ
       уходит на второй план; когда их нет, ТЗ и есть выход. */
    if (!d.total && (d.object || {}).label) {
      var hasGroups = ((d.object || {}).groups || []).length > 0;
      html += '<button type="button" class="' + (hasGroups ? 'psel-ghost psel-tz-ghost' : 'psel-cta') +
        '" id="pselTz">Отправить ТЗ →</button>';
    }

    /* Пустой шаг 3 не прячем, а объясняем: рамка с номером и обещанием
       читается как «сюда придёт результат», пустая — как поломка. */
    out.innerHTML = html || '<p class="psel-placeholder">Здесь появятся позиции каталога ' +
      'и марки стали, допустимые для заданных условий. Отмеченные позиции ' +
      'уйдут в запрос КП одним списком.</p>';

    var cat = $('pselCatalog');
    if (d.catalog) { cat.href = d.catalog; cat.hidden = false; } else { cat.hidden = true; }

    /* Позиции, отмеченные ранее, остаются в наборе — иначе уточнение
       условий молча стирало бы собранную спецификацию. */
    paintPicked();

    /* Курсор — в первое незаполненное обязательное поле: раз уж мы говорим
       «без этого подбор невозможен», не надо заставлять его искать. */
    var need = $('pselGrid').querySelector('.psel-cell--need input');
    if (need && !need.value) need.focus({ preventScroll: true });
  }

  /* ── выбор позиций ── */

  function pick(box) {
    var sku = box.getAttribute('data-sku');
    var hits = (state.last && state.last.hits) || [];
    var hit = null;
    hits.forEach(function (h) { if (h.sku === sku) hit = h; });
    if (box.checked && hit) state.picked[sku] = hit;
    else delete state.picked[sku];
    paintPicked();
  }

  function paintPicked() {
    var n = Object.keys(state.picked).length;
    $('pselPicked').textContent = n ? ('Выбрано позиций: ' + n) : 'Ничего не выбрано';
    $('pselReq').disabled = !n;
  }

  /* ── передача в форму КП / ТЗ ── */

  function specText() {
    var lines = Object.keys(state.picked).map(function (sku, i) {
      var h = state.picked[sku];
      var bits = [(i + 1) + '. ' + h.title];
      if (h.norm) bits.push(h.norm);
      if (h.steels) bits.push('марка: ' + h.steels);
      bits.push('арт. ' + h.sku);
      return bits.join('; ');
    });
    var q = (state.last && state.last.query) || {};
    var head = [];
    if (q.temp !== null && q.temp !== undefined && q.temp !== '') head.push('температура среды ' + num(q.temp) + ' °C');
    if (q.pressure) head.push('давление ' + num(q.pressure) + ' МПа');
    if (q.industry && (CFG.industries || {})[q.industry]) head.push('отрасль: ' + CFG.industries[q.industry]);

    return (head.length ? 'Условия: ' + head.join(', ') + '.\n\n' : '')
      + 'Позиции (подбор на сайте):\n' + lines.join('\n');
  }

  function sendTz() {
    if (typeof window.openRequestModal !== 'function') return;
    var o = (state.last && state.last.object) || {};
    var q = (state.last && state.last.query) || {};
    var lines = [];
    if (o.label) lines.push('Объект: ' + o.label + '.');
    var typed = $('pselQ') && $('pselQ').value.trim();
    if (typed) lines.push('Запрос: ' + typed);
    if (q.industry && (CFG.industries || {})[q.industry]) {
      lines.push('Отрасль: ' + CFG.industries[q.industry] + '.');
    }
    window.openRequestModal('tz', {
      task: lines.join('\n'),
      sub: 'Опишите объект и параметры среды — инженер подберёт номенклатуру, нормативы и материал.'
    });
  }

  function request() {
    if (typeof window.openRequestModal !== 'function') return;
    window.openRequestModal('solution', {
      task: specText(),
      title: 'Запросить КП\nпо подобранным позициям',
      sub: 'Позиции ниже подставлены из подбора — дополните количеством и сроком, если они известны.'
    });
  }

  /* ── открытие / закрытие ── */

  function open() {
    build();
    if (state.inline) return;
    $('pselOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
    var q = $('pselQ');
    if (q) q.focus({ preventScroll: true });
  }

  function close() {
    var o = $('pselOverlay');
    if (o) o.classList.remove('show');
    document.body.style.overflow = '';
  }

  window.openSelector = open;
  window.closeSelector = close;

  /* ── старт ── */

  function start() {
    if ($('promenSelectorMount')) {
      build();
      return; /* встроенный режим: кнопка не нужна */
    }
    /* Ссылки и кнопки страниц открывают панель: data-selector. Работает
       независимо от плавающей кнопки — раз скрипт на странице есть,
       ссылка на подбор должна открывать его. */
    document.addEventListener('click', function (e) {
      var t = e.target.closest && e.target.closest('[data-selector]');
      if (t) { e.preventDefault(); open(); }
    });

    /* Плавающая кнопка выключена по умолчанию (PROMEN_SELECTOR_LAUNCHER):
       пока её нет, ассеты подбора не подключаются нигде, кроме /podbor/. */
    if (!CFG.launcher) return;

    var btn = el('button', 'psel-launch');
    btn.type = 'button';
    btn.innerHTML = '<span class="psel-launch-ico" aria-hidden="true"></span>Подбор';
    btn.addEventListener('click', open);
    document.body.appendChild(btn);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
