<?php
	require '../lib/engine_class.php';
	$eng = new engine(true);
	
	$eng->checkAccess(0);
	
	if(!isset($_GET['action'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Действие не указано']);
	
	switch($_GET['action']){
		case 'sendAvatarForm': {
			if(!isset($_FILES['file'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Файл не выбран']);
			$size = $_FILES['file']['size']/1024/1024;
			if($size <= 3){
				$res = $eng->uploadFile($_FILES['file'], $eng->userid.'-'.time().'.jpg', 'avatars', 'image', 'jpg', true);
				if($res['status']){
					unlink($eng->homePath.$eng->getUserInfo($eng->userid)['avatar']);
					$ress = $eng->editUserInfo($eng->userid, ['avatar' => $res['data']], true);
					if($ress['status']) $eng->ajaxReturn(['status' => true, 'msg' => 'Успех! Аватар загружен']);
					else return $eng->ajaxReturn($ress);
				}
				else return $eng->ajaxReturn($res);
			}
			else $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Размер файла превышает 3Мб']);
		}
		
		case 'saveMain': {
			$data = [
				'name' => $_POST['name']
			];
			
			if(empty($data['name'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Введите ник']);
			
			$eng->ajaxReturn($eng->editUserInfo($eng->userid, $data, true));
		}
		
		case 'changePass': {
			if(empty($_POST['oldPass'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Введите старый пароль']);
			if(empty($_POST['newPass'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Введите новый пароль']);
			if($_POST['newPass'] != $_POST['newPassa']) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Пароли не совпадают']);
			if(!$eng->sql->select('users', ['COUNT(*)'], ['id' => $eng->userid, 'pass' => md5($_POST['oldPass'])])[0]['COUNT(*)']) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Старый пароль указан неверно']);
			
			$eng->ajaxReturn($eng->editUserInfo($eng->userid, ['pass' => $_POST['newPass']], true));
		}
		
		default: {$eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Undefined action']);}
	}