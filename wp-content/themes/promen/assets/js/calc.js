/* ── ПЭ / CALC — раздел «Калькуляторы» ────────────────────────────────────
   Все инструменты считают по ДАННЫМ КАТАЛОГА (REST promen/v1/calc/*):
   результат всегда ведёт на конкретную позицию и в заявку, а не в Excel.
   Разметку страницы даёт шаблон (скелет с [data-calc]); формы, каскады
   и результат строит этот файл. Селекты нативные: подменяющий select.js
   инициализируется один раз на DOMContentLoaded и пересборку опций не
   переживает.
   Конфиг — window.promenCalc { api, deliveryApi, delivery, catalogUrl }. */
(function () {
  'use strict';

  var CFG = window.promenCalc || {};
  if (!document.querySelector('[data-calc]')) return;

  /* ── УТИЛИТЫ ── */

  var NBSP = ' ';

  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html !== undefined) n.innerHTML = html;
    return n;
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* «4 598,25»: тысячные — неразрывный пробел, дробь — запятая. */
  function fmt(v, dec) {
    if (v == null || isNaN(v)) return '—';
    var p = Number(v).toFixed(dec == null ? 2 : dec).split('.');
    p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, NBSP);
    var tail = p[1] ? p[1].replace(/0+$/, '') : '';
    return tail ? p[0] + ',' + tail : p[0];
  }

  /* Масса: точность по величине — 0,018 / 2,4 / 148 / 12 400. */
  function fmtKg(v) {
    if (v == null || isNaN(v)) return '—';
    var a = Math.abs(v);
    return fmt(v, a < 0.1 ? 3 : a < 10 ? 2 : a < 1000 ? 1 : 0);
  }

  /* Число из строки формы: запятая = точка, мусор = NaN. */
  function num(s) {
    var v = parseFloat(String(s == null ? '' : s).replace(',', '.').replace(/\s/g, ''));
    return isNaN(v) ? null : v;
  }

  /* Русские формы: plural(3, ['фланец','фланца','фланцев']). */
  function plural(n, forms) {
    var n10 = Math.abs(n) % 10, n100 = Math.abs(n) % 100;
    if (n10 === 1 && n100 !== 11) return forms[0];
    if (n10 >= 2 && n10 <= 4 && (n100 < 12 || n100 > 14)) return forms[1];
    return forms[2];
  }

  var jsonCache = {};
  function getJSON(url) {
    if (!jsonCache[url]) {
      jsonCache[url] = fetch(url, { credentials: 'same-origin' }).then(function (r) {
        if (!r.ok) throw new Error('http ' + r.status);
        return r.json();
      });
      jsonCache[url].catch(function () { delete jsonCache[url]; });
    }
    return jsonCache[url];
  }

  function api(path, params) {
    var q = [];
    Object.keys(params || {}).forEach(function (k) {
      if (params[k] !== undefined && params[k] !== null && params[k] !== '') {
        q.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
      }
    });
    return (CFG.api || '/wp-json/promen/v1/calc') + path + (q.length ? '?' + q.join('&') : '');
  }

  function uniq(arr) {
    var seen = {}, out = [];
    arr.forEach(function (v) {
      var k = String(v);
      if (v != null && !seen[k]) { seen[k] = 1; out.push(v); }
    });
    return out;
  }
  function asc(a, b) { return a - b; }

  /* ── ПОЛЯ ФОРМЫ ── */

  function makeField(label, control, wide) {
    var f = el('div', 'clc-field' + (wide ? ' clc-field--wide' : ''));
    var l = el('label', 'clc-field-label', esc(label));
    if (control.id) l.setAttribute('for', control.id);
    f.appendChild(l);
    f.appendChild(control);
    return f;
  }

  var fieldSeq = 0;
  function makeSelect(label, onChange, wide) {
    var s = document.createElement('select');
    s.id = 'clc-f-' + (++fieldSeq);
    s.addEventListener('change', onChange);
    var f = makeField(label, s, wide);
    return {
      root: f,
      select: s,
      /* Пересобрать опции, стараясь сохранить текущее значение. */
      fill: function (options, keep) {
        var prev = keep === undefined ? s.value : String(keep);
        s.innerHTML = '';
        options.forEach(function (o) {
          var opt = document.createElement('option');
          opt.value = String(o.v);
          opt.textContent = o.t;
          s.appendChild(opt);
        });
        if (prev !== '' && options.some(function (o) { return String(o.v) === prev; })) s.value = prev;
        f.classList.toggle('is-off', options.length === 0);
        f.style.display = options.length ? '' : 'none';
      },
      value: function () { return s.value; },
      show: function (on) { f.style.display = on ? '' : 'none'; }
    };
  }

  function makeInput(label, val, onInput, attrs, wide) {
    var i = document.createElement('input');
    i.type = 'text';
    i.inputMode = 'decimal';
    i.autocomplete = 'off';
    i.id = 'clc-f-' + (++fieldSeq);
    i.value = val == null ? '' : val;
    Object.keys(attrs || {}).forEach(function (k) { i.setAttribute(k, attrs[k]); });
    i.addEventListener('input', onInput);
    return { root: makeField(label, i, wide), input: i, value: function () { return num(i.value); } };
  }

  /* ── РЕЗУЛЬТАТ ── */

  function resNum(box, value, unit, sub) {
    var w = el('div', 'clc-res');
    var n = el('div', 'clc-res-num');
    n.appendChild(el('span', '', value));
    if (unit) n.appendChild(el('span', 'u', unit));
    w.appendChild(n);
    if (sub) w.appendChild(el('div', 'clc-res-sub', sub));
    box.appendChild(w);
    return w;
  }

  function resRows(parent, rows) {
    var w = el('div', 'clc-res-rows');
    rows.forEach(function (r) {
      if (!r) return;
      var line = el('div', 'clc-row');
      line.appendChild(el('span', 'clc-row-k', esc(r[0])));
      line.appendChild(el('span', 'clc-row-v', r[1]));
      w.appendChild(line);
    });
    parent.appendChild(w);
    return w;
  }

  function linkTo(url, text) {
    return url ? '<a href="' + esc(url) + '">' + esc(text) + '</a>' : esc(text);
  }

  function actions(parent, list) {
    var w = el('div', 'clc-actions');
    list.forEach(function (a) {
      if (!a) return;
      var b;
      if (a.href) {
        b = el('a', 'clc-btn' + (a.ghost ? ' clc-btn--ghost' : ''), esc(a.label));
        b.href = a.href;
      } else {
        b = el('button', 'clc-btn' + (a.ghost ? ' clc-btn--ghost' : ''), esc(a.label));
        b.type = 'button';
        b.addEventListener('click', a.onClick);
      }
      w.appendChild(b);
    });
    parent.appendChild(w);
    return w;
  }

  function empty(box, text) {
    box.innerHTML = '';
    box.appendChild(el('div', 'clc-empty', esc(text)));
  }

  /* ── ДОСТАВКА (Деловые Линии, через наш сервер) ── */

  /* Подсказки городов ДЛ. Общий механизм для расчёта партии и свободного
     калькулятора: list обязан лежать в контейнере с position:relative.
     Возвращает объект состояния — .code пуст, пока город не выбран из
     подсказок (по набранному руками тексту ДЛ считать не умеет). */
  function attachCityPicker(input, list, opts) {
    opts = opts || {};
    var state = { code: '', name: '', full: '', terminal: false };
    var debounce = null;
    function close() { list.classList.remove('show'); }

    input.addEventListener('input', function () {
      state.code = '';
      state.name = '';
      if (opts.onClear) opts.onClear();
      clearTimeout(debounce);
      var q = input.value.trim();
      if (q.length < 2) { close(); return; }
      debounce = setTimeout(function () {
        fetch((CFG.deliveryApi || '/wp-json/promen/v1/delivery') + '/cities?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (json) {
            list.innerHTML = '';
            var cities = (json && json.cities) || [];
            cities.slice(0, 8).forEach(function (c) {
              var note = (c.region || '') + (c.terminal ? '' : ' · нет терминала');
              var b = el('button', 'clc-dlv-opt', esc(c.name) + (note.trim() ? '<small>' + esc(note.trim()) + '</small>' : ''));
              b.type = 'button';
              b.addEventListener('click', function () {
                state.code = c.code;
                state.name = c.name;
                state.full = c.full || c.name;
                state.terminal = !!c.terminal;
                input.value = c.name;
                close();
                if (opts.onPick) opts.onPick(state);
              });
              list.appendChild(b);
            });
            list.classList.toggle('show', cities.length > 0);
          })
          .catch(close);
      }, 250);
    });
    document.addEventListener('click', function (e) {
      if (!(opts.host || input.parentNode).contains(e.target)) close();
    });
    return state;
  }

  function makeDelivery(host, getItems) {
    if (!CFG.delivery || !host) { if (host) host.hidden = true; return function () {}; }
    host.hidden = false;
    host.innerHTML = '';
    host.appendChild(el('div', 'clc-dlv-t', 'Доставка партии — Деловые Линии'));
    var form = el('div', 'clc-dlv-form');
    var input = document.createElement('input');
    input.className = 'clc-dlv-city';
    input.placeholder = 'Город доставки…';
    input.autocomplete = 'off';
    var go = el('button', 'clc-btn clc-dlv-go', 'Рассчитать');
    go.type = 'button';
    var list = el('div', 'clc-dlv-list');
    var out = el('div', 'clc-dlv-res');
    form.appendChild(input);
    form.appendChild(go);
    form.appendChild(list);
    host.appendChild(form);
    host.appendChild(out);

    var city = attachCityPicker(input, list, {
      host: host,
      onClear: function () { out.innerHTML = ''; }
    });

    go.addEventListener('click', function () {
      var items = getItems();
      if (!items || !items.length) { out.innerHTML = '<span class="mut">Для этой позиции расчёт доставки недоступен.</span>'; return; }
      if (!city.code) { out.innerHTML = '<span class="mut">Выберите город из списка подсказок.</span>'; input.focus(); return; }
      out.textContent = 'Считаем…';
      fetch((CFG.deliveryApi || '/wp-json/promen/v1/delivery') + '/quote-batch', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ city_code: city.code, items: items })
      })
        .then(function (r) { return r.json(); })
        .then(function (json) {
          if (json && json.ok) {
            out.innerHTML = '<b>' + fmt(json.price, 0) + NBSP + '₽</b>' +
              (json.eta ? ' · прибытие ~' + esc(json.eta) : '') +
              '<div class="mut">Сборный груз до терминала' + (json.terminal ? ' «' + esc(json.terminal) + '»' : '') +
              ' · ' + fmtKg(json.weight) + NBSP + 'кг · ' + fmt(json.volume, 2) + NBSP + 'м³. Оценка, не оферта.</div>';
          } else {
            var code = json && json.error;
            out.innerHTML = '<span class="mut">' + esc(
              code === 'too_heavy' ? 'Партия тяжелее 20 т — это выделенная машина, посчитаем по запросу.' :
              code === 'no_terminal' ? 'В этом населённом пункте нет терминала — уточним доставку по запросу.' :
              code === 'rate_limited' ? 'Слишком много расчётов подряд — попробуйте через минуту.' :
              'Не удалось рассчитать — укажите город в заявке, посчитаем вручную.') + '</span>';
          }
        })
        .catch(function () { out.innerHTML = '<span class="mut">Сеть недоступна — попробуйте ещё раз.</span>'; });
    });

    return function reset() { out.innerHTML = ''; };
  }

  /* ── ЗАЯВКИ ── */

  function reqProduct(title, sku, qty) {
    if (!window.openRequestModal) return;
    window.openRequestModal('product', { name: title, sku: sku || '', qty: qty ? String(qty) : '' });
  }

  function reqKit(taskText, title) {
    if (!window.openRequestModal) return;
    window.openRequestModal('tz', {
      title: title || 'Запросить\nкомплект',
      sub: 'Состав уже в заявке — добавьте контакты, вышлем КП в течение рабочего дня.',
      task: taskText
    });
  }

  /* ════════════════ 01 · ВЕС СДТ ════════════════ */

  var SDT_TYPES = [
    ['otvody', 'Отводы'],
    ['perekhody', 'Переходы'],
    ['troyniki', 'Тройники'],
    ['zaglushki', 'Заглушки'],
    ['dnishcha', 'Днища']
  ];

  function initSdt(root) {
    var tabsBox = root.querySelector('[data-tabs]');
    var fieldsBox = root.querySelector('[data-fields]');
    var resBox = root.querySelector('[data-result]');
    var dlvBox = root.querySelector('[data-delivery]');

    var state = { type: 'otvody', rows: [], row: null };

    var tabs = {};
    SDT_TYPES.forEach(function (t) {
      var b = el('button', 'clc-tab', esc(t[1]));
      b.type = 'button';
      b.addEventListener('click', function () { setType(t[0]); });
      tabs[t[0]] = b;
      tabsBox.appendChild(b);
    });

    var fNorm = makeSelect('Стандарт', onNorm, true);
    var fD = makeSelect('Дн, мм', function () { refill(false); });
    var fS = makeSelect('Стенка s, мм', function () { refill(false); });
    var fA = makeSelect('Угол', recalc);
    var fB = makeSelect('Второй конец Дн2×s2, мм', recalc);
    var fE = makeSelect('Исполнение', recalc);
    var fQ = makeInput('Количество, шт', '1', recalc, { inputmode: 'numeric' });
    [fNorm, fD, fS, fA, fB, fE, fQ].forEach(function (f) { fieldsBox.appendChild(f.root); });

    var resetDlv = makeDelivery(dlvBox, function () {
      var qty = Math.max(1, Math.round(fQ.value() || 1));
      return state.row && state.row.pid && state.row.m != null ? [{ product_id: state.row.pid, qty: qty }] : null;
    });

    function setType(type) {
      state.type = type;
      Object.keys(tabs).forEach(function (k) { tabs[k].classList.toggle('is-on', k === type); });
      empty(resBox, 'Загружаем нормативы…');
      getJSON(api('/sdt-norms', { type: type })).then(function (json) {
        var norms = json.norms || [];
        fNorm.fill(norms.map(function (n) { return { v: n.slug, t: n.label + ' · ' + n.n }; }), '');
        if (!norms.length) { empty(resBox, 'По этому типу пока нет позиций с массой.'); return; }
        onNorm();
      }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });
    }

    function onNorm() {
      empty(resBox, 'Загружаем типоразмеры…');
      getJSON(api('/sdt-rows', { type: state.type, norm: fNorm.value() })).then(function (json) {
        state.rows = json.rows || [];
        refill(true);
      }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });
    }

    /* Каскад: Дн → s → (угол | второй конец | исполнение). */
    function refill(resetSel) {
      var rows = state.rows;
      var two = state.type === 'perekhody' || state.type === 'troyniki';

      var ds = uniq(rows.map(function (r) { return r.d; }).filter(function (d) { return d != null; })).sort(asc);
      fD.fill(ds.map(function (d) {
        var dn = null;
        rows.some(function (r) { if (r.d === d && r.dn != null) { dn = r.dn; return true; } return false; });
        return { v: d, t: fmt(d, 1) + (dn != null ? ' · DN ' + fmt(dn, 0) : '') };
      }), resetSel ? '' : undefined);

      var d = num(fD.value());
      var inD = rows.filter(function (r) { return r.d === d; });

      var ss = uniq(inD.map(function (r) { return r.s; }).filter(function (s) { return s != null; })).sort(asc);
      fS.fill(ss.map(function (s) { return { v: s, t: fmt(s, 1) }; }), resetSel ? '' : undefined);
      var s = ss.length ? num(fS.value()) : null;
      var inS = inD.filter(function (r) { return r.s === s || (r.s == null && s == null); });

      var angles = uniq(inS.map(function (r) { return r.a; }).filter(function (a) { return a != null; })).sort(asc);
      fA.fill(angles.map(function (a) { return { v: a, t: fmt(a, 1) + '°' }; }), resetSel ? '' : undefined);
      fA.show(angles.length > 0);

      if (two) {
        var pairs = uniq(inS.map(function (r) { return r.d2 != null ? r.d2 + '×' + (r.s2 == null ? '—' : r.s2) : null; }).filter(Boolean));
        fB.fill(pairs.map(function (p) { return { v: p, t: p.replace('.', ',') }; }), resetSel ? '' : undefined);
        fB.show(pairs.length > 0);
      } else {
        fB.show(false);
      }

      var execs = uniq(inS.map(function (r) { return r.e; }).filter(Boolean));
      fE.fill(execs.map(function (e) { return { v: e, t: e }; }), resetSel ? '' : undefined);
      fE.show(execs.length > 1);

      recalc();
    }

    function findRow() {
      var d = num(fD.value());
      var s = fS.root.style.display === 'none' ? null : num(fS.value());
      var a = fA.root.style.display === 'none' ? null : num(fA.value());
      var pair = fB.root.style.display === 'none' ? null : fB.value();
      var e = fE.root.style.display === 'none' ? null : fE.value();
      var best = null;
      state.rows.forEach(function (r) {
        if (r.d !== d || r.s !== s) return;
        if (a != null && r.a !== a) return;
        if (pair && (r.d2 + '×' + (r.s2 == null ? '—' : r.s2)) !== pair) return;
        if (e && r.e !== e) return;
        if (!best || (best.m == null && r.m != null)) best = r;
      });
      return best;
    }

    function recalc() {
      var row = state.row = findRow();
      resBox.innerHTML = '';
      resetDlv();
      if (!row) { empty(resBox, 'Выберите типоразмер — масса появится здесь.'); return; }

      var qty = Math.max(1, Math.round(fQ.value() || 1));
      if (row.m == null) {
        resNum(resBox, '—', 'кг', 'Масса этой позиции уточняется — запросите её у инженера, ответим в течение дня.');
      } else {
        resNum(resBox, fmtKg(row.m * qty), 'кг',
          qty > 1 ? fmt(qty, 0) + ' шт × ' + fmtKg(row.m) + NBSP + 'кг' : 'Масса одной детали');
      }
      resRows(resBox, [
        row.dn != null ? ['DN', fmt(row.dn, 0)] : null,
        row.r != null ? ['Радиус R', fmt(row.r, 1) + NBSP + 'мм'] : null,
        row.h != null ? ['Высота H', fmt(row.h, 1) + NBSP + 'мм'] : null,
        row.e ? ['Исполнение', esc(row.e)] : null,
        ['Позиция каталога', linkTo(row.u, row.t || 'открыть')]
      ]);
      actions(resBox, [
        { label: 'Запросить позицию →', onClick: function () { reqProduct(row.t, row.sku, qty); } },
        row.u ? { label: 'Открыть в каталоге', href: row.u, ghost: true } : null
      ]);
    }

    setType(state.type);
  }

  /* ════════════════ 02 · ФЛАНЦЫ И КРЕПЁЖ (КОФ) ════════════════ */

  /* Теоретическая масса метиза, кг — запасной путь, когда точного
     типоразмера нет в каталоге (модель по геометрии ГОСТ, ±5%). */
  function studMass(d, L) { return Math.PI * d * d / 4 * L * 7.85e-6 * 0.96; }
  function nutMass(d) { return 8.505e-6 * d * d * d; }
  function washerMass(d) { return 2.367e-6 * d * d * d; }

  /* Стандартный ряд длин шпилек/болтов, мм. */
  var LEN_ROW = [30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 110, 120, 130, 140, 150, 160, 170, 180, 190, 200, 220, 240, 260, 280, 300];
  function roundLen(L) {
    for (var i = 0; i < LEN_ROW.length; i++) { if (LEN_ROW[i] >= L - 0.01) return LEN_ROW[i]; }
    return Math.ceil(L / 10) * 10;
  }

  function initFlange(root) {
    var tabsBox = root.querySelector('[data-tabs]');
    var fieldsBox = root.querySelector('[data-fields]');
    var resBox = root.querySelector('[data-result]');
    var dlvBox = root.querySelector('[data-delivery]');

    var state = { rows: [], types: [], type: '', row: null, fasteners: null };

    var fNorm = makeSelect('Стандарт', onNorm, true);
    var fPn = makeSelect('Давление PN', function () { refill(false); });
    var fDn = makeSelect('Условный проход DN', recalc);
    var fKind = makeSelect('Крепёж соединения', recalc);
    var fGasket = makeInput('Прокладка, мм', '3', recalc);
    var fQ = makeInput('Соединений, шт', '1', recalc, { inputmode: 'numeric' });
    fKind.fill([{ v: 'stud', t: 'Шпильки + 2 гайки' }, { v: 'bolt', t: 'Болты + гайки' }]);
    [fNorm, fPn, fDn, fKind, fGasket, fQ].forEach(function (f) { fieldsBox.appendChild(f.root); });

    var resetDlv = makeDelivery(dlvBox, function () {
      var joints = Math.max(1, Math.round(fQ.value() || 1));
      // Крепёж в доставку не попадает: у метизов в каталоге масса за 1000 шт.
      return state.row && state.row.pid && state.row.m != null
        ? [{ product_id: state.row.pid, qty: 2 * joints }] : null;
    });

    getJSON(api('/flange-norms', {})).then(function (json) {
      var norms = json.norms || [];
      fNorm.fill(norms.map(function (n) { return { v: n.slug, t: n.label + ' · ' + n.n }; }), '');
      if (!norms.length) { empty(resBox, 'Фланцы с массой в каталоге не найдены.'); return; }
      onNorm();
    }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });

    function onNorm() {
      empty(resBox, 'Загружаем типоразмеры…');
      getJSON(api('/flange-rows', { norm: fNorm.value() })).then(function (json) {
        state.rows = json.rows || [];
        state.types = uniq(state.rows.map(function (r) { return r.type; }).filter(Boolean));
        tabsBox.innerHTML = '';
        state.types.forEach(function (t) {
          var b = el('button', 'clc-tab', esc(t));
          b.type = 'button';
          b.dataset.type = t;
          b.addEventListener('click', function () { setFType(t); });
          tabsBox.appendChild(b);
        });
        setFType(state.types.indexOf(state.type) >= 0 ? state.type : (state.types[0] || ''));
      }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });
    }

    function setFType(t) {
      state.type = t;
      Array.prototype.forEach.call(tabsBox.children, function (b) {
        b.classList.toggle('is-on', b.dataset.type === t);
      });
      refill(true);
    }

    function refill(reset) {
      var rows = state.rows.filter(function (r) { return r.type === state.type; });
      var pns = uniq(rows.map(function (r) { return r.pn; })).sort(asc);
      fPn.fill(pns.map(function (pn) {
        return { v: pn, t: fmt(pn, 2) + ' МПа · Ру ' + fmt(pn * 10, 1) };
      }), reset ? '' : undefined);
      var pn = num(fPn.value());
      var dns = uniq(rows.filter(function (r) { return r.pn === pn; }).map(function (r) { return r.dn; })).sort(asc);
      fDn.fill(dns.map(function (dn) { return { v: dn, t: 'DN ' + fmt(dn, 0) }; }), reset ? '' : undefined);
      recalc();
    }

    /* Каталог метизов для комплекта: тянем один раз по первому расчёту. */
    function loadFasteners() {
      if (state.fasteners) return state.fasteners;
      function all(kind) {
        return getJSON(api('/fastener-norms', { kind: kind })).then(function (json) {
          var norms = (json.norms || []).map(function (n) { return n.slug; });
          return Promise.all(norms.map(function (slug) {
            return getJSON(api('/fastener-rows', { kind: kind, norm: slug }))
              .then(function (j) { return j.rows || []; })
              .catch(function () { return []; });
          })).then(function (lists) { return [].concat.apply([], lists); });
        }).catch(function () { return []; });
      }
      state.fasteners = Promise.all([all('shpilki'), all('bolty'), all('gayki'), all('shayby')])
        .then(function (r) { return { studs: r[0], bolts: r[1], nuts: r[2], washers: r[3] }; });
      return state.fasteners;
    }

    /* Ближайший типоразмер из каталога: резьба M, длина ≥ L (для гаек/шайб — по резьбе). */
    function pickFastener(rows, M, L) {
      var best = null;
      rows.forEach(function (r) {
        if (r.M !== M || r.kg == null) return;
        if (L != null) {
          if (r.l == null || r.l < L - 0.01) return;
          if (!best || r.l < best.l) best = r;
        } else if (!best) {
          best = r;
        }
      });
      return best;
    }

    function recalc() {
      var pn = num(fPn.value()), dn = num(fDn.value());
      var row = state.row = state.rows.filter(function (r) {
        return r.type === state.type && r.pn === pn && r.dn === dn;
      }).sort(function (a, b) { return (a.m == null) - (b.m == null); })[0] || null;

      resBox.innerHTML = '';
      resetDlv();
      if (!row) { empty(resBox, 'Выберите типоразмер — расчёт появится здесь.'); return; }

      var joints = Math.max(1, Math.round(fQ.value() || 1));
      var gasket = Math.max(0, fGasket.value() == null ? 3 : fGasket.value());
      var isStud = fKind.value() !== 'bolt';
      var haveKit = row.n != null && row.M != null && row.b != null;

      /* Длина: 2 фланца + прокладка + гайки 0,8d + шайбы 0,15d + выступ 0,3d на сторону. */
      var L = null;
      if (haveKit) {
        var d = row.M;
        L = isStud
          ? 2 * row.b + gasket + 2 * (0.8 * d) + 2 * (0.15 * d) + 2 * (0.3 * d)
          : 2 * row.b + gasket + 0.8 * d + 2 * (0.15 * d) + 0.3 * d;
        L = roundLen(L);
      }

      resNum(resBox, row.m != null ? fmtKg(2 * row.m * joints) : '—', 'кг',
        'КОФ: ' + fmt(2 * joints, 0) + NBSP + plural(2 * joints, ['фланец', 'фланца', 'фланцев']) +
        (row.m != null ? ' × ' + fmtKg(row.m) + NBSP + 'кг' : ' — масса уточняется'));

      resRows(resBox, [
        ['Фланец', linkTo(row.u, row.t || 'позиция каталога')],
        ['Геометрия', 'Dн ' + fmt(row.d, 0) + ' · Dб ' + fmt(row.db, 0) + ' · b ' + fmt(row.b, 0) + NBSP + 'мм'],
        haveKit ? ['Отверстия', fmt(row.n, 0) + '×M' + fmt(row.M, 0)] : ['Отверстия', 'уточняется'],
        ['PN / DN', fmt(pn, 2) + NBSP + 'МПа · DN ' + fmt(dn, 0)]
      ]);

      if (!haveKit) {
        resBox.appendChild(el('div', 'clc-empty', 'Для этого типоразмера в каталоге нет данных по крепёжным отверстиям — запросите комплект, инженер подберёт по нормативу.'));
        actions(resBox, [
          { label: 'Запросить КОФ →', onClick: function () { reqProduct(row.t, row.sku, 2 * joints); } },
          row.u ? { label: 'Открыть фланец', href: row.u, ghost: true } : null
        ]);
        return;
      }

      var kitBox = el('div', 'clc-res');
      kitBox.appendChild(el('div', 'clc-dlv-t', 'Комплект крепежа на ' + fmt(joints, 0) + NBSP + plural(joints, ['соединение', 'соединения', 'соединений'])));
      var kitList = el('div', 'clc-kit');
      kitBox.appendChild(kitList);
      resBox.appendChild(kitBox);
      kitList.appendChild(el('div', 'clc-empty', 'Подбираем крепёж из каталога…'));

      loadFasteners().then(function (F) {
        var d = row.M;
        var main = isStud
          ? pickFastener(F.studs, d, L)
          : pickFastener(F.bolts, d, L);
        var nut = pickFastener(F.nuts, d, null);
        var washer = pickFastener(F.washers, d, null);

        var mainQty = row.n * joints;
        var nutQty = (isStud ? 2 : 1) * row.n * joints;
        var washQty = 2 * row.n * joints;

        var mainL = main ? main.l : L;
        var mainM = main ? main.kg / 1000 : studMass(d, L) * (isStud ? 1 : 1.12); // болт ≈ шпилька + головка
        var nutM = nut ? nut.kg / 1000 : nutMass(d);
        var washM = washer ? washer.kg / 1000 : washerMass(d);

        var kindName = isStud ? 'Шпилька' : 'Болт';
        var items = [
          { name: kindName + ' M' + fmt(d, 0) + '×' + fmt(mainL, 0) + (main ? '' : ' <span class="mut">≈ расчётная масса</span>'),
            url: main && main.u, qty: mainQty, mass: mainM * mainQty },
          { name: 'Гайка M' + fmt(d, 0) + (nut ? '' : ' <span class="mut">≈ расчётная масса</span>'),
            url: nut && nut.u, qty: nutQty, mass: nutM * nutQty },
          { name: 'Шайба ' + fmt(d, 0) + (washer ? '' : ' <span class="mut">≈ расчётная масса</span>'),
            url: washer && washer.u, qty: washQty, mass: washM * washQty }
        ];

        kitList.innerHTML = '';
        var kitMass = 0;
        items.forEach(function (it) {
          kitMass += it.mass;
          var r = el('div', 'clc-kit-r');
          r.appendChild(el('div', 'clc-kit-name', it.url ? '<a href="' + esc(it.url) + '">' + it.name + '</a>' : it.name));
          r.appendChild(el('div', 'clc-kit-q', fmt(it.qty, 0) + NBSP + 'шт'));
          r.appendChild(el('div', 'clc-kit-m', fmtKg(it.mass) + NBSP + 'кг'));
          kitList.appendChild(r);
        });
        var totalRow = el('div', 'clc-kit-r');
        totalRow.appendChild(el('div', 'clc-kit-name', '<b>Итого: КОФ + крепёж</b>'));
        totalRow.appendChild(el('div', 'clc-kit-q', ''));
        var total = (row.m != null ? 2 * row.m * joints : 0) + kitMass;
        totalRow.appendChild(el('div', 'clc-kit-m', fmtKg(total) + NBSP + 'кг'));
        kitList.appendChild(totalRow);

        actions(kitBox, [
          { label: 'Запросить весь комплект →', onClick: function () {
            var lines = [
              'Комплект фланцевого соединения ' + (row.t || '') + ' — ' + fmt(joints, 0) + ' соед.:',
              '— Фланец: ' + fmt(2 * joints, 0) + ' шт' + (row.sku ? ' (SKU ' + row.sku + ')' : ''),
              '— ' + kindName + ' M' + fmt(d, 0) + '×' + fmt(mainL, 0) + ': ' + fmt(mainQty, 0) + ' шт',
              '— Гайка M' + fmt(d, 0) + ': ' + fmt(nutQty, 0) + ' шт',
              '— Шайба ' + fmt(d, 0) + ': ' + fmt(washQty, 0) + ' шт',
              'Прокладка ' + fmt(gasket, 1) + ' мм — ' + fmt(joints, 0) + ' шт (уточнить тип).',
              'Ориентировочная масса: ' + fmtKg(total) + ' кг.'
            ];
            reqKit(lines.join('\n'), 'Запросить\nкомплект КОФ');
          } },
          { label: 'Запросить только фланцы', ghost: true, onClick: function () { reqProduct(row.t, row.sku, 2 * joints); } }
        ]);
        kitBox.appendChild(el('div', 'clc-note',
          'Длина расчётная: 2×b + прокладка + ' + (isStud ? '2 гайки (0,8d)' : 'гайка (0,8d)') +
          ' + шайбы + выступ резьбы; округлена вверх до стандартного ряда. Материал и класс прочности крепежа подбираются по температуре и среде — проверьте по проекту.'));
      });
    }
  }

  /* ════════════════ 03 · МЕТИЗЫ: КГ ↔ ШТ ════════════════ */

  var FASTENER_KINDS = [
    ['bolty', 'Болты'],
    ['gayki', 'Гайки'],
    ['shpilki', 'Шпильки'],
    ['shayby', 'Шайбы'],
    ['vinty', 'Винты']
  ];

  function initMetizy(root) {
    var tabsBox = root.querySelector('[data-tabs]');
    var fieldsBox = root.querySelector('[data-fields]');
    var resBox = root.querySelector('[data-result]');

    var state = { kind: 'bolty', rows: [], dir: 'toKg', row: null };

    var tabs = {};
    FASTENER_KINDS.forEach(function (t) {
      var b = el('button', 'clc-tab', esc(t[1]));
      b.type = 'button';
      b.addEventListener('click', function () { setKind(t[0]); });
      tabs[t[0]] = b;
      tabsBox.appendChild(b);
    });

    var fNorm = makeSelect('Стандарт', onNorm, true);
    var fM = makeSelect('Резьба M', function () { refill(false); });
    var fL = makeSelect('Длина L, мм', function () { refill(false); });
    var fC = makeSelect('Класс / тип', recalc);
    var fDir = makeSelect('Направление', function () { syncDir(); recalc(); });
    fDir.fill([{ v: 'toKg', t: 'Штуки → килограммы' }, { v: 'toPcs', t: 'Килограммы → штуки' }]);
    var fQty = makeInput('Количество, шт', '1000', recalc, { inputmode: 'numeric' });
    var fKg = makeInput('Масса партии, кг', '100', recalc);
    [fNorm, fM, fL, fC, fDir, fQty, fKg].forEach(function (f) { fieldsBox.appendChild(f.root); });

    function syncDir() {
      state.dir = fDir.value();
      fQty.root.style.display = state.dir === 'toKg' ? '' : 'none';
      fKg.root.style.display = state.dir === 'toPcs' ? '' : 'none';
    }

    function setKind(kind) {
      state.kind = kind;
      Object.keys(tabs).forEach(function (k) { tabs[k].classList.toggle('is-on', k === kind); });
      empty(resBox, 'Загружаем нормативы…');
      getJSON(api('/fastener-norms', { kind: kind })).then(function (json) {
        var norms = json.norms || [];
        fNorm.fill(norms.map(function (n) { return { v: n.slug, t: n.label + ' · ' + n.n }; }), '');
        if (!norms.length) { empty(resBox, 'По этому виду пока нет позиций с массой.'); return; }
        onNorm();
      }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });
    }

    function onNorm() {
      empty(resBox, 'Загружаем типоразмеры…');
      getJSON(api('/fastener-rows', { kind: state.kind, norm: fNorm.value() })).then(function (json) {
        state.rows = (json.rows || []).filter(function (r) { return r.kg != null; });
        refill(true);
      }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });
    }

    function refill(reset) {
      var rows = state.rows;
      var ms = uniq(rows.map(function (r) { return r.M; })).sort(asc);
      fM.fill(ms.map(function (m) { return { v: m, t: 'M' + fmt(m, 1) }; }), reset ? '' : undefined);
      var M = num(fM.value());
      var inM = rows.filter(function (r) { return r.M === M; });

      var ls = uniq(inM.map(function (r) { return r.l; }).filter(function (l) { return l != null; })).sort(asc);
      fL.fill(ls.map(function (l) { return { v: l, t: fmt(l, 1) }; }), reset ? '' : undefined);
      fL.show(ls.length > 0);
      var L = fL.root.style.display === 'none' ? null : num(fL.value());
      var inL = inM.filter(function (r) { return L == null || r.l === L; });

      var cls = uniq(inL.map(function (r) { return r.cls; }).filter(Boolean));
      fC.fill(cls.map(function (c) { return { v: c, t: c }; }), reset ? '' : undefined);
      fC.show(cls.length > 1);

      recalc();
    }

    function recalc() {
      var M = num(fM.value());
      var L = fL.root.style.display === 'none' ? null : num(fL.value());
      var cls = fC.root.style.display === 'none' ? '' : fC.value();
      var row = state.row = state.rows.filter(function (r) {
        return r.M === M && (L == null || r.l === L) && (!cls || r.cls === cls);
      })[0] || null;

      resBox.innerHTML = '';
      if (!row) { empty(resBox, 'Выберите типоразмер — расчёт появится здесь.'); return; }

      var perPc = row.kg / 1000;
      if (state.dir === 'toKg') {
        var qty = Math.max(1, Math.round(fQty.value() || 1));
        resNum(resBox, fmtKg(perPc * qty), 'кг', fmt(qty, 0) + ' шт × ' + fmtKg(row.kg) + NBSP + 'кг за 1000 шт');
      } else {
        var kg = Math.max(0, fKg.value() || 0);
        var pcs = perPc > 0 ? Math.floor(kg / perPc) : 0;
        resNum(resBox, fmt(pcs, 0), 'шт', 'В ' + fmtKg(kg) + NBSP + 'кг · по ' + fmtKg(row.kg) + NBSP + 'кг за 1000 шт');
      }
      resRows(resBox, [
        ['Масса 1 шт', perPc >= 0.001 ? fmtKg(perPc) + NBSP + 'кг' : fmt(perPc * 1000, 2) + NBSP + 'г'],
        ['Масса 1000 шт', fmtKg(row.kg) + NBSP + 'кг'],
        ['Позиция каталога', linkTo(row.u, row.t || 'открыть')]
      ]);
      actions(resBox, [
        { label: 'Запросить позицию →', onClick: function () {
          var qty2 = state.dir === 'toKg' ? Math.round(fQty.value() || 1) : (perPc > 0 ? Math.floor((fKg.value() || 0) / perPc) : '');
          reqProduct(row.t, row.sku, qty2);
        } },
        row.u ? { label: 'Открыть в каталоге', href: row.u, ghost: true } : null
      ]);
    }

    syncDir();
    setKind(state.kind);
  }

  /* ════════════════ 04 · ТРУБЫ: МЕТРЫ ↔ ТОННЫ ════════════════ */

  function initPipes(root) {
    var tabsBox = root.querySelector('[data-tabs]');
    var fieldsBox = root.querySelector('[data-fields]');
    var resBox = root.querySelector('[data-result]');

    var state = { rows: [], mode: 'catalog', last: 'len' };

    var modes = {};
    [['catalog', 'Из каталога'], ['manual', 'Свои размеры']].forEach(function (t) {
      var b = el('button', 'clc-tab', esc(t[1]));
      b.type = 'button';
      b.addEventListener('click', function () { setMode(t[0]); });
      modes[t[0]] = b;
      tabsBox.appendChild(b);
    });

    var fD = makeSelect('Диаметр Дн, мм', function () { refill(false); });
    var fS = makeSelect('Стенка s, мм', function () { refill(false); });
    var fN = makeSelect('Стандарт', recalc, true);
    var fDm = makeInput('Диаметр Дн, мм', '108', recalc);
    var fSm = makeInput('Стенка s, мм', '4', recalc);
    var fLen = makeInput('Длина, м', '100', function () { state.last = 'len'; recalc(); });
    var fTon = makeInput('Масса, т', '', function () { state.last = 'ton'; recalc(); });
    [fD, fS, fN, fDm, fSm, fLen, fTon].forEach(function (f) { fieldsBox.appendChild(f.root); });

    function setMode(m) {
      state.mode = m;
      Object.keys(modes).forEach(function (k) { modes[k].classList.toggle('is-on', k === m); });
      var cat = m === 'catalog';
      fD.show(cat); fS.show(cat); fN.show(cat);
      fDm.root.style.display = cat ? 'none' : '';
      fSm.root.style.display = cat ? 'none' : '';
      if (cat && !state.rows.length) {
        empty(resBox, 'Загружаем сортамент…');
        getJSON(api('/pipe-rows', {})).then(function (json) {
          state.rows = json.rows || [];
          refill(true);
        }).catch(function () { empty(resBox, 'Данные временно недоступны — обновите страницу.'); });
      } else {
        recalc();
      }
    }

    function refill(reset) {
      var rows = state.rows;
      var ds = uniq(rows.map(function (r) { return r.d; })).sort(asc);
      fD.fill(ds.map(function (d) { return { v: d, t: fmt(d, 1) }; }), reset ? '' : undefined);
      var d = num(fD.value());
      var inD = rows.filter(function (r) { return r.d === d; });
      var ss = uniq(inD.map(function (r) { return r.s; })).sort(asc);
      fS.fill(ss.map(function (s) { return { v: s, t: fmt(s, 1) }; }), reset ? '' : undefined);
      var s = num(fS.value());
      var norms = inD.filter(function (r) { return r.s === s; });
      fN.fill(norms.map(function (r, i) { return { v: i, t: r.norm || 'ГОСТ' }; }), reset ? '' : undefined);
      fN.show(norms.length > 1);
      recalc();
    }

    function current() {
      if (state.mode === 'catalog') {
        var d = num(fD.value()), s = num(fS.value());
        var norms = state.rows.filter(function (r) { return r.d === d && r.s === s; });
        var row = norms[Math.min(norms.length - 1, Math.max(0, parseInt(fN.value() || '0', 10) || 0))] || norms[0];
        return row ? { d: d, s: s, kg: row.kg, row: row } : null;
      }
      var dm = fDm.value(), sm = fSm.value();
      if (!dm || !sm || sm <= 0 || dm <= sm * 2) return null;
      // Формула массы метра стальной трубы: (D − s) · s · 0,02466 кг/м.
      return { d: dm, s: sm, kg: (dm - sm) * sm * 0.02466, row: null };
    }

    function recalc() {
      var c = current();
      resBox.innerHTML = '';
      if (!c) { empty(resBox, 'Укажите размеры трубы — расчёт появится здесь.'); return; }

      var len = null, ton = null;
      if (state.last === 'ton' && fTon.value() != null) {
        ton = Math.max(0, fTon.value());
        len = ton * 1000 / c.kg;
        fLen.input.value = len ? (Math.round(len * 100) / 100).toString().replace('.', ',') : '';
      } else {
        len = Math.max(0, fLen.value() || 0);
        ton = len * c.kg / 1000;
        fTon.input.value = ton ? (Math.round(ton * 1000) / 1000).toString().replace('.', ',') : '';
      }

      var inner = c.d - 2 * c.s;
      var paintM2 = Math.PI * c.d / 1000 * len;
      var volM = Math.PI * inner * inner / 4 / 1e6; // м³ на 1 м = тыс. л
      resNum(resBox, fmt(ton, 3), 'т', fmt(len, 2) + NBSP + 'м × ' + fmt(c.kg, 2) + NBSP + 'кг/м');
      resRows(resBox, [
        ['Масса 1 м', fmt(c.kg, 2) + NBSP + 'кг'],
        ['Метров в тонне', c.kg > 0 ? fmt(1000 / c.kg, 1) + NBSP + 'м' : '—'],
        ['Площадь окраски', fmt(paintM2, 1) + NBSP + 'м²'],
        ['Объём (вместимость)', fmt(volM * 1000, 2) + NBSP + 'л/м · ' + fmt(volM * len, 2) + NBSP + 'м³ всего'],
        c.row ? ['Позиция каталога', linkTo(c.row.u, 'Труба ' + fmt(c.d, 1) + '×' + fmt(c.s, 1))] : null
      ]);
      actions(resBox, [
        { label: 'Запросить трубу →', onClick: function () {
          if (window.openRequestModal) {
            window.openRequestModal('calc', {
              name: 'Труба ' + fmt(c.d, 1) + '×' + fmt(c.s, 1),
              std: c.row ? c.row.norm : '',
              dn: 'Ø ' + fmt(c.d, 1) + ' мм',
              qty: Math.round(len) + ' м'
            });
          }
        } },
        c.row && c.row.u ? { label: 'Открыть в каталоге', href: c.row.u, ghost: true } : null
      ]);
    }

    setMode('catalog');
  }

  /* ════════════════ 05 · DN · ДЮЙМЫ · ДИАМЕТРЫ ════════════════ */

  /* DN · NPS · резьба · Дн по ГОСТ (РФ) · OD EN/ASME. */
  var DN_TABLE = [
    [10, '3/8', 17, 17.2],
    [15, '1/2', 21.3, 21.3],
    [20, '3/4', 26.8, 26.9],
    [25, '1', 33.5, 33.7],
    [32, '1 1/4', 42.3, 42.4],
    [40, '1 1/2', 48, 48.3],
    [50, '2', 57, 60.3],
    [65, '2 1/2', 76, 76.1],
    [80, '3', 89, 88.9],
    [100, '4', 108, 114.3],
    [125, '5', 133, 139.7],
    [150, '6', 159, 168.3],
    [200, '8', 219, 219.1],
    [250, '10', 273, 273.1],
    [300, '12', 325, 323.9],
    [350, '14', 377, 355.6],
    [400, '16', 426, 406.4],
    [500, '20', 530, 508],
    [600, '24', 630, 610],
    [700, '28', 720, 711],
    [800, '32', 820, 813],
    [900, '36', 920, 914],
    [1000, '40', 1020, 1016],
    [1200, '48', 1220, 1219]
  ];

  function initDn(root) {
    var box = root.querySelector('[data-table]');
    var search = root.querySelector('[data-search]');

    var tbl = el('table', 'clc-tbl');
    tbl.innerHTML = '<thead><tr><th>DN (Ду)</th><th>Дюймы (NPS)</th><th>Дн по ГОСТ, мм</th><th>OD EN/ASME, мм</th></tr></thead>';
    var tb = el('tbody');
    DN_TABLE.forEach(function (r) {
      var tr = el('tr');
      tr.innerHTML = '<td>DN ' + r[0] + '</td><td>' + esc(r[1]) + '″</td><td>' + fmt(r[2], 1) + '</td><td class="mut">' + fmt(r[3], 1) + '</td>';
      tr.dataset.k = (r[0] + ' ' + r[1] + ' ' + r[2] + ' ' + r[3]).toLowerCase();
      tb.appendChild(tr);
    });
    tbl.appendChild(tb);
    var wrap = el('div', 'clc-tbl-wrap');
    wrap.appendChild(tbl);
    box.appendChild(wrap);

    if (search) {
      search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase().replace(',', '.');
        Array.prototype.forEach.call(tb.children, function (tr) {
          var hit = q !== '' && tr.dataset.k.indexOf(q) !== -1;
          tr.classList.toggle('hl', hit);
          tr.style.display = q === '' || hit ? '' : 'none';
        });
      });
    }
  }

  /* ════════════════ 06 · АНАЛОГИ МАРОК СТАЛИ ════════════════ */

  /* Ближайшие аналоги (справочно): [марка, EN / W.-Nr., ASTM/AISI, DIN, применение]. */
  var STEELS = [
    ['Ст3сп', 'S235JR / 1.0038', 'A36; A283 Gr.C', 'St37-2', 'Конструкции, крепёж общего назначения'],
    ['10', 'P235GH / 1.0345', 'A106 Gr.A', 'St35.8', 'Трубы и детали до +425 °C'],
    ['20', 'P265GH / 1.0425', 'A106 Gr.B', 'St45.8 / C22', 'Основная марка СДТ и труб ТЭС/ЖКХ'],
    ['09Г2С', 'P355NH / 1.0565', 'A516 Gr.70', '13Mn6 / 19Mn6', 'Низкие температуры до −70 °C, сосуды'],
    ['16ГС', 'P295GH / 1.0481', 'A516 Gr.60', '17Mn4', 'Сосуды и аппараты под давлением'],
    ['17Г1С', 'P355N / L360', 'A572 Gr.50; API 5L X52', 'StE355', 'Магистральные трубопроводы'],
    ['10Г2', 'P275NL1', 'A333 Gr.6', 'TTSt35N', 'Хладостойкие трубы и детали'],
    ['15ХМ', '13CrMo4-5 / 1.7335', 'A335 P12', '13CrMo44', 'Паропроводы до +560 °C'],
    ['12ХМ', '13CrMo4-5 / 1.7335', 'A387 Gr.12', '13CrMo44', 'Котельные и сосудовые элементы'],
    ['12Х1МФ', '14MoV6-3 / 1.7715', '— (ближайший P24)', '14MoV63', 'Паропроводы до +585 °C'],
    ['15Х1М1Ф', '≈14MoV6-3', 'прямого нет', '—', 'Паропроводы высокого давления'],
    ['15Х5М', 'X11CrMo5 / 1.7362', 'A335 P5', '12CrMo19-5', 'Нефтехимия, водородные среды'],
    ['25Х1МФ', '40CrMoV4-6', 'A193 B16', '21CrMoV5-7', 'Теплоустойчивый фланцевый крепёж'],
    ['25Х2М1Ф', '≈40CrMoV4-6', '≈A193 B16', '—', 'Крепёж высоких параметров'],
    ['30ХМА', '25CrMo4 / 1.7218', 'AISI 4130', '25CrMo4', 'Высокопрочный крепёж'],
    ['35', 'C35 / 1.0501', 'AISI 1035', 'C35', 'Крепёж, точёные детали'],
    ['45', 'C45 / 1.0503', 'AISI 1045', 'C45', 'Валы, точёные детали'],
    ['40Х', '41Cr4 / 1.7035', 'AISI 5140', '41Cr4', 'Крепёж классов 8.8–10.9'],
    ['65Г', '≈66Mn4 / C67S', 'AISI 1066', 'Ck67', 'Пружинные шайбы'],
    ['08кп', 'DC01 / 1.0330', 'AISI 1008', 'St12', 'Плоские шайбы, штамповка'],
    ['08Х18Н10Т', 'X6CrNiTi18-10 / 1.4541', 'AISI 321', 'X6CrNiTi18-10', 'Нержавеющие СДТ, АЭС, химия'],
    ['12Х18Н10Т', 'X6CrNiTi18-10 / 1.4541', 'AISI 321 / 321H', 'X10CrNiTi18-9', 'Нержавеющие СДТ, АЭС, химия'],
    ['08Х18Н12Т', '≈1.4541', '≈AISI 321', '—', 'Нержавеющие детали'],
    ['10Х17Н13М2Т', 'X6CrNiMoTi17-12-2 / 1.4571', 'AISI 316Ti', 'X6CrNiMoTi17-12-2', 'Кислотостойкие среды'],
    ['30Х13', 'X30Cr13 / 1.4028', 'AISI 420', 'X30Cr13', 'Мартенситные детали, метизы'],
    ['13ХФА', 'прямого нет', 'прямого нет', '—', 'Нефтепромысловые среды H₂S/CO₂ — подбор по ТУ']
  ];

  function initSteels(root) {
    var box = root.querySelector('[data-table]');
    var search = root.querySelector('[data-search]');

    var tbl = el('table', 'clc-tbl');
    tbl.innerHTML = '<thead><tr><th>ГОСТ (РФ)</th><th>EN / W.-Nr.</th><th>ASTM / AISI</th><th>DIN</th><th>Применение</th></tr></thead>';
    var tb = el('tbody');
    STEELS.forEach(function (r) {
      var tr = el('tr');
      tr.innerHTML = '<td><b>' + esc(r[0]) + '</b></td><td>' + esc(r[1]) + '</td><td>' + esc(r[2]) + '</td><td class="mut">' + esc(r[3]) + '</td><td class="mut">' + esc(r[4]) + '</td>';
      tr.dataset.k = r.join(' ').toLowerCase();
      tb.appendChild(tr);
    });
    tbl.appendChild(tb);
    var wrap = el('div', 'clc-tbl-wrap');
    wrap.appendChild(tbl);
    box.appendChild(wrap);

    if (search) {
      search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase();
        Array.prototype.forEach.call(tb.children, function (tr) {
          var hit = q !== '' && tr.dataset.k.indexOf(q) !== -1;
          tr.classList.toggle('hl', hit);
          tr.style.display = q === '' || hit ? '' : 'none';
        });
      });
    }
    var consult = root.querySelector('[data-consult]');
    if (consult) {
      consult.addEventListener('click', function () {
        if (window.openRequestModal) window.openRequestModal('solution');
      });
    }
  }

  /* ── 07 · СТОИМОСТЬ ДОСТАВКИ (груз задаёт человек) ──
     Единственный калькулятор раздела, который считает не по каталогу: сюда
     приходят с заказом целиком, паллетой или чужим грузом. Поэтому габариты
     и вес принимаются с формы, а рамки значений держит сервер
     (promen_rest_delivery_quote_custom). */

  function initDostavka(root) {
    var tabsBox = root.querySelector('[data-tabs]');
    var rowsBox = root.querySelector('[data-rows]');
    var extraBox = root.querySelector('[data-extra]');
    var routeBox = root.querySelector('[data-route]');
    var goBtn = root.querySelector('[data-go]');
    var resBox = root.querySelector('[data-result]');

    var MODES = [['one', 'Одно грузоместо'], ['same', 'Одинаковые грузоместа'], ['diff', 'Разные грузоместа']];
    var mode = 'one';
    var rows = [blank()];
    var city = null;
    var volumeOverride = null;

    /* Габариты европалеты — самый частый случай отгрузки; вес не выдумываем. */
    function blank() { return { l: 1.2, w: 0.8, h: 0.5, weight: null, qty: 1 }; }

    function autoVolume() {
      var v = 0;
      rows.forEach(function (r) {
        if (r.l && r.w && r.h) v += r.l * r.w * r.h * (mode === 'one' ? 1 : (r.qty || 1));
      });
      return v;
    }
    function totalWeight() {
      var t = 0;
      rows.forEach(function (r) { if (r.weight) t += r.weight * (mode === 'one' ? 1 : (r.qty || 1)); });
      return t;
    }
    function totalPlaces() {
      if (mode === 'one') return 1;
      var n = 0;
      rows.forEach(function (r) { n += (r.qty || 1); });
      return n;
    }

    /* ── груз ── */

    MODES.forEach(function (m) {
      var b = el('button', 'clc-tab' + (m[0] === mode ? ' is-on' : ''), esc(m[1]));
      b.type = 'button';
      b.setAttribute('role', 'tab');
      b.addEventListener('click', function () {
        if (mode === m[0]) return;
        mode = m[0];
        if (mode !== 'diff' && rows.length > 1) rows = [rows[0]];
        if (mode === 'one') rows[0].qty = 1;
        tabsBox.querySelectorAll('.clc-tab').forEach(function (t) { t.classList.toggle('is-on', t === b); });
        drawRows();
        drawExtra();
      });
      tabsBox.appendChild(b);
    });

    function cell(label, row, key, attrs) {
      var f = makeInput(label, row[key] == null ? '' : String(row[key]).replace('.', ','), function () {
        row[key] = num(this.value);
        syncVolume();
      }, attrs);
      return f.root;
    }

    function drawRows() {
      rowsBox.innerHTML = '';
      rows.forEach(function (row, i) {
        var box = el('div', 'dlv-place');
        if (mode === 'diff') {
          var hd = el('div', 'dlv-hd');
          hd.appendChild(el('span', '', 'Место ' + (i + 1)));
          if (rows.length > 1) {
            var del = el('button', 'dlv-del', '✕');
            del.type = 'button';
            del.setAttribute('aria-label', 'Убрать место ' + (i + 1));
            del.addEventListener('click', function () {
              rows.splice(i, 1);
              drawRows();
              syncVolume();
            });
            hd.appendChild(del);
          }
          box.appendChild(hd);
        }
        var grid = el('div', 'dlv-grid' + (mode === 'one' ? '' : ' has-qty'));
        grid.appendChild(cell('Длина, м', row, 'l', { placeholder: '1,2' }));
        grid.appendChild(cell('Ширина, м', row, 'w', { placeholder: '0,8' }));
        grid.appendChild(cell('Высота, м', row, 'h', { placeholder: '0,5' }));
        grid.appendChild(cell('Вес места, кг', row, 'weight', { placeholder: '300' }));
        if (mode !== 'one') grid.appendChild(cell('Мест', row, 'qty', { placeholder: '1' }));
        box.appendChild(grid);
        rowsBox.appendChild(box);
      });

      if (mode === 'diff') {
        var add = el('button', 'clc-btn clc-btn--ghost dlv-add', '+ Добавить место');
        add.type = 'button';
        add.addEventListener('click', function () {
          if (rows.length >= 10) return;
          rows.push(blank());
          drawRows();
          syncVolume();
        });
        rowsBox.appendChild(add);
      }

      var sum = el('div', 'dlv-sum', sumHtml());
      rowsBox.appendChild(sum);
    }

    function sumHtml() {
      return '<span>Мест: <b>' + totalPlaces() + '</b></span>'
        + '<span>Вес: <b>' + (totalWeight() ? fmtKg(totalWeight()) + NBSP + 'кг' : '—') + '</b></span>'
        + '<span>Объём: <b>' + (autoVolume() ? fmt(autoVolume(), 2) + NBSP + 'м³' : '—') + '</b></span>';
    }

    var volField = null;
    function syncVolume() {
      if (volField) volField.input.placeholder = autoVolume() ? fmt(autoVolume(), 3) : '';
      var sum = rowsBox.querySelector('.dlv-sum');
      if (sum) sum.innerHTML = sumHtml();
    }

    function drawExtra() {
      extraBox.innerHTML = '';
      // Поля пересоздаются вместе с вкладкой, поэтому сбрасываем и значения:
      // иначе пустое поле показывало бы старое переопределение.
      volumeOverride = null;
      stated = null;
      // Объём считается из габаритов, но уложенная партия занимает меньше
      // суммы габаритных ящиков — реальную цифру знает только отправитель.
      volField = makeInput('Объём, м³ (если известен)', '', function () {
        volumeOverride = num(this.value);
      }, { placeholder: autoVolume() ? fmt(autoVolume(), 3) : '' });
      extraBox.appendChild(volField.root);

      var val = makeInput('Объявленная стоимость, ₽', '', function () {
        stated = num(this.value);
      }, { placeholder: 'не обязательно' });
      extraBox.appendChild(val.root);
    }

    var stated = null;

    /* ── маршрут ── */

    var cityWrap = el('div', 'clc-field clc-field--wide');
    cityWrap.appendChild(el('label', 'clc-field-label', 'Город назначения'));
    var cityForm = el('div', 'clc-dlv-form');
    var cityIn = document.createElement('input');
    cityIn.type = 'text';
    cityIn.className = 'clc-dlv-city';
    cityIn.placeholder = 'Начните вводить город…';
    cityIn.autocomplete = 'off';
    var cityList = el('div', 'clc-dlv-list');
    cityForm.appendChild(cityIn);
    cityForm.appendChild(cityList);
    cityWrap.appendChild(cityForm);
    routeBox.appendChild(cityWrap);

    var picked = attachCityPicker(cityIn, cityList, {
      host: cityWrap,
      onPick: function (c) { city = c; },
      onClear: function () { city = null; }
    });

    var toSel = makeSelect('Куда', function () {
      addrField.show(toSel.value() === 'address');
    });
    toSel.fill([{ v: 'terminal', t: 'До терминала (самовывоз)' }, { v: 'address', t: 'До адреса' }]);
    routeBox.appendChild(toSel.root);

    var typeSel = makeSelect('Перевозка', function () {});
    typeSel.fill([{ v: 'auto', t: 'Авто — обычная' }, { v: 'express', t: 'Экспресс' }, { v: 'avia', t: 'Авиа' }]);
    routeBox.appendChild(typeSel.root);

    var address = '';
    var addrField = makeInput('Адрес доставки', '', function () { address = this.value; },
      { placeholder: 'улица, дом' }, true);
    routeBox.appendChild(addrField.root);
    addrField.show = function (on) { addrField.root.style.display = on ? '' : 'none'; };
    addrField.show(false);

    /* ── расчёт ── */

    function fail(text) {
      resBox.innerHTML = '';
      resBox.appendChild(el('div', 'clc-empty', esc(text)));
    }

    goBtn.addEventListener('click', function () {
      if (!city || !picked.code) { fail('Выберите город назначения из подсказок.'); cityIn.focus(); return; }
      var bad = rows.some(function (r) {
        return !r.l || !r.w || !r.h || !r.weight || r.l < 0.05 || r.w < 0.05 || r.h < 0.05
          || r.l > 6 || r.w > 6 || r.h > 6;
      });
      if (bad) { fail('Заполните габариты (0,05–6 м) и вес каждого места.'); return; }
      if (toSel.value() === 'address' && !address.trim()) { fail('Укажите адрес доставки или выберите «до терминала».'); return; }
      if (totalWeight() > 20000) { fail('Груз тяжелее 20 т — это уже выделенная машина, посчитаем по запросу.'); return; }

      goBtn.disabled = true;
      goBtn.textContent = 'Считаем…';
      resBox.innerHTML = '';
      resBox.appendChild(el('div', 'clc-empty', 'Запрашиваем тариф «Деловых Линий»…'));

      fetch((CFG.deliveryApi || '/wp-json/promen/v1/delivery') + '/quote-custom', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          city_code: picked.code,
          to: toSel.value(),
          address: toSel.value() === 'address' ? (city.full || city.name) + ', ' + address.trim() : '',
          type: typeSel.value(),
          stated_value: stated || 0,
          volume: volumeOverride || 0,
          places: rows.map(function (r) {
            return { length: r.l, width: r.w, height: r.h, weight: r.weight, qty: mode === 'one' ? 1 : (r.qty || 1) };
          })
        })
      })
        .then(function (r) { return r.json(); })
        .then(show)
        .catch(function () { fail('Сеть недоступна — попробуйте ещё раз.'); })
        .then(function () { goBtn.disabled = false; goBtn.textContent = 'Рассчитать доставку'; });
    });

    function show(json) {
      if (!json || !json.ok) {
        var code = json && json.error;
        fail(
          code === 'too_heavy' ? 'Груз тяжелее 20 т — это выделенная машина, посчитаем по запросу.' :
          code === 'no_terminal' ? 'В этом населённом пункте нет терминала «Деловых Линий» — уточним доставку по запросу.' :
          code === 'rate_limited' ? 'Слишком много расчётов подряд — попробуйте через минуту.' :
          code === 'bad_dims' ? 'Габарит места должен быть от 0,05 до 6 м.' :
          code === 'bad_weight' ? 'Вес места должен быть больше нуля и не больше 20 т.' :
          code === 'bad_qty' ? 'Мест в строке — от 1 до 200.' :
          code === 'not_configured' ? 'Расчёт временно недоступен — напишите менеджеру, посчитаем вручную.' :
          'Не удалось рассчитать — проверьте параметры или напишите менеджеру.');
        return;
      }
      resBox.innerHTML = '';
      var toAddr = toSel.value() === 'address';
      var where = toAddr
        ? 'до адреса' + (city && city.name ? ' · ' + esc(city.name) : '')
        : 'до терминала' + (json.terminal ? ' «' + esc(json.terminal) + '»' : '');
      resNum(resBox, fmt(json.price, 0), '₽',
        where + (json.eta ? ' · выдача с ' + esc(json.eta) : ''));

      var p = json.parts || {};
      var haul = { auto: 'Межтерминальная перевозка', express: 'Экспресс-перевозка', avia: 'Авиаперевозка' }[typeSel.value()];
      resRows(resBox, [
        ['Забор с площадки', fmt(p.pickup, 0) + NBSP + '₽'],
        p.line ? [haul, fmt(p.line, 0) + NBSP + '₽'] : null,
        p.delivery ? ['Доставка по городу', fmt(p.delivery, 0) + NBSP + '₽'] : null,
        p.insurance ? ['Страхование груза', fmt(p.insurance, 0) + NBSP + '₽'] : null,
        p.other ? ['Прочие сборы', fmt(p.other, 0) + NBSP + '₽'] : null,
        ['Груз', totalPlaces() + NBSP + plural(totalPlaces(), ['место', 'места', 'мест'])
          + ' · ' + fmtKg(json.weight) + NBSP + 'кг · ' + fmt(json.volume, 2) + NBSP + 'м³']
      ]);

      actions(resBox, [{
        label: 'Отправить заявку',
        onClick: function () { if (window.openRequestModal) window.openRequestModal('delivery'); }
      }]);
    }

    drawRows();
    drawExtra();
  }

  /* ── INIT ── */

  var MODULES = { sdt: initSdt, flange: initFlange, metizy: initMetizy, pipes: initPipes, dn: initDn, steels: initSteels, dostavka: initDostavka };
  document.querySelectorAll('[data-calc]').forEach(function (root) {
    var mod = MODULES[root.dataset.calc];
    if (mod) mod(root);
  });
})();
