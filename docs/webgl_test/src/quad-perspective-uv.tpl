<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Test (SRC * M)</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.0.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	attribute  highp  vec2  a_uv;
	uniform    highp  vec4  u_pxsize;
	uniform    highp  mat3  u_mat3;
	varying    highp  vec2  v_uv;

	highp  vec3   xyz;
	highp  vec2   uv;
	highp  float  z;
	void main(void){
		xyz = vec3(a_uv, 1.0) * u_mat3;
		z    = 1.0 / xyz.z;
		xyz *= z;

		xyz.xy *= u_pxsize.xy;
		uv      = a_uv.xy * u_pxsize.zw;

		v_uv = uv;
		gl_Position = vec4(xyz, 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	varying  highp  vec2  v_uv;

	void main(void){
		gl_FragColor = texture2D(u_tex, v_uv);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_uv', 'u_pxsize', 'u_mat3', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
APP.texsize = 0;

function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', APP.texsize[0], APP.texsize[1]);
	var mat3 = get_perspective_mat3(APP.src, APP.dst, false);
	QDFN.set_mat3fv('u_mat3', mat3);

	var scx = find_intersect_point(APP.src);
	var dcx = find_intersect_point(APP.dst);

	// for simple and twisted
	if ( dcx !== -1 ){
		var uv = QDFN.quad_tri4(scx, APP.src);
		QDFN.v2_attrib('a_uv', uv);

		console.log('simple and twisted', dcx);
		return QDFN.draw(12);
	}

	// bended
	var area1 = quad_area(APP.dst , 0,1,2 , 0,2,3);
	var area2 = quad_area(APP.dst , 1,2,3 , 1,3,0);

	if ( area1 < area2 )
		var uv = QDFN.quad_getxy(APP.src , 0,1,2 , 0,2,3);
	else
		var uv = QDFN.quad_getxy(APP.src , 1,2,3 , 1,3,0);

	QDFN.v2_attrib('a_uv', uv);
	console.log('bended', dcx);
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
