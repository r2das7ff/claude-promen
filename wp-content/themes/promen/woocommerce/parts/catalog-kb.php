<?php
/**
 * База знаний каталога (cat-kb): 7 вкладок, FAQ.
 * Разметка 1:1 из design-reference/katalog.html.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="cat-kb">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:24px 56px 20px;border-bottom:1px solid var(--ln2);">
    <div style="display:flex;align-items:center;gap:14px;font-family:'DINPro',monospace;font-size:8px;letter-spacing:.28em;text-transform:uppercase;color:var(--g1);">
      <span style="border:1px solid var(--g1);padding:3px 8px;font-family:'DINProCond','DINPro',sans-serif;font-weight:700;font-size:11px;letter-spacing:.14em;line-height:1;">КБ</span>
      База знаний · Справочник
    </div>
    <div style="font-family:'DINPro',monospace;font-size:8px;letter-spacing:.18em;text-transform:uppercase;color:var(--g2);opacity:.45;">KNOWLEDGE BASE / ENGINEERING REFERENCE</div>
  </div>

  <div class="kb-tabrow" role="tablist">
    <button class="kb-tab active" data-panel="catalog" role="tab"><span class="kb-tab-n">01</span>О каталоге</button>
    <button class="kb-tab" data-panel="navigate" role="tab"><span class="kb-tab-n">02</span>Навигация</button>
    <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
    <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
    <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
    <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Заказ</button>
    <button class="kb-tab" data-panel="faq" role="tab"><span class="kb-tab-n">07</span>Частые вопросы</button>
  </div>

  <div class="kb-panels">

    <!-- TAB 1: О КАТАЛОГЕ -->
    <div class="kb-panel kp-active" id="kp-catalog">
      <div class="kb-lead">
        <div class="kb-lead-h">Реестр продукции завода «Промышленная Энергетика»</div>
        <p class="kb-lead-p">Каталог охватывает полную номенклатуру трубопроводных изделий, производимых и поставляемых заводом: <strong>8 номенклатурных групп, 26+ позиций</strong> в диапазоне DN 15–1400 мм, PN 0.6–20 МПа, t −40…+600°С. Для каждой позиции указаны нормативный документ, доступные марки стали, объём НК и тип исполнения (производство / поставка / по КД).</p>
      </div>

      <div class="kb-cards">
        <div class="kb-card">
          <div class="kb-card-badge">АЭС</div>
          <div class="kb-card-title">Атомная энергетика</div>
          <p class="kb-card-body">Трубопроводы <strong>I–IV категорий по НП-089-15</strong>. Расширенный объём НК по НП-045-18, прослеживаемость плавки, паспорт качества 3.1. Все позиции с пометкой АЭС изготавливаются с полным пакетом разрешительной документации.</p>
          <div class="kb-card-tags"><span class="kb-tag">НП-045-18</span><span class="kb-tag">НП-068-05</span><span class="kb-tag">НП-089-15</span></div>
        </div>
        <div class="kb-card">
          <div class="kb-card-badge">ТЭС</div>
          <div class="kb-card-title">Тепловая энергетика</div>
          <p class="kb-card-body">Главные паропроводы, питательные трубопроводы, тепловые тракты. <strong>до +600°С, PN до 25 МПа</strong>. Нормативная база — <strong>СТО ЦКТИ серий 321 и 720</strong>, ОСТ 34, ОСТ 36.</p>
          <div class="kb-card-tags"><span class="kb-tag">СТО ЦКТИ 321</span><span class="kb-tag">СТО ЦКТИ 720</span><span class="kb-tag">ОСТ 34</span></div>
        </div>
        <div class="kb-card">
          <div class="kb-card-badge">НГК</div>
          <div class="kb-card-title">Нефтегаз</div>
          <p class="kb-card-body">Промысловые и магистральные трубопроводы, установки подготовки нефти и газа. Материалы с повышенной коррозионной стойкостью. Изготовление по <strong>ГОСТ 17375–17380</strong>, ОСТ 36 и ТУ предприятия.</p>
          <div class="kb-card-tags"><span class="kb-tag">ГОСТ 17375–17380</span><span class="kb-tag">ТР ТС 032</span></div>
        </div>
        <div class="kb-card">
          <div class="kb-card-badge">КД</div>
          <div class="kb-card-title">По чертежам заказчика</div>
          <p class="kb-card-body">Нестандартные детали по конструкторской документации заказчика. Принимаем <strong>DWG, PDF, STEP</strong>. Инженерная проработка, согласование материала, технологии и объёма НК до запуска.</p>
          <div class="kb-card-tags"><span class="kb-tag">DWG / PDF / STEP</span><span class="kb-tag">ТУ предприятия</span></div>
        </div>
      </div>

      <div class="kb-groups-hd">Номенклатурные группы каталога</div>
      <div class="kb-groups">
        <div class="kb-grp"><span class="kb-grp-code">СДТ</span><span class="kb-grp-name">Соединительные детали трубопровода</span><span class="kb-grp-items">Отводы 45°/90°/180° · Тройники · Переходы · Днища · Заглушки</span></div>
        <div class="kb-grp"><span class="kb-grp-code">ФЛ</span><span class="kb-grp-name">Фланцы трубопроводные</span><span class="kb-grp-items">Воротниковые · Плоские · Свободные на кольце · Глухие · По ГОСТ 33259</span></div>
        <div class="kb-grp"><span class="kb-grp-code">ОП</span><span class="kb-grp-name">Опоры и подвески</span><span class="kb-grp-items">Скользящие · Неподвижные · Пружинные · По ОСТ 36 и СТО ЦКТИ</span></div>
        <div class="kb-grp"><span class="kb-grp-code">ЗРА</span><span class="kb-grp-name">Запорно-регулирующая арматура</span><span class="kb-grp-items">Задвижки · Клапаны · Краны · По ГОСТ 33257 и НП-068-05</span></div>
        <div class="kb-grp"><span class="kb-grp-code">ТР</span><span class="kb-grp-name">Трубы стальные бесшовные</span><span class="kb-grp-items">Горячедеформированные и холоднодеформированные по ГОСТ 8731–8734</span></div>
        <div class="kb-grp"><span class="kb-grp-code">НМ</span><span class="kb-grp-name">Нестандартные металлоизделия</span><span class="kb-grp-items">Детали по КД заказчика · DWG / PDF / STEP</span></div>
        <div class="kb-grp"><span class="kb-grp-code">ИЗ</span><span class="kb-grp-name">Изоляция и покрытия</span><span class="kb-grp-items">Тепловая изоляция · Антикоррозионные покрытия</span></div>
        <div class="kb-grp"><span class="kb-grp-code">ТД</span><span class="kb-grp-name">Точёные крепёжные детали</span><span class="kb-grp-items">Шпильки · Гайки · Втулки · По ГОСТ и КД</span></div>
      </div>
    </div>

    <!-- TAB 2: НАВИГАЦИЯ ПО КАТАЛОГУ -->
    <div class="kb-panel" id="kp-navigate">
      <div class="kb-2col">
        <div>
          <div class="kb-col-title">Фильтры и навигация</div>
          <div class="kb-params">
            <div class="kb-param">
              <div class="kb-param-key">Группа продукции</div>
              <div class="kb-param-val">Левая панель — 9 кнопок: ВСЕ · СДТ · ФЛ · ОП · ЗРА · ТР · НМ · ИЗ · ТД. Сужает реестр до нужного типа изделий.</div>
            </div>
            <div class="kb-param">
              <div class="kb-param-key">Тип исполнения</div>
              <div class="kb-param-val"><strong>Производство</strong> — собственное изготовление с полным пакетом документов. <strong>Поставка</strong> — торговые позиции. <strong>По чертежу</strong> — нестандарт по КД.</div>
            </div>
            <div class="kb-param">
              <div class="kb-param-key">Отрасль</div>
              <div class="kb-param-val">Фильтр <strong>АЭС / ТЭС / НГК</strong> — быстро отбирает изделия, сертифицированные для конкретного типа объектов.</div>
            </div>
            <div class="kb-param">
              <div class="kb-param-key">Нормативный документ</div>
              <div class="kb-param-val"><strong>ГОСТ · ОСТ · СТО ЦКТИ · НП · ТУ</strong> — находит позиции, соответствующие нормативу вашего проекта.</div>
            </div>
            <div class="kb-param">
              <div class="kb-param-key">Строка поиска</div>
              <div class="kb-param-val">Ищет по коду, наименованию, ГОСТ, материалу и DN. Например: <strong>«09Г2С»</strong> или <strong>«ГОСТ 17375»</strong>. Горячая клавиша: <strong>⌘K</strong> или <strong>/</strong>.</div>
            </div>
            <div class="kb-param">
              <div class="kb-param-key">Карточка позиции</div>
              <div class="kb-param-val">Клик на строку — открывает панель с DN, PN, нормативом, маркой стали, объёмом НК и прямой ссылкой на форму запроса КП.</div>
            </div>
          </div>
        </div>

        <div>
          <div class="kb-col-title">Параметры подбора изделий</div>
          <div class="kb-steps">
            <div class="kb-step">
              <span class="kb-step-n">DN</span>
              <div>
                <div class="kb-step-title">Диаметр условный · 15–1400 мм</div>
                <div class="kb-step-body">Соответствует условному проходу трубы. <strong>DN ≠ наружный диаметр</strong>: DN 50 = Dнар 57 мм по ГОСТ 8732. Для каждой позиции каталога — обязательный параметр.</div>
              </div>
            </div>
            <div class="kb-step">
              <span class="kb-step-n">PN</span>
              <div>
                <div class="kb-step-title">Давление условное · 0.6–20 МПа</div>
                <div class="kb-step-body">Условное давление при 20°С. При повышенных температурах допустимое давление снижается по таблицам норматива. Указывайте <strong>рабочее давление и температуру</strong> — мы подберём верное PN.</div>
              </div>
            </div>
            <div class="kb-step">
              <span class="kb-step-n">НД</span>
              <div>
                <div class="kb-step-title">Нормативный документ</div>
                <div class="kb-step-body">Определяет геометрию, допуски, категорию и объём НК. Для ТЭС: предпочтительны <strong>СТО ЦКТИ</strong> и <strong>ОСТ 34</strong>. Для АЭС: <strong>НП-045-18 / НП-089-15</strong>. Для НГК: <strong>ГОСТ 17375–17380</strong>.</div>
              </div>
            </div>
            <div class="kb-step">
              <span class="kb-step-n">М</span>
              <div>
                <div class="kb-step-title">Марка стали</div>
                <div class="kb-step-body">Подбирается по рабочей температуре и среде. <strong>Ст20</strong> до +425°С; <strong>09Г2С</strong> до −70°С; <strong>12Х1МФ</strong> до +570°С; <strong>12Х18Н10Т</strong> — АЭС и агрессивные среды.</div>
              </div>
            </div>
            <div class="kb-step">
              <span class="kb-step-n">НК</span>
              <div>
                <div class="kb-step-title">Объём неразрушающего контроля</div>
                <div class="kb-step-body">Базово: <strong>100% ВИК</strong>. Расширенный: +УЗК / +РК / +МПД / +ПВК. Объём определяется категорией трубопровода и требованиями программы контроля объекта.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: НОРМАТИВНАЯ БАЗА -->
    <div class="kb-panel" id="kp-norms">
      <p class="kb-intro-p">Продукция каталога охвачена четырьмя уровнями нормативных документов: государственные и межгосударственные <strong>ГОСТ</strong>, отраслевые <strong>ОСТ</strong> (Минэнерго, Минмонтажспецстрой, Минхиммаш), стандарты организаций <strong>СТО ЦКТИ</strong> и <strong>СТО СРО</strong> для ТЭС и АЭС, федеральные нормы <strong>НП</strong> и технические условия предприятия <strong>ТУ</strong>. Полный реестр с фильтрами — на странице <a href="<?php echo esc_url( ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : home_url( '/normativnaya-baza/' ) ); ?>">«Нормативная база»</a>.</p>
      <div class="kb-norm-grid">
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">ГОСТ — соединительные детали и фланцы</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17380-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные из углеродистой и низколегированной стали. Общие технические условия — головной документ серии</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17375 / 30753</span><span class="kb-norm-desc">Отводы крутоизогнутые типа 3D (R ≈ 1,5DN) и типа 2D (R ≈ DN). Конструкция</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17376 / 17378 / 17379</span><span class="kb-norm-desc">Тройники, переходы, заглушки эллиптические. Конструкция</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 6533-1978</span><span class="kb-norm-desc">Днища эллиптические отбортованные стальные для сосудов, аппаратов и котлов. D 133–4500 мм</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33259-2015</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов на номинальное давление до PN 250</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 12820 / 12821</span><span class="kb-norm-desc">Фланцы стальные плоские приварные (Ру 0,1–2,5 МПа) и приварные встык (Ру 0,1–20,0 МПа)</span></div>
          </div>
        </div>
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">ОСТ — энергетика, монтаж, высокое давление</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34 10.699-97 / 10.700-97</span><span class="kb-norm-desc">Отводы крутоизогнутые и переходы на Рраб &lt; 2,2 МПа для атомных и тепловых электростанций</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34 10.747-97 ÷ 10.766-97</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов ТЭС на Рраб &lt; 2,2 МПа, t ≤ 425 °C. Части I–III</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-416-90 ÷ 513-90</span><span class="kb-norm-desc">Детали трубопроводов из коррозионностойкой стали на Рраб ≤ 2,2 МПа, T ≤ 300 °C для АС</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-20-77 ÷ 36-25-77</span><span class="kb-norm-desc">Детали трубопроводов Dy 500–1400 мм сварные из углеродистой стали на Ру ≤ 2,5 МПа: отводы, тройники, переходы, заглушки</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-41-81 — 36-49-81</span><span class="kb-norm-desc">Детали трубопроводов из углеродистой стали сварные и гнутые Dy до 500 мм на Ру до 10 МПа</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 24.125.01-89 — 24.125.26-89</span><span class="kb-norm-desc">Детали и сборочные единицы из сталей аустенитного класса для трубопроводов АЭС Dy 14–325 мм</span></div>
          </div>
        </div>
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">СТО — тепловая и атомная энергетика</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.01–.08-2009</span><span class="kb-norm-desc">Отводы гнутые, крутоизогнутые, штампованные и штампосварные для трубопроводов и паропроводов ТЭС</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 720.01–.24-2009</span><span class="kb-norm-desc">Тройники равнопроходные и переходные для трубопроводов и паропроводов ТЭС</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 318.01–.06-2009</span><span class="kb-norm-desc">Переходы точёные, обжатые и штампованные для трубопроводов и паропроводов ТЭС</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 462 / 504 / 530 / 313</span><span class="kb-norm-desc">Штуцера и патрубки, донышки приварные, бобышки, соединения штуцерные. Ресурс 200 000 часов</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">СТО 95 133-2013</span><span class="kb-norm-desc">Заглушки плоские приварные для трубопроводов атомных станций из сталей перлитного класса до 2,2 МПа</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">СТО СРО-П 60542948 00010–2013</span><span class="kb-norm-desc">Детали и элементы трубопроводов групп В и С атомных станций. Соединения сварные. Типы и размеры</span></div>
          </div>
        </div>
        <div class="kb-norm-group">
          <div class="kb-norm-group-hd">Опоры, крепёж, уплотнения, обязательные нормы</div>
          <div class="kb-norm-items">
            <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 24.125.151 / .153 / .154 / .156 / .159-01</span><span class="kb-norm-desc">Опоры неподвижные, скользящие, хомутовые направляющие и катковые трубопроводов ТЭС и АЭС</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 16127-70 · ОСТ 36-94-83 · ОСТ 36-146-88</span><span class="kb-norm-desc">Подвески, опоры подвижные, опоры стальных технологических трубопроводов на Ру до 10 МПа</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 20700-75 · ОСТ 26-2043-91</span><span class="kb-norm-desc">Болты, шпильки, гайки и шайбы для фланцевых соединений с температурой среды от 0 до 650 °С</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 34655-2020 · ОСТ 26.260.454-99</span><span class="kb-norm-desc">Прокладки овального, восьмиугольного сечения, линзовые и спирально-навитые для фланцев арматуры</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">НП-089-15 · НП-045-18 · НП-068-05</span><span class="kb-norm-desc">Федеральные нормы и правила для оборудования, трубопроводов и арматуры АЭУ: категории и объём контроля</span></div>
            <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под избыточным давлением. Обязателен при PN &gt; 0,05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 4: МАТЕРИАЛЫ -->
    <div class="kb-panel" id="kp-materials">
      <p class="kb-intro-p">Завод поставляет полный спектр конструкционных сталей для трубопроводных систем. <strong>Каждая марка стали подтверждается сертификатом качества 3.1</strong> по ГОСТ ISO 10474-2016 с плавочными данными. Прослеживаемость металла от плавки до готового изделия фиксируется документально.</p>
      <div class="kb-mat-grid">
        <div class="kb-mat"><div class="kb-mat-grade">Ст20</div><div class="kb-mat-std">ГОСТ 1050-2013</div><div class="kb-mat-range">до +425°С · PN до 20 МПа</div><div class="kb-mat-apps">Водяные тракты ТЭС · Общепромышленные трубопроводы · НГК низкого давления</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">09Г2С</div><div class="kb-mat-std">ГОСТ 19281-2014</div><div class="kb-mat-range">−70…+350°С · Хладостойкая</div><div class="kb-mat-apps">Криогенные системы · Северное исполнение · НГК в условиях низких температур</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">15ГС</div><div class="kb-mat-std">ОСТ 108.030.118-78</div><div class="kb-mat-range">до +450°С</div><div class="kb-mat-apps">Трубопроводы ТЭС среднего давления · Питательные линии</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">12Х1МФ</div><div class="kb-mat-std">ОСТ 108.030.118-78</div><div class="kb-mat-range">до +570°С · Главные паропроводы</div><div class="kb-mat-apps">Паропроводы высокого давления ТЭС · Свежий пар 25 МПа / 545°С</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">15Х1М1Ф</div><div class="kb-mat-std">ТУ 14-3-460</div><div class="kb-mat-range">до +580°С · Сверхкритика</div><div class="kb-mat-apps">Сверхкритические параметры пара · Энергоблоки 300–800 МВт</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">12Х18Н10Т</div><div class="kb-mat-std">ГОСТ 5632-2014</div><div class="kb-mat-range">−196…+600°С · Нержавеющая</div><div class="kb-mat-apps">АЭС (все контуры) · Химически агрессивные среды · Крио</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">10Х17Н13М2Т</div><div class="kb-mat-std">ГОСТ 5632-2014</div><div class="kb-mat-range">до +700°С · Кислотостойкая</div><div class="kb-mat-apps">Сильноагрессивные среды · Кислоты · Хлориды</div></div>
        <div class="kb-mat"><div class="kb-mat-grade">13Х11Н2В2МФ</div><div class="kb-mat-std">ТУ · Спецназначение</div><div class="kb-mat-range">Мартенситная · Высокопрочная</div><div class="kb-mat-apps">Турбинные диски · Энергетические установки спецназначения</div></div>
      </div>
    </div>

    <!-- TAB 5: ДОКУМЕНТАЦИЯ -->
    <div class="kb-panel" id="kp-docs">
      <div class="kb-2col">
        <div>
          <div class="kb-col-title">Стандартный комплект поставки</div>
          <div class="kb-doclist">
            <div class="kb-doc-item"><div class="kb-doc-name">Паспорт изделия — сертификат 3.1</div><div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Химсостав плавки, механические свойства, результаты приёмочного контроля, маркировка, ссылка на норматив.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Сертификат на металл с плавочными данными</div><div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла: номер плавки, химсостав, механические характеристики, стандарт на металл.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Протокол ВИК — 100% объём</div><div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Подтверждает геометрическое соответствие и качество поверхности.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Протоколы УЗК / РК / МПД / ПВК</div><div class="kb-doc-desc">По требованию заказчика или нормативного документа. УЗК по ГОСТ Р 55724-2013.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Декларация ТР ТС 032/2013 <span class="kb-doc-badge">Обязательно</span></div><div class="kb-doc-desc">RU С-RU.АБ53.В.08323/23. Обязательна при PN &gt; 0.05 МПа для всей продукции в ЕАЭС.</div></div>
          </div>
        </div>
        <div>
          <div class="kb-col-title">Расширенный пакет АЭС <span style="font-weight:400;font-size:7px;color:var(--g1);">по НП-045-18</span></div>
          <div class="kb-doclist">
            <div class="kb-doc-item kb-aes"><div class="kb-doc-name">Программа контроля качества</div><div class="kb-doc-desc">Индивидуальная программа НК для каждой категории трубопровода, согласованная с заказчиком до запуска в производство.</div></div>
            <div class="kb-doc-item kb-aes"><div class="kb-doc-name">Карты идентификации и прослеживаемости</div><div class="kb-doc-desc">От заготовки до готовой детали. Номер плавки, номер детали, ссылки на все протоколы контроля.</div></div>
            <div class="kb-doc-item kb-aes"><div class="kb-doc-name">Технологические карты сварки и PWHT</div><div class="kb-doc-desc">По согласованным WPS и PQR. Параметры сварочных режимов и результаты послесварочной термообработки.</div></div>
          </div>
          <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
          <p class="kb-col-sub">Завод выполняет <strong>комплектные поставки</strong> по проектным спецификациям — от нескольких позиций до полной номенклатуры одного контура. Включает единую сводную ведомость, координацию нормативных документов по каждой позиции и персонального менеджера проекта для крупных комплектаций.</p>
        </div>
      </div>
    </div>

    <!-- TAB 6: ЗАКАЗ -->
    <div class="kb-panel" id="kp-order">
      <div class="kb-3col">
        <div>
          <div class="kb-col-title">Как подготовить заявку</div>
          <div class="kb-checklist">
            <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Наименование и норматив</div><div class="kb-check-body">Тип изделия и нормативный документ: отвод 90° по <strong>ГОСТ 17375</strong>, тройник по <strong>СТО ЦКТИ 720.03</strong>. Если норматив неизвестен — укажите объект.</div></div></div>
            <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN, PN, толщина стенки</div><div class="kb-check-body">DN (условный диаметр), PN в МПа или кгс/см², толщина стенки. Для фланцев — тип уплотнения (FF / RF / RTJ).</div></div></div>
            <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка или рабочие условия (t°С, среда) для подбора нашим инженером. Для АЭС — согласно программе контроля.</div></div></div>
            <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Штуки и желаемая дата поставки. Для крупных комплектаций — поэтапный график.</div></div></div>
            <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК и состав пакета документов. Для АЭС — категория трубопровода по <strong>НП-045-18</strong>.</div></div></div>
          </div>
        </div>
        <div>
          <div class="kb-col-title">Что влияет на стоимость</div>
          <div class="kb-factors">
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Жаропрочные и нержавеющие стали дороже углеродистых в 3–7 раз.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Полный объём НК для АЭС может в 2–4 раза превышать базовый (ВИК).</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN и толщина стенки</div><div class="kb-factor-note">Масса нелинейно растёт с DN. DN 500+ требует специальной оснастки.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийность от 10 шт. снижает себестоимость за счёт амортизации подготовки производства.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Срочность</div><div class="kb-factor-note">Менее 10 рабочих дней — приоритетная загрузка, оговаривается индивидуально.</div></div></div>
          </div>
        </div>
        <div>
          <div class="kb-col-title">Частые ошибки</div>
          <div class="kb-errors">
            <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">DN ≠ наружный диаметр</div><div class="kb-err-note">DN 50 = Dнар 57 мм по ГОСТ 8732. Всегда уточняйте стандарт трубы.</div></div></div>
            <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указана марка стали</div><div class="kb-err-note">«Сталь» без марки — не спецификация. Материал влияет на технологию и объём НК.</div></div></div>
            <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путаница PN и рабочего давления</div><div class="kb-err-note">PN при 20°С. При повышенных температурах допустимое давление снижается по таблицам нормы.</div></div></div>
            <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не учтена категория трубопровода</div><div class="kb-err-note">Для АЭС категория определяет весь объём НК и документации. Ошибка = срыв сдачи объекта.</div></div></div>
            <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть про ТР ТС 032/2013</div><div class="kb-err-note">Без декларации оборудование с PN &gt; 0.05 МПа не может быть введено в эксплуатацию в ЕАЭС.</div></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 7: ЧАСТЫЕ ВОПРОСЫ -->
    <div class="kb-panel" id="kp-faq">
      <div class="kb-lead">
        <div class="kb-lead-h">Частые вопросы о каталоге продукции</div>
        <p class="kb-lead-p">Ответы на вопросы инженеров-проектировщиков, специалистов снабжения и технического надзора при работе с реестром завода «Промышленная Энергетика»: навигация по каталогу, нормативная база, типы поставки, документация и сроки.</p>
      </div>
      <div class="faq-wrap">
        <div class="fq"><div class="fq-q"><span class="fq-num">01</span><span class="fq-t">Как пользоваться реестром каталога — фильтры, группы и технический паспорт?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Слева — <strong>8 номенклатурных групп</strong> (СДТ, фланцы, опоры, ЗРА и др.). Клик по группе фильтрует таблицу «Реестр изделий». Клик по строке открывает панель <strong>«Технический паспорт»</strong> справа: DN/PN, материал, нормативы, объём НК. Для категорий с отдельной страницей (например, СДТ) — кнопка «Страница категории →» в строке группы.</div></div></div>
        <div class="fq"><div class="fq-q"><span class="fq-num">02</span><span class="fq-t">Что означают типы «Производство», «Поставка» и «По КД» в реестре?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in"><strong>Производство</strong> — изделие изготавливается на заводе в Челябинске по нормативному документу. <strong>Поставка</strong> — позиция доступна со склада или у партнёра-изготовителя с полным пакетом документов завода. <strong>По КД</strong> — изготовление по конструкторской документации заказчика (DWG, PDF, STEP). Тип влияет на срок и минимальный объём — уточняется в коммерческом предложении.</div></div></div>
        <div class="fq"><div class="fq-q"><span class="fq-num">03</span><span class="fq-t">Как выбрать нормативный документ, если в проекте указано несколько ГОСТ / ОСТ / СТО?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Норматив определяется <strong>типом объекта и категорией трубопровода</strong>, а не только геометрией изделия. Для АЭС — НП-089-15 и НП-045-18 имеют приоритет над общепромышленными ГОСТ. Для ТЭС — СТО ЦКТИ 321/720 и ОСТ 34/36. В реестре в колонке «Норматив» показаны основные документы; полный список — во всплывающей подсказке. При сомнении направьте фрагмент спецификации на <strong>zakaz@prom-en.com</strong>.</div></div></div>
        <div class="fq"><div class="fq-q"><span class="fq-num">04</span><span class="fq-t">Охватывает ли каталог всю номенклатуру завода или только позиции в таблице?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Реестр содержит <strong>26+ типовых позиций</strong> в 8 группах — это рабочая выборка для проектирования и снабжения. Завод производит и поставляет <strong>полную номенклатуру трубопроводных изделий DN 15–1400</strong>, включая нестандартные детали по КД. Если нужной позиции нет в таблице — отправьте параметры или чертёж: мы добавим в расчёт и укажем норматив и срок.</div></div></div>
        <div class="fq"><div class="fq-q"><span class="fq-num">05</span><span class="fq-t">Нужна ли декларация ТР ТС 032/2013 для всех позиций каталога?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да, для оборудования с <strong>PN &gt; 0.05 МПа</strong> декларация обязательна при вводе в эксплуатацию в ЕАЭС. Вся продукция завода охвачена декларацией <strong>RU С-RU.АБ53.В.08323/23</strong> по ТР ТС 032/2013. Копия включается в комплект документов на каждую поставку независимо от группы изделия.</div></div></div>
        <div class="fq"><div class="fq-q"><span class="fq-num">06</span><span class="fq-t">Можно ли заказать комплектную поставку по ведомости проекта?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Завод выполняет <strong>комплектные поставки</strong> по проектным спецификациям — от нескольких позиций до полной номенклатуры одного контура. Включает сводную ведомость, согласование нормативов по каждой строке, единый график отгрузки и персонального менеджера для крупных комплектаций. Направьте ведомость в Excel/PDF или через форму запроса внизу страницы.</div></div></div>
        <div class="fq"><div class="fq-q"><span class="fq-num">07</span><span class="fq-t">Как быстро приходит ответ на запрос из каталога?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">На запрос с параметрами из реестра (тип, DN, PN, марка стали, количество) инженер завода отвечает <strong>в течение одного рабочего дня</strong>. В ответе — нормативный документ, доступные марки стали, ориентировочный срок изготовления и состав документации. Срочные позиции со склада (DN 50–200, Ст20 / 09Г2С) — от <strong>3–5 рабочих дней</strong>. Почта: <strong>zakaz@prom-en.com</strong>, тел. +7 (351) 217-00-99.</div></div></div>
      </div>
    </div><!-- /kp-faq -->

  </div><!-- /kb-panels -->
</div><!-- /cat-kb -->
