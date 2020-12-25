<?php
/*
|---------------------------------------------------------------
| session_start - Открываем Сессию для php
|---------------------------------------------------------------
|
| Сессии используют стандартные, хорошо известные способы передачи
| данных. Собственно, других-то просто и нет.
| Идентификатор - это обычная переменная. По умолчанию ее имя - PHPSESSID. 
| Задача PHP отправить ее браузеру, чтобы тот вернул ее со следующим запросом.
| Из уже упоминавшегося раздела FAQ ясно, что переменную можно передать 
| только двумя способами: в cookies или POST/GET запросом.
| PHP использует оба варианта. Вызов сессии session_start()
| 
| За это отвечают две настройки в php.ini:
| 
| session.use_cookies - если равно 1, то PHP передает идентификатор в 
| cookies, если 0 - то нет.
| session.use_trans_sid если равно 1, то PHP передает его, добавляя к
| URL и формам, если 0 - то нет.
|
|
| NO TRAILING SLASH!
|
*/

if(isset($_POST["PHPSESSID"])){

	session_id($_POST["PHPSESSID"]);

} @session_start();

/*
|---------------------------------------------------------------
| ob_start — Включение буферизации вывода
|---------------------------------------------------------------
|
| Эта функция включает буферизацию вывода. Если буферизация вывода
| активна, вывод скрипта не высылается (кроме заголовков), а 
| сохраняется во внутреннем буфере. Содержимое этого внутреннего
| буфера может быть скопировано в строковую переменную, 
| используя ob_get_contents(). Для вывода содержимого внутреннего
| буфера следует использовать ob_end_flush(). В качестве 
| альтернативы можно использовать ob_end_clean() для 
| уничтожения содержимого буфера. OptimizeHTML Оптимизируем html.
|
*/

include_once ("html.php");

ini_set('zlib.output_compression', 'On');
ini_set('zlib.output_compression_level', '1');

ob_start(array('OptimizeHTML', 'html'));

/*
|---------------------------------------------------------------
| ob_implicit_flush — Функция включает/выключает неявный сброс
|---------------------------------------------------------------
|
| ob_implicit_flush() включает или выключает неявный сброс. Неявный
| сброс приводит к тому, что операция сброса выполняется после 
| каждого вывода, поэтому явные вызовы функции flush() не нужны.
|
*/

@ob_implicit_flush(0);

/*
|---------------------------------------------------------------
| error_reporting — Задает, какие ошибки PHP попадут в отчет
|---------------------------------------------------------------
|
| Функция error_reporting() задает значение директивы 
| error_reporting во время выполнения. В PHP есть много уровней
| ошибок. Используя эту функцию, можно задать уровень ошибок
| времени выполнения скрипта, которые попадут в отчет. Если
| необязательный аргумент level не задан, error_reporting()
| вернет текущее значение уровня протоколирования ошибок.
|
*/

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

/*
|---------------------------------------------------------------
| define — Определяет именованную константу
|---------------------------------------------------------------
|
| MOZG				- Закрываем прямой просмотр файлов.
| ROOT_DIR			- Прямой путь к файлам.
| APPLICATION_DIR	- Путь к ядру.
|
*/

define('MOZG', true);

define('ROOT_DIR', dirname (__FILE__));

define('APPLICATION_DIR', ROOT_DIR.'/application');

/*
|---------------------------------------------------------------
| header — Отправка HTTP заголовка
|---------------------------------------------------------------
*/

header('Content-type: text/html; charset=utf-8');

/*
|---------------------------------------------------------------
| $ajax — Отправка ajax запросов.
|---------------------------------------------------------------
*/

$ajax = $_POST['ajax'];

/*
|---------------------------------------------------------------
| $logged — Закрываем доступ для тех кто не зарегистрирован.
|---------------------------------------------------------------
*/

$logged = false;

/*
|---------------------------------------------------------------
| $user_info — Вывод данных.
|---------------------------------------------------------------
*/

$user_info = false;

/*
|---------------------------------------------------------------
| init.php — Инициализируем некоторые объекты .
|---------------------------------------------------------------
*/

include ROOT_DIR.'/init.php';

/*
|---------------------------------------------------------------
| reg — Открываем сессию для регистрации рефералов .
|---------------------------------------------------------------
*/

if($_GET['reg']) $_SESSION['ref_id'] = intval($_GET['reg']);

/*
|---------------------------------------------------------------
| HTTP_USER_AGENT — Проверяем наши браузеры, если версия устарела
| то ссылаем на объект, и закрываем доступ к сайту..
|---------------------------------------------------------------
*/

if(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) $xBrowser = 'ie6';

elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) $xBrowser = 'ie7';

elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) $xBrowser = 'ie8';

if($xBrowser == 'ie6' OR $xBrowser == 'ie7' OR $xBrowser == 'ie8')

header("Location: /application/doc_bin/badbrowser.php");

/*
|---------------------------------------------------------------
| $CacheNews — Идентифицируем данные и делаем новую запись в cache сети.
| Проверяем на наличие обновлений пользователя.
|---------------------------------------------------------------
*/

$CacheNews = mozg_cache('user_'.$user_info['user_id'].'/new_news');

if($CacheNews){

	$new_news = "{$CacheNews}";

	$news_link = '/notifications';

}

/*
|---------------------------------------------------------------
| $user_pm_num — Получаем количество сообщений пользователя.
|---------------------------------------------------------------
*/

$user_pm_num = $user_info['user_pm_num'];

if($user_pm_num)

	$user_pm_num = "({$user_pm_num})";

else

	$user_pm_num = '';

/*
|---------------------------------------------------------------
| $user_new_groups — Получаем количество новых групп.
|---------------------------------------------------------------
*/

$user_new_groups = $user_info['user_new_groups'];

if($user_new_groups)

	$user_new_groups = "({$user_new_groups})";

else

	$user_new_groups = '';

/*
|---------------------------------------------------------------
| $user_friends_demands — Получаем количество заявок на дружбу.
|---------------------------------------------------------------
*/

$user_friends_demands = $user_info['user_friends_demands'];

if($user_friends_demands){

	$demands = "({$user_friends_demands})";

	$requests_link = '/requests';

} else

	$demands = '';

/*
|---------------------------------------------------------------
| $user_support — Получаем количество записей в суппорте .
|---------------------------------------------------------------
*/

$user_support = $user_info['user_support'];

if($user_support)

	$support = "{$user_support}";

else

	$support = '';

/*
|---------------------------------------------------------------
| user_new_mark_photos — Получаем отметки на фотографиях .
|---------------------------------------------------------------
*/

if($user_info['user_new_mark_photos']){

	$new_photos_link = 'newphotos';

	$new_photos = "+{$user_info['user_new_mark_photos']}";

} else {

	$new_photos = '';

	$new_photos_link = $user_info['user_id'];

}

/*
|---------------------------------------------------------------
| $ajax — Включаем ajax подгрузку.
|---------------------------------------------------------------
*/

if($ajax == 'yes'){

	if($_SERVER['REQUEST_METHOD'] == 'POST' AND $ajax != 'yes')

		die('No Ajax');

	if($spBar)

		$ajaxSpBar = "$('#fm_wrap_bar').show().html('{$fm_wrap_bar}')";

	else

		$ajaxSpBar = "$('#fm_wrap_bar').hide()";

/*
|---------------------------------------------------------------
| $result_ajax — Получаем и выводим js результаты.
|---------------------------------------------------------------
*/

$result_ajax = <<<HTML

<script type="text/javascript">

	document.title = '{$metatags['title']}';

	{$ajaxSpBar};

	document.getElementById('new_msg').innerHTML = '{$user_pm_num}';

	document.getElementById('new_groups').innerHTML = '{$user_new_groups}';

	document.getElementById('new_news').innerHTML = '{$new_news}';

	document.getElementById('new_support').innerHTML = '{$support}';

	document.getElementById('news_link').setAttribute('href', '/news{$news_link}');

	document.getElementById('new_requests').innerHTML = '{$demands}';

	document.getElementById('new_photos').innerHTML = '{$new_photos}';

	document.getElementById('requests_link_new_photos').setAttribute('href', '/albums/{$new_photos_link}');

	document.getElementById('requests_link').setAttribute('href', '/friends{$requests_link}');

</script>

{$tpl->result['info']}{$tpl->result['content']}

HTML;

echo str_replace('{theme}', '/html/', $result_ajax);

$tpl->global_clear();

$db->close();

/*
|---------------------------------------------------------------
| gzip — значение yes - включено зжатие, закрываем GzipOut();.
|---------------------------------------------------------------
*/

if($config['gzip'] == 'yes')

		GzipOut();

	die();

}

/*
|---------------------------------------------------------------
| main — Загружаем корень шаблона.
|---------------------------------------------------------------
*/

if(!$logged AND $go == 'main'){

	$tpl->load_template('index.html');

	$fm_count_user = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users`");

	$tpl->set('{cnt}', $fm_count_user['cnt']);

} else {

	$tpl->load_template('main.html');

}

/*
|---------------------------------------------------------------
| user_photo — Загружаем мини изображения пользователя в header.
|---------------------------------------------------------------
*/

if($logged){

	if($user_info['user_photo'])

		$ava = $config['home_url'].'uploads/users/'.$user_info['user_id'].'/100_'.$user_info['user_photo'];

	else 

		$ava = '/images/no_ava_50.png';

	$myphoto_header.='<img src="'.$ava.'" width="23" />'."\n";

	$tpl->set('{myphoto_header}', $myphoto_header);

}

/*
|---------------------------------------------------------------
| alias — Проверяем на наличия alias для пользователя, если он 
| установлен то выводим его..
|---------------------------------------------------------------
*/

if($user_info['alias']){

	$tpl->set('{alias-main}', $user_info['alias']); 

		} else {

	$tpl->set('{alias-main}', 'id'.$user_info['user_id']);

}

/*
|---------------------------------------------------------------
| Выводы — Елементы шаблона.
|---------------------------------------------------------------
*/

if($logged){

	$tpl->set_block("'\\[not-logged\\](.*?)\\[/not-logged\\]'si","");

	$tpl->set('[logged]','');

	$tpl->set('[/logged]','');

	$tpl->set('{my-page-link}', '/id'.$user_info['user_id']);

	$tpl->set('{my-id}', $user_info['user_id']);

/*
|---------------------------------------------------------------
| $user_friends_demands — Получаем количество записей в суппорте .
| И выводим в шаблоне.
|---------------------------------------------------------------
*/
	
	$user_friends_demands = $user_info['user_friends_demands'];

	if($user_friends_demands){

		$tpl->set('{demands}', $demands);

		$tpl->set('{requests-link}', $requests_link);

	} else {

		$tpl->set('{demands}', '');

		$tpl->set('{requests-link}', '');

	}

/*
|---------------------------------------------------------------
| $CacheNews — Идентифицируем данные и делаем новую запись в cache сети.
| Проверяем на наличие обновлений пользователя.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($CacheNews){

	$tpl->set('{new-news}', $new_news);

	$tpl->set('{news-link}', $news_link);

} else {

	$tpl->set('{new-news}', '');

	$tpl->set('{news-link}', '');

}

/*
|---------------------------------------------------------------
| $user_pm_num — Получаем количество сообщений пользователя.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($user_pm_num)

	$tpl->set('{msg}', $user_pm_num);

else 

	$tpl->set('{msg}', '');

/*
|---------------------------------------------------------------
| $user_new_groups — Получаем количество новых групп.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($user_new_groups)

	$tpl->set('{new_groups}', $user_new_groups);

else 

	$tpl->set('{new_groups}', '');

/*
|---------------------------------------------------------------
| $user_support — Получаем количество записей в суппорте.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($user_support)

	$tpl->set('{new-support}', $support);

else 

	$tpl->set('{new-support}', '');

/*
|---------------------------------------------------------------
| user_new_mark_photos — Получаем отметки на фотографиях.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($user_info['user_new_mark_photos']){

	$tpl->set('{my-id}', 'newphotos');

	$tpl->set('{new_photos}', $new_photos);

} else 

	$tpl->set('{new_photos}', '');

} else {

	$tpl->set_block("'\\[logged\\](.*?)\\[/logged\\]'si","");

	$tpl->set('[not-logged]','');

	$tpl->set('[/not-logged]','');

	$tpl->set('{my-page-link}', '');

}

/*
|---------------------------------------------------------------
| user_img_fon — Подгружаем фон пользователя если он установлен.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($user_info['user_img_fon']){

	$tpl->set('{fon_facemy}', $user_info['user_img_fon']);

} else {

	$tpl->set('{fon_facemy}', '/images/bg_top.gif');

}

/*
|---------------------------------------------------------------
| {header} — Подгружаем meta данные.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

$tpl->set('{header}', $headers);

/*
|---------------------------------------------------------------
| {fm_wrap_bar} — Подгружаем bar для вывода информации.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

$tpl->set('{fm_wrap_bar}', $fm_wrap_bar);

/*
|---------------------------------------------------------------
| {info} — Выводим с генерирование данные.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

$tpl->set('{info}', $tpl->result['info']);

/*
|---------------------------------------------------------------
| {content} — Загружаем все данные в контент шаблона.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

$tpl->set('{content}', $tpl->result['content']);

/*
|---------------------------------------------------------------
| $spBar — Открываем закрываем bar блок.
| И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($spBar)

	$tpl->set_block("'\\[fm_wrap_bar\\](.*?)\\[/fm_wrap_bar\\]'si","");

else {

	$tpl->set('[fm_wrap_bar]','');

	$tpl->set('[/fm_wrap_bar]','');

}

/*
|---------------------------------------------------------------
| {js} — Подгружаем js файлы. И выводим в шаблоне.
|---------------------------------------------------------------
*/

if($logged)

	$tpl->set('{js}', '

	<script type="text/javascript" src="/js/jquery.lib.js"></script>

	<script type="text/javascript" src="/js/main.js"></script>

	<script type="text/javascript" src="/js/common.js"></script>

	<script type="text/javascript" src="/js/apps.js"></script>

	<script type="text/javascript" src="/js/apps_edit.js"></script>

	<script type="text/javascript" src="/js/rating.js"></script>

	<script type="text/javascript" src="/js/audio_player.js"></script>

');

else

	$tpl->set('{js}', '

	<script type="text/javascript" src="/js/jquery.lib.js"></script>

	<script type="text/javascript" src="/js/main.js"></script>

');
/*
|---------------------------------------------------------------
| mobile — Вывводим и подключаем данные для мобильной версии
|---------------------------------------------------------------
*/

if($_SESSION['user_mobile'] == 1){

$new_actions = $demands+$new_news+$user_pm_num+$support;
if($new_actions > 0)
$tpl->set('{new-actions}', $new_actions);
else
$tpl->set('{new-actions}', '');

if($user_info['user_photo'])
$ava =$config['home_url'].'/uploads/users/'.$user_info['user_id'].'/50_'.$user_info['user_photo'];
else
$ava = '/templates/Default/images/no_ava_50.gif';

$tpl->set('{mobile-speedbar}', $speedbar);
$tpl->set('{my-name}', $user_info['user_search_pref']);
$tpl->set('{status-mobile}', $user_info['user_status']);
$tpl->set('{my-ava}', $ava);

}

/*
|---------------------------------------------------------------
| compile — компилируем все данные для main.
|---------------------------------------------------------------
*/

$tpl->compile('main');

echo str_replace('{theme}', '/html/', $tpl->result['main']);

$tpl->global_clear();

$db->close();

if($config['gzip'] == 'yes')

	GzipOut();

?>