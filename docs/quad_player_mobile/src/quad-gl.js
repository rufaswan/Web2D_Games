function QuadGL(Q){
	var $ = this; // public
	var __ = {};  // private

	//////////////////////////////

	__.GL = '';
	__.SHADER = {};
	__.MAX_TEX_SIZE = -1;

	$.init = function( dom ){
		var opt = {
			alpha                 : true,
			antialias             : true,
			depth                 : true,
			premultipliedAlpha    : false,
			preserveDrawingBuffer : true,
			stencil               : false,
		};
		__.GL = dom.getContext('webgl', opt);
		if ( ! __.GL )
			return Q.func.error('WebGL context failed');
		var form = __.GL.getShaderPrecisionFormat(__.GL.FRAGMENT_SHADER, __.GL.HIGH_FLOAT);
		if ( ! form )
			return Q.func.error('Fragment Shader has no highp support');

		var maxsz = $.detect_max_texsize();
		if ( maxsz < 1 )
			return Q.func.error('MAX_TEXTURE_SIZE < 1', maxsz);
		__.MAX_TEX_SIZE = maxsz | 0;
		var vec2_vram = 'vec2(' + maxsz.toFixed(1) + ' , ' + maxsz.toFixed(1) + ')';

		var vert_src, frag_src;
		Q.func.log('WebGL + highp init OK',
			['precision',form.precision],
			['MAX_TEXTURE_SIZE',__.MAX_TEX_SIZE]
		);

		//////////////////////////////

		vert_src = `
			attribute  highp  vec2  a_xy;
			uniform    highp  vec2  u_pxsize;

			highp  vec2  xy;
			void main(void){
				xy = a_xy * u_pxsize;
				gl_Position = vec4(xy.x, xy.y, 1.0 , 1.0);
			}
		`;
		frag_src = `
			uniform  highp  vec4  u_color;

			void main(void){
				gl_FragColor = u_color;
			}
		`;
		__.SHADER.lines = __.create_shader('draw lines', vert_src, frag_src);

		//////////////////////////////

		vert_src = `
			attribute  highp  vec4   a_fog;
			attribute  highp  vec3   a_xyz;
			attribute  highp  vec2   a_uv;
			attribute  lowp   float  a_z;
			uniform    highp  vec2   u_pxsize;
			varying    highp  vec4   v_fog;
			varying    highp  vec2   v_uv;
			varying    highp  float  v_z;

			highp  vec4   fog;
			highp  vec2   xy;
			highp  vec2   uv;
			highp  float  z;
			void main(void){
				z = 1.0 / a_xyz.z;
				fog = a_fog    * z;
				xy  = a_xyz.xy * z * u_pxsize;
				uv  = a_uv     * z;

				v_fog = fog;
				v_uv  = uv;
				v_z   = z;
				gl_Position = vec4(xy.x, xy.y, a_z, 1.0);
			}
		`;
		frag_src = `
			uniform  sampler2D  u_tex;
			varying  highp  vec4   v_fog;
			varying  highp  vec2   v_uv;
			varying  highp  float  v_z;

			highp  vec4   fog;
			highp  vec2   uv;
			highp  float  z;
			void main(void){
				z   = 1.0 / v_z;
				fog = v_fog * z;
				uv  = v_uv  * z;
				gl_FragColor = texture2D(u_tex, uv / ${vec2_vram}) * fog;
			}
		`;
		__.SHADER.keyframe = __.create_shader('draw keyframe', vert_src, frag_src);

		//////////////////////////////

		vert_src = `
			attribute  highp  vec2   a_xy;
			attribute  highp  vec2   a_uv;
			varying    highp  vec2   v_uv;

			highp  vec2  xy;
			void main(void){
				v_uv = a_uv;
				xy   = a_xy / ${vec2_vram};

				// convert 0.0 to 1.0 => -1.0 to +1.0
				xy = (xy * 2.0) - 1.0;
				gl_Position = vec4(xy.x, xy.y, 1.0, 1.0);
			}
		`;
		frag_src = `
			uniform  sampler2D  u_tex;
			uniform  highp  vec2   u_pxsize;
			varying  highp  vec2   v_uv;

			highp  vec2  uv;
			void main(void){
				UV = v_uv * u_pxsize;
				gl_FragColor = texture2D(u_tex, uv);
			}
		`;
		__.SHADER.vram = __.create_shader('draw vram', vert_src, frag_src);

		//////////////////////////////

		return true;
	}

	__.create_shader = function( name, vert_src, frag_src ){
		var vert_shader = __.GL.createShader(__.GL.VERTEX_SHADER);
		__.GL.shaderSource (vert_shader, vert_src);
		__.GL.compileShader(vert_shader);
		var t = __.GL.getShaderParameter(vert_shader, __.GL.COMPILE_STATUS);
		if ( ! t )
			return Q.func.error( __.GL.getShaderInfoLog(vert_shader) );

		var frag_shader = __.GL.createShader(__.GL.FRAGMENT_SHADER);
		__.GL.shaderSource (frag_shader, frag_src);
		__.GL.compileShader(frag_shader);
		var t = __.GL.getShaderParameter(frag_shader, __.GL.COMPILE_STATUS);
		if ( ! t )
			return Q.func.error( __.GL.getShaderInfoLog(frag_shader) );

		var prog = __.GL.createProgram();
		__.GL.attachShader(prog, vert_shader);
		__.GL.attachShader(prog, frag_shader);
		__.GL.linkProgram (prog);
		var t = __.GL.getProgramParameter(prog, __.GL.LINK_STATUS);
		if ( ! t )
			return Q.func.error( __.GL.getProgramInfoLog(prog) );

		Q.func.log('shader init', name);
		return prog;
	}

	//////////////////////////////

	$.draw_line = function( quads, color ){
		__.GL.useProgram( __.SHADER.lines );
		var loc = __.shader_loc(__.SHADER.lines, 'a_xy', 'u_pxsize', 'u_color');
		var view = [ __.GL.drawingBufferWidth * 0.5 , __.GL.drawingBufferHeight * 0.5 ];

		__.GL.lineWidth(2);
		var pxsz = [ 1.0/view[0] , -1.0/view[1] ];

		__.set_vertex_attrib(loc.a_xy, quads, 2);
		__.GL.uniform4fv    (loc.u_color , color);
		__.GL.uniform2fv    (loc.u_pxsize, pxsz);
		__.GL.viewport(0, 0, view[0]*2, view[1]*2);

		var idxlen = quads.length / 2;  // number of x,y
		__.indice_line(idxlen);
	}

	$.draw_keyframe = function( dst, src, fog, z, image ){
		__.GL.useProgram( __.SHADER.keyframe );
		var loc = __.shader_loc(__.SHADER.keyframe, 'a_fog', 'a_xyz', 'a_uv', 'a_z', 'u_pxsize', 'u_tex');
		var view = [ __.GL.drawingBufferWidth * 0.5 , __.GL.drawingBufferHeight * 0.5 ];

		var pxsz = [ 1.0/view[0] , -1.0/view[1] ];
		__.GL.activeTexture(__.GL.TEXTURE0);
		__.GL.bindTexture  (__.GL.TEXTURE_2D, image.tex);

		__.set_vertex_attrib(loc.a_xyz, dst, 3);
		__.set_vertex_attrib(loc.a_uv , src, 2);
		__.set_vertex_attrib(loc.a_fog, fog, 4);
		__.set_vertex_attrib(loc.a_z  , z  , 1);
		__.GL.uniform2f     (loc.u_pxsize, pxsz[0], pxsz[1]);
		__.GL.uniform1i     (loc.u_tex   , 0   );
		__.GL.viewport(0, 0, view[0]*2, view[1]*2);

		var dstlen = dst.length / 3; // number of x,y
		__.indice_quad(dstlen);
	}

	$.draw_vram = function( vram, tex, rect ){
		__.GL.useProgram( __.SHADER.vram );
		var loc = __.shader_loc(__.SHADER.vram, 'a_xy', 'a_uv', 'u_pxsize', 'u_tex');
		var sw = rect[2] - rect[0];
		var sh = rect[3] - rect[1];

		// to be used with canvas - DO NOT flipy !
		var pxsz = [ 1.0/sw , 1.0/sh ];
		__.GL.activeTexture(__.GL.TEXTURE0);
		__.GL.bindTexture  (__.GL.TEXTURE_2D, tex);

		var dst = [
			rect[0] , rect[1] ,
			rect[2] , rect[1] ,
			rect[2] , rect[3] ,
			rect[0] , rect[3] ,
		];
		var src = [0,0 , sw,0 , sw,sh , 0,sh];
		__.set_vertex_attrib(loc.a_xy, dst, 2);
		__.set_vertex_attrib(loc.a_uv, src, 2);
		__.GL.uniform2f     (loc.u_pxsize, pxsz[0], pxsz[1]);
		__.GL.uniform1i     (loc.u_tex   , 0   );
		__.GL.viewport(0, 0, vram.w, vram.h);

		$.enable_framebuffer(vram.tex);
		__.indice_quad(4);
		$.enable_framebuffer(0);
	}

	__.indice_line = function( len ){
		var idx = [];
		for ( var i=0; i < len; i += 4 )
			idx.push(i+0,i+1 , i+1,i+2 , i+2,i+3 , i+3,i+0);

		var buf = __.GL.createBuffer();
		__.GL.bindBuffer(__.GL.ELEMENT_ARRAY_BUFFER, buf);
		__.GL.bufferData(__.GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), __.GL.STATIC_DRAW);

		// 1 quad = 4 x,y   = 8 numbers
		//        = 4 lines = 8 points / indices
		__.GL.drawElements(__.GL.LINES, len/4*8, __.GL.UNSIGNED_SHORT, 0);
	}

	__.indice_quad = function( len ){
		var idx = [];
		for ( var i=0; i < len; i += 4 )
			idx.push(i+0,i+1,i+2 , i+0,i+2,i+3);

		var buf = __.GL.createBuffer();
		__.GL.bindBuffer(__.GL.ELEMENT_ARRAY_BUFFER, buf);
		__.GL.bufferData(__.GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), __.GL.STATIC_DRAW);

		// 1 quad = 4 x,y       =  8 numbers
		//        = 4 x,y,z     = 12 numbers
		//        = 2 triangles =  6 points / indices
		__.GL.drawElements(__.GL.TRIANGLES, len/4*6, __.GL.UNSIGNED_SHORT, 0);
	}

	__.shader_loc = function(){
		var shader = arguments[0];
		var loc    = {};
		for ( var i=1; i < arguments.length; i++ )
		{
			var v = arguments[i];
			switch ( v.charAt(0) ){
			case 'a':  loc[v] = __.GL.getAttribLocation (shader, v); break;
			case 'u':  loc[v] = __.GL.getUniformLocation(shader, v); break;
			}
		} // for ( var i=1; i < arguments.length; i++ )
		return loc;
	}

	__.set_vertex_attrib = function( loc, data, v ){
		var buf = __.GL.createBuffer();
		__.GL.bindBuffer(__.GL.ARRAY_BUFFER, buf);
		__.GL.bufferData(__.GL.ARRAY_BUFFER, new Float32Array(data), __.GL.STATIC_DRAW);
		__.GL.enableVertexAttribArray(loc);
		__.GL.vertexAttribPointer(loc, v, __.GL.FLOAT, false, 0, 0);
	}

	//////////////////////////////

	$.create_texture = function(){
		var tex = __.GL.createTexture();
		__.GL.bindTexture  (__.GL.TEXTURE_2D, tex);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_WRAP_S    , __.GL.CLAMP_TO_EDGE);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_WRAP_T    , __.GL.CLAMP_TO_EDGE);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_MIN_FILTER, __.GL.NEAREST);
		__.GL.texParameteri(__.GL.TEXTURE_2D, __.GL.TEXTURE_MAG_FILTER, __.GL.NEAREST);
		return tex;
	}

	$.update_texture = function( tex, img ){
		if ( ! tex )
			tex = $.create_texture();
		__.GL.bindTexture(__.GL.TEXTURE_2D, tex);
		__.GL.texImage2D(
			__.GL.TEXTURE_2D , 0 , __.GL.RGBA      , // target , level , internalformat
			__.GL.RGBA       , __.GL.UNSIGNED_BYTE , // format , type
		img);
	}

	$.create_pixel = function( hex, w=1, h=1 ){
		hex = Q.math.clamp(hex, 0, 255) | 0;
		if ( w < 0 )  w = __.MAX_TEX_SIZE;
		if ( h < 0 )  h = __.MAX_TEX_SIZE;

		var size  = w * h * 4;
		var uint8 = new Uint8Array(size);
		for ( var i=0; i < size; i++ )
			uint8[i] = hex;

		var pix = {
			w : w ,
			h : h ,
			tex : $.create_texture() ,
		};
		__.GL.bindTexture(__.GL.TEXTURE_2D, pix.tex);
		__.GL.texImage2D(
			__.GL.TEXTURE_2D , 0 , __.GL.RGBA      , // target , level  , internalformat
			pix.w , pix.h    , 0                   , // width  , height , border
			__.GL.RGBA       , __.GL.UNSIGNED_BYTE , // format , type
			uint8
		);
		return pix;
	}

	$.to_uint8 = function(){
		return new Promise(function(ok,err){
			__.GL.canvas.toBlob(function(blob){
				var reader = new FileReader;
				reader.onload = function(){
					var uint8 = new Uint8Array(reader.result);
					ok(uint8);
				};
				reader.readAsArrayBuffer(blob);
			}, 'image/png');
		});
	}

	$.read_RGBA = function(){
		var bw = __.GL.drawingBufferWidth;
		var bh = __.GL.drawingBufferHeight;
		var buf = new Uint8Array( bw * bh * 4 );
		__.GL.readPixels(0, 0, bw, bh, __.GL.RGBA, __.GL.UNSIGNED_BYTE, buf);

		var pix = new Uint8Array( 12 + (bw * bh * 4) );
		Q.binary.setint(pix, 0, 4, 0x41424752); // RGBA
		Q.binary.setint(pix, 4, 4, bw);
		Q.binary.setint(pix, 8, 4, bh);

		// vflip the image
		var row = bw * 4;
		for ( var dy=0; dy < bh; dy++ ){
			var dyy = 12 + (dy * row);
			var syy = (bh - 1 - dy) * row;

			for ( var x=0; x < row; x++ ){
				pix[dyy] = buf[syy];
				dyy++;
				syy++;
			}
		} // for ( var dy=0; dy < bh; dy++ )
		return pix;
	}

	//////////////////////////////

	$.enable_blend = function( blend ){
		if ( ! blend )
			return __.GL.disable(__.GL.BLEND);

		var c = blend.color;
		__.GL.blendColor(c[0], c[1], c[2], c[3]);

		if ( ! blend.mode_alpha )
			blend.mode_alpha = blend.mode_rgb;

		var mc = blend.mode_rgb;
		var ma = blend.mode_alpha;
		__.GL.blendEquationSeparate(__.GL[ mc[0] ] , __.GL[ ma[0] ]);
		__.GL.blendFuncSeparate    (__.GL[ mc[1] ] , __.GL[ mc[2] ] , __.GL[ ma[1] ] , __.GL[ ma[2] ]);
		return __.GL.enable(__.GL.BLEND);
	}

	$.enable_depth = function( depth ){
		if ( ! depth ){
			__.GL.clear(__.GL.DEPTH_BUFFER_BIT);
			//__.GL.depthMask(true); // can write depth
			__.GL.clearDepth(1.0);
			return __.GL.disable(__.GL.DEPTH_TEST);
		}

		__.GL.depthFunc(__.GL[depth]);
		return __.GL.enable(__.GL.DEPTH_TEST);
	}

	$.enable_framebuffer = function( tex ){
		if ( ! tex ){
			__.GL.bindFramebuffer(__.GL.FRAMEBUFFER, null);
			return null;
		}

		var fb = __.GL.createFramebuffer();
		__.GL.bindFramebuffer     (__.GL.FRAMEBUFFER, fb);
		__.GL.framebufferTexture2D(__.GL.FRAMEBUFFER, __.GL.COLOR_ATTACHMENT0, __.GL.TEXTURE_2D, tex, 0);
		var t = __.GL.checkFramebufferStatus(__.GL.FRAMEBUFFER);
		if ( t === __.GL.FRAMEBUFFER_COMPLETE ){
			Q.func.log('framebuffer OK');
			return fb;
		}
		return Q.func.error('framebuffer failed',t);
	}

	$.clear = function(){
		__.GL.clear(__.GL.COLOR_BUFFER_BIT);
		//__.GL.colorMask(true , true , true , true); // can write red, green, blue, alpha
		__.GL.clearColor(0 , 0 , 0 , 0);
		__.GL.flush();
	}

	$.is_gl_enum = function( str ){
		if ( Array.isArray(str) ){
			for ( var i=0; i < str.length; i++ ){
				str[i] = str[i].toUpperCase();
				if ( ! __.GL[ str[i] ] )
					return false;
			}
			return true;
		}
		return false;
	}

	//////////////////////////////

	$.drawingbuffer_size = function( max ){
		return [ __.GL.drawingBufferWidth , __.GL.drawingBufferHeight ];
	}

	$.detect_max_texsize = function(){
		var tex = $.create_texture();
		__.GL.bindTexture(__.GL.TEXTURE_2D, tex);

		var maxsz = __.GL.getParameter( __.GL.MAX_TEXTURE_SIZE ) >> 1;
		var bw = __.GL.canvas.width;
		var bh = __.GL.canvas.height;

		if ( maxsz < 0 )
			maxsz = 0;
		while ( maxsz > 0 ){
			// test if reading texture at this size
			__.GL.texImage2D(
				__.GL.TEXTURE_2D , 0 , __.GL.RGBA      , // target , level  , internalformat
				maxsz , maxsz    , 0                   , // width  , height , border
				__.GL.RGBA       , __.GL.UNSIGNED_BYTE , // format , type
				null
			);
			// test if writing canvas at this size
			__.GL.canvas.width  = maxsz;
			__.GL.canvas.height = maxsz;

			var error = 0;
			error |= ( __.GL.getError()    !== __.GL.NO_ERROR            );
			error |= ( __.GL.canvas.width  >   __.GL.drawingBufferWidth  );
			error |= ( __.GL.canvas.height >   __.GL.drawingBufferHeight );
			if ( error === 0 )
				break;

			// has error, halved maxsz and test again
			maxsz >>= 1;
		} // while ( maxsz > 0 )

		// restore canvas size after testing
		__.GL.canvas.width  = bw;
		__.GL.canvas.height = bh;
		return maxsz;
	}

	$.max_texsize = function(){
		return __.MAX_TEX_SIZE;
	}
	$.is_max_texsize = function(w, h){
		if ( w > __.MAX_TEX_SIZE || h > __.MAX_TEX_SIZE )
			return false;
		return true;
	}

	$.canvas_size = function(){
		return [ __.GL.canvas.width * 0.5 , __.GL.canvas.height * 0.5 ];
	}

	$.is_canvas_resized = function(){
		// display.block = [w,h] , display.none = [0,0]
		var c = 0;
		c |= ( __.GL.canvas.width  !== __.GL.canvas.clientWidth  );
		c |= ( __.GL.canvas.height !== __.GL.canvas.clientHeight );

		if ( c ){
			__.GL.canvas.width  = __.GL.canvas.clientWidth;
			__.GL.canvas.height = __.GL.canvas.clientHeight;
		}
		return c;
	}

	//////////////////////////////

} // function QuadGL
