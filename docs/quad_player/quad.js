/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
*/
'use strict';

var QUAD = QUAD || {};
//////////////////////////////
// init vars
(function(QUAD){

	QUAD.files = {};
	QUAD.webgl = {};
	QUAD.anim  = {};

	QUAD.init_files = function(){
		QUAD.files = {
			done   : 0,
			axis_x : 0,
			axis_y : 0,
			zoom   : 1.0,
			prefix : '',
			quad   : {},
			image  : {},
		};
	}

	QUAD.init_anim = function(){
		QUAD.anim = {
			cur_frame     : 0,
			cur_anim_key  : '',
			cur_anim_data : [],
			cur_anim_time : 0,
			cur_anim_stop : false,
			prev_anim_frame : '',
		};
		return;
	}

	QUAD.init_webgl = function(dom_canvas){
		QUAD.init_files();

		var option = {
			alpha                 : true,
			depth                 : false,
			stencil               : false,
			antialias             : true,
			premultipliedAlpha    : false,
			preserveDrawingBuffer : true,
		};

		QUAD.webgl.gl = dom_canvas.getContext('webgl', option) || dom_canvas.getContext('experiment-webgl', option);
		if ( ! QUAD.webgl.gl )
			return console.log('WebGL is disabled or not supported');
		QUAD.webgl.draw = 0;

		var GL = QUAD.webgl.gl;
		QUAD.webgl.maxtex = GL.getParameter(GL.MAX_VERTEX_TEXTURE_IMAGE_UNITS);
		console.log('MAX_VERTEX_TEXTURE_IMAGE_UNITS = ' + QUAD.webgl.maxtex);

		//////////////////////////////
		// SHADER //
		function new_shader(vert_src, frag_src){
			var vert_shader = GL.createShader(GL.VERTEX_SHADER);
			GL.shaderSource (vert_shader, vert_src);
			GL.compileShader(vert_shader);

			var frag_shader = GL.createShader(GL.FRAGMENT_SHADER);
			GL.shaderSource (frag_shader, frag_src);
			GL.compileShader(frag_shader);

			var shader_prog = GL.createProgram();
			GL.attachShader(shader_prog, vert_shader);
			GL.attachShader(shader_prog, frag_shader);
			GL.linkProgram (shader_prog);
			return shader_prog;
		}

		// standardized shader
		var vert_src = `
			attribute vec2  a_uv;
			attribute vec3  a_xyz;
			attribute vec4  a_color;
			varying   vec2  v_uv;
			varying   vec4  v_color;

			void main(void){
				v_uv    = a_uv;
				v_color = a_color;
				gl_Position = vec4(a_xyz, 1.0);
			}
		`;

		var frag_src = `
			precision highp float;
			uniform sampler2D u_tex;
			varying vec2      v_uv;
			varying vec4      v_color;

			void main(void){
				gl_FragColor = texture2D(u_tex, v_uv) * v_color;
			}
		`;
		QUAD.webgl.shader = new_shader(vert_src, frag_src);

		//////////////////////////////
		QUAD.webgl.buffer = {};
		var BUFFER = QUAD.webgl.buffer;
		BUFFER.uv  = GL.createBuffer();
		BUFFER.xyz = GL.createBuffer();
		BUFFER.clr = GL.createBuffer();
		BUFFER.idx = GL.createBuffer();
		return;
	}

}(QUAD));
//////////////////////////////
// dom click change
(function(QUAD){

	QUAD.file_reader = function(list, hidden, callback){
		QUAD.init_files();
		hidden.innerHTML = '';

		var done = 0;
		for ( var i=0; i < list.length; i++ ){
			(function(file){

				if ( file.name.match(/.*\.quad$/i) ){
					var reader = new FileReader;
					reader.onload = function(){
						QUAD.files.prefix = file.name.substr(0, file.name.lastIndexOf('.'));

						var tag = document.createElement('script');
						tag.innerHTML  = 'QUAD.files.quad = ' + reader.result + ';';
						tag.innerHTML += 'QUAD.files.done |= 1;';
						hidden.appendChild(tag);

						done++;
						return;
					}
					reader.readAsText(file);
				} // load *.quad

				if ( file.name.match(/.*\.png$/i) ){
					var reader = new FileReader;
					reader.onload = function(){
						var id = file.name.match(/\.([0-9]+)\./);
						var n = id[1];
						if ( n === undefined )
							return;

						var img = new Image;
						img.onload = function(){

							var GL = QUAD.webgl.gl;
							var texture = GL.createTexture();
							GL.bindTexture  (GL.TEXTURE_2D, texture);
							GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_S    , GL.CLAMP_TO_EDGE);
							GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_T    , GL.CLAMP_TO_EDGE);
							GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MIN_FILTER, GL.NEAREST);
							GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MAG_FILTER, GL.NEAREST);
							GL.texImage2D   (GL.TEXTURE_2D, 0, GL.RGBA, GL.RGBA, GL.UNSIGNED_BYTE, img);

							if ( QUAD.files.image[n] !== undefined && QUAD.files.image[n].tex !== undefined )
								GL.deleteTexture( QUAD.files.image[n].tex );

							QUAD.files.image[n] = {
								width  : img.width  - 1,
								height : img.height - 1,
								tex    : texture,
							}

							done++;
							QUAD.files.done |= 2;
							return;
						};
						img.src = reader.result;
					}
					reader.readAsDataURL(file);
				} // load *.png

			}(list[i]));
		} // for ( var i=0; i < list.length; i++ ){

		var timer = setInterval(function(){
			if ( list.length > done )
				return;
			clearInterval(timer);
			console.log(QUAD);

			QUAD.init_anim();
			callback();
		}, 100);
		return;
	}

	QUAD.html_idtag = function(dom){
		var tag = QUAD.files.quad.TAG;
		if ( tag === undefined )
			return;

		var html = '<table>';
		for ( var key in tag )
		{
			html += '<tr>';
			if ( tag.hasOwnProperty(key) ){
				html += '<td>' + key + '</td>';
				html += '<td>' + tag[key] + '</td>';
			}
			html += '</tr>';
		}
		html += '</table>';
		dom.innerHTML = html;
		return;
	}

	QUAD.save_png = function(id){
		if ( QUAD.files.prefix === '' )
			return;

		// Date.now()
		var fn = QUAD.files.prefix + '-' + id + '.png';
		var a  = document.createElement('a');
		var canvas = QUAD.webgl.gl.canvas;

		a.href = canvas.toDataURL('image/png');
		a.setAttribute('download', fn);
		a.click();
		return;
	}

	QUAD.zoom = function(int = 0){
		if ( int == 0 ){
			QUAD.files.zoom = 1.0;
			return;
		}
		if ( int > 0 ){
			// canvas become smaller = sprite become bigger
			QUAD.files.zoom -= 0.1;
			if ( QUAD.files.zoom < 0.1 )
				QUAD.files.zoom = 0.1;
			return;
		}
		if ( int < 0 ){
			// canvas become bigger = sprite become smaller
			QUAD.files.zoom += 0.1;
			if ( QUAD.files.zoom > 10.0 )
				QUAD.files.zoom = 10.0;
			return;
		}
		return;
	}

	QUAD.axis_x = function(){
		QUAD.files.axis_x += 0.1;
		if ( QUAD.files.axis_x > 1 )
			QUAD.files.axis_x = -1;
		return;
	}

	QUAD.axis_y = function(){
		QUAD.files.axis_y -= 0.1;
		if ( QUAD.files.axis_y < -1 )
			QUAD.files.axis_y = 1;
		return;
	}

	QUAD.resize_canvas = function(){
		var GL = QUAD.webgl.gl;
		GL.canvas.width  = GL.canvas.clientWidth;
		GL.canvas.height = GL.canvas.clientHeight;
		return;
	}

	QUAD.performance = function(d=1){
		if ( ! d )  return;
		setInterval(function(){
			console.log('draw/sec', QUAD.webgl.draw);
			QUAD.webgl.draw = 0;
		}, 1000);
		return;
	}
}(QUAD));
//////////////////////////////
// webgl frames
(function(QUAD){

	function render_framebuffer( texid, src, dst, clr, pos, fact, blend ){
		if ( src.length === 0 || dst.length === 0 || clr.length === 0 )
			return;
		//console.log(src.length, dst.length, clr.length, fact.length, blend);

		var GL      = QUAD.webgl.gl;
		var PROGRAM = QUAD.webgl.shader;
		var BUFFER  = QUAD.webgl.buffer;
		GL.useProgram(PROGRAM);

		function glAttr(attr, buf, data, cnt){
			GL.deleteBuffer(buf);
			if ( data.length == 0 )
				return;

			var loc = GL.getAttribLocation(PROGRAM, attr);
			buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(data), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(loc);
			GL.vertexAttribPointer(loc, cnt, GL.FLOAT, false, 0, 0);
			return;
		}

		function set_uv( quad, w, h ){
			var uv = [];
			quad.forEach(function(v){
				uv.push(v[0]/w); uv.push(v[1]/h);
				uv.push(v[2]/w); uv.push(v[3]/h);
				uv.push(v[4]/w); uv.push(v[5]/h);
				uv.push(v[6]/w); uv.push(v[7]/h);
			});
			glAttr("a_uv", BUFFER.uv, uv, 2);
			return;
		}

		function set_xyz( quad, pos, w, h ){
			var xyz = [];
			var hw = w *  0.5 * QUAD.files.zoom;
			var hh = h * -0.5 * QUAD.files.zoom;
			var x = QUAD.files.axis_x;
			var y = QUAD.files.axis_y;
			quad.forEach(function(v){
				xyz.push( (v[0]+pos[0])/hw + x); xyz.push( (v[1]+pos[1])/hh + y); xyz.push(1.0);
				xyz.push( (v[2]+pos[0])/hw + x); xyz.push( (v[3]+pos[1])/hh + y); xyz.push(1.0);
				xyz.push( (v[4]+pos[0])/hw + x); xyz.push( (v[5]+pos[1])/hh + y); xyz.push(1.0);
				xyz.push( (v[6]+pos[0])/hw + x); xyz.push( (v[7]+pos[1])/hh + y); xyz.push(1.0);
			});
			glAttr("a_xyz", BUFFER.xyz, xyz, 3);
			return;
		}

		function set_idx( no ){
			var idx = [];
			for ( var i=0; i < no; i++ )
			{
				var j = i * 4;
				idx.push(j+0); idx.push(j+1); idx.push(j+2);
				idx.push(j+0); idx.push(j+2); idx.push(j+3);
			}
			GL.deleteBuffer(BUFFER.idx);
			BUFFER.idx = GL.createBuffer();
			GL.bindBuffer(GL.ELEMENT_ARRAY_BUFFER, BUFFER.idx);
			GL.bufferData(GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), GL.STATIC_DRAW);
			return;
		}

		function set_clr( quad, fact ){
			var clr = [];
			quad.forEach(function(v, k){
				for ( var i=0; i < 4; i++ )
				{
					var c = v[i];
					if ( c.charAt(0) === '#' )
					{
						var r = parseInt( c.substring(1,3), 16);
						var g = parseInt( c.substring(3,5), 16);
						var b = parseInt( c.substring(5,7), 16);
						var a = parseInt( c.substring(7,9), 16);
						clr.push( r/255 * fact[k] );
						clr.push( g/255 * fact[k] );
						clr.push( b/255 * fact[k] );
						clr.push( a/255 * fact[k] );
					}
					else if ( c.charAt(0) === '0' )
					{
						clr.push(0);
						clr.push(0);
						clr.push(0);
						clr.push(0);
					}
					else
					{
						clr.push( fact[k] );
						clr.push( fact[k] );
						clr.push( fact[k] );
						clr.push( fact[k] );
					}
				} // for ( var i=0; i < 4; i++ )
			});
			glAttr("a_color", BUFFER.clr, clr, 4);
			return;
		}

		function set_tex(tex){
			var u_tex = GL.getUniformLocation(PROGRAM, "u_tex");
			GL.uniform1i(u_tex, 0);
			GL.activeTexture(GL.TEXTURE0);
			GL.bindTexture(GL.TEXTURE_2D, tex);
			return;
		}

		function dummy_tex(texid){
			if ( QUAD.files.image[texid] !== undefined )
				return;

			// a dummy 2x2 white image
			var texture = GL.createTexture();
			var pixel = [
				255,255,255,255,
				255,255,255,255,
				255,255,255,255,
				255,255,255,255,
			];
			GL.bindTexture  (GL.TEXTURE_2D, texture);
			GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_S    , GL.CLAMP_TO_EDGE);
			GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_T    , GL.CLAMP_TO_EDGE);
			GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MIN_FILTER, GL.NEAREST);
			GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MAG_FILTER, GL.NEAREST);
			GL.texImage2D   (GL.TEXTURE_2D, 0, GL.RGBA, 2, 2, 0, GL.RGBA, GL.UNSIGNED_BYTE, new Uint8Array(pixel));

			QUAD.files.image[texid] = {
				width  : 2,
				height : 2,
				tex    : texture,
			}
			return;
		}

		dummy_tex(texid);
		var image = QUAD.files.image[texid];

		set_uv (src, image.width    , image.height);
		set_xyz(dst, pos, GL.canvas.width, GL.canvas.height);
		set_idx(dst.length);
		set_clr(clr, fact);
		set_tex(image.tex);

		GL.enable(GL.BLEND);
		if ( blend === 'NONE' )
		{
			GL.blendEquation( GL.FUNC_ADD );
			GL.blendFunc( GL.ONE , GL.ZERO );
		}
		else
		if ( blend === 'ADD' )
		{
			GL.blendEquation( GL.FUNC_ADD );
			GL.blendFunc( GL.ONE , GL.ONE );
		}
		else
		if ( blend === 'SUB' )
		{
			GL.blendEquation( GL.FUNC_SUBTRACT );
			//GL.blendEquation( GL.FUNC_REVERSE_SUBTRACT );
			GL.blendFunc( GL.ONE , GL.ONE );
		}
		else // NORMAL
		{
			GL.blendEquation( GL.FUNC_ADD );
			GL.blendFunc( GL.SRC_ALPHA , GL.ONE_MINUS_SRC_ALPHA );
		}

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawElements(GL.TRIANGLES, dst.length*6, GL.UNSIGNED_SHORT, 0);
		QUAD.webgl.draw++;
		return;
	}

	QUAD.render_frameid = function(id, pos){
		var GL = QUAD.webgl.gl;
		var frame = QUAD.files.quad.Frame[id];
		if ( frame === undefined )
			return;
		if ( frame.length === 0 )
			return;

		var cur_texid = -1;
		var cur_blend = 'NORMAL';
		var src  = [];
		var dst  = [];
		var clr  = [];
		var fact = [];
		frame.forEach(function(v){
			if ( v.DstQuad === undefined )
				return;

			var clrquad = v.ClrQuad || ['1','1','1','1'];
			var srcquad = v.SrcQuad || [0,0 , 1,0 , 1,1 , 0,1];

			// blending
			var blend = 'NORMAL';
			var bfact = 1;
			if ( v.Blend !== undefined )
			{
				blend = v.Blend[0];
				bfact = v.Blend[1];
			}

			// in case color blending part , no texture
			var texid = -1;
			if ( v.TexID !== undefined )
				texid = v.TexID;

			// dummy texture for color blending part and/or invalid texid
			if ( QUAD.files.image[texid] === undefined
				|| (QUAD.files.image[texid].width === 2 && QUAD.files.image[texid].height === 2) )
				srcquad = [0,0 , 1,0 , 1,1 , 0,1];

			// optimize all same texture / blend type for one draw call
			if ( cur_texid !== v.TexID || cur_blend !== blend )
			{
				render_framebuffer(cur_texid, src, dst, clr, pos, fact, cur_blend);
				cur_texid = v.TexID;
				cur_blend = blend;
				src  = [];
				dst  = [];
				clr  = [];
				fact = [];
			}

			dst.push( v.DstQuad );
			src.push( srcquad );
			clr.push( clrquad );
			fact.push( bfact );
		});
		render_framebuffer(cur_texid, src, dst, clr, pos, fact, cur_blend);
		return;
	}

	QUAD.set_cur_frame = function(){
		var len = QUAD.files.quad.Frame.length;
		if ( len < 1 )
			return;
		while ( QUAD.anim.cur_frame < 0 )
			QUAD.anim.cur_frame += len;
		QUAD.anim.cur_frame %= len;
		return;
	}

	QUAD.render_frame = function(){
		QUAD.resize_canvas();
		if ( QUAD.files.quad === undefined )
			return;

		var GL = QUAD.webgl.gl;
		GL.clear(GL.COLOR_BUFFER_BIT);
		QUAD.render_frameid( QUAD.anim.cur_frame, [0,0] );
		return;
	}

}(QUAD));
//////////////////////////////
// webgl animation
(function(QUAD){

	QUAD.html_select = function(dom, anim){
		dom.innerHTML = "<option value=''>Animation</option>";
		for ( var key in anim )
		{
			if ( anim.hasOwnProperty(key) )
				dom.innerHTML += "<option value='" + key + "'>" + key + "</option>";
		}
		return;
	}

	QUAD.anim_set = function(key){
		QUAD.anim.cur_anim_key  = '';
		QUAD.anim.cur_anim_data = [];
		QUAD.anim.cur_anim_time = 0;
		QUAD.anim.prev_anim_frame = '';

		var anim = QUAD.files.quad.Animation;
		if ( anim === undefined )
			return;

		var data = [];
		if ( anim.hasOwnProperty(key) ){
			QUAD.anim.cur_anim_key = key;
			data = anim[key].slice();

			data.forEach(function(v,k){
				var time = [];
				for ( var i=0; i < v.FPS.length; i++ )
				{
					for ( var j=0; j < v.FPS[i]; j++ )
						time.push(i);
				} // for ( var i=0; i < v.FPS.length; i++ )

				v.TIME = time;
			});

			console.log('cur_anim_data', data);
			QUAD.anim.cur_anim_data = data;
		}
		return;
	}

	QUAD.anim_timer = function(int){
		if ( int === 0 )
			return;
		if ( QUAD.anim.cur_anim_data === undefined )
			return;

		QUAD.anim.cur_anim_time += int;
		if ( QUAD.anim.cur_anim_time < 0 )
			QUAD.anim.cur_anim_time = 0;
		return;
	}

	QUAD.render_anim = function(){
		QUAD.resize_canvas();
		if ( QUAD.anim.cur_anim_key === '' )
			return;
		var data = QUAD.anim.cur_anim_data;
		if ( data === undefined )
			return;

		if ( ! QUAD.anim.cur_anim_stop )
			QUAD.anim_timer(1);
		else
			QUAD.anim_timer(0);

		// reuse previous rendered frame
		var cur = '';
		var reset = true;
		data.forEach(function(v,k){
			// v = {"FID":[1,2,3],"POS":[[0,0],[1,0],[2,0]],"FPS":[2,2,2],"TIME":[0,0,1,1,2,2]}
			var time = QUAD.anim.cur_anim_time % v.TIME.length;
			var id  = v.TIME[time];
			cur += '+' + id;
			if ( time !== 0 )
				reset = false;
		});
		if ( QUAD.anim.prev_anim_frame === cur )
			return;
		if ( reset )
			QUAD.anim.cur_anim_time = 0;

		// render new frame
		var GL = QUAD.webgl.gl;
		GL.clear(GL.COLOR_BUFFER_BIT);
		data.forEach(function(v,k){
			// v = {"FID":[1,2,3],"POS":[[0,0],[1,0],[2,0]],"FPS":[2,2,2],"TIME":[0,0,1,1,2,2]}
			var time = QUAD.anim.cur_anim_time % v.TIME.length;
			var id  = v.TIME[time];
			var pos = [0,0];
			if ( v.POS )  pos = v.POS[id];

			QUAD.render_frameid( v.FID[id], pos );
		});
		QUAD.anim.prev_anim_frame = cur;
		return;
	}

}(QUAD));
//////////////////////////////
