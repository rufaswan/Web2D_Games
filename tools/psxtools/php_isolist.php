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

function isolist( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$detect = array(
		'iso' => 0,
		'cvm' => 0x1800,
		'cdi' => 0x4b000,
	);

	$dir = str_replace('.', '_', $fname);
	foreach ( $detect as $type => $skip )
	{
		$list = lsiso_r($fp, $skip);
		if ( empty($list) )
			continue;

		printf("DETECT %s , %x , %s\n", $type, $skip, $fname);
		$txt = '';
		foreach ( $list as $ent )
		{
			$buf = sprintf("%6x , %8x , %s\n", $ent['lba'], $ent['size'], $ent['file']);
			echo $buf;
			$txt .= $buf;

			$sub = fp2str($fp, $skip+$ent['lba']*0x800, $ent['size']);
			$fn  = sprintf('%s/iso/%s', $dir, $ent['file']);
			save_file($fn, $sub);
		} // foreach ( $list as $ent )
		save_file("$dir/iso-list.txt", $txt);

		$sub = fp2str($fp, $skip, 0x8000);
		if ( trim($sub,ZERO) !== '' )
			save_file("$dir/iso-boot.bin", $sub);

		$sub = fp2str($fp, $skip+0x8000, 0x800);
		$txt  = '';
		$txt .= sprintf("8008  %s\n", substr($sub,0x08 ,0x20)); // system identifier
		$txt .= sprintf("8028  %s\n", substr($sub,0x28 ,0x20)); // volume identifier
		$txt .= sprintf("80be  %s\n", substr($sub,0xbe ,0x80)); // volume set identifier
		save_file("$dir/iso-meta.txt", $txt);
		return;
/*
		$head = fp2str($fp, $skip+0x8000, 0x800);
		if ( substr($head, 1, 5) === 'CD001' )
		{
			printf("DETECT %s , %x , %s\n", $type, $skip, $fname);

			$list = lsiso_r($fp, $skip);
			$txt = '';
			foreach ( $list as $ent )
			{
				$buf = sprintf("%6x , %8x , %s\n", $ent['lba'], $ent['size'], $ent['file']);
				echo $buf;
				$txt .= $buf;

				$sub = fp2str($fp, $skip+$ent['lba']*0x800, $ent['size']);
				$fn  = sprintf('%s/%s', $dir, $ent['file']);
				save_file($fn, $sub);
			} // foreach ( $list as $ent )

			save_file("$dir/isolist.txt", $txt);
			return;
		}
*/
	} // foreach ( $detect as $type => $skip )
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	isolist( $argv[$i] );
