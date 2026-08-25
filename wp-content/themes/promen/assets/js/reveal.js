/**
 * Появление контента — общий модуль для внутренних страниц.
 *
 * На главной такая механика была зашита в front.js инлайновыми стилями и
 * переиспользовать её было нельзя: каталог, проекты, нормативы, калькуляторы,
 * статьи и контакты стояли вовсе без появления.
 *
 * Разметка:
 *   data-reveal        — элемент появляется сам по себе;
 *   data-reveal-group  — появляются прямые дети, по очереди.
 *
 * Принцип: контент НИКОГДА не прячется заранее. Наблюдатель сообщает, что
 * элемент вошёл в кадр, и только тогда на нём проигрывается анимация входа.
 * Первый экран получает её сразу при загрузке — это и есть его появление.
 * Не отработал скрипт, заморожена вкладка, не сработал наблюдатель — страница
 * просто статична и полностью читаема. Скрытого контента не существует.
 */
(function () {
  'use strict';

  if (!('IntersectionObserver' in window)) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

  /* Шаг стаггера — из шкалы движения: ритм правится в base.css вместе со
     всей шкалой, а не числом здесь. */
  function stagger() {
    var raw = getComputedStyle(document.documentElement).getPropertyValue('--stagger').trim();
    var n = parseFloat(raw) || 70;
    return /ms$/.test(raw) || !/s$/.test(raw) ? n : n * 1000;
  }

  /* Длинный список не должен доигрывать полминуты. */
  var MAX_DELAY = 420;
  var step = 70;

  function play(el, delay) {
    if (el.getAttribute('data-reveal-played') !== null) return;
    el.setAttribute('data-reveal-played', '');
    el.style.animationDelay = delay ? delay + 'ms' : '';
    el.classList.add('reveal-anim');
    /* Класс снимаем после проигрывания: иначе он мешал бы собственным
       анимациям элемента — фильтрации карточек, ховерам. */
    setTimeout(function () {
      el.classList.remove('reveal-anim');
      el.style.animationDelay = '';
    }, 1000 + (delay || 0));
  }

  function targets() {
    var out = [];
    Array.prototype.forEach.call(document.querySelectorAll('[data-reveal]'), function (el) {
      out.push(el);
    });
    Array.prototype.forEach.call(document.querySelectorAll('[data-reveal-group]'), function (g) {
      Array.prototype.forEach.call(g.children, function (kid) { out.push(kid); });
    });
    return out;
  }

  var io = null;
  /* Соседи, въезжающие в кадр одним движением, идут чередой, а не вспыхивают
     разом: задержка растёт по порядку входа и сбрасывается, когда поток
     прервался. */
  var queue = 0, queueTimer = null;

  function watch(el) {
    if (el.getAttribute('data-reveal-watched') !== null) return;
    el.setAttribute('data-reveal-watched', '');
    io.observe(el);
  }

  function init() {
    step = stagger();

    io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        play(e.target, reduce.matches ? 0 : Math.min(queue * step, MAX_DELAY));
        queue++;
        if (queueTimer) clearTimeout(queueTimer);
        queueTimer = setTimeout(function () { queue = 0; }, 260);
      });
      /* Запускаем заранее, за нижней границей экрана: анимация успевает
         пройти к моменту, когда элемент реально попадает в поле зрения.
         С отрицательным полем всё было наоборот — карточка сначала
         въезжала, и только потом начинала проявляться, отсюда рывок. */
    }, { threshold: 0, rootMargin: '0px 0px 12% 0px' });

    targets().forEach(watch);

    /* Списки статей, нормативов и каталога рисует JS уже после запуска
       модуля — берём их на наблюдение по мере появления. */
    if ('MutationObserver' in window) {
      var mo = new MutationObserver(function (records) {
        records.forEach(function (rec) {
          Array.prototype.forEach.call(rec.addedNodes, function (node) {
            if (node.nodeType === 1) watch(node);
          });
        });
      });
      Array.prototype.forEach.call(
        document.querySelectorAll('[data-reveal-group]'),
        function (g) { mo.observe(g, { childList: true }); }
      );
    }
  }

  init();
})();
