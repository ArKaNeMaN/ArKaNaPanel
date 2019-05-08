<?php
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'save': {
			/* $data = [
				'siteName' => $_POST['siteName'],
				'homePage' => $_POST['homePage'],
				'panelTheme' => $_POST['panelTheme'],
				'yaMetrika' => (int) $_POST['yaMetrika'],
				'googleAnalytics' => $_POST['googleAnalytics'],
				'zipAvatars' => (int) $_POST['zipAvatars'],
				'captchaPubKey' => $_POST['captchaPubKey'],
				'captchaSecKey' => $_POST['captchaSecKey'],
			]; */
			
			$sets = $_POST['sets'];
			require '../lib/panelSettings.php';
			$data = [];
			for($i = 0; $i < count($tpl); $i++) 
				for($k = 0; $k < count($tpl[$i]['items']); $k++) 
					$data[$tpl[$i]['items'][$k]['id']] = $sets[$tpl[$i]['items'][$k]['id']];
			
			$eng->setModuleSettings('core', $data);
			return $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Настройки сохранены']);
		}
?>