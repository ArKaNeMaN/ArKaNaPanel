<?
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	switch($_GET['action']){
		case 'add': {
			$eng->checkAccess(0);
			$adres = explode(':', $_POST['address']);
			$data = [
				'game' => (int) $_POST['game'],
				'ip' =>  $adres[0],
				'port' => (int) $adres[1],
				'name' => trim($_POST['name']),
				'active' => (int) $_POST['active']
			];
			$eng->ajaxReturn($eng->addServer($data, true));
			break;
		}
		
		case 'edit': {
			$eng->checkAccess(0);
			$adres = explode(':', $_POST['address']);
			$data = [
				'game' => (int) $_POST['game'],
				'ip' =>  $adres[0],
				'port' => (int) $adres[1],
				'name' => trim($_POST['name']),
				'active' => (int) $_POST['active']
			];
			$eng->ajaxReturn($eng->editServer((int) $_POST['id'], $data, true));
			break;
		}
		
		case 'del': {
			$eng->checkAccess(0);
			$eng->ajaxReturn($eng->delServer((int) $_POST['id'], true));
			break;
		}
		
		default: {$eng->ajaxReturn(['status' => false, 'Ошибка! Неизвестное действие']);}
	}