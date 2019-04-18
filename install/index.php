<!DOCTYPE html>
<?php
	ini_set('display_errors', 0);
	error_reporting(E_ALL);
	
	if(file_exists('../configs/sql.php')){
		require('../configs/main.php');
		header('Location: '.PANEL_HOME);
		exit;
	}
?>
<html>
	<head>
		<title>ArKaNaPanel | Установка</title>
		
		<!--Мета тэги-->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta content="Установка ArKaNaPanel" name="description" />
		<meta name="yandex-tableau-widget" content="logo=../public/img/logo.png, color=#1e1e1e" />
		<meta name="theme-color" content="#1e1e1e">
		<meta name="apple-mobile-web-app-status-bar-style" content="#1e1e1e">
		<meta name="viewport" content="ya-title=#1e1e1e,ya-dock=#1e1e1e">
		<link rel="shortcut icon" type="image/x-icon" href="../public/img/favicon.ico">
		<!--Мета тэги-->
		
		<!--Шрифты-->
		<link href="https://fonts.googleapis.com/css?family=Montserrat&amp;subset=cyrillic,cyrillic-ext,latin-ext,vietnamese" rel="stylesheet">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
		<!--Шрифты-->
		
		<!--Стили-->
		<link href="../public/temps/default/css/style.css" rel="stylesheet">
		<link href="../public/temps/default/css/blocks.css" rel="stylesheet">
		<!--Стили-->
		
		<!--jQuery-->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="../public/plugins/formStyler/jquery.formstyler.min.js"></script>
		<link href="../public/plugins/formStyler/jquery.formstyler.css" rel="stylesheet">
		<link href="../public/plugins/formStyler/jquery.formstyler.theme.css" rel="stylesheet">
		<script src="../public/plugins/iziToast/iziToast.min.js"></script>
		<link href="../public/plugins/iziToast/iziToast.min.css" rel="stylesheet">
		<!--jQuery-->
		
		<!--JS-->
		<script>
			var isRequestProc = false;
			$(document).ready(function(){
				$('#installSendBtn').click(function(){
					if(!isRequestProc){
						$.ajax({
							url: 'request.php?action=install',
							dataType: 'json',
							type: 'POST',
							data: {
								panelDir: $('#installPanelDir').val(),
								host: $('#installDbHost').val(),
								user: $('#installDbUser').val(),
								pass: $('#installDbPass').val(),
								name: $('#installDbName').val(),
								prefix: $('#installDbPrefix').val(),
								encode: $('#installDbEncode').val(),
								adminLogin: $('#installAdminLogin').val(),
								adminName: $('#installAdminName').val(),
								adminPass: $('#installAdminPass').val(),
								adminPassa: $('#installAdminPassa').val()
							},
							success: function(res){
								$('#installSendBtn').html('Установить');
								isRequestProc = false;
								if(res instanceof Object){
									$('#installSendBtn').html('Установить');
									if(res.status){
										iziToast.success({title: res.msg});
										$('.pageContent').html(res.data);
									}
									else iziToast.error({title: res.msg});
								}
								else{
									console.log(res);
									iziToast.error({title: res});
								}
							},
							error: function(jqXHR, status, errorThrown){
								$('#installSendBtn').html('Установить');
								alert('Ошибка: '+status+': '+errorThrown);
							}
						});
						
						isRequestProc = true;
						$('#installSendBtn').html('<i class="fa fa-spinner fa-spin"></i>');
					}
				});
				$('#installDbCkeckBtn').click(function(){
					if(!isRequestProc){
						$.ajax({
							url: 'request.php?action=check',
							dataType: 'json',
							type: 'POST',
							data: {
								host: $('#installDbHost').val(),
								user: $('#installDbUser').val(),
								pass: $('#installDbPass').val(),
								name: $('#installDbName').val()
							},
							success: function(res){
								$('#installDbCkeckBtn').html('Проверить подключение');
								isRequestProc = false;
								if(res instanceof Object){
									$('#installDbCkeckBtn').html('Проверить подключение');
									if(res.status) iziToast.success({title: res.msg});
									else iziToast.error({title: res.msg});
								}
								else{
									console.log(res);
									iziToast.error({title: res});
								}
							},
							timeout: 10000,
							error: function(jqXHR, status, errorThrown){
								$('#installDbCkeckBtn').html('Проверить подключение');
								alert('Ошибка: '+status+': '+errorThrown);
							}
						});
						
						isRequestProc = true;
						$('#installDbCkeckBtn').html('<i class="fa fa-spinner fa-spin"></i>');
					}
				});
			});
		</script>
		<!--JS-->
		
	</head>
	
	<body style="background-image: url(../public/temps/default/img/background.jpg);">
		<div class="mainCont">
			<div class="header">
				<div class="panelHead">
					<div class="logo">
						<a href="../"><img src="../public/img/logo.png" /></a>
					</div>
				</div>
			</div>
			
			<div class="bodyContent">
				<div class="pageContent">
					<div style="width: 100%;">
						<h2>Установка панели</h2>
						<div style="margin-left: 15px;">
							<table>
								<tr>
									<td colspan="2" style="text-align: center">Корневая папка панели</td>
								</tr>
								<tr>
									<td>Корневая папка панели</td>
									<td><input type="text" class="styler" placeholder="Корень панели" id="installPanelDir" /></td>
								</tr>
								<tr>
									<td colspan="2" style="text-align: center">Подключение к базе данных</td>
								</tr>
								<tr>
									<td>Хост</td>
									<td><input type="text" class="styler" placeholder="Хост БД" id="installDbHost" /></td>
								</tr>
								<tr>
									<td>Юзер</td>
									<td><input type="text" class="styler" placeholder="Юзер БД" id="installDbUser" /></td>
								</tr>
								<tr>
									<td>Пароль</td>
									<td><input type="password" class="styler" placeholder="Пароль БД" id="installDbPass" /></td>
								</tr>
								<tr>
									<td>Название</td>
									<td><input type="text" class="styler" placeholder="Название БД" id="installDbName" /></td>
								</tr>
								<tr>
									<td>Префикс</td>
									<td><input type="text" class="styler" placeholder="Префикс таблиц" id="installDbPrefix" value="ap_" /></td>
								</tr>
								<tr>
									<td>Кодировка</td>
									<td><input type="text" class="styler" placeholder="Кодировка" id="installDbEncode" value="utf8" /></td>
								</tr>
								<tr>
									<td></td>
									<td><button style="float: right;" class="styler" id="installDbCkeckBtn">Проверить подключение</button></td>
								</tr>
								<tr>
									<td colspan="2" style="text-align: center">Регистрация аккаунта админа</td>
								</tr>
								<tr>
									<td>Логин</td>
									<td><input type="text" class="styler" placeholder="Логин" id="installAdminLogin" /></td>
								</tr>
								<tr>
									<td>Ник</td>
									<td><input type="text" class="styler" placeholder="Ник" id="installAdminName" /></td>
								</tr>
								<tr>
									<td>Пароль</td>
									<td><input type="password" class="styler" placeholder="Пароль" id="installAdminPass" /></td>
								</tr>
								<tr>
									<td>Подтверждение пароля</td>
									<td><input type="password" class="styler" placeholder="Подтверждение пароля" id="installAdminPassa" /></td>
								</tr>
								<tr>
									<td><button class="styler" id="installSendBtn">Установить</button></td>
									<td></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="rightCol">
					<div class="blockCont">
						<div class="blockHead">Спасибо</div>
						<div class="blockBody" style="font-size: 15px;">
							Спасибо, что выбрали ArKaNaPanel<br><br>
							Автор панели:<br>ArKaNeMaN<br><br>
							Дата начала разработки:<br>Сентябрь 2018 года<br><br>
							Официальный сайт:<br><a target="_BLANK" href="http://arkaneman.ru">http://arkaneman.ru</a>
							<?php if($curVer = file_get_contents('../apVer.txt')):?>
								<br><br>Текущая версия:<br><?php echo $curVer;?>
							<?php endif;?>
							<?php if($lastVer = file_get_contents('http://arkaneman.ru/other/apVer.php')):?>
								<br><br>Последняя версия:<br><?php echo $lastVer;?>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script>
		$('.lc-switch').lc_switch('Вкл', 'Выкл');
		$('.styler').styler();
	</script>
</html>