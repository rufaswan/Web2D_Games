'use strict';

function get_html_id(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id  = eles[i].id;
		html[id] = eles[i];
	}
	return html;
}

function mobileparameter( mobile, list, table ){
	var output = '';
	list.forEach(function(lv,lk){
		if ( Array.isArray(lv) ){
			var cur  = mobile;
			var name = '';
			for ( var i=0; i < lv.length; i++ ){
				var tmp = cur[ lv[i] ];
				var p2  = ( tmp ) ? 'ok' : 'fail';
				name   += '.' + lv[i];
				output += '<tr><td>' + name + '</td><td>' + p2 + '</td></tr>';
				cur = tmp;
			} // for ( var i=0; i < lv.length; i++ )
		}
		else {
			var p2 = ( mobile[lv] ) ? 'ok' : 'fail';
			output += '<tr><td>' + lv + '</td><td>' + p2 + '</td></tr>';
		}
	});
	table.innerHTML = output;
}

function mobilecss( list, table ){
	if ( ! window.CSS.supports )
		return;
	var output = '';
	list.forEach(function(lv,lk){
		var p1 = lv[0] + ' : ' + lv[1];
		var p2 = ( CSS.supports(lv[0], lv[1]) ) ? 'ok' : 'fail';
			output += '<tr><td>' + p1 + '</td><td>' + p2 + '</td></tr>';
	});
	table.innerHTML = output;
}

function glparameter( GL, list, table ){
	var output = '';
	list.forEach(function(lv,lk){
		var val = GL.getParameter( GL[lv] );
		var p2  = JSON.stringify(val);
		output += '<tr><td>' + lv.toLowerCase() + '</td><td>' + p2 + '</td></tr>';
	});
	table.innerHTML = output;
}

function glreference( GL, list, table ){
	var output = '';
	list.forEach(function(lv,lk){
		var val = GL.getParameter( GL[ lv[0] ] );
		var p2 = '' + val + ' [' + lv[1] + ']';
		var p3 = ( val >= lv[1] ) ? 'ok' : 'fail';
		output += '<tr><td>' + lv[0].toLowerCase() + '</td><td>' + p2 + '</td><td>' + p3 + '</td></tr>';
	});
	table.innerHTML = output;
}

function glprecision( GL, table ){
	var output = '';
	['LOW','MEDIUM','HIGH'].forEach(function(pr){
		['INT','FLOAT'].forEach(function(ty){
			['VERTEX_SHADER','FRAGMENT_SHADER'].forEach(function(sh){
				var type = pr + '_' + ty;
				var form = GL.getShaderPrecisionFormat(GL[sh], GL[type]);

				var p1 = sh + ' ' + type;
				var p2 = form.precision + ' [-2<sup>' + form.rangeMin + '</sup>,2<sup>' + form.rangeMax + '</sup>]';
				if ( form.precision === 0 )
					var p3 = (1 << form.rangeMin).toLocaleString(); // int
				else
					var p3 = (1 << form.precision).toLocaleString(); // float
				output += '<tr><td>' + p1.toLowerCase() + '</td><td>' + p2 + '</td><td>' + p3 + '</td></tr>';
			});
		});
	});
	table.innerHTML = output;
}
