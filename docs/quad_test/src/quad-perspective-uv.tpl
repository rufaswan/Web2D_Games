<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Corrected Test (SRC * M)</title>
@@<quad.css>@@

</head><body>

@@<quad-canvas.html>@@

<script>
'use strict';

(function(){
	if ( ! GL )  return;

	var vert_src = `
		precision highp float;
		precision highp int;
		attribute vec2  a_uv;
		uniform   vec2  u_half_xy;
		uniform   vec2  u_size_uv;
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
		precision highp float;
		precision highp int;
		varying   vec2  v_uv;
		uniform   sampler2D u_tex;

		void main(void){
			gl_FragColor = texture2D(u_tex, v_uv);
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_uv', 'u_half_xy', 'u_size_uv', 'u_mat3', 'u_tex');

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	function quadDraw()
	{
		var svec = QDFN.quad2vec3(SRC);
		var dvec = QDFN.quad2vec3(DST);

		var mat3 = QDFN.quadMat3(svec, dvec, false);
		GL.uniformMatrix3fv(LOC.u_mat3, false, mat3);

		var t = [
			SRC[2],SRC[3] , SRC[4],SRC[5] , SRC[6],SRC[7] ,
			SRC[2],SRC[3] , SRC[6],SRC[7] , SRC[0],SRC[1] ,
		];
		QDFN.v2AttrBuf(GL, LOC.a_uv, t);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, 6);
	} // quadDraw()

	setInterval(function(){
		if ( ! IS_CLICK )
			return;
		getDstCorner();
		quadDraw()
		IS_CLICK = false;
		//console.log(DST, SRC);
	}, 100);
})();
</script>

</body></html>
