<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Multi-Texture/Draw() Test</title>
@@<quad.css>@@

</head><body>

@@<mona-0.png>@@
@@<mona-1.png>@@
<canvas id='canvas'>Canvas not supported</canvas>
@@<qdfn.js>@@

<script>
'use strict';

(function(){
	var GL = QDFN.webGLContextById('canvas');

	var vert_src = `
		attribute vec2  a_xy;
		attribute vec2  a_uv;
		attribute float a_tid;
		varying   vec2  v_uv;
		varying   float v_tid;

		void main(void){
			v_uv  = a_uv;
			v_tid = a_tid;
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
		}
	`;

	var frag_src = `
		varying vec2  v_uv;
		varying float v_tid;
		uniform sampler2D u_tex[2];

		void main(void){
			vec4  c;
			int  id = int(v_tid);
			if ( id == 0 )
				c = texture2D(u_tex[0], v_uv);
			else
				c = texture2D(u_tex[1], v_uv);
			gl_FragColor = c;
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'a_uv', 'a_tid', 'u_tex');

	var TEX0 = QDFN.tex2DById(GL, 'mona_0_png');
	var TEX1 = QDFN.tex2DById(GL, 'mona_1_png');
	GL.uniform1iv(LOC.u_tex, [0,1]);

	GL.activeTexture(GL.TEXTURE0 + 0);
	GL.bindTexture(GL.TEXTURE_2D, TEX0);

	GL.activeTexture(GL.TEXTURE0 + 1);
	GL.bindTexture(GL.TEXTURE_2D, TEX1);

	var xy = [
		-1,1 , 0,0.5 ,  0,-1   ,
		-1,1 , 0,-1  , -1,-0.5 ,
		 1,1 , 0,0.5 ,  0,-1   ,
		 1,1 , 0,-1  ,  1,-0.5 ,
	];
	QDFN.v2AttrBuf(GL, LOC.a_xy, xy);

	var uv = [
		0,0 , 1,0 , 1,1 ,
		0,0 , 1,1 , 0,1 ,
		0,0 , 1,0 , 1,1 ,
		0,0 , 1,1 , 0,1 ,
	];
	QDFN.v2AttrBuf(GL, LOC.a_uv, uv);

	var tid = [
		0,0,0,
		0,0,0,
		1,1,1,
		1,1,1,
	];

	var buf = GL.createBuffer();
	GL.bindBuffer(GL.ARRAY_BUFFER, buf);
	GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(tid), GL.STATIC_DRAW);
	GL.enableVertexAttribArray(LOC.a_tid);
	GL.vertexAttribPointer(LOC.a_tid, 1, GL.FLOAT, false, 0, 0);

	GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
	GL.drawArrays(GL.TRIANGLES, 0, 12);
})();
</script>

</body></html>
