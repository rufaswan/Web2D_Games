<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Corrected Test (DST * Minv)</title>
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
		varying   vec2  v_xy;

		void main(void){
			v_xy = a_xy;
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		uniform   mat3  u_mat3;
		varying   vec2  v_xy;
		uniform   sampler2D u_tex;

		void main(void){
			vec3 v3 = vec3(v_xy.x, v_xy.y, 1.0) * u_mat3;

			v3.xyz /= v3.z;
			gl_FragColor = texture2D(u_tex, v3.xy);
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'u_mat3', 'u_tex');

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	function xyBuffer( dst )
	{
		// normal rect
		// 0-1
		// | |
		// 3-2
		var intr = QDFN.pointIntersect(dst[0], dst[2], dst[1], dst[3]);
		if ( intr !== -1 )
			return [ dst[0],dst[1],dst[2] , dst[0],dst[2],dst[3] ];

		// twisted top-bottom
		//  0-1
		//   X
		//  2-3
		var intr = QDFN.pointIntersect(dst[0], dst[3], dst[1], dst[2]);
		if ( intr !== -1 )
			return [ intr,dst[0],dst[1] , intr,dst[3],dst[2] ];

		// twisted left-right
		//  0 2
		//  |X|
		//  3 1
		var intr = QDFN.pointIntersect(dst[0], dst[1], dst[3], dst[2]);
		if ( intr !== -1 )
			return [ intr,dst[0],dst[3] , intr,dst[1],dst[2] ];

		// bended
		var area1 = QDFN.quadArea(dst[0], dst[1], dst[2], dst[3]);
		var area2 = QDFN.quadArea(dst[1], dst[2], dst[3], dst[0]);
		// 0,1,2  0,2,3
		if ( area1 < area2 )
			return [ dst[0],dst[1],dst[2] , dst[0],dst[2],dst[3] ];
		// 1,2,3  1,3,0
		else
			return [ dst[1],dst[2],dst[3] , dst[1],dst[3],dst[0] ];
	}

	function quadDraw()
	{
		var svec = QDFN.quad2vec3(SRC);
		var dvec = QDFN.quad2vec3(DST);

		var mat3 = QDFN.quadMat3(svec, dvec, true);
		GL.uniformMatrix3fv(LOC.u_mat3, false, mat3);

		var xy = xyBuffer(dvec);
		var t = [
			xy[0][0],xy[0][1] , xy[1][0],xy[1][1] , xy[2][0],xy[2][1] ,
			xy[3][0],xy[3][1] , xy[4][0],xy[4][1] , xy[5][0],xy[5][1] ,
		];
		QDFN.v2AttrBuf(GL, LOC.a_xy, t);

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
