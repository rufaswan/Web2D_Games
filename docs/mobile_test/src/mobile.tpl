<!doctype html>
<html><head>

<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Mobile Browser Test</title>
@@<mobile.css>@@
@@<mobile.js>@@

</head><body>

<h2>Mobile Browser</h2>
	<h3>Global Vars</h3>
	<table id='valuenavigator'></table>

	<h3>Navigator</h3>
	<table id='listnavigator'></table>

	<h3>Window</h3>
	<table id='listwindow'></table>

	<h3>Document</h3>
	<table id='listdocument'></table>

	<h3>CSS Support</h3>
	<table id='listcss'></table>

<h2>WebGL</h2>
	<h3>WebGL Parameter</h3>
	<table id='listwebgl'></table>

	<h3>WebGL Reference</h3>
	<table id='listreference'></table>

	<h3>WebGL Precision</h3>
	<table id='listprecision'></table>


<script>
var HTML = get_html_id();

// test Mobile Browser
var list = [
	[navigator , 'navigator', [
		'appName',
		'appCodeName',
		'appVersion',
		'product',
		'productSub',
		'vendor',
		'vendorSub',
		'platform',
		'oscpu',
		'buildID',
		'userAgent',
		'language',
		'doNotTrack',
		'cookieEnabled',
		'maxTouchPoints',
	]] ,
	[document , 'document' , [
		'URL',
		'baseURI',
		'location',
		'title',
		'dir',
		'domain',
		'referrer',
		'readyState',
		'visibilityState',
		'compatMode',
		'designMode',
	]] ,
];
mobilevalue(list, HTML.valuenavigator);

var list = [
	['mediaDevices' , 'getUserMedia'],
	['serviceWorker' , 'register'],
	['storage' , 'estimate'],
];
mobileparameter(navigator, 'navigator', list, HTML.listnavigator);

var list = [
	'KeyboardEvent',
	'MouseEvent',
	'TouchEvent',
	'PointerEvent',
	'StorageEvent',
	'File' , 'FileReader',
	'XMLHttpRequest',
	'MediaRecorder',
	'WebAssembly',
	['Promise' , 'all'],
	['JSON'    , 'parse'],
	['CSS'     , 'supports'],
	'atob', 'btoa',
	'sessionStorage' , 'localStorage',
	'requestAnimationFrame',
	'devicePixelRatio',
];
mobileparameter(window, 'window', list, HTML.listwindow);

var list = [
	'querySelector' , 'querySelectorAll',
];
mobileparameter(document, 'document', list, HTML.listdocument);

var list = [
	['display', 'flex'],
	['display', 'contents'],
	['display', 'grid'],
	['width'  , '1vw'],
	['width'  , '1em'],
	['width'  , '1rem'],
	['width'  , 'calc(50% - 200px)'],
];
mobilecss(list, HTML.listcss);

// test WEBGL
var WEBGL_OPT = {
	alpha                 : true,
	antialias             : true,
	depth                 : true,
	premultipliedAlpha    : false,
	preserveDrawingBuffer : true,
	stencil               : true,
};
var WEBGL = document.createElement('canvas').getContext('webgl', WEBGL_OPT);
if ( WEBGL ){
	glprecision(WEBGL, HTML.listprecision);

	// from https://www.khronos.org/files/webgl/webgl-reference-card-1_0.pdf
	var list = [
		['RED_BITS'     ,  8], // page 3 : lowp
		['GREEN_BITS'   ,  8], // page 3 : lowp
		['BLUE_BITS'    ,  8], // page 3 : lowp
		['ALPHA_BITS'   ,  8], // page 3 : lowp
		['DEPTH_BITS'   , 16], // page 1 : webgl context attributes
		['STENCIL_BITS' ,  8], // page 1 : webgl context attributes

		// page 4 : built-in constants with minimum values
		['MAX_VERTEX_ATTRIBS'              ,   8],
		['MAX_VERTEX_UNIFORM_VECTORS'      , 128],
		['MAX_VARYING_VECTORS'             ,   8],
		['MAX_VERTEX_TEXTURE_IMAGE_UNITS'  ,   0],
		['MAX_COMBINED_TEXTURE_IMAGE_UNITS',   8],
		['MAX_TEXTURE_IMAGE_UNITS'         ,   8],
		['MAX_FRAGMENT_UNIFORM_VECTORS'    ,  16],
		['MAX_DRAW_BUFFERS'                ,   1],
	];
	glreference(WEBGL, list, HTML.listreference)

	var list = [
		'VERSION',
		'SHADING_LANGUAGE_VERSION',
		'VENDOR',
		'RENDERER',
		'MAX_CUBE_MAP_TEXTURE_SIZE',
		'MAX_RENDERBUFFER_SIZE',
		'MAX_TEXTURE_SIZE',
		'MAX_VIEWPORT_DIMS',
		'ALIASED_POINT_SIZE_RANGE',
		'ALIASED_LINE_WIDTH_RANGE',
	];
	glparameter(WEBGL, list, HTML.listwebgl);
}
</script>

</body></html>

