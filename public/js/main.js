(function($){
	
	$.ap = {
		isRequestProc: false,
		
		getGetParam: (key) => {
			var val = new RegExp(key + '(?:=(.*?))?(?:[&#]|$)').exec(window.location.search.slice(1));
			return val === null ? null : (
				typeof val[1] === 'string' ? decodeURIComponent(val[1].replace(/\+/g, ' ')) : ''
			);
		},
		addGetParam: (key, value, noReload = false) => { //https://exceptionshub.com/adding-a-parameter-to-the-url-with-javascript.html
			key = encodeURI(key); value = encodeURI(value);
			var kvp = document.location.search.substr(1).split('&');
			var i=kvp.length; var x; while(i--){
				x = kvp[i].split('=');
				if (x[0]==key){
					x[1] = value;
					kvp[i] = x.join('=');
					break;
				}
			}
			if(i<0) {kvp[kvp.length] = [key,value].join('=');}
			var newUrl = location.protocol+'//'+location.host+location.pathname+'?'+kvp.join('&');
			if(!noReload) document.location = newUrl;
			else window.history.pushState({path: newUrl}, '', newUrl);
		},
		rmGetParam: (parameter, noReload = false) => {
			var url = location.href;
			var urlparts = url.split('?');   
			if(urlparts.length>=2){
				var prefix = encodeURIComponent(parameter)+'=';
				var pars = urlparts[1].split(/[&;]/g);
				for(var i = pars.length; i-- > 0;)
					if(pars[i].lastIndexOf(prefix, 0) !== -1) pars.splice(i, 1);
				if(pars.length > 0) url = urlparts[0]+'?'+pars.join('&');
				else url = urlparts[0];
			}
			if(!noReload) document.location = url;
			else window.history.pushState({path: url}, '', url);
		},
		redirect: (url, params = null) => {
			url = url.replace(window.panelHome, '');
			var str = (url == 'this' ? location.protocol+'//'+location.host+location.pathname : window.panelHome+url)+(params != null ? '?'+$.param(params, true) : '');
			//console.log(url); return false;
			location.href = str;
		},
		sendRequest: (request, action, data = {}, successFunc = () => {}, errorFunc = () => {}, onlyOneRequest = false, additionalParams = {}) => {
			if(onlyOneRequest && $.ap.isRequestProc){
				iziToast.info({title: 'Обрабатываетсядругой запрос. Попробуйте позже'});
				return false;
			}
			var ajaxParams = {
				url: window.panelHome+'request/'+request+'.php?action='+action,
				dataType: 'json',
				type: 'POST',
				data: data,
				success: (res) => {
					if(onlyOneRequest) $.ap.isRequestProc = false;
					successFunc(res);
				},
				timeout: 5000,
				error: (jqXHR, status, errorThrown) => {
					if(onlyOneRequest) $.ap.isRequestProc = false;
					iziToast.error({title: 'Ошибка сервера'});
					console.log(jqXHR);
					console.log(status);
					console.log(errorThrown);
					errorFunc(jqXHR, status, errorThrown);
				}
			};
			if(additionalParams != {}) ajaxParams = Object.assign(ajaxParams, additionalParams);
			if(onlyOneRequest) $.ap.isRequestProc = true;
			$.ajax(ajaxParams);
			return true;
		},
		pagination: (element, page, total, click = '') => {
			$(element).apPagination(page, total, click);
		},
		changePage: (page = 1) => {
			page = Math.max(page, 1);
			$.ap.addGetParam('p', page);
			return page;
		},
	};
	
	$.fn.apPagination = function(page, total, click = '$.ap.changePage'){
		str = '<ul class="pagination modal-5">';
		str += '<li><a class="prev fa fa-angle-double-left" onclick="'+click+'(1); return false;" href="?p=1"></a></li>';
		if(page-1 > 0) str += '<li><a class="fa fa-angle-left" onclick="'+click+'('+(page-1)+'); return false;" href="?p='+(page-1)+'"></a></li>';
		if(page-3 > 0) str += '<li><a onclick="'+click+'('+(page-3)+'); return false;" href="?p='+(page-3)+'">'+(page-3)+'</a></li>';
		if(page-2 > 0) str += '<li><a onclick="'+click+'('+(page-2)+'); return false;" href="?p='+(page-2)+'">'+(page-2)+'</a></li>';
		if(page-1 > 0) str += '<li><a onclick="'+click+'('+(page-1)+'); return false;" href="?p='+(page-1)+'">'+(page-1)+'</a></li>';
		str += '<li><a href="#" class="active">'+page+'</a></li>';
		if(page+1 <= total) str += '<li><a onclick="'+click+'('+(page+1)+'); return false;" href="?p='+(page+1)+'">'+(page+1)+'</a></li>';
		if(page+2 <= total) str += '<li><a onclick="'+click+'('+(page+2)+'); return false;" href="?p='+(page+2)+'">'+(page+2)+'</a></li>';
		if(page+3 <= total) str += '<li><a onclick="'+click+'('+(page+3)+'); return false;" href="?p='+(page+3)+'">'+(page+3)+'</a></li>';
		if(page+1 <= total) str += '<li><a class="fa fa-angle-right" onclick="'+click+'('+(page+1)+'); return false;" href="?p='+(page+1)+'"></a></li>';
		str += '<li><a class="next fa fa-angle-double-right" onclick="'+click+'('+total+'); return false;" href="?p='+total+'"></a></li>';
		str +='</ul>';
		console.log(this);
		this.html(str);
		return this;
	};
}(jQuery));