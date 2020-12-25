<?php
@session_start();
@ob_start();
@ob_implicit_flush(0);

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

define('MOZG', true);
define('ROOT_DIR', dirname (__FILE__));
define('APPLICATION_DIR', ROOT_DIR.'/application');
define('ADMIN_DIR', ROOT_DIR.'/application/inc');

@include APPLICATION_DIR.'/data/config.php';

if(!$config['home_url']) die("");

$admin_link = $config['home_url'].'nav.php';

include APPLICATION_DIR.'/classes/mysql.php';
include APPLICATION_DIR.'/data/db.php';
include ADMIN_DIR.'/functions.php';
include ADMIN_DIR.'/login.php';

$db->close();
?>