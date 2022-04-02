'use strict';

var GL = CANVAS.getContext('webgl');

(function(){
	if ( ! GL )  return;

	var vert_src = `
		precision highp float;
		precision highp int;
		attribute vec3 a_xyz;
		varying   vec3 v_xyz;

		void main(void){
			v_xyz = a_xyz;
			gl_Position = vec4(a_xyz, 1.0);
		}
	`;

	var frag_src = `
		precision highp float;
		precision highp int;
		uniform sampler2D u_tex;
		uniform mat3      u_mat3;
		varying vec3      v_xyz;

		void main(void){
			vec3 off = u_mat3 * v_xyz;
			vec2 uv  = off.xy / off.z;
			gl_FragColor = texture2D(u_tex, uv);
		}
	`;

	function getDstCorner(){
		var box = CANVAS.getBoundingClientRect();
		var hw  = box.width  / 2;
		var hh  = box.height / 2;
		DST = new Array(4);
		for ( var i=0; i < 4; i++ )
		{
			var cnr = document.getElementById('corner'+i).getBoundingClientRect();
			var x = cnr.left - box.left - hw;
			var y = cnr.top  - box.top  - hh;
			DST[i] = [ x / hw, y / -hh , 1];
		}
		return;
	}

	var DST = [[-1,1,1] , [1,1,1] , [1,-1,1] , [-1,-1,1]];
	var SRC = [[ 0,0  ] , [1,0  ] , [1, 1  ] , [ 0, 1  ]];

	(function(){
		// compile shader
		var SHADER = QDFN.shaderProgram(GL, vert_src, frag_src);

		function glattr( attr, data, cnt ){
			var loc = GL.getAttribLocation(SHADER, attr);
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(data), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(loc);
			GL.vertexAttribPointer(loc, cnt, GL.FLOAT, false, 0, 0);
			return;
		}

		var TEX = QDFN.tex2DById(GL, 'Mona_Lisa_png');
		var u_tex = GL.getUniformLocation(SHADER, "u_tex");
		GL.uniform1i(u_tex, 0);
		GL.activeTexture(GL.TEXTURE0);
		GL.bindTexture(GL.TEXTURE_2D, TEX);

		var u_mat3 = GL.getUniformLocation(SHADER, "u_mat3");

		function drawtri( stri, dtri )
		{
			var mat3 = QDFN.matrix3_transform(stri, dtri);
			GL.uniformMatrix3fv(u_mat3, false, mat3);

			var xyz = [];
			QDFN.joinXYZ(xyz, dtri[0], dtri[1], dtri[2]);

			glattr('a_xyz', xyz, 3);
			GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
			GL.drawArrays(GL.TRIANGLES, 0, 3);
			return;
		}

		function tessTest()
		{
			// normal rect
			// 0-1
			// | |
			// 3-2
			var intr = QDFN.pointIntersect(DST[0], DST[2], DST[1], DST[3]);
			if ( intr !== -1 )
			{
				var stri = QDFN.quad2tri(SRC);
				var dtri = QDFN.quad2tri(DST);
				var mat3 = QDFN.matrix3_transform(stri, dtri);
				GL.uniformMatrix3fv(u_mat3, false, mat3);

				var xyz = [];
				QDFN.joinXYZ(xyz, DST[0], DST[1], DST[2]);
				QDFN.joinXYZ(xyz, DST[0], DST[2], DST[3]);

				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 6);
				return;
			}

			// twisted top-bottom
			//  0-1
			//   X
			//  2-3
			var intr = QDFN.pointIntersect(DST[0], DST[3], DST[1], DST[2]);
			if ( intr !== -1 )
			{
				intr[2] = 1;
				var scx = QDFN.avgUV(SRC[0], SRC[1], SRC[2], SRC[3]);
				var s12 = QDFN.avgUV(SRC[1], SRC[2]);
				var s30 = QDFN.avgUV(SRC[3], SRC[0]);
					scx[2] = 1;
					s12[2] = 1;
					s30[2] = 1;
				var d12 = QDFN.avgXYZ(DST[1], DST[2]);
				var d30 = QDFN.avgXYZ(DST[3], DST[0]);

				// p,0,1  p,2,3  p,1,1-2  p,1-2,2  p,3,3-0  p,3-0,0
				stri = [ scx,  SRC[0], SRC[1] ];
				dtri = [ intr, DST[0], DST[1] ];
				drawtri(stri, dtri);

				stri = [ scx,  SRC[2], SRC[3] ];
				dtri = [ intr, DST[2], DST[3] ];
				drawtri(stri, dtri);

				stri = [ scx,  SRC[1], s12 ];
				dtri = [ intr, DST[1], d12 ];
				drawtri(stri, dtri);
				stri = [ scx,  s12, SRC[2] ];
				dtri = [ intr, d12, DST[2] ];
				drawtri(stri, dtri);
				stri = [ scx,  SRC[3], s30 ];
				dtri = [ intr, DST[3], d30 ];
				drawtri(stri, dtri);
				stri = [ scx,  s30, SRC[0] ];
				dtri = [ intr, d30, DST[0] ];
				drawtri(stri, dtri);
				return;
			}

			// twisted left-right
			//  0 2
			//  |X|
			//  3 1
			var intr = QDFN.pointIntersect(DST[0], DST[1], DST[3], DST[2]);
			if ( intr !== -1 )
			{
				intr[2] = 1;
				var scx = QDFN.avgUV(SRC[0], SRC[1], SRC[2], SRC[3]);
				var s01 = QDFN.avgUV(SRC[0], SRC[1]);
				var s23 = QDFN.avgUV(SRC[2], SRC[3]);
					scx[2] = 1;
					s01[2] = 1;
					s23[2] = 1;
				var d01 = QDFN.avgXYZ(DST[0], DST[1]);
				var d23 = QDFN.avgXYZ(DST[2], DST[3]);

				// p,1,2  p,3,0  p,0,0-1  p,0-1,1  p,2,2-3  p,2-3,3
				stri = [ scx,  SRC[1], SRC[2] ];
				dtri = [ intr, DST[1], DST[2] ];
				drawtri(stri, dtri);

				stri = [ scx,  SRC[3], SRC[0] ];
				dtri = [ intr, DST[3], DST[0] ];
				drawtri(stri, dtri);

				stri = [ scx,  SRC[0], s01 ];
				dtri = [ intr, DST[0], d01 ];
				drawtri(stri, dtri);
				stri = [ scx,  s01, SRC[1] ];
				dtri = [ intr, d01, DST[1] ];
				drawtri(stri, dtri);
				stri = [ scx,  SRC[2], s23 ];
				dtri = [ intr, DST[2], d23 ];
				drawtri(stri, dtri);
				stri = [ scx,  s23, SRC[3] ];
				dtri = [ intr, d23, DST[3] ];
				drawtri(stri, dtri);
				return;
			}

			// folded back
			var area1 = QDFN.quadArea(DST[0], DST[1], DST[2], DST[3]);
			var area2 = QDFN.quadArea(DST[1], DST[2], DST[3], DST[0]);
			if ( area1 < area2 )
			{
				// 0,1,2  0,2,3
				stri = [ SRC[0], SRC[1], SRC[2] ];
				dtri = [ DST[0], DST[1], DST[2] ];
				drawtri(stri, dtri);

				stri = [ SRC[0], SRC[2], SRC[3] ];
				dtri = [ DST[0], DST[2], DST[3] ];
				drawtri(stri, dtri);
				return;
			}
			else
			{
				// 1,2,3  1,3,0
				stri = [ SRC[1], SRC[2], SRC[3] ];
				dtri = [ DST[1], DST[2], DST[3] ];
				drawtri(stri, dtri);

				stri = [ SRC[1], SRC[3], SRC[0] ];
				dtri = [ DST[1], DST[3], DST[0] ];
				drawtri(stri, dtri);
				return;
			}
		} // tessTest()

		setInterval(function(){
			if ( ! IS_CLICK )
				return;
			getDstCorner();
			tessTest()
			IS_CLICK = false;
			//console.log(DST, SRC);
		}, 100);
	})();
})();
