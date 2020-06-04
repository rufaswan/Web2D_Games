<?php

function htmlhead( $dir )
{
	$html = <<<_HTML
<!DOCTYPE html><html><head>
<title>$dir</title>
<style>
* { margin:0; padding:0; }
body { background-color:#000; }
.pos { position:absolute; }
</style>
</head><body>

_HTML;
	return $html;
}

function htmlfoot()
{
	$html = <<<_HTML
<script>
function position_map()
{
	var tags = document.querySelectorAll(".pos");
	for ( var i=0; i < tags.length; i++ )
	{
		var d1 = tags[i].getAttribute("data-map");
		var d2 = d1.split(',');
		var style = "";
		style += "left:" + d2[0] + "px;";
		style += "top:"  + d2[1] + "px;";
		tags[i].setAttribute("style", style);
	}
	return;
}
position_map();
</script>
</body></html>
_HTML;
	return $html;
}
//////////////////////////////
function mkhtml( $dir, &$zone, &$nest )
{

	foreach ( $zone as $zk => $zv )
	{
		$html .= "<div class='$zk' data-pos='{$zv[0]},{$zv[1]}'>\n";
		$cnt = count($zv);
		for ( $i=2; $I < $cnt; $i++ )
		{
			if ( is_array($zv[$i]) )
				$func( $zv[$i] );
			else
				$html .= "<img src='{$zv[$i]}' title='{$zv[$i]}'>\n";
		}

		$html .= "</div>\n";
	} // 	foreach ( $zone as $zk => $zv )

	return;
}

function layouttxt( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$layout = "$dir/layout.txt";
	if ( ! file_exists($layout) )
		return;

	$html = htmlhead($dir);
	$nest = array();
	$zone = array();
	foreach ( file($layout) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		if ( empty($line) )
			continue;

		$data = explode(',', $line);

		$cnt = count($data);
		if ( $cnt < 3 )
			continue;

		$id = $data[0];
		$sty = sprintf("style='left:%dpx;top:%dpx;'", $data[1], $data[2]);
		$cls = substr($id, strpos($id, '_'));
		$cls = "class='pos $cls'";

		$div = "";
		for ( $i=3; $i < $cnt; $i++ )
		{
			$d = $data[$i];
			if ( isset( $zone[$d] ) )
			{
				$data[$i] = $zone[$d];
				$nest[ $zone[$d] ] = 1;
				continue;
			}

			if ( is_dir("$dir/$d") )
			{
				$png = "$d/0000.png";
				$png = "<img $cls $sty src='$png' title='$png'>\n";
				$data[$i] = $png;
				$div .= $png;
				continue;
			}

			if ( is_file("$dir/$d.png") )
			{
				$png = "$d.png";
				$png = "<img $cls $sty src='$png' title='$png'>\n";
				$data[$i] = $png;
				$div .= $png;
				continue;
			}

			trigger_error("ERROR unknown $d for $id\n", E_USER_WARNING);
		}

		$html .= $div;
		$zone[$id] = $div;
	} // foreach ( file($layout) as $line )

	$html .= htmlfoot();
	file_put_contents("$dir/layout.html", $html);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	layouttxt( $argv[$i] );

/*
sample layout.txt

spr_1 = mon_5,75,75 | mon_5,50,75 | mon_2,30,30
zone_1 = fg_1,256,256 | bg_0,0,0 | spr_1,256,256

sample html
<div class='zone_1 zone'>
</div>
 */
