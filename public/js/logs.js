$(document).ready(() => {
	$('#logsModal').iziModal({
		headerColor: 'rgb(20, 20, 20)',
		background: 'rgb(30, 30, 30)',
		theme: 'dark',
		width: 700,
		icon: 'fa fa-info',
		padding: 10,
		radius: 5,
		title: 'Лог',
		subTitle: 'Подробная информация',
	});
});

function showLogInfo(log){
	log = window.logs[log];
	str = '\
		<p>'+log.text+'</p>\
		<table>\
	';
	if(log.data instanceof Object || log.data instanceof Array) for(key in log.data) str += '\
		<tr>\
			<td>'+key+'</td>\
			<td>'+log.data[key]+'</td>\
		</tr>\
	';
	else str += '\
		<tr>\
			<td>data</td>\
			<td>'+log.data+'</td>\
		</tr>\
	';
	str += '</table>';
	$('#logsModal').iziModal('setContent', str);
	$('#logsModal').iziModal('open');
}