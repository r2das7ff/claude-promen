<?php
/**
 * Подборщик аналогов марок стали — /kalkulyatory/analogi-staley/.
 *
 * Поле поиска принимает марку в любой записи (ГОСТ, EN, ASTM/ASME, AISI/UNS,
 * DIN, JIS, GB) и отвечает паспортом российской марки: аналоги по системам
 * со степенью соответствия, сравнение химсостава, детали из этой стали в
 * каталоге. Ниже — полная таблица соответствия и справочная статья.
 *
 * Данные — inc/steel-analogs.php, серверная отрисовка — inc/steel-analogs-render.php,
 * интерактив — assets/js/steel-analogs.js, стили — assets/css/steel-analogs.css.
 */
add_filter( 'promen_footer_idx', fn () => 'ПЭ-КЛК.06 / REV.2' );
add_filter( 'promen_strip_text', fn () => 'ПЭ-КЛК' );

get_header();
$promen_hub    = promen_calc_url( 'kalkulyatory' );
$promen_grades = promen_steel_analog_grades();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$promen_req  = isset( $_GET['marka'] ) ? sanitize_title( wp_unslash( (string) $_GET['marka'] ) ) : '';
$promen_found = $promen_req !== '' ? promen_steel_analog_find( $promen_req ) : null;
$promen_cur   = $promen_found ?? promen_steel_analog_find( '12h18n10t' ) ?? $promen_grades[0];
$promen_n    = count( $promen_grades );
$promen_pop  = [ '12Х18Н10Т', '09Г2С', '20', '12Х1МФ', '15Х5М', 'AISI 321', 'AISI 304', '316L', 'P265GH', 'A105', 'SUS321', 'Q355' ];
?>
<div class="pg sa-page">

  <?php if ( $promen_hub ) : ?>
    <nav class="clc-crumbs" aria-label="Раздел">
      <a href="<?php echo esc_url( $promen_hub ); ?>">Калькуляторы</a>
      <span class="sep">/</span><span>Аналоги марок стали</span>
    </nav>
  <?php endif; ?>

  <div class="clc-hero sa-hero">
    <div class="clc-eyebrow">Справочник · ПЭ-КЛК/06</div>
    <h1 class="clc-h1">Аналоги марок стали<br><em>ГОСТ, EN, ASTM, AISI, DIN, JIS, GB</em></h1>
    <p class="clc-desc">Введите марку из спецификации в любой записи — российскую, европейскую, американскую,
      японскую или китайскую. Подборщик покажет марку по ГОСТ, её аналоги во всех системах со степенью
      соответствия и сравнит химсостав.</p>
  </div>

  <section class="sa-tool" id="podbor" data-sa aria-label="Подбор аналога марки стали">
    <div class="sa-find" data-find>
      <label class="sa-find-lbl" for="saQ">Марка из документации</label>
      <div class="sa-find-row">
        <svg class="sa-find-ic" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/></svg>
        <input id="saQ" class="sa-find-in" type="text" role="combobox" aria-autocomplete="list" aria-expanded="false"
          aria-controls="saList" aria-describedby="saHint" autocomplete="off" autocapitalize="characters" spellcheck="false"
          enterkeyhint="search" placeholder="12Х18Н10Т, AISI 321, P265GH, A105…">
        <button type="button" class="sa-clear" data-clear aria-label="Очистить поле" hidden>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
        <ul id="saList" class="sa-sug" role="listbox" aria-label="Найденные марки" hidden></ul>
      </div>
      <p id="saHint" class="sa-find-hint">Латиница, кириллица, с приставкой AISI или SA — неважно. <?php echo esc_html( $promen_n . ' ' . promen_sa_plural( $promen_n, 'марка', 'марки', 'марок' ) ); ?> · 6 систем обозначений.</p>
      <div class="sa-pop">
        <span class="sa-lbl">Часто ищут</span>
        <?php foreach ( $promen_pop as $p ) : ?>
          <button type="button" class="sa-pop-b" data-q="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <article class="sa-pass" data-pass data-id="<?php echo esc_attr( $promen_cur['id'] ); ?>" aria-label="Паспорт марки">
      <?php promen_sa_passport( $promen_cur, null === $promen_found ); ?>
    </article>
    <p class="sr-only" data-status role="status" aria-live="polite"></p>
    <div class="sa-toast" data-toast role="status" aria-live="polite"></div>
  </section>

  <section class="sa-sec" id="tablica" aria-labelledby="saTblH">
    <div class="sa-sec-hd">
      <h2 id="saTblH" class="sa-h2">Таблица соответствия марок стали</h2>
      <p class="sa-sec-lead">Все марки подборщика в одной таблице. Риски у обозначения — степень соответствия:
        три — прямой аналог, две — близкий, одна — условный. Нажмите на марку, чтобы открыть её паспорт.</p>
    </div>
    <div class="sa-chips sa-filter" role="group" aria-label="Группа марок" data-filter>
      <button type="button" class="sa-chip" data-g="" aria-pressed="true">Все<span><?php echo esc_html( $promen_n ); ?></span></button>
      <?php foreach ( promen_steel_analog_groups() as $gk => $glabel ) : ?>
        <?php $gn = count( array_filter( $promen_grades, static fn( $g ) => $g['g'] === $gk ) ); ?>
        <button type="button" class="sa-chip" data-g="<?php echo esc_attr( $gk ); ?>" aria-pressed="false"><?php echo esc_html( $glabel ); ?><span><?php echo esc_html( $gn ); ?></span></button>
      <?php endforeach; ?>
    </div>
    <div class="sa-tbl-wrap" role="region" aria-labelledby="saTblH" tabindex="0">
      <?php promen_sa_table( $promen_grades ); ?>
    </div>
    <p class="sa-tbl-note">Соответствия справочные и составлены по стандартам на марки. Аналог помогает
      прочитать импортную документацию и выбрать замену, но не заменяет проверку по требованиям проекта.</p>
  </section>

  <?php get_template_part( 'parts/steel-analogs-article' ); ?>

</div>
<script type="application/json" id="saData"><?php echo wp_json_encode( promen_steel_analog_bootstrap(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
<?php
get_footer();
