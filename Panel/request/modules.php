<?
	require('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(0);
	if(empty($_POST['module'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не указан модуль']);
	
	switch($_GET['action']){
		case 'activate': {
			return $eng->ajaxReturn($eng->activateModule($_POST['module'], true));
			break;
		}
		case 'deactivate': {
			return $eng->ajaxReturn($eng->deactivateModule($_POST['module'], true));
			break;
		}
		case 'install': {
			return $eng->ajaxReturn($eng->installModule($_POST['module'], true));
			break;
		}
		default: return $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Неизвестное действие']);
	}