<?php

if(!defined('MOZG'))
	die('Not Found');

if($ajax == 'yes')
	NoAjaxQuery();

if($logged){
	$user_id = $user_info['user_id'];
	$pid = intval($_GET['pid']);
	
	if(preg_match("/^[a-zA-Z0-9_-]+$/", $_GET['get_adres'])) $get_adres = $db->safesql($_GET['get_adres']);
	
	$sql_where = "id = '".$pid."'";
	
	if($pid){
		$get_adres = '';
		$sql_where = "id = '".$pid."'";
	}
	if($get_adres){
		$pid = '';
		$sql_where = "adres = '".$get_adres."'";
	} else
	
	echo $get_adres;

	//Если страница вывзана через "к предыдущим записям"
	$limit_select = 10;
	if($_POST['page_cnt'] > 0)
		$page_cnt = intval($_POST['page_cnt'])*$limit_select;
	else
		$page_cnt = 0;

	if($page_cnt){
		$row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$pid}'");
		$row['id'] = $pid;
	} else
		$row = $db->super_query("SELECT id, title, descr, traf, ulist, photo, date, admin, feedback, comments, real_admin, rec_num, del, ban, adres, audio_num, discussion, forum_num, status_text, web FROM `".PREFIX."_communities` WHERE ".$sql_where."");
	
	if($row['del'] == 1){
		$user_speedbar = 'Страница удалена';
		msgbox('', '<br /><br />Сообщество удалено администрацией.<br /><br /><br />', 'info_2');
	} elseif($row['ban'] == 1){
		$user_speedbar = 'Страница заблокирована';
		msgbox('', '<br /><br />Сообщество заблокировано администрацией.<br /><br /><br />', 'info_2');
	} elseif($row){
		$metatags['title'] = stripslashes($row['title']);
		$user_speedbar = $lang['public_spbar'];
		
		if(stripos($row['admin'], "id{$user_id}|") !== false)
			$public_admin = true;
		else
			$public_admin = false;

		//Стена
		//Если страница вывзана через "к предыдущим записям"
		if($page_cnt)
			NoAjaxQuery();
		
		include APPLICATION_DIR.'/classes/wall.public.php';
		$wall = new wall();
		$wall->query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, fixed, tb2.title, photo, comments, adres FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = '{$row['id']}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `fixed` DESC, `add_date` DESC LIMIT {$page_cnt}, {$limit_select}");
		$wall->template('groups/record.html');
		//Если страница вывзана через "к предыдущим записям"
		if($page_cnt)
			$wall->compile('content');
		else
			$wall->compile('wall');
			$wall->select($public_admin, $server_time);
		
		//Если страница вывзана через "к предыдущим записям"
		if($page_cnt){
			AjaxTpl();
			exit;
		}
		
		$tpl->load_template('public/main.html');
		
		$tpl->set('{title}', stripslashes($row['title']));

		if($row['photo']){
			$tpl->set('{photo}', "/uploads/groups/{$row['id']}/{$row['photo']}");
			$tpl->set('{display-ava}', '');
		} else {
			$tpl->set('{photo}', "/images/no_ava.gif");
			$tpl->set('{display-ava}', 'no_display');
		}
		
		if($row['descr'])
			$tpl->set('{descr-css}', '');
		else 
			$tpl->set('{descr-css}', 'no_display');
		
		$tpl->set('{edit-descr}', myBrRn(stripslashes($row['descr'])));
		
		//КНопка Показать полностью..
		$expBR = explode('<br />', $row['descr']);
		$textLength = count($expBR);
		$strTXT = strlen($row['descr']);
		if($textLength > 9 OR $strTXT > 600)
			$row['descr'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['id'].'">'.$row['descr'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['id'].', this.id)" id="hide_wall_rec_lnk'.$row['id'].'">Показать полностью..</div>';
				
		$tpl->set('{descr}', stripslashes($row['descr']));
		
		$tpl->set('{num}', '<span id="traf">'.$row['traf'].'</span> '.gram_record($row['traf'], 'subscribers'));
		if($row['traf']){
			$tpl->set('{num-2}', '<a href="/public'.$row['id'].'" onClick="groups.all_people(\''.$row['id'].'\'); return false">'.gram_record($row['traf'], 'subscribers2').'</a>');
			$tpl->set('{no-users}', '');
		} else {
			$tpl->set('{num-2}', '<span class="color777">Вы будете первым.</span>');
			$tpl->set('{no-users}', 'no_display');
		}
		
		//Права админа
		if($public_admin){
			$tpl->set('[admin]', '');
			$tpl->set('[/admin]', '');
			$tpl->set_block("'\\[not-admin\\](.*?)\\[/not-admin\\]'si","");
		} else {
			$tpl->set('[not-admin]', '');
			$tpl->set('[/not-admin]', '');
			$tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
		}
		
		//Проверка подписан юзер или нет
		if(stripos($row['ulist'], "|{$user_id}|") !== false)
			$tpl->set('{yes}', 'no_display');
		else
			$tpl->set('{no}', 'no_display');
			
		//Контакты
		if($row['feedback']){
			$tpl->set('[yes]', '');
			$tpl->set('[/yes]', '');
			$tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
			$tpl->set('{num-feedback}', '<span id="fnumu">'.$row['feedback'].'</span> '.gram_record($row['feedback'], 'feedback'));
			$sql_feedbackusers = $db->super_query("SELECT tb1.fuser_id, office, tb2.user_search_pref, user_photo, alias FROM `".PREFIX."_communities_feedback` tb1, `".PREFIX."_users` tb2 WHERE tb1.cid = '{$row['id']}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC LIMIT 0, 5", 1);
			foreach($sql_feedbackusers as $row_feedbackusers){
				if($row_feedbackusers['user_photo']) $ava = "/uploads/users/{$row_feedbackusers['fuser_id']}/50_{$row_feedbackusers['user_photo']}";
				else $ava = "/images/no_ava_50.png";
			    if($row_feedbackusers['alias']) $alias = "/{$row_feedbackusers['alias']}";
			    else $alias = "/id{$row_feedbackusers['fuser_id']}";
				$row_feedbackusers['office'] = stripslashes($row_feedbackusers['office']);
				$feedback_users .= "

					<div id=\"fm_modbody_big\" class=\"clear_fix\" style=\"padding: 5px 0px 8px 3px;\">

					<a href=\"{$alias}\" onClick=\"Page.Go(this.href); return false\">

					<div class=\"fm_profile_subj\">

					<img src=\"{$ava}\" alt=\"\" />

					</div>

					<div class=\"fm_psub_name\">{$row_feedbackusers['user_search_pref']}</div>

					</a>

					<div class=\"fm_psub_gren\"><small>{$row_feedbackusers['office']}</small></div>

					</div>

					";
			}
			$tpl->set('{feedback-users}', $feedback_users);
			$tpl->set('[feedback]', '');
			$tpl->set('[/feedback]', '');
		} else {
			$tpl->set('[no]', '');
			$tpl->set('[/no]', '');
			$tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
			$tpl->set('{feedback-users}', '');
			if($public_admin){
				$tpl->set('[feedback]', '');
				$tpl->set('[/feedback]', '');
			} else
				$tpl->set_block("'\\[feedback\\](.*?)\\[/feedback\\]'si","");
		}
		
		//Выводим подписчиков
		$sql_users = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo, alias FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$row['id']}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by rand() LIMIT 0, 6", 1);
		foreach($sql_users as $row_users){
			if($row_users['user_photo']) $ava = "/uploads/users/{$row_users['user_id']}/50_{$row_users['user_photo']}";
			else $ava = "/images/no_ava_50.png";
			if($row_users['alias']) $alias = "/{$row_users['alias']}";
			else $alias = "/id{$row_users['user_id']}";
			$users .= "

			<div class=\"fm_user_group\" id=\"subUser{$row_users['user_id']}\">
			
			<a class=\"cursor_pointer\" href=\"{$alias}\" onClick=\"Page.Go(this.href); return false\">
			
			<div><img src=\"{$ava}\" /></div></a><a href=\"{$alias}\" onClick=\"Page.Go(this.href); return false\">{$row_users['user_name']}</a></div>
			
			";
		}
		$tpl->set('{users}', $users); 
		
		$tpl->set('{id}', $row['id']);
		megaDate(strtotime($row['date']), 1, 1);
		
		//Комментарии включены
		if($row['comments'])
			$tpl->set('{settings-comments}', 'comments');
		else
			$tpl->set('{settings-comments}', 'none');
			
		//Выводим админов при ред. страницы
		if($public_admin){
			$admins_arr = str_replace('|', '', explode('id', $row['admin']));
			foreach($admins_arr as $admin_id){
				if($admin_id){
					$row_admin = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$admin_id}'");
					if($row_admin['user_photo']) $ava_admin = "/uploads/users/{$admin_id}/50_{$row_admin['user_photo']}";
					else $ava_admin = "/images/no_ava_50.png";
					if($admin_id != $row['real_admin']) $admin_del_href = "<a href=\"/\" onClick=\"groups.deladmin('{$row['id']}', '{$admin_id}'); return false\"><small>Удалить</small></a>";
					$adminO .= "

					<div class=\"facem_groups_oneadmin\" id=\"admin{$admin_id}\">

					<a href=\"/id{$admin_id}\" onClick=\"Page.Go(this.href); return false\">

					<img src=\"{$ava_admin}\" align=\"left\" width=\"32\" />

					</a>

					<a href=\"/id{$admin_id}\" onClick=\"Page.Go(this.href); return false\">{$row_admin['user_search_pref']}</a>

					<br />{$admin_del_href}</div>

					";
				}
			}
			
			$tpl->set('{admins}', $adminO);
		}

		$tpl->set('{records}', $tpl->result['wall']);
		
		//Стена
		if($row['rec_num'] > 10)
			$tpl->set('{wall-page-display}', '');
		else
			$tpl->set('{wall-page-display}', 'no_display');
			
		if($row['rec_num'])
			$tpl->set('{rec-num}', '<b id="rec_num">'.$row['rec_num'].'</b> '.gram_record($row['rec_num'], 'rec'));
		else {
			$tpl->set('{rec-num}', '<b id="rec_num">Нет записей</b>');
			if($public_admin)
				$tpl->set('{records}', '<div class="wall_none" style="border-top:0px">Новостей пока нет.</div>');
			else
				$tpl->set('{records}', '<div class="wall_none">Новостей пока нет.</div>');
		}
		
		//Выводим информцию о том кто смотрит страницу для себя
		$tpl->set('{viewer-id}', $user_id);
			
		if(!$row['adres']) $row['adres'] = 'public'.$row['id'];
		$tpl->set('{adres}', $row['adres']);

		//Аудиозаписи
		if($row['audio_num']){
			$sql_audios = $db->super_query("SELECT url, artist, name FROM `".PREFIX."_communities_audio` WHERE public_id = '{$row['id']}' ORDER by `adate` DESC LIMIT 0, 3", 1, "groups/audio{$row['id']}");
			$jid = 0;
			foreach($sql_audios as $row_audios){
				$jid++;
				
				$row_audios['artist'] = stripslashes($row_audios['artist']);
				$row_audios['name'] = stripslashes($row_audios['name']);
				
				$audios .= "
				
					<div class=\"fm_audio_onetrack_profile\" style=\"margin-left:0px;\">

					<div class=\"audio_playic cursor_pointer fl_l\" onclick=\"music.newStartPlay('{$jid}')\" id=\"icPlay_{$jid}\"></div>

					<span id=\"music_{$jid}\" data=\"{$row_audios['url']}\" style=\"overflow: hidden;height: 15px;display: block;float: left;width: 350px;\">

					<a href=\"/?go=search&query={$row_audios['artist']}&type=5&n=1\" onclick=\"Page.Go(this.href); return false\">

					<span id=\"artis{aid}\">{$row_audios['artist']}</span></a> &ndash; 

					<span id=\"name2\" style=\"color: #666;\">{$row_audios['name']}</span>

					</span>

					<div id=\"play_time{$jid}\" class=\"color777 fl_r no_display\" style=\"margin-right:5px\"></div>

					<div class=\"clear\"></div>

					<div class=\"player_mini_mbar fl_l no_display\" id=\"ppbarPro{$jid}\" style=\"width:440px\"></div> 

					<div class=\"clear\"></div>

					</div>	
				
				";
				
			}
			
			$tpl->set('{audios}', $audios);
			$tpl->set('{audio-num}', $row['audio_num']);
			$tpl->set('[audios]', '');
			$tpl->set('[/audios]', '');
			$tpl->set('[yesaudio]', '');
			$tpl->set('[/yesaudio]', '');
			$tpl->set_block("'\\[noaudio\\](.*?)\\[/noaudio\\]'si","");
			
		} else {
		
			$tpl->set('{audios}', '');
			$tpl->set('[noaudio]', '');
			$tpl->set('[/noaudio]', '');
			$tpl->set_block("'\\[yesaudio\\](.*?)\\[/yesaudio\\]'si","");
			
			if($public_admin){
				$tpl->set('[audios]', '');
				$tpl->set('[/audios]', '');
			} else
				$tpl->set_block("'\\[audios\\](.*?)\\[/audios\\]'si","");
			
		}

		//Обсуждения
		if($row['discussion']){
		
			$tpl->set('{settings-discussion}', 'discussion');
			$tpl->set('[discussion]', '');
			$tpl->set('[/discussion]', '');
			
		} else {
		
			$tpl->set('{settings-discussion}', 'none');
			$tpl->set_block("'\\[discussion\\](.*?)\\[/discussion\\]'si","");
			
		}
			
		if(!$row['forum_num']) $row['forum_num'] = '';
		$tpl->set('{forum-num}', $row['forum_num']);
		
		if($row['forum_num'] AND $row['discussion']){
			
			$sql_forum = $db->super_query("SELECT fid, title, lastuser_id, lastdate, msg_num FROM `".PREFIX."_communities_forum` WHERE public_id = '{$row['id']}' ORDER by `fixed` DESC, `lastdate` DESC, `fdate` DESC LIMIT 0, 5", 1, "groups_forum/forum{$row['id']}");
			
			foreach($sql_forum as $row_forum){
				
				$row_last_user = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$row_forum['lastuser_id']}'");
				$last_userX = explode(' ', $row_last_user['user_search_pref']);
				$row_last_user['user_search_pref'] = gramatikName($last_userX[0]).' '.gramatikName($last_userX[1]);
	
				$row_forum['title'] = stripslashes($row_forum['title']);
				
				$msg_num = $row_forum['msg_num'].' '.gram_record($row_forum['msg_num'], 'msg');

				$last_date = megaDateNoTpl($row_forum['lastdate']);

				$thems .= "<div class=\"forum_bg\"><div class=\"forum_title cursor_pointer\" onClick=\"Page.Go('/forum{$row['id']}?act=view&id={$row_forum['fid']}'); return false\">{$row_forum['title']}</div><div class=\"forum_bottom\">{$msg_num}. Последнее от <a href=\"/id{$row_forum['lastuser_id']}\" onClick=\"Page.Go(this.href); return false\">{$row_last_user['user_search_pref']}</a>, {$last_date}</div></div>";
				
			}
			
			$tpl->set('{thems}', $thems);
		
		} else 
			$tpl->set('{thems}', '<div class="wall_none">В сообществе ещё нет тем.</div>');

		//Статус
		$tpl->set('{status-text}', stripslashes($row['status_text']));
			
		if($row['status_text']){
		
			$tpl->set('[status]', '');
			$tpl->set('[/status]', '');
			$tpl->set_block("'\\[no-status\\](.*?)\\[/no-status\\]'si","");
			
		} else {
		
			$tpl->set_block("'\\[status\\](.*?)\\[/status\\]'si","");
			$tpl->set('[no-status]', '');
			$tpl->set('[/no-status]', '');
			
		}

		$tpl->set('{web}', $row['web']);
		
		if($row['web']){

			$tpl->set('[web]', '');
			$tpl->set('[/web]', '');
			
		} else
			
			$tpl->set_block("'\\[web\\](.*?)\\[/web\\]'si","");
		
		$tpl->compile('content');
	} else {
		$user_speedbar = $lang['no_infooo'];
		msgbox('', $lang['no_upage'], 'info');
	}
	
	$tpl->clear();
	$db->free();
} else {
	$user_speedbar = $lang['no_infooo'];
	msgbox('', $lang['not_logged'], 'info');
}
?>