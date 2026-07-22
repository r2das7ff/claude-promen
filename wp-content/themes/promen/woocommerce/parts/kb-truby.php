<?php
/**
 * Секция 10 — база знаний «Трубы» (7 табов).
 * Структура и плотность — по образцу otvody; факты — CSV трубы (нормализованный).
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- S10: БАЗА ЗНАНИЙ — ТРУБЫ -->
  <section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / ТРУБЫ</div>
    </div>

    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды труб</button>
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
          <div class="kb-lead-h">Классификация стальных труб</div>
          <p class="kb-lead-p">В каталоге — <strong>четыре типа</strong>, <strong>1&nbsp;944 типоразмера</strong>: бесшовные (БШ), электросварные (ЭС), в ППУ-изоляции и водогазопроводные (ВГП). Подбор по D×s (DN), типу изготовления/изоляции и марке стали.</p>
        </div>
        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">БШ · 700</div>
            <div class="kb-card-title">Бесшовные · ГОСТ 8732 / 8734</div>
            <p class="kb-card-body">Горячедеформированные (ГОСТ 8732, <strong>595 поз.</strong>) и холоднодеформированные (ГОСТ 8734, <strong>105 поз.</strong>). Для давления, пара, НГК и ответственных трактов ТЭС. Диапазон DN от малых до крупных по таблицам стандартов.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 8732-1978</span><span class="kb-tag">ГОСТ 8734-1975</span><span class="kb-tag">700 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ЭС · 733</div>
            <div class="kb-card-title">Электросварные · ГОСТ 10704 / 10705</div>
            <p class="kb-card-body">Прямошовные электросварные: сортамент ГОСТ 10704 (<strong>522 поз.</strong>) и технические условия ГОСТ 10705 (<strong>211 поз.</strong>). Теплосети, общепром, технологические линии.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 10704-1991</span><span class="kb-tag">ГОСТ 10705-1980</span><span class="kb-tag">733 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ППУ · 441</div>
            <div class="kb-card-title">В ППУ-изоляции · ГОСТ 30732-2020</div>
            <p class="kb-card-body">Предизолированные трубы для тепловых сетей: пенополиуретан в оболочке ПЭ или ОЦ. <strong>441 позиция</strong>. Подбор по D×s стальной трубы, типу оболочки и длине плети.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 30732-2020</span><span class="kb-tag">Теплосети</span><span class="kb-tag">441 поз.</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ВГП · 70</div>
            <div class="kb-card-title">Водогазопроводные · ГОСТ 3262-1975</div>
            <p class="kb-card-body">Трубы ВГП — лёгкие, обыкновенные и усиленные; с резьбой или без. <strong>70 позиций</strong>. Внутренние сети воды и газа, ЖКХ.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 3262-1975</span><span class="kb-tag">ЖКХ</span><span class="kb-tag">70 поз.</span></div>
          </div>
        </div>
        <div class="kb-groups-hd">Сравнение типов</div>
        <div class="kb-groups">
          <div class="kb-grp"><span class="kb-grp-code">БШ</span><span class="kb-grp-name">Бесшовные · 700 поз.</span><span class="kb-grp-items">ГОСТ 8732 / 8734 · давление / пар</span></div>
          <div class="kb-grp"><span class="kb-grp-code">ЭС</span><span class="kb-grp-name">Электросварные · 733 поз.</span><span class="kb-grp-items">ГОСТ 10704 / 10705 · теплосети</span></div>
          <div class="kb-grp"><span class="kb-grp-code">ППУ</span><span class="kb-grp-name">В изоляции · 441 поз.</span><span class="kb-grp-items">ГОСТ 30732 · предизол. плети</span></div>
          <div class="kb-grp"><span class="kb-grp-code">ВГП</span><span class="kb-grp-name">Водогазопроводные · 70 поз.</span><span class="kb-grp-items">ГОСТ 3262 · ЖКХ</span></div>
        </div>
      </div>

      <!-- TAB 2: ПАРАМЕТРЫ -->
      <div class="kb-panel" id="kp-params">
        <div class="kb-lead">
          <div class="kb-lead-h">Что задаёт трубу</div>
          <p class="kb-lead-p">Типоразмер задаётся <strong>наружным диаметром и толщиной стенки</strong> (D×s) или условным проходом DN, типом (БШ / ЭС / ППУ / ВГП) и маркой стали. Для ППУ дополнительно — тип оболочки и длина плети.</p>
        </div>
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Чеклист заявки на трубу</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">D×s или DN</div><div class="kb-check-body">Наружный диаметр × толщина стенки (или условный проход). Пример: 159×6 / DN 150.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">Тип трубы</div><div class="kb-check-body"><strong>БШ</strong> — бесшовная; <strong>ЭС</strong> — электросварная; <strong>ППУ</strong> — предизолированная; <strong>ВГП</strong> — водогазопроводная.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Норматив</div><div class="kb-check-body">ГОСТ 8732 / 8734 / 10704 / 10705 / 30732 / 3262 — по объекту и проекту.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Ст20, сталь 10, Ст3сп, 09Г2С, 17Г1С-У и др. — по каталогу или условиям среды.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">ППУ / покрытие</div><div class="kb-check-body">Для ППУ: оболочка ПЭ или ОЦ, длина плети, система ОДК — по проекту теплосети.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Метраж / число труб, желаемая дата отгрузки, условия поставки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Ключевые параметры трубы</div>
            <div class="kb-params">
              <div class="kb-param"><div class="kb-param-key">D×s</div><div class="kb-param-val">Наружный диаметр × толщина стенки. Должны соответствовать ряду выбранного ГОСТ.</div></div>
              <div class="kb-param"><div class="kb-param-key">DN</div><div class="kb-param-val">Условный проход — для ВГП и при указании в проекте; сверяйте с таблицей D×s.</div></div>
              <div class="kb-param"><div class="kb-param-key">Тип</div><div class="kb-param-val"><strong>БШ / ЭС / ППУ / ВГП</strong> — определяет норматив, технологию и область применения.</div></div>
              <div class="kb-param"><div class="kb-param-key">Марка стали</div><div class="kb-param-val"><strong>20 / 10 / Ст3сп</strong> — типовые; <strong>09Г2С</strong> — хладостойкость; <strong>17Г1С-У</strong> — трубные ряды НГК.</div></div>
              <div class="kb-param"><div class="kb-param-key">Длина</div><div class="kb-param-val">Мерная / немерная / кратная. Для ППУ — длина предизолированной плети по проекту.</div></div>
              <div class="kb-param"><div class="kb-param-key">НК</div><div class="kb-param-val">Базовый ВИК; для ЭС — контроль шва; для поднадзорных — объём по ТР ТС 032 и КД.</div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: НОРМЫ -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор нормативного документа определяет сортамент, технические требования, объём контроля и комплектность поставки. В каталоге труб — <strong>шесть действующих серий</strong> ГОСТ.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Бесшовные</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 8732-1978</span><span class="kb-norm-desc">Сортамент бесшовных горячедеформированных. 595 позиций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 8734-1975</span><span class="kb-norm-desc">Сортамент бесшовных холоднодеформированных. 105 позиций</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">Электросварные</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 10704-1991</span><span class="kb-norm-desc">Сортамент прямошовных электросварных. 522 позиции</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 10705-1980</span><span class="kb-norm-desc">Технические условия на электросварные трубы. 211 позиций</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ППУ и ВГП</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30732-2020</span><span class="kb-norm-desc">Трубы и фасонные изделия в ППУ-изоляции. 441 позиция</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 3262-1975</span><span class="kb-norm-desc">Водогазопроводные трубы. 70 позиций</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / ТУ / металл</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">Оборудование под избыточным давлением. Обязателен при PN &gt; 0,05 МПа</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия — комплектность, маркировка, объём контроля</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт 3.1</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050 / 19281</span><span class="kb-norm-desc">Марки стали Ст20, 10, 09Г2С</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 4: МАТЕРИАЛЫ -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">В каталоге труб — углеродистые и низколегированные марки: <strong>Ст20, сталь 10, Ст3сп, 09Г2С, 17Г1С-У</strong> и др. <strong>Каждая партия поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с плавочными данными. Прослеживаемость металла фиксируется документально.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731/8732</div>
            <div class="kb-mat-range">до +425°С · основная марка</div>
            <div class="kb-mat-apps">ТЭС · Общепром · НГК · Теплосети · типовые БШ и ЭС</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">Сталь 10</div>
            <div class="kb-mat-std">ГОСТ 1050-2013</div>
            <div class="kb-mat-range">Общепромышленные трубопроводы</div>
            <div class="kb-mat-apps">ЭС и БШ типовых рядов · Технологические линии</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст3сп</div>
            <div class="kb-mat-std">ГОСТ 380 / трубные ряды</div>
            <div class="kb-mat-range">Общепром · низкое/среднее давление</div>
            <div class="kb-mat-apps">ЭС · ВГП · строительные и сетевые применения</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">09Г2С</div>
            <div class="kb-mat-std">ГОСТ 19281-2014</div>
            <div class="kb-mat-range">−70…+350°С · хладостойкая</div>
            <div class="kb-mat-apps">Северное исполнение · НГК · низкие температуры</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">17Г1С-У</div>
            <div class="kb-mat-std">Трубная · повышенная прочность</div>
            <div class="kb-mat-range">Магистральные / технологические</div>
            <div class="kb-mat-apps">НГК · сопряжение с трубными рядами проекта</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">По проекту</div>
            <div class="kb-mat-std">КД заказчика</div>
            <div class="kb-mat-range">Спецмарки и покрытия</div>
            <div class="kb-mat-apps">Согласование марки, покрытия и объёма НК до запуска поставки</div>
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
                <div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Химсостав плавки, механические свойства, результаты приёмочного контроля, маркировка, ссылка на норматив.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл с плавочными данными</div>
                <div class="kb-doc-desc">Прослеживаемость от плавки: номер плавки, химсостав, механические характеристики, стандарт на металл.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК — 100% объём</div>
                <div class="kb-doc-desc">Визуально-измерительный контроль. Геометрия D×s, качество поверхности, для ЭС — состояние шва.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протоколы УЗК / РК по требованию</div>
                <div class="kb-doc-desc">Для электросварных и поднадзорных объектов — контроль шва и объём НК по заказу / ТР ТС 032.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Декларация ТР ТС 032/2013 <span class="kb-doc-badge">Обязательно</span></div>
                <div class="kb-doc-desc">RU С-RU.АБ53.В.08323/23. Обязательна при PN&nbsp;&gt;&nbsp;0.05&nbsp;МПа для продукции в ЕАЭС.</div>
              </div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Для труб в ППУ</div>
            <div class="kb-doclist">
              <div class="kb-doc-item">
                <div class="kb-doc-name">Паспорт предизолированного изделия</div>
                <div class="kb-doc-desc">Данные по стальной трубе, изоляции и оболочке по ГОСТ 30732-2020.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Система ОДК (по проекту)</div>
                <div class="kb-doc-desc">Комплектность проводников контроля влажности — если предусмотрено проектом теплосети.</div>
              </div>
            </div>
            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> труб вместе с СДТ, фланцами и фасонными в ППУ одного контура. Единая сводная ведомость и координация нормативов по позициям.</p>
          </div>
        </div>
      </div>

      <!-- TAB 6: ЗАКАЗ -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">
          <div>
            <div class="kb-col-title">Как подготовить заявку на трубу</div>
            <div class="kb-checklist">
              <div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип и норматив</div><div class="kb-check-body">БШ / ЭС / ППУ / ВГП и ГОСТ (8732, 8734, 10704, 10705, 30732, 3262).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">D×s или DN</div><div class="kb-check-body">Наружный диаметр × толщина стенки (или условный проход для ВГП).</div></div></div>
              <div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Марка стали</div><div class="kb-check-body">Ст20, 10, Ст3сп, 09Г2С, 17Г1С-У — по каталогу или условиям среды.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Длина / метраж</div><div class="kb-check-body">Мерная, немерная или кратная. Для ППУ — длина плети.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">05</span><div><div class="kb-check-title">Объём НК</div><div class="kb-check-body">Базовый ВИК; для ЭС и поднадзора — УЗК/РК по заказу.</div></div></div>
              <div class="kb-check"><span class="kb-check-n">06</span><div><div class="kb-check-title">Срок и объект</div><div class="kb-check-body">Желаемая дата отгрузки и адрес / город доставки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Тип трубы</div><div class="kb-factor-note">БШ и ППУ обычно дороже типовых ЭС при сопоставимом D×s за счёт технологии и комплектации.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Марка стали</div><div class="kb-factor-note">Низколегированные и спецмарки дороже Ст20 / Ст3сп.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Объём НК</div><div class="kb-factor-note">Расширенный контроль увеличивает стоимость относительно базового ВИК.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Диаметр и толщина</div><div class="kb-factor-note">Крупный D и толстая стенка — больше масса и логистика.</div></div></div>
              <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Объём партии</div><div class="kb-factor-note">Крупный метраж снижает удельную стоимость подготовки и отгрузки.</div></div></div>
            </div>
          </div>
          <div>
            <div class="kb-col-title">Частые ошибки при заказе</div>
            <div class="kb-errors">
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">DN ≠ наружный диаметр</div><div class="kb-err-note">Для DN 50 наружный диаметр по ГОСТ 8732 = 57 мм. Уточняйте стандарт ряда.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Путать БШ и ЭС</div><div class="kb-err-note">Бесшовная и электросварная — разные нормативы и области применения.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">ППУ без оболочки</div><div class="kb-err-note">Укажите ПЭ или ОЦ — от этого зависит тип прокладки.</div></div></div>
              <div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Забыть ТР ТС 032</div><div class="kb-err-note">При PN &gt; 0.05 МПа в ЕАЭС нужна декларация.</div></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 7: ДОСТАВКА -->
      <div class="kb-panel" id="kp-delivery">
        <div class="kb-lead">
          <div class="kb-lead-h">Доставка и оплата</div>
          <p class="kb-lead-p">Отгрузка — после приёмки ОТК и комплектования пакета документов. Стоимость и срок доставки рассчитываются вместе с КП: укажите город или объект в заявке.</p>
        </div>
        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">ДОСТАВКА</div>
            <div class="kb-card-title">Транспортными компаниями по всей России</div>
            <p class="kb-card-body">Отгружаем ТК по выбору заказчика либо предлагаем перевозчика под габарит и срок. Длинномер и ППУ-плети — по согласованной схеме.</p>
            <div class="kb-card-tags"><span class="kb-tag">ТК по выбору</span><span class="kb-tag">Длинномер — по согласованию</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">САМОВЫВОЗ</div>
            <div class="kb-card-title">Со склада завода в Челябинске</div>
            <p class="kb-card-body">454091, г. Челябинск, ул. Орджоникидзе, 37. Отгрузка в рабочие дни 09:00–18:00 МСК после уведомления о готовности. Погрузка силами завода.</p>
            <div class="kb-card-tags"><span class="kb-tag">Пн–Пт 09:00–18:00</span><span class="kb-tag">Погрузка заводом</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">УПАКОВКА</div>
            <div class="kb-card-title">Защита торцов и маркировка</div>
            <p class="kb-card-body">Пакетирование / обрешётка по габариту, защита торцов, маркировка позиций. Для ППУ — бережная погрузка оболочки. Документы — с грузом и по email.</p>
            <div class="kb-card-tags"><span class="kb-tag">Упаковочный лист</span><span class="kb-tag">Паспорт · Сертификат 3.1</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ОПЛАТА</div>
            <div class="kb-card-title">Безналичный расчёт с НДС</div>
            <p class="kb-card-body">Счёт по согласованному КП. Аванс и доплата по готовности либо график по договору; рамочные и объектные условия — индивидуально.</p>
            <div class="kb-card-tags"><span class="kb-tag">Б/н с НДС</span><span class="kb-tag">По договору</span></div>
          </div>
        </div>
      </div>

    </div><!-- /kb-panels -->
  </section>
