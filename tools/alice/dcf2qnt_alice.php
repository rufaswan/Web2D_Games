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
//////////////////////////////
function dcf2qnt( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$ed = strlen($file);
	$st = 0;
	while( $st < $ed )
	{
		$tag = substr($file, $st, 4);
		switch ( $tag )
		{
			case "dcf ":
			case "dfdl":
				printf("%8x , $tag\n", $st);
				$len = str2int($file, $st+4, 4);
				$st += 8;
				$st += $len;
				break;
			case "dcgd":
				printf("%8x , $tag\n", $st);
				$len = str2int($file, $st+4, 4);
				$st += 8;

				$qnt = substr($file, $st, $len);
				$mgc = substr($qnt, 0, 3);
				if ( $mgc == "QNT" )
					file_put_contents("$fname.qnt", $qnt);
				else
					file_put_contents("$fname.dat", $qnt);
				$st += $len;
				break;
			default:
				return;
		}
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dcf2qnt( $argv[$i] );
