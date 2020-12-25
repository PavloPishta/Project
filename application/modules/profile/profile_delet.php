<?php
/* 
	Appointment: Страница удалена
	File: profile_delet.php
 
*/
if(!defined('MOZG'))
	die("Not Found");

if($user_info['user_group'] != '1'){
	$tpl->load_template('profile_deleted.html');
	$tpl->compile('main');
	echo str_replace('{theme}', '/tpl/', $tpl->result['main']);
	die();
}
?>