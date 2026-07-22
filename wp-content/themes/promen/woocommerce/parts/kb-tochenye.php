<?php
/**
 * Секция 10 — база знаний «Переходы» (7 табов).
 * Структура и плотность контента — по образцу taxonomy otvody s10;
 * разметка — классы category-sdt.css; факты — aggregates / ГОСТ / ОСТ / СТО.
 * ЗАПРЕТ: не сокращать тексты карточек материалов и табов 04–07.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ПЕРЕХОДЫ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / ТОЧЕНЫЕ ДЕТАЛИ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды точёных</button>
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
          <div class="kb-lead-h">Классификация точёных переходов</div>
          <p class="kb-lead-p">Точеные детали переходы (тип <strong>ПТ</strong>) изготавливаются механической обработкой по <strong>ГОСТ 22826-1983</strong> (89 позиций) и <strong>ОСТ 34-42-664-84</strong> (10 позиций). В каталоге — <strong>99 типоразмеров</strong>, DN 10–200, исполнения 1–5. Подбор по D×s / d×s (или DN), исполнению и марке стали.</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 17378</div>
            <div class="kb-card-title">Бесшовные приварные · DN 32–600</div>
            <p class="kb-card-body">Бесшовные приварные переходы из углеродистой и низколегированной стали (ГОСТ 17378-2001, ИСО 3419-81). <strong>Концентрические (ПК)</strong> — оси совпадают; <strong>эксцентрические (ПЭ)</strong> — для дренажа и сохранения уклона трассы. Основной тип для общепрома, НГК и трубопроводов ТЭС. <strong>278 позиций</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 17378-2001</span><span class="kb-tag">ПК · ПЭ</span><span class="kb-tag">DN 32–600</span><span class="kb-tag">278 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 22826</div>
            <div class="kb-card-title">На Ру до 100 МПа · DN 25–200</div>
            <p class="kb-card-body">Переходы высокого давления (свыше 10 до 100 МПа) по ГОСТ 22826-83. Применяются в нефтехимии и производстве минеральных удобрений; рабочий диапазон температур −50…+510 °C. Поставка с расширенным объёмом контроля. <strong>68 позиций</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">Ру до 100 МПа</span><span class="kb-tag">DN 25–200</span><span class="kb-tag">ГОСТ 22826-83</span><span class="kb-tag">68 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ОСТ 36 / 34-10</div>
            <div class="kb-card-title">Сварные крупный DN · 300–1600</div>
            <p class="kb-card-body">Сварные переходы: ОСТ 36-22-77 (Dy 500–1400, Ру ≤ 2,5 МПа) и ОСТ 34-10-753-97 (листовые для ТЭС, Рраб &lt; 2,2 МПа). Контроль сварных швов — ВИК / УЗК / РК по объёму норматива и требованиям заказчика. <strong>57 позиций</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">ОСТ 36-22-77</span><span class="kb-tag">ОСТ 34-10-753-97</span><span class="kb-tag">57 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">СТО 318 / ОСТ 754</div>
            <div class="kb-card-title">Точеные детали и мелкий DN · 15–65</div>
            <p class="kb-card-body">Точеные детали переходы по СТО 318.01-2009 (исполнения 01–03) и сварные мелкого DN по ОСТ 34.10.754-97. Котельные обвязки, малые диаметры, точная геометрия. <strong>22 позиции</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">СТО 318.01</span><span class="kb-tag">ОСТ 34.10.754</span><span class="kb-tag">DN 15–65</span><span class="kb-tag">22 поз.</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Сравнение типов</div>
        <div class="kb-groups">
          <div class="kb-grp">
            <span class="kb-grp-code">ПШ</span>
            <span class="kb-grp-name">Бесшовные · DN 32–600</span>
            <span class="kb-grp-items">Штамповка · ГОСТ 17378 · ПК/ПЭ · 278 поз.</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">П-100</span>
            <span class="kb-grp-name">Ру до 100 МПа · DN 25–200</span>
            <span class="kb-grp-items">ГОСТ 22826 · нефтехимия · 68 поз.</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ПСВ</span>
            <span class="kb-grp-name">Сварные · DN 300–1600</span>
            <span class="kb-grp-items">ОСТ 36-22 / 34-10-753 · НК швов · 57 поз.</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ПТ</span>
            <span class="kb-grp-name">Точеные детали / мелкий DN</span>
            <span class="kb-grp-items">СТО 318 / ОСТ 754 · 22 поз.</span>
          </div>
        </div>
      </div>

      <!-- TAB 2: ПАРАМЕТРЫ -->
      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт переход</div>
          <p class="kb-lead-p">Типоразмер задаётся <strong>двумя парами размеров</strong>: D1×s1 (больший конец) и D2×s2 (меньший конец). Для эксцентрических дополнительно указывают ориентацию «плоской» стороны. Норматив определяет конструкцию, допуски и объём контроля.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на точёный переход</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Размеры обоих концов</div><div class="kb-check-body">D1×s1 и D2×s2 (или DN1×DN2 с толщинами). Пример: 108×4 – 89×4 / DN 100×80.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Тип: ПК или ПЭ</div><div class="kb-check-body"><strong>Концентрический (ПК)</strong> — оси совпадают; <strong>эксцентрический (ПЭ)</strong> — для дренажа и сохранения уклона. Укажите ориентацию плоскости для ПЭ.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Норматив объекта</div><div class="kb-check-body">ГОСТ 17378 / ГОСТ 22826 (Ру 100) / ОСТ 36-22 / ОСТ 34-10-753 / СТО 318.01 / ОСТ 34.10.754.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Марка стали и среда</div><div class="kb-check-body">Точная марка из каталога или условия: t°С, среда, агрессивность. Для АЭС — по программе контроля.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Поднадзорность и НК</div><div class="kb-check-body">ТР ТС 032/2013; методы НК (ВИК, УЗК, РК) — особенно для сварных серий.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках, желаемая дата. Для крупного DN сварных — согласуйте перевозку заранее.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры точёного перехода</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">D1×s1</div><div class="kb-param-val">Больший конец: наружный диаметр × толщина стенки. Согласуйте со стандартом трубы большего участка.</div></div>
              <div class="kb-param"><div class="kb-param-key">D2×s2</div><div class="kb-param-val">Меньший конец: диаметр × стенка. Оба конца должны соответствовать ряду выбранного ГОСТ/ОСТ.</div></div>
              <div class="kb-param"><div class="kb-param-key">ПК / ПЭ</div><div class="kb-param-val"><strong>ПК</strong> — концентрический; <strong>ПЭ</strong> — эксцентрический (укажите сторону «плоской» образующей).</div></div>
              <div class="kb-param"><div class="kb-param-key">L / H</div><div class="kb-param-val">Длина / высота конуса — по таблице стандарта. Не задаётся произвольно для типовых позиций.</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка стали</div><div class="kb-param-val"><strong>20 / 10</strong> — общепром.; <strong>09Г2С</strong> — хладостойкость; <strong>12Х18Н10Т</strong> — коррозия; <strong>20ХЗМВФ</strong> — серия Ру 100.</div></div>
              <div class="kb-param"><div class="kb-param-key">Объём НК</div><div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Для сварных — +УЗК / +РК швов. Полный объём для АЭС — по НП-045-18.</div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: НОРМЫ -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор нормативного документа определяет геометрию перехода, допуски, категорию, объём неразрушающего контроля и состав разрешительной документации. Все <strong>шесть серий</strong> каталога — действующие документы; ниже — полная нормативная обвязка поставки.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — бесшовные и высокое давление</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17378-2001</span><span class="kb-norm-desc">Переходы бесшовные приварные (ИСО 3419-81). Концентрические и эксцентрические. Основной стандарт. В каталоге — 278 позиций, DN 32–600</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22826-1983</span><span class="kb-norm-desc">Переходы на Ру свыше 10 до 100 МПа. Детали трубопроводов высокого давления. 68 позиций, DN 25–200</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ОСТ / СТО — сварные и точеные детали</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-22-77</span><span class="kb-norm-desc">Переходы сварные Dy 500–1400, Ру ≤ 2,5 МПа. 33 позиции</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-753-97</span><span class="kb-norm-desc">Переходы сварные листовые для ТЭС (Рраб &lt; 2,2 МПа). 24 позиции</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34.10.754-97</span><span class="kb-norm-desc">Переходы сварные ТЭС — мелкий DN 15–40. 9 позиций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО 318.01-2009</span><span class="kb-norm-desc">Переходы точеные детали ЦКТИ, исполнения 01–03. 13 позиций</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / ТУ / надзор</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия на детали трубопроводов. Применяется при изготовлении по КД заказчика</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">КД заказчика</span><span class="kb-norm-desc">Нестандартные длины, толщины, исполнения — согласование материала, технологии и объёма НК до запуска</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ на металл и контроль</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050-2013</span><span class="kb-norm-desc">Нелегированные конструкционные качественные стали (Ст20, сталь 10)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 19281-2014</span><span class="kb-norm-desc">Прокат высокопрочный. Марка 09Г2С</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5632-2014</span><span class="kb-norm-desc">Нержавеющие стали (12Х18Н10Т, 08Х18Н10Т, 10Х17Н13М2Т)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-045-18</span><span class="kb-norm-desc">Правила контроля сварных соединений оборудования и трубопроводов АЭУ — для объектов АЭС</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: МАТЕРИАЛЫ -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">В каталоге переходов — полный спектр марок: углеродистые, низколегированные, трубные НГК, аустенитные нержавеющие и теплоустойчивые для серии Ру 100. <strong>Каждая марка поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с плавочными данными, химическим составом и механическими характеристиками. Прослеживаемость металла от плавки до готового перехода фиксируется документально.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731-87</div>
            <div class="kb-mat-range">до +425°С · PN до 20 МПа</div>
            <div class="kb-mat-apps">Водяные тракты ТЭС · Общепромышленные трубопроводы · НГК низкого давления · Теплосети и типовые обвязки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">Сталь 10</div>
            <div class="kb-mat-std">ГОСТ 1050-2013</div>
            <div class="kb-mat-range">Общепромышленные трубопроводы</div>
            <div class="kb-mat-apps">Типовые переходы ГОСТ 17378 · Тепловые сети · Технологические линии без агрессивной среды</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">09Г2С</div>
            <div class="kb-mat-std">ГОСТ 19281-2014</div>
            <div class="kb-mat-range">−70…+350°С · Хладостойкая</div>
            <div class="kb-mat-apps">Криогенные системы · Северное и арктическое исполнение · НГК при низких температурах · Установки разделения воздуха</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">10Г2</div>
            <div class="kb-mat-std">Низколегированная · трубная</div>
            <div class="kb-mat-range">Энергетика и НГК</div>
            <div class="kb-mat-apps">Переходы технологических трубопроводов · Объекты с требованиями к низколегированным трубным маркам</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">10Г2С1</div>
            <div class="kb-mat-std">Низколегированная · трубная</div>
            <div class="kb-mat-range">Повышенная прочность</div>
            <div class="kb-mat-apps">Трубопроводы с повышенными требованиями к механическим свойствам · Сопряжение с трубами 10Г2С1</div>
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
            <div class="kb-mat-grade">17Г1С-У · 17ГС</div>
            <div class="kb-mat-std">Трубные марки НГК</div>
            <div class="kb-mat-range">Усиленное / базовое исполнение</div>
            <div class="kb-mat-apps">Нефтегазовые трубопроводы с требованиями к ударной вязкости · Северные трассы · Объекты с усиленным исполнением 17Г1С-У</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">−196…+600°С · Нержавеющая</div>
            <div class="kb-mat-apps">АЭС (все контуры, все категории) · Агрессивные химические среды · Пищевая и фармацевтическая промышленность</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">08Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">Нержавеющая · пониженный углерод</div>
            <div class="kb-mat-apps">Коррозионные среды · Сварные узлы с пониженным риском МКК · Химия и нефтехимия</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">10Х17Н13М2Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">до +700°С · Кислотостойкая</div>
            <div class="kb-mat-apps">Сильноагрессивные среды · Серная и фосфорная кислоты · Хлориды · Установки химической переработки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">20ХЗМВФ</div>
            <div class="kb-mat-std">Теплоустойчивая · серия Ру 100</div>
            <div class="kb-mat-range">Ру до 100 МПа · ГОСТ 22826</div>
            <div class="kb-mat-apps">Переходы высокого давления ГОСТ 22826-83 · Нефтехимия · Минеральные удобрения · t −50…+510 °C</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">Р9</div>
            <div class="kb-mat-std">По каталогу / спецназначение</div>
            <div class="kb-mat-range">Серии ГОСТ 17378</div>
            <div class="kb-mat-apps">Позиции каталога с материалом Р9 · Согласование применимости — с инженером при запросе КП</div>
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
                <div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Геометрическое соответствие обоим концам и качество поверхности.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протоколы УЗК / РК / МПД / ПВК</div>
                <div class="kb-doc-desc">По требованию заказчика или норматива. Для сварных серий — контроль швов обязателен по объёму ОСТ. УЗК по ГОСТ Р 55724-2013.</div>
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
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:7px;letter-spacing:.1em;color:var(--g1);">по НП-045-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Программа контроля качества</div>
                <div class="kb-doc-desc">Индивидуальная программа НК для категории трубопровода. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают изделие от заготовки до готового перехода. Номер плавки, номер детали, ссылки на все протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Технологические карты сварки и PWHT</div>
                <div class="kb-doc-desc">По согласованным WPS и PQR — для сварных переходов ОСТ. Параметры режимов сварки и послесварочной термообработки.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации сварщиков и специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения и аттестационные свидетельства согласно НП-043-18 и ПБ 03-273-99.</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> по проектным спецификациям — переходы вместе с отводами, тройниками и фланцами одного контура. Комплектная поставка включает единую сводную ведомость, координацию нормативов по каждой позиции и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта.</p>
          </div>
        </div>
      </div>

      <!-- TAB 6: ЗАКАЗ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку на точёный переход</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Наименование и норматив</div><div class="kb-check-body">Переход ПК/ПЭ по <strong>ГОСТ 17378</strong>, Ру 100 по <strong>ГОСТ 22826</strong>, сварной по <strong>ОСТ 36-22 / 34-10</strong> или точёный по <strong>СТО 318.01</strong>. Если норматив неизвестен — укажите DN1×DN2, PN и объект.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">D1×s1 и D2×s2</div><div class="kb-check-body">Оба конца с толщинами. При отсутствии — передайте DN1×DN2, PN и t°С для подбора инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка или условия среды. Для серии Ру 100 часто требуется <strong>20ХЗМВФ</strong> и расширенный НК.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках. Желаемая дата. Для сварных крупного DN — поэтапный график.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК и состав пакета. Для АЭС — категория трубопровода по <strong>НП-045-18</strong>.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Чертёж (нестандарт)</div><div class="kb-check-body">DWG, PDF или STEP. Нестандартные длины и толщины — после анализа КД.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип перехода</div><div class="kb-factor-note">Сварные крупного DN (ОСТ) и точеные детали СТО дороже бесшовных ГОСТ 17378 при сопоставимых DN за счёт трудоёмкости.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Нержавеющие (12Х18Н10Т) в 5–6 раз дороже Ст20. Теплоустойчивая 20ХЗМВФ для Ру 100 — по рынку заготовки.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Полный объём для АЭС (УЗК+РК+МПД+ПВК) может в 2–4 раза увеличить стоимость относительно базового ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN и перепад диаметров</div><div class="kb-factor-note">Крупный DN и большой перепад D1/D2 увеличивают массу и сложность штамповки/сварки.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийный заказ (от 10–20 шт.) снижает себестоимость за счёт амортизации подготовки производства.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Указать только один DN</div><div class="kb-err-note">Переход задаётся двумя концами. «Переход DN 100» без второго диаметра — не спецификация.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать ПК и ПЭ</div><div class="kb-err-note">Эксцентрический нужен для дренажа и уклона. Ошибка типа = переделка на объекте.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">DN ≠ наружный диаметр трубы</div><div class="kb-err-note">Для DN 50 наружный диаметр по ГОСТ 8732 = 57 мм. Всегда уточняйте стандарт трубы обоих участков.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Заказать ГОСТ 17378 при Ру 100</div><div class="kb-err-note">Высокое давление (свыше 10 МПа) — серия ГОСТ 22826, не бесшовный общепром.</div></div></div>
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
            <p class="kb-card-body">Отгружаем любой транспортной компанией по выбору заказчика либо предлагаем оптимального перевозчика под габарит и срок. Крупногабаритные сварные переходы — по согласованной схеме перевозки.</p>
            <div class="kb-card-tags"><span class="kb-tag">ТК по выбору</span><span class="kb-tag">Негабарит — по согласованию</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">САМОВЫВОЗ</div>
            <div class="kb-card-title">Со склада завода в Челябинске</div>
            <p class="kb-card-body">454091, г. Челябинск, ул. Орджоникидзе, 37. Отгрузка в рабочие дни 09:00–18:00 МСК после уведомления о готовности. Погрузка силами завода.</p>
            <div class="kb-card-tags"><span class="kb-tag">Пн–Пт 09:00–18:00</span><span class="kb-tag">Погрузка заводом</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">УПАКОВКА</div>
            <div class="kb-card-title">Защита кромок и маркировка каждой позиции</div>
            <p class="kb-card-body">Паллеты или деревянная обрешётка по массе и габариту, защита сварочных кромок обоих концов, маркировка позиций по упаковочному листу. Комплект документов — с грузом и дублируется по email.</p>
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
