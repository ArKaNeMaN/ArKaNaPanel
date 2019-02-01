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
	}
	
	$eng->ajaxReturn($response);