<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Bilinear Test (Fullscreen)</title>
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
	varying   vec2  v_xy;

	void main(void){
		v_xy = a_xy;
		gl_Position = vec4(
			a_xy.x * u_pxsize.x ,
			a_xy.y * u_pxsize.y ,
			1.0 , 1.0);
	}
`;

var frag_src = `
	uniform vec4  u_pxsize;
	uniform mat4  u_mat4;
	uniform sampler2D u_tex;
	varying vec2  v_xy;

	vec2   v2;
	float  tv, tu;
	void main(void){
		float a0 = u_mat4[0][0];
		float a1 = u_mat4[0][1];
		float a2 = u_mat4[0][2];
		float a3 = u_mat4[0][3];
		float b0 = u_mat4[1][0];
		float b1 = u_mat4[1][1];
		float b2 = u_mat4[1][2];
		float b3 = u_mat4[1][3];
		float A  = u_mat4[2][0];
		float B1 = u_mat4[2][1];
		float C1 = u_mat4[2][2];

		float B  = B1 + (b3 * v_xy.x) - (a3 * v_xy.y);
		float C  = C1 + (b1 * v_xy.x) - (a1 * v_xy.y);

		float rt = sqrt( (B * B) - (4.0 * A * C) );

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

var SHADER = QDFN.setShaderProgram(vert_src, frag_src);
QDFN.setShaderLoc('a_xy', 'u_pxsize', 'u_mat4', 'u_tex');

QDFN.setTexCount('u_tex', 1);
var TEX_SIZE = [360,640];

SRC = [0,0 , TEX_SIZE[0],0 , TEX_SIZE[0],TEX_SIZE[1] , 0,TEX_SIZE[1]];
function quadDraw()
{
	QDFN.canvasSize();
	QDFN.setVec4pxSize('u_pxsize', TEX_SIZE[0], TEX_SIZE[1]);

	var mat4 = bilinearwarp(DST, TEX_SIZE[0], TEX_SIZE[1]);
	QDFN.setMatrix4fv('u_mat4', mat4);

	var hw  = QDFN.GL.drawingBufferWidth  * 0.5;
	var hh  = QDFN.GL.drawingBufferHeight * 0.5;

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

/*
	uniform float u_cof[11];
	GL.uniform1fv(LOC.u_cof, co);
*/
</script>

</body></html>
