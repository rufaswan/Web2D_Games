function QuadMath(Q){
	var $ = this; // public
	//var __ = {};  // private

	//////////////////////////////

	$.clamp = function( n, min, max ){
		if ( n < min )  return min;
		if ( n > max )  return max;
		return n;
	}

	$.rect_symmetry = function( rect ){
		var abs = [
			Math.abs(rect[0]) , Math.abs(rect[1]) ,
			Math.abs(rect[2]) , Math.abs(rect[3]) ,
		];
		var maxx = ( abs[0] > abs[2] ) ? abs[0] : abs[2];
		var maxy = ( abs[1] > abs[3] ) ? abs[1] : abs[3];
		return [ maxx , maxy ];
	}

	$.vram_srcquad = function( quad, pos ){
		if ( ! quad )  return 0; // fog color only
		var xy4 = [0,0 , 0,0 , 0,0 , 0,0];
		for ( var i=0; i < 8; i += 2 ){
			xy4[i+0] = quad[i+0] + pos[0];
			xy4[i+1] = quad[i+1] + pos[1];
		}
		return xy4;
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

	$.matrix_multi12 = function( v, m ){
		$.vec_resize(2,v);
		var vm = [
			v[0]*m[0] + v[1]*m[2] ,
			v[0]*m[1] + v[1]*m[3] ,
		];
		return vm;
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

	$.matrix_multi21 = function( m, v ){
		$.vec_resize(2,v);
		var mv = [
			m[0]*v[0] + m[1]*v[1] ,
			m[2]*v[0] + m[3]*v[1] ,
		];
		return mv;
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

	$.matrix_multi22 = function( m2a, m2b ){
		if ( ! m2a && ! m2b )  return 0;
		if ( ! m2a )  return m2b;
		if ( ! m2b )  return m2a;
		var m2 = [
			m2a[0]*m2b[0] + m2a[1]*m2b[2],
			m2a[0]*m2b[1] + m2a[1]*m2b[3],

			m2a[2]*m2b[0] + m2a[3]*m2b[2],
			m2a[2]*m2b[1] + m2a[3]*m2b[3],
		];
		return m2;
	}

	$.matrix_multi33 = function( m3a, m3b ){
		if ( ! m3a && ! m3b )  return 0;
		if ( ! m3a )  return m3b;
		if ( ! m3b )  return m3a;
		var m3 = [
			m3a[0]*m3b[0] + m3a[1]*m3b[3] + m3a[2]*m3b[6],
			m3a[0]*m3b[1] + m3a[1]*m3b[4] + m3a[2]*m3b[7],
			m3a[0]*m3b[2] + m3a[1]*m3b[5] + m3a[2]*m3b[8],

			m3a[3]*m3b[0] + m3a[4]*m3b[3] + m3a[5]*m3b[6],
			m3a[3]*m3b[1] + m3a[4]*m3b[4] + m3a[5]*m3b[7],
			m3a[3]*m3b[2] + m3a[4]*m3b[5] + m3a[5]*m3b[8],

			m3a[6]*m3b[0] + m3a[7]*m3b[3] + m3a[8]*m3b[6],
			m3a[6]*m3b[1] + m3a[7]*m3b[4] + m3a[8]*m3b[7],
			m3a[6]*m3b[2] + m3a[7]*m3b[5] + m3a[8]*m3b[8],
		];
		return m3;
	}

	$.matrix_multi44 = function( m4a, m4b ){
		if ( ! m4a && ! m4b )  return 0;
		if ( ! m4a )  return m4b;
		if ( ! m4b )  return m4a;
		var m4 = [
			m4a[ 0]*m4b[0] + m4a[ 1]*m4b[4] + m4a[ 2]*m4b[ 8] + m4a[ 3]*m4b[12],
			m4a[ 0]*m4b[1] + m4a[ 1]*m4b[5] + m4a[ 2]*m4b[ 9] + m4a[ 3]*m4b[13],
			m4a[ 0]*m4b[2] + m4a[ 1]*m4b[6] + m4a[ 2]*m4b[10] + m4a[ 3]*m4b[14],
			m4a[ 0]*m4b[3] + m4a[ 1]*m4b[7] + m4a[ 2]*m4b[11] + m4a[ 3]*m4b[15],

			m4a[ 4]*m4b[0] + m4a[ 5]*m4b[4] + m4a[ 6]*m4b[ 8] + m4a[ 7]*m4b[12],
			m4a[ 4]*m4b[1] + m4a[ 5]*m4b[5] + m4a[ 6]*m4b[ 9] + m4a[ 7]*m4b[13],
			m4a[ 4]*m4b[2] + m4a[ 5]*m4b[6] + m4a[ 6]*m4b[10] + m4a[ 7]*m4b[14],
			m4a[ 4]*m4b[3] + m4a[ 5]*m4b[7] + m4a[ 6]*m4b[11] + m4a[ 7]*m4b[15],

			m4a[ 8]*m4b[0] + m4a[ 9]*m4b[4] + m4a[10]*m4b[ 8] + m4a[11]*m4b[12],
			m4a[ 8]*m4b[1] + m4a[ 9]*m4b[5] + m4a[10]*m4b[ 9] + m4a[11]*m4b[13],
			m4a[ 8]*m4b[2] + m4a[ 9]*m4b[6] + m4a[10]*m4b[10] + m4a[11]*m4b[14],
			m4a[ 8]*m4b[3] + m4a[ 9]*m4b[7] + m4a[10]*m4b[11] + m4a[11]*m4b[15],

			m4a[12]*m4b[0] + m4a[13]*m4b[4] + m4a[14]*m4b[ 8] + m4a[15]*m4b[12],
			m4a[12]*m4b[1] + m4a[13]*m4b[5] + m4a[14]*m4b[ 9] + m4a[15]*m4b[13],
			m4a[12]*m4b[2] + m4a[13]*m4b[6] + m4a[14]*m4b[10] + m4a[15]*m4b[14],
			m4a[12]*m4b[3] + m4a[13]*m4b[7] + m4a[14]*m4b[11] + m4a[15]*m4b[15],
		];
		return m4;
	}

	$.matrix_det2 = function( m2 ){
		return (m2[0]*m2[3] - m2[1]*m2[2]);
	}

	$.matrix_inv2 = function( m2 ){
		var det = $.matrix_det2(m2);
		if ( det === 0 )
			return 0;
		var det_inv = 1.0 / det;
		var mco = [
			 m2[3] * det_inv , -m2[1] * det_inv ,
			-m2[2] * det_inv ,  m2[0] * det_inv ,
		];
		return mco;
	}

	$.matrix_inv3 = function( m3 ){
		// | 0 1 2 |
		// | 3 4 5 |
		// | 6 7 8 |
		var mdet = [
			$.matrix_det2([ m3[4],m3[5],m3[7],m3[8] ]),
			$.matrix_det2([ m3[3],m3[5],m3[6],m3[8] ]),
			$.matrix_det2([ m3[3],m3[4],m3[6],m3[7] ]),

			$.matrix_det2([ m3[1],m3[2],m3[7],m3[8] ]),
			$.matrix_det2([ m3[0],m3[2],m3[6],m3[8] ]),
			$.matrix_det2([ m3[0],m3[1],m3[6],m3[7] ]),

			$.matrix_det2([ m3[1],m3[2],m3[4],m3[5] ]),
			$.matrix_det2([ m3[0],m3[2],m3[3],m3[5] ]),
			$.matrix_det2([ m3[0],m3[1],m3[3],m3[4] ]),
		];

		var mco = [
			 mdet[0] , -mdet[3] ,  mdet[6] ,
			-mdet[1] ,  mdet[4] , -mdet[7] ,
			 mdet[2] , -mdet[5] ,  mdet[8] ,
		];

		var det = m3[0]*mco[0] + m3[1]*mco[3] + m3[2]*mco[6];
		if ( det === 0 )
			return 0;
		var det_inv = 1.0 / det;
		var i = 9;
		while ( i > 0 ){
			i--;
			mco[i] *= det_inv;
		}
		return mco;
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

	$.perspective_mat3 = function( quad ){
		var v = [
			[ quad[0],quad[1],1 ],
			[ quad[2],quad[3],1 ],
			[ quad[4],quad[5],1 ],
			[ quad[6],quad[7],1 ],
		];

		// 0 1
		// 3 2
		var c = [
			$.cross( $.cross(v[0],v[2]) , $.cross(v[1],v[3]) ), // corner-corner
			$.cross( $.cross(v[0],v[1]) , $.cross(v[3],v[2]) ), //    top-bottom
			$.cross( $.cross(v[0],v[3]) , $.cross(v[1],v[2]) ), //   left-right
		];
		var m3 = [
			c[0][0] , c[1][0] , c[2][0] ,
			c[0][1] , c[1][1] , c[2][1] ,
			c[0][2] , c[1][2] , c[2][2] ,
		];
		return m3;
	}

	$.perspective_quad = function( dst ){
		//   | H1x H2x H3x |   | h1x h2x h3x |
		// M | H1y H2y H3y | = | h1y h2y h3y |
		//   | H1z H2z H3z |   | h1z h2z h3z |
		//                MH = h
		//                M  = hH^-1
		var h = $.perspective_mat3(dst);

		// var H    = pre-computed
		// var Hinv = pre-computed
		var H_inv = [
			 0     , 0     ,  0.005 ,
			-0.001 , 0     ,  0.015 ,
			 0     , 0.001 , -0.015 ,
		];
		var M3 = $.matrix_multi33(h, H_inv);

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
		if ( ! cur && ! next )  return 0; // both identidy matrix
		if ( ! cur  )  cur  = $.matrix4();
		if ( ! next )  next = $.matrix4();
		// 4/4 = rate 1    = cur * 1    + next * 0
		// 3/4 = rate 0.75 = cur * 0.75 + next * 0.25
		// 2/4 = rate 0.5  = cur * 0.5  + next * 0.5
		// 1/4 = rate 0.25 = cur * 0.25 + next * 0.75
		// 0/4 = rate 0    = cur * 0    + next * 1
		var rev = 1.0 - rate;
		var m4  = [0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0];
		for ( var i=0; i < 16; i++ )
			m4[i] = (cur[i] * rate) + (next[i] * rev);
		return m4;
	}

	$.color_mix = function( rate, cur, next ){
		if ( ! cur || ! next )
			Q.func.error('math.color_mix',cur,next);
		var rev = 1.0 - rate;
		var c4  = [0,0,0,0];
		for ( var i=0; i < 4; i++ )
			c4[i] = (cur[i] * rate) + (next[i] * rev);
		return c4;
	}

	$.quad_mix = function( rate, cur, next ){
		if ( ! cur || ! next )
			Q.func.error('math.quad_mix',cur,next);
		var rev = 1.0 - rate;
		var xy4 = [0,0 , 0,0 , 0,0 , 0,0];
		for ( var i=0; i < 8; i++ )
			xy4[i] = (cur[i] * rate) + (next[i] * rev);
		return xy4;
	}

	$.fog_mix = function( rate, cur, next ){
		if ( ! cur || ! next )
			Q.func.error('math.fog_mix',cur,next);
		var rev = 1.0 - rate;
		var f4  = [0,0,0,0 , 0,0,0,0 , 0,0,0,0 , 0,0,0,0];
		for ( var i=0; i < 16; i++ )
			f4[i] = (cur[i] * rate) + (next[i] * rev);
		return f4;
	}

	// order matters
	//   (A*B)*C === A*(B*C)
	//    A*B    !== B*A
	$.rect_multi4 = function( mat4, rect ){
		if ( ! mat4 )  return rect; // mat4=0 is identidy matrix , rect=no change
		var xy2 = [0,0 , 0,0];
		for ( var i=0; i < 4; i += 2 ){
			var x = rect[i+0];
			var y = rect[i+1];
			xy2[i+0] = mat4[0]*x + mat4[1]*y + mat4[2] + mat4[3];
			xy2[i+1] = mat4[4]*x + mat4[5]*y + mat4[6] + mat4[7];
		}
		return xy2;
	}

	$.quad_multi4 = function( mat4, quad ){
		if ( ! mat4 )  return quad; // mat4=0 is identidy matrix , quad=no change
		var xy4 = [0,0 , 0,0 , 0,0 , 0,0];
		for ( var i=0; i < 8; i += 2 ){
			var x = quad[i+0];
			var y = quad[i+1];
			xy4[i+0] = mat4[0]*x + mat4[1]*y + mat4[2] + mat4[3];
			xy4[i+1] = mat4[4]*x + mat4[5]*y + mat4[6] + mat4[7];
		}
		return xy4;
	}

	// color * fogquad
	$.fog_multi4 = function( color, quad ){
		var c16 = [
			// r                g                   b                   a
			quad[ 0]*color[0] , quad[ 1]*color[1] , quad[ 2]*color[2] , quad[ 3]*color[3] , // c1
			quad[ 4]*color[0] , quad[ 5]*color[1] , quad[ 6]*color[2] , quad[ 7]*color[3] , // c2
			quad[ 8]*color[0] , quad[ 9]*color[1] , quad[10]*color[2] , quad[10]*color[3] , // c3
			quad[12]*color[0] , quad[13]*color[1] , quad[14]*color[2] , quad[15]*color[3] , // c4
		];
		return c16;
	}

	//////////////////////////////

} // function QuadMath
