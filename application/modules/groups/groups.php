<?php

if(!defined('MOZG'))
	die('Not Found');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$act = $_GET['act'];
	$user_id = $user_info['user_id'];
	
	if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
	$gcount = 20;
	$limit_page = ($page-1)*$gcount;
	
	$metatags['title'] = $lang['communities'];
	
	switch($act){
		
		//################### Отправка сообщества БД ###################//
		case "send":
			NoAjaxQuery();
			$title = ajax_utf8(textFilter($_POST['title'], false, true));
			if(isset($title) AND !empty($title)){
				$db->query("INSERT INTO `".PREFIX."_communities` SET title = '{$title}', type = 1, traf = 1, ulist = '|{$user_id}|', date = NOW(), admin = 'id{$user_id}|', real_admin = '{$user_id}', comments = 1");
				$cid = $db->insert_id();
				$db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$cid}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num+1 WHERE user_id = '{$user_id}'");
				
				@mkdir(ROOT_DIR.'/uploads/groups/'.$cid.'/', 0777);
				@chmod(ROOT_DIR.'/uploads/groups/'.$cid.'/', 0777);
				
				@mkdir(ROOT_DIR.'/uploads/groups/'.$cid.'/photos/', 0777);
				@chmod(ROOT_DIR.'/uploads/groups/'.$cid.'/photos/', 0777);
				
				mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
				
				echo $cid;
			} else
				echo 'no_title';
				
			die();
		break;
		
		//################### Выход из сообщества ###################//
		case "exit":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
			if($check['cnt']){
				$db->query("DELETE FROM `".PREFIX."_friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num-1 WHERE user_id = '{$user_id}'");
				$db->query("UPDATE `".PREFIX."_communities` SET traf = traf-1, ulist = REPLACE(ulist, '|{$user_id}|', '') WHERE id = '{$id}'");
				
				mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
			}
			die();
		break;

		//################### бокс инвайт ###################//
		case "invitebox":

			NoAjaxQuery();

			$group_id = intval($_POST['group_id']);
			
			$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS tb1.friend_id, tb2.user_id, user_search_pref, user_photo, user_country_city_name, user_status FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 ORDER by `friends_date` DESC LIMIT 0, 10000", 1);

			$checkg = $db->super_query("SELECT ulist, ban, del FROM `".PREFIX."_communities` WHERE id = '{$group_id}'");

				foreach($sql_ as $row){

					$checkq = $db->super_query("SELECT * FROM `".PREFIX."_communities_demands` WHERE for_user_id = '{$row['user_id']}' AND groups_id = '{$group_id}'");
					
					if(stripos($checkg['ulist'], "|{$row['user_id']}|") === false AND $checkg['del'] == 0 AND $checkg['ban'] == 0){
					
					if($row['user_photo']) {

						$ava = $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo'];

					}else{

						$ava = '/images/no_ava_50.png';}

						if($checkq) {

							echo "<div class=\"oneusergr\"><a href=\"/id{$row['user_id']}\"><img src=\"{$ava}\"></a><a href=\"/id{$row['user_id']}\"><span>{$row['user_search_pref']}</span></a><div class=\"sendgroup\" style=\"width:250px;\"><a>Уже приглашен!</a></div></div>";

						} else {

							echo "<div class=\"oneusergr\"><a href=\"/id{$row['user_id']}\"><img src=\"{$ava}\"></a><a href=\"/id{$row['user_id']}\"><span>{$row['user_search_pref']}</span></a><div id=\"invsend_{$row['user_id']}\" class=\"sendgroup\"><a href=\"\" onclick=\"groups.send({$row['user_id']},{$group_id}); return false\">Выслать приглашение</a></div></div>";

						}
					}

				}

			die();

		break;

		//################### отправка инвайт ###################//
		case "invitesend":
			NoAjaxQuery();
			$group_id = intval($_POST['group_id']);
			$id = intval($_POST['id']);
			$for_user_id = intval($_POST['user_id']);

			$db->query("INSERT INTO `".PREFIX."_communities_demands` SET user_id = '{$user_id}', for_user_id = '{$for_user_id}', date = '{$server_time}', groups_id = '{$group_id}'");
			$db->query("UPDATE `".PREFIX."_users` SET user_new_groups = user_new_groups+1 WHERE user_id = '{$for_user_id}'");

			mozg_mass_clear_cache_file("user_{$for_user_id}/profile_{$for_user_id}|groups/{$for_user_id}");
			mozg_clear_cache_file('user_'.$for_user_id.'/profile_'.$for_user_id);	
			
			//Вставляем событие в моментальные оповещания
			$row_owner = $db->super_query("SELECT  tb1.user_last_visit, tb2.title FROM `".PREFIX."_users` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$user_id}' ORDER BY tb2.id DESC");
			$update_time = $server_time - 70;
			if($row_owner['user_last_visit'] >= $update_time){
				$db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_info['user_id']}', type = '14', date = '{$server_time}', text = '{$row_owner['title']}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/groups?act=requests'");
				mozg_create_cache("user_{$for_user_id}/updates", 1);
			}

			die();
		break;
		
		//################### Страница заявок в сообществе ###################//
		case "requests":
			$user_id = $user_info['user_id'];
			
			if($user_info['user_new_groups'] == 0){
				$tpl->set('{out_error}', '<div class="profile_informations"><img src="/images/pics/spamfight.gif" id="login_blocked_img" width="300" /><br>На данный момент приглашений нет. :(<div class="profile_defave_informations"></div></div>');
			} else {
				$tpl->set('{out_error}', '');
			}
			
			if($user_info['user_new_groups'])
				$tpl->set('{user_new_groups}', '('.$user_info['user_new_groups'].')');
			else
				$tpl->set('{user_new_groups}', '');
			
			$tpl->load_template('groups/req_head.html');
			$tpl->compile('info');

			if($user_info['user_id']){
				$sql_ = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_id, groups_id, for_user_id FROM `".PREFIX."_communities_demands` WHERE for_user_id = '{$user_id}' ORDER by `date` DESC LIMIT 0, 5", 1);
				$tpl->load_template('groups/request.html');
				foreach($sql_ as $row){
					$gid = $row['groups_id'];
					$uid = $row['user_id'];
					$rowg= $db->super_query("SELECT * FROM `".PREFIX."_communities` WHERE id = '{$gid}'");
					$riwu = $db->super_query("SELECT user_id, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$uid}'");
					$tpl->set('{user-id}', $riwu['user_id']);
					$tpl->set('{name}', $rowg['title']);
					$tpl->set('{group-id}', $rowg['id']);
					$tpl->set('{invname}', $riwu['user_search_pref']);
					$tpl->set('{user_id}', $user_info['user_id']);
					
					if($rowg['photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/groups/'.$rowg['id'].'/100_'.$rowg['photo']);
					else
						$tpl->set('{ava}', '/images/no_ava.gif');
						$tpl->compile('content');
				}

				navigation($gcount, $user_info['user_friends_demands'], $config['home_url'].'friends/requests/page/');
			
			}
			//foot
			$tpl->load_template('all_foot.html');
			$tpl->compile('content');

		break;
		
		
			//################### окей ###################//
		case "inviteyes":
			NoAjaxQuery();
			$group_id = intval($_POST['gi']);
			$userid = intval($_POST['ui']);
			
			//Проверка на существования юзера в сообществе
			$row = $db->super_query("SELECT ulist, del, ban FROM `".PREFIX."_communities` WHERE id = '{$group_id}'");
			if(stripos($row['ulist'], "|{$userid}|") === false AND $row['del'] == 0 AND $row['ban'] == 0){
				$ulist = $row['ulist']."|{$userid}|";
				$db->query("UPDATE `".PREFIX."_communities` SET traf = traf+1, ulist = '{$ulist}' WHERE id = '{$group_id}'");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num+1 WHERE user_id = '{$userid}'");
				$db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$group_id}', user_id = '{$userid}', friends_date = NOW(), subscriptions = 2");
				$db->query("DELETE FROM `".PREFIX."_communities_demands` WHERE groups_id = '{$group_id}' AND for_user_id = '{$userid}'");
				$db->query("UPDATE `".PREFIX."_users` SET user_new_groups = user_new_groups-1 WHERE user_id = '{$userid}'");
				mozg_mass_clear_cache_file("user_{$userid}/profile_{$userid}|groups/{$userid}");
			mozg_clear_cache_file('user_'.$userid.'/profile_'.$userid);	
			}
			die();
		break;
		
		
			//################### ноу ###################//
		case "inviteno":
			NoAjaxQuery();
			$group_id = intval($_POST['gi']);
			$userid = intval($_POST['ui']);
			
			$db->query("DELETE FROM `".PREFIX."_communities_demands` WHERE groups_id = '{$group_id}' AND for_user_id = '{$userid}'");
			$db->query("UPDATE `".PREFIX."_users` SET user_new_groups = user_new_groups-1 WHERE user_id = '{$userid}'");
			mozg_mass_clear_cache_file("user_{$userid}/profile_{$userid}|groups/{$userid}");
			mozg_clear_cache_file('user_'.$userid.'/profile_'.$userid);	
			
			die();
		break;

		//################### Страница загрузки главного фото сообщества ###################//
		case "loadphoto_page":
			NoAjaxQuery();
			$tpl->load_template('groups/load_photo.html');
			$tpl->set('{id}', $_POST['id']);
			$tpl->compile('content');
			AjaxTpl();
			die();
		break;
		
		//################### Загрузка и изминение главного фото сообщества ###################//
		case "loadphoto":
			NoAjaxQuery();
			
			$id = intval($_GET['id']);
			
			//Проверка на то, что фото обновляет адмиH
			$row = $db->super_query("SELECT admin, photo, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "id{$user_id}|") !== false AND $row['del'] == 0 AND $row['ban'] == 0){
			
				//Разришенные форматы
				$allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');
				
				//Получаем данные о фотографии
				$image_tmp = $_FILES['uploadfile']['tmp_name'];
				$image_name = totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
				$image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
				$image_size = $_FILES['uploadfile']['size']; // размер файла
				$type = end(explode(".", $image_name)); // формат файла
				
				//Проверям если, формат верный то пропускаем
				if(in_array(strtolower($type), $allowed_files)){
					if($image_size < 5000000){
						$res_type = strtolower('.'.$type);
						
						$upload_dir = ROOT_DIR."/uploads/groups/{$id}/";

						if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
							//Подключаем класс для фотографий
							include APPLICATION_DIR.'/classes/images.php';
							
							//Создание оригинала
							$tmb = new thumbnail($upload_dir.$image_rename.$res_type);
							$tmb->size_auto('200', 1);
							$tmb->jpeg_quality('97');
							$tmb->save($upload_dir.$image_rename.$res_type);

							//Создание маленькой копии 100
							$tmb = new thumbnail($upload_dir.$image_rename.$res_type);
							$tmb->size_auto('100x100');
							$tmb->jpeg_quality('100');
							$tmb->save($upload_dir.'100_'.$image_rename.$res_type);
							
							//Создание маленькой копии 50
							$tmb = new thumbnail($upload_dir.$image_rename.$res_type);
							$tmb->size_auto('50x50');
							$tmb->jpeg_quality('100');
							$tmb->save($upload_dir.'50_'.$image_rename.$res_type);

							if($row['photo']){
								@unlink($upload_dir.$row['photo']);
								@unlink($upload_dir.'50_'.$row['photo']);
								@unlink($upload_dir.'100_'.$row['photo']);
							}

							//Вставляем фотографию
							$db->query("UPDATE `".PREFIX."_communities` SET photo = '{$image_rename}{$res_type}' WHERE id = '{$id}'");

							//Результат для ответа
							echo $image_rename.$res_type;
							
							mozg_clear_cache_folder('groups');
							mozg_clear_cache_file("wall/group{$id}");
echo "<p class='shout_text'><style>Loading</style></p>";
echo "<script>clear();</script>";
							
						} else
							echo 'big_size';
					} else
						echo 'big_size';
				} else
					echo 'bad_format';
			}
			die();
		break;
		
		//################### Удаление фото сообщества ###################//
		case "delphoto":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			//Проверка на то, что фото удалет админ
			$row = $db->super_query("SELECT photo, admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "id{$user_id}|") !== false){
				$upload_dir = ROOT_DIR."/uploads/groups/{$id}/";
				@unlink($upload_dir.$row['photo']);
				@unlink($upload_dir.'50_'.$row['photo']);
				@unlink($upload_dir.'100_'.$row['photo']);
				$db->query("UPDATE `".PREFIX."_communities` SET photo = '' WHERE id = '{$id}'");
				
				mozg_clear_cache_folder('groups');
				mozg_clear_cache_file("wall/group{$id}");
				
			}
			die();
		break;
		
		//################### Вступление в сообщество ###################//
		case "login":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			//Проверка на существования юзера в сообществе
			$row = $db->super_query("SELECT ulist, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['ulist'], "|{$user_id}|") === false AND $row['del'] == 0 AND $row['ban'] == 0){
				$ulist = $row['ulist']."|{$user_id}|";
				$db->query("UPDATE `".PREFIX."_communities` SET traf = traf+1, ulist = '{$ulist}' WHERE id = '{$id}'");
				$db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num+1 WHERE user_id = '{$user_id}'");
				$db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$id}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");
				
				mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
			}
			die();
		break;
		
		//################### Страница добавления контактов ###################//
		case "addfeedback_pg":
			NoAjaxQuery();
			$tpl->load_template('groups/addfeedback_pg.html');
			$tpl->set('{id}', $_POST['id']);
			$tpl->compile('content');
			AjaxTpl();
			die();
		break;
		
		//################### Добавления контакт в БД ###################//
		case "addfeedback_db":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$upage = intval($_POST['upage']);
			$office = ajax_utf8(textFilter($_POST['office'], false, true));
			$phone = ajax_utf8(textFilter($_POST['phone'], false, true));
			$email = ajax_utf8(textFilter($_POST['email'], false, true));
			
			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			//Проверяем что такой юзер есть на сайте
			$row = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id = '{$upage}'");
			
			//Проверяем на то что юзера нет в списке контактов
			$checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

			if($row['cnt'] AND stripos($checkAdmin['admin'], "id{$user_id}|") !== false AND !$checkSec['cnt']){
				$db->query("UPDATE `".PREFIX."_communities` SET feedback = feedback+1 WHERE id = '{$id}'");
				$db->query("INSERT INTO `".PREFIX."_communities_feedback` SET cid = '{$id}', fuser_id = '{$upage}', office = '{$office}', fphone = '{$phone}', femail = '{$email}', fdate = '{$server_time}'");
			} else
				echo 1;
			
			die();
		break;

		//################### Удаление контакта из БД ###################//
		case "delfeedback":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$uid = intval($_POST['uid']);
			
			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			//Проверяем на то что юзера есть в списке контактов
			$checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");
			
			if(stripos($checkAdmin['admin'], "id{$user_id}|") !== false AND $checkSec['cnt']){
				$db->query("UPDATE `".PREFIX."_communities` SET feedback = feedback-1 WHERE id = '{$id}'");
				$db->query("DELETE FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");
			}
			
			die();
		break;
		
		//################### Выводим фотографию юзера при указании ИД страницы ###################//
		case "checkFeedUser":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$row = $db->super_query("SELECT user_photo, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
			if($row) echo $row['user_search_pref']."|".$row['user_photo'];
			die();
		break;
		
		//################### Сохранение отредактированых данных контакт в БД ###################//
		case "editfeeddave":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$upage = intval($_POST['uid']);
			$office = ajax_utf8(textFilter($_POST['office'], false, true));
			$phone = ajax_utf8(textFilter($_POST['phone'], false, true));
			$email = ajax_utf8(textFilter($_POST['email'], false, true));
			
			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			//Проверяем на то что юзера есть в списке контактов
			$checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");
			
			if(stripos($checkAdmin['admin'], "id{$user_id}|") !== false AND $checkSec['cnt']){
				$db->query("UPDATE `".PREFIX."_communities_feedback` SET office = '{$office}', fphone = '{$phone}', femail = '{$email}' WHERE fuser_id = '{$upage}' AND cid = '{$id}'");
				
				mozg_clear_cache_file("wall/group{$id}");
				
			} else
				echo 1;
			
			die();
		break;
		
		//################### Все контакты (БОКС) ###################//
		case "allfeedbacklist":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			
			//Выводим ИД админа
			$owner = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			
			$sql_ = $db->super_query("SELECT tb1.fuser_id, office, fphone, femail, tb2.user_search_pref, user_photo FROM `".PREFIX."_communities_feedback` tb1, `".PREFIX."_users` tb2 WHERE tb1.cid = '{$id}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC", 1);
			$tpl->load_template('groups/allfeedbacklist.html');
			if($sql_){
				foreach($sql_ as $row){
					$tpl->set('{id}', $id);
					$tpl->set('{name}', $row['user_search_pref']);
					$tpl->set('{office}', stripslashes($row['office']));
					$tpl->set('{phone}', stripslashes($row['fphone']));
					$tpl->set('{user-id}', $row['fuser_id']);
					if($row['fphone'] AND $row['femail']) $tpl->set('{email}', ', '.stripslashes($row['femail']));
					else $tpl->set('{email}', stripslashes($row['femail']));
					if($row['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$row['fuser_id'].'/50_'.$row['user_photo']);
					else $tpl->set('{ava}', '/images/no_ava_50.png');
					if(stripos($owner['admin'], "id{$user_id}|") !== false){
						$tpl->set('[admin]', '');
						$tpl->set('[/admin]', '');
					} else
						$tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
					$tpl->compile('content');
				}
				AjaxTpl();
			} else
				echo '<div align="center" style="padding-top:10px;color:#777;font-size:13px;">Список контактов пуст.</div>';

			if(stripos($owner['admin'], "id{$user_id}|") !== false)
				echo "<style>#box_bottom_left_text{padding-top:6px;float:left}</style><script>$('#box_bottom_left_text').html('<a href=\"/\" onClick=\"groups.addcontact({$id}); return false\">Добавить контакт</a>');</script>";
			
			die();
		break;
		
		//################### Сохранение отредактированых данных группы ###################//
		case "saveinfo":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$comments = intval($_POST['comments']);
			$discussion = intval($_POST['discussion']);
			$title = ajax_utf8(textFilter($_POST['title'], false, true));
			$adres_page = ajax_utf8(strtolower(textFilter($_POST['adres_page'], false, true)));
			$descr = ajax_utf8(textFilter($_POST['descr'], 5000));
			
			$_POST['web'] = str_replace(array('"', "'"), '', $_POST['web']);
			$web = ajax_utf8(textFilter($_POST['web'], false, true));
			if(!preg_match("/^[a-zA-Z0-9_-]+$/", $adres_page)) $adress_ok = false;
			else $adress_ok = true;

			//Проверка на то, что действиие делает админ
			$checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '".$id."'");

			if(stripos($checkAdmin['admin'], "id{$user_id}|") !== false AND isset($title) AND !empty($title) AND $adress_ok){
				if(preg_match('/public[0-9]/i', $adres_page))
					$adres_page = '';

				//Проверка на то, что адрес страницы свободен
				$adres_page = preg_replace('/\b(u([0-9]+)|friends|editmypage|albums|photo([0-9]+)_([0-9]+)|photo([0-9]+)_([0-9]+)_([0-9]+)|fave|notes|videos|video([0-9]+)_([0-9]+)|news|messages|wall([0-9]+)|settings|support|restore|blog|balance|nonsense|reg([0-9]+)|gifts([0-9]+)|groups|wallgroups([0-9]+)_([0-9]+)|audio|audio([0-9]+)|docs|apps|app([0-9]+)|public|forum([0-9]+)|public([0-9]+))\b/i', '', $adres_page);
				if($adres_page)
					$checkAdres = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities` WHERE adres = '".$adres_page."' AND id != '".$id."'");
					$chek_user = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE alias = '".$adres_page."' "); // Проверяем адреса у пользователей
				
				if(!$checkAdres['cnt'] AND !$chek_user['cnt'] OR $adres_page == ''){
					$db->query("UPDATE `".PREFIX."_communities` SET title = '".$title."', descr = '".$descr."', comments = '".$comments."', discussion = '{$discussion}', adres = '".$adres_page."', web = '{$web}' WHERE id = '".$id."'");
					if(!$adres_page)
						echo 'no_new';
				} else
					echo 'err_adres';
					
				mozg_clear_cache_folder('groups');
				mozg_clear_cache_file("wall/group{$id}");
			}
			
			die();
		break;
		
		//################### Выводим информацию о пользователе которого будем делать админом ###################//
		case "new_admin":
			NoAjaxQuery();
			$new_admin_id = intval($_POST['new_admin_id']);
			$row = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref, user_sex FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$new_admin_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2");
			if($row AND $user_id != $new_admin_id){
				if($row['user_photo']) $ava = "/uploads/users/{$new_admin_id}/100_{$row['user_photo']}";
				else $ava = "/images/100_no_ava.png";
				if($row['user_sex'] == 1) $gram = 'был';
				else $gram = 'была';
				echo "<div style=\"padding:15px\"><img src=\"{$ava}\" align=\"left\" style=\"margin-right:10px\" id=\"adm_ava\" />Вы хотите чтоб <b id=\"adm_name\">{$row['user_search_pref']}</b> {$gram} одним из руководителей страницы?</div>";
			} else
				echo "<div style=\"padding:15px\"><div class=\"err_red\">Пользователь с таким адресом страницы не подписан на эту страницу.</div></div><script>$('#box_but').hide()</script>";
			
			die();
		break;
		
		//################### Запись нового админа в БД ###################//
		case "send_new_admin":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$new_admin_id = intval($_POST['new_admin_id']);
			$row = $db->super_query("SELECT admin, ulist FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "id{$user_id}|") !== false AND stripos($row['admin'], "id{$new_admin_id}|") === false AND stripos($row['ulist'], "|{$user_id}|") !== false){
				$admin = $row['admin']."id{$new_admin_id}|";
				$db->query("UPDATE `".PREFIX."_communities` SET admin = '{$admin}' WHERE id = '{$id}'");
			}
			die();
		break;
		
		//################### Удаление админа из БД ###################//
		case "deladmin":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$uid = intval($_POST['uid']);
			$row = $db->super_query("SELECT admin, ulist, real_admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "id{$user_id}|") !== false AND stripos($row['admin'], "id{$uid}|") !== false AND $uid != $row['real_admin']){
				$admin = str_replace("id{$uid}|", '', $row['admin']);
				$db->query("UPDATE `".PREFIX."_communities` SET admin = '{$admin}' WHERE id = '{$id}'");
			}
			die();
		break;
		
		//################### Добавление записи на стену ###################//
		case "wall_send":
			NoAjaxQuery();
			$id = intval($_POST['id']);
			$wall_text = ajax_utf8(textFilter($_POST['wall_text']));
			function tag($wall_text){   
			$wall_text= preg_replace("/@(\w+) -(.*)-/", '<a href="/$1" target="_blank">$2</a>', $wall_text);
			$wall_text= preg_replace("/:(.*):/", '<span class="h1">$1</span>', $wall_text);
			return $wall_text;
			} 		
			$wall_text = tag($wall_text);
			$attach_files = ajax_utf8(textFilter($_POST['attach_files'], false, true));
			
			//Проверка на админа
			$row = $db->super_query("SELECT admin, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
			if(stripos($row['admin'], "id{$user_id}|") !== false AND isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files) AND $row['del'] == 0 AND $row['ban'] == 0){
		
					//Оприделение изображения к ссылке
					if(stripos($attach_files, 'link|') !== false){
						$attach_arr = explode('||', $attach_files);
						$cnt_attach_link = 1;
						foreach($attach_arr as $attach_file){
							$attach_type = explode('|', $attach_file);
							if($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1){
								$domain_url_name = explode('/', $attach_type[1]);
								$rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);
								$rImgUrl = $attach_type[4];
								$rImgUrl = str_replace("\\", "/", $rImgUrl);
								$img_name_arr = explode(".", $rImgUrl);
								$img_format = totranslit(end($img_name_arr));
								$image_name = substr(md5($server_time.md5($rImgUrl)), 0, 15);
										
								//Разришенные форматы
								$allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

								//Загружаем картинку на сайт
								if(in_array(strtolower($img_format), $allowed_files) AND preg_match("/http:\/\/(.*?)(.jpg|.png|.gif|.jpeg|.jpe)/i", $rImgUrl)){
													
									//Директория загрузки фото
									$upload_dir = ROOT_DIR.'/uploads/attach/'.$user_id;
														
									//Если нет папки юзера, то создаём её
									if(!is_dir($upload_dir)){ 
										@mkdir($upload_dir, 0777);
										@chmod($upload_dir, 0777);
									}
														
									//Подключаем класс для фотографий
									include APPLICATION_DIR.'/classes/images.php';

									if(@copy($rImgUrl, $upload_dir.'/'.$image_name.'.'.$img_format)){
										$tmb = new thumbnail($upload_dir.'/'.$image_name.'.'.$img_format);
										$tmb->size_auto('100x80');
										$tmb->jpeg_quality(100);
										$tmb->save($upload_dir.'/'.$image_name.'.'.$img_format);
														
										$attach_files = str_replace($attach_type[4], '/uploads/attach/'.$user_id.'/'.$image_name.'.'.$img_format, $attach_files);
									}
								}
								$cnt_attach_link++;
							}
						}
					}
			
				$attach_files = str_replace('vote|', 'hack|', $attach_files);
				$attach_files = str_replace(array('&amp;#124;', '&amp;raquo;', '&amp;quot;'), array('&#124;', '&raquo;', '&quot;'), $attach_files);

				//Голосование
				$vote_title = ajax_utf8(textFilter($_POST['vote_title'], false, true));
				$vote_answer_1 = ajax_utf8(textFilter($_POST['vote_answer_1'], false, true));

				$ansers_list = array();

				if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){

					for($vote_i = 1; $vote_i <= 10; $vote_i++){

						$vote_answer = ajax_utf8(textFilter($_POST['vote_answer_'.$vote_i], false, true));
						$vote_answer = str_replace('|', '&#124;', $vote_answer);

						if($vote_answer)
							$ansers_list[] = $vote_answer;

					}

					$sql_answers_list = implode('|', $ansers_list);
									
					//Вставляем голосование в БД
					$db->query("INSERT INTO `".PREFIX."_votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");
									
					$attach_files = $attach_files."vote|{$db->insert_id()}||";
								
				}
				
				//Вставляем саму запись в БД
				$db->query("INSERT INTO `".PREFIX."_communities_wall` SET public_id = '{$id}', text = '{$wall_text}', attach = '{$attach_files}', add_date = '{$server_time}'");
				$dbid = $db->insert_id();
				$db->query("UPDATE `".PREFIX."_communities` SET rec_num = rec_num+1 WHERE id = '{$id}'");
				
				//Вставляем в ленту новотсей
				$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$id}', action_type = 11, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$server_time}'");
				
				//Загружаем все записи
				if(stripos($row['admin'], "id{$user_id}|") !== false)
					$public_admin = true;
				else
					$public_admin = false;
			
				$limit_select = 10;
				$pid = $id;
				include APPLICATION_DIR.'/classes/wall.public.php';
				$wall = new wall();
				$wall->query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments, fixed FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = '{$id}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `fixed` DESC, `add_date` DESC LIMIT 0, {$limit_select}");
				$wall->template('groups/record.html');
				$wall->compile('content');
				$wall->select($public_admin, $server_time);
				AjaxTpl();
			}
			die();
		break;
		
		//################### Добавление комментария к записи ###################//
		case "wall_send_comm":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$public_id = intval($_POST['public_id']);
			$wall_text = ajax_utf8(textFilter($_POST['wall_text']));
						function tag($wall_text){   
			$wall_text= preg_replace("/@(\w+) -(.*)-/", '<a href="/$1" target="_blank">$2</a>', $wall_text);
			$wall_text= preg_replace("/:(.*):/", '<span class="h1">$1</span>', $wall_text);
			return $wall_text;
			} 		
			$wall_text = tag($wall_text);
			$answer_comm_id = intval($_POST['answer_comm_id']);
	
			//Проверка на админа и проверяем включены ли комменты
			$row = $db->super_query("SELECT tb1.fasts_num, public_id, tb2.admin, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");
			
			if($row['comments'] OR stripos($row['admin'], "id{$user_id}|") !== false AND isset($wall_text) AND !empty($wall_text)){

				
				//Если добавляется ответ на комментарий то вносим в ленту новостей "ответы"
				if($answer_comm_id){
								
					//Выводим ид владельца комменатрия
					$row_owner2 = $db->super_query("SELECT public_id, text FROM `".PREFIX."_communities_wall` WHERE id = '{$answer_comm_id}' AND fast_comm_id != '0'");
								
					//Проверка на то, что юзер не отвечает сам себе
					if($user_id != $row_owner2['public_id'] AND $row_owner2){
									
					
						$check2 = $db->super_query("SELECT user_last_visit, user_name FROM `".PREFIX."_users` WHERE user_id = '{$row_owner2['public_id']}'");
									
						$wall_text = str_replace($check2['user_name'], "<a href=\"/id{$row_owner2['public_id']}\" onClick=\"Page.Go(this.href); return false\" style=\"color:#666\">{$check2['user_name']}</a>", $wall_text);
						
						//Вставляем в ленту новостей
						$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$answer_comm_id}', for_user_id = '{$row_owner2['public_id']}', action_time = '{$server_time}'");
									
						//Вставляем событие в моментальные оповещания
						$update_time = $server_time - 70;

						if($check2['user_last_visit'] >= $update_time){
									
							$db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$row_owner2['public_id']}', from_user_id = '{$user_id}', type = '5', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/news/notifications'");
									
							mozg_create_cache("user_{$row_owner2['public_id']}/updates", 1);
									
							//ИНАЧЕ Добавляем +1 юзеру для оповещания
						} else {
										
							$cntCacheNews = mozg_cache("user_{$row_owner2['public_id']}/new_news");
							mozg_create_cache("user_{$row_owner2['public_id']}/new_news", ($cntCacheNews+1));
										
						}
									
					}
								
				}
				
				//Вставляем саму запись в БД
				$db->query("INSERT INTO `".PREFIX."_communities_wall` SET public_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', fast_comm_id = '{$rec_id}'");
				$db->query("UPDATE `".PREFIX."_communities_wall` SET fasts_num = fasts_num+1 WHERE id = '{$rec_id}'");

				$row['fasts_num'] = $row['fasts_num']+1;
				
				if($row['fasts_num'] > 3)
					$comments_limit = $row['fasts_num']-3;
				else
					$comments_limit = 0;
						
				$sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);
				
				//Загружаем кнопку "Показать N запсии"
				$tpl->load_template('groups/record.html');
				$tpl->set('{gram-record-all-comm}', gram_record(($row['fasts_num']-3), 'prev').' '.($row['fasts_num']-3).' '.gram_record(($row['fasts_num']-3), 'comments'));
				if($row['fasts_num'] < 4)
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				else {
					$tpl->set('{rec-id}', $rec_id);
					$tpl->set('[all-comm]', '');
					$tpl->set('[/all-comm]', '');
				}
				$tpl->set('{public-id}', $public_id);
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->compile('content');
					
				$tpl->load_template('groups/record.html');
				//Сообственно выводим комменты
				foreach($sql_comments as $row_comments){
					$tpl->set('{public-id}', $public_id);
					$tpl->set('{name}', $row_comments['user_search_pref']);
					if($row_comments['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
					else
						$tpl->set('{ava}', '/images/no_ava_50.png');
					$tpl->set('{comm-id}', $row_comments['id']);
					$tpl->set('{user-id}', $row_comments['public_id']);
					$tpl->set('{rec-id}', $rec_id);
					
					$expBR2 = explode('<br />', $row_comments['text']);
					$textLength2 = count($expBR2);
					$strTXT2 = strlen($row_comments['text']);
					if($textLength2 > 6 OR $strTXT2 > 470)
						$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
							
					//Обрабатываем ссылки
					$row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);
					
					$tpl->set('{text}', stripslashes($row_comments['text']));
					megaDate($row_comments['add_date']);
					if(stripos($row['admin'], "id{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
						$tpl->set('[owner]', '');
						$tpl->set('[/owner]', '');
					} else
						$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
					if($user_id == $row_comments['public_id'])
						
						$tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
							
					else {

						$tpl->set('[not-owner]', '');
						$tpl->set('[/not-owner]', '');
						
					}
					
					$tpl->set('[comment]', '');
					$tpl->set('[/comment]', '');
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					$tpl->compile('content');
				}
				
				//Загружаем форму ответа
				$tpl->load_template('groups/record.html');
				$tpl->set('{rec-id}', $rec_id);
				$tpl->set('{user-id}', $public_id);
				$tpl->set('[comment-form]', '');
				$tpl->set('[/comment-form]', '');
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				$tpl->compile('content');
				
				AjaxTpl();
			}
			die();
		break;
		
		//################### Удаление записи ###################//
		case "wall_del":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$public_id = intval($_POST['public_id']);

			//Проверка на админа и проверяем включены ли комменты
			if($public_id){
				$row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
				$row_rec = $db->super_query("SELECT fast_comm_id, public_id, add_date FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
			} else
				$row = $db->super_query("SELECT tb1.public_id, attach, fast_comm_id, tb2.admin FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

			if(stripos($row['admin'], "id{$user_id}|") !== false OR $user_id == $row_rec['public_id']){
				if($public_id){
				
					$db->query("UPDATE `".PREFIX."_communities_wall` SET fasts_num = fasts_num-1 WHERE id = '{$row_rec['fast_comm_id']}'");
					$db->query("DELETE FROM `".PREFIX."_news` WHERE ac_user_id = '{$row_rec['public_id']}' AND action_type = '6' AND action_time = '{$row_rec['add_date']}'");
					
					$db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
					
				} else if($row['fast_comm_id'] == 0){
					
					$db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE fast_comm_id = '{$rec_id}'");
					$db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rec_id}' AND action_type = '11'");
					$db->query("UPDATE `".PREFIX."_communities` SET rec_num = rec_num-1 WHERE id = '{$row['public_id']}'");
					
					//Удаляем фотку из прикрипленой ссылке, если она есть
					if(stripos($row['attach'], 'link|') !== false){
						$attach_arr = explode('link|', $row['attach']);
						$attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
						$attach_arr3 = explode('||', $attach_arr2[1]);
						if($attach_arr3[0])
							@unlink(ROOT_DIR.'/uploads/attach/'.$user_id.'/'.$attach_arr3[0]);	
					}
					
					$db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
				}

			}
			die();
		break;
		
		//################### Показ всех комментариев к записи ###################//
		case "all_comm":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$public_id = intval($_POST['public_id']);

			//Проверка на админа и проверяем включены ли комменты
			$row = $db->super_query("SELECT tb2.admin, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

			if($row['comments'] OR stripos($row['admin'], "id{$user_id}|") !== false){
				$sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref, alias FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC", 1);
				$tpl->load_template('groups/record.html');
				//Сообственно выводим комменты
				foreach($sql_comments as $row_comments){
					$tpl->set('{public-id}', $public_id);
					$tpl->set('{name}', $row_comments['user_search_pref']);
					if($row_comments['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
					else
						$tpl->set('{ava}', '/images/no_ava_50.png');
						
					$tpl->set('{rec-id}', $rec_id);
					$tpl->set('{comm-id}', $row_comments['id']);
					$tpl->set('{user-id}', $row_comments['public_id']);
					
					$expBR2 = explode('<br />', $row_comments['text']);
					$textLength2 = count($expBR2);
					$strTXT2 = strlen($row_comments['text']);
					if($textLength2 > 6 OR $strTXT2 > 470)
						$row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';
						
					//Обрабатываем ссылки
					$row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);
					
					$tpl->set('{text}', stripslashes($row_comments['text']));
					megaDate($row_comments['add_date']);
					if(stripos($row['admin'], "id{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
						$tpl->set('[owner]', '');
						$tpl->set('[/owner]', '');
					} else
						$tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
					
					if($user_id == $row_comments['public_id'])
						
						$tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
							
					else {

						$tpl->set('[not-owner]', '');
						$tpl->set('[/not-owner]', '');
						
					}
						
					$tpl->set('[comment]', '');
					$tpl->set('[/comment]', '');
					$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
					$tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
					$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
					$tpl->compile('content');
				}
				
				//Загружаем форму ответа
				$tpl->load_template('groups/record.html');
				$tpl->set('{rec-id}', $rec_id);
				$tpl->set('{user-id}', $public_id);
									if($row_comments['alias']) $tpl->set('{alias}', $row_comments['alias']);
					else $tpl->set('{alias}', 'id'.$row_comments['public_id']);
				$tpl->set('[comment-form]', '');
				$tpl->set('[/comment-form]', '');
				$tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
				$tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
				$tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
				$tpl->compile('content');
				
				AjaxTpl();
			}
			die();
		break;
		
		//################### Страница загрузки фото в сообщество ###################//
		case "photos":
			NoAjaxQuery();
			$public_id = intval($_POST['public_id']);
			$rowPublic = $db->super_query("SELECT admin, photos_num FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
			if(stripos($rowPublic['admin'], "id{$user_id}|") !== false){
				
				if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
				$gcount = 36;
				$limit_page = ($page-1)*$gcount;
			
				//HEAD
				$tpl->load_template('public/photos/head.html');
				$tpl->set('{photo-num}', $rowPublic['photos_num'].' '.gram_record($rowPublic['photos_num'], 'photos'));
				$tpl->set('{public_id}', $public_id);
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
				$tpl->compile('info');
				
				//Выводим фотографии
				if($rowPublic['photos_num']){
					$sql_ = $db->super_query("SELECT photo FROM `".PREFIX."_attach` WHERE public_id = '{$public_id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
					$tpl->load_template('public/photos/photo.html');
					foreach($sql_ as $row){
						$tpl->set('{photo}', $row['photo']);
						$tpl->set('{public-id}', $public_id);
						$tpl->compile('content');
					}

					box_navigation($gcount, $rowPublic['photos_num'], $page, 'groups.wall_attach_addphoto', $public_id);
				} else
					msgbox('', '<div class="clear" style="margin-top:150px;margin-left:27px"></div>В альбоме сообщества нет загруженных фотографий.', 'info_2');
				
				//BOTTOM
				$tpl->load_template('public/photos/head.html');
				$tpl->set('[bottom]', '');
				$tpl->set('[/bottom]', '');
				$tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
				$tpl->compile('content');
				
				AjaxTpl();
			}
			die();
		break;

		//################### Выводим инфу о видео при прикриплении видео на стену ###################//
		case "select_video_info":
			NoAjaxQuery();
			$video_id = intval($_POST['video_id']);
			$row = $db->super_query("SELECT photo FROM `".PREFIX."_videos` WHERE id = '".$video_id."'");
			if($row){
				$photo = end(explode('/', $row['photo']));
				echo $photo;
			} else
				echo '1';
			
			die();
		break;
		
		//################### Ставим мне нравится ###################//
		case "wall_like_yes":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$row = $db->super_query("SELECT likes_users FROM `".PREFIX."_communities_wall` WHERE id = '".$rec_id."'");
			if($row AND stripos($row['likes_users'], "id{$user_id}|") === false){
				$likes_users = "id{$user_id}|".$row['likes_users'];
				$db->query("UPDATE `".PREFIX."_communities_wall` SET likes_num = likes_num+1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
				$db->query("INSERT INTO `".PREFIX."_communities_wall_like` SET rec_id = '".$rec_id."', user_id = '".$user_id."', date = '".$server_time."'");
			}
			die();
		break;
		
		//################### Убераем мне нравится ###################//
		case "wall_like_remove":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$row = $db->super_query("SELECT likes_users FROM `".PREFIX."_communities_wall` WHERE id = '".$rec_id."'");
			if(stripos($row['likes_users'], "id{$user_id}|") !== false){
				$likes_users = str_replace("id{$user_id}|", '', $row['likes_users']);
				$db->query("UPDATE `".PREFIX."_communities_wall` SET likes_num = likes_num-1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
				$db->query("DELETE FROM `".PREFIX."_communities_wall_like` WHERE rec_id = '".$rec_id."' AND user_id = '".$user_id."'");
			}
			die();
		break;
		
		//################### Выводим последних 7 юзеров кто поставил "Мне нравится" ###################//
		case "wall_like_users_five":
			NoAjaxQuery();
			$rec_id = intval($_POST['rec_id']);
			$sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo FROM `".PREFIX."_communities_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rec_id}' ORDER by `date` DESC LIMIT 0, 7", 1);
			if($sql_){
				foreach($sql_ as $row){
					if($row['user_photo']) $ava = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
					else $ava = '/images/no_ava_50.png';
					echo '<a href="/id'.$row['user_id'].'" id="Xlike_user'.$row['user_id'].'_'.$rec_id.'" onClick="Page.Go(this.href); return false"><img src="'.$ava.'" width="32" /></a>';
				}
			}
			die();
		break;
		
		//################### Выводим всех юзеров которые поставили "мне нравится" ###################//
		case "all_liked_users":
			NoAjaxQuery();
			$rid = intval($_POST['rid']);
			$liked_num = intval($_POST['liked_num']);
			
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 24;
			$limit_page = ($page-1)*$gcount;
			
			if(!$liked_num)
				$liked_num = 24;
			
			if($rid AND $liked_num){
				$sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref, alias FROM `".PREFIX."_communities_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);
				
				if($sql_){
					$tpl->load_template('profile_subscription_box_top.html');
					$tpl->set('[top]', '');
					$tpl->set('[/top]', '');
					$tpl->set('{subcr-num}', 'Понравилось '.$liked_num.' '.gram_record($liked_num, 'like'));
					$tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
					$tpl->compile('content');
					
					$tpl->result['content'] = str_replace('Всего', '', $tpl->result['content']);

					$tpl->load_template('profile_people.html');
					foreach($sql_ as $row){

					if($row['user_photo'])
						$tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
					else
						$tpl->set('{ava}', '/images/no_ava_50.png');
						$friend_info_online = explode(' ', $row['user_search_pref']);

						//Замена (id) - на унекальное имя (aliast).
						if($row['alias']){
							$tpl->set('{user-id}', $row['alias']); 
						} else {
							$tpl->set('{user-id}', 'id'.$row['user_id']);
						}

						$tpl->set('{name}', $friend_info_online[0]);
						$tpl->set('{last-name}', $friend_info_online[1]);
						$tpl->compile('content');
					}
					box_navigation($gcount, $liked_num, $rid, 'groups.wall_all_liked_users', $liked_num);
					
					AjaxTpl();
				}
			}
			die();
		break;
		
		//################### Рассказать друзьям "Мне нравится" ###################//
		case "wall_tell":
			NoAjaxQuery();
			$rid = intval($_POST['rec_id']);
			
			//Проверка на существование записи
			$row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `".PREFIX."_communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");

			if($row){
				if($row['tell_uid']){
					$row['add_date'] = $row['tell_date'];
					$row['author_user_id'] = $row['tell_uid'];
					$row['public_id'] = $row['tell_uid'];
				} else
					$row['public'] = 1;
						
				//Проверяем на существование этой записи у себя на стене
				$myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE tell_uid = '{$row['public_id']}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}' AND public = '{$row['public']}'");
				if($row['tell_uid'] != $user_id AND $myRow['cnt'] == false){
					$row['text'] = $db->safesql($row['text']);
					$row['attach'] = $db->safesql($row['attach']);
						
					//Всталвяем себе на стену
					$db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$row['public_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '".$row['attach']."'");
					$dbid = $db->insert_id();
					$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");
						
					//Вставляем в ленту новостей
					$db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");
						
					//Чистим кеш
					mozg_clear_cache_file("user_{$user_id}/profile_{$user_id}");
				} else
					echo 1;
			} else
				echo 1;
				
			die();
		break;
		
		//################### Показ всех подпискок ###################//
		case "all_people":
			NoAjaxQuery();
			
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 24;
			$limit_page = ($page-1)*$gcount;
			
			$public_id = intval($_POST['public_id']);
			$subscr_num = intval($_POST['num']);
			
			$sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo, alias FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$public_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
			
			if($sql_){
				$tpl->load_template('profile_subscription_box_top.html');
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set('{subcr-num}', $subscr_num.' '.gram_record($subscr_num, 'subscribers'));
				$tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
				$tpl->compile('content');
						
				$tpl->load_template('profile_people.html');
				foreach($sql_ as $row){

					if($row['user_photo'])
						$tpl->set('{ava}', '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
					else
						$tpl->set('{ava}', '/images/no_ava_50.png');
						
					//Замена (id) - на унекальное имя (aliast).
					if($row['alias']){
						$tpl->set('{user-id}', $row['alias']); 
					} else {
						$tpl->set('{user-id}', 'id'.$row['user_id']);
					}

					$tpl->set('{name}', $row['user_name']);
					$tpl->set('{last-name}', $row['user_lastname']);
					$tpl->compile('content');
				}
				
				box_navigation($gcount, $subscr_num, $public_id, 'groups.all_people', $subscr_num);
				
			}
			
			AjaxTpl();
			
			die();
		break;
		
		//################### Показ всех сообщества юзера на которые он подписан (BOX) ###################//
		case "all_groups_user":
			if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
			$gcount = 20;
			$limit_page = ($page-1)*$gcount;
			
			$for_user_id = intval($_POST['for_user_id']);
			$subscr_num = intval($_POST['num']);

			$sql_ = $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, traf, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$for_user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}", 1);
			
			if($sql_){
				$tpl->load_template('profile_subscription_box_top.html');
				$tpl->set('[top]', '');
				$tpl->set('[/top]', '');
				$tpl->set('{subcr-num}', $subscr_num.' '.gram_record($subscr_num, 'subscr'));
				$tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
				$tpl->compile('content');
						
				$tpl->load_template('profile_group.html');
				foreach($sql_ as $row){
					if($row['photo']) $tpl->set('{ava}', '/uploads/groups/'.$row['id'].'/50_'.$row['photo']);
					else $tpl->set('{ava}', '/images/no_ava_50.png');
					$tpl->set('{name}', stripslashes($row['title']));
					$tpl->set('{public-id}', $row['id']);
					$tpl->set('{num}', '<span id="traf">'.$row['traf'].' '.gram_record($row['traf'], 'subscribers'));
					if($row['adres']) $tpl->set('{adres}', $row['adres']);
					else $tpl->set('{adres}', 'public'.$row['id']);
					$tpl->compile('content');
				}
				
				box_navigation($gcount, $subscr_num, $for_user_id, 'groups.all_groups_user', $subscr_num);
				
			}
			
			AjaxTpl();
			
			die();
		break;
		
		//################### Одна запись со стены ###################//
		case "wallgroups":
			
			$id = intval($_GET['id']);
			$pid = intval($_GET['pid']);
			
			$row = $db->super_query("SELECT id, adres, del, ban FROM `".PREFIX."_communities` WHERE id = '{$pid}'");
			
			if($row AND !$row['del'] AND !$row['ban']){
			
				$tpl->load_template('groups/wall_head.html');
				$tpl->set('{id}', $id);
				$tpl->set('{pid}', $pid);
				if($row['adres'])
					$tpl->set('{adres}', $row['adres']);
				else
					$tpl->set('{adres}', 'public'.$pid);
				$tpl->compile('info');
				
				include APPLICATION_DIR.'/classes/wall.public.php';
				$wall = new wall();
				$wall->query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments, adres FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.id = '{$id}' AND tb1.public_id = tb2.id AND fast_comm_id = 0");
				$wall->template('groups/record.html');
				$wall->compile('content');
				$wall->select($public_admin, $server_time);
				
				$tpl->result['content'] = str_replace('width:500px;', 'width:710px;', $tpl->result['content']);
				
				if(!$tpl->result['content'])
					msgbox('', '<br /><br /><br />Запись не найдена.<br /><br /><br />', 'info_2');
			
			} else
				msgbox('', '<br /><br />Запись не найдена.<br /><br /><br />', 'info_2');
	
		break;
		
                //################### Загрузка обложки ###################//
		case "upload_cover":
		
			NoAjaxQuery();
			
			$public_id = intval($_GET['id']);
			
			//Проверка на админа
			$row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
			
			if(stripos($row_pub['admin'], "id{$user_id}|") !== false){
			
				//Получаем данные о файле
				$image_tmp = $_FILES['uploadfile']['tmp_name'];
				$image_name = totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
				$image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя файла
				$image_size = $_FILES['uploadfile']['size']; // размер файла
				$type = end(explode(".", $image_name)); // формат файла
				
				$max_size = 1024 * 7000;

				//Проверка размера
				if($image_size <= $max_size){
					
					//Разришенные форматы
					$allowed_files = explode(', ', 'jpg, jpeg, jpe, png, gif');
					
					//Проверям если, формат верный то пропускаем
					if(in_array(strtolower($type), $allowed_files)){
						
						$res_type = strtolower('.'.$type);
						
						$upDir = ROOT_DIR."/uploads/groups/{$public_id}/";
						
						$rImg = $upDir.$image_rename.$res_type;
						
						if(move_uploaded_file($image_tmp, $rImg)){
							
							//Подключаем класс для фотографий
							include_once APPLICATION_DIR.'/classes/images.php';
							
							//Создание маленькой копии
							$tmb = new thumbnail($rImg);
							$tmb->size_auto('794', 1);
							$tmb->jpeg_quality('100');
							$tmb->save($rImg);
							
							//Выводим и удаляем пред. обложку
							$row = $db->super_query("SELECT cover FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
							if($row){
								
								@unlink($upDir.$row['cover']);
								
							}

							$imgData = getimagesize($rImg);
							$rImgsData = round($imgData[1] / ($imgData[0] / 794));

							//Обновдяем обложку в базе
							$pos = round(($rImgsData / 2) - 100);
							
							if($rImgsData <= 230){
								$rImgsData = 230;
								$pos = 0;
							}
							
							$db->query("UPDATE `".PREFIX."_communities` SET cover = '{$image_rename}{$res_type}', cover_pos = '{$pos}' WHERE id = '{$public_id}'");
							
							echo $public_id.'/'.$image_rename.$res_type.'|'.$rImgsData;
							
						}
						
					} else
						echo 2;
				
				} else
					echo 1;
		
			}
			
			exit();
			
		break;
				
		//################### Сохранение новой позиции обложки ###################//
		case "savecoverpos":
			
			NoAjaxQuery();
			
			$public_id = intval($_GET['id']);
			
			//Проверка на админа
			$row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
			
			if(stripos($row_pub['admin'], "id{$user_id}|") !== false){
						
				$pos = intval($_POST['pos']);
				if($pos < 0) $pos = 0;
				
				$db->query("UPDATE `".PREFIX."_communities` SET cover_pos = '{$pos}' WHERE id = '{$public_id}'");
			
			}
			
			exit();
			
		break;
		
		//################### Удаление обложки ###################//
		case "delcover":
		
			NoAjaxQuery();
			
			$public_id = intval($_GET['id']);

			//Проверка на админа
			$row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
			
			if(stripos($row_pub['admin'], "id{$user_id}|") !== false){
			
				//Выводим и удаляем пред. обложку
				$row = $db->super_query("SELECT cover FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
				if($row){
					
					$upDir = ROOT_DIR."/uploads/groups/{$public_id}/";				
					@unlink($upDir.$row['cover']);
								
				}
							
				$db->query("UPDATE `".PREFIX."_communities` SET cover_pos = '', cover = '' WHERE id = '{$public_id}'");
			
			}
			
			exit();
			
		break;
		
		//################### Закрипление записи ###################//
		case "fasten":
		
			NoAjaxQuery();
			
			$rec_id = intval($_POST['rec_id']);
			
			//Выводим ИД группы
			$row = $db->super_query("SELECT public_id FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");

			//Проверка на админа
			$row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");
			
			if(stripos($row_pub['admin'], "id{$user_id}|") !== false){
			
				//Убераем фиксацию у пред записи
				$db->query("UPDATE `".PREFIX."_communities_wall` SET fixed = '0' WHERE fixed = '1' AND public_id = '{$row['public_id']}'");
				
				//Ставим фиксацию записи 
				$db->query("UPDATE `".PREFIX."_communities_wall` SET fixed = '1' WHERE id = '{$rec_id}'");
			
			}
			
			exit();
			
		break;
		
		//################### Убераем фиксацию ###################//
		case "unfasten":
		
			NoAjaxQuery();
			
			$rec_id = intval($_POST['rec_id']);
			
			//Выводим ИД группы
			$row = $db->super_query("SELECT public_id FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");

			//Проверка на админа
			$row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");
			
			if(stripos($row_pub['admin'], "id{$user_id}|") !== false){
			
				//Убераем фиксацию записи 
				$db->query("UPDATE `".PREFIX."_communities_wall` SET fixed = '0' WHERE id = '{$rec_id}'");
			
			}
			
			exit();
			
		break;
		
		default:
		
			//################### Вывод всех сообществ ###################//
			$owner = $db->super_query("SELECT user_public_num FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
			if($act == 'admin'){
				$mobile_speedbar = 'Ваши сообщества';
				$tpl->load_template('groups/head_admin.html');
				$sql_sort = "SELECT id, title, photo, traf, adres FROM `".PREFIX."_communities` WHERE admin regexp '[[:<:]](id{$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
				$sql_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities` WHERE admin regexp '[[:<:]](id{$user_id})[[:>:]]'");
				$owner['user_public_num'] = $sql_count['cnt'];
			} else {
				$mobile_speedbar = 'Сообщества';
				$sql_sort = "SELECT tb1.friend_id, tb2.id, title, photo, traf, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
				$tpl->load_template('groups/head.html');
			}
			
			if($owner['user_public_num']){
				$tpl->set('{num}', $owner['user_public_num'].' '.gram_record($owner['user_public_num'], 'groups'));
				$tpl->set('[yes]', '');
				$tpl->set('[/yes]', '');
				$tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
			} else {
				$tpl->set('[no]', '');
				$tpl->set('[/no]', '');
				$tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
			}
			$tpl->compile('info');
			
			if($owner['user_public_num']){

				$sql_ = $db->super_query($sql_sort, 1);
				
				$tpl->load_template('groups/group.html');
				foreach($sql_ as $row){
					$tpl->set('{id}', $row['id']);
					if($row['adres']) $tpl->set('{adres}', $row['adres']);
					else $tpl->set('{adres}', 'public'.$row['id']);
					
					$tpl->set('{name}', stripslashes($row['title']));
					$tpl->set('{traf}', $row['traf'].' '.gram_record($row['traf'], 'groups_users'));
					
					if($act != 'admin'){
						$tpl->set('[admin]', '');
						$tpl->set('[/admin]', '');
					} else
						$tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
					
					if($row['photo'])
						$tpl->set('{photo}', "/uploads/groups/{$row['id']}/100_{$row['photo']}");
					else
						$tpl->set('{photo}', "/images/no_ava.gif");
					
					$tpl->compile('content');
				}
				
				if($act == 'admin') $admn_act = 'act=admin&';
				
				navigation($gcount, $owner['user_public_num'], 'groups?'.$admn_act.'page=');
				
			}
	}
	$tpl->clear();
	$db->free();
} else {
	$user_speedbar = $lang['no_infooo'];
	msgbox('', $lang['not_logged'], 'info');
}
?>