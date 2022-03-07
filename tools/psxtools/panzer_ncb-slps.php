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
define('SHA1_EXE', 'b4dbf191d529510cd8e45c57999312f8ef63b8cb');

function panzer( $dir )
{
	if ( empty($dir) )
		return;

	$ncb = load_file("$dir/kisindat.ncb");
	$exe = load_file("$dir/slps_008.99");
	if ( empty($ncb) || empty($exe) )
		return;

	$b1 = sha1($exe);
	if ( $b1 != SHA1_EXE )
		return php_error('PSX EXE sha1 not match = %s', $b1);

	$ed = 0x7434c;
	$st = 0x7242c;
	while ( $st < $ed )
	{
		$nam = substr0($exe, $st+0);
		$off = str2int($exe, $st+0x10, 4);
		$siz = str2int($exe, $st+0x14, 4);
			$st += 0x18;

		if ( empty($nam) )
			continue;
		$nam = strtolower($nam);
		printf("%8x , %8x , %s\n", $off, $siz, $nam);

		$sub = substr($ncb, $off, $siz);
		save_file("$dir/panzer/$nam", $sub);
	} // while ( $st < $ed )

/*
	// for *.ext only
	//if ( stripos($fname, '.ext') === false )
		//return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	//if ( substr($file, 0, 4) !== 'FILE' )
		//return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);
	// code template
*/
	return;
}

printf("%s  KISINDAT.NCB  SLPS_FILE\n", $argv[0]);
panzer( $argv[1] );
