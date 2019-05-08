function saveTplSettings(){
	var sets = window.tplSets;
	var data = new FormData();
	for(var i = 0; i < sets.length; i++)
		if(sets[i].type == 'checkbox') data.append(sets[i].id, Number($('#adminTpl'+sets[i].id).is(':checked')));
		else if(sets[i].type == 'file'){
			var settIndex = sets[i].id;
			$.each($('#adminTpl'+sets[i].id)[0].files, (i, file) => {data.append(settIndex, file);});
		}
		else data.append(sets[i].id, $('#adminTpl'+sets[i].id).val());
	var req = $.ap.sendRequest(
		'adminMain', 'save',
		data,
		(res) => {
			$('#tplSettsCont').waitMe('hide');
			if(res instanceof Object)
				if(res.status) iziToast.success({title: res.msg});
				else iziToast.error({title: res.msg});
			else iziToast.error({title: 'Ошибка сохранения настроек'});
		},
		() => {$('#tplSettsCont').waitMe('hide');},
		true,
		{
			cache: false,
			contentType: false,
			processData: false,
			timeout: 30*1000,
		}
	);
	if(req) $('#tplSettsCont').waitMe({
		effect: 'stretch',
		bg: 'rgba(30, 30, 30, .5)',
		color: 'rgba(100, 100, 100, .8)'
	});
}