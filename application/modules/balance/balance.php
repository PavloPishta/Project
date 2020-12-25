<?php

if(!defined('MOZG'))

	die('Not Found');

if($ajax == 'yes')

	NoAjaxQuery();

if($logged){

	$act = $_GET['act'];

	$user_id = $user_info['user_id'];

	$for_user_id = intval($_POST['for_user_id']);

	$nums = str_replace("-", '', $_POST['num']);

	$balanc = $db->super_query("SELECT user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

	$metatags['title'] = $lang['balance'];

	switch($act){

	//################### Выводим фотографию юзера при указании ИД страницы ###################//
  
	case "checkPaymentUser":

	NoAjaxQuery();

		$id = intval($_POST['id']);

		$row = $db->super_query("SELECT user_photo, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$id}'");

		if($row) echo $row['user_search_pref']."|".$row['user_photo'];

		die();

	break;
  
	//################### Окно передачи голосов ###################//

	case "payment_2":

		if($for_user_id){

		if($balanc['user_balance'] >= "$nums"){

		//###### Считываем и перезаписываем ######//

			$db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance+{$nums} WHERE user_id = '{$for_user_id}'");

			$db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance-{$nums} WHERE user_id = '{$user_id}'");

			$db->query("INSERT INTO `".PREFIX."_historytab` SET user_id = '{$user_id}', for_user_id = '{$for_user_id}', title='', type = '4', price='{$nums}', status = '-', date = '{$server_time}'");

			$db->query("INSERT INTO `".PREFIX."_historytab` SET user_id = '{$for_user_id}', for_user_id = '{$user_id}', title='', type = '11', price='{$nums}', status = '+', date = '{$server_time}'");

			//Вставляем событие в моментальные оповещания

			$row_owner = $db->super_query("SELECT user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");

			$update_time = $server_time - 70;

			if($row_owner['user_last_visit'] >= $update_time){

				$db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_info['user_id']}', type = '15', date = '{$server_time}', text = '+{$nums}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/balance'");

				mozg_create_cache("user_{$for_user_id}/updates", 1);

			}

			echo '';

		}

	}

	//################### Подщет пользователей ###################//

    NoAjaxQuery();

		$rowus = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id");

		$owner = $db->super_query("SELECT user_photo, user_id, user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

			$tpl->load_template('balance/payment.html');

			if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");

				else	$tpl->set('{ava}', "/images/no_ava_50.png");

						$tpl->set('{balance}', $owner['user_balance']);

						$tpl->set('{text-balance}', declOfNum($owner['user_balance'], array('голос', 'голоса', 'голосов')));

						$tpl->set('{cnt}', $rowus['cnt']);

						$tpl->set('{userid}', $row['user_id']);

						$tpl->compile('content');

					AjaxTpl();

				die();

			$tpl->clear();

		$db->free();

	break;
  
  	//################### Окно информации ###################//

	case "metodbox":

		NoAjaxQuery();

		$owner = $db->super_query("SELECT user_photo, user_id, user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

		$tpl->load_template('balance/metodbox.html');

			if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");

				else	$tpl->set('{ava}', "/images/no_ava_50.png");

						$tpl->set('{balance}', $owner['user_balance']);

						$tpl->set('{text-balance}', declOfNum($owner['user_balance'], array('голос', 'голоса', 'голосов')));

				$tpl->compile('content');

			AjaxTpl();

		exit();

	break;
 
	//################### Окно invite ###################//

	case "metodbox_invite":

	NoAjaxQuery();

	$owner = $db->super_query("SELECT user_photo, user_id, user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

	$tpl->load_template('balance/metodbox_invite.html');

		if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");

			else	$tpl->set('{ava}', "/images/no_ava_50.png");

					$tpl->set('{balance}', $owner['user_balance']);

					$tpl->set('{text-balance}', declOfNum($owner['user_balance'], array('голос', 'голоса', 'голосов')));

					$tpl->set('{uid}', $user_info['user_id']);

					$tpl->compile('content');

				AjaxTpl();

			die();

		$tpl->clear();

	$db->free();

	break;

	//################### Страница оплаты ###################//

	case "metodbox_rubcmc":

		NoAjaxQuery();

		$owner = $db->super_query("SELECT balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
			$tpl->load_template('balance/metodbox_rubcmc.html');

			if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");

				else	$tpl->set('{ava}', "/images/no_ava_50.png");

						$tpl->set('{rub}', $owner['balance_rub']);

						$tpl->set('{text-rub}', declOfNum($owner['balance_rub'], array('рубль', 'рубля', 'рублей')));

						$tpl->set('{user-id}', $user_info['user_id']);

					$tpl->compile('content');

				AjaxTpl();

			exit();

		break;
		
		//################### Страница покупки голосов ###################//

		case "metodbox_rub":

			NoAjaxQuery();
			
			$owner = $db->super_query("SELECT user_balance, balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
				$tpl->load_template('balance/metodbox_rub.html');

				if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");

					else	$tpl->set('{ava}', "/images/no_ava_50.png");

							$tpl->set('{balance}', $owner['user_balance']);

							$tpl->set('{text-balance}', declOfNum($owner['user_balance'], array('голос', 'голоса', 'голосов')));

							$tpl->set('{rub}', $owner['balance_rub']);

							$tpl->set('{cost}', $config['cost_balance']);

						$tpl->compile('content');

					AjaxTpl();

			exit();

		break;

		//################### Завершение покупки голосов ###################//

		case "ok_payment":
		
			NoAjaxQuery();

			$num = intval($_POST['num']);

				if($num <= 0) $num = 0;

					$resCost = $num * $config['cost_balance'];

					//Выводим тек. баланс юзера (руб.)

					$owner = $db->super_query("SELECT balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
				if($owner['balance_rub'] >= $resCost){
			
					$db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance + '{$num}', balance_rub = balance_rub - '{$resCost}' WHERE user_id = '{$user_id}'");
					$db->query("INSERT INTO `".PREFIX."_historytab` SET user_id = '{$user_id}', for_user_id = '{$user_id}', title='', type = '5', price='{$num}', status = '+', date = '{$server_time}'");

				} else

				echo '1';
				
			exit();

		break;

		//################### История операции ###################//

		case "history":

			$tpl->load_template('balance/history.html');

			$tpl->compile('info');

			$sql_ = $db->super_query("SELECT tb1.user_id,title,for_user_id,price,type,date,status,tb2.user_photo,tb2.user_search_pref FROM `".PREFIX."_historytab` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb2.user_id = tb1.for_user_id ORDER by `date` DESC LIMIT 20", 1);
            
			if($sql_){

				$tpl->load_template('balance/HistoryOperation.html');

				foreach($sql_ as $row){

			     	$tpl->set('{price}', $row['price']);

					if($row['status']=='+')$tpl->set('{status}', '+');

					if($row['status']=='-')$tpl->set('{status}', '-');

					if($row['type']==1)$tpl->set('{type}', 'Подарок для <a href="/id'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">'.$row['user_search_pref'].'</a>');
                    if($row['type']==2)$tpl->set('{type}', 'За приглашение <a href="/id'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">'.$row['user_search_pref'].'</a>');
					if($row['type']==3)$tpl->set('{type}', 'Вход на сайт');
					if($row['type']==4)$tpl->set('{type}', 'Перевод для <a href="/id'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">'.$row['user_search_pref'].'</a>');
					if($row['type']==5)$tpl->set('{type}', 'Зачисление через платёжную систему');
					if($row['type']==6)$tpl->set('{type}', '<a href="/app'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">Приложение</a>');
					if($row['type']==7)$tpl->set('{type}', 'Покупка рекламы');
					if($row['type']==8)$tpl->set('{type}', 'Удаление рекламы');
					if($row['type']==9)$tpl->set('{type}', 'Оценка фото <a href="/id'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">'.$row['user_search_pref'].'</a>');
					if($row['type']==10)$tpl->set('{type}', 'Повышение рейтинга <a href="/id'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">'.$row['user_search_pref'].'</a>');
					if($row['type']==11)$tpl->set('{type}', 'Перевод от <a href="/id'.$row['for_user_id'].'" onclick="Page.Go(this.href); return false">'.$row['user_search_pref'].'</a>');

					megaDate($row['date'], 1, 1);

					$tpl->compile('content');

				}

			} else msgbox('', '<br /> <br />Вы еще не совершали никаких действий... <br /> <br /><br />', 'info_2');

		break;

		//################### Страница приглашённых друзей ###################//

		case "invited":

			$tpl->load_template('balance/invited.html');

			$tpl->compile('info');

			$sql_ = $db->super_query("SELECT tb1.ruid, tb2.user_name, user_search_pref, user_birthday, user_last_visit, user_photo FROM `".PREFIX."_invites` tb1, `".PREFIX."_users` tb2 WHERE tb1.uid = '{$user_id}' AND tb1.ruid = tb2.user_id", 1);

			if($sql_){

				$tpl->load_template('balance/invitedUser.html');

				foreach($sql_ as $row){

					$user_country_city_name = explode('|', $row['user_country_city_name']);

					$tpl->set('{country}', $user_country_city_name[0]);

					if($user_country_city_name[1])

						$tpl->set('{city}', ', '.$user_country_city_name[1]);

					else

						$tpl->set('{city}', '');

						$tpl->set('{user-id}', $row['ruid']);
						
						$tpl->set('{name}', $row['user_search_pref']);

					if($row['user_photo'])

						$tpl->set('{ava}', '/uploads/users/'.$row['ruid'].'/100_'.$row['user_photo']);

					else

						$tpl->set('{ava}', '/images/no_ava.gif');

					//Возраст юзера

					$user_birthday = explode('-', $row['user_birthday']);

					$tpl->set('{age}', user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]));
					
					OnlineTpl($row['user_last_visit']);

					$tpl->compile('content');

				}

			} else

				msgbox('', '<br /><br />Вы еще никого не приглашали.<br /><br /><br />', 'info_2');

		break;

		default:

			//################### Вывод текущего счета ###################//

			$owner = $db->super_query("SELECT user_balance, balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

			$tpl->load_template('balance/main.html');

			$tpl->set('{balance}', $owner['user_balance']);

			$tpl->set('{text-balance}', declOfNum($owner['user_balance'], array('голос', 'голоса', 'голосов')));

			$tpl->set('{rub}', $owner['balance_rub']);

			$tpl->set('{text-rub}', declOfNum($owner['balance_rub'], array('рубль', 'рубля', 'рублей')));

			$tpl->compile('content');

	}

	$tpl->clear();

	$db->free();

} else {

	$user_fm_wrap_bar = $lang['no_infooo'];

	msgbox('', $lang['not_logged'], 'info');

}

?>