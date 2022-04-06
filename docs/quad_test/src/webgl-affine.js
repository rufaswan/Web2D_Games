'use strict';

var GL = CANVAS.getContext('webgl');

(function(){
	if ( ! GL )  return;

	var vert_src = `
		precision highp float;
		precision highp int;
		attribute vec2 a_xy;
		attribute vec2 a_uv;
		varying   vec2 v_uv;

		void main(void){
			v_uv = a_uv;
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		uniform sampler2D u_tex;
		varying vec2      v_uv;

		void main(void){
			gl_FragColor = texture2D(u_tex, v_uv);
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
	var SRC = [ 0,0 , 1,0 , 1, 1 ,  0, 1];

	(function(){
		// compile shader
		var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);

		var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
		var u_tex = GL.getUniformLocation(SHADER, 'u_tex');
		GL.uniform1i(u_tex, 0);
		GL.activeTexture(GL.TEXTURE0);
		GL.bindTexture(GL.TEXTURE_2D, TEX);

		var a_xy = GL.getAttribLocation(SHADER, 'a_xy');
		var a_uv = GL.getAttribLocation(SHADER, 'a_uv');

		function getcx( v3 )
		{
			var intr = QDFN.pointIntersect(v3[0], v3[2], v3[1], v3[3]);
			if ( intr !== -1 )
				return intr;
			var intr = QDFN.pointIntersect(v3[0], v3[3], v3[1], v3[2]);
			if ( intr !== -1 )
				return intr;
			var intr = QDFN.pointIntersect(v3[0], v3[1], v3[3], v3[2]);
			if ( intr !== -1 )
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

		function xyBuf( loc, norm )
		{
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(norm), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(loc);
			GL.vertexAttribPointer(loc, 2, GL.FLOAT, false, 0, 0);
			//console.log(norm);
			return;
		}

		function quadDraw()
		{
			var sqd = QDFN.quad2vec3(SRC);
			var dqd = QDFN.quad2vec3(DST);
			var scx = getcx(sqd);
			var dcx = getcx(dqd);

			if ( dcx !== -1 && scx !== -1 )
			{
				var uv = [];
				addBuffer(uv, scx, sqd[0], sqd[1]);
				addBuffer(uv, scx, sqd[1], sqd[2]);
				addBuffer(uv, scx, sqd[2], sqd[3]);
				addBuffer(uv, scx, sqd[3], sqd[0]);
				xyBuf(a_uv, uv);

				var xy = [];
				addBuffer(xy, dcx, dqd[0], dqd[1]);
				addBuffer(xy, dcx, dqd[1], dqd[2]);
				addBuffer(xy, dcx, dqd[2], dqd[3]);
				addBuffer(xy, dcx, dqd[3], dqd[0]);
				xyBuf(a_xy, xy);

				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 12);
				return;
			}

			var area1 = QDFN.quadArea(dqd[0], dqd[1], dqd[2], dqd[3]);
			var area2 = QDFN.quadArea(dqd[1], dqd[2], dqd[3], dqd[0]);
			var uv = [];
			var xy = [];

			if ( area1 < area2 )
			{
				addBuffer(uv, sqd[0], sqd[1], sqd[2]);
				addBuffer(uv, sqd[0], sqd[2], sqd[3]);
				addBuffer(xy, dqd[0], dqd[1], dqd[2]);
				addBuffer(xy, dqd[0], dqd[2], dqd[3]);
			}
			else
			{
				addBuffer(uv, sqd[1], sqd[2], sqd[3]);
				addBuffer(uv, sqd[1], sqd[3], sqd[0]);
				addBuffer(xy, dqd[1], dqd[2], dqd[3]);
				addBuffer(xy, dqd[1], dqd[3], dqd[0]);
			}
			xyBuf(a_uv, uv);
			xyBuf(a_xy, xy);

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
})();
