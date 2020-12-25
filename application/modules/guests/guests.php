<?php
/* 
	Appointment: Гости пользователя
	File: guests.php 
 
*/
if(!defined('MOZG'))
	die('Not Found');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$act = $_GET['act'];
	$metatags['title'] = 'Гости';

	if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
	$gcount = 20;
	$limit_page = ($page-1)*$gcount;
				
	switch($act){

		//################### Чистим счетчик гостей ###################//
		case "clear_history":
			$user_id = intval($user_info['user_id']);
                $sql_guests = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_guests FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			if($sql_guests){
				$db->query("UPDATE LOW_PRIORITY `".PREFIX."_users` SET user_guests = '' WHERE user_id = '{$user_id}'");	
            }

			//Выводим сообщение о завершении процеса очистки истории.
			msgbox('', ' Очистка истории  кто был на вашей странице, успешно выполнена.. <br /><a href="/news" onclick="Page.Go(this.href); return false;">Перейти на главную?.</a>', 'info_2');

			mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
			mozg_clear_cache();
			
			break;
		default:

				//################### Просмотр всех друзей ###################//
				$get_user_id = intval($_GET['user_id']);
				if(!$get_user_id)
					$get_user_id = intval($user_info['user_id']);
					
				$sql_guests = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_name, alias, user_guests FROM `".PREFIX."_users` WHERE user_id = '{$get_user_id}'");
			        
					if($sql_guests){
                        $gram_name = gramatikName($sql_guests['user_name']);
                        $tpl->load_template('guests/head.html');
                        $tpl->set('{name}', $gram_name);

					//Оригинальный (id) - пользователя.
					$tpl->set('{user-id-original}', $get_user_id);
						
					//Замена (id) - на унекальное имя (aliast).
					if($sql_guests['alias']){
						$tpl->set('{user-id}', $sql_guests['alias']); 
					} else {
						$tpl->set('{user-id}', 'id'.$get_user_id);
					}

					if($get_user_id == $user_info['user_id']){
						$tpl->set('[owner]', '');
						$tpl->set('[/owner]', '');
						$tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

					} else {
						$tpl->set('[not-owner]', '');
						$tpl->set('[/not-owner]', '');
						$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

					}
					
					// Вывод панели навигацыи.
					$tpl->compile('info');

					// Если запись в таблице (user_guests) - (false) - пуста, то выводим сообщение об етом.
					$guests_num = count(array_unique(explode('|',$sql_guests['user_guests']))) - 1;
					if($guests_num == false){
					msgbox('', 'Еще никто не заходил на вашу страницу!', 'info_2');
					}

					$guests_arr = array_unique(explode('|',$sql_guests['user_guests']));
                    foreach($guests_arr as $guest_id) {		
							$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_id, user_country_city_name, user_search_pref, user_birthday, user_photo, user_status, user_last_visit, alias FROM `".PREFIX."_users` WHERE user_id = '{$guest_id}' ORDER by rand() DESC LIMIT {$limit_page}, {$gcount}", 1);

						if($sql_){
							$tpl->load_template('guests/guests.html');
							foreach($sql_ as $row){
								$user_country_city_name = explode('|', $row['user_country_city_name']);
								$tpl->set('{country}', $user_country_city_name[0]);
									
								if($user_country_city_name[1])
									$tpl->set('{city}', ', '.$user_country_city_name[1]);
								else
									$tpl->set('{city}', '');
										
								$tpl->set('{user-id}', $row['user_id']);

								//Оригинальный (id) - пользователя.
								$tpl->set('{user-id-original}', $row['user_id']);
					
								//Замена (id) - на унекальное имя (aliast).
								if($row['alias']){
									$tpl->set('{user-id}', $row['alias']); 
								} else {
									$tpl->set('{user-id}', 'id'.$row['user_id']);
								}

								//Выводимстатус пользователя, если нету статус то выводим, место прожывания.
								if($row['user_status']){
									$tpl->set('{guest_status}', stripslashes(substr($row['user_status'], 0, 100)));
								} else {
									$country_city = explode('|', $row['user_country_city_name']);
									$tpl->set('{guest_status}', $country_city[1]);
								}

								$tpl->set('{name}', $row['user_search_pref']);
									
								if($row['user_photo'])
									$tpl->set('{ava}', $config['home_url'].'/uploads/users/'.$row['user_id'].'/100_'.$row['user_photo']);
								else
									$tpl->set('{ava}', '/images/no_ava.gif');
								
								if($row['user_last_visit'] >= $online_time)
									$tpl->set('{online}', $lang['online']);
								else
									$tpl->set('{online}', '');
								
								//Возраст юзера
								$user_birthday = explode('-', $row['user_birthday']);
								$tpl->set('{age}', user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]));
	
								if($row['user_id'] == $user_info['user_id'])
									$tpl->set_block("'\\[viewer\\](.*?)\\[/viewer\\]'si","");
								else {
									$tpl->set('[viewer]', '');
									$tpl->set('[/viewer]', '');
                                if($get_user_id == $user_info['user_id']){
					                $tpl->set('[owner]', '');
					                $tpl->set('[/owner]', '');
					                $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                } else {
					                $tpl->set('[not-owner]', '');
					                $tpl->set('[/not-owner]', '');
					                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

				                }}

								$tpl->compile('content');
							}
							navigation($gcount, $guests_num, $config['home_url'].'guests/'.$get_user_id.'/page/');
						}

					}

				 }
	}
	
	$db->free();
	$tpl->clear();
	
} else {
	$user_fm_wrap_bar = 'Информация';
	msgbox('', $lang['not_logged'], 'info');
}
?>
