<?php
/**
 * Статья «ГОСТ, ОСТ и ТУ на соединительные детали трубопровода» — 1:1 из html/statya-normativnaya-baza.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет).
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-09.ART‑04 / REV.1' );

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
    <b>ГОСТ, ОСТ и ТУ на СДТ</b>
  </div>

  <!-- HERO -->
  <div class="ar-hero">
    <span class="ar-cat">Нормативы</span>
    <h1 class="ar-h1">ГОСТ, ОСТ и ТУ на соединительные детали трубопровода</h1>
    <p class="ar-lead">В трубопроводной арматуре одного ГОСТа часто недостаточно — над ним надстраиваются отраслевые стандарты и технические условия конкретного завода. Разбираем, как устроена эта многоуровневая система и почему для АЭС и ТЭС набор документов шире, чем для промышленного объекта.</p>
    <div class="ar-meta">
      <span>03.07.2026</span>
      <span>Чтение · <b>6 мин</b></span>
      <span>Инженерный отдел «Промышленная Энергетика»</span>
    </div>
  </div>

  <!-- MEDIA -->
  <div class="ar-media">
    <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-4.jpg' ) ); ?>" alt="Документация и нормативная база производства" loading="eager">
    <span class="ar-media-tag">Технический архив завода · Челябинск</span>
  </div>

  <!-- BODY + TOC -->
  <div class="ar-split">
    <article class="ar-content">

      <p>Спецификация на соединительную деталь трубопровода редко ссылается на один документ. Даже когда в чертеже указан конкретный ГОСТ, за ним стоит целая иерархия нормативов — от базового государственного стандарта до технических условий, разработанных под единичное изделие. Понимание этой структуры напрямую влияет на то, какую деталь и с каким пакетом документов получит заказчик.</p>

      <h2 id="s1"><span class="num">01</span>Четыре уровня нормативной базы</h2>
      <p>Нормативные документы, применяемые в производстве СДТ, выстроены иерархически — от общего к частному.</p>

      <div class="ar-pyramid">
        <div class="ar-tier"><span class="l">ГОСТ</span><div class="r"><span class="r-t">Государственный стандарт</span><span class="r-d">Базовый уровень: общие технические требования, размерные ряды, методы испытаний — единые для всей отрасли.</span></div></div>
        <div class="ar-tier"><span class="l">ОСТ</span><div class="r"><span class="r-t">Отраслевой стандарт</span><span class="r-d">Уточняет требования ГОСТ для конкретной отрасли — энергетики, нефтехимии — с учётом специфики эксплуатации.</span></div></div>
        <div class="ar-tier"><span class="l">СТО / ПНАЭ</span><div class="r"><span class="r-t">Корпоративный и атомный стандарт</span><span class="r-d">Стандарты организаций (например, СТО Росатома) и нормы ПНАЭ — обязательны для поставок на объекты АЭС-класса.</span></div></div>
        <div class="ar-tier"><span class="l">ТУ</span><div class="r"><span class="r-t">Технические условия</span><span class="r-d">Разрабатываются заводом под нестандартное изделие, когда деталь не описана ни одним действующим ГОСТ или ОСТ.</span></div></div>
      </div>

      <h2 id="s2"><span class="num">02</span>Когда действует ГОСТ, а когда нужен ОСТ</h2>
      <p>ГОСТ задаёт общие правила — размеры, допуски, методы контроля, применимые в большинстве отраслей. Но энергетика — особенно тепловая и атомная — предъявляет к деталям требования, которые общий ГОСТ не покрывает: расширенный объём неразрушающего контроля, дополнительные требования к прослеживаемости плавки, ужесточённые допуски. Эти требования фиксируются в отраслевых ОСТ и корпоративных СТО, которые действуют поверх базового ГОСТа, а не вместо него.</p>

      <h2 id="s3"><span class="num">03</span>ПНАЭ и НП — специфика атомной энергетики</h2>
      <p>Для объектов АЭС-класса добавляется ещё один слой — нормы и правила ПНАЭ и НП, регулирующие безопасность на объектах использования атомной энергии. Именно эти документы, наравне с маркой стали, определяют объём и метод контроля детали — вплоть до того, какой процент сварных швов подлежит 100% неразрушающему контролю. Подробнее о том, как устроен сам процесс контроля, — в статье <a href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">«Контроль качества СДТ»</a>.</p>

      <div class="ar-callout">
        <span class="ar-callout-lbl">Важно</span>
        <p>Указать в заказе «деталь по ГОСТ» недостаточно для объекта АЭС-класса — нужно явно зафиксировать применимый ОСТ, СТО или норму ПНАЭ, иначе объём контроля и пакет документов будет собран по умолчанию для промышленного трубопровода.</p>
      </div>

      <h2 id="s4"><span class="num">04</span>Технические условия — когда стандарта не существует</h2>
      <p>Если деталь имеет нестандартную геометрию или комбинацию параметров, для которой нет применимого ГОСТ или ОСТ — например, деталь по индивидуальному чертежу заказчика, — завод разрабатывает собственные технические условия (ТУ). Это не обход стандартов, а официальный механизм: ТУ проходят согласование и регистрацию, после чего становятся полноценным нормативным документом для конкретного изделия. Подробнее о минимальном комплекте документации, который нужен заводу для разработки ТУ под изделие, — в статье <a href="<?php echo esc_url( promen_article_url( 'chertezh-zakazchika' ) ); ?>">«Изготовление по чертежам заказчика»</a>.</p>

      <div class="ar-quote">
        <p>«Нормативный документ объекта определяет не только форму детали, но и то, какой марки будет сталь, каким методом её проконтролируют и какие документы получит заказчик».</p>
      </div>

      <h2 id="s5"><span class="num">05</span>Полный реестр документов завода</h2>
      <p>Действующие сертификаты, декларации соответствия и технические условия, применяемые заводом «Промышленная Энергетика», собраны в едином реестре — с фильтром по типу документа и категории изделия.</p>
      <p>Открыть реестр можно на странице <?php if ( $promen_nb_url ) : ?><a href="<?php echo esc_url( $promen_nb_url ); ?>"></a><?php endif; ?> — там же указана привязка каждого документа к соответствующей категории каталога.</p>

    </article>

    <!-- TOC SIDEBAR -->
    <aside class="ar-toc">
      <div class="ar-toc-box">
        <span class="ar-toc-lbl">В этой статье</span>
        <ul class="ar-toc-list" id="tocList">
          <li><a href="#s1"><span class="n">01</span>Четыре уровня стандартов</a></li>
          <li><a href="#s2"><span class="n">02</span>ГОСТ и ОСТ</a></li>
          <li><a href="#s3"><span class="n">03</span>ПНАЭ и атомная энергетика</a></li>
          <li><a href="#s4"><span class="n">04</span>Технические условия</a></li>
          <li><a href="#s5"><span class="n">05</span>Реестр документов завода</a></li>
        </ul>
      </div>
      <div class="ar-toc-cta">
        <div class="ar-toc-cta-t">Не уверены, какой документ нужен?</div>
        <p class="ar-toc-cta-p">Пришлите параметры объекта — инженер подберёт применимый нормативный документ.</p>
        <button class="ar-toc-cta-btn" onclick="openRequestModal('solution')">Уточнить документ →</button>
      </div>
    </aside>
  </div>

  <!-- TAGS -->
  <div class="ar-tags">
    <span class="ar-tags-lbl">Теги</span>
    <span class="ar-tag">ГОСТ</span>
    <span class="ar-tag">ОСТ</span>
    <span class="ar-tag">ТУ</span>
    <span class="ar-tag">ПНАЭ</span>
    <span class="ar-tag">Нормативы</span>
  </div>

  <!-- RELATED -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">→</span>
      <h2 class="pd-sec-title">Читайте также</h2>
    </div>
    <div class="pd-rel-grid">
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'vybor-stali' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.jpg' ) ); ?>" alt="Выбор стали для СДТ" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Материаловедение</div><div class="pd-rel-title">Как выбрать сталь для СДТ</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-6.jpg' ) ); ?>" alt="Контроль качества СДТ" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Контроль качества</div><div class="pd-rel-title">От входного контроля до паспорта изделия</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'postavka-aes-tes' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-3.jpg' ) ); ?>" alt="Поставки для АЭС и ТЭС" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Проекты</div><div class="pd-rel-title">Поставки для АЭС и ТЭС: требования</div></div>
      </a>
    </div>
  </div>

  <!-- CTA -->
  <div class="pd-cta">
    <div>
      <div class="pd-cta-h">Нужна консультация<br>по <em>нормативам</em>?</div>
      <p class="pd-cta-p">Опишите объект — инженер подберёт применимый ГОСТ, ОСТ или ТУ и уточнит состав пакета документов.</p>
    </div>
    <?php if ( $promen_nb_url ) : ?><a href="<?php echo esc_url( $promen_nb_url ); ?>"></a><?php endif; ?>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
