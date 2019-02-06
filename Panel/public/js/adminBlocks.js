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
					str = '';
					for(var i = 0; i < res.length; i++){
						str += '\
							<tr>\
								<td>'+res[i].id+'</td>\
								<td>'+res[i].pos+'</td>\
								<td>'+res[i].info.name+'</td>\
								<td>'+res[i].info.module+'</td>\
								<td>'+res[i].block+'</td>\
								<td>\
									<button style="border-top-right-radius: 0; border-bottom-right-radius: 0;" onclick="changeBlockPos('+res[i].id+', \''+list+'\', 1); return false;" class="styler"><i class="fa fa-sort-up"></i></button>\
									<button style="border-top-left-radius: 0; border-bottom-left-radius: 0;" onclick="changeBlockPos('+res[i].id+', \''+list+'\', -1); return false;" class="styler"><i class="fa fa-sort-down"></i></button>\
									<button title="Удалить блок из списка" onclick="removeBlockFromList('+res[i].id+', \''+list+'\'); return false;" class="styler"><i class="fa fa-times"></i></button>\
								</td>\
							</tr>\
						';
					}
					$('#blocksList-'+list).html(str);
				}
				else $('#blocksList-'+list).html('</tr><td colspan="5"><div align=center>Пусто</div></td></tr>');
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
				block: block,
				list: list
			},
			success: function(res){
				isRequestProc = false;
				$('#blocksList-'+list).waitMe('hide');
				if(res instanceof Object){
					console.log(res);
					if(res.status){
						reloadBlocksList(list);
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
				block: block,
				list: list
			},
			success: function(res){
				isRequestProc = false;
				$('#blocksList-'+list).waitMe('hide');
				if(res instanceof Object){
					if(res.status){
						reloadBlocksList(list);
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

function changeBlockPos(id, list, pos){
	if(!isRequestProc){
		$('#blocksList-'+list).waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminBlocks.php?action=changePos',
			dataType: 'json',
			type: 'POST',
			data: {
				id: id,
				list: list,
				pos: pos
			},
			success: function(res){
				isRequestProc = false;
				$('#blocksList-'+list).waitMe('hide');
				if(res instanceof Object){
					console.log(res);
					if(res.status){
						reloadBlocksList(list);
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