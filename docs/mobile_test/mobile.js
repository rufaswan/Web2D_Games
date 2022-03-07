var dom_canvas = document.getElementById('canvas');

var vert_src = `
	precision highp float;
	attribute vec3 a_xyz;
	attribute vec4 a_color;
	varying   vec4 v_color;

	void main(void){
		v_color = a_color;
		gl_Position = vec4(a_xyz, 1.0);
	}
`;
var frag_src = `
	precision highp float;
	varying   vec4 v_color;

	void main(void){
		gl_FragColor = v_color;
	}
`;
var gl_data = {
	'cnt' : 6,
	'xyz' : [
		-1, 1,1,  1, 1,1,   1,-1,1,
		-1, 1,1,  1,-1,1,  -1,-1,1,
	],
	'color' : [
		1,0,0,1,  0,1,0,1,  0,0,1,1,
		1,0,0,1,  0,0,1,1,  1,1,1,1,
	],
};

function test_webgl(){
	var GL = dom_canvas.getContext('webgl');
	if ( ! GL )  return;

	// compile shader
	var vert_shader = GL.createShader(GL.VERTEX_SHADER);
	GL.shaderSource (vert_shader, vert_src);
	GL.compileShader(vert_shader);

	var frag_shader = GL.createShader(GL.FRAGMENT_SHADER);
	GL.shaderSource (frag_shader, frag_src);
	GL.compileShader(frag_shader);

	var SHADER = GL.createProgram();
	GL.attachShader(SHADER, vert_shader);
	GL.attachShader(SHADER, frag_shader);
	GL.linkProgram (SHADER);

	function glattr( attr, data, cnt )
	{
		var loc = GL.getAttribLocation(SHADER, attr);
		var buf = GL.createBuffer();
		GL.bindBuffer(GL.ARRAY_BUFFER, buf);
		GL.bufferData(GL.ARRAY_BUFFER, new Float32Array(data), GL.STATIC_DRAW);
		GL.enableVertexAttribArray(loc);
		GL.vertexAttribPointer(loc, cnt, GL.FLOAT, false, 0, 0);
		return;
	}

	GL.useProgram(SHADER);
	glattr('a_xyz',   gl_data.xyz,   3);
	glattr('a_color', gl_data.color, 4);

	GL.viewport(0, 0, GL.drawingBufferWidth, GL.drawingBufferHeight);
	GL.drawArrays(GL.TRIANGLES, 0, gl_data.cnt);
	return;
}
test_webgl();
//////////////////////////////
var dom_filereader = document.getElementById('filereader');

document.getElementById('upload').addEventListener('change', function(e){
	for ( let up of this.files )
	{
		console.log(up.name, up.type);
		var promise = new Promise(function(resolve, reject){
			if ( up.type === 'text/plain' || up.type === 'application/json' )
			{
				var reader = new FileReader;
				reader.onload = function(){
					var tag = document.createElement('p');
					tag.innerHTML = reader.result;
					dom_filereader.appendChild(tag);
					resolve();
				}
				return reader.readAsText(up);
			}
			if ( up.type === 'image/png' )
			{
				var reader = new FileReader;
				reader.onload = function(){
					var img = new Image;
					img.onload = function(){
						// img.width
						// img.height
					}
					img.src = reader.result;
					dom_filereader.appendChild(img);
					resolve();
				}
				return reader.readAsDataURL(up);
			}
		});
	} // for ( let up of this.files )
});
//////////////////////////////
