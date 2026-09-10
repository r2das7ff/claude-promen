<?php
/**
 * Карта переездов каталога после правок по замечаниям ОТК (2026-09-10).
 * Генерируется scripts/otk-fix/. Руками не править.
 *
 *   moved — товар сменил слаг, потому что род изделия в адресе не совпадал
 *           с нормативом (бобышка и пробка лежали под zaglushka-, донышко
 *           под dnische-).
 *   gone  — типоразмера нет в нормативе, товар удалён; отдаём 410, чтобы
 *           поисковик выбросил адрес сразу, а не ждал повторных обходов.
 */
return [
'moved' => [
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d108-h-s5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d108-h-s5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d108-h-s7-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d108-h-s7-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d108-h-s9-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d108-h-s9-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d133-h-s6-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d133-h-s6-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d133-h-s8-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d133-h-s8-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d159-h-s13-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d159-h-s13-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d159-h-s6-5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d159-h-s6-5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d159-h-s9-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d159-h-s9-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d219-h-s12-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d219-h-s12-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d220-h-s8-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d220-h-s8-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d25-h-s3-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d25-h-s3-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d32-h-s3-5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d32-h-s3-5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d38-h-s3-5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d38-h-s3-5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d57-h-s4-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d57-h-s4-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d57-h-s5-5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d57-h-s5-5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d76-h-s4-5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d76-h-s4-5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d76-h-s7-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d76-h-s7-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d89-h-s5-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d89-h-s5-ost-24-125-21-1989-2/',
	'/catalog/sdt/dnishcha/dnische-ellipticheskoe-d89-h-s8-ost-24-125-21-1989-2/' => '/catalog/sdt/dnishcha/donyshko-ellipticheskoe-d89-h-s8-ost-24-125-21-1989-2/',
	'/catalog/sdt/zaglushki/zaglushka-1-20h1-5-ost-24-125-23-1989/' => '/catalog/sdt/zaglushki/probka-1-20h1-5-ost-24-125-23-1989/',
	'/catalog/sdt/zaglushki/zaglushka-20h1-5-ost-24-125-22-1989/' => '/catalog/sdt/zaglushki/bobyshka-20h1-5-ost-24-125-22-1989/',
	'/catalog/sdt/zaglushki/zaglushka-22h1-5-ost-24-125-22-1989/' => '/catalog/sdt/zaglushki/bobyshka-22h1-5-ost-24-125-22-1989/',
	'/catalog/sdt/zaglushki/zaglushka-22h1-5-ost-24-125-23-1989/' => '/catalog/sdt/zaglushki/probka-22h1-5-ost-24-125-23-1989/',
	'/catalog/sdt/zaglushki/zaglushka-27h1-5-ost-24-125-22-1989/' => '/catalog/sdt/zaglushki/bobyshka-27h1-5-ost-24-125-22-1989/',
	'/catalog/sdt/zaglushki/zaglushka-27h1-5-ost-24-125-23-1989/' => '/catalog/sdt/zaglushki/probka-27h1-5-ost-24-125-23-1989/',
	'/catalog/sdt/zaglushki/zaglushka-27h2-ost-24-125-22-1989/' => '/catalog/sdt/zaglushki/bobyshka-27h2-ost-24-125-22-1989/',
	'/catalog/sdt/zaglushki/zaglushka-27h2-ost-24-125-23-1989/' => '/catalog/sdt/zaglushki/probka-27h2-ost-24-125-23-1989/',
	'/catalog/sdt/zaglushki/zaglushka-33h2-ost-24-125-22-1989/' => '/catalog/sdt/zaglushki/bobyshka-33h2-ost-24-125-22-1989/',
	'/catalog/sdt/zaglushki/zaglushka-33h2-ost-24-125-23-1989/' => '/catalog/sdt/zaglushki/probka-33h2-ost-24-125-23-1989/',
],
'gone' => [
	'/catalog/sdt/troyniki/troynik-1320h14-ost-34-10-764-1997/' => 1,
	'/catalog/sdt/perekhody/perehod-k-25h-25h15-ost-34-10-423-1990/' => 1,
	'/catalog/sdt/troyniki/troynik-245h19-ost-24-125-18-1989-3/' => 1,
],
];
