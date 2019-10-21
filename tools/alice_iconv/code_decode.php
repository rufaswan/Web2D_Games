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
define("ROOT", dirname(__FILE__) );
////////////////////////////////////////
function float32( $int )
{
	// IEEE 754
	$sig = ($int >> 31) & 1;
	$exp = ($int >> 23) & 0xff;
	$man = ($int >>  0) & 0x7fffff;

	$neg = ( $sig ) ? -1 : 1;
	$norm = 1;
	if ( $exp == 0 )
	{
		if ( $man != 0 ) // denormalized
			$norm = 0;
		if ( $man == 0 ) // exact zero
			return 0;
	}
	if ( $exp == 255 )
	{
		//if ( $man != 0 ) // nan/not-a-number
		//if ( $man == 0 ) // infinity
			return 0;
	}

	$exp -= 0x7f;
	$man = $norm + ($man / 0x800000);
	$man = $man * (2 ** $exp);
	return $man * $neg;
}
////////////////////////////////////////
function cb_inst( &$ret, $line )
{
	$sep = explode(',', $line);
	$k = $sep[0];
	$v = $sep[1];
	//if ( isset( $sep[2] ) )
	if ( empty($v) )
		$v = $sep[2];
	$ret[$k] = $v;
}
function cb_key2( &$ret, $line )
{
	$sep = explode(',', $line);
	$k1 = array_shift($sep);
	$k2 = array_shift($sep);
	$v = implode(',', $sep);
	$ret[$k1][$k2] = $v;
}
function cb_key1( &$ret, $line )
{
	$sep = strpos($line, ',');
	$k = substr($line, 0, $sep);
	$v = substr($line, $sep+1);
	$ret[$k] = $v;
}

function loadfile( $fname , $callback )
{
	$ret = array();
	if ( ! file_exists($fname) )
		return $ret;
	foreach ( file($fname) as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;
		if ( $line[0] == '#' )
			continue;
		$callback($ret, $line);
	}
	return $ret;
}
function loadvar( &$list, $id )
{
	if ( isset( $list[$id] ) )
		return $list[$id];
	else
		return $id;
}
////////////////////////////////////////
$gp_code = array();
$gp_code["sys"] = loadfile(ROOT."/code_sys.txt" , "cb_key1");
$gp_code["var"] = loadfile(ROOT."/code_var.txt" , "cb_key1");
$gp_code["SWI"] = loadfile("SWI0" , "cb_key1");
$gp_code["STR"] = loadfile("STR0" , "cb_key1");
$gp_code["MSG"] = loadfile("MSG0" , "cb_key1"); // v6-1+ as MSG1
$gp_code["FUNC"] = loadfile("FUNC" , "cb_key1");
$gp_code["GLOB"] = loadfile("GLOB" , "cb_key1");
$gp_code["STRT"] = loadfile("STRT" , "cb_key1");
$gp_code["OBJG"] = loadfile("OBJG" , "cb_key1"); // v5+
$gp_code["DELG"] = loadfile("DELG" , "cb_key1"); // v6-0+
$gp_code["FNCT"] = loadfile("FNCT" , "cb_key1"); // v6-1+ removed
$gp_code["ENUM"] = loadfile("ENUM" , "cb_key1"); // v14+
$gp_code["FNAM"] = loadfile("FNAM" , "cb_key1"); // v14+ removed
$gp_code["GSET"] = loadfile("GSET" , "cb_key1"); // v14+ removed

$gp_code["HLL"] = loadfile("HLL0" , "cb_key2");
$gp_code["ins"] = loadfile(ROOT . "/code_inst.txt" , "cb_inst");

$code = file("CODE");
$ed = count($code);
while ( $ed > 0 )
{
	$ed--;
	$line = explode(',', trim( $code[$ed] ));
	$line[0] = hexdec( $line[0] );

	if ( isset( $gp_code["ins"][ $line[1] ] ) )
		$line[1] = $gp_code["ins"][ $line[1] ];

	switch ( $line[1] )
	{
		case "CALLHLL":
			$k1 = $line[2];
			$k2 = $line[3];  unset( $line[3] );
			$line[2] = $gp_code["HLL"][$k1][$k2];
			break;
		case "MSG":
			$line[2] = loadvar( $gp_code["MSG"] , $line[2]);
			break;
		case "S_PUSH":
			$line[2] = loadvar( $gp_code["STR"] , $line[2]);
			break;
		case "EOF":
			$line[2] = loadvar( $gp_code["FNAM"] , $line[2]);
			break;
		case "SH_GLOBALREF":
			$line[2] = loadvar( $gp_code["GLOB"] , $line[2]);
			break;
		case "CALLSYS":
			$line[2] = loadvar( $gp_code["sys"] , $line[2]);
			break;
		case "FUNC":
		case "ENDFUNC":
		case "CALLFUNC":
		case "CALLMETHOD":
			$line[2] = loadvar( $gp_code["FUNC"] , $line[2]);
			break;
		case "F_PUSH":
			$line[2] = float32( $line[2] );
			break;
	}

	$line = implode(',', $line);
	$code[$ed] = $line;
}
$code = implode("\n", $code);
file_put_contents("CODE.dec", $code);
