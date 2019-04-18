var isRequestProc = false;
$(document).ready(function(){
	//$('select').styler();
	$('#serversAdminAddBtn').click(function(){
		if(!isRequestProc){
			$.ajax({
				url: window.panelHome+'request/servers.php?action=add',
				dataType: 'json',
				type: 'POST',
				data: {
					active: Number($('#serversAdminAddActive').is(':checked')),
					name: $('#serversAdminAddName').val(),
					address: $('#serversAdminAddAddress').val(),
					game: $('#serversAdminAddGame').val()
				},
				success: function(res){
					$('#serversAdminAddBtn').html('Добавить');
					isRequestProc = false;
					if(res instanceof Object){
						$('#serversAdminAddBtn').html('Добавить');
						if(res.status){
							
							$('#serversAdminAddName').val('');
							$('#serversAdminAddAddress').val('');
							
							$('#serversAdminList').append('\
								<tr id="serversAdminListItem-'+res['data']['id']+'">\
									<td id="serversAdminGameName" game="'+res['data']['game']+'">'+res['data']['gameName']+'</td>\
									<td id="serversAdminName">'+res['data']['name']+'</td>\
									<td id="serversAdminFullAddress">'+res['data']['fullAddress']+'</td>\
									<td id="serversAdminActive" active="'+res['data']['active']+'"><i class="fa fa-'+(res['data']['active'] ? 'check' : 'times')+'"></i></td>\
									<td><button class="styler" id="serversAdminEditBtn" onclick="clickServerEdit('+res['data']['id']+'); return false;"><i class="fa fa-wrench"></i></button></td>\
								</tr>\
							');
							
							iziToast.success({title: res.msg});
						}
						else iziToast.error({title: res.msg});
					}
					else{
						console.log(res);
						iziToast.error({title: res});
					}
				},
				timeout: 5000,
				error: function(jqXHR, status, errorThrown){
					$('#serversAdminAddBtn').html('Добавить');
					alert('Ошибка: '+status+': '+errorThrown);
				}
			});
			
			isRequestProc = true;
			$('#serversAdminAddBtn').html('<i class="fa fa-spinner fa-spin"></i>');
		}
	});
});

var editInfo = null;

function clickServerEdit(id){
	console.log('Редактирование сервера с id = '+id);
	editInfo = {
		id: id,
		name: $('#serversAdminListItem-'+id+' > #serversAdminName').html(),
		game: $('#serversAdminListItem-'+id+' > #serversAdminGameName').attr('game'),
		address: $('#serversAdminListItem-'+id+' > #serversAdminFullAddress').html(),
		active: $('#serversAdminListItem-'+id+' > #serversAdminActive').attr('active')
	};
	
	$("#serversEditModal").iziModal({
		title: 'Редактирование сервера',
		subtitle: editInfo.name,
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',  // light
		icon: 'fa fa-wrench',
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
		navigateCaption: true,
		navigateArrows: true, // Boolean, 'closeToModal', 'closeScreenEdge'
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
			$('#serversEditModal').iziModal('startLoading');
			setTimeout(function(){$('.styler').styler();}, 1);
		},
		onOpened: function(){},
		onClosing: function(){},
		onClosed: function(){editInfo = null; $('#serversEditModal').iziModal('destroy');},
		afterRender: function(){}
	});
	$('#serversEditModal').iziModal('setContent', '\
		<table>\
			<tr>\
				<td class="width: auto;">Игра</td>\
				<td><select id="serversAdminEditGame" class="styler w100" data-search-limit="7" data-visible-options="5" data-search="true"></select></td>\
			</tr>\
			<tr>\
				<td class="width: auto;">Название</td>\
				<td><input id="serversAdminEditName" type="text" class="styler w100" placeholder="Название" value="'+editInfo.name+'" /></td>\
			</tr>\
			<tr>\
				<td class="width: auto;">Адрес IP:PORT</td>\
				<td><input id="serversAdminEditAddress" type="text" class="styler w100" placeholder="Адрес" value="'+editInfo.address+'" /></td>\
			</tr>\
			<tr>\
				<td class="width: auto;">Активен?</td>\
				<td><input id="serversAdminEditActive" type="checkbox" class="lc-switch" '+(editInfo.active == 1 ? 'checked' : '')+'/></td>\
			</tr>\
			<tr>\
				<td><button id="serversAdminEditSaveBtn" class="styler" style="font-weight: bold;"><i class="fa fa-save"></i> Сохранить</button></td>\
				<td><button id="serversAdminEditDelBtn" class="styler" style="float: right; color: red; font-weight: bold;"><i class="fa fa-trash"></i> Удалить</button></td>\
			</tr>\
		</table>\
	');
	for(var k in window.gamesList) $('#serversAdminEditGame').append('<option value="'+k+'" '+(k == editInfo.game ? 'selected' : '')+'>'+window.gamesList[k].name+'</option>');
	$('.lc-switch').lc_switch('Вкл', 'Выкл');
	$('#serversAdminEditSaveBtn').click(clickSaveServer);
	$('#serversAdminEditDelBtn').click(clickDelServer);
	$('#serversEditModal').iziModal('stopLoading');
}

function clickSaveServer(){
	if(!isRequestProc){
		$('#serversEditModal').iziModal('startLoading');
		$.ajax({
			url: window.panelHome+'request/servers.php?action=edit',
			dataType: 'json',
			type: 'POST',
			data: {
				active: Number($('#serversAdminEditActive').is(':checked')),
				name: $('#serversAdminEditName').val(),
				address: $('#serversAdminEditAddress').val(),
				game: Number($('#serversAdminEditGame').val()),
				id: editInfo.id
			},
			success: function(res){
				$('#serversEditModal').iziModal('stopLoading');
				isRequestProc = false;
				if(res instanceof Object){
					if(res.status){
						$('#serversAdminListItem-'+res['data']['id']).html('\
							<td id="serversAdminGameName" game="'+res['data']['game']+'">'+res['data']['gameName']+'</td>\
							<td id="serversAdminName">'+res['data']['name']+'</td>\
							<td id="serversAdminFullAddress">'+res['data']['fullAddress']+'</td>\
							<td id="serversAdminActive" active="'+res['data']['active']+'"><i class="fa fa-'+(res['data']['active'] ? 'check' : 'times')+'"></i></td>\
							<td><button class="styler" id="serversAdminEditBtn" onclick="clickServerEdit('+res['data']['id']+'); return false;"><i class="fa fa-wrench"></i></button></td>\
						');
						iziToast.success({title: res.msg});
					}
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: res});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#serversEditModal').iziModal('stopLoading');
				alert('Ошибка: '+status+': '+errorThrown);
			}
		});
		isRequestProc = true;
	}
}

function clickDelServer(){
	if(!isRequestProc){
		$('#serversEditModal').iziModal('startLoading');
		$.ajax({
			url: window.panelHome+'request/servers.php?action=del',
			dataType: 'json',
			type: 'POST',
			data: {id: editInfo.id},
			success: function(res){
				$('#serversEditModal').iziModal('stopLoading');
				isRequestProc = false;
				if(res instanceof Object){
					if(res.status){
						$('#serversAdminListItem-'+res['data']['id']).remove();
						iziToast.success({title: res.msg});
						$('#serversEditModal').iziModal('close');
					}
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: res});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#serversEditModal').iziModal('stopLoading');
				alert('Ошибка: '+status+': '+errorThrown);
			}
		});
		isRequestProc = true;
	}
}