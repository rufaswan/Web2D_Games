'use strict';

function getHtmlIds(){
	var html = {};
	var eles = document.querySelectorAll('*[id]');
	for ( var i=0; i < eles.length; i++ ) {
		var id  = eles[i].id;
		html[id] = eles[i];
	}
	return html;
}

function displayViewer( html, toggle ){
	if ( toggle ){
		html.viewer.style.display   = 'block';
		html.debugger.style.display = 'none';
	}
	else {
		html.viewer.style.display   = 'none';
		html.debugger.style.display = 'block';
	}
}

function btnPrevNext( qdata, adj ){
	adj = adj | 0;
	if ( ! qdata || adj === 0 )
		return;
	if ( ['keyframe','hitbox','slot'].indexOf(qdata.attach.type) !== -1 )
		qdata.attach.id += adj;
	else
		qdata.anim_fps  += adj;
}

function qdata_tagtable( tag, parent ){
	if ( ! tag )
		return '';
	parent.innerHTML = '<h2>tag</h2>';

	function wikilink( tagkey, tagval ){
		if ( tagkey.toLowerCase() === 'comment' || tagval === '-' )
			return tagval;
		var href = tagval.replace(/ /g, '_');
		return '<a href="https://en.m.wikipedia.org/wiki/' +href+ '" target="_blank">' +tagval+ '</a>';
	}

	var table = '<table id="quad_data_tags">';
	var keys = Object.keys(tag);
	keys.forEach(function(k){
		var t = {};
		t.l = k;
		if ( Array.isArray(tag[k]) ){
			t.v = [];
			tag[k].forEach(function(tv){
				t.v.push( wikilink( k, tv ) );
			});
			t.r = t.v.join(' , ');
		}
		else {
			t.r = wikilink( k, tag[k] );
		}

		table += '<tr><td><p>' + t.l + '</p></td><td><p>' + t.r + '</p></td></tr>';
	});
	table += '</table>';
	parent.innerHTML += table;
}

function qdata_attach( qdata, type, id ){
	qdata.attach.type = type;
	qdata.attach.id   = id;
	qdata.anim_fps    = 0;
}

/*
					video.currentTime += 1;
					while( video.currentTime >= video.duration )
						video.currentTime -= video.duration;

		m.GL.enable(m.GL.DEPTH_TEST);
		m.GL.depthFunc(m.GL.LESS);

			$.vec_resize(2, c0);
			$.vec_resize(2, c1);
			$.vec_resize(2, c2);
			$.vec_resize(2, c3);
		return [].concat(c0,c1,c2,c3);

function button_export_mp4( qdata, out, size ){
	var camera = [1,0,0,0 , 0,1,0,0 , 0,0,1,0 , 0,0,0,1];
	var color  = [1,1,1,1];
	var len = 60; // 60 fps for 1 secs
	if ( qdata.attach.type === 'keyframe' )
		return;

	HTML.canvas.width  = tex.w;
	HTML.canvas.height = tex.h;

	var stream = HTML.canvas.captureStream(0);
	var record = new MediaRecorder(stream);
	var chunks = [];
	record.ondataavailable = function(e){
		chunks.push(e.data);
	};

	for ( var i=0; i < len; i++ ){
		qdata.anim_fps = i;
		QUAD.gl.clear();
		QUAD.func.qdata_draw(qdata, camera, color);

		stream.getVideoTracks()[0].requestFrame();
	} // for ( var i=0; i < len; i++ )

	var a = document.createElement('a');
	a.href = 'data:application/zip;base64,' + QUAD.binary.toBase64(zip);
	a.setAttribute('download', out + '.zip');
	a.setAttribute('target'  , '_blank');
	a.click();
	return;
}

		case 'mp4':  button_export_mp4(qdata, out, size); break;
							t.btn3 = '<button onclick="button_export(\'mp4\', this);">mp4</button>';
 */
