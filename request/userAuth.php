<?
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	switch($_GET['action']){
		case 'auth': {
			$data = [
				'login' => $_POST['login'],
				'pass' => $_POST['pass']
			];
			$response = $eng->userAuth($data, true);
			break;
		}
		case 'logout': {
			$response = $eng->userLogout();
			break;
		}
		case 'getHash': {
			$hash = $eng->getUserHash($_POST['login'], $_POST['pass'], $err);
			if($hash) $eng->ajaxReturnStatus(true, 'Авторизация прошла успешно', $hash);
			else $eng->ajaxReturnStatus(false, $err);
		}
	}
	
	$eng->ajaxReturn($response);