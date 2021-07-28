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
function lba2frame( $lba )
{
	$lba += (75 * 2); // +2 sec

	$f = $lba % 75;
	$lba = ($lba - $f) / 75;

	$s = $lba % 60;
	$lba = ($lba - $s) / 60;

	$m = $lba;
	$cd = sprintf("%02d:%02d:%02d", $m, $s, $f);
	return $cd;
}

function isofile_r( $iso, &$sub, &$list, $par )
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
				$s = fp2str($iso, $lba*0x800, $lsz);
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

function lsiso_r( $iso )
{
	$s = fp2str($iso, 0x8000, 0x800);
	if ( substr($s,1,5) !== 'CD001' )
		return '';

	$s = substr($s, 0x9c); // root dir
	$lba = str2int($s,  2, 4);
	$siz = str2int($s, 10, 4);
	$s = fp2str($iso, $lba*0x800, $siz);

	$list = array();
	isofile_r($iso, $s, $list, '');
	return $list;
}
