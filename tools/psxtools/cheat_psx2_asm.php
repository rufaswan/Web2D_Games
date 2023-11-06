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
require 'common-iso.inc';
require 'cheat_psx2_asm.inc';

function lbu_update( &$sub, $pos, $val )
{
	$b1 = $val & BIT8;
	$sub[$pos] = chr($b1);
	return;
}

function lhu_update( &$sub, $pos, $val )
{
	$b1 = ($val >> 0) & BIT8;
	$b2 = ($val >> 8) & BIT8;
	$sub[$pos+0] = chr($b1);
	$sub[$pos+1] = chr($b2);
	return;
}

function lw_update( &$sub, $pos, $val )
{
	$b1 = ($val >>  0) & BIT8;
	$b2 = ($val >>  8) & BIT8;
	$b3 = ($val >> 16) & BIT8;
	$b4 = ($val >> 24) & BIT8;
	$sub[$pos+0] = chr($b1);
	$sub[$pos+1] = chr($b2);
	$sub[$pos+2] = chr($b3);
	$sub[$pos+3] = chr($b4);
	return;
}

function baksub( $fn, $isop, $pos, $siz, $sha1 )
{
	$sub = @file_get_contents($fn);
	if ( empty($sub) )
	{
		$sub = fp2str($isop, $pos, $siz);
		save_file($fn, $sub);
	}

	if ( sha1($sub) !== $sha1 )
		return php_error('baksub sha1 %s !== %s', sha1($sub), $sha1);
	return $sub;
}

function psxboot( $isop, &$list )
{
	$sys = isosearch($list, '/system.cnf');
	if ( $sys === -1 )
		return 0;

	$file = fp2str($isop, $sys['lba']*0x800, $sys['size']);
		$file = str_replace("\r", "\n", $file);
		$file = str_replace('\\', '/' , $file);

	foreach ( explode("\n",$file) as $line )
	{
		preg_match('|BOOT.*/([A-Z0-9\._]+);1|', $line, $m);
		//print_r($m);
		if ( ! empty($m) )
			return $m[1];
	} // foreach ( $file as $line )
	return 0;
}

function psx2( $fname )
{
	$isop = fopen($fname, 'rb+');
	if ( ! $isop )  return;

	$list = lsiso_r($isop);
	if ( empty($list) )  return;

	$slps = psxboot($isop, $list);
	if ( ! $slps )  return;

	global $gp_cheat;
	if ( ! isset( $gp_cheat[$slps] ) )
		return printf("slps %s not found\n", $slps);

	$f = $gp_cheat[$slps];
	$f($isop, $list);

	fclose($isop);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psx2( $argv[$i] );
