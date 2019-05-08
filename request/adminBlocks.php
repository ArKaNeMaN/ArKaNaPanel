<?php
	require '../lib/engine_class.php';
	$eng = new engine(true);
	
	$eng->checkAccess(1);
	
	switch($_GET['action']){
		
		case 'getBlocksList': {
			$eng->ajaxReturn($eng->getBlocksList($_POST['list']));
		}
		case 'add': {
			if(!isset($_POST['block'])) $eng->ajaxReturnStatus(false, 'Блок не указан');
			if(!isset($_POST['list'])) $eng->ajaxReturnStatus(false, 'Место не указано');
			if($id = $eng->addBlock((int) $_POST['block'], $_POST['list'], $err)) $eng->ajaxReturnStatus(true, 'Блок добавлен в указанный список', $id);
			else $eng->ajaxReturnStatus(false, $err);
		}
		case 'remove': {
			if($res = $eng->rmBlockFromList((int) $_POST['block'])) $eng->ajaxReturnStatus(true, 'Блок удалён из списка');
			else $eng->ajaxReturnStatus(false, 'Не удалось удалить блок. Возможно его не существует');
		}
		case 'changePos': {
			if(!isset($_POST['id'])) $eng->ajaxReturnStatus(false, 'Блок не указан');
			if(!isset($_POST['pos'])) $eng->ajaxReturnStatus(false, 'Разница позиции не указана');
			$oldPos = $eng->sql->select('blocksShow', ['pos'], ['id' => $_POST['id']])[0]['pos'];
			if(!$oldPos) $eng->ajaxReturnStatus(false, 'Указанного блока не существует');
			$eng->sql->update('blocksShow', ['pos' => $oldPos+$_POST['pos']], ['id' => $_POST['id']]);
			$eng->ajaxReturnStatus(true, 'Позиция блока изменена', $oldPos+$_POST['pos']);
		}
		case 'getPlaces': {
			$eng->ajaxReturn($eng->getBlocksPlaces());
		}
		case 'editData': {
			if(!isset($_POST['index'])) $eng->ajaxReturn(false);
			$eng->ajaxReturn($eng->updateBlockData($_POST['index'], $data));
		}
		
		
		case 'importBlocks': {
			$eng->importAllBlocks();
			$eng->ajaxReturn(true);
		}
		case 'getFromFiles': {
			$eng->ajaxReturn($eng->getBlocksFromFiles());
		}
		case 'installFromFile': {
			if(!isset($_POST['index'])) $eng->ajaxReturnStatus(false, 'Блок не указан');
			$eng->ajaxReturn($eng->installBlockFromFile('../temps/default/blocks/'.$_POST['index'].'/info.json', []));
		}
		
		case 'getBlocks': {
			$eng->ajaxReturn($eng->getBlocks());
		}
		case 'del': {
			if(!isset($_POST['id'])) $eng->ajaxReturn(false);
			$eng->ajaxReturn($eng->deleteBlock($_POST['id']));
		}
	}