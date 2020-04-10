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

function sco_cmd( $cmd, $arg )
{
	global $gp_pc, $gp_input, $gp_key;
	global $gp_run, $gp_sel, $gp_data;
	trace("%s , $cmd , %s", $gp_pc["pc"][1]+1, json_encode($arg));

	switch ( $cmd )
	{
		case "B_1":
		case "B_3":
		case "DC":
		case "ES":
		case "VV":
		case "VP":
		case "ZC":
			$id = array_shift($arg);
			$gp_pc[$cmd][$id] = $arg;
			return;
		case "MS":
			list($id,$str) = $arg;
			$gp_pc['X'][$id] = $str;
			return;
		case "PS":
			$id = array_shift($arg);
			$clr = sprintf("#%02x%02x%02x", $arg[0], $arg[1], $arg[2]);
			$gp_pc[$cmd][$id] = $clr;
			return;
		//////////////////////////////
		case 'A':
			if ( empty($gp_input) )
				$gp_run = false;
			else
			{
				sco_add_text("_NEXT_");
				$gp_input = array();
			}
			return;
		case 'R':
			sco_add_text("_CRLF_");
			return;
		case 'H':
		case "HH":
			list($fig,$num) = $arg;
			$str = sprintf("%0{$fig}d ", $num);
			sco_add_text($str);
			return;
		case "MSG":
			sco_add_text($arg[0]);
			return;
		case 'X':
			$str = $gp_pc['X'][ $arg[0] ];
			sco_add_text($str);
			return;
		case "ZZ_0":
			$gp_pc["var"][0] = $arg[0];
			$gp_run = false;
			sco_add_text("_NEXT_");
			sco_add_text("SYSTEM 3.5 END");
			return;
		//////////////////////////////
		case "G_0":
			sco_add_img( $arg[0], -1 );
			return;
		case "G_1":
			sco_add_img( $arg[0], $arg[1] );
			return;
		case "CB":
			$clr = array_pop($arg);
			sco_add_div( "border" , $arg , $clr );
			return;
		case "CP":
			$clr = array_pop($arg);
			sco_add_div( "paint" , $arg , $clr );
			return;
		case "CC":
			return;
		case "CF":
			return;
		case "EC":
			if ( $arg[0] == 0 )
				$es = $gp_pc["WV"];
			else
			{
				$es = $gp_pc["ES"][ $arg[0] ];
				array_shift($es);
			}
			sco_add_div( "clear" , $es , 0 );
			return;
		//////////////////////////////
		case "SG_0":
		case "SG_1":
			$gp_pc["bgm"] = array("midi", $arg[0]);
			return;
		case "SS":
			$gp_pc["bgm"] = array("bgm", $arg[0]);
			return;
		//////////////////////////////
		case 'Y':
			switch ( $arg[0] )
			{
				case 1:
					sco_add_text("_NEXT_");
					return;
				case 2:
					for ( $i=0; $i < 21; $i++ )
						$gp_pc["var"][$i] = 0;
					return;
				case 3:
					$gp_pc["var"][0] = 0;
					return;
				case 4:
					if ( $arg[1] == 0 )
						$gp_pc["var"][0] = 0;
					else
					{
						$rand = rand();
						$gp_pc["var"][0] = ($rand % $arg[1]) + 1;
					}
					return;
			}
			return;
		case "US": // stack push
			for ( $i=0; $i < 21; $i++ )
				array_unshift( $gp_pc["stack"] , $gp_pc["var"][$i] );
			return;
		case "UG": // stack pop
			$i = 21;
			while ( $i )
			{
				$i--;
				$gp_pc["var"][$i] = array_shift( $gp_pc["stack"] );
			}
			return;
		//////////////////////////////
		case "jump":
			list($t1,$t2) = $arg;
			if ( $t2 == 0 )
				$t1++;
			$gp_pc["pc"][1] = sco_find( "$t1,$t2" );
			return;
		case "call":
		case '~':
			if ( $arg[0] == 0 )
			{
				$gp_pc["return"] = $arg[1];
				$gp_pc["pc"] = array_shift( $gp_pc["jal"] );
				$gp_pc["pc"][1]++;
				return;
			}
			if ( $arg[0] == -1 )
			{
				$arg[1] = $gp_pc["return"];
				$gp_pc["return"] = 0;
				return;
			}

			list($t1,$t2) = $arg;

			$patch = "sco_{$t1}_{$t2}";
			if ( function_exists($patch) )
				return $patch();

			array_unshift( $gp_pc["jal"], $gp_pc["pc"] );
			$gp_pc["pc"][1] = sco_find( "$t1,$t2" );
			return;
		//////////////////////////////
		case "sel_st":
			$gp_sel = array($arg[0], "");
			return;
		case "sel_ed":
			trace("add_sel %s = %d", $gp_sel[1], $gp_sel[0]);
			$gp_pc["select"][] = $gp_sel;
			$gp_sel = array();
			return;
		case ']':
			if ( isset( $gp_input["select"] ) )
			{
				$sel = $gp_input["select"];
				if ( isset( $gp_pc["select"][$sel] ) )
				{
					trace("select = $sel");
					$gp_pc["pc"][1] = $gp_pc["select"][$sel][0];
					$gp_pc["select"] = array();
					$gp_input = array();
					sco_add_text("_NEXT_");
					return;
				}
			}
			$gp_run = false;
			return;
		//////////////////////////////
		case "input_set":
			if ( empty($gp_input) )
			{
				$gp_run = false;
				return;
			}
			if ( isset( $gp_input["mouse"] ) && ! isset( $gp_input["key"] ) )
				$gp_input["key"] = $gp_key["enter"];

			if ( isset( $gp_input["key"] ) && ! isset( $gp_input["mouse"] ) )
				$gp_input["mouse"] = array(-1,-1);
			return;
		case "input_clear":
			$gp_input = array();
			return;
		case "IK_0":
		case "IK_1":
		case "IK_2":
		case "IK_3":
		case "IK_4":
		case "IK_5":
		case "IK_6":
			$gp_pc["var"][0] = $gp_input["key"];
			return;
		case "IM":
			$arg[0] = $gp_input["mouse"][0];
			$arg[1] = $gp_input["mouse"][1];
			return;
		//////////////////////////////
		case '#':
			$sco = sco_file("disk", $arg[0], "");
			$gp_data = array();
			$gp_data[0] = file_get_contents( ROOT . "/$sco" );
			$gp_data[1] = $arg[1];
			return;
		case "F_1":
			$num = $arg[0];
			$st = $gp_data[1] + ($arg[1] * 2);
			$len = 0;
			while ( $gp_data[0][$st+$len] != ZERO )
				$len++;
			$str = substr($gp_data, $st, $len);
			$str = utf8_conv( CHARSET, sjistxt($str) );
			$gp_pc['X'][$num] = $str;
			$gp_data[1] += ($len + 1); // skip 0
			return;
		case "F_2":
		case "F_3":
			$st = $gp_data[1] + ($arg[1] * 2);
			$int = str2int($gp_data, $st, 2);
			$arg[0] = $int;
			if ( $cmd == "F_2" )
				$gp_data[1] += 2;
			return;
		//////////////////////////////
		default:
			$gp_pc[$cmd] = $arg;
			return;
	} // switch ( $cmd )

	return;
}
