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

define("SCO_CMD", ROOT . "/cmd_sco39.inc");

$gp_code = array("# <?php exit();\n");
$gp_data = array("");
$gp_msg  = array("", "iconv() error");
$gp_func = array();

$sco_cmd = array();
$ain_hel0 = array();
////////////////////////////////////////
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

	global $gp_msg;
	$str = sjis2utf8($gp_msg, $str);
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
	global $gp_msg;
	$id = add_str($gp_msg, $msg);
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
	$str = sjis2utf8($str);
	if ( $str == "" )
		$str = "iconv() error";
	return array("MSG", $str);
}

function sco_get_cmd( &$file, &$st, &$select, &$jump )
{
	$cmd = ord( $file[$st] );
	if ( $cmd & 0x80 || $cmd == 0x20 )
		return sco_cmd_msg( $file, $st, $jump );

	global $sco_cmd, $gp_msg;
	$len = 5;
	while ( $len )
	{
		$cmd = substr($file, $st, $len);
		foreach ( $sco_cmd as $c )
		{
			if ( $c[1] == $cmd )
			{
				$st += $len;
				$arg = sco_cmd_arg($file, $st, array_slice($c, 2), $gp_msg);
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
////////////////////////////////////////
function save_sco( $fname, $var )
{
	$fp = fopen($fname, "w");
	foreach( $var as $v )
		fwrite($fp, "$v\n");
	fclose($fp);
}

function sco_run( $id, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )
		return;

	$valid = array("S350", "S351", "153S", "S360", "S380");
	$mgc = substr($file, 0, 4);
	if ( ! in_array($mgc, $valid) )
		return;
	printf("[%s] %s\n", $mgc, $fname);

	global $gp_func;
	$select = false;
	$jump = array();

	$ed = strlen($file);
	$st = 0;
	$buf = "# $id,0\n";
	while ( $st < $ed )
	{
		$bak = $st;
		list($cmd,$arg) = sco_get_cmd( $file, $st, $select, $jump );
		if ( ! $cmd )
			break;

		if ( $cmd == "MSG" )
			$arg = sco_msg($arg);
		if ( $cmd == '#' ) // data
			$arg = sco_table($file, $id, $bak, $cmd, $arg);

		array_unshift($arg, $cmd);
		$buf .= "$id,$bak," .implode(',', $arg). "\n";

		switch ( $cmd )
		{
			case "S350":
			case "S351":
			case "153S":
			case "S360":
			case "S380":
				$st = $arg[0];
				break;

			case '@': // label jump
				$jump[] = $arg[0];
				break;
			case '<': // for
			case '{': // if
				$jump[] = $arg[1];
				break;
			case "sel_st":
				$jump[] = $arg[0];
				break;

			case '~': // function call
				$jal = "{$arg[0]},{$arg[1]}";
				if ( $arg[0] != 0 )
					$gp_func[$jal] = 1;
				break;
			case '%': // page call
				$jal = "{$arg[0]},0";
				if ( $arg[0] != 0 )
					$gp_func[$jal] = 1;
				break;
			case '\\': // label call
				$jal = "$id,{$arg[0]}";
				if ( $arg[0] != 0 )
					$gp_func[$jal] = 1;
				break;
			default:
				break;
		} // switch ( $cmd )

	} // while ( $st < $ed )
	if ( $file[$st] != ZERO )
		printf("ERROR $fname + %x unknown command\n", $st);

	global $gp_code;
	$gp_code[$id] = $buf;
	return;
}

function sco_init_cmd()
{
	global $sco_cmd;
	$file = file_get_contents(SCO_CMD);
	$sco_cmd = unserialize($file);
	return;
}
////////////////////////////////////////
sco_init_cmd();

// S380 Escalayer
//   Data (should be in *DA.ald) compiled as SCO
// S360_153S_Umini_Ochite
//   1,647,&,|39|0| (page jump by $var[39])
foreach ( scandir('.') as $f )
{
	if ( $f[0] == '.' )
		continue;
	if ( is_dir($f) || $f == 'log' )
		continue;
	if ( stripos($f, '.txt') || stripos($f, '.php') )
		continue;

	// should support *.sco *.dat *.s350 *.s351 *.153s *.s360 *.380
	$id = substr($f, 0, strrpos($f, '.')) + 0;
	sco_run($id, $f);
}
//sco_run(1, "001.sco");

$gp_func = array_keys($gp_func);
sort($gp_func);

save_sco("sco_code.txt", $gp_code);
save_sco("sco_data.txt", $gp_data);
save_sco("sco_msg.txt",  $gp_msg);
save_sco("sco_func.txt", $gp_func);
