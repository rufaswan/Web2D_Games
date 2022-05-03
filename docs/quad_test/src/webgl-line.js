'use strict';

var GL = CANVAS.getContext('webgl');

(function(){
	if ( ! GL )  return;

	var vert_src = `
		precision highp float;
		precision highp int;
		attribute vec2 a_xy;

		void main(void){
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		uniform vec4 u_color;

		void main(void){
			gl_FragColor = u_color;
		}
	`;

	function getDstCorner(){
		var box = CANVAS.getBoundingClientRect();
		var hw  = box.width  * 0.5;
		var hh  = box.height * 0.5;
		DST = [];
		for ( var i=0; i < 4; i++ )
		{
			var cnr = document.getElementById('corner'+i).getBoundingClientRect();
			var x = cnr.left - box.left - hw;
			var y = cnr.top  - box.top  - hh;
			DST.push( x /  hw );
			DST.push( y / -hh );
		}
		return;
	}

	var DST = [-1,1 , 1,1 , 1,-1 , -1,-1];
	var COLOR = [0.0 , 1.0 , 0.0 , 1.0];

	(function(){
		// compile shader
		var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);

		var a_xy = GL.getAttribLocation(SHADER, 'a_xy');

		var u_color = GL.getUniformLocation(SHADER, 'u_color');
		GL.uniform4fv(u_color, COLOR);

		function quadDraw()
		{
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(DST), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(a_xy);
			GL.vertexAttribPointer(a_xy, 2, GL.FLOAT, false, 0, 0);

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
})();
