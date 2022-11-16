<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Lines Test</title>
@@<quad.css>@@

</head><body>

@@<quad-canvas.html>@@

<script>
'use strict';

(function(){
	if ( ! GL )  return;

	var vert_src = `
		attribute vec2  a_xy;

		void main(void){
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
		}
	`;

	var frag_src = `
		uniform vec4  u_color;

		void main(void){
			gl_FragColor = u_color;
		}
	`;

	var COLOR = [0.0 , 1.0 , 0.0 , 1.0];
	var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);
	var LOC = QDFN.shaderLoc(GL, SHADER, 'a_xy', 'u_color');

	function quadDraw()
	{
		GL.uniform4fv(LOC.u_color, COLOR);

		QDFN.v2AttrBuf(GL, LOC.a_xy, DST);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.LINE_LOOP, 0, 4);
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
