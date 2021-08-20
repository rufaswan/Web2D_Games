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

function palbyte( &$pal )
{
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 4 )
		$pal[$i+3] = BYTE;
	return;
}

function get_big4( &$buf, &$file, &$pos, $cnt )
{
	while ( count($buf) < $cnt )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$buf[] = ($b >> 4) & BIT4;
		$buf[] = ($b >> 0) & BIT4;
	}
	return;
}

function save_txt( &$file, &$sect, $dir )
{
	if ( empty($sect[1]) )
		return;
	printf("== save_txt( $dir )\n");

	foreach ( $sect[1] as $k => $v )
	{
		$end = str2int($file, $v-4, 4);
		$sub = substr($file, $v, $end-$v);
		$txt = '';

		$siz = strlen($sub);
		$pos = 0;
		while ( $pos < $siz )
		{
			$len = ord($sub[$pos]);
			$s = substr($sub, $pos+1, $len);

			$pos += (1 + $len);
			$txt .= "$s\n";
		} // while ( $pos < $siz )

		$fn = sprintf("$dir/%04d.txt", $k);
		trace("save text = %s\n", $fn);
		save_file($fn, $txt);
	} // foreach ( $sect[1] as $k => $v )

	return;
}

function scnsect( &$file )
{
	$sect = array();
	$sect[1] = array();

	$pos = 0;
	while (1)
	{
		if ( substr($file,$pos+1,3) !== "\x0043" )
			return $sect;

		$id = ord( $file[$pos] );
		$nx = str2int($file, $pos+4, 4);
		trace("%8x , %8x , %2x\n", $pos, $nx-$pos-8, $id);

		if ( $id == 1 )
			$sect[1][] = $pos + 8;
		else
		{
			if ( isset( $sect[$id] ) )
				return php_error("DUP 43 @ %x = %x", $pos, $id);
			$sect[$id] = $pos + 8;
		}

		$pos = $nx;
		if ( $pos == 0 )
			break;
	} // while (1)

	return $sect;
}
//////////////////////////////
function dw2_syspal( $file, $st5 )
{
	// Discworld 2 reuses background palette for sprites
	// it is to use the FIRST palette from st5
	// and if st5 is empty, return grayscale palette
	$nx = str2int($file, $st5-4, 4);
	if ( ($st5+0x400) < $nx ) // no palette available
		return grayclut(0x100);

	$pal = substr($file, $st5, 0x400);
	palbyte($pal);
	return $pal;
}

/*
function pc_dw2_pak( &$file, $st19, $w, $h, $pak, $fn )
{
	if ( $pak == 0 )
		return;

	$pix = '';
	$pal = '';
	$clr = '';

	if ( $pak == 3 )
	{
		$b1  = ord( $file[$st19] );
		$pal = substr($file, $st19+1, $b1);
			$st19 += ($b1 + 1);
	}

	for ( $y=0; $y < $h; $y++ )
	{
		$xoff = ord( $file[$st19] );
			$st19++;

		$eol = false;
		$x = 0;
		while ( $x < $w )
		{
			while (1)
			{
				$pix .= str_repeat(ZERO, $xoff);
				$x += $xoff;
				$xoff = 0;

				$b = ord( $file[$st19] );
					$st19++;

				$b1 = ($b >> 0) & BIT4;
				$b2 = ($b >> 4) & BIT4;

				switch ( $pak )
				{
					case 1:
						$b2 += 0xf0;
						$clr = chr($b2);
						break;
					case 2:
						$b2 += 0xe0;
						$clr = chr($b2);
						break;
					case 3:
						$clr = $pal[$b2];
						break;
				} // switch ( $pak )

				if ( $b1 > 0 )
					break;

				$b1 = ord( $file[$st19] );
					$st19++;

				$xoff = $b1 + $b;
				if ( $xoff == 0 )
				{
					$eol = true;
					break;
				}
			} // while (1)

			if ( $eol )
				break;

			$pix .= str_repeat($clr, $b1);
			$x += $b1;
		} // while ( $x < $w )

		if ( ! $eol )
		{
			$st19 += 2;
		}

	} // for ( $y=0; $y < $h; $y++ )

	$img = array(
		'cc'  => 0x100,
		'w'   => $w,
		'h'   => $h,
		'pal' => grayclut(0x100),
		'pix' => $pix,
	);
	save_clutfile("$fn.clut", $img);
	return;
}
*/
