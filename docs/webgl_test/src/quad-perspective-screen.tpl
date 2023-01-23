<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Test (Fullscreen * Minv)</title>
@@<quad-inc.css>@@
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.png>@@

@@<quad-inc.inc>@@
@@<quad-inc.js>@@

<script>
var vert_src = `
	attribute vec2  a_xy;
	uniform   vec4  u_pxsize;
	uniform   mat3  u_mat3;
	varying   vec3  v_tuv;

	vec3  v3;
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
	uniform sampler2D u_tex;
	varying vec3  v_tuv;

	vec2  v2;
	void main(void){
		if ( v_tuv.z == 0.0 )
			discard;

		v2.x = v_tuv.x / v_tuv.z;
		v2.y = v_tuv.y / v_tuv.z;
		if ( v2.x < 0.0 || v2.x > 1.0 )  discard;
		if ( v2.y < 0.0 || v2.y > 1.0 )  discard;
		gl_FragColor = texture2D(u_tex, v2);
	}
`;

var SHADER = QDFN.setShaderProgram(vert_src, frag_src);
QDFN.setShaderLoc('a_xy', 'u_pxsize', 'u_mat3', 'u_tex');

QDFN.setTexCount('u_tex', 1);
var TEX_SIZE = [360,640];

SRC = [0,0 , TEX_SIZE[0],0 , TEX_SIZE[0],TEX_SIZE[1] , 0,TEX_SIZE[1]];
function quadDraw()
{
	QDFN.setVec4pxSize('u_pxsize', TEX_SIZE[0], TEX_SIZE[1]);
	var mat3 = getTransMat3(SRC, DST, true);
	QDFN.setMatrix3fv('u_mat3', mat3);

	var box = QDFN.getBoundingClientRect();
	var hw  = box.width  * 0.5;
	var hh  = box.height * 0.5;

	var xy = [
		-hw,-hh , hw,-hh ,  hw,hh ,
		-hw,-hh , hw,hh  , -hw,hh ,
	];
	QDFN.v2Attrib('a_xy', xy);
	return QDFN.draw(6);
}

function render(){
	if ( IS_CLICK ){
		getDstCorner();
		quadDraw();
		IS_CLICK = false;
	}
	requestAnimationFrame(render);
}

QDFN.bindTex2DById(0, 'mona_lisa_png').then(function(){
	IS_CLICK = true;
	requestAnimationFrame(render);
});
</script>

</body></html>
