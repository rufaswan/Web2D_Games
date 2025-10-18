'use strict';

var APP = {};

APP.get_html_id = function(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id   = eles[i].id;
		html[id] = eles[i];
	}
	return html;
}

APP.qdata_filetable = function( qdata, files ){
	files.innerHTML = '';
	if ( qdata.name )
		files.innerHTML += '<li>[QUAD] ' + qdata.name + '</li>';
	for ( var i=0; i < qdata.image.length; i++ ){
		if ( ! qdata.image[i] || ! qdata.image[i].name )
			continue;
		var img = qdata.image[i];
		files.innerHTML += '<li>[IMAGE][' + i + '] ' + img.name + ' (' + JSON.stringify(img.pos) + ')</li>';
	}
}

APP.process_uploads = function(){
	var proall = [];
	while ( APP.upload_queue.length > 0 ){
		var up = APP.upload_queue.shift();

		if ( typeof up.name !== 'string' )  continue;
		if ( typeof up.data !== 'string' )  continue;

		up.id |= 0;
		if ( up.id < 0 )
			continue;
		if ( ! APP.QuadList[up.id] )
			APP.QuadList[up.id] = new QUAD.QuadData(APP.QuadList);

		var pro = QUAD.func.queue_promise(up, APP.QuadList[up.id]);
		proall.push(pro);
	} // while ( queue.length > 0 )

	return Promise.all(proall).then(function(res){
		return APP.process_uploads_done();
	});
}

APP.remove_upload = function(){
	document.getElementById('input_file').remove();
	document.getElementById('btn_upload').remove();
}
