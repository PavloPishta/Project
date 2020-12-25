<?

header('Access-Control-Allow-Origin:*');

require_once('conapi.class.php');

$app_id = 78; // id приложения

$api_secret = 'aba4b81278475f809161bb0e5cad9af7'; // Ваш защитный код в приложении

$viewer_id = intval($_REQUEST['viewer_id']); // уид порсмотра

$uid = intval($_REQUEST['user_id']); //уид пользователя

$FM = new conapi($app_id, $api_secret);

if($_POST['message_wall']){

	$resp = $FM->api('wall.post', array('uids'=>$uid, 'message' =>'test999', 'owner_id' =>$uid));

}

$resp = $FM->api('getProfiles', array('uids'=>$uid));

echo '<br>Uid:' .$resp['uid'];

echo '<br>Имя:' .$resp['first_name'];

echo '<br>Фамилия: '.$resp['last_name'];

echo '<br>Пол: '.$resp['sex'];

echo '<br>Год рождения: '.$resp['bdate'];

echo '<br>Рейтинг: '.$resp['rate'];

echo '<br>Фотография:  <img src="'.$resp['photo'].'">';

echo '<br> <form name="test" method="post" action="index.php"><input type="submit" name="message_wall" value="Оставить тестовую запись на стене" ></form> ';

print_r($resp);

?>