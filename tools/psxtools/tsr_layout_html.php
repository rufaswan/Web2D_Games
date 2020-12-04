<?php
/*
[license]
[/license]
 */
require "common.inc";
define("NONE_PNG", "data:image/png;charset=utf-8;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAgMAAABinRfyAAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAAJUExURQAAAP8AAP///2cZZB4AAAABdFJOUwBA5thmAAAAAWJLR0QCZgt8ZAAAAAd0SU1FB+QGGgEMCSVOLPAAAAA4SURBVAjXYwhgYGBlCGFgEAVCIM3qwBjAwBjA6gDkAAUYRIFSDKwBMALMBUmAlYAVg7WBDQAZBQAWAQb1CwbkAwAAAABJRU5ErkJggg==");

function htmlhead( $dir, $g )
{
	$none = NONE_PNG;
	$grid = "";

	$png = __DIR__ . "/patch/$g.png";
	if ( is_file($png) )
	{
		$f = file_get_contents($png);
		$grid = "background:";
		$grid .= sprintf(" url('data:image/png;charset=utf-8;base64,%s')", base64_encode($f));
		$grid .= " top left;";
	}

	$html = <<<_HTML
<!DOCTYPE html><html><head>
<title>$dir/layout.txt</title>
<style>
* {
	margin:  0;
	padding: 0;
	position: absolute;
	left: 0;
	top:  0;
}
body { $grid background-color:#000; }
img:hover { background-color:#fff; }
.none {
	width:  16px;
	height: 16px;
	margin-top:  -8px;
	margin-left: -8px;
	background: url('$none') no-repeat center center;
}
</style>
</head><body>

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
window.onload = function() {
	var tags = document.querySelectorAll("img.sprite");
	for ( var i=0; i < tags.length; i++ )
	{
		var x1 = 0 - ( tags[i].width  / 2 );
		var y1 = 0 - ( tags[i].height / 2 );
		tags[i].style.left = x1 + "px";
		tags[i].style.top  = y1 + "px";
	}
	return;
};
</script>
</body></html>
_HTML;
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
	echo $html;
	return;
}

function imghtml( &$img, $png, $tab, $class = false )
{
	if ( ! empty($img) )  return;
	if ( ! is_file($png) )  return;
	if ( $class )
		$img = "$tab<img class='sprite' src='$png' title='$png'>";
	else
		$img = "$tab<img src='$png' title='$png'>";
	return;
}

function htmldiv( &$layout, $dir, $zone, $tab_no = 0 )
{
	$tab = str_repeat(' ', $tab_no*2);
	$func = __FUNCTION__;

	// recursive divs
	if ( isset( $layout[$zone] ) )
	{
		if ( empty( $layout[$zone] ) )
			return;
		foreach ( $layout[$zone] as $v )
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

	# monsters , objects , items ...
	$img = "";
	imghtml($img, "$dir/$zone/center.png", $tab, true);
	imghtml($img, "$dir/$zone/0000.png"  , $tab, true);
	imghtml($img, "$dir/$zone.png"       , $tab, false);
	imghtml($img, "$zone/center.png", $tab, true);
	imghtml($img, "$zone/0000.png"  , $tab, true);
	imghtml($img, "$zone.png"       , $tab, false);
	if ( ! empty($img) )
		return printf("$img\n");

	$z = str_replace('_', '/', $zone);
	if ( $z != $zone )
	{
		imghtml($img, "$dir/$z.png", $tab, false);
		imghtml($img, "$z.png"     , $tab, false);
		if ( ! empty($img) )
			return printf("$img\n");
	}

	// nothing matched
	echo "$tab<div class='sprite none' title='$zone'></div>\n";
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

$gp_grid = "";
ob_start();
for ( $i=1; $i < $argc; $i++ )
{
	if ( $argv[$i][0] == '-' )
		$gp_grid = substr($argv[$i], 1);
	else
		layouttxt( $argv[$i] );
}
$html = ob_get_clean();
if ( empty($html) )
	exit();

htmlhead(__DIR__ , $gp_grid);
echo $html;
htmlfoot();
//save_file("layout.html", $html);
