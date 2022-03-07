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
require 'common.inc';
require 'common-guest.inc';

function png_chunk( &$png )
{
	$chunk = array();
	$chunk['PNG'] = substr($png, 0, 8);

	$ed = strlen($png);
	$st = 8;
	while ( $st < $ed )
	{
		//   uppercase     lowercase
		// 1 is critical / optional
		// 2 is public   / private
		// 3 *reserved*  / *invalid*
		// 4 is unsafe   / safe to copy by editor
		$mgc = substr($png, $st+4, 4);
		$len = str2big($png, $st+0, 4);
		printf("%8x , %8x , $mgc\n", $st, $len);

		$dat = substr($png, $st+8, $len);
		if ( ! isset( $chunk[$mgc] ) )
			$chunk[$mgc] = '';
		$chunk[$mgc] .= $dat;

		$st += (8 + $len + 4);
	} // while ( $st < $ed )

	return $chunk;
}

// to strip off any optional PNG chunks (tIME,gAMA,iCCP,etc)
function pngstrip( $fname )
{
	$png = file_get_contents($fname);
	if ( empty($png) )  return;

	if ( substr($png, 1, 3) !== 'PNG' )
		return;

	$chunk = png_chunk($png);

	// chunks to keep
	$tag = array('IHDR', 'PLTE', 'tRNS', 'IDAT', 'IEND');
	$strip = $chunk['PNG'];
	foreach ( $tag as $t )
	{
		if ( ! isset( $chunk[$t] ) )
			continue;
		$len = strlen($chunk[$t]);
		$crc = crc32( $t . $chunk[$t] );

		$strip .= chrbig($len, 4);
		$strip .= $t . $chunk[$t];
		$strip .= chrbig($crc, 4);
	}

	file_put_contents($fname, $strip);
	return;
}

for ( $i=0; $i < $argc; $i++ )
	pngstrip( $argv[$i] );
