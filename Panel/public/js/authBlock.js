var isRequestProc = false;

$(document).ready(function(){
	$('#authBlockBtn').click(function(){
		if(!isRequestProc){
			$('.userBlock').waitMe({
				effect: 'stretch',
				bg: 'rgba(30, 30, 30, .5)',
				color: 'rgba(100, 100, 100, .8)'
			});
			$.ajax({
				url: window.panelHome+'request/userAuth.php?action=auth',
				dataType: 'json',
				type: 'POST',
				data: {
					login: $('#authBlockLogin').val(),
					pass: $('#authBlockPass').val()
				},
				success: function(res){
					$('.userBlock').waitMe('hide');
					isRequestProc = false;
					if(res instanceof Object){
						if(res.status){
							iziToast.success({title: res.msg, message: ''});
							setTimeout(function(){location.reload();}, 1000);
						}
						else iziToast.error({title: res.msg, message: ''});
					}
					else{
						console.log(res);
						iziToast.error({title: 'Возникла непредвиденная ошибка!', message: ''});
					}
				},
				timeout: 5000,
				error: function(jqXHR, status, errorThrown){
					$('.userBlock').waitMe('hide');
					alert('Ошибка! '+status+': '+errorThrown);
				}
			});
			isRequestProc = true;
		}
	});
	$('#userBlockLogout').click(function(){
		$('.userBlock').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		$('html').append('<form id="formLogout" style="display: none" method="POST" action="'+window.panelHome+'profile/logout"><input name="path" type="hidden" value="'+location+'" /></form>');
		$('#formLogout').submit();
	});
});