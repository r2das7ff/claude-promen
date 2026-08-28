<?php
/**
 * Статья «Поставки для АЭС и ТЭС: требования и особенности» — 1:1 из html/statya-postavka-aes-tes.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет).
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-09.ART‑06 / REV.1' );

$promen_catalog_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_stati_url    = ( $p = promen_page( 'stati' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_proekty_url  = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_nb_url       = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : '';

get_header();
?>
<div class="pg">

  <!-- BREADCRUMB -->
  <div class="pd-crumb">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a><span>/</span>
    <a href="<?php echo esc_url( $promen_stati_url ); ?>">Статьи</a><span>/</span>
    <b>Поставки для АЭС и ТЭС</b>
  </div>

  <!-- HERO -->
  <div class="ar-hero">
    <span class="ar-cat">Проекты</span>
    <h1 class="ar-h1">Поставки для АЭС и ТЭС: требования и особенности</h1>
    <p class="ar-lead">Поставка деталей на объект атомной энергетики и поставка на промышленный трубопровод — разные по требованиям задачи, даже если чертёж детали идентичен. Разбираем на реальных проектах завода, чем отличается прослеживаемость, контроль и сроки для АЭС и ТЭС.</p>
    <div class="ar-meta">
      <span>22.06.2026</span>
      <span>Чтение · <b>11 мин</b></span>
      <span>Инженерный отдел «Промышленная Энергетика»</span>
    </div>
  </div>

  <!-- MEDIA -->
  <div class="ar-media">
    <picture><source srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-3.webp' ) ); ?>" type="image/webp"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-3.jpg' ) ); ?>" alt="Отгрузка партии деталей трубопровода" loading="eager" width="1333" height="2000"></picture>
    <span class="ar-media-tag">Участок отгрузки готовой продукции · Челябинск</span>
  </div>

  <!-- BODY + TOC -->
  <div class="ar-split">
    <article class="ar-content">

      <p>Завод «Промышленная Энергетика» поставляет соединительные детали трубопровода как на объекты атомной энергетики, так и на тепловые электростанции и ГРЭС — в России и на экспорт. Формально это одна и та же продукция — отводы, фланцы, тройники, переходы, — но требования к её изготовлению, контролю и сопроводительной документации для АЭС и ТЭС различаются заметно.</p>

      <h2 id="s1"><span class="num">01</span>Что уже поставлено — пять реализованных проектов</h2>
      <p>Ниже — реальные параметры поставок завода по объектам, которые различаются по классу, региону и статусу.</p>

      <div class="ar-proj-grid">
        <div class="ar-proj"><span class="ar-proj-t">Курская АЭС‑2</span><span class="ar-proj-l">Курчатов, Россия · АЭС</span><span class="ar-proj-v">Сталь 08Х18Н10Т · ≈36 т</span></div>
        <div class="ar-proj"><span class="ar-proj-t">АЭС «Руппур»</span><span class="ar-proj-l">Пабна, Бангладеш · АЭС</span><span class="ar-proj-v">Сталь 15Х1М1Ф · ≈96 т</span></div>
        <div class="ar-proj"><span class="ar-proj-t">АЭС «Аккую»</span><span class="ar-proj-l">Мерсин, Турция · АЭС</span><span class="ar-proj-v">Сталь 20 / 08Х18Н10Т · ≈148 т</span></div>
        <div class="ar-proj"><span class="ar-proj-t">Черепетская ГРЭС</span><span class="ar-proj-l">Суворов, Россия · ГРЭС</span><span class="ar-proj-v">Сталь 20 · ≈157 т</span></div>
        <div class="ar-proj"><span class="ar-proj-t">Омская ТЭЦ‑3</span><span class="ar-proj-l">Омск, Россия · ТЭЦ</span><span class="ar-proj-v">Сталь 15Х1М1Ф · ≈96 т</span></div>
      </div>

      <h2 id="s2"><span class="num">02</span>АЭС-класс: прослеживаемость превыше всего</h2>
      <p>Для объектов атомной энергетики — как Курская АЭС‑2, «Руппур» или «Аккую» — определяющее требование не столько сама деталь, сколько её сопроводимая история: от сертификата на металл и номера плавки до полного протокола неразрушающего контроля каждого шва. Это тот же принцип прослеживаемости, что описан в статье <a href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">«Контроль качества СДТ»</a> — но применяется без исключений и с расширенным объёмом проверок.</p>
      <p>Материал для АЭС-класса чаще всего — нержавеющая аустенитная сталь 08Х18Н10Т, как на Курской АЭС‑2, или жаропрочная 15Х1М1Ф для высокотемпературных контуров, как на «Руппуре».</p>

      <div class="ar-callout">
        <span class="ar-callout-lbl">Важно</span>
        <p>Для экспортных поставок — «Руппур» (Бангладеш), «Аккую» (Турция) — дополнительно учитываются сроки логистики и таможенного оформления, поэтому график изготовления партии согласовывается с запасом на международную отгрузку.</p>
      </div>

      <h2 id="s3"><span class="num">03</span>ТЭС и ГРЭС: объём важнее, чем для единичной АЭС-партии</h2>
      <p>Поставки на тепловую генерацию — как Черепетская ГРЭС (≈157 т, диапазон Ø25–530 мм) — чаще характеризуются большим объёмом номенклатуры при менее строгих требованиях к прослеживаемости, чем АЭС-класс. Материал обычно проще — углеродистая сталь 20, — а нормативная база опирается на отраслевые ОСТ и ГОСТ без дополнительных норм ПНАЭ.</p>

      <div class="ar-cmp">
        <div class="ar-cmp-col">
          <div class="ar-cmp-h">АЭС-класс</div>
          <ul>
            <li>Полная прослеживаемость плавки до маркировки готовой детали</li>
            <li>Расширенный неразрушающий контроль сварных швов</li>
            <li>Нормы ПНАЭ и НП поверх базового ГОСТ/ОСТ</li>
            <li>Материал — чаще нержавеющие и жаропрочные стали</li>
          </ul>
        </div>
        <div class="ar-cmp-col">
          <div class="ar-cmp-h">ТЭС / ГРЭС</div>
          <ul>
            <li>Стандартный объём контроля по ГОСТ/ОСТ объекта</li>
            <li>Крупная номенклатура и объём партии</li>
            <li>Материал — чаще углеродистые и низколегированные стали</li>
            <li>Сроки изготовления определяются объёмом, а не сложностью контроля</li>
          </ul>
        </div>
      </div>

      <div class="ar-quote">
        <p>«Для АЭС-объекта деталь без полной истории плавки не может быть принята — независимо от того, насколько точно она соответствует чертежу геометрически».</p>
      </div>

      <h2 id="s4"><span class="num">04</span>Нормативная база: почему для АЭС документов больше</h2>
      <p>И для АЭС, и для ТЭС применяется государственная и отраслевая нормативная база — ГОСТ и ОСТ. Но для атомных объектов к ней добавляются нормы и правила ПНАЭ и корпоративные СТО заказчика (например, Росатома), которые задают собственные требования к контролю и документообороту. Подробно эта иерархия разобрана в статье <a href="<?php echo esc_url( promen_article_url( 'normativnaya-baza' ) ); ?>">«ГОСТ, ОСТ и ТУ на СДТ»</a>.</p>

      <h3>Три федеральные нормы, которые действуют одновременно</h3>
      <p>Разница между АЭС и ТЭС начинается не с детали, а с того, какой документ вообще применяется к объекту. НП-089-15 «Правила устройства и безопасной эксплуатации оборудования и трубопроводов атомных энергетических установок» утверждены приказом Ростехнадзора от 17.12.2015 № 521 и введены в действие 23.02.2016; этот документ заменил ПНАЭ Г-7-008-89, на который до сих пор ссылаются старые спецификации. Он делит оборудование и трубопроводы на группы по классам безопасности: к группе B отнесены элементы 3-го класса безопасности, к группе C — остальные элементы 3-го класса, не вошедшие в группу B.</p>
      <p>Трубопроводы пара и горячей воды для объектов использования атомной энергии выведены в отдельный документ — НП-045-18. Он распространяется на специально спроектированные трубопроводы, отнесённые к элементам 4-го класса безопасности, и оперирует категориями I–IV. Сварка и наплавка оборудования и трубопроводов АЭУ нормируется третьим документом — НП-104-18. Для одной и той же детали в атомном заказе эти нормы действуют одновременно, и объём контроля определяется их пересечением, а не одним «главным» стандартом — именно поэтому пакет документов для АЭС толще, чем для тепловой генерации.</p>
      <p>Методики контроля для отрасли тоже свои. Ультразвуковой контроль сварных соединений и наплавленных покрытий на атомных объектах выполняется по ГОСТ Р 50.05.02-2018 из серии «Система оценки соответствия в области использования атомной энергии» — а не по общепромышленному ГОСТ Р 55724-2013, по которому идёт контроль тех же швов для ТЭС. Разбор методов и того, что находит каждый из них, — в статье <a href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">«Контроль качества СДТ»</a>.</p>

      <h2 id="s5"><span class="num">05</span>Как начинается поставка на ваш объект</h2>
      <p>Независимо от класса объекта, порядок работы одинаков: инженер завода анализирует параметры трубопровода и применимый нормативный документ, подбирает материал и технологию, после чего фиксирует объём контроля и состав пакета документов в спецификации. Для нестандартной геометрии этот процесс начинается с чертежа заказчика — см. статью <a href="<?php echo esc_url( promen_article_url( 'chertezh-zakazchika' ) ); ?>">«Изготовление по чертежам заказчика»</a>.</p>
      <p>Полный список реализованных проектов с подробной историей поставки — на странице <a href="<?php echo esc_url( $promen_proekty_url ); ?>">«Проекты»</a>.</p>

    </article>

    <!-- TOC SIDEBAR -->
    <aside class="ar-toc">
      <div class="ar-toc-box">
        <span class="ar-toc-lbl">В этой статье</span>
        <ul class="ar-toc-list" id="tocList">
          <li><a href="#s1"><span class="n">01</span>Пять реализованных проектов</a></li>
          <li><a href="#s2"><span class="n">02</span>АЭС-класс</a></li>
          <li><a href="#s3"><span class="n">03</span>ТЭС и ГРЭС</a></li>
          <li><a href="#s4"><span class="n">04</span>Нормативная база</a></li>
          <li><a href="#s5"><span class="n">05</span>Как начинается поставка</a></li>
        </ul>
      </div>
      <div class="ar-toc-cta">
        <div class="ar-toc-cta-t">Готовите поставку для объекта?</div>
        <p class="ar-toc-cta-p">Опишите объект и класс требований — инженер рассчитает материал, контроль и срок изготовления.</p>
        <button class="ar-toc-cta-btn" onclick="openRequestModal('solution')">Обсудить поставку →</button>
      </div>
    </aside>
  </div>

  <!-- TAGS -->
  <div class="ar-tags">
    <span class="ar-tags-lbl">Теги</span>
    <span class="ar-tag">АЭС</span>
    <span class="ar-tag">ТЭС</span>
    <span class="ar-tag">Прослеживаемость</span>
    <span class="ar-tag">Проекты</span>
    <span class="ar-tag">Экспорт</span>
  </div>

  <!-- RELATED -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">→</span>
      <h2 class="pd-sec-title">Читайте также</h2>
    </div>
    <div class="pd-rel-grid">
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-6.webp' ) ); ?>" type="image/webp"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-6.jpg' ) ); ?>" alt="Контроль качества СДТ" loading="lazy" width="1125" height="2000"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Контроль качества</div><div class="pd-rel-title">От входного контроля до паспорта изделия</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'normativnaya-baza' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-4.webp' ) ); ?>" type="image/webp"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-4.jpg' ) ); ?>" alt="Нормативная база СДТ" loading="lazy" width="1333" height="2000"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Нормативы</div><div class="pd-rel-title">ГОСТ, ОСТ и ТУ на СДТ</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'vybor-stali' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.webp' ) ); ?>" type="image/webp"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.jpg' ) ); ?>" alt="Выбор стали для СДТ" loading="lazy" width="1333" height="2000"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Материаловедение</div><div class="pd-rel-title">Как выбрать сталь для СДТ</div></div>
      </a>
    </div>
  </div>

  <!-- CTA -->
  <div class="pd-cta">
    <div>
      <div class="pd-cta-h">Готовите поставку<br>для <em>своего объекта</em>?</div>
      <p class="pd-cta-p">Опишите параметры трубопровода и объект — инженер подберёт материал, срок изготовления и стоимость партии.</p>
    </div>
    <a class="pd-cta-btn" href="<?php echo esc_url( $promen_contacts_url ); ?>">Обсудить поставку →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
