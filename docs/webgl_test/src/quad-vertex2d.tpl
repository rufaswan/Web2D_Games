<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Affine Vertex2D Test</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	uniform    sampler2D    u_tex;
	attribute  highp  vec2  a_xy;
	attribute  highp  vec2  a_uv;
	uniform    highp  vec4  u_pxsize;
	varying    highp  vec4  v_color;

	highp vec2  UV;
	void main(void){
		UV = vec2(a_uv.x * u_pxsize.z , a_uv.y * u_pxsize.w);
		v_color = texture2D(u_tex, UV);
		gl_Position = vec4(
			a_xy.x * u_pxsize.x ,
			a_xy.y * u_pxsize.y ,
			1.0 , 1.0);
	}
`;

var frag_src = `
	varying  highp  vec4  v_color;

	void main(void){
		gl_FragColor = v_color;
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xy', 'a_uv', 'u_pxsize', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
var TEX_SIZE = [360,640];

SRC = [0,0 , TEX_SIZE[0],0 , TEX_SIZE[0],TEX_SIZE[1] , 0,TEX_SIZE[1]];
function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', TEX_SIZE[0], TEX_SIZE[1]);

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

		var uv = [
			scx[0],scx[1] , SRC[0],SRC[1] , SRC[2],SRC[3] ,
			scx[0],scx[1] , SRC[2],SRC[3] , SRC[4],SRC[5] ,
			scx[0],scx[1] , SRC[4],SRC[5] , SRC[6],SRC[7] ,
			scx[0],scx[1] , SRC[6],SRC[7] , SRC[0],SRC[1] ,
		];
		QDFN.v2_attrib('a_uv', uv);

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
		var uv = [
			SRC[0],SRC[1] , SRC[2],SRC[3] , SRC[4],SRC[5] ,
			SRC[0],SRC[1] , SRC[4],SRC[5] , SRC[6],SRC[7] ,
		];
	}
	else {
		var xy = [
			DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7] ,
			DST[2],DST[3] , DST[6],DST[7] , DST[0],DST[1] ,
		];
		var uv = [
			SRC[2],SRC[3] , SRC[4],SRC[5] , SRC[6],SRC[7] ,
			SRC[2],SRC[3] , SRC[6],SRC[7] , SRC[0],SRC[1] ,
		];
	}
	QDFN.v2_attrib('a_xy', xy);
	QDFN.v2_attrib('a_uv', uv);

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
