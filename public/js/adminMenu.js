var isRequestProc = false;
var modalInfo = null;

$(document).ready(function(){
	/* import { Sortable } from '@shopify/draggable';
	const sortableSub = new Sortable(document.querySelectorAll('ul.adminMenu li ul'), {
		draggable: 'ul.adminMenu li ul li'
	});
	const sortableMain = new Sortable(document.querySelectorAll('ul.adminMenu'), {
		draggable: 'ul.adminMenu li'
	}); */
});

var canClick = true;
function adminMenuEdit(id){
	if(!canClick) return false;
	canClick = false; setTimeout(function(){canClick = true;}, 1);
	if(isRequestProc) return false;
	$("#menuModal").iziModal({
		title: 'Настройка пункта меню',
		subtitle: '',
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',  // light
		icon: 'fa fa-list-ul',
		iconText: null,
		iconColor: '',
		rtl: false,
		width: 600,
		top: null,
		bottom: null,
		borderBottom: true,
		padding: 20,
		radius: 5,
		zindex: 1000,
		iframe: false,
		iframeHeight: 400,
		iframeURL: null,
		focusInput: true,
		group: 'custom',
		loop: false,
		arrowKeys: true,
		navigateCaption: false,
		navigateArrows: false, // Boolean, 'closeToModal', 'closeScreenEdge'
		history: false,
		restoreDefaultContent: false,
		autoOpen: true, // Boolean, Number
		bodyOverflow: false,
		fullscreen: false,
		openFullscreen: false,
		closeOnEscape: true,
		closeButton: true,
		appendTo: 'body', // or false
		appendToOverlay: 'body', // or false
		overlay: true,
		overlayClose: true,
		overlayColor: 'rgba(0, 0, 0, .4)',
		timeout: false,
		timeoutProgressbar: false,
		pauseOnHover: false,
		timeoutProgressbarColor: 'rgba(255,255,255,0.5)',
		transitionIn: 'comingIn',
		transitionOut: 'comingOut',
		transitionInOverlay: 'fadeIn',
		transitionOutOverlay: 'fadeOut',
		onFullscreen: function(){},
		onResize: function(){},
		onOpening: function(){
			$('#menuModal').iziModal('startLoading');
			isRequestProc = true;
			$.ajax({
				url: window.panelHome+'request/adminMenu.php?action=getMenuItem',
				dataType: 'json',
				type: 'POST',
				data: {id: id},
				success: function(res){
					isRequestProc = false;
					$('#menuModal').iziModal('stopLoading');
					if(res instanceof Object){
						if(res.status){
							iziToast.success({title: res.msg});
							modalInfo = res.data;
							
							accessStr = '';
							for(var i = 0; i < window.userGroups.length; i++) accessStr += '<option value="'+window.userGroups[i].id+'">'+window.userGroups[i].name+'</option>'
							
							$('#menuModal').iziModal('setSubtitle', modalInfo.name+' (ID: '+modalInfo.id+')');
							$('#menuModal').iziModal('setContent', '\
								<table>\
									<tbody>\
										<tr>\
											<td>Позиция</td>\
											<td><input id="adminMenuPosInp" class="styler" type="number" min="1" value="'+modalInfo.pos+'"></td>\
										</tr>\
										<tr>\
											<td>Доступ</td>\
											<td>\
												<select id="adminMenuAccessInp" class="styler">\
													'+accessStr+'\
												</select>\
											</td>\
										</tr>\
										<tr>\
											<td>Название</td>\
											<td><input id="adminMenuNameInp" class="styler" type="text" value="'+modalInfo.name+'"></td>\
										</tr>\
										<tr>\
											<td>Ссылка</td>\
											<td><input id="adminMenuLinkInp" class="styler" type="text" value="'+modalInfo.link+'"></td>\
										</tr>\
										<tr>\
											<td>Подменю</td>\
											<td><input id="adminMenuSubmenuInp" class="lc-switch" type="checkbox" '+(modalInfo.submenu instanceof Object ? 'checked' : '')+'></td>\
										</tr>\
										<tr>\
											<td>ID Родителя</td>\
											<td><input id="adminMenuParentInp" class="styler" type="number" min="0" value="'+modalInfo.parent+'" '+(modalInfo.submenu instanceof Object ? 'disabled' : '')+'></td>\
										</tr>\
										<tr>\
											<td>Актив</td>\
											<td><input id="adminMenuActiveInp" class="lc-switch" type="checkbox" '+(modalInfo.active > 0 ? 'checked' : '')+'></td>\
										</tr>\
									</tbody>\
								</table>\
								<br>\
								<button class="styler" id="modalMenuItemSaveBtn" style="float: left; font-weight: bold;"><i class="fa fa-save"></i> Сохранить</button>\
								<button class="styler" id="modalMenuItemDelBtn" style="float: right; color: red; font-weight: bold;"><i class="fa fa-trash"></i> Удалить</button>\
							');
							setTimeout(function(){$('.styler').styler();}, 1);
							$('.lc-switch').lc_switch('Вкл', 'Выкл');
							
							$('#adminMenuAccessInp :selected').attr('selected', null);
							$('#adminMenuAccessInp [value="'+modalInfo.group+'"]').attr('selected', '');
							
							$('#modalMenuItemSaveBtn').click(function(){
								if(!isRequestProc){
									$('#menuModal').iziModal('startLoading');
									isRequestProc = true;
									$.ajax({
										url: window.panelHome+'request/adminMenu.php?action=editMenuItem',
										dataType: 'json',
										type: 'POST',
										data: {
											id: modalInfo.id,
											pos: $('#adminMenuPosInp').val(),
											access: $('#adminMenuAccessInp').val(),
											name: $('#adminMenuNameInp').val(),
											link: $('#adminMenuLinkInp').val(),
											submenu: Number($('#adminMenuSubmenuInp').is(':checked')),
											parent: $('#adminMenuParentInp').val(),
											active: Number($('#adminMenuActiveInp').is(':checked')),
										},
										success: function(res){
											isRequestProc = false;
											$('#menuModal').iziModal('stopLoading');
											if(res instanceof Object){
												if(res.status){
													reloadMenuItemsList();
													iziToast.success({title: res.msg});
												}
												else iziToast.error({title: res.msg});
											}
											else{
												$('#menuModal').iziModal('stopLoading');
												console.log(res);
												iziToast.error({title: 'Возникла непредвиденная ошибка'});
											}
										},
										timeout: 5000,
										error: function(jqXHR, status, errorThrown){
											$('#menuModal').iziModal('stopLoading');
											isRequestProc = false;
											alert('Ошибка! '+status+': '+errorThrown);
										}
									});
								}
							});
							
							$('#modalMenuItemDelBtn').click(function(){
								if(!isRequestProc){
									$('#menuModal').iziModal('startLoading');
									isRequestProc = true;
									$.ajax({
										url: window.panelHome+'request/adminMenu.php?action=deleteMenuItem',
										dataType: 'json',
										type: 'POST',
										data: {id: modalInfo.id},
										success: function(res){
											isRequestProc = false;
											$('#menuModal').iziModal('stopLoading');
											if(res instanceof Object){
												if(res.status){
													reloadMenuItemsList();
													iziToast.success({title: res.msg});
													$('#menuModal').iziModal('close');
												}
												else iziToast.error({title: res.msg});
											}
											else{
												$('#menuModal').iziModal('stopLoading');
												console.log(res);
												iziToast.error({title: 'Возникла непредвиденная ошибка'});
											}
										},
										timeout: 5000,
										error: function(jqXHR, status, errorThrown){
											$('#menuModal').iziModal('stopLoading');
											isRequestProc = false;
											alert('Ошибка! '+status+': '+errorThrown);
										}
									});
								}
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
				timeout: 5000,
				error: function(jqXHR, status, errorThrown){
					$('#menuModal').iziModal('close');
					isRequestProc = false;
					alert('Ошибка! '+status+': '+errorThrown);
				}
			});
		},
		onOpened: function(){},
		onClosing: function(){},
		onClosed: function(){modalInfo = null; $('#menuModal').iziModal('destroy');},
		afterRender: function(){}
	});
	return true;
}

function clickAddMenuItem(){
	if(!isRequestProc){
		$('.adminMenu').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminMenu.php?action=addMenuItem',
			dataType: 'json',
			type: 'POST',
			success: function(res){
				isRequestProc = false;
				$('.adminMenu').waitMe('hide');
				if(res instanceof Object){
					if(res.status){
						reloadMenuItemsList();
						iziToast.success({title: res.msg});
						adminMenuEdit(res.data.id);
					}
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('.adminMenu').waitMe('hide');
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}

function reloadMenuItemsList(){
	if(!isRequestProc){
		$('.adminMenu').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminMenu.php?action=getMenuItems',
			dataType: 'json',
			type: 'POST',
			success: function(res){
				isRequestProc = false;
				$('.adminMenu').waitMe('hide');
				if(res instanceof Array) menuItemsTpl(res);
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('.adminMenu').waitMe('hide');
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
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
