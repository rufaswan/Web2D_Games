
function cross(){
	// x = y1*z2 - y2*z1
	// y = z1*x2 - z2*x1
	// z = x1*y2 - x2*y1
	if ( arguments.length === 4 ){ // x1,y1,x2,y2
		var x = arguments[1]*1            - arguments[3]*1;
		var y =            1*arguments[2] -            1*arguments[0];
		var z = arguments[0]*arguments[3] - arguments[2]*arguments[1];
		return [x,y,z];
	}
	if ( arguments.length === 2 ){ // [x1,y1,z1],[x2,y2,z2]
		var x = arguments[0][1]*arguments[1][2] - arguments[1][1]*arguments[0][2];
		var y = arguments[0][2]*arguments[1][0] - arguments[1][2]*arguments[0][0];
		var z = arguments[0][0]*arguments[1][1] - arguments[1][0]*arguments[0][1];
		return [x,y,z];
	}
	return -1;
}
//////////////////////////////
function matrix_inv3( M ){
	function matrix_det2( M ){
		return (M[0]*M[3] - M[1]*M[2]);
	}

	var Mdet = [
		matrix_det2( [ M[4],M[5] , M[7],M[8] ] ) ,
		matrix_det2( [ M[3],M[5] , M[6],M[8] ] ) ,
		matrix_det2( [ M[3],M[4] , M[6],M[7] ] ) ,

		matrix_det2( [ M[1],M[2] , M[7],M[8] ] ) ,
		matrix_det2( [ M[0],M[2] , M[6],M[8] ] ) ,
		matrix_det2( [ M[0],M[1] , M[6],M[7] ] ) ,

		matrix_det2( [ M[1],M[2] , M[4],M[5] ] ) ,
		matrix_det2( [ M[0],M[2] , M[3],M[5] ] ) ,
		matrix_det2( [ M[0],M[1] , M[3],M[4] ] ) ,
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

function matrix_multi13( V, M ){
	//            | a b c |
	// | 0 1 2 |  | d e f | = | 0a+1d+2g  0b+1e+2h  0c+1f+2i |
	//            | g h i |
	var VM = [
		V[0]*M[0] + V[1]*M[3] + V[2]*M[6] ,
		V[0]*M[1] + V[1]*M[4] + V[2]*M[7] ,
		V[0]*M[2] + V[1]*M[5] + V[2]*M[8] ,
	];
	return VM;
}

function matrix_multi31( M, V ){
	// | 0 1 2 |  | a |   | 0a+1b+2c |
	// | 3 4 5 |  | b | = | 3a+4b+5c |
	// | 6 7 8 |  | c |   | 6a+7b+8c |
	var MV = [
		M[0]*V[0] + M[1]*V[1] + M[2]*V[2] ,
		M[3]*V[0] + M[4]*V[1] + M[5]*V[2] ,
		M[6]*V[0] + M[7]*V[1] + M[8]*V[2] ,
	];
	return MV;
}

function matrix_multi33( M1, M2 ){
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

function get_perspective_mat3( src, dst, inv=true ){
	// 0,1  2,3
	// 6,7  4,5
	var H1 = cross( cross(src[0],src[1] , src[4],src[5]) , cross(src[2],src[3] , src[6],src[7]) ); // corner-corner
	var H2 = cross( cross(src[0],src[1] , src[2],src[3]) , cross(src[6],src[7] , src[4],src[5]) ); //    top-bottom
	var H3 = cross( cross(src[0],src[1] , src[6],src[7]) , cross(src[2],src[3] , src[4],src[5]) ); //   left-right

	var h1 = cross( cross(dst[0],dst[1] , dst[4],dst[5]) , cross(dst[2],dst[3] , dst[6],dst[7]) ); // corner-corner
	var h2 = cross( cross(dst[0],dst[1] , dst[2],dst[3]) , cross(dst[6],dst[7] , dst[4],dst[5]) ); //    top-bottom
	var h3 = cross( cross(dst[0],dst[1] , dst[6],dst[7]) , cross(dst[2],dst[3] , dst[4],dst[5]) ); //   left-right

	//   | H1x H2x H3x |   | h1x h2x h3x |
	// M | H1y H2y H3y | = | h1y h2y h3y |
	//   | H1z H2z H3z |   | h1z h2z h3z |
	//                MH = h
	//                M  = hH^-1
	var H = [
		H1[0] , H2[0] , H3[0] ,
		H1[1] , H2[1] , H3[1] ,
		H1[2] , H2[2] , H3[2] ,
	];
	var h = [
		h1[0] , h2[0] , h3[0] ,
		h1[1] , h2[1] , h3[1] ,
		h1[2] , h2[2] , h3[2] ,
	];

	var Hinv = matrix_inv3(H);
	var M    = matrix_multi33(h, Hinv);
	if ( ! inv )
		return M;
	else
		return matrix_inv3(M);
}
//////////////////////////////
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/index.php
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/bilinearwarp
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/BilinearImageWarping2.pdf
// http://www.fmwconcepts.com/imagemagick/bilinearwarp/FourCornerImageWarp2.pdf
function bilinearwarp( d, nx, ny ){
	// x  = a0 + a1*u + a2*v + a3*u*v
	// y  = b0 + b1*u + b2*v + b3*u*v

	// u,v = 0,0 => x0,y0
	// x  = a0 + a1*0 + a2*0 + a3*0*0
	// x  = a0
	var a0 = d[0];
	var b0 = d[1];

	// u,v = 1,0 => x1,y1
	// x  = a0 + a1*1 + a2*0 + a3*1*0
	// x  = a0 + a1*1
	// a1 = x - a0
	var a1 = (d[2] - a0) / nx;
	var b1 = (d[3] - b0) / nx;

	// u,v = 0,1 => x3,y3
	// x  = a0 + a1*0 + a2*1 + a3*0*1
	// x  = a0 + a2*1
	// a2 = x - a0
	var a2 = (d[6] - a0) / ny;
	var b2 = (d[7] - b0) / ny;

	// u,v = 1,1 => x2,y2
	// x  = a0 + a1*1 + a2*1 + a3*1*1
	// a3 = x - (a0 + a1 + a2)
	var a3 = (d[4] - (a0 + a1*nx + a2*ny)) / (nx*ny);
	var b3 = (d[5] - (b0 + b1*nx + b2*ny)) / (nx*ny);

	var A  = b2*a3 - b3*a2;
	var C1 = b0*a1 - b1*a0;
	var B1 = b0*a3 - b3*a0 + b2*a1 - b1*a2;
	return [
		a0,a1,a2,a3 ,
		b0,b1,b2,b3 ,
		A ,B1,C1,0  ,
		0 ,0 ,0 ,0  ,
	];
}
//////////////////////////////
function get_intersect_point(){
	function isPointInLine( pt, v1, v2 ){
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

	if ( arguments.length === 8 ){ // x1,y1...
		var p1a = [arguments[0], arguments[1], 1];
		var p1b = [arguments[2], arguments[3], 1];
		var p2a = [arguments[4], arguments[5], 1];
		var p2b = [arguments[6], arguments[7], 1];
	}
	if ( arguments.length === 4 ){ // [x1,y1,1]...
		var p1a = arguments[0];
		var p1b = arguments[1];
		var p2a = arguments[2];
		var p2b = arguments[3];
	}

	var intr = cross( cross(p1a,p1b) , cross(p2a,p2b) );
	if ( intr[2] === 0 )
		return -1;

	intr[0] /= intr[2];
	intr[1] /= intr[2];
	intr[2] /= intr[2];
	if ( ! isPointInLine(intr,p1a,p1b) || ! isPointInLine(intr,p2a,p2b) )
		return -1;
	return intr;
}

function find_intersect_point( quad ){
	// 0,1  2,3
	// 6,7  4,5
	var intr = get_intersect_point(quad[0],quad[1] , quad[4],quad[5] , quad[2],quad[3] , quad[6],quad[7]);
	if ( intr !== -1 ) // is simple
		return intr;
	var intr = get_intersect_point(quad[0],quad[1] , quad[2],quad[3] , quad[6],quad[7] , quad[4],quad[5]);
	if ( intr !== -1 ) // is twisted top-bottom
		return intr;
	var intr = get_intersect_point(quad[0],quad[1] , quad[6],quad[7] , quad[2],quad[3] , quad[4],quad[5]);
	if ( intr !== -1 ) // is twisted left-right
		return intr;
	return -1;
}

function quad_area( x1,y1 , x2,y2 , x3,y3 , x4,y4 ){
	function triArea( ax,ay , bx,by , cx,cy )
	{
		var r1 = ax * (by - cy);
		var r2 = bx * (cy - ay);
		var r3 = cx * (ay - by);
		var area = 0.5 * (r1 + r2 + r3);
		return Math.abs(area);
	}

	// 0,1,2  0,2,3
	var t1 = triArea(x1,y1 , x2,y2 , x3,y3);
	var t2 = triArea(x1,y1 , x3,y3 , x4,y4);
	return (t1 + t2);
}

function quad_type( quad ){
	var midx = (quad[0] + quad[2] + quad[4] + quad[6]) * 0.25;
	var midy = (quad[1] + quad[3] + quad[5] + quad[7]) * 0.25;

	// 1 2
	// 4 3
	var ty = 'q';
	for ( var i=0; i < 8; i += 2 ){
		if ( quad[i+0] < midx && quad[i+1] < midy )  ty += '1';
		else
		if ( quad[i+0] < midx && quad[i+1] > midy )  ty += '4';
		else
		if ( quad[i+0] > midx && quad[i+1] < midy )  ty += '2';
		else
		if ( quad[i+0] > midx && quad[i+1] > midy )  ty += '3';
		else
			ty += '-';
	}
	return ty;
}

function quad_normal( type ){
	switch ( type ){
		case 'q1234':  case 'q2341':  case 'q3412':  case 'q4123':
			return 'normal';
		case 'q2143':  case 'q3214':  case 'q4321':  case 'q1432':
			return 'normal';
		case 'q1243':  case 'q2134':  case 'q1324':  case 'q4231':
		case 'q2314':  case 'q3241':  case 'q1342':  case 'q2431':
		case 'q3421':  case 'q4312':  case 'q2413':  case 'q3142':
		case 'q4132':  case 'q1423':
			return 'twist';
	} // switch ( type )
	return 'bend';
}
//////////////////////////////
