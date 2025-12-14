<!DOCTYPE html>
@@license.txt@@
<html xmlns='http://www.w3.org/1999/xhtml'><head>

<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1' />
<title>Quad Debugger - Keyframe</title>
@@<common.js>@@
@@<debugger-keyframe.css>@@
@@<debugger-keyframe.js>@@
@@<quad.js>@@
</head><body>

<main>
	<nav>
		<input type='file' id='input_file' multiple='multiple' class='hidden' />
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
			<h1 id='quad_version'>version</h1>
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

APP.html         = APP.get_html_id();
APP.upload_queue = []; // { id:int , name:string , data:string }
APP.upload_id    = -1;
APP.autozoom     = 1.0;
APP.is_redraw    = true;
APP.camera       = QUAD.math.matrix4();
APP.color        = [1,1,1,1];
APP.QuadList     = [];

APP.on_key       = -1;
APP.on_layer     = [];

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

		var proall = [];
		for ( var up of this.files ){
			var p = QUAD.func.upload_promise(up, APP.upload_id, APP.upload_queue);
			proall.push(p);
		}

		Promise.all(proall).then(function(res){
			return APP.process_uploads();
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
			//QUAD.draw.qdata_draw(qdata, APP.camera, APP.color);
			APP.is_redraw = false;
		}
	}
	render();
})();
</script>

</body></html>
