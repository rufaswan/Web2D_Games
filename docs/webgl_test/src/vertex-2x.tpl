<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Vertex Position 2x</title>
@@<quad.css>@@

</head><body>

@@<canvas.html>@@

<script>
'use strict';

(function(){
	if ( ! GL )  return;

	var vert_src = `
		attribute vec2  a_xy;
		attribute vec4  a_color;
		varying   vec4  v_color;

		void main(void){
			v_color = a_color;
			gl_Position = vec4(a_xy.x * 10.0, a_xy.y * 10.0, 1.0, 1.0);
		}
	`;

	var frag_src = `
		varying vec4  v_color;

		void main(void){
			gl_FragColor = v_color;
		}
	`;

	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'a_color');
	var DST   = [-0.1,0 , 0,0.1 , 0.1,-0.1];
	var COLOR = [1,0,0,1 , 0,1,0,1 , 0,0,1,1];

	QDFN.v2AttrBuf(GL, LOC.a_xy,    DST);
	QDFN.v4AttrBuf(GL, LOC.a_color, COLOR);

	GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
	GL.drawArrays(GL.TRIANGLES, 0, 3);
})();
</script>

</body></html>
