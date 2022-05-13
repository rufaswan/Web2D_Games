<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Rect-Quad 1.5 Order Polynomial Test</title>
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
		uniform   float u_cof[8];
		uniform   vec2  u_half_xy;
		uniform   vec2  u_size_uv;
		varying   vec2  v_uv;

		void main(void){
			vec2 v_uv = vec2(
				u_cof[0] + u_cof[1]*a_xy[0] + u_cof[2]*a_xy[1] + u_cof[3]*a_xy[0]*a_xy[1],
				u_cof[4] + u_cof[5]*a_xy[0] + u_cof[6]*a_xy[1] + u_cof[7]*a_xy[0]*a_xy[1]
			);
			gl_Position = vec4(a_xy.x , a_xy.y , 1.0 , 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		varying   vec2  v_uv;
		uniform   sampler2D u_tex;

		void main(void){
			vec2 uv = vec2(
				(v_uv.x + 1.0) / 2.0,
				(v_uv.y + 1.0) / 2.0
			);
			gl_FragColor = texture2D(u_tex, uv);
		}
	`;

	// U = a0 + a1X + a2Y + a3XY
	// V = b0 + b1X + b2Y + b3XY

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'u_cof', 'u_half_xy', 'u_size_uv', 'u_tex');

	function getcx( v3 )
	{
		var intr = QDFN.pointIntersect(v3[0], v3[2], v3[1], v3[3]);
		if ( intr !== -1 ) // is simple
			return intr;
		var intr = QDFN.pointIntersect(v3[0], v3[3], v3[1], v3[2]);
		if ( intr !== -1 ) // is twisted left-right
			return intr;
		var intr = QDFN.pointIntersect(v3[0], v3[1], v3[3], v3[2]);
		if ( intr !== -1 ) // is twisted top-bottom
			return intr;

		return -1;
	}

	function addBuffer()
	{
		for ( var i=1; i < arguments.length; i++ )
		{
			arguments[0].push( arguments[i][0] );
			arguments[0].push( arguments[i][1] );
		}
		return;
	}

	function quadDraw()
	{
		var co = RectQuadCoefficient();
		GL.uniform1f(u_cof, co);

		var sqd = QDFN.quad2vec3(SRC);
		var dqd = QDFN.quad2vec3(DST);
		var scx = getcx(sqd);
		var dcx = getcx(dqd);

		// for simple and twisted
		if ( dcx !== -1 && scx !== -1 )
		{
			var xy = [];
			addBuffer(xy, dcx, dqd[0], dqd[1]);
			addBuffer(xy, dcx, dqd[1], dqd[2]);
			addBuffer(xy, dcx, dqd[2], dqd[3]);
			addBuffer(xy, dcx, dqd[3], dqd[0]);
			QDFN.v2AttrBuf(GL, LOC.a_xy, xy);

			GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
			GL.drawArrays(GL.TRIANGLES, 0, 12);
			return;
		}

		// bended
		var area1 = QDFN.quadArea(dqd[0], dqd[1], dqd[2], dqd[3]);
		var area2 = QDFN.quadArea(dqd[1], dqd[2], dqd[3], dqd[0]);
		var xy = [];

		if ( area1 < area2 )
		{
			addBuffer(xy, dqd[0], dqd[1], dqd[2]);
			addBuffer(xy, dqd[0], dqd[2], dqd[3]);
		}
		else
		{
			addBuffer(xy, dqd[1], dqd[2], dqd[3]);
			addBuffer(xy, dqd[1], dqd[3], dqd[0]);
		}
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
