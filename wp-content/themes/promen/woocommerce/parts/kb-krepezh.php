<?php
/**
 * Секция 10 — база знаний «Крепёж» (7 табов).
 * Структура и плотность — по образцу otvody s10; классы category-sdt.css.
 * Факты — aggregates / ГОСТ болтов, шпилек, гаек, шайб.
 * ЗАПРЕТ: не сокращать тексты карточек материалов и табов 04–07.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — КРЕПЁЖ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / КРЕПЁЖ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды крепежа</button>
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
          <div class="kb-lead-h">Классификация крепёжных изделий</div>
          <p class="kb-lead-p">Крепёж обеспечивает <strong>сборку фланцевых пар</strong> и монтаж оборудования. В каталоге — <strong>пять типов</strong>, <strong>13&nbsp;746 типоразмеров</strong> по 23 нормативам: болты (Б), шпильки (ШП), гайки (Г), шайбы (Ш) и винты (В). Подбор ведётся по <strong>резьбе M</strong> и <strong>длине L</strong> (для болтов и шпилек), классу прочности и ГОСТ/ОСТ изготовления.</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">Б</div>
            <div class="kb-card-title">Болты · 10 467 позиций</div>
            <p class="kb-card-body">Фундаментные (ГОСТ 22032 / 22043), с шестигранной головкой (ГОСТ 7798 / 7795 / 7796), с уменьшенной головкой (ГОСТ 7805 / 7808) и высокопрочные (ГОСТ 10602). Основной объём каталога крепежа.</p>
            <div class="kb-card-tags"><span class="kb-tag">Б</span><span class="kb-tag">M × L</span><span class="kb-tag">10 467 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ШП</div>
            <div class="kb-card-title">Шпильки · 3 012 позиций</div>
            <p class="kb-card-body">Шпильки общепромышленные ГОСТ 15590 / 15591, фланцевые ГОСТ 9066, шпильки ОСТ 26-2040 для сосудов и аппаратов, ряд ГОСТ 10494 (Ст20). Типовое исполнение фланцевого крепежа.</p>
            <div class="kb-card-tags"><span class="kb-tag">ШП</span><span class="kb-tag">фланцы</span><span class="kb-tag">3 012 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">Г</div>
            <div class="kb-card-title">Гайки · 168 позиций</div>
            <p class="kb-card-body">Гайки для фланцевых соединений ГОСТ 9064, шестигранные ГОСТ 5915 / 10605, низкие ГОСТ 5916 / 10607, с уменьшенным размером под ключ и колпачковые (ГОСТ 5927 / 5929).</p>
            <div class="kb-card-tags"><span class="kb-tag">Г</span><span class="kb-tag">M</span><span class="kb-tag">168 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">Ш / В</div>
            <div class="kb-card-title">Шайбы и винты · 99 позиций</div>
            <p class="kb-card-body">Шайбы ГОСТ 6402 (80 поз.) и усиленные ГОСТ 11371 (18 поз.). Винт по ГОСТ 6958 — 1 позиция. Комплектуют болтовые и шпилечные соединения.</p>
            <div class="kb-card-tags"><span class="kb-tag">Ш 98</span><span class="kb-tag">В 1</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Сравнение типов</div>
        <div class="kb-groups">
          <div class="kb-grp">
            <span class="kb-grp-code">Б</span>
            <span class="kb-grp-name">Болты · 10 467 поз.</span>
            <span class="kb-grp-items">22032 · 7805 · 7798 · 10602 и др.</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ШП</span>
            <span class="kb-grp-name">Шпильки · 3 012 поз.</span>
            <span class="kb-grp-items">15590 · 9066 · ОСТ 26-2040 · 10494</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">Г</span>
            <span class="kb-grp-name">Гайки · 168 поз.</span>
            <span class="kb-grp-items">9064 · 5915 · 10605 · 5927…</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">Ш+В</span>
            <span class="kb-grp-name">Шайбы и винты · 99 поз.</span>
            <span class="kb-grp-items">6402 · 11371 · 6958</span>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт крепёж</div>
          <p class="kb-lead-p">Типоразмер крепежа задаётся <strong>резьбой M</strong> и (для болтов/шпилек) <strong>длиной L</strong>, типом изделия (Б / ШП / Г / Ш / В) и нормативом. Класс прочности, покрытие и марка металла — по среде, нагрузке и требованиям объекта. Для фланцевой пары удобно передавать DN/PN фланца — инженер согласует M и L.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на крепёж</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип изделия</div><div class="kb-check-body">Болт, шпилька, гайка, шайба или винт. Для фланца — обычно шпилька + две гайки + шайбы.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Резьба M и длина L</div><div class="kb-check-body">Пример: M16 × 80. Если неизвестно — укажите DN/PN фланца или толщину пакета сжимаемых деталей.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Норматив</div><div class="kb-check-body">ГОСТ 7798, 9066, 15590, ОСТ 26-2040 и др. Если норматив неизвестен — тип соединения и объект.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Класс прочности / марка</div><div class="kb-check-body">5.6, 8.8, Ст20 и др. — по стандарту или условиям эксплуатации. В каталоге явно указана Ст20 для ряда ГОСТ 10494 (207 поз.).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Покрытие и НК</div><div class="kb-check-body">Цинк, кадмий, без покрытия — по КД. Объём НК (ВИК и др.) — по требованию объекта / ТР ТС 032/2013.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках (часто комплектами на соединение). Желаемая дата поставки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры крепежа</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">Резьба M</div><div class="kb-param-val">Номинальный диаметр метрической резьбы. В каталоге — по типоразмеру позиции (от M1 и выше в зависимости от стандарта).</div></div>
              <div class="kb-param"><div class="kb-param-key">Длина L</div><div class="kb-param-val">Длина стержня, мм. Для болтов и шпилек в каталоге — ряд <strong>8–900 мм</strong>. Для гаек и шайб длина не задаётся.</div></div>
              <div class="kb-param"><div class="kb-param-key">Тип</div><div class="kb-param-val"><strong>Б</strong> — болт; <strong>ШП</strong> — шпилька; <strong>Г</strong> — гайка; <strong>Ш</strong> — шайба; <strong>В</strong> — винт.</div></div>
              <div class="kb-param"><div class="kb-param-key">Класс прочности</div><div class="kb-param-val">По таблице стандарта (например 5.6, 8.8 для болтов). Указывается в заявке, если не следует из выбранного ГОСТ.</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка металла</div><div class="kb-param-val">В агрегатах каталога явно зафиксирована <strong>Ст20</strong> (207 позиций шпилек ГОСТ 10494). Остальной ряд — по материалу стандарта изготовления, согласуется в КП.</div></div>
              <div class="kb-param"><div class="kb-param-key">Объём НК</div><div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Расширенный — по требованию заказчика и надзорности объекта.</div></div>
            </div>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Крепёж для энергетики нормируется на двух уровнях: <strong>ГОСТ 20700-75</strong> и <strong>ОСТ 26-2043-91</strong> задают технические требования к материалу, термообработке и контролю для фланцевых соединений с температурой среды до 650 °С, а конструкцию и размерный ряд определяют отдельные ГОСТ на болты, шпильки, гайки и шайбы. В каталоге — <strong>23 норматива</strong>; в скобках — число позиций реестра.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Технические требования — головные документы</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 20700-75</span><span class="kb-norm-desc">Болты, шпильки, гайки и шайбы для фланцевых и анкерных соединений, пробки и хомуты с температурой среды от 0 до 650 °С. Технические условия</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 26-2043-91</span><span class="kb-norm-desc">Болты, шпильки, гайки и шайбы для фланцевых соединений. Технические требования. Ру 0–16 МПа, t −70…+600 °C</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением — при поставке в составе поднадзорных узлов. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия — нестандартные M×L, покрытия и классы прочности по КД заказчика</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Болты — конструкция и размеры</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 7798-70</span><span class="kb-norm-desc">Болты с шестигранной головкой класса точности B (1 512 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 7805-70</span><span class="kb-norm-desc">Болты с шестигранной головкой класса точности A (1 936 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 7796-70 / 7808-70</span><span class="kb-norm-desc">Болты с шестигранной уменьшенной головкой, классы точности B и A (по 904 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 7795-70</span><span class="kb-norm-desc">Болты с шестигранной уменьшенной головкой и направляющим подголовком, класс точности B, M6–M48 (1 080 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 15590-70 / 15591-70</span><span class="kb-norm-desc">Болты с шестигранной уменьшенной головкой класса точности C — с направляющим подголовком и без него (1 505 + 240 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 10602-94</span><span class="kb-norm-desc">Болты с шестигранной головкой класса точности B с диаметром резьбы свыше 48 мм, M52–M150 (242 поз.)</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Шпильки — конструкция и размеры</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 9066-75</span><span class="kb-norm-desc">Шпильки для фланцевых соединений с температурой среды от 0 до 650 °С. Типы и основные размеры (662 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 10494-80</span><span class="kb-norm-desc">Шпильки для фланцевых соединений арматуры и трубопроводов на Ру св. 10 до 100 МПа (207 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 26-2040-96</span><span class="kb-norm-desc">Шпильки для фланцевых соединений сосудов, аппаратов и трубопроводов. Конструкция и размеры (398 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22032-76</span><span class="kb-norm-desc">Шпильки с ввинчиваемым концом длиной 1d, класс точности B (2 301 поз. — крупнейшая серия каталога)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22043-76</span><span class="kb-norm-desc">Шпильки для деталей с гладкими отверстиями, класс точности A (1 588 поз.)</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Гайки и шайбы</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 9064-75</span><span class="kb-norm-desc">Гайки для фланцевых соединений с температурой среды от 0 до 650 °С. Типы и основные размеры (26 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5915-70 / 5927-70</span><span class="kb-norm-desc">Гайки шестигранные классов точности B и A (22 + 24 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5916-70 / 5929-70</span><span class="kb-norm-desc">Гайки шестигранные низкие классов точности B и A, M1–M48 (24 + 24 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 10605-94 / 10607-94</span><span class="kb-norm-desc">Гайки шестигранные и низкие с диаметром резьбы свыше 48 мм, класс точности B, M52–M150 (24 + 24 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 11371-78 / 6958-78</span><span class="kb-norm-desc">Шайбы нормального ряда и шайбы увеличенные, классы точности A и C, M1–M48 (18 + 1 поз.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 6402-70</span><span class="kb-norm-desc">Шайбы пружинные. Технические условия (80 поз.)</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">В агрегатах каталога крепежа явно указана марка <strong>Ст20</strong> для <strong>207 позиций</strong> шпилек по ГОСТ 10494-1980. Для остального ряда (13&nbsp;539 позиций) материал и класс прочности задаются <strong>стандартом изготовления</strong> и согласуются в коммерческом предложении — без выдуманных марок. <strong>Каждая поставка сопровождается сертификатом на металл</strong> (паспорт качества 3.1 по ГОСТ ISO 10474-2016) с плавочными данными, где это применимо к выбранному нормативу.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ряд ГОСТ 10494</div>
            <div class="kb-mat-range">207 позиций в каталоге</div>
            <div class="kb-mat-apps">Шпильки ГОСТ 10494-1980 · Фланцевые и общепромышленные соединения · Типовые трубопроводы</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">По стандарту изделия</div>
            <div class="kb-mat-std">Таблица материалов выбранного ГОСТ / ОСТ</div>
            <div class="kb-mat-range">13 539 позиций без явной марки в агрегате</div>
            <div class="kb-mat-apps">Болты 7798 / 7805 / 22032 · Шпильки 15590 / 9066 · Гайки и шайбы — материал по норме или заявке</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">Класс прочности</div>
            <div class="kb-mat-std">По ГОСТ на болты / шпильки</div>
            <div class="kb-mat-range">Указывается в заявке</div>
            <div class="kb-mat-apps">Типовые классы болтов (напр. 5.6, 8.8) · Высокопрочный ряд ГОСТ 10602 · Согласование под нагрузку узла</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">Покрытие</div>
            <div class="kb-mat-std">По КД / стандарту</div>
            <div class="kb-mat-range">По согласованию</div>
            <div class="kb-mat-apps">Без покрытия · Цинкование · Специальные покрытия для агрессивных сред — в заявке</div>
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
                <div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Химсостав плавки (где применимо), механические свойства, результаты приёмочного контроля, маркировка, ссылка на норматив серии.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл с плавочными данными</div>
                <div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла: номер плавки, химсостав, механические характеристики, стандарт на металл.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК — 100% объём</div>
                <div class="kb-doc-desc">Визуально-измерительный контроль по позициям партии. Геометрия резьбы, длина, качество поверхности и покрытия.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протоколы дополнительных методов НК</div>
                <div class="kb-doc-desc">По требованию заказчика или нормативного документа объекта. Объём контроля фиксируется в договоре.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Декларация ТР ТС 032/2013 <span class="kb-doc-badge">При применимости</span></div>
                <div class="kb-doc-desc">RU С-RU.АБ53.В.08323/23 — при поставке в составе оборудования под избыточным давлением в ЕАЭС.</div>
              </div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:10px;letter-spacing:.1em;color:var(--g1);">по НП-045-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Программа контроля качества</div>
                <div class="kb-doc-desc">Индивидуальная программа НК для категории трубопровода / узла. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают партию от заготовки до готового крепежа. Номер плавки, номер партии, ссылки на протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения согласно требованиям объекта и НП-043-18 / ПБ 03-273-99.</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> фланцевых соединений: фланцы + шпильки + гайки + шайбы в одной спецификации. Комплектная поставка включает единую сводную ведомость, согласование M×L под DN/PN фланца и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта.</p>
          </div>
        </div>
      </div>

      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку на крепёж</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Наименование и норматив</div><div class="kb-check-body">Болт / шпилька / гайка по <strong>ГОСТ 7798</strong>, <strong>9066</strong>, <strong>15590</strong>, <strong>ОСТ 26-2040</strong> и др. Если норматив неизвестен — тип узла и DN/PN фланца.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">M и L</div><div class="kb-check-body">Резьба и длина. При отсутствии — передайте толщину пакета или параметры фланца для подбора инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Материал / класс прочности</div><div class="kb-check-body">Точная марка, класс (5.6 / 8.8…) или условия среды. Для АЭС — по программе контроля объекта.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках (часто комплектами). Желаемая дата поставки.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК и состав пакета. Для АЭС — категория по <strong>НП-045-18</strong>.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Чертёж (нестандарт)</div><div class="kb-check-body">DWG, PDF или спецификация. Нестандартные M×L и покрытия — после анализа КД.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип и норматив</div><div class="kb-factor-note">Высокопрочные болты (ГОСТ 10602) и шпильки ОСТ для аппаратов дороже типовых общепромышленных рядов при сопоставимых M.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Класс прочности / марка</div><div class="kb-factor-note">Повышенный класс и спецстали увеличивают стоимость относительно базового углеродистого исполнения.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Покрытие и НК</div><div class="kb-factor-note">Специальные покрытия и расширенный объём НК увеличивают цену относительно базового ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Крупный M и длина</div><div class="kb-factor-note">Крупная резьба и длинные шпильки — индивидуальная заготовка и контроль.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийный заказ (от десятков / сотен шт.) снижает себестоимость за счёт амортизации подготовки производства.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Указать только M без L</div><div class="kb-err-note">Для болта и шпильки длина обязательна. «Шпилька M16» без L — не спецификация.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать болт и шпильку</div><div class="kb-err-note">Фланцевая пара чаще на шпильках (ГОСТ 9066) с двумя гайками. Ошибка типа = несовместимость с проектом.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть гайки и шайбы</div><div class="kb-err-note">Заказ только шпилек без гаек ГОСТ 9064 / шайб — неполный комплект фланцевого соединения.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать класс прочности</div><div class="kb-err-note">При нагрузках выше типовых нужен явный класс (или ссылка на КД). Иначе подбор идёт по базовому ряду стандарта.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать M с DN фланца</div><div class="kb-err-note">DN 100 фланца ≠ резьба M100. Резьба крепежа выбирается по таблице фланца / КД.</div></div></div>
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
