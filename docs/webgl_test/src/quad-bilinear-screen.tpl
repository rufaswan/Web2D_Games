<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Bilinear Test (Fullscreen)</title>
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
	varying    highp  vec2  v_xy;

	highp  vec2  xy;
	void main(void){
		xy = a_xy.xy * u_pxsize.xy;

		v_xy = a_xy;
		gl_Position = vec4(xy.x , xy.y , 1.0 , 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	uniform  highp  vec4  u_pxsize;
	uniform  highp  mat4  u_mat4;
	varying  highp  vec2  v_xy;

	highp  vec2   v2;
	highp  float  tv;
	highp  float  tu;
	void main(void){
		highp  float  a0 = u_mat4[0][0];
		highp  float  a1 = u_mat4[0][1];
		highp  float  a2 = u_mat4[0][2];
		highp  float  a3 = u_mat4[0][3];
		highp  float  b0 = u_mat4[1][0];
		highp  float  b1 = u_mat4[1][1];
		highp  float  b2 = u_mat4[1][2];
		highp  float  b3 = u_mat4[1][3];
		highp  float  A  = u_mat4[2][0];
		highp  float  B1 = u_mat4[2][1];
		highp  float  C1 = u_mat4[2][2];

		highp  float  B  = B1 + (b3 * v_xy.x) - (a3 * v_xy.y);
		highp  float  C  = C1 + (b1 * v_xy.x) - (a1 * v_xy.y);

		highp  float  rt = sqrt( (B * B) - (4.0 * A * C) );

		tv = (-B + rt) / (2.0 * A);
		tu = (v_xy.x - a0 - (a2 * tv)) / (a1 + (a3 * tv));
			v2.x = tu * u_pxsize.z;
			v2.y = tv * u_pxsize.w;

		// no texel matched
		// probably hflip/vflip with opposite direction
		if ( v2.x < 0.0 || v2.x > 1.0 || v2.y < 0.0 || v2.y > 1.0 )
		{
			tv = (-B - rt) / (2.0 * A);
			tu = (v_xy.x - a0 - (a2 * tv)) / (a1 + (a3 * tv));
				v2.x = tu * u_pxsize.z;
				v2.y = tv * u_pxsize.w;
		}

		if ( v2.x < 0.0 || v2.x > 1.0 || v2.y < 0.0 || v2.y > 1.0 )
			discard;
		gl_FragColor = texture2D(u_tex, v2);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_xy', 'u_pxsize', 'u_mat4', 'u_tex');

QDFN.set_tex_count('u_tex', 1);
__.texsize = 0;

function quad_draw(){
	QDFN.canvas_resize();
	QDFN.set_vec4_size('u_pxsize', __.texsize[0], __.texsize[1]);

	var mat4 = bilinearwarp(__.dst, __.texsize[0], __.texsize[1]);
	QDFN.set_mat4fv('u_mat4', mat4);

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

/*
	uniform float u_cof[11];
	GL.uniform1fv(LOC.u_cof, co);
*/
</script>

</body></html>
