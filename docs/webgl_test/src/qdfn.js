'use strict';

function QuadFunc(){
	var $  = this; // public
	var __ = {};   // private

	__.GL = '';
	__.SHADER = '';
	__.LOC = {};

	$.set_webgl_by_id = function( id ){
		var opt = {
			alpha                 : true,
			antialias             : false,
			preserveDrawingBuffer : false,
			depth                 : false,
			stencil               : false,
			premultipliedAlpha    : false,
		};
		__.GL = document.getElementById(id).getContext('webgl', opt);
		console.log('QDFN webgl context OK');
	}

	$.set_shader_program = function( vert_src, frag_src ){
		var vert_shader = __.GL.createShader(__.GL.VERTEX_SHADER);
		__.GL.shaderSource (vert_shader, vert_src);
		__.GL.compileShader(vert_shader);
		var t = __.GL.getShaderParameter(vert_shader, __.GL.COMPILE_STATUS);
		if ( ! t )
			console.error( __.GL.getShaderInfoLog(vert_shader) );

		var frag_shader = __.GL.createShader(__.GL.FRAGMENT_SHADER);
		__.GL.shaderSource (frag_shader, frag_src);
		__.GL.compileShader(frag_shader);
		var t = __.GL.getShaderParameter(frag_shader, __.GL.COMPILE_STATUS);
		if ( ! t )
			console.error( __.GL.getShaderInfoLog(frag_shader) );

		var prog = __.GL.createProgram();
		__.GL.attachShader(prog, vert_shader);
		__.GL.attachShader(prog, frag_shader);
		__.GL.linkProgram (prog);
		var t = __.GL.getProgramParameter(prog, __.GL.LINK_STATUS);
		if ( ! t )
			console.error( __.GL.getProgramInfoLog(prog) );

		__.GL.useProgram(prog);
		__.SHADER = prog;
		console.log('QDFN shader program OK');
	}

	$.set_shader_loc = function(){
		__.LOC = {};
		for ( var i=0; i < arguments.length; i++ )
		{
			var v = arguments[i];
			switch ( v.charAt(0) )
			{
				case 'a':  __.LOC[v] = __.GL.getAttribLocation (__.SHADER, v); break;
				case 'u':  __.LOC[v] = __.GL.getUniformLocation(__.SHADER, v); break;
			}
		} // for ( var i=0; i < arguments.length; i++ )
		return;
	}

	$.is_valid_loc = function( loc ){
		if ( typeof __.LOC[loc] === 'undefined' ){
			console.error('QDFN LOC not found', loc);
			return false;
		}
		return true;
	}

	$.set_vec4_size = function( loc, sw=1, sh=1 ){
		if ( ! $.is_valid_loc(loc) )
			return;
		var half = $.get_drawing_half();
		var px  = [ 1/half[0], -1/half[1] , 1/sw , 1/sh ];
		__.GL.uniform4fv(__.LOC[loc], px);
	}

	$.set_mat3fv = function( loc, mat3 ){
		if ( ! $.is_valid_loc(loc) )
			return;

		// https://developer.mozilla.org/en-US/docs/Web/API/WebGLRenderingContext/uniformMatrix
		//   The values are assumed to be supplied in column major order.
		// https://webglfundamentals.org/webgl/lessons/webgl-matrix-vs-math.html
		__.GL.uniformMatrix3fv(__.LOC[loc], false, mat3);
	}

	$.set_mat4fv = function( loc, mat4 ){
		if ( ! $.is_valid_loc(loc) )
			return;
		__.GL.uniformMatrix4fv(__.LOC[loc], false, mat4);
	}

	$.create_texture = function(){
		var tex = __.GL.createTexture();
		__.GL.bindTexture  (__.GL.TEXTURE_2D, tex);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_WRAP_S    , __.GL.CLAMP_TO_EDGE);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_WRAP_T    , __.GL.CLAMP_TO_EDGE);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_MIN_FILTER, __.GL.NEAREST);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_MAG_FILTER, __.GL.NEAREST);
		return tex;
	}

	$.texImage2D = function( img='' ){
		var target         = __.GL.TEXTURE_2D;
		var level          = 0;
		var internalformat = __.GL.RGBA;
		var format         = __.GL.RGBA;
		var type           = __.GL.UNSIGNED_BYTE;
		//var pixels         = img;
		if ( img === '' )
			__.GL.texImage2D(target, level, internalformat, format, type, __.GL.canvas);
		else
			__.GL.texImage2D(target, level, internalformat, format, type, img);
	}

	$.bind_tex2D_id = function( bind, id ){
		var p = new Promise(function(ok,err){
			// src=dataurl doesnt trigger img.onload event
			// works only after reload
			var img = document.getElementById(id);
			ok([bind,img]);
		}).then(function(res){
			var tex = $.create_texture();
			$.texImage2D(res[1]);
			__.GL.activeTexture( __.GL.TEXTURE0 + res[0] );
			__.GL.bindTexture  ( __.GL.TEXTURE_2D, tex );
			return [res[1].width , res[1].height];
		});
		return p;
	}

	$.set_tex_count = function( loc, cnt ){
		if ( ! __.LOC[loc] )
			return console.log('QDFN LOC not found', loc);
		if ( cnt < 1 )
			return;
		if ( cnt === 1 )
			__.GL.uniform1i(__.LOC[loc], 0);
		else {
			var iv = [];
			for ( var i=0; i < cnt; i++ )
				iv.push(i);
			__.GL.uniform1iv(__.LOC[loc], iv);
		}
	}

	$.set_vertex_attrib = function( loc, data, vec ){
		if ( ! $.is_valid_loc(loc) )
			return;
		var buf = __.GL.createBuffer();
		__.GL.bindBuffer(__.GL.ARRAY_BUFFER, buf);
		__.GL.bufferData(__.GL.ARRAY_BUFFER, new Float32Array(data), __.GL.STATIC_DRAW);
		__.GL.enableVertexAttribArray(__.LOC[loc]);
		__.GL.vertexAttribPointer(__.LOC[loc], vec, __.GL.FLOAT, false, 0, 0);
	}

	$.v2_attrib = function( loc, data )  { $.set_vertex_attrib(loc, data, 2); }
	$.v3_attrib = function( loc, data )  { $.set_vertex_attrib(loc, data, 3); }
	$.v4_attrib = function( loc, data )  { $.set_vertex_attrib(loc, data, 4); }

	$.draw = function( cnt ){
		__.GL.viewport(0, 0, __.GL.drawingBufferWidth, __.GL.drawingBufferHeight);
		__.GL.drawArrays(__.GL.TRIANGLES, 0, cnt);
	}

	$.draw_line = function( cnt ){
		__.GL.viewport(0, 0, __.GL.drawingBufferWidth, __.GL.drawingBufferHeight);
		__.GL.drawArrays(__.GL.LINES, 0, cnt);
	}

	$.draw_outline = function( cnt ){
		__.GL.viewport(0, 0, __.GL.drawingBufferWidth, __.GL.drawingBufferHeight);
		__.GL.drawArrays(__.GL.LINE_LOOP, 0, cnt);
	}

	$.canvas_resize = function(){
		__.GL.canvas.width  = __.GL.canvas.clientWidth;
		__.GL.canvas.height = __.GL.canvas.clientHeight;
	}

	$.get_drawing_half = function(){
		return [ __.GL.drawingBufferWidth * 0.5 , __.GL.drawingBufferHeight * 0.5 ];
	}

	$.get_bounding_rect = function(){
		return __.GL.canvas.getBoundingClientRect();
	}

	$.xywh2quad = function( w, h, x=0, y=0 ){
		return [
			x   , y   ,
			x+w , y   ,
			x+w , y+h ,
			x   , y+h ,
		];
	}

	$.quad_getxy = function(){
		if ( arguments.length < 2 )
			return [];
		var quad = arguments[0];
		var res  = [];
		for ( var i=1; i < arguments.length; i++ ){
			var id = arguments[i] << 1;
			res.push(quad[id+0] , quad[id+1]);
		}
		//console.log('arg',arguments,'res',res);
		return res;
	}

	$.quad_tri4 = function( cen, quad ){
		var vec = [
			[ quad[0] , quad[1] ] ,
			[ quad[2] , quad[3] ] ,
			[ quad[4] , quad[5] ] ,
			[ quad[6] , quad[7] ] ,
		];
		return [].concat(
			cen , vec[0] , vec[1] ,
			cen , vec[1] , vec[2] ,
			cen , vec[2] , vec[3] ,
			cen , vec[3] , vec[0] ,
		);
	}
};
var QDFN = new QuadFunc;
