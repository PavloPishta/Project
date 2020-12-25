<?php
/* 
    Appointment: Граффити
    File: graffiti.php
 
*/
if(!defined('MOZG'))
    die("Not Found");
if($ajax == 'yes')
    NoAjaxQuery();
 
    $user_id = $user_info['user_id'];
 
if($logged){
    $act = $_GET['act'];
    $for_user_id =  intval($_GET['id']);
    $str_date = time();
    switch($act){
         
//################### Отображения шаблона Граффити ###################//
        case "add":
        echo'<script src="/js/swfobject.js"></script>
            <embed type="application/x-shockwave-flash" src="/swf/graffiti.swf" width="580" height="385" style="undefined" id="player" name="player" quality="high" allowfullscreen="false" flashvars="overstretch=false&amp;postTo=/index.php?go=graffiti%26id='.$for_user_id.'%26act=send&amp;redirectTo=/id'.$for_user_id.'"\></embed>';
        exit;
        break;
 
//################### Процесс сохранения Граффити ###################//

 case "send":
 NoAjaxQuery();
 $id = intval($_GET['id']);
    $user_id = $user_info['user_id'];
    if(isset($_FILES['Filedata']))
    {
        $fHandle = fopen($_FILES['Filedata']['tmp_name'], "rb");
        if($fHandle)
        {
            $fData = bin2hex(fread($fHandle, 32));
            if($fData == "89504e470d0a1a0a0000000d494844520000024a0000012508060000001b69cd")
            {
                $fImageData	= getimagesize($_FILES['Filedata']['tmp_name']);
                if($fImageData[0] == 586 && $fImageData[1] == 293)
                {
                    $file_time	= time();
                    $file_rand	= rand(0,9);
                    $file_time	= $file_time . $file_rand;
                    $file_name	= md5($file_time) . ".png";
                    $i			= 0;
                    while(file_exists($file_name) && $i < 20)
                    {
                        // Берем увеличение 20x, но не факт, что что-то выйдет.
                        $i++;
                        // Если такой файл уже есть, то создаем новый.
                        $file_time	= time();
                        $file_rand	= rand(0,9)+$i;
                        $file_time	= $file_time . $file_rand;
                        $file_name	= md5($file_time) . ".png";
                    }
                    $origImage = imagecreatefrompng($_FILES['Filedata']['tmp_name']);
                    $newImage	= imagecreatetruecolor(600, 300);
                    imagecopyresized($newImage, $origImage, 0, 0, 0, 0, 600, 300, $fImageData[0], $fImageData[1]);
                    imagepng($newImage, ROOT_DIR."/uploads/graffiti/".$file_name);
                    $date = time();
                    $image_wall = "<div class=\"profile_wall_attach_photo cursor_pointer\"><img id=\"fm-wall_graffiti\" onclick=\"showImg(this.src); return false\" src=\"/uploads/graffiti/".$file_name."\" border=\"0\" /></div>";
                    
                    //Вставляем саму запись в БД
                    $db->super_query("UPDATE `".PREFIX."_users` SET user_wall_num=user_wall_num+1 WHERE user_id = '$id'");
				    $db->super_query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '$user_id', for_user_id = '$id', text = '$image_wall', add_date = '$date'");
                    
                    //Чистим кеш
					mozg_clear_cache_file('user_'.$id.'/profile_'.$id);

					}}}}
 
                    break;
 
    }
    $tpl->clear();
    $db->free();
} else {
    $user_fm_wrap_bar = $lang['no_infooo'];
    msgbox('', $lang['not_logged'], 'info');
}
 
?>