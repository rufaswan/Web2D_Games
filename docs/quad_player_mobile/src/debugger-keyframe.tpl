<!doctype html>
<license>
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
</license>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Debugger - Keyframe</title>
@@<debugger-keyframe.css>@@
@@<debugger-keyframe.js>@@
@@<quad.js>@@
</head><body>

<main>
	<nav>
		<input type='file' id='input_file' multiple class='hidden'>
		<button id='btn_upload' data-id='0'>upload</button>

		<button id='btn_lines'  >line</button>
		<button id='btn_zoomin' >+</button>
		<button id='btn_zoomout'>-</button>
	</nav>

	<div id='viewer'>
		@@<bg-q3.png>@@
		<canvas id='canvas'>Canvas not supported</canvas>
	</div>

	<div id='debugger'>

		<div id='quad_data'>
			<h1 id='quad_version'></h1>
			<h2>Files</h2>
			<ol id='debugger_files'></ol>
			<ul id='keylist'></ul>
			<p>
				<a href='https://rufaswan.github.io/Web2D_Games/quad_player_mobile/debugger-keyframe.tpl.html' target='_blank'>Latest version</a> -
				<a href='https://rufaswan.github.io/Web2D_Games/quad_player_mobile/spec.html' target='_blank'>QUAD File Spec</a> -
				<a href='https://github.com/rufaswan/Web2D_Games' target='_blank'>Github</a>
			</p>
		</div>

		<div id='layerdata'>
			<div><button onclick='layer_close();'>close</button> <span id='layer_name'></span></div>

			<div>
				<select id='debuglist'></select>
				<button id='btn_selectall' class='hidden'>on</button>
				<button id='btn_selectnone' class='hidden'>off</button>
			</div>

			<ul id='layerlist'></ul>
		</div>

	</div>
</main>

<script>
'use strict';

var __ = {
	HTML      : get_html_id(),
	UPLOAD_ID : -1,
	ON_KEY    : -1,
	ON_LAYER  : [],
	IS_REDRAW : true,
	AUTOZOOM  : 1.0,
	CAMERA    : QUAD.math.matrix4(),
	COLOR     : [1,1,1,1],
};
var QuadList = [];

(function(){
	if ( ! QUAD.gl.init(__.HTML.canvas) )
		return;

	document.title += ' - ' + QUAD.version;
	__.HTML.quad_version.innerHTML = document.title;

	// BETWEEN DEBUGGER-VIEWER
	__.HTML.btn_upload.addEventListener('click', function(){
		__.UPLOAD_ID = this.getAttribute('data-id');
		__.HTML.input_file.click();
	});
	__.HTML.input_file.addEventListener('change', function(){
		QUAD.func.log('QuadList[]', __.UPLOAD_ID);
		if ( QUAD.func.is_undef( QuadList[ __.UPLOAD_ID ] ) )
			QuadList[ __.UPLOAD_ID ] = new QuadData(QuadList);
		var qdata = QuadList[ __.UPLOAD_ID ];

		var promises = [];
		for ( var up of this.files )
			promises.push( QUAD.func.upload_promise(up, qdata) );

		Promise.all(promises).then(function(resolve){
			qdata_filetable(qdata, __.HTML.debugger_files);
			if ( qdata.name ){
				__.HTML.keylist.innerHTML = '';
				document.title = '[' + qdata.name + '] ' + __.HTML.quad_version.innerHTML;

				if ( ! qdata.quad.keyframe )
					return;
				qdata.attach.type = 'keyframe';

				var buffer = '';
				qdata.quad.keyframe.forEach(function(v,k){
					if ( ! v )
						return;
					var name = v.name + ' (' + v.debug + ')';
					buffer += '<li><p onclick="keyframe_select(' + k + ');">' + name + '</p></li>';
				});
				__.HTML.keylist.innerHTML = buffer;
			} // if ( qdata.name )
			__.AUTOZOOM  = 1.0;
			__.IS_REDRAW = true;
		});
	});

	// VIEWER
	__.HTML.btn_zoomin.addEventListener('click', function(){
		__.AUTOZOOM *= 1.1;
		if ( __.AUTOZOOM > 10.0 )
			__.AUTOZOOM = 10.0;
		__.IS_REDRAW = true;
	});
	__.HTML.btn_zoomout.addEventListener('click', function(){
		__.AUTOZOOM /= 1.1;
		if ( __.AUTOZOOM < 0.1 )
			__.AUTOZOOM = 0.1;
		__.IS_REDRAW = true;
	});
	__.HTML.btn_lines.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		var qdata = QuadList[0];
		qdata.is_lines = ! qdata.is_lines;
		__.HTML.btn_lines.innerHTML = ( qdata.is_lines ) ? 'line' : 'tex';
		__.IS_REDRAW = true;
	});
	__.HTML.btn_selectall.addEventListener('click', function(){
		var dbg = __.HTML.debuglist.value;
		if ( dbg.indexOf('#') < 0 )
			dbg = 0;
		button_select_layers(dbg);
		__.IS_REDRAW = true;
	});
	__.HTML.btn_selectnone.addEventListener('click', function(){
		var dbg = __.HTML.debuglist.value;
		if ( dbg.indexOf('#') < 0 )
			dbg = 0;
		button_unselect_layers(dbg);
		__.IS_REDRAW = true;
	});

	function render(){
		requestAnimationFrame(render);
		if ( ! QuadList[0] || ! QuadList[0].name )
			return;
		var qdata = QuadList[0];

		// update/redraw only when changed
		if ( __.IS_REDRAW || QUAD.gl.is_canvas_resized() ){
			__.CAMERA = QUAD.func.viewer_camera(qdata, __.AUTOZOOM);

			QUAD.func.qdata_clear(qdata);
			keydebug_draw(qdata, __.CAMERA, __.COLOR);
			__.IS_REDRAW = false;
		}
	}
	render();
})();
</script>

</body></html>
