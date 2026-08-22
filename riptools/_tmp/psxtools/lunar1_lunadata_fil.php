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

function lunadata( &$sub, $dir )
{
	$pos = 0;
	$id  = 0;
	if ( strpos($dir, '.snd') )
	{
		$dir = str_replace('.', '_', $dir);
		$len = strlen($sub);
		while ( $pos < $len )
		{
			//$typ = str2int($sub, $pos+0, 4);
			$siz = str2int($sub, $pos+4, 4);

			save_file("$dir/$id.bin", substr($sub, $pos+8, $siz));
			$id++;
			$pos += (8 + $siz);
		} // while ( $pos < $len )
		return;
	}

	// default
	save_file($dir, $sub);
	return;
}

function lunar1( $fname )
{
	// for lunadata.fil only
	if ( stripos($fname, 'lunadata.fil') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$st = 0x20;
	while (1)
	{
		$fn = substr0($file, $st+0);
		if ( empty($fn) )
			return;

		$fn = strtolower($fn);
		$of = str2int($file, $st+0x14, 4);
		$sz = str2int($file, $st+0x18, 4);
			$st += 0x20;

		printf("%6x , %8x , %8x , %s\n", $of, $of*0x800, $sz, $fn);
		$sub = substr($file, $of*0x800, $sz);
		lunadata($sub, "$dir/$fn");
	} // while (1)
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar1( $argv[$i] );
