<?php
	class engine{
		
		private $isRequest = false;
		
		protected $pageContent;
		protected $error = '';
		
		public $homePath = '';
		public $userid;
		public $sql;
		public $userInfo;
		public $modules;
		public $settings;
		public $title = '';
		
		//--------------------| Конструктор |--------------------//
		
		public function __construct($req = false){
			session_start();
			ini_set('display_errors', 0);
			error_reporting(E_ALL);
			
			$this->isRequest = $req;
			if($this->isRequest) $this->homePath = '../';
			
			require_once($this->homePath.'configs/main.php');
			require($this->homePath.'configs/sql.php');
			require($this->homePath.'lib/db_class.php');
			
			$this->sql = new DataBase();
			
			/* $this->mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);
			$this->mysqli->query('SET NAMES UTF8'); */
			
			$this->settings = $this->getSettings();
			$this->checkAuth();
			$this->loadModules();
		}
		
		//--------------------| Получение контента |--------------------//
		
		public function loadContent(){
			$link = parse_url($_SERVER['REQUEST_URI']);
			$path = explode('/', str_replace(PANEL_DIR, '', $link['path']));
			$defCont = isset($this->settings['core']['homePage']) ? $this->settings['core']['homePage'] : 'home';
			$content = '';
			if(is_array($path)){
				if(!empty($path[1]) && !empty($path[2])){
					for($i = 1; $i < count($path); $i++){
						if($i != count($path) - 1) $content .= $path[$i].'/';
						else $content .= $path[$i];
					}
				}
				elseif(!empty($path[1])) $content = $path[1];
			}
			if($content == $defCont) header('Location: /'.PANEL_DIR);
			if(!$content) $content = $defCont;
			if(!file_exists('temps/'.PANEL_THEME.'/'.$content.'/index.aptpl')) $content = 'error/404';
			$this->pageContent = $this->getTpl($content);
			if($this->error != '') $this->pageContent = $this->getTpl('error/'.$this->error);
			echo $this->getTpl('main');
		}
		
		protected function getTpl($content){
			$content = file_exists('temps/'.PANEL_THEME.'/'.$content.'.aptpl') ? 'temps/'.PANEL_THEME.'/'.$content.'.aptpl' : 'temps/'.PANEL_THEME.'/'.$content.'/index.aptpl';
			ob_start(); 				 
			include($content);
			return ob_get_clean();
		}
		
		public function incBlock($name){
			$fullName = 'temps/'.PANEL_THEME.'/blocks/'.$name.'.aptpl';
			if(file_exists($fullName)) include($fullName);
		}
		
		public function loadJsPlugin($name, $inputDir = false){
			$dir = $inputDir ? 'public/plugins/'.$name.'/' : $name;
			if(is_dir($dir)){
				if($dh = opendir($dir)){
					while(($file = readdir($dh)) !== false){
						if(preg_match("/\.css$/i", $file)) echo '<script>loadCSS("'.PANEL_HOME.$dir.$file.'");</script>';
						elseif(preg_match("/\.js$/i", $file)) echo '<script src="'.PANEL_HOME.$dir.$file.'" defer></script>';
					}
					closedir($dh);
				}
			}
		}
		
		public function loadAllJsPlugins(){
			$dir = 'public/plugins/';
			if(is_dir($dir)){
				if($dh = opendir($dir)){
					while(($plugDir = readdir($dh)) !== false){
						$this->loadJsPlugin($plugDir, true);
					}
					closedir($dh);
				}
			}
			
		}
		
		public function checkAccess($group, $return = false){
			$r = false;
			if($this->userid){
				if(is_array($group)) $r = (bool) (in_array($this->userInfo['group'], $group));
				else $r = (bool) ($this->userInfo['group'] <= $group || $group == 0);
			}
			if(!$r && !$return) $this->isRequest ? $this->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Нет доступа']) : $this->error = '403';
			else return $r;
		}
		
		//--------------------| Аккаунт |--------------------//
		
		public function userReg($data, $more = false){
			
			if($this->settings['core']['captchaPubKey'] && $this->settings['core']['captchaSecKey']){
				$reCap = $this->post('https://www.google.com/recaptcha/api/siteverify', [
					'secret' => $this->settings['core']['captchaSecKey'],
					'response' => $data['cToken'],
					'remoteip' => $this->getIp(),
				]);
				if(!$reCap['success']) return $more ? ['status' => false, 'msg' => 'Ошибка! Не введена капча'] : false;
			}
			
			$data['login'] = trim($data['login']);
			$data['name'] = trim($data['name']);
			$data['pass'] = trim($data['pass']);
			$data['passa'] = trim($data['passa']);
			
			$check = $this->regDataCheck($data, true);
			if(!$check['status']) return $more ? $check : false;
			
			$sendData = [
				'login' => $data['login'],
				'name' => $data['name'],
				'pass' => md5($data['pass']),
				'regIp' => $this->getIp(),
				'lastIp' => $this->getIp(),
				'regTime' => time()
			];
			
			if(isset($custom)) $sendData['custom'] = json_encode($custom);
			
			if($data['email'] != '') $sendData['email'] = $data['email'];
			
			$data['id'] = $this->sql->insert('users', $sendData);
			
			$this->userAuth($data);
			
			if(is_numeric($data['id']) && $data['id']) return $more ? ['status' => true, 'msg' => 'Успех! Регистрация прошла успешно', 'data' => ['userid' => $data['id']]] : true;
			else return $more ? ['status' => false, 'msg' => 'Ошибка! Возникла непредвиденная ошибка'] : false;
		}
		
		public function regDataCheck($data, $more = false){
			if(!empty($this->userid)) return $more ? ['status' => false, 'msg' => 'Ошибка! Вы уже авторизованы'] : false;
			
			if(!is_array($data)) return $more ? ['status' => false, 'msg' => 'Ошибка! При отправке данных возникла ошибка :('] : false;
			if($data['login'] == '') return $more ? ['status' => false, 'msg' => 'Ошибка! Введите логин'] : false;
			if(strlen($data['login']) < 4) return $more ? ['status' => false, 'msg' => 'Ошибка! Логин должен быть длиннее 3-х символов'] : false;
			if(!preg_match('#^[aA-zZ0-9\-_]+$#', $data['login'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Логин не должен содержать спецсимволы'] : false;
			if($data['name'] == '') return $more ? ['status' => false, 'msg' => 'Ошибка! Введите ник'] : false;
			if($data['pass'] != $data['passa']) return $more ? ['status' => false, 'msg' => 'Ошибка! Пароли не совпадают'] : false;
			if($data['pass'] == '') return $more ? ['status' => false, 'msg' => 'Ошибка! Введите пароль'] : false;
			if(strlen($data['pass']) < 4) return $more ? ['status' => false, 'msg' => 'Ошибка! Пароль должен быть длиннее 3-х символов'] : false;
			
			if($data['email'] != ''){
				if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный формат почты'] : false;
				if($this->sql->select('users', ['COUNT(*)'], ['email' => $data['email']])[0]['COUNT(*)']) return $more ? ['status' => false, 'msg' => 'Ошибка! Игрок с такой почтой уже существует'] : false;
			}
			
			if($this->sql->select('users', ['COUNT(*)'], ['login' => $data['login']])[0]['COUNT(*)']) return $more ? ['status' => false, 'msg' => 'Ошибка! Игрок с таким логинои уже существует'] : false;
			if($this->sql->select('users', ['COUNT(*)'], ['name' => $data['name']])[0]['COUNT(*)']) return $more ? ['status' => false, 'msg' => 'Ошибка! Игрок с таким ником уже существует'] : false;
			
			return $more ? ['status' => true, 'msg' => 'Успех! Все данные введены верно'] : true;
		}
		
		public function userAuth($data, $more = false){
			if($this->userid) return $more ? ['status' => false, 'msg' => 'Ошибка! Вы уже авторизованы'] : false;
			
			if($data['login'] == '') return $more ? ['status' => false, 'msg' => 'Ошибка! Введите логин'] : false;
			if($data['pass'] == '') return $more ? ['status' => false, 'msg' => 'Ошибка! Введите пароль'] : false;
			
			$res = $this->sql->select('users', ['id'], '`login`="'.$data['login'].'" AND `pass`="'.md5($data['pass']).'"');
			
			if(!is_array($res)) return $more ? ['status' => false, 'msg' => 'Ошибка! Введён неверный логин или пароль'] : false;
			
			$userHash = md5($data['login'].'saltysalt'.$data['pass'].time());
			
			$this->sql->update('users', ['userHash' => $userHash], ['id' => $res[0]['id']]);
			
			$_SESSION['userid'] = $res[0]['id'];
			SetCookie('userHash', $userHash, time()+2592000, '/', null, null, true);
			
			return $more ? ['status' => true, 'msg' => 'Успех! Авторизация прошла успешно'] : true;
		}
		
		private function updateUserHash($id){
			$data = $this->getUserInfo($id);
			$userHash = md5($data['login'].'saltysalt'.$data['pass'].time());
			$this->sql->update('users', ['userHash' => $userHash], ['id' => $id]);
			return $userHash;
		}
		
		public function userLogout($path){
			if(is_array($_SESSION)) foreach($_SESSION as $key => $value) unset($_SESSION[$key]);
			SetCookie('userHash', '', 32600, '/', null, null, true);
			unset($this->userid);
			session_destroy();
			$this->redirect($path);
			die;
		}
		
		public function checkAuth(){
			if(isset($_COOKIE['userHash']) && !empty($_COOKIE['userHash'])){
				if(is_array($res = $this->sql->select('users', ['id'], ['userHash' => $_COOKIE['userHash']]))){
					$_SESSION['userid'] = $res[0]['id'];
				}
			}
			if(isset($_SESSION['userid'])){
				$this->userid = $_SESSION['userid'];
				$this->userInfo = $this->getUserInfo($this->userid);
				$this->sql->update('users', ['lastIp' => $this->getIp()], ['id' => $this->userid]);
			}
		}
		
		public function getUserInfo($userid){
			$res = $this->sql->select('users', '*', ['id' => $userid], 'id', true, 1)[0];
			if($res != '') $res['custom'] = json_decode($res['custom'], true);
			$res['pass'] = null;
			return $res;
		}
		
		public function getLastUser(){
			return $this->getUserInfo($this->sql->select('users', ['id'], '', 'id', false, 1)[0]['id']);
		}
		
		public function editUserInfo($id, $data, $more = false){
			
			$userInfo = $this->getUserInfo($id);
			
			if(isset($data['name']) && $userInfo['name'] != $data['name']){
				$data['name'] = htmlentities($data['name']);
				$data['name'] = str_replace("&nbsp;", '', $data['name']);
				$data['name'] = htmlspecialchars($data['name']);
				if($this->sql->select('users', ['COUNT(*)'], ['name' => $data['name']])[0]['COUNT(*)']) return $more ? ['status' => false, 'msg' => 'Ошибка! Игрок с таким ником уже существует'] : false;
				$this->sql->update('users', ['name' => $data['name']], ['id' => $id]);
			}
			
			if(isset($data['avatar'])){
				if(!file_exists($this->homePath.$data['avatar'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Файл аватара не найден'] : false;
				$this->sql->update('users', ['avatar' => $data['avatar']], ['id' => $id]);
			}
			
			if(isset($data['pass'])){
				$this->sql->update('users', ['pass' => md5($data['pass'])], ['id' => $id]);
			}
			
			$this->updateUserHash($id);
			return $more ? ['status' => true, 'msg' => 'Успех! Данные о пользователе изменены'] : true;
		}
		
		public function getUserGroups($more = false){
			$ret[0] = ['name' => 'Все', 'id' => 0];
			$res = $this->sql->select('groups', '*');
			for($i = 0; $i < count($res); $i++) $ret[$i+1] = $res[$i];
			return $more ? ['status' => true, 'msg' => 'Успех! группы пользователей получены', 'data' => $ret] : $ret;
		}
		
		//--------------------| Сервера |--------------------//
		
		public $gamesNames = [
			1 => 'Counter Strike 1.6',
			2 => 'Counter Strike: Source',
			3 => 'Counter Strike: Global Offensive',
			4 => 'Rust',
			5 => 'SA:MP',
		];
		
		public function getServers($onlyActive = true, $game = '', $limit = ''){
			$where = '';
			if($onlyActive) $where['active'] = 1;
			if($game != '') $where['game'] = $game;
			if(!($res = $this->sql->select('servers', '*', $where, 'id', true, $limit))) return false;
			
			for($i = 0; $i < count($res); $i++){
				$data[] = [
					'id' => (int) $res[$i]['id'],
					'name' => $res[$i]['name'],
					'game' => (int) $res[$i]['game'],
					'gameName' => $this->gamesNames[$res[$i]['game']],
					'ip' => $res[$i]['ip'],
					'port' => (int) $res[$i]['port'],
					'fullAddress' => $res[$i]['ip'].':'.$res[$i]['port'],
					'data' => json_decode($res[$i]['data'], true),
					'active' => (bool) $res[$i]['active']
				];
			}
			return $data;
		}
		
		public function getServer($id){
			if(!($res = $this->sql->select('servers', '*', ['id' => $id], '`id`', true, 1)[0])) return false;
			$data = [
				'name' => $res['name'],
				'game' => $res['game'],
				'gameName' => $this->gamesNames[$res['game']],
				'ip' => $res['ip'],
				'port' => $res['port'],
				'fullAddress' => $res['ip'].':'.$res['port'],
				'data' => json_decode($res['data'], true),
				'active' => $res['active']
			];
		}
		
		public function addServer($data, $more = false){
			if(!isset($this->gamesNames[$data['game']])) return $more ? ['status' => false, 'msg' => 'Ошибка! Неизвестная игра'] : false;
			if(!filter_var($data['ip'], FILTER_VALIDATE_IP)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный формат IP адреса'] : false;
			if($data['port'] > 49151) return $more ? ['status' => false, 'msg' => 'Ошибка! Порт указан неверно'] : false;
			
			$sendData = [
				'port' => (int) $data['port'],
				'ip' => $data['ip'],
				'game' => (int) $data['game'],
				'name' => htmlspecialchars($data['name']),
				'active' => (int) $data['active']
			];
			$id = $this->sql->insert('servers', $sendData);
			return $more ? ['status' => true, 'msg' => 'Успех! Сервер добавлен', 'data' => array_merge($sendData, ['id' => $id, 'gameName' => $this->gamesNames[$sendData['game']], 'fullAddress' => $sendData['ip'].':'.$sendData['port']])] : false;
		}
		
		public function delServer($id, $more = false){
			if($id < 1) return $more ? ['status' => false, 'msg' => 'Ошибка! Такого сервера не существует'] : false;
			if($this->sql->delete('servers', ['id' => $id])) return $more ? ['status' => true, 'msg' => 'Успех! Сервер удалён', 'data' => ['id' => $id]] : true;
			return $more ? ['status' => false, 'msg' => 'Ошибка! Что-то пошло не так :('] : false;
		}
		
		public function editServer($id, $data, $more = false){
			if(!isset($this->gamesNames[$data['game']])) return $more ? ['status' => false, 'msg' => 'Ошибка! Неизвестная игра'] : false;
			if(!filter_var($data['ip'], FILTER_VALIDATE_IP)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный формат IP адреса'] : false;
			if($data['port'] > 49151) return $more ? ['status' => false, 'msg' => 'Ошибка! Порт указан неверно'] : false;
			
			$sendData = [
				'port' => (int) $data['port'],
				'ip' => $data['ip'],
				'game' => (int) $data['game'],
				'name' => htmlspecialchars($data['name']),
				'active' => (int) $data['active']
			];
			$this->sql->update('servers', $sendData, ['id' => $id]);
			return $more ? ['status' => true, 'msg' => 'Успех! Сервер изменён', 'data' => array_merge($sendData, ['id' => $id, 'gameName' => $this->gamesNames[$sendData['game']], 'fullAddress' => $sendData['ip'].':'.$sendData['port']])] : false;
		}
		
		public function setServerData($id, $data){
			if($old = json_decode($this->sql->select('servers', ['data'], ['id' => $id], '`id`', true, 1)[0]['data'], true)){
				$data = array_replace_recursive($old, $data);
			}
			$this->sql->update('servers', ['data' => json_encode($data)], ['id' => $id]);
			return true;
		}
		
		//--------------------| Меню |--------------------//
		
		public function getMenuItems($onlyActive = true){
			$where = ['parent' => 0];
			if($onlyActive) $where = array_merge($where, ['active' => 1]);
			$res = $this->sql->select('menu', '*', $where, 'pos', true);
			for($i = 0; $i < count($res); $i++){
				if($res[$i]['submenu'] && !$res[$i]['parent']){
					$where = [];
					if($onlyActive) $where = ['active' => 1];
					$res[$i]['submenu'] = $this->sql->select('menu', '*', array_merge($where, ['parent' => $res[$i]['id']]), 'pos', true);
				}
			}
			return $res;
		}
		
		public function addMenuItem($data = null, $more = false){
			if($data == null){
				$sendData = [
					'name' => 'Новый пункт',
					'active' => false,
				];
			}
			else $sendData = $data;
			
			$sendData['id'] = $this->sql->insert('menu', $sendData);
			
			return $more ? ['status' => true, 'msg' => 'Успех! Пункт меню добавлен', 'data' => $sendData] : true;
		}
		
		public function editMenuItem($id, $data, $more = false){
			
			if($data['parent'] > 0) if($this->getMenuItem($data['parent'])['submenu'] == false) return $more ? ['status' => false, 'msg' => 'Ошибка! ID родителя указан неверно'] : false;
			
			$sendData = [
				'id' => (int) $data['id'],
				'pos' => (int) $data['pos'],
				'name' => $data['name'],
				'link' => $data['link'],
				'parent' => (int) $data['parent'],
				'submenu' => (bool) $data['submenu'],
				'group' => (int) $data['access'],
				'active' => (bool) $data['active'],
			];
			
			$this->sql->update('menu', $sendData, ['id' => $id]);
			return $more ? ['status' => true, 'msg' => 'Успех! Пункт меню изменён', 'data' => $sendData] : true;
		}
		
		public function getMenuItem($id){
			$res = $this->sql->select('menu', '*', ['id' => $id])[0];
			if($res['submenu'] && !$res['parent']) $res['submenu'] = $this->sql->select('menu', '*', ['parent' => $res['parent']], 'pos', true);
			return $res;
		}
		
		public function checkTypeAccess(){
			
		}
		
		public function deleteMenuItem($id, $more = false){
			if(!$this->isMenuItemExists($id)) return $more ? ['status' => false, 'msg' => 'Ошибка! Такого пункта не существует'] : false;
			$this->sql->delete('menu', '`id`='.$id.' OR `parent`='.$id);
			if(!$this->isMenuItemExists($id)) return $more ? ['status' => true, 'msg' => 'Успех! Пункт меню удалён', 'data' => $id] : true;
			else return $more ? ['status' => false, 'msg' => 'Ошибка! Что-то пошло не так :('] : false;
		}
		
		public function isMenuItemExists($id){
			return (bool) $this->sql->select('menu', ['COUNT(*)'], ['id' => $id])[0]['COUNT(*)'];
		}
		
		//--------------------| Настройки |--------------------//
		
		protected function getSettings(){
			if(!($res = $this->sql->select('settings', '*'))) return false;
			for($i = 0; $i < count($res); $i++){
				$setts = json_decode($res[$i]['data'], true);
				$data[$res[$i]['module']] = $setts;
			}
			return $data;
		}
		
		public function getSettingsByModule($module){
			$res = json_decode($this->sql->select('settings', '*', ['module' => $module])[0]['data'], true);
			return $res != null ? $res : false;
		}
		
		public function setModuleSettings($module, $settings){
			if($res = $this->sql->select('settings', ['data'], ['module' => $module])){
				$old = json_decode($res[0]['data'], true);
				$new = array_replace_recursive($old, $settings);
				$this->sql->update('settings', ['data' => json_encode($new)], ['module' => $module]);
				$this->settings[$module] = $new;
			}
			else{
				$this->sql->insert('settings', ['module' => $module, 'data' => json_encode($settings)]);
				$this->settings[$module] = $settings;
			}
			
			return true;
		}
		
		//--------------------| Система модулей |--------------------//
		
		public function getModulesAdmins(){
			$path = $this->homePath.'lib/modules';
			if(file_exists($path) && is_dir($path)){
				$result = scandir($path);
				$files = array_diff($result, array('.', '..'));
				if(count($files) > 0){
					foreach($files as $file){
						if(is_dir("$path/$file")){
							$modules[] = $this->getModuleInfo($file);
						}
					}
					return $modules;
				}
			}
			return false;
		}
		
		private function loadModules(){
			$modules = $this->settings['core']['activeModules'];
			foreach($modules as $k => $v){
				if($v == true){
					if(file_exists($this->homePath.'lib/modules/'.$k.'/class.php') && is_file($this->homePath.'lib/modules/'.$k.'/class.php')){
						require($this->homePath.'lib/modules/'.$k.'/class.php');
						$this->modules[$k] = new $k($this);
					}
					else $this->deactivateModule($k);
				}
			}
		}
		
		public function getModuleInfo($module){
			$file = $this->homePath.'lib/modules/'.$module.'/info.json';
			if(file_exists($file) && is_file($file)){
				$info = json_decode(file_get_contents($file), true);
				
				if($info['settPage']) $info['settLink'] = $info['settTpl'] ? PANEL_HOME.'admin/modules/auto?module='.$module : PANEL_HOME.'admin/modules/'.$module;
				else $info['settLink'] = false;
				$info['installed'] = $this->isModuleInstalled($module);
				$info['active'] = $this->isModuleActive($module);
				$info['id'] = $module;
				
				return $info;
			}
			else return false;
		}
		
		public function getModuleSettTpl($module){
			$tplFile = $this->homePath.'lib/modules/'.$module.'/settings.json';
			if(!file_exists($tplFile)) return false;
			$info = json_decode(file_get_contents($tplFile), true);
			return $info;
		}
		
		public function isModuleInstalled($module){
			return isset($this->settings['core']['activeModules'][$module]);
		}
		
		public function installModule($module, $more = false){
			if($info = $this->getModuleInfo($module)){
				if($info['installed']) return $more ? ['status' => false, 'msg' => 'Ошибка! Модуль уже установлен'] : false;
				
				if(!$this->checkModuleFiles($module, $fileError)) return $more ? ['status' => false, 'msg' => 'Ошибка! Отсутствует файл модуля ('.$fileError.')'] : false;
				
				if(!$info['needToInstall']){
					$this->setModuleSettings('core', ['activeModules' => [$module => false]]);
					$info['installed'] = true;
					return $more ? ['status' => true, 'msg' => 'Успех! Модуль установлен', 'data' => $info] : true;
				}
				
				$installFile = $this->homePath.'lib/modules/'.$module.'/install.php';
				if(file_exists($installFile)){
					require($installFile);
					if($iSuccess === true){
						$this->setModuleSettings('core', ['activeModules' => [$module => false]]);
						$info['installed'] = true;
						return $more ? ['status' => true, 'msg' => 'Успех! Модуль установлен', 'data' => $info] : true;
					}
					else return $more ? ['status' => false, 'msg' => $iError] : false;
				}
				else return $more ? ['status' => false, 'msg' => 'Ошибка! Отсутствует файл установки'] : false;
			}
			else return $more ? ['status' => false, 'msg' => 'Ошибка! Модуль не найден'] : false;
		}
		
		protected function checkModuleFiles($module, &$fileError){
			$info = $this->getModuleInfo($module)['files'];
			if(isset($info)){
				for($i = 0; $i < count($info); $i++){
					if(!file_exists($this->homePath.$info[$i]) || !is_file($this->homePath.$info[$i])){
						$fileError = $info[$i];
						return false;
					}
				}
			}
			return true;
		}
		
		public function isModuleActive($module){
			return $this->isModuleInstalled($module) && $this->settings['core']['activeModules'][$module] === true;
		}
		
		public function activateModule($module, $more = false){
			if($this->isModuleActive($module)) return $more ? ['status' => false, 'msg' => 'Ошибка! Модуль уже активен'] : false;
			if(!$this->checkModuleFiles($module, $fileError)) return $more ? ['status' => false, 'msg' => 'Ошибка! Не найден файл модуля ('.$fileError.')'] : false;
			$this->setModuleSettings('core', ['activeModules' => [$module => true]]);
			return $more ? ['status' => true, 'msg' => 'Успех! Модуль активирован', 'data' => $this->getModuleInfo($module)] : true;
		}
		
		public function deactivateModule($module, $more = false){
			if(!$this->isModuleActive($module)) return $more ? ['status' => false, 'msg' => 'Ошибка! Модуль уже неактивен'] : false;
			$this->setModuleSettings('core', ['activeModules' => [$module => false]]);
			return $more ? ['status' => true, 'msg' => 'Успех! Модуль деактивирован', 'data' => $this->getModuleInfo($module)] : true;
		}
		
		//--------------------| Для упрощения моддинга |--------------------//
		
		private function engInclude($folder){
			$path = $this->homePath.'lib/engIncludes/'.$folder;
			if(file_exists($path) && is_dir($path)){
				$result = scandir($path);
				$files = array_diff($result, array('.', '..'));
				if(count($files) > 0){
					foreach($files as $file){
						if(is_file("$path/$file")){
							include($path.'/'.$file);
						}
					}
				}
			}
		}
		
		protected function engForward($name, $data){
			$fwdData = $data;
			$this->engInclude($name);
		}
		
		//--------------------| Всякое |--------------------//
		
		protected $fileTypes = [
			'image' => ['png', 'jpg', 'gif'],
		];
		
		protected $fileBlackList = ['php', 'html', 'phtml', 'php3', 'php4', 'htm'];
		
		public function uploadFile($file, $fileName = '', $path = 'other', $type = '', $fileExp = '', $more = false){
			for($i = 0; $i < count($this->fileBlackList); $i++) if(preg_match("/\.".$this->fileBlackList[$i]."$/i", $file['name'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Недопустимый тип файла'] : false;
			if(!$this->checkFileExp($file['name'], $type)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный тип файла'] : false;
			if(!preg_match("/\.".$fileExp."$/i", $file['name'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверное расширение файла'] : false;
			if(!file_exists($this->homePath.'upload/'.$path)){
				if(!mkdir($this->homePath.'upload/'.$path, 0777, true)) return $more ? ['status' => false, 'msg' => 'Ошибка! Не удалось создать директорию'] : false;
			}
			if(move_uploaded_file($file['tmp_name'], $this->homePath.'upload/'.$path.'/'.$fileName)) return $more ? ['status' => true, 'msg' => 'Успех! Файл загружен', 'data' => 'upload/'.$path.'/'.$fileName] : true;
			else return $more ? ['status' => false, 'msg' => 'Ошибка! Ошибка записи файла'] : false;
		}
		
		private function checkFileExp($fileName, $type){
			if(!$type) return true;
			for($i = 0; $i < count($this->fileTypes[$type]); $i++) if(preg_match("/\.".$this->fileTypes[$type][$i]."$/i", $fileName)) return true;
			return false;
		}
		
		public function png2jpg($originalFile, $outputFile, $quality){
			$image = imagecreatefrompng($originalFile);
			imagejpeg($image, $outputFile, $quality);
			imagedestroy($image);
		}
		
		public static function redirect($link, $msg = ''){
			$_SESSION['msg'] = $msg;
			header('Location: '.$link);
		}
		
		public function post($url, $postVars = []){
			$postStr = http_build_query($postVars);
			$options = [
				'http' => [
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postStr
				]
			];
			$streamContext = stream_context_create($options);
			$result = file_get_contents($url, false, $streamContext);
			if($result === false){
				$error = error_get_last();
				throw new Exception('POST request failed: '.$error['message']);
			}
			return $result;
		}
		
		public function pageButtons($page, $total){
			
			$str = '<div align=center><ul class="pagination modal-5">';
			$str .= '<li><a class="prev fa fa-angle-double-left" href="?p=1"></a></li>';
			if($page-1 > 0) $str .= '<li><a class="fa fa-angle-left" href="?p='.($page-1).'"></a></li>';
			if($page-3 > 0) $str .= '<li><a href="?p='.($page-3).'">'.($page-3).'</a></li>';
			if($page-2 > 0) $str .= '<li><a href="?p='.($page-2).'">'.($page-2).'</a></li>';
			if($page-1 > 0) $str .= '<li><a href="?p='.($page-1).'">'.($page-1).'</a></li>';
			$str .= '<li><a href="#" class="active">'.$page.'</a></li>';
			if($page+1 <= $total) $str .= '<li><a href="?p='.($page+1).'">'.($page+1).'</a></li>';
			if($page+2 <= $total) $str .= '<li><a href="?p='.($page+2).'">'.($page+2).'</a></li>';
			if($page+3 <= $total) $str .= '<li><a href="?p='.($page+3).'">'.($page+3).'</a></li>';
			if($page+1 <= $total) $str .= '<li><a class="fa fa-angle-right" href="?p='.($page+1).'"></a></li>';
			$str .= '<li><a class="next fa fa-angle-double-right" href="?p='.$total.'"></a></li>';
			$str .='</ul></div>';
			
			return $str;
		}
		
		public function timeIntervalFormat($time){
			$hours = floor($time / 3600);
			$time = $time % 3600;
			$mins = floor($time / 60);
			$secs = $time % 60;
			return $hours.':'.($mins < 10 ? '0' : '').$mins.':'.($secs < 10 ? '0' : '').$secs;
		}
		
		public $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
		
		public function timeFormat($time, $firstLetter = false){
			$dayToday = (int) date('z');
			$dayYesterday = (int) date('z', time() - 86400);
			$daySend = (int) date('z', $time);
			$yearToday = (int) date('Y');
			$yearSend = (int) date('Y', $time);
			$monthSendRusWord = $this->months[(int) date('n', $time)-1];
			$timeSend = 'Когда-то :D ['.$dayToday.' | '.$dayYesterday.' | '.$daySend.' | '.$yearToday.' | '.$yearSend.']';
			if($yearToday == $yearSend){
				if($daySend == $dayToday) $timeSend = date('сегодня в G:i', $time);
				elseif($daySend == $dayYesterday) $timeSend = date('вчера в G:i', $time);
				else $timeSend = date('j ', $time).$monthSendRusWord.date(' G:i', $time);
			}
			else $timeSend = date('j ', $time).$monthSendRusWord.date(' G:i ', $time).$yearSend;
			return $firstLetter ? $this->mbUcfirst($timeSend) : $timeSend;
		}
		
		public function mbUcfirst($str, $encoding='UTF-8'){
			$str = mb_ereg_replace('^[\ ]+', '', $str);
			$str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).mb_substr($str, 1, mb_strlen($str), $encoding);
			return $str;
		}
		
		public function ajaxReturn($data){
			echo json_encode($data);
			die;
		}
		
		public function debug($msg){
			echo '<pre>';
			echo json_encode($msg);
			die;
		}
		
		public function getIp(){
			$client  = @$_SERVER['HTTP_CLIENT_IP'];
			$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
			$remote  = @$_SERVER['REMOTE_ADDR'];
			if(filter_var($client, FILTER_VALIDATE_IP)) $ip = $client;
			elseif(filter_var($forward, FILTER_VALIDATE_IP)) $ip = $forward;
			else $ip = $remote;
			return $ip;
		}
		
		//--------------------| Конец :) |--------------------//
	}
?>