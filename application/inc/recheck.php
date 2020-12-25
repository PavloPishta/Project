 <?php

if(!defined('MOZG'))
    die("Hacking attempt!");
if($ajax == 'yes')
    NoAjaxQuery();
 
    $user_id = $user_info['user_id'];
        $act = $_GET['act'];
if($logged){

// Пересчитываем показатели одного пользователя
    switch($act){
    case "recheck":

//Пересчитываем друзей
$check_friends = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND subscriptions = 0");
$db->query("UPDATE `".PREFIX."_users` SET user_friends_num = '{$check_friends['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем записи на стене
$check_wall = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$user_id}'");
$db->query("UPDATE `".PREFIX."_users` SET user_wall_num = '{$check_wall['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем заметки
$check_notes = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_notes` WHERE owner_user_id = '{$user_id}'");
$db->query("UPDATE `".PREFIX."_users` SET user_notes_num = '{$check_notes['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем видеозаписи
$check_videos = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_videos` WHERE owner_user_id = '{$user_id}'");
$db->query("UPDATE `".PREFIX."_users` SET user_videos_num = '{$check_videos['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем личные сообщения
$check_msg = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_messages` WHERE for_user_id = '{$user_id}' AND pm_read = 'no' AND folder = 'inbox'");
$db->query("UPDATE `".PREFIX."_users` SET user_pm_num = '{$check_msg['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем подписки
$check_subscriptions = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND subscriptions = 1");
$db->query("UPDATE `".PREFIX."_users` SET user_subscriptions_num = '{$check_subscriptions['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем альбомы
$check_albums = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_albums` WHERE user_id = '{$user_id}'");
$db->query("UPDATE `".PREFIX."_users` SET user_albums_num = '{$check_albums['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем подарки
$check_gifts = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_gifts` WHERE uid = '{$user_id}'");
$db->query("UPDATE `".PREFIX."_users` SET user_gifts = '{$check_gifts['cnt']}' WHERE user_id = '{$user_id}'");
//

//Пересчитываем заявки в друзья
$check_demands = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_friends_demands` WHERE for_user_id = '{$user_id}'");
$db->query("UPDATE `".PREFIX."_users` SET user_friends_demands = '{$check_demands['cnt']}' WHERE user_id = '{$user_id}'");
//

// И после всего этого чистим кешик
mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);

        break;
header("Location: /settings");
}
} else {
    $user_speedbar = $lang['no_infooo'];
    msgbox('', $lang['not_logged'], 'info');
}
?> 