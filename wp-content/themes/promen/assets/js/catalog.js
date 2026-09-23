/**
 * Каталог: фильтры и пагинация через JSON API (/wp-json/promen/v1/catalog).
 */
(function () {
  function expandMoreChips(more) {
    var box = more.closest('.cbf-multi');
    if (!box) return;
    var hadFocus = document.activeElement === more;
    var first = box.querySelector('.c-chip--extra');
    box.classList.add('is-expanded');
    box.querySelectorAll('.c-chip--extra').forEach(function (c) {
      c.classList.remove('c-chip--extra');
    });
    more.remove();
    // Кнопка исчезает — с клавиатуры фокус переходим на первую раскрытую фишку.
    if (hadFocus && first) first.focus();
  }

  // capture: перехватываем до AJAX-обработчика ссылок (.c-chip)
  document.addEventListener('click', function (e) {
    var more = e.target.closest('.c-chip--more');
    if (!more) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    expandMoreChips(more);
  }, true);

  var cfg = window.promenCatalog || {};
  var list = document.getElementById('productList');
  if (!list || !cfg.apiUrl) return;

  // Параметры фильтров приходят из PHP (promen_range_params /
  // promen_multi_taxonomies). Здесь они раньше были продублированы строками,
  // и добавленная на сервере стенка s в эти списки не попала: запрос уходил
  // без s_min/s_max, а кнопка «Сбросить» не появлялась. Литералы оставлены
  // только как страховка на случай старого кеша конфигурации.
  var RANGE_PARAMS = cfg.rangeParams || ['dn', 'pn', 's'];
  var MULTI_PARAMS = cfg.multiParams || ['steel', 'industry', 'angle', 'gost'];

  var count = document.getElementById('pCount');
  var pagination = document.querySelector('.cat-pagination');
  var pathSub = document.getElementById('pathSub');
  var mainTitle = document.getElementById('mainTitle');
  var pathCatLink = document.getElementById('pathCatLink');
  var catNav = document.getElementById('catNav');
  var searchForm = document.querySelector('.cb-search');
  var tblHd = document.getElementById('tblHd');

  function esc(s) {
    if (s == null) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function groupFromHref(href) {
    var u = new URL(href, location.origin);
    var g = u.searchParams.get('group');
    if (g) return g;
    var views = cfg.views || {};
    var path = u.pathname.replace(/\/$/, '');
    var slug;
    for (slug in views) {
      if (!slug || !views[slug] || !views[slug].termUrl) continue;
      try {
        var tp = new URL(views[slug].termUrl, location.origin).pathname.replace(/\/$/, '');
        if (tp === path) return slug;
      } catch (e) { /* ignore bad termUrl */ }
    }
    return '';
  }

  function parsePageUrl(url) {
    var u = new URL(url, location.origin);
    var p = { group: groupFromHref(u.toString()) || cfg.group || '', page: 1 };
    // ЧПУ-пагинация: WordPress отдаёт /catalog/page/2/, номер лежит в пути,
    // а не в query. Без этого клик по «2» менял URL, но список оставался
    // первой страницей — и хвостовые секции не скрывались, как на сервере.
    var pretty = u.pathname.match(/\/page\/(\d+)\/?$/);
    if (pretty) p.page = parseInt(pretty[1], 10) || 1;
    u.searchParams.forEach(function (v, k) {
      if (k === 'group') return; // группа из ЧПУ / ?group=
      if (k === 'paged') p.page = parseInt(v, 10) || 1;
      else if (k === 'page') p.page = parseInt(v, 10) || 1;
      else p[k] = v;
    });
    return { url: u, params: p };
  }

  function buildApiQuery(params) {
    var q = new URLSearchParams();
    if (params.group) q.set('group', params.group);
    if (params.q) q.set('q', params.q);
    if (params.page && params.page > 1) q.set('page', String(params.page));
    q.set('per_page', String(cfg.perPage || 30));
    RANGE_PARAMS.forEach(function (p) {
      if (params[p + '_min']) q.set(p + '_min', params[p + '_min']);
      if (params[p + '_max']) q.set(p + '_max', params[p + '_max']);
    });
    MULTI_PARAMS.forEach(function (p) {
      if (params[p]) q.set(p, params[p]);
    });
    if (params.sort) q.set('sort', params.sort);
    if (params.scope === 'all') q.set('scope', 'all');
    return q.toString();
  }

  // Держать в синхроне с promen_catalog_grid_template() (inc/catalog-render.php):
  // minmax(0,…) даёт трекам нулевой базовый размер, поэтому широкие схемы
  // ужимаются вместо вылета за колонку контента на 1024–1280px.
  function gridTpl(columns) {
    var widths = (columns || []).map(function (c) { return 'minmax(min-content,' + c.w + ')'; }).join(' ');
    return 'minmax(min-content,150px) minmax(120px,1fr) ' + widths
      + ' minmax(min-content,120px) minmax(min-content,96px) 32px';
  }

  var SORT_FIELDS = { dn: 'dn', mass: 'mass', massm: 'mass', pn: 'pn' };
  function currentSort() {
    var s = (new URL(location.href)).searchParams.get('sort') || '';
    var p = s.split(':');
    return { field: p[0] || '', dir: p[1] === 'desc' ? 'desc' : 'asc' };
  }

  var industryTagLabels = (cfg.industryTags) || { aes: 'АЭС', tes: 'ТЭС', gkh: 'ЖКХ', ngk: 'НГК' };

  function industryTagsHtml(slugs) {
    slugs = (slugs || []).slice(0, 3);
    if (!slugs.length) return '—';
    var tags = slugs.map(function (s) {
      var lbl = industryTagLabels[s] || s.toUpperCase();
      return '<span class="pr-tag' + (s === 'aes' ? ' hi' : '') + '">' + esc(lbl) + '</span>';
    }).join('');
    return '<span class="pr-tags">' + tags + '</span>';
  }

  /**
   * Подсветить слова запроса. Зеркало promen_highlight_html() на сервере:
   * текст сначала экранируем, потом размечаем — и слова экранируем так же,
   * иначе «&» из запроса не совпал бы с «&amp;» в тексте.
   */
  function hl(text, tokens) {
    var safe = esc(text);
    if (!tokens || !tokens.length) return safe;
    var parts = [];
    tokens.forEach(function (t) {
      t = String(t || '').trim();
      if (t.length < 2) return; // односимвольные подсвечивать бессмысленно
      parts.push(esc(t).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    });
    if (!parts.length) return safe;
    return safe.replace(new RegExp('(' + parts.join('|') + ')', 'gi'), '<mark class="hl">$1</mark>');
  }

  // Зеркало promen_steel_cell_html() из catalog-render.php — без него
  // подсказка живёт только до первой AJAX-перерисовки списка.
  function steelCellHtml(hit) {
    var txt = String(hit.steel_display || '').trim();
    if (!txt) return '—';
    var labels = (hit.steel_labels || []).filter(Boolean);
    var m = txt.match(/^(.*?)\s*(…\s*\+\d+)$/);
    if (!labels.length || !m) return esc(txt);
    return esc(m[1]) + ' <span class="pr-mat-more" data-steels="' +
      esc(labels.join(', ')) + '">' + esc(m[2]) + '</span>';
  }

  function renderRow(hit, columns, tpl, i, tokens) {
    var cells = (columns || []).map(function (col) {
      var val = (hit.cells && hit.cells[col.key]) ? hit.cells[col.key] : '—';
      return '<span class="pr-' + esc(col.key) + '">' + esc(val) + '</span>';
    }).join('');
  return '<a class="prod-row" href="' + esc(hit.url) + '" style="grid-template-columns:' + esc(tpl) + '"' +
      ' data-sku="' + esc(hit.sku) + '" data-title="' + esc(hit.title) + '"' +
      ' data-norm="' + esc(hit.norm) + '" data-steel="' + esc(hit.steel_display) + '"' +
      ' data-industry="' + esc(hit.industry_display) + '">' +
      '<span class="pr-norm"><span class="pr-norm-code">' + hl(hit.norm || '—', tokens) + '</span></span>' +
      '<span class="pr-name">' + hl(hit.title, tokens) + (hit.family ? '<small>' + esc(hit.family) + '</small>' : '') + '</span>' +
      cells +
      '<span class="pr-mat">' + steelCellHtml(hit) + '</span>' +
      '<span class="pr-ind">' + industryTagsHtml(hit.industries) + '</span>' +
      '<span class="pr-arr">›</span></a>';
  }

  function renderList(data, pageUrl) {
    var cols = data.columns || [];
    var tpl = gridTpl(cols);
    if (tblHd) {
      tblHd.style.gridTemplateColumns = tpl;
      var cs = currentSort();
      var hdr = '<span>Норматив</span><span>Наименование</span>';
      cols.forEach(function (c) {
        var sf = SORT_FIELDS[c.key];
        // Зеркало promen_catalog_header_cells(): c.html — подпись с индексом
        // («Dн»), c.hint — расшифровка колонки. Оба приходят из схемы каталога
        // на сервере, поэтому html здесь не из пользовательских данных.
        var lbl = c.html || esc(c.label);
        var hint = c.hint || '';
        if (!sf) {
          hdr += '<span' + (hint ? ' title="' + esc(hint) + '"' : '') + '>' + lbl + '</span>';
          return;
        }
        var active = sf === cs.field;
        var arr = active ? (cs.dir === 'desc' ? '↓' : '↑') : '⇅';
        var title = hint ? hint + ' · сортировка' : 'Сортировать';
        hdr += '<span class="th-sort' + (active ? ' is-active' : '') + '" role="button" tabindex="0" data-sort-field="' + esc(sf) + '" title="' + esc(title) + '">' +
          lbl + '<i class="th-arr">' + arr + '</i></span>';
      });
      hdr += '<span>Материал</span><span>Отрасль</span><span></span>';
      tblHd.innerHTML = hdr;
    }
    if (!data.hits || !data.hits.length) {
      var u = new URL(pageUrl || location.href);
      var qv = u.searchParams.get('q') || '';
      var grp = u.searchParams.get('group') || '';
      var allLink = '';
      if (qv && (grp || u.searchParams.get('scope') !== 'all')) {
        var au = new URL(u.pathname, location.origin);
        au.searchParams.set('q', qv);
        au.searchParams.set('scope', 'all');
        allLink = '<a class="ce-all" href="' + esc(au.toString()) + '">Искать «' + esc(qv) + '» во всём каталоге →</a>';
      }
      // Сброс фильтров сохраняет группу: иначе с ?group=troyniki уводил
      // в общий реестр всего каталога.
      var ru = new URL(u.pathname, location.origin);
      if (grp) ru.searchParams.set('group', grp);
      list.innerHTML = '<div class="cat-empty"><div class="ce-code">—</div>' +
        '<div class="ce-msg">Нет позиций по заданным параметрам</div>' + allLink +
        '<a class="ce-reset" href="' + esc(ru.toString()) + '">Сбросить фильтры</a></div>';
      return;
    }
    var tokens = data.tokens || [];
    list.innerHTML = data.hits.map(function (h, i) { return renderRow(h, cols, tpl, i, tokens); }).join('');
    /* Фильтр сработал молча: выдача подменялась без единого признака, что
       она пересчиталась именно сейчас. Класс снимаем сразу после кадра —
       так анимация перезапускается на каждом обновлении, а не только на
       первом. Строк тут бывает под сотню, поэтому отклик отдаём списком,
       а не каждой строкой отдельно. */
    list.classList.remove('is-refreshed');
    void list.offsetWidth;
    list.classList.add('is-refreshed');
  }

  function chipHref(param, slug, pageUrl) {
    var u = new URL(pageUrl);
    var cur = (u.searchParams.get(param) || '').split(',').filter(Boolean);
    var i = cur.indexOf(slug);
    if (i >= 0) cur.splice(i, 1); else cur.push(slug);
    if (cur.length) u.searchParams.set(param, cur.join(',')); else u.searchParams.delete(param);
    u.searchParams.delete('paged');
    u.searchParams.delete('page');
    return u.toString();
  }

  function activeFilterCount(u) {
    var n = 0;
    MULTI_PARAMS.forEach(function (p) {
      var v = u.searchParams.get(p);
      if (v) n += v.split(',').filter(Boolean).length;
    });
    RANGE_PARAMS.forEach(function (p) {
      if (u.searchParams.get(p + '_min') || u.searchParams.get(p + '_max')) n++;
    });
    return n;
  }

  // Отрасль — single-select табы (клик по активному = сброс на «Все отрасли»).
  function renderTabs(data, pageUrl) {
    var box = document.getElementById('cbTabs');
    if (!box) return;
    var u = new URL(pageUrl);
    var sel = (u.searchParams.get('industry') || '').split(',').filter(Boolean)[0] || '';
    function href(slug) {
      var t = new URL(pageUrl);
      t.searchParams.delete('paged');
      t.searchParams.delete('page');
      if (slug && slug !== sel) t.searchParams.set('industry', slug);
      else t.searchParams.delete('industry');
      return t.toString();
    }
    var opts = (data.facets && data.facets.industry) || [];
    var html = '<a class="cb-tab' + (sel === '' ? ' on' : '') + '" href="' + esc(href('')) + '" data-industry="">Все отрасли</a>';
    opts.forEach(function (o) {
      var on = sel === o.slug;
      html += '<a class="cb-tab' + (on ? ' on' : '') + '" href="' + esc(href(o.slug)) + '" data-industry="' + esc(o.slug) + '">' +
        esc(o.name) + '<span class="cb-tab-n">' + Number(o.count).toLocaleString('ru-RU') + '</span></a>';
    });
    box.innerHTML = html;
  }

  function renderReset(pageUrl) {
    var el = document.getElementById('cbReset');
    if (!el) return;
    var u = new URL(pageUrl);
    var n = activeFilterCount(u);
    var ru = new URL(location.pathname, location.origin);
    var g = u.searchParams.get('group');
    if (g) ru.searchParams.set('group', g);
    el.href = ru.toString();
    var badge = el.querySelector('.cb-reset-n');
    if (badge) badge.textContent = n;
    if (n) el.removeAttribute('hidden'); else el.setAttribute('hidden', '');

    var toggle = document.getElementById('cbToggle');
    if (toggle) {
      var tb = toggle.querySelector('.cb-toggle-n');
      if (n) {
        if (!tb) {
          tb = document.createElement('span');
          tb.className = 'cb-toggle-n';
          toggle.appendChild(tb);
        }
        tb.textContent = n;
      } else if (tb) {
        tb.remove();
      }
    }
  }

  function sliderVals(box) {
    try { return JSON.parse(box.dataset.values || '[]'); } catch (e) { return []; }
  }

  function updateSliderUI(box) {
    var vals = sliderVals(box);
    if (!vals.length) return;
    var minR = box.querySelector('.cbf-r[data-bound=min]');
    var maxR = box.querySelector('.cbf-r[data-bound=max]');
    var fill = box.querySelector('.cbf-fill');
    var a = +minR.value, b = +maxR.value, last = Math.max(vals.length - 1, 1);
    if (fill) {
      fill.style.left = (a / last * 100) + '%';
      fill.style.right = (100 - b / last * 100) + '%';
    }
    // Поля ручного ввода: не перетираем то, что пользователь сейчас печатает.
    box.querySelectorAll('.cbf-in').forEach(function (inp) {
      if (inp === document.activeElement) return;
      var i = inp.dataset.bound === 'min' ? a : b;
      if (vals[i]) inp.value = vals[i].name;
    });
  }

  // Применить текущие индексы бегунков к URL (крайние позиции = без ограничения).
  function applyRange(box) {
    var vals = sliderVals(box);
    if (!vals.length) return;
    var param = box.dataset.param;
    var a = +box.querySelector('.cbf-r[data-bound=min]').value;
    var b = +box.querySelector('.cbf-r[data-bound=max]').value;
    var url = new URL(location.href);
    var wasMin = url.searchParams.get(param + '_min') || '';
    var wasMax = url.searchParams.get(param + '_max') || '';
    if (a <= 0) url.searchParams.delete(param + '_min');
    else url.searchParams.set(param + '_min', String(vals[a].val));
    if (b >= vals.length - 1) url.searchParams.delete(param + '_max');
    else url.searchParams.set(param + '_max', String(vals[b].val));
    var nowMin = url.searchParams.get(param + '_min') || '';
    var nowMax = url.searchParams.get(param + '_max') || '';
    if (nowMin === wasMin && nowMax === wasMax) return; // ничего не изменилось
    url.searchParams.delete('paged');
    swap(url.toString(), true, { scroll: false });
  }

  // Ближайший индекс ряда к произвольному числу (для ручного ввода и клика по треку).
  function nearestIdx(vals, x) {
    var best = 0, bd = Infinity;
    for (var i = 0; i < vals.length; i++) {
      var d = Math.abs(vals[i].val - x);
      if (d < bd) { bd = d; best = i; }
    }
    return best;
  }

  function sliderHtml(param, opts, u) {
    var lbl = (cfg.rangeLbl && cfg.rangeLbl[param]) || param;
    var min = parseFloat(u.searchParams.get(param + '_min'));
    var max = parseFloat(u.searchParams.get(param + '_max'));
    var last = opts.length - 1, iMin = 0, iMax = last;
    var vals = opts.map(function (o) {
      return { val: parseFloat(o.val != null ? o.val : o.name), name: String(o.name) };
    });
    vals.forEach(function (o, i) {
      if (!isNaN(min) && o.val <= min) iMin = i;
      if (!isNaN(max) && o.val <= max) iMax = i;
    });
    if (iMax < iMin) iMax = iMin;
    return '<div class="cbf-slider" data-param="' + esc(param) + '" data-values="' + esc(JSON.stringify(vals)) + '">' +
      '<span class="cbf-lbl">' + esc(lbl) + '</span>' +
      '<div class="cbf-track"><div class="cbf-fill"></div>' +
      '<input type="range" class="cbf-r" data-bound="min" min="0" max="' + last + '" step="1" value="' + iMin + '" aria-label="' + esc(lbl) + ' от">' +
      '<input type="range" class="cbf-r" data-bound="max" min="0" max="' + last + '" step="1" value="' + iMax + '" aria-label="' + esc(lbl) + ' до">' +
      '</div><span class="cbf-io">' +
      '<input type="text" class="cbf-in" data-bound="min" inputmode="decimal" value="' + esc(vals[iMin].name) + '" aria-label="' + esc(lbl) + ' от, ручной ввод">' +
      '<span class="cbf-dash">–</span>' +
      '<input type="text" class="cbf-in" data-bound="max" inputmode="decimal" value="' + esc(vals[iMax].name) + '" aria-label="' + esc(lbl) + ' до, ручной ввод">' +
      '</span></div>';
  }

  var CHIP_ORDER = ['gost', 'steel', 'angle'];

  function renderFilters(data, pageUrl) {
    var filtersEl = document.getElementById('cbFilters');
    if (!filtersEl) return;
    var facets = data.facets || {};
    var facetParams = data.facet_params || Object.keys(facets);
    var rangeOptions = data.range_options || {};
    var labels = cfg.labels || {};
    var u = new URL(pageUrl);
    var html = '';

    var sliders = '';
    (data.ranges || []).forEach(function (param) {
      var opts = rangeOptions[param] || [];
      if (opts.length > 1) sliders += sliderHtml(param, opts, u);
    });
    if (sliders) html += '<div class="cbf-sliders">' + sliders + '</div>';

    CHIP_ORDER.forEach(function (param) {
      if (facetParams.indexOf(param) < 0) return;
      var opts = facets[param] || [];
      if (!opts.length) return;
      var sel = (u.searchParams.get(param) || '').split(',').filter(Boolean);
      var vis = 8;
      html += '<div class="cbf-multi" data-param="' + esc(param) + '">';
      html += '<span class="cbf-lbl">' + esc(labels[param] || param) + '</span>';
      html += '<div class="cbf-chips">';
      opts.forEach(function (o, i) {
        var on = sel.indexOf(o.slug) >= 0;
        var empty = o.count === 0;
        html += '<a class="c-chip' + (on ? ' on' : '') + (empty ? ' c-chip--zero' : '') + (i >= vis ? ' c-chip--extra' : '') + '" href="' + esc(chipHref(param, o.slug, pageUrl)) + '" data-slug="' + esc(o.slug) + '">' +
          esc(o.name) + '<span class="c-chip-n">' + o.count + '</span></a>';
      });
      if (opts.length > vis) {
        html += '<button type="button" class="c-chip c-chip--more">+ ещё ' + (opts.length - vis) + '</button>';
      }
      html += '</div></div>';
    });

    filtersEl.innerHTML = html;
    if (data.group) filtersEl.dataset.group = data.group;
    filtersEl.querySelectorAll('.cbf-slider').forEach(updateSliderUI);
    renderTabs(data, pageUrl);
    renderReset(pageUrl);
  }

  /**
   * Строка под поиском: что из запроса поняли и чего не учли.
   * Зеркало серверного рендера в woocommerce/parts/catalog-registry.php.
   */
  function renderNote(data) {
    var note = document.getElementById('cbNote');
    if (!note) return;
    var dropped = data.dropped || [];
    var hints = data.hints || {};
    var labels = hints.labels || [];
    var html = '';
    if (dropped.length) {
      html += '<span class="cb-note-drop">Не учтены: ' + esc(dropped.join(', ')) + ' — по ним ничего не нашлось</span>';
    }
    if (labels.length && hints.url) {
      html += '<a class="cb-note-hint" href="' + esc(hints.url) + '">Понято: ' + esc(labels.join(' · ')) +
        '<span class="cb-note-go" aria-hidden="true">применить фильтры →</span></a>';
    }
    note.innerHTML = html;
    if (html) note.removeAttribute('hidden');
    else note.setAttribute('hidden', '');
  }

  function renderPagination(data, pageUrl) {
    if (!pagination) return;
    var pages = data.pages || 0;
    if (pages <= 1) { pagination.innerHTML = ''; return; }
    var cur = data.page || 1;
    // Ссылки строим в том же виде, что и paginate_links на сервере:
    // /catalog/ для первой, /catalog/page/N/ дальше, query сохраняем.
    // Раньше номер дописывался как ?paged=N поверх уже существующего
    // /page/2/ в пути — получалось /catalog/page/2/?paged=3, где путь и
    // query противоречат друг другу.
    function pageHref(p) {
      var u = new URL(pageUrl);
      u.searchParams.delete('paged');
      u.searchParams.delete('page');
      var path = u.pathname.replace(/\/page\/\d+\/?$/, '/');
      if (path.charAt(path.length - 1) !== '/') path += '/';
      u.pathname = p > 1 ? path + 'page/' + p + '/' : path;
      return u.toString();
    }
    function link(p, label, cls) {
      return '<a class="' + cls + '" href="' + esc(pageHref(p)) + '">' + label + '</a> ';
    }
    // Разметка стрелок — как в promen_catalog_pagination_links(): слово в
    // отдельном span, чтобы CSS мог спрятать его на узких экранах.
    var PREV = '<span class="pg-arr">←</span><span class="pg-txt">Назад</span>';
    var NEXT = '<span class="pg-txt">Вперёд</span><span class="pg-arr">→</span>';
    var html = '';
    if (cur > 1) html += link(cur - 1, PREV, 'prev page-numbers');

    // Компактное окно: 1, последняя и ±2 вокруг текущей; разрывы — многоточие.
    // ±1 — как mid_size в promen_catalog_pagination_links(): при ±2 ряд
    // на глубоких страницах не помещался в телефон.
    var win = 1, set = {};
    set[1] = 1; set[pages] = 1;
    for (var i = cur - win; i <= cur + win; i++) { if (i >= 1 && i <= pages) set[i] = 1; }
    var list = Object.keys(set).map(Number).sort(function (a, b) { return a - b; });
    var prev = 0;
    for (var j = 0; j < list.length; j++) {
      var p = list[j];
      if (p - prev > 1) html += '<span class="page-numbers dots">…</span> ';
      if (p === cur) html += '<span class="page-numbers current">' + p + '</span> ';
      else html += link(p, String(p), 'page-numbers');
      prev = p;
    }
    if (cur < pages) html += link(cur + 1, NEXT, 'next page-numbers');
    pagination.innerHTML = html.trim();
  }

  function updateCount(total) {
    if (!count) return;
    var next = Number(total).toLocaleString('ru-RU') + ' позиций';
    // Не изменилось — не мигаем: pop только когда есть что сообщить.
    if (count.textContent === next) return;
    count.textContent = next;
    count.classList.remove('pop');
    // Двойной rAF вместо void offsetWidth: тот же рестарт класса, но без
    // принудительного синхронного layout сразу после трёх innerHTML.
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { count.classList.add('pop'); });
    });
  }

  var sidebarParentActive = {
    sbnSdt: ['', 'sdt'],
    sbnFlancy: ['flancy'],
    sbnKrepezh: ['krepezh'],
    sbnTruby: ['truby'],
    sbnIzolyatsiya: ['izolyatsiya'],
    sbnOpory: ['opory'],
    sbnArmatura: ['armatura']
  };

  var sidebarOpenGroups = {
    sbnFlancy: ['flancy', 'flancy-plosk', 'flancy-vorot', 'flancy-01', 'flancy-11'],
    sbnKrepezh: ['krepezh', 'bolty', 'gayki', 'shpilki', 'shayby', 'vinty'],
    sbnTruby: ['truby', 'truby-bsh', 'truby-es', 'truby-vgp'],
    sbnIzolyatsiya: ['izolyatsiya', 'izolyatsiya-truby', 'izolyatsiya-troyniki'],
    sbnOpory: ['opory', 'opory-nepodv', 'opory-skolz', 'opory-pruzh'],
    sbnArmatura: ['armatura', 'armatura-zadvizhki', 'armatura-klapany', 'armatura-krany']
  };

  function updateSidebar(group) {
    group = group || '';
    if (!catNav) return;

    catNav.querySelectorAll('.active').forEach(function (el) { el.classList.remove('active'); });

    catNav.querySelectorAll('a.sbn-filter').forEach(function (a) {
      if (groupFromHref(a.href) !== group) return;
      if (a.classList.contains('sbn-child')) {
        a.classList.add('active');
      } else if (a.classList.contains('sbn-parent-link')) {
        var item = a.closest('.sbn-item');
        if (item) item.classList.add('active');
      } else {
        a.classList.add('active');
      }
    });

    Object.keys(sidebarParentActive).forEach(function (id) {
      var box = document.getElementById(id);
      if (!box) return;
      if (sidebarParentActive[id].indexOf(group) >= 0) {
        var parent = box.querySelector('.sbn-item--parent');
        if (parent) parent.classList.add('active');
      }
    });

    var sdt = document.getElementById('sbnSdt');
    if (sdt) sdt.classList.add('open');

    Object.keys(sidebarOpenGroups).forEach(function (id) {
      var box = document.getElementById(id);
      if (!box) return;
      var open = sidebarOpenGroups[id].indexOf(group) >= 0;
      box.classList.toggle('open', open);
      var btn = box.querySelector('.sbn-toggle');
      if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    var views = cfg.views || {};
    var view = views[group] || views[''] || {};
    if (pathSub && view.path) pathSub.textContent = view.path;
    if (mainTitle && view.title) mainTitle.textContent = view.title;

    if (!pathCatLink) {
      var titleRow = document.querySelector('.mh-title-row');
      if (titleRow) {
        pathCatLink = document.createElement('a');
        pathCatLink.id = 'pathCatLink';
        pathCatLink.className = 'mh-cat-link';
        pathCatLink.innerHTML = 'Страница категории<span class="gr-go-arr" aria-hidden="true">→</span>';
        titleRow.appendChild(pathCatLink);
      }
    }
    if (pathCatLink) {
      var onCatPage = false;
      if (view.termUrl) {
        try {
          onCatPage = new URL(view.termUrl, location.origin).pathname.replace(/\/$/, '') === location.pathname.replace(/\/$/, '');
        } catch (e) { onCatPage = false; }
      }
      if (view.termUrl && !onCatPage) {
        pathCatLink.href = view.termUrl;
        pathCatLink.title = view.termName
          ? ('Открыть страницу категории «' + view.termName + '»')
          : 'Открыть страницу категории';
        pathCatLink.removeAttribute('hidden');
        pathCatLink.style.display = '';
      } else {
        pathCatLink.href = '#';
        pathCatLink.title = '';
        pathCatLink.setAttribute('hidden', '');
        pathCatLink.style.display = 'none';
      }
    }
  }

  function scrollToCatalog() {
    var target = document.getElementById('registry') || document.querySelector('.catalog-embed');
    if (!target) return;
    target.classList.add('is-flash');
    setTimeout(function () { target.classList.remove('is-flash'); }, 900);
    // Скролл-контейнер — body (html/body height:100%), не window.
    // scrollIntoView сам находит нужный контейнер; абсолютные координаты не нужны.
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    try {
      target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
    } catch (err) {
      target.scrollIntoView(true);
    }
  }

  function markSeriesActive(gost) {
    document.querySelectorAll('#regList a.reg-r').forEach(function (r) {
      var on = false;
      try {
        on = !!gost && new URL(r.href, location.origin).searchParams.get('gost') === gost;
      } catch (err) { /* ignore */ }
      r.classList.toggle('is-active', on);
      if (on) r.setAttribute('aria-current', 'true');
      else r.removeAttribute('aria-current');
    });
  }

  // Сервер рендерит SEO-досье и базу знаний только на чистом корне каталога
  // (archive-product.php, $promen_clean_root). JS-фильтрация этого не повторяла:
  // до перезагрузки секции оставались на месте, после — исчезали, и выглядело
  // это как пропавший контент. Держим состояние синхронно.
  function syncTailSections(params) {
    // Зеркало $promen_clean_root (archive-product.php): секции прячем только
    // на пагинации. Под фильтром и группой они остаются — там noindex+canonical,
    // дублей нет, а пропажа половины страницы по клику на фильтр сбивает.
    var clean = !params.page || params.page <= 1;
    document.querySelectorAll('.cat-seo, .cat-kb').forEach(function (el) {
      el.hidden = !clean;
    });
  }

  // Фильтры, табы отраслей, шапка таблицы и пагинация пересобираются через
  // innerHTML. Элемент под фокусом при этом исчезает, фокус падает на body,
  // и следующий Tab начинает обход страницы с начала — пройти фильтры
  // клавиатурой было нельзя. Перед перерисовкой запоминаем, что это было
  // (параметр + значение), после — находим такой же элемент и возвращаем
  // фокус. Заодно переживают перерисовку раскрытые «+ ещё»: раньше выбранная
  // в хвосте списка марка пряталась обратно.
  function captureFocus() {
    var el = document.activeElement;
    var key = null;
    if (el && el !== document.body && el.closest) {
      var slider = el.closest('.cbf-slider');
      var chip = el.closest('.cbf-multi .c-chip');
      var tab = el.closest('a.cb-tab');
      var th = el.closest('.th-sort[data-sort-field]');
      if (slider && (el.classList.contains('cbf-r') || el.classList.contains('cbf-in'))) {
        key = { t: 'slider', param: slider.dataset.param, cls: el.classList.contains('cbf-r') ? 'cbf-r' : 'cbf-in', bound: el.dataset.bound };
      } else if (chip) {
        key = { t: 'chip', param: chip.closest('.cbf-multi').dataset.param, slug: chip.dataset.slug || '', more: chip.classList.contains('c-chip--more') };
      } else if (tab) {
        key = { t: 'tab', slug: tab.dataset.industry || '' };
      } else if (th) {
        key = { t: 'sort', field: th.getAttribute('data-sort-field') };
      } else if (el.closest('.cat-pagination')) {
        key = { t: 'page' };
      }
    }
    var expanded = [];
    document.querySelectorAll('#cbFilters .cbf-multi.is-expanded').forEach(function (m) {
      expanded.push(m.dataset.param);
    });
    return { key: key, expanded: expanded };
  }

  function findMulti(param) {
    var found = null;
    document.querySelectorAll('#cbFilters .cbf-multi').forEach(function (m) {
      if (m.dataset.param === param) found = m;
    });
    return found;
  }

  function restoreFocus(state) {
    state.expanded.forEach(function (param) {
      var m = findMulti(param);
      var more = m && m.querySelector('.c-chip--more');
      if (more) expandMoreChips(more);
    });
    var k = state.key;
    if (!k) return;
    var target = null;
    if (k.t === 'slider') {
      document.querySelectorAll('#cbFilters .cbf-slider').forEach(function (s) {
        if (s.dataset.param === k.param) target = s.querySelector('.' + k.cls + '[data-bound=' + k.bound + ']');
      });
    } else if (k.t === 'chip') {
      var m = findMulti(k.param);
      if (m) {
        m.querySelectorAll('.c-chip').forEach(function (c) {
          if (!target && !k.more && c.dataset.slug === k.slug) target = c;
        });
        // Фишка в свёрнутом хвосте — раскрываем, иначе фокус не встанет.
        if (target && target.classList.contains('c-chip--extra')) {
          var more = m.querySelector('.c-chip--more');
          if (more) expandMoreChips(more);
        }
        if (!target) target = m.querySelector('.c-chip');
      }
    } else if (k.t === 'tab') {
      document.querySelectorAll('#cbTabs a.cb-tab').forEach(function (t) {
        if ((t.dataset.industry || '') === k.slug) target = t;
      });
    } else if (k.t === 'sort') {
      target = document.querySelector('.th-sort[data-sort-field="' + k.field + '"]');
    } else if (k.t === 'page') {
      // Новая страница — фокус в начало выдачи, куда её и прокрутили.
      list.setAttribute('tabindex', '-1');
      target = list;
    }
    if (!target) return;
    target.focus({ preventScroll: true });
    if (target.classList.contains('cbf-in') && target.setSelectionRange) {
      var n = target.value.length;
      target.setSelectionRange(n, n);
    }
  }

  // Пока запрос летит, человек успевает набрать дальше или нажать крестик.
  // Ответ на устаревший запрос применять нельзя: он возвращает выдачу,
  // от которой уже отказались (ловилось на проде, где поиск идёт по секунде).
  var swapSeq = 0;

  function swap(url, push, opts) {
    opts = opts || {};
    var parsed = parsePageUrl(url);
    var seq = ++swapSeq;
    list.style.opacity = '.35';
    fetch(cfg.apiUrl + '?' + buildApiQuery(parsed.params), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (seq !== swapSeq) return;
        var focusState = captureFocus();
        renderList(data, parsed.url.toString());
        renderNote(data);
        renderFilters(data, parsed.url.toString());
        renderPagination(data, parsed.url.toString());
        restoreFocus(focusState);
        updateCount(data.total || 0);
        updateSidebar(parsed.params.group);
        markSeriesActive(parsed.params.gost || '');
        syncTailSections(parsed.params);
        if (window._promenBindFilterToggle) window._promenBindFilterToggle();
        list.style.opacity = '';
        if (push) history.pushState({ promen: true }, '', parsed.url.toString());
        if (opts.scroll !== false) scrollToCatalog();
      })
      .catch(function () {
        if (seq !== swapSeq) return;
        location.href = url;
      });
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('.c-chip--more')) return;

    // Серия из «Реестра исполнений» / ссылки «в реестре» → фильтр без перезагрузки.
    var series = e.target.closest('a.reg-r, a.sg-link');
    if (series && series.href && list) {
      var su;
      try { su = new URL(series.href, location.origin); } catch (err) { su = null; }
      var gost = su && su.searchParams.get('gost');
      if (gost) {
        e.preventDefault();
        var dest = new URL(location.href);
        dest.searchParams.set('gost', gost);
        dest.searchParams.delete('paged');
        dest.searchParams.delete('page');
        dest.hash = '';
        markSeriesActive(gost);
        swap(dest.toString(), true, { scroll: true });
        return;
      }
    }

    // «Применить фильтры» из строки-пояснения. Подсказка часто ведёт в другой
    // раздел — у него своя страница категории, и подменять там одну выдачу
    // нельзя: шапка и секции остались бы от прежнего раздела.
    var hint = e.target.closest('a.cb-note-hint');
    if (hint && hint.href) {
      var hu = null;
      try { hu = new URL(hint.href, location.origin); } catch (err) { hu = null; }
      var samePath = hu && hu.pathname.replace(/\/$/, '') === location.pathname.replace(/\/$/, '');
      if (samePath) {
        e.preventDefault();
        if (searchInput) { searchInput.value = ''; syncSearchClear(); }
        swap(hint.href, true, { scroll: false });
      }
      return;
    }

    var a = e.target.closest('a.c-chip, .cat-pagination a, .ce-reset, .ce-all, a.sbn-filter, .cbs-tag, .cbs-reset, a.cb-tab, a.cb-reset');
    if (!a || !a.href) return;
    if (a.classList.contains('mh-cat-link')) return;
    e.preventDefault();
    var keepPos = a.classList.contains('cb-tab') || a.classList.contains('cb-reset') || a.classList.contains('c-chip');
    swap(a.href, true, keepPos ? { scroll: false } : {});
  });

  // Клик по сортируемой шапке (DN/PN/Масса) — toggle asc/desc.
  function applySort(field) {
    var cs = currentSort();
    var dir = (cs.field === field && cs.dir === 'asc') ? 'desc' : 'asc';
    var u = new URL(location.href);
    u.searchParams.set('sort', field + ':' + dir);
    u.searchParams.delete('paged');
    u.searchParams.delete('page');
    swap(u.toString(), true);
  }
  document.addEventListener('click', function (e) {
    var th = e.target.closest('.th-sort[data-sort-field]');
    if (!th) return;
    e.preventDefault();
    applySort(th.getAttribute('data-sort-field'));
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var th = e.target.closest && e.target.closest('.th-sort[data-sort-field]');
    if (!th) return;
    e.preventDefault();
    applySort(th.getAttribute('data-sort-field'));
  });

  function bindFilterToggle() {
    var toggle = document.getElementById('cbToggle');
    var panel = document.getElementById('cbFilters');
    if (!toggle || !panel) return;
    var sticky = panel.closest('.sticky-hd');
    // Раскрытая панель делает шапку выше экрана (на 390px — 1041px против
    // 780px доступных). Прилипшая к верху шапка тогда не даёт долистать себя
    // до низа: скроллится только таблица под ней. Помечаем состояние —
    // CSS на это время снимает залипание.
    var mark = function (open) { if (sticky) sticky.classList.toggle('filters-open', open); };
    if (window._promenFiltersOpen) {
      panel.classList.remove('is-collapsed');
      toggle.setAttribute('aria-expanded', 'true');
      mark(true);
    }
    toggle.onclick = function () {
      var open = panel.classList.toggle('is-collapsed') === false;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      window._promenFiltersOpen = open;
      mark(open);
    };
  }
  bindFilterToggle();
  window._promenBindFilterToggle = bindFilterToggle;

  // Плейсхолдер поиска подбирается по ФАКТИЧЕСКОЙ ширине поля, а не по
  // медиазапросу: поле сужают и ширина экрана, и кнопка «Фильтры», и кнопка
  // «Сбросить» с бейджем при активных фильтрах. На брейкпоинтах это ловилось
  // не везде — например, на десктопе 1440 с активным фильтром полный вариант
  // всё равно обрезался. Меряем текст канвасом и берём самый длинный из
  // влезающих.
  (function bindSearchPlaceholder() {
    var input = document.getElementById('searchInput');
    if (!input || !input.dataset.phSm) return;
    var variants = [input.getAttribute('placeholder'), input.dataset.phSm];
    var ctx = document.createElement('canvas').getContext('2d');
    var apply = function () {
      var cs = getComputedStyle(input);
      ctx.font = cs.fontWeight + ' ' + cs.fontSize + ' ' + cs.fontFamily;
      var room = input.clientWidth - 4; // небольшой запас на курсор
      for (var i = 0; i < variants.length; i++) {
        if (ctx.measureText(variants[i]).width <= room || i === variants.length - 1) {
          if (input.getAttribute('placeholder') !== variants[i]) {
            input.setAttribute('placeholder', variants[i]);
          }
          return;
        }
      }
    };
    apply();
    if (window.ResizeObserver) {
      var ro = new ResizeObserver(apply);
      ro.observe(input);
    } else {
      window.addEventListener('resize', apply);
    }
    // Шрифт DINPro грузится асинхронно — после подмены метрики меняются.
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(apply);
  })();

  // Сворачивание сайдбара категорий на ≤900px (на десктопе кнопка скрыта CSS).
  // Высоту задаём по факту, а после анимации снимаем ограничение: список выше
  // экрана, и фиксированный max-height превращал панель в отдельный
  // скролл-контейнер со своей полосой прокрутки.
  (function bindCatNavToggle() {
    var toggle = document.getElementById('catSbToggle');
    var panel = document.getElementById('catSb');
    if (!toggle || !panel) return;

    panel.addEventListener('transitionend', function (e) {
      if (e.target === panel && e.propertyName === 'max-height' && panel.classList.contains('is-open')) {
        panel.style.maxHeight = 'none';
      }
    });

    toggle.addEventListener('click', function () {
      var willOpen = !panel.classList.contains('is-open');
      if (willOpen) {
        panel.classList.add('is-open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
      } else {
        // Фиксируем текущую высоту (могла быть none), иначе схлопывание не анимируется.
        panel.style.maxHeight = panel.scrollHeight + 'px';
        void panel.offsetHeight;
        panel.classList.remove('is-open');
        panel.style.maxHeight = '0px';
      }
      toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    // Раскрытие вложенной группы меняет высоту панели — пока идёт анимация
    // открытия, max-height ещё зафиксирован, поэтому подтягиваем его.
    panel.addEventListener('click', function (e) {
      if (!panel.classList.contains('is-open')) return;
      if (!e.target.closest('.sbn-toggle')) return;
      setTimeout(function () {
        if (panel.style.maxHeight !== 'none') panel.style.maxHeight = panel.scrollHeight + 'px';
      }, 0);
    });
  })();

  // Клавиатура на бегунках (стрелки при фокусе): живой UI + запрос на change.
  document.addEventListener('input', function (e) {
    var r = e.target.closest && e.target.closest('.cbf-slider .cbf-r');
    if (!r) return;
    var box = r.closest('.cbf-slider');
    var minR = box.querySelector('.cbf-r[data-bound=min]');
    var maxR = box.querySelector('.cbf-r[data-bound=max]');
    if (+minR.value > +maxR.value) {
      if (r === minR) maxR.value = minR.value; else minR.value = maxR.value;
    }
    updateSliderUI(box);
  });
  document.addEventListener('change', function (e) {
    var r = e.target.closest && e.target.closest('.cbf-slider .cbf-r');
    if (!r) return;
    applyRange(r.closest('.cbf-slider'));
  });

  // Мышь/тач: весь трек — одна зона. Двигается БЛИЖАЙШИЙ бегунок; при слипшихся
  // бегунках решает сторона клика (слева — min, справа — max). Перехлёст
  // исключён клампом, z-index-фокусы не нужны.
  document.addEventListener('pointerdown', function (e) {
    var track = e.target.closest && e.target.closest('.cbf-track');
    if (!track || e.button > 0) return;
    var box = track.closest('.cbf-slider');
    var vals = sliderVals(box);
    if (!vals.length) return;
    var last = vals.length - 1;
    var rect = track.getBoundingClientRect();
    var minR = box.querySelector('.cbf-r[data-bound=min]');
    var maxR = box.querySelector('.cbf-r[data-bound=max]');

    function idxAt(ev) {
      var t = (ev.clientX - rect.left) / Math.max(rect.width, 1);
      return Math.max(0, Math.min(last, Math.round(t * last)));
    }

    var i = idxAt(e);
    var a = +minR.value, b = +maxR.value;
    var target;
    if (Math.abs(i - a) < Math.abs(i - b)) target = minR;
    else if (Math.abs(i - a) > Math.abs(i - b)) target = maxR;
    else target = i < a ? minR : maxR; // слиплись/равноудалён — по стороне клика

    e.preventDefault();
    if (track.setPointerCapture) {
      try { track.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
    }

    function move(ev) {
      var j = idxAt(ev);
      if (target === minR) minR.value = Math.min(j, +maxR.value);
      else maxR.value = Math.max(j, +minR.value);
      updateSliderUI(box);
    }
    function up() {
      track.removeEventListener('pointermove', move);
      track.removeEventListener('pointerup', up);
      track.removeEventListener('pointercancel', up);
      applyRange(box);
    }
    move(e);
    track.addEventListener('pointermove', move);
    track.addEventListener('pointerup', up);
    track.addEventListener('pointercancel', up);
  });

  // Ручной ввод границ: Enter/blur — снап к ближайшему значению ряда и запрос.
  function applyManualInput(inp) {
    var box = inp.closest('.cbf-slider');
    var vals = sliderVals(box);
    if (!vals.length) return;
    var minR = box.querySelector('.cbf-r[data-bound=min]');
    var maxR = box.querySelector('.cbf-r[data-bound=max]');
    var raw = inp.value.trim().replace(',', '.').replace(/[^\d.\-]/g, '');
    var isMin = inp.dataset.bound === 'min';
    var idx;
    if (raw === '') {
      idx = isMin ? 0 : vals.length - 1; // пусто = без ограничения
    } else {
      var x = parseFloat(raw);
      if (isNaN(x)) { updateSliderUI(box); return; } // мусор — вернуть как было
      idx = nearestIdx(vals, x);
    }
    if (isMin) minR.value = Math.min(idx, +maxR.value);
    else maxR.value = Math.max(idx, +minR.value);
    updateSliderUI(box);
    applyRange(box);
  }
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    var inp = e.target.closest && e.target.closest('.cbf-in');
    if (!inp) return;
    e.preventDefault();
    inp.blur(); // blur применит значение
  });
  document.addEventListener('focusout', function (e) {
    var inp = e.target.closest && e.target.closest('.cbf-in');
    if (!inp) return;
    applyManualInput(inp);
  });

  // Поиск: живой, без Enter (Enter тоже работает — submit ниже).
  var searchInput = searchForm && searchForm.querySelector('input[name=q]');
  var qTimer = null;

  /**
   * @param {boolean} force Применить, даже если в адресе то же самое.
   *                        Нужно крестику: запрос по набранному тексту может
   *                        быть ещё в полёте, и в адресе его пока нет.
   */
  function applySearch(val, force) {
    var url = new URL(location.href);
    var cur = url.searchParams.get('q') || '';
    if (!force && val === cur) return;
    url.searchParams.delete('paged');
    if (val) url.searchParams.set('q', val); else url.searchParams.delete('q');
    swap(url.toString(), true, { scroll: false });
  }

  // Крестик очистки: живёт в самом поле, поэтому AJAX-перерисовка списка
  // и фильтров его не трогает — хватает одной привязки.
  var searchClear = document.getElementById('searchClear');
  var searchHint = document.getElementById('searchHint');
  function syncSearchClear() {
    if (!searchInput) return;
    var filled = searchInput.value.trim() !== '';
    if (searchClear) {
      if (filled) searchClear.removeAttribute('hidden');
      else searchClear.setAttribute('hidden', '');
    }
    // Подсказку «/» убираем, как только в поле что-то есть или в нём работают:
    // напоминать про клавишу тому, кто уже в поле, незачем.
    if (searchHint) {
      if (filled || document.activeElement === searchInput) searchHint.setAttribute('hidden', '');
      else searchHint.removeAttribute('hidden');
    }
  }

  /* Горячая клавиша: «/» или Ctrl/⌘+K переводят курсор в поиск. В реестре
     на 15 тысяч строк это главное действие страницы, а поле живёт в липкой
     шапке — тянуться к нему мышью каждый раз не нужно. */
  document.addEventListener('keydown', function (e) {
    if (!searchInput) return;
    var slash = e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey;
    // «л» — та же клавиша K в русской раскладке.
    var ctrlK = (e.ctrlKey || e.metaKey) && ['k', 'K', 'л', 'Л'].indexOf(e.key) >= 0;
    if (!slash && !ctrlK) return;
    var t = e.target;
    var tag = t && t.tagName ? t.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea' || tag === 'select' || (t && t.isContentEditable)) return;
    e.preventDefault();
    var r = searchInput.getBoundingClientRect();
    if (r.top < 0 || r.bottom > (window.innerHeight || document.documentElement.clientHeight)) {
      searchInput.scrollIntoView({ block: 'center' });
    }
    searchInput.focus();
    searchInput.select();
    syncSearchClear(); // событие focus приходит не всегда — значок гасим сами
  });

  if (searchInput) {
    syncSearchClear();
    searchInput.addEventListener('focus', syncSearchClear);
    searchInput.addEventListener('blur', syncSearchClear);
    searchInput.addEventListener('input', function () {
      clearTimeout(qTimer);
      clearTimeout(sugTimer);
      syncSearchClear();
      var val = searchInput.value.trim();
      qTimer = setTimeout(function () {
        if (val.length === 1) return; // один символ — ждём продолжения
        applySearch(val);
      }, 350);
      sugTimer = setTimeout(function () { sugFetch(val); }, 250);
    });
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        if (sugMove(e.key === 'ArrowDown' ? 1 : -1)) e.preventDefault();
        return;
      }
      if (e.key === 'Enter') {
        var picked = sugActive();
        if (picked) {
          e.preventDefault();
          location.href = picked.href;
        }
        return;
      }
      if (e.key !== 'Escape') return;
      // Первый Escape закрывает подсказки, второй очищает поле.
      if (sugBox && !sugBox.hasAttribute('hidden')) {
        sugHide();
        return;
      }
      if (searchInput.value !== '') {
        searchInput.value = '';
        clearTimeout(qTimer);
        clearTimeout(sugTimer);
        syncSearchClear();
        applySearch('', true);
      }
    });
  }

  /* ── Подсказки при вводе ──────────────────────────────────────────
     Список поверх реестра: клик ведёт сразу в карточку, минуя таблицу.
     Порог в три символа и своя ручка /suggest — на проде без Meilisearch
     каждый запрос это скан таблицы, и подсказка обязана быть дешёвой. */
  var sugBox = null;
  var sugSeq = 0;
  var sugTimer = null;

  function sugEnsure() {
    if (sugBox || !searchForm) return sugBox;
    sugBox = document.createElement('div');
    sugBox.className = 'cb-sug';
    sugBox.id = 'cbSug';
    sugBox.setAttribute('hidden', '');
    searchForm.appendChild(sugBox);
    return sugBox;
  }

  function sugHide() {
    if (!sugBox) return;
    sugBox.setAttribute('hidden', '');
    sugBox.innerHTML = '';
  }

  function sugRender(items, total) {
    var box = sugEnsure();
    if (!box) return;
    if (!items.length) { sugHide(); return; }
    var html = items.map(function (it) {
      return '<a class="cb-sug-i" href="' + esc(it.url) + '">' +
        '<span class="cb-sug-t">' + esc(it.title) + '</span>' +
        '<span class="cb-sug-n">' + esc(it.norm || '') + '</span></a>';
    }).join('');
    if (total > items.length) {
      html += '<span class="cb-sug-all">Ещё ' + Number(total - items.length).toLocaleString('ru-RU') + ' — в реестре ниже</span>';
    }
    box.innerHTML = html;
    box.removeAttribute('hidden');
  }

  function sugFetch(val) {
    if (!cfg.suggestUrl || val.length < 3) { sugHide(); return; }
    var seq = ++sugSeq;
    var params = new URLSearchParams();
    params.set('q', val);
    var grp = parsePageUrl(location.href).params.group || '';
    if (grp) params.set('group', grp);
    fetch(cfg.suggestUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        // Ответ на устаревший ввод и подсказки в поле, из которого уже ушли,
        // показывать незачем.
        if (seq !== sugSeq || document.activeElement !== searchInput) return;
        sugRender(d.items || [], d.total || 0);
      })
      .catch(function () { sugHide(); });
  }

  function sugMove(step) {
    if (!sugBox || sugBox.hasAttribute('hidden')) return false;
    var items = sugBox.querySelectorAll('.cb-sug-i');
    if (!items.length) return false;
    var cur = -1;
    for (var i = 0; i < items.length; i++) {
      if (items[i].classList.contains('is-on')) { cur = i; break; }
    }
    if (cur >= 0) items[cur].classList.remove('is-on');
    var next = cur + step;
    if (next < 0) next = items.length - 1;
    if (next >= items.length) next = 0;
    items[next].classList.add('is-on');
    items[next].scrollIntoView({ block: 'nearest' });
    return true;
  }

  function sugActive() {
    return sugBox && !sugBox.hasAttribute('hidden') ? sugBox.querySelector('.cb-sug-i.is-on') : null;
  }

  // Закрываем по клику мимо поля, а не по blur: blur срабатывает раньше
  // клика по самой подсказке и уводил бы список из-под курсора.
  document.addEventListener('mousedown', function (e) {
    if (!sugBox || sugBox.hasAttribute('hidden')) return;
    if (searchForm && searchForm.contains(e.target)) return;
    sugHide();
  });

  if (searchClear && searchInput) {
    searchClear.addEventListener('click', function () {
      searchInput.value = '';
      clearTimeout(qTimer); // отложенный запрос по прежнему тексту отменяем
      clearTimeout(sugTimer);
      sugHide();
      syncSearchClear();
      applySearch('', true);
      searchInput.focus();
    });
  }

  if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      clearTimeout(qTimer);
      applySearch(searchForm.querySelector('input[name=q]').value.trim());
    });
  }

  window.addEventListener('popstate', function () {
    swap(location.href, false, { scroll: false });
  });

  markSeriesActive(parsePageUrl(location.href).params.gost || '');
})();

/* PDP + sidebar + KB — без изменений */
(function () {
  var pdp = document.getElementById('pdp');
  var overlay = document.getElementById('pdpOverlay');
  if (!pdp || !overlay) return;
  var openBtn = document.getElementById('pdpOpen');
  function fill(row) {
    var d = row.dataset;
    document.getElementById('pdpCode').textContent = d.sku || '';
    document.getElementById('pdpTitle').textContent = d.title || '';
    document.getElementById('pdpSub').textContent = d.family || '';
    var params = [
      ['Материал', d.steel], ['Отрасль', d.industry], ['Норматив', d.norm]
    ].filter(function (p) { return p[1]; });
    document.getElementById('pdpParams').innerHTML = params.map(function (p) {
      return '<div class="pdp-prow"><span class="pdp-pk">' + p[0] + '</span><span class="pdp-pv">' + p[1] + '</span></div>';
    }).join('');
    if (openBtn) openBtn.href = row.href;
  }
  function open() { pdp.classList.add('open'); overlay.classList.add('show'); }
  function close() { pdp.classList.remove('open'); overlay.classList.remove('show'); }
  document.addEventListener('click', function (e) {
    var arrow = e.target.closest('.prod-row .pr-arr');
    if (!arrow) return;
    var row = arrow.closest('.prod-row');
    e.preventDefault();
    e.stopPropagation();
    document.querySelectorAll('.prod-row.sel').forEach(function (r) { r.classList.remove('sel'); });
    row.classList.add('sel');
    fill(row);
    open();
  });
  document.getElementById('pdpClose').addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();

(function () {
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.sbn-toggle');
    if (!btn) return;
    e.preventDefault();
    var group = btn.closest('.sbn-group');
    if (!group) return;
    var open = group.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();

(function () {
  var tabs = document.querySelectorAll('.kb-tab');
  var panels = document.querySelectorAll('.kb-panel');
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('kp-active'); });
      btn.classList.add('active');
      var target = document.getElementById('kp-' + btn.dataset.panel);
      if (target) target.classList.add('kp-active');
    });
  });
  document.querySelectorAll('.fq-q').forEach(function (q) {
    q.addEventListener('click', function () { q.parentElement.classList.toggle('open'); });
  });
})();

/**
 * Подсказка марок стали (введена 2026-07-30, оставлена совсем 2026-09-23).
 *
 * Колонка «Материал» усечена до 2 марок на 77% строк реестра («20, 09Г2С … +12»).
 * Хвост «… +N» показывает полный список из data-steels: на компьютере по
 * наведению, на телефоне по нажатию.
 *
 * Тап приходится гасить вручную: хвост живёт внутри строки-ссылки, и без
 * preventDefault нажатие уводило бы в карточку товара вместо подсказки.
 * Клавиатура не покрыта намеренно: span внутри <a> нельзя сделать кнопкой,
 * не сломав Tab по строкам реестра, а те же марки целиком есть в карточке.
 */
(function () {
  var HIDE_DELAY = 90;
  var pop = null;
  var hideTimer = null;
  var current = null;

  function ensurePop() {
    if (pop) return pop;
    pop = document.createElement('div');
    pop.className = 'mat-pop';
    pop.setAttribute('role', 'tooltip');
    document.body.appendChild(pop);
    return pop;
  }

  function place(trigger) {
    var r = trigger.getBoundingClientRect();
    var p = pop.getBoundingClientRect();
    var gap = 8;
    // Вправо места нет — колонка «Материал» прижата к «Отрасли», поэтому
    // прижимаем правый край подсказки к правому краю триггера.
    var left = Math.min(r.right - p.width, window.innerWidth - p.width - 12);
    var top = r.top - p.height - gap;
    if (top < 8) top = r.bottom + gap;
    pop.style.left = Math.max(12, left) + 'px';
    pop.style.top = top + 'px';
  }

  function show(trigger) {
    var raw = trigger.getAttribute('data-steels') || '';
    if (!raw) return;
    clearTimeout(hideTimer);
    current = trigger;
    var box = ensurePop();
    var items = raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    box.innerHTML = '<b class="mat-pop-h">Марки стали · ' + items.length + '</b>' +
      '<span class="mat-pop-list">' + items.map(function (s) {
        return '<i>' + s.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</i>';
      }).join('') + '</span>';
    box.classList.add('is-on');
    place(trigger);
  }

  function hide() {
    if (!pop) return;
    current = null;
    pop.classList.remove('is-on');
  }

  // Делегирование: строк в реестре до 15k, свой слушатель на каждую не вешаем.
  document.addEventListener('mouseover', function (e) {
    var t = e.target.closest ? e.target.closest('.pr-mat-more') : null;
    if (t && t !== current) show(t);
  });

  document.addEventListener('mouseout', function (e) {
    if (!current) return;
    var t = e.target.closest ? e.target.closest('.pr-mat-more') : null;
    if (t !== current) return;
    // Небольшая задержка: без неё подсказка мигает на дрожании курсора.
    hideTimer = setTimeout(hide, HIDE_DELAY);
  });

  // Тап на телефоне: открыть по нажатию на хвост, закрыть повторным нажатием
  // или касанием мимо. Строка реестра — ссылка, поэтому нажатие по хвосту
  // останавливаем, иначе вместо подсказки откроется карточка.
  document.addEventListener('click', function (e) {
    var t = e.target.closest ? e.target.closest('.pr-mat-more') : null;
    if (!t) {
      if (current) hide();
      return;
    }
    e.preventDefault();
    e.stopPropagation();
    if (current === t) hide();
    else show(t);
  });

  // Скролл/ресайз — подсказка отвязана от потока, пересчитывать дороже, чем скрыть.
  window.addEventListener('scroll', hide, true);
  window.addEventListener('resize', hide);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hide();
  });
})();
