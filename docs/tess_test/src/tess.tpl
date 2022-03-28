<!doctype html>
<html>
<head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Tessellation Test</title>
@@tess.css@@

</head>
<body>

@@Mona_Lisa.png@@
<canvas id='canvas'>Canvas not supported</canvas>

<div id='corner0' class='clickable'>A</div>
<div id='corner2' class='clickable'>B</div>
<div id='corner4' class='clickable'>C</div>
<div id='corner6' class='clickable'>D</div>

<script>
	var CANVAS = document.getElementById('canvas');
</script>

@@click.js@@
@@webgl.js@@

</body>
</html>

