<?
	require_once('../configs/main.php');
	require_once('../configs/sql.php');
	require_once('../lib/engine_class.php');
	$eng = new engine(true);
	
	if(empty($_POST['login']) || empty($_POST['name']) || empty($_POST['pass']) || empty($_POST['passa'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Получены не все данные']);
	
	$regData = [
		'login' => $_POST['login'],
		'name' => $_POST['name'],
		'pass' => $_POST['pass'],
		'passa' => $_POST['passa'],
		'email' => $_POST['email']
	];
	
	switch($_GET['action']){
		case 'check':{
			$responce = $eng->regDataCheck($regData, true);
			break;
		}
		case 'reg':{
			$responce = $eng->userReg($regData, true);
			break;
		}
	}
	
	$eng->ajaxReturn($responce);