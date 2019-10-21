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
function flat( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	$mgc = substr($file, 0, 4);
	if ( $mgc != "FLAT" )  return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$tag = substr($file, $st, 4);
		switch ( $tag )
		{
			case "FLAT":
			case "MTLC":
				printf("%8x : $tag\n", $st);
				$st += 4;
				$len = sint32($file, $st);
				$st += $len;
				break;
			case "LIBL":
				printf("%8x : $tag\n", $st);
				$st += 4;
				$len = sint32($file, $st);

				$bak = $st;
				$liblno = sint32($file, $st);

				for ( $ln=0; $ln < $liblno; $ln++ )
				{
					$bak = $st;
					$fnl = sint32($file, $st);
					$fnm = substr($file, $st, $fnl);
						$st += $fnl;
					while ( ($st%4) != 0 )
						$st++;

					$fnm = sjistxt($fnm);
					$fnm = iconv("MS932", "UTF-8", $fnm);

					$sep = strrpos($fnm, '/');
					if ( $sep != false )
					{
						$dn = substr($fnm, 0, $sep);
						@mkdir("$dir/$dn", 0755, true);
					}

					$fflag = sint32($file, $st);
					$fdlen = sint32($file, $st);
					printf("%8x , %2x , %8x , %s\n", $bak, $fflag, $fdlen, $fnm);

					if ( $fflag == 2 )
					{
						$st += 4;
						$fdlen -= 4;
					}

					$data = substr($file, $st, $fdlen);
					file_put_contents("$dir/$fnm", $data);

					$st += $fdlen;
					while ( ($st%4) != 0 )
						$st++;
				}

				$st = $bak + $len;
				break;
		}
	}

}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	flat( $argv[$i] );
