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

function animtxt( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$anim = "$dir/anim.txt";
	if ( ! file_exists($anim) )
		return;

	foreach ( file($anim) as $line )
	{
		$line = preg_replace('|[\s]+|', '', $line);
		if ( empty($line) )
			continue;

		list($sub,$seq) = explode('=', $line);
		@mkdir("$dir/$sub", 0755, true);

		foreach ( explode(',', $seq) as $k => $v )
		{
			list($no,$fps) = explode('-', $v);
			if ( $fps == 0 )
				continue;

			$frn = sprintf('%s/%04d.png', $dir, $no);
			$ton = sprintf('%s/%s/%04d.png', $dir, $sub, $k);
			echo "COPY $frn -> $ton\n";
			copy($frn, $ton);
		} // foreach ( explode(',', $seq) as $k => $v )

	} // foreach ( file($anim) as $line )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	animtxt( $argv[$i] );
