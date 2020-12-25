﻿<?php

if(!defined('MOZG'))
	die('Not Found');

$mod = htmlspecialchars(strip_tags(stripslashes(trim(urldecode(mysql_escape_string($_GET['mod']))))));

check_xss();

// Локализация для даты
$langdate = array (
'January'		=>	"января",
'February'		=>	"февраля",
'March'			=>	"марта",
'April'			=>	"апреля",
'May'			=>	"мая",
'June'			=>	"июня",
'July'			=>	"июля",
'August'		=>	"августа",
'September'		=>	"сентября",
'October'		=>	"октября",
'November'		=>	"ноября",
'December'		=>	"декабря",
'Jan'			=>	"янв",
'Feb'			=>	"фев",
'Mar'			=>	"мар",
'Apr'			=>	"апр",
'Jun'			=>	"июн",
'Jul'			=>	"июл",
'Aug'			=>	"авг",
'Sep'			=>	"сен",
'Oct'			=>	"окт",
'Nov'			=>	"ноя",
'Dec'			=>	"дек",

'Sunday'		=>	"Воскресенье",
'Monday'		=>	"Понедельник",
'Tuesday'		=>	"Вторник",
'Wednesday'		=>	"Среда",
'Thursday'		=>	"Четверг",
'Friday'		=>	"Пятница",
'Saturday'		=>	"Суббота",

'Sun'			=>	"Вс",
'Mon'			=>	"Пн",
'Tue'			=>	"Вт",
'Wed'			=>	"Ср",
'Thu'			=>	"Чт",
'Fri'			=>	"Пт",
'Sat'			=>	"Сб",
);

$server_time = intval($_SERVER['REQUEST_TIME']);

switch($mod){

	//Настройки системы
	case "application":
		include ADMIN_DIR.'/application.php';
	break;

	//Управление БД
	case "db":
		include ADMIN_DIR.'/db.php';
	break;

	//dumper
	case "dumper":
		include ADMIN_DIR.'/dumper.php';
	break;

	//Личные настройки
	case "mysettings":
		include ADMIN_DIR.'/mysettings.php';
	break;

	//Пользователи
	case "users":
		include ADMIN_DIR.'/users.php';
	break;

	//Массовые действия
	case "massaction":
		include ADMIN_DIR.'/massaction.php';
	break;

	//Заметки
	case "notes":
		include ADMIN_DIR.'/notes.php';
	break;

	//Подарки
	case "gifts":
		include ADMIN_DIR.'/gifts.php';
	break;

	//Сообщества
	case "groups":
		include ADMIN_DIR.'/groups.php';
	break;

	//Шаблоны сообщений
	case "mail_tpl":
		include ADMIN_DIR.'/mail_tpl.php';
	break;

	//Рассылка сообщений
	case "mail":
		include ADMIN_DIR.'/mail.php';
	break;

	//Фильтр по: IP, E-Mail
	case "ban":
		include ADMIN_DIR.'/ban.php';
	break;

	//Поиск и Замена
	case "search":
		include ADMIN_DIR.'/search.php';
	break;

	//Статические страницы
	case "static":
		include ADMIN_DIR.'/static.php';
	break;

	//Антивирус
	case "antivirus":
		include ADMIN_DIR.'/antivirus.php';
	break;

	//Логи посещений
	case "logs":
		include ADMIN_DIR.'/logs.php';
	break;

	//Статистика
	case "stats":
		include ADMIN_DIR.'/stats.php';
	break;

	//Видео
	case "videos":
		include ADMIN_DIR.'/videos.php';
	break;

	//Музыка
	case "musics":
		include ADMIN_DIR.'/musics.php';
	break;

	//Альбомы
	case "albums":
		include ADMIN_DIR.'/albums.php';
	break;

	//Страны
	case "country":
		include ADMIN_DIR.'/country.php';
	break;

	//Города
	case "city":
		include ADMIN_DIR.'/city.php';
	break;

	//Список жалоб
	case "report":
		include ADMIN_DIR.'/report.php';
	break;

	//Доп. поля профилей
	case "xfields":
		include ADMIN_DIR.'/xfields.php';
	break;
	
	//Игры
	case "apps":
		include ADMIN_DIR.'/apps.php';
	break;

	//Отчеты по SMS
	case "sms":
		include ADMIN_DIR.'/sms.php';
	break;
	
	//cache
    case "cache":
		include ADMIN_DIR.'/cache.php';
    break;
		  
    //recheck
    case "recheck":
        include ADMIN_DIR.'/recheck.php';
    break; 
     
	default:
	
		include ADMIN_DIR.'/main.php';
}
?>