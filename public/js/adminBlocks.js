var curBlocksList = null;
var blocks = [];
var blocksShow = [];

reloadPlacesList();
reloadAllBlocksList();
reloadBlocksList();

$(document).ready(() => {
	$('#blocksModal').iziModal({
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',
		width: 700,
		icon: 'fa fa-th-large',
		padding: 20,
		radius: 5,
	});
});

function reloadPlacesList(){
	$.ap.sendRequest(
		'adminBlocks', 'getPlaces', {},
		(res) => {
			curBlocksList = null;
			str = '<option value="0">Выберите список блоков</option>';
			if(res instanceof Array){
				for(var i = 0; i < res.length; i++) if(res[i] != null) str += '<option value="'+res[i]+'">'+res[i]+'</option>';
			}
			else iziToast.info({title: 'Места бля блоков не найдены'});
			$('#blocksPlaces').html(str);
			setTimeout(() => {$('#blocksPlaces').trigger('refresh');}, 1);
		},
	);
}

function reloadAllBlocksList(){
	$('#allBlocksList').waitMe(window.waitMeSettings);
	$.ap.sendRequest(
		'adminBlocks', 'getBlocks', {},
		(res) => {
			$('#allBlocksList').waitMe('hide');
			if(res instanceof Array){
				blocks = [];
				str = '';
				for(var i = 0; i < res.length; i++){
					blocks[res[i].id] = res[i];
					places = '';
					if(res[i].places) for(var k = 0; k < res[i].places.length; k++) places += '\
						<li title="Добавить блок в правую колонку" onclick="addBlockToList('+res[i].id+', \''+res[i].places[k]+'\'); return false;">\
							<i class="fa fa-list"></i> '+res[i].places[k]+'\
						</li>\
					';
					str += '\
						<tr>\
							<td>'+res[i].index+'</td>\
							<td>'+res[i].name+'</td>\
							<td>'+res[i].module+'</td>\
							<td>\
								<button class="styler" data-dropdown="#blocks_dropdownMenu-'+i+'"><i class="fa fa-angle-down"></i>\
									<ul class="dropdown-menu dropdown-anchor-top-left dropdown-has-anchor blocksActionsMenu" id="blocks_dropdownMenu-'+i+'">\
										'+places+'\
										<li title="Удалить" onclick="delBlock('+res[i].id+'); return false;">\
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
			else $('#allBlocksList').html('</tr><td colspan="5"><div align=center>Пусто</div></td></tr>');
		},
		() => {$('#allBlocksList').waitMe('hide');}
	);
}

function changePlaceList(place){
	curBlocksList = place == '0' ? null : place;
	reloadBlocksList();
	$('#blocksPlaces :selected').attr('selected', null);
	$('#blocksPlaces [value="'+place+'"]').attr('selected', '');
	setTimeout(() => {$('#blocksPlaces').trigger('refresh');}, 1);
}

function reloadBlocksList(){
	if(curBlocksList == null){
		$('#placeBlocksList').html('</tr><td colspan="3"><div align=center>Не выбран список</div></td></tr>');
		return;
	}
	$('#placeBlocksList').waitMe(window.waitMeSettings);
	$.ap.sendRequest(
		'adminBlocks', 'getBlocksList',
		{list: curBlocksList},
		(res) => {
			$('#placeBlocksList').waitMe('hide');
			if(res instanceof Array){
				blocksShow = [];
				str = '';
				for(var i = 0; i < res.length; i++){
					blocksShow[res[i].id] = res[i];
					str += '\
						<tr>\
							<td>'+res[i].pos+'</td>\
							<td>'+res[i].info.name+'</td>\
							<td>\
								<button onclick="changeBlockPos('+res[i].id+', 1); return false;" class="styler"><i class="fa fa-sort-up"></i></button>\
								<button onclick="changeBlockPos('+res[i].id+', -1); return false;" class="styler"><i class="fa fa-sort-down"></i></button>\
								'+(res[i].dataList instanceof Array ?
									'<button onclick="showBlockSettings('+res[i].id+'); return false;" class="styler"><i class="fa fa-sort-down"></i></button>'
								: '')+'\
								<button title="Удалить блок из списка" onclick="removeBlockFromList('+res[i].id+'); return false;" class="styler"><i class="fa fa-times"></i></button>\
							</td>\
						</tr>\
					';
				}
				$('#placeBlocksList').html(str);
			}
			else $('#placeBlocksList').html('</tr><td colspan="3"><div align=center>Пусто</div></td></tr>');
		},
		() => {$('#placeBlocksList').waitMe('hide');}
	);
}

function showBlocksFromFiles(){
	var req = $.ap.sendRequest(
		'adminBlocks', 'getFromFiles', {},
		(res) => {
			if(res instanceof Array){
				$('#blocksModal').iziModal('setTitle', 'Блоки, доступные для установки');
				str = '<table>';
				for(var i = 0; i < res.length; i++) str += '<tr>\
					<td>'+res[i].index+'</td>\
					<td>'+res[i].name+'</td>\
					<td>'+res[i].module+'</td>\
					<td><button class="styler" onclick="installBlockFromFile(\''+res[i].index+'\'); return false;"><i class="fa fa-plus"></i></button></td>\
				</tr>';
				str += '</table>';
				$('#blocksModal').iziModal('setContent', str);
				$('#blocksModal').iziModal('stopLoading');
			}
			else{
				console.log(res);
				iziToast.info({title: 'Файлы блоков не найдены'});
				$('#blocksModal').iziModal('close');
			}
		},
		() => {$('#blocksModal').iziModal('close');},
		true
	);
	if(req){
		$('#blocksModal').iziModal('open');
		$('#blocksModal').iziModal('startLoading');
	}
}

function installBlockFromFile(index){
	var req = $.ap.sendRequest(
		'adminBlocks', 'installFromFile',
		{index: index},
		(res) => {
			$('#blocksModal').iziModal('stopLoading');
			if(res){
				reloadAllBlocksList();
				iziToast.success({title: 'Успех! Блок установлен'});
			}
			else iziToast.error({title: 'Ошибка! Блок не установлен'});
		},
		() => {$('#blocksModal').iziModal('stopLoading');},
		
	);
	if(req) $('#blocksModal').iziModal('startLoading');
}

function removeBlockFromList(block){
	var req = $.ap.sendRequest(
		'adminBlocks', 'remove',
		{block: block},
		(res) => {
			$('#placeBlocksList').waitMe('hide');
			if(res instanceof Object){
				if(res.status){
					reloadBlocksList();
					iziToast.success({title: res.msg});
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {$('#placeBlocksList').waitMe('hide');},
		true
	);
	if(req) $('#placeBlocksList').waitMe(window.waitMeSettings);
}

function addBlockToList(block, list){
	var req = $.ap.sendRequest(
		'adminBlocks', 'add',
		{
			block: block,
			list: list
		},
		(res) => {
			$('#placeBlocksList').waitMe('hide'); 
			if(res instanceof Object){
				if(res.status){
					changePlaceList(list);
					iziToast.success({title: res.msg});
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {$('#placeBlocksList').waitMe('hide');},
		true
	);
	if(req) $('#placeBlocksList').waitMe(window.waitMeSettings);
}

function changeBlockPos(id, pos){
	var req = $.ap.sendRequest(
		'adminBlocks', 'changePos',
		{
			id: id,
			pos: pos,
		},
		(res) => {
			$('#placeBlocksList').waitMe('hide');
			if(res instanceof Object){
				if(res.status){
					reloadBlocksList();
					iziToast.success({title: res.msg});
				}
				else iziToast.error({title: res.msg});
			}
			else{
				console.log(res);
				iziToast.error({title: 'Возникла непредвиденная ошибка'});
			}
		},
		() => {$('#placeBlocksList').waitMe('hide');},
		true
	);
	if(req) $('#placeBlocksList').waitMe(window.waitMeSettings);
}

function delBlock(id){
	$.ap.showConfirmModal('Вы действительно хотите удалить блок?', {
		agree: {
			text: '<b><i class="fa fa-check"></i> Да</b>',
			func: () => {
				var req = $.ap.sendRequest(
					'adminBlocks', 'del',
					{id: id},
					(res) => {
						$('#allBlocksList').waitMe('hide');
						if(res){
							reloadAllBlocksList();
							iziToast.success({title: 'Успех! Блок удалён'});
						}
						else iziToast.error({title: 'Ошибка удаления блока'});
					},
					() => {}, true
				);
			},
		},
		disagree: {
			text: '<b style="color: red;"><i class="fa fa-times"></i> Нет</b>',
			func: () => {},
		},
	});
}

function importAllBlocks(){
	var req = $.ap.sendRequest(
		'adminBlocks', 'importBlocks', {},
		(res) => {reloadAllBlocksList();$('#allBlocksList').waitMe('hide');},
		() => {$('#allBlocksList').waitMe('hide');},
		true, {timeout: 10000}
	);
	if(req) $('#allBlocksList').waitMe(window.waitMeSettings);
}