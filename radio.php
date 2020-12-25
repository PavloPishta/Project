<?php

define('ROOT_DIR', dirname (__FILE__));

define('APPLICATION_DIR', ROOT_DIR.'/application');

@include APPLICATION_DIR.'/data/config.php';

if(!$config['home_url']) die("TO Not installed");

header('Content-type: text/html; charset=utf-8');

?>

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

</head>

<script type="text/javascript" src="<?php echo $config['home_url']; ?>js/index.js"></script>

<style>

#pfm_radio{
	padding:10px;
	width: 180px;
}

#pfm_info{
	float: left;
	margin-top: -160px;
	color: #FFF;
	font-weight: bold;
	margin-left: 200px;
}

#pfm_text{
	font-size:25px;
}

.pfm_color_red{color:#D6E248;}

.pfm_color_red:hover{color:#C25930;}

a {text-decoration: none; color: #C25930;}

a:hover {text-decoration: none; color: #D6E248;}

</style>

<body>

<div id="pfm_radio" class="fl_l">

<object width="150" height="150" id="mju">

<param name="allowScriptAccess" value="sameDomain" />

<param name="swLiveConnect" value="true" />

<param name="movie" value="<?php echo $config['home_url']; ?>swf/mju.swf" />

<param name="flashvars" value="file=http://188.138.1.7:8050/753newfminua?type=.flv" />

<param name="loop" value="false" />

<param name="menu" value="false" />

<param name="quality" value="high" />

<param name="wmode" value="transparent" />

<embed src="<?php echo $config['home_url']; ?>swf/mju.swf" flashvars="file="http://188.138.1.7:8050/753newfminua?type=.flv" loop="false" menu="false" quality="high" wmode="transparent" bgcolor="#000000" width="170" height="150" allowScriptAccess="sameDomain" swLiveConnect="true" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />

</object>

</div>

<div id="pfm_info">

<b id="pfm_text">Э</b>нергичная и стильная танцевальная интернет радиостанция Украины с качественной клубной и поп-музыкой. В эфире царит атмосфера летней фиесты, которую создают мелодичная современная клубная и стильная музыка, новинки и стопроцентные танцевальные хиты <br />

</div>

</body>

</html>