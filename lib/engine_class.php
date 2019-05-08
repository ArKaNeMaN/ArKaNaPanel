<?php
	class engine{
		
		private $isRequest = false; // true если класс объявлен в реквесте (Указывается вручную при объявлении класса)
		private $debug = false;
		
		protected $pageContent; // Содержимое страницы
		
		public $error = ''; // Код ошибки
		public $homePath = ''; // Корневая папка панели (Используется для инклюда)
		public $userid = 0; // ID авторизованного пользователя (Для гостей null)
		public $sql; // Объект класса для работы с БД
		public $userInfo; // Информация об авторизованном пользователе (Для гостей false)
		public $modules; // Массив с объектами классов всех подгруженным модулей
		public $settings; // Настройки
		public $title = ''; // Заголовок страницы (Обычно присваивается в начале кода страницы)
		public $gamesList;
		
		//--------------------| Конструктор |--------------------//
		
		public function __construct($req = false){ // Конструктор класса
			session_start();
			
			ini_set('display_errors', $this->debug ? 1 : 0);
			error_reporting($this->debug ? E_ALL : 0);
			
			$this->isRequest = $req;
			if($this->isRequest) $this->homePath = '../';
			
			require_once $this->homePath.'configs/main.php';
			require $this->homePath.'configs/sql.php';
			require $this->homePath.'lib/db_class.php';
			require $this->homePath.'lib/gamesList.php';
			
			$this->sql = new DataBase();
			
			$this->modules['engine'] = &$this;
			$this->modules['core'] = &$this;
			
			$this->settings = $this->getSettings();
			$this->checkAuth();
			$this->loadActiveModules();
		}
		
		//--------------------| Получение контента |--------------------//
		
		public function loadContent(){ // Загрузка страницы
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
			if(!file_exists($this->getTplThemePath('temps', '/'.$content.'/index.aptpl')) && !file_exists($this->getTplThemePath('temps', '/'.$content.'.aptpl'))) $content = 'error/404';
			$this->pageContent = $this->getTpl($content);
			if($this->error != '') $this->pageContent = $this->getTpl('error/'.$this->error);
			echo $this->getTpl('main');
		}
		
		protected function getTpl($content){ // Получение содержимого страницы
			$content = file_exists($this->getTplThemePath('temps', '/'.$content.'.aptpl')) ? $this->getTplThemePath('temps', '/'.$content.'.aptpl') : $this->getTplThemePath('temps', '/'.$content.'/index.aptpl');
			ob_start(); 				 
			include($content);
			return ob_get_clean();
		}
		
		protected function getTplThemePath($folder, $path){
			$path_ = $folder.'/'.$this->settings['core']['panelTheme'].$path;
			if(file_exists($path_)) return $path_;
			return $folder.'/default'.$path;
		}
		
		public function loadJsPlugin($name, $inputDir = false){ // Добавление JS плагина на страницу
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
		
		public function loadAllJsPlugins(){ // Добавление всех JS плагинов на страницу
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
		
		public function checkAccess($group, $return = false){ // Проверка доступа пользователя
			$r = false;
			if($this->userid){
				if(is_array($group)) $r = (bool) (in_array($this->userInfo['group'], $group));
				else $r = (bool) ($this->userInfo['group'] <= $group || $group == 0);
			}
			if(!$r && !$return) $this->isRequest ? $this->ajaxReturn(['status' => false, 'msg' => 'Ошибка! Нет доступа']) : $this->error = '403';
			else return $r;
		}
		
		//--------------------| Манагер блоков |--------------------//
		
		public function incBlock($name){ // Добавление блока на страницу
			$fullName = $this->getTplThemePath('temps', '/blocks/'.$name.'/block.aptpl');
			if(file_exists($fullName)) include($fullName);
		}
		
		public function getAdminBlocks(){
			$path = $this->homePath.'temps/default/blocks';
			if(file_exists($path) && is_dir($path)){
				$result = scandir($path);
				$files = array_diff($result, array('.', '..'));
				if(count($files) > 0){
					foreach($files as $file){
						if(is_dir("$path/$file")){
							$blocks[] = $this->getBlockInfo($file);
						}
					}
					return $blocks;
				}
			}
			else return false;
		}
		
		public function getBlocksList($list){
			if(!isset($this->settings['core']['blocks'][$list])) return false;
			$blocks = json_decode($this->settings['core']['blocks'][$list], true);
			foreach($blocks as $k => $v){
				$blocks_[] = array_merge($v, [
					'id' => $k,
					'info' => $this->getBlockInfo($v['block']),
				]);
			}
			return $blocks_;
		}
		
		public function getBlockInfo($block){
			$infoFile = $this->homePath.'temps/default/blocks/'.$block.'/info.json';
			if(!file_exists($infoFile)) return false;
			$info = json_decode(file_get_contents($infoFile), true);
			$info['id'] = $block;
			return $info;
		}
		
		public function addBlock($block, $list, $pos){
			if(!$this->isBlockExists($block)) return false;
			$blocks = json_decode($this->settings['core']['blocks'][$list], true);
			$blocks[] = ['block' => $block, 'pos' => $pos];
			/* function sortFunc($a, $b){return ($a['pos'] > $b['pos']);}
			uasort($this->settings['core']['blocks'][$list], 'sortFunc'); */
			$this->setModuleSettings('core', ['blocks' => [$list => json_encode($blocks)]]);
			return true;
		}
		
		public function removeBlockByNum($blockId, $list){
			$blocks = json_decode($this->settings['core']['blocks'][$list], true);
			if(!isset($blocks[$blockId])) return false;
			unset($blocks[$blockId]);
			/* function sortFunc($a, $b){return ($a['pos'] > $b['pos']);}
			uasort($blocks, 'sortFunc'); */
			$this->setModuleSettings('core', ['blocks' => [$list => json_encode($blocks)]]);
			return true;
		}
		
		public function isBlockExists($block){
			return (bool) file_exists($this->homePath.'temps/default/blocks/'.$block.'/info.json');
		}
		
		
		public function getBlocksList_($list){
			if(!isset($this->settings['core']['blocks'][$list])) return false;
			$blocks = json_decode($this->settings['core']['blocks'][$list], true);
			foreach($blocks as $k => $v){
				$blocks_[] = array_merge($v, [
					'id' => $k,
					'info' => $this->getBlock($v['block']),
				]);
			}
			return $blocks_;
		}
		
		public function incBlock_($index){
			if(!$this->isBlockInstalled($index)) return false;
			$block = $this->getBlock($index);
			
			switch($block['type']){
				case 'file': {
					$contFile = $this->homePath.'temps/default/blocks/'.$block['index'].'/block.aptpl';
					if(!file_exists($contFile) || !is_file($contFile)) return false;
					include $contFile;
				}
				case 'custom': {
					try{
						eval($block['content']);
					}
					catch(Exception $e){
						echo $e->getTraceAsString();
						return false;
					}
					echo $block['content'];
				}
			}
			return true;
		}
		
		public function getBlocks(){
			$res = $this->sql->select('blocks', '*');
			return $res;
		}
		
		public function getBlock($index){
			if(!$this->isBlockInstalled($index)) return false;
			$res = $this->sql->select('blocks', '*', ['index' => $index])[0];
			return $res;
		}
		
		public function isBlockInstalled($index){
			return (bool) $this->sql->select('blocks', ['COUNT(*)'], ['index' => $index])[0]['COUNT(*)'];
		}
		
		public function importAllBlocks($output = false){
			$path = $this->homePath.'temps/default/blocks';
			if(file_exists($path) && is_dir($path)){
				$result = scandir($path);
				$files = array_diff($result, array('.', '..'));
				if(count($files) > 0){
					foreach($files as $file){
						if(is_dir("$path/$file")){
							if(!$this->isBlockInstalled($file)){
								$status = $this->installBlockFromFile($path.'/'.$file.'/info.json', [
									'type' => 'file',
									'index' => $file,
								], true);
								if($output) echo $file.': '.($status['status'] ? 'true' : 'false').' ('.$status['msg'].')<br>';
							}
							else if($output) echo $file.': Блок уже установлен<br>';
						}
					}
				}
			}
			return;
		}
		
		public function installBlockFromFile($file, $_data = [], $more = false){
			if(!file_exists($file) || !is_file($file)) return $more ? ['status' => false, 'msg' => 'Ошибка! Файл не найден'] : false;
			$data_ = json_decode(file_get_contents($file), true);
			if(!$data_) return $more ? ['status' => false, 'msg' => 'Ошибка! Ошибка синтаксиса файла'] : false;
			
			$data_ = array_merge_recursive($data_, $_data);
			$blockInfoItems = ['index', 'name', 'module', 'rightCol', 'homePage', 'type', 'content', 'dataList'];
			for($i = 0; $i < count($blockInfoItems); $i++){
				if(isset($data_[$blockInfoItems[$i]])) $data[$blockInfoItems[$i]] = $data_[$blockInfoItems[$i]];
				else $data[$blockInfoItems[$i]] = null;
			}
			
			// Для совместимости
			if(isset($data_['allowRightCol'])) $data['rightCol'] = $data_['allowRightCol'];
			if(isset($data_['allowHomePage'])) $data['homePage'] = $data_['allowHomePage'];
			
			$contFile = $this->homePath.'temps/default/blocks/'.$data['index'].'/block.aptpl';
			$data['content'] = '';
			
			switch($data['type']){
				case 'file': {
					if(!file_exists($contFile) || !is_file($contFile)) return $more ? ['status' => false, 'msg' => 'Ошибка! Файл с содержимым блока не найден'] : false;
					break;
				}
				case 'custom': {
					if($data_['content']) $data['content'] = $data_['content'];
					elseif(file_exists($contFile) && is_file($contFile)) $data['content'] = file_get_contents($contFile);
					else return $more ? ['status' => false, 'msg' => 'Ошибка! Не указано содержимое блока'] : false;
					break;
				}
			}
			
			$result = $this->installBlock($data['index'], $data['type'], $data['content'], $data['name'], $data['module'], $data['rightCol'], $data['homePage']);
			return $more ? [
				'status' => $result, 'msg' => $result ? 'Успех! Блок установлен' : 'Ошибка! Ошибка установки блока'
			] : $result;
		}
		
		/*
			Типы блока:
				custom	Содержимое блока хранится в БД.
						Указывается в параметре $content или так же в файле, но в любом случае записывается в БД и берётся от туда.
						Можно, но не рекомендуется использовать PHP код.
				
				file	Контент блока хранится в файле. Параметр $content игнорируется.
		*/
		
		public function installBlock($index, $type = 'file', $content = '', $name = 'Unnamed', $module = 'custom', $rightCol = true, $homePage = false){
			$id = $this->sql->insert('blocks', [
				'index' => $index,
				'name' => $name,
				'module' => $module,
				'content' => $content,
				'type' => $type,
				'rightCol' => (int) $rightCol,
				'homePage' => (int) $homePage,
			]);
			if(!$id) return false;
			return $id;
		}
		
		public function updateBlock($index, $data){
			if(!$this->isBlockInstalled($index)) return $more ? ['status' => false, 'msg' => 'Ошибка! Указанный блок не найден'] : false;
			//$block = $this->getBlock($index);
			
			$update = [];
			$blockInfoItems = ['name', 'rightCol', 'homePage', 'data', 'content', 'dataList'];
			for($i = 0; $i < count($blockInfoItems); $i++){
				if(isset($data[$blockInfoItems[$i]])) $update[$blockInfoItems[$i]] = $data[$blockInfoItems[$i]];
			}
			
			return $this->sql->update('blocks', $update, ['index' => $index]);
		}
		
		public function updateBlockData($index, $data){
			if(!$this->isBlockInstalled($index)) return false;
			$block = $this->getBlock($index);
			
			$newData = array_merge_recursive($block['data'], $data);
			
			return $this->updateBlock($index, ['data' => json_encode($newData)]);
		}
		
		public function deleteBlock($index){
			if(!$this->isBlockInstalled($index)) return false;
			$this->sql->delete('blocks', ['index' => $index]);
			return !$this->isBlockInstalled($index);
		public function updateBlockFromFile($index, &$err = 0){
			if(!$this->isBlockInstalled($index, $err)) return false;
			$block = $this->getBlockFromFile($index);
			$id = $this->sql->select('blocks', ['id'], ['index' => $index])[0]['id'];
			return $this->updateBlock($id, $block);
		}
		
		//--------------------| Аккаунт |--------------------//
		
		public function userReg($data, $more = false){ // Регистрация нового пользователя
			
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
			
			$data['name'] = htmlentities($data['name']);
			$data['name'] = str_replace("&nbsp;", '', $data['name']);
			$data['name'] = htmlspecialchars($data['name']);
			
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
		
		public function regDataCheck($data, $more = false){ // Проверка полученных данных для регистрации
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
		
		public function userAuth($data, $more = false){ // Авторизация пользователя
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
		
		private function updateUserHash($id){ // Пересоздание хеша сессии пользователя
			$data = $this->getUserInfo($id);
			$userHash = md5($data['login'].'saltysalt'.$data['pass'].time());
			$this->sql->update('users', ['userHash' => $userHash], ['id' => $id]);
			return $userHash;
		}
		
		public function userLogout($path){ // Логаут пользователя
			if(is_array($_SESSION)) foreach($_SESSION as $key => $value) unset($_SESSION[$key]);
			SetCookie('userHash', '', 32600, '/', null, null, true);
			unset($this->userid);
			session_destroy();
			$this->redirect($path);
			die;
		}
		
		public function checkAuth(){ // Проверка сессии пользователя
			if(isset($_COOKIE['userHash']) && !empty($_COOKIE['userHash'])){
				if(is_array($res = $this->sql->select('users', ['id'], ['userHash' => $_COOKIE['userHash']]))){
					$this->userid = $res[0]['id'];
					$this->userInfo = $this->getUserInfo($this->userid);
					$this->sql->update('users', ['lastIp' => $this->getIp()], ['id' => $this->userid]);
				}
			}
		}
		
		public function getUserInfo($userid){ // Получение информации об аккаунте
			$res = $this->sql->select('users', '*', ['id' => $userid], 'id', true, 1)[0];
			if($res != '') $res['data'] = json_decode($res['data'], true);
			$res['pass'] = null;
			return $res;
		}
		
		public function getUsers($page = 1, $items = 15){
			$total = $this->sql->select('users', ['COUNT(*)'])[0]['COUNT(*)'];
			$totalPages = round($total/$items, 0, PHP_ROUND_HALF_UP);
			$page = max(min($totalPages, $page), 1);
			$res = $this->sql->select('users', '*', '', '`id`', false, ($page-1)*$items.', '.$items);
			for($i = 0; $i < count($res); $i++){
				$res[$i]['data'] = json_decode($res[$i]['data'], true);
				$res[$i]['groupInfo'] = $res[$i]['data'];
			}
			return $res;
		}
		
		public function getLastUser(){ // Получение идентификатора последнего зарегистрированного пользователя [НАДО ПЕРЕНЕСТИ В ОТДЕЛЬНЫЙ МОДУЛЬ]
			return $this->getUserInfo($this->sql->select('users', ['id'], '', 'id', false, 1)[0]['id']);
		}
		
		public function isUserExists($id, &$err = 0){
			$res = (bool) $this->sql->select('users', ['COUNT(*)'], ['id' => $id])[0]['COUNT(*)'];
			if(!$res) $err = 'Пользователь не найден';
			return $res;
		}
		
		public function editUserInfo($id, $data, &$err = 0){ // Изменение информации об аккаунте
			if(!$this->isUserExists($id, $err)) return false;
			
			$userInfo = $this->getUserInfo($id);
			$needUpdateHash = false;
			$newData = [];
			
			if(isset($data['email']) && $data['email'] != $userInfo['email'] && $data['email'] != ''){
				if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
					$err = 'Почта введена неверно';
					return false;
				}
				if($this->sql->select('users', ['COUNT(*)'], ['email' => $data['email']])[0]['COUNT(*)']){
					$err = 'Указанная почта уже занята';
					return false;
				}
				$newData['email'] = $data['email'];
			}
			
			if(isset($data['login']) && $data['login'] != $userInfo['login']){
				if(strlen($data['login']) < 4){
					$err = 'Логин должен быть длиннее 3-х символов';
					return false;
				}
				if(!preg_match('#^[aA-zZ0-9\-_]+$#', $data['login'])){
					$err = 'Логин не должен содержать спец. символы';
					return false;
				}
				if($this->sql->select('users', ['COUNT(*)'], ['login' => $data['login']])[0]['COUNT(*)']){
					$err = 'Логин '.$data['login'].' занят другим пользователем';
					return false;
				}
				$newData['login'] = $data['login'];
				$needUpdateHash = true;
			}
			
			if(isset($data['name']) && $userInfo['name'] != $data['name']){
				$data['name'] = htmlentities($data['name']);
				$data['name'] = str_replace("&nbsp;", '', $data['name']);
				$data['name'] = htmlspecialchars($data['name']);
				if($this->sql->select('users', ['COUNT(*)'], ['name' => $data['name']])[0]['COUNT(*)']){
					$err = 'Игрок с таким ником уже существует';
					return false;
				}
				$newData['name'] = $data['name'];
			}
			
			if(isset($data['pass']) && $data['pass'] != ''){
				if(strlen($data['pass']) < 4){
					$err = 'Пароль должен быть длиннее 3-х символов';
					return false;
				}
				$newData['pass'] = md5($data['pass']);
				$needUpdateHash = true;
			}
			
			if(isset($data['avatar']) && $userInfo['avatar'] != $data['avatar']){
				if(!file_exists($this->homePath.$data['avatar'])){
					$err = 'Файл аватара не найден';
					return false;
				}
				$newData['avatar'] = $data['avatar'];
			}
			
			if(isset($data['money'])){
				$newData['money'] = (int) $data['money'];
			}
			
			if(isset($data['group'])){
				if(!$this->isUserGroupExists((int) $data['group'], $err)) return false;
				$newData['group'] = (int) $data['group'];
			}
			
			if($newData == []){
				$err = 'Данные не введены';
				return false;
			}
			$this->sql->update('users', $newData, ['id' => $id]);
			
			if($needUpdateHash) $this->updateUserHash($id);
			return true;
		}
		
		public function editUserData($id, $module, $data, $more = false){
			if(!$data) 
			
			$res = json_decode($this->sql->select('users', ['data'], ['id' => $id])[0]['data'], true);
			$newData[$module] = array_replace_recursive($res[$module], $data);
			
			$this->sql->update('users', ['data' => $newData], ['id' => $id]);
			
		}
		
		public function isUserGroupExists($id, &$err = 0){
			if(!$this->sql->select('groups', ['COUNT(*)'], ['id' => $id])[0]['COUNT(*)']){
				$err = 'Указанная группа не найдена';
				return false;
			}
			return true;
		}
		
		public function getUserGroupsCount(){
			return $this->sql->select('groups', ['COUNT(*)'])[0]['COUNT(*)'];
		}
		
		public function addUserGroup($name, $access){
			return $this->sql->insert('groups', ['name' => $name, 'accessLevel' => $access]);
		}
		
		public function editUserGroup($id, $name, $access, &$err = 0){
			if(!$this->isUserGroupExists($id, $err)) return false;
			return $this->sql->update('groups', ['name' => $name, 'accessLevel' => $access], ['id' => $id]);
		}
		
		public function editUserGroupData($id, $module, $data, &$err = 0){
			if(!$this->isUserGroupExists($id, $err)) return false;
			$old = json_decode($this->getUserGroup($id)['data']);
			$old[$module] = array_merge_recursive($old[$module], $data);
			return $this->sql->update('groups', ['data' => $data_], ['id' => $id]);
		}
		
		public function delUserGroup($id, &$err = 0){
			if(!$this->isUserGroupExists($id, $err)) return false;
			return $this->sql->delete('groups', ['id' => $id]);
		}
		
		public function getUserGroup($id, &$err = 0){
			$res = $this->sql->select('groups', '*', ['id' => $id])[0];
			if(!$res){
				$err = 'Указанная группа не найдена';
				return false;
			}
			$res['data'] = json_decode($res['data'], true);
			return $res;
		}
		
		public function getUserGroups(&$err = 0){ // Получение массива групп пользователей
			$res = $this->sql->select('groups', '*');
			if(!$res){
				$err = 'Группы пользователей не найдены';
				return false;
			}
			for($i = 0; $i < count($res); $i++) $res[$i]['data'] = json_decode($res[$i]['data'], true);
			return $res;
		}
		
		//--------------------| Сервера |--------------------//
		
		public function getServers($onlyActive = true, $game = '', $limit = ''){ // Получение всех серверов
			$where = '';
			if($onlyActive) $where['active'] = 1;
			if($game != '') $where['game'] = $game;
			if(!($res = $this->sql->select('servers', '*', $where, 'id', true, $limit))) return false;
			
			for($i = 0; $i < count($res); $i++){
				$data[] = [
					'id' => (int) $res[$i]['id'],
					'name' => $res[$i]['name'],
					'game' => (int) $res[$i]['game'],
					'gameName' => $this->gamesList[$res[$i]['game']]['name'],
					'gameData' => $this->gamesList[$res[$i]['game']],
					'ip' => $res[$i]['ip'],
					'port' => (int) $res[$i]['port'],
					'fullAddress' => $res[$i]['ip'].':'.$res[$i]['port'],
					'data' => json_decode($res[$i]['data'], true),
					'active' => (bool) $res[$i]['active'],
				];
			}
			return $data;
		}
		
		public function getServer($id){ // Получение одного сервера
			if(!($res = $this->sql->select('servers', '*', ['id' => $id], '`id`', true, 1)[0])) return false;
			$data = [
				'id' => (int) $res['id'],
				'name' => $res['name'],
				'game' => $res['game'],
				'gameName' => $this->gamesList[$res['game']]['name'],
				'gameData' => $this->gamesList[$res['game']],
				'ip' => $res['ip'],
				'port' => $res['port'],
				'fullAddress' => $res['ip'].':'.$res['port'],
				'data' => json_decode($res['data'], true),
				'active' => (bool) $res['active'],
			];
			
			return $data;
		}
		
		public function addServer($data, $more = false){ // Добавление нового сервера
			if(!isset($this->gamesList[$data['game']]['name'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Неизвестная игра'] : false;
			if(!filter_var($data['ip'], FILTER_VALIDATE_IP) && !filter_var($data['ip'], FILTER_VALIDATE_DOMAIN)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный формат IP адреса'] : false;
			if($data['port'] > 49151) return $more ? ['status' => false, 'msg' => 'Ошибка! Порт указан неверно'] : false;
			
			$sendData = [
				'port' => (int) $data['port'],
				'ip' => $data['ip'],
				'game' => (int) $data['game'],
				'name' => htmlspecialchars($data['name']),
				'active' => (int) $data['active']
			];
			$id = $this->sql->insert('servers', $sendData);
			$sendData['id'] = $id;
			$this->addLog('core', 'addServer', 'Добавлен сервер '.$sendData['name'].'['.$sendData['ip'].':'.$sendData['port'].'].', $sendData);
			return $more ? ['status' => true, 'msg' => 'Успех! Сервер добавлен', 'data' => array_merge($sendData, ['id' => $id, 'gameName' => $this->gamesList[$sendData['game']]['name'], 'fullAddress' => $sendData['ip'].':'.$sendData['port']])] : false;
		}
		
		public function delServer($id, $more = false){ // Удаление сервера
			if($id < 1) return $more ? ['status' => false, 'msg' => 'Ошибка! Такого сервера не существует'] : false;
			if($this->sql->delete('servers', ['id' => $id])) return $more ? ['status' => true, 'msg' => 'Успех! Сервер удалён', 'data' => ['id' => $id]] : true;
			return $more ? ['status' => false, 'msg' => 'Ошибка! Что-то пошло не так :('] : false;
		}
		
		public function editServer($id, $data, $more = false){ // Изменение информации о сервере
			if(!isset($this->gamesList[$data['game']]['name'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Неизвестная игра'] : false;
			if(!filter_var($data['ip'], FILTER_VALIDATE_IP) && !filter_var($data['ip'], FILTER_VALIDATE_DOMAIN)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный формат IP адреса'] : false;
			if($data['port'] > 49151) return $more ? ['status' => false, 'msg' => 'Ошибка! Порт указан неверно'] : false;
			
			$sendData = [
				'port' => (int) $data['port'],
				'ip' => $data['ip'],
				'game' => (int) $data['game'],
				'name' => htmlspecialchars($data['name']),
				'active' => (int) $data['active']
			];
			$this->sql->update('servers', $sendData, ['id' => $id]);
			return $more ? ['status' => true, 'msg' => 'Успех! Сервер изменён', 'data' => array_merge($sendData, ['id' => $id, 'gameName' => $this->gamesList[$sendData['game']]['name'], 'fullAddress' => $sendData['ip'].':'.$sendData['port']])] : false;
		}
		
		public function setServerData($id, $data){ // Изменение дополнительной информации о сервере
			if($old = json_decode($this->sql->select('servers', ['data'], ['id' => $id], '`id`', true, 1)[0]['data'], true)){
				$data = array_replace_recursive($old, $data);
			}
			$this->sql->update('servers', ['data' => json_encode($data)], ['id' => $id]);
			return true;
		}
		
		public function setServerSettings($id, $module, $data){
			if($old = json_decode($this->sql->select('servers', ['settings'], ['id' => $id], '`id`', true, 1)[0]['settings'], true)){
				$data_ = array_replace_recursive($old, [$module => $data]);
			}
			else $data_ = [$module => $data];
			$this->sql->update('servers', ['settings' => json_encode($data_)], ['id' => $id]);
			return true;
		}
		
		public function getServerSettings($id, $module){
			return json_decode($this->sql->select('servers', ['settings'], ['id' => $id])[0]['settings'], true)[$module];
		}
		
		public function isServerExists($id){
			return (bool) $this->sql->select('servers', ['COUNT(*)'], ['id' => $id])[0]['COUNT(*)'];
		}
		
		//--------------------| Меню |--------------------//
		
		public function getMenuItems($onlyActive = true){ // Получение ВСЕХ пунктов меню с их подпунктами
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
		
		public function addMenuItem($data = null, $more = false){ // Добавление нового пункта меню
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
		
		public function editMenuItem($id, $data, $more = false){ // Изменение информации о пункте меню
			
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
		
		public function getMenuItem($id){ // Получеие пункта меню с его подпунктами
			$res = $this->sql->select('menu', '*', ['id' => $id])[0];
			if($res['submenu'] && !$res['parent']) $res['submenu'] = $this->sql->select('menu', '*', ['parent' => $res['parent']], 'pos', true);
			return $res;
		}
		
		public function checkTypeAccess(){
			
		}
		
		public function deleteMenuItem($id, $more = false){ // Удаление пункта меню
			if(!$this->isMenuItemExists($id)) return $more ? ['status' => false, 'msg' => 'Ошибка! Такого пункта не существует'] : false;
			$this->sql->delete('menu', '`id`='.$id.' OR `parent`='.$id);
			if(!$this->isMenuItemExists($id)) return $more ? ['status' => true, 'msg' => 'Успех! Пункт меню удалён', 'data' => $id] : true;
			else return $more ? ['status' => false, 'msg' => 'Ошибка! Что-то пошло не так :('] : false;
		}
		
		public function isMenuItemExists($id){ // Проверка пункта меню на наличие
			return (bool) $this->sql->select('menu', ['COUNT(*)'], ['id' => $id])[0]['COUNT(*)'];
		}
		
		//--------------------| Настройки |--------------------//
		
		protected function getSettings(){ // Получение ВСЕХ настроек
			if(!($res = $this->sql->select('settings', '*'))) return false;
			for($i = 0; $i < count($res); $i++){
				$setts = json_decode($res[$i]['data'], true);
				$data[$res[$i]['module']] = $setts;
			}
			return $data;
		}
		
		public function getSettingsByModule($module){ // Получение настроек указанного модуля
			$res = json_decode($this->sql->select('settings', '*', ['module' => $module])[0]['data'], true);
			return $res != null ? $res : false;
		}
		
		public function setModuleSettings($module, $settings){ // Изменение настроек для указанного модуля
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
		
		//--------------------| Манагер модулей |--------------------//
		
		public function getModulesFromFiles(&$err = 0){
			$path = $this->homePath.'lib/modules';
			if(file_exists($path) && is_dir($path)){
				$result = scandir($path);
				$files = array_diff($result, array('.', '..'));
				if(count($files) > 0){
					foreach($files as $file){
						if(is_dir("$path/$file")){
							$modules[] = $this->getModuleFromFile($file);
						}
					}
					return $modules;
				}
			}
			$err = 'Модули не найдены';
			return false;
		}
		
		public function getModuleFromFile($index, &$err = 0){
			$path = $this->homePath.'lib/modules/'.$index;
			if(file_exists($path) && is_dir($path)){
				$data = [
					'index' => $index,
					'info' => file_exists($path.'/info.json') ? json_decode(file_get_contents($path.'/info.json'), true) : null,
					'settingsTpl' => file_exists($path.'/settings.json') ? json_decode(file_get_contents($path.'/settings.json'), true) : null,
				];
				return $data;
			}
			$err = 'Не найден файл с информацией о модуле';
			return false;
		}
		
		public function getModules(){
			$res = $this->sql->select('modules', '*');
			if(!$res) return false;
			for($i = 0; $i < count($res); $i++){
				$res[$i] = array_merge($res[$i], [
					'data' => json_decode($res[$i]['data'], true),
					'files' => json_decode($res[$i]['files'], true),
					'reqs' => json_decode($res[$i]['reqs'], true),
					'settingsTpl' => json_decode($res[$i]['settingsTpl'], true),
					'settingsPage' => $res[$i]['settingsTpl'] ? 'admin/modules/auto?module='.$res[$i]['index'] : ($res[$i]['settingsPage'] ? $res[$i]['settingsPage'] : null),
					'hasSettings' => (bool) ($res[$i]['settingsPage'] || $res[$i]['settingsTpl']),
				]);
			}
			return $res;
		}
		
		public function getModule($index){
			if($index == 'core'){
				return [
					'name' => 'Ядро',
					'index' => 'core',
					'version' => file_get_contents($this->homePath.'apVer.txt'),
					'author' => 'ArKaNeMaN',
					'status' => true,
					'data' => null,
					'settingsTpl' => null,
					'files' => null,
					'reqs' => null,
					'settingsPage' => 'admin',
					'settingsPage' => 'hasSettings',
				];
			}
			$res = $this->sql->select('modules', '*', ['index' => $index])[0];
			if(!$res) return false;
			return array_merge($res, [
				'data' => json_decode($res['data'], true),
				'files' => json_decode($res['files'], true),
				'reqs' => json_decode($res['reqs'], true),
				'settingsTpl' => json_decode($res['settingsTpl'], true),
				'settingsPage' => $res['settingsTpl'] ? 'admin/modules/auto?module='.$res['index'] : ($res['settingsPage'] ? $res['settingsPage'] : null),
				'hasSettings' => (bool) ($res['settingsPage'] || $res['settingsTpl']),
			]);
		}
		
		public function registerModule($data, &$err = 0){
			if(!isset($data['index'])){
				$err = 'Не указан индекс модуля';
				return false;
			}
			if($this->isRegisteredModule($data['index'], $err)) return false;
			if(isset($data['reqs']) && !$this->checkReqsModules($data['reqs'], $err)) return false;
			if(isset($data['files']) && !$this->checkModuleFiles_($data['files'], $err)) return false;
			
			$data_ = [];
			$data_['index'] = $data['index'];
			if(isset($data['data'])) $data_['data'] = json_encode($data['data']);
			if(isset($data['reqs'])) $data_['reqs'] = json_encode($data['reqs']);
			if(isset($data['files'])) $data_['files'] = json_encode($data['files']);
			if(isset($data['settingsTpl'])) $data_['settingsTpl'] = json_encode($data['settingsTpl']);
			else if(isset($data['settingsPage'])) $data_['settingsPage'] = $data['settingsPage'];
			if(isset($data['version'])) $data_['version'] = $data['version'];
			if(isset($data['verInt'])) $data_['verInt'] = (int) $data['verInt'];
			if(isset($data['author'])) $data_['author'] = $data['author'];
			if(isset($data['name'])) $data_['name'] = $data['name'];
			if(isset($data['needInstall']) && $data['needInstall']) $data_['status'] = -1;
			
			$id = $this->sql->insert('modules', $data_);
			if($id) $this->addLog('core', 'regModule', 'Добавлен модуль '.$data_['name'].'['.$data_['index'].'].', $data_);
			return $id;
		}
		
		public function installModule($index, &$err = 0){ // Установка модуля
			
			if(!($module = $this->getModule($index, $err))) return false;
			if($module['status'] != -1){
				$err = 'Модуль не нуждается в установке';
				return false;
			}
			if(isset($module['reqs']) && !$this->checkReqsModules($module['reqs'], $err)) return false;
			if(isset($module['files']) && !$this->checkModuleFiles_($module['files'], $err)) return false;
			
			$installer = $this->homePath.'lib/modules/'.$module['index'].'/install.php';
			if(!file_exists($installer) || !is_file($installer)){
				$err = 'Не найден скрипт установки';
				return false;
			}
			
			$iSuccess = null;
			require $installer;
			if($iSuccess === true){
				$this->turnModule($module['index'], 0);
				return true;
			}
			else{
				$err = $iError;
				return false;
			}
		}
		
		public function updateModule($index){
			
		}
		
		public function turnModule($index, $turn, &$err = 0){
			if(!$this->isRegisteredModule($index, $err)) return false;
			$this->sql->update('modules', ['status' => $turn ? 1 : 0], ['index' => $index]);
			return true;
		}
		
		public function registerModuleFromFile($index, &$err = 0){
			if(!($data = $this->getModuleFromFile($index, $err))) return false;
			$data_ = $data['info'];
			$data_['settingsTpl'] = $data['settingsTpl'];
			$data_['index'] = $data['index'];
			return $this->registerModule($data_, $err);
		}
		
		public function isRegisteredModule($index, &$err = 0){
			$res = (bool) $this->sql->select('modules', ['COUNT(*)'], ['index' => $index])[0]['COUNT(*)'];
			if(!$res) $err = 'Модуль `'.$index.'` не найден';
			else $err = 'Модуль `'.$index.'` уже установлен';
			return $res;
		}
		
		public function isModuleActive($index){ // Проверка модуля на активность
			return (bool) $this->sql->select('modules', ['COUNT(*)'], ['index' => $index, 'status' => 1])[0]['COUNT(*)'];
		}
		
		protected function checkModuleFiles_($list, &$err = 0){ // Проверка целостности файлов модуля
			for($i = 0; $i < count($list); $i++){
				if(!file_exists($this->homePath.$list[$i]) || !is_file($this->homePath.$list[$i])){
					$err = 'Не найден файл `'.$list[$i].'`';
					return false;
				}
			}
			return true;
		}
		
		public function checkReqsModules($list, &$err = 0){
			for($i = 0; $i < count($list); $i++){
				if(!$this->isRegisteredModule($list[$i]['index'], $err)) return false;
				if(!isset($list[$i]['version'])) continue;
				$module = $ths->getModule($list[$i]['index']);
				if($module['verInt'] >= (int) $list[$i]['ver']['int']) continue;
				else{
					$err = '`'.$list[$i]['index'].'` module is wrong version. Current '.$module['version'].'. Required '.$list[$i]['ver']['str'];
					return false;
				}
			}
			return true;
		}
		
		private function loadActiveModules(){
			$res = $this->sql->select('modules', ['index'], ['status' => 1]);
			for($i = 0; $i < count($res); $i++){
				$classFile = $this->homePath.'lib/modules/'.$res[$i]['index'].'/class.php';
				if(file_exists($classFile) && is_file($classFile)){
					require $classFile;
					$this->modules[$res[$i]['index']] = new $res[$i]['index']($this);
				}
				else $this->turnModule($res[$i]['index'], 0);
			}
		}
		
		public function delModule($index, &$err = 0){
			if(!$this->isRegisteredModule($index, $err)) return false;
			$this->sql->delete('modules', ['index' => $index]);
			return !$this->isRegisteredModule($index);
		}
		
		//--------------------| Логи |--------------------//
		
		public function addLog($module, $type, $text, $data = []){
			return $this->sql->insert('logs', [
				'userid' => $this->userid,
				'module' => $module,
				'type' => $type,
				'text' => $text,
				'time' => time(),
				'data' => json_encode($data),
			]);
		}
		
		public function getLogs($page = 1, $items = 20, $module = null, $type = null, &$err = 0){
			$where = [];
			if($module) $where['module'] = $module;
			if($type) $where['type'] = $type;
			if($where == []) $where = '';
			$total = $this->getLogsCount($module, $type);
			$totalPages = round($total/$items, 0, PHP_ROUND_HALF_UP);
			$page = max(min($totalPages, $page), 1);
			$res = $this->sql->select('logs', '*', $where, '`time`', false, ($page-1)*$items.', '.$items);
			if(!$res){
				$err = 'Не найдено ни одного лога';
				return false;
			}
			for($i = 0; $i < count($res) ;$i++){
				$res[$i]['data'] = json_decode($res[$i]['data'], true);
				$res[$i]['timeF'] = $this->timeFormat($res[$i]['time'], true);
			}
			return $res;
		}
		
		public function getLogsCount($module = null, $type = null){
			$where = [];
			if($module) $where['module'] = $module;
			if($type) $where['type'] = $type;
			if($where == []) $where = '';
			return $this->sql->select('logs', ['COUNT(*)'], $where)[0]['COUNT(*)'];
		}
		
		public function getLogTypes(){
			$res = $this->sql->select('logs', ['type'], '`id`>0 GROUP BY `type`', '`type`');
			for($i = 0; $i < count($res) ;$i++) $res[$i] = $res[$i]['type'];
			return $res;
		}
		
		//--------------------| Для моддинга |--------------------//
		
		private $fwds = [];
		
		public function onEvent($event, $module, $funcName){ // Регистрация обработчика форварда ('Название форварда', 'Идентификитор модуля', 'Название функции')
			$this->fwds[$event][] = [
				'module' => $module,
				'func' => $funcName,
			];
		}
		
		public function callForward($event, &$data){ // Вызов форварда ('Название форврда', [Данные передаваемые в обработчик])
			for($i = 0; $i < count($this->fwds[$event]); $i++){
				call_user_func([$this->modules[$this->fwds[$event][$i]['module']], $this->fwds[$event][$i]['func']], $data);
			}
		}
		
		//--------------------| Всякое |--------------------//
		
		protected $fileTypes = [ // Список типов файлов с возможными расширениями
			'image' => ['png', 'jpg', 'gif'],
		];
		
		// Список запрещённых расширений (Без точки)
		protected $fileBlackList = ['php', 'html', 'phtml', 'php3', 'php4', 'htm'];
		
		public function uploadFile($file, $fileName = '', $path = 'other', $type = '', $fileExp = '', $more = false){ // Загрузка файла
			for($i = 0; $i < count($this->fileBlackList); $i++) if(preg_match("/\.".$this->fileBlackList[$i]."$/i", $file['name'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Недопустимый тип файла'] : false;
			if(!$this->checkFileExp($file['name'], $type)) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверный тип файла'] : false;
			if(!preg_match("/\.".$fileExp."$/i", $file['name'])) return $more ? ['status' => false, 'msg' => 'Ошибка! Неверное расширение файла'] : false;
			if(!file_exists($this->homePath.'upload/'.$path)){
				if(!mkdir($this->homePath.'upload/'.$path, 0777, true)) return $more ? ['status' => false, 'msg' => 'Ошибка! Не удалось создать директорию'] : false;
			}
			if(move_uploaded_file($file['tmp_name'], $this->homePath.'upload/'.$path.'/'.$fileName)) return $more ? ['status' => true, 'msg' => 'Успех! Файл загружен', 'data' => 'upload/'.$path.'/'.$fileName] : true;
			else return $more ? ['status' => false, 'msg' => 'Ошибка! Ошибка записи файла'] : false;
		}
		
		private function checkFileExp($fileName, $type){ // Првоерка пасширение файла для указанного типа файла
			if(!$type) return true;
			for($i = 0; $i < count($this->fileTypes[$type]); $i++) if(preg_match("/\.".$this->fileTypes[$type][$i]."$/i", $fileName)) return true;
			return false;
		}
		
		/* public function png2jpg($originalFile, $outputFile, $quality){ // ???
			$image = imagecreatefrompng($originalFile);
			imagejpeg($image, $outputFile, $quality);
			imagedestroy($image);
		} */
		
		public static function redirect($link, $msg = ''){ // Редирект на указанный URL
			$_SESSION['msg'] = $msg;
			header('Location: '.$link);
		}
		
		public function post($url, $postVars = []){ // Отправка POST запроса
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
		
		public function pageButtons($page, $total){ // Вывод кнопок навигации по страницам
			
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
		
		public function timeIntervalFormat($time){ // Форматирование временнОго промежутка в формате hh:mm:ss
			$hours = floor($time / 3600);
			$time = $time % 3600;
			$mins = floor($time / 60);
			$secs = $time % 60;
			return $hours.':'.($mins < 10 ? '0' : '').$mins.':'.($secs < 10 ? '0' : '').$secs;
		}
		
		public $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
		
		public function timeFormat($time, $firstLetter = false){ // Форматировани времени в формате как в ВК
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
		
		public function ajaxReturnStatus($status, $msg, $data = null){
			$this->ajaxReturn([
				'status' => $status,
				'msg' => ($status ? 'Успех! ' : 'Ошибка! ').$msg,
				'data' => $data,
			]);
		}
		
		public function debug($msg){
			echo '<pre>';
			echo json_encode($msg);
			die;
		}
		
		public function getIp(){ // Получение IP
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