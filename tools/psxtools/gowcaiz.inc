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

function gowcaiz_decode( &$file )
{
	$dec = '';
	trace("== begin sub_80028960()\n");

	$dict = str_repeat(ZERO, 0x1000);
	$doff = 0xfee;

	$bycod = 0;
	$bylen = 0;

	$len = strlen($file);
	$pos = 4;

	while ( $pos < $len )
	{
		if ( $bylen === 0 )
		{
			$bycod = ord( $file[$pos] );
				$pos++;
			$bylen = 8;
			continue;
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		if ( $flg )
		{
			if ( ! isset($file[$pos]) )
				goto done;
			$b1 = $file[$pos];
				$pos++;

			$dict[$doff] = $b1;
				$doff = ($doff + 1) & 0xfff;
			$dec .= $b1;
		}
		else
		{
			if ( ! isset($file[$pos+1]) )
				goto done;
			$b1 = ord( $file[$pos+0] );
			$b2 = ord( $file[$pos+1] );
				$pos += 2;

			$dpos = (($b2 & 0xf0) << 4) | $b1;
			$dlen =  ($b2 & 0x0f) + 2;

			for ( $i=0; $i <= $dlen; $i++ )
			{
				$b1 = $dict[$dpos];
					$dpos = ($dpos + 1) & 0xfff;
				$dict[$doff] = $b1;
					$doff = ($doff + 1) & 0xfff;
				$dec .= $b1;
			} // for ( $i=0; $i <= $dlen; $i++ )
		}
	} // while ( $pos < $len )

done:
	trace("== end sub_80028960()\n");
	$file = $dec;
	return;
}

function secttmg( &$file )
{
	$cnt = str2int($file, 0, 2);

	$tmg = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$off = 4 + ($i * 0x10);

		$pos = str2int($file, $off + 0, 3);
		$siz = str2int($file, $off + 4, 2);
		$fnm = substr0($file, $off + 8);

		if ( isset( $tmg[$fnm] ) )
			return php_error('secttmg() duplicate %s at %x', $fnm, $off);

		$tim = substr($file, $pos, $siz);
		gowcaiz_decode($tim);

		$img = psxtim($tim);
		$img['pal'][3] = ZERO;

		$tmg[$fnm] = $img;
	} // for ( $i=0; $i < $cnt; $i++ )
	return $tmg;
}

function sectmeta( &$file )
{
	$meta = array();

	$ed = str2int($file, 4, 2);
	$st = 0;
	while ( $st < $ed )
	{
		$fnm = substr0($file, $st + 0);
		$pos = str2int($file, $st + 4, 2);
		$siz = str2int($file, $st + 8, 2);
			$st += 12;

		if ( $siz < 1 )
			continue;
		if ( isset( $meta[$fnm] ) )
			return php_error('sectmeta() duplicate %s at %x', $fnm, $st);

		$s = substr($file, $pos, $siz);
			$s = str_replace("\x1a", ' ', $s);
			$s = preg_replace('|[\s]+|', "\n", $s);
			$s = trim($s);

		$meta[$fnm] = $s;
	} // while ( $st < $ed )
	return $meta;
}
