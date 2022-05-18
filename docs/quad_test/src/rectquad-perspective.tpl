<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Rect-Quad Perspective Test</title>
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
		attribute vec2  a_xy;
		uniform   mat3  u_mat3;
		varying   vec3  v_xyz;

		void main(void){
			v_xyz = vec3(a_xy.x, a_xy.y, 1.0) * u_mat3;
			gl_Position = vec4(a_xy.x , a_xy.y , 1.0 , 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		varying   vec3  v_xyz;
		uniform   sampler2D u_tex;

		void main(void){
			if ( v_xyz.z == 0.0 )  discard;
			vec3 v3 = v_xyz.xyz / v_xyz.z;

			vec2 uv = v3.xy;
			if ( uv.x < 0.0 || uv.x > 1.0 )  discard;
			if ( uv.y < 0.0 || uv.y > 1.0 )  discard;
			gl_FragColor = texture2D(u_tex, uv);
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'u_mat3', 'u_tex');

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	function quadDraw()
	{
		var svec = QDFN.quad2vec3(SRC);
		var dvec = QDFN.quad2vec3(DST);

		var mat3 = QDFN.quadMat3(svec, dvec, true);
		GL.uniformMatrix3fv(LOC.u_mat3, false, mat3);

		var xy = [
			-1,1 , 1, 1 ,  1,-1 ,
			-1,1 , 1,-1 , -1,-1 ,
		];
		QDFN.v2AttrBuf(GL, LOC.a_xy, xy);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, 6);
	}

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
