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
require "common.inc";
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
	return;
}
function cb_key2( &$ret, $line )
{
	$sep = explode(',', $line);
	$k1 = array_shift($sep);
	$k2 = array_shift($sep);
	$v = implode(',', $sep);
	$ret[$k1][$k2] = $v;
	return;
}
function cb_key1( &$ret, $line )
{
	$sep = strpos($line, ',');
	$k = substr($line, 0, $sep);
	$v = substr($line, $sep+1);
	$ret[$k] = $v;
	return;
}

function loadfile( $fname , $callback )
{
	$ret = array();
	if ( ! file_exists($fname) )
		return $ret;
	foreach ( file($fname, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
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
function code2inst( &$code, &$st, &$inst )
{
	$c = sint16($code, $st);

	if ( isset( $inst[$c] ) )
		$c = $inst[$c];
	switch ( $c )
	{
		case 184:
		case 185:
		case 192:
		case 201:
		case 202:
		case 204:
		case 205:
			$n1 = sint32($code, $st);
			$n2 = sint32($code, $st);
			$n3 = sint32($code, $st);
			return array($c,$n1,$n2,$n3);
		case "CALLHLL":
		case "SH_LOCALCREATE":
		case "SH_LOCALASSIGN":
		case 178:
		case 181:
		case 188:
		case 189:
		case 191:
		case 199:
		case 206:
		case 208:
		case 225:
		case 230:
			$n1 = sint32($code, $st);
			$n2 = sint32($code, $st);
			return array($c,$n1,$n2);
		case "PUSH":
		case "F_PUSH":
		case "S_PUSH":
		case "CALLFUNC":
		case "CALLMETHOD":
		case "CALLSYS":
		case "SH_GLOBALREF":
		case "SH_LOCALREF":
		case "SH_STRUCTREF":
		case "SH_LOCALDELETE":
		case "SH_LOCALINC":
		case "SH_LOCALDEC":
		case "SR_REF":
		case "SR_REF2":
		case "SWITCH":
		case "STRSWITCH":
		case "FUNC":
		case "ENDFUNC":
		case "IFZ":
		case "IFNZ":
		case "JUMP":
		case "MSG":
		case "EOF":
		case 179:
		case 180:
		case 182:
		case 220:
		case 223:
		case 224:
		case 228:
		case 229:
		case 231:
		case 234:
			$n1 = sint32($code, $st);
			return array($c,$n1);
		default:
			return array($c);
	}
}
//////////////////////////////
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
//////////////////////////////
$code = file_get_contents("CODE");

$ed = strlen($code);
$st = 0;
$buf = "";
$lin = "";
while ( $st < $ed )
{
	$bak = $st;
	$inst = code2inst($code, $st, $gp_code["ins"]);

	$lin .= "$bak\n";
	$log = "";
	//$log = sprintf("%d,", $bak);
	switch ( $inst[0] )
	{
		case "FUNC":
		case "ENDFUNC":
		case "CALLFUNC":
		case "CALLMETHOD":
			$v = loadvar( $gp_code["FUNC"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		case "CALLHLL":
			$k1 = $inst[1];
			$k2 = $inst[2];
			$v = $gp_code["HLL"][$k1][$k2];
			$log .= "{$inst[0]},$v\n";
			break;
		case "CALLSYS":
			$v = loadvar( $gp_code["sys"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		case "S_PUSH":
			$v = loadvar( $gp_code["STR"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		case "F_PUSH":
			$v = float32( $inst[1] );
			$log .= "{$inst[0]},$v\n";
			break;
		case "SWITCH":
			$v = loadvar( $gp_code["SWI"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		case "MSG":
			$v = loadvar( $gp_code["MSG"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		case "EOF":
			$v = loadvar( $gp_code["FNAM"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		case "SH_GLOBALREF":
			$v = loadvar( $gp_code["GLOB"] , $inst[1]);
			$log .= "{$inst[0]},$v\n";
			break;
		default:
			if ( isset($inst[1]) )
				$log .= implode(',', $inst) . "\n";
			else
				$log .= "{$inst[0]}\n";
			break;
	}
	echo $log;
	$buf .= $log;
}
file_put_contents("CODE.dec", $buf);
file_put_contents("CODE.lin", $lin);
