<?php
/* 
	Appointment: Страница заблокирована
	File: profile_ban.php
 
*/
if(!defined('MOZG'))
	die("Not Found");

	$tpl->load_template('profile_baned.html');
	if($user_info['user_ban_date'])
		$tpl->set('{date}', langdate('j F Y в H:i', $user_info['user_ban_date']));
	else
		$tpl->set('{date}', 'Неограниченно');
	$tpl->compile('main');
	echo str_replace('{theme}', '/tpl/', $tpl->result['main']);
	die();
?>