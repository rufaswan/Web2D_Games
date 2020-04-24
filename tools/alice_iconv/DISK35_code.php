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
require "common_sco.inc";

define("SCO_CMD", ROOT . "/sco_cmd.inc");
define("SEP", chr(254));

$sco_func = array();
$sco_code = array();

$sco_file = array();
$sco_cmd = array();
//////////////////////////////
function ain_str8b( &$file, &$st )
{
	$str = "";
	$st++;
	while ( $file[$st] != ZERO )
	{
		$b   = ord( $file[$st] );
		$b1  = (($b >> 4) | ($b << 4)) & BIT8;
		$str .= chr($b1);
		$st++;
	}
	$st++; // skip 0

	$str = utf8_conv( CHARSET, sjistxt($str) );
	if ( $str == "" )
		$str = "iconv() error";
	$id = add_str($str);
	return "@MSG=$id";
}

function ain_hel0( &$file, &$st, $key )
{
	global $ain_hel0;
	if ( ! isset($ain_hel0[$key]) )
		return array();

	//sco,pos,dll,func,argc
	$hel = $ain_hel0[$key];
	$arg = array($hel[2], $hel[3]);

	$cnt = $hel[4];
	$arg += sco_var_arg($cnt, $file, $st);
	return $arg;
}

function ain_init()
{
	global $ain_hel0;
	if ( empty($ain_hel0) && file_exists("AINI_HEL0") )
	{
		foreach ( file("AINI_HEL0", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line )
		{
			$dll = explode(',', $line);
			$k = "{$dll[0]},{$dll[1]}";
			$ain_hel0[ $k ] = $dll;
		}
	}
	return;
}

////////////////////////////////////////
function sco_table( &$file, $id, $pos, $cmd, $arg )
{
	$off = $arg[0] + ($arg[1] * 4) - 4;
	$off = sint32($file, $off);
	$en = "$id,$pos,$cmd,{$arg[0]},{$arg[1]},$off";

	global $gp_data;
	$arid = array_search($en, $gp_data);
	if ( $arid === false )
		$gp_data[] = $en;
	$arid = array_search($en, $gp_data);
	return array($id,$off);
}

function sco_msg( $msg )
{
	$id = add_str($msg);
	return array("@MSG=$id");
}

function sco_cmd_msg( &$file, &$st, &$jump )
{
	$len = 0;
	while (1)
	{
		$by = $file[$st+$len];
		if ( $by == ZERO || $by == ':' )
			break;
		if ( in_array($st+$len, $jump) )
			break;

		$b1 = ord($by);
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
	if ( $file[$st] == ZERO || $file[$st] == ':' )
		$st++;
	$str = utf8_conv( CHARSET, sjistxt($str) );
	if ( $str == "" )
		$str = "iconv() error";
	return array("MSG", $str);
}

function sco_get_cmd( &$file, &$st, &$select, &$jump )
{
	$cmd = ord( $file[$st] );
	if ( $cmd & 0x80 || $cmd == 0x20 )
		return sco_cmd_msg( $file, $st, $jump );

	global $sco_cmd;
	$len = 5;
	while ( $len )
	{
		$cmd = substr($file, $st, $len);
		foreach ( $sco_cmd as $c )
		{
			if ( $c[1] == $cmd )
			{
				$st += $len;
				$arg = sco_cmd_arg($file, $st, array_slice($c, 2));
				return array( $c[0], $arg );
			}
		}
		$len--;
	}

	$cmd = $file[$st];
	$arg = array();
	switch ( $cmd )
	{
		case '!': // 0x21
			$st++;
			$arg[] = sco_varno($file, $st);
			$arg[] = sco_calli($file, $st);
			return array($cmd, $arg);
		case '$': // 0x24
			$st++;
			if ( $select )
			{
				$select = false;
				$cmd = "sel_ed";
			}
			else
			{
				$select = true;
				$cmd = "sel_st";
				$arg[] = sint32($file, $st);
			}
			return array($cmd, $arg);
		case '~': // 0x7e
			$st++;
			$t = sint16( $file, $st );
			$arg[] = $t;
			if ( $t == 0 || $t == -1 )
				$arg[] = sco_calli($file, $st);
			else
				$arg[] = sint32($file, $st);
			return array($cmd, $arg);
		case '<': // 0x3c
			$t = ord( $file[$st+1] );
			$st += 2;
			$cmd = "for_$t";
			if ( $t != 0 )
			{
				$arg[] = sint32($file, $st);
				$arg += sco_var_arg( 4, $file, $st );
			}
			return array($cmd, $arg);
		case '/': // 0x2f
			$fnc = ord( $file[$st+1] );
			$cmd = "AIN_$fnc";
			$st += 2;
			switch ( $fnc )
			{
				case 0x60: // 96 , ain HEL0
					ain_init();
					$dll1 = sint32($file, $st);
					$dll2 = sint32($file, $st);
					$cmd = "AIN_HEL0";
					$arg = ain_hel0($file, $st, "$dll1,$dll2");
					break;
				case 0x8b: // 139
					$arg[] = ain_str8b($file, $st);
					$arg[] = sco_calli($file, $st);
					$arg[] = sco_calli($file, $st);
					$arg[] = ain_str8b($file, $st);
					$arg[] = sco_calli($file, $st);
					break;
			}
			return array($cmd, $arg);
	}
	return array(0,0);
}
//////////////////////////////
function sco_run( $id, $pos )
{
	$func = __FUNCTION__;
	global $sco_file;
	if ( ! isset( $sco_file[$id] ); )
	{
		$fname = sprintf("%03d.sco", $id);
		$file  = file_get_contents($fname);
		if ( empty($file) )
			return;
		$sco_file[$id] = array(
			'f' => $file,
			's' => strlen($file),
		);
	}

	$select = false;
	$jump = array();

	global $sco_code, $sco_func;
	$sco_func["$id,$pos"] = 1;
	$len = strlen( $sco_file[$id]['f'] );
	while ( $pos < $len )
	{
		$sid = "$id,$pos";
		if ( isset( $sco_code[$sid] ) )
			return;

		$bak = $pos;
		list($cmd,$arg) = sco_get_cmd( $sco_file[$id]['f'], $pos, $select, $jump );
		if ( ! $cmd )
			break;
		$sco_file[$id]['s'] -= ($pos - $bak);

		if ( $cmd == "MSG" )
			$arg = sco_msg($arg);
		if ( $cmd == '#' ) // data
			$arg = sco_table($sco_file[$id]['f'], $id, $bak, $cmd, $arg);

		array_unshift($arg, $cmd);
		$sco_code[$sid] = implode(',', $arg). "\n";

		switch ( $cmd )
		{
			case '@': // label jump
				$j1 = $arg[0];
				if ( (int)$j1 === $j1 )
				{
					$jump[] = $j1;
					$func($id, $j1);
				}
				break;
			case '&': // page jump
				$j1 = $arg[0];
				if ( (int)$j1 === $j1 )
					$func($j1, 0);
				break;
			case '\\': // label call
				$j1 = $arg[0];
				if ( (int)$j1 === $j1 && $j1 > 0 )
				{
					$jump[] = $j1;
					$func($id, $j1);
				}
				break;
			case '%': // page call
				$j1 = $arg[0];
				if ( (int)$j1 === $j1 && $j1 > 0 )
					$func($j1, 0);
				break;

			case '~': // function call
				$j1 = $arg[0];
				$j2 = $arg[1];
				if ( (int)$j1 === $j1 && (int)$j2 === $j2 && $j1 > 0 )
					$func($j1, $j2);
				break;

			case '<': // for
			case '{': // if
				$jump[] = $arg[1];
				break;
			case "sel_st":
				$jump[] = $arg[0];
				break;

			default:
				break;
		} // switch ( $cmd )

	}
	return;
}

function sco_init_cmd()
{
	global $sco_cmd;
	$sco_cmd = array();
	$file = file_get_contents(SCO_CMD);
	foreach ( explode(BYTE, $file) as $line )
	{
		if ( strpos($line, SEP) )
			$sco_cmd[] = explode(SEP, $line);
	}
	//print_r($sco_cmd);
	return;
}
//////////////////////////////
sco_init_cmd();

// S380 Escalayer
//   DATA (should be in DA/*.dat) are compiled into SA/*.sco
// S360_153S_Umini_Ochite
//   1,647,&,|39|0| (page jump by $var[39])
for ( $i=1; $i < $argc; $i++ )
{
	$id = $argv[$i];
	if ( (int)$id === $id )
		sco_run($id, 0);
}

ksort($sco_code);
$fp = fopen("CODE", "w");
fwrite($fp, "# <?php exit();\n");
foreach( $sco_code as $ck => $cv )
{
	if ( isset($sco_func[$ck]) )
		fwrite($fp, "\n# $ck\n");
	fwrite($fp, "$ck,$cv\n");
}
foreach( $sco_file as $ck => $cv )
	fwrite($fp, "# $ck.sco has {$cv['s']} left\n");
fclose($fp);
