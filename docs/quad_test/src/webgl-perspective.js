'use strict';

var GL = CANVAS.getContext('webgl');

(function(){
	if ( ! GL )  return;

	var vert_src = `
		precision highp float;
		precision highp int;
		attribute vec2 a_xy;
		varying   vec2 v_xy;

		void main(void){
			v_xy = a_xy;
			gl_Position = vec4(a_xy.x, a_xy.y, 1.0, 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		uniform sampler2D u_tex;
		uniform mat3      u_mat3;
		varying vec2      v_xy;

		void main(void){
			vec3 v3 = vec3(v_xy.x, v_xy.y, 1.0) * u_mat3;
			v3.x = v3.x / v3.z;
			v3.y = v3.y / v3.z;

			//if ( v3.x > 1.0 )  discard;
			//if ( v3.y > 1.0 )  discard;
			gl_FragColor = texture2D(u_tex, v3.xy);
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

		var u_mat3 = GL.getUniformLocation(SHADER, 'u_mat3');
		var a_xy   = GL.getAttribLocation (SHADER, 'a_xy');

		function xyBuffer( dst )
		{
			var cnt = [
				(dst[0][0] + dst[1][0] + dst[2][0] + dst[3][0]) * 0.25,
				(dst[0][1] + dst[1][1] + dst[2][1] + dst[3][1]) * 0.25,
				1
			];
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

			// folded back
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

			var mat3 = QDFN.quadMat3(svec, dvec);
			GL.uniformMatrix3fv(u_mat3, false, mat3);

			var xy = xyBuffer(dvec);
			var t = [
				xy[0][0],xy[0][1] , xy[1][0],xy[1][1] , xy[2][0],xy[2][1] ,
				xy[3][0],xy[3][1] , xy[4][0],xy[4][1] , xy[5][0],xy[5][1] ,
			];

			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(t), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(a_xy);
			GL.vertexAttribPointer(a_xy, 2, GL.FLOAT, false, 0, 0);

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
