<?
	require('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		/* case 'activate': {
			if(empty($_POST['module'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не указан модуль']);
			return $eng->ajaxReturn($eng->activateModule($_POST['module'], true));
			break;
		}
		case 'deactivate': {
			if(empty($_POST['module'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Не указан модуль']);
			return $eng->ajaxReturn($eng->deactivateModule($_POST['module'], true));
			break;
		} */
		
		case 'install': {
			if(!isset($_POST['module'])) $eng->ajaxReturnStatus(false, 'Не указан модуль');
			$status = $eng->installModule($_POST['module'], $err);
			$eng->ajaxReturnStatus($status, $status ? 'Модуль установлен' : $err);
			break;
		}
		
		case 'getFromFiles': {
			$eng->ajaxReturn($eng->getModulesFromFiles());
			break;
		}
		case 'getAll': {
			$eng->ajaxReturn($eng->getModules());
			break;
		}
		case 'delete': {
			if(!isset($_POST['module'])) $eng->ajaxReturnStatus(false, 'Не указан модуль');
			$status = $eng->delModule($_POST['module'], $err);
			$eng->ajaxReturnStatus($status, $status ? 'Модуль удалён' : $err);
			break;
		}
		case 'turn': {
			if(!isset($_POST['module'])) $eng->ajaxReturnStatus(false, 'Не указан модуль');
			if(!isset($_POST['turn'])) $eng->ajaxReturnStatus(false, 'Не указан статус переключателя');
			$status = $eng->turnModule($_POST['module'], $_POST['turn'], $err);
			$eng->ajaxReturnStatus($status, $status ? 'Модуль '.($_POST['turn'] ? 'включен' : 'выключен') : $err);
		}
		case 'register': {
			if(isset($_POST['data'])){
				if(!isset($_POST['data'])) $eng->ajaxReturnStatus(false, 'Не указана информация о модуле');
				$id = $eng->registerModule($_POST['data'], $err);
			}
			else{
				if(!isset($_POST['module'])) $eng->ajaxReturnStatus(false, 'Не указан индекс модуля');
				$id = $eng->registerModuleFromFile($_POST['module'], $err);
			}
			//if(!$id) $err = 'Не удалось добавить модуль. Возможно он уже добавлен';
			$eng->ajaxReturnStatus((bool) $id, $id ? 'Модуль добавлен' : $err, $id);
			break;
		}
		default: return $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Неизвестное действие']);
	}