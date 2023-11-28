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
require 'common-iso.inc';
require 'xeno.inc';

function xeno_isofile( &$list, $lba, $dir )
{
	foreach ( $list as $f )
	{
		if ( $f['lba'] === $lba )
			return $dir . $f['file'];
	}
	return -1;
}

function ripxeno( $fp, &$list, &$sub, &$pos, &$id, $cnt, $entsiz, $root, $dir )
{
	$func = __FUNCTION__;

	$txt = '';
	while ( $cnt > 0 )
	{
		$lba = str2int($sub, $pos + 0         , 3, true);
		$siz = str2int($sub, $pos + $entsiz[0], 4, true);

		$pos += $entsiz[1];
		$id++;
		$cnt--;

		if ( $lba < 0 ) // -1 == is EOF
			break;
		if ( $lba === 0 || $siz === 0 ) // is dummy
			continue;

		// -999 == is dir
		if ( $siz < 0 )
		{
			$fn = sprintf('%s/%04d', $dir, $id-1);
			$txt .= $func($fp, $list, $sub, $pos, $id, -$siz, $entsiz, $root, $fn);
			continue;
		}

		// lba > 0 && siz > 0 == is file
		$bak = $pos - $entsiz[1];
		$s   = fp2str($fp, $lba*0x800, $siz);

		$fn = xeno_isofile($list, $lba, $root);
		if ( $fn === -1 )
			$fn = sprintf('%s/%s/%04d.%s', $root, $dir, $id-1, detect_ext($s));

		$b1 = sprintf("%8x , %8x , %s\n", $bak, $bak + $entsiz[0], $fn);
		echo $b1;
		$txt .= $b1;

		save_file($fn, $s);
	} // while ( $cnt > 0 )
	return $txt;
}

function xeno_exe( &$list )
{
	foreach ( $list as $f )
	{
		switch ( $f['file'] )
		{
			// official releases
			case '/slps_011.60': // JP Disc 1
			case '/slps_011.61': // JP Disc 2
			case '/slus_006.64': // US Disc 1
			case '/slus_006.69': // US Disc 2
				return array($f, 3, 7);

			// JP/chinese hack by Agemo, bluerabit, focus, wooddoo
			//   ERROR : custom code in LBA area
			case '/omega___.__':
				return -1;

			// DEMOS
			case '/slps_012.35': // JP demo from Fushigi no Data Disc
				foreach ( $list as $exe )
				{
					if ( $exe['file'] === '/x.exe' )
						return array($exe, 4, 8);
				}
				return -1;

			case '/papx_900.22': // JP demo from Yoi Ko to Yoi Otona no. PlayStation Taikenban Vol.1
				foreach ( $list as $exe )
				{
					if ( $exe['file'] === '/psx_cd.exe' )
						return array($exe, 4, 8);
				}
				return -1;

			case '/slus_900.28': // US demo from Squaresoft on PlayStation 1998 Collector's CD Vol.1
				return array($f, 3, 7);
		} // switch ( $f['file'] )
	}
	return -1;
}

function xeno( $fname )
{
	$fp = fopen_file($fname);
	if ( ! $fp )  return;

	$list = lsiso_r($fp);
	if ( empty($list) )  return;

	$dir = str_replace('.', '_', $fname);

	$exe = xeno_exe($list);
	if ( $exe === -1 )
		return;

	$boot = fp2str($fp, 0, 0x8000);
	save_file("$dir/__CDXA__/boot.bin", $boot);


	$txt  = sprintf("FILE = %s\n", $exe[0]['file']);
	$txt .= "OFFSET = LBA\n";
	$txt .= "SIZE   = BYTE\n";

	$sub = fp2str($fp, $exe[0]['lba']*0x800, $exe[0]['size']);
	$pos = 0x804;
	$id  = 0;
	$txt .= ripxeno($fp, $list, $sub, $pos, $id, 99999, array($exe[1],$exe[2]), $dir, 'cdrom');

	$txt = str_replace($dir, '', $txt);
	save_file("$dir/__CDXA__/patch.txt", $txt);

	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
