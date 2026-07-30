<?php
/**
 * Секция 10 — база знаний «Изоляция и покрытия» (7 табов).
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ИЗОЛЯЦИЯ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / ИЗОЛЯЦИЯ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды изделий</button>
      <button class="kb-tab" data-panel="params" role="tab"><span class="kb-tab-n">02</span>Параметры подбора</button>
      <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
      <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
      <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
      <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Как заказать</button>
      <button class="kb-tab" data-panel="delivery" role="tab"><span class="kb-tab-n">07</span>Доставка и оплата</button>
    </div>

    <div class="kb-panels">

      <div class="kb-panel kp-active" id="kp-types">
        <div class="kb-lead">
          <div class="kb-lead-h">Фасонные изделия в ППУ-изоляции</div>
          <p class="kb-lead-p">В каталоге изоляции — <strong>тройники ППУ</strong> по ГОСТ 30732-2020, <strong>72 типоразмера</strong>: оболочка <strong>ПЭ</strong> (37) и <strong>ОЦ</strong> (35). Для прямых участков в ППУ см. раздел «Трубы».</p>
        </div>
        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ПЭ · 37</div>
            <div class="kb-card-title">Тройник ППУ · оболочка полиэтиленовая</div>
            <p class="kb-card-body">Для бесканальной прокладки тепловых сетей. Стальной тройник + пенополиуретан + оболочка ПЭ. D основного ряда 530–1020 мм.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 30732-2020</span><span class="kb-tag">ПЭ</span><span class="kb-tag">37 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ОЦ · 35</div>
            <div class="kb-card-title">Тройник ППУ · оболочка оцинкованная</div>
            <p class="kb-card-body">Для канальной и надземной прокладки. Стальной тройник + ППУ + оцинкованная оболочка. Тот же сортамент D 530–1020.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 30732-2020</span><span class="kb-tag">ОЦ</span><span class="kb-tag">35 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ТР · ППУ</div>
            <div class="kb-card-title">Прямые трубы в ППУ</div>
            <p class="kb-card-body">Предизолированные трубы (не фасонные) — в разделе «Трубы», тип ППУ, <strong>441 позиция</strong> по ГОСТ 30732-2020.</p>
            <div class="kb-card-tags"><span class="kb-tag">Трубы</span><span class="kb-tag">441 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ОДК</div>
            <div class="kb-card-title">Система контроля изоляции</div>
            <p class="kb-card-body">Оперативный дистанционный контроль влажности изоляции — по проекту теплосети. Указывается при заказе комплекта.</p>
            <div class="kb-card-tags"><span class="kb-tag">ОДК</span><span class="kb-tag">Проект</span></div>
          </div>
        </div>
        <div class="kb-groups-hd">Сравнение исполнений</div>
        <div class="kb-groups">
          <div class="kb-grp"><span class="kb-grp-code">ПЭ</span><span class="kb-grp-name">Полиэтилен · 37 поз.</span><span class="kb-grp-items">Бесканальная · ГОСТ 30732</span></div>
          <div class="kb-grp"><span class="kb-grp-code">ОЦ</span><span class="kb-grp-name">Оцинковка · 35 поз.</span><span class="kb-grp-items">Канальная / надзем · ГОСТ 30732</span></div>
        </div>
      </div>

      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт изделие в ППУ</div>
          <p class="kb-lead-p">Типоразмер тройника — <strong>D×s основного</strong> и <strong>d×s ответвления</strong>, тип оболочки (ПЭ/ОЦ) и требования проекта к ОДК.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на изоляцию</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Размеры тройника</div><div class="kb-check-body">D×s × d×s (основной × ответвление). Пример: 530×10 – 273×8.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Оболочка</div><div class="kb-check-body"><strong>ПЭ</strong> — бесканальная; <strong>ОЦ</strong> — канальная / надземная.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Норматив</div><div class="kb-check-body">ГОСТ 30732-2020 (+ требования СП / проекта сети).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">ОДК</div><div class="kb-check-body">Нужна ли система оперативного дистанционного контроля — по проекту.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Число изделий, дата поставки, адрес объекта.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">D×s</div><div class="kb-param-val">Основной проход: наружный диаметр × стенка стальной трубы.</div></div>
              <div class="kb-param"><div class="kb-param-key">d×s</div><div class="kb-param-val">Ответвление тройника — диаметр × стенка.</div></div>
              <div class="kb-param"><div class="kb-param-key">Оболочка</div><div class="kb-param-val"><strong>ПЭ</strong> или <strong>ОЦ</strong> — по типу прокладки теплосети.</div></div>
              <div class="kb-param"><div class="kb-param-key">ППУ</div><div class="kb-param-val">Толщина изоляции — по ГОСТ 30732 / проекту (стандарт / усиленная).</div></div>
              <div class="kb-param"><div class="kb-param-key">ОДК</div><div class="kb-param-val">Проводники контроля влажности — комплектность по КД.</div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Нормативная база изоляции в каталоге опирается на <strong>ГОСТ 30732-2020</strong> и правила проектирования тепловых сетей.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Основной стандарт</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30732-2020</span><span class="kb-norm-desc">Трубы и фасонные изделия стальные с тепловой изоляцией из пенополиуретана с защитной оболочкой. Технические условия (EN 253:2015 / EN 448:2016, NEQ). Взамен ГОСТ 30732-2006</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30732-2020, разд. 5</span><span class="kb-norm-desc">Защитная оболочка: полиэтиленовая (ПЭ) для подземной бесканальной прокладки, оцинкованная (ОЦ) для надземной — указывается в обозначении изделия</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Проектирование</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">СП 124.13330</span><span class="kb-norm-desc">Тепловые сети — тип прокладки и требования к предизолированным изделиям</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Общие</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">К стальной части при PN &gt; 0,05 МПа</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия — комплектность и контроль</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">Изделие состоит из <strong>стальной фасонной детали</strong>, слоя <strong>пенополиуретана</strong> и <strong>защитной оболочки</strong> (ПЭ или ОЦ). Сталь — по проекту / сертификату 3.1; изоляция и оболочка — по ГОСТ 30732.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Сталь</div>
            <div class="kb-mat-std">По ГОСТ / проекту сети</div>
            <div class="kb-mat-range">Сертификат 3.1</div>
            <div class="kb-mat-apps">Несущая часть тройника · прослеживаемость плавки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">ППУ</div>
            <div class="kb-mat-std">ГОСТ 30732-2020</div>
            <div class="kb-mat-range">Тепловая изоляция</div>
            <div class="kb-mat-apps">Заполнение между трубой и оболочкой · толщина по стандарту/проекту</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">ПЭ</div>
            <div class="kb-mat-std">Оболочка полиэтиленовая</div>
            <div class="kb-mat-range">Бесканальная</div>
            <div class="kb-mat-apps">Подземная бесканальная прокладка теплосети</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">ОЦ</div>
            <div class="kb-mat-std">Оболочка оцинкованная</div>
            <div class="kb-mat-range">Канальная / надзем</div>
            <div class="kb-mat-apps">Каналы, эстакады, надземные участки</div>
          </div>
        </div>
      </div>


<!-- TAB 5: ДОКУМЕНТАЦИЯ -->
      <div class="kb-panel" id="kp-docs">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Стандартный комплект поставки</div>
            <div class="kb-doclist">
              <div class="kb-doc-item">
                <div class="kb-doc-name">Паспорт предизолированного изделия</div>
                <div class="kb-doc-desc">По ГОСТ 30732-2020: стальная часть, ППУ, оболочка, маркировка, результаты приёмочного контроля.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл 3.1</div>
                <div class="kb-doc-desc">Плавочные данные стальной фасонной детали (ГОСТ ISO 10474-2016).</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК</div>
                <div class="kb-doc-desc">Геометрия тройника, целостность оболочки, качество изоляции в зоне торцов.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Декларация ТР ТС 032/2013</div>
                <div class="kb-doc-desc">Для стальной части при PN&nbsp;&gt;&nbsp;0.05&nbsp;МПа — RU С-RU.АБ53.В.08323/23.</div>
              </div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">По проекту теплосети</div>
            <div class="kb-doclist">
              <div class="kb-doc-item">
                <div class="kb-doc-name">Система ОДК</div>
                <div class="kb-doc-desc">Комплектность проводников контроля влажности — если предусмотрено проектом.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Совместимость с трубами ППУ</div>
                <div class="kb-doc-desc">Фасонные изделия согласуются с прямыми плетями того же ГОСТ 30732 (раздел «Трубы»).</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Поставка тройников ППУ в комплекте с трубами в ППУ и СДТ одного контура теплосети — единая ведомость и сроки.</p>
          </div>
        </div>
      </div>

      <!-- TAB 6: ЗАКАЗ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип изделия</div><div class="kb-check-body">Тройник ППУ, оболочка <strong>ПЭ</strong> или <strong>ОЦ</strong>, ГОСТ 30732-2020.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">D×s × d×s</div><div class="kb-check-body">Основной и ответвление с толщинами. Пример: 530×10 – 273×8.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Тип прокладки</div><div class="kb-check-body">Бесканальная (ПЭ) / канальная или надземная (ОЦ).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">ОДК</div><div class="kb-check-body">Нужна ли система контроля влажности — по проекту.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Число изделий, дата, объект.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Диаметр и перепад</div><div class="kb-factor-note">Крупный D и большое ответвление — больше масса и объём изоляции.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип оболочки</div><div class="kb-factor-note">ОЦ и ПЭ — разная комплектация под тип прокладки.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">ОДК</div><div class="kb-factor-note">Система контроля добавляет комплектацию и проверку.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Партия</div><div class="kb-factor-note">Серия тройников одного ряда снижает удельную подготовку.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать оболочку</div><div class="kb-err-note">ПЭ и ОЦ — разные исполнения. Без оболочки заявка неполная.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Только один диаметр</div><div class="kb-err-note">Тройник задаётся основным и ответвлением.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать с трубами ППУ</div><div class="kb-err-note">Прямые плети — в разделе «Трубы»; здесь — фасонные.</div></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 7: ДОСТАВКА -->
      <div class="kb-panel" id="kp-delivery">
        <div class="kb-lead">
          <div class="kb-lead-h">Доставка и оплата</div>
          <p class="kb-lead-p">Отгрузка после ОТК и комплектования документов. Логистику считаем в КП — укажите объект или город.</p>
        </div>
        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ДОСТАВКА</div>
            <div class="kb-card-title">ТК по всей России</div>
            <p class="kb-card-body">Бережная перевозка изделий с оболочкой ПЭ/ОЦ. Негабарит и спецсхемы — по согласованию.</p>
            <div class="kb-card-tags"><span class="kb-tag">ТК по выбору</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">САМОВЫВОЗ</div>
            <div class="kb-card-title">Склад в Челябинске</div>
            <p class="kb-card-body">454091, г. Челябинск, ул. Орджоникидзе, 37. Пн–Пт 09:00–18:00 МСК. Погрузка заводом.</p>
            <div class="kb-card-tags"><span class="kb-tag">Пн–Пт 09:00–18:00</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">УПАКОВКА</div>
            <div class="kb-card-title">Защита оболочки</div>
            <p class="kb-card-body">Защита торцов изоляции и оболочки, маркировка позиций, документы с грузом и по email.</p>
            <div class="kb-card-tags"><span class="kb-tag">Паспорт · 3.1</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ОПЛАТА</div>
            <div class="kb-card-title">Б/н с НДС</div>
            <p class="kb-card-body">Счёт по КП. Аванс / доплата или график по договору.</p>
            <div class="kb-card-tags"><span class="kb-tag">По договору</span></div>
          </div>
        </div>
      </div>

    </div><!-- /kb-panels -->
  </section>
