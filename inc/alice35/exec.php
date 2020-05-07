<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
//require ROOT . "/inc/funcs-bmp.php";
require ROOT . "/inc/funcs-sjis.php";
require "exec_alice35.inc";

$sco_asm = array();
$gp_data = array();
$gp_run  = true;
$gp_sel  = array();
////////////////////////////////////////
function sco_divhtml( $div, &$cg )
{
	$mouse = "data-mouse='" . implode(',', $div['p']) . "'";
	$class = "class='sprites {$div['t']}'";
	switch ( $div['t'] )
	{
		case "text":
			$text  = $div["text"];
			$style = "style='color:#fff;'";
			echo "<div $class $mouse $style>$text</div>";
			return;
		case "img":
			$png = $div["img"];
			echo "<img $class $mouse src='$png'>";
			return;
		case "bg":
			list($id,$png,$sx,$sy) = $div["bg"];

			if ( ($sx | $sy) == 0 )
				echo "<img $class $mouse src='$png'>";
			else
			{
				$sxy = "data-sxy='$sx,$sy'";
				if ( ! isset($cg[$id]) )
				{
					echo "<style>$id {background-image:url('$png');}</style>";
					$cg[$id] = 1;
				}
				echo "<div $class class='$id' $mouse $sxy></div>";
			}
			return;
		case "border":
			$border = $div["border"];
			$style = "style='border:1px solid $border;'";
			echo "<div $class $mouse $style></div>";
			return;
		case "paint":
			$paint = $div["paint"];
			$style = "style='background-color:$paint;'";
			echo "<div $class $mouse $style></div>";
			return;
/*
		case "color":
			$color = $div["color"];
			$box .= ",{$div['color']}";
			echo "<div $class $mouse></div>";
			return;
*/
	}
	return;
}

function sco_jshtml()
{
	global $gp_pc;
	$timer = 0;
	echo "<script>";
	foreach ( $gp_pc["script"] as $js )
	{
		echo "";
	}
	echo "</script>";
	return;
}

function sco_html()
{
	global $gp_init, $gp_pc, $ajax_html;
	$ajax_html = "";
	sco_div_cleanup();
	ob_start();

/// CSS ///

	echo "<style>";

	// border-width:1px;
	// border-style:dotted/solid;
	$zs = $gp_pc["ZS"][0];
	echo "#select {";
		echo "background-color:#000;";
		echo "border:1px #fff solid;";
		echo "color:#fff;";
		echo "font-size:{$zs}px;";
	echo "}";
	$zm = $gp_pc["ZM"][0];
	echo ".text {";
		//echo "background-color:#000;";
		//echo "border:1px #fff solid;";
		echo "font-size:{$zm}px;";
	echo "}";
	echo "</style>";

/// CSS ///

/// WINDOW ///

	///////////
	$t1 = array();
	foreach ( $gp_pc["div"] as $div )
		sco_divhtml( $div, $t1 );
	///////////
	if ( ! empty( $gp_pc["select"] ) )
	{
		$tpos = sco_varref(0, "B_2", "B_1");
		$mouse = "data-mouse='{$tpos[0]},{$tpos[1]},0,0'";

		echo "<ul id='select' class='sprites' $mouse>";
		foreach ( $gp_pc["select"] as $k => $v )
		{
			echo "<li data-select='$k'>" .str_html($v[1]). "</li>";
		}
		echo "</ul>";
	}
	///////////

/// WINDOW ///

/// AUDIO ///

	if ( ! isset( $gp_pc["bgm"] ) )
		$gp_pc["bgm"] = array("", 0);
	$ogg = sco_file( $gp_pc["bgm"][0], $gp_pc["bgm"][1], PATH_OGG_1S );
	echo "<input id='filebgm' type='hidden' value='$ogg'>";

	$wave = PATH_OGG_1S;
	if ( isset( $gp_pc["SP"] ) )
		$wave = sco_file( "wave", $gp_pc["SP"][0], PATH_OGG_1S );
	echo "<input id='filewav' type='hidden' value='$wave'>";

/// AUDIO ///

/// SCRIPT ///

	echo "<script>function ajax_auto(){ return; }</script>";

/// SCRIPT ///
	$ajax_html = ob_get_clean();
}
////////////////////////////////////////
function sco_div_cleanup()
{
	global $gp_pc;
	$keys = array_keys( $gp_pc["div"] );
	$len = count($keys);
	$j = $len;
	while ( $j )
	{
		$j--;
		$kj = $keys[$j];
		if ( ! isset( $gp_pc["div"][$kj] ) )
			continue;

		for ( $i=0; $i < $j; $i++ )
		{
			$ki = $keys[$i];
			if ( ! isset( $gp_pc["div"][$ki] ) )
				continue;

			// skip sprites w/alpha
			if ( $gp_pc["div"][$kj]['t'] == "img" )
				continue;

			if ( box_within( $gp_pc["div"][$kj]['p'] , $gp_pc["div"][$ki]['p'] ) )
				unset( $gp_pc["div"][$ki] );

		} // for ( $i=0; $i < $j; $i++ )
	} // while ( $j )

	// re-numbering keys from overflow
	array_splice( $gp_pc["div"] , 0 , 0);
}

function sco_add_div( $type , $box , $arg )
{
	global $gp_pc;

	if ( $type == "border" )
		$arg = '#fff';
	if ( $type == "paint" )
	{
		list($x,$y) = $box;
		$box = var_box($x-8, $y-8, 16, 16, $gp_pc["WV"][2], $gp_pc["WV"][3]);
		$arg = '#f00';
	}

	$div = array(
		't' => $type,
		'p' => $box,
		$type => $arg,
	);
	$gp_pc["div"][] = $div;
	return;
}

function sco_add_text( $str )
{
	global $gp_pc, $gp_sel;

	if ( isset( $gp_sel[1] ) )
	{
		$gp_sel[1] .= $str;
		return;
	}

	$font = $gp_pc["ZM"][0];
	$tpos = sco_varref(0, "B_4", "B_3");

	if ( ! isset( $gp_pc['T'] ) )
		$gp_pc['T'] = array($tpos[0], $tpos[1]);

	if ( $str == "_NEXT_" )
	{
		$gp_pc['T'] = array($tpos[0], $tpos[1]);
		foreach ( $gp_pc["div"] as $k => $div )
		{
			if ( $div['t'] == "text" )
				unset( $gp_pc["div"][$k] );
		}
		return;
	}

	if ( $str == "_CRLF_" )
	{
		$gp_pc['T'][0]  = $tpos[0];
		$gp_pc['T'][1] += $font;
		return;
	}

	$len = utf8_strlen($str);

	$ulen = $len["utf8" ] *  $font;
	$alen = $len["ascii"] * ($font/2);
	$tlen = $ulen + $alen;

	$box = array(
		$gp_pc['T'][0],
		$gp_pc['T'][1],
		$tlen,
		$font,
	);
	sco_add_div( "text", $box, $text );
	$gp_pc['T'][0] += $tlen;
	return;
}

function sco_img_path( $id, $alpha )
{
	$img = sco_file("cg", $id, "");
	if ( stripos($img, ".png") )
		return $img;
	if ( stripos($img, ".clut") === false )
		return "";

	//$png = str_ireplace(".clut", ".png", $img);
	$png = sprintf("sav/%05.png", $id);
	clut2bmp( ROOT."/$img" , ROOT."/$png" , $alpha );

	//init_listfile( true );
	return $png;
}

function sco_img_adj( &$meta, $j, $rel, $rm )
{
	global $gp_pc;
	if ( ! isset( $gp_pc[$j] ) )
		return;

	list($x,$y) = $gp_pc[$j];
	if ( $rel )
	{
		$x += $meta[0];
		$y += $meta[1];
	}
	$meta[0] = $x;
	$meta[1] = $y;

	if ( $rm )
		unset( $gp_pc[$j] );
}

function sco_img_meta( $id )
{
	// get image X , Y , width , height
	$meta = file_get_contents( PATH_META );
	$meta = json_decode( $meta, true );
	if ( ! isset( $meta[$id] ) )
		return array();
	return $meta[$id];
}

function sco_add_img( $id, $alpha )
{
	$meta = sco_img_meta( $id );
	if ( empty($meta) )
		return;

	// affected by J command beforehand
	sco_img_adj( $meta , "J_0" , false , true  ); // abs once
	sco_img_adj( $meta , "J_1" , true  , true  ); // rel once
	sco_img_adj( $meta , "J_2" , false , false ); // abs
	sco_img_adj( $meta , "J_3" , true  , false ); // rel

	$png = sco_img_path($id, $alpha);
	if ( ! $png )  return;

	$div = array( 'p' => $meta );
	if ( $alpha == -1 )
		sco_add_div( "bg" , $meta, array(".bg_{$id}",$png,0,0) );
	else
		sco_add_div( "img", $meta, $png );
	return;
}
////////////////////////////////////////
function sco_varref($id, $var1, $var2)
{
	global $gp_pc;
	if ( isset( $gp_pc[$var1][$id] ) )
	{
		$n = $gp_pc[$var1][$id];
		return $gp_pc[$var2][$n];
	}
	return false;
}
////////////////////////////////////////
// for $line = sco_find("17,32");
function sco_find( $label )
{
	global $sco_asm;
	foreach ( $sco_asm as $k => $asm )
	{
		if ( strpos($asm, "// $label ") !== false )
			return $k+1;
	}
	return -1;
}

// for game files
function sco_file( $type, $id, $default )
{
	if ( $id === 0 )
		return $default;
	$fn = "";
	switch ( $type )
	{
		// by int , single  SA DA RA MA BA
		case "disk":  $fn = sprintf(GAME . "/sa/%03d", $id); break;
		case "bgm" :  $fn = sprintf(GAME . "/ba/%03d", $id); break;
		case "midi":  $fn = sprintf(GAME . "/ma/%03d", $id); break;
		case "data":  $fn = sprintf(GAME . "/da/%03d", $id); break;
		case "res" :  $fn = sprintf(GAME . "/ra/%03d", $id); break;

		// by int , multi  GA-GB-GC  WA-WB
		case "wave":  $fn = sprintf(GAME . "/wa/%03d/%05d", $id >> 8, $id); break;
		case "cg"  :  $fn = sprintf(GAME . "/ga/%03d/%05d", $id >> 8, $id); break;

		// by filename
		case "files":  $fn = sprintf(GAME . "/files/%s", $id); break;
	}
	if ( empty($fn) )
		return $default;

	$fp = fopen(LIST_FILE, "r");
	while ( ! feof($fp) )
	{
		$line = fgets($fp);
		if ( stripos($line,$fn) === 0 )
			return rtrim($line);
	}

	return $default;
}

function sco_msg( $id )
{
	return fgetline( PATH_SCOMSG, $id );
}
////////////////////////////////////////
function exec_alice35()
{
	global $gp_pc, $sco_asm, $gp_run;
	if ( empty($gp_pc["pc"]) )
		$gp_pc["pc"] = array("phpasm",1);

	$sco_asm = file(PATH_PHPASM, FILE_IGNORE_NEW_LINES);
	if ( file_exists(PATH_PATCH) )
		include PATH_PATCH;

	$if = false;
	while (1)
	{
		$bak = $gp_pc["pc"][1];
		if ( ! isset( $sco_asm[$bak] ) )
			break;

		$line = $sco_asm[$bak];
		if ( ! empty($line) && $line[0] != '/' )
			eval( $line );

		if ( ! $gp_run )
			break;
		if ( $bak == $gp_pc["pc"][1] )
			$gp_pc["pc"][1]++;
	}
	sco_html();
}
