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
		v2.x = v_tuv.x / v_tuv.z;
		v2.y = v_tuv.y / v_tuv.z;
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

	var scx = findIntersectPoint(SRC);
	var dcx = findIntersectPoint(DST);

	// for simple and twisted
	if ( dcx !== -1 )
	{
		var xy = [
			dcx[0],dcx[1] , DST[0],DST[1] , DST[2],DST[3] ,
			dcx[0],dcx[1] , DST[2],DST[3] , DST[4],DST[5] ,
			dcx[0],dcx[1] , DST[4],DST[5] , DST[6],DST[7] ,
			dcx[0],dcx[1] , DST[6],DST[7] , DST[0],DST[1] ,
		];
		QDFN.v2Attrib('a_xy', xy);

		console.log('simple and twisted', dcx);
		return QDFN.draw(12);
	}

	// bended
	var area1 = quadArea(DST[0],DST[1] , DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7]);
	var area2 = quadArea(DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7] , DST[0],DST[1]);

	if ( area1 < area2 )
	{
		var xy = [
			DST[0],DST[1] , DST[2],DST[3] , DST[4],DST[5] ,
			DST[0],DST[1] , DST[4],DST[5] , DST[6],DST[7] ,
		];
	}
	else
	{
		var xy = [
			DST[2],DST[3] , DST[4],DST[5] , DST[6],DST[7] ,
			DST[2],DST[3] , DST[6],DST[7] , DST[0],DST[1] ,
		];
	}
	QDFN.v2Attrib('a_xy', xy);

	console.log('bended', dcx);
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
