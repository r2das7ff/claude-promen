/**
 * Аудит контраста текста на тёмных поверхностях (правило brand-spec.md
 * «Текст на тёмных поверхностях»).
 *
 * Как запускать: открыть страницу, прокрутить её до конца (анимации появления
 * держат opacity:0), вставить файл целиком в консоль браузера. Печатает
 * CSS-правила темы, у которых текст на тёмном фоне ниже 7:1:
 *   контраст | файл | селектор | цвет из правила | фон | пример текста
 * Ниже 4.5:1 — чинить обязательно; 4.5–7 — допустимо только для --g2 на --blue.
 *
 * Скрипт идёт по правилам, а не по видимым элементам: ловит скрытые состояния
 * (раскрывашки, инфоблоки видео, подсказки) и ::before/::after с текстом.
 * Фон берётся по первому непрозрачному предку; градиенты не учитываются.
 * Декор (штамп .ft-idx, водяные цифры .stg-num, пустые линии ::before) в
 * выдаче останется — он разрешён правилом, его пропускать глазами.
 */
(() => {
  const parse = c => { const m = c && c.match(/rgba?\(([^)]+)\)/); if (!m) return null; const p = m[1].split(/[ ,\/]+/).filter(Boolean).map(parseFloat); return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 }; };
  const lin = v => { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); };
  const lum = c => .2126 * lin(c.r) + .7152 * lin(c.g) + .0722 * lin(c.b);
  const blend = (f, b) => ({ r: f.r * f.a + b.r * (1 - f.a), g: f.g * f.a + b.g * (1 - f.a), b: f.b * f.a + b.b * (1 - f.a), a: 1 });
  const hex = c => '#' + [c.r, c.g, c.b].map(v => Math.round(v).toString(16).padStart(2, '0')).join('');
  function bgOf(el) {
    const layers = []; let e = el;
    while (e && e.nodeType === 1) { const c = parse(getComputedStyle(e).backgroundColor); if (c && c.a > 0) { layers.push(c); if (c.a >= 1) break; } e = e.parentElement; }
    let base = { r: 232, g: 236, b: 240, a: 1 };
    for (let i = layers.length - 1; i >= 0; i--) base = blend(layers[i], base);
    return base;
  }
  function anc(el) { let o = 1, e = el.parentElement; while (e && e.nodeType === 1) { const v = parseFloat(getComputedStyle(e).opacity); if (v > 0) o *= v; e = e.parentElement; } return o; }
  const res = {};
  const sheets = [...document.styleSheets].filter(s => s.href && s.href.includes('/themes/promen/'));
  const walk = (rules, file) => {
    for (const r of rules) {
      if (r.cssRules && !r.selectorText) { walk(r.cssRules, file); continue; }
      if (!r.selectorText || !r.style) continue;
      const hasColor = r.style.color || r.style.opacity || r.style.fill;
      if (!hasColor) continue;
      for (const raw of r.selectorText.split(',')) {
        const pe = (raw.match(/::?(before|after)\s*$/) || [])[1];
        const sel = raw.replace(/::?(before|after)\s*$/, '').replace(/:(hover|focus|focus-visible|focus-within|active|visited)\b/g, '').trim();
        let els; try { els = document.querySelectorAll(sel || '*:not(*)'); } catch (e) { continue; }
        for (const el of els) {
          let text, cs;
          if (pe) { cs = getComputedStyle(el, '::' + pe); const c = cs.content; if (!c || c === 'none' || c === 'normal' || !/["']\s*\S/.test(c)) continue; text = c.slice(1, 30); }
          else { cs = getComputedStyle(el); text = [...el.childNodes].filter(n => n.nodeType === 3 && n.textContent.trim()).map(n => n.textContent.trim()).join(' '); if (!text) continue; }
          const bg = bgOf(el); if (lum(bg) > .1) continue;
          let fg = parse(el instanceof SVGElement ? cs.fill : cs.color); if (!fg) continue;
          const own = parseFloat(cs.opacity);
          fg = { ...fg, a: fg.a * (own > 0 ? own : 1) * anc(el) };
          const eff = blend(fg, bg);
          const ratio = (Math.max(lum(eff), lum(bg)) + .05) / (Math.min(lum(eff), lum(bg)) + .05);
          if (ratio >= 7) continue;
          const key = file + ' | ' + raw.trim();
          if (!res[key] || res[key].ratio > ratio) res[key] = { ratio: +ratio.toFixed(2), color: (el instanceof SVGElement ? 'fill ' : '') + (r.style.color || r.style.fill || '') + (r.style.opacity ? ' op' + r.style.opacity : ''), bg: hex(bg), text: text.slice(0, 32) };
        }
      }
    }
  };
  for (const s of sheets) { try { walk(s.cssRules, s.href.split('/').pop().split('?')[0]); } catch (e) {} }
  return location.pathname + '\n' + Object.entries(res).sort((a, b) => a[0].localeCompare(b[0]) || a[1].ratio - b[1].ratio).map(([k, v]) => `${v.ratio}\t${k}\t${v.color}\ton ${v.bg}\t«${v.text}»`).join('\n');
})()
