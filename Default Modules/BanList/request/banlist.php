<?
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	switch($_GET['action']){
		case 'getMoreInfo': {
			$res = $eng->modules['banlist']->getBanInfo((int) $_POST['bid']);
			//if(!$res) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не удалось получить информацию о бане']);
			$eng->modules['banlist']->removePersonalData($res);
			$eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Информация о бане успешно получена', 'data' => $res]);
		}
	}