<?php
require "common.inc";
define("NONE_PNG", "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAXElEQVQ4y62SSxaAMAgDJ9z/znWjLrAfeJFlYAKlaMAAEIhGPFxkoQPfjT+CGrC0SlTgd4KKySqnSped8XHUVKyJdl7Wbi+BGf8/wVqi9Y3WIVmn3IFnJtGFc+0FVYAyEC56pV4AAAAASUVORK5CYII=");

function htmlhead( $dir )
{
	$none = NONE_PNG;
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
body { background-color:#000; }
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

function htmldiv( &$layout, $dir, $zone, $tab_no = 0 )
{
	$tab = str_pad('', $tab_no*2, ' ');
	$func = __FUNCTION__;

	// recursive divs
	if ( isset( $layout[$zone] ) )
	{
		foreach ( $layout[$zone] as $v )
		{
			list($z,$x,$y) = explode('+', $v);
			$zz = substr($z, 0, strpos($z, '_'));
			echo "$tab<div class='$z $zz' style='left:{$x}px;top:{$y}px;'>\n";
			$func($layout, $dir, $z, $tab_no+1);
			echo "$tab</div>\n";
		}
		return;
	}

	// for monsters
	$png = "$dir/$zone/0000.png";
	if ( is_file($png) )
	{
		echo "$tab<img class='sprite' src='$png' title='$png'>\n";
		return;
	}

	// for maps
	$png = "$dir/$zone.png";
	if ( is_file($png) )
	{
		echo "$tab<img src='$png' title='$png'>\n";
		return;
	}

	// for items
	if ( strpos($zone, '_') !== false )
	{
		$b1 = explode('_', $zone);
		$png = sprintf("$dir/%s/%04d.png", $b1[0], $b1[1]);
		if ( is_file($png) )
		{
			echo "$tab<img src='$png' title='$png'>\n";
			return;
		}
	}

	// nothing matched
	//printf("%s<img class='sprite none' src='%s' title='$zone'>\n", $tab, XFILE_PNG);
	echo "$tab<div class='sprite none' title='$zone'></div>";
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

//ob_start();
htmlhead($dir);
for ( $i=1; $i < $argc; $i++ )
	layouttxt( $argv[$i] );
htmlfoot();
//$html = ob_get_clean();
//save_file("layout.html", $html);
