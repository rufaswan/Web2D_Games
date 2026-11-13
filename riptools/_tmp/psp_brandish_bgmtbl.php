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
declare( strict_types=1 );

require 'tool.inc';

function bgmtbl( string $dir ) : void
{
	if ( empty($dir) )
		return;
	if ( ! is_dir($dir) )
		return;

	$dir = rtrim($dir, '/\\');
	if ( ! is_file("$dir/bgmtbl.txt") )
		return;

	$file = file("$dir/bgmtbl.txt");
	foreach ( $file as $line )
	{
		$l = trim($line);
		if ( empty($l) )
			continue;
		$tkn = preg_split('|[\s]+|', $l);
		if ( $tkn[0] !== 'bgmtbl' )
			continue;

		$realname = $tkn[1];
		$filename = $tkn[2];
		$loop_cnt = (int)$tkn[3];
		$loop_st  = (int)$tkn[4];
		$loop_ed  = (int)$tkn[5];
		if ( $loop_cnt < 1 )
			continue;

		$at3 = "$dir/$filename.at3";
		if ( ! is_file($at3) )
			continue;

		$at3 = file_get_contents($at3);
		if ( substr($at3,0,4) !== 'RIFF' )
			continue;
		if ( substr($at3,8,8) !== 'WAVEfmt ' )
			continue;

		$sample_rate = tool::ordstr($at3, 0x18, 4);
		if ( $sample_rate < 1 )
			continue;

		$loop_st /= $sample_rate;
		$loop_ed /= $sample_rate;

		$txt  = sprintf("LOOP_START=%.6f\r\n", $loop_st);
		$txt .= sprintf("LOOP_END=%.6f\r\n", $loop_ed);
		tool::save("$dir/$realname.wav.loop", $txt);

		tool::copy("$dir/$filename.at3", "$dir/$realname.at3");
	} // foreach ( $file as $line )
}

if ( $argc > 1 )
	bgmtbl( $argv[1] );
