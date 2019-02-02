<?
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'save': {
			$data = [
				'siteName' => $_POST['siteName'],
				'homePage' => $_POST['homePage'],
				'yaMetrika' => (int) $_POST['yaMetrika'],
				'googleAnalytics' => $_POST['googleAnalytics'],
				'zipAvatars' => (int) $_POST['zipAvatars'],
				'captchaPubKey' => $_POST['captchaPubKey'],
				'captchaSecKey' => $_POST['captchaSecKey'],
			];
			$eng->setModuleSettings('core', $data);
			return $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Настройки сохранены']);
		}
	}