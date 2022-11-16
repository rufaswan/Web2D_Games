<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Rect-Quad Perspective Test (in Pixels)</title>
@@<quad.css>@@

</head><body>

@@<quad-canvas.html>@@

<script>
'use strict';

(function(){
	if ( ! GL )  return;

	var vert_src = `
		attribute vec2  a_xy;
		uniform   mat3  u_mat3;
		uniform   vec2  u_half_xy;
		varying   vec3  v_xyz;

		void main(void){
			v_xyz = vec3(a_xy.x, a_xy.y, 1.0) * u_mat3;
			gl_Position = vec4(
				a_xy.x *  u_half_xy.x,
				a_xy.y * -u_half_xy.y,
			1.0 , 1.0);
		}
	`;

	var frag_src = `
		varying vec3  v_xyz;
		uniform vec2  u_size_uv;
		uniform sampler2D u_tex;

		void main(void){
			// divide-by-zero check
			if ( v_xyz.z == 0.0 )
				discard;
			vec3 v3 = v_xyz.xyz / v_xyz.z;

			// get texel
			vec2 uv = vec2(
				v3.x * u_size_uv.x,
				v3.y * u_size_uv.y
			);
			if ( uv.x < 0.0 || uv.x > 1.0 || uv.y < 0.0 || uv.y > 1.0 )
				discard;
			gl_FragColor = texture2D(u_tex, uv);
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'u_half_xy', 'u_size_uv', 'u_mat3', 'u_tex');

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	var NX = 360;
	var NY = 640;

	function quadDraw()
	{
		var svec = [[0,0,1] , [NX,0,1] , [NX,NY,1], [0,NY,1]];
		var dvec = QDFN.quad2vec3(DST);

		var mat3 = QDFN.quadMat3(svec, dvec, true);
		GL.uniformMatrix3fv(LOC.u_mat3, false, mat3);

		var box = CANVAS.getBoundingClientRect();
		var hw  = box.width  * 0.5;
		var hh  = box.height * 0.5;

		GL.uniform2fv(LOC.u_size_uv, [1/NX,1/NY]);
		GL.uniform2fv(LOC.u_half_xy, [1/hw,1/hh]);

		var xy = [
			-hw,hh , hw, hh ,  hw,-hh ,
			-hw,hh , hw,-hh , -hw,-hh ,
		];
		QDFN.v2AttrBuf(GL, LOC.a_xy, xy);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, 6);
	}

	setInterval(function(){
		if ( ! IS_CLICK )
			return;
		getDstCorner(true);
		quadDraw()
		IS_CLICK = false;
		//console.log(DST, SRC);
	}, 100);
})();
</script>

</body></html>
