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
 *   cmdpack - vb2rip
 *     Neill Corlett
 */
function pvab_def()
{
	// same options as ffmpeg
	return array(
		'ar' => 44100, // sample rate
		'ac' => 1,     // channels
		'in' => 0x10,  // interlace
	);
	// ac=2 , in = 0x4000
}

function pvabline( &$vab, &$p1, &$p2 )
{
	$len = int_ceil(strlen($vab), -0x10);
	$coef = array(
		array(  0 ,   0),
		array( 60 ,   0),
		array(115 , -52),
		array( 98 , -55),
		array(122 , -60),
	);
	$sra16 = (PHP_INT_SIZE - 2) * 8;

	$wav = '';
	for ( $i=0; $i < $len; $i += 0x10 )
	{
		$b = ord( $vab[$i] );
		$fil = $b >> 4;
		$mag = $b & BIT4;
		if ( $mag > 12 || $fil > 4 )
		{
			$mag = 12;
			$fil =  0;
		}

		for ( $j=2; $j < 0x10; $j++)
		{
			$b = ord( $vab[$i+$j] );
			$d1 = ((($b & 0x0f) << 12) << $sra16) >> $sra16;
			$d2 = ((($b & 0xf0) <<  8) << $sra16) >> $sra16;
				$d1 >>= $mag;
				$d2 >>= $mag;

			$b = ($p1 * $coef[$fil][0]) + ($p2 * $coef[$fil][1]);
			$c = int_clamp($d1 + ($b >> 6), -32768, 32767);
			$wav .= chrint($c, 2);
			$p2 = $p1;
			$p1 = $c;

			$b = ($p1 * $coef[$fil][0]) + ($p2 * $coef[$fil][1]);
			$c = int_clamp($d2 + ($b >> 6), -32768, 32767);
			$wav .= chrint($c, 2);
			$p2 = $p1;
			$p1 = $c;
		} // for ( $j=2; $j < 0x10; $j++)
	} // for ( $i=0; $i < $len, $i += 0x10 )

	return $wav;
}

function pvabblock( &$vab, &$vbop )
{
	$len = int_ceil(strlen($vab), -$vbop['in']*$vbop['ac']);
	$pos = 0;

	$p1a = 0;
	$p1b = 0;
	$p2a = 0;
	$p2b = 0;

	$wav_l = '';
	$wav_r = '';
	while ( $pos < $len )
	{
		$sub = substr($vab, $pos, $vbop['in']);
			$pos += $vbop['in'];
		$wav_l .= pvabline($sub, $p1a, $p1b);

		if ( $vbop['ac'] == 2 )
		{
			$sub = substr($vab, $pos, $vbop['in']);
				$pos += $vbop['in'];
			$wav_r .= pvabline($sub, $p2a, $p2b);
		}
	} // while ( $pos < $len )

	$wav = '';
	if ( $vbop['ac'] == 1 )
		$wav = $wav_l;
	else
	if ( $vbop['ac'] == 2 )
	{
		$len = strlen($wav_l);
		for ( $i=0; $i < $len; $i += 2 )
		{
			$wav .= $wav_l[$i+0] . $wav_l[$i+1];
			$wav .= $wav_r[$i+0] . $wav_r[$i+1];
		} // for ( $i=0; $i < $len; $i += 2 )
	}
	return $wav;
}

function save_wavefile( $fname, &$wav, &$vbop )
{
	$len = strlen($wav);
	$head = str_repeat(ZERO, 0x2c);

	str_update($head,    0, 'RIFF');
	str_update($head,    4, chrint($len + 0x24, 4));
	str_update($head,    8, 'WAVEfmt ');
	$head[0x10] = "\x10";
	$head[0x14] = "\x01"; // type format , 1 = PCM
	$head[0x16] = chr( $vbop['ac'] );
	str_update($head, 0x18, chrint($vbop['ar'], 4));
	str_update($head, 0x1c, chrint($vbop['ar'] * $vbop['ac'] * 2, 4));

	// bit/sample*ac/8 , 1 = 8 bit mono , 2 = 8 bit stereo/16 bit mono , 4 = 16 bit stereo
	$head[0x20] = ( $vbop['ac'] == 2 ) ? "\x04" : "\x02";

	$head[0x22] = "\x10"; // bit/sample
	str_update($head, 0x24, 'data');
	str_update($head, 0x28, chrint($len, 4));

	save_file($fname, $head.$wav);
	return;
}
