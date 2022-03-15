'use strict';

(function(){
	var dom_outjs = document.getElementById('outjs');

	function logJS( name, test ){
		var p = document.createElement('p');
		p.innerHTML = 'checking for "' + name + '" support ... ';
		if ( test )
			p.innerHTML += '<span class="ok">[OK]</span>';
		else
			p.innerHTML += '<span class="err">[ERROR]</span>';
		dom_outjs.appendChild(p);
		return;
	}

	var e;

	e = document.createElement('canvas').getContext('webgl');
	logJS('CANVAS.getContext("webgl")', e);

	e = ( window['Promise'] !== undefined );
	logJS('new Promise', e);

	e = ( window['FileReader'] !== undefined );
	logJS('new FileReader', e);

	e = ( window['Promise']['all'] !== undefined );
	logJS('Promise.all()', e);

	e = ( window['JSON']['parse'] !== undefined );
	logJS('JSON.parse()', e);

	e = ( window['CSS']['supports'] !== undefined );
	logJS('CSS.supports()', e);

	e = ( window['atob'] !== undefined );
	logJS('base64 atob()/btoa()', e);

	//e = ( window['WebAssembly']['validate'] !== undefined );
	//logJS('WebAssembly.validate()', e);
})();
//////////////////////////////
(function(){
	var dom_outcss = document.getElementById('outcss');

	function logCSS( key, val ){
		var p = document.createElement('p');
		p.innerHTML = 'CSS "' + key + ' : ' + val + '" support ... ';
		if ( CSS.supports(key, val) )
			p.innerHTML += '<span class="ok">[OK]</span>';
		else
			p.innerHTML += '<span class="err">[ERROR]</span>';
		dom_outcss.appendChild(p);
		return;
	}

	logCSS('display', 'flex');
	//logCSS('display', 'contents');
	//logCSS('display', 'grid');
	logCSS('width'  , '1vw');
})();
//////////////////////////////
(function(){
	var dom_canvas = document.getElementById('canvas');

	var vert_src = `
		precision highp float;
		attribute vec3 a_xyz;
		attribute vec4 a_color;
		varying   vec4 v_color;

		void main(void){
			v_color = a_color;
			gl_Position = vec4(a_xyz, 1.0);
		}
	`;
	var frag_src = `
		precision highp float;
		varying   vec4 v_color;

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

	(function(){
		var GL = dom_canvas.getContext('webgl');
		if ( ! GL )  return;

		var dom_outgl = document.getElementById('outgl');
		function logGL( key, min ){
			var name = key.toLowerCase().replace(/_/g, ' ');
			//console.log(key, GL[key]);

			var p = document.createElement('p');
			p.innerHTML  = name + ' = ' + JSON.stringify(GL[key]);
			p.innerHTML += ' (<span class="ok">' + min + '</span>)';
			dom_outgl.appendChild(p);
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
})();
//////////////////////////////
(function(){
	var dom_filereader = document.getElementById('filereader');

	document.getElementById('upload').addEventListener('change', function(e){
		var elem = this;
		elem.disabled = true;

		var promises = [];
		for ( var up of this.files )
		{
			console.log(up.type, up.name);
			var p1 = new Promise(function(resolve, reject){
				if ( up.type === 'text/plain' || up.type === 'application/json' )
				{
					var reader = new FileReader;
					reader.onload = function(){
						var txt = reader.result;
						try {
							var json = JSON.parse(txt);
							console.log(json);
						} catch(e){
						}

						var tag = document.createElement('p');
						tag.innerHTML = txt;
						dom_filereader.appendChild(tag);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsText(up);
				}
				else
				if ( up.type === 'image/png' )
				{
					var reader = new FileReader;
					reader.onload = function(){
						var img = new Image;
						img.onload = function(){
							// img.width
							// img.height
						}
						img.src = reader.result;
						dom_filereader.appendChild(img);
						resolve(1);
					}
					reader.onerror = reject;
					reader.readAsDataURL(up);
				}
				else
					resolve(-1);
			});
			promises.push(p1);
		} // for ( var up of this.files )

		Promise.all(promises).then(function(result){
			elem.disabled = false;
			console.log('Promise all then', result);
		}).catch(function(reason){
			elem.disabled = false;
			console.log('Promise all catch', reason);
		});
	});
})();
//////////////////////////////
