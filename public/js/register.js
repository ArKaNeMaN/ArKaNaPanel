var checkProc = false;

$(document).ready(function(){
	/* $('#userRegBtnReg').click(function(){
		if(!checkProc){
			$.ajax({
				url: window.panelHome+'request/userReg.php?action=reg',
				dataType: 'json',
				type: 'POST',
				data: {
					login: $('#userRegLogin').val(),
					pass: $('#userRegPass').val(),
					passa: $('#userRegPassa').val(),
					name: $('#userRegName').val(),
					email: $('#userRegEmail').val()
				},
				success: function(res){
					$('#userRegBtnReg').html('Регистрация');
					if(res instanceof Object){
						if(res.status){
							iziToast.success({title: 'Успех!', message: res.msg});
							setTimeout(function(){window.location.replace('/');}, 2000);
						}
						else{
							iziToast.error({title: 'Ошибка!', message: res.msg});
						}
					}
					else{
						console.log(res);
						iziToast.error({title: 'Ошибка', message: 'Возникла непредвиденная ошибка!'});
					}
				},
				timeout: 5000,
				error: function(jqXHR, status, errorThrown){
					$('#userRegBtnReg').html('Регистрация');
					alert('Ошибка! '+status+': '+errorThrown);
				}
			});
			$('#userRegBtnReg').html('<i class="fa fa-spinner fa-spin"></i>');
		}
	}); */
	$('#userRegBtnCheck').click(function(){
		if(!checkProc){
			$('#regForm').waitMe({
				effect: 'stretch',
				bg: 'rgba(30, 30, 30, .5)',
				color: 'rgba(100, 100, 100, .8)'
			});
			$.ajax({
				url: window.panelHome+'request/userReg.php?action=check',
				dataType: 'json',
				type: 'POST',
				data: {
					login: $('#userRegLogin').val(),
					pass: $('#userRegPass').val(),
					passa: $('#userRegPassa').val(),
					name: $('#userRegName').val(),
					email: $('#userRegEmail').val()
				},
				success: function(res){
					$('#regForm').waitMe('hide');
					if(res instanceof Object){
						if(res.status) iziToast.success({title: res.msg});
						else iziToast.error({title: res.msg});
					}
					else{
						iziToast.error({title: 'Возникла непредвиденная ошибка!'});
					}
				}
			});
		}
	});
});

var regSendForm = function(token = {}){
	if(!checkProc){
		$('#regForm').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		$.ajax({
			url: window.panelHome+'request/userReg.php?action=reg',
			dataType: 'json',
			type: 'POST',
			data: {
				login: $('#userRegLogin').val(),
				pass: $('#userRegPass').val(),
				passa: $('#userRegPassa').val(),
				name: $('#userRegName').val(),
				email: $('#userRegEmail').val(),
				cToken: token,
			},
			success: function(res){
				$('#regForm').waitMe('hide');
				if(res instanceof Object){
					if(res.status){
						iziToast.success({title: res.msg});
						setTimeout(function(){window.location.replace(window.panelHome);}, 1000);
					}
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка!'});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#regForm').waitMe('hide');
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}