<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Rect-Quad Bilinear Transformation Test (UV)</title>
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
		uniform   float u_cof[8];
		varying   vec2  v_uv;

		void main(void){
			v_uv = a_uv;

			float a0 = u_cof[0];
			float a1 = u_cof[1];
			float a2 = u_cof[2];
			float a3 = u_cof[3];
			float b0 = u_cof[4];
			float b1 = u_cof[5];
			float b2 = u_cof[6];
			float b3 = u_cof[7];

			vec2 xy = vec2(
				a0 + a1*a_uv.x + a2*a_uv.y + a3*a_uv.x*a_uv.y,
				b0 + b1*a_uv.x + b2*a_uv.y + b3*a_uv.x*a_uv.y
			);
			gl_Position = vec4(
				xy.x *  u_half_xy.x,
				xy.y * -u_half_xy.y,
			1.0, 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		varying   vec2  v_uv;
		uniform   vec2  u_size_uv;
		uniform   sampler2D u_tex;

		void main(void){
			vec2 uv = vec2(
				v_uv.x * u_size_uv.x,
				v_uv.y * u_size_uv.y
			);
			gl_FragColor = texture2D(u_tex, uv);
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_uv', 'u_half_xy', 'u_size_uv', 'u_cof', 'u_tex');

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	var NX = 360;
	var NY = 640;

	// http://www.fmwconcepts.com/imagemagick/bilinearwarp/index.php
	// http://www.fmwconcepts.com/imagemagick/bilinearwarp/bilinearwarp
	// http://www.fmwconcepts.com/imagemagick/bilinearwarp/BilinearImageWarping2.pdf
	// http://www.fmwconcepts.com/imagemagick/bilinearwarp/FourCornerImageWarp2.pdf
	function Coefficient( d, nx, ny)
	{
		// x  = a0 + a1*u + a2*v + a3*u*v
		// y  = b0 + b1*u + b2*v + b3*u*v

		// u,v = 0,0 => x0,y0
		// x  = a0 + a1*0 + a2*0 + a3*0*0
		// x  = a0
		var a0 = d[0];
		var b0 = d[1];

		// u,v = 1,0 => x1,y1
		// x  = a0 + a1*1 + a2*0 + a3*1*0
		// x  = a0 + a1*1
		// a1 = x - a0
		var a1 = (d[2] - a0) / nx;
		var b1 = (d[3] - b0) / nx;

		// u,v = 0,1 => x3,y3
		// x  = a0 + a1*0 + a2*1 + a3*0*1
		// x  = a0 + a2*1
		// a2 = x - a0
		var a2 = (d[6] - a0) / ny;
		var b2 = (d[7] - b0) / ny;

		// u,v = 1,1 => x2,y2
		// x  = a0 + a1*1 + a2*1 + a3*1*1
		// a3 = x - (a0 + a1 + a2)
		var a3 = (d[4] - (a0 + a1*nx + a2*ny)) / (nx*ny);
		var b3 = (d[5] - (b0 + b1*nx + b2*ny)) / (nx*ny);

		return [a0,a1,a2,a3,b0,b1,b2,b3];
	}

	function quadDraw()
	{
		var box = CANVAS.getBoundingClientRect();
		var hw  = box.width  * 0.5;
		var hh  = box.height * 0.5;

		var co = Coefficient(DST, NX, NY);
		GL.uniform1fv(LOC.u_cof, co);
		//console.log(co);

		GL.uniform2fv(LOC.u_size_uv, [1/NX,1/NY]);
		GL.uniform2fv(LOC.u_half_xy, [1/hw,1/hh]);

		var uv = [
			0,0 , NX,0  , NX,NY,
			0,0 , NX,NY ,  0,NY,
		];
		QDFN.v2AttrBuf(GL, LOC.a_uv, uv);

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

/*
	TEX = [360 , 640]
	DST = [
		 -81 , -130,
		  95 ,  -84,
		 122 ,   99,
		-133 ,  116,
	];
	coefficient (calculated in pixels)
		a0 :  -81
		a1 :    0.490251
		a2 :   -0.081377
		a3 :    0.000344
		b0 : -130
		b1 :    0.128134
		b2 :    0.384977
		b3 :   -0.000275
		A  :    0.000110
		B1 :    0.132149
		C1 :  -53.353760
*/
})();
</script>

</body></html>
