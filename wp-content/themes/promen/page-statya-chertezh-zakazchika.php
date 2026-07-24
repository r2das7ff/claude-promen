<?php
/**
 * Статья «Изготовление по чертежам заказчика: что нужно передать заводу» — 1:1 из html/statya-chertezh-zakazchika.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет).
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-09.ART‑05 / REV.1' );

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
    <b>Изготовление по чертежам заказчика</b>
  </div>

  <!-- HERO -->
  <div class="ar-hero">
    <span class="ar-cat">Производство</span>
    <h1 class="ar-h1">Изготовление по чертежам заказчика: что нужно передать заводу</h1>
    <p class="ar-lead">Единичная деталь по индивидуальному чертежу — не исключение, а рабочий формат завода. Но чтобы инженер посчитал срок и стоимость с первого раза, а не после трёх раундов уточнений, нужен минимальный, но точный комплект исходных данных.</p>
    <div class="ar-meta">
      <span>28.06.2026</span>
      <span>Чтение · <b>6 мин</b></span>
      <span>Инженерный отдел «Промышленная Энергетика»</span>
    </div>
  </div>

  <!-- MEDIA -->
  <div class="ar-media">
    <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-5.jpg' ) ); ?>" alt="Работа с конструкторской документацией" loading="eager">
    <span class="ar-media-tag">Конструкторский отдел · Челябинск</span>
  </div>

  <!-- BODY + TOC -->
  <div class="ar-split">
    <article class="ar-content">

      <p>Часть заказов на заводе «Промышленная Энергетика» — это не позиции из каталога, а детали, спроектированные под конкретный узел трубопровода: нестандартная геометрия, особый угол, комбинация параметров, которой нет в серийном сортаменте. Для таких заказов завод работает по конструкторской документации (КД) заказчика — либо разрабатывает техническую документацию самостоятельно на основе присланных требований.</p>

      <h2 id="s1"><span class="num">01</span>Минимальный комплект для расчёта</h2>
      <p>Чтобы завод мог посчитать срок изготовления и стоимость партии без дополнительных согласований, в заявке должны быть закрыты четыре пункта.</p>

      <div class="ar-check">
        <div class="ar-check-row"><span class="m">01</span><div class="b"><span class="t">Чертёж детали с допусками</span><span class="d">Геометрия, размеры и предельные отклонения — в формате, пригодном для производства (не эскиз «на глаз»).</span></div></div>
        <div class="ar-check-row"><span class="m">02</span><div class="b"><span class="t">Марка материала</span><span class="d">Указанная сталь или ссылка на параметры среды, по которым завод подберёт материал самостоятельно.</span></div></div>
        <div class="ar-check-row"><span class="m">03</span><div class="b"><span class="t">Применимый нормативный документ</span><span class="d">ГОСТ, ОСТ, СТО или ТУ объекта — определяет допуски, методы контроля и состав пакета документов.</span></div></div>
        <div class="ar-check-row"><span class="m">04</span><div class="b"><span class="t">Требования по контролю и испытаниям</span><span class="d">Объём неразрушающего контроля, требования к прослеживаемости плавки — особенно важно для АЭС-класса.</span></div></div>
      </div>

      <h2 id="s2"><span class="num">02</span>Анализ и адаптация документации</h2>
      <p>Полученная КД проходит инженерный анализ: проверяется технологичность геометрии, достижимость допусков выбранным методом изготовления, соответствие материала параметрам среды. Если в документации заказчика есть пробелы — например, не указана марка стали, — инженер завода подбирает материал по тем же принципам, что описаны в статье <a href="<?php echo esc_url( promen_article_url( 'vybor-stali' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">«Как выбрать сталь для СДТ»</a>.</p>

      <h2 id="s3"><span class="num">03</span>Выбор технологии изготовления</h2>
      <p>По итогам анализа чертежа инженер определяет, каким методом деталь будет изготовлена — штамповкой, протяжкой или сваркой из секторов. Выбор технологии напрямую зависит от геометрии, диаметра и толщины стенки — подробно логика выбора между бесшовным и сварным исполнением разобрана в статье <a href="<?php echo esc_url( promen_article_url( 'otvod-svarnoy-besshovnyy' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">«Бесшовные и сварные отводы»</a>.</p>

      <div class="ar-callout">
        <span class="ar-callout-lbl">Важно</span>
        <p>Единичная партия по чертежу заказчика не означает более низкие требования к контролю. Каждая деталь проходит тот же набор этапов — от идентификации плавки до паспорта изделия, — что и серийная продукция.</p>
      </div>

      <h2 id="s4"><span class="num">04</span>Единичные и серийные партии</h2>
      <p>Завод изготавливает как единичные детали для одного узла, так и серийные партии на весь объект — логика расчёта та же, но серийность влияет на сроки и стоимость оснастки. Для сложных трубопроводных узлов — с установкой бобышек, штуцеров или отборников давления — эти особенности фиксируются на этапе анализа чертежа, до запуска партии в производство.</p>

      <div class="ar-quote">
        <p>«Чем точнее исходные данные в заявке, тем быстрее завод посчитает срок и стоимость — большинство задержек на старте связаны не со сложностью детали, а с неполным комплектом документации».</p>
      </div>

      <h2 id="s5"><span class="num">05</span>Что дальше — контроль и паспорт изделия</h2>
      <p>После согласования технологии и запуска партии деталь проходит тот же путь контроля, что и любое изделие завода — восемь этапов от входного контроля до финального паспорта. Подробно этот процесс описан в статье <a href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">«Контроль качества СДТ»</a>.</p>

    </article>

    <!-- TOC SIDEBAR -->
    <aside class="ar-toc">
      <div class="ar-toc-box">
        <span class="ar-toc-lbl">В этой статье</span>
        <ul class="ar-toc-list" id="tocList">
          <li><a href="#s1"><span class="n">01</span>Минимальный комплект</a></li>
          <li><a href="#s2"><span class="n">02</span>Анализ документации</a></li>
          <li><a href="#s3"><span class="n">03</span>Выбор технологии</a></li>
          <li><a href="#s4"><span class="n">04</span>Единичные и серийные партии</a></li>
          <li><a href="#s5"><span class="n">05</span>Контроль и паспорт</a></li>
        </ul>
      </div>
      <div class="ar-toc-cta">
        <div class="ar-toc-cta-t">Есть чертёж детали?</div>
        <p class="ar-toc-cta-p">Пришлите КД — инженер оценит технологичность и рассчитает срок изготовления партии.</p>
        <button class="ar-toc-cta-btn" onclick="openRequestModal('tz')">Отправить ТЗ →</button>
      </div>
    </aside>
  </div>

  <!-- TAGS -->
  <div class="ar-tags">
    <span class="ar-tags-lbl">Теги</span>
    <span class="ar-tag">Чертёж заказчика</span>
    <span class="ar-tag">КД</span>
    <span class="ar-tag">Единичные партии</span>
    <span class="ar-tag">Производство</span>
  </div>

  <!-- RELATED -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">→</span>
      <h2 class="pd-sec-title">Читайте также</h2>
    </div>
    <div class="pd-rel-grid">
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'otvod-svarnoy-besshovnyy' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-7.jpg' ) ); ?>" alt="Бесшовные и сварные отводы" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Производство</div><div class="pd-rel-title">Бесшовные и сварные отводы: в чём разница</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'vybor-stali' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.jpg' ) ); ?>" alt="Выбор стали для СДТ" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Материаловедение</div><div class="pd-rel-title">Как выбрать сталь для СДТ</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-6.jpg' ) ); ?>" alt="Контроль качества СДТ" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Контроль качества</div><div class="pd-rel-title">От входного контроля до паспорта изделия</div></div>
      </a>
    </div>
  </div>

  <!-- CTA -->
  <div class="pd-cta">
    <div>
      <div class="pd-cta-h">Есть нестандартная<br>деталь по <em>чертежу</em>?</div>
      <p class="pd-cta-p">Пришлите документацию — инженер оценит технологичность, подберёт материал и рассчитает срок изготовления.</p>
    </div>
    <a class="pd-cta-btn" href="javascript:void(0)" onclick="openRequestModal('tz')">Отправить ТЗ →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
