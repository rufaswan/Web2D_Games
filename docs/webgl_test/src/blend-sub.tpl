<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Blend Subtract</title>
@@<quad.css>@@

</head><body>

@@<canvas.html>@@

<script>
'use strict';

(function(){
	if ( ! GL )  return;
	GL.clearColor(1,1,1,1);
	GL.clear(GL.COLOR_BUFFER_BIT);
	GL.enable(GL.BLEND);

	var vert_src = `
		attribute vec2  a_xy;
		attribute vec4  a_color;
		varying   vec4  v_color;

		void main(void){
			v_color = a_color;
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
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

	// result : subtract = fg - bg
	//          = 0.5 - 1 = clamped to 0
	var COLOR = [0.5,0,0,1 , 0.5,0,0,1 , 0.5,0,0,1];
	var DST = [0,0 , 1,0 , 0,1];

		QDFN.v2AttrBuf(GL, LOC.a_xy,    DST);
		QDFN.v4AttrBuf(GL, LOC.a_color, COLOR);

		GL.blendEquationSeparate(GL.FUNC_SUBTRACT, GL.FUNC_ADD);
		GL.blendFuncSeparate(GL.ONE, GL.ONE, GL.ONE, GL.ONE);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, 3);

	// result : reverse subtract = bg - fg
	//          = 1 - 0.5 = 0.5
	var COLOR = [0,0,0.5,1 , 0,0,0.5,1 , 0,0,0.5,1];
	var DST = [0,0 , -1,0 , 0,-1];

		QDFN.v2AttrBuf(GL, LOC.a_xy,    DST);
		QDFN.v4AttrBuf(GL, LOC.a_color, COLOR);

		GL.blendEquationSeparate(GL.FUNC_REVERSE_SUBTRACT, GL.FUNC_ADD);
		GL.blendFuncSeparate(GL.ONE, GL.ONE, GL.ONE, GL.ONE);

		GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
		GL.drawArrays(GL.TRIANGLES, 0, 3);
})();
</script>

</body></html>
