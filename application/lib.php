<?php
/* 
	Appointment: Подключение модулей
	File: lib.php 
 
*/
if(!defined('MOZG'))
	die('Not Found');

if(isset($_GET['go']))
	$go = htmlspecialchars(strip_tags(stripslashes(trim(urldecode(mysql_escape_string($_GET['go']))))));
else
	$go = "main";

$mozg_module = $go;

check_xss();

switch($go){

	//Новая регистрация
	case "signup":
		include APPLICATION_DIR.'/modules/register/register_new.php';
	break;

	//Регистрация
	case "register":
		include APPLICATION_DIR.'/modules/register/register.php';
	break;

	//Login
	case "loginp":
		$spBar = true;
		include APPLICATION_DIR.'/modules/login/login_page.php';
	break;

//Игры
    case "game" :
		include APPLICATION_DIR.'/modules/api/api.php';
    break;

	//Профиль пользователя
	case "profile":
		include APPLICATION_DIR.'/modules/profile/profile.php';
	break;
	
	//Редактирование моей страницы
	case "editprofile":
		$spBar = true;
		include APPLICATION_DIR.'/modules/profile/editprofile.php';
	break;
	
	//Загрузка городов
	case "loadcity":
		include APPLICATION_DIR.'/modules/loadcity/loadcity.php';
	break;
	
	//Альбомы
	case "albums":
		$spBar = true;
		if($config['album_mod'] == 'yes')
			include APPLICATION_DIR.'/modules/albums/albums.php';
		else {
			$user_fm_wrap_bar = 'Информация';
			msgbox('', 'Сервис отключен.', 'info');
		}
	break;

	//Просмотр фотографии
	case "photo":
		include APPLICATION_DIR.'/modules/photo/photo.php';
	break;

	//Друзья
	case "friends":
		$spBar = true;
		include APPLICATION_DIR.'/modules/friends/friends.php';
	break;
	
    //Заявки в AJAX
	case "ajaxfriends":
		include APPLICATION_DIR.'/modules/friends/ajaxfriends.php';
	break;
	
	// Гости
	case "guests":
		include APPLICATION_DIR . '/modules/guests/guests.php';
	break; 
	
	//Закладки
	case "fave":
		$spBar = true;
		include APPLICATION_DIR.'/modules/fave/fave.php';
	break;
	
	//Сообщения
	case "messages":
		$spBar = true;
		include APPLICATION_DIR.'/modules/messages/messages.php';
	break;

	//Чат с диалогами
	case "im_chat":
		include APPLICATION_DIR.'/modules/im_chat/im_chat.php';
	break;

	//Заметки
	case "notes":
		$spBar = true;
		include APPLICATION_DIR.'/modules/notes/notes.php';
	break;

	//Команда сайта
	case "about":
		include APPLICATION_DIR.'/modules/about/about.php';
	break;
	
	//Подписки
	case "subscriptions":
		include APPLICATION_DIR.'/modules/subscriptions/subscriptions.php';
	break;
	
	//Видео
	case "videos":
		$spBar = true;
		if($config['video_mod'] == 'yes')
			include APPLICATION_DIR.'/modules/videos/videos.php';
		else {
			$user_fm_wrap_bar = 'Информация';
			msgbox('', 'Сервис отключен.', 'info');
		}
	break;
	
	//Поиск
	case "search":
		include APPLICATION_DIR.'/modules/search/search.php';
	break;
	
	//Стена
	case "wall":
		$spBar = true;
		include APPLICATION_DIR.'/modules/wall/wall.php';
	break;
	
	//Статус
	case "status":
		include APPLICATION_DIR.'/modules/status/status.php';
	break;
	
	//Новости
	case "news":
		$spBar = true;
		include APPLICATION_DIR.'/modules/news/news.php';
	break;
	
	//Настройки
	case "settings":
		include APPLICATION_DIR.'/modules/settings/settings.php';
	break;

	//Помощь
	case "support":
		include APPLICATION_DIR.'/modules/support/support.php';
	break;
	
	//Воостановление доступа
	case "restore":
		include APPLICATION_DIR.'/modules/restore/restore.php';
	break;
	
	//Загрузка картинок при прикриплении файлов со стены, заметок, или сообщений
	case "attach":
		include APPLICATION_DIR.'/modules/attach/attach.php';
	break;
	
	//Блог сайта
	case "blog":
		$spBar = true;
		include APPLICATION_DIR.'/modules/blog/blog.php';
	break;

	//Баланс
	case "balance":
		include APPLICATION_DIR.'/modules/balance/balance.php';
	break;
	
	//Рейтинг
	case "rating":
		include APPLICATION_DIR.'/modules/rating/rating.php';
	break;
	
	//Фон
	case "fon":
		include APPLICATION_DIR.'/modules/fon/fon.php';
	break;

	//project fm
	case "project_fm":
		$spBar = true;
		include APPLICATION_DIR.'/modules/project_fm/project_fm.php';
	break;
	
	//Подарки
	case "gifts":
		include APPLICATION_DIR.'/modules/gifts/gifts.php';
	break;

	//Сообщества
	case "groups":
		include APPLICATION_DIR.'/modules/groups/groups.php';
	break;
	
	//Сообщества -> Публичные страницы
	case "public":
		include APPLICATION_DIR.'/modules/public/public.php';
	break;
	
	//Сообщества -> Загрузка фото
	case "attach_groups":
		include APPLICATION_DIR.'/modules/groups/attach_groups.php';
	break;
	
	 //Обсуждения в группах
	case "groups_forum":
		include APPLICATION_DIR.'/modules/groups/groups_forum.php';
	break;

	 //Обсуждения в группах
	case "groups_forum":
		include APPLICATION_DIR.'/modules/groups/groups_forum.php';
	break;
	
	//Коментарии для фоток
	case "attach_comm":
		include APPLICATION_DIR.'/modules/attach/attach_comm.php';
	break;
	
	//Граффити
	case "graffiti":
		include APPLICATION_DIR.'/modules/graffiti/graffiti.php';
	break;

	//Музыка
	case "audio":
		if($config['audio_mod'] == 'yes')
			include APPLICATION_DIR.'/modules/audio/audio.php';
		else {
			$spBar = true;
			$user_fm_wrap_bar = 'Информация';
			msgbox('', 'Сервис отключен.', 'info');
		}
	break;

	//Музыка player
	case "audio_player":
		if($config['audio_mod'] == 'yes')
			include APPLICATION_DIR.'/modules/audio/audio_player.php';
		else {
			$spBar = true;
			$user_fm_wrap_bar = 'Информация';
			msgbox('', 'Сервис отключен.', 'info');
		}
	break;

	//Статические страницы
	case "static":
		include APPLICATION_DIR.'/modules/static/static.php';
	break;

	//Выделить человека на фото
	case "distinguish":
		include APPLICATION_DIR.'/modules/distinguish/distinguish.php';
	break;

	//Скрываем блок Дни рожденья друзей
	case "happy_friends_block_hide":
		$_SESSION['happy_friends_block_hide'] = 1;
		die();
	break;

	//Скрываем блок Дни рожденья друзей
	case "fast_search":
		include APPLICATION_DIR.'/modules/search/fast_search.php';
	break;

	//Жалобы
	case "report":
		include APPLICATION_DIR.'/modules/report/report.php';
	break;
	
	// Алиасы
	case "alias":
    $alias = $db->safesql($_GET['url']);
	if($alias){
 	$alias_public = $db->super_query("SELECT id,title FROM `".PREFIX."_communities` WHERE adres = '".$alias."' "); //Проверяем адреса у публичных страниц
	$alias_user = $db->super_query("SELECT user_id, user_search_pref FROM `".PREFIX."_users` WHERE alias = '".$alias."'"); // Проверяем адреса у пользователей
    if($alias_user){   			
	    $_GET['id']= $alias_user['user_id'];
	    include APPLICATION_DIR.'/modules/profile/profile.php';
		}elseif($alias_public){   
		$_GET['pid']= $alias_public['id'];
		include APPLICATION_DIR.'/modules/public/public.php';
	}else{
	$spBar = true;
		$user_fm_wrap_bar = 'Информация';
		msgbox('', 'Доменное имя <b>'.$alias.'</b> свободно для регистрации.', 'info');
		}
	}
    break;

	//Отправка записи в сообщество или другу
	case "repost":
		include APPLICATION_DIR.'/modules/repost/repost.php';
	break;

	//Моментальные оповещания
	case "updates":
		include APPLICATION_DIR.'/modules/updates/updates.php';
	break;

	//Документы
	case "doc":
		include APPLICATION_DIR.'/modules/doc/doc.php';
	break;

	//Опросы
	case "votes":
		include APPLICATION_DIR.'/modules/votes/votes.php';
	break;
	
	//Сообщества -> Публичные страницы -> Аудиозаписи
	case "public_audio":
		include APPLICATION_DIR.'/modules/public/public_audio.php';
	break;
	
	//Приложения
	case "apps":
		include APPLICATION_DIR.'/modules/apps/apps.php';
	break;
	
	case "editapp":
		include APPLICATION_DIR.'/modules/apps/editapp.php';
	break;

	// Удаление приложения
    case "delete_app":
    $id = intval($_POST['id']);
    if($id){
        $sql_ = $db->super_query("SELECT id, flash, img FROM `".PREFIX."_apps` WHERE id = '$id' ");
        $db->super_query("DELETE FROM `".PREFIX."_apps` WHERE id = '$id'"); 
        $del_dir = '/uploads/apps/'.$sql_['id'].'/';
        @unlink($del_dir.$sql_['flash']);
        @unlink($del_dir.$sql_['img']);
    }        
    break;

	//Обявление
	case "ads":
		include APPLICATION_DIR.'/modules/ads/ads.php';
	break;

		default:
			$spBar = true;

		if($go != 'main')
			msgbox('', $lang['no_str_bar'], 'info');
}

if(!$metatags['title'])
	$metatags['title'] = $config['home'];
	
if($user_fm_wrap_bar) 
	$fm_wrap_bar = $user_fm_wrap_bar;
else 
	$fm_wrap_bar = $lang['welcome'];


$headers = '

<title>'.$metatags['title'].'</title>

<meta name="generator" content="www.koftagard.ru" />

<meta http-equiv="content-type" content="text/html; charset="utf-8" />

';
?>