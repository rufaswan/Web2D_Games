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
 *
 * Special Thanks
 *   vgmstream
 *   https://github.com/vgmstream/vgmstream/blob/master/src/meta/jstm.c
 *   https://github.com/vgmstream/vgmstream/blob/master/src/meta/jstm_streamfile.h
 *     hcs
 */
require 'common.inc';

function pcm_xor( &$wav )
{
	$len = strlen($wav);
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $wav[$i] );
		$b ^= 0x5a;
		$wav[$i] = chr($b);
	}
	return;
}

function jstm2wav( &$file )
{
	$pcm = substr($file, 0x20);
	pcm_xor($pcm);

	$ac = str2int($file, 4, 1);
	$ar = str2int($file, 8, 2);
	//$loop = str2int($file, 0x14, 4, true);

	// save as .wave
	$len = strlen($pcm);
	$head = str_repeat(ZERO, 0x2c);

	str_update($head,    0, 'RIFF');
	str_update($head,    4, chrint($len + 0x24, 4));
	str_update($head,    8, 'WAVEfmt ');
	$head[0x10] = "\x10";
	$head[0x14] = "\x01"; // type format , 1 = PCM
	$head[0x16] = chr( $ac );
	str_update($head, 0x18, chrint($ar, 4));
	str_update($head, 0x1c, chrint($ar * $ac * 2, 4));

	// bit/sample*ac/8 , 1 = 8 bit mono , 2 = 8 bit stereo/16 bit mono , 4 = 16 bit stereo
	$head[0x20] = ( $ac == 2 ) ? "\x04" : "\x02";

	$head[0x22] = "\x10"; // bit/sample
	str_update($head, 0x24, 'data');
	str_update($head, 0x28, chrint($len, 4));

	return $head.$pcm;
}

function jinguji( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$id  = 0;
	$pos = 0;
	$len = strlen($file);
	while ( $pos < $len )
	{
		if ( substr($file,$pos,4) !== 'JSTM' )
			return;

		$siz = str2int($file, $pos + 12, 4);
		$sub = substr ($file, $pos, $siz + 0x20);
		$fn  = sprintf('%s-%d.wav', $fname, $id);
		printf("%8x  %8x  %s\n", $pos, $siz, $fn);

		$wav = jstm2wav($sub);
		save_file($fn, $wav);

		$id++;
		$pos = int_ceil($pos + 0x20 + $siz, 0x1000);
	} // while ( $pos < $len )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jinguji( $argv[$i] );
