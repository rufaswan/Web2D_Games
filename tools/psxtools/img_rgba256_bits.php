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
require "common.inc";
require "common-guest.inc";

function rgba256( $fname, $msb )
{
	$rgba = load_clutfile($fname);
	if ( $rgba === 0 )  return;

	// already clut
	if ( isset($rgba['pal']) )
		return;

	$sll  = 0;
	$mask = BIT8;
	$len  = strlen($rgba['pix']);
	while (1)
	{
		for ( $i=0; $i < $len; $i++ )
		{
			// skip 00 and ff
			if ( $rgba['pix'][$i] === ZERO )
				continue;
			if ( $rgba['pix'][$i] === BYTE )
				continue;
			$p = ord( $rgba['pix'][$i] );
			$p &= $mask;

			// duplicate MSB
			if ( $msb )
			{
				$srl = 8 - $sll;
				for ( $j = $srl; $j < 8; $j += $srl )
					$p |= ($p >> $srl);
			}

			$rgba['pix'][$i] = chr($p);
		} // for ( $i=0; $i < $len; $i++ )

		$img = rgba2clut($rgba['pix']);
		if ( $img === -1 )
		{
			$sll++;
			$mask <<= 1;
		}
		else
		{
			$rgba['cc']  = strlen($img[0]) >> 2; // div 4
			$rgba['pal'] = $img[0];
			$rgba['pix'] = $img[1];
			save_clutfile("$fname.$sll.clut", $rgba);
			return;
		}
	} // while (1)
	return;
}

printf("%s  [-/+msb]  RGBA_FILE...\n", $argv[0]);
$msb = false;
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '+msb':  $msb =  true; break;
		case '-msb':  $msb = false; break;
		default:
			rgba256( $argv[$i], $msb );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )
