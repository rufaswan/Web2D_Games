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

define("SCO_CMD", ROOT . "/sco_cmd.txt");

$gp_msg = array();
$gp_mid = 1;
$sco_cmd = array();
////////////////////////////////////////
function sco_cmd_punct( &$file, &$st, &$select )
{
	$bak = $st;
	$cmd = $file[$st];
	switch ( $cmd )
	{
		case '!': // 0x21
			$st++;
			$arg[] = sco_varno( $file, $st );
			$arg[] = sco_calli( $file, $st );
			$arg = implode(',', $arg);
			echo "$cmd,$arg";
			return true;
		case '{': // 0x7b
			$st++;
			$arg[] = sco_calli( $file, $st );
			$arg[] = sint32( $file, $st );
			$arg = implode(',', $arg);
			echo "$cmd,$arg";
			return true;
		case '#': // 0x23
			$st++;
			$arg[] = sint32( $file, $st );
			$arg[] = sco_calli( $file, $st );
			$arg = implode(',', $arg);
			echo "$cmd,$arg";
			return true;
		case '/': // 0x2f
			$id = ord( $file[$st+1] );
			$st += 2;
			if ( ! sco_cmd_id( $id, $file, $st ) )
			{
				$st = $bak;
				return false;
			}
			return true;
		case '$': // 0x24
			if ( $select )
			{
				$st++;
				echo "$,end";
				$select = false;
			}
			else
			{
				$st++;
				$select = true;
				$arg = sint32( $file, $st );
				echo "$cmd,$arg";
			}
			return true;
		case '~': // 0x7e
			$st++;
			$t = sint16( $file, $st );
			if ( $t == 0 || $t == -1 )
			{
				$arg = sco_calli( $file, $st );
				echo "$cmd,$t,$arg";
			}
			else
			{
				$arg = sint32( $file, $st );
				echo "$cmd,$t,$arg";
			}
			return true;
		case '<': // 0x3c
			$t = ord( $file[$st+1] );
			if ( $t == 0 )
			{
				$st += 2;
				echo "$cmd,$t";
			}
			else
			{
				$st += 2;
				$arg[] = sint32( $file, $st );
				$arg[] = sco_var_args( 4, $file, $st );
				$arg = implode(',', $arg);
				echo "$cmd,$t,$arg";
			}
			return true;
		case ']': // 0x5d
			$st++;
			echo "$cmd";
			return true;
		case '>': // 0x3e
		case '@': // 0x40
		case '\\': // 0x5c
			$st++;
			$arg = sint32( $file, $st );
			echo "$cmd,$arg";
			return true;
		case '%': // 0x25
		case '&': // 0x26
			$st++;
			$arg = sco_calli( $file, $st );
			echo "$cmd,$arg";
			return true;
	}
	$st = $bak;
	return false;
}

function sco_cmd_id( $id, &$file, &$st )
{
	global $sco_cmd;
	foreach ( $sco_cmd as $cmd )
	{
		if ( $id != $cmd[0] )
			continue;

		$id = $cmd[0];
		$cm = $cmd[1];
		$arg = array_slice($cmd, 2);
		$arg = sco_cmd_arg( $file, $st, $arg );
		echo "/,$id,$cm,$arg";
		return true;
	}
	return false;
}

function sco_cmd_upper( &$file, &$st, &$select )
{
	$bak = $st;
	global $sco_cmd;
	$bin = substr($file, $st, 5);
	foreach ( $sco_cmd as $cmd )
	{
		if ( strpos($bin, $cmd[1]) !== 0 )
			continue;

		$cm = $cmd[1];
		switch ( $cm )
		{
			case "SX":
				if ( $file[$st+3] != $cmd[3] )
					continue;
				$st += 2;
				$arg = array_slice($cmd, 2);
				$arg = sco_cmd_arg( $file, $st, $arg );
				echo "$cm,$arg";
				return true;
			case 'B':
			case 'G':
			case 'J':
			case 'F':
				if ( $file[$st+1] != $cmd[2] )
					continue;
				$st += 1;
				$arg = array_slice($cmd, 2);
				$arg = sco_cmd_arg( $file, $st, $arg );
				echo "$cm,$arg";
				return true;
			case "CK":
			case "PF":
			case "PT":
			case "PW":
			case "SG":
			case "SR":
			case "VA":
			case "WZ":
			case "ZT":
			case "ZZ":
				if ( $file[$st+2] != $cmd[2] )
					continue;
				$st += 2;
				$arg = array_slice($cmd, 2);
				$arg = sco_cmd_arg( $file, $st, $arg );
				echo "$cm,$arg";
				return true;
			default:
				$len = strlen($cmd[1]);
				$st += $len;
				$arg = array_slice($cmd, 2);
				$arg = sco_cmd_arg( $file, $st, $arg );
				echo "$cm,$arg";
				return true;
		}
	}
	$st = $bak;
	return false;
}

function sco_init_cmd()
{
	global $sco_cmd;
	$sco_cmd = array();
	foreach ( file(SCO_CMD) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		if ( empty($line) )
			continue;
		if ( $line[0] == '#' )
			continue;
		$sco_cmd[] = explode(',', $line);
	}
	return;
}
////////////////////////////////////////
function sco35txt( $rem, $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	sco_init_cmd();
	$base = substr($fname, 0, strrpos($fname, '.'));

	$ed = strlen($file);
	$st = 4;
	$st = sint32($file, $st);
	$select = false;
	while ( $st < $ed )
	{
		printf("$base,%x,%d,", $st, $st);

		$bak = $st;
		$cmd = $file[$st];

		$b = ord( $cmd );
		if ( $b == 0 )
		{
			echo "SYS_END\n";
			return;
		}

		// SJIS text + space has no command
		if ( $b == 0x20 || $b & 0x80  )
		{
			$arg = sco_sjis($file, $st);
			$id = add_str( $arg );
			echo "MSG,$id\n";
			continue;
		}

		// symbol commands
		if ( ctype_punct( $cmd ) )
		{
			if ( sco_cmd_punct( $file, $st, $select ) )
			{
				echo "\n";
				continue;
			}
		}

		// uppercase commands
		if ( ctype_upper( $cmd ) )
		{
			if ( sco_cmd_upper( $file, $st, $select ) )
			{
				echo "\n";
				continue;
			}
		}

		if ( $bak == $st )
			return;
	} // while ( $st < $ed )
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	sco35txt( $argc-$i, $argv[$i] );

foreach ( $gp_msg as $k => $v )
	echo "$k,$v\n";
return;

