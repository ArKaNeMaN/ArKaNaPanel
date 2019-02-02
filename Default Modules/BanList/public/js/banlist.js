var isRequestProc = false;

function bans_getMoreInfo(id){
	if(!isRequestProc){
		$("#blModule").iziModal({
			title: 'Информация о бане',
			subtitle: '',
			headerColor: 'rgb(20, 20, 20)',
			background: 'rgb(30, 30, 30)',
			theme: 'dark',  // light
			icon: 'fa fa-info',
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
				$('#blModule').iziModal('startLoading');
				setTimeout(function(){$('.styler').styler();}, 1);
			},
			onOpened: function(){},
			onClosing: function(){},
			onClosed: function(){editInfo = null; $('#blModule').iziModal('destroy');},
			afterRender: function(){}
		});
		
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/banlist.php?action=getMoreInfo',
			dataType: 'json',
			type: 'POST',
			data: {bid: id},
			success: function(res){
				isRequestProc = false;
				$('#blModule').iziModal('stopLoading');
				if(res instanceof Object){
					if(res.status){
						$('#blModule').iziModal('setSubtitle', '[ID: '+res.data.bid+'] '+res.data.player_nick);
						
						$('#blModule').iziModal('setContent', '\
							<table>\
								<tr>\
									<td>ID бана</td>\
									<td>'+res.data.bid+'</td>\
								</tr>\
								<tr>\
									<td>SteamID</td>\
									<td>'+res.data.player_id+'</td>\
								</tr>\
								<tr>\
									<td>IP</td>\
									<td>'+res.data.player_ip+'</td>\
								</tr>\
								<tr>\
									<td>Ник</td>\
									<td>'+res.data.player_nick+'</td>\
								</tr>\
								<tr>\
									<td>Админ</td>\
									<td>'+res.data.admin_nick+'</td>\
								</tr>\
								<tr>\
									<td>Причина</td>\
									<td>'+res.data.ban_reason+'</td>\
								</tr>\
								<tr>\
									<td>Дата</td>\
									<td>'+res.data.ban_createdF+'</td>\
								</tr>\
								<tr>\
									<td>Срок</td>\
									<td>'+(res.data.ban_length > 0 ? res.data.ban_lengthF : 'Навсегда')+'</td>\
								</tr>\
								<tr>\
									<td>Сервер</td>\
									<td>'+res.data.server_name+'</td>\
								</tr>\
							</table>\
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
				$('#blModule').iziModal('stopLoading');
				alert('Ошибка: '+status+': '+errorThrown);
			}
		});
	}
}