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

		canvas.toBlob(function(blob){
			a.href = window.URL.createObjectURL(blob);
			a.setAttribute('download', fn);
			a.click();
		}, 'image/png')
