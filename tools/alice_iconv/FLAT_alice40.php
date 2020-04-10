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
function xorstr( &$str, $key )
{
	$ed = strlen($str);
	while ( $ed > 0 )
	{
		$ed--;
		$b = ord( $str[$ed] );
		$b ^= $key;
		$str[$ed] = chr($b);
	}
	return;
}
//////////////////////////////
function flat( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	//$mgc = substr($file, 0, 4);
	//if ( $mgc != "FLAT" )  return;
	echo "=== FLAT : $fname ===\n";

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$elna = false;
	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$tag = substr($file, $st, 4);
		switch ( $tag )
		{
			case "ELNA":
				printf("$tag , %8x\n", $st);
				$st += 8;
				$elna = true;
				break;
			case "FLAT":
				$bak = $st;
				$st += 4;
				$len = sint32($file, $st);
				printf("$tag , %8x , %8x\n", $bak, $len);
				$st += $len;
				break;
			case "TMNL":
			case "MTLC":
				$bak = $st;
				$st += 4;
				$len = sint32($file, $st);
				printf("$tag , %8x , %8x\n", $bak, $len);

				$bak = $st;

				$data = substr($file, $st+4, $len-4);
				$data = zlib_decode($data);
				file_put_contents("$dir/$tag", $data);

				$st = $bak + $len;
				break;
			case "LIBL":
				$bak = $st;
				$st += 4;
				$len = sint32($file, $st);
				printf("$tag , %8x , %8x\n", $bak, $len);

				$bak = $st;
				$liblno = sint32($file, $st);

				for ( $ln=0; $ln < $liblno; $ln++ )
				{
					$bak1 = $st;
					$fnl = sint32($file, $st);
					$fnm = substr($file, $st, $fnl);
						$st += $fnl;

					if ( $elna )
						xorstr($fnm, 0x55);

					while ( ($st%4) != 0 )
						$st++;

					$fnm = utf8_conv( CHARSET, sjistxt($fnm) );

					$sep = strrpos($fnm, '/');
					if ( $sep != false )
					{
						$dn = substr($fnm, 0, $sep);
						@mkdir("$dir/$dn", 0755, true);
					}

					$fflag = sint32($file, $st);
					$fdlen = sint32($file, $st);
					printf("%8x , %2x , %8x , %s\n", $bak1, $fflag, $fdlen, $fnm);

					if ( $fflag == 2 )
					{
						$st += 4;
						$fdlen -= 4;
						$data = substr($file, $st, $fdlen);
					}
					else
					if ( $fflag == 5 )
					{
						$st += 4;
						$fdlen -= 4;
						$data = substr($file, $st, $fdlen);
						$data = zlib_decode($data);
					}
					else
						$data = substr($file, $st, $fdlen);

					file_put_contents("$dir/$fnm", $data);

					$st += $fdlen;
					while ( ($st%4) != 0 )
						$st++;
				}

				$st = $bak + $len;
				break;
			default:
				return;
		}
	}
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	flat( $argv[$i] );
