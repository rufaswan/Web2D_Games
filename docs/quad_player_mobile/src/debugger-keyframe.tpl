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
		<div id='viewer_xline'></div>
		<div id='viewer_yline'></div>
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

APP.html      = APP.get_html_id();
APP.upload_id = -1;
APP.on_key    = -1;
APP.on_layer  = [];
APP.is_redraw = true;
APP.autozoom  = 1.0;
APP.camera    = QUAD.math.matrix4();
APP.color     = [1,1,1,1];
APP.QuadList  = [];

(function(){
	if ( ! QUAD.gl.init(APP.html.canvas) )
		return;

	document.title += ' - ' + QUAD.version;
	APP.html.quad_version.innerHTML = document.title;

	// BETWEEN DEBUGGER-VIEWER
	APP.html.btn_upload.addEventListener('click', function(){
		APP.upload_id = this.getAttribute('data-id');
		APP.html.input_file.click();
	});
	APP.html.input_file.addEventListener('change', function(){
		QUAD.func.log('QuadList[]', APP.upload_id);
		if ( QUAD.func.is_undef( APP.QuadList[ APP.upload_id ] ) )
			APP.QuadList[ APP.upload_id ] = new QuadData(APP.QuadList);
		var qdata = APP.QuadList[ APP.upload_id ];

		var proall = [];
		for ( var up of this.files )
			proall.push( QUAD.func.upload_promise(up, qdata) );

		Promise.all(proall).then(function(resolve){
			APP.qdata_filetable(qdata, APP.html.debugger_files);
			if ( qdata.name ){
				APP.html.keylist.innerHTML = '';
				document.title = '[' + qdata.name + '] ' + APP.html.quad_version.innerHTML;

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
				APP.html.keylist.innerHTML = buffer;
			} // if ( qdata.name )
			APP.autozoom  = 1.0;
			APP.is_redraw = true;
		});
	});

	// VIEWER
	APP.html.btn_zoomin.addEventListener('click', function(){
		APP.autozoom *= 1.1;
		if ( APP.autozoom > 10.0 )
			APP.autozoom = 10.0;
		APP.is_redraw = true;
	});
	APP.html.btn_zoomout.addEventListener('click', function(){
		APP.autozoom /= 1.1;
		if ( APP.autozoom < 0.1 )
			APP.autozoom = 0.1;
		APP.is_redraw = true;
	});
	APP.html.btn_lines.addEventListener('click', function(){
		if ( ! APP.QuadList[0] )
			return;
		var qdata = APP.QuadList[0];
		qdata.is_lines = ! qdata.is_lines;
		APP.html.btn_lines.innerHTML = ( qdata.is_lines ) ? 'line' : 'tex';
		APP.is_redraw = true;
	});
	APP.html.btn_selectall.addEventListener('click', function(){
		var dbg = APP.html.debuglist.value;
		if ( dbg.indexOf('#') < 0 )
			dbg = 0;
		APP.button_select_layers(dbg);
		APP.is_redraw = true;
	});
	APP.html.btn_selectnone.addEventListener('click', function(){
		var dbg = APP.html.debuglist.value;
		if ( dbg.indexOf('#') < 0 )
			dbg = 0;
		APP.button_unselect_layers(dbg);
		APP.is_redraw = true;
	});

	function render(){
		requestAnimationFrame(render);
		if ( ! APP.QuadList[0] || ! APP.QuadList[0].name )
			return;
		var qdata = APP.QuadList[0];

		// update/redraw only when changed
		if ( APP.is_redraw || QUAD.gl.is_canvas_resized() ){
			APP.camera = QUAD.func.viewer_camera(qdata, APP.autozoom);

			QUAD.func.qdata_clear(qdata);
			APP.keydebug_draw(qdata, APP.camera, APP.color);
			APP.is_redraw = false;
		}
	}
	render();
})();
</script>

</body></html>
