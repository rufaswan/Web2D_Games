/*
[license]
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
			prefix : '',
			quad   : {},
			image  : {},
		};
	}

	QUAD.init_anim = function(){
		QUAD.anim = {
			zoom : 1.0,
			cur_frame : -1,
		};
		return;
	}

	QUAD.init_webgl = function(dom_canvas){
		var WEB = QUAD.webgl;
		WEB.vert_src = `
			//attribute float a_mask;
			//varying   float v_mask;
			attribute vec2  a_uv;
			varying   vec2  v_uv;
			attribute vec3  a_xyz;

			void main(void){
				//v_mask = a_mask;
				v_uv   = a_uv;
				gl_Position = vec4(a_xyz, 1.0);
			}
		`

		WEB.frag_src = `
			precision highp float;
			uniform sampler2D u_tex;
			varying vec2      v_uv;
			//varying float     v_mask;

			void main(void){
				//gl_FragColor = texture2D(u_tex, v_uv) * v_mask;
				gl_FragColor = texture2D(u_tex, v_uv);
			}
		`

		WEB.gl = dom_canvas.getContext('webgl', {
			alpha                 : true,
			depth                 : false,
			stencil               : false,
			antialias             : true,
			premultipliedAlpha    : false,
			preserveDrawingBuffer : true,
		});
		var GL = WEB.gl;

		WEB.vert_shader = GL.createShader(GL.VERTEX_SHADER);
		GL.shaderSource (WEB.vert_shader, WEB.vert_src);
		GL.compileShader(WEB.vert_shader);

		WEB.frag_shader = GL.createShader(GL.FRAGMENT_SHADER);
		GL.shaderSource (WEB.frag_shader, WEB.frag_src);
		GL.compileShader(WEB.frag_shader);

		WEB.shader_prog = GL.createProgram();
		GL.attachShader(WEB.shader_prog, WEB.vert_shader);
		GL.attachShader(WEB.shader_prog, WEB.frag_shader);
		GL.linkProgram (WEB.shader_prog);
		return;
	}

}(QUAD));
//////////////////////////////
// dom click change
(function(QUAD){

	QUAD.file_reader = function(list, select){
		QUAD.init_files();
		html_select(select, {});

		var done = 0;
		for ( var i=0; i < list.length; i++ ){
			(function(file){

				if ( file.name.match(/.*\.quad$/i) ){
					var reader = new FileReader;
					reader.onload = function(){
						done++;
						QUAD.files.prefix = file.name.substr(0, file.name.lastIndexOf('.'));
						QUAD.files.quad = JSON.parse( reader.result );
						html_select(select, QUAD.files.quad.Animation);
						//console.log(QUAD.files);
						return;
					}
					reader.readAsText(file);
				} // load *.quad

				if ( file.name.match(/.*\.png$/i) ){
					var reader = new FileReader;
					reader.onload = function(){
						var id = file.name.match(/\.([0-9]+)\./);
						var n = id[1];
						var img = new Image;
						img.onload = function(){
							done++;
							QUAD.files.image[n] = img;
							//console.log(QUAD.files.image[n]);
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
			console.log(QUAD.files);

			QUAD.init_anim();
			QUAD.render_frame();

		}, 100);
		return QUAD;
	}

	QUAD.save_png = function(canvas){
		if ( QUAD.files.prefix === '' )
			return;

		var fn = QUAD.files.prefix + '-' + 999 + '.png';
		var a  = document.createElement('a');
		a.href = canvas.toDataURL('image/png');
		a.setAttribute('download', fn);
		a.setAttribute('target', '_blank');
		a.click();
		return;
	}

	function html_select(dom, anim){
		dom.innerHTML = "<option value='-1'>Animation</option>";
		for ( var key in anim )
		{
			if ( anim.hasOwnProperty(key) ){
				dom.innerHTML += "<option value='" + key + "'>" + key + "</option>";
			}
		}
		return;
	}

}(QUAD));
//////////////////////////////
// webgl animation
(function(QUAD){

	QUAD.anim_set = function(key){
		return QUAD;
	}

	QUAD.render_anim = function(id){
		return QUAD;
	}

}(QUAD));
//////////////////////////////
// webgl frames
(function(QUAD){

	function quad2uv( quad, w, h ){
		var uv = [];
		quad.forEach(function(v){
			uv.push(v[0]/w); uv.push(v[1]/h);
			uv.push(v[2]/w); uv.push(v[3]/h);
			uv.push(v[4]/w); uv.push(v[5]/h);
			uv.push(v[6]/w); uv.push(v[7]/h);
		});
		return uv;
	}

	function quad2xyz( quad, w, h ){
		var xyz = [];
		var hw = w /  2 * QUAD.anim.zoom;
		var hh = h / -2 * QUAD.anim.zoom;
		quad.forEach(function(v){
			xyz.push(v[0]/hw); xyz.push(v[1]/hh); xyz.push(1.0);
			xyz.push(v[2]/hw); xyz.push(v[3]/hh); xyz.push(1.0);
			xyz.push(v[4]/hw); xyz.push(v[5]/hh); xyz.push(1.0);
			xyz.push(v[6]/hw); xyz.push(v[7]/hh); xyz.push(1.0);
		});
		return xyz;
	}

	function indices( no ){
		var ind = [];
		for ( var i=0; i < no; i++ )
		{
			var j = i * 4;
			ind.push(j+0); ind.push(j+1); ind.push(j+2);
			ind.push(j+0); ind.push(j+2); ind.push(j+3);
		}
		return ind;
	}
	function render_framebuffer(texid, src, dst){
		if ( src.length == 0 || dst.length == 0 )
			return;

		var WEB = QUAD.webgl;
		var GL  = WEB.gl;
		GL.useProgram(WEB.shader_prog);

		var image = QUAD.files.image[texid];
		var uv  = quad2uv (src, image.width, image.height);
		var xyz = quad2xyz(dst, GL.canvas.width, GL.canvas.height);
		var index = indices( dst.length );

		var a_uv = GL.getAttribLocation(WEB.shader_prog, "a_uv");
		var uv_buf = GL.createBuffer();
		GL.bindBuffer(GL.ARRAY_BUFFER, uv_buf);
		GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(uv), GL.STATIC_DRAW);
		GL.enableVertexAttribArray(a_uv);
		GL.vertexAttribPointer(a_uv, 2, GL.FLOAT, false, 0, 0);

		var a_xyz = GL.getAttribLocation(WEB.shader_prog, "a_xyz");
		var xyz_buf = GL.createBuffer();
		GL.bindBuffer(GL.ARRAY_BUFFER, xyz_buf);
		GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(xyz), GL.STATIC_DRAW);
		GL.enableVertexAttribArray(a_xyz);
		GL.vertexAttribPointer(a_xyz, 3, GL.FLOAT, false, 0, 0);

		var index_buf = GL.createBuffer();
		GL.bindBuffer(GL.ELEMENT_ARRAY_BUFFER, index_buf);
		GL.bufferData(GL.ELEMENT_ARRAY_BUFFER, new Uint16Array(index), GL.STATIC_DRAW);

		var texture = GL.createTexture();
		GL.bindTexture(GL.TEXTURE_2D, texture);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_S, GL.CLAMP_TO_EDGE);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_T, GL.CLAMP_TO_EDGE);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MIN_FILTER, GL.NEAREST);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MAG_FILTER, GL.NEAREST);

		GL.texImage2D(GL.TEXTURE_2D, 0, GL.RGBA, GL.RGBA, GL.UNSIGNED_BYTE, image);

		GL.enable(GL.BLEND);
		GL.blendEquation(GL.FUNC_ADD);
		GL.blendFunc(GL.SRC_ALPHA, GL.ONE_MINUS_SRC_ALPHA);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawElements(GL.TRIANGLES, index.length, GL.UNSIGNED_SHORT, 0);
		return;
	}

	QUAD.render_frameid = function(id){
		var GL = QUAD.webgl.gl;
		GL.clear(GL.COLOR_BUFFER_BIT);

		GL.canvas.width  = GL.canvas.clientWidth;
		GL.canvas.height = GL.canvas.clientHeight;

		var frame = QUAD.files.quad.Frame[id];
		var cur_texid = -1;
		var cur_blend = [];
		var src = [];
		var dst = [];
		frame.forEach(function(v){
			if ( cur_texid != v.TexID )
			{
				render_framebuffer(cur_texid, src, dst);
				cur_texid = v.TexID;
				src = [];
				dst = [];
			}

			src.push( v.SrcQuad );
			dst.push( v.DstQuad );
		});

		render_framebuffer(cur_texid, src, dst);
		return QUAD;
	}

	QUAD.render_frame = function(){
		QUAD.render_frameid(1);
		return QUAD;
	}

	function set_glblend(blend){
		var GL = QUAD.webgl.gl;
		if ( blend === 'ONE' )
			return GL.ONE;
		if ( blend === 'ZERO' )
			return GL.ZERO;
		if ( blend === 'SRC_ALPHA' )
			return GL.SRC_ALPHA;
		if ( blend === 'ONE_MINUS_SRC_ALPHA' )
			return GL.ONE_MINUS_SRC_ALPHA;
		return '';
	}

}(QUAD));
//////////////////////////////
