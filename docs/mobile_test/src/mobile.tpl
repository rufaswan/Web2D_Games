<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Mobile Test</title>
@@<mobile.css>@@

</head><body>
<main>

<input type='file' id='upload' multiple>
<div id='filereader' class='section'>test</div>
@@<mobile-filereader.js>@@

</main>
<script>var DOM_MAIN = document.getElementsByTagName('main')[0];</script>

@@<mobile-user-agent.js>@@
@@<mobile-log-js.js>@@
@@<mobile-log-css.js>@@
@@<mobile-webgl.js>@@

</body></html>
