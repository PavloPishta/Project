<?php
/*
|---------------------------------------------------------------
| Css компрессор для сжатия.. WWW.FACEMY.ORG
|---------------------------------------------------------------
| header — Отправка HTTP заголовка
|---------------------------------------------------------------
*/

header('Content-type: text/html; charset=utf-8');

header("Content-type: text/css");

$file = $_GET['id'];

switch($file){

	case "compression":

		$fileDirectory = 'css/';

		$nameExplode = explode('.', $file);

		$ext = $nameExplode[1];

		$fileName = $fileDirectory . $file . '.css';

			if ($ext != 'css') {

			//Начнем
			$handle = fopen($fileName, 'r');

			$fileData = fread($handle, filesize($fileName));

			//Чудеса регулярных выражений
			$newData = preg_replace('/\s+/', ' ', $fileData);

			fclose($handle);

			echo $newData;

		}

		exit();

	break;

default:

}

?>