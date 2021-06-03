<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
/*
 * 00 4 HEAD end
 * 04 4
 * 08 4
 * 0c 4 CHR offset(+4)
 * 10 4 CHR end
 * 1c 4 MAP TEX data(+4)
 * 20 4 MAP TEX end
 * 28 4
 * 2c 4
 * 30 4 MOVE data
 * 34 4 MOVE end
 *
 * MAP TEX data
 * 00 4 [loop] data head start(+4)
 *   [loop]
 *   00 4 [loop] bg start/data head end
 *     [loop]
 *     00 2 img_tex 16*16 offsets (in 16s)
 *     ...
 *   ...
 * 31 1 map width (in tiles)
 *
 * bg data
 * = 00 0e 1c 2a ... 01 0f 1d 2b ... 02 10 1e 2c ...
 *
 * tex in 16*16 tile
 * 00 0e 1c 2a ...
 * 01 0f 1d 2b ...
 * 02 10 1e 2c ...
 * ...
 *
 * ffff = dummy
 * 00   =    c in map%03x.tex (size = 0x100)
 * 01   =  10c in map%03x.tex (size = 0x100)
 * 10   = 100c in map%03x.tex (size = 0x100)
 */
require "common.inc";

function savechr( &$img, $dir )
{
	$st = str2int($img, 0x0c, 4) + 4;
	$ed = str2int($img, 0x10, 4);
	$no = 1;
	while ( $st < $ed )
	{
		$fn = sprintf("$dir/chr%03d.chr", $no);
		$sz = str2int($img, $st, 4);
		if ( $sz == 0 )
			return;

		printf("=== savechr() , %x , %x , $fn\n", $st, $sz);
		$chr = substr($img, $st, $sz);
		save_file($fn, $chr);

		$st += $sz;
		$no++;
	}
	return;
}

function saga2( $fname )
{
	if ( stripos($fname, '.img') === false )
		return;

	$img = file_get_contents($fname);
	if ( empty($img) )  return;

	$dir = str_replace('.', '_', $fname);
	savechr($img, "{$dir}chr");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );

/*
	/mout/map.out is loaded to 800ac000
	data is loaded to 801a0000
 */
