<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>setInterval FPS Test</title>
@@<fps.css>@@

</head><body>

<canvas id='canvas'>Canvas not supported</canvas>
<ol id='console'></ol>

<script>
'use strict';

(function(){
	var CON = document.getElementById('console');
	var cDraw = [];
	var nDraw = 0;

	var t2 = setInterval(function(){
		nDraw++;
	}, 1);

	var t1  = setInterval(function(){
		cDraw.push(nDraw);
		nDraw = 0;
		//console.log(cDraw.length);

		if ( cDraw.length > 5 ){
			clearInterval(t1);
			clearInterval(t2);

			cDraw.forEach(function(v){
				var li = document.createElement('li');
				li.innerHTML = v+ ' draw/sec , ' +1000/v+ ' ms/draw()';
				CON.appendChild(li);
			});
		}
	}, 1000);
})();
</script>

</body></html>
