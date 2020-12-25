<?php

if(!defined('MOZG'))
	die('Not Found');

if(!$logged){
    $metatags['title'] = 'Вход на сайт';
	$user_speedbar = 'Вход на сайт';	
	$msg = intval($_GET['msg']);

	$tpl->set('{mssg}', $msg);
	if($msg) {
	if($msg==1)$tpl->set('{msg}', '<div class="news_ad" style="font-weight: 700;padding: 10px;border: 1px solid #D5C2C2;background: #F0E6E2;color: #555;">Этот почтовый адрес не зарегистрирован, либо пароль неверный. Ну и что теперь делать?<br><br>1. Внимательно проверь раскладку клавиатуры, не включена ли кнопка Caps Lock, попробуй ввести пароль и адрес ещё раз, не торопясь.<br><br>2. Если пароль забыт, но ты помнишь е-майл, и он реально существует - <a href="/restore" onClick="Page.Go(this.href);">жми сюда</a>, и новый пароль придёт тебе на е-майл.<br><br>3. Иногда случается, что пользователи допускают опечатки при регистрации и потом не могут войти с правильными данными. Тогда придётся <a href="/signup" onClick="Page.Go(this.href);">регистрироваться</a> снова и выяснять что случилось со старой страницей. Не забудь указать её адрес или имя пользователя, когда будешь описывать проблему.</div>');
	}
	else
	$tpl->set('{msg}', '');
	
	$tpl->load_template('login.html');
    $tpl->compile('content');	
}else{
	msgbox('', 'Вы уже вошли на сайт.', 'info');
}

?>