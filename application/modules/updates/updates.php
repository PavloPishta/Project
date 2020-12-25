<?php
/* 
	Appointment: Моментальные оповещания
	File: updates.php 
 
*/
if(!defined('MOZG'))
	die('Not Found');

NoAjaxQuery();

if($logged){

	$user_id = $user_info['user_id'];
	
	$cntCacheUp = mozg_cache("user_{$user_id}/updates");
	
	if($cntCacheUp){
	
		$update_time = $server_time - 70;
		
		$row = $db->super_query("SELECT id, type, from_user_id, text, lnk, user_search_pref, user_photo, onk FROM `".PREFIX."_updates` WHERE for_user_id = '{$user_id}' AND date > '{$update_time}' ORDER by `date` ASC");
		
		if($row){
		
			if($row['user_photo']) $ava = "/uploads/users/{$row['from_user_id']}/50_{$row['user_photo']}";
			else $ava = "/images/no_ava_50.png";
            if(!empty($row['lnk'])){
                $link = 'onClick="GoPage(event, \''.$row['lnk'].'\');';
            } else {
                $lnk = explode('|', $row['onk']);
                $link = 'onClick="im_chat.opens(\''.$lnk[0].'\',\''.$lnk[1].'\',\'fc_contact_status\');';
            }
			
			echo $row['type'].'|'.$row['user_search_pref'].'|'.$row['from_user_id'].'|'.stripslashes($row['text']).'|'.$server_time.'|'.$ava.'|'.$link;
					
			$db->query("DELETE FROM `".PREFIX."_updates` WHERE id = '{$row['id']}'");
			
		} else
		
			mozg_create_cache("user_{$user_id}/updates", '');
	
	}

}

die();
?>