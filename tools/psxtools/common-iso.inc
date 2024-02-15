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

function isofile_r( $isop, &$sub, &$list, $par, $skip=0 )
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

			// file = min 3 = 'A;1'
			// dir  = min 1 = 'A'
			$len2 = ord( $sub[$p+32] );
			$lfn  = substr($sub, $p+33, $len2);
			if ( $lfn === "\x00" || $lfn === "\x01" ) // for  . and ..
				goto endp;

			$lfn = strtolower($lfn);
			$lba = str2int($sub, $p+ 2, 4);
			$lsz = str2int($sub, $p+10, 4);

			$lfg = ord( $sub[$p+25] );
			if ( $lfg & 2 ) // is_dir
			{
				$s = fp2str($isop, $skip+$lba*0x800, $lsz);
				$func($isop, $s, $list, "$par/$lfn", $skip);
				goto endp;
			}
			else // is_file
			{
				$s = substr($lfn, 0, strrpos($lfn, ';'));
				$ent = array(
					'lba'  => $lba,
					'size' => $lsz,
					'file' => "$par/$s",
				);

				$pxa = $p + 0x21 + $len2;
				if ( $pxa & 1 )
					$pxa++;
				if ( ($p+$len1) > ($pxa+8) && substr($sub,$pxa+6,2) === 'XA' )
				{
					// 0d 55 XA = file
					// 8d 55 XA = dir
					// 25 55 XA = XA/STR file
					$ent['xa'] = ordint( $sub[$pxa+5] . $sub[$pxa+4] );
				}
				$list[] = $ent;
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

function lsiso_r( $isop, $skip=0 )
{
	$s = fp2str($isop, $skip+0x8000, 0x800);
	if ( substr($s,0,6) !== "\x01CD001" )
		return '';

	$s = substr($s, 0x9c); // root dir
	$lba = str2int($s,  2, 4);
	$siz = str2int($s, 10, 4);
	$s = fp2str($isop, $skip+$lba*0x800, $siz);

	$list = array();
	isofile_r($isop, $s, $list, '', $skip);
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
//
// mkisofs
//   -full-iso9660-filenames = 31 char
//   -max-iso9660-filenames  = 37 char (violates ISO9660)
//
// https://opensource.apple.com/source/xnu/xnu-344.32/bsd/isofs/cd9660/iso.h.auto.html
// https://problemkaputt.de/psxspx-cdrom-iso-file-and-directory-descriptors.htm
// XA flags
//   0001  Owner Read    (usually 1)
//   0004  Owner Execute (usually 1)
//   0010  Group Read    (usually 1)
//   0040  Group Execute (usually 1)
//   0100  World Read    (usually 1)
//   0400  World Execute (usually 1)
//   0800  IS_MODE2        (0=MODE1 or CD-DA, 1=MODE2)
//   1000  IS_MODE2_FORM2  (0=FORM1, 1=FORM2)
//   2000  IS_INTERLEAVED  (0=No, 1=Yes...?) (by file and/or channel?)
//   4000  IS_CDDA         (0=Data or ADPCM, 1=CD-DA Audio Track)
//   8000  IS_DIRECTORY    (0=File or CD-DA, 1=Directory Record)

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
	$lba = ($m * 60 + $s) * 75 + $f;
	$lba -= (75 * 2);
	return $lba;
}

function printframe( $frame )
{
	$m = ord( $frame[0] );
	$s = ord( $frame[1] );
	$f = ord( $frame[2] );
	return sprintf('%2x:%2x:%2x', $m, $s, $f);
}

function isosearch( &$list, $fname )
{
	foreach ( $list as $v )
	{
		if ( stripos($v['file'], $fname) !== false )
			return $v;
	}
	return -1;
}

function isolba( &$list, $lba )
{
	foreach ( $list as $v )
	{
		if ( $v['lba'] === $lba )
			return $v;
	}
	return -1;
}
