<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Test (DST * Minv)</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	attribute  highp  vec2  a_xy;
	uniform    highp  vec4  u_pxsize;
	uniform    highp  mat3  u_mat3;
	varying    highp  vec3  v_tuv;

	highp  vec3  v3;
	void main(void){
		v3 = vec3(a_xy, 1.0) * u_mat3;
			v3.x *= u_pxsize.z;
			v3.y *= u_pxsize.w;
		v_tuv = v3;

		gl_Position = vec4(
			a_xy.x * u_pxsize.x ,
			a_xy.y * u_pxsize.y ,
			1.0 , 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	varying  highp  vec3  v_tuv;

	highp  vec2  v2;
	void main(void){
		v2.x = v_tuv.x / v_tuv.z;
		v2.y = v_tuv.y / v_tuv.z;
		gl_FragColor = texture2D(u_tex, v2);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xy', 'u_pxsize', 'u_mat3', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
var TEX_SIZE = [360,640];

SRC = [0,0 , TEX_SIZE[0],0 , TEX_SIZE[0],TEX_SIZE[1] , 0,TEX_SIZE[1]];
function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', TEX_SIZE[0], TEX_SIZE[1]);
	var mat3 = get_perspective_mat3(SRC, DST, true);
	QDFN.set_mat3fv('u_mat3', mat3);

	var scx = find_intersect_point(SRC);
	var dcx = find_intersect_point(DST);

	// for simple and twisted
	if ( dcx !== -1 ){
		var xy = [
			dcx[0],dcx[1] , DST[0],DST[1] , DST[2],DST[3] ,
			dcx[0],dcx[1] , DST[2],DST[3] , DST[4],DST[5] ,
			dcx[0],dcx[1] , DST[4],DST[5] , DST[6],DST[7] ,
			dcx[0],dcx[1] , DST[6],DST[7] , DST[0],DST[1] ,
		];
		QDFN.v2_attrib('a_xy', xy);

		console.log('simple and twisted', dcx);
		return QDFN.draw(12);
	}

	// bended
	var area1 = quad_area(DST[0],DST[1] , DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7]);
	var area2 = quad_area(DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7] , DST[0],DST[1]);

	if ( area1 < area2 ){
		var xy = [
			DST[0],DST[1] , DST[2],DST[3] , DST[4],DST[5] ,
			DST[0],DST[1] , DST[4],DST[5] , DST[6],DST[7] ,
		];
	}
	else {
		var xy = [
			DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7] ,
			DST[2],DST[3] , DST[6],DST[7] , DST[0],DST[1] ,
		];
	}
	QDFN.v2_attrib('a_xy', xy);

	console.log('bended', dcx);
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
