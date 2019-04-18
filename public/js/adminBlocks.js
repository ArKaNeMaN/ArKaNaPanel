reloadAllBlocksList();
reloadBlocksList('homePage');
reloadBlocksList('rightCol');

function reloadAllBlocksList(){
	$('#allBlocksList').waitMe({
		effect: 'stretch',
		bg: 'rgba(30, 30, 30, .5)',
		color: 'rgba(100, 100, 100, .8)'
	});
	$.ajax({
		url: window.panelHome+'request/adminBlocks.php?action=getBlocks',
		dataType: 'json',
		type: 'POST',
		success: function(res){
			$('#allBlocksList').waitMe('hide');
			if(res instanceof Array){
				str = '';
				console.log(res);
				for(var i = 0; i < res.length; i++){
					str += '\
						<tr>\
							<td>'+res[i].name+'</td>\
							<td>'+res[i].module+'</td>\
							<td>'+res[i].index+'</td>\
							<td>'+res[i].type+'</td>\
							<td>\
								<button class="styler" data-dropdown="#blocks_dropdownMenu-'+i+'"><i class="fa fa-angle-down"></i>\
									<ul class="dropdown-menu dropdown-anchor-top-left dropdown-has-anchor blocksActionsMenu" id="blocks_dropdownMenu-'+i+'">\
										'+( res[i].homePage == '1' ? '\
											<li title="Добавить блок на главную страницу" onclick="addBlockToList(\''+res[i].index+'\', \'homePage\'); return false;">\
												<i class="fa fa-home"></i> На главную\
											</li>\
										' : '')+'\
										'+( res[i].rightCol == '1' ? '\
											<li title="Добавить блок в правую колонку" onclick="addBlockToList(\''+res[i].index+'\', \'rightCol\'); return false;">\
												<i class="fa fa-list"></i> В правую колонку\
											</li>\
										' : '')+'\
										<li title="Добавить блок в правую колонку" onclick="delBlock(\''+res[i].index+'\'); return false;">\
											<i class="fa fa-trash"></i> Удалить\
										</li>\
									</ul>\
								</button>\
							</td>\
						</tr>\
					';
				}
				if(str.length) $('#allBlocksList').html(str);
			}
			else $('#blocksList-'+list).html('</tr><td colspan="4"><div align=center>Пусто</div></td></tr>');
		},
		timeout: 5000,
		error: function(jqXHR, status, errorThrown){
			$('#allBlocksList').waitMe('hide');
			alert('Ошибка! '+status+': '+errorThrown);
		}
	});
}

function reloadBlocksList(list){
	$('#blocksList-'+list).waitMe({
		effect: 'stretch',
		bg: 'rgba(30, 30, 30, .5)',
		color: 'rgba(100, 100, 100, .8)'
	});
	$.ajax({
		url: window.panelHome+'request/adminBlocks.php?action=getBlocksList',
		dataType: 'json',
		type: 'POST',
		data: {list: list},
		success: function(res){
			$('#blocksList-'+list).waitMe('hide');
			if(res instanceof Array){
				str = '';
				for(var i = 0; i < res.length; i++){
					str += '\
						<tr>\
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
			alert('Ошибка! '+status+': '+errorThrown);
		}
	});
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

function delBlock(index){
	if(!isRequestProc){
		$('#allBlocksList').waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminBlocks.php?action=del',
			dataType: 'json',
			type: 'POST',
			data: {
				index: index,
			},
			success: function(res){
				isRequestProc = false;
				$('#allBlocksList').waitMe('hide');
				if(res){
					reloadAllBlocksList();
					iziToast.success({title: 'Успех! Блок удалён'});
				}
				else iziToast.error({title: 'Ошибка удаления блока'});
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				$('#allBlocksList').waitMe('hide');
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}

function importAllBlocks(){
	if(!isRequestProc){
		isRequestProc = true;
		$.ajax({
			url: window.panelHome+'request/adminBlocks.php?action=importBlocks',
			dataType: 'text',
			type: 'POST',
			success: function(res){
				isRequestProc = false;
				reloadAllBlocksList();
			},
			timeout: 5000,
			error: function(jqXHR, status, errorThrown){
				isRequestProc = false;
				alert('Ошибка! '+status+': '+errorThrown);
			}
		});
	}
}