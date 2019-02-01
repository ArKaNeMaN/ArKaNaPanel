var isRequest = false;
var requestData;

function moduleAdminSendRequest(action, id){
	if(!isRequest){
		requestData = id;
		$('#modulesAdminItem-'+id).waitMe({
			effect: 'stretch',
			bg: 'rgba(30, 30, 30, .5)',
			color: 'rgba(100, 100, 100, .8)'
		});
		$.ajax({
			url: window.panelHome+'request/modules.php?action='+action,
			dataType: 'json',
			type: 'POST',
			data: {  
				module: id
			},
			success: function(res){
				$('#modulesAdminItem-'+requestData).waitMe('hide');
				isRequest = false;
				if(res instanceof Object){
					if(res.status){
						console.log(res.data);
						if(!res.data.installed) $('#modulesAdminItem-'+res.data.id+' #modulesAdminActBtn').html('<i class="fa fa-download"></i>');
						else if(res.data.active) $('#modulesAdminItem-'+res.data.id+' #modulesAdminActBtn').html('<i style="color: green" class="fa fa-power-off"></i>');
						else $('#modulesAdminItem-'+res.data.id+' #modulesAdminActBtn').html('<i style="color: red" class="fa fa-power-off"></i>');
						
						$('#modulesAdminItem-'+res.data.id+' #modulesAdminActBtn').attr('onclick', 'moduleAdminSendRequest(\''+(res.data.installed ? (res.data.active ? 'deactivate' : 'activate') : 'install')+'\', \''+res.data.id+'\')');
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
				$('#modulesAdminItem-'+requestData).waitMe('hide');
				isRequest = false;
				alert('Ошибка: '+status+': '+errorThrown);
			}
		});
		isRequest = true;
	}
}