<?php
	require '../lib/engine_class.php';
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'add': {
			if($eng->addBlock($_POST['block'], $_POST['list'], 1)) $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Блок добавлен в указанный список']);
			else $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не удалось добавить блок. Возможно его не существует']);
		}
		case 'getBlocksList': {
			$eng->ajaxReturn($eng->getBlocksList($_POST['list']));
		}
		case 'remove': {
			if($res = $eng->removeBlockByNum((int) $_POST['block'], $_POST['list'])) $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Блок удалён из списка']);
			else $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не удалось удалить блок. Возможно его не существует']);
		}
		case 'changePos': {
			$pos = $_POST['pos'];
			$id = $_POST['id'];
			$list = $_POST['list'];
			$blocks = json_decode($eng->settings['core']['blocks'][$list], true);
			if(!isset($blocks[$id])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Такого блока не существует']);
			$blocks[$id]['pos'] += $pos;
			$eng->setModuleSettings('core', ['blocks' => [$list => json_encode($blocks)]]);
			$eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Позиция блока изменена на '.$pos]);
		}
	}