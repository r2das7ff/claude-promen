<?php
/**
 * Секция 10 — база знаний «Фланцы» (7 табов).
 * Структура и плотность — по образцу otvody s10; классы category-sdt.css.
 * Факты — aggregates / ГОСТ 33259, 12820, 12821, 28759.2.
 * ЗАПРЕТ: не сокращать тексты карточек материалов и табов 04–07.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ФЛАНЦЫ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">ФЛАНЦЫ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды фланцев</button>
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
          <div class="kb-lead-h">Классификация фланцев</div>
          <p class="kb-lead-p">Фланец обеспечивает <strong>разъёмное соединение</strong> труб, арматуры и аппаратов с возможностью обслуживания. В каталоге — <strong>четыре типоисполнения</strong>, <strong>655 типоразмеров</strong> по четырём сериям: тип 11 и тип 01 по ГОСТ 33259-2015, плоские ФП (ГОСТ 12820 / 28759.2) и воротниковые ФВ (ГОСТ 12821). DN 10–4000, PN 1–250.</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 33259</div>
            <div class="kb-card-title">Тип 11 · воротниковые · PN до 250</div>
            <p class="kb-card-body">Фланцы арматуры, соединительных частей и трубопроводов по ГОСТ 33259-2015 (ISO 7005, NEQ). <strong>Тип 11</strong> — приварные встык (воротниковые). Основной ряд каталога: <strong>273 позиции</strong>, DN 10–2400, PN до 250.</p>
            <div class="kb-card-tags"><span class="kb-tag">тип 11</span><span class="kb-tag">PN до 250</span><span class="kb-tag">273 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 33259</div>
            <div class="kb-card-title">Тип 01 · плоские · PN до 250</div>
            <p class="kb-card-body"><strong>Тип 01</strong> по ГОСТ 33259-2015 — плоские фланцы того же нормативного ряда. Применяются в разъёмных соединениях трубопроводов и арматуры. <strong>139 позиций</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">тип 01</span><span class="kb-tag">PN до 250</span><span class="kb-tag">139 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ФП</div>
            <div class="kb-card-title">Плоские приварные · 12820 / 28759.2</div>
            <p class="kb-card-body">Плоские приварные фланцы: трубопроводные по ГОСТ 12820-80 (Ру 0,1–2,5 МПа, DN 10–1200, 111 поз.) и сосудовые по ГОСТ 28759.2-2022 (DN 400–4000, 100 поз.). Итого <strong>211 позиций</strong> типа ФП.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 12820</span><span class="kb-tag">ГОСТ 28759.2</span><span class="kb-tag">211 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ФВ</div>
            <div class="kb-card-title">Воротниковые · ГОСТ 12821</div>
            <p class="kb-card-body">Стальные приварные встык фланцы по ГОСТ 12821-80 на Ру от 0,1 до 20 МПа, температура среды от −253 до +600 °C. <strong>32 позиции</strong>, DN 10–350.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 12821-80</span><span class="kb-tag">Ру до 20</span><span class="kb-tag">32 поз.</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Сравнение типов</div>
        <div class="kb-groups">
          <div class="kb-grp">
            <span class="kb-grp-code">11</span>
            <span class="kb-grp-name">Воротниковые · 273 поз.</span>
            <span class="kb-grp-items">ГОСТ 33259 · PN до 250 · DN 10–2400</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">01</span>
            <span class="kb-grp-name">Плоские тип 01 · 139 поз.</span>
            <span class="kb-grp-items">ГОСТ 33259 · PN до 250</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ФП</span>
            <span class="kb-grp-name">Плоские · 211 поз.</span>
            <span class="kb-grp-items">12820 + 28759.2 · DN 10–4000</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ФВ</span>
            <span class="kb-grp-name">Воротниковые 12821 · 32 поз.</span>
            <span class="kb-grp-items">Ру до 20 МПа · DN 10–350</span>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт фланец</div>
          <p class="kb-lead-p">Типоразмер задаётся <strong>DN</strong> (Dy) и <strong>PN / Ру</strong>, типом конструкции (01 / 11 / ФП / ФВ) и исполнением уплотнительной поверхности. Марка стали и объём НК — по среде, температуре и надзорности объекта.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на фланец</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">DN и PN / Ру</div><div class="kb-check-body">Условный проход и номинальное (условное) давление. Пример: DN 100 / PN 16.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Тип конструкции</div><div class="kb-check-body">Тип 11 или 01 (ГОСТ 33259), ФП (12820 / 28759.2) или ФВ (12821).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Уплотнительная поверхность</div><div class="kb-check-body">Исполнение по стандарту (B, F, E и др. — по таблице выбранного ГОСТ). Если неизвестно — укажите тип прокладки.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Марка стали и среда</div><div class="kb-check-body">20, 10, 09Г2С, 13ХФА, 17Г1С, 12Х18Н10Т, 08Х18Н10Т — или условия t°С и среды.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Поднадзорность и НК</div><div class="kb-check-body">ТР ТС 032/2013; методы НК (ВИК, УЗК, РК) — по нормативу объекта.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках (часто парами), желаемая дата поставки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры фланца</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">DN / Dy</div><div class="kb-param-val">Условный проход, мм. В каталоге — DN 10–4000 (сосудовые до 4000 по ГОСТ 28759.2).</div></div>
              <div class="kb-param"><div class="kb-param-key">PN / Ру</div><div class="kb-param-val">Номинальное / условное давление. ГОСТ 33259 — до PN 250; 12820 — Ру 0,1–2,5 МПа; 12821 — до 20 МПа.</div></div>
              <div class="kb-param"><div class="kb-param-key">Тип</div><div class="kb-param-val"><strong>11</strong> — воротниковый; <strong>01</strong> — плоский (33259); <strong>ФП</strong> — плоский приварной; <strong>ФВ</strong> — воротниковый 12821.</div></div>
              <div class="kb-param"><div class="kb-param-key">Уплотнение</div><div class="kb-param-val">Исполнение уплотнительной поверхности по таблице стандарта. Согласуйте с типом прокладки (ГОСТ 15180 и др.).</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка стали</div><div class="kb-param-val"><strong>20 / 10</strong> — типовые; <strong>09Г2С</strong> — хладостойкость; <strong>12Х18Н10Т / 08Х18Н10Т</strong> — коррозия.</div></div>
              <div class="kb-param"><div class="kb-param-key">Объём НК</div><div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Расширенный: +УЗК / +РК / +МПД / +ПВК. Для поднадзорных — по ТР ТС 032/2013.</div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор норматива определяет конструкцию фланца, ряд PN, допуски и объём контроля. Все <strong>четыре серии</strong> каталога — действующие документы.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — основной ряд</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33259-2015</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов на номинальное давление до PN 250. Типы 01 и 11. В каталоге — 412 позиций, DN 10–2400</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 12820-1980</span><span class="kb-norm-desc">Фланцы стальные плоские приварные на Ру от 0,1 до 2,5 МПа. 111 позиций, DN 10–1200</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 12821-1980</span><span class="kb-norm-desc">Фланцы стальные приварные встык на Ру от 0,1 до 20 МПа. 32 позиции, DN 10–350</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 28759.2-2022</span><span class="kb-norm-desc">Фланцы сосудов и аппаратов стальные плоские приварные. Конструкция и размеры. 100 позиций, DN 400–4000</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 28759.3-1990</span><span class="kb-norm-desc">Фланцы сосудов и аппаратов стальные приварные встык. D 400–4000 мм, PN 0,6–6,3 МПа, t −70…+540 °C. 12 исполнений уплотнительной поверхности</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 54432-2011</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов на номинальное давление от PN 1 до PN 200 (ISO 7005, NEQ)</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Специальные исполнения</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 28919-1991</span><span class="kb-norm-desc">Фланцевые соединения устьевого оборудования. Типы, основные параметры и размеры. Рабочее давление 14–140 МПа, DN 50–680</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 25660-1983</span><span class="kb-norm-desc">Фланцы изолирующие для подводных трубопроводов на Ру 10,0 МПа. Конструкция и размеры</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34 10.747-97 ÷ 10.754-97</span><span class="kb-norm-desc">Фланцы в составе деталей и сборочных единиц трубопроводов ТЭС на Рраб &lt; 2,2 МПа, t ≤ 425 °C. Часть I</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / ТУ / надзор</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия на детали трубопроводов. Применяется при изготовлении по КД заказчика</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">КД заказчика</span><span class="kb-norm-desc">Нестандартные размеры и исполнения уплотнительных поверхностей — согласование до запуска</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ на металл и контроль</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050-2013</span><span class="kb-norm-desc">Нелегированные конструкционные качественные стали (Ст20, сталь 10)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 19281-2014</span><span class="kb-norm-desc">Прокат высокопрочный. Марка 09Г2С</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5632-2014</span><span class="kb-norm-desc">Нержавеющие стали (12Х18Н10Т, 08Х18Н10Т)</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Связанные документы</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 15180-86</span><span class="kb-norm-desc">Прокладки плоские эластичные. Основные параметры и размеры — согласование с исполнением уплотнительной поверхности фланца</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 34655-2020</span><span class="kb-norm-desc">Прокладки овального, восьмиугольного сечения и линзовые стальные для фланцев арматуры. Действующая замена ОСТ 26.260.461-99</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 26.260.454-99</span><span class="kb-norm-desc">Прокладки спирально-навитые (СНП). Типы и размеры — для уплотнений «выступ-впадина» и «шип-паз»</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 10493-81</span><span class="kb-norm-desc">Линзы уплотнительные жёсткие и компенсирующие на Ру 20–100 МПа. Технические условия</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 20700-75 · ОСТ 26-2043-91</span><span class="kb-norm-desc">Крепёж фланцевых соединений: болты, шпильки, гайки и шайбы с температурой среды от 0 до 650 °С</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-045-18</span><span class="kb-norm-desc">Правила контроля сварных соединений оборудования и трубопроводов АЭУ — для объектов АЭС</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">В каталоге фланцев — <strong>семь марок стали</strong>, каждая доступна на всём ряде из 655 типоразмеров. <strong>Каждая марка поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с плавочными данными, химическим составом и механическими характеристиками. Прослеживаемость металла от плавки до готового фланца фиксируется документально.</p>
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
            <div class="kb-mat-apps">Типовые фланцы ГОСТ 12820 / 33259 · Тепловые сети · Технологические линии без агрессивной среды</div>
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
            <div class="kb-mat-grade">08Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">Нержавеющая · пониженный углерод</div>
            <div class="kb-mat-apps">Коррозионные среды · Сварные узлы с пониженным риском МКК · Химия и нефтехимия</div>
          </div>
        </div>
      </div>

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
                <div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Геометрия, уплотнительная поверхность, качество обработки.</div>
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
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:11px;letter-spacing:.1em;color:var(--g1);">по НП-045-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Программа контроля качества</div>
                <div class="kb-doc-desc">Индивидуальная программа НК для категории трубопровода. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают изделие от заготовки до готового фланца. Номер плавки, номер детали, ссылки на все протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Технологические карты сварки и PWHT</div>
                <div class="kb-doc-desc">По согласованным WPS и PQR — для приварных встык исполнений. Параметры режимов и послесварочной термообработки.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации сварщиков и специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения и аттестационные свидетельства согласно НП-043-18 и ПБ 03-273-99.</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> по проектным спецификациям — фланцы вместе с отводами, тройниками, переходами и крепежом фланцевых соединений. Комплектная поставка включает единую сводную ведомость, координацию нормативов по каждой позиции и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта.</p>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку на фланец</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Наименование и норматив</div><div class="kb-check-body">Фланец тип 11/01 по <strong>ГОСТ 33259</strong>, ФП по <strong>ГОСТ 12820</strong> / <strong>28759.2</strong> или ФВ по <strong>ГОСТ 12821</strong>. Если норматив неизвестен — укажите DN, PN и способ присоединения.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN и PN / Ру</div><div class="kb-check-body">Условный проход и давление. При отсутствии — передайте рабочие параметры для подбора инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка или условия среды. Для АЭС — согласно программе контроля объекта.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках (часто парами / комплектами). Желаемая дата поставки.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК и состав пакета. Для АЭС — категория трубопровода по <strong>НП-045-18</strong>.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Чертёж (нестандарт)</div><div class="kb-check-body">DWG, PDF или STEP. Нестандартные исполнения уплотнительных поверхностей — после анализа КД.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип и PN</div><div class="kb-factor-note">Воротниковые высокого PN и сосудовые крупного DN дороже плоских низкого давления при сопоставимых DN.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Нержавеющие (12Х18Н10Т) в 5–6 раз дороже Ст20. Трубные марки — по рынку заготовки.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Полный объём для АЭС может в 2–4 раза увеличить стоимость относительно базового ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN и толщина</div><div class="kb-factor-note">Крупногабаритные фланцы DN 500+ и сосудовые DN 1600–4000 — индивидуальное производство и логистика.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийный заказ (от 10–20 шт.) снижает себестоимость за счёт амортизации подготовки производства.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать тип 01 и тип 11</div><div class="kb-err-note">Плоский и воротниковый — разная конструкция и стыковка с трубой. Ошибка типа = несовместимость на объекте.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать PN / Ру</div><div class="kb-err-note">«Фланец DN 100» без давления — не спецификация. PN определяет ряд размеров и толщину.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать 12820 и 33259</div><div class="kb-err-note">ГОСТ 12820 — Ру до 2,5 МПа. Для PN выше нужен ряд ГОСТ 33259 или 12821.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть исполнение уплотнения</div><div class="kb-err-note">Без исполнения уплотнительной поверхности нельзя подобрать прокладку. Укажите код из таблицы стандарта.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть ТР ТС 032/2013</div><div class="kb-err-note">Изделия с PN &gt; 0.05 МПа в ЕАЭС требуют декларации. Заказывайте заблаговременно.</div></div></div>
            </div>
          </div>
        </div>
      </div>

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

    </div>
  </section>
