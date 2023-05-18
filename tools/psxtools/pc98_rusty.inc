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

function mag_decode( &$file, $w, $h, $pb1, $pb4, $pc )
{
	trace("== mag_decode( %x , %x , %x , %x , %x )\n", $w, $h, $pb1, $pb4, $pc);
	// https://github.com/46OkuMen/rusty/blob/master/mag.py
	$pix = array();
	$bycod = 0;
	$bylen = 0;
	$flgno = array(0 => 0, 0x80 => 0);

	$action = array_fill(0, $w/8, 0);
	$actpos = 0;

	$actdx = array(0,1,2,4, 0,1, 0,1,2, 0,1,2, 0,1,2,  0);
	$actdy = array(0,0,0,0, 1,1, 2,2,2, 4,4,4, 8,8,8, 16);

	$bak = $pb4;
	while ( $pb1 < $bak )
	{
		if ( $bylen === 0 )
		{
			$bycod = ord( $file[$pb1] );
				$pb1++;
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 0x80;
			$bycod <<= 1;
			$bylen--;

		$flgno[$flg]++;
		if ( $flg === 0 )
		{
			$act = ord( $file[$pb4] );
				$pb4++;
			$action[ $actpos ] ^= $act;
			//trace("%6x ACT[%d] ^ %2x\n", $pb4-1, $actpos, $act);
		}

		//trace("-- ACT %2x\n", $action[$actpos]);
		$by = array();
		$by[] = ($action[$actpos] >> 4) & BIT4;
		$by[] = ($action[$actpos] >> 0) & BIT4;
		$actpos = ($actpos + 1) % ($w/8);

		foreach ( $by as $b )
		{
			if ( $b == 0 )
			{
				//trace("---- COPY %x\n", $pc);
				if ( isset( $file[$pc+1] ) )
					$pix[] = substr($file, $pc, 2);
				else
					$pix[] = ZERO . ZERO;
				$pc += 2;
			}
			else
			{
				$p = ($actdy[$b] * $w/4) + $actdx[$b];
				//trace("---- REF  %x  [-%d,-%d]\n", $p, $actdx[$b], $actdy[$b]);
				$p = count($pix) - $p;
				if ( isset( $pix[$p] ) )
					$pix[] = $pix[$p];
				else
					$pix[] = ZERO . ZERO;
			}
		} // foreach ( $by as $b )

	} // while ( $pb1 < $bak )

	trace("flags [0]%x , [1]%x\n", $flgno[0], $flgno[0x80]);
	return implode('', $pix);
}
