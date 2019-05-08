var isRequestProc = false;

function sendSaveMain(){
	var req = $.ap.sendRequest(
		'userSettings', 'saveMain',
		{name: $('#userSettingsNameInp').val()},
		(res) => {
			$('#userSettingsCont').waitMe('hide');
			if(res instanceof Object){
				if(res.status) iziToast.success({title: res.msg});
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {$('#userSettingsCont').waitMe('hide');}
	);
	if(req) $('#userSettingsCont').waitMe(window.waitMeSettings);
}

function sendChangePass(){
	var req = $.ap.sendRequest(
		'userSettings', 'changePass',
		{
			oldPass: $('#userSettingsOldPassInp').val(),
			newPass: $('#userSettingsNewPassInp').val(),
			newPassa: $('#userSettingsNewPassaInp').val(),
		},
		(res) => {
			$('#userSettingsCont').waitMe('hide');
			if(res instanceof Object){
				if(res.status) iziToast.success({title: res.msg});
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Что-то пошло не так :('});
			}
		},
		() => {$('#userSettingsCont').waitMe('hide');}
	);
	if(req) $('#userSettingsCont').waitMe(window.waitMeSettings);
}

function sendAvatarForm(){
	var file_data = $('#userSettingsAvatarInp').prop('files')[0];
	var form_data = new FormData();
	form_data.append('file', file_data);
	var req = $.ap.sendRequest(
		'userSettings', 'sendAvatarForm', form_data,
		(res) => {
			$('#userSettingsCont').waitMe('hide');
			if(res instanceof Object){
				if(res.status){
					$('#userSettingsAvatarPrev').attr('src', window.panelHome+res.data);
					iziToast.success({title: res.msg});
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Что-то пошло не так :('});
			}
		},
		() => {$('#userSettingsCont').waitMe('hide');}, true,
		{
			cache: false,
			contentType: false,
			processData: false,
		}
	);
	if(req) $('#userSettingsCont').waitMe(window.waitMeSettings);
}