<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Test (DST * Minv)</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.0.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	attribute  highp  vec2  a_xy;
	uniform    highp  vec4  u_pxsize;
	uniform    highp  mat3  u_mat3;
	varying    highp  vec3  v_uvw;

	highp  vec2   xy;
	highp  vec3   uvw;
	highp  float  zinv;
	void main(void){
		uvw = vec3(a_xy, 1.0) * u_mat3;

		xy      = a_xy.xy * u_pxsize.xy;
		uvw.xy *= u_pxsize.zw;

		v_uvw = uvw;
		gl_Position = vec4(xy.x , xy.y , 1.0 , 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	varying  highp  vec3  v_uvw;

	highp  vec2   uv;
	highp  float  z;
	void main(void){
		z  = 1.0 / v_uvw.z;
		uv = v_uvw.xy * z;
		gl_FragColor = texture2D(u_tex, uv);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xy', 'u_pxsize', 'u_mat3', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
APP.texsize = 0;

function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', APP.texsize[0], APP.texsize[1]);
	var mat3 = get_perspective_mat3(APP.src, APP.dst, true);
	QDFN.set_mat3fv('u_mat3', mat3);

	var scx = find_intersect_point(APP.src);
	var dcx = find_intersect_point(APP.dst);

	// for simple and twisted
	if ( dcx !== -1 ){
		var xy = QDFN.quad_tri4(dcx, APP.dst);
		QDFN.v2_attrib('a_xy', xy);

		console.log('simple and twisted', dcx);
		return QDFN.draw(12);
	}

	// bended
	var area1 = quad_area(APP.dst , 0,1,2 , 0,2,3);
	var area2 = quad_area(APP.dst , 1,2,3 , 1,3,0);

	if ( area1 < area2 )
		var xy = QDFN.quad_getxy(APP.dst , 0,1,2 , 0,2,3);
	else
		var xy = QDFN.quad_getxy(APP.dst , 1,2,3 , 1,3,0);

	QDFN.v2_attrib('a_xy', xy);
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
