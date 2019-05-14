var isRequestProc = false;
var modalInfo = null;

var canClick = true;
function adminMenuEdit(id){
	if(!canClick) return false;
	canClick = false; setTimeout(function(){canClick = true;}, 1);
	$('#menuModal').iziModal({
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',
		width: 650,
		icon: 'fa fa-list-ul',
		padding: 20,
		radius: 5,
		title: 'Настройка пункта меню',
		autoOpen: true,
		onOpening: function(){
			$('#menuModal').iziModal('startLoading');
			$.ap.sendRequest(
				'adminMenu', 'getMenuItem',
				{id: id},
				(res) => {
					$('#menuModal').iziModal('stopLoading');
					if(res instanceof Object){
						if(res.status){
							modalInfo = res.data;
							
							accessStr = '<option value="0">Все</option>';
							for(var i = 0; i < window.userGroups.length; i++) accessStr += '<option value="'+window.userGroups[i].id+'">'+window.userGroups[i].name+'</option>'
							
							$('#menuModal').iziModal('setSubtitle', modalInfo.name+' (ID: '+modalInfo.id+')');
							$('#menuModal').iziModal('setContent', '\
								<table>\
									<tbody>\
										<tr>\
											<td>Позиция</td>\
											<td><input id="adminMenuPosInp" class="styler w100" type="number" min="1" value="'+modalInfo.pos+'"></td>\
										</tr>\
										<tr>\
											<td>Доступ</td>\
											<td>\
												<select id="adminMenuAccessInp" class="styler w100">\
													'+accessStr+'\
												</select>\
											</td>\
										</tr>\
										<tr>\
											<td>Название</td>\
											<td><input id="adminMenuNameInp" class="styler w100" type="text" value="'+modalInfo.name+'"></td>\
										</tr>\
										<tr>\
											<td>Ссылка</td>\
											<td><input id="adminMenuLinkInp" class="styler w100" type="text" value="'+modalInfo.link+'"></td>\
										</tr>\
										<tr>\
											<td>Подменю</td>\
											<td><input id="adminMenuSubmenuInp" class="lc-switch" type="checkbox" '+(modalInfo.submenu instanceof Object ? 'checked' : '')+'></td>\
										</tr>\
										<tr>\
											<td>ID Родителя</td>\
											<td><input id="adminMenuParentInp" class="styler w100" type="number" min="0" value="'+modalInfo.parent+'" '+(modalInfo.submenu instanceof Object ? 'disabled' : '')+'></td>\
										</tr>\
										<tr>\
											<td>Актив</td>\
											<td><input id="adminMenuActiveInp" class="lc-switch" type="checkbox" '+(modalInfo.active > 0 ? 'checked' : '')+'></td>\
										</tr>\
									</tbody>\
								</table>\
								<br>\
								<button class="styler" id="modalMenuItemSaveBtn" style="float: left; font-weight: bold;"><i class="fa fa-save"></i> Сохранить</button>\
								<button class="styler" onclick="deleteMenuItem('+modalInfo.id+');" id="modalMenuItemDelBtn" style="float: right; color: red; font-weight: bold;"><i class="fa fa-trash"></i> Удалить</button>\
							');
							setTimeout(function(){$('.styler').styler();}, 1);
							$('.lc-switch').lc_switch('Вкл', 'Выкл');
							
							$('#adminMenuAccessInp :selected').attr('selected', null);
							$('#adminMenuAccessInp [value="'+modalInfo.group+'"]').attr('selected', '');
							
							$('#modalMenuItemSaveBtn').click(function(){
								var req = $.ap.sendRequest(
									'adminMenu', 'editMenuItem',
									{
										id: modalInfo.id,
										pos: $('#adminMenuPosInp').val(),
										access: $('#adminMenuAccessInp').val(),
										name: $('#adminMenuNameInp').val(),
										link: $('#adminMenuLinkInp').val(),
										submenu: Number($('#adminMenuSubmenuInp').is(':checked')),
										parent: $('#adminMenuParentInp').val(),
										active: Number($('#adminMenuActiveInp').is(':checked')),
									},
									(res) => {
										$('#menuModal').iziModal('stopLoading');
										if(res instanceof Object){
											if(res.status){
												reloadMenuItemsList();
												iziToast.success({title: res.msg});
											}
											else iziToast.error({title: res.msg});
										}
										else iziToast.error({title: 'Возникла непредвиденная ошибка'});
									},
									() => {$('#menuModal').iziModal('stopLoading');},
									true
								);
								if(req) $('#menuModal').iziModal('startLoading');
							});
						}
						else{
							$('#menuModal').iziModal('close');
							iziToast.error({title: res.msg});
						}
					}
					else{
						$('#menuModal').iziModal('close');
						console.log(res);
						iziToast.error({title: 'Возникла непредвиденная ошибка'});
					}
				},
				() => {$('#menuModal').iziModal('close');},
				true
			);
		},
		onClosed: function(){modalInfo = null; $('#menuModal').iziModal('destroy');},
	});
	return true;
}

function deleteMenuItem(id){
	var req = $.ap.sendRequest(
		'adminMenu', 'deleteMenuItem',
		{id: id},
		(res) => {
			$('#menuModal').iziModal('stopLoading');
			if(res instanceof Object){
				if(res.status){
					reloadMenuItemsList();
					iziToast.success({title: res.msg});
					$('#menuModal').iziModal('close');
				}
				else iziToast.error({title: res.msg});
			}
			else iziToast.error({title: 'Возникла непредвиденная ошибка'});
		},
		() => {$('#menuModal').iziModal('stopLoading');},
		true
	);
	if(req) $('#menuModal').iziModal('startLoading');
}

function clickAddMenuItem(){
	var req = $.ap.sendRequest(
		'adminMenu', 'addMenuItem', {},
		(res) => {
			$('.adminMenu').waitMe('hide');
			if(res instanceof Object){
				if(res.status){
					reloadMenuItemsList();
					iziToast.success({title: res.msg});
					setTimeout(function(){adminMenuEdit(res.data.id);}, 500);
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {$('.adminMenu').waitMe('hide');},
		true
	);
	if(req) $('.adminMenu').waitMe({
		effect: 'stretch',
		bg: 'rgba(30, 30, 30, .5)',
		color: 'rgba(100, 100, 100, .8)'
	});
}

function reloadMenuItemsList(){
	var req = $.ap.sendRequest(
		'adminMenu', 'getMenuItems', {},
		(res) => {
			$('.adminMenu').waitMe('hide');
				if(res instanceof Array) menuItemsTpl(res);
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
		},
		() => {$('.adminMenu').waitMe('hide');},
		true
	);
	if(req) $('.adminMenu').waitMe({
		effect: 'stretch',
		bg: 'rgba(30, 30, 30, .5)',
		color: 'rgba(100, 100, 100, .8)'
	});
}

function menuItemsTpl(data){
	str = '';
	for(var i = 0; i < data.length; i++){
		str += '\
			<li onclick="adminMenuEdit('+data[i].id+'); return false;">\
				<span style="margin-right: 10px;">'+data[i].id+'</span>\
				<span>'+(data[i].active == true ? '<i title="Активен" class="fa fa-power-off menuActive"></i>' : '<i title="Неактивен" class="fa fa-power-off menuInactive"></i>')+'</span>\
				<span style="margin-right: 5px; font-weight: bold;">'+data[i].name+'</span>\
				<span>'+(empty(data[i].link) ? '' : '('+data[i].link+')')+'</span>\
		';
		if(data[i].submenu instanceof Array){
			str += '<ul>';
			for(var k = 0; k < data[i].submenu.length; k++){
				str += '\
					<li onclick="adminMenuEdit('+data[i].submenu[k].id+'); return false;">\
						<i class="fa fa-caret-right" style="margin-right: 10px;"></i>\
						<span style="margin-right: 10px;">'+data[i].submenu[k].id+'</span>\
						<span>'+(data[i].submenu[k].active == true ? '<i title="Активен" class="fa fa-power-off menuActive"></i>' : '<i title="Неактивен" class="fa fa-power-off menuInactive"></i>')+'</span>\
						<span style="margin-right: 5px; font-weight: bold;">'+data[i].submenu[k].name+'</span>\
						<span>'+(empty(data[i].submenu[k].link) ? '' : '('+data[i].submenu[k].link+')')+'</span>\
					</li>\
				';
			}
			str += '</ul>';
		}
		str += '</li>';
	}
	$('.adminMenu').html(str);
}

function empty(mixed_var){
	return (mixed_var === "" || mixed_var === 0 || mixed_var === "0" || mixed_var === null || mixed_var === false || (mixed_var instanceof Array && mixed_var.length === 0));
}
