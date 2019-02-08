<?php
	/*
		Автор панели: ArKaNeMaN
		Сайт: http://arkaneman.ru
	*/
	
	if(version_compare(phpversion(), '5.4', '<') === true) {
		exit('Для ArKaNaPanel необходимо PHP версии 5.4<br/> У Вас версия php '.phpversion());
	}
	
	mb_internal_encoding("utf-8");
	
	if(!file_exists('configs/sql.php')) header('Location: install');
	
	require_once('configs/main.php');
	
	require_once('lib/engine_class.php');
	$engine = new engine();
	
	$engine->loadContent();