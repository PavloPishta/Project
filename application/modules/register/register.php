<?php

if(!defined('MOZG'))
	die('Not Found');

//Проверяем была ли нажата кнопка, если нет, то делаем редирект на главную
if(!$logged){
	NoAjaxQuery();

	//Код безопасности
	$session_sec_code = $_SESSION['sec_code'];
	$sec_code = $_POST['sec_code'];

	//Если код введные юзером совпадает, то пропускаем, иначе выводим ошибку
	if($sec_code == $session_sec_code){
		//Входные POST Данные
		$user_name = ajax_utf8(textFilter(ucfirst($_POST['name']), false, true)); 
        $user_lastname = ajax_utf8(textFilter(ucfirst($_POST['lastname']), false, true));
		$user_email = ajax_utf8(textFilter($_POST['email'], false, true));

		$user_name = ucfirst($user_name);
		$user_lastname = ucfirst($user_lastname);

		$user_sex = intval($_POST['sex']);
		if($user_sex < 0 OR $user_sex > 2) $user_sex = 0;

		$user_day = intval($_POST['day']);
		if($user_day < 0 OR $user_day > 31) $user_day = 0;

		$user_month = intval($_POST['month']);
		if($user_month < 0 OR $user_month > 12) $user_month = 0;

		$user_year = intval($_POST['year']);
		if($user_year < 1930 OR $user_year > 2007) $user_year = 0;

		$user_country = intval($_POST['country']);
		if($user_country < 0 OR $user_country > 218) $user_country = 0;		//Вслучаи добавления стран в базу данных, то количество стран вписывать сюда...

		$user_city = intval($_POST['city']);
		if($user_city < 0 OR $user_city > 17589) $user_city = 0;			//Вслучаи добавления городов в базу данных, то количество городов вписывать сюда...

		$_POST['password_first'] = ajax_utf8($_POST['password_first']);
		$_POST['password_second'] = ajax_utf8($_POST['password_second']);

		$password_first = GetVar($_POST['password_first']);
		$password_second = GetVar($_POST['password_second']);
		$user_birthday = $user_year.'-'.$user_month.'-'.$user_day;

		$errors = array();

		//Проверка имени
		if(preg_match('#^[a-zа-яё0-9]+$#ui', $user_name) AND strlen($user_name) >= 2) $errors[] = 0;

		//Проверка фамилии
		if(preg_match('#^[a-zа-яё0-9]+$#ui', $user_lastname) AND strlen($user_lastname) >= 2) $errors[] = 0; 

		//Проверка E-mail
		if(preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $user_email)) $errors[] = 0;

		//Проверка Паролей
		if(strlen($password_first) >= 6 AND $password_first == $password_second) $errors[] = 0;

		$allEr = count($errors);

		//Если нет ошибок то пропускаем и добавляем в базу
		if($allEr == 4){
			$check_email = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_email = '{$user_email}'");
			if(!$check_email['cnt']){
				$md5_pass = md5(md5($password_first));
				$user_group = '5';
				
				if($user_country > 0 or $user_city > 0){
					$country_info = $db->super_query("SELECT name FROM `".PREFIX."_country` WHERE id = '".$user_country."'");
					$city_info = $db->super_query("SELECT name FROM `".PREFIX."_city` WHERE id = '".$user_city."'");
					
					$user_country_city_name = $country_info['name'].'|'.$city_info['name'];
				}
				
				$user_search_pref = $user_name.' '.$user_lastname;
				
				//Hash ID
				$hid = $md5_pass.md5(md5($_IP));

				$db->query("INSERT INTO `".PREFIX."_users` (user_email, user_password, user_name, user_lastname, user_sex, user_day, user_month, user_year, user_country, user_city, user_reg_date, user_lastdate, user_group, user_hid, user_country_city_name, user_search_pref, user_birthday, user_privacy) VALUES ('{$user_email}', '{$md5_pass}', '{$user_name}', '{$user_lastname}', '{$user_sex}', '{$user_day}', '{$user_month}', '{$user_year}', '{$user_country}', '{$user_city}', '{$server_time}', '{$server_time}', '{$user_group}', '{$hid}', '{$user_country_city_name}', '{$user_search_pref}', '{$user_birthday}', 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||val_guests1|1||val_guests2|1||')");
				$id = $db->insert_id();

				//Устанавливаем в сессию ИД юзера
				$_SESSION['user_id'] = intval($id);
				
				$subj = 'Спасибо за регистрацию.'; 
				$message = 'Доброго вам время суток! Мы очень рады видеть Вас в социальной сети, посвященной нашей Родине - Приднестровью. В честь этого мы Вам начисляем 10 баллов за регистрацию.'; // HTML разрешен 
				$sender_id = '1'; // ID пользователя, от которого отправляем

				$db->query("INSERT INTO `".PREFIX."_messages` SET theme = '{$subj}', text = '{$message}', for_user_id = '{$id}', from_user_id = '{$sender_id}', date = '{$server_time}', pm_read = 'no', folder = 'inbox', history_user_id = '{$id}'");  
				$db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num+1 WHERE user_id = '{$id}'");  

				//Записываем COOKIE
				set_cookie("user_id", intval($id), 365);
				set_cookie("password", md5(md5($password_first)), 365);
				set_cookie("hid", $hid, 365);
				
				//Создаём папку юзера в кеше
				mozg_create_folder_cache("user_{$id}");
				
				//Директория юзеров
				$uploaddir = ROOT_DIR.'/uploads/users/';
	
				@mkdir($uploaddir.$id, 0777);
				@chmod($uploaddir.$id, 0777);
				@mkdir($uploaddir.$id.'/albums', 0777);
				@chmod($uploaddir.$id.'/albums', 0777);

				//Если юзер регался по реф ссылки, то начисляем рефералу 10 балов
				if($_SESSION['ref_id']){
					//Проверям на накрутку балов, что юзер не сам регистрирует анкеты
					$check_ref = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_log` WHERE ip = '{$_IP}'");
					if(!$check_ref['cnt']){
						$ref_id = intval($_SESSION['ref_id']);
						
						//Даём рефералу +10 балов
						$db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance+10 WHERE user_id = '{$ref_id}'");
						$db->query("INSERT INTO `".PREFIX."_historytab` SET user_id = '{$ref_id}', for_user_id = '{$id}', title='', type = '2', price='10', status = '+', date = '{$server_time}'");

						//Вставялем рефералу ид регистратора
						$db->query("INSERT INTO `".PREFIX."_invites` SET uid = '{$ref_id}', ruid = '{$id}'");
					}
				}

//При регистрации +4 балов		
$db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance+9");


				//Вставляем лог в бд
				$db->query("INSERT INTO `".PREFIX."_log` SET uid = '{$id}', browser = '{$_BROWSER}', ip = '{$_IP}'");

				echo 'ok|'.$id;
			} else
				echo 'err_mail|';
		} else
			echo 'no_val';
	}
	die();
}
?>