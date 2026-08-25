<?php
/**
 * Секция 10 — база знаний «Тройники» (7 табов).
 * Структура — по образцу kb-otvody; факты — content/aggregates.json,
 * content/norm_aggregates.json, ГОСТ 17376-2001, ГОСТ 22801-83/22822-83.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ТРОЙНИКИ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">ТРОЙНИКИ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды тройников</button>
      <button class="kb-tab" data-panel="params" role="tab"><span class="kb-tab-n">02</span>Параметры подбора</button>
      <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
      <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
      <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
      <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Как заказать</button>
      <button class="kb-tab" data-panel="delivery" role="tab"><span class="kb-tab-n">07</span>Доставка и оплата</button>
    </div>

    <div class="kb-panels">

      <!-- TAB 1: ВИДЫ -->
      <div class="kb-panel kp-active" id="kp-types">
        <div class="kb-lead">
          <div class="kb-lead-h">Классификация тройников</div>
          <p class="kb-lead-p">Тройник разветвляет поток или врезает ответвление в магистраль. Конструктивное исполнение выбирается по соотношению диаметров ответвления и магистрали, рабочему давлению и нормативу объекта. В каталоге — <strong>четыре типоисполнения</strong>, 491 типоразмер по восьми сериям.</p>
        </div>
        <div class="kb-type-grid">
          <div class="kb-type">
            <div class="kb-type-badge">ГОСТ 17376-2001</div>
            <div class="kb-type-title">Штампованные бесшовные · DN 15–600</div>
            <p class="kb-type-body">Бесшовные приварные тройники из углеродистой и низколегированной стали (ГОСТ 17376-2001, ИСО 3419-81). <strong>Равнопроходные (d = D)</strong> и <strong>переходные (d &lt; D)</strong>. Основной тип для общепромышленных трубопроводов и трубопроводов ТЭС.</p>
            <div class="kb-type-tags"><span class="kb-tag">ГОСТ 17376-2001</span><span class="kb-tag">DN 15–600</span><span class="kb-tag">96 позиций</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">ГОСТ 22801-83/22822-83</div>
            <div class="kb-type-title">На Ру до 100 МПа · DN 6–200</div>
            <p class="kb-type-body">Детали трубопроводов высокого давления (свыше 10 до 100 МПа): ГОСТ 22801-83 — тройники, ГОСТ 22822-83 — <strong>тройники с опорой</strong>. Применяются на поднадзорных объектах; поставка с расширенным объёмом контроля.</p>
            <div class="kb-type-tags"><span class="kb-tag">Ру до 100 МПа</span><span class="kb-tag">DN 6–200</span><span class="kb-tag">222 позиции</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">ОСТ 34-10</div>
            <div class="kb-type-title">Сварные для энергетики · DN 65–1600</div>
            <p class="kb-type-body">Сварные тройники для трубопроводов ТЭС по ОСТ 34-10-762…765-97: равнопроходные и переходные, крупные диаметры до DN 1600. Контроль сварных швов — ВИК / УЗК / РК по объёму норматива и требованиям заказчика.</p>
            <div class="kb-type-tags"><span class="kb-tag">ОСТ 34-10-762…765</span><span class="kb-tag">DN 65–1600</span><span class="kb-tag">140 позиций</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">СЕРИЯ 4.903-10</div>
            <div class="kb-type-title">Для тепловых сетей · DN до 950</div>
            <p class="kb-type-body">Тройники по типовой серии 4.903-10 «Изделия и детали трубопроводов тепловых сетей». Применяются в городских теплосетях, котельных и системах ЖКХ.</p>
            <div class="kb-type-tags"><span class="kb-tag">СЕРИЯ 4.903-10</span><span class="kb-tag">Теплосети</span><span class="kb-tag">33 позиции</span></div>
          </div>
        </div>
        <div class="kb-compare">
          <div class="kb-compare-hd">
            <span class="kb-cmp-h">Параметр</span>
            <span class="kb-cmp-h">Штампованные (17376)</span>
            <span class="kb-cmp-h">Ру 100 (22801/22822)</span>
            <span class="kb-cmp-h">Сварные (ОСТ 34-10)</span>
            <span class="kb-cmp-h">Теплосети (4.903-10)</span>
          </div>
          <div class="kb-compare-row"><span class="kb-cmp-k">DN</span><span class="kb-cmp-v">15–600</span><span class="kb-cmp-v">6–200</span><span class="kb-cmp-v">65–1600</span><span class="kb-cmp-v">до 950</span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Давление</span><span class="kb-cmp-v">по стенке и стали</span><span class="kb-cmp-v"><strong>до 100 МПа</strong></span><span class="kb-cmp-v">по проекту ТЭС</span><span class="kb-cmp-v">теплосети</span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Метод</span><span class="kb-cmp-v">Штамповка, бесшовные</span><span class="kb-cmp-v">Ковка / мехобработка</span><span class="kb-cmp-v">Сварка</span><span class="kb-cmp-v">По серии</span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Применение</span><span class="kb-cmp-v">Общепром., НГК, ТЭС</span><span class="kb-cmp-v">Поднадзорные объекты</span><span class="kb-cmp-v">ТЭС / ГРЭС, крупный DN</span><span class="kb-cmp-v">ЖКХ, котельные</span></div>
        </div>
      </div>

      <!-- TAB 2: ПАРАМЕТРЫ ПОДБОРА -->
      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт тройник</div>
          <p class="kb-lead-p">Типоразмер тройника определяется <strong>двумя парами размеров</strong>: наружный диаметр × стенка магистрали (D×s) и ответвления (d×s₁). У равнопроходных d = D, у переходных d &lt; D.</p>
        </div>
        <div class="kb-cols">
          <div class="kb-col">
            <div class="kb-col-title">Чеклист заявки на тройник</div>
            <div class="kb-check"><span class="kb-check-n">01</span>DN и PN магистрали (или D×s, напр. 219×8)</div>
            <div class="kb-check"><span class="kb-check-n">02</span>DN ответвления (d×s₁) — или «равнопроходный»</div>
            <div class="kb-check"><span class="kb-check-n">03</span>Норматив объекта: ГОСТ 17376-2001 / Ру 100 / ОСТ 34-10</div>
            <div class="kb-check"><span class="kb-check-n">04</span>Марка стали и рабочая среда (t, °C)</div>
            <div class="kb-check"><span class="kb-check-n">05</span>Поднадзорность (ТР ТС 032/2013) и объём НК</div>
            <div class="kb-check"><span class="kb-check-n">06</span>Количество и срок поставки</div>
          </div>
          <div class="kb-col">
            <div class="kb-col-title">Ключевые параметры тройника</div>
            <div class="kb-param"><span class="kb-param-k">D×s</span><span class="kb-param-v">магистраль: наружный диаметр × стенка</span></div>
            <div class="kb-param"><span class="kb-param-k">d×s₁</span><span class="kb-param-v">ответвление: диаметр × стенка</span></div>
            <div class="kb-param"><span class="kb-param-k">H / h</span><span class="kb-param-v">высоты по осям — по таблице стандарта</span></div>
            <div class="kb-param"><span class="kb-param-k">Масса</span><span class="kb-param-v">по стандарту изготовления, уточняется по КД</span></div>
          </div>
        </div>
      </div>

      <!-- TAB 3: НОРМАТИВНАЯ БАЗА -->
      <div class="kb-panel" id="kp-norms">
        <div class="kb-lead">
          <div class="kb-lead-h">Нормативная база тройников</div>
          <p class="kb-lead-p">Выбор норматива определяет конструкцию, допуски и объём контроля. Все серии каталога — действующие документы: базовый ГОСТ на бесшовные приварные тройники, ГОСТ на исполнения высокого давления, отраслевые ОСТ для ТЭС и АС и стандарты ЦКТИ для паропроводов.</p>
        </div>
        <div class="kb-norm-list">
          <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17376-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные из углеродистой и низколегированной стали. Тройники. Конструкция (ИСО 3419-81). Равнопроходные и переходные — основной стандарт</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17380-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные. Общие технические условия — материалы, приёмка, маркировка для всей серии</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22801-83</span><span class="kb-norm-desc">Тройники переходные и проходные с фланцами на Ру св. 10 до 100 МПа. Конструкция и размеры. Dy 6×6…200×200, t −50…+510 °C</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22822-83</span><span class="kb-norm-desc">Тройники переходные на Ру св. 10 до 100 МПа. Конструкция и размеры. Dy 6×6…200×200</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-761-97 ÷ 10.766-97</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов ТЭС из углеродистой и низколегированной сталей на Рраб &lt; 2,2 МПа, t ≤ 425 °C. Часть III — тройники, штуцеры, ответвления</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-23-77 / 36-24-77</span><span class="kb-norm-desc">Детали трубопроводов Dy 500–1400 мм сварные из углеродистой стали на Ру ≤ 2,5 МПа. Тройники сварные и сварные с усилением</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-416-90 ÷ 513-90</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов из коррозионностойкой стали на Рраб ≤ 2,2 МПа, T ≤ 300 °C для АС. Конструкция и размеры</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 720.01–720.24-2009</span><span class="kb-norm-desc">Тройники равнопроходные и переходные (штампованные, сварные, кованые) для трубопроводов и паропроводов тепловых станций. Ресурс 200 000 часов</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">Серия 4.903-10</span><span class="kb-norm-desc">Изделия и детали трубопроводов тепловых сетей — типовая серия для ЖКХ</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением — поднадзорные исполнения с расширенным контролем. Декл. RU С-RU.АБ53.В.08323/23</span></div>
        </div>
      </div>

      <!-- TAB 4: МАТЕРИАЛЫ -->
      <div class="kb-panel" id="kp-materials">
        <div class="kb-lead">
          <div class="kb-lead-h">Марки стали тройников</div>
          <p class="kb-lead-p">Шесть марок в каталоге; выбор — по температуре, давлению и среде. <strong>Сертификат качества</strong> с плавочными данными — в стандартном комплекте каждой поставки.</p>
        </div>
        <div class="kb-norm-list">
          <div class="kb-norm-item"><span class="kb-norm-code">09Г2С</span><span class="kb-norm-desc">Низколегированная, до 475 °C — основная марка для энергетики и северного исполнения</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">Сталь 20</span><span class="kb-norm-desc">Углеродистая, до 450 °C — общепромышленные трубопроводы</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">12Х18Н10Т</span><span class="kb-norm-desc">Нержавеющая аустенитная — коррозионные среды, химия и нефтехимия</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">10Х17Н13М2Т</span><span class="kb-norm-desc">Нержавеющая с молибденом — повышенная стойкость к агрессивным средам (тройники с опорой Ру 100)</span></div>
          <div class="kb-norm-item"><span class="kb-norm-code">13ХФА · 17Г1С</span><span class="kb-norm-desc">Трубные марки для нефтегазовых трубопроводов</span></div>
        </div>
      </div>

      <!-- TAB 5: ДОКУМЕНТАЦИЯ -->
      <div class="kb-panel" id="kp-docs">
        <div class="kb-lead">
          <div class="kb-lead-h">Комплект документов</div>
          <p class="kb-lead-p">С каждой партией: <strong>паспорт изделия</strong>, <strong>сертификат на металл 3.1</strong> с номером плавки, протоколы контроля (ВИК; УЗК/РК — по объёму норматива и заказа), для сварных — протоколы контроля швов. Для поднадзорных объектов по ТР ТС 032/2013 — расширенный объём НК. Документы передаются с грузом и дублируются по email.</p>
        </div>
      </div>

      <!-- TAB 6: КАК ЗАКАЗАТЬ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-lead">
          <div class="kb-lead-h">Порядок заказа</div>
          <p class="kb-lead-p">1) Выберите типоразмер в реестре (фильтры по DN, стали, ГОСТ) или отправьте параметры через форму. 2) В карточке укажите марку стали и поднадзорность, нажмите «Запросить КП». 3) Инженер проверит применимость норматива и подготовит КП в течение рабочего дня. Нестандартные исполнения — врезки, косые тройники, усиленные — изготовим по КД: приложите чертёж к заявке.</p>
        </div>
      </div>

      <!-- TAB 7: ДОСТАВКА И ОПЛАТА -->
      <div class="kb-panel" id="kp-delivery">
        <div class="kb-lead">
          <div class="kb-lead-h">Доставка и оплата</div>
          <p class="kb-lead-p">Отгрузка — после приёмки ОТК и комплектования пакета документов. Стоимость и срок доставки рассчитываются вместе с КП: укажите город или объект в заявке.</p>
        </div>
        <div class="kb-type-grid">
          <div class="kb-type">
            <div class="kb-type-badge">ДОСТАВКА</div>
            <div class="kb-type-title">Транспортными компаниями по всей России</div>
            <p class="kb-type-body">Отгружаем любой транспортной компанией по выбору заказчика либо предлагаем оптимального перевозчика под габарит и срок. Крупногабаритные сварные тройники — по согласованной схеме перевозки.</p>
            <div class="kb-type-tags"><span class="kb-tag">ТК по выбору</span><span class="kb-tag">Негабарит — по согласованию</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">САМОВЫВОЗ</div>
            <div class="kb-type-title">Со склада завода в Челябинске</div>
            <p class="kb-type-body">454091, г. Челябинск, ул. Орджоникидзе, 37. Отгрузка в рабочие дни 09:00–18:00 МСК после уведомления о готовности. Погрузка силами завода.</p>
            <div class="kb-type-tags"><span class="kb-tag">Пн–Пт 09:00–18:00</span><span class="kb-tag">Погрузка заводом</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">УПАКОВКА</div>
            <div class="kb-type-title">Защита кромок и маркировка каждой позиции</div>
            <p class="kb-type-body">Паллеты или деревянная обрешётка по массе и габариту, защита сварочных кромок, маркировка позиций по упаковочному листу. Комплект документов — с грузом и дублируется по email.</p>
            <div class="kb-type-tags"><span class="kb-tag">Упаковочный лист</span><span class="kb-tag">Паспорт · Сертификат качества</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">ОПЛАТА</div>
            <div class="kb-type-title">Безналичный расчёт с НДС</div>
            <p class="kb-type-body">Счёт выставляется по согласованному КП. Порядок оплаты — аванс и доплата по готовности либо график по договору поставки; условия для рамочных и объектных договоров согласуются индивидуально.</p>
            <div class="kb-type-tags"><span class="kb-tag">Б/н с НДС</span><span class="kb-tag">По договору</span></div>
          </div>
        </div>
      </div>

    </div><!-- /kb-panels -->
  </section>
