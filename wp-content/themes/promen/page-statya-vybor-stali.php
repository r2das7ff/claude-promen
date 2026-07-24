<?php
/**
 * Статья «Как выбрать сталь для соединительных деталей трубопровода» — 1:1 из html/statya-vybor-stali.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет).
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-09.ART‑01 / REV.1' );

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
    <b>Как выбрать сталь для СДТ</b>
  </div>

  <!-- HERO -->
  <div class="ar-hero">
    <span class="ar-cat">Материаловедение</span>
    <h1 class="ar-h1">Как выбрать сталь для соединительных деталей трубопровода</h1>
    <p class="ar-lead">Марка стали — не формальность в спецификации, а решение, от которого зависит, выдержит ли деталь давление, температуру и среду трубопровода все расчётные годы эксплуатации. Разбираем, какие стали применяются в СДТ и как завод подбирает материал под техническое задание.</p>
    <div class="ar-meta">
      <span>14.07.2026</span>
      <span>Чтение · <b>8 мин</b></span>
      <span>Инженерный отдел «Промышленная Энергетика»</span>
    </div>
  </div>

  <!-- MEDIA -->
  <div class="ar-media">
    <img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-1.jpg' ) ); ?>" alt="Стальные конструкции производственного цеха" loading="eager">
    <span class="ar-media-tag">Металлоконструкции цеха · Челябинск</span>
  </div>

  <!-- BODY + TOC -->
  <div class="ar-split">
    <article class="ar-content">

      <p>Соединительная деталь трубопровода — отвод, тройник, переход, фланец — работает в тех же условиях, что и сама труба: давление, температура среды, её химическая агрессивность, цикличность нагрузок. Ошибка в выборе марки стали не проявляется на приёмке — она проявляется через годы, в виде коррозии, ползучести металла при высоких температурах или хрупкого разрушения на морозе. Поэтому подбор стали — это не пункт спецификации, который можно скопировать из соседнего проекта, а расчёт под конкретные параметры объекта.</p>

      <h2 id="s1"><span class="num">01</span>Три группы сталей в трубопроводной арматуре</h2>
      <p>На заводе «Промышленная Энергетика» детали изготавливаются из трёх укрупнённых групп сталей — каждая закрывает свой диапазон условий эксплуатации.</p>

      <div class="ar-table">
        <div class="ar-row"><span class="k">Углеродистые / низколегированные</span><span class="v">09Г2С, Ст20 — трубопроводы низкого и среднего давления, температура среды до +450°C, без агрессивных сред.</span></div>
        <div class="ar-row"><span class="k">Жаропрочные легированные</span><span class="v">12Х1МФ, 15Х5М — паропроводы ТЭС, температура среды до +570°C, длительная работа под давлением при высокой температуре.</span></div>
        <div class="ar-row"><span class="k">Нержавеющие аустенитные</span><span class="v">08Х18Н10Т и аналоги — АЭС-класс, агрессивные и особо чистые среды, требования по прослеживаемости плавки.</span></div>
      </div>

      <h2 id="s2"><span class="num">02</span>Углеродистые и низколегированные стали</h2>
      <p>Сталь 09Г2С — рабочая лошадка промышленных трубопроводов: хорошая свариваемость, стабильные механические свойства, умеренная цена. Применяется там, где среда не агрессивна, а температура не превышает пределов, при которых начинается заметная ползучесть металла. Это основной материал для деталей общепромышленного назначения — вода, пар низких параметров, нефтепродукты в трубопроводах общего назначения.</p>
      <p>Ст20 — более простой аналог для деталей, где не требуется повышенная хладостойкость или свариваемость на уровне 09Г2С. Выбор между ними чаще определяется техническими условиями заказчика и уже применяемым на объекте сортаментом труб — деталь и труба должны быть металлургически совместимы по свариваемости.</p>

      <h2 id="s3"><span class="num">03</span>Жаропрочные легированные стали</h2>
      <p>Как только температура среды приближается к 450–570°C — а это типичные параметры паропроводов ТЭС — в дело вступают легированные жаропрочные стали. 12Х1МФ — стандарт для паропроводов высокого давления: добавки хрома, молибдена и ванадия замедляют ползучесть металла под длительной нагрузкой при высокой температуре. Для более тяжёлых условий применяется 15Х5М с повышенным содержанием хрома.</p>

      <div class="ar-callout">
        <span class="ar-callout-lbl">Важно</span>
        <p>Ползучесть — это медленная необратимая деформация металла под постоянной нагрузкой при высокой температуре. Обычная углеродистая сталь, рассчитанная только на предел прочности, в паропроводе ТЭС может «потечь» за несколько лет эксплуатации даже без превышения давления.</p>
      </div>

      <h2 id="s4"><span class="num">04</span>Нержавеющие аустенитные стали для АЭС-класса</h2>
      <p>08Х18Н10Т и близкие по составу аналоги применяются там, где на первый план выходят коррозионная стойкость и требования атомной энергетики к прослеживаемости материала. Аустенитная структура даёт стали пластичность и стойкость к межкристаллитной коррозии — критично для контуров с деминерализованной водой и агрессивными средами.</p>
      <p>Для объектов АЭС-класса, как например поставки на <a href="<?php echo esc_url( promen_project_url( 'kurskaya-aes' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">Курскую АЭС‑2</a>, к марке стали добавляется требование полной прослеживаемости плавки — от сертификата на металл до маркировки готового изделия. Подробнее о том, как это устроено на производстве, — в статье о <a href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">контроле качества СДТ</a>.</p>

      <div class="ar-quote">
        <p>«Материал не выбирают по каталогу — его подбирают под параметры конкретного трубопровода: давление, температуру, среду и нормативный документ объекта».</p>
      </div>

      <h2 id="s5"><span class="num">05</span>Как завод подбирает сталь под ваше ТЗ</h2>
      <p>На практике заказчик редко присылает готовую марку стали — чаще это параметры: DN, PN, температура и характер среды, применимый нормативный документ (ГОСТ, ОСТ, ТУ). Инженер завода сопоставляет их с сортаментом доступных сталей и с нормативной базой объекта — подробнее о том, как устроена система стандартов, читайте в статье про <a href="<?php echo esc_url( promen_article_url( 'normativnaya-baza' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">ГОСТ, ОСТ и ТУ на СДТ</a>.</p>
      <ol>
        <li><strong>Параметры трубопровода.</strong> DN, PN, температура и агрессивность среды — базовые вводные для отсева непригодных марок.</li>
        <li><strong>Нормативный документ объекта.</strong> ГОСТ, ОСТ, ПНАЭ или ТУ заказчика — часто напрямую ограничивает допустимый сортамент сталей.</li>
        <li><strong>Технология изготовления.</strong> Не каждая сталь одинаково хорошо ведёт себя при штамповке или сварке — см. статью о <a href="<?php echo esc_url( promen_article_url( 'otvod-svarnoy-besshovnyy' ) ); ?>" style="color:var(--dark);border-bottom:1px solid var(--g1);">бесшовных и сварных отводах</a>.</li>
        <li><strong>Согласование с заказчиком.</strong> Финальная марка фиксируется в спецификации до запуска партии в производство.</li>
      </ol>

    </article>

    <!-- TOC SIDEBAR -->
    <aside class="ar-toc">
      <div class="ar-toc-box">
        <span class="ar-toc-lbl">В этой статье</span>
        <ul class="ar-toc-list" id="tocList">
          <li><a href="#s1"><span class="n">01</span>Три группы сталей</a></li>
          <li><a href="#s2"><span class="n">02</span>Углеродистые и низколегированные</a></li>
          <li><a href="#s3"><span class="n">03</span>Жаропрочные легированные</a></li>
          <li><a href="#s4"><span class="n">04</span>Нержавеющие аустенитные</a></li>
          <li><a href="#s5"><span class="n">05</span>Как подбирает завод</a></li>
        </ul>
      </div>
      <div class="ar-toc-cta">
        <div class="ar-toc-cta-t">Не уверены в марке стали?</div>
        <p class="ar-toc-cta-p">Пришлите параметры трубопровода — инженер подберёт материал и нормативный документ.</p>
        <button class="ar-toc-cta-btn" onclick="openRequestModal('solution')">Подобрать решение →</button>
      </div>
    </aside>
  </div>

  <!-- TAGS -->
  <div class="ar-tags">
    <span class="ar-tags-lbl">Теги</span>
    <span class="ar-tag">Сталь</span>
    <span class="ar-tag">Материаловедение</span>
    <span class="ar-tag">09Г2С</span>
    <span class="ar-tag">12Х1МФ</span>
    <span class="ar-tag">08Х18Н10Т</span>
    <span class="ar-tag">АЭС</span>
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
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'kontrol-kachestva' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-6.jpg' ) ); ?>" alt="Контроль качества СДТ" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Контроль качества</div><div class="pd-rel-title">От входного контроля до паспорта изделия</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_article_url( 'normativnaya-baza' ) ); ?>">
        <div class="pd-rel-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/photos/promen-photo-hor-4.jpg' ) ); ?>" alt="Нормативная база СДТ" loading="lazy"></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">Нормативы</div><div class="pd-rel-title">ГОСТ, ОСТ и ТУ на СДТ</div></div>
      </a>
    </div>
  </div>

  <!-- CTA -->
  <div class="pd-cta">
    <div>
      <div class="pd-cta-h">Знаете параметры,<br>но не марку <em>стали</em>?</div>
      <p class="pd-cta-p">Пришлите DN, PN, температуру и среду трубопровода — инженер подберёт материал и рассчитает срок изготовления.</p>
    </div>
    <a class="pd-cta-btn" href="javascript:void(0)" onclick="openRequestModal('solution')">Подобрать решение →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
