var isRequestProc = false;

function sendSaveMain(){
	if(!isRequestProc){
		$('#userSettingsCont').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		$.ajax({
			url: window.panelHome+'request/userSettings.php?action=saveMain',
			dataType: 'json',
			type: 'POST',
			data: {
				name: $('#userSettingsNameInp').val(),
			},
			success: function(res){
				$('#userSettingsCont').waitMe('hide');
				if(res instanceof Object){
					if(res.status) iziToast.success({title: res.msg});
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
				isRequestProc = false;
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#userSettingsCont').waitMe('hide');
				alert('Ошибка! '+status+': '+errorThrown);
				isRequestProc = false;
			}
		});
		isRequestProc = true;
	}
}

function sendChangePass(){
	if(!isRequestProc){
		$('#userSettingsCont').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		$.ajax({
			url: window.panelHome+'request/userSettings.php?action=changePass',
			dataType: 'json',
			type: 'POST',
			data: {
				oldPass: $('#userSettingsOldPassInp').val(),
				newPass: $('#userSettingsNewPassInp').val(),
				newPassa: $('#userSettingsNewPassaInp').val(),
			},
			success: function(res){
				$('#userSettingsCont').waitMe('hide');
				if(res instanceof Object){
					if(res.status) iziToast.success({title: res.msg});
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
				isRequestProc = false;
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#userSettingsCont').waitMe('hide');
				alert('Ошибка! '+status+': '+errorThrown);
				isRequestProc = false;
			}
		});
		isRequestProc = true;
	}
}

function sendAvatarForm(){
	if(!isRequestProc){
		$('#userSettingsCont').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		var file_data = $('#userSettingsAvatarInp').prop('files')[0];
		var form_data = new FormData();
		form_data.append('file', file_data);
		$.ajax({
			url: window.panelHome+'request/userSettings.php?action=sendAvatarForm',
			dataType: 'json',
			type: 'POST',
			data: form_data,
			cache: false,
			contentType: false,
			processData: false,
			success: function(res){
				$('#userSettingsCont').waitMe('hide');
				if(res instanceof Object){
					if(res.status) iziToast.success({title: res.msg});
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
				isRequestProc = false;
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#userSettingsCont').waitMe('hide');
				alert('Ошибка! '+status+': '+errorThrown);
				isRequestProc = false;
			}
		});
		isRequestProc = true;
	}
}