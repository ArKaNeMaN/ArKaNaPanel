<?php
	require '../lib/engine_class.php';
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		case 'search': {
			$eng->ajaxReturn($eng->sql->search('users', $_POST['str'], ['id', 'login', 'name', 'email', 'group']));
		}
		case 'getUsers': {
			$eng->ajaxReturn($eng->getUsers(isset($_POST['page']) ? $_POST['page'] : 1));
		}
		case 'getUser': {
			if(!isset($_POST['userid'])) $eng->ajaxReturn(false);
			$eng->ajaxReturn($eng->getUserInfo((int) $_POST['userid']));
		}
		case 'getCount': {
			$eng->ajaxReturn($eng->sql->select('users', ['COUNT(*)'])[0]['COUNT(*)']);
		}
		case 'editUser': {
			if(!isset($_POST['userid'])) $eng->ajaxReturnStatus(false, 'Не указан userid');
			$status = $eng->editUserInfo($_POST['userid'], $_POST['data'], $err);
			$eng->ajaxReturnStatus($status, $status ? 'Информация о пользователе обновлена' : $err);
		}
	}
?>