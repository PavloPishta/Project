<?
header('Access-Control-Allow-Origin:*');
require_once 'viiapi.class.php';
$app_id = 1; // id ����������
$api_secret = '123'; // ��� �������� ��� � ����������
$uid = '1'; // ��� ������������
$VII = new viiapi($app_id, $api_secret);
if($_POST['lol']){
$resp = $VII->api('wall.post', array('uids'=>$uid,'message' =>'13213212131231����5��4�231321', 'owner_id' =>$uid));
}
$resp = $VII->api('getProfiles', array('uids'=>$uid));
echo 'Uid:' .$resp['uid'];
echo '<br>���:' .$resp['first_name'];
echo '<br>�������: '.$resp['last_name'];
echo '<br>���: '.$resp['sex'];
echo '<br>��� ��������: '.$resp['bdate'];
echo '<br>�������: '.$resp['rate'];
echo '<br>����������:  <img src="'.$resp['photo'].'">';
echo '<br> <form name="test" method="post" action="in.php"><input type="submit" name="lol" value="�������� �������� ������ �� ����� ������������ id 1 " ></form> ';
//
echo '<br>����� ����������� ����� � ��� ������� ����� ��������! ������ ��� ����� ��� �� ������ ������ �� �������:<br>';
print_r($resp);

?>
