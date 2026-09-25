<?php
/**
 * Проект «АЭС «Руппур»» — 1:1 из html/proekt-aes-ruppur.html (Open Design, 2026-07-23).
 * Хром — header.php; футер без s10 (в макете его нет) — promen_footer_form.
 * Скрипты/стили раздела — assets/js/projects.js, assets/css/proekt.css.
 */
add_filter( 'promen_footer_form', '__return_false' );
add_filter( 'promen_footer_idx', fn () => 'ПЭ-07.PRJ‑03 / REV.1' );

$promen_catalog_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_proekty_url  = ( $p = promen_page( 'proekty' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_contacts_url = ( $p = promen_page( 'contacts' ) ) ? get_permalink( $p ) : home_url( '/' );
$promen_sdt_term     = get_term_by( 'slug', 'sdt', 'product_cat' );
$promen_sdt_url      = ( $promen_sdt_term && ! is_wp_error( $l = get_term_link( $promen_sdt_term ) ) ) ? $l : $promen_catalog_url;
$promen_nb_url       = ( $p = promen_page( 'normativnaya-baza' ) ) ? get_permalink( $p ) : '';

get_header();
?>
<div class="pg">

  <!-- BREADCRUMB -->
  <div class="pd-crumb">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a><span>/</span>
    <a href="<?php echo esc_url( $promen_proekty_url ); ?>">Проекты</a><span>/</span>
    <b>АЭС «Руппур»</b>
  </div>

  <!-- HERO -->
  <div class="pd-hero">
    <div class="pd-hero-l">
      <div class="pd-badges">
        <span class="pd-badge">АЭС</span>
        <span class="pd-badge status building"><span class="dot"></span>В стадии строительства</span>
        <span class="pd-badge intl">Экспорт</span>
      </div>
      <h1 class="pd-h1">АЭС «Руппур»</h1>
      <div class="pd-loc">Ишварди, округ Пабна, восточный берег р. Падма · Бангладеш · 160 км от Дакки</div>
      <p class="pd-desc">Первая АЭС Бангладеш: два энергоблока ВВЭР‑1200 (проект В‑523), генподрядчик —
        АО «Атомстройэкспорт»; в 2026 году в реактор первого блока загружено топливо. Завод
        «Промышленная Энергетика» поставил трубы, соединительные детали трубопровода и арматуру
        для вспомогательных систем энергоблоков.</p>
      <div class="pd-stats">
        <div class="hs"><span class="hs-v">20 / 08Х18Н10Т</span><span class="hs-k">Марки стали</span></div>
        <div class="hs"><span class="hs-v">3 группы</span><span class="hs-k">Трубы · СДТ · арматура</span></div>
        <div class="hs"><span class="hs-v">2×1200 МВт</span><span class="hs-k">Мощность энергоблоков</span></div>
        <div class="hs"><span class="hs-v">7,0 МПа</span><span class="hs-k">Пар второго контура ВВЭР‑1200</span></div>
      </div>
    </div>
    <div class="pd-hero-r">
      <picture><source srcset="/wp-content/themes/promen/assets/img/projects/rupp.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/rupp.png" alt="АЭС Руппур" loading="eager" referrerpolicy="no-referrer" onerror="this.style.display='none';this.nextElementSibling.style.display='block';" width="1536" height="1024"></picture>
      <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice"><rect width="400" height="320" fill="#2EA8BA" opacity=".22"/><rect x="60" y="140" width="280" height="160" fill="#0F2A44"/><circle cx="200" cy="130" r="80" fill="none" stroke="#2EA8BA" stroke-width="2.5" opacity=".55"/><circle cx="200" cy="130" r="55" fill="none" stroke="#2EA8BA" stroke-width="1.5" opacity=".4"/></svg>
      <span class="pd-hero-r-tag">АЭС «Руппур» · ВВЭР‑1200</span>
    </div>
  </div>

  <!-- СОСТАВ ПОСТАВКИ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">01</span>
      <h2 class="pd-sec-title">Состав поставки</h2>
    </div>
    <div class="pd-phases">
      <div class="pd-phase">
        <div class="pd-phase-lbl">Группа 1</div>
        <div class="pd-phase-v">Трубы</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Сортамент</span><span class="pd-phase-rv">Бесшовные стальные трубы</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Материал</span><span class="pd-phase-rv">Сталь 20, аустенитная сталь 08Х18Н10Т</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Назначение</span><span class="pd-phase-rv">Вспомогательные системы энергоблоков</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Группа 2</div>
        <div class="pd-phase-v">СДТ</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Номенклатура</span><span class="pd-phase-rv">Отводы, тройники, переходы, заглушки</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Нормативы</span><span class="pd-phase-rv">НП‑089‑15, ОСТ 34‑42‑6хх‑84</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Изготовление</span><span class="pd-phase-rv">Серийные и по чертежам заказчика</span></div>
        </div>
      </div>
      <div class="pd-phase">
        <div class="pd-phase-lbl">Группа 3</div>
        <div class="pd-phase-v">Арматура</div>
        <div class="pd-phase-rows">
          <div class="pd-phase-row"><span class="pd-phase-rk">Состав</span><span class="pd-phase-rv">Штуцеры, клапаны и вентили</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Крепёж</span><span class="pd-phase-rv">Шпильки и гайки ГОСТ 9066 / 9064</span></div>
          <div class="pd-phase-row"><span class="pd-phase-rk">Испытания</span><span class="pd-phase-rv">Гидравлические, ГОСТ 33257‑2015</span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ЭТАПЫ ПОСТАВКИ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">02</span>
      <h2 class="pd-sec-title">Этапы поставки</h2>
    </div>
    <div class="pd-tl">
      <div class="pd-step">
        <div class="pd-step-n">01</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Документация генподрядчика</div>
          <div class="pd-step-d">Приём КД АО «Атомстройэкспорт», согласование марок стали, сортамента труб и объёма контроля для вспомогательных систем энергоблоков ВВЭР‑1200.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">02</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Изготовление и комплектация</div>
          <div class="pd-step-d">Соединительные детали — в том числе по чертежам заказчика — изготовлены в Челябинске и собраны в одну экспортную поставку вместе с трубами, арматурой и крепежом.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">03</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Контроль качества</div>
          <div class="pd-step-d">Визуально-измерительный, ультразвуковой и капиллярный контроль по марке стали, гидравлические испытания арматуры по ГОСТ 33257‑2015, сверка сертификатов на металл.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">04</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Экспортная логистика</div>
          <div class="pd-step-d">Морем до порта Монгла, дальше баржами по Падме к причалу площадки. Упаковка рассчитана на перевалки и влажный тропический климат.</div>
        </div>
      </div>
      <div class="pd-step">
        <div class="pd-step-n">05</div>
        <div class="pd-step-b">
          <div class="pd-step-t">Документация и передача заказчику</div>
          <div class="pd-step-d">Паспорта изделий, сертификаты на металл с номерами плавок, протоколы контроля и испытаний, сертификат соответствия ТР ТС 032 RU С‑RU.АБ53.В.08323/23.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ИСПОЛЬЗУЕМАЯ ПРОДУКЦИЯ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">03</span>
      <h2 class="pd-sec-title">Используемая продукция</h2>
    </div>
    <div class="pd-prod-grid">
      <div class="pd-prod">
        <span class="pd-prod-code">ТР</span>
        <div class="pd-prod-name">Трубы бесшовные</div>
        <p class="pd-prod-desc">Горяче- и холоднодеформированные трубы из стали 20 и 08Х18Н10Т по ГОСТ 8732/8734 — сортамент для вспомогательных систем.</p>
        <?php if ( $u = promen_project_term_url( 'product_cat', 'truby-bsh' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Бесшовные трубы →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">АС</span>
        <div class="pd-prod-name">Детали для атомных станций</div>
        <p class="pd-prod-desc">Тройники, переходы и заглушки по серии ОСТ 34‑42‑6хх‑84 для трубопроводов АС до 2,2 МПа, в том числе исполнения по чертежам.</p>
        <?php if ( $u = promen_project_term_url( 'norm', 'ost-34-42-676-84' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Тройники ОСТ 34‑42‑676 →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">ЗРА</span>
        <div class="pd-prod-name">Арматура и крепёж</div>
        <p class="pd-prod-desc">Клапаны, вентили и штуцеры с испытаниями по ГОСТ 33257‑2015, шпильки и гайки для фланцевых соединений по ГОСТ 9066/9064.</p>
        <?php if ( $u = promen_project_term_url( 'product_cat', 'armatura' ) ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $u ); ?>">Арматура в каталоге →</a><?php endif; ?>
      </div>
      <div class="pd-prod">
        <span class="pd-prod-code">НБ</span>
        <div class="pd-prod-name">Нормативная база</div>
        <p class="pd-prod-desc">НП‑089‑15, ОСТ, ГОСТ и сертификат ТР ТС 032 — документы, по которым изготовлена и проверена поставка.</p>
        <?php if ( $promen_nb_url ) : ?><a class="pd-prod-link" href="<?php echo esc_url( $promen_nb_url ); ?>">Нормативная база →</a><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ДРУГИЕ ПРОЕКТЫ -->
  <div class="pd-sec">
    <div class="pd-sec-head">
      <span class="pd-sec-num">04</span>
      <h2 class="pd-sec-title">Другие проекты</h2>
    </div>
    <div class="pd-rel-grid">
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'kurskaya-aes' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/kursk2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/kursk2.png" alt="Курская АЭС-2" loading="lazy" referrerpolicy="no-referrer" width="1024" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Россия</div><div class="pd-rel-title">Курская АЭС‑2</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'cherepetskaya-gres' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/tec2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/tec2.png" alt="Черепетская ГРЭС" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">ГРЭС · Россия</div><div class="pd-rel-title">Черепетская ГРЭС</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'aes-akkuyu' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/turk2.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/turk2.png" alt="АЭС Аккую" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">АЭС · Турция</div><div class="pd-rel-title">АЭС «Аккую»</div></div>
      </a>
      <a class="pd-rel" href="<?php echo esc_url( promen_project_url( 'teploelektrocentral-tec-3' ) ); ?>">
        <div class="pd-rel-media"><picture><source srcset="/wp-content/themes/promen/assets/img/projects/tec3.webp" type="image/webp"><img src="/wp-content/themes/promen/assets/img/projects/tec3.png" alt="Омская ТЭЦ-3" loading="lazy" referrerpolicy="no-referrer" width="1536" height="1024"></picture></div>
        <div class="pd-rel-body"><div class="pd-rel-tag">ТЭЦ · Россия</div><div class="pd-rel-title">Омская ТЭЦ‑3</div></div>
      </a>
    </div>
  </div>

  <!-- CTA -->
  <div class="pd-cta">
    <div>
      <div class="pd-cta-h">Нужна похожая<br>поставка для <em>вашего объекта</em>?</div>
      <p class="pd-cta-p">Пришлите чертёж или спецификацию — рассчитаем материал, срок изготовления и стоимость партии по аналогии с этим проектом.</p>
    </div>
    <a class="pd-cta-btn" href="<?php echo esc_url( $promen_contacts_url ); ?>">Обсудить поставку →</a>
  </div>

  <!-- BAR -->
</div><!-- /.pg -->
<?php get_footer(); ?>
