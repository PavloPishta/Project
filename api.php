<?php 

/*
	Appointment: API 
    File: api.php

*/

define('MOZG', true);

define('ROOT_DIR', dirname (__FILE__));

define('APPLICATION_DIR', ROOT_DIR.'/application');


@include APPLICATION_DIR.'/data/config.php';

include APPLICATION_DIR.'/classes/mysql.php';

include APPLICATION_DIR.'/data/db.php';

require_once(APPLICATION_DIR.'/api_class/api.php');


$server_time = intval($_SERVER['REQUEST_TIME']);

$api_id = intval($_REQUEST['api_id']);

$userid = intval($_REQUEST['uids']);

$method = $_REQUEST['method'];

$db->query("SET NAMES 'utf8'"); // Я почему то не смог нормально записать)

$apps = $db->super_query("SELECT * FROM `".PREFIX."_apps` WHERE id = '{$api_id}'");


if(!intval($_POST['api_id']) && !intval($_POST['uids']) && !$_POST['method']){

	$sigr = $_GET['sig'];

	$params['api_id'] = $api_id;

	$params['uids'] = $userid;

	$params['method'] = $method;

	if($_REQUEST['message']) $params['message'] = $_REQUEST['message'];

	if(intval($_REQUEST['owner_id'])) $params['owner_id'] = intval($_REQUEST['owner_id']);

	if(intval($_REQUEST['rate'])) $params['rate'] = intval($_REQUEST['rate']);

	ksort($params);

	$sig = '';

	foreach($params as $k=>$v) {

		$sig .= $k.'='.$v;

	}

	$sig .= $apps['secret'];

	$sigg = md5($sig);

	$security = Api::security($userid,$method,$api_id,$sigr,$sigg);

} else $security = 'ok';

if($security == 'ok'){

	switch($method){

		case 'getProfiles':

        		$ApiResult = Api::getProfiles($userid);

			break;

		case 'isAppUser':

				$ApiResult = Api::isAppUser($userid,$api_id);

			break;

		case 'secure.getBalance':

				$ApiResult = Api::getuserBalance($userid,$api_id);

			break;

		case 'secure.getAppBalance':

				$ApiResult = Api::getAppBalance($api_id);

			break;

		case 'secure.withdrawVotes':

				$votes = intval($_REQUEST['votes']);

				$ApiResult = Api::withdrawVotes($userid,$api_id,$votes);

			break;

		case 'secure.addRating':

				$rate = intval($_REQUEST['rate']);

				$ApiResult = Api::addRating($api_id,$rate,$userid);

			break;

		case 'wall.post':

				$message = $_REQUEST['message'];

				$owner_id = intval($_REQUEST['owner_id']);

				$ApiResult = Api::wall($userid,$api_id,$message,$owner_id);

			break;

	}

} else $ApiResult = array( 'error' => array('error_msg' => $security));

//} else $ApiResult = array( 'error' => array('error_msg' => $security, 'qwe' => $userid.' '.$method.' '.$api_id.' '.$sigr.' '.$sigg));

echo json_encode($ApiResult);

?>