'use strict';

(function(){
	var vert_src = `
		precision highp float;
		precision highp int;
		attribute vec3 a_xyz;
		attribute vec4 a_color;
		varying   vec4 v_color;

		void main(void){
			v_color = a_color;
			gl_Position = vec4(a_xyz, 1.0);
		}
	`;
	var frag_src = `
		#ifdef GL_FRAGMENT_PRECISION_HIGH
			precision highp float;
			precision highp int;
		#else
			precision mediump float;
			precision mediump int;
		#endif

		varying vec4 v_color;

		void main(void){
			gl_FragColor = v_color;
		}
	`;
	var gl_data = {
		'cnt' : 6,
		'xyz' : [
			-1, 1,1,  1, 1,1,   1,-1,1,
			-1, 1,1,  1,-1,1,  -1,-1,1,
		],
		'color' : [
			1,0,0,1,  0,1,0,1,  0,0,1,1,
			1,0,0,1,  0,0,1,1,  1,1,1,1,
		],
	};

	var CANVAS = document.createElement('canvas');
	CANVAS.classList.add('section');

	var DIV = document.createElement('div');
	DIV.classList.add('section');

	var PREC = document.createElement('div');
	PREC.classList.add('section');

	function glprecision( GL )
	{
		['VERTEX_SHADER','FRAGMENT_SHADER'].forEach(function(sh){
			['LOW','MEDIUM','HIGH'].forEach(function(pr){
				['INT','FLOAT'].forEach(function(ty){
					var type = pr + '_' + ty;
					var form = GL.getShaderPrecisionFormat(GL[sh], GL[type]);
					//console.log(sh, type, form);

					var p = document.createElement('p');
					p.innerHTML  = 'GL.getShaderPrecisionFormat';
					p.innerHTML += '<span class="ok">[ ' + sh + ' ][ ' + type + ' ]</span> = ';
					p.innerHTML += 'range '+ form.rangeMin +'-'+ form.rangeMax +' , precision '+ form.precision;
					PREC.appendChild(p);
				});
			});
		});
		return;
	}

	(function(){
		var GL = CANVAS.getContext('webgl');
		if ( ! GL )  return;

		function logGL( key, min ){
			var name = key.toLowerCase().replace(/_/g, ' ');
			//console.log(key, GL[key]);

			var p = document.createElement('p');
			p.innerHTML  = name + ' = ' + JSON.stringify( GL.getParameter(GL[key]) );
			p.innerHTML += ' (<span class="ok">' + min + '</span>)';
			DIV.appendChild(p);
			return;
		}
		logGL('MAX_CUBE_MAP_TEXTURE_SIZE'       , '4096');
		logGL('MAX_RENDERBUFFER_SIZE'           , '4096');
		logGL('MAX_TEXTURE_SIZE'                , '4096');
		logGL('MAX_VIEWPORT_DIMS'               , '[4096,4096]');
		logGL('MAX_VERTEX_TEXTURE_IMAGE_UNITS'  , '4');
		logGL('MAX_TEXTURE_IMAGE_UNITS'         , '8');
		logGL('MAX_COMBINED_TEXTURE_IMAGE_UNITS', '8');
		logGL('MAX_VERTEX_ATTRIBS'              , '16');
		logGL('MAX_VARYING_VECTORS'             , '8');
		logGL('MAX_VERTEX_UNIFORM_VECTORS'      , '128');
		logGL('MAX_FRAGMENT_UNIFORM_VECTORS'    , '64');
		logGL('ALIASED_POINT_SIZE_RANGE'        , '[1,100]');

		logGL('MAX_VERTEX_ATTRIBS'              , '8');
		logGL('MAX_VERTEX_UNIFORM_VECTORS'      , '128');
		logGL('MAX_VARYING_VECTORS'             , '8');
		logGL('MAX_VERTEX_TEXTURE_IMAGE_UNITS'  , '0');
		logGL('MAX_COMBINED_TEXTURE_IMAGE_UNITS', '8');
		logGL('MAX_TEXTURE_IMAGE_UNITS'         , '8');
		logGL('MAX_FRAGMENT_UNIFORM_VECTORS'    , '16');
		logGL('MAX_DRAW_BUFFERS'                , '1');

		logGL('RED_BITS'     , '-');
		logGL('GREEN_BITS'   , '-');
		logGL('BLUE_BITS'    , '-');
		logGL('ALPHA_BITS'   , '-');
		logGL('SUBPIXEL_BITS', '-');
		logGL('DEPTH_BITS'   , '-');
		logGL('STENCIL_BITS' , '-');

		glprecision(GL);

		// compile shader
		var vert_shader = GL.createShader(GL.VERTEX_SHADER);
		GL.shaderSource (vert_shader, vert_src);
		GL.compileShader(vert_shader);

		var frag_shader = GL.createShader(GL.FRAGMENT_SHADER);
		GL.shaderSource (frag_shader, frag_src);
		GL.compileShader(frag_shader);

		var SHADER = GL.createProgram();
		GL.attachShader(SHADER, vert_shader);
		GL.attachShader(SHADER, frag_shader);
		GL.linkProgram (SHADER);

		function glattr( attr, data, cnt ){
			var loc = GL.getAttribLocation(SHADER, attr);
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(data), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(loc);
			GL.vertexAttribPointer(loc, cnt, GL.FLOAT, false, 0, 0);
			return;
		}

		GL.useProgram(SHADER);
		glattr('a_xyz'  , gl_data.xyz  , 3);
		glattr('a_color', gl_data.color, 4);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, gl_data.cnt);

	})();

	DOM_MAIN.appendChild(CANVAS);
	DOM_MAIN.appendChild(DIV);
	DOM_MAIN.appendChild(PREC);
})();
