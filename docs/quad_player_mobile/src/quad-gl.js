function QuadGL(Q){
	var $ = this;
	var m = {};

	//////////////////////////////

	m.GL = '';
	m.SHADER = {};
	$.init = function( dom ){
		var opt = {
			alpha                 : true,
			antialias             : true,
			depth                 : false,
			premultipliedAlpha    : false,
			preserveDrawingBuffer : true,
			stencil               : false,
		};
		m.GL = dom.getContext('webgl', opt);
		if ( ! m.GL )
			return Q.func.error('WebGL context failed');
		var form = m.GL.getShaderPrecisionFormat(m.GL.FRAGMENT_SHADER, m.GL.HIGH_FLOAT);
		if ( ! form )
			return Q.func.error('Fragment Shader has no highp support');

		var vert_src, frag_src;
		Q.func.log('WebGL + highp init OK', ['precision',form.precision]);

		//////////////////////////////

		vert_src = `
			attribute  highp  vec2  a_xy;
			uniform    highp  vec2  u_pxsize;

			void main(void){
				gl_Position = vec4(
					(a_xy.x + 0.5) * u_pxsize.x ,
					(a_xy.y + 0.5) * u_pxsize.y ,
				1.0 , 1.0);
			}
		`;
		frag_src = `
			uniform  highp  vec4  u_color;

			void main(void){
				gl_FragColor = u_color;
			}
		`;
		m.SHADER.lines = m.createShader('draw lines', vert_src, frag_src);

		//////////////////////////////

		vert_src = `
			attribute  highp  vec4   a_fog;
			attribute  highp  vec3   a_xyz;
			attribute  highp  vec2   a_uv;
			attribute  lowp   float  a_tid;
			uniform    highp  vec2   u_pxsize[5];
			varying    highp  vec4   v_fog;
			varying    highp  vec2   v_uv;
			varying    highp  float  v_z;
			varying    lowp   float  v_tid;

			highp  vec4   FOG;
			highp  vec3   POS;
			highp  vec2   TEX;
			highp  float  z;
			void main(void){
				z = 1.0 / a_xyz.z;
				FOG = a_fog * z;
				POS = a_xyz * z;
				TEX = a_uv  * z;

				POS.x = (POS.x + 0.5) * u_pxsize[0].x;
				POS.y = (POS.y + 0.5) * u_pxsize[0].y;

				v_fog = FOG;
				v_uv  = TEX;
				v_z   = z;
				v_tid = a_tid;
				gl_Position = vec4(POS.x, POS.y, 1.0, 1.0);
			}
		`;
		frag_src = `
			uniform  sampler2D  u_tex[4];
			uniform  highp  vec2   u_pxsize[5];
			varying  highp  vec4   v_fog;
			varying  highp  vec2   v_uv;
			varying  highp  float  v_z;
			varying  lowp   float  v_tid;

			highp  vec4   FOG;
			highp  vec2   TEX;
			highp  float  z;
			lowp   int    tid;
			void main(void){
				z = 1.0 / v_z;
				FOG = v_fog * z;
				TEX = v_uv  * z;

				tid = int(v_tid);
				if ( tid == 0 ){
					TEX *= u_pxsize[1];
					gl_FragColor = texture2D(u_tex[0], TEX) * FOG;
				}
				else
				if ( tid == 1 ){
					TEX *= u_pxsize[2];
					gl_FragColor = texture2D(u_tex[1], TEX) * FOG;
				}
				else
				if ( tid == 2 ){
					TEX *= u_pxsize[3];
					gl_FragColor = texture2D(u_tex[2], TEX) * FOG;
				}
				else
				if ( tid == 3 ){
					TEX *= u_pxsize[4];
					gl_FragColor = texture2D(u_tex[3], TEX) * FOG;
				}
				else
					gl_FragColor = FOG;
			}
		`;
		// Error: '[]' : Index expression must be constant
		m.SHADER.keyframe = m.createShader('draw keyframe', vert_src, frag_src);

		//////////////////////////////

		vert_src = `
			attribute  highp  vec2  a_xy;
			attribute  highp  vec2  a_uv;
			uniform    highp  vec4  u_pxsize;
			varying    highp  vec2  v_uv;

			highp  vec2 xy;
			highp  vec2 uv;
			void main(void){
				xy.x = (a_xy.x + 0.5) * u_pxsize.x;
				xy.y = (a_xy.y + 0.5) * u_pxsize.y;

				uv.x = a_uv.x * u_pxsize.z;
				uv.y = a_uv.y * u_pxsize.w;

				v_uv = uv;
				gl_Position = vec4(xy.x , xy.y , 1.0 , 1.0);
			}
		`;
		frag_src = `
			uniform  sampler2D  u_tex;
			uniform  highp  vec4  u_color;
			varying  highp  vec2  v_uv;

			void main(void){
				gl_FragColor = texture2D(u_tex, v_uv) * u_color;
			}
		`;
		m.SHADER.image = m.createShader('draw image', vert_src, frag_src);

		//////////////////////////////

		return true;
	}

	m.createShader = function( name, vert_src, frag_src ){
		var vert_shader = m.GL.createShader(m.GL.VERTEX_SHADER);
		m.GL.shaderSource (vert_shader, vert_src);
		m.GL.compileShader(vert_shader);
		var t = m.GL.getShaderParameter(vert_shader, m.GL.COMPILE_STATUS);
		if ( ! t )
			return Q.func.error( m.GL.getShaderInfoLog(vert_shader) );

		var frag_shader = m.GL.createShader(m.GL.FRAGMENT_SHADER);
		m.GL.shaderSource (frag_shader, frag_src);
		m.GL.compileShader(frag_shader);
		var t = m.GL.getShaderParameter(frag_shader, m.GL.COMPILE_STATUS);
		if ( ! t )
			return Q.func.error( m.GL.getShaderInfoLog(frag_shader) );

		var prog = m.GL.createProgram();
		m.GL.attachShader(prog, vert_shader);
		m.GL.attachShader(prog, frag_shader);
		m.GL.linkProgram (prog);
		var t = m.GL.getProgramParameter(prog, m.GL.LINK_STATUS);
		if ( ! t )
			return Q.func.error( m.GL.getProgramInfoLog(prog) );

		Q.func.log('shader init', name);
		return prog;
	}

	//////////////////////////////

	$.drawLine = function( quads, color ){
		m.GL.useProgram( m.SHADER.lines );
		var loc = m.shaderLoc(m.SHADER.lines, 'a_xy', 'u_pxsize', 'u_color');
		var view = [ m.GL.drawingBufferWidth * 0.5 , m.GL.drawingBufferHeight * 0.5 ];

		m.GL.lineWidth(2);
		var pxsz = [ 1.0/view[0] , -1.0/view[1] ];

		$.setVertexAttrib(loc.a_xy, quads, 2);
		m.GL.uniform4fv  (loc.u_color , color);
		m.GL.uniform2fv  (loc.u_pxsize, pxsz);
		m.GL.viewport(0, 0, view[0]*2, view[1]*2);

		var idxlen = quads.length / 2;  // number of x,y
		m.indiceLine(idxlen);
	}

	$.drawKeyframe = function( dst, src, fog, tid, image ){
		m.GL.useProgram( m.SHADER.keyframe );
		var loc = m.shaderLoc(m.SHADER.keyframe, 'a_fog', 'a_xyz', 'a_uv', 'a_tid', 'u_pxsize', 'u_tex');
		var view = [ m.GL.drawingBufferWidth * 0.5 , m.GL.drawingBufferHeight * 0.5 ];

		// set up 4 textures
		var pxsz = [ 1.0/view[0] , -1.0/view[1] ];
		[0,1,2,3].forEach(function(v){
			var img = image[v];
			pxsz = pxsz.concat([ 1.0/img.w , 1.0/img.h ]);
			m.GL.activeTexture(m.GL.TEXTURE0 + v);
			m.GL.bindTexture  (m.GL.TEXTURE_2D, img.tex);
		});

		$.setVertexAttrib(loc.a_xyz, dst, 3);
		$.setVertexAttrib(loc.a_uv , src, 2);
		$.setVertexAttrib(loc.a_fog, fog, 4);
		$.setVertexAttrib(loc.a_tid, tid, 1);
		m.GL.uniform2fv  (loc.u_pxsize, pxsz);
		m.GL.uniform1iv  (loc.u_tex, [0,1,2,3]);
		m.GL.viewport(0, 0, view[0]*2, view[1]*2);

		var dstlen = dst.length / 3; // number of x,y
		m.indiceQuad(dstlen);
	}

	$.drawImage = function( dst, src, color, image ){
		m.GL.useProgram( m.SHADER.image );
		var loc = m.shaderLoc(m.SHADER.image, 'a_xy', 'a_uv', 'u_pxsize', 'u_tex', 'u_color');
		var view = [ m.GL.drawingBufferWidth * 0.5 , m.GL.drawingBufferHeight * 0.5 ];

		// 1 texture per draw
		var pxsz = [ 1.0/view[0] , -1.0/view[1] , 1.0/image.w ,  1.0/image.h ];
		m.GL.activeTexture(m.GL.TEXTURE0);
		m.GL.bindTexture  (m.GL.TEXTURE_2D, image.tex);

		$.setVertexAttrib(loc.a_xy, dst, 2);
		$.setVertexAttrib(loc.a_uv, src, 2);
		m.GL.uniform4fv  (loc.u_color , color);
		m.GL.uniform4fv  (loc.u_pxsize, pxsz);
		m.GL.viewport(0, 0, view[0]*2, view[1]*2);

		var dstlen = dst.length / 2; // number of x,y
		m.indiceQuad(dstlen);
	}

	m.indiceLine = function( len ){
		var idx = [];
		for ( var i=0; i < len; i += 4 )
			idx.push(i+0,i+1 , i+1,i+2 , i+2,i+3 , i+3,i+0);

		var buf = m.GL.createBuffer();
		m.GL.bindBuffer(m.GL.ELEMENT_ARRAY_BUFFER, buf);
		m.GL.bufferData(m.GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), m.GL.STATIC_DRAW);

		// 1 quad = 4 x,y   = 8 numbers
		//        = 4 lines = 8 points / indices
		m.GL.drawElements(m.GL.LINES, len/4*8, m.GL.UNSIGNED_SHORT, 0);
	}

	m.indiceQuad = function( len ){
		var idx = [];
		for ( var i=0; i < len; i += 4 )
			idx.push(i+0,i+1,i+2 , i+0,i+2,i+3);

		var buf = m.GL.createBuffer();
		m.GL.bindBuffer(m.GL.ELEMENT_ARRAY_BUFFER, buf);
		m.GL.bufferData(m.GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), m.GL.STATIC_DRAW);

		// 1 quad = 4 x,y       =  8 numbers
		//        = 4 x,y,z     = 12 numbers
		//        = 2 triangles =  6 points / indices
		m.GL.drawElements(m.GL.TRIANGLES, len/4*6, m.GL.UNSIGNED_SHORT, 0);
	}

	m.shaderLoc = function(){
		var shader = arguments[0];
		var loc    = {};
		for ( var i=1; i < arguments.length; i++ )
		{
			var v = arguments[i];
			switch ( v.charAt(0) ){
			case 'a':  loc[v] = m.GL.getAttribLocation (shader, v); break;
			case 'u':  loc[v] = m.GL.getUniformLocation(shader, v); break;
			}
		} // for ( var i=1; i < arguments.length; i++ )
		return loc;
	}

	$.setVertexAttrib = function( loc, data, v ){
		var buf = m.GL.createBuffer();
		m.GL.bindBuffer(m.GL.ARRAY_BUFFER, buf);
		m.GL.bufferData(m.GL.ARRAY_BUFFER, new Float32Array(data), m.GL.STATIC_DRAW);
		m.GL.enableVertexAttribArray(loc);
		m.GL.vertexAttribPointer(loc, v, m.GL.FLOAT, false, 0, 0);
	}

	//////////////////////////////

	$.createTexture = function(){
		var tex = m.GL.createTexture();
		m.GL.bindTexture  (m.GL.TEXTURE_2D, tex);
		m.GL.texParameteri(m.GL.TEXTURE_2D, m.GL.TEXTURE_WRAP_S    , m.GL.CLAMP_TO_EDGE);
		m.GL.texParameteri(m.GL.TEXTURE_2D, m.GL.TEXTURE_WRAP_T    , m.GL.CLAMP_TO_EDGE);
		m.GL.texParameteri(m.GL.TEXTURE_2D, m.GL.TEXTURE_MIN_FILTER, m.GL.NEAREST);
		m.GL.texParameteri(m.GL.TEXTURE_2D, m.GL.TEXTURE_MAG_FILTER, m.GL.NEAREST);
		return tex;
	}

	$.updateTexture = function( tex, img ){
		if ( ! tex )
			tex = $.createTexture();
		m.GL.bindTexture(m.GL.TEXTURE_2D, tex);
		m.GL.texImage2D(
			m.GL.TEXTURE_2D , 0 , m.GL.RGBA      , // target , level , internalformat
			m.GL.RGBA       , m.GL.UNSIGNED_BYTE , // format , type
		img);
	}

	$.createPixel = function( hex, w=1, h=1 ){
		hex = Q.math.clamp(hex, 0, 255) | 0;
		var size  = w * h * 4;
		var uint8 = new Uint8Array(size);
		for ( var i=0; i < size; i++ )
			uint8[i] = hex;

		var pix = {
			w : w ,
			h : h ,
			tex : $.createTexture() ,
		};
		m.GL.bindTexture(m.GL.TEXTURE_2D, pix.tex);
		m.GL.texImage2D(
			m.GL.TEXTURE_2D , 0 , m.GL.RGBA      , // target , level  , internalformat
			pix.w , pix.h   , 0                  , // width  , height , border
			m.GL.RGBA       , m.GL.UNSIGNED_BYTE , // format , type
			uint8
		);
		return pix;
	}

	$.readRGBA = function(){
		var bw = m.GL.drawingBufferWidth;
		var bh = m.GL.drawingBufferHeight;
		var buf = new Uint8Array( bw * bh * 4 );
		m.GL.readPixels(0, 0, bw, bh, m.GL.RGBA, m.GL.UNSIGNED_BYTE, buf);

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

	$.enableBlend = function( blend ){
		if ( ! blend )
			return m.GL.disable(m.GL.BLEND);

		var c = blend.color;
		m.GL.blendColor(c[0], c[1], c[2], c[3]);

		var mode = blend.mode;
		if ( ! Array.isArray(mode) )
			return m.GL.disable(m.GL.BLEND);

		if ( mode.length === 6 ){
			m.GL.blendEquationSeperate(m.GL[ mode[0] ] , m.GL[ mode[1] ]);
			m.GL.blendFuncSeperate    (m.GL[ mode[2] ] , m.GL[ mode[3] ] , m.GL[ mode[4] ] , m.GL[ mode[5] ]);
			return m.GL.enable(m.GL.BLEND);
		}
		if ( mode.length === 3 ){
			m.GL.blendEquation(m.GL[ mode[0] ]);
			m.GL.blendFunc    (m.GL[ mode[1] ] , m.GL[ mode[2] ]);
			return m.GL.enable(m.GL.BLEND);
		}
		return m.GL.disable(m.GL.BLEND);
	}

	$.clear = function(){
		m.GL.clear( m.GL.COLOR_BUFFER_BIT );
		m.GL.colorMask ( true , true , true , true );
		m.GL.clearColor( 0 , 0 , 0 , 0 );
	}

	$.isValidConstant = function(){
		for ( var i=0; i < arguments.length; i++ ){
			if ( ! m.GL[ arguments[i] ] )
				return false;
		}
		return true;
	}

	//////////////////////////////

	$.maxTextureSize = function(){
		var tex = $.createTexture();
		m.GL.bindTexture(m.GL.TEXTURE_2D, tex);

		// 1 << 32 is negative number
		var i = 32;
		while ( i > 0 ){
			i--;
			var maxsz = 1 << i;
			m.GL.texImage2D(
				m.GL.TEXTURE_2D , 0 , m.GL.RGBA      , // target , level  , internalformat
				maxsz , maxsz   , 0                  , // width  , height , border
				m.GL.RGBA       , m.GL.UNSIGNED_BYTE , // format , type
				null
			);

			var error = m.GL.getError();
			if ( error === m.GL.NO_ERROR )
				return maxsz;
		} // while ( i > 0 )
		return 0;
	}

	$.isMaxTextureSize = function(w, h){
		var max = $.maxTextureSize();
		if ( w > max )  return true;
		if ( h > max )  return true;
		return false;
	}

	$.canvasSize = function(){
		m.GL.canvas.width  = m.GL.canvas.clientWidth;
		m.GL.canvas.height = m.GL.canvas.clientHeight;
		return [ m.GL.canvas.width * 0.5 , m.GL.canvas.height * 0.5 ];
	}

	//////////////////////////////

} // function QuadGL
