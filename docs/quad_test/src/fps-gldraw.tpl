<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>WebGL draw() FPS Test</title>
@@<fps.css>@@

</head><body>

<canvas id='canvas'>Canvas not supported</canvas>
<ol id='console'></ol>
@@<Mona_Lisa.png>@@

@@<qdfn.js>@@

<script>
'use strict';

var GL = QDFN.webGLContextById('canvas');

var DST = [-1,1 , 1,1 , 1,-1 , -1,-1];
var SRC = [ 0,0 , 1,0 , 1, 1 ,  0, 1];

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
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'u_mat3', 'u_tex');
	var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');

	GL.uniform1i(LOC.u_tex, 0);
	GL.activeTexture(GL.TEXTURE0);
	GL.bindTexture(GL.TEXTURE_2D, TEX);

	var CON = document.getElementById('console');
	var cDraw = [];
	var nDraw = 0;
	var isRun = true;

	var t2 = setInterval(function(){
		GL.clear(GL.COLOR_BUFFER_BIT);

		var svec = QDFN.quad2vec3(SRC);
		var dvec = QDFN.quad2vec3(DST);

		var mat3 = QDFN.quadMat3(svec, dvec, true);
		GL.uniformMatrix3fv(LOC.u_mat3, false, mat3);

		var t = [
			DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7] ,
			DST[2],DST[3] , DST[6],DST[7] , DST[0],DST[1] ,
		];
		QDFN.v2AttrBuf(GL, LOC.a_xy, t);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, 6);
		nDraw++;
	}, 1);

	var t1 = setInterval(function(){
		cDraw.push(nDraw);
		nDraw = 0;
		//console.log(cDraw.length);

		if ( cDraw.length > 5 ){
			isRun = false;
			clearInterval(t1);
			clearInterval(t2);

			cDraw.forEach(function(v){
				var li = document.createElement('li');
				li.innerHTML = v+ ' draw/sec , ' +1000/v+ ' ms/draw()';
				CON.appendChild(li);
			});
		}
	}, 1000);
})();
</script>

</body></html>
