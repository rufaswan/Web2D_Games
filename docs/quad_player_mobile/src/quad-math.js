function QuadMath(Q){
	var $ = this;
	//var m = {}; // all public

	//////////////////////////////

	$.clamp = function( n, min, max ){
		if ( n < min )  return min;
		if ( n > max )  return max;
		return n;
	}

	$.pow2_ceil = function( n ){
		var sign = false;
		if ( n < 0 ){
			sign = true;
			n = -n;
		}

		var i = 1;
		while ( n > i )
			i <<= 1;
		return ( sign ) ? -i : i;
	}

	//////////////////////////////

	$.vec_resize = function( len, vec ){
		if ( ! Array.isArray(vec) )
			return;
		while ( vec.length < len )
			vec.push(1);
		while ( vec.length > len ){
			var last = vec.pop() || 0;
			var z = ( last === 0 ) ? 0 : 1.0 / last;
			for ( var i=0; i < vec.length; i++ )
				vec[i] *= z;
		}
	}

	$.vec_multi = function( v, f ){
		if ( ! Array.isArray(v) )
			return;
		for ( var i=0; i < v.length; i++ )
			v[i] *= f;
	}

	$.vec4_multi = function( v1, v2 ){
		var v4 = [
			v1[0] * v2[0],
			v1[1] * v2[1],
			v1[2] * v2[2],
			v1[3] * v2[3],
		];
		return v4;
	}

	//////////////////////////////

	$.matrix4 = function(){
		var m4 = [
			1,0,0,0,
			0,1,0,0,
			0,0,1,0,
			0,0,0,1,
		];
		return m4;
	}

	$.matrix_multi13 = function( v, m ){
		$.vec_resize(3,v);
		var vm = [
			v[0]*m[0] + v[1]*m[3] + v[2]*m[6] ,
			v[0]*m[1] + v[1]*m[4] + v[2]*m[7] ,
			v[0]*m[2] + v[1]*m[5] + v[2]*m[8] ,
		];
		return vm;
	}

	$.matrix_multi14 = function( v, m ){
		$.vec_resize(4,v);
		var vm = [
			v[0]*m[0] + v[1]*m[4] + v[2]*m[ 8] + v[3]*m[12] ,
			v[0]*m[1] + v[1]*m[5] + v[2]*m[ 9] + v[3]*m[13] ,
			v[0]*m[2] + v[1]*m[6] + v[2]*m[10] + v[3]*m[14] ,
			v[0]*m[3] + v[1]*m[7] + v[2]*m[11] + v[3]*m[15] ,
		];
		return vm;
	}

	$.matrix_multi31 = function( m, v ){
		$.vec_resize(3,v);
		var mv = [
			m[0]*v[0] + m[1]*v[1] + m[2]*v[2] ,
			m[3]*v[0] + m[4]*v[1] + m[5]*v[2] ,
			m[6]*v[0] + m[7]*v[1] + m[8]*v[2] ,
		];
		return mv;
	}

	$.matrix_multi41 = function( m, v ){
		$.vec_resize(4,v);
		var mv = [
			m[ 0]*v[0] + m[ 1]*v[1] + m[ 2]*v[2] + m[ 3]*v[3] ,
			m[ 4]*v[0] + m[ 5]*v[1] + m[ 6]*v[2] + m[ 7]*v[3] ,
			m[ 8]*v[0] + m[ 9]*v[1] + m[10]*v[2] + m[11]*v[3] ,
			m[12]*v[0] + m[13]*v[1] + m[14]*v[2] + m[15]*v[3] ,
		];
		return mv;
	}

	$.matrix_multi33 = function( m1, m2 ){
		var m3 = [
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
		return m3;
	}

	$.matrix_multi44 = function( m1, m2 ){
		var m4 = [
			m1[ 0]*m2[0] + m1[ 1]*m2[4] + m1[ 2]*m2[ 8] + m1[ 3]*m2[12],
			m1[ 0]*m2[1] + m1[ 1]*m2[5] + m1[ 2]*m2[ 9] + m1[ 3]*m2[13],
			m1[ 0]*m2[2] + m1[ 1]*m2[6] + m1[ 2]*m2[10] + m1[ 3]*m2[14],
			m1[ 0]*m2[3] + m1[ 1]*m2[7] + m1[ 2]*m2[11] + m1[ 3]*m2[15],

			m1[ 4]*m2[0] + m1[ 5]*m2[4] + m1[ 6]*m2[ 8] + m1[ 7]*m2[12],
			m1[ 4]*m2[1] + m1[ 5]*m2[5] + m1[ 6]*m2[ 9] + m1[ 7]*m2[13],
			m1[ 4]*m2[2] + m1[ 5]*m2[6] + m1[ 6]*m2[10] + m1[ 7]*m2[14],
			m1[ 4]*m2[3] + m1[ 5]*m2[7] + m1[ 6]*m2[11] + m1[ 7]*m2[15],

			m1[ 8]*m2[0] + m1[ 9]*m2[4] + m1[10]*m2[ 8] + m1[11]*m2[12],
			m1[ 8]*m2[1] + m1[ 9]*m2[5] + m1[10]*m2[ 9] + m1[11]*m2[13],
			m1[ 8]*m2[2] + m1[ 9]*m2[6] + m1[10]*m2[10] + m1[11]*m2[14],
			m1[ 8]*m2[3] + m1[ 9]*m2[7] + m1[10]*m2[11] + m1[11]*m2[15],

			m1[12]*m2[0] + m1[13]*m2[4] + m1[14]*m2[ 8] + m1[15]*m2[12],
			m1[12]*m2[1] + m1[13]*m2[5] + m1[14]*m2[ 9] + m1[15]*m2[13],
			m1[12]*m2[2] + m1[13]*m2[6] + m1[14]*m2[10] + m1[15]*m2[14],
			m1[12]*m2[3] + m1[13]*m2[7] + m1[14]*m2[11] + m1[15]*m2[15],
		];
		return m4;
	}

	//////////////////////////////

	$.cross = function( a, b ){
		$.vec_resize(3,a);
		$.vec_resize(3,b);
		// x = y1*z2 - y2*z1
		// y = z1*x2 - z2*x1
		// z = x1*y2 - x2*y1
		var x = a[1]*b[2] - b[1]*a[2];
		var y = a[2]*b[0] - b[2]*a[0];
		var z = a[0]*b[1] - b[0]*a[1];
		return [x,y,z];
	}

	$.perspective_quad = function( dst ){
		var q = [
			dst.slice(0,2),
			dst.slice(2,4),
			dst.slice(4,6),
			dst.slice(6,8),
		];

		// 0 1
		// 3 2
		var t = [
			$.cross( $.cross(q[0],q[2]) , $.cross(q[1],q[3]) ), // corner-corner
			$.cross( $.cross(q[0],q[1]) , $.cross(q[3],q[2]) ), //    top-bottom
			$.cross( $.cross(q[0],q[3]) , $.cross(q[1],q[2]) ), //   left-right
		];

		//   | H1x H2x H3x |   | h1x h2x h3x |
		// M | H1y H2y H3y | = | h1y h2y h3y |
		//   | H1z H2z H3z |   | h1z h2z h3z |
		//                MH = h
		//                M  = hH^-1
		var h = [
			t[0][0] , t[1][0] , t[2][0] ,
			t[0][1] , t[1][1] , t[2][1] ,
			t[0][2] , t[1][2] , t[2][2] ,
		];

		// var H    = pre-computed
		// var Hinv = pre-computed
		var Hinv = [
			 0     , 0     ,  0.005 ,
			-0.001 , 0     ,  0.015 ,
			 0     , 0.001 , -0.015 ,
		];
		var M3 = $.matrix_multi33(h, Hinv);

		var t = [
			$.matrix_multi31( M3, [10,10] ),
			$.matrix_multi31( M3, [20,10] ),
			$.matrix_multi31( M3, [20,20] ),
			$.matrix_multi31( M3, [10,20] ),
		];
		return [].concat(t[0],t[1],t[2],t[3]);
	}
	/*
	dummy src = [10,10 , 20,10 , 20,20 , 10,20]

		var a = cross( cross([10,10,1] , [20,20,1]) , cross([20,10,1] , [10,20,1]) );
			= cross([-10,10,0] , [-10,10,300])
			= [3000,3000,200]
		var b = cross( cross([10,10,1] , [20,10,1]) , cross([10,20,1] , [20,20,1]) );
			= cross([0,10,-100] , [0,10,-200])
			= [-1000,0,0]
		var c = cross( cross([10,10,1] , [10,20,1]) , cross([20,10,1] , [20,20,1]) );
			= cross([-10,0,100] , [-10,0,200])
			= [0,1000,0]

		var H = [
			3000 , -1000 ,    0 ,
			3000 ,     0 , 1000 ,
			 200 ,     0 ,    0 ,
		]
		var Hinv = [
			 0     , 0     ,  0.005 ,
			-0.001 , 0     ,  0.015 ,
			 0     , 0.001 , -0.015 ,
		]
	 */

	//////////////////////////////

	$.matrix_mix = function( rate, cur, next ){
		// 4/4 = rate 1    = cur * 1    + next * 0
		// 3/4 = rate 0.75 = cur * 0.75 + next * 0.25
		// 2/4 = rate 0.5  = cur * 0.5  + next * 0.5
		// 1/4 = rate 0.25 = cur * 0.25 + next * 0.75
		// 0/4 = rate 0    = cur * 0    + next * 1
		var rev = 1.0 - rate;
		var m4 = [0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0];
		for ( var i=0; i < 16; i++ )
			m4[i] = (cur[i] * rate) + (next[i] * rev);
		return m4;
	}

	$.color_mix = function( rate, cur, next ){
		var rev = 1.0 - rate;
		var c = [0,0,0,0];
		for ( var i=0; i < 4; i++ )
			c[i] = (cur[i] * rate) + (next[i] * rev);
		return c;
	}

	$.css_color = function( css ){
		// css = '#rrggbbaa'
		if ( typeof css !== 'string' || ! /^#[0-9a-fA-F]{8}$/.test(css) )
			return [1,1,1,1];

		var div = 1.0 / 255;
		var rgba = [
			parseInt( css.substring(1,3) , 16 ) * div ,
			parseInt( css.substring(3,5) , 16 ) * div ,
			parseInt( css.substring(5,7) , 16 ) * div ,
			parseInt( css.substring(7,9) , 16 ) * div ,
		];
		return rgba;
	}

	//////////////////////////////

	// order matters
	//   (A*B)*C === A*(B*C)
	//    A*B    !== B*A
	$.image_multi4 = function( mat4, image ){
		var hw = image.w * 0.5;
		var hh = image.h * 0.5;
		var quad = [-hw,hh , hw,hh , hw,-hh , -hw,-hh];
		return $.quad_multi4(mat4, quad);
	}

	$.quad_multi4 = function( mat4, quad ){
		var c0 = $.matrix_multi41(mat4, quad.slice(0,2));
		var c1 = $.matrix_multi41(mat4, quad.slice(2,4));
		var c2 = $.matrix_multi41(mat4, quad.slice(4,6));
		var c3 = $.matrix_multi41(mat4, quad.slice(6,8));
		return [].concat( c0.slice(0,2) , c1.slice(0,2) , c2.slice(0,2) , c3.slice(0,2) );
	}

	$.fog_multi4 = function( color, quad ){
		var c16 = [
			quad[ 0]*color[0] , quad[ 1]*color[1] , quad[ 2]*color[2] , quad[ 3]*color[3] ,
			quad[ 4]*color[0] , quad[ 5]*color[1] , quad[ 6]*color[2] , quad[ 7]*color[3] ,
			quad[ 8]*color[0] , quad[ 9]*color[1] , quad[10]*color[2] , quad[10]*color[3] ,
			quad[12]*color[0] , quad[13]*color[1] , quad[14]*color[2] , quad[15]*color[3] ,
		];
		return c16;
	}

	//////////////////////////////


	//////////////////////////////

} // function QuadMath
