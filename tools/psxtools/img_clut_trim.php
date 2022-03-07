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

function img_rgba( &$img )
{
	// convert into RGBA
	if ( isset( $img['pal'] ) )
	{
		$pix = '';
		$len = strlen($img['pix']);
		for ( $i=0; $i < $len; $i++ )
		{
			$b = ord( $img['pix'][$i] );
			$pix .= substr($img['pal'], $b*4, 4);
		}
		$img['pix'] = $pix;
		unset($img['cc' ]);
		unset($img['pal']);
	}
	return;
}

function img_trim( &$diff, &$img )
{
	$ow = $img['w'];
	$oh = $img['h'];

	$rt = 0;
	$rb = $oh;
	$rl = 0;
	$rr = $ow;

	// trim top bottom
		while ( $rt < $rb )
		{
			$s = substr($img['pix'], $rt*$ow*4, $ow*4);
			if ( trim($s, ZERO) === '' )
				$rt++;
			else
				break;
		} // while ( $rt > $rb )

		while ( $rb > $rt )
		{
			$s = substr($img['pix'], ($rb-1)*$ow*4, $ow*4);
			if ( trim($s, ZERO) === '' )
				$rb--;
			else
				break;
		} // while ( $rt > $rb )

	// trim left right
		while ( $rl < $rr )
		{
			$s = '';
			for ( $i=$rt; $i < $rb; $i++ )
			{
				$rxx = ($i * $ow) + $rl;
				$s .= substr($img['pix'], $rxx*4, 4);
			}
			if ( trim($s, ZERO) === '' )
				$rl++;
			else
				break;
		} // while ( $rl < $rr )

		while ( $rr > $rl )
		{
			$s = '';
			for ( $i=$rt; $i < $rb; $i++ )
			{
				$rxx = ($i * $ow) + $rr - 1;
				$s .= substr($img['pix'], $rxx*4, 4);
			}
			if ( trim($s, ZERO) === '' )
				$rr--;
			else
				break;
		} // while ( $rl < $rr )

	if ( $rt == 0 && $rb == $oh && $rl == 0 && $rr == $ow )
		return;

	$diff = true;
	$rw = $rr - $rl;
	$rh = $rb - $rt;
	$pix = '';
	for ( $y=$rt; $y < $rb; $y++ )
	{
		$rxx = ($y * $ow) + $rl;
		$pix .= substr($img['pix'], $rxx*4, $rw*4);
	} // for ( $y=$rt; $y < $rb; $y++ )

	$img['w'] = $rw;
	$img['h'] = $rh;
	$img['pix'] = $pix;
	return;
}

function img_clut( &$diff, &$img )
{
	// convert back to CLUT
	$pal = array(PIX_ALPHA);
	$pix = '';
	$len = strlen($img['pix']);
	for ( $i=0; $i < $len; $i += 4 )
	{
		$s = substr($img['pix'], $i, 4);
		if ( array_search($s, $pal) === false )
			$pal[] = $s;
		if ( count($pal) > 0x100 )
			return;

		$id = array_search($s, $pal);
		$pix .= chr($id);
	} // for ( $i=0; $i < $len; $i += 4 )

	$diff = true;
	$img['cc']  = count($pal);
	$img['pal'] = implode('', $pal);
	$img['pix'] = $pix;
	return;
}
//////////////////////////////
function cluttrim( $trim, $clut, $fname )
{
	if ( ! is_file($fname) )
		return;
	$img = load_clutfile($fname);
	if ( empty($img) )
		return;
	echo "IMG = $fname\n";

	img_rgba($img);
	if ( trim($img['pix'], ZERO) === '' )
		return php_warning('%s is all 00 / FF', $fname);

	$diff = false;
	if ( $trim )  img_trim($diff, $img);
	if ( $clut )  img_clut($diff, $img);

	if ( ! $diff )
		return;
	save_clutfile($fname, $img);
	return;
}
//////////////////////////////
echo <<<_ERR
{$argv[0]}  [option]  FILE...
option :
  -/+trim  blindly remove spaces around the image [default +trim]
  -/+clut  attempt to convert RGBA into CLUT      [default +clut]

_ERR;

$trim = true;
$clut = true;
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-trim':  $trim = false; break;
		case '+trim':  $trim = true;  break;
		case '-clut':  $clut = false; break;
		case '+clut':  $clut = true;  break;
		default:
			cluttrim($trim, $clut, $argv[$i]);
			break;
	} // switch ( $argv[$i] )
}
