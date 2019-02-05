function reloadAllBLocksList(){
	
}

function reloadBlocksList(list){
	if(!isRequestProc){
		$('#blocksList-'+list).waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminBlocks.php?action=getBlocksList',
			dataType: 'json',
			type: 'POST',
			data: {list: list},
			success: function(res){
				isRequestProc = false;
				$('#blocksList-'+list).waitMe('hide');
				if(res instanceof Array){
					srt = '';
					for(var i = 0; i < res.length; i++){
						str += '\
							<li>\
								'+res[i].name+' (Модуль: '+res[i].module+')\
								<button title="Удалить блок из списка" onclick="removeBlockFromList(\''+res[i].id+'\', \''+list+'\'); return false;" class="styler"><i class="fa fa-times"></i></button>\
							</li>\
						';
					}
					$('#blocksList-'+list).html(str);
				}
				else{
					console.log(res);
					iziToast.error({title: 'Возникла непредвиденная ошибка'});
				}
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#blocksList-'+list).waitMe('hide');
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}

function removeBlockFromList(block, list){
	if(!isRequestProc){
		$('#blocksList-'+list).waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminBlocks.php?action=remove',
			dataType: 'json',
			type: 'POST',
			data: {
				id: block,
				list: list
			},
			success: function(res){
				isRequestProc = false;
				$('#blocksList-'+list).waitMe('hide');
				if(res instanceof Object){
					if(res.status){
						reloadBlockList(list);
						iziToast.success({title: res.msg});
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
				$('#blocksList-'+list).waitMe('hide');
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}

function addBlockToList(block, list){
	if(!isRequestProc){
		$('#blocksList-'+list).waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminBlocks.php?action=add',
			dataType: 'json',
			type: 'POST',
			data: {
				id: block,
				list: list
			},
			success: function(res){
				isRequestProc = false;
				$('#blocksList-'+list).waitMe('hide');
				if(res instanceof Object){
					if(res.status){
						reloadBlockList(list);
						iziToast.success({title: res.msg});
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
				$('#blocksList-'+list).waitMe('hide');
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}