<?php
/**
 * Справочная статья под подборщиком аналогов марок стали.
 *
 * FAQ в конце свёрстан буквально (без циклов): FAQPage-разметку
 * promen_faq_schema() собирает регуляркой из исходника этого файла.
 */

defined( 'ABSPATH' ) || exit;

$sa_link = static fn( string $slug ): string => function_exists( 'promen_product_cat_link' ) ? (string) promen_product_cat_link( $slug ) : '';
$sa_dn   = function_exists( 'promen_calc_url' ) ? promen_calc_url( 'dn-dyuym' ) : '';
?>
<div class="sa-body" id="spravka">

    <section class="sa-part" id="chtenie">
      <h2 class="sa-h2">Как читать обозначение марки</h2>
      <p>У каждой системы своя логика. Российская и китайская записи перечисляют легирующие элементы с их
        содержанием, европейская — либо состав, либо назначение и прочность, американская ASTM называет не
        марку, а стандарт на изделие. Разобравшись в логике, аналог часто видно без таблицы.</p>

      <div class="sa-dec-grid">
        <figure class="sa-dec">
          <figcaption><span class="sa-lbl">ГОСТ</span>Легирование в процентах</figcaption>
          <div class="sa-dec-code" aria-label="12Х18Н10Т"><span>12</span><span>Х18</span><span>Н10</span><span>Т</span></div>
          <ol class="sa-dec-leg">
            <li><b>12</b>углерод в сотых долях процента — до 0,12 %</li>
            <li><b>Х18</b>хром около 18 %</li>
            <li><b>Н10</b>никель около 10 %</li>
            <li><b>Т</b>титан; без цифры — до 1–1,5 %</li>
          </ol>
        </figure>

        <figure class="sa-dec">
          <figcaption><span class="sa-lbl">EN · по составу</span>Высоколегированная сталь</figcaption>
          <div class="sa-dec-code" aria-label="X6CrNiTi18-10"><span>X</span><span>6</span><span>CrNiTi</span><span>18-10</span></div>
          <ol class="sa-dec-leg">
            <li><b>X</b>хотя бы один элемент — от 5 %</li>
            <li><b>6</b>углерод в сотых долях — около 0,06 %</li>
            <li><b>CrNiTi</b>легирующие элементы по убыванию</li>
            <li><b>18-10</b>хром 18 %, никель 10 %</li>
          </ol>
        </figure>

        <figure class="sa-dec">
          <figcaption><span class="sa-lbl">EN · по назначению</span>Сталь для давления</figcaption>
          <div class="sa-dec-code" aria-label="P265GH"><span>P</span><span>265</span><span>GH</span></div>
          <ol class="sa-dec-leg">
            <li><b>P</b>для оборудования под давлением</li>
            <li><b>265</b>минимальный предел текучести, МПа</li>
            <li><b>GH</b>для работы при повышенных температурах</li>
          </ol>
        </figure>

        <figure class="sa-dec">
          <figcaption><span class="sa-lbl">AISI · SAE</span>Конструкционная сталь</figcaption>
          <div class="sa-dec-code" aria-label="4130"><span>41</span><span>30</span></div>
          <ol class="sa-dec-leg">
            <li><b>41</b>система легирования: хром и молибден</li>
            <li><b>30</b>углерод в сотых долях — 0,30 %</li>
          </ol>
        </figure>

        <figure class="sa-dec">
          <figcaption><span class="sa-lbl">ASTM</span>Стандарт на изделие</figcaption>
          <div class="sa-dec-code" aria-label="A234 WPB"><span>A</span><span>234</span><span>WPB</span></div>
          <ol class="sa-dec-leg">
            <li><b>A</b>стандарты на чёрные металлы</li>
            <li><b>234</b>номер стандарта — фитинги из углеродистой и легированной стали</li>
            <li><b>WPB</b>марка внутри этого стандарта</li>
          </ol>
        </figure>

        <figure class="sa-dec">
          <figcaption><span class="sa-lbl">GB</span>Китайская котельная сталь</figcaption>
          <div class="sa-dec-code" aria-label="12Cr1MoVG"><span>12</span><span>Cr1MoV</span><span>G</span></div>
          <ol class="sa-dec-leg">
            <li><b>12</b>углерод в сотых долях — 0,12 %</li>
            <li><b>Cr1MoV</b>хром около 1 %, молибден, ванадий</li>
            <li><b>G</b>для котлов; R — для сосудов, DG — для низких температур</li>
          </ol>
        </figure>
      </div>

      <h3 class="sa-h3">Буквы легирующих элементов в ГОСТ</h3>
      <ul class="sa-letters">
        <li><b>Х</b>Cr<span>хром</span></li>
        <li><b>Н</b>Ni<span>никель</span></li>
        <li><b>М</b>Mo<span>молибден</span></li>
        <li><b>Г</b>Mn<span>марганец</span></li>
        <li><b>С</b>Si<span>кремний</span></li>
        <li><b>Т</b>Ti<span>титан</span></li>
        <li><b>Ф</b>V<span>ванадий</span></li>
        <li><b>В</b>W<span>вольфрам</span></li>
        <li><b>Б</b>Nb<span>ниобий</span></li>
        <li><b>Ю</b>Al<span>алюминий</span></li>
        <li><b>Д</b>Cu<span>медь</span></li>
        <li><b>Р</b>B<span>бор</span></li>
        <li><b>К</b>Co<span>кобальт</span></li>
        <li><b>Ц</b>Zr<span>цирконий</span></li>
        <li><b>А</b>N<span>азот — в середине марки</span></li>
      </ul>
      <p>Буква «А» в конце марки (30ХМА) означает высококачественную сталь с пониженным содержанием серы и
        фосфора. «сп», «пс», «кп» у углеродистых сталей — степень раскисления: спокойная, полуспокойная, кипящая.
        Обозначения ЭИ и ЭП (ЭИ415, ЭП182) — заводские шифры марок, созданных на заводе «Электросталь»;
        в документации они встречаются наравне с марочными.</p>
      <p>В американских нержавеющих сериях 3xx — аустенитные стали, 4xx — ферритные и мартенситные. Буква
        L означает низкий углерод (до 0,03 %), H — повышенный углерод для высоких температур, Ti — стабилизацию
        титаном. Японская JIS повторяет номера AISI с приставкой SUS: SUS321 — это 321. Европейский номер
        1.4541 — другая запись той же марки X6CrNiTi18-10: 1 — сталь, 45 — группа, 41 — порядковый номер.</p>
    </section>

    <section class="sa-part" id="izdelie">
      <h2 class="sa-h2">Марка и спецификация на изделие — не одно и то же</h2>
      <p>По ГОСТ марку стали и изделие записывают раздельно: «отвод ГОСТ 17375-2001 из стали 20». В ASTM
        материал и изделие объединены в одной спецификации: A106 — трубы, A234 — фитинги, A105 — поковки и
        фланцы. Поэтому одна и та же сталь 20 в импортной документации встречается под тремя разными
        обозначениями — в зависимости от того, что из неё сделано.</p>

      <div class="sa-tbl-wrap sa-tbl-wrap--art" role="region" aria-label="Спецификации ASTM по изделиям и российские аналоги" tabindex="0">
        <table class="sa-tbl sa-tbl--forms">
          <thead>
            <tr>
              <th scope="col">Изделие</th>
              <th scope="col">Углеродистая<small>сталь 20</small></th>
              <th scope="col">Хладостойкая<small>09Г2С, 10Г2</small></th>
              <th scope="col">Хромомолибденовая<small>15ХМ, 12ХМ</small></th>
              <th scope="col">Нержавеющая с Ti<small>08Х18Н10Т, 12Х18Н10Т</small></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">Труба бесшовная<small>ГОСТ 8731-74, 8732-78</small></th>
              <td>A106 Gr.B</td><td>A333 Gr.6</td><td>A335 P12, P11</td><td>A312 TP321</td>
            </tr>
            <tr>
              <th scope="row"><?php if ( $sa_link( 'otvody' ) ) : ?><a href="<?php echo esc_url( $sa_link( 'otvody' ) ); ?>">Отвод</a><?php else : ?>Отвод<?php endif; ?>, <?php if ( $sa_link( 'troyniki' ) ) : ?><a href="<?php echo esc_url( $sa_link( 'troyniki' ) ); ?>">тройник</a><?php else : ?>тройник<?php endif; ?>, <?php if ( $sa_link( 'perekhody' ) ) : ?><a href="<?php echo esc_url( $sa_link( 'perekhody' ) ); ?>">переход</a><?php else : ?>переход<?php endif; ?><small>ГОСТ 17375, 17376, 17378-2001</small></th>
              <td>A234 WPB</td><td>A420 WPL6</td><td>A234 WP12, WP11</td><td>A403 WP321</td>
            </tr>
            <tr>
              <th scope="row"><?php if ( $sa_link( 'flancy' ) ) : ?><a href="<?php echo esc_url( $sa_link( 'flancy' ) ); ?>">Фланец</a><?php else : ?>Фланец<?php endif; ?>, поковка<small>ГОСТ 33259-2015</small></th>
              <td>A105</td><td>A350 LF2 Cl.1</td><td>A182 F12, F11</td><td>A182 F321</td>
            </tr>
            <tr>
              <th scope="row">Лист<?php if ( $sa_link( 'dnishcha' ) ) : ?> для <a href="<?php echo esc_url( $sa_link( 'dnishcha' ) ); ?>">днищ</a><?php endif; ?><small>ГОСТ 6533-78</small></th>
              <td>A516 Gr.60</td><td>A516 Gr.70</td><td>A387 Gr.12</td><td>A240 321</td>
            </tr>
            <tr>
              <th scope="row"><?php if ( $sa_link( 'krepezh' ) ) : ?><a href="<?php echo esc_url( $sa_link( 'krepezh' ) ); ?>">Шпильки</a><?php else : ?>Шпильки<?php endif; ?><small>ГОСТ 9066-75</small></th>
              <td>A193 B7<small>сталь 35ХМ, 40Х</small></td><td>A320 L7</td><td>A193 B16<small>сталь 25Х1МФ</small></td><td>A193 B8T</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p>Отсюда практическое правило: ищите аналог не только марки, но и изделия. Если в спецификации стоит
        A234 WPB, заказывают отвод, тройник или переход по ГОСТ 17376, 17375, 17378 из стали 20 — а размеры
        сверяют отдельно: сортаменты ASME B16.9 и ГОСТ по наружным диаметрам не совпадают<?php if ( $sa_dn ) : ?>
        (соответствие DN, дюймов и диаметров — в <a href="<?php echo esc_url( $sa_dn ); ?>">таблице DN и дюймов</a>)<?php endif; ?>.</p>
    </section>

    <section class="sa-part" id="stepen">
      <h2 class="sa-h2">Прямой, близкий, условный: как читать степень соответствия</h2>
      <p>Слово «аналог» в справочниках означает разное — от почти точной копии до «ближайшего по назначению».
        Подборщик показывает степень соответствия явно, по стандартам на марку: сравниваются ключевые
        легирующие элементы, углерод и уровень прочности.</p>
      <ul class="sa-levels">
        <li>
          <?php echo promen_sa_q( 3 ); // phpcs:ignore ?>
          <p><b>Та же система легирования, диапазоны ключевых элементов совпадают или перекрываются.</b>
            Пример: 08Х18Н10Т и AISI 321, 15Х5М и A335 P5, 40Х и 41Cr4. Замену обычно подтверждают сравнением
            сертификатов.</p>
        </li>
        <li>
          <?php echo promen_sa_q( 2 ); // phpcs:ignore ?>
          <p><b>Тот же класс и назначение, но заметно отличается один-два элемента или прочность.</b>
            Пример: сталь 20 и P265GH — у европейской марки вдвое больше марганца; 12Х18Н10Т и AISI 321 —
            разный допуск по углероду. Сверяют с требованиями проекта.</p>
        </li>
        <li>
          <?php echo promen_sa_q( 1 ); // phpcs:ignore ?>
          <p><b>Прямого аналога нет, показан ближайший по назначению.</b> Пример: 12Х1МФ и A335 P12 — в
            американской стали нет ванадия. Замена возможна только по расчёту и с согласованием.</p>
        </li>
      </ul>
      <p class="sa-caution">Степень соответствия — оценка по составу, а не разрешение на замену. Решение о
        замене принимает автор проекта с учётом условий работы.</p>
    </section>

    <section class="sa-part" id="sravnenie">
      <h2 class="sa-h2">Что сравнить, прежде чем заменять марку</h2>
      <ol class="sa-checks">
        <li><b>Химсостав.</b> Углерод и легирующие элементы — по стандартам обеих марок. Углерод определяет
          свариваемость, хром, никель и молибден — коррозионную стойкость и жаропрочность.</li>
        <li><b>Прочность при рабочей температуре.</b> Трубопроводы рассчитывают по допускаемым напряжениям
          при расчётной температуре, а не по пределу прочности при +20 °C. У двух марок с одинаковой прочностью
          на холоде допускаемые напряжения при +400 °C могут отличаться.</li>
        <li><b>Ударная вязкость на холоде.</b> Для хладостойких сталей важна гарантированная ударная вязкость
          при минимальной температуре эксплуатации — у 09Г2С её нормируют до −70 °C.</li>
        <li><b>Длительная прочность.</b> Для теплоустойчивых сталей при температурах выше +450 °C сравнивают
          сопротивление ползучести — химсостав здесь не показатель.</li>
        <li><b>Стойкость к межкристаллитной коррозии.</b> Для аустенитных сталей — испытание по ГОСТ 6032;
          титан в 08Х18Н10Т и 321 нужен именно для этого.</li>
        <li><b>Состояние поставки и термообработка.</b> Нормализация, закалка с отпуском или аустенитизация
          меняют свойства одной и той же марки.</li>
        <li><b>Сварка.</b> Сварочные материалы и режимы подбирают под фактическую марку детали и трубы.</li>
        <li><b>Стандарт на изделие.</b> Он задаёт свои требования к контролю и испытаниям, которых нет в
          стандарте на марку.</li>
      </ol>
    </section>

    <section class="sa-part" id="nadzor">
      <h2 class="sa-h2">Замена марки на поднадзорном объекте</h2>
      <p>Для трубопроводов пара и горячей воды, технологических трубопроводов под давлением и оборудования
        атомных станций марку стали задаёт проект. Замена марки — это изменение проектного решения: её
        согласуют с проектной организацией и отражают в документации на изделие.</p>
      <p>Детали для оборудования под давлением выпускают с подтверждённым соответствием ТР ТС 032/2013 —
        у завода «Промышленная Энергетика» это сертификат соответствия RU С-RU.АБ53.В.08323/23. Для объектов
        атомной энергетики действуют НП-089-15 и НП-045-18: применяют материалы, предусмотренные этими
        правилами и связанными с ними стандартами, а другой материал — только после обоснования
        в установленном порядке.</p>
      <p>Поэтому зарубежную марку в российском проекте обычно не ищут «один в один», а заменяют российской
        по ГОСТ — с проверкой по пунктам из предыдущего раздела.</p>
    </section>

    <section class="sa-part" id="primer">
      <h2 class="sa-h2">Пример: перевод импортной спецификации</h2>
      <p>Условная спецификация трубопровода по американским стандартам и то, что по ней заказывают у
        российского завода. Пример показывает ход рассуждения, а не готовое решение для конкретного объекта.</p>
      <div class="sa-tbl-wrap sa-tbl-wrap--art" role="region" aria-label="Пример перевода спецификации" tabindex="0">
        <table class="sa-tbl sa-tbl--spec">
          <thead>
            <tr><th scope="col">В спецификации</th><th scope="col">Что заказать по ГОСТ</th><th scope="col">На что обратить внимание</th></tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">Pipe, seamless, A106 Gr.B</th>
              <td>Труба ГОСТ 8732-78 / 8731-74 из стали 20</td>
              <td>Сортаменты по диаметру и стенке не совпадают — толщину стенки подтверждают расчётом.</td>
            </tr>
            <tr>
              <th scope="row">Elbow 90° LR, A234 WPB</th>
              <td>Отвод 90° ГОСТ 17375-2001 из стали 20</td>
              <td>Радиус LR (1,5 DN) соответствует крутоизогнутому отводу; наружный диаметр 4″ — 114,3 мм, по ГОСТ ближайшие 108 и 114 мм.</td>
            </tr>
            <tr>
              <th scope="row">Elbow, A420 WPL6</th>
              <td>Отвод из стали 09Г2С</td>
              <td>Минимальная температура эксплуатации и требование к ударной вязкости переносятся в заказ.</td>
            </tr>
            <tr>
              <th scope="row">Flange WN, A105</th>
              <td>Фланец воротниковый ГОСТ 33259-2015, тип 11, из стали 20</td>
              <td>Классы давления ASME и PN не пересчитываются один в один — фланец подбирают по давлению и температуре.</td>
            </tr>
            <tr>
              <th scope="row">Flange, A182 F321</th>
              <td>Фланец из стали 08Х18Н10Т или 12Х18Н10Т</td>
              <td>Для сварного узла ближе 08Х18Н10Т — меньше углерода, выше стойкость к межкристаллитной коррозии.</td>
            </tr>
            <tr>
              <th scope="row">Stud bolt A193 B7, nut A194 2H</th>
              <td>Шпилька ГОСТ 9066-75 из 35ХМ или 40Х, гайка ГОСТ 9064-75</td>
              <td>Марку гайки подбирают в паре со шпилькой по ГОСТ 20700-75.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="sa-inline-cta">
        <p><b>Своя спецификация на десятки позиций?</b> Пришлите файл — инженер переведёт её на российские марки
          и нормативы, отметит позиции, где нужен расчёт, и ответит в течение рабочего дня.</p>
        <button type="button" class="clc-btn" data-act="spec">Отправить спецификацию</button>
      </div>
    </section>

    <section class="sa-part" id="voprosy">
      <h2 class="sa-h2">Вопросы и ответы</h2>
      <div class="faq-wrap sa-faq">
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">01</span><span class="fq-t">Какой российский аналог у AISI 321?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Прямой аналог — 08Х18Н10Т: углерода до 0,08 %, как у 321, и та же стабилизация титаном. 12Х18Н10Т — близкий аналог: углерода в ней допускается до 0,12 %, поэтому по составу она ближе к 321H. Для сварных узлов, где важна стойкость к межкристаллитной коррозии, выбирают 08Х18Н10Т.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">02</span><span class="fq-t">Чем заменить AISI 304 и 316L?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">AISI 304 соответствует 08Х18Н10, 304L — 03Х18Н11, 316L — 03Х17Н14М3. Если деталь будет свариваться и работать в среде, опасной межкристаллитной коррозией, вместо сталей без титана берут стабилизированные: 08Х18Н10Т вместо 304, 10Х17Н13М2Т вместо 316.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">03</span><span class="fq-t">Сталь 20 и A106 Gr.B — одно и то же?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Нет, это близкие аналоги. A106 Gr.B — спецификация на трубы, а не марка: она допускает больше углерода (до 0,30 %) и марганца (до 1,06 %), чем сталь 20 по ГОСТ 1050, но по прочности трубы одного уровня. Для фитингов из того же металла в ASTM есть A234 WPB, для фланцев — A105.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">04</span><span class="fq-t">Какой зарубежный аналог у 09Г2С?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">В Европе — P355NH и её хладостойкие варианты P355NL1 и P355NL2, в США — лист A516 Gr.70, а для изделий на холоде — трубы A333 Gr.6, отводы A420 WPL6 и фланцы A350 LF2. В Китае — Q355 и котельная Q345R. Все они близкие, а не прямые аналоги: у 09Г2С больше кремния и нормированная ударная вязкость до −70 °C.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">05</span><span class="fq-t">Есть ли аналог у 12Х1МФ?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Ближе всего европейская 14MoV6-3 — та же система легирования хромом, молибденом и ванадием, но в другой пропорции. Китайская 12Cr1MoVG по составу совпадает с 12Х1МФ. В американских стандартах прямого аналога нет: трубы P11 и P12 — хромомолибденовые без ванадия, и замена на них возможна только по расчёту.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">06</span><span class="fq-t">Можно ли заменить 12Х18Н10Т на 08Х18Н10Т?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Технически это замена в лучшую сторону по стойкости к межкристаллитной коррозии: углерода в 08Х18Н10Т меньше при том же легировании. Но для поднадзорных объектов марку задаёт проект, поэтому и такую замену согласуют с проектной организацией и отражают в документации.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">07</span><span class="fq-t">Как читать китайские марки Q355, 12Cr1MoVG, 06Cr18Ni11Ti?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Q355 — конструкционная сталь с пределом текучести 355 МПа, буква после числа — класс по ударной вязкости. В легированных марках первые цифры — углерод, затем элементы с содержанием: 12Cr1MoVG — около 0,12 % углерода и 1 % хрома, G означает сталь для котлов, R — для сосудов. 06Cr18Ni11Ti — новое обозначение нержавеющей стали типа 321; в старых документах она записана как 0Cr18Ni10Ti.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">08</span><span class="fq-t">Нужно ли согласовывать замену марки стали?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Для оборудования и трубопроводов под давлением — да: марку задаёт проект, и её замена — изменение проектного решения, которое согласуют с проектной организацией. Для деталей общего назначения решение принимает заказчик, но сравнить химсостав, прочность при рабочей температуре и ударную вязкость нужно в любом случае.</div></div>
        </div>
        <div class="fq">
          <button type="button" class="fq-q" aria-expanded="false"><span class="fq-num">09</span><span class="fq-t">Изготовит ли завод детали, если в проекте указана зарубежная марка?</span><span class="fq-arr" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span></button>
          <div class="fq-a"><div class="fq-a-in">Пришлите спецификацию или чертёж: инженер завода подберёт российские марки и нормативы по ГОСТ, ОСТ и СТО, отметит позиции, где замену нужно подтвердить расчётом, и ответит в течение рабочего дня. Детали по чертежу заказчика, в том числе нестандартные, завод изготавливает.</div></div>
        </div>
      </div>
      <?php
      // Разметка FAQ собирается из этой же вёрстки, см. promen_faq_schema().
      if ( function_exists( 'promen_faq_schema' ) ) {
        promen_faq_schema( get_theme_file_path( 'parts/steel-analogs-article.php' ) );
      }
      ?>
    </section>

</div>
