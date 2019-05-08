<?php
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'save': {
			if(!isset($_POST['module'])) $eng->ajaxReturnStatus(false, 'Не указан модуль');
			if(!$eng->isRegisteredModule($_POST['module'], $err)) $eng->ajaxReturnStatus(false, $err);
			
			$tpl = $eng->getModule($_POST['module'])['settingsTpl'];
			$data = [];
			for($i = 0; $i < count($tpl); $i++)
				if(!isset($tpl[$i]['custom']) || !$tpl[$i]['custom'])
					for($k = 0; $k < count($tpl[$i]['items']); $k++)
						if(!isset($tpl[$i]['items'][$k]['custom']) || !$tpl[$i]['items'][$k]['custom'])
							if($tpl[$i]['items'][$k]['type'] == 'file'){
								if(!isset($_FILES[$tpl[$i]['items'][$k]['id']])) continue;
								$file = $_FILES[$tpl[$i]['items'][$k]['id']];
								
								$params = [];
								if(isset($tpl[$i]['items'][$k]['uploadFolder'])) $params['uploadFolder'] = $tpl[$i]['items'][$k]['uploadFolder'];
								else $params['uploadFolder'] = 'other';
								if(isset($tpl[$i]['items'][$k]['fileName'])) $params['fileName'] = $tpl[$i]['items'][$k]['fileName'];
								else $params['fileName'] = '';
								if(isset($tpl[$i]['items'][$k]['fileType'])) $params['fileType'] = $tpl[$i]['items'][$k]['fileType'];
								else $params['fileType'] = '';
								if(isset($tpl[$i]['items'][$k]['fileExp'])) $params['fileExp'] = $tpl[$i]['items'][$k]['fileExp'];
								else $params['fileExp'] = '';
								$res = $eng->uploadFile($file, $params['fileName'], $params['uploadFolder'], $params['fileType'], $params['fileExp'], true);
								if(!$res['status']) continue;
								$data[$tpl[$i]['items'][$k]['id']] = $res['data'];
							}
							else if(isset($_POST[$tpl[$i]['items'][$k]['id']])) $data[$tpl[$i]['items'][$k]['id']] = $_POST[$tpl[$i]['items'][$k]['id']];
			
			$eng->setModuleSettings($_POST['module'], $data);
			$eng->ajaxReturnStatus(true, 'Настройки сохранены');
		}
	}