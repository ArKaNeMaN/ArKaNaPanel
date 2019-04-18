var isRequestProc = false;
function saveTplSettings(){
	if(!isRequestProc){
		isRequestProc = true;
		$('#tplSettsCont').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		var sets = window.tplSets;
		var data = {};
		for(var i = 0; i < sets.length; i++) data[sets[i].id] = sets[i].type == 'checkbox' ? Number($('#adminTpl'+sets[i].id).is(':checked')) : $('#adminTpl'+sets[i].id).val();
		$.ajax({
			url: window.panelHome+'request/modulesTplSets.php?action=save',
			dataType: 'json',
			type: 'POST',
			data: {
				sets: data,
				module: window.tplModule
			},
			success: function(res){
				isRequestProc = false;
				$('#tplSettsCont').waitMe('hide');
				if(res instanceof Object){
					if(res.status) iziToast.success({title: res.msg});
					else iziToast.error({title: res.msg});
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				isRequestProc = false;
				iziToast.error({title: 'Ошибка сервера'});
				$('#tplSettsCont').waitMe('hide');
				console.log(jqXHR);
			}
		});
	}
}