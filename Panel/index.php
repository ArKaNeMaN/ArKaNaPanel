<?php
	/*
		Автор панели: ArKaNeMaN
		Сайт: http://arkaneman.ru
	*/
	
	if(version_compare(phpversion(), '5.4', '<') === true) {
		exit('Для ArKaNaPanel необходимо PHP версии 5.4<br/> У Вас версия php '.phpversion());
	}
	
	mb_internal_encoding("utf-8");
	
	require_once('configs/main.php');
	
	if(!file_exists('configs/sql.php')) header('Location: /'.PANEL_DIR.'install');
	
	require_once('lib/engine_class.php');
	$engine = new engine();
	
	$engine->loadContent();