<?php
/**
 * 404 — 1:1 из html/404.html (Open Design, 2026-07-23): echo запрошенного
 * пути (assets/js/page-404.js), поиск → живой реестр каталога (?q=),
 * чипы групп, быстрые разделы (только опубликованные). Футера в макете
 * нет — promen_footer_zone=false.
 */
add_filter( 'promen_footer_zone', '__return_false' );

$promen_catalog_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/catalog/' );
$promen_privacy_url = promen_privacy_url();

$promen_404_rows = [
	[ 'label' => 'Главная', 'url' => home_url( '/' ), 'desc' => 'Завод, производство, охват отраслей' ],
	[ 'label' => 'Каталог', 'url' => $promen_catalog_url, 'desc' => 'Соединительные детали, СДТ, фланцы, опоры, арматура' ],
];
foreach ( [
	'proekty'           => [ 'Проекты', 'Реализованные поставки для ТЭС и АЭС' ],
	'normativnaya-baza' => [ 'Нормативы', 'Реестр ГОСТ, ОСТ, ТУ и деклараций соответствия' ],
	'contacts'          => [ 'Контакты', 'Реквизиты, адрес производственной площадки, форма связи' ],
] as $slug => $row ) {
	$page = promen_page( $slug );
	if ( $page ) {
		$promen_404_rows[] = [ 'label' => $row[0], 'url' => get_permalink( $page ), 'desc' => $row[1] ];
	}
}

get_header();
?>
<div class="pg">

  <!-- STAGE: hyperframes + glitching numeral -->
  <section class="nf-stage">
    <div class="nf-scan" aria-hidden="true"></div>
    <div class="nf-hud nf-hud-tl" aria-hidden="true">СИГНАЛ ПОТЕРЯН</div>
    <div class="nf-hud nf-hud-tr" aria-hidden="true">HTTP <span>404</span><br>REV.1 · ПЭ-00.404</div>

    <div class="nf-core">
      <div class="nf-glitch-zone">
        <div class="nf-frames" aria-hidden="true">
          <div class="hf hf-1">
            <span class="c c-tl"></span><span class="c c-tr"></span><span class="c c-bl"></span><span class="c c-br"></span>
            <span class="hf-label">FRAME 01/03 · SYNC</span>
          </div>
          <div class="hf hf-2"><span class="hf-label">FRAME 02/03 · Δ+14MS</span></div>
          <div class="hf hf-3"><span class="hf-label">FRAME 03/03 · DESYNC</span></div>
        </div>

        <div class="nf-content">
          <div class="nf-eyebrow">
            <span class="nf-ey-num">ERR</span>
            <span class="nf-ey-label">HTTP · Not Found</span>
          </div>

          <h1 class="nf-num" data-text="404">404</h1>
        </div>
      </div>

      <h2 class="nf-h1">СТРАНИЦА <span class="hl">НЕ НАЙДЕНА</span></h2>
      <p class="nf-desc">Запрошенный раздел не существует, был перемещён или ещё не опубликован.
        Воспользуйтесь поиском по каталогу или перейдите в один из основных разделов ниже.</p>

      <div class="nf-path">
        <span>Запрошенный адрес</span>
        <code id="reqPath">—</code>
      </div>

      <form class="nf-search" id="nfSearch" method="get" action="<?php echo esc_url( $promen_catalog_url ); ?>">
        <div class="nf-search-ic" aria-hidden="true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20L16.5 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <input id="nfSearchInput" name="q" type="text" placeholder="Код, наименование, ГОСТ, материал…" autocomplete="off">
        <button type="submit">Найти →</button>
      </form>

      <div class="nf-chips">
        <a class="nf-chip" href="<?php echo esc_url( add_query_arg( 'group', 'otvody', $promen_catalog_url ) ); ?>">Отвод 90°</a>
        <a class="nf-chip" href="<?php echo esc_url( add_query_arg( 'group', 'flancy', $promen_catalog_url ) ); ?>">Фланец плоский</a>
        <a class="nf-chip" href="<?php echo esc_url( add_query_arg( 'group', 'opory', $promen_catalog_url ) ); ?>">Опоры</a>
        <a class="nf-chip" href="<?php echo esc_url( add_query_arg( 'group', 'sdt', $promen_catalog_url ) ); ?>">СДТ</a>
        <a class="nf-chip" href="<?php echo esc_url( add_query_arg( 'group', 'zaglushki', $promen_catalog_url ) ); ?>">Заглушка</a>
      </div>
    </div>
  </section>

  <!-- QUICK NAV REGISTRY -->
  <section class="nf-quick">
    <div class="nf-quick-label">Основные разделы</div>
    <div class="nf-quick-list">
      <?php foreach ( $promen_404_rows as $i => $row ) : ?>
      <a class="nf-qrow" href="<?php echo esc_url( $row['url'] ); ?>">
        <span class="nf-qn"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
        <span class="nf-qt"><?php echo esc_html( $row['label'] ); ?></span>
        <span class="nf-qd"><?php echo esc_html( $row['desc'] ); ?></span>
        <span class="nf-qarrow">→</span>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- BAR -->
  <div class="nf-bar">
    <span class="nf-bar-copy">© 2017–<?php echo esc_html( date_i18n( 'Y' ) ); ?> Промышленная Энергетика. Все права защищены.</span>
    <?php if ( $promen_privacy_url ) : ?><a class="nf-bar-policy" href="<?php echo esc_url( $promen_privacy_url ); ?>">Политика обработки персональных данных</a><?php endif; ?>
    <span class="nf-bar-idx">ПЭ-00.404 / REV.1</span>
  </div>

</div>
<?php get_footer(); ?>
