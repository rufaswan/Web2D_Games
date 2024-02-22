<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Test (no matrix)</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	attribute  highp  vec3   a_xyz;
	attribute  highp  vec2   a_uv;
	uniform    highp  vec4   u_pxsize;
	varying    highp  vec2   v_uv;
	varying    highp  float  v_z;

	highp  vec3   v3;
	highp  vec2   v2;
	highp  float  z;
	void main(void){
		z  = 1.0 / a_xyz.z;
		v3 = a_xyz * z;
		v2 = a_uv  * z;

		v3.xy *= u_pxsize.xy;
		v2.xy *= u_pxsize.zw;

		v_z  = z;
		v_uv = v2;
		gl_Position = vec4(v3, 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	varying  highp  vec2   v_uv;
	varying  highp  float  v_z;

	highp  float  z;
	void main(void){
		if ( v_z == 0.0 )
			discard;
		z = 1.0 / v_z;
		gl_FragColor = texture2D(u_tex, v_uv * z);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xyz', 'a_uv', 'u_pxsize', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
var TEX_SIZE = [360,640];

SRC = [0,0 , TEX_SIZE[0],0 , TEX_SIZE[0],TEX_SIZE[1] , 0,TEX_SIZE[1]];

function dst_perp( dst, src='' )
{
	if ( src === '' )
		src = [10,10 , 20,10 , 20,20 , 10,20];
	var mat3 = get_perspective_mat3(src, dst, false);
	var v0 = matrix_multi31(mat3, [ src[0],src[1],1 ]);
	var v1 = matrix_multi31(mat3, [ src[2],src[3],1 ]);
	var v2 = matrix_multi31(mat3, [ src[4],src[5],1 ]);
	var v3 = matrix_multi31(mat3, [ src[6],src[7],1 ]);
	console.log(dst, [v0,v1,v2,v3]);
	return [v0,v1,v2,v3];
}

function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', TEX_SIZE[0], TEX_SIZE[1]);
	var dst = dst_perp(DST);

	var xyz = [
		dst[0][0],dst[0][1],dst[0][2] , dst[1][0],dst[1][1],dst[1][2] , dst[2][0],dst[2][1],dst[2][2] ,
		dst[0][0],dst[0][1],dst[0][2] , dst[2][0],dst[2][1],dst[2][2] , dst[3][0],dst[3][1],dst[3][2] ,
	];
	var uv = [
		SRC[0],SRC[1] , SRC[2],SRC[3] , SRC[4],SRC[5] ,
		SRC[0],SRC[1] , SRC[4],SRC[5] , SRC[6],SRC[7] ,
	];

	QDFN.v3_attrib('a_xyz', xyz);
	QDFN.v2_attrib('a_uv' , uv);
	return QDFN.draw(6);
}

function render(){
	if ( IS_CLICK ){
		get_dst_corner();
		quad_draw();
		IS_CLICK = false;
	}
	requestAnimationFrame(render);
}

QDFN.bind_tex2D_id(0, 'mona_lisa_png').then(function(){
	IS_CLICK = true;
	requestAnimationFrame(render);
});
</script>

</body></html>
