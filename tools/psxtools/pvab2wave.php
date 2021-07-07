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

function vab2wav( &$vbop, $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	echo "pVAB $fname\n";
	$wav = pvabblock($file, $vbop);
	save_wavefile("$fname.wav", $wav, $vbop);
	return 1;
}

$vbop = PVAB_DEF();
$i = 1;
while ( $i < $argc )
{
	if ( is_file($argv[$i]) )
		$i += vab2wav($vbop, $argv[$i]);
	else
	{
		switch( $argv[$i+0] )
		{
			case 'ar': // default 44100
			case '-ar':
				$vbop['ar'] = (int)$argv[$i+1];
				printf("SET sample rate %d\n", $vbop['ar']);
				$i += 2;
				break;
			case 'ac': // default 1 / mono
			case '-ac':
				$vbop['ac'] = (int)$argv[$i+1];
				printf("SET channels %d\n", $vbop['ac']);
				$i += 2;
				break;
			case 'in': // default 0x10
			case '-in':
				$vbop['in'] = hexdec($argv[$i+1]);
				printf("SET interlace %x\n", $vbop['in']);
				$i += 2;
				break;
			default:
				exit();
		} // switch( $argv[$i+0] )
	}
} // while ( $i < $argc )

/*
vb2rip
	-     ac     ar     in
	.8     2  44100   4000
	.msa   2    [10]  4000
	.vb2   2  44100  [fsiz]
	.xa2  [0] 44100     [4]

game note
	discworld 1  ac 1  ar 44100  in 10
	discworld 2  ac 1  ar 22050  in 10
 */
