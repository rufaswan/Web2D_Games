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
		<button id='btn_hits'>hitbox</button>
		<button id='btn_lines'>lines</button>
	</div>
	<div id='viewer_bottom_nav'>
		<button id='btn_prev2'>&lt;&lt;</button>

		<button id='btn_prev'>&lt;</button>
		<button id='btn_cur'>0</button>
		<button id='btn_next'>&gt;</button>

		<button id='btn_next2'>&gt;&gt;</button>
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

	qdata_attach(QuadList[0], type, id);
	displayViewer(HTML, true);
}

function button_export_png( qdata, out, size ){
	if ( ['keyframe','hitbox','slot'].indexOf(qdata.attach.type) !== -1 )
		return;
	var camera = [1,0,0,0 , 0,-1,0,0 , 0,0,1,0 , 0,0,0,1];
	var color  = [1,1,1,1];
	var maxsz = QUAD.gl.maxTextureSize() >> 1;
	var maxps = maxsz * 0.5;

	var framebuf = QUAD.gl.canvasBuffer(0);
	var tex      = QUAD.gl.createPixel (0, maxsz, maxsz);
	HTML.canvas.width  = maxsz;
	HTML.canvas.height = maxsz;
	QUAD.gl.framebufferTexture2D(framebuf, tex.tex);

	//var half = [size[0]*0.5 , size[1]*0.5];
	var pos  = [
		-maxps + (size[0] * 0.5),
		 maxps - (size[1] * 0.5),
	];
	qdata.anim_fps = 0;
	var is_skip = false;
	for ( var y = pos[1] ; y > pos[0]; y -= size[1] ){
		if ( (y-size[1]) < pos[0] )  continue;
		if ( (y+size[1]) > pos[1] )  continue;
		if ( is_skip )  continue;

		for ( var x = pos[0]; x < pos[1]; x += size[0] ){
			if ( (x-size[0]) < pos[0] )  continue;
			if ( (x+size[0]) > pos[1] )  continue;
			if ( is_skip )  continue;

			camera[0+3] = x;
			camera[4+3] = y;

			QUAD.func.qdata_draw(qdata, camera, color);
			if ( ! qdata.is_draw )
				is_skip = true;
			qdata.anim_fps++;
		} // for ( var x = pos[0]; x < pos[1]; x += size[0] )
	} // for ( var y = pos[1]; y > pos[0]; y -= size[1] )

	var a = document.createElement('a');
	a.href = QUAD.gl.texture2DtoDataURL(tex.tex, tex.w, tex.h);
	a.setAttribute('download', out + '.png');
	a.setAttribute('target'  , '_blank');
	a.click();

	return QUAD.gl.canvasBuffer(1);
}

function button_export_zip( qdata, out, size ){
	if ( ['keyframe','hitbox','slot'].indexOf(qdata.attach.type) !== -1 )
		return;
	var camera = [1,0,0,0 , 0,-1,0,0 , 0,0,1,0 , 0,0,0,1];
	var color  = [1,1,1,1];
	var len = 60; // 60 fps for 1 secs

	var list = {};
	var framebuf = QUAD.gl.canvasBuffer(0);
	var tex      = QUAD.gl.createPixel (0, size[0], size[1]);
	HTML.canvas.width  = tex.w;
	HTML.canvas.height = tex.h;
	QUAD.gl.framebufferTexture2D(framebuf, tex.tex);

	for ( var i=0; i < len; i++ ){
		var pad = '00000000' + i + '.png';
		var fn  = pad.substring( pad.length - 8 );

		qdata.anim_fps = i;
		QUAD.gl.clear();
		QUAD.func.qdata_draw(qdata, camera, color);
		if ( ! qdata.is_draw )
			break;

		var b64  = QUAD.gl.texture2DtoDataURL(tex.tex, tex.w, tex.h);
		list[fn] = QUAD.binary.fromBase64(b64);
	} // for ( var i=0; i < len; i++ )

	var zip = QUAD.binary.zipwrite(list);
	var a = document.createElement('a');
	a.href = 'data:application/zip;base64,' + QUAD.binary.toBase64(zip);
	a.setAttribute('download', out + '.zip');
	a.setAttribute('target'  , '_blank');
	a.click();

	return QUAD.gl.canvasBuffer(1);
}

function button_export( type, elem ){
	// backup
	var qdata = QuadList[0];
	var bak = {
		type  : qdata.attach.type  ,
		id    : qdata.attach.id    ,
		fps   : qdata.anim_fps     ,
		line  : qdata.is_lines     ,
		hit   : qdata.is_hits      ,
		canvw : HTML.canvas.width  ,
		canvh : HTML.canvas.height ,
	};

	var par2 = elem.parentElement.parentElement;
	qdata.attach.type = par2.getAttribute('data-type');
	qdata.attach.id   = par2.getAttribute('data-id') | 0;
	qdata.is_lines    = false;
	qdata.is_hits     = false;
	QUAD.func.log('export ' + type + ' = ' + qdata.attach.type + '/' + qdata.attach.id);

	var size = [256,256];
	var out = qdata.name + '_' + qdata.attach.type + '_' + qdata.attach.id;
	switch ( type ){
		case 'png':  button_export_png(qdata, out, size); break;
		case 'zip':  button_export_zip(qdata, out, size); break;
	} // switch ( type )

	// restore
	qdata.attach.type  = bak.type;
	qdata.attach.id    = bak.id;
	qdata.anim_fps     = bak.fps;
	qdata.is_lines     = bak.line;
	qdata.is_hits      = bak.hit;
	HTML.canvas.width  = bak.canvw;
	HTML.canvas.height = bak.canvh;
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
							t.btn1 = '<button onclick="button_export(\'png\', this);">png</button>';
							t.btn2 = '<button onclick="button_export(\'zip\', this);">zip</button>';

							var tr = document.createElement('tr');
							tr.setAttribute('data-type', qv);
							tr.setAttribute('data-id'  , k);
							if ( ['keyframe','hitbox','slot'].indexOf(qv) === -1 )
								tr.innerHTML = '<td>' + t.p + '</td><td>' + t.btn1 + t.btn2 + '</td>';
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
		HTML.btn_lines.innerHTML = ( QuadList[0].is_lines ) ? 'lines' : 'texture';
	});
	HTML.btn_hits.addEventListener('click', function(){
		if ( ! QuadList[0] )
			return;
		QuadList[0].is_hits = ! QuadList[0].is_hits;
	});

	var IS_BTN_CLICK = 0;
	HTML.btn_prev.addEventListener('click', function(){
		IS_BTN_CLICK = 0;
		btnPrevNext(QuadList[0], -1);
	});
	HTML.btn_next.addEventListener('click', function(){
		IS_BTN_CLICK = 0;
		btnPrevNext(QuadList[0], 1);
	});
	HTML.btn_prev2.addEventListener('click', function(){
		if ( IS_BTN_CLICK === 0 )
			IS_BTN_CLICK = -1;
		else
			IS_BTN_CLICK = 0;
	});
	HTML.btn_next2.addEventListener('click', function(){
		if ( IS_BTN_CLICK === 0 )
			IS_BTN_CLICK = 1;
		else
			IS_BTN_CLICK = 0;
	});

	var FPS_DRAW = 0;
	var CAMERA = QUAD.gl.canvasSpace(0 , 0.5 , 1);
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
		}

		// redraw only when changed
		if ( QUAD.func.isChanged(qdata) ){
			CAMERA = QUAD.gl.canvasSpace(0 , 0.5 , 1);
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
