<?php
	$tpl = [
		[
			'name' => 'Основные настройки',
			'items' => [
				[
					'id' => 'siteName',
					'name' => 'Название проекта',
					'type' => 'text',
					'placeholder' => 'Введите название проекта',
					'default' => 'ArKaNaPanel',
				],
				[
					'id' => 'homePage',
					'name' => 'Домашняя страница',
					'type' => 'text',
					'placeholder' => 'Укажите начальную страницу',
					'default' => 'home',
				],
				[
					'id' => 'panelTheme',
					'name' => 'Шаблон',
					'type' => 'text',
					'placeholder' => 'Укажите шаблон',
					'default' => 'dafeult',
				],
				[
					'id' => 'zipAvatars',
					'name' => 'Сжимать аватарки?',
					'type' => 'checkbox',
					'placeholder' => '',
					'default' => false,
				],
				[
					'id' => 'logo',
					'name' => 'Логотип',
					'type' => 'file',
					'uploadFolder' => 'panel',
					'fileName' => 'logo.png',
					'fileType' => 'image',
					'fileExp' => 'png',
				],
			],
		],
		[
			'name' => 'Метрики',
			'items' => [
				[
					'id' => 'yaMetrika',
					'name' => 'Номер счётчика Яндекс.Метрики',
					'type' => 'number',
					'placeholder' => 'Отсавьте пустым, чтобы отключить',
					'default' => '0',
				],
				[
					'id' => 'googleAnalytics',
					'name' => 'ID отслеживания Google Аналитики',
					'type' => 'text',
					'placeholder' => 'Отсавьте пустым, чтобы отключить',
					'default' => '',
				],
			],
		],
		[
			'name' => 'Капча (ReCAPTCHA v2 Invisible)',
			'items' => [
				[
					'id' => 'captchaPubKey',
					'name' => 'Публичный ключ',
					'type' => 'text',
					'placeholder' => 'Отсавьте пустым, чтобы отключить Капчу',
					'default' => '',
				],
				[
					'id' => 'captchaSecKey',
					'name' => 'Секретный ключ',
					'type' => 'text',
					'placeholder' => 'Отсавьте пустым, чтобы отключить Капчу',
					'default' => '',
				],
			],
		],
	];
?>