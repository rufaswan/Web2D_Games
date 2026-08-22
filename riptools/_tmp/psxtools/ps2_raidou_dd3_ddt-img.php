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

function ddtimg_folder( &$ddt, $img, $pos, $cnt, $dir )
{
	$func = __FUNCTION__;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pnam = str2int($ddt, $pos + 0, 4);
		$pdir = str2int($ddt, $pos + 4, 4);
		$size = str2int($ddt, $pos + 8, 4, true);

		if ( $pnam > 0 )
			$name = $dir . '/' . substr0($ddt, $pnam);
		else
			$name = $dir;

		if ( $size < 0 )
			$func($ddt, $img, $pdir, -$size, $name);
		else
		{
			printf("%8x , %8x , %s\n", $pdir*0x800, $size, $name);
			$sub = fp2str($img, $pdir*0x800, $size);
			save_file($name, $sub);
		}

		$pos += 0xc;
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

function raidou_dd3( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$ddt =  load_file("$pfx.ddt", false);
	$img = fopen_file("$pfx.img", false);
	if ( empty($ddt) || empty($img) )
		return;

	$dir = sprintf('%s_ddt-img', $pfx);

	ob_start();
	ddtimg_folder($ddt, $img, 0, 1, $dir);
	$txt = ob_get_clean();

	$txt = str_replace("$dir/", '', $txt);
	save_file("$dir/list.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	raidou_dd3( $argv[$i] );
