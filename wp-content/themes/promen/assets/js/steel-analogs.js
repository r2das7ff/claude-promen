/* ── ПЭ / ПОДБОРЩИК АНАЛОГОВ МАРОК СТАЛИ ─────────────────────────────────
   /kalkulyatory/analogi-staley/. Данные — JSON-блок #saData из шаблона
   (inc/steel-analogs.php). Первый паспорт рисует сервер
   (inc/steel-analogs-render.php); render() здесь повторяет ту же разметку —
   меняете одну сторону, меняйте и другую.

   Поиск прощает запись: латиница и кириллица вперемешку (12X18H10T,
   12h18n10t), приставки AISI/SA-/UNS, «Gr.», пробелы и дефисы, даже набор
   в неверной раскладке (09u2c → 09г2с). */
(function () {
  'use strict';

  var root = document.querySelector('[data-sa]');
  var dataEl = document.getElementById('saData');
  if (!root || !dataEl) return;
  var D;
  try { D = JSON.parse(dataEl.textContent); } catch (e) { return; }

  var GRADES = D.grades || [];
  var SYS = D.systems || {};
  var SYS_KEYS = Object.keys(SYS);
  var CHEM = D.chem || {};
  var QL = D.q || {};
  var GROUPS = D.groups || {};
  var byId = {};
  GRADES.forEach(function (g, i) { g._i = i; byId[g.id] = g; });

  var NBSP = ' ';
  var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ── УТИЛИТЫ ── */

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function int(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, NBSP); }
  /* 1 позиция, 2 позиции, 5 позиций — как promen_sa_plural(). */
  function plural(n, one, few, many) {
    var m10 = n % 10, m100 = n % 100;
    if (m10 === 1 && m100 !== 11) return one;
    return m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14) ? few : many;
  }

  /* 0,17 · 0,035 · 1,06 · 16,8 · 17 — как promen_sa_num(). */
  function num(x) {
    var s;
    if (x >= 1) return x.toFixed(2).replace(/\.?0+$/, '').replace('.', ',');
    s = x.toFixed(3).replace('.', ',');
    return /0$/.test(s) ? s.slice(0, -1) : s;
  }
  function range(r) {
    if (!r) return '—';
    if (r[2]) return r[2];
    if (r[0] != null && r[1] != null) return num(r[0]) + '–' + num(r[1]);
    if (r[1] != null) return '≤ ' + num(r[1]);
    return '≥ ' + num(r[0]);
  }

  var ICONS = {
    copy: '<rect x="8" y="8" width="12" height="12" rx="1"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    link: '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/>'
  };
  function icon(n) {
    return '<svg class="sa-ic" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + ICONS[n] + '</svg>';
  }
  var Q_HINT = {
    3: 'Та же система легирования, состав и свойства одного уровня',
    2: 'Тот же класс и назначение, отличаются отдельные элементы или прочность',
    1: 'Прямого аналога нет, ближайший по назначению — только по расчёту'
  };
  function qHtml(q, word) {
    return '<span class="sa-q" data-q="' + q + '" title="' + esc(Q_HINT[q] || '') + '">' +
      '<span class="sa-q-t" aria-hidden="true"><i></i><i></i><i></i></span>' +
      (word ? '<span class="sa-q-w">' + esc(QL[q] || '') + '</span>' : '<span class="sr-only">' + esc(QL[q] || '') + '</span>') +
      '</span>';
  }

  var toastEl = root.querySelector('[data-toast]');
  var toastT = null;
  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.add('is-on');
    clearTimeout(toastT);
    toastT = setTimeout(function () { toastEl.classList.remove('is-on'); }, 1800);
  }
  function copy(text, done) {
    function ok() { toast('Скопировано: ' + text); if (done) done(); }
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(ok, fallback);
    } else {
      fallback();
    }
    function fallback() {
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.setAttribute('readonly', '');
      ta.style.cssText = 'position:fixed;top:-100px;opacity:0;';
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); ok(); } catch (e) { toast('Не удалось скопировать'); }
      document.body.removeChild(ta);
    }
  }
  function goal(name, params) {
    var id = window.PROMEN_YM_ID;
    if (id && typeof window.ym === 'function') {
      try { window.ym(id, 'reachGoal', name, params || {}); } catch (e) { /* счётчик недоступен — не мешаем */ }
    }
  }

  /* ── НОРМАЛИЗАЦИЯ ЗАПИСИ ── */

  /* Кириллица → латинские двойники по начертанию (12Х18Н10Т → 12X18H10T). */
  var LOOK = { 'А': 'A', 'В': 'B', 'Е': 'E', 'К': 'K', 'М': 'M', 'Н': 'H', 'О': 'O', 'Р': 'P', 'С': 'C', 'Т': 'T', 'Х': 'X', 'У': 'Y' };
  /* Кириллица → транслит (12Х18Н10Т → 12H18N10T, 09Г2С → 09G2S). */
  var TR = {
    'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ж': 'ZH', 'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M',
    'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C', 'Ч': 'CH', 'Ш': 'SH', 'Щ': 'SCH',
    'Ы': 'Y', 'Э': 'E', 'Ю': 'YU', 'Я': 'YA', 'Ь': '', 'Ъ': ''
  };
  /* Раскладка ЙЦУКЕН ↔ QWERTY: «09u2c» набрано вместо «09г2с». */
  var KB_RU = 'йцукенгшщзхъфывапролджэячсмитьбю';
  var KB_EN = "qwertyuiop[]asdfghjkl;'zxcvbnm,.";
  function swapLayout(s) {
    var out = '';
    for (var i = 0; i < s.length; i++) {
      var c = s.charAt(i), lc = c.toLowerCase(), k = KB_RU.indexOf(lc), j = KB_EN.indexOf(lc);
      out += k >= 0 ? KB_EN.charAt(k) : (j >= 0 && /[a-z\[\];',.]/.test(lc) ? KB_RU.charAt(j) : c);
    }
    return out;
  }
  function prep(s) {
    s = String(s || '').toUpperCase().replace(/Ё/g, 'Е').trim();
    for (var i = 0; i < 2; i++) {
      s = s.replace(/^(?:СТАЛЬ|МАРКА|AISI|SAE|UNS|ASTM|ASME|DIN|EN|JIS|GB|ГОСТ|GOST|W\.?\s*-?\s*NR\.?|WERKSTOFF)(?=[\s.:\-]|\d|$)[\s.:\-]*/, '');
    }
    s = s.replace(/[\s.\-_\/\\,;:()«»"'#№+]+/g, '');
    s = s.replace(/^SA(?=\d)/, 'A');
    s = s.replace(/(\d)GRADE(?=[A-Z0-9])/g, '$1').replace(/(\d)GR(?=[A-Z0-9])/g, '$1');
    s = s.replace(/CL\d$/, '');
    return s;
  }
  function look(s) { return s.replace(/[А-Я]/g, function (c) { return LOOK[c] || c; }); }
  function tr(s) { return s.replace(/[А-Я]/g, function (c) { return TR[c] != null ? TR[c] : c; }); }
  function keysOf(s) { var p = prep(s); return { l: look(p), t: tr(p) }; }

  /* ── ИНДЕКС ── */

  var INDEX = [];
  var seen = {};
  function add(g, sys, label, q, ex) {
    if (!label) return;
    var k = keysOf(label);
    if (!k.l) return;
    var key = g.id + '|' + k.l;
    if (seen[key]) return;
    seen[key] = 1;
    INDEX.push({ g: g, sys: sys, label: label, q: q, l: k.l, t: k.t, ex: ex || '' });
  }
  GRADES.forEach(function (g) {
    add(g, 'RU', g.name, 4);
    (g.aka || []).forEach(function (a) { add(g, 'RU', a, 4); });
    SYS_KEYS.forEach(function (s) {
      var a = g.an && g.an[s];
      if (!a) return;
      add(g, s, a.g, a.q);
      String(a.g).split(/\s*\/\s*/).forEach(function (p) { add(g, s, p, a.q); });
      if (a.n) add(g, s, a.n.replace(/^UNS\s+/, ''), a.q);
      (a.forms || []).forEach(function (f) {
        String(f[1]).split(/\s*,\s*/).forEach(function (p) { add(g, s, p, a.q, f[0]); });
      });
      (a.aka || []).forEach(function (x) { add(g, s, x, a.q); });
    });
  });

  function lev(a, b) {
    if (Math.abs(a.length - b.length) > 2) return 9;
    var prev = [], cur, i, j;
    for (j = 0; j <= b.length; j++) prev[j] = j;
    for (i = 1; i <= a.length; i++) {
      cur = [i];
      for (j = 1; j <= b.length; j++) {
        cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1));
      }
      prev = cur;
    }
    return prev[b.length];
  }

  /* Лучшее совпадение на марку: точное > начало > вхождение > опечатка. */
  function search(q) {
    var raw = String(q || '').trim();
    if (!raw) return [];
    var vars = [keysOf(raw)];
    var sw = swapLayout(raw);
    if (sw !== raw) vars.push(keysOf(sw));
    var best = {};
    function put(e, sc, exact) {
      sc += e.q;
      var cur = best[e.g.id];
      if (!cur || sc > cur.sc) best[e.g.id] = { g: e.g, e: e, sc: sc, exact: exact };
    }
    vars.forEach(function (v, vi) {
      if (!v.l) return;
      var pen = vi ? 15 : 0;
      INDEX.forEach(function (e) {
        if (e.l === v.l || e.t === v.t) return put(e, 100 - pen, !vi);
        if (e.l.indexOf(v.l) === 0 || e.t.indexOf(v.t) === 0) return put(e, 60 + 20 * v.l.length / e.l.length - pen, false);
        if (v.l.length >= 2 && (e.l.indexOf(v.l) > 0 || e.t.indexOf(v.t) > 0)) return put(e, 30 + 10 * v.l.length / e.l.length - pen, false);
      });
    });
    var out = Object.keys(best).map(function (k) { return best[k]; });
    if (!out.length && vars[0].l.length >= 3) {
      var v = vars[0], lim = v.l.length >= 7 ? 2 : 1;
      INDEX.forEach(function (e) {
        var d = Math.min(lev(v.l, e.l), lev(v.t, e.t));
        if (d <= lim) put(e, 10 - d * 3, false);
      });
      out = Object.keys(best).map(function (k) { best[k].fuzzy = true; return best[k]; });
    }
    out.sort(function (a, b) { return b.sc - a.sc || a.g._i - b.g._i; });
    return out.slice(0, 8);
  }

  /* ── ПАСПОРТ (зеркало promen_sa_passport) ── */

  var ELS = ['C', 'Si', 'Mn', 'Cr', 'Ni', 'Mo', 'V', 'Ti', 'W', 'Nb', 'B', 'Cu', 'S', 'P'];
  var STEPS = [0.01, 0.02, 0.05, 0.1, 0.2, 0.25, 0.5, 1, 2, 2.5, 5, 10, 20, 25, 50];
  function nice(v) { for (var i = 0; i < STEPS.length; i++) if (v <= STEPS[i]) return STEPS[i]; return 100; }
  function isAlloy(r) { return r && ((r[0] != null && r[0] > 0) || r[2]); }

  function chemRows(ru, an) {
    var rows = [], rest = [];
    ELS.forEach(function (el) {
      var a = ru[el] || null, b = an ? (an[el] || null) : null;
      if (!a && !b) return;
      if (el === 'S' || el === 'P' || !(el === 'C' || el === 'Si' || el === 'Mn' || isAlloy(a) || isAlloy(b))) {
        rest.push({ el: el, a: a, b: b });
        return;
      }
      var hi = 0;
      [a, b].forEach(function (r) { if (r) hi = Math.max(hi, r[1] != null ? r[1] : r[0] * 1.6); });
      var scale = nice(hi * 1.12);
      function bar(r) {
        if (!r) return null;
        var lo = r[0] == null ? 0 : r[0], up = r[1] == null ? scale : r[1];
        return { l: Math.round(lo / scale * 10000) / 100, w: Math.max(1.2, Math.round((up - lo) / scale * 10000) / 100), ol: r[0] == null, or: r[1] == null };
      }
      var diff = false;
      if (a && b) {
        var la = a[0] || 0, ha = a[1] == null ? Infinity : a[1], lb = b[0] || 0, hb = b[1] == null ? Infinity : b[1];
        diff = ha < lb || hb < la;
      }
      rows.push({ el: el, a: a, b: b, ba: bar(a), bb: bar(b), diff: diff });
    });
    return { rows: rows, rest: rest };
  }

  function defaultCmp(g) {
    for (var i = 0; i < SYS_KEYS.length; i++) {
      var a = g.an && g.an[SYS_KEYS[i]];
      if (a && a.chem && CHEM[a.chem]) return a.chem;
    }
    return '';
  }

  function chemHtml(g, cmp) {
    if (!g.chem || !Object.keys(g.chem).length) {
      return '<p class="sa-chem-empty">Химсостав марки задают технические условия поставщика — в подборщике его нет. Требования к составу для вашей задачи подтвердит инженер завода.</p>';
    }
    var an = CHEM[cmp] || null;
    var data = chemRows(g.chem, an ? an.c : null);
    var chips = '', seenK = {};
    SYS_KEYS.forEach(function (s) {
      var k = g.an && g.an[s] && g.an[s].chem;
      if (!k || seenK[k] || !CHEM[k]) return;
      seenK[k] = 1;
      chips += '<button type="button" class="sa-chip" data-cmp="' + esc(k) + '" aria-pressed="' + (k === cmp) + '">' +
        (/^AISI/.test(CHEM[k].label) ? '' : '<span class="sa-chip-sys">' + esc(s) + '</span>') + esc(CHEM[k].label) + '</button>';
    });
    var h = '<div class="sa-chem-hd">';
    h += '<div class="sa-chem-key"><span class="sa-sw sa-sw--ru" aria-hidden="true"></span><b>' + esc(g.name) + '</b><span>' + esc(g.std) + '</span></div>';
    if (an) h += '<div class="sa-chem-key"><span class="sa-sw sa-sw--an" aria-hidden="true"></span><b>' + esc(an.label) + '</b><span>' + esc(an.doc) + '</span></div>';
    if (chips) h += '<div class="sa-chips" role="group" aria-label="Сравнить с аналогом">' + chips + '</div>';
    h += '</div>';
    h += '<div class="sa-els" role="table" aria-label="Химический состав, %">';
    h += '<div class="sa-el sa-el--hd" role="row"><span role="columnheader"><span class="sr-only">Элемент</span></span><span role="columnheader" class="sa-el-scale">Диапазон, % массы</span><span role="columnheader" class="sa-el-v"><b>' + esc(g.name) + '</b>' + (an ? '<span>' + esc(an.label) + '</span>' : '') + '</span></div>';
    data.rows.forEach(function (r, i) {
      h += '<div class="sa-el' + (r.diff ? ' is-diff' : '') + '" role="row" style="--i:' + i + '"><span class="sa-el-k" role="rowheader">' + r.el + '</span><span class="sa-el-bars" role="cell">';
      [['ba', 'ru'], ['bb', 'an']].forEach(function (p) {
        var b = r[p[0]];
        if (b) {
          h += '<span class="sa-bar sa-bar--' + p[1] + (b.ol ? ' is-ol' : '') + (b.or ? ' is-or' : '') + '" style="left:' + b.l + '%;width:' + b.w + '%"></span>';
        } else if (p[0] === 'bb' && an) {
          h += '<span class="sa-bar sa-bar--nil"></span>';
        }
      });
      h += '</span><span class="sa-el-v" role="cell"><b>' + esc(range(r.a)) + '</b>' + (an ? '<span>' + esc(range(r.b)) + '</span>' : '') + '</span></div>';
    });
    h += '</div>';
    if (data.rest.length) {
      h += '<p class="sa-rest"><span class="sa-lbl">Остаточные и примеси</span>' + data.rest.map(function (r) {
        return '<span><b>' + r.el + '</b> ' + esc(range(r.a)) + (an ? ' <i>|</i> ' + esc(range(r.b)) : '') + '</span>';
      }).join('') + '</p>';
    }
    if (data.rows.some(function (r) { return r.diff; })) {
      h += '<p class="sa-chem-diff"><span class="sa-diff-mark" aria-hidden="true"></span>Диапазоны не пересекаются — по этому элементу марки различаются по составу.</p>';
    }
    return h;
  }

  function copyText(s, a) {
    var pre = { EN: 'EN', ASTM: '', AISI: 'AISI', DIN: 'DIN', JIS: 'JIS', GB: 'GB' }[s];
    return ((pre == null ? s : pre) + ' ' + a.g).trim() + (a.n ? ' (' + a.n + ')' : '');
  }

  function analogRow(s, a) {
    var m = SYS[s];
    if (!a) {
      return '<li class="sa-an-row is-nil" data-sys="' + s + '"><span class="sa-sys">' + esc(m.label) + '<small>' + esc(m.name) + '</small></span><div class="sa-an-main"><span class="sa-nil">Аналога нет</span></div></li>';
    }
    var h = '<li class="sa-an-row" data-sys="' + s + '"><span class="sa-sys">' + esc(m.label) + '<small>' + esc(m.name) + '</small></span><div class="sa-an-main">';
    h += '<div class="sa-an-g"><b>' + esc(a.g) + '</b>' + (a.n ? '<span class="sa-num">' + esc(a.n) + '</span>' : '') + '</div>';
    if (a.forms && a.forms.length) {
      h += '<ul class="sa-forms">' + a.forms.map(function (f) { return '<li><span>' + esc(f[0]) + '</span><b>' + esc(f[1]) + '</b></li>'; }).join('') + '</ul>';
    } else if (a.doc) {
      h += '<div class="sa-an-doc">' + esc(a.doc) + '</div>';
    }
    if (a.note) h += '<p class="sa-note">' + esc(a.note) + '</p>';
    if (a.alt) h += '<p class="sa-alt"><span>Также</span> ' + esc(a.alt) + '</p>';
    var ct = copyText(s, a);
    h += '</div>' + qHtml(a.q, true) + '<button type="button" class="sa-copy" data-copy="' + esc(ct) + '" aria-label="' + esc('Скопировать ' + ct) + '">' + icon('copy') + '</button></li>';
    return h;
  }

  function catalogHtml(g) {
    var L = g.links || [];
    if (!L.length) {
      return '<div class="sa-cat is-off"><span class="sa-lbl">В каталоге завода</span><p>Серийных позиций из ' + esc(g.name) +
        ' в каталоге нет. Детали по чертежу из этой марки — по запросу: инженер ответит, возьмёт ли завод заказ и в какой срок.</p></div>';
    }
    var m = L[0];
    var h = '<div class="sa-cat"><div class="sa-cat-hd"><span class="sa-lbl">В каталоге завода</span><a class="sa-cat-all" href="' + esc(m.url) + '">' + int(m.n) + ' ' + plural(m.n, 'позиция', 'позиции', 'позиций') + ' ' + icon('arrow') + '</a></div>';
    h += '<ul class="sa-cat-list">' + m.by.slice(0, 8).map(function (b) {
      return '<li><a href="' + esc(b.url) + '">' + esc(b.label) + '<b>' + int(b.n) + '</b></a></li>';
    }).join('') + '</ul>';
    L.slice(1).forEach(function (l) {
      h += '<a class="sa-cat-also" href="' + esc(l.url) + '">Отдельно в каталоге: ' + esc(l.label) + ' — ' + int(l.n) + '</a>';
    });
    return h + '</div>';
  }

  function passportHtml(g, cmp) {
    var h = '<div class="sa-ctx" data-ctx hidden></div><div class="sa-side"><div class="sa-id">';
    h += '<div class="sa-id-top"><span class="sa-lbl">Марка по ГОСТ</span><span class="sa-grp">' + esc(GROUPS[g.g] || '') + '</span></div>';
    h += '<h2 class="sa-name">' + esc(g.name) + '</h2>';
    if (g.desc) h += '<p class="sa-desc">' + esc(g.desc) + '</p>';
    h += '<dl class="sa-facts">';
    if (g.std) h += '<div><dt>Норматив</dt><dd>' + esc(g.std) + '</dd></div>';
    if (g.temp) h += '<div><dt>Температура среды</dt><dd>' + esc(g.temp) + '</dd></div>';
    if (g.mech) h += '<div><dt class="sa-nc">σт · σв, МПа · δ, %</dt><dd>' + esc(g.mech.st + ' · ' + g.mech.sv + ' · ' + g.mech.delta) + '</dd></div>';
    if (g.apps && g.apps.length) h += '<div><dt>Отрасли</dt><dd>' + esc(g.apps.join(' · ')) + '</dd></div>';
    h += '</dl></div><div class="sa-buy">' + catalogHtml(g);
    h += '<div class="sa-acts"><button type="button" class="clc-btn" data-act="kp">Запросить КП ' + icon('arrow') + '</button>' +
      '<button type="button" class="clc-btn clc-btn--ghost" data-act="link">' + icon('link') + ' Ссылка на марку</button></div></div></div>';
    h += '<div class="sa-an"><div class="sa-an-hd"><span class="sa-lbl">Аналоги по системам</span><a class="sa-legend" href="#stepen">' +
      qHtml(3, false) + 'прямой' + qHtml(2, false) + 'близкий' + qHtml(1, false) + 'условный</a></div>';
    if (g.none) {
      h += '<p class="sa-none">' + esc(g.none) + '</p>';
    } else {
      h += '<ol class="sa-an-list">' + SYS_KEYS.map(function (s) { return analogRow(s, g.an && g.an[s]); }).join('') + '</ol>';
    }
    h += '</div><section class="sa-chem" data-chem aria-label="Химический состав">' + chemHtml(g, cmp) + '</section>';
    return h;
  }

  /* Обозначение с системой, как его пишут в документации: AISI 321, UNS S32100, EN 1.4541. */
  function sysLabel(e) {
    var l = e.label;
    if (/^(API|UNS)\s/.test(l)) return l;
    if (e.sys === 'AISI' && /^[SG]\d{5}$/.test(l)) return 'UNS ' + l;
    return e.sys + ' ' + l;
  }

  /* ── СОСТОЯНИЕ И ОТРИСОВКА ── */

  var pass = root.querySelector('[data-pass]');
  var input = root.querySelector('#saQ');
  var list = root.querySelector('#saList');
  var clearBtn = root.querySelector('[data-clear]');
  var statusEl = root.querySelector('[data-status]');
  var NARROW = window.matchMedia ? window.matchMedia('(max-width: 1100px)') : { matches: false };
  var state = { g: byId[pass.getAttribute('data-id')] || GRADES[0], cmp: '', ctx: null };
  state.cmp = defaultCmp(state.g);

  function ctxHtml(ctx, g) {
    if (!ctx) return '';
    var h = '';
    var e = ctx.e;
    if (e && e.sys !== 'RU') {
      h += '<span><span class="sa-lbl">Запрос</span> <b>«' + esc(ctx.query) + '»</b> — ' + esc(sysLabel(e)) +
        (e.ex ? ' (' + esc(e.ex.toLowerCase()) + ')' : '') + ' → по ГОСТ <b>' + esc(g.name) + '</b>, ' + esc(QL[e.q] || '') + ' аналог' +
        (ctx.fuzzy ? ' — ближайшее совпадение по написанию' : '') + '</span>';
    } else if (e && ctx.query && prep(ctx.query) !== prep(g.name)) {
      h += '<span><span class="sa-lbl">Запрос</span> <b>«' + esc(ctx.query) + '»</b> → <b>' + esc(g.name) + '</b>' + (ctx.fuzzy ? ' — ближайшее совпадение по написанию' : '') + '</span>';
    }
    if (ctx.also && ctx.also.length > 1) {
      h += '<span class="sa-ctx-also"><span class="sa-lbl">Соответствует маркам</span>' + ctx.also.map(function (r) {
        return '<button type="button" data-go="' + esc(r.g.id) + '"' + (r.g.id === g.id ? ' aria-current="true"' : '') + '>' + esc(r.g.name) + qHtml(r.e.q < 4 ? r.e.q : 3, false) + '</button>';
      }).join('') + '</span>';
    }
    return h;
  }

  function render(g, ctx, keepCmp) {
    state.g = g;
    state.ctx = ctx || null;
    var e = ctx && ctx.e;
    if (!keepCmp) {
      var hitChem = e && e.sys !== 'RU' && g.an[e.sys] && g.an[e.sys].chem;
      state.cmp = hitChem && CHEM[hitChem] ? hitChem : defaultCmp(g);
    }
    pass.setAttribute('data-id', g.id);
    pass.innerHTML = passportHtml(g, state.cmp);
    var ctxEl = pass.querySelector('[data-ctx]');
    var ch = ctxHtml(state.ctx, g);
    if (ch) { ctxEl.innerHTML = ch; ctxEl.hidden = false; }
    if (e && e.sys !== 'RU') {
      var row = pass.querySelector('.sa-an-row[data-sys="' + e.sys + '"]');
      if (row) {
        row.classList.add('is-hit');
        var sc = row.querySelector('.sa-sys');
        if (sc) sc.insertAdjacentHTML('beforeend', '<em class="sa-hit-l">по запросу</em>');
      }
    }
    if (statusEl) {
      statusEl.textContent = 'Паспорт марки ' + g.name + (e && e.sys !== 'RU' ? ': ' + (QL[e.q] || '') + ' аналог ' + sysLabel(e) : '');
    }
    /* Подтверждение смены марки; при reduced-motion CSS оставляет только проявление. */
    pass.classList.remove('is-swap');
    void pass.offsetWidth;
    pass.classList.add('is-swap');
    animChem(pass.querySelector('[data-chem]'));
    markTable(g.id);
    markPop();
    setUrl(g.id);
  }

  function animChem(box) {
    if (!box) return;
    box.classList.remove('is-anim');
    void box.offsetWidth;
    box.classList.add('is-anim');
  }

  function setUrl(id) {
    try {
      var u = new URL(window.location.href);
      u.searchParams.set('marka', id);
      window.history.replaceState(null, '', u.pathname + u.search + u.hash);
    } catch (e) { /* старый браузер — без адреса */ }
  }

  /* ── ПОДСКАЗКИ ── */

  var results = [];
  var active = -1;

  function openList(open) {
    list.hidden = !open;
    input.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (!open) { active = -1; input.removeAttribute('aria-activedescendant'); }
  }

  function sugHtml(r, i) {
    var e = r.e, g = r.g, m;
    if (e.sys === 'RU') {
      m = esc(GROUPS[g.g] || '') + (g.std ? '<small>' + esc(g.std) + '</small>' : '');
    } else {
      m = '<mark>' + esc(sysLabel(e)) + '</mark><small>' + esc(e.ex || SYS[e.sys].name) + '</small>';
    }
    return '<li id="saOpt' + i + '" class="sa-sug-o" role="option" aria-selected="false" data-i="' + i + '">' +
      '<span class="sa-sug-g">' + esc(g.name) + '</span><span class="sa-sug-m">' + m + '</span>' +
      (e.sys === 'RU' ? '<span class="sa-lbl">ГОСТ</span>' : qHtml(e.q, true)) + '</li>';
  }

  function showList() {
    var q = input.value.trim();
    clearBtn.hidden = !q;
    if (!q) { results = []; openList(false); return; }
    results = search(q);
    if (!results.length) {
      list.innerHTML = '<li class="sa-sug-empty" role="presentation"><p>Марки <b>«' + esc(q) + '»</b> в базе подборщика нет. Инженер завода подберёт аналог по вашей марке и ответит в течение рабочего дня — нажмите Enter или кнопку.</p>' +
        '<button type="button" class="clc-btn" data-act="miss">Спросить инженера ' + icon('arrow') + '</button></li>';
      openList(true);
      return;
    }
    var fuzzy = results[0].fuzzy;
    list.innerHTML = (fuzzy ? '<li class="sa-sug-empty" role="presentation"><p>Точного совпадения нет. Возможно, вы имели в виду — выберите марку:</p></li>' : '') +
      results.map(sugHtml).join('');
    active = -1;
    openList(true);
  }

  function setActive(i) {
    var opts = list.querySelectorAll('.sa-sug-o');
    if (!opts.length) return;
    active = (i + opts.length) % opts.length;
    opts.forEach(function (o, k) { o.setAttribute('aria-selected', k === active ? 'true' : 'false'); });
    input.setAttribute('aria-activedescendant', opts[active].id);
    opts[active].scrollIntoView({ block: 'nearest' });
  }

  function choose(r, query) {
    if (!r) return;
    var also = results.filter(function (x) { return x.exact; });
    if (also.length < 2) also = [];
    render(r.g, { e: r.e, query: query, also: also, fuzzy: r.fuzzy });
    openList(false);
    if (NARROW.matches) {
      var top = pass.getBoundingClientRect().top;
      if (top < 0 || top > window.innerHeight * 0.4) {
        input.blur(); /* убрать экранную клавиатуру */
        pass.scrollIntoView({ block: 'start', behavior: REDUCED ? 'auto' : 'smooth' });
      }
    }
    goal('steel_analog_pick', { grade: r.g.name, query: query, sys: r.e.sys });
  }

  input.addEventListener('input', showList);
  input.addEventListener('focus', function () { if (input.value.trim()) showList(); });
  input.addEventListener('keydown', function (ev) {
    var open = !list.hidden;
    if (ev.key === 'ArrowDown') { ev.preventDefault(); if (!open) showList(); setActive(active + 1); }
    else if (ev.key === 'ArrowUp') { ev.preventDefault(); if (open) setActive(active - 1); }
    else if (ev.key === 'Enter') {
      ev.preventDefault();
      var q = input.value.trim();
      if (!q) return;
      if (list.hidden) showList();
      if (active >= 0 && results[active]) choose(results[active], q);
      /* Похоже по написанию — не выбираем за человека: список остаётся открытым. */
      else if (results.length && !results[0].fuzzy) choose(results[0], q);
      else if (!results.length) ask(q);
    } else if (ev.key === 'Escape') {
      if (open) openList(false); else { input.value = ''; clearBtn.hidden = true; }
    }
  });
  list.addEventListener('mousedown', function (ev) { ev.preventDefault(); });
  list.addEventListener('click', function (ev) {
    var o = ev.target.closest('.sa-sug-o');
    if (o) { choose(results[+o.getAttribute('data-i')], input.value.trim()); return; }
    if (ev.target.closest('[data-act="miss"]')) ask(input.value.trim());
  });
  input.addEventListener('blur', function () { setTimeout(function () { openList(false); }, 120); });
  clearBtn.addEventListener('click', function () {
    input.value = '';
    clearBtn.hidden = true;
    openList(false);
    input.focus();
  });

  /* Частые марки: сразу паспорт, без списка. */
  var popBtns = root.querySelectorAll('[data-q]');
  function markPop() {
    popBtns.forEach(function (b) {
      var r = search(b.getAttribute('data-q'))[0];
      b.setAttribute('aria-current', r && r.g.id === state.g.id && state.ctx && state.ctx.query === b.getAttribute('data-q') ? 'true' : 'false');
    });
  }
  popBtns.forEach(function (b) {
    b.addEventListener('click', function () {
      var q = b.getAttribute('data-q');
      input.value = q;
      clearBtn.hidden = false;
      results = search(q);
      if (results.length) choose(results[0], q);
    });
  });

  function ask(q) {
    goal('steel_analog_miss', { query: q });
    if (window.openRequestModal) {
      window.openRequestModal('solution', { task: 'Нужен российский аналог марки стали «' + q + '». Изделие и условия работы: ' });
    }
  }

  /* ── ДЕЙСТВИЯ В ПАСПОРТЕ ── */

  pass.addEventListener('click', function (ev) {
    var t = ev.target;
    var cmp = t.closest('[data-cmp]');
    if (cmp) {
      state.cmp = cmp.getAttribute('data-cmp');
      var box = pass.querySelector('[data-chem]');
      box.innerHTML = chemHtml(state.g, state.cmp);
      animChem(box);
      return;
    }
    var cp = t.closest('[data-copy]');
    if (cp) {
      copy(cp.getAttribute('data-copy'), function () {
        cp.classList.add('is-done');
        setTimeout(function () { cp.classList.remove('is-done'); }, 1200);
      });
      return;
    }
    var go = t.closest('[data-go]');
    if (go && byId[go.getAttribute('data-go')]) {
      var ctx = state.ctx;
      var g = byId[go.getAttribute('data-go')];
      var hit = ctx && ctx.also ? ctx.also.filter(function (r) { return r.g.id === g.id; })[0] : null;
      render(g, hit ? { e: hit.e, query: ctx.query, also: ctx.also } : null);
      return;
    }
    var act = t.closest('[data-act]');
    if (!act) return;
    var a = act.getAttribute('data-act');
    if (a === 'kp' && window.openRequestModal) {
      var e = state.ctx && state.ctx.e;
      var mat = state.g.name + (e && e.sys !== 'RU' ? ' (аналог ' + sysLabel(e) + ')' : '');
      window.openRequestModal('calc', { mat: mat });
    } else if (a === 'link') {
      var u = new URL(window.location.href);
      u.searchParams.set('marka', state.g.id);
      u.hash = 'podbor';
      copy(u.toString());
    }
  });

  /* Отправка спецификации — из статьи и боковой колонки. */
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-act="spec"]');
    if (!b || !window.openRequestModal) return;
    window.openRequestModal('tz', { task: 'Перевести спецификацию с импортными марками стали на российские марки и нормативы (файл во вложении).' });
  });

  /* ── ТАБЛИЦА ── */

  var tbl = document.querySelector('.sa-tbl:not(.sa-tbl--forms):not(.sa-tbl--spec)');
  function markTable(id) {
    if (!tbl) return;
    tbl.querySelectorAll('tr[aria-current]').forEach(function (r) { r.removeAttribute('aria-current'); });
    var row = tbl.querySelector('tr[data-id="' + id + '"]');
    if (row) row.setAttribute('aria-current', 'true');
  }
  /* Прокручивается body, а не окно (html,body{height:100%} в base.css) —
     window.scrollTo здесь не работает; scroll-margin у [id] держит отступ под шапку. */
  function toTool() {
    root.scrollIntoView({ block: 'start', behavior: REDUCED ? 'auto' : 'smooth' });
  }
  if (tbl) {
    tbl.addEventListener('click', function (ev) {
      var tr = ev.target.closest('tr[data-id]');
      if (!tr || !byId[tr.getAttribute('data-id')]) return;
      ev.preventDefault();
      render(byId[tr.getAttribute('data-id')], null);
      input.value = '';
      clearBtn.hidden = true;
      toTool();
    });
  }
  var filter = document.querySelector('[data-filter]');
  if (filter && tbl) {
    filter.addEventListener('click', function (ev) {
      var b = ev.target.closest('[data-g]');
      if (!b) return;
      var g = b.getAttribute('data-g');
      filter.querySelectorAll('[data-g]').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
      tbl.querySelectorAll('tbody[data-g]').forEach(function (tb) { tb.hidden = !!g && tb.getAttribute('data-g') !== g; });
    });
  }

  /* ── FAQ ── */

  document.querySelectorAll('.sa-faq .fq-q').forEach(function (q) {
    q.addEventListener('click', function () {
      var open = q.parentElement.classList.toggle('open');
      q.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  /* ── СОДЕРЖАНИЕ: подсветка текущего раздела ── */

  var tocLinks = document.querySelectorAll('.sa-toc-list a');
  if ('IntersectionObserver' in window && tocLinks.length) {
    var byHash = {};
    tocLinks.forEach(function (a) { byHash[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        tocLinks.forEach(function (a) { a.classList.remove('is-on'); });
        var a = byHash[en.target.id];
        if (a) a.classList.add('is-on');
      });
    }, { rootMargin: '-30% 0px -60% 0px' });
    Object.keys(byHash).forEach(function (id) {
      var s = document.getElementById(id);
      if (s) io.observe(s);
    });
  }

  /* ── СТАРТ: марка из адреса ── */

  markTable(state.g.id);
  (function fromUrl() {
    var p;
    try { p = new URL(window.location.href).searchParams.get('marka'); } catch (e) { return; }
    if (!p || byId[p]) return;
    /* ?marka=AISI 321 — свободная запись: ищем как в поле. */
    input.value = p;
    clearBtn.hidden = false;
    results = search(p);
    if (results.length && !results[0].fuzzy) choose(results[0], p);
  })();
})();
