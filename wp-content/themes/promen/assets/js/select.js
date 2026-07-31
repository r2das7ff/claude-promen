/* ── ПЭ / SELECT — подменяющий выпадающий список ─────────────────────────
   Нативный <select> нельзя оформить: список рисует операционная система,
   поэтому в форме контактов он выпадал системным белым прямоугольником с
   синей подсветкой Windows — мимо всей палитры сайта.

   Механика: нативный элемент остаётся в DOM носителем значения (форма
   отправляется как раньше, обработчики change продолжают работать), но
   прячется от глаз и от табуляции; поверх него строится кнопка + список.
   Разметку в шаблоне менять не нужно — достаточно data-select у <select>.

   Доступность: кнопка получает role=combobox, список role=listbox, пункты
   role=option с aria-selected; клавиатура — стрелки, Home/End, Enter/Space,
   Esc, плюс поиск по первым буквам. Атрибут for у <label> переносится на
   кнопку, иначе клик по подписи фокусировал бы скрытый элемент.
   Стили — base.css (.pm-select*). ── */
(function () {
  'use strict';

  var TYPE_TIMEOUT = 700;

  function enhance(native) {
    if (native.dataset.pmSelect) return;
    native.dataset.pmSelect = '1';

    var id = native.id || ('pm-select-' + Math.random().toString(36).slice(2, 8));
    var wrap = document.createElement('div');
    wrap.className = 'pm-select';

    var trigger = document.createElement('button');
    trigger.type = 'button';
    // Классы нативного поля переносим на кнопку: страничные правила вроде
    // .par-sel написаны по классу, а не по тегу, поэтому кнопка получает тот
    // же вид (рамка, фон, кегль), что было у select. Базовые .pm-select-*
    // задают только раскладку и стрелку.
    trigger.className = 'pm-select-trigger' + (native.className ? ' ' + native.className : '');
    trigger.id = id + '-trigger';
    trigger.setAttribute('role', 'combobox');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-controls', id + '-menu');

    var value = document.createElement('span');
    value.className = 'pm-select-value';
    var arrow = document.createElement('span');
    arrow.className = 'pm-select-arrow';
    arrow.setAttribute('aria-hidden', 'true');
    trigger.appendChild(value);
    trigger.appendChild(arrow);

    var menu = document.createElement('div');
    menu.className = 'pm-select-menu';
    menu.id = id + '-menu';
    menu.setAttribute('role', 'listbox');

    var opts = Array.prototype.map.call(native.options, function (o, i) {
      var el = document.createElement('div');
      el.className = 'pm-select-opt';
      el.id = id + '-opt-' + i;
      el.setAttribute('role', 'option');
      el.textContent = o.text;
      el.dataset.index = i;
      // Индекс для лесенки появления пунктов (см. transition-delay в CSS).
      el.style.setProperty('--i', i);
      menu.appendChild(el);
      return el;
    });

    native.parentNode.insertBefore(wrap, native);
    wrap.appendChild(native);
    wrap.appendChild(trigger);
    wrap.appendChild(menu);
    native.classList.add('pm-select-native');
    native.tabIndex = -1;
    native.setAttribute('aria-hidden', 'true');

    var label = native.id ? document.querySelector('label[for="' + native.id + '"]') : null;
    if (label) {
      label.setAttribute('for', trigger.id);
      trigger.setAttribute('aria-labelledby', (label.id || (label.id = id + '-label')) + ' ' + trigger.id);
    }

    var open = false;
    var active = native.selectedIndex < 0 ? 0 : native.selectedIndex;
    var typed = '';
    var typeTimer = null;

    function paint() {
      value.textContent = native.options[native.selectedIndex] ? native.options[native.selectedIndex].text : '';
      opts.forEach(function (el, i) {
        var sel = i === native.selectedIndex;
        el.classList.toggle('is-selected', sel);
        el.setAttribute('aria-selected', sel ? 'true' : 'false');
        el.classList.toggle('is-active', open && i === active);
      });
      trigger.setAttribute('aria-activedescendant', open ? opts[active].id : '');
    }

    function setIndex(i, commit) {
      if (i < 0 || i >= opts.length) return;
      active = i;
      if (commit && native.selectedIndex !== i) {
        native.selectedIndex = i;
        native.dispatchEvent(new Event('change', { bubbles: true }));
      }
      paint();
      if (open) {
        var el = opts[active];
        var top = el.offsetTop, bottom = top + el.offsetHeight;
        if (top < menu.scrollTop) menu.scrollTop = top;
        else if (bottom > menu.scrollTop + menu.clientHeight) menu.scrollTop = bottom - menu.clientHeight;
      }
    }

    function setOpen(next) {
      if (open === next) return;
      open = next;
      wrap.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        active = native.selectedIndex < 0 ? 0 : native.selectedIndex;
        // Меню длиннее экрана снизу — раскрываем вверх.
        var r = trigger.getBoundingClientRect();
        wrap.classList.toggle('is-up', window.innerHeight - r.bottom < 240 && r.top > 240);
      }
      paint();
      if (open) setIndex(active, false);
    }

    trigger.addEventListener('click', function () { setOpen(!open); });

    trigger.addEventListener('keydown', function (e) {
      var k = e.key;
      if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'Enter' || k === ' ' || k === 'Spacebar') {
        e.preventDefault();
        if (!open) { setOpen(true); return; }
        if (k === 'ArrowDown') setIndex(active + 1, false);
        else if (k === 'ArrowUp') setIndex(active - 1, false);
        else { setIndex(active, true); setOpen(false); }
        return;
      }
      if (k === 'Home' || k === 'End') { e.preventDefault(); setIndex(k === 'Home' ? 0 : opts.length - 1, !open); return; }
      if (k === 'Escape' && open) { e.preventDefault(); setOpen(false); return; }
      if (k === 'Tab' && open) { setOpen(false); return; }
      // Поиск по первым буквам — как в нативном списке.
      if (k.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
        typed += k.toLowerCase();
        clearTimeout(typeTimer);
        typeTimer = setTimeout(function () { typed = ''; }, TYPE_TIMEOUT);
        for (var i = 0; i < opts.length; i++) {
          if (opts[i].textContent.toLowerCase().indexOf(typed) === 0) { setIndex(i, !open); break; }
        }
      }
    });

    menu.addEventListener('mousemove', function (e) {
      var el = e.target.closest('.pm-select-opt');
      if (el) setIndex(+el.dataset.index, false);
    });
    menu.addEventListener('click', function (e) {
      var el = e.target.closest('.pm-select-opt');
      if (!el) return;
      setIndex(+el.dataset.index, true);
      setOpen(false);
      trigger.focus();
    });

    document.addEventListener('click', function (e) {
      if (open && !wrap.contains(e.target)) setOpen(false);
    });
    document.addEventListener('focusin', function (e) {
      if (open && !wrap.contains(e.target)) setOpen(false);
    });
    window.addEventListener('resize', function () { if (open) setOpen(false); }, { passive: true });
    // Значение могли поменять снаружи (сброс формы, автозаполнение).
    native.addEventListener('change', function () { if (!open) paint(); });

    /* Программная установка value/selectedIndex события не поднимает, а так
       делает страничный код: product.js выставляет марку из ?steel=… уже
       после инициализации. Без перехвата кнопка показывала бы первую опцию,
       пока значение формы другое. Дескрипторы берём с прототипа, поведение
       не меняем — только перерисовываем подпись. */
    ['value', 'selectedIndex'].forEach(function (prop) {
      var proto = Object.getPrototypeOf(native);
      var desc = Object.getOwnPropertyDescriptor(proto, prop);
      if (!desc || !desc.set) return;
      Object.defineProperty(native, prop, {
        configurable: true,
        enumerable: desc.enumerable,
        get: function () { return desc.get.call(this); },
        set: function (v) { desc.set.call(this, v); paint(); }
      });
    });

    paint();
  }

  function init() {
    document.querySelectorAll('select[data-select]').forEach(enhance);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
