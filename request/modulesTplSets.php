<?php
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'save': {
			if(!isset($_POST['module'])) $eng->ajaxReturnStatus(false, 'Не указан модуль');
			if(!$eng->isRegisteredModule($_POST['module'], $err)) $eng->ajaxReturnStatus(false, $err);
			
			$sets = $_POST['sets'];
			$tpl = $eng->getModule($_POST['module'])['settingsTpl'];
			$data = [];
			for($i = 0; $i < count($tpl); $i++)
				if(!isset($tpl[$i]['custom']) || !$tpl[$i]['custom'])
					for($k = 0; $k < count($tpl[$i]['items']); $k++)
						if($tpl[$i]['items'][$k]['type'] == 'file'){
							//$eng->uploadFile();
						}
						else $data[$tpl[$i]['items'][$k]['id']] = $sets[$tpl[$i]['items'][$k]['id']];
			
			$eng->setModuleSettings($_POST['module'], $data);
			$eng->ajaxReturnStatus(true, 'Настройки сохранены');
		}
		case 'sendFile': {
			
		}
	}