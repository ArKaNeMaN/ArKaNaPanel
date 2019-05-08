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
					
					$status = $eng->editUserInfo($eng->userid, ['avatar' => $res['data']], $err);
					$eng->ajaxReturnStatus($status, $status ? 'Аватар сохранён' : $err, $status ? $res['data'] : null);
				}
				else return $eng->ajaxReturn($res);
			}
			else $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Размер файла превышает 3Мб']);
		}
		
		case 'saveMain': {
			$data = ['name' => $_POST['name']];
			
			if(empty($data['name'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Введите ник']);
			
			$status = $eng->editUserInfo($eng->userid, $data, $err);
			$eng->ajaxReturnStatus($status, $status ? 'Настройки сохранены' : $err);
		}
		
		case 'changePass': {
			if(empty($_POST['oldPass'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Введите старый пароль']);
			if(empty($_POST['newPass'])) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Введите новый пароль']);
			if($_POST['newPass'] != $_POST['newPassa']) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Пароли не совпадают']);
			if(!$eng->sql->select('users', ['COUNT(*)'], ['id' => $eng->userid, 'pass' => md5($_POST['oldPass'])])[0]['COUNT(*)']) $eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Старый пароль указан неверно']);
			
			$status = $eng->editUserInfo($eng->userid, ['pass' => $_POST['newPass']], $err);
			$eng->ajaxReturnStatus($status, $status ? 'Пароль изменён' : $err);
		}
		
		default: {$eng->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Undefined action']);}
	}