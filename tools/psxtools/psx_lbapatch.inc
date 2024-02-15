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

$gp_patch = array(
	'/gunner.exe'  => 'jp_gunner_heaven',
	'/slps_012.84' => 'jp_xmen_vs_sf',
	'/slps_019.15' => 'jp_msh_vs_sf',
	'/slps_023.68' => 'jp_marvel_vs_capcom',
);

function jp_gunner_heaven( $dir, &$iso )
{
	$patch  = patch_min4($dir, 'gunner.exe', 0xee400, 0xee430, $iso); // *.xa
	$patch .= patch_min4($dir, 'gunner.exe', 0xee1c8, 0xee1e4, $iso); // *cl.dat  load.bin
	$patch .= patch_min4($dir, 'load.bin', 0x13568, 0x13580, $iso); // *st.bin
	$patch .= patch_min4($dir, 'load.bin', 0x13588, 0x135a0, $iso); // *st2.bin
	return $patch;
}

function jp_xmen_vs_sf( $dir, &$iso )
{
	$patch = patch_minbyte8($dir, 'slps_012.84', 0x2dfdc, 0x2e844, $iso);
	return $patch;
}

function jp_msh_vs_sf( $dir, &$iso )
{
	$patch = patch_minbyte8($dir, 'slps_019.15', 0x14f70, 0x157d0, $iso);
	return $patch;
}

function jp_marvel_vs_capcom( $dir, &$iso )
{
	$patch = patch_minbyte8($dir, 'slps_023.68', 0x15de4, 0x16b44, $iso);
	return $patch;
}
