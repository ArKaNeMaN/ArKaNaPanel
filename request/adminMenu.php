<?php
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'getMenuItem': {
			if($item = $eng->getMenuItem((int) $_POST['id'])) $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Пункт меню загружен', 'data' => $item]);
			else $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Что-то пошло не так. Возможно такого пункта меню не существует']);
		}
		case 'getMenuItems': {
			$eng->ajaxReturn($eng->getMenuItems(false));
		}
		case 'editMenuItem': {
			$data = [
				'id' => (int) $_POST['id'],
				'pos' => (int) $_POST['pos'],
				'access' => (int) $_POST['access'],
				'name' => $_POST['name'],
				'link' => $_POST['link'],
				'submenu' => (bool) $_POST['submenu'],
				'parent' => (int) $_POST['parent'],
				'active' => (bool) $_POST['active'],
			];
			$eng->ajaxReturn($eng->editMenuItem((int) $_POST['id'], $data, true));
		}
		case 'addMenuItem': {
			$eng->ajaxReturn($eng->addMenuItem(null, true));
		}
		case 'deleteMenuItem': {
			$eng->ajaxReturn($eng->deleteMenuItem((int) $_POST['id'], true));
		}
		
		default: {$eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Неизвестное действие']);}
	}