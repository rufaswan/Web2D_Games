
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
function matrix_inv3( m ){
	function matrix_det2( m ){
		return (m[0]*m[3] - m[1]*m[2]);
	}

	var mdet = [
		matrix_det2( [ m[4],m[5] , m[7],m[8] ] ) ,
		matrix_det2( [ m[3],m[5] , m[6],m[8] ] ) ,
		matrix_det2( [ m[3],m[4] , m[6],m[7] ] ) ,

		matrix_det2( [ m[1],m[2] , m[7],m[8] ] ) ,
		matrix_det2( [ m[0],m[2] , m[6],m[8] ] ) ,
		matrix_det2( [ m[0],m[1] , m[6],m[7] ] ) ,

		matrix_det2( [ m[1],m[2] , m[4],m[5] ] ) ,
		matrix_det2( [ m[0],m[2] , m[3],m[5] ] ) ,
		matrix_det2( [ m[0],m[1] , m[3],m[4] ] ) ,
	];

	var mco = [
		 mdet[0] , -mdet[3] ,  mdet[6] ,
		-mdet[1] ,  mdet[4] , -mdet[7] ,
		 mdet[2] , -mdet[5] ,  mdet[8] ,
	];

	var d = m[0]*mco[0] + m[1]*mco[3] + m[2]*mco[6];
	var dinv = 1.0 / d;

	for ( var i=0; i < 9; i++ )
		mco[i] *= dinv;
	return mco;
}

function matrix_multi13( v, m ){
	//            | a b c |
	// | 0 1 2 |  | d e f | = | 0a+1d+2g  0b+1e+2h  0c+1f+2i |
	//            | g h i |
	var vm = [
		v[0]*m[0] + v[1]*m[3] + v[2]*m[6] ,
		v[0]*m[1] + v[1]*m[4] + v[2]*m[7] ,
		v[0]*m[2] + v[1]*m[5] + v[2]*m[8] ,
	];
	return vm;
}

function matrix_multi31( m, v ){
	// | 0 1 2 |  | a |   | 0a+1b+2c |
	// | 3 4 5 |  | b | = | 3a+4b+5c |
	// | 6 7 8 |  | c |   | 6a+7b+8c |
	var mv = [
		m[0]*v[0] + m[1]*v[1] + m[2]*v[2] ,
		m[3]*v[0] + m[4]*v[1] + m[5]*v[2] ,
		m[6]*v[0] + m[7]*v[1] + m[8]*v[2] ,
	];
	return mv;
}

function matrix_multi33( m1, m2 ){
	//            | 1 0 0 |
	// M * Minv = | 0 1 0 | or identify matrix
	//            | 0 0 1 |
	var m = [
		m1[0]*m2[0] + m1[1]*m2[3] + m1[2]*m2[6],
		m1[0]*m2[1] + m1[1]*m2[4] + m1[2]*m2[7],
		m1[0]*m2[2] + m1[1]*m2[5] + m1[2]*m2[8],

		m1[3]*m2[0] + m1[4]*m2[3] + m1[5]*m2[6],
		m1[3]*m2[1] + m1[4]*m2[4] + m1[5]*m2[7],
		m1[3]*m2[2] + m1[4]*m2[5] + m1[5]*m2[8],

		m1[6]*m2[0] + m1[7]*m2[3] + m1[8]*m2[6],
		m1[6]*m2[1] + m1[7]*m2[4] + m1[8]*m2[7],
		m1[6]*m2[2] + m1[7]*m2[5] + m1[8]*m2[8],
	];
	return m;
}

function get_perspective_mat3( src, dst, inv=true ){
	// 0,1  2,3
	// 6,7  4,5
	var cs1 = cross( cross(src[0],src[1] , src[4],src[5]) , cross(src[2],src[3] , src[6],src[7]) ); // corner-corner
	var cs2 = cross( cross(src[0],src[1] , src[2],src[3]) , cross(src[6],src[7] , src[4],src[5]) ); //    top-bottom
	var cs3 = cross( cross(src[0],src[1] , src[6],src[7]) , cross(src[2],src[3] , src[4],src[5]) ); //   left-right

	var cd1 = cross( cross(dst[0],dst[1] , dst[4],dst[5]) , cross(dst[2],dst[3] , dst[6],dst[7]) ); // corner-corner
	var cd2 = cross( cross(dst[0],dst[1] , dst[2],dst[3]) , cross(dst[6],dst[7] , dst[4],dst[5]) ); //    top-bottom
	var cd3 = cross( cross(dst[0],dst[1] , dst[6],dst[7]) , cross(dst[2],dst[3] , dst[4],dst[5]) ); //   left-right

	// https://mrl.nyu.edu/~dzorin/ug-graphics/lectures/lecture7/sld024.html
	// matrix * SRC = DST
	//       matrix = DST * SRC_inv
	//          SRC = matrix_inv * DST
	var src3 = [
		cs1[0] , cs2[0] , cs3[0] ,
		cs1[1] , cs2[1] , cs3[1] ,
		cs1[2] , cs2[2] , cs3[2] ,
	];
	var dst3 = [
		cd1[0] , cd2[0] , cd3[0] ,
		cd1[1] , cd2[1] , cd3[1] ,
		cd1[2] , cd2[2] , cd3[2] ,
	];

	var src_inv = matrix_inv3(src3);
	var mat3    = matrix_multi33(dst3, src_inv);
	if ( ! inv )
		return mat3;
	else
		return matrix_inv3(mat3);
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
	var z    = intr.pop();
	var zinv = ( z == 0 ) ? 0 : 1.0 / z;

	intr[0] *= zinv;
	intr[1] *= zinv;
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
