<?php
/**
 * Секция 10 — база знаний «Отводы» (6 табов).
 * Разметка 1:1 из design-reference/product-otvod-90.html; динамика — PHP.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ОТВОДЫ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">ОТВОДЫ<?php echo esc_html( $angle_sp ); ?></div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды отводов<?php echo esc_html( $angle_sp ); ?></button>
      <button class="kb-tab" data-panel="params" role="tab"><span class="kb-tab-n">02</span>Параметры подбора</button>
      <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
      <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
      <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
      <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Как заказать</button>
      <button class="kb-tab" data-panel="delivery" role="tab"><span class="kb-tab-n">07</span>Доставка и оплата</button>
    </div>

    <div class="kb-panels">

      <!-- TAB 1: ВИДЫ ОТВОДОВ -->
      <div class="kb-panel kp-active" id="kp-types">
        <div class="kb-lead">
          <div class="kb-lead-h">Классификация отводов<?php echo esc_html( $angle_sp ); ?></div>
          <p class="kb-lead-p">Отводы<?php echo esc_html( $angle_sp ); ?> — один из наиболее распространённых типов соединительных деталей трубопровода. Выбор конструктивного исполнения определяется диаметром трубопровода, рабочим давлением, применяемым нормативным документом и доступной технологией изготовления. Завод «Промышленная Энергетика» поставляет <strong>все четыре основных типа</strong>.</p>
        </div>

        <div class="kb-type-grid">
          <div class="kb-type">
            <div class="kb-type-badge">ГОСТ 17375</div>
            <div class="kb-type-title">Крутоизогнутые штампованные · R = 1.5DN</div>
            <p class="kb-type-body">Изготавливаются штамповкой из трубной заготовки. <strong>DN 15–500</strong>, радиус изгиба R = 1.5DN. Основной тип для общепромышленных трубопроводов, НГК и трубопроводов ТЭС до DN 500. Минимальные потери давления в системе. Допускают сварку непосредственно к трубе без переходных патрубков.</p>
            <div class="kb-type-tags"><span class="kb-tag">ГОСТ 17375-2001</span><span class="kb-tag">DN 15–500</span><span class="kb-tag">R = 1.5DN</span><span class="kb-tag">Штамповка</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">ОСТ 36-21-77</div>
            <div class="kb-type-title">Секторные сварные · DN 100–1400</div>
            <p class="kb-type-body">Сварные отводы из сегментов (секторов). <strong>DN 100–1400</strong>, R = 1.5DN. Применяются при больших диаметрах, когда штамповка нецелесообразна. Широко используются в паровых трактах ТЭС и ГРЭС. Изготавливаются по ОСТ 36-21-77, для энергетики — по сводному тому ОСТ 34 10.747-97 ÷ 10.754-97.</p>
            <div class="kb-type-tags"><span class="kb-tag">ОСТ 36-21-77</span><span class="kb-tag">DN 100–1400</span><span class="kb-tag">Сварные секторные</span><span class="kb-tag">ТЭС / ГРЭС</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">СТО ЦКТИ 321</div>
            <div class="kb-type-title">Гнутые трубные · R = 3.5–5DN</div>
            <p class="kb-type-body">Гнутые из трубной заготовки на специальном оборудовании. <strong>R = 3.5–5DN</strong> — большой радиус изгиба снижает гидравлическое сопротивление и напряжения. Применяются в главных паропроводах ТЭС, где важно снизить эрозионный износ. Нормируются серией СТО ЦКТИ 321.01–321.08 (2009 г.): исполнения .01–.04 — для трубопроводов питательной воды, пара и горячей воды, .05–.08 — для паропроводов из хромомолибденованадиевых сталей.</p>
            <div class="kb-type-tags"><span class="kb-tag">СТО ЦКТИ 321.01–.08</span><span class="kb-tag">R = 3.5–5DN</span><span class="kb-tag">Гнутые</span><span class="kb-tag">Главные паропроводы</span></div>
          </div>
          <div class="kb-type">
            <div class="kb-type-badge">ОСТ 36-20-77</div>
            <div class="kb-type-title">Штампосварные · DN 25–400</div>
            <p class="kb-type-body">Изготавливаются методом горячей штамповки с последующей сваркой для закрытия шва. <strong>DN 25–400</strong>, R = 1.5DN. Используются в трубопроводах ТЭС и нефтехима, когда размер превышает возможности цельноштампованного производства, но меньше экономичного диапазона секторной сварки.</p>
            <div class="kb-type-tags"><span class="kb-tag">ОСТ 36-20-77</span><span class="kb-tag">DN 25–400</span><span class="kb-tag">Штампосварные</span></div>
          </div>
        </div>

        <div style="font-family:'DINPro',monospace;font-size:10px;letter-spacing:.28em;text-transform:uppercase;color:var(--g1);margin-bottom:10px;display:flex;align-items:center;gap:16px;"><span style="flex:1;height:1px;background:var(--ln);display:block;"></span>Сравнение типов<span style="flex:1;height:1px;background:var(--ln);display:block;"></span></div>
        <div class="kb-compare">
          <div class="kb-compare-hd">
            <span class="kb-cmp-h">Параметр</span>
            <span class="kb-cmp-h">Крутоизогн. (ГОСТ 17375)</span>
            <span class="kb-cmp-h">Секторные (ОСТ 36-21)</span>
            <span class="kb-cmp-h">Гнутые (СТО ЦКТИ 321)</span>
            <span class="kb-cmp-h">Штампосварные (ОСТ 36-20)</span>
          </div>
          <div class="kb-compare-row"><span class="kb-cmp-k">DN</span><span class="kb-cmp-v">15–500</span><span class="kb-cmp-v">100–1400</span><span class="kb-cmp-v">Согласно ТУ</span><span class="kb-cmp-v">25–400</span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Радиус R</span><span class="kb-cmp-v"><strong>1.5DN</strong></span><span class="kb-cmp-v"><strong>1.5DN</strong></span><span class="kb-cmp-v"><strong>3.5–5DN</strong></span><span class="kb-cmp-v"><strong>1.5DN</strong></span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Метод</span><span class="kb-cmp-v">Штамповка</span><span class="kb-cmp-v">Сварка секторов</span><span class="kb-cmp-v">Гибка трубы</span><span class="kb-cmp-v">Штамп + сварка</span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Применение</span><span class="kb-cmp-v">НГК, ТЭС, общепром.</span><span class="kb-cmp-v">ТЭС/ГРЭС, крупный диаметр</span><span class="kb-cmp-v">Главные паропроводы ТЭС</span><span class="kb-cmp-v">ТЭС, нефтехим.</span></div>
          <div class="kb-compare-row"><span class="kb-cmp-k">Потери давления</span><span class="kb-cmp-v">Умеренные</span><span class="kb-cmp-v">Повышенные (сварные швы)</span><span class="kb-cmp-v"><strong>Минимальные</strong></span><span class="kb-cmp-v">Умеренные</span></div>
        </div>
      </div>

      <!-- TAB 2: ПАРАМЕТРЫ ПОДБОРА -->
      <div class="kb-panel" id="kp-params">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Ключевые параметры отвода<?php echo esc_html( $angle_sp ); ?></div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">DN · Диаметр условный</div><div class="kb-param-val">От <strong>DN 15 до DN 1400</strong>. Соответствует условному проходу трубы. <strong>DN ≠ Dнар</strong>: DN 50 = 57 мм по ГОСТ 8732. Крутоизогнутые (ГОСТ 17375) — до DN 500. Секторные (ОСТ 36-21) — от DN 100 до DN 1400.</div></div>
              <div class="kb-param"><div class="kb-param-key">PN · Давление условное</div><div class="kb-param-val">От <strong>PN 0.6 до PN 20 МПа</strong>. Толщина стенки определяется из расчёта прочности по нормативному документу. При температурах выше 100°С допустимое давление снижается — уточняйте по таблицам норматива.</div></div>
              <div class="kb-param"><div class="kb-param-key">Радиус изгиба R</div><div class="kb-param-val"><strong>R = 1.5DN</strong> — крутоизогнутые (ГОСТ 17375, ОСТ 36-20, ОСТ 36-21). <strong>R = 3.5–5DN</strong> — гнутые (СТО ЦКТИ 321). Больший R снижает гидравлическое сопротивление и эрозию потоком.</div></div>
              <div class="kb-param"><div class="kb-param-key">Толщина стенки S</div><div class="kb-param-val">Определяется по нормативному документу из условия прочности при заданных DN, PN и t°С. Для ответственных объектов — дополнительный запас по прибавке на коррозию.</div></div>
              <div class="kb-param"><div class="kb-param-key">Нормативный документ</div><div class="kb-param-val"><strong>ГОСТ 17375</strong> — DN 15–500, общепром. <strong>ОСТ 36-21-77</strong> — Dy 500–1400, ТЭС. <strong>СТО ЦКТИ 321.01–.08</strong> — гнутые и штампованные для ТЭС. <strong>НП-045-18</strong> — для АЭС (расширенный НК). Норматив определяет допуски, объём контроля и документацию.</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка стали</div><div class="kb-param-val"><strong>Ст20</strong> — общепром., до +425°С. <strong>09Г2С</strong> — низкие температуры, НГК. <strong>12Х1МФ</strong> — паропроводы ТЭС до +570°С. <strong>15Х1М1Ф</strong> — сверхкритика +580°С. <strong>12Х18Н10Т</strong> — АЭС, агрессивные среды.</div></div>
              <div class="kb-param"><div class="kb-param-key">Объём НК</div><div class="kb-param-val">Базово: <strong>100% ВИК</strong>. По требованию заказчика: +<strong>УЗК</strong> (ГОСТ Р 55724-2013) / +<strong>РК</strong> / +<strong>МПД</strong> / +<strong>ПВК</strong>. Полный объём для АЭС — согласно НП-045-18 и программе контроля объекта.</div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Как выбрать тип отвода</div>
            <div class="kb-steps">
              <div class="kb-step"><span class="kb-step-n">01</span><div><div class="kb-step-title">Определите DN трубопровода</div><div class="kb-step-body">DN до 500 — выбор между ГОСТ 17375 (крутоизогнутые) и СТО ЦКТИ 321 (гнутые). DN 100–1400 и ТЭС — рассмотрите секторные по ОСТ 36-21-77.</div></div></div>
              <div class="kb-step"><span class="kb-step-n">02</span><div><div class="kb-step-title">Проверьте требования объекта</div><div class="kb-step-body">АЭС — обязательно НП-089-15 и НП-045-18. Главные паропроводы ТЭС — предпочтительны гнутые СТО ЦКТИ 321 с R = 3.5–5DN. Общепромышленные — ГОСТ 17375.</div></div></div>
              <div class="kb-step"><span class="kb-step-n">03</span><div><div class="kb-step-title">Учтите гидравлику</div><div class="kb-step-body">На высокоскоростных паропроводах (v &gt; 40 м/с) рекомендуются <strong>гнутые отводы с R = 3.5–5DN</strong> — снижают эрозионный износ и турбулентность потока.</div></div></div>
              <div class="kb-step"><span class="kb-step-n">04</span><div><div class="kb-step-title">Согласуйте материал и НК</div><div class="kb-step-body">Для ответственных объектов — до начала производства согласуйте марку стали с нашим инженером и объём НК с заказчиком / представителем органа надзора.</div></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: НОРМАТИВНАЯ БАЗА -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор нормативного документа для отвода<?php echo esc_html( $angle_sp ); ?> определяет конструктивное исполнение, допуски на геометрию, требования к металлу и полный объём контроля. Большинство позиций каталога охвачено одновременно базовым ГОСТ и дополнительным отраслевым стандартом для конкретного типа объектов.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — базовые стандарты</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17375-2001</span><span class="kb-norm-desc">Отводы крутоизогнутые типа 3D (R ≈ 1,5DN). Конструкция. Углы 45°/60°/90°/180° — основной стандарт каталога</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30753-2001</span><span class="kb-norm-desc">Отводы крутоизогнутые типа 2D (R ≈ DN). Конструкция — исполнение малого радиуса той же серии</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17380-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные из углеродистой и низколегированной стали. Общие технические условия — головной документ серии</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 22793-83 / 22818-83</span><span class="kb-norm-desc">Отводы гнутые и колена 90° с опорой на Ру св. 10 до 100 МПа. DN 6–200, t −50…+510 °C — нефтехимия</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 24950-81</span><span class="kb-norm-desc">Отводы гнутые и вставки кривые на поворотах линейной части стальных магистральных трубопроводов. ТУ</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений. Методы и оценка результатов</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ОСТ — тепловая и атомная энергетика</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34 10.699-97</span><span class="kb-norm-desc">Отводы крутоизогнутые на Рраб &lt; 2,2 МПа для атомных и тепловых электростанций. Конструкция и размеры</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34 10.747-97 ÷ 10.754-97</span><span class="kb-norm-desc">Детали и сборочные единицы трубопроводов ТЭС из углеродистой и низколегированной сталей, Рраб &lt; 2,2 МПа, t ≤ 425 °C. Часть I</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34-10-416-90 ÷ 433-90</span><span class="kb-norm-desc">Детали трубопроводов из коррозионностойкой стали на Рраб ≤ 2,2 МПа, T ≤ 300 °C для АС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-20-77</span><span class="kb-norm-desc">Отводы штампосварные Dy 500–1400 мм из углеродистой стали на Ру ≤ 2,5 МПа</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-21-77</span><span class="kb-norm-desc">Отводы сварные секторные Dy 500–1400 мм — паровые и водяные тракты ТЭС/ГРЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-41-81 — 36-49-81</span><span class="kb-norm-desc">Детали трубопроводов из углеродистой стали сварные и гнутые Dy до 500 мм на Ру до 10 МПа</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">СТО ЦКТИ — отводы для ТЭС, ресурс 200 000 ч</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.01-2009</span><span class="kb-norm-desc">Отводы гнутые для трубопроводов питательной воды тепловых станций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.02-2009</span><span class="kb-norm-desc">Отводы гнутые для трубопроводов пара и горячей воды тепловых станций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.03 / 321.04-2009</span><span class="kb-norm-desc">Отводы крутоизогнутые и штампованные для трубопроводов пара и горячей воды ТЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.05–321.08-2009</span><span class="kb-norm-desc">Отводы гнутые, крутоизогнутые, штампованные и штампосварные для паропроводов ТЭС — хромомолибденованадиевые стали, p ≥ 4,0 МПа</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">НП и ТР ТС — АЭС и обязательные нормы</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">НП-045-18</span><span class="kb-norm-desc">Правила контроля сварных соединений оборудования и трубопроводов АЭУ. Расширенный НК</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-089-15</span><span class="kb-norm-desc">Общие требования к оборудованию и трубопроводам АЭУ. Категории I–IV</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования под давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-2023</span><span class="kb-norm-desc">ТУ предприятия на детали трубопроводов. Применяется при изготовлении по КД заказчика</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: МАТЕРИАЛЫ -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">Марка стали для отвода<?php echo esc_html( $angle_sp ); ?> выбирается исходя из рабочей температуры, давления и характера транспортируемой среды. <strong>Сертификат качества 3.1 по ГОСТ ISO 10474-2016</strong> с плавочными данными включён в стандартный комплект поставки для каждой позиции.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat"><div class="kb-mat-grade">Ст20</div><div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731-87</div><div class="kb-mat-range">до +425°С · PN до 20 МПа</div><div class="kb-mat-apps">Водяные тракты ТЭС · Общепромышленные трубопроводы · Паропроводы низкого давления</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">09Г2С</div><div class="kb-mat-std">ГОСТ 19281-2014</div><div class="kb-mat-range">−70…+350°С · Хладостойкая</div><div class="kb-mat-apps">НГК в районах с низкими температурами · Северное исполнение · Криогенные системы</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">15ГС</div><div class="kb-mat-std">ОСТ 108.030.118-78</div><div class="kb-mat-range">до +450°С</div><div class="kb-mat-apps">Трубопроводы ТЭС среднего давления · Питательные трубопроводы</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">12Х1МФ</div><div class="kb-mat-std">ОСТ 108.030.118-78</div><div class="kb-mat-range">до +570°С · Главные паропроводы</div><div class="kb-mat-apps">Паропроводы высокого давления ТЭС и ГРЭС · Свежий пар 25 МПа / 545°С · Линии промперегрева</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">15Х1М1Ф</div><div class="kb-mat-std">ТУ 14-3-460</div><div class="kb-mat-range">до +580°С · Сверхкритика</div><div class="kb-mat-apps">Энергоблоки СКД 300–800 МВт · Повышенные требования к длительной прочности</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">12Х18Н10Т</div><div class="kb-mat-std">ГОСТ 5632-2014</div><div class="kb-mat-range">−196…+600°С · Нержавеющая</div><div class="kb-mat-apps">АЭС — все контуры, все категории · Химически агрессивные среды · Пищевая/фарм. промышленность</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">10Х17Н13М2Т</div><div class="kb-mat-std">ГОСТ 5632-2014</div><div class="kb-mat-range">до +700°С · Кислотостойкая</div><div class="kb-mat-apps">Кислоты · Хлориды · Агрессивные химические среды высоких температур</div></div>
          <div class="kb-mat"><div class="kb-mat-grade">13Х11Н2В2МФ</div><div class="kb-mat-std">ТУ · Спецназначение</div><div class="kb-mat-range">Мартенситная · Высокопрочная</div><div class="kb-mat-apps">Энергетические установки со специальными прочностными требованиями</div></div>
        </div>
      </div>

      <!-- TAB 5: ДОКУМЕНТАЦИЯ -->
      <div class="kb-panel" id="kp-docs">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Стандартный комплект поставки</div>
            <div class="kb-doclist">
              <div class="kb-doc-item"><div class="kb-doc-name">Паспорт изделия — сертификат 3.1</div><div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Химсостав плавки, механические свойства, результаты приёмочного контроля, маркировка, ссылка на норматив.</div></div>
              <div class="kb-doc-item"><div class="kb-doc-name">Сертификат на металл с плавочными данными</div><div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла. Номер плавки, химсостав, механические характеристики.</div></div>
              <div class="kb-doc-item"><div class="kb-doc-name">Протокол ВИК — 100% объём</div><div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Геометрическое соответствие и качество поверхности.</div></div>
              <div class="kb-doc-item"><div class="kb-doc-name">Протоколы УЗК / РК / МПД / ПВК</div><div class="kb-doc-desc">По требованию заказчика или нормативного документа. УЗК по ГОСТ Р 55724-2013.</div></div>
              <div class="kb-doc-item"><div class="kb-doc-name">Декларация ТР ТС 032/2013 <span class="kb-doc-badge">Обязательно</span></div><div class="kb-doc-desc">RU С-RU.АБ53.В.08323/23. Обязательна при PN &gt; 0.05 МПа.</div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:10px;color:var(--g1);">по НП-045-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes"><div class="kb-doc-name">Программа контроля качества</div><div class="kb-doc-desc">Индивидуальная программа НК для категории трубопровода. Согласовывается до запуска в производство.</div></div>
              <div class="kb-doc-item kb-aes"><div class="kb-doc-name">Карты идентификации и прослеживаемости</div><div class="kb-doc-desc">От заготовки до готовой детали. Номер плавки, детали, ссылки на все протоколы контроля.</div></div>
              <div class="kb-doc-item kb-aes"><div class="kb-doc-name">Технологические карты сварки и PWHT</div><div class="kb-doc-desc">По согласованным WPS и PQR. Параметры режимов сварки и послесварочной термообработки.</div></div>
            </div>
            <div class="kb-col-title" style="margin-top:24px;">Маркировка изделий</div>
            <p class="kb-col-sub">По требованию заказчика возможна <strong>индивидуальная маркировка</strong>: код объекта, номер спецификации, позиция трубопровода. Маркировка выполняется по ГОСТ 4666-2015 и внутреннему регламенту завода.</p>
          </div>
        </div>
      </div>

      <!-- TAB 6: КАК ЗАКАЗАТЬ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Чеклист заявки на отвод<?php echo esc_html( $angle_sp ); ?></div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип и нормативный документ</div><div class="kb-check-body">Крутоизогнутый <strong>ГОСТ 17375</strong>; секторный <strong>ОСТ 36-21</strong>; гнутый <strong>СТО ЦКТИ 321.0X</strong>; штампосварной <strong>ОСТ 36-20</strong>. Если тип неизвестен — укажите DN, объект, рабочие параметры.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN и толщина стенки</div><div class="kb-check-body">DN в мм и толщину стенки (если нестандартная). При отсутствии — указать PN и t°С для подбора нашим инженером.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Точная марка или условия: t°С, среда, требования объекта. Для АЭС — согласно программе контроля и категории трубопровода.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Количество в штуках. Желаемая дата поставки или срок с момента подтверждения. Для крупных комплектаций — поэтапный график.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК и документация</div><div class="kb-check-body">Методы НК (ВИК + УЗК / РК / МПД / ПВК). Состав документационного пакета. Для АЭС — категория трубопровода по <strong>НП-045-18</strong>.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Маркировка и спецтребования</div><div class="kb-check-body">Маркировка объекта, позиция трубопровода по проекту, цвет, индивидуальная упаковка. Нестандартные требования — укажите при запросе.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип отвода</div><div class="kb-factor-note">Гнутые (СТО ЦКТИ) и секторные (ОСТ 36-21) DN 800–1400 дороже крутоизогнутых ГОСТ 17375 при тех же DN за счёт трудоёмкости изготовления.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Жаропрочные (12Х1МФ) в 2–3 раза, нержавеющие (12Х18Н10Т) в 5–6 раз дороже углеродистой Ст20.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Полный объём НК для АЭС (УЗК+РК+МПД+ПВК) может в 2–4 раза увеличить стоимость по сравнению с базовым ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Тираж заказа</div><div class="kb-factor-note">Серийный заказ (от 10–20 шт.) снижает себестоимость за счёт амортизации затрат на подготовку производства.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN и толщина стенки</div><div class="kb-factor-note">DN 500+ требует специализированной оснастки. Крупногабаритные детали DN 800–1400 — индивидуальное производство.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Указать Dнар вместо DN</div><div class="kb-err-note">DN 50 ≠ 50 мм. Наружный диаметр трубы по ГОСТ 8732 для DN 50 = 57 мм. Всегда уточняйте стандарт трубы.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать R/DN для гнутых</div><div class="kb-err-note">Серия СТО ЦКТИ 321 содержит 8 исполнений с разными R и назначением тракта. Если R не указан — уточняется по номеру типа или рабочим условиям.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Заказ ГОСТ 17375 для DN 600+</div><div class="kb-err-note">ГОСТ 17375 распространяется до DN 500. Для Dy 500–1400 необходим ОСТ 36-21-77 (секторные) или специсполнение по ТУ предприятия.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не учесть категорию (АЭС)</div><div class="kb-err-note">Категория I–IV по НП-089-15 определяет весь объём НК и документации. Ошибка в категории = несоответствие программе контроля.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путаница PN и рабочего давления</div><div class="kb-err-note">При t &gt; 100°С допустимое давление снижается. PN подбирается с учётом рабочей температуры по таблицам норматива.</div></div></div>
            </div>
          </div>
        </div>
      </div>


      <!-- TAB 7: ДОСТАВКА И ОПЛАТА -->
      <div class="kb-panel" id="kp-delivery">
        <div class="kb-lead">
          <div class="kb-lead-h">Доставка и оплата</div>
          <p class="kb-lead-p">Отгрузка — после приёмки ОТК и комплектования пакета документов. Стоимость и срок доставки рассчитываются вместе с коммерческим предложением: укажите город или объект в заявке — менеджер включит логистику в расчёт.</p>
        </div>
        <div class="kb-type-grid">
          <div class="kb-type">
            <div class="kb-type-badge">ДОСТАВКА</div>
            <div class="kb-type-title">Транспортными компаниями по всей России</div>
            <p class="kb-type-body">Отгружаем любой транспортной компанией по выбору заказчика либо предлагаем оптимального перевозчика под габарит и срок. Негабаритные позиции (секторные отводы крупных DN) — по согласованной схеме перевозки.</p>
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
            <div class="kb-type-tags"><span class="kb-tag">Упаковочный лист</span><span class="kb-tag">Паспорт · Сертификат 3.1</span></div>
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
