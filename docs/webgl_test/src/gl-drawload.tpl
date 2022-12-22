<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>WebGL Draw() Load Test</title>
<style>
body {
	position         : relative;
	background-color : #000;
	color            : #fff;
	margin           : 0;
	padding          : 0;
}

#canvas {
	position : absolute;
	width    : 100vw;
	height   : 100vh;
	top      : 0;
	left     : 0;
	z-index  : -1;
}

#mona_lisa_png {
	position : absolute;
	top      : -999px;
	left     : -999px;
}

#message {
	position         : absolute;
	background-color : #000;
	color            : #fff;
	top              : 0;
	left             : 0;
	white-space      : nowrap;
}
</style>
@@<qdfn.js>@@

</head><body>
@@<mona_lisa.png>@@
<canvas id='canvas'>Canvas not supported</canvas>
<div id='message'><p>DEBUG TEXT HERE</p></div>

<script>
QDFN.setWebGLCanvasById('canvas');

var vert_src = `
	attribute vec2  a_pos;
	varying   vec2  v_uv;

	void main(void){
		v_uv = vec2(a_pos.x * 0.5, a_pos.y * 0.5);
		gl_Position = vec4(
			a_pos.x  - 1.0 ,
			-a_pos.y + 1.0 ,
			1.0, 1.0);
	}
`;

var frag_src = `
	uniform sampler2D u_tex;
	varying vec2  v_uv;

	void main(void){
		gl_FragColor = texture2D(u_tex, v_uv);
	}
`;

var SHADER = QDFN.setShaderProgram(vert_src, frag_src);
QDFN.setShaderLoc('a_pos', 'u_tex');

QDFN.setTexCount('u_tex', 1);
QDFN.bindTex2DById(0, 'mona_lisa_png');

var pos = [
	0,0 , 2,0 , 2,2 ,
	0,0 , 2,2 , 0,2 ,
];
QDFN.v2Attrib('a_pos', pos);

var MESSAGE = document.getElementById('message');
//var TEX = QDFN.createTexture();
(function(){
	function drawload(){
		var prev = performance.now();
		var count = 0;
		var t;
		while (1){
			QDFN.draw(6);
			//QDFN.texImage2D();
			count++;

			t = performance.now();
			if ( (t-prev) > 1000 ){
				var p = document.createElement('p');
				var fps = (count / 60) | 0;
				p.innerHTML = count + ' draw()/sec , ' + fps + ' draw()/60 fps';
				MESSAGE.appendChild(p);
				return;
			}
		} // while (1)
	}

	for (var i=0; i < 10; i++ )
		drawload();
})();
</script>

</body></html>
