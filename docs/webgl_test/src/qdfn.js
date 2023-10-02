'use strict';

var QDFN = QDFN || {};

(function($){

	$.GL = '';
	$.Shader = '';
	$.LOC = {};

	$.setWebGLCanvasById = function( id ){
		var opt = {
			alpha                 : true,
			antialias             : true,
			preserveDrawingBuffer : false,
			depth                 : false,
			stencil               : false,
			premultipliedAlpha    : false,
		};
		$.GL = document.getElementById(id).getContext('webgl', opt);
		console.log('QDFN webgl ready');
	}

	$.canvasSize = function(){
		$.GL.canvas.width  = $.GL.canvas.clientWidth;
		$.GL.canvas.height = $.GL.canvas.clientHeight;
	}

	$.getBoundingClientRect = function(){
		return $.GL.canvas.getBoundingClientRect();
	}

	$.setShaderProgram = function( vert_src, frag_src ){
		var vert_shader = $.GL.createShader($.GL.VERTEX_SHADER);
		$.GL.shaderSource (vert_shader, vert_src);
		$.GL.compileShader(vert_shader);
		var t = $.GL.getShaderParameter(vert_shader, $.GL.COMPILE_STATUS);
		if ( ! t )
			console.error( $.GL.getShaderInfoLog(vert_shader) );

		var frag_shader = $.GL.createShader($.GL.FRAGMENT_SHADER);
		$.GL.shaderSource (frag_shader, frag_src);
		$.GL.compileShader(frag_shader);
		var t = $.GL.getShaderParameter(frag_shader, $.GL.COMPILE_STATUS);
		if ( ! t )
			console.error( $.GL.getShaderInfoLog(frag_shader) );

		var prog = $.GL.createProgram();
		$.GL.attachShader(prog, vert_shader);
		$.GL.attachShader(prog, frag_shader);
		$.GL.linkProgram (prog);
		var t = $.GL.getProgramParameter(prog, $.GL.LINK_STATUS);
		if ( ! t )
			console.error( $.GL.getProgramInfoLog(prog) );

		$.GL.useProgram(prog);
		$.Shader = prog
	}

	$.setShaderLoc = function(){
		$.LOC = {};
		for ( var i=0; i < arguments.length; i++ )
		{
			var v = arguments[i];
			switch ( v.charAt(0) )
			{
				case 'a':  $.LOC[v] = $.GL.getAttribLocation ($.Shader, v); break;
				case 'u':  $.LOC[v] = $.GL.getUniformLocation($.Shader, v); break;
			}
		} // for ( var i=0; i < arguments.length; i++ )
		return;
	}

	$.setVec4pxSize = function( loc, sw=1, sh=1 ){
		if ( $.LOC[loc] === undefined )
			console.log('QDFN LOC not found', loc);
		var hw  = $.GL.drawingBufferWidth  * 0.5;
		var hh  = $.GL.drawingBufferHeight * 0.5;
		var px  = [ 1/hw, -1/hh , 1/sw , 1/sh ];
		$.GL.uniform4fv($.LOC[loc], px);
	}

	$.setMatrix3fv = function( loc, mat3 ){
		if ( $.LOC[loc] === undefined )
			console.log('QDFN LOC not found', loc);

		// https://developer.mozilla.org/en-US/docs/Web/API/WebGLRenderingContext/uniformMatrix
		//   The values are assumed to be supplied in column major order.
		// https://webglfundamentals.org/webgl/lessons/webgl-matrix-vs-math.html
		$.GL.uniformMatrix3fv($.LOC[loc], false, mat3);
	}

	$.setMatrix4fv = function( loc, mat4 ){
		if ( $.LOC[loc] === undefined )
			console.log('QDFN LOC not found', loc);
		$.GL.uniformMatrix4fv($.LOC[loc], false, mat4);
	}

	$.createTexture = function(){
		var tex = $.GL.createTexture();
		$.GL.bindTexture  ($.GL.TEXTURE_2D, tex);
		$.GL.texParameteri($.GL.TEXTURE_2D, $.GL.TEXTURE_WRAP_S    , $.GL.CLAMP_TO_EDGE);
		$.GL.texParameteri($.GL.TEXTURE_2D, $.GL.TEXTURE_WRAP_T    , $.GL.CLAMP_TO_EDGE);
		$.GL.texParameteri($.GL.TEXTURE_2D, $.GL.TEXTURE_MIN_FILTER, $.GL.NEAREST);
		$.GL.texParameteri($.GL.TEXTURE_2D, $.GL.TEXTURE_MAG_FILTER, $.GL.NEAREST);
		return tex;
	}

	$.texImage2D = function( img='' ){
		var target         = $.GL.TEXTURE_2D;
		var level          = 0;
		var internalformat = $.GL.RGBA;
		var format         = $.GL.RGBA;
		var type           = $.GL.UNSIGNED_BYTE;
		//var pixels         = img;
		if ( img === '' )
			$.GL.texImage2D(target, level, internalformat, format, type, $.GL.canvas);
		else
			$.GL.texImage2D(target, level, internalformat, format, type, img);
	}

	$.bindTex2DById = function( bind, id ){
		var p1 = Promise.resolve().then(function(){
			var img = document.getElementById(id);
			// img.onload wont fired when img.src is data-url
			var tex = $.createTexture();
			$.texImage2D(img);
			$.GL.activeTexture( $.GL.TEXTURE0 + bind );
			$.GL.bindTexture  ( $.GL.TEXTURE_2D, tex );
			return id;
		});
		return p1;
	}

	$.setTexCount = function( loc, cnt ){
		if ( $.LOC[loc] === undefined )
			console.log('QDFN LOC not found', loc);
		if ( cnt < 1 )
			return;
		if ( cnt === 1 )
			$.GL.uniform1i($.LOC[loc], 0);
		else {
			var iv = [];
			for ( var i=0; i < cnt; i++ )
				iv.push(i);
			$.GL.uniform1iv($.LOC[loc], iv);
		}
	}

	$.setVertexAttrib = function( loc, data, vec ){
		if ( $.LOC[loc] === undefined )
			console.log('QDFN LOC not found', loc);
		var buf = $.GL.createBuffer();
		$.GL.bindBuffer($.GL.ARRAY_BUFFER, buf);
		$.GL.bufferData($.GL.ARRAY_BUFFER, new Float32Array(data), $.GL.STATIC_DRAW);
		$.GL.enableVertexAttribArray($.LOC[loc]);
		$.GL.vertexAttribPointer($.LOC[loc], vec, $.GL.FLOAT, false, 0, 0);
	}

	$.v2Attrib = function( loc, data )  { $.setVertexAttrib(loc, data, 2); }
	$.v3Attrib = function( loc, data )  { $.setVertexAttrib(loc, data, 3); }
	$.v4Attrib = function( loc, data )  { $.setVertexAttrib(loc, data, 4); }

	$.draw = function( cnt ){
		$.GL.viewport(0, 0, $.GL.drawingBufferWidth, $.GL.drawingBufferHeight);
		$.GL.drawArrays($.GL.TRIANGLES, 0, cnt);
	}

	$.drawLine = function( cnt ){
		$.GL.viewport(0, 0, $.GL.drawingBufferWidth, $.GL.drawingBufferHeight);
		$.GL.drawArrays($.GL.LINES, 0, cnt);
	}

	$.drawOutline = function( cnt ){
		$.GL.viewport(0, 0, $.GL.drawingBufferWidth, $.GL.drawingBufferHeight);
		$.GL.drawArrays($.GL.LINE_LOOP, 0, cnt);
	}
})(QDFN);
