<?php
/**
 * Секция 10 — база знаний «Заглушки» (7 табов).
 * Структура и плотность контента — по образцу taxonomy otvody s10;
 * разметка — классы category-sdt.css; факты — aggregates / ГОСТ 17379, 22815.
 * ЗАПРЕТ: не сокращать тексты карточек материалов и табов 04–07.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ЗАГЛУШКИ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">ЗАГЛУШКИ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды заглушек</button>
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
          <div class="kb-lead-h">Классификация заглушек</div>
          <p class="kb-lead-p">Заглушка обеспечивает глухое закрытие торца трубы или штуцера — на время монтажа, гидравлических испытаний или в постоянном режиме. В каталоге — <strong>два типоисполнения</strong>, <strong>128 типоразмеров</strong> по двум сериям: эллиптические приварные (<strong>ЗЭ</strong>) по ГОСТ 17379-2001 и фланцевые на Ру до 100 МПа (<strong>ЗФ</strong>) по ГОСТ 22815-83.</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 17379</div>
            <div class="kb-card-title">Эллиптические приварные · DN 15–600</div>
            <p class="kb-card-body">Бесшовные приварные эллиптические заглушки из углеродистой и низколегированной стали (ГОСТ 17379-2001, ИСО 3419-81). Конструкция и размеры — по рисунку 1 и таблицам исполнений 1 и 2 стандарта. Основной тип для общепромышленных трубопроводов и трубопроводов ТЭС. <strong>93 позиции</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 17379-2001</span><span class="kb-tag">тип ЗЭ</span><span class="kb-tag">DN 15–600</span><span class="kb-tag">93 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 22815</div>
            <div class="kb-card-title">Фланцевые · Ру св. 10 до 100 МПа</div>
            <p class="kb-card-body">Заглушки фланцевые на Ру свыше 10 до 100 МПа (св. 100 до 1000 кгс/см²) по ГОСТ 22815-83. Применяются в нефтехимии и смежных отраслях; присоединение — фланцевое, исполнения 1–4 по таблицам стандарта. <strong>35 позиций</strong>, DN 6–200.</p>
            <div class="kb-card-tags"><span class="kb-tag">Ру до 100 МПа</span><span class="kb-tag">тип ЗФ</span><span class="kb-tag">DN 6–200</span><span class="kb-tag">35 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ЗЭ</div>
            <div class="kb-card-title">Приварка встык · постоянное / монтажное</div>
            <p class="kb-card-body">Эллиптическая форма снижает концентрацию напряжений на торце. Приваривается к трубе; область применения — в соответствии с разделом 1 ГОСТ 17380 (общие ТУ на бесшовные приварные детали). Исполнения 1 и 2 — по таблицам размеров стандарта.</p>
            <div class="kb-card-tags"><span class="kb-tag">Приварные</span><span class="kb-tag">Исп. 1 / 2</span><span class="kb-tag">ГОСТ 17380</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ЗФ</div>
            <div class="kb-card-title">Фланцевое · разъёмное закрытие</div>
            <p class="kb-card-body">Фланцевая заглушка позволяет снимать и ставить узел без резки трубы. Критична при высоком давлении: материал (в т.ч. 20Х3МВФ) и объём НК согласуются с категорией объекта и ТР ТС 032/2013.</p>
            <div class="kb-card-tags"><span class="kb-tag">Фланцевые</span><span class="kb-tag">20Х3МВФ</span><span class="kb-tag">Поднадзорные</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Сравнение типов</div>
        <div class="kb-groups" style="grid-template-columns:repeat(2,1fr);">
          <div class="kb-grp">
            <span class="kb-grp-code">ЗЭ</span>
            <span class="kb-grp-name">Эллиптические · DN 15–600</span>
            <span class="kb-grp-items">Приварные · ГОСТ 17379 · 93 поз.</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ЗФ</span>
            <span class="kb-grp-name">Фланцевые · DN 6–200</span>
            <span class="kb-grp-items">Ру св. 10 до 100 МПа · ГОСТ 22815 · 35 поз.</span>
          </div>
        </div>
      </div>

      <!-- TAB 2: ПАРАМЕТРЫ -->
      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт заглушку</div>
          <p class="kb-lead-p">Для эллиптических типоразмер задаётся <strong>D×s</strong> трубы (или DN и стенкой) и исполнением по таблице ГОСТ 17379. Для фланцевых — <strong>DN</strong>, <strong>Ру</strong> и исполнение фланца по ГОСТ 22815. Марка стали и объём НК — по среде и надзорности объекта.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на заглушку</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип: ЗЭ или ЗФ</div><div class="kb-check-body"><strong>Эллиптическая приварная</strong> (ГОСТ 17379) или <strong>фланцевая</strong> (ГОСТ 22815, Ру до 100 МПа).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Размеры</div><div class="kb-check-body">Для ЗЭ — D×s трубы (пример: 108×4). Для ЗФ — DN и Ру (или рабочее давление).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Исполнение</div><div class="kb-check-body">ЗЭ: исполнение 1 или 2 по таблицам ГОСТ 17379. ЗФ: исполнение фланца 1–4 по ГОСТ 22815.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Марка стали и среда</div><div class="kb-check-body">20, 09Г2С, 13ХФА, 17Г1С, 12Х18Н10Т; для ЗФ дополнительно 20Х3МВФ. Или условия t°С и среды.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Поднадзорность и НК</div><div class="kb-check-body">ТР ТС 032/2013; методы НК (ВИК, УЗК, РК) — особенно для фланцевых Ру 100.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках, желаемая дата поставки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры заглушки</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">D×s / DN</div><div class="kb-param-val">Для ЗЭ — наружный диаметр × стенка трубы. Для ЗФ — условный проход DN. Согласуйте со стандартом трубы / фланца.</div></div>
              <div class="kb-param"><div class="kb-param-key">Ру</div><div class="kb-param-val">Для фланцевых ЗФ — Ру свыше 10 до 100 МПа по ГОСТ 22815. Для эллиптических — по стенке, стали и проекту трубопровода.</div></div>
              <div class="kb-param"><div class="kb-param-key">Исполнение</div><div class="kb-param-val">ЗЭ: исп. 1 / 2 (таблицы ГОСТ 17379). ЗФ: исп. 1–4 фланца (таблицы ГОСТ 22815).</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка стали</div><div class="kb-param-val"><strong>20</strong> — типовые; <strong>09Г2С</strong> — хладостойкость; <strong>12Х18Н10Т</strong> — коррозия; <strong>20Х3МВФ</strong> — серия Ру 100.</div></div>
              <div class="kb-param"><div class="kb-param-key">Объём НК</div><div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Расширенный: +УЗК / +РК / +МПД / +ПВК. Для поднадзорных — по ТР ТС 032/2013.</div></div>
              <div class="kb-param"><div class="kb-param-key">Масса</div><div class="kb-param-val">По стандарту изготовления; уточняется по КД и фактической толщине / исполнению фланца.</div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: НОРМЫ -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор норматива определяет конструкцию заглушки, допуски, способ присоединения и объём контроля. Обе серии каталога — действующие документы; ниже — нормативная обвязка поставки.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — заглушки</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17379-2001</span><span class="kb-norm-desc">Заглушки эллиптические бесшовные приварные (ИСО 3419-81). Конструкция и размеры. В каталоге — 93 позиции, DN 15–600</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22815-1983</span><span class="kb-norm-desc">Заглушки фланцевые на Ру свыше 10 до 100 МПа. Конструкция и размеры. 35 позиций, DN 6–200</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17380-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные. Общие технические условия — область применения эллиптических заглушек</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ОСТ и СТО — энергетика</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-25-77</span><span class="kb-norm-desc">Детали трубопроводов Dy 500–1400 мм сварные из углеродистой стали на Ру ≤ 2,5 МПа. Заглушки</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34 10.755-97 ÷ 10.760-97</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов ТЭС на Рраб &lt; 2,2 МПа, t ≤ 425 °C. Часть II</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-508-90 ÷ 513-90</span><span class="kb-norm-desc">Детали трубопроводов из коррозионностойкой стали на Рраб ≤ 2,2 МПа, T ≤ 300 °C для АС. Часть 2</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО 95 133-2013</span><span class="kb-norm-desc">Заглушки плоские приварные для трубопроводов пара, горячей воды и технологических трубопроводов атомных станций из сталей перлитного класса до 2,2 МПа</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / ТУ / надзор</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия на детали трубопроводов. Применяется при изготовлении по КД заказчика</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">КД заказчика</span><span class="kb-norm-desc">Нестандартные размеры и исполнения — согласование материала, технологии и объёма НК до запуска</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ на металл и контроль</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050-2013</span><span class="kb-norm-desc">Нелегированные конструкционные качественные стали (Ст20)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 19281-2014</span><span class="kb-norm-desc">Прокат высокопрочный. Марка 09Г2С</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5632-2014</span><span class="kb-norm-desc">Нержавеющие стали (12Х18Н10Т)</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Связанные документы серии СДТ</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17375–17380</span><span class="kb-norm-desc">Серия стандартов на СДТ: 17375 и 30753 — отводы, 17376 — тройники, 17378 — переходы, 17379 — заглушки эллиптические, 17380 — общие технические условия</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-045-18</span><span class="kb-norm-desc">Правила контроля сварных соединений оборудования и трубопроводов АЭУ — для объектов АЭС</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: МАТЕРИАЛЫ -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">В каталоге заглушек — <strong>шесть марок стали</strong>: пять доступны на всём ряде из 128 типоразмеров, теплоустойчивая <strong>20Х3МВФ</strong> — на серии фланцевых ГОСТ 22815 (35 позиций). <strong>Каждая марка поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с плавочными данными, химическим составом и механическими характеристиками. Прослеживаемость металла от плавки до готовой заглушки фиксируется документально.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731-87</div>
            <div class="kb-mat-range">до +425°С · типовые трубопроводы</div>
            <div class="kb-mat-apps">Водяные тракты ТЭС · Общепромышленные трубопроводы · НГК низкого давления · Теплосети и монтажные закрытия</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">09Г2С</div>
            <div class="kb-mat-std">ГОСТ 19281-2014</div>
            <div class="kb-mat-range">−70…+350°С · Хладостойкая</div>
            <div class="kb-mat-apps">Криогенные системы · Северное и арктическое исполнение · НГК при низких температурах · Установки разделения воздуха</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">13ХФА</div>
            <div class="kb-mat-std">ТУ / трубная марка</div>
            <div class="kb-mat-range">Нефтегаз · технологические линии</div>
            <div class="kb-mat-apps">Промысловые и технологические трубопроводы НГК · Сепарационные системы · Газоперерабатывающие установки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">17Г1С</div>
            <div class="kb-mat-std">ГОСТ / конструкционная</div>
            <div class="kb-mat-range">НГК · магистральные системы</div>
            <div class="kb-mat-apps">Магистральные и промысловые трубопроводы · Сопряжение с трубами 17Г1С · Технологические обвязки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">−196…+600°С · Нержавеющая</div>
            <div class="kb-mat-apps">АЭС (все контуры, все категории) · Агрессивные химические среды · Пищевая и фармацевтическая промышленность</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">20Х3МВФ</div>
            <div class="kb-mat-std">Теплоустойчивая · серия Ру 100</div>
            <div class="kb-mat-range">Ру до 100 МПа · ГОСТ 22815</div>
            <div class="kb-mat-apps">Фланцевые заглушки ГОСТ 22815-83 · Нефтехимия · Высокое давление · Поднадзорные объекты</div>
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
                <div class="kb-doc-name">Паспорт изделия — сертификат качества 3.1</div>
                <div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Химсостав плавки, механические свойства, результаты приёмочного контроля, маркировка, ссылка на норматив серии.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл с плавочными данными</div>
                <div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла: номер плавки, химсостав, механические характеристики, стандарт на металл.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК — 100% объём</div>
                <div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Геометрическое соответствие и качество поверхности по требованиям норматива.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протоколы УЗК / РК / МПД / ПВК</div>
                <div class="kb-doc-desc">По требованию заказчика или нормативного документа. УЗК по ГОСТ Р 55724-2013. Объём контроля фиксируется в договоре.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Акт гидравлических испытаний</div>
                <div class="kb-doc-desc">При наличии требования в заказе или нормативе. Давление испытания 1.25–1.5 × Pраб, выдержка не менее 5 минут.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Декларация ТР ТС 032/2013 <span class="kb-doc-badge">Обязательно</span></div>
                <div class="kb-doc-desc">RU С-RU.АБ53.В.08323/23, серия RU 0418908. Обязательна при PN&nbsp;&gt;&nbsp;0.05&nbsp;МПа для всей продукции в ЕАЭС.</div>
              </div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:10px;letter-spacing:.1em;color:var(--g1);">по НП-045-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Программа контроля качества</div>
                <div class="kb-doc-desc">Индивидуальная программа НК для категории трубопровода. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают изделие от заготовки до готовой заглушки. Номер плавки, номер детали, ссылки на все протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Технологические карты сварки и PWHT</div>
                <div class="kb-doc-desc">По согласованным WPS и PQR — при приварке эллиптических заглушек. Параметры режимов и послесварочной термообработки.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации сварщиков и специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения и аттестационные свидетельства согласно НП-043-18 и ПБ 03-273-99.</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> по проектным спецификациям — заглушки вместе с отводами, тройниками, переходами и фланцами одного контура. Комплектная поставка включает единую сводную ведомость, координацию нормативов по каждой позиции и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта.</p>
          </div>
        </div>
      </div>

      <!-- TAB 6: ЗАКАЗ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку на заглушку</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Наименование и норматив</div><div class="kb-check-body">Заглушка эллиптическая по <strong>ГОСТ 17379</strong> или фланцевая по <strong>ГОСТ 22815</strong>. Если норматив неизвестен — укажите DN, способ присоединения и давление.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN / D×s и Ру</div><div class="kb-check-body">Для ЗЭ — D×s трубы. Для ЗФ — DN и Ру. При отсутствии — передайте рабочие параметры для подбора инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка или условия среды. Для серии Ру 100 часто требуется <strong>20Х3МВФ</strong> и расширенный НК.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках. Желаемая дата поставки или срок с момента подтверждения.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК и состав пакета. Для АЭС — категория трубопровода по <strong>НП-045-18</strong>.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Чертёж (нестандарт)</div><div class="kb-check-body">DWG, PDF или STEP. Нестандартные размеры — после анализа КД.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип заглушки</div><div class="kb-factor-note">Фланцевые Ру 100 (ГОСТ 22815) дороже эллиптических приварных при сопоставимых DN за счёт конструкции и материала.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Нержавеющие (12Х18Н10Т) в 5–6 раз дороже Ст20. Теплоустойчивая 20Х3МВФ — по рынку заготовки.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Полный объём для АЭС может в 2–4 раза увеличить стоимость относительно базового ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN и толщина</div><div class="kb-factor-note">Масса растёт с DN и s. Крупные эллиптические DN 400–600 — индивидуальная штамповка.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийный заказ снижает себестоимость за счёт амортизации подготовки производства.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать ЗЭ и ЗФ</div><div class="kb-err-note">Приварная эллиптическая ≠ фланцевая. Ошибка типа = несовместимость с узлом на объекте.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">DN ≠ наружный диаметр трубы</div><div class="kb-err-note">Для DN 50 наружный диаметр по ГОСТ 8732 = 57 мм. Для ЗЭ всегда уточняйте D×s трубы.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Заказать ГОСТ 17379 при Ру 100</div><div class="kb-err-note">Высокое давление (свыше 10 МПа) — серия ГОСТ 22815 (фланцевые), не эллиптический общепром.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать исполнение</div><div class="kb-err-note">Для ЗЭ — исп. 1 или 2; для ЗФ — исп. фланца 1–4. Без исполнения — неоднозначная спецификация.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть ТР ТС 032/2013</div><div class="kb-err-note">Изделия с PN &gt; 0.05 МПа в ЕАЭС требуют декларации. Заказывайте заблаговременно.</div></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 7: ДОСТАВКА -->
      <div class="kb-panel" id="kp-delivery">
        <div class="kb-lead">
          <div class="kb-lead-h">Доставка и оплата</div>
          <p class="kb-lead-p">Отгрузка — после приёмки ОТК и комплектования пакета документов. Стоимость и срок доставки рассчитываются вместе с коммерческим предложением: укажите город или объект в заявке — менеджер включит логистику в расчёт.</p>
        </div>
        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ДОСТАВКА</div>
            <div class="kb-card-title">Транспортными компаниями по всей России</div>
            <p class="kb-card-body">Отгружаем любой транспортной компанией по выбору заказчика либо предлагаем оптимального перевозчика под габарит и срок. Негабаритные позиции — по согласованной схеме перевозки.</p>
            <div class="kb-card-tags"><span class="kb-tag">ТК по выбору</span><span class="kb-tag">Негабарит — по согласованию</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">САМОВЫВОЗ</div>
            <div class="kb-card-title">Со склада завода в Челябинске</div>
            <p class="kb-card-body">454091, г. Челябинск, ул. Орджоникидзе, 37. Отгрузка в рабочие дни 09:00–18:00 МСК после уведомления о готовности. Погрузка силами завода.</p>
            <div class="kb-card-tags"><span class="kb-tag">Пн–Пт 09:00–18:00</span><span class="kb-tag">Погрузка заводом</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">УПАКОВКА</div>
            <div class="kb-card-title">Защита кромок и маркировка каждой позиции</div>
            <p class="kb-card-body">Паллеты или деревянная обрешётка по массе и габариту, защита сварочных / уплотнительных кромок, маркировка позиций по упаковочному листу. Комплект документов — с грузом и дублируется по email.</p>
            <div class="kb-card-tags"><span class="kb-tag">Упаковочный лист</span><span class="kb-tag">Паспорт · Сертификат 3.1</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ОПЛАТА</div>
            <div class="kb-card-title">Безналичный расчёт с НДС</div>
            <p class="kb-card-body">Счёт выставляется по согласованному КП. Порядок оплаты — аванс и доплата по готовности либо график по договору поставки; условия для рамочных и объектных договоров согласуются индивидуально.</p>
            <div class="kb-card-tags"><span class="kb-tag">Б/н с НДС</span><span class="kb-tag">По договору</span></div>
          </div>
        </div>
      </div>

    </div><!-- /kb-panels -->
  </section>
