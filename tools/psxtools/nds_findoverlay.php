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
require 'nds.inc';

function ndsov( $dsram, $dsdir )
{
	$ram = file_get_contents($dsram);
	if ( empty($ram) )  return;

	$len = strlen($file);
	if ( $len != NDS_RAM && $len != NDSI_RAM )
		return php_error('%s not NDS/DSi size', $dsram);

	$buf = nds_ram($dsdir);
	$bin = load_file("$dsdir/arm9/overlay.bin");
	$len = strlen($bin);
	for ( $i=0; $i < $len; $i += 0x20 )
	{
		$ov_id  = str2int($bin, $i+0, 4);
		$ov_ram = str2int($bin, $i+4, 3);
		$ov_siz = str2int($bin, $i+8, 4);
		nds_overlay( $buf, $dsdir, $ov_id );

		$same = 0;
		for ( $j=0; $j < $ov_siz; $j++ )
		{
			$p = $ov_ram + $j;
			if ( $ram[$p] === $buf[$p] )
				$same++;
		} // for ( $j=0; $j < $ov_siz; $j++ )

		$perc = ($same * 100) / $ov_siz;
		if ( 50.0 > $perc )
			continue;
		printf("%6x = %04d.overlay [%.2f%%]\n", $ov_ram, $ov_id, $perc);
	} // for ( $i=0; $i < $len; $i += 0x20 )

	return;
}

printf("%s  RAM_FILE  NDS_DIR\n", $argv[0]);
if ( $argc != 3 )  exit();
if ( ! is_file($argv[1]) )  exit();
if ( ! is_dir ($argv[2]) )  exit();
ndsov( $argv[1] , $argv[2] );
