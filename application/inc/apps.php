<?
if(!defined('MOZG')) die('И че ты тут забыл??');


if(isset($_GET['act'])){

//Если нажали "Добавить"
if(isset($_POST['saves'])){
	$id = intval($_POST['id']);
	$title = $_POST['title'];
	$desc = htmlspecialchars($_POST['desc']);
	
	
	$db->query("UPDATE `".PREFIX."_apps` SET `title`='{$title}', `desc`='{$desc}' WHERE id='{$id}'");
	header('Location: /nav.php?mod=apps&act=edit&id='.$id);
	die();
}

	$id = intval($_GET['id']);

	$row = $db->super_query("SELECT * FROM `".PREFIX."_apps` WHERE id='{$id}'");
	if($row['img']!= '') $img = '/uploads/apps/'.$row['id'].'/'.$row['img']; else $img = '/uploads/apps/no.gif';
	echoheader();
	echohtmlstart('Редактирование игры');
echo'
<script type="text/javascript" src="/js/jquery.lib.js"></script>
<div id=\'apps'.$row['id'].'\'>
<form action="?mod=apps&act=edit" method="POST">
<input type="hidden" name="id" value="'.$row['id'].'" />
<div class="fllogall" style="width:180px">Id приложения:</div>'.$row['id'].'
<div class="mgcler"></div>
<div class="fllogall" style="width:180px">Название:</div>
 <input type="text" name="title" class="inpu" value="'.$row['title'].'" />
<div class="mgcler"></div>
<div class="fllogall" style="width:180px">Описание:</div>
<textarea class="inpu" name="desc" style="height: 57px;">'.$row['desc'].'</textarea>
<div class="mgcler"></div>
<div class="fllogall" style="width:180px">Защищенный ключ:</div>
 <input type="text" class="inpu" value="'.$row['secret'].'" readonly />
<div class="mgcler"></div>
<div class="fllogall" style="width:180px">Баланс:</div>
 <input type="text" class="inpu" value="'.$row['balance'].'" readonly />
<div class="mgcler"></div>
<div class="fllogall" style="width:180px">&nbsp;</div>
	<input type="submit" value="Редактировать" class="inp" name="saves" style="margin-top:0px" />
</form>
<div style="width:234px;margin:20px auto;">
<img src="'.$img.'" width="215">
</div>
<center><a style=\'text-decoration:none;cursor:pointer;\' onClick=\'delete_app('.$row['id'].');\'>Удалить приложение</a></center>
<script>
function delete_app(id){
    $.post(\'/index.php?go=delete_app\', {id: id});
    $(\'#delete_app\'+id).show();
    $(\'#apps\'+id).hide();
}
</script>
</div>
<div style=\'display:none;\' id=\'delete_app'.$row['id'].'\'><center>Приложение успешно удалено.</center><br /><a style=\'text-decoration:none;cursor:pointer;\' onClick=\'javascript:history.go(-1);\'><center>Вернуться назад</center></a></div>
';

}else{

	echoheader();

	$sql_ = $db->super_query("SELECT id,url,cols,title,img FROM `".PREFIX."_apps` ORDER BY id DESC",1);
	$b=0;
	foreach($sql_ as $row){
		$num = $row['cols'];
		if($row['img']!= '') $img = '/uploads/apps/'.$row['id'].'/'.$row['img']; else $img = '/uploads/apps/no.gif';
		
		$words = Array("человек", "человека", "человек");
		$game .= '
		<div class="fl_l" style="float: left; text-align: center; height: 115px; margin-top: 15px; width: 150px;">
		<a href="?mod=apps&act=edit&id='.$row['id'].'"><img src="'.$img.'" width="75" height="75"><div>'.$row['title'].'</div></a>
		</div>';
		$b++;
	}
	echohtmlstart('Приложения ('.$b.')');
echo <<<HTML
<div>{$game}</div>
<div class="clr"></div>
HTML;

}
echohtmlend();
?>