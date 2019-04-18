var isRequestProc = false;
var serversData = [];

$(document).ready(function(){
	reloadServers();
	$('#monitorModal').iziModal({
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',
		width: 800,
		icon: 'fa fa-server',
		padding: 10,
		radius: 5,
	});
});

function reloadServers(){
	if(!isRequestProc){
		$('#monitorServersList').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/monitoring.php?action=getServers',
			dataType: 'json',
			type: 'POST',
			success: function(res){
				isRequestProc = false;
				$('#monitorServersList').waitMe('hide');
				console.log(res);
				if(res instanceof Array){
					var str = '';
					var totalOnline = {slots: 0, online: 0};
					for(var i = 0; i < res.length; i++){
						str += '\
							<tr class="monitorServerItem" id="monitorServer-'+res[i].info.id+'" data-servId="'+res[i].info.id+'" onclick="monitorShowMoreInfo('+i+')">\
								<td>'+res[i].info.gameData.name+'</td>\
								<td>'+res[i].info.name+'</td>\
								<td><a title="Подключиться к серверу" class="monitorConnect" href="steam://connect/'+res[i].info.fullAddress+'">'+res[i].info.fullAddress+' <i class="fas fa-link"></i></a></td>\
								<td>'+res[i].server.Map+'</td>\
								<td><progress class="monitorProgBar" title="'+res[i].server.Players+'/'+res[i].server.MaxPlayers+'" value="'+res[i].server.Players+'" max="'+res[i].server.MaxPlayers+'">\
									<style>#monitorServer-'+res[i].info.id+' progress.monitorProgBar:before{content: \''+res[i].server.Players+'/'+res[i].server.MaxPlayers+'\'}</style>\
								</progress></td>\
							</tr>\
						';
						totalOnline.slots += res[i].server.MaxPlayers;
						totalOnline.online += res[i].server.Players;
					}
					updateTotalOnlineBar(totalOnline);
					$('#monitorServersList').html(str);
					serversData = res;
				}
				else iziToast.error({title: 'Ошибка загрузки информации о серверах'});
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#monitorServersList').waitMe('hide');
				iziToast.error({title: 'Ошибка загрузки информации о серверах'});
				isRequestProc = false;
			}
		});
	}
}

function updateTotalOnlineBar(data){
	$('#monitorTotalOnlineBar').attr('value', data.online);
	$('#monitorTotalOnlineBar').attr('max', data.slots);
	$('#monitorTotalOnlineBar').attr('title', data.online+'/'+data.slots);
	$('#monitorTotalOnlineBar').html('<style>#monitorTotalOnlineBar:before{content: \''+data.online+'/'+data.slots+'\'}</style>');
}

function monitorShowMoreInfo(id){
	var data = serversData[id];
	$('#monitorModal').iziModal('setTitle', data.info.name);
	$('#monitorModal').iziModal('setSubtitle', data.info.fullAddress);
	var plList = '';
	for(var i = 0; i < data.players.length; i++){
		plList += '\
			<tr>\
				<td>'+data.players[i].Id+'</td>\
				<td>'+data.players[i].Name+'</td>\
				<td>'+data.players[i].Frags+'</td>\
				<td>'+data.players[i].TimeF+'</td>\
			</tr>\
		';
	}
	var mapImg = 'https://image.gametracker.com/images/maps/160x120/'+data.info.gameData.shortName+'/'+data.server.Map.toLowerCase()+'.jpg';
	imageExists(mapImg, (isImgExists) => {
		$('#monitorModal').iziModal('setContent', '\
			<div class="monitorModalServerInfo">\
				<img src="'+(isImgExists ? mapImg : 'https://image.gametracker.com/images/maps/160x120/nomap.jpg')+'" />\
				<ul class="monitorModalInfoList">\
					<li><b>Название:</b> '+data.server.HostName+'</li>\
					<li><b>Текущая карта:</b> '+data.server.Map+'</li>\
					<li><b>Игроков онлайн:</b> '+data.server.Players+'/'+data.server.MaxPlayers+'</li>\
					<li><b>VAC защита:</b> <i class="fas fa-shield-alt" style="color: '+(data.server.Secure ? 'green' : 'red')+'; font-size: 120%;"></i></li>\
				</ul>\
				<button style="float: right; font-weight: bold; color: green;" onclick="location.href=\'steam://connect/'+data.info.fullAddress+'\';" class="styler"><i class="fas fa-link"></i> Подключиться</button>\
			</div>\
			<div class="monitorModalPlayersList">\
				<h3 align=center>Список игроков</h3>\
				<table>\
					<thead>\
						<th>ID</th>\
						<th>Ник</th>\
						<th>Фраги</th>\
						<th>Время онлайн</th>\
					</thead>\
					<tbody class="monitorModulePlayersList">\
						'+plList+'\
					</tbody>\
				</table>\
			</div>\
		');
		$('#monitorModal').iziModal('open');
	});
	
}

//https://stackoverflow.com/questions/14651348/checking-if-image-does-exists-using-javascript
function imageExists(url, callback) {
	var img = new Image();
	img.onload = function() {callback(true);};
	img.onerror = function() {callback(false);};
	img.src = url;
}