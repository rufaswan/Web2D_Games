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

		<button id='btn_lines'>line</button>
		<button id='btn_zoomin'>+</button>
		<button id='btn_zoomout'>-</button>

		<button id='btn_selectall' class='hidden'>all</button>
		<button id='btn_selectnone' class='hidden'>none</button>
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
			<p><button onclick='layer_close();'>close</button> <span id='layer_name'></span></p>
			<ul id='layerlist'></ul>
		</div>

	</div>
</main>

<script>
var HTML = get_html_id();
var QuadList  = [];
var ON_KEY   = -1;
var ON_LAYER = [];
var IS_REDRAW = true;

(function(){
	if ( ! QUAD.gl.init(HTML.canvas) )
		return;

	document.title += ' - ' + QUAD.version;
	HTML.quad_version.innerHTML = document.title;

	// BETWEEN DEBUGGER-VIEWER
	var UPLOAD_ID = -1;
	HTML.btn_upload.addEventListener('click', function(){
		UPLOAD_ID = this.getAttribute('data-id');
		HTML.input_file.click();
	});
	HTML.input_file.addEventListener('change', function(){
		QUAD.func.log('QuadList[]', UPLOAD_ID);
		if ( QUAD.func.is_undef( QuadList[ UPLOAD_ID ] ) )
			QuadList[ UPLOAD_ID ] = new QuadData(QuadList);
		var qdata = QuadList[ UPLOAD_ID ];

		var promises = [];
		for ( var up of this.files )
			promises.push( QUAD.func.upload_promise(up, qdata) );

		Promise.all(promises).then(function(resolve){
			qdata_filetable(qdata, HTML.debugger_files);
			if ( qdata.name ){
				HTML.keylist.innerHTML = '';
				document.title = '[' + qdata.name + '] ' + HTML.quad_version.innerHTML;

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
				HTML.keylist.innerHTML = buffer;
			} // if ( qdata.name )
			IS_REDRAW = true;
		});
	});

	// VIEWER
	var AUTOZOOM = 1.0;
	HTML.btn_zoomin.addEventListener('click', function(){
		AUTOZOOM *= 1.1;
		if ( AUTOZOOM > 10.0 )
			AUTOZOOM = 10.0;
		IS_REDRAW = true;
	});
	HTML.btn_zoomout.addEventListener('click', function(){
		AUTOZOOM /= 1.1;
		if ( AUTOZOOM < 0.1 )
			AUTOZOOM = 0.1;
		IS_REDRAW = true;
	});
	HTML.btn_lines.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		QuadList[0].is_lines = ! QuadList[0].is_lines;
		HTML.btn_lines.innerHTML = ( QuadList[0].is_lines ) ? 'line' : 'tex';
		IS_REDRAW = true;
	});
	HTML.btn_selectall.addEventListener('click', function(){
		ON_LAYER = [];
		var list = document.querySelectorAll('#layerlist li');
		for ( var i=0; i < list.length; i++ ){
			var id = list[i].getAttribute('data-id') | 0;
			list[i].classList.add('layer_on');
			ON_LAYER.push(id);
		}
		IS_REDRAW = true;
	});
	HTML.btn_selectnone.addEventListener('click', function(){
		ON_LAYER = [];
		var list = document.querySelectorAll('#layerlist li');
		for ( var i=0; i < list.length; i++ ){
			list[i].classList.remove('layer_on');
		}
		IS_REDRAW = true;
	});

	var CAMERA = QUAD.math.matrix4();
	var COLOR  = [1,1,1,1];
	function render(){
		requestAnimationFrame(render);
		if ( ! QuadList[0] || ! QuadList[0].name )
			return;
		var qdata = QuadList[0];

		// update/redraw only when changed
		if ( QUAD.gl.is_canvas_resized() || IS_REDRAW ){
			CAMERA = QUAD.func.viewer_camera(qdata, AUTOZOOM);

			QUAD.func.qdata_clear(qdata);
			keydebug_draw(qdata, CAMERA, COLOR);
			IS_REDRAW = false;
		}
	}
	render();
})();
</script>

</body></html>
