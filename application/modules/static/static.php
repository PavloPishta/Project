<?php
/* 
	Appointment: Статические страницы
	File: static.php 
 
*/
if(!defined('MOZG'))
	die('Not Found');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$alt_name = $db->safesql(totranslit($_GET['page']));
	$row = $db->super_query("SELECT title, text FROM `".PREFIX."_static` WHERE alt_name = '".$alt_name."'");
	if($row){
		$tpl->load_template('static.html');
		$tpl->set('{alt_name}', $alt_name);
		$tpl->set('{title}', stripslashes($row['title']));
		$tpl->set('{text}', stripslashes($row['text']));
		$tpl->compile('content');
	} else
		msgbox('', 'Страница не найдена.', 'info_2');
	
	$tpl->clear();
	$db->free();
} else {
	$user_fm_wrap_bar = $lang['no_infooo'];
	msgbox('', $lang['not_logged'], 'info');
}
?>