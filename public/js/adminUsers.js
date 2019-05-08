var currentUsersList = [];

function getPage(){
	return $.ap.getGetParam('p') ? Number($.ap.getGetParam('p')) : 1;
}

reloadUsersList();

function reloadUsersList(){
	if($.ap.getGetParam('search')){
		searchUsers($.ap.getGetParam('search'));
		$('#searchInput').val($.ap.getGetParam('search'));
	}
	else loadUsers(getPage());
}

$(document).ready(() => {
	$('#usersModal').iziModal({
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',
		width: 700,
		icon: 'fa fa-user',
		padding: 20,
		radius: 5,
		title: 'Редактирование пользователя',
	});
});

function changePage(page){
	$.ap.addGetParam('p', page, true);
	loadUsers();
}

function loadUsers(){
	$.ap.sendRequest(
		'adminUsers', 'getUsers',
		{page: getPage()},
		(res) => {
			if(res instanceof Array){
				echoUsersList(res);
				echoPagination();
			}
			else iziToast.error({title: 'Пользователи не найдены'});
		},
	);
}

function searchUsers(str){
	$.ap.sendRequest(
		'adminUsers', 'search',
		{str: str},
		(res) => {
			if(res instanceof Array){
				$.ap.addGetParam('p', 1, true);
				$.ap.addGetParam('search', str, true);
				echoUsersList(res);
				echoPagination(true);
			}
			else iziToast.info({title: 'Поиск не дал результатов'});
		},
	);
}

function resetSearch(){
	$('#searchInput').val('');
	$.ap.rmGetParam('p', true);
	$.ap.rmGetParam('search', true);
	loadUsers();
}

function echoUsersList(users){
	var str = '';
	currentUsersList = [];
	for(var i = 0; i < users.length; i++){
		currentUsersList[users[i].id] = users[i];
		str += '\
			<tr class="clickable" onclick="showUserModal('+users[i].id+'); return false;">\
				<td>'+users[i].id+'</td>\
				<td>'+users[i].login+'</td>\
				<td>'+users[i].name+'</td>\
				<td>'+users[i].group+'</td>\
			</tr>\
		';
	}
	$('#usersList').html(str);
}

function echoPagination(rm = false){
	if(!rm) $.ap.sendRequest(
		'adminUsers', 'getCount',
		{},
		(res) => {
			$('#usersPagination').apPagination(getPage(), Math.ceil(Number(res)/15), 'changePage');
		}
	);
	else $('#usersPagination').html('');
}

function showUserModal(userid){
	var req = $.ap.sendRequest(
		'adminUsers', 'getUser',
		{userid: userid},
		(res) => {
			$('#usersModal').iziModal('stopLoading');
			if(res instanceof Object){
				$('#usersModal').iziModal('setSubtitle', res.name+' ['+res.id+']');
				groups = '';
				for(var i = 0; i < window.userGroups.length; i++) groups += '<option value="'+window.userGroups[i].id+'" '+(window.userGroups[i].id == res.group ? 'selected' : '')+'>'+window.userGroups[i].name+' ['+window.userGroups[i].accessLevel+']</option>';
				$('#usersModal').iziModal('setContent', '\
					<form id="userSettingsForm" onsubmit="return false;">\
						<table>\
							<tr>\
								<td>Логин</td>\
								<td><input class="styler w100" type="text" name="login" value="'+res.login+'" placeholder="Логин пользователя" /></td>\
							</tr>\
							<tr>\
								<td>Ник</td>\
								<td><input class="styler w100" type="text" name="name" value="'+res.name+'" placeholder="Имя пользователя" /></td>\
							</tr>\
							<tr>\
								<td>Пароль</td>\
								<td><input class="styler w100" type="text" name="pass" value="" placeholder="Пароль" /></td>\
							</tr>\
							<tr>\
								<td>EMail</td>\
								<td><input class="styler w100" type="text" name="email" value="'+(res.email != null ? res.email : '')+'" placeholder="Электронная почта пользователя" /></td>\
							</tr>\
							<tr>\
								<td>Группа</td>\
								<td><select class="styler" name="group">'+groups+'</select></td>\
							</tr>\
							<tr>\
								<td>Аватарка</td>\
								<td><input class="styler w100" type="text" name="avatar" value="'+res.avatar+'" placeholder="Аватарка пользователя" /></td>\
							</tr>\
							<tr>\
								<td>Баланс</td>\
								<td><input class="styler w100" type="text" name="money" value="'+res.money+'" placeholder="Баланс пользователя" /></td>\
							</tr>\
							<tr>\
								<td colspan="2">\
									<button style="font-weight: bold;" class="styler"><i class="fa fa-save"></i> Сохранить</button>\
									<button style="float: right; font-weight: bold; color: red;" class="styler" onclick="delUser('+res.id+'); return false;"><i class="fa fa-trash"></i> Удалить</button>\
								</td>\
							</tr>\
						</table>\
					</form>\
				');
				$('.styler').styler()
				$('#userSettingsForm').submit(() => {
					var req = $.ap.sendRequest(
						'adminUsers', 'editUser',
						{
							userid: res.id,
							data: $('#userSettingsForm').serializeToJSON(),
						},
						(res) => {
							$('#usersModal').iziModal('stopLoading');
							if(res instanceof Object){
								if(res.status){
									reloadUsersList();
									iziToast.success({title: res.msg});
								}
								else iziToast.error({title: res.msg});
							}
							else iziToast.error({title: 'Что-то пошло не так :('});
						},
						() => {$('#usersModal').iziModal('stopLoading');},
						true,
					);
					if(req) $('#usersModal').iziModal('startLoading');
					return false;
				});
			}
			else{
				iziToast.info({title: 'Пользователь не найден'});
				$('#usersModal').iziModal('close');
			}
		},
		() => {$('#usersModal').iziModal('close');}
	);
	if(req){
		$('#usersModal').iziModal('open');
		$('#usersModal').iziModal('startLoading');
	}
}

function delUser(user){
	if(confirm('Вы действительно хотите удалить пользователя '+currentUsersList[user].name+' ['+user+']')){
		
	}
}