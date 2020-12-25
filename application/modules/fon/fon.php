<?php
/* 
	Appointment: Фон
	File: fon.php
	Author: Александр Конотопский
	Url: http://www.facemy.org
 
*/
if(!defined('MOZG'))
	die('Не найдено.');

if($ajax == 'yes')
	NoAjaxQuery();
	
if($logged){
	$act = $_GET['act'];
	$user_img_fon = $_POST['user_img_fon'];
	$user_id = $user_info['user_id'];
	switch($act){

	//Загрузка фотографии на облик
	case "upload_fon":

		//проверяем запись пользователя с базы.
		$user_id = intval($user_info['user_id']);
		$sql_delete = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_img_fon FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
	
			//удаляем фото если оно имеется, если нету то идем дальше.
			foreach($sql_delete as $row_del){
				if(file_exists(ROOT_DIR.''.$row_del) && ($row_del!='')){
					unlink(ROOT_DIR.''.$row_del);
					chmod(ROOT_DIR.''.$row_del,0777);
				}
			}

			//Подключаем класс для фотографий
			include APPLICATION_DIR.'/classes/images.php';
			
			$user_id = $user_info['user_id'];
			$uploaddir = ROOT_DIR.'/uploads/fon/';
			
			//Если нет папок юзера, то создаём её
			if(!is_dir($uploaddir.$user_id)){ 
				@mkdir($uploaddir.$user_id, 0777 );
				@chmod($uploaddir.$user_id, 0777 );
			}
			
			//Разришенные форматы
			$allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');
			
			//Получаем данные о фотографии
			$image_tmp = $_FILES['uploadfile']['tmp_name'];
			$image_name = totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
			$image_rename = substr(md5($server_time+rand(1,100000)), 0, 15); // имя фотографии
			$image_size = $_FILES['uploadfile']['size']; // размер файла
			$type = end(explode(".", $image_name)); // формат файла
			
			//Проверям если, формат верный то пропускаем
			if(in_array($type, $allowed_files)){
				if($image_size < 5000000){
					$res_type = '.'.$type;
					$uploaddir = ROOT_DIR.'/uploads/fon/'.$user_id.'/'; // Директория куда загружать
					if(move_uploaded_file($image_tmp, $uploaddir.$image_rename.$res_type)) {

						//Создание оригинала
						$tmb = new thumbnail($uploaddir.$image_rename.$res_type);
						$tmb->size_auto(770);
						$tmb->jpeg_quality(95);
						$tmb->save($uploaddir.$image_rename.$res_type);
						
						$image_rename = $db->safesql($image_rename);
						$res_type = $db->safesql($res_type);
						
						$fon_facemy = '/uploads/fon/'.$user_id.'/'.$image_rename.$res_type;
						
						//Заносим данные об обложке в бд
						$db->query("UPDATE `".PREFIX."_users` SET user_img_fon = '{$fon_facemy}' WHERE user_id = '{$user_id}'");	
						echo '/uploads/fon/'.$user_id.'/'.$image_rename.$res_type;

					} else
						echo 'bad';
				} else
					echo 'big_size';
			} else
				echo 'bad_format';
		die();
	break;

	//Чистим фон
	case "del_fon":
	
		//проверяем запись пользователя с базы.
		$user_id = intval($user_info['user_id']);
		$sql_delete = $db->super_query("SELECT SQL_CALC_FOUND_ROWS user_img_fon FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
			
			//удаляем саму фотографию
			foreach($sql_delete as $row_del){
				if(file_exists(ROOT_DIR.''.$row_del) && ($row_del!='')){
					unlink(ROOT_DIR.''.$row_del);
					chmod(ROOT_DIR.''.$row_del,0777);
				}
			}
			
			//Удаляем запись с базы.
			if($sql_delete){
				$db->query("UPDATE LOW_PRIORITY `".PREFIX."_users` SET user_img_fon = '' WHERE user_id = '{$user_id}'");	
			}
	break;
	default:

	//Подгрузка
	$row_fon = $db->super_query("SELECT user_img_fon FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");
		if($row_fon['user_img_fon']){
			$tpl->set('{fon_facemy}', $row_fon['user_img_fon']);					
		} else {						
			$tpl->set('{fon_facemy}', '/images/bg_top.jpg');
		}
		$tpl->load_template('fon/main.html');
		$tpl->set('{uid}', $user_id);
		$tpl->compile('content');
		AjaxTpl();
	die();

	}

}
?>