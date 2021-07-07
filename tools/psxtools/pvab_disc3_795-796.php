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

function discnoir( $idx, $smp )
{
	$idxp = file_get_contents($idx);
	$smpp = fopen($smp, 'rb');
	if ( empty($idxp) || ! $smpp )
		return;

	$dir = str_replace('.', '_', $smp);
	$siz = filesize($smp) >> 11;

	$len = strlen($idxp);
	$ids = array(
		array(-1,$siz),
	);
	for ( $i=0; $i < $len; $i += 3 )
	{
		$b = str2int($idxp, $i, 3);
		if ( $b === 0 )
			continue;
		$ids[] = array($i/3, $b);
	}
	// sort offset ascending
	usort($ids, function ($a,$b){ return ($a[1] > $b[1]); });

	$vbop = PVAB_DEF();
	$vbop['ac'] = 1;
	$vbop['ar'] = 11025;

	$len = count($ids) - 1;
	for ( $i=0; $i < $len; $i++ )
	{
		$id = $ids[$i][0];
		$of = $ids[$i][1] << 11;
		$sz = ($ids[$i+1][1] - $ids[$i][1]) << 11;

		$fn = sprintf("%s/%06d.wav", $dir, $id);
		printf("%8x , %8x , %s\n", $of, $sz, $fn);

		$b = fp2str($smpp, $of, $sz);
		$w = pvabblock($b, $vbop);
		save_wavefile($fn, $w, $vbop);
	} // for ( $i=0; $i < $len; $i++ )

	return;
}

printf("%s  795.bin  796.bin\n", $argv[0]);
if ( $argc != 3 )  exit();
discnoir( $argv[1] , $argv[2] );
