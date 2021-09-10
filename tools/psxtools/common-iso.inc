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

function isofile_r( $iso, &$sub, &$list, $par, $skip=0 )
{
	$func = __FUNCTION__;
	$size = strlen($sub);
	for ( $i=0; $i < $size; $i += 0x800 )
	{
		$j = 0;
		while ( $j < 0x800 )
		{
			$p = $i + $j;
			$len1 = ord( $sub[$p+ 0] );
			if ( $len1 == 0 ) // entry on next sector
				goto endj;

			$len2 = ord( $sub[$p+32] );
			if ( $len2 == 1 ) // for  . and ..
				goto endp;

			$lba = str2int($sub, $p+ 2, 4);
			$lsz = str2int($sub, $p+10, 4);
			$lfn = substr ($sub, $p+33, $len2);
				$lfn = strtolower($lfn);

			$lfg = ord( $sub[$p+25] );
			if ( $lfg & 2 ) // is_dir
			{
				$s = fp2str($iso, $skip+$lba*0x800, $lsz);
				$func($iso, $s, $list, "$par/$lfn");
				goto endp;
			}
			else // is_file
			{
				$s = substr($lfn, 0, strrpos($lfn, ';'));
				$list[] = array(
					'lba'  => $lba,
					'size' => $lsz,
					'file' => "$par/$s",
				);
				goto endp;
			}

endj:
			$j += 0x800;
			continue;
endp:
			$j += $len1;
			continue;

		} // while ( $j < 0x800 )
	} // for ( $i=0; $i < $size; $i += 0x800 )
	return;
}

function lsiso_r( $iso, $skip=0 )
{
	$s = fp2str($iso, $skip+0x8000, 0x800);
	if ( substr($s,1,5) !== 'CD001' )
		return '';

	$s = substr($s, 0x9c); // root dir
	$lba = str2int($s,  2, 4);
	$siz = str2int($s, 10, 4);
	$s = fp2str($iso, $skip+$lba*0x800, $siz);

	$list = array();
	isofile_r($iso, $s, $list, '', $skip);
	return $list;
}
//////////////////////////////
// frame = min:sec:frame binary format
//       = 3 byte BIN , as 00 02 00
//
// min   = frame in string format
//       = 6 char STR , as "000200"
//       = 8 char STR , as "00:02:00"
//
// lba   = int format
//       = 3 byte BIN , as 17 00 00
//       - INT        , as 0x17
//
// NOTE : 4 byte lba starts from offset 0x800000000
//        or (1 << (11+24))
//        or 32 GB (not used in CD/DVD)

function lba2frame( $lba )
{
	// frames starts at 00:02:00
	$lba += (75 * 2);

	$f = $lba % 75;
	$f = ( $f > 9 ) ? "$f" : "0$f";
		$lba = (int)($lba / 75);

	$s = $lba % 60;
	$s = ( $s > 9 ) ? "$s" : "0$s";
		$lba = (int)($lba / 60);

	$m = $lba;
	$m = ( $m > 9 ) ? "$m" : "0$m";
	return hex2bin( $m.$s.$f );
}

function frame2lba( $frame )
{
	$frame = bin2hex($frame);
	$m = (int)( $frame[0].$frame[1] );
	$s = (int)( $frame[2].$frame[3] );
	$f = (int)( $frame[4].$frame[5] );

	// frames starts at 00:02:00
	$lba = ($m * 60 + $s) * 60 + $f;
	$lba -= (75 * 2);
	return $lba;
}
