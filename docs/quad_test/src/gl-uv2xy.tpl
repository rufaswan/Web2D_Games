<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>UV*Mat3 Holes Test</title>
@@<quad.css>@@

</head><body>

<canvas id='canvas'>Canvas not supported</canvas>
@@<qdfn.js>@@

<script>
'use strict';

(function(){
	var GL = QDFN.webGLContextById('canvas');

	var vert_src = `
		attribute vec2  a_uv;
		uniform   mat3  u_mat3;
		varying   vec2  v_uv;

		void main(void){
			v_uv = a_uv;
			vec3 v3 = vec3(a_uv, 1.0) * u_mat3;

			v3.xyz /= v3.z;
			gl_Position = vec4(v3, 1.0);
		}
	`;

	var frag_src = `
		varying vec2  v_uv;
		uniform sampler2D u_tex;

		void main(void){
			gl_FragColor = texture2D(u_tex, v_uv);
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_uv', 'u_mat3', 'u_tex');

	var TEX = QDFN.tex2DNoMipmap(GL);
	var clr = [
		255,0,  0,255 ,   0,255,  0,255 ,
		  0,0,255,255 , 255,255,255,255 ,
	];
	GL.texImage2D(GL.TEXTURE_2D, 0, GL.RGBA, 2, 2, 0, GL.RGBA, GL.UNSIGNED_BYTE, new Uint8Array(clr));

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	var svec = [[ 0,0,1] , [1,0,1] , [1, 1,1] , [ 0, 1,1]];
	var dvec = [[-1,1,1] , [1,1,1] , [1,-1,1] , [-1,-1,1]];
	var mat3 = QDFN.quadMat3(svec, dvec, false);
	GL.uniformMatrix3fv(LOC.u_mat3, false, mat3);

	var t = [
		0,0 , 1,0 , 1,1 ,
		0,0 , 1,1 , 0,1 ,
	];
	QDFN.v2AttrBuf(GL, LOC.a_uv, t);

	GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
	GL.drawArrays(GL.TRIANGLES, 0, 6);

})();
</script>

</body></html>
