<?php
require "common.inc";

function htmlhead( $dir )
{
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
.none { display:none; }
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
	$png = "$zone/0000.png";
	if ( is_file("$dir/$png") )
	{
		echo "$tab<img class='sprite' src='$png' title='$png'>\n";
		return;
	}

	// for maps
	$png = "$zone.png";
	if ( is_file("$dir/$png") )
	{
		echo "$tab<img src='$png' title='$png'>\n";
		return;
	}

	// for items
	if ( strpos($zone, '_') !== false )
	{
		$b1 = explode('_', $zone);
		$png = sprintf("%s/%04d.png", $b1[0], $b1[1]);
		if ( is_file("$dir/$png") )
		{
			echo "$tab<img src='$png' title='$png'>\n";
			return;
		}
	}

	// nothing matched
	echo "$tab<div class='$zone none'></div>\n";
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

	ob_start();
		htmlhead($dir);
		htmldiv($layout, $dir, 'main');
		htmlfoot();
	$html = ob_get_clean();
	save_file("$dir/layout.html", $html);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	layouttxt( $argv[$i] );

/*
=== sample layout.txt
main = map_1,0,0 | zone_1,0,0
zone_1 = mon_5,75,75 | mon_5,50,75 | mon_2,30,30

=== sample html
<div class='map_1 map' style='left:0px;top:0px'>
	<img src='map_1.png' title='map_1.png'>
</div>
<div class='zone_1 zone' style='left:0px;top:0px'>
	<div class='mon_5 mon' style='left:75px;top:75px'>
		<img class='sprite' src='mon_5/0000.png' title='mon_5/0000.png'>
	</div>
	<div class='mon_5 mon' style='left:50px;top:75px'>
		<img class='sprite' src='mon_5/0000.png' title='mon_5/0000.png'>
	</div>
	<div class='mon_2 mon' style='left:30px;top:30px'>
		<img class='sprite' src='mon_2/0000.png' title='mon_2/0000.png'>
	</div>
</div>
 */
