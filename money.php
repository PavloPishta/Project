<?php

define('MOZG', true);
define('ROOT_DIR', dirname (__FILE__));
define('APPLICATION_DIR', ROOT_DIR.'/application');

header('Content-type: text/html; charset=utf-8');

@include APPLICATION_DIR.'/data/config.php';
include APPLICATION_DIR.'/classes/mysql.php';
include APPLICATION_DIR.'/data/db.php';


//Кодовое слово
$code_word = "$fsh45874dsfjg";

//Входные данные
$from = $_GET['from']; //Номер абонента, который отправил SMS
$date = $_GET['date']; //Дата отправки SMS в формате ГГГГ-ММ-ДД чч:мм:сс (пример: 2008-08-17 18:00:24)
$msg = $_GET['msg']; //Сообщение, отправленное абонентом
$cost = $_GET['cost']; //Ваша прибыль в RUR
$operator_id = $_GET['operator_id']; //идентификатор оператора (расшифровка в таблицах выше)
$country = $_GET['country']; //идентификатор страны (расшифровка в таблицах выше)
$short_number = $_GET['short_number']; //короткий номер
$sms_id = $_GET['sms_id']; //уникальный номер сообщения в нашей системе (ВНИМАНИЕ: номер не целочисленный, а строковый, т.е. могут быть латинские буквы, длина от 15 символов до 20). При проверке обработчика всегда равен значению "1debug" (без кавычек)
$abonent_cost = $_GET['abonent_cost']; //стоимость для оператора в RUR
$clear_msg = $_GET['clear_msg']; //сообщение без префиксов и сабпрефиксов (если оно есть)
$sign = $_GET['sign']; //параметр, защищающий от мошенничества. Принимает значение md5($_GET['sms_id'].'ваш_секретный_код'). Если вы не заполняли поле "секретный код", этот параметр будет пустым. Пример по использованию этого параметра приведен ниже
$sms_status = $_GET['sms_status'] ;
$sms_id=$_GET['sms_id'];
$pay_status=$_GET['pay_status'];

//если скрипт был вызван с неправильным параметром безопасности, завершить выполнение.
if($sign != md5($sms_id.$code_word)) die('hacking attempt');

$user_id =  str_replace('4245646', '', $msg);
$user_id =  intval($user_id);

if ($pay_status=="not_ok") {
   	$db->query("UPDATE `".PREFIX."_sms_log`SET abonent_cost = '0' WHERE sms_id = '{$sms_id}' AND cost=0");
   	die("ok\n");
} elseif ($pay_status=="ok") {
	$row = $db->super_query("SELECT * FROM `".PREFIX."_sms_log` WHERE sms_id = '{$sms_id}'");

    $db->query("UPDATE `".PREFIX."_users` SET balance_rub = balance_rub + '{$row[abonent_cost]}' WHERE user_id = '{$row[user_id]}'");
    die("ok\n");
}elseif ($pay_status == '' and $sms_status == 'normal') {
	//Проверка на существование юзера
	$row = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

	if($row['cnt'] AND $abonent_cost){

		$abonent_cost = $db->safesql($abonent_cost);
		$from = $db->safesql($from);
		$msg = $db->safesql($msg);
		$sms_id = $db->safesql($sms_id);
		$short_number = $db->safesql($short_number);
		$date = $db->safesql($date);
		$operator_id = intval($operator_id);
		$country = intval($country);

		//Начисляем
		if($country != '3') //Проверяем не украина ли это
			$db->query("UPDATE `".PREFIX."_users` SET balance_rub = balance_rub + '{$abonent_cost}' WHERE user_id = '{$user_id}'");

		//Вставляем в лог смс
		$db->query("INSERT INTO `".PREFIX."_sms_log` SET user_id = '{$user_id}', from_u = '{$from}', msg = '{$msg}', operator_id = '{$operator_id}', country = '{$country}', short_number = '{$short_number}', abonent_cost = '{$abonent_cost}', date = '{$date}', sms_id='{$sms_id}'");

		//Ответ
		echo "ok\n";
		echo "Spasibo! Vam nachisleno: {$abonent_cost} rub.";
	
	}else echo "ok\n not users id";

}
?>