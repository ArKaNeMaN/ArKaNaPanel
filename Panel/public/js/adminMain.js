var isRequestProc = false;

$(document).ready(function(){
	$('#adminMainSaveBtn').click(function(){
		if(!isRequestProc){
			$('#adminMainPanel').waitMe({
				effect: 'stretch',
				bg: 'rgba(30, 30, 30, .5)',
				color: 'rgba(100, 100, 100, .8)'
			});
			$.ajax({
				url: window.panelHome+'request/adminMain.php?action=save',
				dataType: 'json',
				type: 'POST',
				data: {
					siteName: $('#adminMainSiteName').val(),
					homePage: $('#adminMainHomePage').val(),
					panelTheme: $('#adminMainPanelTheme').val(),
					yaMetrika: $('#adminMainYaMetrika').val(),
					googleAnalytics: $('#adminMainGoogleAnalytics').val(),
					zipAvatars: Number($('#adminMainZipAvatars').is(':checked')),
					captchaPubKey: $('#adminMainCaptchaPubKey').val(),
					captchaSecKey: $('#adminMainCaptchaSecKey').val(),
				},
				success: function(res){
					$('#adminMainPanel').waitMe('hide');
					if(res instanceof Object){
						if(res.status) iziToast.success({title: res.msg, message: ''});
						else iziToast.error({title: res.msg, message: ''});
					}
					else{
						console.log(res);
						iziToast.error({title: 'Возникла непредвиденная ошибка', message: ''});
					}
					isRequestProc = false;
				},
				timeout: 5000,
				error: function(jqXHR, status, errorThrown){
					alert('Ошибка! '+status+': '+errorThrown);
					isRequestProc = false;
				}
			});
			isRequestProc = true;
		}
	});
});