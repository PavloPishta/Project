<?php
/* 
    Appointment: CLASS API
    File: api.php
 
*/
if(!defined('MOZG'))  die('Not Found');

$server_time = intval($_SERVER['REQUEST_TIME']);

$db->query("SET NAMES 'utf8'"); // Я почему то не смог нормально записать)

class Api {
   
    public static function security($userid,$method,$api_id,$sigr,$sigg){

        if($api_id == '') return 'Invalid app id';

        elseif($userid == '') return 'Invalid user id';

        elseif($method == '') return 'Invalid method api';

        elseif($sigg == $sigr) return 'Incorrect signature';

        return 'ok';

    }

    public static function getProfiles($userid){

        global $db;

        $data = $db->super_query("SELECT user_id, user_name, user_lastname, user_sex, user_last_visit, user_country_city_name, user_rate, user_birthday, user_city, user_photo FROM  `".PREFIX."_users` WHERE user_id = '{$userid}' LIMIT 1 ");

        if(isset($data)){

            $ApiResult = array('uid'=> $data['user_id'], 'first_name' => $data['user_name'], 'last_name' => $data['user_lastname'],  'photo' => 'http://koftagard.ru/uploads/users/'.$userid.'/'.$data['user_photo'], 'sex' => $data['user_sex'], 'rate' => $data['user_rate'], 'bdate' => $data['user_birthday'], 'city' => $data['user_city'] );

            return $ApiResult;

        } else {

            return "No user found";

        }

    }

    public static function isAppUser($userid,$api_id){

        global $db;

        $db->query("SELECT application_id FROM `".PREFIX."_apps_users` WHERE user_id = '{$userid}' and application_id = '{$api_id}'");

        if(!$db->num_rows()) $isApp = 0; else $isApp = 1;

            $ApiResult = array( 'response' => $isApp );

        return $ApiResult;

    }

    public static function getuserBalance($userid,$api_id){

        global $db;

        $data = $db->super_query("SELECT balance FROM `".PREFIX."_apps_users` WHERE user_id = '{$userid}' and application_id = '{$api_id}'");

        $ApiResult = array( 'response' => $data['balance'] );

        return $ApiResult;

    }

    public static function getAppBalance($api_id){

        global $db;

        $data = $db->super_query("SELECT balance FROM `".PREFIX."_apps` WHERE id = '{$api_id}'");

        $ApiResult = array( 'response' => $data['balance']);

        return $ApiResult;

    }

    public static function withdrawVotes($userid,$api_id,$votes){

        global $db;

        $data = $db->super_query("SELECT balance FROM `".PREFIX."_apps_users` WHERE user_id = '{$userid}' and application_id='{$api_id}'");

        if($votes != '') {

            if($votes <= $data['balance']){

                $db->query("UPDATE `".PREFIX."_apps_users` SET balance=balance-'{$votes}' WHERE user_id = '{$userid}' and application_id='{$api_id}' ");

                $db->query("INSERT INTO `".PREFIX."_apps_transactions` (`application_id`,`votes`,`from`,`whom`,`date`) VALUES ('".$api_id."','".$votes."','".$userid."','0','".time()."') "); 

                $db->query("UPDATE `".PREFIX."_apps` SET balance=balance+'{$votes}' WHERE id = '{$api_id}'");

                $ApiResult = array( 'response' => $votes);

            } else $ApiResult = array( 'error' => array('error_code' => '502', 'error_msg' => 'Not enough votes on user`s balance') );

        } else $ApiResult = array( 'error' => array('error_code' => '151', 'error_msg' => 'Invalid votes') );

        return $ApiResult;

    }

    public static function addRating($api_id,$rate,$userid){

        global $db;

        if($rate != '' and $uids != '') {

            if($rate <= $apps['balance']){

                $db->query("UPDATE `".PREFIX."_users` SET user_rate=user_rate+'{$rate}' WHERE user_id = '{$userid}'");

                $db->query("UPDATE `".PREFIX."_apps` SET balance=balance-'{$rate}' WHERE id = '{$api_id}'");

                $ApiResult = array( 'response' => array('rating_added' => $rate ) );

            } else $ApiResult = array( 'error' => array('error_code' => '147', 'error_msg' => 'Application has insufficient funds') );

        } else $ApiResult = array( 'error' => array('error_code' => '100', 'error_msg' => 'One of the parameters specified was missing or invalid.') );

        return $ApiResult;

    }

    public static function wall($userid,$api_id,$message,$owner_id){

        global $db;

        if($message!='' and $owner_id!=''){

            $torr ='Отправлено через <a href="/app'. $api_id .'">'.$apps['title'].'</a>';

            $db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$userid}', for_user_id = '{$owner_id}', text = '{$message}', add_date = '{$server_time}', type = '{$torr}'");

			$dbid = $db->insert_id();

			$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$owner_id}'");

            $ApiResult = array( 'response' => array('post_id' => $dbid ) );

        } else $ApiResult = array( 'error' => array('error_code' => '100', 'error_msg' => 'One of the parameters specified was missing or invalid.') );

        return $ApiResult;

    }

}

?>