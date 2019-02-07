<?php
	ini_set('display_errors', 0);
	error_reporting(E_ALL);
	
	function rJson($data){
		echo json_encode($data);
		die;
	}
	
	function getIp(){
		if($_SERVER['HTTP_X_FORWARDED_FOR'] != ''){
		  $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown" );
		  $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
		  reset($entries);
		  while (list(, $entry) = each($entries)){
			 $entry = trim($entry);
			 if(preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)){
				$private_ip = array('/^0\./',  '/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/', '/^10\..*/');
				$found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
				if($client_ip != $found_ip){
				   $client_ip = $found_ip;
				   break;
				}
			 }
		  }
	   }
	   else $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown");
	   return $client_ip;
	}
	
	switch($_GET['action']){
		
		case 'check': {
			$sql = new mysqli($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['name']);
			if($sql->connect_errno) rJson(['status' => false, 'msg' => 'Ошибка! Подключение не удалось. Код ошибки: '.$sql->connect_errno]);
			else rJson(['status' => true, 'msg' => 'Успех! Подключение удалось']);
			break;
		}
		
		case 'install': {
			if($_POST['adminLogin'] == '') rJson(['status' => false, 'msg' => 'Ошибка! Введите логин админа']);
			if(strlen($_POST['adminLogin']) < 4) rJson(['status' => false, 'msg' => 'Ошибка! Логин должен быть длиннее 3-х символов']);
			if(!preg_match('#^[aA-zZ0-9\-_]+$#', $_POST['adminLogin'])) rJson(['status' => false, 'msg' => 'Ошибка! Логин не должен содержать спецсимволы']);
			if($_POST['adminName'] == '') rJson(['status' => false, 'msg' => 'Ошибка! Введите ник админа']);
			if($_POST['adminPass'] != $_POST['adminPassa']) rJson(['status' => false, 'msg' => 'Ошибка! Пароли не совпадают']);
			if($_POST['adminPass'] == '') rJson(['status' => false, 'msg' => 'Ошибка! Введите пароль админа']);
			if(strlen($_POST['adminPass']) < 4) rJson(['status' => false, 'msg' => 'Ошибка! Пароль должен быть длиннее 3-х символов']);
			
			$sqlCfgFile = '../configs/sql.php';
			$cfgStr = "<?php
	define('SQL_HOST', '".$_POST['host']."');
	define('SQL_USER', '".$_POST['user']."');
	define('SQL_PASS', '".$_POST['pass']."');
	define('SQL_DB', '".$_POST['name']."');
	define('SQL_PREFIX', '".$_POST['prefix']."');
	define('SQL_ENCODE', '".$_POST['encode']."');"; // Пардон за кривость... Но если я тут сделаю норм, то в файле будет криво... Лучше уж так...
			
			if(!$fHandler = fopen($sqlCfgFile, 'w')) rJson(['status' => false, 'msg' => 'Ошибка! Файл настроек БД недоступен']);
			if(fwrite($fHandler, $cfgStr) === FALSE) rJson(['status' => false, 'msg' => 'Ошибка! Файл настроек БД недоступен для записи']);
			fclose($fHandler);
			
			$sql = new mysqli($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['name']);
			if($sql->connect_errno) rJson(['status' => false, 'msg' => 'Ошибка! Подключение к БД не удалось. Код ошибки: '.$sql->connect_errno]);
			
			$sql->query("SET NAMES '".$_POST['encode']."'");
			
			require('createTables.php');
			
			require '../lib/db_class.php';
			
			$sql_ = new DataBase($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['name'], $_POST['prefix'], $_POST['encode']);
			
			$sql_->insert('users', [
				'login' => $_POST['adminLogin'],
				'name' => $_POST['adminName'],
				'pass' => md5($_POST['adminPass']),
				'group' => 1,
				'regIp' => getIp(),
				'lastIp' => getIp(),
				'regTime' => time(),
			]);
			
			// Создание аккаунта админа
			/* $sql->query("INSERT INTO `".$_POST['prefix']."users` (`login`, `name`, `pass`, `group`, `regIp`, `lastIp`, `regTime`) 
			VALUES('".$_POST['adminLogin']."', '".$_POST['adminName']."', '".md5($_POST['adminPass'])."', 1, '".getIp()."', '".getIp()."', ".time().")"); */
			
			// Добавление дефолтных пунктов меню
			$sql->query("
				INSERT INTO `".$_POST['prefix']."menu` (`id`, `pos`, `name`, `link`, `active`, `submenu`, `type`, `group`, `parent`) VALUES
					(1, 1, 'Главная', '', 1, 0, 0, 0, 0),
					(2, 2, 'Админка', '', 1, 1, 1, 1, 0),
					(3, 1, 'Настройка панели', 'admin', 1, 0, 1, 1, 2),
					(4, 2, 'Менеджер модулей', 'admin/modules', 1, 0, 1, 1, 2),
					(5, 3, 'Менеджер блоков', 'admin/blocks', 1, 0, 1, 1, 2),
					(6, 4, 'Менеджер серверов', 'admin/servers', 1, 0, 1, 1, 2),
					(7, 5, 'Меню', 'admin/menu', 1, 0, 1, 1, 2),
					(8, 6, 'Логи', 'admin/logs', 1, 0, 1, 1, 2)
			");
			
			// Добавление дефолтных групп пользователей
			$sql->query("
				INSERT INTO `ap_groups` (`id`, `name`) VALUES
					(1, 'Гл.Админ'),
					(100, 'Пользователь')
			");
			
			$defSetts = [
				'siteName' => 'ArKaNaPanel',
				'homePage' => 'home',
				'yaMetrika' => 0,
				'googleAnalytics' => 0,
				'zipAvatars' => 0,
				'blocks' => [
					'homePage' => json_encode([
						[
							'block' => 'monitoring',
							'pos' => 1
						],
						[
							'block' => 'helloWorld',
							'pos' => 2
						],
					]),
					'rightCol' => json_encode([
						[
							'block' => 'helloWorld',
							'pos' => 1
						],
						[
							'block' => 'lastUser',
							'pos' => 2
						],
					]),
				],
			];
			
			$sql_->insert('settings', [
				'id' => 1,
				'module' => 'core',
				'data' => json_encode($defSetts),
			]);
			
			// Добавление дефолтных настроек панели
			//$sql->query('INSERT INTO `'.$_POST['prefix'].'settings` (`id`, `module`, `data`) VALUES (1, "core", "{"siteName":"ArKaNaPanel","homePage":"home","yaMetrika":0,"googleAnalytics":"","zipAvatars":1,"activeModules":{"banlist":true,"timeStats":true,"amxAdmins":true},"captchaPubKey":"","captchaSecKey":"","blocks":{"homePage":"[{\\"block\\":\\"helloWorld\\",\\"pos\\":2},{\\"block\\":\\"monitoring\\",\\"pos\\":1}]","rightCol":"[{\\"block\\":\\"helloWorld\\",\\"pos\\":1},{\\"block\\":\\"lastUser\\",\\"pos\\":2}]"}}");');
			
			
			rJson(['status' => true, 'msg' => 'Успех! Установка панели прошла успешно', 'data' => '<h2>Панель установлена</h2>Для безопасности, удалите папку install из корня панели']);
			
			break;
		}
		
		default: {rJson(['status' => false, 'msg' => 'Ошибка! Неизвестное действие']);}
	}
	
	rJson(['status' => false, 'msg' => 'Ошибка! Что-то пошло не так :(']);