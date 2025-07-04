<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml'><head>

<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1' />
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
@@<mona-lisa.png>@@
<canvas id='canvas'>Canvas not supported</canvas>
<div id='message'><p>DEBUG TEXT HERE</p></div>

<script>
QDFN.set_webgl_by_id('canvas');

var vert_src = `
	attribute  highp  vec2  a_pos;
	varying    highp  vec2  v_uv;

	highp  vec2  xy;
	highp  vec2  uv;
	void main(void){
		uv = a_pos.xy * 0.5;
		xy = a_pos.xy - 1.0;
			xy.y = -xy.y;

		v_uv = uv;
		gl_Position = vec4(xy.x , xy.y , 1.0, 1.0);
	}
`;

var frag_src = `
	uniform  sampler2D  u_tex;
	varying  highp  vec2  v_uv;

	void main(void){
		gl_FragColor = texture2D(u_tex, v_uv);
	}
`;

QDFN.set_shader_program(vert_src, frag_src);
QDFN.set_shader_loc('a_pos', 'u_tex');

QDFN.set_tex_count('u_tex', 1);

var pos = [
	0,0 , 2,0 , 2,2 ,
	0,0 , 2,2 , 0,2 ,
];
QDFN.v2_attrib('a_pos', pos);

var MESSAGE = document.getElementById('message');
//var TEX = QDFN.create_texture();

QDFN.bind_tex2D_id(0, 'mona_lisa_png').then(function(){
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
});
</script>

</body></html>
