'use strict';

var QDFN = QDFN || {};

(function($){

	$.webGLContextById = function( id ){
		var opt = {
			alpha                 : true,
			antialias             : true,
			preserveDrawingBuffer : true,
			depth                 : false,
			stencil               : false,
			premultipliedAlpha    : false,
		};
		return document.getElementById(id).getContext('webgl', opt);
	}

	$.shaderProgram = function( GL, vert_src, frag_src ){
		var vert_shader = GL.createShader(GL.VERTEX_SHADER);
		GL.shaderSource (vert_shader, vert_src);
		GL.compileShader(vert_shader);
		var t = GL.getShaderParameter(vert_shader, GL.COMPILE_STATUS);
		if ( ! t )
			console.error( GL.getShaderInfoLog(vert_shader) );

		var frag_shader = GL.createShader(GL.FRAGMENT_SHADER);
		GL.shaderSource (frag_shader, frag_src);
		GL.compileShader(frag_shader);
		var t = GL.getShaderParameter(frag_shader, GL.COMPILE_STATUS);
		if ( ! t )
			console.error( GL.getShaderInfoLog(frag_shader) );

		var prog = GL.createProgram();
		GL.attachShader(prog, vert_shader);
		GL.attachShader(prog, frag_shader);
		GL.linkProgram (prog);
		var t = GL.getProgramParameter(prog, GL.LINK_STATUS);
		if ( ! t )
			console.error( GL.getProgramInfoLog(prog) );

		GL.useProgram(prog);
		return prog;
	}

	$.tex2DById = function( GL, id ){
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

	$.quad2vec3 = function( q ){
		var vec = [
			[ q[0], q[1] , 1 ],
			[ q[2], q[3] , 1 ],
			[ q[4], q[5] , 1 ],
			[ q[6], q[7] , 1 ],
		];
		return vec;
	}

	$.cross3D = function( v1, v2 ){
		var crx = [
			v1[1]*v2[2] - v2[1]*v1[2], // by*cz - cy*bz
			v1[2]*v2[0] - v2[2]*v1[0], // bz*cx - cz*bx
			v1[0]*v2[1] - v2[0]*v1[1], // bx*cy - cx*by
		];
		return crx;
	}

	$.quad2tri = function( quad ){
		// 0 1
		// 3 2
		var tri = [
			$.cross3D( $.cross3D(quad[0],quad[2]) , $.cross3D(quad[1],quad[3]) ), // corner-corner
			$.cross3D( $.cross3D(quad[0],quad[1]) , $.cross3D(quad[3],quad[2]) ), //    top-bottom
			$.cross3D( $.cross3D(quad[0],quad[3]) , $.cross3D(quad[1],quad[2]) ), //   left-right
		];
		return tri;
	}

	$.matrix_det2 = function( M ){
		var d = M[0]*M[3] - M[1]*M[2];
		return d;
	}

	$.matrix_inv3 = function( M ){
		var Mdet = [
			$.matrix_det2( [ M[4],M[5] , M[7],M[8] ] ) ,
			$.matrix_det2( [ M[3],M[5] , M[6],M[8] ] ) ,
			$.matrix_det2( [ M[3],M[4] , M[6],M[7] ] ) ,

			$.matrix_det2( [ M[1],M[2] , M[7],M[8] ] ) ,
			$.matrix_det2( [ M[0],M[2] , M[6],M[8] ] ) ,
			$.matrix_det2( [ M[0],M[1] , M[6],M[7] ] ) ,

			$.matrix_det2( [ M[1],M[2] , M[4],M[5] ] ) ,
			$.matrix_det2( [ M[0],M[2] , M[3],M[5] ] ) ,
			$.matrix_det2( [ M[0],M[1] , M[3],M[4] ] ) ,
		];

		var Mco = [
			 Mdet[0] , -Mdet[3] ,  Mdet[6] ,
			-Mdet[1] ,  Mdet[4] , -Mdet[7] ,
			 Mdet[2] , -Mdet[5] ,  Mdet[8] ,
		];

		var d = M[0]*Mco[0] + M[1]*Mco[3] + M[2]*Mco[6];
		var dinv = 1 / d;

		for ( var i=0; i < 9; i++ )
			Mco[i] *= dinv;
		return Mco;
	}

	$.matrix_multi33 = function( M1, M2 ){
		//            | 1 0 0 |
		// M * Minv = | 0 1 0 | or identify matrix
		//            | 0 0 1 |
		var M = [
			M1[0]*M2[0] + M1[1]*M2[3] + M1[2]*M2[6],
			M1[0]*M2[1] + M1[1]*M2[4] + M1[2]*M2[7],
			M1[0]*M2[2] + M1[1]*M2[5] + M1[2]*M2[8],

			M1[3]*M2[0] + M1[4]*M2[3] + M1[5]*M2[6],
			M1[3]*M2[1] + M1[4]*M2[4] + M1[5]*M2[7],
			M1[3]*M2[2] + M1[4]*M2[5] + M1[5]*M2[8],

			M1[6]*M2[0] + M1[7]*M2[3] + M1[8]*M2[6],
			M1[6]*M2[1] + M1[7]*M2[4] + M1[8]*M2[7],
			M1[6]*M2[2] + M1[7]*M2[5] + M1[8]*M2[8],
		];
		return M;
	}

	$.triMat3 = function( stri, dtri ){
		//   | H1x H2x H3x |   | h1x h2x h3x |
		// M | H1y H2y H3y | = | h1y h2y h3y |
		//   | H1z H2z H3z |   | h1z h2z h3z |
		//                MH = h
		//                M  = hH^-1
		var H = [
			stri[0][0] , stri[1][0] , stri[2][0] ,
			stri[0][1] , stri[1][1] , stri[2][1] ,
			stri[0][2] , stri[1][2] , stri[2][2] ,
		];

		var h = [
			dtri[0][0] , dtri[1][0] , dtri[2][0] ,
			dtri[0][1] , dtri[1][1] , dtri[2][1] ,
			dtri[0][2] , dtri[1][2] , dtri[2][2] ,
		];

		var Hinv = $.matrix_inv3(H);
		var M    = $.matrix_multi33(h, Hinv);
		var Minv = $.matrix_inv3(M);
		return Minv;
	}

	$.quadMat3 = function( src, dst ){
		var stri = $.quad2tri(src);
		var dtri = $.quad2tri(dst);
		return $.triMat3(stri, dtri);
	}

	$.isPointInLine = function( pt, v1, v2 ){
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

	$.pointIntersect = function( v1a, v1b, v2a, v2b ){
		var crx = $.cross3D( $.cross3D(v1a,v1b) , $.cross3D(v2a,v2b) );
		if ( crx[2] === 0 )
			return -1;

		crx[0] /= crx[2];
		crx[1] /= crx[2];
		crx[2] /= crx[2];
		if ( ! $.isPointInLine(crx,v1a,v1b) )  return -1;
		if ( ! $.isPointInLine(crx,v2a,v2b) )  return -1;
		return crx;
	}

	$.triadArea = function( v0, v1, v2 )
	{
		var r1 = v0[0] * (v1[1] - v2[1]); // ax * (by-cy)
		var r2 = v1[0] * (v2[1] - v0[1]); // bx * (cy-ay)
		var r3 = v2[0] * (v0[1] - v1[1]); // cx * (ay-by)
		var area = 0.5 * (r1 + r2 + r3);
		return Math.abs(area);
	}

	$.quadArea = function( v0, v1, v2, v3 ){
		// 0,1,2  0,2,3
		var t1 = $.triadArea(v0, v1, v2);
		var t2 = $.triadArea(v0, v2, v3);
		return t1 + t2;
	}

/*
	$.cross2D = function( v1, v2 ){
		if ( v1.length < 3 )  v1.push(1);
		if ( v2.length < 3 )  v2.push(1);
		var crx = $.cross3D(v1, v2);
		var z = crx.pop();
		if ( z === 0 )
			return -1;
		crx[0] /= z;
		crx[1] /= z;
		return crx;
	}

	$.splitUV = function( v2 ){
		if ( (v2.length % 2) !== 0 )
			return -1;
		var uv = new Array( v2.length / 2 );

		var i = 0;
		for ( var j=0; j < v2.length; j += 2 )
		{
			uv[i] = [ v2[j+0] , v2[j+1] ];
			i++;
		}
		return uv;
	}

	$.splitXYZ = function( v3 ){
		if ( (v3.length % 3) !== 0 )
			return -1;
		var xyz = new Array( v3.length / 3 );

		var i = 0;
		for ( var j=0; j < v3.length; j += 3 )
		{
			xyz[i] = [ v3[j+0] , v3[j+1] , v3[j+2] ];
			i++;
		}
		return xyz;
	}

	$.joinUV = function(){
		for ( var i=1; i < arguments.length; i++ )
		{
			arguments[0].push( arguments[i][0] );
			arguments[0].push( arguments[i][1] );
		}
		return;
	}

	$.joinXYZ = function(){
		for ( var i=1; i < arguments.length; i++ )
		{
			var z = arguments[i][2] || 1;
			arguments[0].push( arguments[i][0] );
			arguments[0].push( arguments[i][1] );
			arguments[0].push( z );
		}
		return;
	}

	$.avgUV = function(){
		var avg = [0,0];
		for ( var i=0; i < arguments.length; i++ )
		{
			avg[0] += arguments[i][0];
			avg[1] += arguments[i][1];
		}
		avg[0] /= arguments.length;
		avg[1] /= arguments.length;
		return avg;
	}

	$.avgXYZ = function(){
		var avg = [0,0];
		for ( var i=0; i < arguments.length; i++ )
		{
			var z = arguments[i][2] || 1;
			avg[0] += arguments[i][0];
			avg[1] += arguments[i][1];
			avg[2] += z;
		}
		avg[0] /= arguments.length;
		avg[1] /= arguments.length;
		avg[2] /= arguments.length;
		return avg;
	}

	$.distIntersect = function( v1a, v1b, v2a, v2b ){
		var pt = $.pointIntersect(v1a, v1b, v2a, v2b);
		if ( pt === -1 )
			return -1;

		// return distance
		var d1 = [ v1a[0]-v1b[0] , v1a[1]-v1b[1] ];
		var d2 = [ v2a[0]-v2b[0] , v2a[1]-v2b[1] ];
		return [
			[ Math.abs( (v1a[0]-x)/d1[0] ) , Math.abs( (v1a[1]-y)/d1[1] ) ],
			[ Math.abs( (v1b[0]-x)/d1[0] ) , Math.abs( (v1b[1]-y)/d1[1] ) ],
			[ Math.abs( (v2a[0]-x)/d2[0] ) , Math.abs( (v2a[1]-y)/d2[1] ) ],
			[ Math.abs( (v2b[0]-x)/d2[0] ) , Math.abs( (v2b[1]-y)/d2[1] ) ],
		];
	}
*/

	$.matrix_multi13 = function( V, M ){
		var VM = [
			V[0]*M[0] + V[1]*M[3] + V[2]*M[6] ,
			V[0]*M[1] + V[1]*M[4] + V[2]*M[7] ,
			V[0]*M[2] + V[1]*M[5] + V[2]*M[8] ,
		];
		return VM;
	}

	$.matrix_multi31 = function( M, V ){
		var MV = [
			M[0]*V[0] + M[1]*V[1] + M[2]*V[2] ,
			M[3]*V[0] + M[4]*V[1] + M[5]*V[2] ,
			M[6]*V[0] + M[7]*V[1] + M[8]*V[2] ,
		];
		return MV;
	}

})(QDFN);
