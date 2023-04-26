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

$gp_clut = '';

function psxtimfile( $fname, $check )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// can also be 00
	//$mgc = str2int($file, 0, 4);
	//if ( $mgc != 0x10 )  return;

	$count = array();

	// support converting multiple continous TIM file
	global $gp_clut;
	$pos = 0;
	$len = strlen($file);
	while ( $pos < $len )
	{
		$tim = psxtim($file, $pos, $check);
		if ( $tim === -1 )
			goto savetim;

		$pos += $tim['siz'];
		switch ( $tim['t'] )
		{
			case 'RGBA':
				$count[] = $tim;
				break;

			case 'CLUT':
				// for TIM missing palette
				// reusing palette from previous TIM file
				if ( ! isset($tim['pal']) || empty($tim['pal']) )
				{
					printf("passing palette from last TIM\n");
					$tim['pal'] = $gp_clut;
					$tim['cc' ] = strlen($gp_clut) >> 2;
				}

				// detected one CLUT in one TIM
				$cc = strlen($tim['pal']) >> 2;
				if ( $cc === $tim['cc'] )
					$count[] = $tim;
				else // detected multiple CLUT in one TIM
				{
					$pal = $tim['pal'];
					$cc  = $tim['cc'] << 2;
					$p   = 0;
					while (1)
					{
						$s = substr($pal, $p, $cc);
							$p += $cc;

						if ( empty($s) )
							break;
						if ( trim($s, BYTE.ZERO) === '' )
							continue;
						$tim['pal'] = $s;

						$count[] = $tim;
					} // while (1)
				}

				$gp_clut = $tim['pal'];
				break;
		} // switch ( $tim['t'] )
	} // while ( $pos < $len )

savetim:
	$cnt = count($count);
	if ( $cnt === 0 )
		return;

	if ( $cnt === 1 )
	{
		if ( $count[0]['t'] === 'RGBA' )
			return save_clutfile("$fname.rgba", $count[0]);
		if ( $count[0]['t'] === 'CLUT' )
			return save_clutfile("$fname.clut", $count[0]);
	}

	$dir = str_replace('.', '_', $fname);
	for ( $i=0; $i < $cnt; $i++ )
	{
		if ( $count[$i]['t'] === 'RGBA' )
			$fn = sprintf('%s/%04d.rgba', $dir, $i);
		if ( $count[$i]['t'] === 'CLUT' )
			$fn = sprintf('%s/%04d.clut', $dir, $i);

		save_clutfile($fn, $count[$i]);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

printf("%s  [-f]  TIM...\n", $argv[0]);
printf("  -f : force convert TIM larger then VRAM size\n");
$check = true;
for ( $i=1; $i < $argc; $i++ )
{
	if ( $argv[$i] === '-f' )
		$check = false;
	else
		psxtimfile( $argv[$i], $check );
}

/*
 * Weird TIM files
 * - Legend of Mana /wm/wmap/wm1.tim
 *   - TIM 2 = 8-bpp CLUT gray
 *
*/
