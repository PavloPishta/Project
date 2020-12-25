<?
header('Access-Control-Allow-Origin:*');
require_once 'viiapi.class.php';
$app_id = 1; // id приложения
$api_secret = '123'; // Ваш защитный код в приложении
$uid = '1'; // уид пользователя
$VII = new viiapi($app_id, $api_secret);
if($_POST['lol']){
$resp = $VII->api('wall.post', array('uids'=>$uid,'message' =>'13213212131231фывф5ыв4ы231321', 'owner_id' =>$uid));
}
$resp = $VII->api('getProfiles', array('uids'=>$uid));
echo 'Uid:' .$resp['uid'];
echo '<br>Имя:' .$resp['first_name'];
echo '<br>Фамилия: '.$resp['last_name'];
echo '<br>Пол: '.$resp['sex'];
echo '<br>Год рождения: '.$resp['bdate'];
echo '<br>Рейтинг: '.$resp['rate'];
echo '<br>Фотография:  <img src="'.$resp['photo'].'">';
echo '<br> <form name="test" method="post" action="in.php"><input type="submit" name="lol" value="Оставить тестовую запись на стене пользователя id 1 " ></form> ';
//
echo '<br>Можно реализовать много я вам показал самое основное! Данные эти взяты вот из такого ответа от сервера:<br>';
print_r($resp);

?>
