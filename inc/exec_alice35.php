<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
/*
 * 00  4  magic "S350/S351"
 * 04  4  start
 * 08  4  filesize
 * 0c  4  sco index
 * 10  2  adv filename length
 * 12  a  adv filename length
 * 1c  4
 * 20  ...  bytecode
 *
 * G0 G1 GS - GA.ald
 * LL       - DA.ald
 * SG       - MA.ald
 * SM SP SQ - WA.ald
 * SS       - audio
 */
$sco_file = array();
require "cmd_alice35.php";
require "tbl_half2kata.php";
require "funcs-bmp.php";

function sco35_html( &$run )
{
	$run = false;
	global $gp_init, $gp_pc, $ajax_html;
	$ajax_html = "";
	ob_start();

/// CSS ///

	list($win_x,$win_y,$win_w,$win_h) = $gp_pc["WV"];

	$style  = "";
	//$style .= "margin-left:-{$win_x}px;";
	//$style .= "margin-top:-{$win_y}px;";
	$style .= "width:{$win_w}px;";
	$style .= "height:{$win_h}px;";
	echo "<input id='window_css' type='hidden' value='$style'>";
	///////////

	// border-width:1px;
	// border-style:dotted/solid;
	echo "<style>";
	$zs = $gp_pc["ZS"] - 2;
	echo "#select {";
		echo "background-color:" .sco35_zc2ps(4, "#000"). ";";
		echo "border:1px " .sco35_zc2ps(3, "#fff"). " solid;";
		echo "color:" .sco35_zc2ps(2, "#fff"). ";";
		echo "font-size:{$zs}px;";
	echo "}";
	$zm = $gp_pc["ZM"] - 2;
	echo ".text {";
		echo "background-color:" .sco35_zc2ps(6, "#000"). ";";
		//echo "border:1px " .sco35_zc2ps(5, "#fff"). " solid;";
		echo "font-size:{$zm}px;";
	echo "}";
	echo "</style>";

/// CSS ///

/// WINDOW ///

	if ( ! isset( $gp_pc["G0"] ) )
		$gp_pc["G0"] = array();
	if ( ! isset( $gp_pc["div"] ) )
		$gp_pc["div"] = array();

	foreach ( $gp_pc["G0"] as $k => $v )
	{
		$png = sco35_g0_path( $k, $v[4] );

		echo "<style>.cg_$k{background-image:url('$png');}</style>";
		echo "<img src='$png' class='sprites' mouse='{$v[0]},{$v[1]}'>";
	} // foreach ( $gp_pc["G0"] as $k => $v )
	///////////
	foreach ( $gp_pc["div"] as $div )
	{
		$mouse = "{$div['p'][0]},{$div['p'][1]}";
		$box = "{$div['p'][2]},{$div['p'][3]}";
		$class = "";
		if ( $div['t'] == "bg" )
		{
			$class = "cg_{$div['bg'][0]}";
			$box .= ",{$div['bg'][1]},{$div['bg'][2]}";
		}
		else
		if ( $div['t'] == "clr" )
		{
			$box .= ",{$div['clr']}";
		}
		echo "<div class='sprites div $class' mouse='$mouse' box='$box'></div>";
	}
	///////////
	$text = empty( $gp_pc["text"] );
	if ( ! $text )
		echo $gp_pc["text"];
	///////////
	$select = empty( $gp_pc["select"] );
	if ( ! $select )
	{
		$num = $gp_pc["B2"][0];
		$select_pos = $gp_pc["B1"][$num];

		echo "<ul id='select' class='sprites' mouse='{$select_pos[0]},{$select_pos[1]}'>";
		foreach ( $gp_pc["select"] as $k => $v )
		{
			echo "<li data='$k'>" .base64_decode( $v[1] ). "</li>";
		}
		echo "</ul>";
	}
	///////////
	/*
	if ( $text && $select )
	{
		$tw = (int)($win_w / 8);
		$th = (int)($win_h / 8);
		echo "<style>.tiled { width:{$tw}px;height:{$th}px; }</style>";
		for ( $x=0; $x < $win_w; $x += $tw )
			for ( $y=0; $y < $win_h; $y += $th )
				echo "<div class='sprites tiled' mouse='$x,$y'></div>";
	}
	*/

/// WINDOW ///

/// AUDIO ///
	$ogg = PATH_OGG_1S;
	if ( isset( $gp_pc["SS"] ) )
		$ogg = findfile( $gp_init["path_ogg"], $gp_pc["SS"], PATH_OGG_1S );
	echo "<input id='ogg' type='hidden' value='$ogg'>";

	$wave = PATH_OGG_1S;
	if ( isset( $gp_pc["SP"] ) )
	{
		$wave = findfile( $gp_init["path_wav"], $gp_pc["SP"], PATH_OGG_1S );
		unset( $gp_pc["SP"] );
	}
	echo "<input id='wave' type='hidden' value='$wave'>";


	$midi = PATH_OGG_1S;
	if ( isset( $gp_pc["SG"] ) && $gp_pc["SG"][1] )
		$midi = findfile( $gp_init["path_mid"], $gp_pc["SG"][0], PATH_OGG_1S );
	echo "<input id='midi' type='hidden' value='$midi'>";
/// AUDIO ///

	$ajax_html = ob_get_clean();
}

function sco35_box_clear( $box )
{
	global $gp_pc;
	if ( ! isset( $gp_pc["G0"] ) )
		$gp_pc["G0"] = array();
	if ( ! isset( $gp_pc["div"] ) )
		$gp_pc["div"] = array();
	foreach ( $gp_pc["G0"] as $k => $v )
	{
		if ( $v[0] < 0 || $v[1] < 0 )
			unset( $gp_pc["G0"][$k] );
		else
		if ( box_within($box, $v) )
			unset( $gp_pc["G0"][$k] );
	}

	foreach ( $gp_pc["div"] as $k => $v )
	{
		if ( $v['p'][0] < 0 || $v['p'][1] < 0 )
			unset( $gp_pc["div"][$k] );
		else
		if ( box_within($box, $v['p']) )
			unset( $gp_pc["div"][$k] );
	}

}

function sco35_ec_clear( $num )
{
	global $gp_pc;
	if ( $num == 0 )
		$box = $gp_pc["WV"];
	else
	{
		$es = $gp_pc["ES"][$num];
		array_shift($es);
		$box = $es;
	}
	sco35_box_clear( $box );
}

function sco35_text_add( $jp )
{
	global $gp_pc;

	$font = $gp_pc["ZM"];
	$num = $gp_pc["B4"][0];
	$text_pos = $gp_pc["B3"][$num];

	if ( $jp == "_NEXT_" )
	{
		$gp_pc["T"] = array($text_pos[0], $text_pos[1]);
		$gp_pc["text"] = "";
		return;
	}

	if ( $jp == "_CRLF_" )
	{
		$gp_pc["T"][0]  = $text_pos[0];
		$gp_pc["T"][1] += ($font + 2);
		return;
	}

	// skip hardcoded padding zeroes
	$zero = chr(0x82) . chr(0x4f);
	if ( $jp == $zero )
		return;

	$b1 = ord( $jp[0] );
	if ( $b1 & 0x80 )
		$len = (strlen($jp) / 2) * $font;
	else
		$len = strlen($jp) * ($font/2);

	$mouse = "{$gp_pc['T'][0]},{$gp_pc['T'][1]}";
	$style = "color:" .sco35_zc2ps(1, "#fff"). ";";

	$gp_pc["text"] .= "<span class='sprites text' mouse='$mouse' style='$style'>$jp</span>";
	$gp_pc["T"][0] += (int)$len;
}

function sco35_vp_div_add( $src )
{
	global $gp_pc;
	list($px,$py,$sx,$sy,$ot,$fl) = $src;
	list($n,$x0,$y0,$mx,$my,$ux,$uy) = $gp_pc["VC"];

	$box = array($x0,$y0,$sx*$ux,$sy*$uy);
	sco35_box_clear( $box );

	for ( $page=0; $page < $n; $page++ )
	{
		if ( ! isset($gp_pc["VR"][$page]) )
			continue;
		if ( ! $gp_pc["VV"][$page] )
			continue;

		$type = 0;
		while ( $type < 4 )
		{
			$type++;
			if ( ! isset( $gp_pc["VR"][$page][$type] ) )
				continue;

			$varno = $gp_pc["VR"][$page][$type];

			for ( $y=0; $y < $sy; $y++ )
			{
				for ( $x=0; $x < $sx; $x++ )
				{
					$dx = $x0 + ($x * $ux); // display box on screen
					$dy = $y0 + ($y * $uy);
					$p = array($dx,$dy,$ux,$uy);
					$key = "{$p[0]},{$p[1]},{$p[2]},{$p[3]}";

					$t1 = (($py + $y) * $mx) + ($px + $x);
					$val = $gp_pc["var"][$varno][$t1];
					if ( $val == 0 )
						continue;

					$cc = $gp_pc["VP"][$page][$val];
					if ( empty($cc) )
						continue;

					$des = array(
						't' => "bg",
						'p' => $p,
						"bg"  => $cc,
					);
					$gp_pc["div"][$key] = $des;
				} // for ( $x=0; $x < $sx; $x++ )
			} // for ( $y=0; $y < $sy; $y++ )

		} // while ( $type > 0 )

	} // for ( $page=0; $page < $n; $page++ )
}

function sco35_vp_g0( $src )
{
	global $gp_pc;
	list($pg,$px,$py,$nx,$ny,$s) = $src;
	list($n,$x0,$y0,$mx,$my,$ux,$uy) = $gp_pc["VC"];
	$vp = array();

	for ( $y=0; $y < $ny; $y++ )
	{
		for ( $x=0; $x < $nx; $x++ )
		{
			$x0 = $px + ($x * $ux);
			$y0 = $py + ($y * $uy);
			$box = array($x0,$y0,$ux,$uy);
			$g0 = sco35_g0_bg_find( $box );
			$vp[] = $g0;
		} // for ( $x=0; $x < $nx; $x++ )
	} // for ( $y=0; $y < $ny; $y++ )
	return $vp;
}

function sco35_cc_div_add( $type, $src )
{
	global $gp_pc;
	switch ( $type )
	{
		case "_CP_":
			list($x,$y,$c) = $src;
			//$pix = (($c & 0xf) << 4) | ($c >> 4);
			$pix = (($c << 4) + $c) & 0xf0;
			$clr = sprintf( "#%02x%02x%02x", $pix, $pix, $pix );
			$p = array($x-8,$y-8,16,16);
			$key = "{$p[0]},{$p[1]},{$p[2]},{$p[3]}";
			$des = array(
				't' => "clr",
				'p' => $p,
				"clr" => $clr,
			);
			$gp_pc["div"][$key] = $des;
			return;
		case "_CF_":
			list($x,$y,$w,$h,$c) = $src;
			$p = array($x,$y,$w,$h);
			sco35_box_clear( $p );
			return;
	}
	return;
/*
	if ( $type == "_CC_" )
	{
		$cc = sco35_g0_bg_find( $src );
		if ( $cc[0] == 0 )
			return;
		$p = array($src[4],$src[5],$src[2],$src[3]);
		$key = "{$p[0]},{$p[1]},{$p[2]},{$p[3]}";
		$des = array(
			't' => "bg",
			'p' => $p,
			"bg"  => $cc,
		);
		//sco35_box_clear( array($src[4],$src[5],$src[2],$src[3]) );
		$gp_pc["div"][$key] = $des;
		return;
	}
*/
}

function sco35_g0_bg_find( $src )
{
	global $gp_pc;
	$des = array(0,0,0);
	foreach ( $gp_pc["G0"] as $k => $v )
	{
		if ( $k == 0 )
			continue;
		if ( box_within($v,$src) )
		{
			$des = array(
				$k,
				$v[0] - $src[0],
				$v[1] - $src[1],
			);
			return $des;
		}
	}
	return $des;
}

function sco35_g0_path( $num, $alpha )
{
	global $gp_init;
	$png = sprintf( $gp_init["path_img"], ($num >> 8), $num );
	if ( 0 > $alpha ) // -1 , image with solid bg
	{
		if ( file_exists( ROOT."/$png" ) )
			return $png;
		$img = $png;
	}
	else // 0-255 , sprites with transparent bg
	{
		$spr = sprintf( $gp_init["path_spr"], ($num >> 8), $num , $alpha );
		if ( file_exists( ROOT."/$spr" ) )
			return $spr;
		$img = $spr;
	}

	$clut = str_replace(".png", ".clut", $png);
	clut2bmp( ROOT."/$clut" , ROOT."/$img" , $alpha );
	//unlink( ROOT."/$clut" );

	return $img;
}

function sco35_g0_add( $num , $alpha )
{
	global $gp_pc, $gp_img_meta;
	if ( $num == 0 )
		return;

	// PC = palette read/decompress/cd
	if ( ! isset( $gp_pc["PPC"]) )
		$gp_pc["PPC"] = 1;
	if ( ($gp_pc["PPC"] & 1) == 0 )
		return;

	$img_pos = array(0,0,0,0,$alpha);
	// move it to the end of the queue
	// to display in front
	if ( isset( $gp_pc["G0"][$num] ) )
	{
		//$img_pos = $gp_pc["G0"][$num];
		unset( $gp_pc["G0"][$num] );
	}
	// use meta data from image file itself
	if ( isset( $gp_img_meta[$num] ) )
	{
		$img_pos = $gp_img_meta[$num];
		$img_pos[4] = $alpha;
	}

	// affected by J command beforehand
	sco35_g0_j0( $img_pos , 0 , true  , true  );
	sco35_g0_j0( $img_pos , 1 , false , true  );
	sco35_g0_j0( $img_pos , 2 , true  , false );
	sco35_g0_j0( $img_pos , 3 , false , false );

	// keep bg with sprites
	if ( $alpha < 0 )
		sco35_box_clear( $img_pos );
	$gp_pc["G0"][$num] = $img_pos;
}

function sco35_g0_j0( &$img_pos , $j0 , $rel , $rm )
{
	global $gp_pc;
	if ( isset( $gp_pc["J"][$j0] ) )
	{
		list($x,$y) = $gp_pc["J"][$j0];
		if ( $rel )
		{
			$img_pos[0] += $x;
			$img_pos[1] += $y;
		}
		else
		{
			$img_pos[0] = $x;
			$img_pos[1] = $y;
		}
		if ( $rm )
			unset( $gp_pc["J"][$j0] );
	}
}

function sco35_ps( $num )
{
	global $gp_pc;
	if ( isset( $gp_pc["PS"][$num] ) )
	{
		$ps = $gp_pc["PS"][$num];
		$clr = sprintf("#%02x%02x%02x", $ps[0], $ps[1], $ps[2] );
		return $clr;
	}
	return "";
}

function sco35_zc2ps( $num, $clr )
{
	global $gp_pc;
	if ( isset( $gp_pc["ZC"][$num] ) )
	{
		$ps = sco35_ps( $gp_pc["ZC"][$num] );
		return ( empty($ps) ) ? $clr : $ps;
	}
	return $clr;
}

function sco35_n_math( $opr , $var1 , $num , $count , $e = 0 )
{
	global $gp_pc;
	if ( is_array( $gp_pc["var"][$var1] ) )
	{
		for ( $i=0; $i < $count; $i++ )
		{
			if ( ! isset( $gp_pc["var"][$var1][$e+$i] ) )
				$gp_pc["var"][$var1][$e+$i] = 0;
			$n = var_math( $opr, $gp_pc["var"][$var1][$e+$i], $num );
			$gp_pc["var"][$var1][$e+$i] = $n;
		}
	}
	else
	{
		for ( $i=0; $i < $count; $i++ )
		{
			if ( ! isset( $gp_pc["var"][$var1+$e+$i] ) )
				$gp_pc["var"][$var1+$e+$i] = 0;
			$n = var_math( $opr, $gp_pc["var"][$var1+$e+$i], $num );
			$gp_pc["var"][$var1+$e+$i] = $n;
		}
	}
}

function sco35_loop_inf( &$file, &$st )
{
	$bak = $st;
	if ( $file[$st+7] == '>' )
	{
		$bak += 8;
		$loc = str2int($file, $bak, 4);
		if ( $loc == $st )
		{
			trace("infinite loop");
			sco35_text_add( "_NEXT_" );
			sco35_text_add( "INFINITE LOOP" );
			return true;
		}
		return false;
	}
	return false;
}

function sco35_IK_bnez( &$file, &$st )
{
	if (
		$file[$st+3] == '{'
		&& $file[$st+12] == 'I'
		&& $file[$st+13] == 'K'
		&& $file[$st+15] == '>'
	){
		$bak = $st + 8;
		$end = str2int( $file, $bak, 4 );
		trace("skip IK <@RND!=0 IK>");
		$st = $end;
		return true;
	}
	return false;
}

function sco35_sjis( &$file, &$st )
{
	global $halftbl;
	$str = "";
	while(1)
	{
		$b1 = ord( $file[$st] );
		if ( $b1 >= 0xe0 )
		{
			$str .= $file[$st+0];
			$str .= $file[$st+1];
			$st += 2;
		}
		else
		if ( $b1 >= 0xa0 )
		{
			$full = $halftbl[$b1];
			$str .= chr( $full >> 8   );
			$str .= chr( $full  & 0xff);
			$st++;
		}
		else
		if ( $b1 >= 0x80 )
		{
			$str .= $file[$st+0];
			$str .= $file[$st+1];
			$st += 2;
		}
		else
		if ( $b1 == 0x20 ) // space
		{
			$str .= chr(0x81);
			$str .= chr(0x40);
			$st++;
		}
		else
			return $str;
	} // while(1)
}

function sco35_varno_tbl( &$file, &$st )
{
	$b1 = ord( $file[$st+0] );
	$b2 = ord( $file[$st+1] );
	$st += 2;

	$v = ($b1 << 8) + $b2;
	$e = sco35_calli( $file, $st );
	return array($v,$e);
}

function sco35_varno( &$file, &$st )
{
	// 80    - bf    =  00 -   3f
	// c0 40 - c0 ff =  40 -   ff
	// c1 00 - ff ff = 100 - 3fff
	// c0 01 vv vv calli = vvvv[ calli ]
	$b1 = ord( $file[$st] );
	$n1 = $b1 & 0x3f;
	if ( $b1 & 0x40 )
	{
		$b2 = ord( $file[$st+1] );
		$st += 2;
		if ( 0xc0 == $b1 )
		{
			if ( $b2 < 0x40 )
				return $b2 * -1;
			else
				return $b2;
		}
		else
			return ($n1 << 8) + $b2;
	}
	else
	{
		$st++;
		return $n1;
	}
}

function sco35_calli_opr( $opr, &$ret )
{
	$t1 = array_shift($ret);
	$t2 = array_shift($ret);
	$r  = 0;
	switch( $opr )
	{
		case 0x7e:  $r = ( $t2 != $t1 ); break;
		case 0x7d:  $r = ( $t2 >  $t1 ); break;
		case 0x7c:  $r = ( $t2 <  $t1 ); break;
		case 0x7b:  $r = ( $t2 == $t1 ); break;
		case 0x7a:  $r = ( $t2 -  $t1 ); break;
		case 0x79:  $r = ( $t2 +  $t1 ); break;
		case 0x78:  $r = ( $t2 /  $t1 ); break;
		case 0x77:  $r = ( $t2 *  $t1 ); break;
		case 0x76:  $r = ( $t2 ^  $t1 ); break;
		case 0x75:  $r = ( $t2 |  $t1 ); break;
		case 0x74:  $r = ( $t2 &  $t1 ); break;
	}
	array_unshift($ret, (int)$r);
}

function sco35_calli( &$file, &$st )
{
	global $gp_pc;
	$ret = array();
	while (1)
	{
		$b1 = ord( $file[$st] );

		if ( $b1 & 0x80 )
		{
			$varno = sco35_varno( $file, $st );
			if ( $varno < 0 )
			{
				list($v,$e) = sco35_varno_tbl( $file, $st );

				if ( ! isset( $gp_pc["var"][$v] ) )
					$gp_pc["var"][$v] = array();
				if ( is_array( $gp_pc["var"][$v] ) )
				{
					if ( ! isset( $gp_pc["var"][$v][$e] ) )
						$gp_pc["var"][$v][$e] = 0;
					$var = $gp_pc["var"][$v][$e];
				}
				else
				{
					if ( ! isset( $gp_pc["var"][$v+$e] ) )
						$gp_pc["var"][$v+$e] = 0;
					$var = $gp_pc["var"][$v+$e];
				}

			}
			else
			{
				if ( ! isset( $gp_pc["var"][$varno] ) )
					$gp_pc["var"][$varno] = 0;
				$var = $gp_pc["var"][$varno];
			}
			array_unshift($ret, $var);
			continue;
		}

		$st++;

		if ( $b1 == 0x7f )
			return $ret[0];
		else
		if ( $b1 >= 0x74 )
		{
			sco35_calli_opr($b1, $ret);
			continue;
		}
		else
		{
			// 40    - 7f    = 00 -   3f
			// 00 40 - 3f ff = 40 - 3fff
			if ( $b1 & 0x40 )
				array_unshift($ret, $b1 & 0x3f);
			else
			{
				$b2 = ord( $file[$st] );
				$st++;
				array_unshift($ret, (($b1 & 0x3f) * 0x100) + $b2);
			}
		}
	} // while (1)
}

function sco35_load_data($num , $varno , $len)
{
	global $gp_init;
	$dat = sprintf( $gp_init["path_dat"], ($num >> 8), $num );
	$file = file_get_contents( ROOT . "/$dat" );
	if ( empty($file) )  return;

	$data = array();
	$st = 0;
	while ( $len > 0 )
	{
		$b1 = ord( $file[$st+0] );
		$b2 = ord( $file[$st+1] );
		$data[] = ($b2 << 8) + $b1;
		$len--;
		$st += 2;
	}

	global $gp_pc;
	$gp_pc["var"][$varno] = $data;
}

function sco35_load_sco( $id )
{
	global $sco_file, $gp_init;
	if ( ! isset( $sco_file[$id] ) )
	{
		$sco = sprintf( $gp_init["path_sco"], ($id >> 8), $id );
		$sco_file[$id] = file_get_contents( ROOT . "/$sco" );
		trace("load $sco");
	}
}

function exec_alice35( $id, &$st, &$run )
{
	global $sco_file;
	if ( $id == 0 )  $id = 1;
	if ( $st == 0 )  $st = 0x20;
	sco35_load_sco( $id );
	trace("= sco_%d_%x : ", $id, $st);

	$now = $st;
	$select = false;
	sco35_cmd( $id, $st, $run, $select);
	if ( $st == $now )
		sco35_html( $run );
}
