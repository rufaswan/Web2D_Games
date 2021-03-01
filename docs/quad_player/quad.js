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
var QUAD = {};
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
			cur_anim_time : [],
			cur_anim_stop : false,
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

		var GL = QUAD.webgl.gl;
		QUAD.webgl.maxtex = GL.getParameter(GL.MAX_VERTEX_TEXTURE_IMAGE_UNITS);
		console.log('MAX_VERTEX_TEXTURE_IMAGE_UNITS = ' + QUAD.webgl.maxtex);

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

		QUAD.webgl.shader = {};
		var SHADER = QUAD.webgl.shader;
		// Normal Shader
		//   = (FG.rgb * FG.a) + (BG.rgb * BG.a * (1 - FG.a))
		//   Blend = ['ADD', 'SRC_ALPHA', '-SRC_ALPHA'] (default)
		//   no ClrQuad
		//   has TexID
		//   has SrcQuad
		var vert_src = `
			attribute vec2  a_uv;
			attribute vec3  a_xyz;
			varying   vec2  v_uv;

			void main(void){
				v_uv = a_uv;
				gl_Position = vec4(a_xyz, 1.0);
			}
		`

		var frag_src = `
			precision highp float;
			uniform sampler2D u_tex;
			varying vec2      v_uv;

			void main(void){
				gl_FragColor = texture2D(u_tex, v_uv);
			}
		`
		SHADER.normal = new_shader(vert_src, frag_src);

		// Additive Shader
		//   = FG.rgba + BG.rgba
		//   Blend = ['ADD', 'ONE', 'ONE']
		//   no ClrQuad
		//   has TexID
		//   has SrcQuad
		SHADER.add = new_shader(vert_src, frag_src);

		// Additive Grayscale Shader
		//   = (FG.rgba * mask) + BG.rgba
		//   Blend = ['ADD', 'ONE', 'ONE']
		//   ClrQuad = [1,1,1,1 , 1,1,1,1 , 1,1,1,1 , 1,1,1,1]
		//   has TexID
		//   has SrcQuad
		var vert_src = `
			attribute vec2  a_uv;
			attribute vec3  a_xyz;
			attribute vec4  a_clr;
			varying   vec2  v_uv;
			varying   vec4  v_clr;

			void main(void){
				v_uv  = a_uv;
				v_clr = a_clr;
				gl_Position = vec4(a_xyz, 1.0);
			}
		`

		var frag_src = `
			precision highp float;
			uniform sampler2D u_tex;
			varying vec2      v_uv;
			varying vec4      v_clr;

			void main(void){
				gl_FragColor = texture2D(u_tex, v_uv) * v_clr;
			}
		`
		SHADER.addgray = new_shader(vert_src, frag_src);

		// Color Shader
		//   = ???
		//   Blend = ???
		//   ClrQuad = [1,1,1,1 , 1,1,1,1 , 1,1,1,1 , 1,1,1,1]
		//   no TexID
		//   no SrcQuad
		var vert_src = `
			attribute vec3  a_xyz;
			attribute vec4  a_clr;
			varying   vec4  v_clr;

			void main(void){
				v_clr = a_clr;
				gl_Position = vec4(a_xyz, 1.0);
			}
		`

		var frag_src = `
			precision highp float;
			varying   vec4  v_clr;

			void main(void){
				gl_FragColor = v_clr;
			}
		`
		SHADER.color = new_shader(vert_src, frag_src);

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
								width  : img.width,
								height : img.height,
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
/*
		canvas.toBlob(function(blob){
			a.href = window.URL.createObjectURL(blob);
			a.setAttribute('download', fn);
			a.click();
		}, 'image/png')
*/
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
		GL.clear(GL.COLOR_BUFFER_BIT);

		GL.canvas.width  = GL.canvas.clientWidth;
		GL.canvas.height = GL.canvas.clientHeight;
		return;
	}

}(QUAD));
//////////////////////////////
// webgl frames
(function(QUAD){

	function glEnum( blend ){
		var GL = QUAD.webgl.gl;
		if ( blend === 'ADD'        )  return GL.FUNC_ADD;
		if ( blend === 'SUB'        )  return GL.FUNC_SUBTRACT;
		if ( blend === '-SUB'       )  return GL.FUNC_REVERSE_SUBTRACT;
		if ( blend === 'ONE'        )  return GL.ONE;
		if ( blend === 'ZERO'       )  return GL.ZERO;
		if ( blend === 'SRC_ALPHA'  )  return GL.SRC_ALPHA;
		if ( blend === '-SRC_ALPHA' )  return GL.ONE_MINUS_SRC_ALPHA;
		return '';
	}

	function render_framebuffer( mode, texid, src, dst, clr, blend ){
		if ( src.length === 0 || dst.length === 0 )
			return;

		var WEB = QUAD.webgl;
		var GL  = WEB.gl;
		var PROGRAM = WEB.shader[mode];
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
			glAttr("a_uv", WEB.uv, uv, 2);
			return;
		}

		function set_xyz( quad, w, h ){
			var xyz = [];
			var hw = w *  0.5 * QUAD.files.zoom;
			var hh = h * -0.5 * QUAD.files.zoom;
			var x = QUAD.files.axis_x;
			var y = QUAD.files.axis_y;
			quad.forEach(function(v){
				xyz.push(v[0]/hw+x); xyz.push(v[1]/hh+y); xyz.push(1.0);
				xyz.push(v[2]/hw+x); xyz.push(v[3]/hh+y); xyz.push(1.0);
				xyz.push(v[4]/hw+x); xyz.push(v[5]/hh+y); xyz.push(1.0);
				xyz.push(v[6]/hw+x); xyz.push(v[7]/hh+y); xyz.push(1.0);
			});
			glAttr("a_xyz", WEB.xyz, xyz, 3);
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
			GL.deleteBuffer(WEB.idx);
			WEB.idx = GL.createBuffer();
			GL.bindBuffer(GL.ELEMENT_ARRAY_BUFFER, WEB.idx);
			GL.bufferData(GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), GL.STATIC_DRAW);
			return;
		}

		function set_clr( quad ){
			glAttr("a_clr", WEB.clr, quad, 4);
			return;
		}

		function set_tex(tex){
			var u_tex = GL.getUniformLocation(PROGRAM, "u_tex");
			GL.uniform1i(u_tex, 0);
			GL.activeTexture(GL.TEXTURE0);
			GL.bindTexture(GL.TEXTURE_2D, tex);
			return;
		}

		var image = QUAD.files.image[texid];
		set_uv (src, image.width    , image.height);
		set_xyz(dst, GL.canvas.width, GL.canvas.height);
		set_idx(dst.length);
		set_clr(clr);
		set_tex(image.tex);

		GL.enable(GL.BLEND);
		GL.blendEquation( glEnum(blend[0]) );
		GL.blendFunc( glEnum(blend[1]) , glEnum(blend[2]) );

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawElements(GL.TRIANGLES, dst.length*6, GL.UNSIGNED_SHORT, 0);
		return;
	}

	QUAD.render_frameid = function(id){
		var GL = QUAD.webgl.gl;
		var frame = QUAD.files.quad.Frame[id];
		if ( frame === undefined )
			return;
		if ( frame.length === 0 )
			return;


		var cur_mode  = "normal";
		var cur_texid = undefined;
		var cur_clr   = [];
		var cur_blend = ['ADD', 'SRC_ALPHA', '-SRC_ALPHA'];
		var src = [];
		var dst = [];
		frame.forEach(function(v){
			var blend = v.Blend || ['ADD', 'SRC_ALPHA', '-SRC_ALPHA'];
			if ( v.TexID === undefined )
				return;

			if ( cur_texid !== v.TexID || cur_blend.toString() !== blend.toString() )
			{
				render_framebuffer(cur_mode, cur_texid, src, dst, cur_clr, cur_blend);
				cur_texid = v.TexID;
				src = [];
				dst = [];
			}

			src.push( v.SrcQuad );
			dst.push( v.DstQuad );
			cur_blend = blend;
		});
		render_framebuffer(cur_mode, cur_texid, src, dst, cur_clr, cur_blend);

/*

		function set_cur(mode, texid, clr){
			cur_mode  = mode;
			cur_texid = texid;
			cur_clr   = clr;
			src = [];
			dst = [];
		}


		frame.forEach(function(v){
			var blend = v.Blend || ['ADD', 'SRC_ALPHA', '-SRC_ALPHA'];

			// SHADER.normal
			// SHADER.add
			// SHADER.addgray
			// SHADER.color
			if ( v.TexID === undefined ){
				var mode = 'color';
				if ( cur_mode === mode && cur_clr.toString() === clr.toString() ){
					dst.push( v.DstQuad );
				} else {
					render_framebuffer(cur_mode, v.TexID, src, dst, cur_clr, blend);
					set_cur(mode, v.TexID, v.ClrQuad);
				}
				return;
			}

			if ( v.TexID >= QUAD.webgl.maxtex )
				return console.log('TexID ' + v.TexID + ' >= Max ' + QUAD.webgl.maxtex);

			if ( v.ClrQuad !== undefined ){
				var mode = 'addgray';
				if ( cur_mode === mode && cur_texid === v.TexID && cur_clr.toString() === clr.toString() ){
					src.push( v.SrcQuad );
					dst.push( v.DstQuad );
				} else {
					render_framebuffer(cur_mode, v.TexID, src, dst, cur_clr, blend);
					set_cur(mode, v.TexID, v.ClrQuad);
				}
				return;
			}



				if ( v.Blend === undefined )
					mode = 'normal';
				else
					mode = 'add';
			if ( mode === '' )
				return console.log('Cant detect shader mode');


			if ( cur_mode === 'normal' ){
				render_framebuffer(cur_mode, cur_texid, src, dst, cur_clr, ['ADD', 'SRC_ALPHA', '-SRC_ALPHA']);
				set_cur(mode, v.TexID, []);
			} else if ( cur_mode === 'add' ){
			}
			} else if ( cur_mode === 'addgray' ){
			}
			} else if ( cur_mode === 'addcolor' ){
			}

			if ( cur_mode !== mode )
			{
			}




*/
		return;
	}

	QUAD.render_frame = function(){
		if ( QUAD.files.quad === undefined )
			return;
		var len = QUAD.files.quad.Frame.length;
		if ( len < 1 )
			return;
		while ( QUAD.anim.cur_frame < 0 )
			QUAD.anim.cur_frame += len;

		QUAD.resize_canvas();
		QUAD.anim.cur_frame = QUAD.anim.cur_frame % len;
		QUAD.render_frameid( QUAD.anim.cur_frame );
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
		QUAD.anim.cur_anim_time = [];

		var anim = QUAD.files.quad.Animation;
		if ( anim.hasOwnProperty(key) ){
			QUAD.anim.cur_anim_key  = key;
			QUAD.anim.cur_anim_data = anim[key];
		}
		return;
	}

	function anim_timer_add(time, data){
		// data = [[0,22],[1,4],[2,3]]
		// time = [0,0]
		time[1]++;
		if ( time[1] >= data[time[0]][1] )
		{
			time[1] = 0;
			time[0]++;
		}
		if ( time[0] >= data.length )
			time[0] -= data.length;
		return;
	}

	function anim_timer_sub(time, data){
		// data = [[0,22],[1,4],[2,3]]
		// time = [0,0]
		time[1]--;
		var len = data.length;
		if ( time[1] < 0 )
		{
			if ( time[0] === 0 )
				time[0] = len - 1;
			else
				time[0]--;
			time[1] = data[time[0]][1];
		}
		return;
	}

	QUAD.anim_timer = function(int){
		var data = QUAD.anim.cur_anim_data;
		var time = QUAD.anim.cur_anim_time;
		if ( data === undefined )
			return;

		data.forEach(function(v,k){
			// per track timer
			if ( time[k] === undefined )
				time[k] = [0,0];
			else
			{
				if ( int > 0 )
					anim_timer_add(time[k], v);
				else
				if ( int < 0 )
					anim_timer_sub(time[k], v);
			}
		});
		return;
	}

	QUAD.render_anim = function(){
		if ( QUAD.anim.cur_anim_key === '' )
			return;

		if ( ! QUAD.anim.cur_anim_stop )
			QUAD.anim_timer(1);

		QUAD.resize_canvas();
		var data = QUAD.anim.cur_anim_data;
		var time = QUAD.anim.cur_anim_time;
		time.forEach(function(v,k){
			// data = [[0,22],[1,4],[2,3]]
			// time = [0,0]
			QUAD.render_frameid( data[k][v[0]][0] );
		});
		return;
	}

}(QUAD));
//////////////////////////////
