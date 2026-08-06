<?php
/**
 * Секция «Отдел продаж» — менеджеры направлений с прямыми контактами.
 * Используется на главной (перед футер-зоной) и на «Контактах» (внутри .pg).
 *
 * Контракт: реестровая сетка карточек в мире КТЛ-01 — фото 2:3, имя,
 * направление, прямые линии (тел с добавочным + почта) как mono-строки;
 * группы «атомная продукция» / «общее назначение» полосами-заголовками;
 * замыкающая плашка общей линии для сценария «не знаю, кому писать».
 * ≤639px карточка перекомпонуется в горизонтальную строку (фото 104px) —
 * на телефоне из секции звонят, а не разглядывают.
 *
 * Аргументы get_template_part():
 *   num   — код в шильдике eyebrow ('10' на главной, 'ОП' на контактах);
 *   lines — вешать ли js-lines на заголовок (front.js есть только на главной);
 *   flush — секция внутри .pg: ось rail уже дана обёрткой, не дублировать.
 *
 * Данные перенесены с prom-en.com/kontakty (2026-08-06), фото — в
 * assets/img/managers/. Телефоны: href без добавочного, добавочный текстом.
 */

$smgr_num   = $args['num']   ?? '10';
$smgr_lines = ! empty( $args['lines'] );
$smgr_flush = ! empty( $args['flush'] );

$smgr_groups = [
	[
		'label'    => 'Департамент продаж атомной продукции',
		'managers' => [
			[ 'img' => 'saitgareev',  'name' => 'Виктор Саитгареев',  'role' => 'Специалист по трубам и деталям для АЭС и ТЭС',   'tel' => '+7 (351) 217-00-99', 'href' => '+73512170099', 'ext' => '201', 'email' => 'zakaz1@prom-en.com' ],
			[ 'img' => 'kalashnikov', 'name' => 'Дмитрий Калашников', 'role' => 'Специалист по деталям трубопровода для АЭС и ТЭС', 'tel' => '+7 (351) 217-00-99', 'href' => '+73512170099', 'ext' => '206', 'email' => 'zakaz6@prom-en.com' ],
			[ 'img' => 'belov',       'name' => 'Кирилл Белов',       'role' => 'Специалист по трубам и СДТ для ТЭС',              'tel' => '+7 (351) 217-00-99', 'href' => '+73512170099', 'ext' => '205', 'email' => 'zakaz5@prom-en.com' ],
			[ 'img' => 'hadeev',      'name' => 'Виталий Хадеев',     'role' => 'Специалист по трубам и деталям для АЭС и ТЭС',   'tel' => '+7 (351) 220-02-65', 'href' => '+73512200265', 'ext' => '208', 'email' => 'zakaz8@prom-en.com' ],
		],
	],
	[
		'label'    => 'Отдел продаж продукции общего назначения',
		'managers' => [
			[ 'img' => 'otvagina',  'name' => 'Наталья Отвагина', 'role' => 'Специалист по трубам и деталям трубопровода', 'tel' => '+7 (351) 220-02-65', 'href' => '+73512200265', 'ext' => '213', 'email' => 'zakaz13@prom-en.com' ],
			[ 'img' => 'menshikov', 'name' => 'Илья Меньшиков',   'role' => 'Специалист по трубам и деталям в изоляции',   'tel' => '+7 (351) 220-02-65', 'href' => '+73512200265', 'ext' => '212', 'email' => 'zakaz12@prom-en.com' ],
			[ 'img' => 'kurbatov',  'name' => 'Сергей Курбатов',  'role' => 'Специалист по трубам и деталям трубопровода', 'tel' => '+7 (351) 217-00-99', 'href' => '+73512170099', 'ext' => '207', 'email' => 'zakaz7@prom-en.com' ],
			[ 'img' => 'belyakov',  'name' => 'Сергей Беляков',   'role' => 'Специалист по трубам и деталям трубопровода', 'tel' => '+7 (351) 217-00-99', 'href' => '+73512170099', 'ext' => '149', 'email' => 'zakaz15@prom-en.com' ],
			[ 'img' => 'romanov',   'name' => 'Максим Романов',   'role' => 'Специалист по трубам и деталям трубопровода', 'tel' => '+7 (351) 217-00-99', 'href' => '+73512170099', 'ext' => '214', 'email' => 'zakaz14@prom-en.com' ],
		],
	],
];

$smgr_img_base = get_theme_file_uri( 'assets/img/managers' );
$smgr_i        = 0;

// Иконки — feather-грамматика, единый штрих 1.8, currentColor.
$smgr_ic = [
	'phone' => '<svg class="smgr-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
	'mail'  => '<svg class="smgr-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
	'copy'  => '<svg class="smgr-copy-ic smgr-copy-ic--copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
	'check' => '<svg class="smgr-copy-ic smgr-copy-ic--check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>',
];
?>
<section class="smgr<?php echo $smgr_flush ? ' smgr--flush' : ''; ?>" id="managers" aria-labelledby="smgrTitle">
  <div class="smgr-head">
    <div class="smgr-eyebrow">
      <span class="smgr-eye-num"><?php echo esc_html( $smgr_num ); ?></span>
      <span class="smgr-eye-label">ОТДЕЛ ПРОДАЖ</span>
    </div>
    <h2 class="smgr-h2<?php echo $smgr_lines ? ' js-lines' : ''; ?>" id="smgrTitle">Кто ведёт<br>ваш заказ</h2>
    <p class="smgr-desc">Каждое направление закреплено за специалистом — он ответит по нормативу,
      материалу и сроку без передачи по цепочке. Звоните напрямую или пишите на личную почту.</p>
  </div>

  <div class="smgr-groups">
    <?php foreach ( $smgr_groups as $smgr_group ) : ?>
      <div class="smgr-group">
        <div class="smgr-group-bar">
          <span class="smgr-group-name"><?php echo esc_html( $smgr_group['label'] ); ?></span>
          <span class="smgr-group-count"><?php echo count( $smgr_group['managers'] ); ?> СПЕЦ.</span>
        </div>
        <div class="smgr-grid">
          <?php foreach ( $smgr_group['managers'] as $smgr_m ) : $smgr_i++; ?>
            <article class="smgr-card">
              <div class="smgr-photo">
                <img src="<?php echo esc_url( "$smgr_img_base/{$smgr_m['img']}.jpg" ); ?>"
                     alt="<?php echo esc_attr( $smgr_m['name'] . ' — ' . mb_strtolower( mb_substr( $smgr_m['role'], 0, 1 ) ) . mb_substr( $smgr_m['role'], 1 ) ); ?>"
                     width="267" height="400" loading="lazy" decoding="async">
                <span class="smgr-idx" aria-hidden="true">ОП-<?php echo esc_html( str_pad( (string) $smgr_i, 2, '0', STR_PAD_LEFT ) ); ?></span>
              </div>
              <div class="smgr-body">
                <h3 class="smgr-name"><?php echo esc_html( $smgr_m['name'] ); ?></h3>
                <p class="smgr-role"><?php echo esc_html( $smgr_m['role'] ); ?></p>
                <div class="smgr-lines">
                  <?php /* Запятая — pause-dial: мобильный дайлер сам донабирает добавочный после ответа IVR. */ ?>
                  <div class="smgr-line">
                    <a class="smgr-line-link" href="tel:<?php echo esc_attr( $smgr_m['href'] . ',' . $smgr_m['ext'] ); ?>" aria-label="Позвонить: <?php echo esc_attr( $smgr_m['name'] ); ?>">
                      <?php echo $smgr_ic['phone']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                      <span class="smgr-line-v"><?php echo esc_html( $smgr_m['tel'] ); ?><em class="smgr-ext">доб. <?php echo esc_html( $smgr_m['ext'] ); ?></em></span>
                    </a>
                    <button class="smgr-copy" type="button" title="Скопировать"
                            data-copy="<?php echo esc_attr( $smgr_m['tel'] . ', доб. ' . $smgr_m['ext'] ); ?>"
                            aria-label="Скопировать телефон: <?php echo esc_attr( $smgr_m['name'] ); ?>">
                      <?php echo $smgr_ic['copy'] . $smgr_ic['check']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </button>
                  </div>
                  <div class="smgr-line">
                    <a class="smgr-line-link" href="mailto:<?php echo esc_attr( $smgr_m['email'] ); ?>" aria-label="Написать: <?php echo esc_attr( $smgr_m['name'] ); ?>">
                      <?php echo $smgr_ic['mail']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                      <span class="smgr-line-v"><?php echo esc_html( $smgr_m['email'] ); ?></span>
                    </a>
                    <button class="smgr-copy" type="button" title="Скопировать"
                            data-copy="<?php echo esc_attr( $smgr_m['email'] ); ?>"
                            aria-label="Скопировать почту: <?php echo esc_attr( $smgr_m['name'] ); ?>">
                      <?php echo $smgr_ic['copy'] . $smgr_ic['check']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </button>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="smgr-foot">
      <div class="smgr-foot-q">
        <span class="smgr-foot-t">Не знаете, с кого начать?</span>
        <span class="smgr-foot-s">Общая линия завода — соединим с нужным специалистом.</span>
      </div>
      <div class="smgr-foot-lines">
        <a href="tel:+73512170099">+7 (351) 217-00-99</a>
        <a href="mailto:zakaz@prom-en.com">zakaz@prom-en.com</a>
        <span>Пн–Пт, 09:00–18:00 МСК</span>
      </div>
    </div>
  </div>
</section>
