<?php
	define('PANEL_THEME', 'default');
	define('PANEL_DIR_', '');
	
	
	define('PANEL_DIR', PANEL_DIR_ ? PANEL_DIR_.'/' : '');
	define('PANEL_HOME', 'http://'.$_SERVER['SERVER_NAME'].'/'.PANEL_DIR);