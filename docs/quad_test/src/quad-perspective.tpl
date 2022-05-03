<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Perspective Transformation Test</title>
@@<quad.css>@@

</head><body>

@@<Mona_Lisa.png>@@
<canvas id='canvas'>Canvas not supported</canvas>

<div id='corner0' class='clickable'>A</div>
<div id='corner1' class='clickable'>B</div>
<div id='corner2' class='clickable'>C</div>
<div id='corner3' class='clickable'>D</div>

<script>
	var CANVAS = document.getElementById('canvas');
	var IS_CLICK = true;
</script>

@@<click.js>@@

@@<qdfn.js>@@
@@<webgl-perspective.js>@@

</body></html>

