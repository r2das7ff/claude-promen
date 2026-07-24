<?php
/**
 * Контент категории «otvody»: hero, карта типоисполнений, подбор, знания, модалка.
 * Извлечено 1:1 из прежнего taxonomy-шаблона; каркас — inc/category-page.php.
 */

defined( 'ABSPATH' ) || exit;

return [
	'hero' => static function ( array $ctx ): void { ?>
<div class="sdt-hero" id="hero">
    <div class="hero-left">
      <nav class="hero-crumb">
        <?php foreach ( $ctx['crumbs'] as $i => [ $label, $url ] ) : ?>
          <?php if ( $i > 0 ) : ?><span class="hero-crumb-sep">/</span><?php endif; ?>
          <?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
          <?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <div class="hero-eyebrow">СДТ · Семейство изделий — изготовление под заказ</div>
      <h1 class="hero-h1">Отводы<br><em>стальные</em><br>приварные</h1>
      <p class="hero-desc">Крутоизогнутые, гнутые, секторные отводы и колена с опорой для поворота трубопроводов ТЭС, АЭС, нефтегаза и химической промышленности. Восемь серий по ГОСТ, ОСТ и СТО ЦКТИ, углы 15–180°, семь марок стали. Полный пакет технической документации.</p>
      <div class="hero-params">
        <div class="hp"><span class="hp-v">DN 6–1400</span><span class="hp-k">Типоразмеры</span></div>
        <div class="hp"><span class="hp-v">15–180°</span><span class="hp-k">Углы поворота</span></div>
        <div class="hp"><span class="hp-v">7 марок</span><span class="hp-k">Сталей</span></div>
      </div>
      <div class="hero-cta-row">
        <button class="nav-cta hero-order-btn" type="button" id="orderOpen">Оформить заявку →</button>
</div>
    </div>
    <div class="hero-right">
      <div class="hud-block">
        <div class="hud-label">Технические диапазоны / OTVODY SPECS</div>
        <div class="hud-row"><span class="hud-rk">DN, мм</span><span class="hud-rv">6 — 1400</span></div>
        <div class="hud-row"><span class="hud-rk">Углы поворота</span><span class="hud-rv">15° — 180°</span></div>
        <div class="hud-row"><span class="hud-rk">Температура среды, °C</span><span class="hud-rv">−70 — +700</span></div>
        <div class="hud-row"><span class="hud-rk">Радиус гиба R / DN</span><span class="hud-rv">1,5 — 5,0</span></div>
      </div>
      <div class="hud-block">
        <div class="hud-label">Нормативный статус</div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 17375 / 30753</span><span class="hud-rv live">Крутоизогнутые</span></div>
        <div class="hud-row"><span class="hud-rk">ГОСТ 22793 / 22818</span><span class="hud-rv live">Ру до 100 МПа</span></div>
        <div class="hud-row"><span class="hud-rk">СТО ЦКТИ 321.01–.05</span><span class="hud-rv live">Гнутые для ТЭС</span></div>
        <div class="hud-row"><span class="hud-rk">Декларация</span><span class="hud-rv live">RU С-RU.АБ53</span></div>
      </div>
    </div>
  </div>
<?php },
	's02' => static function ( array $ctx ): void { ?>
<section class="s map-outer" id="s02">
    <div class="map-grid"></div>
    <div class="s-hd" style="border-bottom:1px solid rgba(109,140,166,.15);">
      <div class="s-badge s-dark" style="display:flex;"><span class="s-badge-num">02</span><span style="color:rgba(109,140,166,.6);font-family:'DINPro',monospace;font-size:8px;letter-spacing:.28em;text-transform:uppercase;margin-left:14px;">Карта типоисполнений</span></div>
      <div class="s-meta">PRODUCT TYPE MAP</div>
    </div>
    <div class="map-body">
      <div class="map-root">
        <div class="map-root-label">Отводы — типоисполнения семейства</div>
      </div>
      <div class="map-groups" id="mapGroups" style="grid-template-columns:repeat(4,1fr);">
        <!-- КРУТОИЗОГНУТЫЕ -->
        <div class="mg" data-type="ok">
          <div class="mg-hd">
            <div class="mg-code">ОК</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'otvody', 'ok' ) ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Крутоизогнутые</div>
          <div class="mg-items">
            <div class="mg-item">Тип 3D · R = 1,5DN · 45/60/90/180°<span class="mg-norm">ГОСТ 17375-2001</span></div>
            <div class="mg-item">Тип 2D · R ≈ DN<span class="mg-norm">ГОСТ 30753-2001</span></div>
            <div class="mg-item">Штамповка / протяжка, бесшовные<span class="mg-norm">приварные</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 15–800</span><span class="mg-ftag">Основной тип</span></div>
        </div>
        <!-- ГНУТЫЕ -->
        <div class="mg" data-type="og">
          <div class="mg-hd">
            <div class="mg-code">ОГ</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'otvody', 'og' ) ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Гнутые</div>
          <div class="mg-items">
            <div class="mg-item">R = 3,5–5DN, углы от 15°<span class="mg-norm">СТО ЦКТИ 321.01/.02/.05</span></div>
            <div class="mg-item">На давление до 100 МПа<span class="mg-norm">ГОСТ 22793-83</span></div>
            <div class="mg-item">Главные паропроводы ТЭС<span class="mg-norm">15ГС · 12Х1МФ</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 6–300</span><span class="mg-ftag">Мин. потери</span></div>
        </div>
        <!-- КОЛЕНА -->
        <div class="mg" data-type="ko">
          <div class="mg-hd">
            <div class="mg-code">КО</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'otvody', 'ko' ) ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Колена с опорой</div>
          <div class="mg-items">
            <div class="mg-item">Опорная пята, высокое давление<span class="mg-norm">ГОСТ 22818-83</span></div>
            <div class="mg-item">Исполнения 1–4<span class="mg-norm">Ру до 100 МПа</span></div>
            <div class="mg-item">20 · 09Г2С · 20Х3МВФ<span class="mg-norm">поднадзорные объекты</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 6–200</span><span class="mg-ftag">ТР ТС 032</span></div>
        </div>
        <!-- СЕКТОРНЫЕ -->
        <div class="mg" data-type="oss">
          <div class="mg-hd">
            <div class="mg-code">ОСС</div>
            <div class="mg-cnt"><?php echo esc_html( number_format_i18n( promen_category_bucket_count( 'otvody', 'oss' ) ) ); ?> поз.</div>
          </div>
          <div class="mg-name">Сварные секторные</div>
          <div class="mg-items">
            <div class="mg-item">Сборка из сегментов, R = 1,5DN<span class="mg-norm">ОСТ 36-21-77</span></div>
            <div class="mg-item">Крупные диаметры<span class="mg-norm">DN 500–1400</span></div>
            <div class="mg-item">НК сварных швов: ВИК / УЗК / РК<span class="mg-norm">по объёму заказа</span></div>
          </div>
          <div class="mg-footer"><span class="mg-ftag">DN 500–1400</span><span class="mg-ftag">ТЭС / ГРЭС</span></div>
        </div>

      </div>
      
    </div>
  </section>
<?php },
	's03' => static function ( array $ctx ): void { ?>
<section class="s" id="s03">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">03</span>Подбор отвода</div>
      <div class="s-meta">OTVODY / SELECTION GUIDE</div>
    </div>
    <div class="s-body">
      <div class="sel-guide reveal">
        <div class="sg-thead">
          <div class="sg-th">Задача в трубопроводе</div>
          <div class="sg-th">Нужное исполнение</div>
          <div class="sg-th">Что передать для расчёта</div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 01</div>
            <div class="sg-task-h">Повернуть трубу на стандартный угол 45° / 60° / 90° / 180°</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы крутоизогнутые штампованные — R = 1,5DN (тип 3D) или R ≈ DN (тип 2D)</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 17375-2001</span><span class="sg-tag hi">ГОСТ 30753-2001</span><span class="sg-tag">DN 15–800</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'otvody', 'gost-17375-2001' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'otvody', 'gost-17375-2001' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-17375-2001', $ctx['url'] ) ); ?>">К крутоизогнутым в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN и PN трубопровода</div>
              <div class="sg-param">Угол поворота и радиус: 1,5DN (3D) или DN (2D)</div>
              <div class="sg-param">Наружный диаметр × толщина стенки (напр. 108×4)</div>
              <div class="sg-param">Марка стали и требования к среде</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 02</div>
            <div class="sg-task-h">Снизить сопротивление и эрозию в паропроводе ТЭС</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы гнутые с увеличенным радиусом — R = 3,5–5DN, углы от 15°</div>
            <div class="sg-tags">
              <span class="sg-tag hi">СТО ЦКТИ 321.01/321.02/321.05</span><span class="sg-tag">DN 10–300</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'otvody', 'sto-321-05' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'otvody', 'sto-321-05' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'sto-321-05', $ctx['url'] ) ); ?>">К гнутым СТО ЦКТИ в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN, PN и температура среды</div>
              <div class="sg-param">Угол поворота (в т.ч. нестандартный 15–30°)</div>
              <div class="sg-param">Марка стали: 15ГС, 12Х1МФ — для паропроводов</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 03</div>
            <div class="sg-task-h">Трубопровод высокого давления — свыше 10 до 100 МПа</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы гнутые Ру 100 и колена с опорой — усиленная стенка, опорная пята</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ГОСТ 22793-83</span><span class="sg-tag hi">ГОСТ 22818-83</span><span class="sg-tag">DN 6–200</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'otvody', 'gost-22793-1983' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'otvody', 'gost-22793-1983' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'gost-22793-1983', $ctx['url'] ) ); ?>">К отводам Ру 100 в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Рабочее давление Ру и температура</div>
              <div class="sg-param">Исполнение по стандарту (1–4)</div>
              <div class="sg-param">Наличие опоры, марка стали (20, 09Г2С, 20Х3МВФ)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 04</div>
            <div class="sg-task-h">Крупный диаметр — DN 500–1400, где штамповка недоступна</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Отводы сварные секторные — сборка из сегментов, R = 1,5DN</div>
            <div class="sg-tags">
              <span class="sg-tag hi">ОСТ 36-21-77</span><span class="sg-tag">DN 500–1400</span><span class="sg-tag"><?php echo esc_html( number_format_i18n( promen_category_norm_count( 'otvody', 'ost-36-21-77' ) ) ); ?> <?php echo esc_html( promen_ru_plural( promen_category_norm_count( 'otvody', 'ost-36-21-77' ), 'позиция', 'позиции', 'позиций' ) ); ?></span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( add_query_arg( 'gost', 'ost-36-21-77', $ctx['url'] ) ); ?>">К секторным в реестре →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">DN, толщина стенки, угол</div>
              <div class="sg-param">Число секторов / допуски по проекту</div>
              <div class="sg-param">Объём НК сварных швов (ВИК / УЗК / РК)</div>
            </div>
          </div>
        </div>
        <div class="sg-row">
          <div class="sg-task">
            <div class="sg-task-code">Задача 05</div>
            <div class="sg-task-h">Нестандартный угол, радиус или сталь вне каталога</div>
          </div>
          <div class="sg-product">
            <div class="sg-prod-name">Изготовление по КД заказчика — гибка под произвольный угол, спецстали</div>
            <div class="sg-tags">
              <span class="sg-tag hi">По чертежу</span><span class="sg-tag">ТУ 24.20.40</span><span class="sg-tag">Согласование 1–3 дня</span>
            </div>
            <a class="sg-link" href="<?php echo esc_url( '#request' ); ?>">Отправить чертёж — форма запроса →</a>
          </div>
          <div class="sg-params">
            <div class="sg-param-list">
              <div class="sg-param">Чертёж или эскиз с размерами</div>
              <div class="sg-param">Среда, давление, температура</div>
              <div class="sg-param">Количество и срок поставки</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php },
	's10' => static function ( array $ctx ): void { ?>
<section class="s kb-wrap" id="s10">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
      <div class="s-meta">KNOWLEDGE BASE / ENGINEERING REFERENCE</div>
    </div>

    <!-- Tab bar -->
    <div class="kb-tabrow" role="tablist">
      <button class="kb-tab active" data-panel="product" role="tab"><span class="kb-tab-n">01</span>О продукции</button>
      <button class="kb-tab" data-panel="select" role="tab"><span class="kb-tab-n">02</span>Подбор изделий</button>
      <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
      <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
      <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
      <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Заказ и стоимость</button>
      <button class="kb-tab" data-panel="faq" role="tab"><span class="kb-tab-n">07</span>Частые вопросы</button>
    </div>

    <!-- Panels -->
    <div class="kb-panels">

      <!-- ─── TAB 1: О ПРОДУКЦИИ ─── -->
      <div class="kb-panel kp-active" id="kp-product">
        <div class="kb-lead">
          <div class="kb-lead-h">Соединительные детали трубопроводов</div>
          <p class="kb-lead-p">СДТ — группа трубопроводных изделий, обеспечивающих изменение направления потока, разветвление, соединение труб разных диаметров и концевое заглушение трубопроводных линий. Применяются во всех видах промышленных трубопроводных систем: паровых, водяных, газовых, нефтяных трактах при любых давлениях и температурах — от криогенных (−196°С) до жаровысокотемпературных (+600°С).</p>
        </div>

        <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">АЭС</div>
            <div class="kb-card-title">Атомная энергетика</div>
            <p class="kb-card-body">Трубопроводы <strong>I–IV категорий по НП-089-15</strong>. Расширенный объём НК согласно НП-045-18, прослеживаемость плавки, паспортизация по ГОСТ ISO 10474. Первый контур реакторного отсека, системы аварийного охлаждения, вспомогательные контуры. Изготовление по ТУ предприятия и КД заказчика.</p>
            <div class="kb-card-tags"><span class="kb-tag">НП-045-18</span><span class="kb-tag">НП-068-05</span><span class="kb-tag">НП-089-15</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ТЭС / ГРЭС</div>
            <div class="kb-card-title">Тепловая энергетика</div>
            <p class="kb-card-body">Главные паропроводы, питательные трубопроводы, линии отборов пара. <strong>Рабочие параметры до +600°С, PN до 25 МПа</strong>. Нормативная база — <strong>СТО ЦКТИ серий 321</strong> (гнутые детали) и <strong>720</strong> (тройники, переходы), ОСТ 34 для паровых трактов ТЭС и ГРЭС.</p>
            <div class="kb-card-tags"><span class="kb-tag">СТО ЦКТИ 321</span><span class="kb-tag">СТО ЦКТИ 720</span><span class="kb-tag">ОСТ 34</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">НГК</div>
            <div class="kb-card-title">Нефтегазовый комплекс</div>
            <p class="kb-card-body">Промысловые и магистральные трубопроводы, установки подготовки нефти и газа. Требования по коррозионной стойкости к агрессивным средам (H₂S, CO₂). Изготовление по <strong>ГОСТ 17375–17380</strong>, ОСТ 36 и ТУ предприятия с возможностью учёта требований корпоративных стандартов.</p>
            <div class="kb-card-tags"><span class="kb-tag">ГОСТ 17375–17380</span><span class="kb-tag">ТР ТС 032</span><span class="kb-tag">09Г2С</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ХИМИЯ / ПП</div>
            <div class="kb-card-title">Химия и промышленность</div>
            <p class="kb-card-body">Реакторы, трубопроводы агрессивных сред, криогенные системы. Материалы — <strong>нержавеющие стали 12Х18Н10Т, 10Х17Н13М2Т</strong> и специальные сплавы. Рабочий диапазон −196…+700°С, среды: кислоты, щёлочи, хлориды, водород, аммиак, перегретый пар.</p>
            <div class="kb-card-tags"><span class="kb-tag">12Х18Н10Т</span><span class="kb-tag">Нержавейка</span><span class="kb-tag">−196°С</span></div>
          </div>
        </div>

        <div class="kb-groups-hd">Номенклатура завода — 8 групп продукции</div>
        <div class="kb-groups">
          <div class="kb-grp">
            <span class="kb-grp-code">СДТ</span>
            <span class="kb-grp-name">Соединительные детали трубопровода</span>
            <span class="kb-grp-items">Отводы 45°/90°/180° · Тройники равнопроходные и переходные · Переходы · Днища эллиптические · Заглушки</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ФЛ</span>
            <span class="kb-grp-name">Фланцы трубопроводные</span>
            <span class="kb-grp-items">Воротниковые приварные · Плоские · Свободные на кольце · Глухие · По ГОСТ 33259-2015 и СТО ЦКТИ</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ОП</span>
            <span class="kb-grp-name">Опоры и подвески</span>
            <span class="kb-grp-items">Скользящие · Неподвижные · Пружинные по ОСТ 36-17-85 и СТО ЦКТИ серии 321</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ЗРА</span>
            <span class="kb-grp-name">Запорно-регулирующая арматура</span>
            <span class="kb-grp-items">Задвижки · Клапаны · Краны · По ГОСТ 33257-2015 и НП-068-05 для АЭС</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ТР</span>
            <span class="kb-grp-name">Трубы стальные бесшовные</span>
            <span class="kb-grp-items">Горячедеформированные и холоднодеформированные по ГОСТ 8731–8734 и ГОСТ 9940–9941</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">НМ</span>
            <span class="kb-grp-name">Нестандартные металлоизделия</span>
            <span class="kb-grp-items">Детали трубопроводов по чертежам заказчика · Приём КД в форматах DWG / PDF / STEP</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ИЗ</span>
            <span class="kb-grp-name">Изоляция и покрытия</span>
            <span class="kb-grp-items">Тепловая изоляция · Антикоррозионные покрытия · Комплектация для изолированного монтажа</span>
          </div>
          <div class="kb-grp">
            <span class="kb-grp-code">ТД</span>
            <span class="kb-grp-name">Точёные крепёжные детали</span>
            <span class="kb-grp-items">Шпильки · Гайки · Втулки · Компенсаторы · По ГОСТ и КД заказчика</span>
          </div>
        </div>
      </div><!-- /kp-product -->

      <!-- ─── TAB 2: ПОДБОР ИЗДЕЛИЙ ─── -->
      <div class="kb-panel" id="kp-select">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Ключевые параметры подбора</div>
            <div class="kb-params">
              <div class="kb-param">
                <div class="kb-param-key">DN · Диаметр условный</div>
                <div class="kb-param-val">От <strong>DN 15 до DN 1400</strong> мм. Соответствует условному проходу трубы. Не совпадает с наружным диаметром — DN 50 = Dнар 57 мм по ГОСТ 8732.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">PN · Давление условное</div>
                <div class="kb-param-val">От <strong>PN 0.6 до PN 20.0 МПа</strong> (6–200 кгс/см²). Условное давление при 20°С — при повышенных температурах допустимое давление снижается по таблицам норматива.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Нормативный документ</div>
                <div class="kb-param-val"><strong>ГОСТ, ОСТ, СТО ЦКТИ, НП, ТУ, КД</strong>. Определяет геометрию, допуски на размеры, категорию изделия и обязательный объём контроля качества.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Марка стали</div>
                <div class="kb-param-val">Подбирается по рабочей температуре и среде. <strong>Ст20</strong> — до +425°С; <strong>09Г2С</strong> — до −70°С; <strong>12Х1МФ</strong> — до +570°С; <strong>12Х18Н10Т</strong> — агрессивные среды и АЭС.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Исполнение</div>
                <div class="kb-param-val"><strong>Производство</strong> / <strong>Поставка</strong> / <strong>По чертежу</strong>. Для ответственных объектов предпочтительно производство с полным пакетом собственной документации.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Объём НК</div>
                <div class="kb-param-val">Базовый: <strong>ВИК 100%</strong>. Расширенный: +<strong>УЗК</strong> / +<strong>РК</strong> / +<strong>МПД</strong> / +<strong>ПВК</strong>. Полный объём для АЭС согласно НП-045-18 и требованиям программы контроля объекта.</div>
              </div>
              <div class="kb-param">
                <div class="kb-param-key">Комплект документов</div>
                <div class="kb-param-val">Паспорт 3.1 (ГОСТ ISO 10474) + сертификат на металл + протоколы НК + ГИ (по необходимости). Для АЭС — расширенный пакет с картами прослеживаемости плавки.</div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Как ориентироваться в каталоге</div>
            <div class="kb-steps">
              <div class="kb-step">
                <span class="kb-step-n">01</span>
                <div>
                  <div class="kb-step-title">Выберите группу продукции</div>
                  <div class="kb-step-body">В левой навигационной панели каталога — 9 групп: ВСЕ, СДТ, ФЛ, ОП, ЗРА, ТР, НМ, ИЗ, ТД. Клик сужает реестр до изделий выбранного типа.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">02</span>
                <div>
                  <div class="kb-step-title">Уточните тип: производство или поставка</div>
                  <div class="kb-step-body">Фильтр «Тип» разделяет позиции <strong>собственного производства</strong> (с полным пакетом документов) и торговые поставочные позиции.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">03</span>
                <div>
                  <div class="kb-step-title">Фильтруйте по отрасли</div>
                  <div class="kb-step-body">Фильтр «Отрасль» — <strong>АЭС, ТЭС, НГК</strong> — быстро выбирает изделия, сертифицированные или аттестованные для применения на конкретном типе объектов.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">04</span>
                <div>
                  <div class="kb-step-title">Фильтруйте по нормативу</div>
                  <div class="kb-step-body">Фильтр «Нормы» — <strong>ГОСТ, ОСТ, СТО ЦКТИ, НП, ТУ</strong> — находит позиции, соответствующие конкретному нормативному документу вашего проекта.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">05</span>
                <div>
                  <div class="kb-step-title">Используйте строку поиска</div>
                  <div class="kb-step-body">Поиск работает по <strong>коду, наименованию, ГОСТ, материалу, DN</strong>. Например: «09Г2С» или «ГОСТ 17375» мгновенно фильтрует весь реестр. Горячая клавиша: <strong>⌘K</strong> или <strong>/</strong>.</div>
                </div>
              </div>
              <div class="kb-step">
                <span class="kb-step-n">06</span>
                <div>
                  <div class="kb-step-title">Откройте карточку и запросите КП</div>
                  <div class="kb-step-body">Каждая позиция открывается в панели с техническими данными, нормативами, объёмом НК и параметрами контроля. Прямая ссылка на форму запроса коммерческого предложения.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /kp-select -->

      <!-- ─── TAB 3: НОРМАТИВНАЯ БАЗА ─── -->
      <div class="kb-panel" id="kp-norms">
        <p class="kb-intro-p">Выбор нормативного документа определяет геометрию изделия, допуски на размеры, категорию, объём неразрушающего контроля и состав разрешительной документации. Большинство позиций каталога охвачено одновременно несколькими нормативами — базовым ГОСТ и отраслевым (СТО ЦКТИ, ОСТ, НП) для конкретного типа объектов.</p>
        <div class="kb-norm-grid">
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ — государственные стандарты</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17375-2001</span><span class="kb-norm-desc">Отводы крутоизогнутые бесшовные приварные. DN 15–500, R = 1.5DN, углы 45°, 90°, 180°</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17376-2001</span><span class="kb-norm-desc">Тройники равнопроходные и переходные бесшовные приварные. DN 15–500</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17378-2001</span><span class="kb-norm-desc">Переходы концентрические и эксцентрические бесшовные приварные. DN 15–500</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 17379-2001</span><span class="kb-norm-desc">Заглушки эллиптические приварные. DN 15–500</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 30753-2001</span><span class="kb-norm-desc">Детали трубопроводов бесшовные приварные из углеродистой стали. Общие ТУ</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 33259-2015</span><span class="kb-norm-desc">Фланцы арматуры, соединительных частей и трубопроводов. DN 10–4000, PN 1–250</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ОСТ — отраслевые стандарты Минэнерго и Минмаша</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-20-77</span><span class="kb-norm-desc">Отводы штампосварные. DN 25–400, для трубопроводов ТЭС и нефтехима</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-21-77</span><span class="kb-norm-desc">Отводы секторные/сварные. DN 100–1400, паровые и водяные тракты ТЭС/ГРЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-22-77</span><span class="kb-norm-desc">Тройники сварные. DN 100–1400 для трубопроводов ТЭС и ГРЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-24-77</span><span class="kb-norm-desc">Переходы сварные. DN 100–1400 для трубопроводов высокого давления</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34.10.752-97</span><span class="kb-norm-desc">Отводы для трубопроводов тепловых электростанций</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ОСТ 34.10.762-97</span><span class="kb-norm-desc">Тройники равнопроходные для трубопроводов ТЭС</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">СТО ЦКТИ — стандарты организации (ТЭС, ГРЭС, ТЭЦ)</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.01–.06</span><span class="kb-norm-desc">Отводы гнутые для трубопроводов ТЭС. 6 типоисполнений, серия 2009 г.</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 321.14</span><span class="kb-norm-desc">Отводы для теплоэнергетических применений специального исполнения</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 720.01–.29</span><span class="kb-norm-desc">Тройники и переходы для трубопроводов ТЭС. 29 типоисполнений 2009–2011 гг.</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО ЦКТИ 101.18-2015</span><span class="kb-norm-desc">Фланцы трубопроводные специального исполнения для объектов ТЭС</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">СТО 79814898-111-2009</span><span class="kb-norm-desc">Энергетические системы — стандарт организации</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">НП — нормы ядерной и радиационной безопасности (АЭС)</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">НП-045-18</span><span class="kb-norm-desc">Правила контроля сварных соединений оборудования и трубопроводов АЭУ. Объём и методы НК</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-068-05</span><span class="kb-norm-desc">Требования к арматуре для атомных станций. Проектирование, изготовление, испытания</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">НП-089-15</span><span class="kb-norm-desc">Общие требования к оборудованию и трубопроводам АЭУ. Категории трубопроводов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ПБ 03-585-03</span><span class="kb-norm-desc">Правила устройства и безопасной эксплуатации технологических трубопроводов</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ТР ТС / ТУ — регламенты и технические условия</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">О безопасности оборудования, работающего под избыточным давлением. Обязателен при PN &gt; 0.05 МПа. Декл. RU С-RU.АБ53.В.08323/23</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">Технические условия ООО Завод «Промышленная Энергетика» на детали трубопроводов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">КД заказчика</span><span class="kb-norm-desc">Изготовление по индивидуальным чертежам. Согласование материала, технологии и объёма НК до запуска в производство</span></div>
            </div>
          </div>
          <div class="kb-norm-group">
            <div class="kb-norm-group-hd">ГОСТ на металл и контроль</div>
            <div class="kb-norm-items">
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ ISO 10474-2016</span><span class="kb-norm-desc">Документы о контроле металлопродукции. Паспорт качества 3.1 с плавочными данными</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ Р 55724-2013</span><span class="kb-norm-desc">НК. Ультразвуковой контроль сварных соединений. Методы и оценка результатов</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 1050-2013</span><span class="kb-norm-desc">Металлопродукция из нелегированных конструкционных качественных сталей (Ст20 и др.)</span></div>
              <div class="kb-norm-item"><span class="kb-norm-code">ГОСТ 5632-2014</span><span class="kb-norm-desc">Нержавеющие стали и сплавы коррозионно-стойкие, жаростойкие и жаропрочные</span></div>
            </div>
          </div>
        </div>
      </div><!-- /kp-norms -->

      <!-- ─── TAB 4: МАТЕРИАЛЫ ─── -->
      <div class="kb-panel" id="kp-materials">
        <p class="kb-intro-p">Завод работает с полным спектром конструкционных сталей для трубопроводных систем — от углеродистых для общепромышленных применений до жаропрочных перлитных и аустенитных нержавеющих для объектов атомной и тепловой энергетики. <strong>Каждая марка стали поставляется с сертификатом качества 3.1</strong> (ГОСТ ISO 10474-2016) с указанием плавочных данных, химического состава и механических характеристик. Прослеживаемость металла от плавки завода-поставщика до готового изделия фиксируется документально.</p>
        <div class="kb-mat-grid">
          <div class="kb-mat">
            <div class="kb-mat-grade">Ст20</div>
            <div class="kb-mat-std">ГОСТ 1050-2013 · ГОСТ 8731-87</div>
            <div class="kb-mat-range">до +425°С · PN до 20 МПа</div>
            <div class="kb-mat-apps">Водяные тракты ТЭС · Общепромышленные трубопроводы · НГК низкого давления · Бытовые и отопительные системы</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">09Г2С</div>
            <div class="kb-mat-std">ГОСТ 19281-2014</div>
            <div class="kb-mat-range">−70…+350°С · Хладостойкая</div>
            <div class="kb-mat-apps">Криогенные системы · Северное и арктическое исполнение · НГК при низких температурах · Установки разделения воздуха</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">15ГС</div>
            <div class="kb-mat-std">ОСТ 108.030.118-78</div>
            <div class="kb-mat-range">до +450°С</div>
            <div class="kb-mat-apps">Трубопроводы ТЭС среднего давления · Паровые тракты · Питательные линии</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х1МФ</div>
            <div class="kb-mat-std">ОСТ 108.030.118-78 · ТУ</div>
            <div class="kb-mat-range">до +570°С · Главные паропроводы</div>
            <div class="kb-mat-apps">Паропроводы высокого давления ТЭС и ГРЭС · Главные паровые тракты энергоблоков СКД · Свежий пар 25 МПа / 545°С</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">15Х1М1Ф</div>
            <div class="kb-mat-std">ТУ 14-3-460</div>
            <div class="kb-mat-range">до +580°С · Сверхкритика</div>
            <div class="kb-mat-apps">Сверхкритические параметры пара · Блоки мощностью 300–800 МВт · Повышенные требования к ползучести</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">12Х18Н10Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">−196…+600°С · Нержавеющая</div>
            <div class="kb-mat-apps">АЭС (все контуры, все категории) · Агрессивные химические среды · Пищевая и фармацевтическая промышленность</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">10Х17Н13М2Т</div>
            <div class="kb-mat-std">ГОСТ 5632-2014</div>
            <div class="kb-mat-range">до +700°С · Кислотостойкая</div>
            <div class="kb-mat-apps">Сильноагрессивные среды · Серная и фосфорная кислоты · Хлориды · Установки химической переработки</div>
          </div>
          <div class="kb-mat">
            <div class="kb-mat-grade">13Х11Н2В2МФ</div>
            <div class="kb-mat-std">ТУ · Спецназначение</div>
            <div class="kb-mat-range">Мартенситная · Высокопрочная</div>
            <div class="kb-mat-apps">Турбинные диски и детали · Энергетические установки со специальными требованиями по прочности и коррозионной стойкости</div>
          </div>
        </div>
      </div><!-- /kp-materials -->

      <!-- ─── TAB 5: ДОКУМЕНТАЦИЯ ─── -->
      <div class="kb-panel" id="kp-docs">
        <div class="kb-2col">
          <div>
            <div class="kb-col-title">Стандартный комплект поставки</div>
            <div class="kb-doclist">
              <div class="kb-doc-item">
                <div class="kb-doc-name">Паспорт изделия — сертификат качества 3.1</div>
                <div class="kb-doc-desc">По ГОСТ ISO 10474-2016. Содержит химический состав плавки, механические свойства, результаты приёмочного контроля, маркировку и ссылку на норматив.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Сертификат на металл с плавочными данными</div>
                <div class="kb-doc-desc">Прослеживаемость от плавки завода-изготовителя металла: номер плавки, химсостав, механические характеристики, стандарт на металл.</div>
              </div>
              <div class="kb-doc-item">
                <div class="kb-doc-name">Протокол ВИК — 100% объём</div>
                <div class="kb-doc-desc">Визуально-измерительный контроль по всем позициям. Подтверждает геометрическое соответствие и качество поверхности по требованиям норматива.</div>
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
            <div class="kb-col-title">Расширенный пакет для АЭС <span style="font-weight:400;font-size:7px;letter-spacing:.1em;color:var(--g1);">по НП-045-18</span></div>
            <div class="kb-doclist">
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Программа контроля качества</div>
                <div class="kb-doc-desc">Индивидуальная программа НК для каждой категории трубопровода. Согласовывается с заказчиком до запуска в производство.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Карты идентификации и прослеживаемости</div>
                <div class="kb-doc-desc">Сопровождают изделие от заготовки до готовой детали. Содержат номер плавки, номер детали, ссылки на все протоколы контроля.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Технологические карты сварки и PWHT</div>
                <div class="kb-doc-desc">По согласованным WPS и PQR. Фиксируют параметры сварочных режимов и результаты послесварочной термообработки.</div>
              </div>
              <div class="kb-doc-item kb-aes">
                <div class="kb-doc-name">Протоколы аттестации сварщиков и специалистов НК</div>
                <div class="kb-doc-desc">Действующие удостоверения и аттестационные свидетельства согласно НП-043-18 и ПБ 03-273-99.</div>
              </div>
            </div>

            <div class="kb-col-title" style="margin-top:28px;">Комплексные поставки</div>
            <p class="kb-col-sub">Завод «Промышленная Энергетика» выполняет <strong>комплектные поставки</strong> по проектным спецификациям — от нескольких позиций до полной номенклатуры одного трубопроводного контура. Комплектная поставка включает единую сводную ведомость с позициями, координацию нормативных документов по каждой позиции и общее сопроводительное письмо. Для крупных комплектаций назначается персональный менеджер проекта, обеспечивающий поэтапную приёмку и отчётность.</p>
          </div>
        </div>
      </div><!-- /kp-docs -->

      <!-- ─── TAB 6: ЗАКАЗ И СТОИМОСТЬ ─── -->
      <div class="kb-panel" id="kp-order">
        <div class="kb-3col">

          <div>
            <div class="kb-col-title">Как подготовить заявку</div>
            <div class="kb-checklist">
              <div class="kb-check">
                <span class="kb-check-n">01</span>
                <div>
                  <div class="kb-check-title">Наименование и норматив</div>
                  <div class="kb-check-body">Тип изделия и нормативный документ: отвод 90° по <strong>ГОСТ 17375</strong>, тройник по <strong>СТО ЦКТИ 720.03</strong> и т.д. Если норматив неизвестен — укажите тип объекта / установки.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">02</span>
                <div>
                  <div class="kb-check-title">DN, PN, толщина стенки</div>
                  <div class="kb-check-body">DN (диаметр условный), PN в МПа или кгс/см², толщина стенки (если нестандартная). Для фланцев — дополнительно тип уплотнения (FF / RF / RTJ).</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">03</span>
                <div>
                  <div class="kb-check-title">Марка стали</div>
                  <div class="kb-check-body">Точная марка или рабочие условия (t°С, среда, агрессивность) для подбора. Для АЭС — согласно программе контроля объекта.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">04</span>
                <div>
                  <div class="kb-check-title">Количество и срок</div>
                  <div class="kb-check-body">Количество в штуках. Желаемая дата поставки или срок с момента подтверждения заказа. Для крупных комплектаций — поэтапный график.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">05</span>
                <div>
                  <div class="kb-check-title">Объём НК и документация</div>
                  <div class="kb-check-body">Требуемые методы НК и состав документационного пакета. Для АЭС — ссылка на программу контроля или категорию трубопровода по <strong>НП-045-18</strong>.</div>
                </div>
              </div>
              <div class="kb-check">
                <span class="kb-check-n">06</span>
                <div>
                  <div class="kb-check-title">Чертёж или КД (для нестандарта)</div>
                  <div class="kb-check-body">DWG, PDF или STEP. Проводим инженерную проработку, согласование материала и технологии. Срок и стоимость — после анализа КД.</div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Что влияет на стоимость</div>
            <div class="kb-factors">
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Марка стали</div>
                  <div class="kb-factor-note">Жаропрочные и нержавеющие стали дороже углеродистых в 3–7 раз. 12Х18Н10Т — примерно в 5–6 раз дороже Ст20.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Объём неразрушающего контроля</div>
                  <div class="kb-factor-note">Каждый дополнительный метод НК увеличивает трудоёмкость. Полный объём для АЭС может в 2–4 раза превышать базовый (ВИК).</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">DN и толщина стенки</div>
                  <div class="kb-factor-note">Масса изделия нелинейно растёт с DN. Крупногабаритные детали DN&nbsp;500+ требуют специальной оснастки и прессового оборудования.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↓</span>
                <div>
                  <div class="kb-factor-name">Тираж заказа</div>
                  <div class="kb-factor-note">Единичные позиции дороже серийных — фиксированные затраты на подготовку производства делятся на весь объём. Серийность от 10 шт. снижает себестоимость.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Срочность</div>
                  <div class="kb-factor-note">Сжатые сроки (менее 10 рабочих дней) требуют приоритетной загрузки производства. Срочные заказы оговариваются индивидуально.</div>
                </div>
              </div>
              <div class="kb-factor">
                <span class="kb-factor-ic">↑</span>
                <div>
                  <div class="kb-factor-name">Нестандартная геометрия</div>
                  <div class="kb-factor-note">Изготовление по КД заказчика требует разработки или адаптации технологической оснастки, что увеличивает подготовительные затраты.</div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="kb-col-title">Частые ошибки при подборе</div>
            <div class="kb-errors">
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">DN ≠ наружный диаметр трубы</div>
                  <div class="kb-err-note">Для DN&nbsp;50 наружный диаметр по ГОСТ&nbsp;8732 составляет 57&nbsp;мм. Всегда уточняйте стандарт и серию трубы при заказе.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Не указана марка стали</div>
                  <div class="kb-err-note">«Сталь» без марки — не спецификация. Для ответственных объектов недопустимо: материал влияет на технологию сварки и объём НК.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Путаница PN и Рабочего давления</div>
                  <div class="kb-err-note">PN — условное (нормативное) давление при 20°С. При повышенных температурах допустимое давление снижается по таблицам норматива — учитывайте это при подборе.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Не учтена категория трубопровода</div>
                  <div class="kb-err-note">Для АЭС объём НК и документации определяется категорией (I–IV). Ошибка в категории — несоответствие программе контроля и срыв сдачи объекта.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">КД вместо ГОСТ для типовых изделий</div>
                  <div class="kb-err-note">Для стандартных позиций (отводы, тройники) заказ «по чертежу» при совпадении с ГОСТ добавляет документооборот и задержку без технического смысла.</div>
                </div>
              </div>
              <div class="kb-err">
                <span class="kb-err-ic">!</span>
                <div>
                  <div class="kb-err-title">Забыть про ТР ТС 032/2013</div>
                  <div class="kb-err-note">Изделия с PN &gt; 0.05 МПа в ЕАЭС требуют декларации ТР ТС. Без неё оборудование не может быть введено в эксплуатацию — заказывайте заблаговременно.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div><!-- /kp-order -->

      <!-- ─── TAB 7: ЧАСТЫЕ ВОПРОСЫ ─── -->
      <div class="kb-panel" id="kp-faq">
        <div class="kb-lead">
          <div class="kb-lead-h">Частые вопросы о стальных деталях трубопроводов</div>
          <p class="kb-lead-p">Ответы на вопросы, которые чаще всего задают инженеры-проектировщики, специалисты отделов снабжения и технического надзора при работе с СДТ для объектов ТЭС, АЭС, НГК и химической промышленности.</p>
        </div>
        <div class="faq-wrap reveal">
          <div class="fq"><div class="fq-q"><span class="fq-num">01</span><span class="fq-t">Чем отличаются изделия по ОСТ и ГОСТ — можно ли их заменить друг другом?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">ГОСТ и ОСТ — разные нормативные документы с отличающимися допусками, маркировкой и требованиями к контролю. <strong>Взаимозаменяемость — только по письменному согласованию с проектировщиком и представителем надзора.</strong> Для объектов ТЭС/АЭС самовольная замена нормативного документа недопустима.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">02</span><span class="fq-t">Поставляете ли изделия с сертификацией по ТР ТС 032/2013?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Вся продукция завода охвачена декларацией о соответствии <strong>RU С-RU.АБ53.В.08323/23</strong> по ТР ТС 032/2013 «О безопасности оборудования, работающего под давлением». Декларация включается в комплект документов на поставку.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">03</span><span class="fq-t">Какой объём неразрушающего контроля применяется по умолчанию?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Базовый объём — <strong>100% ВИК</strong> (визуально-измерительный контроль) для всех изделий. По требованию заказчика или в соответствии с нормативным документом добавляются:<ul><li>УЗК — по ГОСТ Р 55724-2013</li><li>РК (рентгенографический контроль)</li><li>МПД (магнитопорошковая дефектоскопия)</li><li>ПВК (капиллярный контроль)</li></ul>Для объектов АЭС — полный объём по <strong>НП-045-18</strong> и программе контроля объекта.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">04</span><span class="fq-t">Можно ли заказать нестандартные типоразмеры или исполнение по чертежам заказчика?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Да. Завод изготавливает изделия по конструкторской документации заказчика — в том числе нестандартные диаметры, углы, толщины стенок и специальные исполнения. Для согласования — отправьте КД через форму запроса или на <strong>zakaz@prom-en.com</strong>.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">05</span><span class="fq-t">Как долго хранится прослеживаемость документации после поставки?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Архив производственной документации (паспорта, протоколы НК, сертификаты плавок) хранится на производстве <strong>не менее 10 лет</strong>. Для объектов АЭС — в соответствии с требованиями НП-017-14 и НП-089-15. По запросу возможно предоставление дубликатов документов.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">06</span><span class="fq-t">Какие сроки изготовления для типовых позиций каталога?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Типовые позиции из складской программы (DN 50–200, массовые марки стали) — <strong>от 3–5 рабочих дней</strong>. Серийный заказ с полным НК и паспортизацией — <strong>от 10–15 рабочих дней</strong>. Изделия DN 500+ и спецсплавы — по согласованию. Точный срок указывается в коммерческом предложении.</div></div></div>
          <div class="fq"><div class="fq-q"><span class="fq-num">07</span><span class="fq-t">Есть ли складская программа или всё производится под заказ?</span><span class="fq-arr">↓</span></div><div class="fq-a"><div class="fq-a-in">Часть позиций номенклатуры поддерживается на складе — прежде всего <strong>отводы, тройники и переходы DN 50–200 из Ст20 и 09Г2С</strong> по ГОСТ 17375 / 17376 / 17378. Для уточнения наличия — направьте запрос: мы предоставим актуальный остаток и срок дополнительного выпуска.</div></div></div>
        </div>
      </div><!-- /kp-faq -->

    </div><!-- /kb-panels -->
  </section>
<?php },
	'modal' => static function ( array $ctx ): void { ?>
<!-- Модал заявки (hero CTA) -->
<div class="order-overlay" id="orderOverlay"></div>
<div class="order-modal" id="orderModal" role="dialog" aria-modal="true" aria-label="Заявка на отводы">
  <div class="om-hd">
    <span class="om-sys">ПЭ-ФОРМА/КТЛ · ЗАЯВКА</span>
    <button class="om-close" id="orderClose" aria-label="Закрыть">✕</button>
  </div>
  <div class="om-title">Заявка на отводы</div>
  <p class="om-sub">Укажите параметры — инженер подберёт исполнение и подготовит КП в течение рабочего дня.</p>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="promen_request">
    <?php wp_nonce_field( 'promen_request', 'promen_nonce' ); ?>
    <input type="text" name="company_url" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
    <div class="om-grid">
      <div class="om-field"><label class="om-lbl" for="om-name">Наименование</label><input id="om-name" name="product" type="text" value="Отвод" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-std">Стандарт</label><input id="om-std" name="standard" type="text" placeholder="ГОСТ 17375, СТО ЦКТИ 321…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-dn">DN / D×s, мм</label><input id="om-dn" name="dn" type="text" placeholder="DN 100 / 108×4" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-pn">Давление, МПа</label><input id="om-pn" name="pn" type="text" placeholder="PN 16" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-mat">Марка стали</label><input id="om-mat" name="material" type="text" placeholder="09Г2С, 12Х1МФ…" autocomplete="off"></div>
      <div class="om-field"><label class="om-lbl" for="om-qty">Количество, шт</label><input id="om-qty" name="qty" type="text" placeholder="100" autocomplete="off"></div>
      <div class="om-field om-field--wide"><label class="om-lbl" for="om-contact">Ваш email / телефон *</label><input id="om-contact" name="contact" type="text" placeholder="Для ответа на запрос" required autocomplete="off"></div>
    </div>
    <div class="om-actions">
      <button type="submit" class="s10-submit">Отправить запрос →</button>
      <span class="om-note">Без обязательств · ответ за 1 рабочий день</span>
    </div>
  </form>
</div>
<?php },
];
