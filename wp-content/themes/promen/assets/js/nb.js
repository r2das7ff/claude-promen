/* ── ПЭ / NB — «Нормативная база»: реестр DOCS (ГОСТ/ОСТ/СТО/ТУ),
   фильтры, пагинация, боковая панель документа, честный тост
   «PDF пока не опубликован». Порт инлайна html/normativnaya-baza.html
   (2026-07-23); часы — chrome.js; прогресс/наезд — projects.js.
   Конфиг: window.promenNB { catalogUrl, groups{} }. ── */
function NB_CATALOG(cat) {
  var cfg = window.promenNB || {};
  var slug = (cfg.groups || {})[cat];
  if (!slug) return cfg.catalogUrl || '/catalog/';
  return (cfg.catalogUrl || '/catalog/') + ((cfg.catalogUrl || '').indexOf('?') > -1 ? '&' : '?') + 'group=' + slug;
}
/* ── DATA ── sourced from the project's own catalog (katalog.html P[]) and
   product-page normative registry (product-otvod-90.html) — no invented numbers */
const DOCS=[
  {cat:'sdt',sub:'otvody',type:'gost',code:'ГОСТ 17375-2001',title:'Детали трубопроводов бесшовные приварные из углеродистой и низколегированной стали. Отводы крутоизогнутые',desc:'Основной стандарт на отводы 45°, 90°, 180°. R = 1,5DN. DN 15–1400, PN 0,6–16,0 МПа.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 34-42-621-83',title:'Отводы гнутые из труб. Технические условия',desc:'Гнутые из трубных заготовок отводы для DN и радиусов, не охваченных ГОСТ 17375.'},
  {cat:'sdt',sub:'otvody',type:'tu',code:'ТУ 1468-029-00696843-2009',title:'Отводы крутоизогнутые. Технические условия завода',desc:'Заводские ТУ на отводы нестандартной геометрии и материалов сверх сортамента ГОСТ.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 34-42-622-83',title:'Отводы штампованные. Технические условия',desc:'Горячая штамповка отводов с контролем геометрических параметров и толщины стенки.'},
  {cat:'sdt',sub:'troyniki',type:'gost',code:'ГОСТ 17376-2001',title:'Детали трубопроводов бесшовные приварные. Тройники',desc:'Тройники равнопроходные и переходные, кованые или штампосварные. DN 15–1000.'},
  {cat:'sdt',sub:'troyniki',type:'ost',code:'ОСТ 34-42-632-83',title:'Тройники сварные. Технические условия',desc:'Сварные тройники для трубопроводов ТЭС и ЖКХ вне типоразмерного ряда ГОСТ 17376.'},
  {cat:'sdt',sub:'perehody',type:'gost',code:'ГОСТ 17378-2001',title:'Детали трубопроводов бесшовные приварные. Переходы',desc:'Переходы концентрические и эксцентрические. DN 15–1400, PN 0,6–16,0 МПа.'},
  {cat:'sdt',sub:'perehody',type:'ost',code:'ОСТ 34-42-641-83',title:'Переходы штампосварные. Технические условия',desc:'Переходы для крупных диаметров и нестандартных сочетаний, изготавливаемые сваркой.'},
  {cat:'sdt',sub:'dnishcha',type:'gost',code:'ГОСТ 17379-2001',title:'Детали трубопроводов бесшовные приварные. Днища эллиптические отбортованные',desc:'Днища для сосудов давления и коллекторов. Контроль толщины стенки ультразвуком.'},
  {cat:'sdt',sub:'dnishcha',type:'gost',code:'ГОСТ 6533-78',title:'Днища эллиптические стальные штампованные. Основные размеры',desc:'Базовый сортамент эллиптических днищ, холодная и горячая штамповка.'},
  {cat:'sdt',sub:'zaglushki',type:'gost',code:'ГОСТ 17380-2001',title:'Детали трубопроводов бесшовные приварные. Заглушки эллиптические',desc:'Торцевые заглушки на конец трубопровода. DN 15–1400.'},
  {cat:'sdt',sub:'flanci',type:'gost',code:'ГОСТ 12821-80',title:'Фланцы стальные приварные встык. Конструкция и размеры',desc:'Воротниковые фланцы тип 11, основной типоразмерный ряд DN 10–1600, PN до 20,0 МПа.'},
  {cat:'sdt',sub:'flanci',type:'gost',code:'ГОСТ 28759.3-90',title:'Фланцы трубопроводной арматуры и соединительных частей. Уплотнительные поверхности',desc:'Требования к форме и шероховатости уплотнительных поверхностей фланцев.'},
  {cat:'sdt',sub:'flanci',type:'gost',code:'ГОСТ 12820-80',title:'Фланцы стальные плоские приварные. Конструкция и размеры',desc:'Тип 01, для давлений PN ≤ 2,5 МПа. DN 10–1600.'},
  {cat:'sdt',sub:'flanci',type:'gost',code:'ГОСТ 12822-80',title:'Фланцы стальные свободные на приварном кольце. Конструкция и размеры',desc:'Для коррозионностойких сред: кольцо из нержавеющей стали, фланец — углеродистый.'},
  {cat:'sdt',sub:'flanci',type:'gost',code:'ГОСТ 12836-80',title:'Фланцы стальные глухие. Конструкция и размеры',desc:'Глухие фланцевые заглушки для отключения ответвлений трубопровода.'},
  {cat:'op',type:'ost',code:'ОСТ 36-94-83',title:'Опоры трубопроводов неподвижные хомутовые. Конструкция',desc:'Фиксация трубопровода хомутовым креплением. DN 25–1400.'},
  {cat:'op',type:'ost',code:'ОСТ 36-95-83',title:'Опоры трубопроводов неподвижные приварные. Конструкция',desc:'Приварные неподвижные опоры для протяжённых участков трубопровода.'},
  {cat:'op',type:'ost',code:'ОСТ 36-90-83',title:'Опоры трубопроводов скользящие. Конструкция',desc:'Опоры с антифрикционным покрытием для термокомпенсации трубопровода.'},
  {cat:'op',type:'ost',code:'ОСТ 36-17-83',title:'Подвески пружинные для трубопроводов. Технические требования',desc:'Пружинные подвески с регулируемой нагрузкой, протокол нагрузочных испытаний.'},
  {cat:'zra',type:'gost',code:'ГОСТ 9698-86',title:'Задвижки клиновые стальные литые. Основные параметры',desc:'Задвижки с невыдвижным шпинделем, DN 50–1200, PN 1,6–6,3 МПа.'},
  {cat:'zra',type:'gost',code:'ГОСТ 33257-2015',title:'Арматура трубопроводная. Клапаны запорные и обратные. Общие технические условия',desc:'Требования к запорным и обратным клапанам стальным и нержавеющим.'},
  {cat:'zra',type:'gost',code:'ГОСТ 33522-2015',title:'Арматура трубопроводная. Краны шаровые. Общие технические условия',desc:'Краны шаровые полнопроходные, в т.ч. для объектов атомной энергетики.'},
  {cat:'zra',type:'np',code:'НП-068-05',title:'Правила устройства и безопасной эксплуатации оборудования и трубопроводов атомных энергетических установок',desc:'Обязательный документ для арматуры и трубопроводов, поставляемых на объекты АЭС.'},
  {cat:'tr',type:'gost',code:'ГОСТ 8732-78',title:'Трубы стальные бесшовные горячедеформированные. Сортамент',desc:'Базовый сортамент труб-заготовок DN 20–1420 для последующей штамповки деталей.'},
  {cat:'tr',type:'gost',code:'ГОСТ 8733-74',title:'Трубы стальные бесшовные холоднодеформированные и горячедеформированные. Технические требования',desc:'Технические требования к трубам общего назначения, дополняет ГОСТ 8732.'},
  {cat:'tr',type:'tu',code:'ТУ 14-3Р-55-2001',title:'Трубы стальные бесшовные горячедеформированные для паровых котлов и трубопроводов',desc:'Трубы для сред ТЭС и АЭС с повышенными требованиями к контролю металла.'},
  {cat:'tr',type:'gost',code:'ГОСТ 8734-75',title:'Трубы стальные бесшовные холоднодеформированные. Сортамент',desc:'Точные размеры, малые диаметры DN 5–250, повышенная точность стенки.'},
  {cat:'iz',type:'gost',code:'ГОСТ 32313-2012',title:'Конструкции теплоизоляционные трубопроводов и оборудования. Общие технические условия',desc:'Требования к тепловой изоляции из минеральной ваты и пенополиуретана.'},
  {cat:'iz',type:'gost',code:'ГОСТ 17538-72',title:'Изделия теплоизоляционные из минеральной ваты. Общие технические требования',desc:'Маты и скорлупы для изоляции трубопроводов, входной контроль материала.'},
  {cat:'iz',type:'gost',code:'ГОСТ 9.402-2004',title:'Единая система защиты от коррозии и старения. Покрытия лакокрасочные. Подготовка металлических поверхностей',desc:'Требования к подготовке поверхности перед антикоррозионным покрытием.'},
  {cat:'td',type:'gost',code:'ГОСТ 9066-75',title:'Шпильки для фланцевых соединений с гайками. Конструкция и размеры',desc:'Крепёж для фланцевых соединений ТЭС / АЭС, резьба М16–М64.'},
  {cat:'td',type:'gost',code:'ГОСТ 10494-80',title:'Шпильки для фланцевых соединений трубопроводной арматуры на Ру до 20 МПа',desc:'Повышенная прочность, контроль резьбы и твёрдости по Бринеллю.'},
  {cat:'td',type:'gost',code:'ГОСТ 9064-75',title:'Гайки шестигранные для фланцевых соединений. Конструкция и размеры',desc:'Базовый сортамент гаек для фланцевых соединений трубопроводов.'},
  {cat:'td',type:'gost',code:'ГОСТ 10495-80',title:'Гайки для фланцевых соединений трубопроводной арматуры',desc:'Повышенная прочность, парная поставка со шпильками по ГОСТ 10494.'},
  {cat:'mat',type:'gost',code:'ГОСТ 1050',title:'Сталь Ст20 — углеродистая качественная конструкционная',desc:'Для трубопроводов и деталей при рабочей температуре среды до 450°C.'},
  {cat:'mat',type:'gost',code:'ГОСТ 19281',title:'Сталь 09Г2С — прокат из низколегированной стали',desc:'Свариваемая сталь для деталей ТЭС, АЭС и нефтехимии, до 475°C, PN до 16,0 МПа.'},
  {cat:'mat',type:'gost',code:'ГОСТ 19282',title:'Сталь 15ГС — прокат для трубопроводов повышенного давления',desc:'Для трубопроводов и коллекторов ТЭС и ГРЭС при температуре до 500°C.'},
  {cat:'mat',type:'gost',code:'ГОСТ 20072',title:'Сталь 12Х1МФ — теплоустойчивая легированная сталь',desc:'Для паропроводов и котельных деталей при температуре среды до 585°C.'},
  {cat:'mat',type:'gost',code:'ГОСТ 5632',title:'Сталь 12Х18Н10Т — высоколегированная коррозионностойкая сталь',desc:'Аустенитная сталь для АЭС и химически агрессивных сред, до 600°C.'},
  {cat:'qc',type:'tr',code:'ТР ТС 032/2013',title:'О безопасности оборудования, работающего под избыточным давлением',desc:'Технический регламент Таможенного союза. Основание декларации соответствия завода.'},
  {cat:'qc',type:'decl',code:'RU С-RU.АБ53.В.08323/23',title:'Декларация о соответствии продукции завода требованиям ТР ТС 032/2013',desc:'Серия RU 0418908. Действует на продукцию, изготовленную заводом «Промышленная Энергетика».'},
  {cat:'qc',type:'pb',code:'ПБ 03-585-03',title:'Правила устройства и безопасной эксплуатации технологических трубопроводов',desc:'Контроль сварных швов и приёмка деталей трубопроводов, включая объекты АЭС.'},
  {cat:'qc',type:'pnae',code:'ПНАЭ Г-7-010-89',title:'Оборудование и трубопроводы атомных энергетических установок. Сварные соединения и наплавка. Правила контроля',desc:'Рентгеноскопия и ультразвуковой контроль сварных швов для объектов АЭС.'},
  {cat:'qc',type:'gost',code:'ГОСТ 16037-80',title:'Соединения сварные стальных трубопроводов. Основные типы, конструктивные элементы и размеры',desc:'Типовые сварные соединения, применяемые при монтаже и изготовлении узлов.'},
  /* ── Добавлено: сверка с действующим реестром продукции завода ── */
  {cat:'sdt',sub:'otvody',type:'gost',code:'ГОСТ 30753-2001',title:'Детали трубопроводов бесшовные приварные из углеродистой и низколегированной стали. Отводы крутоизогнутые с торцами под приварку',desc:'Дополняет ГОСТ 17375 для отводов с иной геометрией торца под сварку.'},
  {cat:'sdt',sub:'otvody',type:'sto',code:'СТО 79814898 111-2009',title:'Отводы крутоизогнутые энергетические (соответствует ОСТ 34-10-418-90)',desc:'Действующий отраслевой стандарт энергомашиностроения, заменивший ОСТ 34-10-418-90.',supersedes:'ОСТ 34-10-418-90'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 34.10.699-97',title:'Отводы крутоизогнутые. Технические требования',desc:'Отраслевой стандарт энергетического машиностроения на крутоизогнутые отводы.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 34-42-661-84',title:'Отводы гнутые из труб для трубопроводов энергетических установок',desc:'Гнутые отводы для DN и радиусов вне сортамента ГОСТ 17375.'},
  {cat:'sdt',sub:'otvody',type:'sto',code:'СТО 95 115-2013',title:'Отводы гнутые. Технические условия (аттестация ЦКТИ)',desc:'Отводы гнутые, изготовленные по технологии, аттестованной СТО ЦКТИ.'},
  {cat:'sdt',sub:'otvody',type:'sto',code:'СТО СРО-П 60542948-00011-2013',title:'Отводы гнутые. Стандарт саморегулируемой организации',desc:'Требования СРО в области строительства к гнутым отводам трубопроводов.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 24.125.03-89',title:'Отводы гнутые. Номенклатура энергомашиностроения',desc:'Типоразмерный ряд гнутых отводов для котельного и турбинного оборудования.'},
  {cat:'sdt',sub:'otvody',type:'gost',code:'ГОСТ 22793-83',title:'Детали трубопроводов бесшовные приварные из коррозионно-стойких сталей. Отводы крутоизогнутые',desc:'Отводы из нержавеющих марок стали для химически агрессивных сред.'},
  {cat:'sdt',sub:'obshchie',type:'gost',code:'ГОСТ 24950-81',title:'Детали трубопроводов бесшовные приварные из коррозионно-стойкой стали. Общие технические условия',desc:'Общие требования к деталям из нержавеющей стали помимо ГОСТ 22793.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 36-42-81',title:'Отводы энергетического машиностроения. Технические требования',desc:'Отраслевой стандарт Минэнергомаш на отводы трубопроводов ТЭС.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 36-20-77',title:'Отводы штампосварные (ОКШС). Конструкция и размеры',desc:'Отводы, изготавливаемые штамповкой половин с последующей продольной сваркой.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 36-21-77',title:'Отводы сварные секторные. Технические условия',desc:'Крупногабаритные секторные отводы для DN свыше 500 на объектах ТЭС и АЭС.'},
  {cat:'sdt',sub:'otvody',type:'ost',code:'ОСТ 24.125.07-89',title:'Колена штампованные. Номенклатура энергомашиностроения',desc:'Штампованные колена для котельных и турбинных трубопроводов.'},
  {cat:'sdt',sub:'troyniki',type:'ost',code:'ОСТ 34-10-432-90',title:'Тройники равнопроходные сверлёные',desc:'Тройники, изготавливаемые сверлением отверстия в основной трубе.'},
  {cat:'sdt',sub:'troyniki',type:'ost',code:'ОСТ 34-10-510-90',title:'Тройники сварные равнопроходные',desc:'Сварные равнопроходные тройники для энергетических трубопроводов.'},
  {cat:'sdt',sub:'troyniki',type:'sto',code:'СТО 79814898 125-2009',title:'Тройники сварные переходные (соответствует ОСТ 34-10-511-90)',desc:'Действующий стандарт на переходные сварные тройники энергомашиностроения.',supersedes:'ОСТ 34-10-511-90'},
  {cat:'sdt',sub:'troyniki',type:'ost',code:'ОСТ 34-10-433-90',title:'Тройники переходные с усиленным штуцером',desc:'Тройники с дополнительным усилением зоны врезки ответвления.'},
  {cat:'sdt',sub:'troyniki',type:'ost',code:'ОСТ 24.125.17-89',title:'Тройники штампованные равнопроходные с вытянутой горловиной',desc:'Штампованные тройники с удлинённой горловиной ответвления.'},
  {cat:'sdt',sub:'troyniki',type:'ost',code:'ОСТ 34 10.762-97',title:'Тройники сварные. Технические требования',desc:'Отраслевой стандарт на сварные тройники энергетических трубопроводов.'},
  {cat:'sdt',sub:'troyniki',type:'gost',code:'ГОСТ 22822-83',title:'Тройники переходные штампосварные из углеродистой стали',desc:'Переходные тройники для магистральных и технологических трубопроводов.'},
  {cat:'sdt',sub:'perehody',type:'sto',code:'СТО 79814898 115-2009',title:'Переходы концентрические (соответствует ОСТ 34-10-422-90)',desc:'Действующий стандарт на концентрические переходы энергомашиностроения.',supersedes:'ОСТ 34-10-422-90'},
  {cat:'sdt',sub:'perehody',type:'ost',code:'ОСТ 36-22-77',title:'Переходы сварные. Технические условия',desc:'Сварные концентрические и эксцентрические переходы для ТЭС.'},
  {cat:'sdt',sub:'perehody',type:'ost',code:'ОСТ 24.125.09-89',title:'Переходы штампованные. Номенклатура энергомашиностроения',desc:'Штампованные переходы для котельных трубопроводов.'},
  {cat:'sdt',sub:'perehody',type:'sto',code:'СТО 79814898 113-2009',title:'Переходы точёные (соответствует ОСТ 34-10-423-90)',desc:'Переходы малых диаметров, изготавливаемые механической обработкой.',supersedes:'ОСТ 34-10-423-90'},
  {cat:'sdt',sub:'perehody',type:'ost',code:'ОСТ 34-10-424-90',title:'Переходы сварные листовые',desc:'Переходы, изготавливаемые вальцовкой и сваркой листового проката.'},
  {cat:'sdt',sub:'perehody',type:'sto',code:'СТО 79814898 110-2009',title:'Переходники (соответствует ОСТ 34-10-417-90)',desc:'Переходники для соединения трубопроводов разного диаметра или профиля.',supersedes:'ОСТ 34-10-417-90'},
  {cat:'sdt',sub:'shtutsery',type:'ost',code:'ОСТ 24.125.21-85',title:'Донышки. Номенклатура энергомашиностроения',desc:'Малогабаритные донышки для арматуры и приборных врезок.'},
  {cat:'sdt',sub:'zaglushki',type:'ost',code:'ОСТ 34-10-428-90',title:'Заглушки с соединительным выступом фланцевые',desc:'Фланцевые заглушки с уплотнительным выступом для высокого давления.'},
  {cat:'sdt',sub:'zaglushki',type:'sto',code:'СТО 95 166-2013',title:'Заглушки плоские приварные (соответствует ОСТ 34-42-666-84)',desc:'Плоские приварные заглушки для отключения участков трубопровода.',supersedes:'ОСТ 34-42-666-84'},
  {cat:'sdt',sub:'zaglushki',type:'atk',code:'АТК 26-18-5-93',title:'Заглушки поворотные для трубопроводной арматуры',desc:'Ведомственный технический каталог на поворотные заглушки.'},
  {cat:'sdt',sub:'zaglushki',type:'atk',code:'АТК 24.200.02-90',title:'Заглушки фланцевые',desc:'Ведомственный технический каталог на фланцевые заглушки трубопроводов.'},
  {cat:'sdt',sub:'zaglushki',type:'ost',code:'ОСТ 36-25-77',title:'Заглушки эллиптические. Технические условия',desc:'Отраслевой стандарт, дополняющий ГОСТ 17380 для нестандартных серий.'},
  {cat:'sdt',sub:'shtutsery',type:'ost',code:'ОСТ 34-10-509-90',title:'Штуцеры для ответвлений трубопроводов',desc:'Приварные штуцеры для установки приборов и отборов давления.'},
  {cat:'sdt',sub:'shtutsery',type:'gost',code:'ГОСТ 22792-83',title:'Штуцера для трубопроводов. Технические условия',desc:'Общие технические требования к приварным штуцерам.'},
  {cat:'sdt',sub:'shtutsery',type:'ost',code:'ОСТ 24.125.22-89',title:'Бобышки. Номенклатура энергомашиностроения',desc:'Бобышки для установки термопар, манометров и датчиков давления.'},
  {cat:'sdt',sub:'shtutsery',type:'gost',code:'ГОСТ 22820-83',title:'Угольники стальные для трубопроводов',desc:'Резьбовые угольники малых диаметров для импульсных линий.'},
  {cat:'td',type:'gost',code:'ГОСТ 22826-83',title:'Переходы точёные для трубопроводов малых диаметров',desc:'Точёные переходники, применяемые совместно со штуцерами и бобышками.'},
  {cat:'sdt',sub:'flanci',type:'ost',code:'ОСТ 34-10-425-90',title:'Фланцы плоские приварные с патрубком',desc:'Фланцы с интегрированным патрубком для узлов малого диаметра.'},
  {cat:'sdt',sub:'flanci',type:'ost',code:'ОСТ 24.125.24-89',title:'Фланцы приварные встык с выступом. Номенклатура энергомашиностроения',desc:'Фланцы с уплотнительным выступом для повышенного давления.'},
  {cat:'sdt',sub:'flanci',type:'ost',code:'ОСТ 24.125.25-89',title:'Фланцы приварные встык с впадиной. Номенклатура энергомашиностроения',desc:'Ответная пара к фланцам с выступом ОСТ 24.125.24-89.'},
  {cat:'sdt',sub:'obshchie',type:'gost',code:'ГОСТ 33259-2015',title:'Детали трубопроводов, арматура и фланцы стальные. Общие технические условия',desc:'Обобщающий стандарт на стальные фланцы и соединительные детали.'},
  {cat:'tr',type:'gost',code:'ГОСТ 3262-75',title:'Трубы стальные водогазопроводные. Технические условия',desc:'Трубы для систем ЖКХ, водо- и газоснабжения низкого давления.'},
  {cat:'tr',type:'gost',code:'ГОСТ 9941-81',title:'Трубы бесшовные холоднодеформированные из коррозионно-стойкой стали',desc:'Точные тонкостенные трубы из нержавеющей стали, парный стандарт к ГОСТ 9940.'},
  {cat:'tr',type:'gost',code:'ГОСТ 10704-91',title:'Трубы стальные электросварные прямошовные. Сортамент',desc:'Сортамент сварных прямошовных труб общего назначения.'},
  {cat:'tr',type:'gost',code:'ГОСТ 10705-80',title:'Трубы стальные электросварные. Технические условия',desc:'Общие технические требования к электросварным трубам.'},
  {cat:'tr',type:'gost',code:'ГОСТ 20295-85',title:'Трубы стальные сварные для газонефтепроводов. Технические условия',desc:'Трубы повышенной прочности для магистральных трубопроводов.'},
  {cat:'op',type:'ost',code:'ОСТ 36-146-88',title:'Опоры трубопроводов тепловых сетей. Конструкция и размеры',desc:'Базовая серия опор: тавровые, корпусные, трубчатые, швеллерные, уголковые, катковые исполнения.'},
  {cat:'op',type:'gost',code:'ГОСТ 14911-82',title:'Опоры и подвески трубопроводов. Общие технические условия',desc:'Базовый стандарт для серии подвижных опор ОПП/ОПБ/ОПХ.'},
  {cat:'iz',type:'gost',code:'ГОСТ 30732-2006',title:'Трубы и фасонные изделия стальные с индустриальной тепловой изоляцией из пенополиуретана в полиэтиленовой оболочке',desc:'Основной стандарт на предизолированные трубы и фасонные детали ППУ.'},
  {cat:'iz',type:'tu',code:'ТУ 24.20.10-003-01293553-2020',title:'Трубы в ППУ-изоляции. Технические условия',desc:'Заводские ТУ на предизолированные трубы сверх сортамента ГОСТ 30732.'},
  {cat:'iz',type:'gost',code:'ГОСТ 9.602-2016',title:'Единая система защиты от коррозии и старения. Сооружения подземные. Общие требования к защите от коррозии',desc:'Требования к изоляционным покрытиям подземных трубопроводов (ВУС-изоляция).'},
  {cat:'qc',type:'sto',code:'СТО ЦКТИ',title:'Аттестация технологии сварки и производства энергетического оборудования',desc:'Подтверждает квалификацию завода на изготовление деталей ТЭС и АЭС.'},
  {cat:'qc',type:'sto',code:'СТО СРО-П',title:'Стандарт саморегулируемой организации в области строительства и монтажа',desc:'Требования СРО, подтверждающие право завода на монтажные и строительные работы.'},
];

const CAT_META={
  all:{label:'Все',short:'ВСЕ'},
  sdt:{label:'Соединительные детали и фланцы',short:'СДТ',catalog:'sdt'},
  op:{label:'Опоры',short:'ОП',catalog:'op'},
  zra:{label:'Арматура',short:'ЗРА',catalog:'zra'},
  tr:{label:'Трубы',short:'ТР',catalog:'tr'},
  iz:{label:'Изоляция',short:'ИЗ',catalog:'iz'},
  td:{label:'Точёные детали',short:'ТД',catalog:'td'},
  mat:{label:'Материалы',short:'МАТ'},
  qc:{label:'Контроль и сертификация',short:'КС'},
};
/* sub-facet — only meaningful inside cat:'sdt'; flanci keeps its own catalog section */
const SUBTYPE_META={
  otvody:{label:'Отводы',catalog:'sdt'},
  troyniki:{label:'Тройники',catalog:'sdt'},
  perehody:{label:'Переходы',catalog:'sdt'},
  dnishcha:{label:'Днища',catalog:'sdt'},
  zaglushki:{label:'Заглушки',catalog:'sdt'},
  flanci:{label:'Фланцы',catalog:'fl'},
  shtutsery:{label:'Штуцеры и бобышки',catalog:'sdt'},
  obshchie:{label:'Общие технические условия',catalog:'sdt'},
};
const TYPE_META={
  gost:{short:'ГОСТ',full:'Государственный стандарт',color:'var(--dark)'},
  ost:{short:'ОСТ',full:'Отраслевой стандарт',color:'var(--blue)'},
  tu:{short:'ТУ',full:'Технические условия',color:'var(--g1)'},
  np:{short:'НП',full:'Федеральные нормы и правила (Ростехнадзор)',color:'#5C4A6B'},
  pb:{short:'ПБ',full:'Правила безопасности',color:'#5C4A6B'},
  pnae:{short:'ПНАЭ',full:'Правила атомной энергетики',color:'#8A4B3E'},
  tr:{short:'ТР ТС',full:'Технический регламент Таможенного союза',color:'#3E6B4B'},
  decl:{short:'ДЕКЛ',full:'Декларация о соответствии',color:'#3E6B4B'},
  sto:{short:'СТО',full:'Стандарт организации',color:'#3E5C7A'},
  atk:{short:'АТК',full:'Ведомственный технический каталог',color:'#6D6350'},
};

let state={cat:'all',sub:'all',type:'all',q:''};
let visibleCount=12;
const PAGE_SIZE=12;

function docCatalog(d){
  return (d.sub&&SUBTYPE_META[d.sub]?.catalog)||CAT_META[d.cat].catalog;
}
function escRe(s){return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');}
function hl(text,q){
  if(!q)return text;
  const re=new RegExp('('+escRe(q)+')','ig');
  return text.replace(re,'<mark>$1</mark>');
}

function filteredDocs(){
  return DOCS.filter(d=>{
    if(state.cat!=='all'&&d.cat!==state.cat)return false;
    if(state.cat==='sdt'&&state.sub!=='all'&&d.sub!==state.sub)return false;
    if(state.type!=='all'&&d.type!==state.type)return false;
    if(state.q){
      const q=state.q.toLowerCase();
      const hay=(d.code+' '+d.title+' '+d.desc+' '+CAT_META[d.cat].label).toLowerCase();
      if(!hay.includes(q))return false;
    }
    return true;
  });
}

function resetFilters(){
  state={cat:'all',sub:'all',type:'all',q:''};
  document.getElementById('nbSearch').value='';
  visibleCount=PAGE_SIZE;
  renderChips();renderSubChips();renderTypeChips();renderActiveFilters();renderGrid();
}

function renderChips(){
  const wrap=document.getElementById('nbChips');
  const catPool=DOCS.filter(d=>state.type==='all'||d.type===state.type);
  const counts={all:catPool.length};
  catPool.forEach(d=>{counts[d.cat]=(counts[d.cat]||0)+1;});
  wrap.innerHTML=Object.keys(CAT_META).map(key=>{
    const n=counts[key]||0;
    if(key!=='all'&&n===0)return '';
    return `<button class="nb-chip${state.cat===key?' active':''}" data-cat="${key}">${CAT_META[key].label}<span class="nb-chip-n">${n}</span></button>`;
  }).join('');
  wrap.querySelectorAll('.nb-chip').forEach(btn=>{
    btn.addEventListener('click',()=>{
      state.cat=btn.dataset.cat;
      if(state.cat!=='sdt')state.sub='all';
      visibleCount=PAGE_SIZE;
      renderChips();
      renderSubChips();
      renderTypeChips();
      renderActiveFilters();
      renderGrid();
    });
  });
}

function renderSubChips(){
  const row=document.getElementById('nbSubRow');
  const wrap=document.getElementById('nbSubChips');
  if(state.cat!=='sdt'){row.style.display='none';return;}
  row.style.display='';
  const subPool=DOCS.filter(d=>d.cat==='sdt'&&(state.type==='all'||d.type===state.type));
  const counts={all:subPool.length};
  subPool.forEach(d=>{counts[d.sub]=(counts[d.sub]||0)+1;});
  wrap.innerHTML=['all',...Object.keys(SUBTYPE_META)].map(key=>{
    const n=counts[key]||0;
    if(key!=='all'&&n===0)return '';
    const label=key==='all'?'Все':SUBTYPE_META[key].label;
    return `<button class="nb-chip nb-chip--sm${state.sub===key?' active':''}" data-sub="${key}">${label}<span class="nb-chip-n">${n}</span></button>`;
  }).join('');
  wrap.querySelectorAll('.nb-chip').forEach(btn=>{
    btn.addEventListener('click',()=>{
      state.sub=btn.dataset.sub;
      visibleCount=PAGE_SIZE;
      renderSubChips();
      renderTypeChips();
      renderActiveFilters();
      renderGrid();
    });
  });
}

function renderTypeChips(){
  const wrap=document.getElementById('nbTypeChips');
  const typePool=DOCS.filter(d=>(state.cat==='all'||d.cat===state.cat)&&(state.cat!=='sdt'||state.sub==='all'||d.sub===state.sub));
  const counts={all:typePool.length};
  typePool.forEach(d=>{counts[d.type]=(counts[d.type]||0)+1;});
  const order=['all',...Object.keys(TYPE_META)];
  wrap.innerHTML=order.map(key=>{
    const n=counts[key]||0;
    if(key!=='all'&&n===0)return '';
    const label=key==='all'?'Все':TYPE_META[key].short;
    return `<button class="nb-chip nb-chip--sm${state.type===key?' active':''}" data-type="${key}">${label}<span class="nb-chip-n">${n}</span></button>`;
  }).join('');
  wrap.querySelectorAll('.nb-chip').forEach(btn=>{
    btn.addEventListener('click',()=>{
      state.type=btn.dataset.type;
      visibleCount=PAGE_SIZE;
      renderChips();
      renderSubChips();
      renderTypeChips();
      renderActiveFilters();
      renderGrid();
    });
  });
}

function renderActiveFilters(){
  const wrap=document.getElementById('nbActive');
  const tags=[];
  if(state.cat!=='all')tags.push({k:'cat',label:'Категория: '+CAT_META[state.cat].label});
  if(state.cat==='sdt'&&state.sub!=='all')tags.push({k:'sub',label:'Тип детали: '+SUBTYPE_META[state.sub].label});
  if(state.type!=='all')tags.push({k:'type',label:'Тип документа: '+TYPE_META[state.type].short});
  if(state.q)tags.push({k:'q',label:'Поиск: «'+state.q+'»'});
  if(!tags.length){wrap.innerHTML='';wrap.style.display='none';return;}
  wrap.style.display='flex';
  wrap.innerHTML=tags.map(t=>`<span class="nb-active-tag" data-k="${t.k}">${t.label}<b>✕</b></span>`).join('')
    +`<button class="nb-active-clear" id="nbClearAll">Сбросить всё</button>`;
  wrap.querySelectorAll('.nb-active-tag').forEach(tag=>{
    tag.addEventListener('click',()=>{
      const k=tag.dataset.k;
      if(k==='cat'){state.cat='all';state.sub='all';}
      if(k==='sub')state.sub='all';
      if(k==='type')state.type='all';
      if(k==='q'){state.q='';document.getElementById('nbSearch').value='';}
      visibleCount=PAGE_SIZE;
      renderChips();renderSubChips();renderTypeChips();renderActiveFilters();renderGrid();
    });
  });
  const clearBtn=document.getElementById('nbClearAll');
  if(clearBtn)clearBtn.addEventListener('click',resetFilters);
}

function renderGrid(){
  const grid=document.getElementById('nbGrid');
  const list=filteredDocs();
  const shown=list.slice(0,visibleCount);
  document.getElementById('nbCount').innerHTML=list.length
    ? `Показано <b>${shown.length}</b> из <b>${list.length}</b> (всего в базе ${DOCS.length})`
    : `Ничего не найдено — всего в базе ${DOCS.length} документов`;

  if(!list.length){
    grid.innerHTML=`<div class="nb-empty"><div class="ne-code">∅</div><div class="ne-msg">По запросу документы не найдены</div><button class="ne-reset" id="neReset">Сбросить фильтры</button></div>`;
    document.getElementById('nbMoreWrap').style.display='none';
    document.getElementById('neReset').addEventListener('click',resetFilters);
    return;
  }

  grid.innerHTML=shown.map((d,i)=>{
    const t=TYPE_META[d.type];
    const cm=CAT_META[d.cat];
    const subLabel=d.sub?SUBTYPE_META[d.sub].label:cm.short;
    const cat=docCatalog(d);
    const catLink=cat?`<a class="nb-cat-link" href="${NB_CATALOG(cat)}">Каталог: ${d.sub?SUBTYPE_META[d.sub].label:cm.label} →</a>`:'';
    const superNote=d.supersedes?`<div class="nb-super">Заменяет ${d.supersedes} — используйте актуальную редакцию</div>`:'';
    return `<article class="nb-card" style="animation-delay:${(i%PAGE_SIZE)*0.02}s" data-idx="${DOCS.indexOf(d)}">
      <div class="nb-card-top">
        <span class="nb-type" style="background:${t.color}">${t.short}</span>
        <span class="nb-cat-label">${subLabel}</span>
      </div>
      <div class="nb-code">${hl(d.code,state.q)}</div>
      <h3 class="nb-title">${hl(d.title,state.q)}</h3>
      <p class="nb-desc">${hl(d.desc,state.q)}</p>
      ${superNote}
      <div class="nb-foot">
        <span class="nb-status"><span class="nb-status-dot"></span>Действует</span>
        <button class="nb-btn nb-btn--dl" data-act="dl" data-idx="${DOCS.indexOf(d)}">Скачать PDF</button>
        <button class="nb-btn nb-btn--view" data-act="view" data-idx="${DOCS.indexOf(d)}">Просмотр</button>
      </div>
      ${catLink}
    </article>`;
  }).join('');

  document.getElementById('nbMoreWrap').style.display=(visibleCount<list.length)?'flex':'none';
}

document.getElementById('nbMore').addEventListener('click',()=>{
  visibleCount+=PAGE_SIZE;
  renderGrid();
});

document.getElementById('nbSearch').addEventListener('input',(e)=>{
  state.q=e.target.value.trim();
  visibleCount=PAGE_SIZE;
  renderActiveFilters();
  renderGrid();
});

/* CARD ACTIONS (event delegation) */
document.getElementById('nbGrid').addEventListener('click',(e)=>{
  const btn=e.target.closest('[data-act]');
  if(!btn)return;
  const d=DOCS[+btn.dataset.idx];
  if(btn.dataset.act==='view')openPanel(d);
  if(btn.dataset.act==='dl')requestDownload(d);
});

/* DIRECTION-AWARE CARD HOVER — white flows in from the edge the
   cursor crosses on entry, and flows out toward the edge it exits
   through, so the highlight reads as passing between neighbour cards */
function cardFlowEdge(e,card){
  const r=card.getBoundingClientRect();
  const w=r.width,h=r.height;
  const x=(e.clientX-r.left-w/2)*(w>h?h/w:1);
  const y=(e.clientY-r.top-h/2)*(h>w?w/h:1);
  return Math.round((Math.atan2(y,x)*(180/Math.PI)+180)/90+3)%4; // 0 top,1 right,2 bottom,3 left
}
const CARD_FLOW_OFFSET=[{x:'0%',y:'-100%'},{x:'100%',y:'0%'},{x:'0%',y:'100%'},{x:'-100%',y:'0%'}];
const nbGridEl=document.getElementById('nbGrid');
nbGridEl.addEventListener('mouseover',(e)=>{
  const card=e.target.closest('.nb-card');
  if(!card||card.contains(e.relatedTarget))return;
  const off=CARD_FLOW_OFFSET[cardFlowEdge(e,card)];
  card.classList.add('flow-instant');
  card.style.setProperty('--flow-x',off.x);
  card.style.setProperty('--flow-y',off.y);
  void card.offsetWidth;
  card.classList.remove('flow-instant');
  card.style.setProperty('--flow-x','0%');
  card.style.setProperty('--flow-y','0%');
});
nbGridEl.addEventListener('mouseout',(e)=>{
  const card=e.target.closest('.nb-card');
  if(!card||card.contains(e.relatedTarget))return;
  const off=CARD_FLOW_OFFSET[cardFlowEdge(e,card)];
  card.style.setProperty('--flow-x',off.x);
  card.style.setProperty('--flow-y',off.y);
});

/* DETAIL PANEL */
const overlay=document.getElementById('nbOverlay');
const panel=document.getElementById('nbPanel');
function openPanel(d){
  const t=TYPE_META[d.type];
  const cm=CAT_META[d.cat];
  document.getElementById('pType').textContent=t.short;
  document.getElementById('pType').style.background=t.color;
  document.getElementById('pCode').textContent=d.code;
  document.getElementById('pTitle').textContent=d.title;
  document.getElementById('pCat').textContent=cm.label;
  document.getElementById('pTypeFull').textContent=t.full;
  document.getElementById('pDesc').textContent=d.desc;
  const subRow=document.getElementById('pSubRow');
  if(d.sub){subRow.style.display='grid';document.getElementById('pSub').textContent=SUBTYPE_META[d.sub].label;}
  else{subRow.style.display='none';}
  const superSec=document.getElementById('pSuperSec');
  if(d.supersedes){superSec.style.display='block';document.getElementById('pSuperText').textContent=`Действующий документ заменяет ${d.supersedes}. При проектировании и приёмке используйте актуальную редакцию — ${d.code}.`;}
  else{superSec.style.display='none';}
  const cat=docCatalog(d);
  const catBtn=document.getElementById('pCatalogLink');
  if(cat){catBtn.style.display='flex';catBtn.href=NB_CATALOG(cat);}
  else{catBtn.style.display='none';}
  document.getElementById('pDownload').onclick=()=>requestDownload(d);
  overlay.classList.add('show');
  panel.classList.add('open');
}
function closePanel(){overlay.classList.remove('show');panel.classList.remove('open');}
document.getElementById('nbPanelClose').addEventListener('click',closePanel);
overlay.addEventListener('click',closePanel);
document.addEventListener('keydown',(e)=>{if(e.key==='Escape')closePanel();});

/* DOWNLOAD — honest placeholder, no fake file */
const toast=document.getElementById('nbToast');
let toastTimer=null;
function requestDownload(d){
  toast.textContent=`${d.code} — PDF пока не опубликован в базе. Запросите файл у отдела технического контроля: zakaz@prom-en.com`;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer=setTimeout(()=>toast.classList.remove('show'),4200);
}

/* INIT */
document.getElementById('hsTotal').textContent=DOCS.length;
document.getElementById('hsCats').textContent=Object.keys(CAT_META).length-1;
renderChips();
renderSubChips();
renderTypeChips();
renderActiveFilters();
renderGrid();

/* deep-link ?cat= support (legacy ?cat=fl maps to merged sdt category + flanci subtype) */
(()=>{
  const initCat=new URLSearchParams(location.search).get('cat');
  if(initCat==='fl'){
    state.cat='sdt';state.sub='flanci';
    renderChips();renderSubChips();renderTypeChips();renderActiveFilters();renderGrid();
  }else if(initCat&&CAT_META[initCat]){
    state.cat=initCat;
    renderChips();renderSubChips();renderTypeChips();renderActiveFilters();renderGrid();
  }
})();

