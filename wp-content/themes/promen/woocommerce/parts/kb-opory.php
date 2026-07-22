<?php
/**
 * Секция 10 — база знаний «Опоры» (7 табов).
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="s kb-wrap" id="s10">
  <div class="s-hd">
    <div class="s-badge"><span class="s-badge-num">10</span>База знаний</div>
    <div class="s-meta">KNOWLEDGE BASE / ОПОРЫ</div>
  </div>
  <div class="kb-tabrow" role="tablist">
    <button class="kb-tab active" data-panel="types" role="tab"><span class="kb-tab-n">01</span>Виды</button>
    <button class="kb-tab" data-panel="params" role="tab"><span class="kb-tab-n">02</span>Параметры подбора</button>
    <button class="kb-tab" data-panel="norms" role="tab"><span class="kb-tab-n">03</span>Нормативная база</button>
    <button class="kb-tab" data-panel="materials" role="tab"><span class="kb-tab-n">04</span>Материалы</button>
    <button class="kb-tab" data-panel="docs" role="tab"><span class="kb-tab-n">05</span>Документация</button>
    <button class="kb-tab" data-panel="order" role="tab"><span class="kb-tab-n">06</span>Как заказать</button>
    <button class="kb-tab" data-panel="delivery" role="tab"><span class="kb-tab-n">07</span>Доставка и оплата</button>
  </div>
  <div class="kb-panels">
    <div class="kb-panel kp-active" id="kp-types">
      <div class="kb-lead"><div class="kb-lead-h">Опоры и подвески трубопроводов</div><p class="kb-lead-p">В каталоге — <strong>три типа</strong> опор по ОСТ 36-17-85: неподвижные хомутовые, скользящие и пружинные. Подбор по DN трубы, типу крепления и расчётной нагрузке.</p></div>
      <div class="kb-cards">
          <div class="kb-card">
            <div class="kb-card-badge">НП</div>
            <div class="kb-card-title">Неподвижные хомутовые</div>
            <p class="kb-card-body">Фиксация участка трассы. Хомутовое крепление к трубе. DN 50–1000.</p>
            <div class="kb-card-tags"><span class="kb-tag">ОСТ 36-17-85</span><span class="kb-tag">НП</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">СК</div>
            <div class="kb-card-title">Скользящие</div>
            <p class="kb-card-body">Компенсация температурных перемещений. Антифрикционный слой по проекту.</p>
            <div class="kb-card-tags"><span class="kb-tag">ОСТ 36-17-85</span><span class="kb-tag">СК</span></div>
          </div>
          <div class="kb-card">
            <div class="kb-card-badge">ПР</div>
            <div class="kb-card-title">Пружинные</div>
            <p class="kb-card-body">Вертикальные нагрузки и подвески. Подбор по нагрузке и ходу пружины.</p>
            <div class="kb-card-tags"><span class="kb-tag">ОСТ 36-17-85</span><span class="kb-tag">ПР</span></div>
          </div>
      </div>
    </div>
    <div class="kb-panel" id="kp-params">
      <div class="kb-2col">
        <div><div class="kb-col-title">Чеклист заявки на опору</div><div class="kb-checklist"><div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип опоры</div><div class="kb-check-body">НП / СК / ПР</div></div></div><div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN трубы</div><div class="kb-check-body">Условный проход участка</div></div></div><div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Нагрузка</div><div class="kb-check-body">Расчётная вертикальная / горизонтальная</div></div></div><div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Число опор, дата поставки</div></div></div></div></div>
        <div><div class="kb-col-title">Ключевые параметры</div><div class="kb-params"><div class="kb-param"><div class="kb-param-key">Тип</div><div class="kb-param-val">Неподвижная, скользящая или пружинная</div></div><div class="kb-param"><div class="kb-param-key">DN</div><div class="kb-param-val">По диаметру трубы</div></div><div class="kb-param"><div class="kb-param-key">Нагрузка</div><div class="kb-param-val">кН — из расчёта трассы</div></div><div class="kb-param"><div class="kb-param-key">Покрытие</div><div class="kb-param-val">Грунт, цинк, спец — по проекту</div></div></div></div>
      </div>
    </div>
    <div class="kb-panel" id="kp-norms">
      <div class="kb-norm-grid"><div class="kb-norm-group"><div class="kb-norm-group-hd">Основной стандарт</div><div class="kb-norm-items"><div class="kb-norm-item"><span class="kb-norm-code">ОСТ 36-17-85</span><span class="kb-norm-desc">Опоры и подвески трубопроводов промышленных предприятий</span></div></div></div><div class="kb-norm-group"><div class="kb-norm-group-hd">Общие</div><div class="kb-norm-items"><div class="kb-norm-item"><span class="kb-norm-code">ТУ 24.20.40-001-13842829-2023</span><span class="kb-norm-desc">ТУ предприятия</span></div><div class="kb-norm-item"><span class="kb-norm-code">ТР ТС 032/2013</span><span class="kb-norm-desc">К трубопроводу объекта</span></div></div></div></div>
    </div>
    <div class="kb-panel" id="kp-materials">
      <div class="kb-mat-grid"><div class="kb-mat"><div class="kb-mat-grade">Ст3сп</div><div class="kb-mat-std">ГОСТ 380</div><div class="kb-mat-range">Основная марка</div><div class="kb-mat-apps">Неподвижные и скользящие</div></div><div class="kb-mat"><div class="kb-mat-grade">Ст20</div><div class="kb-mat-std">ГОСТ 1050</div><div class="kb-mat-range">Пружинные / ответственные</div><div class="kb-mat-apps">По проекту</div></div></div>
    </div>
    <div class="kb-panel" id="kp-docs">
      <div class="kb-2col">
        <div>
          <div class="kb-col-title">Стандартный комплект поставки</div>
          <div class="kb-doclist">
            <div class="kb-doc-item"><div class="kb-doc-name">Паспорт изделия</div><div class="kb-doc-desc">Сертификат качества 3.1, маркировка, ссылка на норматив.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Сертификат на металл</div><div class="kb-doc-desc">Плавочные данные по ГОСТ ISO 10474-2016.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Протокол ВИК</div><div class="kb-doc-desc">Геометрия и качество поверхности / сборки.</div></div>
            <div class="kb-doc-item"><div class="kb-doc-name">Декларация ТР ТС 032/2015</div><div class="kb-doc-desc">При работе под избыточным давлением свыше 0,05 МПа.</div></div>
          </div>
        </div>
        <div>
          <div class="kb-col-title">Комплексные поставки</div>
          <p class="kb-col-sub">Поставка в комплекте с трубами, СДТ и фланцами одного контура — единая ведомость и сроки.</p>
        </div>
      </div>
    </div>
    <div class="kb-panel" id="kp-order">
      <div class="kb-3col">
        <div><div class="kb-col-title">Чеклист заявки на опору</div><div class="kb-checklist"><div class="kb-check"><span class="kb-check-n">01</span><div><div class="kb-check-title">Тип опоры</div><div class="kb-check-body">НП / СК / ПР</div></div></div><div class="kb-check"><span class="kb-check-n">02</span><div><div class="kb-check-title">DN трубы</div><div class="kb-check-body">Условный проход участка</div></div></div><div class="kb-check"><span class="kb-check-n">03</span><div><div class="kb-check-title">Нагрузка</div><div class="kb-check-body">Расчётная вертикальная / горизонтальная</div></div></div><div class="kb-check"><span class="kb-check-n">04</span><div><div class="kb-check-title">Количество и срок</div><div class="kb-check-body">Число опор, дата поставки</div></div></div></div></div>
        <div>
          <div class="kb-col-title">Что влияет на стоимость</div>
          <div class="kb-factors">
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">DN / нагрузка</div><div class="kb-factor-note">Крупный диаметр и расчётные нагрузки увеличивают массу и комплектацию.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↑</span><div><div class="kb-factor-name">Исполнение</div><div class="kb-factor-note">Спецпокрытия, приводы, пружинные блоки — по проекту.</div></div></div>
            <div class="kb-factor"><span class="kb-factor-ic">↓</span><div><div class="kb-factor-name">Партия</div><div class="kb-factor-note">Серийный заказ снижает удельную подготовку.</div></div></div>
          </div>
        </div>
        <div><div class="kb-col-title">Частые ошибки</div><div class="kb-errors"><div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Не указать тип</div><div class="kb-err-note">НП, СК и ПР — разные конструкции</div></div></div><div class="kb-err"><span class="kb-err-ic">!</span><div><div class="kb-err-title">Только DN без нагрузки</div><div class="kb-err-note">Для пружинных нужна расчётная нагрузка</div></div></div></div></div>
      </div>
    </div>
    <div class="kb-panel" id="kp-delivery">
      <div class="kb-lead"><div class="kb-lead-h">Доставка и оплата</div><p class="kb-lead-p">Отгрузка после ОТК. Логистику считаем в КП — укажите объект или город.</p></div>
      <div class="kb-cards">
        <div class="kb-card"><div class="kb-card-badge">ДОСТАВКА</div><div class="kb-card-title">ТК по всей России</div><p class="kb-card-body">Отгрузка транспортной компанией по выбору заказчика.</p></div>
        <div class="kb-card"><div class="kb-card-badge">САМОВЫВОЗ</div><div class="kb-card-title">Челябинск</div><p class="kb-card-body">454091, ул. Орджоникидзе, 37. Пн–Пт 09:00–18:00 МСК.</p></div>
        <div class="kb-card"><div class="kb-card-badge">ОПЛАТА</div><div class="kb-card-title">Б/н с НДС</div><p class="kb-card-body">Счёт по КП. Аванс / доплата или график по договору.</p></div>
      </div>
    </div>
  </div>
</section>
