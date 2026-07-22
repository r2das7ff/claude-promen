<?php
/**
 * Секция 05 — материалы исполнения (марки стали; строки марок товара подсвечиваются JS).
 * Разметка 1:1 из design-reference/product-otvod-90.html; динамика — PHP.
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="s s-alt" id="s05">
    <div class="s-hd">
      <div class="s-badge"><span class="s-badge-num">05</span>Материалы исполнения</div>
      <div class="s-meta">MATERIAL GRADES</div>
    </div>
    <div class="s-body">
      <div class="mat-tbl-wrap reveal">
        <div class="mat-tbl-hd"><span>Марка</span><span>Назначение</span><span>t max, °C</span><span>PN</span><span>Отрасль</span><span>Стандарт</span></div>
        <div class="mat-r" data-grade="Ст20"><span class="mr-g">Ст20</span><span>Углеродистая для t до 450°C</span><span class="mr-temp">450</span><span class="mr-pn">до 16</span><span class="mr-apps"><span class="mr-app-t">ТЭС</span><span class="mr-app-t">ЖКХ</span></span><span class="mr-std">ГОСТ 1050</span></div>
        <div class="mat-r" data-grade="09Г2С"><span class="mr-g">09Г2С</span><span>Низколегированная, свариваемая</span><span class="mr-temp">475</span><span class="mr-pn">до 160</span><span class="mr-apps"><span class="mr-app-t">АЭС</span><span class="mr-app-t">ТЭС</span><span class="mr-app-t">Нефтехим</span></span><span class="mr-std">ГОСТ 19281</span></div>
        <div class="mat-r" data-grade="15ГС"><span class="mr-g">15ГС</span><span>Для трубопроводов повышенного давления</span><span class="mr-temp">500</span><span class="mr-pn">до 100</span><span class="mr-apps"><span class="mr-app-t">ТЭС</span><span class="mr-app-t">ГРЭС</span></span><span class="mr-std">ГОСТ 19282</span></div>
        <div class="mat-r" data-grade="12Х1МФ"><span class="mr-g">12Х1МФ</span><span>Теплоустойчивая для паропроводов</span><span class="mr-temp">585</span><span class="mr-pn">до 100</span><span class="mr-apps"><span class="mr-app-t">ТЭС</span><span class="mr-app-t">Котлы</span></span><span class="mr-std">ГОСТ 20072</span></div>
        <div class="mat-r" data-grade="12Х18Н10Т"><span class="mr-g">12Х18Н10Т</span><span>Коррозионностойкая austenitic</span><span class="mr-temp">600</span><span class="mr-pn">до 63</span><span class="mr-apps"><span class="mr-app-t">АЭС</span><span class="mr-app-t">Хим</span></span><span class="mr-std">ГОСТ 5632</span></div>

        <?php if ( isset( $steels ) && array_intersect( [ '20Х3МВФ' ], $steels ) ) : ?>
        <div class="mat-r" data-grade="20Х3МВФ"><span class="mr-g">20Х3МВФ</span><span>Теплоустойчивая хромомолибденованадиевая</span><span class="mr-temp">560</span><span class="mr-pn">до 100</span><span class="mr-apps"><span class="mr-app-t">ТЭС</span><span class="mr-app-t">Котлы</span></span><span class="mr-std">ГОСТ 20072</span></div>
        <?php endif; ?>
        <?php if ( isset( $steels ) && array_intersect( [ 'ВСт3сп' ], $steels ) ) : ?>
        <div class="mat-r" data-grade="ВСт3сп"><span class="mr-g">ВСт3сп</span><span>Углеродистая общего назначения</span><span class="mr-temp">425</span><span class="mr-pn">до 16</span><span class="mr-apps"><span class="mr-app-t">ЖКХ</span><span class="mr-app-t">Общепром</span></span><span class="mr-std">ГОСТ 380</span></div>
        <?php endif; ?>
      </div>
    </div>
  </section>
