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

function getbits( &$bits, &$file, &$pos, $c )
{
	while ( count($bits) < $c )
	{
		$b = ord( $file[$pos] );
			$pos++;
		$i = 8;
		while ( $i > 0 )
		{
			$i--;
			$bits[] = ($b >> $i) & 1;
		}
	} // while ( count($bits) < $c )

	$int = 0;
	for ( $i=0; $i < $c; $i++ )
	{
		$int <<= 1;
		$int |= array_shift($bits);
	}
	return $int;
}

function wav_decode( &$wav, $size )
{
	$dec = '';
	trace("== begin sub_414c90()\n");

	$bits = array();

	$pos = 1;
	$b1  = $wav[$pos];
		$pos++;
	$dec = $b1;
		$last = ord($b1);
		$size--;

	while ( $size > 0 )
	{
		$flg = getbits($bits, $wav, $pos, 1);
		if ( $flg ) // 80-ff
		{
			//trace("%8x  %8x  by 80\n", $size, $pos);
			$cl = getbits($bits, $wav, $pos, 3); // ($flg & 0x70) >> 4;
			$al = getbits($bits, $wav, $pos, 4); //  $flg & 0x0f;
			$blen = $al + 1;
			$bbit = $cl + 1;
			for ( $i=0; $i < $blen; $i++ )
			{
				// 1 bit  0=-1  1=0   2=1   3=2
				// 2 bit  0=-3  1=-2  2=-1  3=0  4=1  5=2  6=3  7=4
				// ...
				$b1 = getbits($bits, $wav, $pos, $bbit);
				$b2 = (1 << $cl) - 1;

				$b = ($b1 - $b2) * 2;
				$last = ($last + $b) & BIT8;

				$dec .= chr($last);
				$size--;
			} // for ( $i=0; $i < $blen; $i++ )

			$b1 = getbits($bits, $wav, $pos, 2);
			$b  = $b1 - 1;
			$last = ($last + $b) & BIT8;
		}
		else
		{
			$flg = getbits($bits, $wav, $pos, 1);
			if ( $flg ) // 40-7f
			{
				//trace("%8x  %8x  by 40\n", $size, $pos);
				$al = getbits($bits, $wav, $pos, 6); // $flg & 0x3f;

				$dec  .= str_repeat(chr($last), $al);
				$size -= $al;
			}
			else // 00-3f
			{
				// 4295b8,4295bc
				$dict1 = array(
					array(1,15), // 0
					array(2,14), // 1
					array(4, 8), // 2
					array(6, 0), // 3
				);
				// 428d38 + 80
				$dict2 = array(
					-16,-15,-14,-13 , -12,-11,-10,-9 , -8,-7,-6,-5 , -4,-3,-2,-1 ,
					  1,  2,  3,  4 ,   5,  6,  7, 8 ,  9,10,11,12 , 13,14,15,16 ,
				);

				//trace("%8x  %8x  by --\n", $size, $pos);
				$dl = getbits($bits, $wav, $pos, 2); // ($flg & 0x30) >> 4;
				$al = getbits($bits, $wav, $pos, 4); //  $flg & 0x0f;
				$bdic = $dict1[$dl];

				$b = ($bdic[0] >> 1) + 2;
				$blen = getbits($bits, $wav, $pos, $b) + 1;

				for ( $i=0; $i < $blen; $i++ )
				{
					$b1 = getbits($bits, $wav, $pos, $bdic[0]);

					$b = $dict2[ $bdic[1] + $b1 ] * ($al + 1);
					$last = ($last + $b) & BIT8;

					$dec .= chr($last);
					$size--;
				} // for ( $i=0; $i < $blen; $i++ )
			}
		}
	} // while ( $size > 0 )

	trace("== end sub_414c90()\n");
	$wav = $dec;
	return;
}
//////////////////////////////
function save_wavefile( $fname, &$wave )
{
	$riff = str_repeat(ZERO, 0x2c);
	$len  = strlen($wave);

	str_update($riff, 0, 'RIFF');
	str_update($riff, 4, chrint($len + 0x24, 4));
	str_update($riff, 8, 'WAVEfmt ');

	$riff[0x10] = "\x10"; // length of format data
	$riff[0x14] = "\x01"; // type=pcm
	$riff[0x16] = "\x01"; // ac=1
	str_update($riff, 0x18, chrint(22050, 4)); // ar=22050
	str_update($riff, 0x1c, chrint(22050, 4)); // 22050*1*1

	$riff[0x20] = "\x01"; // 8 bit mono
	$riff[0x22] = "\x08"; // bit/sample
	str_update($riff, 0x24, 'data');
	str_update($riff, 0x28, chrint($len, 4));

	save_file($fname, $riff.$wave);
	return;
}

function tinytoon( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pos = 0;
	if ( substr($file,4,4) === substr($file,8,4) )
		$pos = 0x3e;

	$sz = str2int($file, $pos + 0, 4);
	$b2 = str2int($file, $pos + 4, 1);
	if ( $b2 !== 0x10 )
		return;

	$wav = substr($file, $pos + 6);
	$b1  = ord( $wav[0] );
	if ( ($b1 & 0x80) === 0 )
		wav_decode($wav, $sz);

	save_wavefile("$fname.wav", $wav);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	tinytoon( $argv[$i] );

/*
ebp+58 = inp
ebp+50 = outp

break 414e54 - op 80
break 414f99 - op 40
break 414fd3 - def
break 4150f4 - end loop
	22 56 -- --  10 -- -- 80  7f 7f 7f 7f
	=
	80 80 80 80  ...

	22 56 -- --  10 -- -- 87  -- c2 88 c5  22 48 b2 85
	22 4a 1c a2  ac a1 08 39  19 02 42 98  05 56 24 20
	=
	87 86 85 84  83 7f 81 7f  80 80 80 80  80 82 82 82
	80 82 82 82  82 82 82 82  82 84 84 84  85 84 82 81
	7f 80 80 7e  7c 7c 7c 7c  7c 7c 7e 7c  7d 7d 7e 7f
	7f 80 80 80  80 7e 7e 7c  7c 7c 7c 7c  7c 7c 7c 7c

ebp = 33f530
	inp  = 33f588 = 7b3e0000
	outp = 33f580 = 3b3f6000

168 = 33f698
8,c2  def  [87 56 -- --]
2,88  80    87 86 85 84  [83 -- -- 87]
7,48  40    83 7f 81 7f  [-- c2 88 c5]
7,b2  80    80 80 80 80  [22 48 b2 85]
1,85  40   [80 82 b2 85]
1,22  80    80 82 82 82  [22 4a 1c a2]
3,1c  40   [80 82 1c a2]
3,a2  80    80 82 82 82  [82 a1 08 39]
7,8   40    82 82 82 82  [82 84 42 98]
7,39  def   82 84 84 84  [05 56 24 20]
4,2   80    85 84 82 81  [07 90 64 49]
8,98  40   [7f 90 64 49]
8,5   80   [7f 80 80 49]
4,20  40    7f 80 80 7e   7c 7c 7c 7c  7c 7c 7e 7c  [66 42 -- 79]

--  def+0,0  ---- ---- , 11 , [- , - , - , -]
a2  80 +2,2  1-  1--- 1- , [--  1 , 1-- , -1-] , 1  -
44  40 +4    -1- --1-  -
91  80 +1,1  1-- 1---  1 , [-1 , 1 -] , -1
42  40 +2    -  1--- -1-
91  80 +1,1  1  --1- --1 , [-  - , 1-] , - 1
43  40 +3    -1-  ---1 1
94  80 +1,4  1--  1-1- - , [-1 , -  1 , -1 , - 1 , 1-] , -  1
42  40 +2    -1- ---1  -
10  def+1,0  --- 1---  - , -11 , [1- , -1 , -- , -1]
90  80 +1,0  1--1  ---- , [--] , 1-
42  40 +2    -1-- --1-
98  80 +1,8  1--1 1--- , [-- , -- , -1 , -1 , -1 , -1 , -1 , 1- , --] , 1-
42  40 +2    -1--  --1-
----
 */
