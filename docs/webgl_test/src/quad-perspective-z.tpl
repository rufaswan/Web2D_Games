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
	attribute vec3   a_xyz;
	attribute vec2   a_uv;
	uniform   vec4   u_pxsize;
	varying   vec2   v_uv;
	varying   float  v_z;

	vec3   v3;
	vec2   v2;
	float  z;
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
	uniform sampler2D u_tex;
	varying vec2   v_uv;
	varying float  v_z;

	float  z;
	void main(void){
		if ( v_z == 0.0 )
			discard;
		z = 1.0 / v_z;
		gl_FragColor = texture2D(u_tex, v_uv * z);
	}
`;

var SHADER = QDFN.setShaderProgram(vert_src, frag_src);
QDFN.setShaderLoc('a_xyz', 'a_uv', 'u_pxsize', 'u_tex');

QDFN.setTexCount('u_tex', 1);
var TEX_SIZE = [360,640];

SRC = [0,0 , TEX_SIZE[0],0 , TEX_SIZE[0],TEX_SIZE[1] , 0,TEX_SIZE[1]];

function dst_perp( dst, src='' )
{
	if ( src === '' )
		src = [10,10 , 20,10 , 20,20 , 10,20];
	var mat3 = getTransMat3(src, dst, false);
	var v0 = matrix_multi31(mat3, [ src[0],src[1],1 ]);
	var v1 = matrix_multi31(mat3, [ src[2],src[3],1 ]);
	var v2 = matrix_multi31(mat3, [ src[4],src[5],1 ]);
	var v3 = matrix_multi31(mat3, [ src[6],src[7],1 ]);
	console.log(dst, [v0,v1,v2,v3]);
	return [v0,v1,v2,v3];
}

function quadDraw()
{
	QDFN.canvasSize();
	QDFN.setVec4pxSize('u_pxsize', TEX_SIZE[0], TEX_SIZE[1]);
	var dst = dst_perp(DST);

	var xyz = [
		dst[0][0],dst[0][1],dst[0][2] , dst[1][0],dst[1][1],dst[1][2] , dst[2][0],dst[2][1],dst[2][2] ,
		dst[0][0],dst[0][1],dst[0][2] , dst[2][0],dst[2][1],dst[2][2] , dst[3][0],dst[3][1],dst[3][2] ,
	];
	var uv = [
		SRC[0],SRC[1] , SRC[2],SRC[3] , SRC[4],SRC[5] ,
		SRC[0],SRC[1] , SRC[4],SRC[5] , SRC[6],SRC[7] ,
	];

	QDFN.v3Attrib('a_xyz', xyz);
	QDFN.v2Attrib('a_uv' , uv);
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
