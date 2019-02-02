<?php
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."users` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`login` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
			`pass` varchar(33) COLLATE utf8_unicode_ci NOT NULL,
			`custom` text COLLATE utf8_unicode_ci,
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
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."settings` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`module` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			`data` text COLLATE utf8_unicode_ci,
			PRIMARY KEY (`id`),
			UNIQUE KEY `module` (`module`),
			KEY `id` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
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
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."servers` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`game` int(11) NOT NULL DEFAULT '1',
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unnamed',
			`ip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
			`port` smallint(6) NOT NULL,
			`data` text COLLATE utf8_unicode_ci,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			UNIQUE KEY `ip` (`ip`,`port`),
			KEY `id` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
	");
	
	$sql->query("
		CREATE TABLE IF NOT EXISTS `".$_POST['prefix']."groups` (
			`id` int(11) NOT NULL,
			`name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			PRIMARY KEY (`id`),
			KEY `id` (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	");
	