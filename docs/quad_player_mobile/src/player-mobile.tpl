<!doctype html>
[license]
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
[/license]
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Player - Mobile</title>
@@<player.css>@@
@@<player.js>@@
@@<quad.js>@@
</head><body>

<div id='debugger'>
	<input type='file' id='input_file' multiple class='hidden'>
	<div id='debugger_top_nav'>
		<button id='btn_view'>view</button>
		<button id='btn_upload' data-id='0'>upload</button>
	</div>
	<h1 id='quad_version'></h1>
	<h2>Files</h2>
	<ol id='debugger_files'></ol>
	<div id='quad_data'></div>
	<h3>Logs</h3>
	<textarea id='logger' disabled></textarea>
</div>

<div id='viewer'>
	@@<bg-q3.png>@@
	<canvas id='canvas'>Canvas not supported</canvas>
	<div id='viewer_top_nav'>
		<button id='btn_debug'>debug</button>
		<button id='btn_hits' class='btn_on'>hit</button>
		<button id='btn_autofit' class='btn_on'>fit</button>
		<button id='btn_flipx' class='btn_off'>X</button>
		<button id='btn_flipy' class='btn_off'>Y</button>
		<button id='btn_lines'>line</button>
	</div>
	<div id='viewer_bottom_nav'>
		<button id='btn_prev'>&lt;&lt;</button>

		<button id='btn_autonext'>auto</button>
		<button id='btn_cur'>0</button>

		<button id='btn_next'>&gt;&gt;</button>
	</div>
</div>

<script>
function button_select( elem ){
	if ( SELECTED )
		SELECTED.classList.remove('current');

	var par1 = elem.parentElement;
	SELECTED = par1;
	SELECTED.classList.add('current');

	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;

	var qdata = QuadList[0];
	qdata_attach(qdata, type, id);
	displayViewer(HTML, true);

	// canvas == [0,0] when VIEWER == display none
	qdata.zoom = QUAD.func.autofitZoom(qdata);
}

function button_export( elem ){
	var qdata = QuadList[0];
	var fmt   = elem.innerHTML.toLowerCase();

	var par2 = elem.parentElement.parentElement;
	var type = par2.getAttribute('data-type');
	var id   = par2.getAttribute('data-id') | 0;
	var fps  = qdata.anim_fps;
	QUAD.export.export(fmt, qdata, HTML.canvas, type, id, fps);
}

var HTML = getHtmlIds();
var QuadList = [];
var SELECTED = '';

(function(){
	if ( ! QUAD.gl.init(HTML.canvas) )
		return;

	HTML.quad_version.innerHTML = 'Quad Player ' + QUAD.version;

	// BETWEEN DEBUGGER-VIEWER
	HTML.btn_view.addEventListener('click', function(){
		displayViewer(HTML, true);
	});
	HTML.btn_debug.addEventListener('click', function(){
		displayViewer(HTML, false);
		HTML.logger.innerHTML = QUAD.func.console();
	});

	var UPLOAD_ID = -1;
	HTML.btn_upload.addEventListener('click', function(){
		UPLOAD_ID = this.getAttribute('data-id');
		HTML.input_file.click();
	});
	HTML.input_file.addEventListener('change', function(){
		QUAD.func.log('QuadList[]', UPLOAD_ID);
		if ( QuadList[ UPLOAD_ID ] === undefined )
			QuadList[ UPLOAD_ID ] = new QuadData(QuadList);
		var qdata = QuadList[ UPLOAD_ID ];

		var promises = [];
		for ( var up of this.files )
			promises.push( QUAD.func.uploadPromise(up, qdata) );

		Promise.all(promises).then(function(resolve){
			HTML.debugger_files.innerHTML = '';
			if ( qdata.name )
				HTML.debugger_files.innerHTML += '<li>[QUAD] ' + qdata.name + '</li>';
			[0,1,2,3].forEach(function(v){
				if ( qdata.IMAGE[v].name )
				HTML.debugger_files.innerHTML += '<li>[IMAGE][' + v + '] ' + qdata.IMAGE[v].name + '</li>';
			});

			if ( qdata.name ){
				HTML.quad_data.innerHTML = '';
				document.title = qdata.name + ' [Quad Player Mobile]';

				qdata_tagtable( qdata.QUAD.tag, HTML.quad_data );

				['skeleton','animation','keyframe','hitbox','slot'].forEach(function(qv,qk){
					if ( qdata.QUAD[qv] ){
						HTML.quad_data.innerHTML += '<h2>' + qv + '</h2>';

						var table = '<table>';
						qdata.QUAD[qv].forEach(function(v,k){
							if ( ! v )
								return;

							var t = {};
							t.name = v.name || qv + ' ' + k;
							t.p    = '<p onclick="button_select(this);">' + t.name + '</p>';
							t.btn  = '';
							t.btn += '<button onclick="button_export(this);">png</button>';
							t.btn += '<button onclick="button_export(this);">zip</button>';
							//t.btn += '<button onclick="button_export(this);">rgba</button>';

							var tr = document.createElement('tr');
							tr.setAttribute('data-type', qv);
							tr.setAttribute('data-id'  , k);
							if ( ['keyframe','hitbox','slot'].indexOf(qv) === -1 )
								tr.innerHTML = '<td>' + t.p + '</td><td>' + t.btn + '</td>';
							else
								tr.innerHTML = '<td>' + t.p + '</td>';

							table += tr.outerHTML;
						});
						table += '</table>';
						HTML.quad_data.innerHTML += table;
					}
				});
			} // if ( qdata.name )
		});
	});

	// VIEWER
	var HAS_VIEWER = 1;
	HTML.canvas.addEventListener('click', function(){
		if ( HAS_VIEWER ) {
			HTML.viewer_top_nav.style.display    = 'none';
			HTML.viewer_bottom_nav.style.display = 'none';
		} else {
			HTML.viewer_top_nav.style.display    = 'flex';
			HTML.viewer_bottom_nav.style.display = 'flex';
		}
		HAS_VIEWER = ! HAS_VIEWER;
	});
	HTML.btn_lines.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		QuadList[0].is_lines = ! QuadList[0].is_lines;
		HTML.btn_lines.innerHTML = ( QuadList[0].is_lines ) ? 'line' : 'tex';
	});
	HTML.btn_hits.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		if ( HTML.btn_hits.classList.contains('btn_on') ){
			btnToggle(HTML.btn_hits, -1);
			QuadList[0].is_hits = false;
		} else {
			btnToggle(HTML.btn_hits, 1);
			QuadList[0].is_hits = true;
		}
	});
	HTML.btn_autofit.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		if ( HTML.btn_autofit.classList.contains('btn_on') ){
			btnToggle(HTML.btn_autofit, -1);
			QuadList[0].zoom = 1;
		} else {
			btnToggle(HTML.btn_autofit, 1);
			QuadList[0].zoom = QUAD.func.autofitZoom(QuadList[0]);
		}
	});
	HTML.btn_flipx.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		if ( HTML.btn_flipx.classList.contains('btn_on') ){
			btnToggle(HTML.btn_flipx, -1);
			QuadList[0].is_flipx = false;
		} else {
			btnToggle(HTML.btn_flipx, 1);
			QuadList[0].is_flipx = true;
		}
	});
	HTML.btn_flipy.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		if ( HTML.btn_flipy.classList.contains('btn_on') ){
			btnToggle(HTML.btn_flipy, -1);
			QuadList[0].is_flipy = false;
		} else {
			btnToggle(HTML.btn_flipy, 1);
			QuadList[0].is_flipy = true;
		}
	});

	var IS_BTN_CLICK = 0;
	var IS_AUTONEXT  = false;
	HTML.btn_prev.addEventListener('click', function(){
		if ( IS_AUTONEXT ){
			btnToggle(HTML.btn_next, -1);
			if ( HTML.btn_prev.classList.contains('btn_on') ){
				btnToggle(HTML.btn_prev, -1);
				IS_BTN_CLICK = 0;
			} else {
				btnToggle(HTML.btn_prev, 1);
				IS_BTN_CLICK = -1;
			}
		} else
			IS_BTN_CLICK = -1;
	});
	HTML.btn_next.addEventListener('click', function(){
		if ( IS_AUTONEXT ){
			btnToggle(HTML.btn_prev, -1);
			if ( HTML.btn_next.classList.contains('btn_on') ){
				btnToggle(HTML.btn_next, -1);
				IS_BTN_CLICK = 0;
			} else {
				btnToggle(HTML.btn_next, 1);
				IS_BTN_CLICK = 1;
			}
		} else
			IS_BTN_CLICK = 1;
	});
	HTML.btn_autonext.addEventListener('click', function(){
		if ( IS_AUTONEXT ){
			btnToggle(HTML.btn_prev, 0);
			btnToggle(HTML.btn_next, 0);
			IS_AUTONEXT = false;
		} else {
			btnToggle(HTML.btn_prev, -1);
			btnToggle(HTML.btn_next, -1);
			IS_AUTONEXT = true;
		}
	});

	var FPS_DRAW = 0;
	var CAMERA = QUAD.math.matrix4();
	var COLOR  = [1,1,1,1];
	function render(){
		requestAnimationFrame(render);
		if ( HTML.viewer.style.display !== 'block' )
			return;
		if ( ! QuadList[0] || ! QuadList[0].name )
			return;
		var qdata = QuadList[0];

		// auto forward by 60/8 fps = 7.5 fps
		if ( (FPS_DRAW & 7) === 0 ){
			btnPrevNext(qdata, IS_BTN_CLICK);
			if ( ! IS_AUTONEXT )
				IS_BTN_CLICK = 0;
		}

		// redraw only when changed
		if ( QUAD.func.isChanged(qdata) ){
			CAMERA = QUAD.func.viewerCamera(qdata);
			HTML.btn_cur.innerHTML = qdata.attach.id + '/' + qdata.anim_fps;
			HTML.logger.innerHTML  = QUAD.func.console();

			QUAD.func.qdata_clear(qdata);
			QUAD.func.qdata_draw(qdata, CAMERA, COLOR);
			if ( ! qdata.is_draw ){
				HTML.btn_cur.innerHTML = 'END';
			}
		}

		FPS_DRAW = (FPS_DRAW + 1) & 0xff;
	}
	render();
})();
</script>

</body></html>
