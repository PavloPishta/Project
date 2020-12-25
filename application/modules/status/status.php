<?php

if(!defined('MOZG'))
	die('Not Found');

NoAjaxQuery();

if($logged){

	$user_id = $user_info['user_id'];
	$text = ajax_utf8(textFilter($_POST['text'], false, true));
	$public_id = intval($_POST['public_id']);
	
	//Если обновляем статус группы
	if($_GET['act'] == 'public'){
		
		$row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
		
		if(stripos($row['admin'], "id{$user_id}|") !== false){
		
			$db->query("UPDATE `".PREFIX."_communities` SET status_text = '{$text}' WHERE id = '{$public_id}'");
			mozg_clear_cache_folder('groups');
		
		}
	
	//Если пользователь
	} else {
	
		$db->query("UPDATE `".PREFIX."_users` SET user_status = '{$text}' WHERE user_id = '{$user_id}'");
		
		//Чистим кеш
		mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
		mozg_clear_cache();
		
	}
	
	echo htmlspecialchars_decode(stripslashes(textFilter(ajax_utf8($_POST['text']))));
	$db->query("INSERT INTO `".PREFIX."_statuses` SET uid = '{$user_info['user_id']}', date = '{$server_time}', text = '{$text}'");
}

die();
?>