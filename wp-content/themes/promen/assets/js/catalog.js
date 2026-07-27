/**
 * Каталог: фильтры и пагинация через JSON API (/wp-json/promen/v1/catalog).
 */
(function () {
  function expandMoreChips(more) {
    var box = more.closest('.cbf-multi');
    if (!box) return;
    box.classList.add('is-expanded');
    box.querySelectorAll('.c-chip--extra').forEach(function (c) {
      c.classList.remove('c-chip--extra');
    });
    more.remove();
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
    ['dn', 'pn'].forEach(function (p) {
      if (params[p + '_min']) q.set(p + '_min', params[p + '_min']);
      if (params[p + '_max']) q.set(p + '_max', params[p + '_max']);
    });
    ['steel', 'industry', 'angle', 'gost'].forEach(function (p) {
      if (params[p]) q.set(p, params[p]);
    });
    if (params.sort) q.set('sort', params.sort);
    if (params.scope === 'all') q.set('scope', 'all');
    return q.toString();
  }

  function gridTpl(columns) {
    var widths = (columns || []).map(function (c) { return c.w; }).join(' ');
    return '150px minmax(200px,1fr) ' + widths + ' 120px 96px 32px';
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

  function renderRow(hit, columns, tpl, i) {
    var cells = (columns || []).map(function (col) {
      var val = (hit.cells && hit.cells[col.key]) ? hit.cells[col.key] : '—';
      return '<span class="pr-' + esc(col.key) + '">' + esc(val) + '</span>';
    }).join('');
    var delay = Math.min(i * 0.025, 0.35);
  return '<a class="prod-row" href="' + esc(hit.url) + '" style="grid-template-columns:' + esc(tpl) + ';animation-delay:' + delay + 's"' +
      ' data-sku="' + esc(hit.sku) + '" data-title="' + esc(hit.title) + '"' +
      ' data-norm="' + esc(hit.norm) + '" data-steel="' + esc(hit.steel_display) + '"' +
      ' data-industry="' + esc(hit.industry_display) + '">' +
      '<span class="pr-norm"><span class="pr-norm-code">' + esc(hit.norm || '—') + '</span></span>' +
      '<span class="pr-name">' + esc(hit.title) + (hit.family ? '<small>' + esc(hit.family) + '</small>' : '') + '</span>' +
      cells +
      '<span class="pr-mat">' + esc(hit.steel_display || '—') + '</span>' +
      '<span class="pr-ind">' + industryTagsHtml(hit.industries) + '</span>' +
      '<span class="pr-arr">›</span></a>';
  }

  function renderList(data) {
    var cols = data.columns || [];
    var tpl = gridTpl(cols);
    if (tblHd) {
      tblHd.style.gridTemplateColumns = tpl;
      var cs = currentSort();
      var hdr = '<span>Норматив</span><span>Наименование</span>';
      cols.forEach(function (c) {
        var sf = SORT_FIELDS[c.key];
        if (!sf) { hdr += '<span>' + esc(c.label) + '</span>'; return; }
        var active = sf === cs.field;
        var arr = active ? (cs.dir === 'desc' ? '↓' : '↑') : '⇅';
        hdr += '<span class="th-sort' + (active ? ' is-active' : '') + '" role="button" tabindex="0" data-sort-field="' + esc(sf) + '" title="Сортировать">' +
          esc(c.label) + '<i class="th-arr">' + arr + '</i></span>';
      });
      hdr += '<span>Материал</span><span>Отрасль</span><span></span>';
      tblHd.innerHTML = hdr;
    }
    if (!data.hits || !data.hits.length) {
      var u = new URL(location.href);
      var qv = u.searchParams.get('q') || '';
      var grp = u.searchParams.get('group') || '';
      var allLink = '';
      if (qv && (grp || u.searchParams.get('scope') !== 'all')) {
        var au = new URL(location.pathname, location.origin);
        au.searchParams.set('q', qv);
        au.searchParams.set('scope', 'all');
        allLink = '<a class="ce-all" href="' + esc(au.toString()) + '">Искать «' + esc(qv) + '» во всём каталоге →</a>';
      }
      list.innerHTML = '<div class="cat-empty"><div class="ce-code">—</div>' +
        '<div class="ce-msg">Нет позиций по заданным параметрам</div>' + allLink +
        '<a class="ce-reset" href="' + esc(location.pathname) + '">Сбросить фильтры</a></div>';
      return;
    }
    list.innerHTML = data.hits.map(function (h, i) { return renderRow(h, cols, tpl, i); }).join('');
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
    ['steel', 'gost', 'angle', 'industry'].forEach(function (p) {
      var v = u.searchParams.get(p);
      if (v) n += v.split(',').filter(Boolean).length;
    });
    ['dn', 'pn'].forEach(function (p) {
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
        html += '<a class="c-chip' + (on ? ' on' : '') + (empty ? ' c-chip--zero' : '') + (i >= vis ? ' c-chip--extra' : '') + '" href="' + esc(chipHref(param, o.slug, pageUrl)) + '">' +
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

  function renderPagination(data, pageUrl) {
    if (!pagination) return;
    var pages = data.pages || 0;
    if (pages <= 1) { pagination.innerHTML = ''; return; }
    var u = new URL(pageUrl);
    var cur = data.page || 1;
    function link(p, label, cls) {
      u.searchParams.set('paged', String(p));
      return '<a class="' + cls + '" href="' + esc(u.toString()) + '">' + label + '</a> ';
    }
    var html = '';
    if (cur > 1) html += link(cur - 1, '← Назад', 'prev page-numbers');

    // Компактное окно: 1, последняя и ±2 вокруг текущей; разрывы — многоточие.
    var win = 2, set = {};
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
    if (cur < pages) html += link(cur + 1, 'Вперёд →', 'next page-numbers');
    pagination.innerHTML = html.trim();
  }

  function updateCount(total) {
    if (!count) return;
    count.textContent = Number(total).toLocaleString('ru-RU') + ' позиций';
    count.classList.remove('pop');
    void count.offsetWidth;
    count.classList.add('pop');
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

  function swap(url, push, opts) {
    opts = opts || {};
    var parsed = parsePageUrl(url);
    list.style.opacity = '.35';
    fetch(cfg.apiUrl + '?' + buildApiQuery(parsed.params), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        renderList(data);
        renderFilters(data, parsed.url.toString());
        renderPagination(data, parsed.url.toString());
        updateCount(data.total || 0);
        updateSidebar(parsed.params.group);
        markSeriesActive(parsed.params.gost || '');
        if (window._promenBindFilterToggle) window._promenBindFilterToggle();
        list.style.opacity = '';
        if (push) history.pushState({ promen: true }, '', parsed.url.toString());
        if (opts.scroll !== false) scrollToCatalog();
      })
      .catch(function () { location.href = url; });
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
    if (window._promenFiltersOpen) { panel.classList.remove('is-collapsed'); toggle.setAttribute('aria-expanded', 'true'); }
    toggle.onclick = function () {
      var open = panel.classList.toggle('is-collapsed') === false;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      window._promenFiltersOpen = open;
    };
  }
  bindFilterToggle();
  window._promenBindFilterToggle = bindFilterToggle;

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

  function applySearch(val) {
    var url = new URL(location.href);
    var cur = url.searchParams.get('q') || '';
    if (val === cur) return;
    url.searchParams.delete('paged');
    if (val) url.searchParams.set('q', val); else url.searchParams.delete('q');
    swap(url.toString(), true, { scroll: false });
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(qTimer);
      var val = searchInput.value.trim();
      qTimer = setTimeout(function () {
        if (val.length === 1) return; // один символ — ждём продолжения
        applySearch(val);
      }, 350);
    });
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && searchInput.value !== '') {
        searchInput.value = '';
        clearTimeout(qTimer);
        applySearch('');
      }
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
