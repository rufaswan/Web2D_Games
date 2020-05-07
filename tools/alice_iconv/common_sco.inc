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
////////////////////////////////////////
function sco_varno( &$file, &$st )
{
	$b1 = ord( $file[$st] );
	$st++;

	// same as &var , return array()
	if ( $b1 & 0x80 )
	{
		// 80    - bf    =  00 -   3f
		// c0 40 - c0 ff =  40 -   ff
		// c1 00 - ff ff = 100 - 3fff
		// c0 01 vv vv calli = vvvv[ calli ]
		$n1 = $b1 & 0x3f;
		if ( $b1 & 0x40 )
		{
			$b2 = ord( $file[$st] );
			$st++;
			if ( $b1 == 0xc0 )
			{
				if ( $b2 > 0x3f )
					return "|$b2|0|";

				if ( $b2 == 1 )
				{
					$b3 = ord( $file[$st+0] );
					$b4 = ord( $file[$st+1] );
						$st += 2;
					$v = ($b3 << 8) + $b4;
					$e = sco_calli( $file, $st );
					return "|$v|$e|";
				}
				else
				{
					$b2 *= -1;
					return "|$b2|0|";
				}
			}
			else
			{
				$b2 = ($n1 << 8) + $b2;
				return "|$b2|0|";
			}
		}
		else
			return "|$n1|0|";
	}
	// same as var , return int
	else
	{
		// 40    - 7f    = 00 -   3f
		// 00 40 - 3f ff = 40 - 3fff
		$n1 = $b1 & 0x3f;
		if ( $b1 & 0x40 )
			return $n1;
		else
		{
			$b2 = ord( $file[$st] );
			$st++;
			return ($n1 * 0x100) + $b2;
		}
	}
	return 0;
}

function sco_calli_opr( $opr, &$ret )
{
	$t1 = array_shift($ret);
	$t2 = array_shift($ret);

	$oprlist = array('&','|','^','*','/','+','-','==','<','>','!=');
	$opr -= 0x74;
	$r = "( $t2 {$oprlist[$opr]} $t1 )";
	array_unshift($ret, $r);
}

function sco_calli( &$file, &$st )
{
	$ret = array();
	while (1)
	{
		$b1 = ord( $file[$st] );

		if ( $b1 & 0x80 )
		{
			$var = sco_varno( $file, $st );
			array_unshift($ret, $var);
			continue;
		}

		if ( $b1 == 0x7f )
		{
			$st++;
			return $ret[0];
		}
		else
		if ( $b1 >= 0x74 )
		{
			$st++;
			sco_calli_opr($b1, $ret);
			continue;
		}
		else
		{
			$var = sco_varno( $file, $st );
			array_unshift($ret, $var);
			continue;
		}
	} // while (1)
	return 0;
}

function sco_var_arg( $cnt, &$file, &$st )
{
	$arg = array();
	while ( $cnt > 0 )
	{
		$arg[] = sco_calli($file, $st);
		$cnt--;
	}
	return $arg;
}
////////////////////////////////////////
function sco_ascii( &$file, &$st, $sep )
{
	$len = 0;
	while ( $file[$st+$len] != $sep && $file[$st+$len] != ZERO )
		$len++;
	$str = substr($file, $st, $len);
	$st += $len;
	$st++; // skip $sep

	$str = sjis2utf8($str);
	return $str;
}

function sco_sjis( &$file, &$st )
{
	$str = sco_getsjis( $file, $st );
	$str = sjis2utf8($str);
	return $str;
}

function sco_getsjis( &$file, &$st, $end = BIT24 )
{
	$len = 0;
	while( ($st+$len) < $end )
	{
		$b1 = ord( $file[$st+$len] );
		if ( $b1 >= 0xe0 )
			$len += 2;
		else
		if ( $b1 >= 0xa0 )
			$len++;
		else
		if ( $b1 >= 0x80 )
			$len += 2;
		else
		if ( $b1 == 0x20 )
			$len++;
		else
			break;
	}
	$str = substr($file, $st, $len);
	$st += $len;
	return $str;
}

function add_str( &$sco_str, $str )
{
	$id = count($sco_str);
	foreach ( $sco_str as $k => $v )
	{
		if ( $str == $v )
			return $k;
	}
	$sco_str[$id] = $str;
	return $id;
}
////////////////////////////////////////
function sco_cmd_arg( &$file, &$st, $ret, &$sco_msg )
{
	$arg = array();
	foreach ( $ret as $r )
	{
		switch ($r)
		{
			case "400":
				$arg[] = ord( $file[$st] );
				$st++;
				continue;
			case "401":
				$arg[] = sco_calli($file, $st);
				continue;
			case "402":
				$arg[] = sco_calli($file, $st);
				continue;
			case "403":
				$v = sco_ascii( $file, $st, ':' );
				$k = add_str($sco_msg, $v);
				$arg[] = "@MSG=$k";
				continue;
			case "404":  continue;
			case "405":  continue;
			case "406":  continue;
			case "int16":
				$arg[] = sint16($file, $st);
				continue;
			case "int32":
				$arg[] = sint32($file, $st);
				continue;
			default:
				$arg[] = ord( $file[$st] );
				$st++;
				continue;
		}
	}
	return $arg;
}

