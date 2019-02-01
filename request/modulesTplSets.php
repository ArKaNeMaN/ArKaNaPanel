<?php
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'save': {
			if(!isset($_POST['module'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не указан модуль']);
			if(!$eng->isModuleInstalled($_POST['module'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не установлен']);
			
			$sets = $_POST['sets'];
			$tpl = $eng->getModuleSettTpl($_POST['module']);
			$data = [];
			for($i = 0; $i < count($tpl); $i++) 
				for($k = 0; $k < count($tpl[$i]['items']); $k++) 
					$data[$tpl[$i]['items'][$k]['id']] = $sets[$tpl[$i]['items'][$k]['id']];
			
			$eng->setModuleSettings($_POST['module'], $data);
			return $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Настройки сохранены']);
		}
	}