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
function palbyte( &$pal )
{
	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 4 )
		$pal[$i+3] = BYTE;
	return;
}

function save_txt( &$file, &$sect, $dir )
{
	if ( empty($sect[1]) )
		return;
	printf("== save_txt( $dir )\n");

	foreach ( $sect[1] as $k => $v )
	{
		$ed = str2int($file, $v-4, 4);
		$st = $v;
		$txt = '';
		while ( $st < $ed )
		{
			$len = ord($file[$st]);
			$sub = substr($file, $st+1, $len);

			$st += (1 + $len);
			$txt .= "$sub\n";
		} // while ( $st < $ed )

		$fn = sprintf("$dir/%04d.txt", $k);
		save_file($fn, $txt);
	} // foreach ( $sect[1] as $k => $v )

	return;
}

function scnsect( &$file )
{
	$sect = array();
	$sect[1] = array();

	$pos = 0;
	while (1)
	{
		if ( substr($file,$pos+1,3) !== "\x0043" )
			return php_error("NOT 43 @ %x", $pos);

		$id = ord( $file[$pos] );
		$nx = str2int($file, $pos+4, 4);

		if ( $id == 1 )
			$sect[1][] = $pos + 8;
		else
		{
			if ( isset( $sect[$id] ) )
				return php_error("DUP 43 @ %x = %x", $pos, $id);
			$sect[$id] = $pos + 8;
		}

		$pos = $nx;
		if ( $pos == 0 )
			break;
	} // while (1)

	return $sect;
}
