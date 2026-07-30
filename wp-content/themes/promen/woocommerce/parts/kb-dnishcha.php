<?php
/**
 * Секция 10 — база знаний «Днища» (7 табов).
 * Структура и плотность контента — по образцу taxonomy otvody s10;
 * разметка — классы category-sdt.css; факты — aggregates / ГОСТ 6533-78.
 * ЗАПРЕТ: не сокращать тексты карточек материалов и табов 04–07.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ДНИЩА -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / ДНИЩА</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды днищ</button>
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
          <div class="kb-lead-h">Классификация днищ</div>
          <p class="kb-lead-p">Днище закрывает цилиндрическую обечайку сосуда, аппарата или котла и воспринимает давление среды вместе с корпусом. В каталоге завода — <strong>эллиптические отбортованные днища типа ДЭ</strong> по ГОСТ 6533-78: <strong>250 типоразмеров</strong>, DN 100–3800, толщина стенки 4–90 мм, пять марок стали. Ряд разбит на три рабочих диапазона по диаметру — для подбора по габариту аппарата и логистике.</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ДЭ-С</div>
            <div class="kb-card-title">Стандартный DN · 100–600</div>
            <p class="kb-card-body">Эллиптические отбортованные днища основного ряда ГОСТ 6533-78 для аппаратов, коллекторов, сепараторов и сосудов среднего диаметра. Геометрия эллиптической части и высота отбортовки — по таблице стандарта; толщина стенки подбирается под расчётное давление аппарата. <strong>142 позиции</strong> в каталоге.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 6533-78</span><span class="kb-tag">DN 100–600</span><span class="kb-tag">s 4–90</span><span class="kb-tag">142 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ДЭ-М</div>
            <div class="kb-card-title">Средний DN · 600–1400</div>
            <p class="kb-card-body">Днища среднего диаметра для сосудов, котлов и технологических аппаратов. Толщина стенки — строго по таблице ГОСТ 6533 под расчётное давление и температуру. Монтаж и стыковка с обечайкой согласуются по КД аппарата. <strong>37 позиций</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 6533-78</span><span class="kb-tag">DN 600–1400</span><span class="kb-tag">Сосуды · котлы</span><span class="kb-tag">37 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ДЭ-К</div>
            <div class="kb-card-title">Крупный DN · 1400–3800</div>
            <p class="kb-card-body">Крупногабаритные днища для аппаратов большого диаметра. Требуют согласованной схемы перевозки (негабарит), подготовки монтажной площадки и контроля стыкового шва с обечайкой по программе НК объекта. <strong>71 позиция</strong>.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 6533-78</span><span class="kb-tag">DN 1400–3800</span><span class="kb-tag">Негабарит</span><span class="kb-tag">71 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ГОСТ 6533</div>
            <div class="kb-card-title">Эллиптические отбортованные · тип ДЭ</div>
            <p class="kb-card-body">Единый стандарт на эллиптические отбортованные днища из углеродистых, легированных и двухслойных сталей для сосудов, аппаратов и котлов. В каталоге завода — полный ряд DN 100–3800, s 4–90 мм; рабочее давление сосуда задаётся расчётом аппарата, не «PN перехода».</p>
            <div class="kb-card-tags"><span class="kb-tag">тип ДЭ</span><span class="kb-tag">Отбортованные</span><span class="kb-tag">250 поз.</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Сравнение диапазонов</div>
        <div class="kb-groups" style="grid-template-columns:repeat(3,1fr);">
          <div class="kb-grp">
            <span class="kb-grp-code">ДЭ-С</span>
            <span class="kb-grp-name">DN 100–600 · 142 поз.</span>
            <span class="kb-grp-items">Аппараты, коллекторы, сепараторы · ГОСТ 6533-78</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ДЭ-М</span>
            <span class="kb-grp-name">DN 600–1400 · 37 поз.</span>
            <span class="kb-grp-items">Сосуды, котлы · толщина по таблице стандарта</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ДЭ-К</span>
            <span class="kb-grp-name">DN 1400–3800 · 71 поз.</span>
            <span class="kb-grp-items">Крупные аппараты · транспорт по согласованию</span>
          </div>
        </div>
      </div>

      <!-- TAB 2: ПАРАМЕТРЫ -->
      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт днище</div>
          <p class="kb-lead-p">Типоразмер определяется <strong>диаметром D</strong> (база размеров по проекту сосуда) и <strong>толщиной стенки s</strong> по ряду ГОСТ 6533. Высота эллиптической части и параметры отбортовки — по таблице стандарта. Рабочее давление и температура аппарата задаются расчётом сосуда; марка стали — по среде и температурному режиму.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на днище</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Диаметр и толщина стенки</div><div class="kb-check-body">D по ряду ГОСТ 6533 (или внутренний/наружный диаметр обечайки из КД) и толщина s, мм. Пример: D 800 / s 10.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка из ряда каталога: <strong>20, 09Г2С, 13ХФА, 17Г1С, 12Х18Н10Т</strong> — или условия среды и температуры для подбора инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Параметры сосуда</div><div class="kb-check-body">Расчётное давление, температура, среда, категория аппарата. Давление днища не задаётся отдельно от расчёта корпуса.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Поднадзорность и объём НК</div><div class="kb-check-body">ТР ТС 032/2013 при избыточном давлении; методы НК (ВИК, УЗК, РК) — по нормативу объекта и заказу.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Оснастка и КД</div><div class="kb-check-body">Люки, штуцеры, усиления, отверстия под уровнемеры — приложите чертёж. Нестандарт изготовим по КД.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках, желаемая дата поставки. Для DN 1400+ — заранее согласуйте схему перевозки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры днища</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">D</div><div class="kb-param-val">Диаметр днища по ряду ГОСТ 6533-78. В каталоге — DN 100–3800. Согласуйте с диаметром обечайки аппарата по КД.</div></div>
              <div class="kb-param"><div class="kb-param-key">s</div><div class="kb-param-val">Толщина стенки, мм. В каталоге — 4–90 мм. Подбирается по таблице стандарта под расчётное давление и температуру сосуда.</div></div>
              <div class="kb-param"><div class="kb-param-key">H</div><div class="kb-param-val">Высота эллиптической части и параметры отбортовки — по таблице ГОСТ 6533. Не задаются произвольно при типовом заказе.</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка стали</div><div class="kb-param-val"><strong>20</strong> — типовые сосуды; <strong>09Г2С</strong> — хладостойкость; <strong>13ХФА / 17Г1С</strong> — нефтегаз; <strong>12Х18Н10Т</strong> — коррозия и АЭС.</div></div>
              <div class="kb-param"><div class="kb-param-key">Объём НК</div><div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Расширенный: +УЗК / +РК / +МПД / +ПВК. Для поднадзорных сосудов — по ТР ТС 032/2013 и программе объекта.</div></div>
              <div class="kb-param"><div class="kb-param-key">Масса</div><div class="kb-param-val">По стандарту изготовления; уточняется по КД и фактической толщине. Для крупных DN — влияет на схему такелажа и перевозки.</div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: НОРМЫ -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Каталог днищ опирается на <strong>ГОСТ 6533-1978</strong> как основной стандарт геометрии. Давление сосуда, категория аппарата и объём контроля задаются проектом и надзорными документами. Ниже — нормативы, с которыми работает поставка днищ завода.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — геометрия днищ</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 6533-1978</span><span class="kb-norm-desc">Днища эллиптические отбортованные стальные для сосудов, аппаратов и котлов. Основные размеры. Действует, взамен ГОСТ 6533-68. Область стандарта — D 133–4500 мм, s 4–120 мм; в каталоге — 250 типоразмеров, DN 100–3800, s 4–90 мм</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 28759.3-1990</span><span class="kb-norm-desc">Фланцы сосудов и аппаратов стальные приварные встык — ответная часть к днищам и обечайкам, D 400–4000 мм, PN 0,6–6,3 МПа</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 26-2008-83</span><span class="kb-norm-desc">Крышки плоские люков стальных сварных сосудов и аппаратов. Конструкция — для люков и лазов в днищах</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 504.01/504.02-2009</span><span class="kb-norm-desc">Донышки приварные для трубопроводов и паропроводов тепловых станций. Конструкция и размеры</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / надзор</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования, работающего под избыточным давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">КД заказчика</span><span class="kb-norm-desc">Люки, штуцеры, усиления, нестандартная геометрия — изготовление по чертежам. Согласование материала, технологии и объёма НК до запуска</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ на металл и контроль</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений. Методы и оценка результатов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050-2013</span><span class="kb-norm-desc">Металлопродукция из нелегированных конструкционных качественных сталей (сталь 20 и др.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 19281-2014</span><span class="kb-norm-desc">Прокат из высокопрочной стали. Марка 09Г2С — хладостойкое исполнение</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5632-2014</span><span class="kb-norm-desc">Нержавеющие стали и сплавы коррозионно-стойкие, жаростойкие и жаропрочные (12Х18Н10Т)</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТУ предприятия</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">Технические условия ООО Завод «Промышленная Энергетика» на детали трубопроводов и аппаратные элементы. Применяется при изготовлении по КД</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: МАТЕРИАЛЫ -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">В каталоге днищ — <strong>пять марок стали</strong>, каждая доступна на всём ряде из 250 типоразмеров. Выбор марки — по температуре, среде и требованиям аппарата. <strong>Каждая марка поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с указанием плавочных данных, химического состава и механических характеристик. Прослеживаемость металла от плавки завода-поставщика до готового днища фиксируется документально.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731-87</div>
            <div class="kb-mat-range">до +425°С · типовые сосуды</div>
            <div class="kb-mat-apps">Теплотехническое оборудование · Общепромышленные аппараты и коллекторы · Сосуды низкого и среднего давления · Водяные и паровые тракты без агрессивной среды</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">09Г2С</div>
            <div class="kb-mat-std">ГОСТ 19281-2014</div>
            <div class="kb-mat-range">−70…+350°С · Хладостойкая</div>
            <div class="kb-mat-apps">Сосуды и аппараты северного исполнения · НГК при низких температурах · Криогенные и низкотемпературные установки · Арктические объекты</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">13ХФА</div>
            <div class="kb-mat-std">ТУ / трубная марка</div>
            <div class="kb-mat-range">Нефтегаз · технологические аппараты</div>
            <div class="kb-mat-apps">Технологические аппараты НГК · Сепараторы и коллекторы промысловых систем · Трубопроводно-аппаратные обвязки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">17Г1С</div>
            <div class="kb-mat-std">ГОСТ / конструкционная</div>
            <div class="kb-mat-range">НГК · магистральные системы</div>
            <div class="kb-mat-apps">Аппаратные системы нефтегазовых трубопроводов · Сосуды технологических установок · Объекты с требованиями к трубным маркам</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">−196…+600°С · Нержавеющая</div>
            <div class="kb-mat-apps">АЭС и коррозионные среды · Химические и пищевые аппараты · Сосуды с агрессивными средами · Объекты с требованием стойкости к МКК</div>
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
                <div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Содержит химический состав плавки, механические свойства, результаты приёмочного контроля, маркировку и ссылку на ГОСТ 6533-78.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл с плавочными данными</div>
                <div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла: номер плавки, химсостав, механические характеристики, стандарт на металл.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК — 100% объём</div>
                <div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Подтверждает геометрическое соответствие ряду ГОСТ 6533 и качество поверхности отбортовки.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протоколы УЗК / РК / МПД / ПВК</div>
                <div class="kb-doc-desc">По требованию заказчика или нормативного документа объекта. УЗК по ГОСТ Р 55724-2013. Объём контроля фиксируется в договоре.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Акт гидравлических испытаний</div>
                <div class="kb-doc-desc">При наличии требования в заказе или нормативе сосуда. Давление испытания и выдержка — по программе испытаний аппарата.</div>
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
                <div class="kb-doc-desc">Индивидуальная программа НК для категории оборудования. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают изделие от заготовки до готового днища. Содержат номер плавки, номер детали, ссылки на все протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Технологические карты сварки и PWHT</div>
                <div class="kb-doc-desc">По согласованным WPS и PQR — при сварных присоединениях, люках и штуцерах. Параметры режимов и послесварочной термообработки.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации сварщиков и специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения и аттестационные свидетельства согласно НП-043-18 и ПБ 03-273-99.</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> по проектным спецификациям — днища вместе с отводами, тройниками, переходами и фланцами одного контура. Комплектная поставка включает единую сводную ведомость, координацию нормативов по каждой позиции и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта.</p>
          </div>
        </div>
      </div>

      <!-- TAB 6: ЗАКАЗ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку на днище</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Наименование и норматив</div><div class="kb-check-body">Днище эллиптическое отбортованное по <strong>ГОСТ 6533-78</strong>, тип ДЭ. Если ряд неизвестен — укажите D обечайки и расчётные параметры сосуда.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">D и толщина стенки</div><div class="kb-check-body">Диаметр по ряду стандарта и s, мм. При отсутствии — передайте расчётное давление, температуру и диаметр обечайки для подбора инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка (20, 09Г2С, 13ХФА, 17Г1С, 12Х18Н10Т) или условия среды. Для АЭС — согласно программе контроля объекта.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках. Желаемая дата поставки. Для DN 1400+ заложите время на согласование перевозки.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК и состав пакета документов. Для поднадзорных сосудов — ссылка на ТР ТС 032/2013 и категорию аппарата.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Чертёж (люки, штуцеры)</div><div class="kb-check-body">DWG, PDF или STEP. Инженерная проработка, согласование материала и технологии. Срок и стоимость — после анализа КД.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Нержавеющие (12Х18Н10Т) существенно дороже углеродистой Ст20. Трубные марки 13ХФА / 17Г1С — по рынку заготовки.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Диаметр и толщина</div><div class="kb-factor-note">Масса растёт с D и s. Крупногабаритные днища DN 1400–3800 — индивидуальное производство и логистика негабарита.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Полный объём НК для поднадзорных сосудов может в 2–4 раза увеличить стоимость относительно базового ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Оснастка по КД</div><div class="kb-factor-note">Люки, штуцеры, усиления — дополнительная мехобработка, сварка и контроль стыков.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийный заказ снижает себестоимость за счёт амортизации подготовки производства на весь объём.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать D днища и DN трубы</div><div class="kb-err-note">Ряд ГОСТ 6533 задаёт диаметр днища для обечайки сосуда. Не подставляйте DN трубопровода без сверки с КД аппарата.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть толщину стенки</div><div class="kb-err-note">«Днище D 800» без s — не спецификация. Толщина определяется расчётом сосуда и таблицей ГОСТ 6533.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать марку стали</div><div class="kb-err-note">«Сталь» без марки недопустима для ответственных аппаратов: материал влияет на сварку обечайки и объём НК.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Игнорировать негабарит DN 1400+</div><div class="kb-err-note">Крупные днища требуют согласованной схемы перевозки и такелажа. Заказывайте логистику вместе с КП.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть ТР ТС 032/2013</div><div class="kb-err-note">Сосуды под избыточным давлением в ЕАЭС требуют декларации. Без неё оборудование не вводится в эксплуатацию.</div></div></div>
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
            <p class="kb-card-body">Отгружаем любой транспортной компанией по выбору заказчика либо предлагаем оптимального перевозчика под габарит и срок. Негабаритные днища DN 1400+ — по согласованной схеме перевозки и крепления.</p>
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
            <div class="kb-card-title">Защита кромок отбортовки и маркировка</div>
            <p class="kb-card-body">Паллеты или деревянная обрешётка по массе и габариту, защита кромок отбортовки, маркировка позиций по упаковочному листу. Комплект документов — с грузом и дублируется по email.</p>
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
