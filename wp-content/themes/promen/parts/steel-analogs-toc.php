<?php
/**
 * Оглавление справочника аналогов: sticky-колонка от таблицы соответствия
 * до конца статьи. До 1280px — свёрнутый блок «Содержание» над таблицей
 * (кнопкой его делает steel-analogs.js, без JS список просто раскрыт).
 */

defined( 'ABSPATH' ) || exit;

$sa_toc = [
	'podbor'    => 'Подбор аналога',
	'tablica'   => 'Таблица соответствия',
	'chtenie'   => 'Как читать обозначение марки',
	'izdelie'   => 'Марка и спецификация на изделие',
	'stepen'    => 'Прямой, близкий, условный',
	'sravnenie' => 'Что сравнить перед заменой',
	'nadzor'    => 'Замена на поднадзорном объекте',
	'primer'    => 'Пример: импортная спецификация',
	'voprosy'   => 'Вопросы и ответы',
];
?>
<aside class="sa-toc" aria-label="Содержание">
  <nav class="sa-toc-box" data-toc>
    <span class="sa-lbl sa-toc-lbl" data-toc-tg>Содержание</span>
    <ol class="sa-toc-list" id="saTocList">
      <?php foreach ( $sa_toc as $id => $t ) : ?>
        <li><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $t ); ?></a></li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <div class="sa-toc-cta">
    <p class="sa-toc-cta-t">Импортная спецификация?</p>
    <p class="sa-toc-cta-p">Пришлите её целиком — инженер подберёт марки и нормативы по ГОСТ и ответит в течение рабочего дня.</p>
    <button type="button" class="sa-toc-cta-btn" data-act="spec">Отправить спецификацию</button>
  </div>
</aside>
