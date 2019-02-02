<?
	class banlist{
		
		private $sql;
		public $pageItems;
		private $engine;
		//private $servers;
		
		public function __construct(&$engine){
			$this->engine = &$engine;
			$this->pageItems = $this->engine->settings['banlist']['pageItems'];
			require_once($this->engine->homePath.'lib/db_class.php');
			$this->sql = new DataBase($this->engine->settings['banlist']['sqlHost'], $this->engine->settings['banlist']['sqlUser'], $this->engine->settings['banlist']['sqlPass'], $this->engine->settings['banlist']['sqlDb'], $this->engine->settings['banlist']['sqlPrefix'], $this->engine->settings['banlist']['sqlEncode']);
		}
		
		public function getBans($onlyActive = true, $page = 1, &$totalPages = null, $pageItems = null){
			if($pageItems == null) $pageItems = $this->pageItems;
			$where = '';
			if($onlyActive) $where = '`ban_length`*60+`ban_created`>'.time().' OR `ban_length`=0';
			$totalPages = round($this->sql->select('bans', ['COUNT(*)'], $where, 'bid')[0]['COUNT(*)']/$pageItems);
			$page = max(1, $page);
			$page = min($page, $totalPages);
			$res = $this->sql->select('bans', '*', $where, 'bid', false, ($page-1)*$pageItems.', '.$pageItems);
			for($i = 0; $i < count($res); $i++) $this->addFormatedData($res[$i]);
			return $res;
		}
		
		public function getBanInfo($bid){
			$res = $this->sql->select('bans', '*', ['bid' => $bid], 'bid')[0];
			$this->addFormatedData($res);
			return $res;
		}
		
		public function addFormatedData(&$ban){
			if(isset($ban['ban_length'])) $ban['ban_lengthF'] = $ban['ban_length'] > 0 ? $this->engine->timeIntervalFormat($ban['ban_length']*60) : 'Навсегда';
			if(isset($ban['ban_created'])) $ban['ban_createdF'] = $this->engine->timeFormat($ban['ban_created'], true);
		}
		
		public function removePersonalData(&$ban){
			if(!$this->engine->checkAccess(1, true)){
				if(isset($ban['admin_ip'])) $ban['admin_ip'] = '*Скрыто*';
				if(isset($ban['player_ip'])) $ban['player_ip'] = '*Скрыто*';
			}
		}
		
	}