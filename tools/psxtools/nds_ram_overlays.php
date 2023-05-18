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
require 'nds.inc';

function ndsover( $dsram, $dsrom )
{
	$ram = file_get_contents($dsram);
	if ( empty($ram) )  return;

	$len = strlen($ram);
	if ( $len !== NDS_RAM && $len !== NDSI_RAM )
		return php_error('%s not NDS/DSi size', $dsram);

	$nds = new NDSList;
	$ntr = $nds->load($dsrom);
	if ( $ntr === -1 )
		return;

	if ( count($nds->list) < 2 ) // head.bin
		return;

	$txt = '';
	foreach ( $nds->list as $lk => $lv )
	{
		if ( $lv['ram'] < 0 )
			continue;

		$nds_sub = $nds->loadfile($lk);
		$ram_sub = substr($ram, $lv['ram'] & BIT24, $lv['siz']);
		$same = 0;
		for ( $i=0; $i < $lv['siz']; $i += 4 )
		{
			if ( substr($nds_sub,$i,4) === substr($ram_sub,$i,4) )
				$same += 4;
		}

		$log = sprintf('%5.1f  %8x  %s', $same * 100 / $lv['siz'], $lv['ram'], $lk);
		echo "$log\n";
		$txt .= "$log\n";
	} // foreach ( $nds->list as $lk => $lv )

	save_file("$dsrom.ram.txt", $txt);
	return;
}

printf("%s  RAM  NDS\n", $argv[0]);
if ( $argc !== 3 )  exit();
ndsover( $argv[1] , $argv[2] );
