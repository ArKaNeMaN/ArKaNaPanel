reloadModules();

$(document).ready(() => {
	$('#modulesModal').iziModal({
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',
		width: 800,
		icon: 'fa fa-download',
		padding: 10,
		radius: 5,
		title: 'Найденые модули',
	});
});

function moduleAdminSendRequest(action, id){
	$.ap.sendRequest(
		'modules', action,
		{module: id},
		(res) => {
			$('#modulesAdminItem-'+id).waitMe('hide');
			if(res instanceof Object){
				if(res.status){
					reloadModules();
					iziToast.success({title: res.msg});
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {$('#modulesAdminItem-'+id).waitMe('hide');},
		true
	);
}

function turnModuleStatus(index, turn){
	$.ap.sendRequest(
		'modules', 'turn',
		{
			module: index,
			turn: turn,
		},
		(res) => {
			if(res instanceof Object){
				if(res.status){
					reloadModules();
					iziToast.success({title: res.msg});
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {},
		true
	);
}

function showModulesFromFiles(){
	$.ap.sendRequest(
		'modules', 'getFromFiles',
		{},
		(res) => {
			console.log(res);
			if(!(res instanceof Array)){
				iziToast.info({title: 'Модули не найдены'});
				return;
			}
			str = '';
			for(var i = 0; i < res.length; i++) str += '\
				<tr>\
					<td>'+res[i].info.name+'</td>\
					<td>'+res[i].info.version+'</td>\
					<td>'+res[i].info.author+'</td>\
					<td><button class="styler" title="Добавить" onclick="moduleAdminSendRequest(\'register\', \''+res[i].index+'\');"><i class="fa fa-plus"></i></button></td>\
				</tr>\
			';
			$('#modulesModal').iziModal('setContent', '\
				<table>\
					'+str+'\
				</table>\
			');
			$('#modulesModal').iziModal('open');
		},
		() => {},
		true
	);
}

function reloadModules(){
	var req = $.ap.sendRequest(
		'modules', 'getAll',
		{},
		(res) => {
			str = '';
			if(!(res instanceof Array)) str += '<tr><td colspan="5"><div align=center>Модули не найдены</div></td></tr>';
			else{
				for(var i = 0; i < res.length; i++){
					str += '\
						<tr id="modulesAdminItem-'+res[i].index+'">\
							<td><i class="fa fa-power-off fa-2x" style="color: '+(res[i].status == 1 ? 'green' : 'red')+'"></i></td>\
							<td>'+res[i].name+'</td>\
							<td>'+res[i].version+'</td>\
							<td>'+res[i].author+'</td>\
							<td>\
								<button class="styler" data-dropdown="#modules_Menu-'+res[i].index+'"> <i class="fa fa-angle-down"></i>\
									<ul class="dropdown-menu dropdown-anchor-top-left dropdown-has-anchor dropdownActionMenu" id="modules_Menu-'+res[i].index+'">\
										'+(res[i].status == 1 ? '\
											<li onclick="turnModuleStatus(\''+res[i].index+'\', 0);">\
												<i class="fa fa-power-off"></i> Выключить\
											</li>\
										' : (res[i].status == -1 ? '\
											<li onclick="moduleAdminSendRequest(\'install\', \''+res[i].index+'\');">\
												<i class="fa fa-download"></i> Установить\
											</li>\
										' : '\
											<li onclick="turnModuleStatus(\''+res[i].index+'\', 1);">\
												<i class="fa fa-power-off"></i> Включить\
											</li>\
										'))+'\
										'+(res[i].hasSettings ? '\
											<li onclick="$.ap.redirect(\''+res[i].settingsPage+'\');">\
												<i class="fa fa-cog"></i> Настройки\
											</li>\
										' : '')+'\
										<li onclick="moduleAdminSendRequest(\'delete\', \''+res[i].index+'\');">\
											<i class="fa fa-trash"></i> Удалить\
										</li>\
									</ul>\
								</button>\
							</td>\
						</tr>\
					';
				}
			}
			$('#modulesList').html(str);
			$('#modulesList').waitMe('hide');
		},
		() => {
			$('#modulesList').waitMe('hide');
		}
	);
	if(req){
		$('#modulesList').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
	}
}