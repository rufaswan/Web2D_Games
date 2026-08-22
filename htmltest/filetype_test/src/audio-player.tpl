<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml'><head>

<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1' />
<title>Audio Player</title>
@@<main.css>@@

</head><body>
<main>
	<input id='pfile'  type='file' />
	<audio id='player' controls loop></audio>
</main>

<ol id='plist' class='block'>
</ol>

<footer>

<script>
	var PLAYER = document.getElementById('player');
	var PLIST  = document.getElementById('plist');
</script>

@@<uint8base64.js>@@
@@<unpackfile.js>@@

@@<audio-player.js>@@

</footer>
</body></html>
