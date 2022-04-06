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

			v3.x = abs(v3.x / v3.z);
			v3.y = abs(v3.y / v3.z);
			if ( v3.x > 1.0 )  discard;
			if ( v3.y > 1.0 )  discard;
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

		function bufferdraw( xy )
		{
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(xy), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(a_xy);
			GL.vertexAttribPointer(a_xy, 2, GL.FLOAT, false, 0, 0);

			GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
			GL.drawArrays(GL.TRIANGLES, 0, 6);
			return;
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
			bufferdraw(t);

/*
			var t1 = [
				QDFN.matrix_multi13(xy[0], mat3),
			];
			var t2 = [
				QDFN.matrix_multi31(mat3, xy[0]),
			];
			console.log(xy, t1, t2);
			console.log(xy);
			console.log(mat3, t1, t2);
		function glattr( attr, data, cnt ){
			var loc = GL.getAttribLocation(SHADER, attr);
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(data), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(loc);
			GL.vertexAttribPointer(loc, cnt, GL.FLOAT, false, 0, 0);
			return;
		}

			function drawtri( stri, dtri )
			{
				var mat3 = QDFN.triMat3(stri, dtri);
				GL.uniformMatrix3fv(u_mat3, false, mat3);

				var xyz = [];
				QDFN.joinXYZ(xyz, dtri[0], dtri[1], dtri[2]);

				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 3);
				return;
			}

			function drawquad( sqd, dqd )
			{
				var stri = QDFN.quad2tri(sqd);
				var dtri = QDFN.quad2tri(dqd);
				var mat3 = QDFN.triMat3(stri, dtri);
				GL.uniformMatrix3fv(u_mat3, false, mat3);

				var xyz = [];
				QDFN.joinXYZ(xyz, dqd[0], dqd[1], dqd[2]);
				QDFN.joinXYZ(xyz, dqd[0], dqd[2], dqd[3]);

				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 6);
				return;
			}

			// normal rect
			// 0-1
			// | |
			// 3-2
			var intr = QDFN.pointIntersect(DST[0], DST[2], DST[1], DST[3]);
			if ( intr !== -1 )
				return drawquad(SRC, DST);

			// twisted top-bottom
			//  0-1
			//   X
			//  2-3
			var intr = QDFN.pointIntersect(DST[0], DST[3], DST[1], DST[2]);
			if ( intr !== -1 )
			{
				var s12 = QDFN.avgUV(SRC[1], SRC[2]);
				var s30 = QDFN.avgUV(SRC[3], SRC[0]);
					s12[2] = 1;
					s30[2] = 1;
				var crx1 = QDFN.cross3D(s12, s30);

				var stri = [
					crx1,
					SRC[0],
					SRC[1]
				];
				var dtri = [
					intr,
					DST[0],
					DST[1]
				];
				drawtri(stri, dtri);

				var stri = [ crx1, SRC[3], SRC[2] ];
				var dtri = [ intr, DST[3], DST[2] ];
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
				var s01 = QDFN.avgUV(SRC[0], SRC[1]);
				var s23 = QDFN.avgUV(SRC[2], SRC[3]);
					s01[2] = 1;
					s23[2] = 1;
				var crx1 = QDFN.cross3D(s01, s23);

				var stri = [ crx1, SRC[3], SRC[0]  ];
				var dtri = [ intr, DST[3], DST[0] ];
				drawtri(stri, dtri);

				var stri = [ crx1, SRC[2], SRC[1] ];
				var dtri = [ intr, DST[2], DST[1] ];
				drawtri(stri, dtri);
				return;
			}

			// folded back
			var area1 = QDFN.quadArea(DST[0], DST[1], DST[2], DST[3]);
			var area2 = QDFN.quadArea(DST[1], DST[2], DST[3], DST[0]);
			if ( area1 < area2 )
			{
				// 0,1,2  0,2,3
				var stri = [ SRC[0], SRC[1], SRC[2] ];
				var dtri = [ DST[0], DST[1], DST[2] ];
				drawtri(stri, dtri);

				var stri = [ SRC[0], SRC[2], SRC[3] ];
				var dtri = [ DST[0], DST[2], DST[3] ];
				drawtri(stri, dtri);
				return;
			}
			else
			{
				// 1,2,3  1,3,0
				var stri = [ SRC[1], SRC[2], SRC[3] ];
				var dtri = [ DST[1], DST[2], DST[3] ];
				drawtri(stri, dtri);

				var stri = [ SRC[1], SRC[3], SRC[0] ];
				var dtri = [ DST[1], DST[3], DST[0] ];
				drawtri(stri, dtri);
				return;
			}
*/
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
