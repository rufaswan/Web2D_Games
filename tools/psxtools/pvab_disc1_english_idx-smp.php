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
require "pvab.inc";

function disc1( $idx, $smp )
{
	$idxp = file_get_contents($idx);
	$smpp = fopen($smp, 'rb');
	if ( empty($idxp) || ! $smpp )
		return;

	$dir = str_replace('.', '_', $smp);
	$len = strlen($idxp);

	$ids = array();
	for ( $i=0; $i < $len; $i += 4 )
	{
		$b = str2int($idxp, $i, 4);
		if ( $b === 0 )
			continue;
		$ids[ $i/4 ] = $b;
	}

	$vbop = PVAB_DEF();
	$vbop['ac'] = 1;
	$vbop['ar'] = 44100;

	foreach ( $ids as $k => $v )
	{
		$b = fp2str($smpp, $v, 0x10);
		$size = str2int($b, 0, 4);
		$skip = 4;

		if ( $size > 0xfffff )
		{
			$size = str2int($b, 4, 4);
			$skip = 8;
		}

		if ( $size > 0xfffff )
			continue;

		$fn = sprintf("%s/%06d.wav", $dir, $k);
		printf("%8x , %8x , %s\n", $v, $size, $fn);

		$b = fp2str($smpp, $v+$skip, $size);
		$w = pvabblock($b, $vbop);
		save_wavefile($fn, $w, $vbop);
	} // foreach ( $ids as $k => $v )

	return;
}

printf("%s  english.idx  english.smp\n", $argv[0]);
if ( $argc != 3 )  exit();
disc1( $argv[1] , $argv[2] );
