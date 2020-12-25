<?php
/* 
	Appointment: Временное отключение сайта
	File: offline.php
 
*/
if(!defined('MOZG'))
	die("Not Found");

if($user_info['user_group'] != '1'){
	$tpl->load_template('offline.html');
	$config['offline_msg'] = str_replace('&quot;', '"', stripslashes($config['offline_msg']));
	$tpl->set('{reason}', nl2br($config['offline_msg']));
	$tpl->compile('main');
	echo $tpl->result['main'];
	die();
}
?>