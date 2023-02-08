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
require 'common.inc';
//////////////////////////////
function sect($fp, $base, $ed, $dir, $id)
{
	printf("=== sect( %x , %x , %s , %d )\n", $base, $ed, $dir, $id);
	if ( $ed == 0 )
	{
		fseek($fp, 0, SEEK_END);
		$ed = ftell($fp);
	}

	$sid = 1;
	while ( $base < $ed )
	{
		$num = fp2int( $fp, $base, 4 );
		$base += 4;
		for ( $i=0; $i < $num; $i++ )
		{
			$siz = fp2int( $fp, $base, 4 );
			$fn = sprintf('%s/%03d-%03d-%03d.dat', $dir, $id, $sid, $i);
			printf("%8x , %8x , %s\n", $base, $siz, $fn);

			fseek($fp, $base + 4, SEEK_SET);
			file_put_contents($fn, fread($fp, $siz));
			$base += (4 + $siz);
		}
		$sid++;
	}
	return;
}
//////////////////////////////
// Alice7/Dungeon&Dolls  Data/Texture.dtl
function alice( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$mgc = fread($fp, 3);
	if ( $mgc !== 'DTL' )  return;

	$dir = str_replace('.', '_', $fname);
	@mkdir($dir, 0755, true);

	$st = 0x10;
	$id = 1;
	while (1)
	{
		$off1 = fp2int( $fp, $st+0, 4 );
		$off2 = fp2int( $fp, $st+4, 4 );
		if ( $off1 == 0 )
			break;
		sect($fp, $off1, $off2, $dir, $id);
		$st += 4;
		$id++;
	}

	fclose($fp);
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	alice( $argv[$i] );

