<!DOCTYPE html>
@@license.txt@@
<html xmlns='http://www.w3.org/1999/xhtml'><head>

<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1' />
<title>Quad Player - Mobile</title>
@@<common.js>@@
@@<player-mobile.css>@@
@@<player-mobile.js>@@
@@<quad.js>@@
</head><body>

<div id='debugger'>
	<p id='debugger_top_nav'>
		<input type='file' id='input_file' multiple='multiple' class='hidden' />
		<button id='btn_view'>view</button>
		<button id='btn_upload' data-id='0'>upload</button>
	</p>

	<div id='debugger_top_dummy'>&nbsp;</div>
	<h1 id='quad_version'>version</h1>
	<div class='div_range'>
		<label for='bgcontrast_range'>BG Contrast = </label>
		<span>-</span>
		<input id='bgcontrast_range' type='range' onchange='div_range_span(this);' min='0' max='255' step='1' value='18' />
	</div>
	<h2>Files</h2>
	<ol id='debugger_files'></ol>
	<div id='quad_data'></div>
	<h3>Logs</h3>
	<textarea id='logger' disabled='disabled'></textarea>
	<p>
		<a href='https://rufaswan.github.io/Web2D_Games/quad_player_mobile/player-mobile.tpl.html' target='_blank'>Latest version</a> -
		<a href='https://rufaswan.github.io/Web2D_Games/quad_player_mobile/spec.html' target='_blank'>QUAD File Spec</a> -
		<a href='https://github.com/rufaswan/Web2D_Games' target='_blank'>Github</a>
	</p>

	<div id='export_menu' data-type='' data-id=''>
		<p><button onclick='button_close(this);'>close</button> Export Menu</p>
		<p><span id='export_name'></span></p>
		<div class='div_range'>
			<label for='export_range'>START = </label>
			<span>-</span>
			<input id='export_range' type='range' onchange='div_range_span(this);' min='0' max='0' step='1' value='0' />
		</div>
		<div class='div_range'>
			<label for='export_times'>ZOOM = </label>
			<span>-</span>
			<input id='export_times' type='range' onchange='div_range_span(this);' min='0.25' max='4.0' step='0.01' value='1.0' />
		</div>
		<p>
			<button onclick='button_export_type(this);'>png</button>
			<button onclick='button_export_type(this);'>zip</button>
			<button onclick='button_export_type(this);'>rgba</button>
		</p>
	</div>
</div>

<div id='viewer'>
	<div id='viewer_xline'></div>
	<div id='viewer_yline'></div>
	<canvas id='canvas'>Canvas not supported</canvas>
	<nav id='viewer_top_nav'>
		<button id='btn_debug'>debug</button>
		<button id='btn_lines'>line</button>

		<button id='btn_ellip'>&vellip;</button>
		<nav class='hidden'>
			<button id='btn_hitattr'  class='btn_on' title='hitbox layers'>hits</button>
			<button id='btn_keyattr'  title='keyframe layers' >keys</button>
			<button id='btn_colorize' title='custom colors'>color</button>

			<button id='btn_flipx' class='btn_off'>X</button>
			<button id='btn_flipy' class='btn_off'>Y</button>
			<button id='btn_autozoom' class='btn_on' title='autozoom'>zoom</button>
		</nav>
	</nav>
	<nav id='viewer_bottom_nav'>
		<button id='btn_prev'>&lt;&lt;</button>

		<button id='btn_autonext'>auto</button>
		<button id='btn_cur'>0</button>

		<button id='btn_next'>&gt;&gt;</button>
	</nav>

	<nav id='colorize_menu'>
		<p><button onclick='button_close(this);'>close</button> Colorize Menu</p>
		<p id='colorize_list'></p>
	</nav>
	<nav id='keyattr_menu'>
		<p><button onclick='button_close(this);'>close</button> Keyframe Attribute Menu</p>
		<p id='keyattr_list'></p>
	</nav>
	<nav id='hitattr_menu'>
		<p><button onclick='button_close(this);'>close</button> Hitbox Attribute Menu</p>
		<p id='hitattr_list'></p>
	</nav>
</div>

<script>
'use strict';

APP.html          = APP.get_html_id();
APP.upload_queue  = []; // { id:int , name:string , data:string }
APP.upload_id     = -1;
APP.autozoom      = -1;
APP.is_redraw     = true;
APP.camera        = QUAD.math.matrix4();
APP.color         = [1,1,1,1];
APP.QuadList      = [];

APP.selected      = '';
APP.is_btn_click  = 0;
APP.is_autonext   = false;
APP.is_viewer_nav = true;
APP.fps_draw      = 0;

(function(){
	if ( ! QUAD.gl.init(APP.html.canvas) )
		return;

	document.title += ' - ' + QUAD.version;
	APP.html.quad_version.innerHTML = document.title;

	div_range_span(APP.html.bgcontrast_range);
	div_range_span(APP.html.export_range);
	div_range_span(APP.html.export_times);

	// BETWEEN DEBUGGER-VIEWER
	APP.html.btn_view.addEventListener('click', function(){
		APP.display_viewer(APP.html, true);
		APP.is_redraw = true;
	});
	APP.html.btn_debug.addEventListener('click', function(){
		APP.display_viewer(APP.html, false);
		APP.html.logger.innerHTML = QUAD.func.console();
	});

	APP.html.btn_upload.addEventListener('click', function(){
		APP.upload_id = this.getAttribute('data-id');
		APP.html.input_file.click();
		APP.html.logger.innerHTML = QUAD.func.console();
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
	APP.html.bgcontrast_range.addEventListener('change', function(){
		var light = this.value & 0xff;
		var dark  = 0xff - light;
		var root  = document.documentElement;
		root.style.setProperty('--body-bg-color'   , `rgb(${light},${light},${light})`);
		root.style.setProperty('--body-text-color' , `rgb(${dark} ,${dark} ,${dark} )`);
		if ( light & 0x80 ){
			root.style.setProperty('--button-bg-color'   , '#121212');
			root.style.setProperty('--button-text-color' , '#ededed');
		}
		else {
			root.style.setProperty('--button-bg-color'   , '#ededed');
			root.style.setProperty('--button-text-color' , '#121212');
		}
	});

	// VIEWER
	APP.html.canvas.addEventListener('click', function(){
		if ( APP.is_viewer_nav ) {
			APP.html.viewer_top_nav.style.display    = 'none';
			APP.html.viewer_bottom_nav.style.display = 'none';
		} else {
			APP.html.viewer_top_nav.style.display    = 'flex';
			APP.html.viewer_bottom_nav.style.display = 'flex';
		}
		APP.is_viewer_nav = ! APP.is_viewer_nav;
	});
	APP.html.btn_ellip.addEventListener('click', function(){
		var nav = APP.html.viewer_top_nav.getElementsByTagName('nav');
		if ( nav[0].classList.contains('hidden') )
			nav[0].classList.remove('hidden');
		else
			nav[0].classList.add('hidden');
	});
	APP.html.btn_lines.addEventListener('click', function(){
		if ( ! APP.QuadList[0] )
			return;
		APP.QuadList[0].is_lines = ! APP.QuadList[0].is_lines;
		APP.html.btn_lines.innerHTML = ( APP.QuadList[0].is_lines ) ? 'line' : 'tex';
		APP.is_redraw = true;
	});
	APP.html.btn_hitattr.addEventListener('click', function(){
		APP.qdata_toggle(APP.QuadList[0], this, 'is_hits');
		APP.is_redraw = true;
	});
	APP.html.btn_colorize.addEventListener('click', function(){
		APP.html.colorize_menu.style.display = 'block';
		APP.is_redraw = true;
	});
	APP.html.btn_keyattr.addEventListener('click', function(){
		APP.html.keyattr_menu.style.display = 'block';
		APP.is_redraw = true;
	});
	APP.html.btn_flipx.addEventListener('click', function(){
		APP.qdata_toggle(APP.QuadList[0], this, 'is_flipx');
		APP.is_redraw = true;
	});
	APP.html.btn_flipy.addEventListener('click', function(){
		APP.qdata_toggle(APP.QuadList[0], this, 'is_flipy');
		APP.is_redraw = true;
	});
	APP.html.btn_autozoom.addEventListener('click', function(){
		if ( this.classList.contains('btn_on') ){
			APP.button_toggle(this, -1);
			APP.autozoom = 1;
		} else {
			APP.button_toggle(this, 1);
			APP.autozoom = -1;
		}
		if ( APP.QuadList[0] )
			APP.QuadList[0].zoom = APP.autozoom;
		APP.is_redraw = true;
	});

	APP.html.btn_prev.addEventListener('click', function(){
		if ( APP.is_autonext ){
			APP.button_toggle(APP.html.btn_next, -1);
			if ( APP.html.btn_prev.classList.contains('btn_on') ){
				APP.button_toggle(APP.html.btn_prev, -1);
				APP.is_btn_click = 0;
			} else {
				APP.button_toggle(APP.html.btn_prev, 1);
				APP.is_btn_click = -1;
			}
		} else
			APP.is_btn_click = -1;
	});
	APP.html.btn_next.addEventListener('click', function(){
		if ( APP.is_autonext ){
			APP.button_toggle(APP.html.btn_prev, -1);
			if ( APP.html.btn_next.classList.contains('btn_on') ){
				APP.button_toggle(APP.html.btn_next, -1);
				APP.is_btn_click = 0;
			} else {
				APP.button_toggle(APP.html.btn_next, 1);
				APP.is_btn_click = 1;
			}
		} else
			APP.is_btn_click = 1;
	});
	APP.html.btn_autonext.addEventListener('click', function(){
		if ( APP.is_autonext ){
			APP.button_toggle(APP.html.btn_prev, 0);
			APP.button_toggle(APP.html.btn_next, 0);
			APP.is_autonext = false;
		} else {
			APP.button_toggle(APP.html.btn_prev, -1);
			APP.button_toggle(APP.html.btn_next, -1);
			APP.is_autonext = true;
		}
	});

	function render(){
		requestAnimationFrame(render);
		if ( APP.html.viewer.style.display !== 'block' )
			return;
		if ( ! APP.QuadList[0] || ! APP.QuadList[0].name )
			return;
		var qdata = APP.QuadList[0];

		// auto forward by 60/8 fps = 7.5 fps
		if ( (APP.fps_draw & 7) === 0 ){
			APP.button_prev_next(qdata, APP.is_btn_click);
			if ( APP.is_btn_click !== 0 )
				APP.is_redraw = true;
			if ( ! APP.is_autonext )
				APP.is_btn_click = 0;
		}

		// update/redraw only when changed
		if ( APP.is_redraw || QUAD.gl.is_canvas_resized() ){
			APP.camera = QUAD.func.viewer_camera(qdata, APP.autozoom);
			APP.html.btn_cur.innerHTML = qdata.attach.id + '/' + qdata.anim_fps;
			APP.html.logger.innerHTML  = QUAD.func.console();

			QUAD.func.qdata_clear(qdata);
			QUAD.func.qdata_draw(qdata, APP.camera, APP.color);
			if ( ! qdata.is_draw ){
				APP.html.btn_cur.innerHTML = 'END';
			}
			APP.is_redraw = false;
		}

		APP.fps_draw = (APP.fps_draw + 1) & 0xff;
	}
	render();
})();
</script>

</body></html>
