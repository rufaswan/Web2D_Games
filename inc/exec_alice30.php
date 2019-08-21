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
/*
 * 00  2  file size
 * 02  ...  bytecode
 *
 * Tested OK
 * Ambivalenz 1.03w  -  98 (iconv 98-ca6)
 * Ayumi-chan 1.11w  -  89 (iconv 4-1b,4-86)
 * FunnyBee   1.03wb -  78 (iconv *lot*)
 * Mugen      1.00   - 126 OK
 * Rance 4.1   .100  -  47 (iconv 2-*lot*,4-*lot*)
 * Rance 4.2  1.01   -  49 (iconv 2-*lot*,4-*lot*,7-12b,10-14c7,22-*lot*)
 * Yakata 3          -  99 OK
 */
$sco_file = array();
require "cmd_alice30.php";
require "tbl_half2kata.php";
require "funcs-bmp.php";

function sco30_html( &$run )
{
	$run = false;
	global $gp_init, $gp_pc, $ajax_html;
	$ajax_html = "";
	ob_start();

/// CSS ///

/// CSS ///

/// WINDOW ///

/// WINDOW ///

/// AUDIO ///

/// AUDIO ///

	$ajax_html = ob_get_clean();
}

function sco30_text_add( $jp )
{
	global $gp_pc;
/*
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
*/
}

function sco30_sjis( &$file, &$st )
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

function sco30_varno_tbl( &$file, &$st )
{
	$b1 = ord( $file[$st+0] );
	$b2 = ord( $file[$st+1] );
	$st += 2;

	$v = ($b1 << 8) + $b2;
	$e = sco30_calli( $file, $st );
	return array($v,$e);
}

function sco30_varno( &$file, &$st )
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

function sco30_calli_opr( $opr, &$ret )
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

function sco30_calli( &$file, &$st )
{
	global $gp_pc;
	$ret = array();
	while (1)
	{
		$b1 = ord( $file[$st] );

		if ( $b1 & 0x80 )
		{
			$varno = sco30_varno( $file, $st );
			if ( $varno < 0 )
			{
				list($v,$e) = sco30_varno_tbl( $file, $st );

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
			sco30_calli_opr($b1, $ret);
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

function sco30_load_sco( $id )
{
	global $sco_file, $gp_init;
	if ( ! isset( $sco_file[$id] ) )
	{
		$sco = sprintf( $gp_init["path_sco"], $id );
		$sco_file[$id] = file_get_contents( ROOT . "/$sco" );
		trace("load $sco");
	}
}

function exec_alice30( $id, &$st, &$run )
{
	global $sco_file;
	if ( $id == 0 )  $id = 1;
	if ( $st == 0 )  $st = 2;
	sco30_load_sco( $id );
	trace("= sco_%d_%x : ", $id, $st);

	$now = $st;
	$select = false;
	sco30_cmd( $id, $st, $run, $select );
	if ( $st == $now )
		sco30_html( $run );
}
