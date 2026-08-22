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

function fnt_pix( &$fnt, $off, $w, $h )
{
	$pix = '';
	$y = $h;
	while ( $y > 0 )
	{
		$y--;
		$p = $off + ($y * $w * 4);
		for ( $x=0; $x < $w; $x++ )
		{
			$pix .= $fnt[$p+2];
			$pix .= $fnt[$p+1];
			$pix .= $fnt[$p+0];
			$pix .= $fnt[$p+3];
				$p += 4;
		} // for ( $x=0; $x < $w; $x++ )
	} // while ( $y > 0 )

	return $pix;
}

function load_mapfnt( $fname )
{
	$file = load_file($fname);
	if ( empty($file) )  return '';

	$fnt = [];

	$len = strlen($file);
	if ( str2int($file, 8,4) == $len ) // bsm mgv gv gv2
		$pos = str2int($file, 4, 4);
	else
	if ( str2int($file,16,4) == $len ) // gva
		$pos = str2int($file, 8, 4);
	else
		return $fnt;

	$cnt = str2int($file, $pos, 4);
	$off = $pos + 4 + ($cnt * 0x20 * 2);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = $pos + 4 + ($i * 0x20);

		$p2 = str2int($file, $p1+ 0, 4);
		$sz = str2int($file, $p1+ 4, 4);
		$st = str2int($file, $p1+ 8, 4);
		$w  = str2int($file, $p1+16, 2);
		$h  = str2int($file, $p1+18, 2);
		if ( ($w*$h*4) != $sz )
			return php_error("%d  %x x %x != %x", $i, $w, $h, $sz);

		$fnt[] = [
			'w'   => $w,
			'h'   => $h,
			'pix' => fnt_pix($file, $off+$st, $w, $h),
			'fn'  => substr0($file, $pos+$p2),
		];
	} // for ( $i=0; $i < $cnt; $i++ )

	return $fnt;
}
//////////////////////////////
function load_mapbin( $fname )
{
	$file = load_file($fname);
	if ( empty($file) )  return '';

	$bin = [];
	$b64 = 4;
	if ( str2int($file,4,4) === 0 )
		$b64 = 8;

	$cnt = str2int($file, 0, 4);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $b64 + ($i * $b64);
		$of1 = str2int($file, $p+0   , 4);
		$of2 = str2int($file, $p+$b64, 4);
		$sz  = $of2 - $of1;

		$bin[] = substr($file, $of1, $sz);
	} // for ( $i=0; $i < $cnt; $i++ )

	return $bin;
}
