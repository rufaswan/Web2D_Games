'use strict';

var GL = CANVAS.getContext('webgl');

(function(){
	if ( ! GL )  return;

	var vert_src = `
		precision highp float;
		attribute vec2 a_uv;
		attribute vec3 a_xyz;
		varying   vec2 v_uv;

		void main(void){
			v_uv = a_uv;
			gl_Position = vec4(a_xyz, 1.0);
		}
	`;
	var frag_src = `
		precision highp float;
		uniform sampler2D u_tex;
		varying vec2      v_uv;

		void main(void){
			gl_FragColor = texture2D(u_tex, v_uv);
		}
	`;

	function createTexById(id){
		var img = document.getElementById(id);
		var tex = GL.createTexture();
		GL.bindTexture  (GL.TEXTURE_2D, tex);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_S    , GL.CLAMP_TO_EDGE);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_WRAP_T    , GL.CLAMP_TO_EDGE);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MIN_FILTER, GL.NEAREST);
		GL.texParameteri(GL.TEXTURE_2D, GL.TEXTURE_MAG_FILTER, GL.NEAREST);
		GL.texImage2D   (GL.TEXTURE_2D, 0, GL.RGBA, GL.RGBA, GL.UNSIGNED_BYTE, img);
		return tex;
	}

	function getDstCorner(){
		var box = CANVAS.getBoundingClientRect();
		var hw  = box.width  / 2;
		var hh  = box.height / 2;
		for ( var i=0; i < 8; i += 2 )
		{
			var cnr = document.getElementById('corner'+i).getBoundingClientRect();
			var x = cnr.left - box.left - hw;
			var y = cnr.top  - box.top  - hh;
			DST[i+0] = x /  hw;
			DST[i+1] = y / -hh;
		}
		return;
	}

	var DST = [0,0 , 0,0 , 0,0 , 0,0];
	var SRC = [0,0 , 1,0 , 1,1 , 0,1];

	(function(){
		// compile shader
		var vert_shader = GL.createShader(GL.VERTEX_SHADER);
		GL.shaderSource (vert_shader, vert_src);
		GL.compileShader(vert_shader);

		var frag_shader = GL.createShader(GL.FRAGMENT_SHADER);
		GL.shaderSource (frag_shader, frag_src);
		GL.compileShader(frag_shader);

		var SHADER = GL.createProgram();
		GL.attachShader(SHADER, vert_shader);
		GL.attachShader(SHADER, frag_shader);
		GL.linkProgram (SHADER);
		GL.useProgram  (SHADER);

		function glattr( attr, data, cnt ){
			var loc = GL.getAttribLocation(SHADER, attr);
			var buf = GL.createBuffer();
			GL.bindBuffer(GL.ARRAY_BUFFER, buf);
			GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(data), GL.STATIC_DRAW);
			GL.enableVertexAttribArray(loc);
			GL.vertexAttribPointer(loc, cnt, GL.FLOAT, false, 0, 0);
			return;
		}

		var TEX = createTexById('Mona_Lisa_png');
		var u_tex = GL.getUniformLocation(SHADER, "u_tex");
		GL.uniform1i(u_tex, 0);
		GL.activeTexture(GL.TEXTURE0);
		GL.bindTexture(GL.TEXTURE_2D, TEX);

		function tessTest()
		{
			function cross( v1, v2 )
			{
				var crx = [
					v1[1]*v2[2] - v2[1]*v1[2], // by*cz - cy*bz
					v1[2]*v2[0] - v2[2]*v1[0], // bz*cx - cz*bx
					v1[0]*v2[1] - v2[0]*v1[1], // bx*cy - cx*by
				];
				return crx;
			}

			function isPointInLine( pt, v1, v2 )
			{
				var x1 = Math.min(v1[0], v2[0]);
				var y1 = Math.min(v1[1], v2[1]);
				var x2 = Math.max(v1[0], v2[0]);
				var y2 = Math.max(v1[1], v2[1]);

				if ( pt[0] < x1 || pt[0] > x2 )
					return false;
				if ( pt[1] < y1 || pt[1] > y2 )
					return false;
				return true;
			}

			function isLinesIntersect(quad, c1a, c1b, c2a, c2b)
			{
				var v1a = [ quad[c1a*2+0] , quad[c1a*2+1] , 1 ];
				var v1b = [ quad[c1b*2+0] , quad[c1b*2+1] , 1 ];
				var v2a = [ quad[c2a*2+0] , quad[c2a*2+1] , 1 ];
				var v2b = [ quad[c2b*2+0] , quad[c2b*2+1] , 1 ];

				var crx = cross( cross(v1a,v1b) , cross(v2a,v2b) );
				var z = crx.pop();
				if ( z === 0 )
					return -1;

				crx[0] /= z;
				crx[1] /= z;
				if ( isPointInLine(crx,v1a,v1b) && isPointInLine(crx,v2a,v2b) )
					return crx;
				return -1;
			}

			function quadArea( quad, c0, c1, c2, c3 )
			{
				var r1, r2, r3;
				r1 = quad[c0*2+0] * (quad[c1*2+1] - quad[c2*2+1])
				r2 = quad[c1*2+0] * (quad[c2*2+1] - quad[c0*2+1])
				r3 = quad[c2*2+0] * (quad[c0*2+1] - quad[c1*2+1])
				var t1 = 0.5 * (r1 + r2 + r3);

				r1 = quad[c0*2+0] * (quad[c2*2+1] - quad[c3*2+1])
				r2 = quad[c2*2+0] * (quad[c3*2+1] - quad[c0*2+1])
				r3 = quad[c3*2+0] * (quad[c0*2+1] - quad[c2*2+1])
				var t2 = 0.5 * (r1 + r2 + r3);

				return Math.abs(t1) + Math.abs(t2);
			}

			// normal rect
			var intr = isLinesIntersect(DST, 0, 2, 1, 3);
			if ( intr !== -1 )
			{
				var uv = [
					SRC[0],SRC[1] , SRC[2],SRC[3] , SRC[4],SRC[5],
					SRC[0],SRC[1] , SRC[4],SRC[5] , SRC[6],SRC[7],
				];
				var xyz = [
					DST[0],DST[1],1 , DST[2],DST[3],1 , DST[4],DST[5],1,
					DST[0],DST[1],1 , DST[4],DST[5],1 , DST[6],DST[7],1,
				];

				glattr('a_uv' , uv , 2);
				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 6);
				return;
			}

			// twisted top-bottom
			//  0-1
			//   X
			//  2-3
			var intr = isLinesIntersect(DST, 0, 3, 1, 2);
			if ( intr !== -1 )
			{
				var sc0123 = [
					(SRC[0] + SRC[2] + SRC[4] + SRC[6]) / 4,
					(SRC[1] + SRC[3] + SRC[5] + SRC[7]) / 4,
				];
				var sc03 = [ (SRC[0] + SRC[6])/2 , (SRC[1] + SRC[7])/2 ];
				var sc12 = [ (SRC[2] + SRC[4])/2 , (SRC[3] + SRC[5])/2 ];
				var dc03 = [ (DST[0] + DST[6])/2 , (DST[1] + DST[7])/2 ];
				var dc12 = [ (DST[2] + DST[4])/2 , (DST[3] + DST[5])/2 ];

				var uv = [
					sc0123[0],sc0123[1] ,  SRC[0], SRC[1] ,  SRC[2], SRC[3],
					sc0123[0],sc0123[1] ,  SRC[2], SRC[3] , sc12[0],sc12[1],
					sc0123[0],sc0123[1] , sc12[0],sc12[1] ,  SRC[4], SRC[5],
					sc0123[0],sc0123[1] ,  SRC[4], SRC[5] ,  SRC[6], SRC[7],
					sc0123[0],sc0123[1] ,  SRC[6], SRC[7] , sc03[0],sc03[1],
					sc0123[0],sc0123[1] , sc03[0],sc03[1] ,  SRC[0], SRC[1],
				];
				var xyz = [
					intr[0],intr[1],0 ,  DST[0], DST[1],1 ,  DST[2], DST[3],1,
					intr[0],intr[1],0 ,  DST[2], DST[3],1 , dc12[0],dc12[1],1,
					intr[0],intr[1],0 , dc12[0],dc12[1],1 ,  DST[4], DST[5],1,
					intr[0],intr[1],0 ,  DST[4], DST[5],1 ,  DST[6], DST[7],1,
					intr[0],intr[1],0 ,  DST[6], DST[7],1 , dc03[0],dc03[1],1,
					intr[0],intr[1],0 , dc03[0],dc03[1],1 ,  DST[0], DST[1],1,
				];

				glattr('a_uv' , uv , 2);
				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 18);
				return;
			}

			// twisted left-right
			//  0 2
			//  |X|
			//  3 1
			var intr = isLinesIntersect(DST, 0, 1, 3, 2);
			if ( intr !== -1 )
			{
				var sc0123 = [
					(SRC[0] + SRC[2] + SRC[4] + SRC[6]) / 4,
					(SRC[1] + SRC[3] + SRC[5] + SRC[7]) / 4,
				];
				var sc01 = [ (SRC[0] + SRC[2])/2 , (SRC[1] + SRC[3])/2 ];
				var sc23 = [ (SRC[4] + SRC[6])/2 , (SRC[5] + SRC[7])/2 ];
				var dc01 = [ (DST[0] + DST[2])/2 , (DST[1] + DST[3])/2 ];
				var dc23 = [ (DST[4] + DST[6])/2 , (DST[5] + DST[7])/2 ];

				var uv = [
					sc0123[0],sc0123[1] ,  SRC[0], SRC[1] , sc01[0],sc01[1],
					sc0123[0],sc0123[1] , sc01[0],sc01[1] ,  SRC[2], SRC[3],
					sc0123[0],sc0123[1] ,  SRC[2], SRC[3] ,  SRC[4], SRC[5],
					sc0123[0],sc0123[1] ,  SRC[4], SRC[5] , sc23[0],sc23[1],
					sc0123[0],sc0123[1] , sc23[0],sc23[1] ,  SRC[6], SRC[7],
					sc0123[0],sc0123[1] ,  SRC[6], SRC[7] ,  SRC[0], SRC[1],
				];
				var xyz = [
					intr[0],intr[1],0 ,  DST[0], DST[1],1 , dc01[0],dc01[1],1,
					intr[0],intr[1],0 , dc01[0],dc01[1],1 ,  DST[2], DST[3],1,
					intr[0],intr[1],0 ,  DST[2], DST[3],1 ,  DST[4], DST[5],1,
					intr[0],intr[1],0 ,  DST[4], DST[5],1 , dc23[0],dc23[1],1,
					intr[0],intr[1],0 , dc23[0],dc23[1],1 ,  DST[6], DST[7],1,
					intr[0],intr[1],0 ,  DST[6], DST[7],1 ,  DST[0], DST[1],1,
				];

				glattr('a_uv' , uv , 2);
				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 18);
				return;
			}

			// arrow
			var area1 = quadArea(DST, 0, 1, 2, 3);
			var area2 = quadArea(DST, 1, 2, 3, 0);
			if ( area1 < area2 )
			{
				var uv = [
					SRC[0],SRC[1] , SRC[2],SRC[3] , SRC[4],SRC[5],
					SRC[0],SRC[1] , SRC[4],SRC[5] , SRC[6],SRC[7],
				];
				var xyz = [
					DST[0],DST[1],1 , DST[2],DST[3],1 , DST[4],DST[5],1,
					DST[0],DST[1],1 , DST[4],DST[5],1 , DST[6],DST[7],1,
				];

				glattr('a_uv' , uv , 2);
				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 6);
				return;
			}
			else
			{
				// 1,2,3  1,3,0
				var uv = [
					SRC[2],SRC[3] , SRC[4],SRC[5] , SRC[6],SRC[7],
					SRC[2],SRC[3] , SRC[6],SRC[7] , SRC[0],SRC[1],
				];
				var xyz = [
					DST[2],DST[3],1 , DST[4],DST[5],1 , DST[6],DST[7],1,
					DST[2],DST[3],1 , DST[6],DST[7],1 , DST[0],DST[1],1,
				];

				glattr('a_uv' , uv , 2);
				glattr('a_xyz', xyz, 3);
				GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
				GL.drawArrays(GL.TRIANGLES, 0, 6);
				return;
			}
		} // tessTest()

		setInterval(function(){
			getDstCorner();
			tessTest()
/*
			var uv = [
				SRC[0],SRC[1] , SRC[2],SRC[3] , SRC[4],SRC[5],
				SRC[0],SRC[1] , SRC[4],SRC[5] , SRC[6],SRC[7],
			];
			var xyz = [
				DST[0],DST[1],1 , DST[2],DST[3],1 , DST[4],DST[5],1,
				DST[0],DST[1],1 , DST[4],DST[5],1 , DST[6],DST[7],1,
			];

			glattr('a_uv' , uv , 2);
			glattr('a_xyz', xyz, 3);
			GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
			GL.drawArrays(GL.TRIANGLES, 0, 6);
*/
			//console.log(DST, SRC);
		}, 500);
	})();
})();

