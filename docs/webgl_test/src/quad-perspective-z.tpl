<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml'><head>

<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1' />
<title>Quad Perspective Test (no matrix)</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.0.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	attribute  highp  vec3   a_xyz;
	attribute  highp  vec2   a_uv;
	uniform    highp  vec4   u_pxsize;
	varying    highp  vec2   v_uv;
	varying    highp  float  v_z;

	highp  vec3   xyz;
	highp  vec2   uv;
	highp  float  z;
	void main(void){
		z = 1.0 / a_xyz.z;
		xyz = a_xyz * z;
		uv  = a_uv  * z;

		xyz.xy *= u_pxsize.xy;
		 uv.xy *= u_pxsize.zw;

		v_z  = z;
		v_uv = uv;
		gl_Position = vec4(xyz.x, xyz.y, 1.0, 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	varying  highp  vec2   v_uv;
	varying  highp  float  v_z;

	highp  vec2   uv;
	highp  float  z;
	void main(void){
		z  = 1.0 / v_z;
		uv = v_uv * z;
		gl_FragColor = texture2D(u_tex, uv);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xyz', 'a_uv', 'u_pxsize', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
APP.texsize = 0;

function dst_perp( dst, src='' ){
	if ( src === '' )
		src = [10,10 , 20,10 , 20,20 , 10,20];
	var mat3 = get_perspective_mat3(src, dst, false);
	var v0 = matrix_multi31(mat3, [ src[0],src[1],1 ]);
	var v1 = matrix_multi31(mat3, [ src[2],src[3],1 ]);
	var v2 = matrix_multi31(mat3, [ src[4],src[5],1 ]);
	var v3 = matrix_multi31(mat3, [ src[6],src[7],1 ]);
	//console.log('dst',dst, 'src',src, 'mat3',mat3, 'res',[v0,v1,v2,v3]);
	return [v0,v1,v2,v3];
}

function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', APP.texsize[0], APP.texsize[1]);
	var dst = dst_perp(APP.dst);

	var xyz = [].concat(
		dst[0] , dst[1] , dst[2] ,
		dst[0] , dst[2] , dst[3] ,
	);
	var uv = QDFN.quad_getxy(APP.src , 0,1,2 , 0,2,3);

	QDFN.v3_attrib('a_xyz', xyz);
	QDFN.v2_attrib('a_uv' , uv);
	//console.log('xyz',xyz,'uv',uv,'texsize',APP.texsize);
	return QDFN.draw(6);
}

function render(){
	if ( APP.is_click ){
		get_dst_corner();
		quad_draw();
		APP.is_click = false;
	}
	requestAnimationFrame(render);
}

QDFN.bind_tex2D_id(0, 'mona_lisa_0_png').then(function(res){
	APP.is_click = true;
	APP.texsize  = res;
	APP.src = QDFN.xywh2quad(res[0],res[1]);
	requestAnimationFrame(render);
});
</script>

</body></html>
