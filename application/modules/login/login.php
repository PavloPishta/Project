<?php
/* 
	Appointment: Авторизация пользователей
	File: login.php 
 
*/
if(!defined('MOZG'))
	die('Not Found');

header('Content-type: text/html; charset=utf-8');

$_IP = $db->safesql($_SERVER['REMOTE_ADDR']);
$_BROWSER = $db->safesql($_SERVER['HTTP_USER_AGENT']);

//Если делаем выход
if(isset($_GET['act']) AND $_GET['act'] == 'logout'){
	set_cookie("user_id", "", 0);
	set_cookie("password", "", 0);
	set_cookie("hid", "", 0);
	unset($_SESSION['user_id']);
	@session_destroy();
	@session_unset();
	$logged = false;
	$user_info = array();
	header('Location: /');
	die();
}

//Если есть данные сесии
if(isset($_SESSION['user_id']) > 0){
	$logged = true;
	$logged_user_id = intval($_SESSION['user_id']);
	$user_info = $db->super_query("SELECT user_id, user_email, user_group, user_new_groups, user_friends_demands, user_pm_num, user_support, user_lastupdate, user_photo, user_delet, user_ban_date, user_new_mark_photos, user_search_pref, user_img_fon, alias FROM `".PREFIX."_users` WHERE user_id = '".$logged_user_id."'");

	//Если есть данные о сесии, но нет инфы о юзере, то выкидываем его
	if(!$user_info['user_id'])
		header('Location: /index.php?act=logout');

	//Если юзер нажимает "Главная" то скидываем на его стр.
	$host_site = $_SERVER['QUERY_STRING'];
	if($logged AND !$host_site)
		header('Location: /id'.$user_info['user_id']);

//Если есть данные о COOKIE то проверяем
} elseif(isset($_COOKIE['user_id']) > 0 AND $_COOKIE['password'] AND $_COOKIE['hid']){
	$cookie_user_id = intval($_COOKIE['user_id']);
	$user_info = $db->super_query("SELECT user_id, user_email, user_group, user_new_groups, user_password, user_hid, user_friends_demands, user_pm_num, user_support, user_lastupdate, user_photo, user_delet, user_ban_date, user_new_mark_photos, user_search_pref, user_img_fon, alias FROM `".PREFIX."_users` WHERE user_id = '".$cookie_user_id."'");
	
	//Если пароль и HID совпадает то пропускаем
	if($user_info['user_password'] == $_COOKIE['password'] AND $user_info['user_hid'] == $_COOKIE['password'].md5(md5($_IP))){
		$_SESSION['user_id'] = $user_info['user_id'];
		
		//Вставляем лог в бд
		$db->query("UPDATE `".PREFIX."_log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$user_info['user_id']."'");
		
		//Удаляем все рание события
		$db->query("DELETE FROM `".PREFIX."_updates` WHERE for_user_id = '{$user_info['user_id']}'");
				
		$logged = true;
	} else {
		$user_info = array();
		$logged = false;
	}
	
	//Если юзер нажимает "Главная" то скидываем на его стр.
	$host_site = $_SERVER['QUERY_STRING'];
	if($logged AND !$host_site)
		header('Location: /id'.$user_info['user_id']);
	
} else {
	$user_info = array();
	$logged = false;
}

//Если данные поступили через пост и пользователь не авторизован
if(isset($_POST['log_in']) AND !$logged){

	//Приготавливаем данные
	$email = textFilter(strip_tags($_POST['email']));

	$password = md5(md5(GetVar($_POST['password'])));
	
	//Проверяем правильность e-mail
	if(!preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $email)){
		header('Location: /login/error');
	} else {
		//Считаем кол-во символов в пароле и email
		if(isset($email) AND !empty($email)){
			$check_user = $db->super_query("SELECT user_id FROM `".PREFIX."_users` WHERE user_email = '".$email."' AND user_password = '".$password."'");
				
			//Если есть юзер то пропускаем
			if($check_user){
				//Hash ID
				$hid = $password.md5(md5($_IP));
					
				//Обновляем хэш входа
				$db->query("UPDATE `".PREFIX."_users` SET user_hid = '".$hid."' WHERE user_id = '".$check_user['user_id']."'");
					
				//Удаляем все рание события
				$db->query("DELETE FROM `".PREFIX."_updates` WHERE for_user_id = '{$check_user['user_id']}'");
	
				//Устанавливаем в сессию ИД юзера
				$_SESSION['user_id'] = intval($check_user['user_id']);
					
				//Записываем COOKIE
				set_cookie("user_id", intval($check_user['user_id']), 365);
				set_cookie("password", $password, 365);
				set_cookie("hid", $hid, 365);

				//Вставляем лог в бд
				$db->query("UPDATE `".PREFIX."_log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$check_user['user_id']."'");
				
				header('Location: /');
			} else
				header('Location: /login/error');
		} else
			header('Location: /login/error');
	}
}
?>