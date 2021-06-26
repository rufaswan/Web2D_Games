<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
define("NONE_PNG", "data:image/png;charset=utf-8;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAgMAAABinRfyAAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAAJUExURQAAAP8AAP///2cZZB4AAAABdFJOUwBA5thmAAAAAWJLR0QCZgt8ZAAAAAd0SU1FB+QGGgEMCSVOLPAAAAA4SURBVAjXYwhgYGBlCGFgEAVCIM3qwBjAwBjA6gDkAAUYRIFSDKwBMALMBUmAlYAVg7WBDQAZBQAWAQb1CwbkAwAAAABJRU5ErkJggg==");

// MARL ( Map - Area - Room - Layer )
$gp_grid = "";

function bggrid()
{
	global $gp_grid;
	if ( empty($gp_grid) )
		return '';

	$png = sprintf("%s/patch/%s.png", __DIR__, $gp_grid);
	if ( is_file($png) )
		$gp_grid = $png;

	if ( ! is_file($gp_grid) )
		return '';

	$f   = file_get_contents($gp_grid);
	$bg  = "background :";
	$bg .= sprintf(" url('data:image/png;charset=utf-8;base64,%s')", base64_encode($f));
	$bg .= " top left;";
	return $bg;
}

function htmlhead( $title )
{
	$grid = bggrid();
	$none = NONE_PNG;

	$html = <<<_HTML
<!DOCTYPE html>
<html>
<head>
	<title>$title</title>
	<meta charset='utf-8'>
	<style>
		* {
			margin   : 0;
			padding  : 0;
			position : absolute;
			left     : 0;
			top      : 0;
		}
		body {
			$grid
			background-color : #000;
		}
		img:hover {
			background-color : #fff;
		}
		.none {
			width       : 16px;
			height      : 16px;
			margin-top  : -8px;
			margin-left : -8px;
			background  : url('$none') no-repeat center center;
		}
	</style>
</head>
<body>

_HTML;
	echo $html;
	return;
}

function htmlfoot()
{
	// <element onload="sampleScript">
	// object.onload = () => sampleScript();
	// object.addEventListener("load", sampleScript);
	$html = <<<_HTML
	<script>
	window.onload = function(){
		var tags = document.querySelectorAll("img.sprite");
		for ( var i=0; i < tags.length; i++ )
		{
			var x1 = ( tags[i].width  / 2 ) | 0;
			var y1 = ( tags[i].height / 2 ) | 0;
			tags[i].style.left = -x1 + "px";
			tags[i].style.top  = -y1 + "px";
		}
		return;
	};
	</script>
</body>
</html>

_HTML;
	echo $html;
	return;
}

function imghtml( &$img, $png, $tab, $class=false )
{
	if ( ! empty  ($img) )  return;
	if ( ! is_file($png) )  return;

	// javascript to auto-center the image with 'sprite' class
	if ( $class )
		$img = "$tab<img class='sprite' src='$png' title='$png'>";
	else
		$img = "$tab<img src='$png' title='$png'>";
	return;
}

function htmldiv( &$layout, $dir, $room, $tab_no = 0 )
{
	$tab = str_repeat(' ', $tab_no*2);

	// recursive divs
	if ( isset( $layout[$room] ) )
	{
		if ( empty( $layout[$room] ) )
			return;

		$func = __FUNCTION__;
		foreach ( $layout[$room] as $v )
		{
			if ( strpos($v, '+') === false ) // map_1+256+192
				continue;
			list($z,$x,$y) = explode('+', $v);

			$zz = substr($z, 0, strpos($z, '_'));
			echo "$tab<div class='$z $zz' style='left:{$x}px;top:{$y}px;'>\n";

			$func($layout, $dir, $z, $tab_no+1);
			echo "$tab</div>\n";
		}
		return;
	}

	# layers , monsters , objects , items ...
	$z = str_replace('_', '/', $room);

	$img = "";
	imghtml($img, "$dir/$room/center.png", $tab, true);
	imghtml($img, "$dir/$room/0000.png"  , $tab, true);
	imghtml($img, "$dir/$room.png"       , $tab, false);
	imghtml($img, "$dir/$z.png"          , $tab, false);
	imghtml($img, "$room/center.png"     , $tab, true);
	imghtml($img, "$room/0000.png"       , $tab, true);
	imghtml($img, "$room.png"            , $tab, false);
	imghtml($img, "$z.png"               , $tab, false);
	if ( ! empty($img) )
		return printf("$img\n");

	// nothing matched
	// display centered 'X'
	echo "$tab<div class='sprite none' title='$room'></div>\n";
	return;
}
//////////////////////////////
function layouttxt( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$fname = "$dir/layout.txt";
	if ( ! file_exists($fname) )
		return;

	$layout = array();
	foreach ( file($fname) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		if ( empty($line) )
			continue;
		list($id,$data) = explode('=', $line);
		$data = explode(',', $data);
		$layout[$id] = $data;
	}

	htmldiv($layout, $dir, 'main');
	return;
}

printf("usage : %s  [16x15]  [title]  DIR...\n", $argv[0]);
$title = "Layout txt2html";

ob_start();
	for ( $i=1; $i < $argc; $i++ )
	{
		if ( is_dir ( $argv[$i] ) )
			layouttxt( $argv[$i] );
		else
		if ( is_file( $argv[$i] ) )
			$gp_grid = $argv[$i];
		else
			$title = $argv[$i];
	}
$html = ob_get_clean();
if ( empty($html) )
	exit();

ob_start();
	htmlhead($title);
	echo $html;
	htmlfoot();
$html = ob_get_clean();
save_file("$title.html", $html);

/*
		var x1 = parseInt( tags[i].parentNode.style.left ) - ( tags[i].width  / 2 );
		var y1 = parseInt( tags[i].parentNode.style.top  ) - ( tags[i].height / 2 );
		tags[i].parentNode.style.left = x1 + "px";
		tags[i].parentNode.style.top  = y1 + "px";

		var x1 = 0 - ( tags[i].width  / 2 );
		var y1 = 0 - ( tags[i].height / 2 );
		tags[i].style.left = x1 + "px";
		tags[i].style.top  = y1 + "px";
 */
