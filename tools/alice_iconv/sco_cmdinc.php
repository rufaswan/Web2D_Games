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
$txt = "sco_cmd.txt";
$bin = "sco_cmd.inc";

define("ZERO", chr(  0));
define("BYTE", chr(255));
define("SEP",  chr(254));

if ( ! file_exists($txt) )
	exit();

// to compile sco_cmd.txt to binary friendly format
$data = "";
foreach ( file($txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line )
{
	$line = preg_replace('|[\s]|', '', $line);
	if ( empty($line) )
		continue;
	if ( $line[0] == '#' )
		continue;
	$line = explode(',', $line);

	// for sys38+ func uses id
	if ( $line[0] >= 0 )
	{
		switch ( $line[1] )
		{
			case 'F':
			case "LE":
			case "QE":
				$cmd = "{$line[1]}_{$line[2]}" . SEP;
				$cmd .= '/' . chr($line[0]) . chr($line[2]);
				$skp = 3;
				break;
			default:
				$cmd = "{$line[1]}" . SEP;
				$cmd .= '/' . chr($line[0]);
				$skp = 2;
				break;
		}
		$arg = implode(SEP, array_slice($line, $skp));
		if ( $arg )
			$data .= $cmd . SEP . $arg . BYTE;
		else
			$data .= $cmd . BYTE;
	}

	// for sys35
	switch ( $line[1] )
	{
		case 'SX':
			$cmd = "{$line[1]}_{$line[2]}_{$line[3]}" . SEP;
			$cmd .= $line[1] . chr($line[2]) . chr($line[3]);
			$skp = 4;
			break;
		case 'B':  case 'J':  case 'F':
		case 'CK':
		case 'IK':
		case 'LL':  case 'LE':
		case 'MG':  case 'MN':  case 'MZ':
		case 'NO':
		case 'PF':  case 'PT':  case 'PW':
		case 'QE':
		case 'SG':  case 'SI':  case 'SR':
		case 'UC':
		case 'VA':  case 'VZ':  case 'WZ':
		case 'ZA':  case 'ZD':  case 'ZT':  case 'ZZ':
		case 'LHD':  case 'LHG':  case 'LHM': case 'LHS':  case 'LHW':
		case 'G':
			$cmd = "{$line[1]}_{$line[2]}" . SEP;
			$cmd .= $line[1] . chr($line[2]);
			$skp = 3;
			break;
		default:
			$cmd = "{$line[1]}" . SEP;
			$cmd .= $line[1];
			$skp = 2;
			break;
	}
	$arg = implode(SEP, array_slice($line, $skp));
	if ( $arg )
		$data .= $cmd . SEP . $arg . BYTE;
	else
		$data .= $cmd . BYTE;
}
file_put_contents($bin, $data);
