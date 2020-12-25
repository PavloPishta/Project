<?

if(!defined('MOZG'))
	die('Not Found');

	
@include APPLICATION_DIR.'/data/config.php';

if(!$config['home_url']) die("TO Not installed");

include APPLICATION_DIR.'/classes/mysql.php';
include APPLICATION_DIR.'/data/db.php';
include APPLICATION_DIR.'/classes/tpl.php';
if($config['gzip'] == 'yes') include APPLICATION_DIR.'/modules/gzip/gzip.php';
include APPLICATION_DIR.'/lang/Russian/site.lng';
include APPLICATION_DIR.'/modules/functions/functions.php';

if($_GET['ver'] == 'm'){
$_SESSION['user_mobile'] = 1;
header("Location: /?go=main");
}

if($_GET['ver'] == 'f'){
$_SESSION['user_mobile'] = 0;
header("Location: /");
}

if($_SESSION['user_mobile'] == 1){
$tpl = new mozg_template;
$tpl->dir = ROOT_DIR.'/html/mobile/';
define('TEMPLATE_DIR', $tpl->dir);
if($_GET['go'] == '' AND $_GET['act'] != 'logout'){ header("Location: /?go=main"); exit; }
} else {

$tpl = new mozg_template;
$tpl->dir = ROOT_DIR.'/html/';
define('TEMPLATE_DIR', $tpl->dir);

}


$_DOCUMENT_DATE = false;
$Timer = new microTimer();
$Timer->start();

$server_time = intval($_SERVER['REQUEST_TIME']);

include APPLICATION_DIR.'/modules/login/login.php';

if($config['offline'] == "yes") include APPLICATION_DIR . '/modules/offline/offline.php';
if($user_info['user_delet']) include APPLICATION_DIR . '/modules/profile/profile_delet.php';
$sql_banned = $db->super_query("SELECT * FROM ".PREFIX."_banned", true, "banned", true);
if(isset($sql_banned)) $blockip = check_ip($sql_banned); else $blockip = false;
if($user_info['user_ban_date'] >= $server_time OR $user_info['user_ban_date'] == '0' OR $blockip) include APPLICATION_DIR . '/modules/profile/profile_ban.php';

//Елси юзер залогинен то обновляем последнюю дату посещения в таблице друзей и на личной стр
if($logged){
	//Начисления 1 балов.
	if(!$user_info['user_lastupdate']) $user_info['user_lastupdate'] = 1;

	if(date('Y-m-d', $user_info['user_lastupdate']) < date('Y-m-d', $server_time))
		$sql_balance = ", user_balance = user_balance+1, user_lastupdate = '{$server_time}'";

	$db->query("UPDATE LOW_PRIORITY `".PREFIX."_users` SET user_last_visit = '{$server_time}' {$sql_balance} WHERE user_id = '{$user_info['user_id']}'");
	//$db->query("INSERT INTO `".PREFIX."_historytab` SET user_id = '{$user_info['user_id']}', type = '3', price='1', status = '+', date = '{$server_time}'");
}

//Настройки групп пользователей
$user_group = unserialize(serialize(array(
							1 => array( #Администрация
								'addnews' => '1', 
							),
							2 => array( #Главный модератор
								'addnews' => '0', 
							),
							3 => array( #Модератор
								'addnews' => '0', 
							),
							4 => array( #Техподдержка
								'addnews' => '0', 
							), 
							5 => array( #Пользователи
								'addnews' => '0', 
							),
						)));

//Время онлайна
$online_time = $server_time - $config['online_time'];

//Загрузка модулей

include APPLICATION_DIR.'/lib.php';

?>