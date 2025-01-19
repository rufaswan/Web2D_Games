<?php
if ( ! empty($_GET) )
{
	function json_files( &$list, $dir, $preg )
	{
		$func = __FUNCTION__;
		foreach ( scandir(__DIR__ . "/$dir") as $fn )
		{
			if ( $fn[0] === '.' )
				continue;
			$full = __DIR__ . "/$dir/$fn";

			if ( is_dir($full) )
			{
				$func($list, "$dir/$fn", $preg);
				continue;
			}

			if ( is_file($full) )
			{
				if ( preg_match($preg, $fn) )
					$list[] = "$dir/$fn";
				continue;
			}
		}
		return;
	}

	if ( isset($_GET['quad']) )
	{
		$list = array();
		json_files($list, '.', '|\.quad$|i');
		echo json_encode($list);
		exit();
	}
	if ( isset($_GET['png']) )
	{
		$list = array();
		json_files($list, '.', '|\.[0-9]+\..*\.png$|i');
		echo json_encode($list);
		exit();
	}

	function read_subfile( $path )
	{
		$full = __DIR__ . '/' . $_GET['getquad'];
		$real = realpath($full);
		// valid + file only
		if ( ! is_readable($real) || is_dir($real) )
			return '';
		// for security reason , only files from current dir only
		if ( stripos($real,__DIR__) === false )
			return '';
		return file_get_contents($real);
	}
	if ( isset($_GET['getquad']) )
	{
		$file = read_subfile($_GET['getquad']);
		echo $file;
		exit();
	}
	if ( isset($_GET['getpng']) )
	{
		$file = read_subfile($_GET['getpng']);
		echo 'data:image/png;base64,' . base64_encode($file);
		exit();
	}
	exit();
}
?><!doctype html>
<html>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Quad Player Embedded</title>
<style>
body {
	font-family : sans-serif;
	margin      : 0;
	padding     : 0;
	width       : 100vw;
	height      : 100vh;
	line-height : 1.5em;
}

table {
	border-collapse : collapse;
	width           : 100%;
	height          : 100%;
	margin          : 0 auto;
	overflow        : hidden;
}

td {
	width          : 50%;
	border         : 1px solid #000000;
	vertical-align : top;
}

#quadlist,
#pnglist {
	height  : 50%;
	padding : 1em;
}

#quadplayer {
	height  : 100%;
	padding : 0;
}

#quadplayer iframe {
	width  : 100%;
	height : 100%;
}
</style>
<body>

<table>
<tr>
	<td id='quadlist'>
		<button onclick='listfile("quad");'>QUAD</button>
		<ul></ul>
	</td>
	<td id='quadplayer' rowspan='2'>
		<iframe src='player-mobile.tpl.html' sandbox='allow-scripts allow-same-origin'></iframe>
	</td>
</tr>
<tr>
	<td id='pnglist'>
		<button onclick='listfile("png");'>PNG</button>
		<ul></ul>
	</td>
</tr>
</table>

<script>
'use strict';

function listfile( type ){
	var url = window.location.href + `?${type}`;
	window.fetch(url).then(function(res){
		return res.json(); // return promise
	}).then(function(res){
		var html = '';
		res.forEach(function(v,k){
			var li = document.createElement('li');
			li.setAttribute('data-type' , type);
			li.setAttribute('data-name' , v);
			li.setAttribute('onclick'   , 'getfile(this);');
			li.innerHTML = v;
			html += li.outerHTML;
		});
		var ul = document.querySelector(`#${type}list ul`);
		ul.innerHTML = html;
	});
}

function getfile( elem ){
	var type = elem.getAttribute('data-type');
	var name = elem.getAttribute('data-name');

	var url = window.location.href + `?get${type}=${name}`;
	window.fetch(url).then(function(res){
		return res.text(); // return promise
	}).then(function(res){
		// quad = '{"keyframe":[]}'        , min 15 char
		// png  = 'data:image/png;base64,' , min 22 char
		if ( res.length < 15 )
			return 0;
		if ( type === 'quad' || type === 'png' ){
			var qapp = TEST.iframe.contentWindow.APP;
			qapp.upload_queue.push({
				id   : 0,
				name : name,
				data : res,
			});
			qapp.upload_id = 0;
			qapp.process_uploads();
		}
	});
}

var TEST = {};
TEST.iframe = document.querySelector('#quadplayer iframe');
TEST.iframe.onload = function(){
	listfile('quad');
	listfile('png');
}
</script>
</body>
</html>
