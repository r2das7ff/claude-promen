<?php
/**
 * Секция 10 — база знаний «Арматура» (7 табов).
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="s kb-wrap" id="s10">
  <div class="s-hd">
    <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
    <div class="s-meta">АРМАТУРА</div>
  </div>
  <div class="kb-tabrow" role="tablist">
    <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды</button>
    <button class="kb-tab" data-panel="params" role="tab"><span class="kb-tab-n">02</span>Параметры подбора</button>
    <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
    <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
    <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
    <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Как заказать</button>
    <button class="kb-tab" data-panel="delivery" role="tab"><span class="kb-tab-n">07</span>Доставка и оплата</button>
  </div>
  <div class="kb-panels">
    <div class="kb-panel kp-active" id="kp-types">
      <div class="kb-lead"><div class="kb-lead-h">Запорно-регулирующая арматура</div><p class="kb-lead-p">Пилотная витрина: <strong>задвижки клиновые</strong>, <strong>клапаны обратные</strong> и <strong>краны шаровые</strong> по ГОСТ 33257-2015. DN от 15, PN до 10 МПа.</p></div>
      <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ЗД</div>
            <div class="kb-card-title">Задвижки клиновые</div>
            <p class="kb-card-body">Стальные клиновые с невыдвижным шпинделем. DN 50–1000, PN 1.6–6.3.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 33257-2015</span><span class="kb-tag">ЗД</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">КО</div>
            <div class="kb-card-title">Клапаны обратные</div>
            <p class="kb-card-body">Подъёмные стальные. DN 15–200.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 33257-2015</span><span class="kb-tag">КО</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">КР</div>
            <div class="kb-card-title">Краны шаровые</div>
            <p class="kb-card-body">Полнопроходные. DN 15–500, PN до 10 МПа.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 33257-2015</span><span class="kb-tag">КР</span></div>
          </div>
      </div>
    </div>
    <div class="kb-panel" id="kp-params">
      <div class="kb-2col">
        <div><div class="kb-col-title">Чеклист заявки на арматуру</div><div class="kb-checklist"><div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип</div><div class="kb-check-body">Задвижка / клапан / кран</div></div></div><div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN и PN</div><div class="kb-check-body">Условный проход и давление</div></div></div><div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Среда и t°</div><div class="kb-check-body">Рабочая среда и температура</div></div></div><div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Привод</div><div class="kb-check-body">Ручной / электро / пневмо — по проекту</div></div></div><div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Количество</div><div class="kb-check-body">Штуки и срок</div></div></div></div></div>
        <div><div class="kb-col-title">Ключевые параметры</div><div class="kb-params"><div class="kb-param"><div class="kb-param-key">Тип</div><div class="kb-param-val">ЗД / КО / КР</div></div><div class="kb-param"><div class="kb-param-key">DN</div><div class="kb-param-val">Условный проход</div></div><div class="kb-param"><div class="kb-param-key">PN</div><div class="kb-param-val">Рабочее давление, МПа</div></div><div class="kb-param"><div class="kb-param-key">Среда</div><div class="kb-param-val">Вода, пар, нефть, газ…</div></div></div></div>
      </div>
    </div>
    <div class="kb-panel" id="kp-norms">
      <p class="kb-intro-p">Помимо стандартов на конструкцию и испытания арматуры, в спецификациях объектов почти всегда фигурируют два блока общетехнических требований: <strong>климатическое исполнение</strong> по ГОСТ 15150-69 и <strong>сейсмостойкость</strong> по серии ГОСТ 30546. Их указывают в заявке вместе с DN, PN и средой.</p>
      <div class="kb-norm-grid">
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">Конструкция и испытания</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33257-2015</span><span class="kb-norm-desc">Арматура трубопроводная. Методы контроля и испытаний</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 9544-2015</span><span class="kb-norm-desc">Арматура трубопроводная. Нормы герметичности затворов</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33259-2015</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов на номинальное давление до PN 250</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 34655-2020</span><span class="kb-norm-desc">Прокладки овального, восьмиугольного сечения и линзовые стальные для фланцев арматуры</span></div>
          </div>
        </div>
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">Условия эксплуатации и сейсмостойкость</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 15150-69</span><span class="kb-norm-desc">Исполнения для различных климатических районов. Категории, условия эксплуатации, хранения и транспортирования — исполнения У, УХЛ, Т, О и категории размещения 1–5</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30546.1-98</span><span class="kb-norm-desc">Общие требования к техническим изделиям и методы расчёта их сложных конструкций в части сейсмостойкости</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30546.2-98</span><span class="kb-norm-desc">Испытания на сейсмостойкость машин, приборов и других технических изделий. Общие положения и методы испытаний</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30546.3-98</span><span class="kb-norm-desc">Методы определения сейсмостойкости изделий, установленных на месте эксплуатации, при аттестации или сертификации на сейсмическую безопасность</span></div>
          </div>
        </div>
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">АЭС и обязательные нормы</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">НП-068-05</span><span class="kb-norm-desc">Трубопроводная арматура для атомных станций. Общие технические требования</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">НП-089-15</span><span class="kb-norm-desc">Общие требования к оборудованию и трубопроводам АЭУ. Категории трубопроводов I–IV</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением. Обязателен при PN &gt; 0,05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия — комплектность поставки и объём контроля</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="kb-panel" id="kp-materials">
      <div class="kb-mat-grid"><div class="kb-mat"><div class="kb-mat-grade">Ст20</div><div class="kb-mat-std">ГОСТ 1050-2013</div><div class="kb-mat-range">Основная марка</div><div class="kb-mat-apps">Корпус и крышка</div></div><div class="kb-mat"><div class="kb-mat-grade">09Г2С</div><div class="kb-mat-std">ГОСТ 19281-2014</div><div class="kb-mat-range">Хладостойкость</div><div class="kb-mat-apps">По проекту</div></div><div class="kb-mat"><div class="kb-mat-grade">12Х18Н10Т</div><div class="kb-mat-std">ГОСТ 5632-2014</div><div class="kb-mat-range">Коррозия</div><div class="kb-mat-apps">Агрессивные среды</div></div></div>
    </div>
    <div class="kb-panel" id="kp-docs">
      <div class="kb-2col">
        <div>
          <div class="kb-col-title">Стандартный комплект поставки</div>
          <div class="kb-doclist">
            <div class="kb-doc-item"><div class="kb-doc-name">Паспорт изделия</div><div class="kb-doc-desc">Сертификат качества 3.1, маркировка, ссылка на норматив.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Сертификат на металл</div><div class="kb-doc-desc">Плавочные данные по ГОСТ ISO 10474-2016.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Протокол ВИК</div><div class="kb-doc-desc">Геометрия и качество поверхности / сборки.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Декларация ТР ТС 032/2013</div><div class="kb-doc-desc">При работе под избыточным давлением свыше 0,05 МПа.</div></div>
          </div>
        </div>
        <div>
          <div class="kb-col-title">Комплексные поставки</div>
          <p class="kb-col-sub">Поставка в комплекте с трубами, СДТ и фланцами одного контура — единая ведомость и сроки.</p>
        </div>
      </div>
    </div>
    <div class="kb-panel" id="kp-order">
      <div class="kb-3col">
        <div><div class="kb-col-title">Чеклист заявки на арматуру</div><div class="kb-checklist"><div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип</div><div class="kb-check-body">Задвижка / клапан / кран</div></div></div><div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN и PN</div><div class="kb-check-body">Условный проход и давление</div></div></div><div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Среда и t°</div><div class="kb-check-body">Рабочая среда и температура</div></div></div><div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Привод</div><div class="kb-check-body">Ручной / электро / пневмо — по проекту</div></div></div><div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Количество</div><div class="kb-check-body">Штуки и срок</div></div></div></div></div>
        <div>
          <div class="kb-col-title">Что влияет на стоимость</div>
          <div class="kb-factors">
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN / нагрузка</div><div class="kb-factor-note">Крупный диаметр и расчётные нагрузки увеличивают массу и комплектацию.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Исполнение</div><div class="kb-factor-note">Спецпокрытия, приводы, пружинные блоки — по проекту.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Партия</div><div class="kb-factor-note">Серийный заказ снижает удельную подготовку.</div></div></div>
          </div>
        </div>
        <div><div class="kb-col-title">Частые ошибки</div><div class="kb-errors"><div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать PN и Ру</div><div class="kb-err-note">Указывайте единицы и стандарт ряда</div></div></div><div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Без среды</div><div class="kb-err-note">От среды зависят уплотнения и материал</div></div></div></div></div>
      </div>
    </div>
    <div class="kb-panel" id="kp-delivery">
      <div class="kb-lead"><div class="kb-lead-h">Доставка и оплата</div><p class="kb-lead-p">Отгрузка после ОТК. Логистику считаем в КП — укажите объект или город.</p></div>
      <div class="kb-cards">
        <div class="kb-card"><div class="kb-card-badge">ДОСТАВКА</div><div class="kb-card-title">ТК по всей России</div><p class="kb-card-body">Отгрузка транспортной компанией по выбору заказчика.</p></div>
        <div class="kb-card"><div class="kb-card-badge">САМОВЫВОЗ</div><div class="kb-card-title">Челябинск</div><p class="kb-card-body">454091, ул. Орджоникидзе, 37. Пн–Пт 09:00–18:00 МСК.</p></div>
        <div class="kb-card"><div class="kb-card-badge">ОПЛАТА</div><div class="kb-card-title">Б/н с НДС</div><p class="kb-card-body">Счёт по КП. Аванс / доплата или график по договору.</p></div>
      </div>
    </div>
  </div>
</section>
