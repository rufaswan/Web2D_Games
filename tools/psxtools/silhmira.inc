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

function silhmira_poslen( &$dec, $dpos, $dlen )
{
	for ( $i=0; $i < $dlen; $i++ )
	{
		$dp = strlen($dec) - $dpos;
		$dec .= $dec[$dp];
	}
	return;
}

function silhmira_decode( &$file, $pos )
{
	$dec = '';
	trace("== begin sub_800ae284()\n");

	// ff cases , a = 4 , b = 8
	//   00-0f  outp-(a-2)   len 1-10
	//   10-1f  outp-(a-1)   len 1-10
	//   20-2f  outp-(a  )   len 1-10
	//   30-3f  outp-(a+1)   len 1-10
	//   40-4f  outp-(a+2)   len 1-10
	//   50-5f  outp-(b-1)   len 1-10
	//   60-6f  outp-(b  )   len 1-10
	//   70-7f  outp-(b+1)   len 1-10
	//   80     a = ([0] << 8) | [1] , b = a << 1
	//   81-9f  outp-[0]     len 2-20
	//   a0-bf  copy         len 1-20
	//   c0-cf  [0] dup      len 2-11
	//   d0-df  zero         len 1-10
	//   e0-ef  last dup 10  len 1*10-10*10
	//   f0-fe  last dup     len 1-f

	$p4 = 4;
	$p8 = 8;
	while (1)
	{
		$bycod = ord( $file[$pos] );
			$pos++;

		$by1 = $bycod >> 4;
		$by2 = $bycod & 0xf;
		switch ( $by1 )
		{
			case 0:  silhmira_poslen($dec, $p4 - 2, $by2 + 1); break;
			case 1:  silhmira_poslen($dec, $p4 - 1, $by2 + 1); break;
			case 2:  silhmira_poslen($dec, $p4    , $by2 + 1); break;
			case 3:  silhmira_poslen($dec, $p4 + 1, $by2 + 1); break;
			case 4:  silhmira_poslen($dec, $p4 + 2, $by2 + 1); break;

			case 5:  silhmira_poslen($dec, $p8 - 1, $by2 + 1); break;
			case 6:  silhmira_poslen($dec, $p8    , $by2 + 1); break;
			case 7:  silhmira_poslen($dec, $p8 + 1, $by2 + 1); break;

			case 8:
			case 9:
				$bycod -= 0x80;
				if ( $bycod === 0 )
				{
					$b1 = ord( $file[$pos+0] );
					$b2 = ord( $file[$pos+1] );
						$pos += 2;
					$p4 = ($b1 << 8) | $b2;
					$p8 = $p4 << 1;
					break;
				}
				else
				{
					$b1 = ord( $file[$pos] );
						$pos++;
					silhmira_poslen($dec, $b1, $bycod + 1);
					break;
				}

			case 10: // a
			case 11: // b
				$bycod -= 0xa0;
				$dec .= substr($file, $pos, $bycod + 1);
					$pos += ($bycod + 1);
				break;

			case 12: // c
				$b1 = $file[$pos];
					$pos++;
				$dec .= str_repeat($b1, $by2 + 2);
				break;

			case 13: // d
				$dec .= str_repeat(ZERO, $by2 + 1);
				break;

			case 14: // e
				$b1 = strlen($dec);
				$b2 = $dec[ $b1-1 ];
				$dec .= str_repeat($b2, ($by2 + 1) * 0x10);
				break;

			case 15: // f
				if ( $by2 === 0xf )
					goto done;

				$b1 = strlen($dec);
				$b2 = $dec[ $b1-1 ];
				$dec .= str_repeat($b2, $by2 + 1);
				break;

			default:
				goto done;
		} // switch ( $by1 )
	} // while (1)
done:
	trace("== end sub_800ae284()\n");
	return $dec;
}
//////////////////////////////
function silhmira_sint8( $c, $t )
{
	$c = ord($c);
	switch ( $t )
	{
		case 0: // uint8 , 0 - +255
		case 1: // uint8 , 0 - +255
			return $c;
		case 2: // sint8 , -127 - +127
			return sint8($c);
		case 3: // or ff00 , -255 - -1
			if ( $c === 0 )
				return 0;
			$c |= 0xff00;
			return sint16($c);
	} // switch ( $t )
	return $c;
}
//////////////////////////////
function silhmirabin( &$file )
{
	$len = strlen($file);
	$pal_off = -1;

	$b00 = str2big($file, 0, 4);
	$off = array($len);
	for ( $i=0; $i < $b00; $i += 4 )
	{
		$b1 = str2big($file, $i, 4);

		$off[] = $b1;
		$siz = $len - $b1;
		if ( $siz === 0x1e0 || $siz === 0x60 )
		{
			$pal_off = $b1;
			goto part2;
		}
	} // for ( $i=0; $i < $b00; $i += 4 )

	if ( $pal_off < 0 )
		return php_error('palette no found');

part2:
	sort($off);
	$spr = array();
	$anm = array();
	$hit = array();
	$pal = '';

	$cnt = count($off) - 1;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = $off[$i+0];
		$p2 = $off[$i+1];
		$s = substr($file, $p1, $p2-$p1);

		if ( $p1 === $pal_off )
		{
			printf("detect pal @ %x\n", $p1);
			$pal = pal555( big2little16($s) );
			continue;
		}

		$b1 = str2big($s, 0, 4);
		if ( $b1 === 0x0200020c || $b1 === 0x02000200 )
		{
			printf("detect anm @ %x\n", $p1);
			$anm[] = $s;
			continue;
		}
		if ( $b1 === 0x02000202 )
		{
			printf("detect hit @ %x\n", $p1);
			$hit[] = $s;
			continue;
		}

		printf("detect spr @ %x\n", $p1);
		$spr[] = $s;
	} // for ( $i=0; $i < $cnt; $i++ )

	$file = array($spr,$anm,$hit,$pal);
	return;
}
//////////////////////////////
