<?php
/**
 * Технический реестр каталога (cat-seo): баннер, редакция, группы, характеристики.
 * Разметка 1:1 из design-reference/katalog.html.
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="cat-seo">

  <!-- Шапка-баннер: мета + ключевые цифры -->
  <div class="seo-header">
    <div class="seo-hd-meta">ООО Завод «Промышленная Энергетика» · г. Челябинск · ПЭ-КТЛ / 2026</div>
    <div class="seo-stats">
      <div class="seo-stat">
        <span class="seo-stat-v">8</span>
        <span class="seo-stat-k">групп продукции</span>
      </div>
      <div class="seo-stat">
        <span class="seo-stat-v"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
        <span class="seo-stat-k">типоразмеров в реестре</span>
      </div>
      <div class="seo-stat">
        <span class="seo-stat-v">DN&nbsp;15–1400</span>
        <span class="seo-stat-k">диапазон типоразмеров</span>
      </div>
      <div class="seo-stat">
        <span class="seo-stat-v">с&nbsp;2004</span>
        <span class="seo-stat-k">года производства</span>
      </div>
    </div>
    <div class="seo-hd-ref">Реестр продукции · ТР ТС 032/2013</div>
  </div>

  <!-- Редакционная зона: заголовок + тексты -->
  <div class="seo-body">
    <div class="seo-title-col">
      <div class="seo-kicker">Технический реестр</div>
      <h2 class="seo-h2">Трубо-<br>проводные<br>изделия для<br><em>АЭС</em> и <em>ТЭС</em></h2>
    </div>
    <div class="seo-text-col">
      <p class="seo-p">Завод «Промышленная Энергетика» производит и поставляет <strong>полную номенклатуру трубопроводных деталей и арматуры</strong> для ответственных трубопроводных систем высокого давления и температуры. Специализация — объекты атомной энергетики (I–IV категория трубопроводов по НП-045-18), тепловые электростанции, нефтехимические и газоперерабатывающие комплексы. Реестр охватывает 8 номенклатурных групп: соединительные детали, фланцы, опоры и подвески, запорно-регулирующая арматура, стальные трубы, нестандартные изделия, изоляционные системы, точёные крепёжные детали.</p>
      <p class="seo-p">Диапазон типоразмеров: <strong>DN 15–1400 мм, PN 0.6–20.0 МПа, рабочая температура −40…+600°С</strong>. Поставка единичными изделиями и серийными партиями, а также комплектами по проектной документации заказчика. Изготовление по ГОСТ, ОСТ, СТО ЦКТИ, НП и конструкторской документации. Каждое изделие сопровождается <strong>паспортом качества 3.1 по ГОСТ ISO 10474-2016</strong>, сертификатами на металл с плавочными данными и протоколами контроля. Продукция декларирована по ТР ТС 032/2013.</p>
      <p class="seo-p">Для объектов атомной энергетики: <strong>НП-045-18</strong> (сварные соединения оборудования и трубопроводов АЭУ), <strong>НП-068-05</strong> (арматура атомных станций), <strong>НП-089-15</strong> (общие требования к оборудованию и трубопроводам). Прослеживаемость металла от плавки до готового изделия подтверждается документально по всей производственной цепочке.</p>
    </div>
  </div>

  <!-- Карточки номенклатурных групп -->
  <div class="seo-cats">
    <div class="seo-cat">
      <span class="seo-cat-code">СДТ</span>
      <span class="seo-cat-name">Соединительные детали трубопровода</span>
      <span class="seo-cat-cnt">Отводы · Тройники · Переходы · Днища</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">ФЛ</span>
      <span class="seo-cat-name">Фланцы трубопроводные</span>
      <span class="seo-cat-cnt">Воротниковые · Плоские · Глухие</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">ОП</span>
      <span class="seo-cat-name">Опоры и подвески</span>
      <span class="seo-cat-cnt">Скользящие · Неподвижные · Пружинные</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">ЗРА</span>
      <span class="seo-cat-name">Запорно-регулирующая арматура</span>
      <span class="seo-cat-cnt">Задвижки · Клапаны · Краны</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">ТР</span>
      <span class="seo-cat-name">Стальные трубы</span>
      <span class="seo-cat-cnt">Бесшовные г/д и х/д</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">НМ</span>
      <span class="seo-cat-name">Нестандартные изделия</span>
      <span class="seo-cat-cnt">По чертежам заказчика</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">ИЗ</span>
      <span class="seo-cat-name">Изоляция и покрытия</span>
      <span class="seo-cat-cnt">Тепловая · Антикоррозионная</span>
    </div>
    <div class="seo-cat">
      <span class="seo-cat-code">ТД</span>
      <span class="seo-cat-name">Точёные детали</span>
      <span class="seo-cat-cnt">Шпильки · Гайки · Втулки</span>
    </div>
  </div>

  <!-- Технические характеристики: двухколонная таблица -->
  <div class="seo-specs">
    <div class="seo-spec-col">
      <div class="seo-spec-item">
        <span class="seo-spec-k">Диаметр условный</span>
        <span class="seo-spec-v">DN 15 — DN 1400 мм</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Условное давление</span>
        <span class="seo-spec-v">PN 0.6 — PN 20.0 МПа (6–200 кгс/см²)</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Рабочая температура</span>
        <span class="seo-spec-v">−40 … +600°С</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Марки стали</span>
        <span class="seo-spec-v">Ст20 · 09Г2С · 15ГС · 15Х1М1Ф · 12Х1МФ · 12Х18Н10Т · 10Х17Н13М2Т · 13Х11Н2В2МФ</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Отрасли применения</span>
        <span class="seo-spec-v">АЭС · ТЭС · ГРЭС · ТЭЦ · НГК · Нефтехим</span>
      </div>
    </div>
    <div class="seo-spec-col">
      <div class="seo-spec-item">
        <span class="seo-spec-k">Нормативные документы</span>
        <span class="seo-spec-v">ГОСТ · ОСТ · СТО ЦКТИ 321/720 · НП-045-18 · НП-068-05 · НП-089-15 · ТУ · КД</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Методы контроля</span>
        <span class="seo-spec-v">УЗК · РК · ВИК · МПД · ПВК · Гидравлические испытания (ГОСТ Р 55724-2013)</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Сертификация</span>
        <span class="seo-spec-v">ТР ТС 032/2013 · Декл. RU С-RU.АБ53.В.08323/23 · Серия RU 0418908</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Документация</span>
        <span class="seo-spec-v">Паспорт 3.1 (ГОСТ ISO 10474-2016) · Сертификат на металл · Протокол НК · Прослеживаемость плавки</span>
      </div>
      <div class="seo-spec-item">
        <span class="seo-spec-k">Производство</span>
        <span class="seo-spec-v">454091, г. Челябинск, ул. Орджоникидзе, 37 · zakaz@prom-en.com · +7 (351) 217-00-99</span>
      </div>
    </div>
  </div>

  <!-- Нижняя полоса -->
  <div class="seo-foot">
    <span class="seo-foot-item">ООО Завод «Промышленная Энергетика»</span>
    <span class="seo-foot-item">454091, Челябинск · с 2004 года</span>
    <span class="seo-foot-item">ТР ТС 032/2013</span>
    <span class="seo-foot-item">НП-045-18 · НП-089-15</span>
    <span class="seo-foot-item">ГОСТ · ОСТ · СТО ЦКТИ · НП</span>
    <span class="seo-foot-item">zakaz@prom-en.com</span>
  </div>

</section>
