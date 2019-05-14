<?php
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."users` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`login` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
			`pass` varchar(33) COLLATE utf8_unicode_ci NOT NULL,
			`data` text COLLATE utf8_unicode_ci,
			`regIp` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			`lastIp` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			`userHash` varchar(33) COLLATE utf8_unicode_ci DEFAULT NULL,
			`group` int(11) NOT NULL DEFAULT '100',
			`regTime` int(11) NOT NULL,
			`avatar` varchar(128) COLLATE utf8_unicode_ci DEFAULT 'public/img/defaultAvatar.jpg',
			`money` int(11) NOT NULL DEFAULT '0',
			`allMoney` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `login` (`login`),
			UNIQUE KEY `name` (`name`),
			UNIQUE KEY `email` (`email`),
			UNIQUE KEY `userHash` (`userHash`),
			KEY `id` (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."settings` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`module` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`data` text COLLATE utf8_unicode_ci,
			PRIMARY KEY (`id`),
			UNIQUE KEY `module` (`module`),
			KEY `id` (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."menu` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`pos` int(11) NOT NULL DEFAULT '0',
			`name` varchar(128) COLLATE utf8_unicode_ci DEFAULT 'Unnamed',
			`link` varchar(256) COLLATE utf8_unicode_ci DEFAULT '',
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`submenu` tinyint(1) NOT NULL DEFAULT '0',
			`type` tinyint(1) NOT NULL DEFAULT '0',
			`group` int(11) NOT NULL DEFAULT '0',
			`parent` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `id` (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."servers` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`game` int(11) NOT NULL DEFAULT '1',
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unnamed',
			`ip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
			`port` smallint(6) NOT NULL,
			`data` text COLLATE utf8_unicode_ci,
			`settings` text COLLATE utf8_unicode_ci,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			UNIQUE KEY `ip` (`ip`,`port`),
			KEY `id` (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."groups` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`accessLevel` int(11) NOT NULL DEFAULT '100',
			`data` text COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`),
			KEY `id` (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."blocks` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`index` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			`type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'file',
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unnamed',
			`module` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'core',
			`content` text COLLATE utf8_unicode_ci NULL,
			`dataList` text COLLATE utf8_unicode_ci NULL,
			`places` text COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `index` (`index`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."blocksShow` (
			`id` int(11) NOT NULL,
			`block` int(11) NOT NULL,
			`pos` int(11) NOT NULL DEFAULT 1,
			`place` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`data` text COLLATE utf8_unicode_ci DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `id` (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."modules` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`index` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unnamed',
			`version` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Undefined',
			`verInt` int(11) NOT NULL DEFAULT '0',
			`author` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Undefined',
			`data` text COLLATE utf8_unicode_ci,
			`status` tinyint(1) NOT NULL DEFAULT '0',
			`files` text COLLATE utf8_unicode_ci,
			`reqs` text COLLATE utf8_unicode_ci,
			`settingsTpl` text COLLATE utf8_unicode_ci,
			`settingsPage` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `index` (`index`),
			KEY `id` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."logs` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`userid` int(11) NOT NULL DEFAULT '0',
			`module` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'core',
			`type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			`text` text COLLATE utf8_unicode_ci NOT NULL,
			`data` text COLLATE utf8_unicode_ci,
			PRIMARY KEY (`id`),
			KEY `id` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	