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
function cmpfile( &$list, $act )
{
	foreach ( $list as $lk => $lv )
	{
		$cnt = count($lv);
		if ( $cnt < 2 )
			continue;

		for ( $i=0; $i < ($cnt-1); $i++ )
		{
			if ( ! file_exists($lv[$i]) )
				continue;
			if ( is_link($lv[$i]) )
				continue;

			for ( $j=$i+1; $j < $cnt; $j++ )
			{
				if ( ! file_exists($lv[$j]) )
					continue;
				if ( is_link($lv[$j]) )
					continue;

				$b1 = file_get_contents($lv[$i]);
				$b2 = file_get_contents($lv[$j]);
				if ( $b1 == $b2 )
				{
					printf("SAME %s == %s\n", $lv[$i], $lv[$j]);
					switch ( $act )
					{
						case 'rm':
							unlink ($lv[$j]);
							break;
						case 'ln':
							unlink ($lv[$j]);
							symlink($lv[$i], $lv[$j]);
							break;
					} // switch ( $act )
				}
				else
					printf("DIFF %s != %s\n", $lv[$i], $lv[$j]);
			} // for ( $j=$i+1; $j < $cnt; $j++ )
		} // for ( $i=0; $i < ($cnt-1); $i++ )
	} // foreach ( $list as $lk => $lv )

	return;
}

printf("%s  [-rm/-ln]  FILE  FILE...\n", $argv[0]);
if ( $argc < 3 )  exit();

$list = array();
$act  = '';
for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( $opt == '-rm' )  { $act = 'rm'; continue; }
	if ( $opt == '-ln' )  { $act = 'ln'; continue; }
	if ( is_dir ($opt) )  continue;
	if ( is_link($opt) )  continue;

	$sz = filesize($opt);
	if ( ! isset($list[$sz]) )
		$list[$sz] = array();
	$list[$sz][] = $opt;
}
cmpfile($list, $act);
