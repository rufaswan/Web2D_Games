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
require "common.inc";
require "common-iso.inc";

function ripxeno( $fp, &$sub, &$txt, &$pos, &$id, $cnt, $ez, $dir )
{
	$func = __FUNCTION__;
	$exts = array(
		'77647320' => '.wds',
		'73656473' => '.eds',
		'736d6473' => '.mds',
		'01120000' => '.tex',
		'00120000' => '.tex',
		'60010180' => '.str',
	);

	while ( $cnt > 0 )
	{
		$bak = $pos;
		$lba = str2int($sub, $pos+0, 3, true);
		if ( $lba < 0 ) // -1 == EOF
			return;

		$siz = str2int($sub, $pos+$ez[0], 4, true);
			$pos += $ez[1];

		$fn = sprintf("%s/%04d", $dir, $id);
			$id++;

		if ( $lba == 0 || $siz == 0 )
			continue;

		$cnt--;
		if ( $siz < 0 ) // -999 == is_dir
			$func($fp, $sub, $txt, $pos, $id, -$siz, $ez, $fn);
		else // is_file
		{
			$lba2 = str2int($sub, $pos, 3, true);
			if ( $lba == $lba2 )
				continue;

			$s = fp2str($fp, $lba*0x800, $siz);
			$e = '.bin';

			$b1 = substr($s, 0, 4);
			$b2 = ordint($b1);
			if ( $b2 > 0 && $b2 < 0x1000 )
				$e = sprintf(".%x", $b2);
			else
			{
				$b2 = bin2hex($b1);
				if ( isset( $exts[$b2] ) )
					$e = $exts[$b2];
			}
			$fn .= $e;
			if ( $e == '.str' )  $s = ZERO;

			$b1 = sprintf("%8x , %8x , %s\n", $bak, $bak+$ez[0], $fn);
			echo $b1;
			$txt .= $b1;
			save_file($fn, $s);
		}
	} // while ( $cnt > 0 )
	return;
}

function xeno( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$list = lsiso_r($fp);
	if ( empty($list) )  return;

	$dir = str_replace('.', '_', $fname);
	foreach ( $list as $f )
	{
		$ez = '';
		switch ( $f['file'] )
		{
			case '/x.exe': // JP demo from SLPS_012.35
				$ez = array(4,8);
				break;
			case '/slps_011.60': // JP Disc 1
			case '/slps_011.61': // JP Disc 2
			case '/slus_006.64': // US Disc 1
			case '/slus_006.69': // US Disc 2
				$ez = array(3,7);
				break;
		} // switch ( $f['file'] )

		if ( empty($ez) )
			continue;

		$txt  = sprintf("FILE = %s\n", $f['file']);
		$txt .= "TYPE = INT\n";

		$sub = fp2str($fp, $f['lba']*0x800, $f['size']);
		$pos = 0x804;
		$id  = 0;
		ripxeno($fp, $sub, $txt, $pos, $id, 9999, $ez, "$dir/cdrom");

		$txt = str_replace($dir, '', $txt);
		save_file("$dir/patch.txt", $txt);
	} // foreach ( $list as $f )

	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
