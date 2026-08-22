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

function setvar( &$out, $pos, $var, $byte=0 )
{
	if ( "$var" === $var )
	{
		$byte = strlen($var);
		for ( $i=0; $i < $byte; $i++ )
			$out[$pos+$i] = $var[$i];
		return;
	}

	for ( $i=0; $i < $byte; $i++ )
	{
		$c = $var & 0xff;
			$var >>= 8;
		$out[$pos+$i] = chr($c);
	}
	return;
}

function at3head( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// https://github.com/dcherednik/atracdenc/blob/master/src/at3.cpp
	//   libnetmd            : #define NETMD_RIFF_FORMAT_TAG_ATRAC3 0x270
	//   mmreg.h (mingw-w64) : WAVE_FORMAT_SONY_SCX                 0x270
	//   riff.c  (ffmpeg)    : AV_CODEC_ID_ATRAC3                   0x0270
	//
	//  0 4  'RIFF'
	//  4 4  size            = LEN + 0x34
	//  8 8  'WAVEfmt '
	// 10 4  size            = 0x20
	// 14 2  format          = 0x270
	// 16 2  channel         = 2
	// 18 4  sample rate     = 48000
	// 1c 4  byte rate       = 0xc0 * 48000 / 1024 = 9000
	// 20 2  block align     = 0xc0
	// 22 2  bit per sample  = 0
	// 24 2  extra data size = 14
	// 26 2  =1
	// 28 4  byte per frame  = 0x1000
	// 2c 2  coding mode     = 1/joint stereo
	// 2e 2  coding mode     = 1/joint stereo
	// 30 2  =1
	// 32 2  =0
	// 34 4  'data'
	// 38 4  size            = LEN
	// 3c FILE
	$len = strlen($file);

	$out = str_repeat("\x00", 0x3c);
	setvar($out, 0x00, 'RIFF');
	setvar($out, 0x04, $len + 0x34, 4);

	setvar($out, 0x08, 'WAVEfmt ');
	setvar($out, 0x10,   0x20, 4);
	setvar($out, 0x14,  0x270, 2); // format
	setvar($out, 0x16,      2, 2); // channel
	setvar($out, 0x18,  48000, 4); // sample
	setvar($out, 0x1c,   9000, 4); // byte
	setvar($out, 0x20,   0xc0, 2); // block
	setvar($out, 0x22,      0, 2); // bit/sample

	setvar($out, 0x24,     14, 2); // extra size
	setvar($out, 0x26,      1, 2);
	setvar($out, 0x28, 0x1000, 4); // byte/frame
	setvar($out, 0x2c,      1, 2); // 1=joint stereo
	setvar($out, 0x2e,      1, 2); // 1=joint stereo
	setvar($out, 0x30,      1, 2);
	setvar($out, 0x32,      0, 2);

	setvar($out, 0x34, 'data');
	setvar($out, 0x38, $len, 4);
	$out .= $file;

	file_put_contents("$fname.wav", $out);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	at3head( $argv[$i] );

/*
https://hcs64.com/mboard/forum.php?showthread=37482
https://wiki.multimedia.cx/index.php/ATRAC3

https://wiki.multimedia.cx/index.php/ATRAC3plus
	Streams coded with ATRAC3plus are usually stored either in the WAV container (those files have the ".at3" extension though) or in the Sony's proprietary Oma/Omg container. In the case of the WAV container the undocumented GUID:
		E923AABF-CB58-4471-A119-FFFA01E4CE62
	is used in order to indicate the ATRAC3plus codec.
 */
