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

function sortlba( $a, $b )
{
	return $a['lba'] - $b['lba'];
}

function iso_substr( $sect, &$sub, $pos, $len )
{
	$str = substr($sub, $pos, $len);
	if ( strlen($str) !== $len )
		$str = '_';

	$str = str_replace(' ', '_', $str);
	$str = rtrim($str, '_');

	if ( empty($str) )
		$str = '_';
	return "$sect $str\n";
}

function isolist( $fname )
{
	$fp = fopen_file($fname);
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
		usort($list, 'sortlba');

		printf("DETECT %s , %x , %s\n", $type, $skip, $fname);
		$txt = '';
		foreach ( $list as $ent )
		{
			if ( isset($ent['xa']) )
			{
				$min = lba2frame( $ent['lba'] );
				$buf = sprintf("%6x , %8s , %8x , %4x , %s\n", $ent['lba'], printframe($min), $ent['size'], $ent['xa'], $ent['file']);
				if ( $ent['xa'] & 0x7000 ) // cdda-inter-form2
					$sub = ZERO;
				else
					$sub = fp2str($fp, $skip+$ent['lba']*0x800, $ent['size']);
			}
			else
			{
				$buf = sprintf("%6x , %8x , %s\n", $ent['lba'], $ent['size'], $ent['file']);
				$sub = fp2str($fp, $skip+$ent['lba']*0x800, $ent['size']);
			}
			echo $buf;
			$txt .= $buf;

			$fn  = sprintf('%s/%s', $dir, $ent['file']);
			save_file($fn, $sub);
		} // foreach ( $list as $ent )
		save_file("$dir/__CDXA__/list.txt", $txt);


		if ( $skip > 0 )
		{
			$sub = fp2str($fp, 0, $skip);
			save_file("$dir/__CDXA__/$type.bin", $sub);
		}


		$sub = fp2str($fp, $skip, 0x8000);
		if ( trim($sub,ZERO) !== '' )
			save_file("$dir/__CDXA__/boot.bin", $sub);


		$sub = fp2str($fp, $skip+0x8000, 0x800);
		$txt  = '';
		$txt .= iso_substr('-sysid    ', $sub, 0x08 , 0x20);
		$txt .= iso_substr('-volid    ', $sub, 0x28 , 0x20);
		$txt .= iso_substr('-volset   ', $sub, 0xbe , 0x80);
		$txt .= iso_substr('-publisher', $sub, 0x13e, 0x80);
		$txt .= iso_substr('-preparer ', $sub, 0x1be, 0x80);
		$txt .= iso_substr('-appid    ', $sub, 0x23e, 0x80);
		$txt .= iso_substr('-copyright', $sub, 0x2be, 0x26);
		$txt .= iso_substr('-abstract ', $sub, 0x2e4, 0x24);
		$txt .= iso_substr('-biblio   ', $sub, 0x308, 0x25);
		save_file("$dir/__CDXA__/mkisofs.txt", $txt);
		return;
	} // foreach ( $detect as $type => $skip )
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	isolist( $argv[$i] );
