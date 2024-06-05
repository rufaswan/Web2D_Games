<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Test (Fullscreen * Minv)</title>
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

	highp  vec3  uvw;
	highp  vec2  xy;
	void main(void){
		uvw = vec3(a_xy, 1.0) * u_mat3;

		xy      = a_xy.xy * u_pxsize.xy;
		uvw.xy *= u_pxsize.zw;

		v_uvw   = uvw;
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

		if ( uv.x < 0.0 || uv.x > 1.0 )  discard;
		if ( uv.y < 0.0 || uv.y > 1.0 )  discard;
		gl_FragColor = texture2D(u_tex, uv);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xy', 'u_pxsize', 'u_mat3', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
__.texsize = 0;

function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', __.texsize[0], __.texsize[1]);
	var mat3 = get_perspective_mat3(__.src, __.dst, true);
	QDFN.set_mat3fv('u_mat3', mat3);

	var half = QDFN.get_drawing_half();
	var hw = half[0];
	var hh = half[1];

	var xy = [
		-hw,-hh , hw,-hh ,  hw,hh ,
		-hw,-hh , hw,hh  , -hw,hh ,
	];
	QDFN.v2_attrib('a_xy', xy);
	return QDFN.draw(6);
}

function render(){
	if ( __.is_click ){
		get_dst_corner();
		quad_draw();
		__.is_click = false;
	}
	requestAnimationFrame(render);
}

QDFN.bind_tex2D_id(0, 'mona_lisa_0_png').then(function(res){
	__.is_click = true;
	__.texsize  = res;
	__.src = QDFN.xywh2quad(res[0],res[1]);
	requestAnimationFrame(render);
});
</script>

</body></html>
