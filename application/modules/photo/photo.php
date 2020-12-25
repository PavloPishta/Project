<?php
/* 
	Appointment: Просмотр фотографии
	File: photo.php 
 
*/
if(!defined('MOZG'))
	die('Not Found');

if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];
	$rate_num = 3;
	$balanc = $db->super_query("SELECT user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
	$num = $balanc['user_balance']-1;
	$balance = $balanc['user_balance'];

	switch($act){
	
		//################### Добавления комментария ###################//
		case "addcomm":
			NoAjaxQuery();
			$pid = intval($_POST['pid']);
			$comment = ajax_utf8(textFilter($_POST['comment']));
			$date = date('Y-m-d H:i:s', $server_time);
			$hash = md5($user_id.$server_time.$_IP.$user_info['user_email'].rand(0, 1000000000)).$comment.$pid;
			
			$check_photo = $db->super_query("SELECT album_id, user_id, photo_name FROM `".PREFIX."_photos` WHERE id = '{$pid}'");

			//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
			if($user_info['user_id'] != $check_photo['user_id']){
				$check_friend = CheckFriends($check_photo['user_id']);
				
				$row_album = $db->super_query("SELECT privacy FROM `".PREFIX."_albums` WHERE aid = '{$check_photo['album_id']}'");
				$album_privacy = explode('|', $row_album['privacy']);
			}
				
			//ЧС
			$CheckBlackList = CheckBlackList($check_photo['user_id']);
			
			//Проверка на существование фотки и приватность
			if(!$CheckBlackList AND $check_photo AND $album_privacy[1] == 1 OR $album_privacy[1] == 2 AND $check_friend OR $user_info['user_id'] == $check_photo['user_id']){
				$db->query("INSERT INTO `".PREFIX."_photos_comments` (pid, user_id, text, date, hash, album_id, owner_id, photo_name) VALUES ('{$pid}', '{$user_id}', '{$comment}', '{$date}', '{$hash}', '{$check_photo['album_id']}', '{$check_photo['user_id']}', '{$check_photo['photo_name']}')");
				$id = $db->insert_id();
				$db->query("UPDATE `".PREFIX."_photos` SET comm_num = comm_num+1 WHERE id = '{$pid}'");
				$db->query("UPDATE `".PREFIX."_albums` SET comm_num = comm_num+1 WHERE aid = '{$check_photo['album_id']}'");

				$date = langdate('сегодня в H:i', $server_time);
				$tpl->load_template('photo_comment.html');
				$tpl->set('{author}', $user_info['user_search_pref']);
				$tpl->set('{comment}', stripslashes($comment));
				$tpl->set('{uid}', $user_id);
				$tpl->set('{hash}', $hash);
				$tpl->set('{id}', $id);
				
				if($user_info['user_photo'])
					$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
				else
					$tpl->set('{ava}', '/images/no_ava_50.png');
				
				$tpl->set('{online}', $lang['online']);
				$tpl->set('{date}', langdate('сегодня в H:i', $server_time));
				$tpl->set('[owner]', '');
				$tpl->set('[/owner]', '');
				$tpl->compile('content');
				
				//Добавляем действие в ленту новостей "ответы" владельцу фотографии
				if($user_id != $check_photo['user_id']){
					$comment = str_replace("|", "&#124;", $comment);
					$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 8, action_text = '{$comment}|{$check_photo['photo_name']}|{$pid}|{$check_photo['album_id']}', obj_id = '{$id}', for_user_id = '{$check_photo['user_id']}', action_time = '{$server_time}'");

					//Вставляем событие в моментальные оповещания
					$row_userOW = $db->super_query("SELECT user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$check_photo['user_id']}'");
					$update_time = $server_time - 70;
									
					if($row_userOW['user_last_visit'] >= $update_time){
									
						$db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$check_photo['user_id']}', from_user_id = '{$user_id}', type = '2', date = '{$server_time}', text = '{$comment}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/photo{$check_photo['user_id']}_{$pid}_{$check_photo['album_id']}'");
									
						mozg_create_cache("user_{$check_photo['user_id']}/updates", 1);
						
					//ИНАЧЕ Добавляем +1 юзеру для оповещания				
					} else {
					
						//Добавляем +1 юзеру для оповещания
						$cntCacheNews = mozg_cache('user_'.$check_photo['user_id'].'/new_news');
						mozg_create_cache('user_'.$check_photo['user_id'].'/new_news', ($cntCacheNews+1));
					
					}
									
					//Отправка уведомления на E-mail
					if($config['news_mail_4'] == 'yes'){
						$rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$check_photo['user_id']."'");
						if($rowUserEmail['user_email']){
							include_once APPLICATION_DIR.'/classes/mail.php';
							$mail = new dle_mail($config);
							$rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
							$rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '4'");
							$rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
							$rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
							$rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'photo'.$check_photo['user_id'].'_'.$vid.'_'.$check_photo['album_id'], $rowEmailTpl['text']);
							$mail->send($rowUserEmail['user_email'], 'Новый комментарий к Вашей фотографии', $rowEmailTpl['text']);
						}
					}
				}
				
				//Чистим кеш кол-во комментов
				mozg_mass_clear_cache_file("user_{$check_photo['user_id']}/albums_{$check_photo['user_id']}_comm|user_{$check_photo['user_id']}/albums_{$check_photo['user_id']}_comm_all|user_{$check_photo['user_id']}/albums_{$check_photo['user_id']}_comm_friends");

				AjaxTpl();
			} else
				echo 'err_privacy';
		break;
		
		//################### Удаление комментария ###################//
		case "del_comm":
			NoAjaxQuery();
			$comm_id = intval($_POST['comm_id']);
			$check_comment = $db->super_query("SELECT id, pid, album_id, owner_id FROM `".PREFIX."_photos_comments` WHERE id = '{$comm_id}'");
			if($check_comment){
				$db->query("DELETE FROM `".PREFIX."_photos_comments` WHERE id = '{$comm_id}'");
				$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$check_comment['id']}' AND action_type = 8");
				$db->query("UPDATE `".PREFIX."_photos` SET comm_num = comm_num-1 WHERE id = '{$check_comment['pid']}'");
				$db->query("UPDATE `".PREFIX."_albums` SET comm_num = comm_num-1 WHERE aid = '{$check_comment['album_id']}'");

				//Чистим кеш кол-во комментов
				mozg_mass_clear_cache_file("user_{$check_comment['owner_id']}/albums_{$check_comment['owner_id']}_comm|user_{$check_comment['owner_id']}/albums_{$check_comment['owner_id']}_comm_all|user_{$check_comment['owner_id']}/albums_{$check_comment['owner_id']}_comm_friends");
				}
			die();
		break;

		//################### Помещение фотографии на свою страницу ###################//
		case "crop":
			NoAjaxQuery();
			$pid = intval($_POST['pid']);
			$i_left = intval($_POST['i_left']);
			$i_top = intval($_POST['i_top']);
			$i_width = intval($_POST['i_width']);
			$i_height = intval($_POST['i_height']);
			$check_photo = $db->super_query("SELECT photo_name, album_id FROM `".PREFIX."_photos` WHERE id = '{$pid}' AND user_id = '{$user_id}'");
			if($check_photo AND $i_width >= 100 AND $i_height >= 100 AND $i_left >= 0 AND $i_height >= 0){
				$imgInfo = explode('.', $check_photo['photo_name']);
				$newName = substr(md5($server_time.$check_photo['check_photo']), 0, 15).".".$imgInfo[1];
				$newDir = ROOT_DIR."/uploads/users/{$user_id}/";
				
				include APPLICATION_DIR.'/classes/images.php';
				
				//Создаём оригинал
				$tmb = new thumbnail(ROOT_DIR."/uploads/users/{$user_id}/albums/{$check_photo['album_id']}/{$check_photo['photo_name']}");
				$tmb->size_auto($i_width."x".$i_height, 0, "{$i_left}|{$i_top}");
				$tmb->jpeg_quality(90);
				$tmb->save($newDir."o_{$newName}");
				
				//Создание главной фотографии
				$tmb = new thumbnail($newDir."o_{$newName}");
				$tmb->size_auto(200, 1);
				$tmb->jpeg_quality(100);
				$tmb->save($newDir.$newName);
				
				//Создание уменьшеной копии 50х50
				$tmb = new thumbnail($newDir."o_{$newName}");
				$tmb->size_auto('50x50');
				$tmb->jpeg_quality(100);
				$tmb->save($newDir.'50_'.$newName);
				
				//Создание уменьшеной копии 100х100
				$tmb = new thumbnail($newDir."o_{$newName}");
				$tmb->size_auto('100x100');
				$tmb->jpeg_quality(100);
				$tmb->save($newDir.'100_'.$newName);

				//Добавляем на стену
				$row = $db->super_query("SELECT user_sex FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
				if($row['user_sex'] == 2)
					$sex_text = 'обновила';
				else
					$sex_text = 'обновил';
						
				$wall_text = "<div class=\"profile_update_photo\"><a href=\"\" onClick=\"Photo.Profile(\'{$user_id}\', \'{$newName}\'); return false\"><img src=\"/uploads/users/{$user_id}/o_{$newName}\" style=\"margin-top:3px\"></a></div>";
						
				$db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', type = '{$sex_text} фотографию на странице:'");
				$dbid = $db->insert_id();
						
				$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");
						
				//Добавляем в ленту новостей
				$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$server_time}'");
						
				//Обновляем имя фотки в бд
				$db->query("UPDATE `".PREFIX."_users` SET user_photo = '{$newName}', user_wall_id = '{$dbid}' WHERE user_id = '{$user_id}'");
				
				mozg_clear_cache_file("user_{$user_id}/profile_{$user_id}");
				mozg_clear_cache();
			}
			die();
		break;
		
		//################### Показ всех комментариев ###################//
		case "all_comm":
			NoAjaxQuery();
			$pid = intval($_POST['pid']);
			$num = intval($_POST['num']);
			if($num > 7){
					$limit = $num-3;
					$sql_comm = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id,text,date,id,hash,pid, tb2.user_search_pref, user_photo, user_last_visit FROM `".PREFIX."_photos_comments` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.pid = '{$pid}' ORDER by `date` ASC LIMIT 0, {$limit}", 1);
					
					$tpl->load_template('photo_comment.html');
					foreach($sql_comm as $row_comm){
						$tpl->set('{comment}', stripslashes($row_comm['text']));
						$tpl->set('{uid}', $row_comm['user_id']);
						$tpl->set('{id}', $row_comm['id']);
						$tpl->set('{hash}', $row_comm['hash']);
						$tpl->set('{author}', $row_comm['user_search_pref']);
						
						if($row_comm['user_photo'])
							$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['user_id'].'/50_'.$row_comm['user_photo']);
						else
							$tpl->set('{ava}', '/images/no_ava_50.png');
						

						OnlineTpl($row_comm['user_last_visit']);
						megaDate(strtotime($row_comm['date']));
							
						$row_photo = $db->super_query("SELECT user_id FROM `".PREFIX."_photos` WHERE id = '{$row_comm['pid']}'");
						
						if($row_comm['user_id'] == $user_info['user_id'] OR $row_photo['user_id'] == $user_info['user_id']){
							$tpl->set('[owner]', '');
							$tpl->set('[/owner]', '');
						} else
							$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
						$tpl->compile('content');
					}
				AjaxTpl();
			}
		break;
		
		//################### Просмотр ПРОСТОЙ фотографии не из альбома ###################//
		case "profile":
			$uid = intval($_POST['uid']);
			
			if($_POST['type'])
				$photo = ROOT_DIR."/uploads/attach/{$uid}/c_{$_POST['photo']}";
			else
				$photo = ROOT_DIR."/uploads/users/{$uid}/o_{$_POST['photo']}";
			
			if(file_exists($photo)){
				$tpl->load_template('photos/photo_profile.html');
				$tpl->set('{uid}', $uid);
				if($_POST['type'])
					$tpl->set('{photo}', "/uploads/attach/{$uid}/{$_POST['photo']}");
				else
					$tpl->set('{photo}', "/uploads/users/{$uid}/o_{$_POST['photo']}");
				$tpl->set('{close-link}', $_POST['close_link']);
				$tpl->compile('content');
				AjaxTpl();
			} else
				echo 'no_photo';
		break;
		
		//################### Поворот фотографии ###################//
		case "rotation":
			$id = intval($_POST['id']);
			$row = $db->super_query("SELECT photo_name, album_id, user_id FROM `".PREFIX."_photos` WHERE id = '".$id."'");
			
			if($row['photo_name'] AND $_POST['pos'] == 'left' OR $_POST['pos'] == 'right' AND $user_id == $row['user_id']){
				$filename = ROOT_DIR.'/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/'.$row['photo_name'];

				if($_POST['pos'] == 'right') $degrees = -90;
				if($_POST['pos'] == 'left') $degrees = 90;

				$source = imagecreatefromjpeg($filename);
				$rotate = imagerotate($source, $degrees, 0);

				imagejpeg($rotate, ROOT_DIR.'/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/'.$row['photo_name'], 93);

				//Подключаем класс для фотографий
				include APPLICATION_DIR.'/classes/images.php';
				
				//Создание маленькой копии
				$tmb = new thumbnail(ROOT_DIR.'/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/'.$row['photo_name']);
				$tmb->size_auto('140x100');
				$tmb->jpeg_quality('100');
				$tmb->save(ROOT_DIR.'/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/c_'.$row['photo_name']);
								
				echo '/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/'.$row['photo_name'];
			}
		break;

		case "allrating":
			$limit_rate = 6;
			$page_cnt = intval($_POST['page_cnt'])*$limit_rate;
			$bc = intval($_POST['bc']);

			$sql_rate = $db->super_query("SELECT tb1.author_eval_user_id, tb1.id, tb1.eval, tb1.date, tb2.user_name, tb2.user_lastname, tb2.user_photo FROM `".PREFIX."_photos_eval` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_photo = '{$bc}' and tb1.author_eval_user_id = tb2.user_id ORDER BY date DESC LIMIT {$page_cnt},{$limit_rate}",1);
				
				$tpl->load_template('photo_view_rating.html');
				$tpl->set('{id}', $bc);
				if($sql_rate){
						$c = 0;
						foreach($sql_rate as $sql_list){
							if($sql_list['user_photo']){
								$ava = '/uploads/users/'.$sql_list['author_eval_user_id'].'/50_'.$sql_list['user_photo'];
							}else{
								$ava = '/images/no_ava_50.png';
							}
							
							if($sql_list['eval']==1) $back = 'rating3';
							elseif($sql_list['eval']==6) $back = 'rating2';
							else $back = '';
							
							if($sql_list['eval'] == 6) $eval = '5+';
							else $eval = $sql_list['eval'];
							
							$temp_list.='
								<div class="rate_block" id="rate_block'.$sql_list['id'].'">
									<a href="/id'.$sql_list['author_eval_user_id'].'" onClick="Page.Go(this.href); return false"><img src="'.$ava.'" width="50" height="50" /></a>
									<a href="/id'.$sql_list['author_eval_user_id'].'" onClick="Page.Go(this.href); return false"><b>'.$sql_list['user_name'].' '.$sql_list['user_lastname'].'</b></a>
									<div class="profile_ratingview" style="margin: 5px 10px 0px 0px"><div class="rating '.$back.'" style="margin: 0px 0px 0px 0px">'.$eval.'</div></div>
									<div class="rate_date">'.megaDateNoTpl($sql_list['date']).', <a class="cursor_pointer" onClick="Photo.deleval('.$bc.','.$sql_list['id'].')">удалить оценку</a></div>
								</div>'."\n";
							$c++;
						}
						
						if($c > 5 and !$_POST['page_cnt']){
							$tpl->set('{users}', $temp_list);
							$tpl->set('[prev]', '');
							$tpl->set('[/prev]', '');
							$tpl->compile('content');
						} else if($c < 5 and !$_POST['page_cnt']){
							$tpl->set('{users}', $temp_list);
							$tpl->set_block("'\\[prev\\](.*?)\\[/prev\\]'si","");
							$tpl->compile('content');
						}
				} else {
					if(!isset($_POST['page_cnt'])) {
						$tpl->set('{users}', 'У фотографии пока нету оценок.');
						$tpl->set_block("'\\[prev\\](.*?)\\[/prev\\]'si","");
						$tpl->compile('content');
					}
				}
				
				if(isset($_POST['page_cnt'])) {
					echo $temp_list;
				}

			AjaxTpl();
		break;
		
		case "addrating":
			$eval = intval($_POST['eval']);
			$bc = intval($_POST['bc']);

			$check_album = $db->super_query("SELECT id FROM `".PREFIX."_photos_eval` WHERE author_eval_user_id = '{$user_id}' and for_photo = '{$bc}'");
			$user_balance = $db->super_query("SELECT user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
			if(!$check_album) {
				if($eval<=0 and $eval>6) $eval = 1;
				if($eval == 6) {
					if($user_balance >= "$rate_num") {
						$db->query("INSERT INTO `".PREFIX."_photos_eval` (author_eval_user_id,for_photo,eval,date) values('".$user_id."','".$bc."','".$eval."','".$server_time."')");
						$db->query("UPDATE `".PREFIX."_photos` SET eval = eval+1, evalplus = evalplus+1, rate = rate+5 WHERE id = '{$bc}'");
						$db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance-{$rate_num} WHERE user_id = '{$user_id}'");
						$db->query("INSERT INTO `".PREFIX."_historytab` SET user_id = '{$user_id}', title='1', for_user_id = '{$bc}', type = '9', price='{$rate_num}', status = '-', date = '{$server_time}'");
						echo "<div class='rating rating3' style='background:url(/images/rating2.png)'>5+</div>";
					}
				}
				
				if($eval != 6) {
					$db->query("INSERT INTO `".PREFIX."_photos_eval` (author_eval_user_id,for_photo,eval,date) values('".$user_id."','".$bc."','".$eval."','".$server_time."')");
					$db->query("UPDATE `".PREFIX."_photos` SET eval = eval+1, rate = rate+{$eval} WHERE id = '{$bc}'");
					if($eval == 1) echo "<div class='rating rating3' style='background:url(/images/rating3.png)'>1</div>";
					else echo "<div class='rating rating3' style='background:url(/images/rating0.png)'>".$eval."</div>";
				}
			}
			
			$check_photo = $db->super_query("SELECT album_id, user_id FROM `".PREFIX."_photos` WHERE id = '{$bc}'");
			if($user_id != $check_photo['user_id']){
			//Вставляем событие в моментальные оповещания
					$row_userOW = $db->super_query("SELECT user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$check_photo['user_id']}'");
					$update_time = $server_time - 70;
									
					if($row_userOW['user_last_visit'] >= $update_time){
									
						$db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$check_photo['user_id']}', from_user_id = '{$user_id}', type = '13', date = '{$server_time}', text = '+{$eval}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/photo{$check_photo['user_id']}_{$bc}_{$check_photo['album_id']}'");
									
						mozg_create_cache("user_{$check_photo['user_id']}/updates", 1);
			
					}
			}
			break;
		
		case "delete_eval":
			$bc = intval($_POST['bc']);

				$check_album = $db->super_query("SELECT eval FROM `".PREFIX."_photos_eval` WHERE author_eval_user_id = '{$user_id}' and for_photo = '{$bc}'");
				if($check_album) {
					if($check_album['eval'] == 6) {
						$db->query("UPDATE `".PREFIX."_photos` SET eval = eval-1, evalplus = evalplus-1, rate = rate-{$check_album['eval']} WHERE id = '{$bc}'");
					}
					if($check_album['eval'] != 6) {
						$db->query("UPDATE `".PREFIX."_photos` SET eval = eval-1, rate = rate-{$check_album['eval']} WHERE id = '{$bc}'");
					}
						$db->query("DELETE FROM `".PREFIX."_photos_eval` WHERE author_eval_user_id = '{$user_id}' and for_photo = '{$bc}'");
				}
		
		break;
		default:

			//################### Просмотр фотографии ###################//
			NoAjaxQuery();
			$user_id = intval($_POST['uid']);
			$photo_id = intval($_POST['pid']);
			$fuser = intval($_POST['fuser']);
			$section = $_POST['section'];

			//ЧС
			$CheckBlackList = CheckBlackList($user_id);
			if(!$CheckBlackList){
				//Получаем ID альбома
				$check_album = $db->super_query("SELECT album_id FROM `".PREFIX."_photos` WHERE id = '{$photo_id}'");

				//Если фотография вызвана не со стены
				if(!$fuser AND $check_album){
				
					//Проверяем на наличии файла с позициям только для этого фоток
					$check_pos = mozg_cache('user_'.$user_id.'/position_photos_album_'.$check_album['album_id']);
		 
					//Если нету, то вызываем функцию генерации
					if(!$check_pos){
						GenerateAlbumPhotosPosition($user_id, $check_album['album_id']);
						$check_pos = mozg_cache('user_'.$user_id.'/position_photos_album_'.$check_album['album_id']);
					}
						
					$position = xfieldsdataload($check_pos);
				}

				$row = $db->super_query("SELECT tb1.id, photo_name, comm_num, rate, eval, evalplus, descr, date, position, tb2.user_id, user_search_pref, user_country_city_name FROM `".PREFIX."_photos` tb1, `".PREFIX."_users` tb2 WHERE id = '{$photo_id}' AND tb1.user_id = tb2.user_id");
				
				if($row){
					//Вывод названия альбома, приватноть из БД
					$info_album = $db->super_query("SELECT name, privacy FROM `".PREFIX."_albums` WHERE aid = '{$check_album['album_id']}'");
					$album_privacy = explode('|', $info_album['privacy']);
					
					//Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
					if($user_info['user_id'] != $row['user_id'])
						$check_friend = CheckFriends($row['user_id']);

					//Приватность
					if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_info['user_id'] == $row['user_id']){
				
						//Если фотография вызвана не со стены
						if(!$fuser){
							$exp_photo_num = count(explode('||', $check_pos));
							$row_album['photo_num'] = $exp_photo_num-1;
						}

						//Выводим комментарии если они есть
						if($row['comm_num'] > 0){
							$tpl->load_template('photo_comment.html');
								
							if($row['comm_num'] > 7)
								$limit_comm = $row['comm_num']-3;
							else
								$limit_comm = 0;
								
							$sql_comm = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.user_id,text,date,id,hash, tb2.user_search_pref, user_photo, user_last_visit FROM `".PREFIX."_photos_comments` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.pid = '{$photo_id}' ORDER by `date` ASC LIMIT {$limit_comm}, {$row['comm_num']}", 1);
							foreach($sql_comm as $row_comm){
								$tpl->set('{comment}', stripslashes($row_comm['text']));
								$tpl->set('{uid}', $row_comm['user_id']);
								$tpl->set('{id}', $row_comm['id']);
								$tpl->set('{hash}', $row_comm['hash']);
								$tpl->set('{author}', $row_comm['user_search_pref']);
									
								if($row_comm['user_photo'])
									$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['user_id'].'/50_'.$row_comm['user_photo']);
								else
									$tpl->set('{ava}', '/images/no_ava_50.png');
										
								OnlineTpl($row_comm['user_last_visit']);
								megaDate(strtotime($row_comm['date']));
									
								if($row_comm['user_id'] == $user_info['user_id'] OR $row['user_id'] == $user_info['user_id']){
									$tpl->set('[owner]', '');
									$tpl->set('[/owner]', '');
								} else 
									$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
							
								$tpl->compile('comments');
							}
						}

						//Сама фотография
						$tpl->load_template('photo_view.html');
						$tpl->set('{photo}', $config['home_url'].'uploads/users/'.$row['user_id'].'/albums/'.$check_album['album_id'].'/'.$row['photo_name'].'?'.$server_time);
						$tpl->set('{descr}', stripslashes($row['descr']));
						$tpl->set('{photo-num}', $row_album['photo_num']);
						$tpl->set('{id}', $row['id']);
						$tpl->set('{aid}', $check_album['album_id']);
						$tpl->set('{album-name}', stripslashes($info_album['name']));
						$tpl->set('{uid}', $row['user_id']);
						
						$check_eval = $db->super_query("SELECT eval FROM `".PREFIX."_photos_eval` WHERE author_eval_user_id = '{$user_info['user_id']}' and for_photo = '{$row['id']}'");
							
						if($check_eval or $row['user_id'] == $user_info['user_id']) {
							if($check_eval['eval'] == 6) $tpl->set('{eval_style}', '2');
							elseif($check_eval['eval'] == 1) $tpl->set('{eval_style}', '3');
							else $tpl->set('{eval_style}', '0');
							if($check_eval['eval'] == 6) $tpl->set('{eval}', '+5');
							else $tpl->set('{eval}', $check_eval['eval']);
							$tpl->set_block("'\\[not_eval\\](.*?)\\[/not_eval\\]'si","");
						} else {
							$tpl->set('{eval_count}', '');
							$tpl->set('[not_eval]', '');
							$tpl->set('[/not_eval]', '');
							$tpl->set('{eval}', '');
						}
							
						if($row['user_id'] == $user_info['user_id']) {
							$tpl->set('{all_eval}', $row['eval']);
							$tpl->set('{five_eval}', $row['evalplus']);
							$tpl->set('{all_rate}', $row['rate']);
							$tpl->set('[eval_owner]', '');
							$tpl->set('[/eval_owner]', '');
							$tpl->set_block("'\\[eval_notowner\\](.*?)\\[/eval_notowner\\]'si","");
						} else {
							$tpl->set_block("'\\[eval_owner\\](.*?)\\[/eval_owner\\]'si","");
							$tpl->set('[eval_notowner]', '');
							$tpl->set('[/eval_notowner]', '');
						}
							
						//Составляем адрес строки который будет после закрытия и опридиляем секцию
						if($section == 'all_comments'){
							$tpl->set('{close-link}', '/albums/comments/'.$row['user_id']);
							$tpl->set('{section}', '_sec=all_comments');
						} elseif($section == 'album_comments'){
							$tpl->set('{close-link}', '/albums/view/'.$check_album['album_id'].'/comments/');
							$tpl->set('{section}', '_'.$check_album['album_id'].'_sec=album_comments');
						} elseif($section == 'user_page'){
							$tpl->set('{close-link}', '/id'.$row['user_id']);
							$tpl->set('{section}', '_sec=user_page');
						} elseif($section == 'wall'){
							$tpl->set('{close-link}', '/id'.$fuser);
							$tpl->set('{section}', '_sec=wall/fuser='.$fuser);
						} elseif($section == 'notes'){
							$tpl->set('{close-link}', '/notes/view/'.$fuser);
							$tpl->set('{section}', '_sec=notes/id='.$fuser);
						} elseif($section == 'loaded'){
							$tpl->set('{close-link}', '/albums/add/'.$check_album['album_id']);
							$tpl->set('{section}', '_sec=loaded');
						} elseif($section == 'news'){
							$fuser = 1;
							$tpl->set('{close-link}', '/news');
							$tpl->set('{section}', '_sec=news');
						} elseif($section == 'msg'){
							$tpl->set('{close-link}', '/messages/show/'.$fuser);
							$tpl->set('{section}', '_sec=msg');
						} elseif($section == 'newphotos'){
							$tpl->set('{close-link}', '/albums/newphotos');
							$tpl->set('{section}', '_'.$check_album['album_id'].'_sec=newphotos');
						} else {
							$tpl->set('{close-link}', '/albums/view/'.$check_album['album_id']);
							$tpl->set('{section}', '_'.$check_album['album_id']);
						}
							
						if(!$fuser){
							$tpl->set('[all]', '');
							$tpl->set('[/all]', '');
							$tpl->set_block("'\\[wall\\](.*?)\\[/wall\\]'si","");
						} else {
							$tpl->set('[wall]', '');
							$tpl->set('[/wall]', '');
							$tpl->set_block("'\\[all\\](.*?)\\[/all\\]'si","");
						}
											
						$tpl->set('{jid}', $row['position']);
						$tpl->set('{comm_num}', ($row['comm_num']-3).' '.gram_record(($row['comm_num']-3), 'comments'));
						$tpl->set('{num}', $row['comm_num']);

						$tpl->set('{author}', $row['user_search_pref']);
						$author_info = explode('|', $row['user_country_city_name']);
						
						if($author_info[0]) $tpl->set('{author-info}', $author_info[0]); 
						else $tpl->set('{author-info}', '');
						if($author_info[1]) $tpl->set('{author-info}', $author_info[0].', '.$author_info[1].'<br />');

						megaDate(strtotime($row['date']), 1, 1);

						if($user_id == $user_info['user_id']){
							$tpl->set('[owner]', '');
							$tpl->set('[/owner]', '');
						} else 
							$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

						$tpl->set('{comments}', $tpl->result['comments']);
							
						//Показываем стрелочки если фотографий больше одной и фотография вызвана не со стены
						if($row_album['photo_num'] > 1 && !$fuser){
							
							//Если фотография вызвана из альбом "все фотографии" или вызвана со страницы юзера
							if($row['position'] == $row_album['photo_num'])
								$next_photo = $position[1];
							else
								$next_photo = $position[($row['position']+1)];
									
							if($row['position'] == 1)
								$prev_photo = $position[($row['position']+$row_album['photo_num']-1)];
							else
								$prev_photo = $position[($row['position']-1)];

							$tpl->set('{next-id}', $next_photo);
							$tpl->set('{prev-id}', $prev_photo);
						} else {
							$tpl->set('{next-id}', $row['id']);
							$tpl->set('{prev-id}', $row['id']);
						}

						if($row['comm_num'] < 8){
							$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
						} else {
							$tpl->set('[all-comm]', '');
							$tpl->set('[/all-comm]', '');
						}
							
						//Приватность комментариев
						if($album_privacy[1] == 1 OR $album_privacy[1] == 2 AND $check_friend OR $user_info['user_id'] == $row['user_id']){
							$tpl->set('[add-comm]', '');
							$tpl->set('[/add-comm]', '');
						} else
							$tpl->set_block("'\\[add-comm\\](.*?)\\[/add-comm\\]'si","");
							
						//Выводим отмеченых людей на фото если они есть
						$sql_mark = $db->super_query("SELECT SQL_CALC_FOUND_ROWS muser_id, mphoto_name, msettings_pos, mmark_user_id, mapprove FROM `".PREFIX."_photos_mark` WHERE mphoto_id = '".$photo_id."' ORDER by `mdate` ASC", 1, 'photos_mark/p'.$photo_id);
						if($sql_mark){
							$cnt_mark = 0;
							$mark_peoples .= '<div class="fl_l" id="peopleOnPhotoText'.$photo_id.'" style="margin-right:5px">На этой фотографии:</div>';
							foreach($sql_mark as $row_mark){
								$cnt_mark++;
								
								if($cnt_mark != 1) $comma = ', ';
								else $comma = '';

								if($row_mark['muser_id'] AND $row_mark['mphoto_name'] == ''){
									if($row['user_id'] == $user_info['user_id'] OR $user_info['user_id'] == $row_mark['muser_id'] OR $user_info['user_id'] == $row_mark['mmark_user_id'])
										$del_mark_link = '<div class="fl_l"><img src="/images/hide_lef.gif" class="distin_del_user" title="Удалить отметку" onclick="Distinguish.DeletUser('.$row_mark['muser_id'].', '.$photo_id.')"/></div>';
									else
										$del_mark_link = '';
										
									$row_user = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$row_mark['muser_id']."'");
									
									if($row_mark['mapprove'] OR $row['user_id'] == $user_info['user_id'] OR $user_info['user_id'] == $row_mark['mmark_user_id'] OR $row_mark['muser_id'] == $user_info['user_id']){
										$user_link = '<a href="/id'.$row_mark['muser_id'].'" id="selected_us_'.$row_mark['muser_id'].$photo_id.'" onclick="Page.Go(this.href); return false" onmouseover="Distinguish.ShowTag('.$row_mark['msettings_pos'].', '.$photo_id.')" onmouseout="Distinguish.HideTag('.$photo_id.')" class="one_dis_user'.$photo_id.'">';
										$user_link_end = '</a>';
									} else {
										$user_link = '<span style="color:#000" id="selected_us_'.$row_mark['muser_id'].$photo_id.'" onmouseover="Distinguish.ShowTag('.$row_mark['msettings_pos'].', '.$photo_id.')" onmouseout="Distinguish.HideTag('.$photo_id.')" class="one_dis_user'.$photo_id.'">';
										$user_link_end = '</span>';
									}
									
									$mark_peoples .= '<span id="selectedDivIser'.$row_mark['muser_id'].$photo_id.'"><div class="fl_l" style="margin-right:4px">'.$comma.'</div><div class="fl_l"> '.$user_link.$row_user['user_search_pref'].$user_link_end.'</div>'.$del_mark_link.'</span>';
								} else {
									if($row['user_id'] == $user_info['user_id'] OR $user_info['user_id'] == $row_mark['mmark_user_id'])
										$del_mark_link = '<div class="fl_l"><img src="/images/hide_lef.gif" class="distin_del_user" title="Удалить отметку" onclick="Distinguish.DeletUser('.$row_mark['muser_id'].', '.$photo_id.', \''.$row_mark['mphoto_name'].'\')"/></div>';
									else
										$del_mark_link = '';
										
									$mark_peoples .= '<span id="selectedDivIser'.$row_mark['muser_id'].$photo_id.'"><div class="fl_l" style="margin-right:4px">'.$comma.'</div><div class="fl_l"><span style="color:#000" id="selected_us_'.$row_mark['muser_id'].$photo_id.'" onmouseover="Distinguish.ShowTag('.$row_mark['msettings_pos'].', '.$photo_id.')" onmouseout="Distinguish.HideTag('.$photo_id.')" class="one_dis_user'.$photo_id.'">'.$row_mark['mphoto_name'].'</span></div>'.$del_mark_link.'</span>';
								}
								
								//Если человек отмечен но не потвердил
								if(!$row_mark['mapprove'] AND $row_mark['muser_id'] == $user_info['user_id']){
									$row_mmark_user_id = $db->super_query("SELECT user_search_pref, user_sex FROM `".PREFIX."_users` WHERE user_id = '".$row_mark['mmark_user_id']."'");
									if($row_mmark_user_id['user_sex'] == 1) $approve_mark_gram_text = 'отметил';
									else $approve_mark_gram_text = 'отметила';
									$approve_mark = $row_mmark_user_id['user_search_pref'];
									$approve_mark_user_id = $row_mark['mmark_user_id'];
									$approve_mark_del_link = 'Distinguish.DeletUser('.$row_mark['muser_id'].', '.$photo_id.', \''.$row_mark['mphoto_name'].'\')';
								} else {
									$approve_mark = '';
									$approve_mark_gram_text = '';
									$approve_mark_user_id = '';
								}
							}
						}
						$tpl->set('{mark-peoples}', $mark_peoples);
						if($approve_mark){
							$tpl->set('{mark-user-name}', $approve_mark);
							$tpl->set('{mark-gram-text}', $approve_mark_gram_text);
							$tpl->set('{mark-user-id}', $approve_mark_user_id);
							$tpl->set('{mark-del-link}', $approve_mark_del_link);
							$tpl->set('[mark-block]', '');
							$tpl->set('[/mark-block]', '');
						} else
							$tpl->set_block("'\\[mark-block\\](.*?)\\[/mark-block\\]'si","");

						$tpl->set('{balance_num}', $rate_num);
						$tpl->set('{balance_view}', $num);
						$tpl->set('{balance}', $balance);
						
								
						$tpl->compile('content');
							
						AjaxTpl();
							
						if($config['gzip'] == 'yes')
							GzipOut();
					} else
						echo 'err_privacy';
				} else
					echo 'no_photo';
			} else
				echo 'err_privacy';
	}
	$tpl->clear();
	$db->free();
} else
	echo 'no_photo';

die();
?>