<?php
	require '../lib/engine_class.php';
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'add': {
			if($eng->addBlock($_POST['block'], $_POST['list'], 1)) $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Блок добавлен в указанный список']);
			else $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не удалось добавить блок. Возможно его не существует']);
		}
	}