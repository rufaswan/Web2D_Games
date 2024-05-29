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
<title>Quad Player - Mobile</title>
@@<player-mobile.css>@@
@@<player-mobile.js>@@
@@<quad.js>@@
</head><body>

<div id='debugger'>
	<p id='debugger_top_nav'>
		<input type='file' id='input_file' multiple class='hidden'>
		<button id='btn_view'>view</button>
		<button id='btn_upload' data-id='0'>upload</button>
	</p>

	<button>dummy</button>
	<h1 id='quad_version'></h1>
	<h2>Files</h2>
	<ol id='debugger_files'></ol>
	<div id='quad_data'></div>
	<h3>Logs</h3>
	<textarea id='logger' disabled></textarea>
	<p>
		<a href='https://rufaswan.github.io/Web2D_Games/quad_player_mobile/player-mobile.tpl.html' target='_blank'>Latest version</a> -
		<a href='https://rufaswan.github.io/Web2D_Games/quad_player_mobile/spec.html' target='_blank'>QUAD File Spec</a> -
		<a href='https://github.com/rufaswan/Web2D_Games' target='_blank'>Github</a>
	</p>

	<div id='export_menu' data-type='' data-id=''>
		<p><button onclick='button_close(this);'>close</button> Export Menu</p>
		<p><span id='export_name'></span></p>
		<p>
			START = <span id='export_start'>0</span>
			<input id='export_range' type='range' min='0' max='0' step='1' value='0'>
		</p>
		<p>
			ZOOM = <span id='export_zoom'>1.0</span>
			<input id='export_times' type='range' min='0.1' max='10.0' step='0.1' value='1.0'>
		</p>
		<p>
			<button onclick='button_export_type(this);'>png</button>
			<button onclick='button_export_type(this);'>zip</button>
			<button onclick='button_export_type(this);'>rgba</button>
		</p>
	</div>
</div>

<div id='viewer'>
	@@<bg-q3.png>@@
	<canvas id='canvas'>Canvas not supported</canvas>
	<p id='viewer_top_nav'>
		<button id='btn_debug'>debug</button>
		<button id='btn_lines'>line</button>

		<button id='btn_hitattr'  class='btn_on' title='hitbox layers'>hits</button>
		<button id='btn_keyattr'  title='keyframe layers' >keys</button>
		<button id='btn_colorize' title='custom colors'>color</button>

		<button id='btn_flipx' class='btn_off'>X</button>
		<button id='btn_flipy' class='btn_off'>Y</button>
		<button id='btn_autozoom' class='btn_on' title='autozoom'>zoom</button>
	</p>
	<p id='viewer_bottom_nav'>
		<button id='btn_prev'>&lt;&lt;</button>

		<button id='btn_autonext'>auto</button>
		<button id='btn_cur'>0</button>

		<button id='btn_next'>&gt;&gt;</button>
	</p>

	<div id='colorize_menu'>
		<p><button onclick='button_close(this);'>close</button> Colorize Menu</p>
		<p id='colorize_list'></p>
	</div>
	<div id='keyattr_menu'>
		<p><button onclick='button_close(this);'>close</button> Keyframe Attribute Menu</p>
		<p id='keyattr_list'></p>
	</div>
	<div id='hitattr_menu'>
		<p><button onclick='button_close(this);'>close</button> Hitbox Attribute Menu</p>
		<p id='hitattr_list'></p>
	</div>
</div>

<script>
'use strict';

var __ = {
	HTML          : get_html_id(),
	SELECTED      : '',
	UPLOAD_ID     : -1,
	AUTOZOOM      : -1,
	IS_BTN_CLICK  : 0,
	IS_AUTONEXT   : false,
	IS_REDRAW     : true,
	IS_VIEWER_NAV : true,
	FPS_DRAW      : 0,
	CAMERA        : QUAD.math.matrix4(),
	COLOR         : [1,1,1,1],
};
var QuadList = [];

(function(){
	if ( ! QUAD.gl.init(__.HTML.canvas) )
		return;

	document.title += ' - ' + QUAD.version;
	__.HTML.quad_version.innerHTML = document.title;

	// BETWEEN DEBUGGER-VIEWER
	__.HTML.btn_view.addEventListener('click', function(){
		display_viewer(__.HTML, true);
		__.IS_REDRAW = true;
	});
	__.HTML.btn_debug.addEventListener('click', function(){
		display_viewer(__.HTML, false);
		__.HTML.logger.innerHTML = QUAD.func.console();
	});

	__.HTML.btn_upload.addEventListener('click', function(){
		__.UPLOAD_ID = this.getAttribute('data-id');
		__.HTML.input_file.click();
		__.HTML.logger.innerHTML = QUAD.func.console();
	});
	__.HTML.input_file.addEventListener('change', function(){
		QUAD.func.log('QuadList[]', __.UPLOAD_ID);
		if ( QUAD.func.is_undef( QuadList[ __.UPLOAD_ID ] ) )
			QuadList[ __.UPLOAD_ID ] = new QuadData(QuadList);
		var qdata = QuadList[ __.UPLOAD_ID ];

		var proall = [];
		for ( var up of this.files )
			proall.push( QUAD.func.upload_promise(up, qdata) );

		Promise.all(proall).then(function(resolve){
			qdata_filetable(qdata, __.HTML.debugger_files);
			if ( qdata.name ){
				__.HTML.quad_data.innerHTML = '';
				document.title = '[' + qdata.name + '] ' + __.HTML.quad_version.innerHTML;

				var buffer = qdata_tagtable(qdata.quad.tag);
				__.HTML.quad_data.innerHTML += buffer;

				var quad_main = quad_mainlist(qdata.quad);
				if ( quad_main === -1 )
					return;

				var buffer = '<h2>' + quad_main + '</h2>';
				buffer += '<ul>';
				qdata.quad[quad_main].forEach(function(v,k){
					if ( ! v )
						return;
					buffer += qdata_listing(qdata, quad_main, k);
				});
				buffer += '</ul>';
				__.HTML.quad_data.innerHTML += buffer;

				viewer_btn_menu(qdata);
			} // if ( qdata.name )
			__.HTML.logger.innerHTML = QUAD.func.console();
		});
	});
	__.HTML.export_range.addEventListener('change', function(){
		__.HTML.export_start.innerHTML = this.value;
	});
	__.HTML.export_times.addEventListener('change', function(){
		__.HTML.export_zoom.innerHTML = this.value;
	});

	// VIEWER
	__.HTML.canvas.addEventListener('click', function(){
		if ( __.IS_VIEWER_NAV ) {
			__.HTML.viewer_top_nav.style.display    = 'none';
			__.HTML.viewer_bottom_nav.style.display = 'none';
		} else {
			__.HTML.viewer_top_nav.style.display    = 'flex';
			__.HTML.viewer_bottom_nav.style.display = 'flex';
		}
		__.IS_VIEWER_NAV = ! __.IS_VIEWER_NAV;
	});
	__.HTML.btn_lines.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		QuadList[0].is_lines = ! QuadList[0].is_lines;
		__.HTML.btn_lines.innerHTML = ( QuadList[0].is_lines ) ? 'line' : 'tex';
		__.IS_REDRAW = true;
	});
	__.HTML.btn_hitattr.addEventListener('click', function(){
		qdata_toggle(QuadList[0], this, 'is_hits');
		__.IS_REDRAW = true;
	});
	__.HTML.btn_colorize.addEventListener('click', function(){
		__.HTML.colorize_menu.style.display = 'block';
		__.IS_REDRAW = true;
	});
	__.HTML.btn_keyattr.addEventListener('click', function(){
		__.HTML.keyattr_menu.style.display = 'block';
		__.IS_REDRAW = true;
	});
	__.HTML.btn_flipx.addEventListener('click', function(){
		qdata_toggle(QuadList[0], this, 'is_flipx');
		__.IS_REDRAW = true;
	});
	__.HTML.btn_flipy.addEventListener('click', function(){
		qdata_toggle(QuadList[0], this, 'is_flipy');
		__.IS_REDRAW = true;
	});
	__.HTML.btn_autozoom.addEventListener('click', function(){
		if ( this.classList.contains('btn_on') ){
			button_toggle(this, -1);
			__.AUTOZOOM = 1;
		} else {
			button_toggle(this, 1);
			__.AUTOZOOM = -1;
		}
		if ( QuadList[0] )
			QuadList[0].zoom = __.AUTOZOOM;
		__.IS_REDRAW = true;
	});

	__.HTML.btn_prev.addEventListener('click', function(){
		if ( __.IS_AUTONEXT ){
			button_toggle(__.HTML.btn_next, -1);
			if ( __.HTML.btn_prev.classList.contains('btn_on') ){
				button_toggle(__.HTML.btn_prev, -1);
				__.IS_BTN_CLICK = 0;
			} else {
				button_toggle(__.HTML.btn_prev, 1);
				__.IS_BTN_CLICK = -1;
			}
		} else
			__.IS_BTN_CLICK = -1;
	});
	__.HTML.btn_next.addEventListener('click', function(){
		if ( __.IS_AUTONEXT ){
			button_toggle(__.HTML.btn_prev, -1);
			if ( __.HTML.btn_next.classList.contains('btn_on') ){
				button_toggle(__.HTML.btn_next, -1);
				__.IS_BTN_CLICK = 0;
			} else {
				button_toggle(__.HTML.btn_next, 1);
				__.IS_BTN_CLICK = 1;
			}
		} else
			__.IS_BTN_CLICK = 1;
	});
	__.HTML.btn_autonext.addEventListener('click', function(){
		if ( __.IS_AUTONEXT ){
			button_toggle(__.HTML.btn_prev, 0);
			button_toggle(__.HTML.btn_next, 0);
			__.IS_AUTONEXT = false;
		} else {
			button_toggle(__.HTML.btn_prev, -1);
			button_toggle(__.HTML.btn_next, -1);
			__.IS_AUTONEXT = true;
		}
	});

	function render(){
		requestAnimationFrame(render);
		if ( __.HTML.viewer.style.display !== 'block' )
			return;
		if ( ! QuadList[0] || ! QuadList[0].name )
			return;
		var qdata = QuadList[0];

		// auto forward by 60/8 fps = 7.5 fps
		if ( (__.FPS_DRAW & 7) === 0 ){
			button_prev_next(qdata, __.IS_BTN_CLICK);
			if ( __.IS_BTN_CLICK !== 0 )
				__.IS_REDRAW = true;
			if ( ! __.IS_AUTONEXT )
				__.IS_BTN_CLICK = 0;
		}

		// update/redraw only when changed
		if ( __.IS_REDRAW || QUAD.gl.is_canvas_resized() ){
			__.CAMERA = QUAD.func.viewer_camera(qdata, __.AUTOZOOM);
			__.HTML.btn_cur.innerHTML = qdata.attach.id + '/' + qdata.anim_fps;
			__.HTML.logger.innerHTML  = QUAD.func.console();

			QUAD.func.qdata_clear(qdata);
			QUAD.func.qdata_draw(qdata, __.CAMERA, __.COLOR);
			if ( ! qdata.is_draw ){
				__.HTML.btn_cur.innerHTML = 'END';
			}
			__.IS_REDRAW = false;
		}

		__.FPS_DRAW = (__.FPS_DRAW + 1) & 0xff;
	}
	render();
})();
</script>

</body></html>
