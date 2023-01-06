<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require 'common.inc';

function datfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$fname = strtolower($fname);
	$mat = array();
	preg_match('|([a-z])([a-z]+)\.([a-z]+)|', $fname, $mat);

	// 0=adisk.dat  1=a  2=disk  3=dat
	//print_r($mat);

	if ( $mat[3] !== 'dat' )
		return;

	$als = '-abcdefghijklmnopqrstuvwxyz';
	$fid = strpos($als, $mat[1]);

	$txt = '';
	$dir = $mat[2];

	$st = str2int($file, 0, 2);
	$ed = str2int($file, 2, 2);
		$st = ($st - 1) << 8;
		$ed = ($ed - 1) << 8;

	$id = 0;
	while ( $st < $ed )
	{
		$fdi_i = ord( $file[$st+0] );
		$fdi_d = ord( $file[$st+1] );
			$id++;
			$st += 2;

		// not on this disk
		if ( $fdi_i !== $fid )
			continue;

		// File EOC
		if ( $fdi_i === 0x1a )
			break;

		// get offset than calculate size
		$off1 = str2int($file, ($fdi_d + 0) * 2, 2);
		$off2 = str2int($file, ($fdi_d + 1) * 2, 2);
			$off1 = ($off1 - 1) << 8;
			$off2 = ($off2 - 1) << 8;
			$size = $off2 - $off1;

		$fn  = sprintf('%s/%04d.bin', $dir, $id-1);
		$log = sprintf("%8x , %8x , %s , %s\n", $off1, $size, $fn, $fname);

		$txt .= $log;
		echo $log;

		save_file($fn, substr($file, $off1, $size));
	} // while ( $st < $ed )

	save_file("$dir/{$mat[1]}.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	datfile( $argv[$i] );
