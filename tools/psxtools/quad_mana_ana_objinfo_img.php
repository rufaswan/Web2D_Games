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
require 'common-json.inc';
require 'class-atlas.inc';
require 'quad.inc';
require 'quad_mana.inc';

function infoimg( &$file, $pfx )
{
	$b14 = str2int($file, 0x14, 3); // spr  list
	$b18 = str2int($file, 0x18, 3); // meta list
	$b1c = str2int($file, 0x1c, 3); // tim  list

	$tim = array();
	$ed = str2int($file, $b1c, 3);
	for ( $i=0; $i < $ed; $i += 4 )
	{
		$p1 = str2int($file, $b1c + $i, 3);
		$t = psxtim($file, $b1c + $p1);
		$tim[] = $t;
	} // for ( $i=0; $i < $ed; $i += 4 )

	$meta = array();
	$ed = str2int($file, $b18, 3) - 4;
	for ( $i=0; $i < $ed; $i += 4 )
	{
		$p1 = str2int($file, $b18 + $i + 0, 3);
		$p2 = str2int($file, $b18 + $i + 4, 3);
		$meta[] = substr($file, $b18 + $p1, $p2 - $p1);
	}

	$cnt = str2int($file, $b14 + 6, 2);
	if ( $cnt !== count($meta) )
		return php_error('b14 cnt %x !== meta[] %x', $cnt, count($meta));

	$bak = array();
	foreach ( $meta as $mk => $mv )
	{
		$p1 = 8 + ($mk * 4);
		$p2 = str2int($file, $b14 + $p1, 3);
		$p3 = $b14 + $p2 + 2;

		$ids = array();
		while ( $file[$p3] !== BYTE )
		{
			$t = ord( $file[$p3] );
				$p3++;
			$ids[] = $tim[$t];
		}
		if ( empty($ids) )
			$ids = $bak;

		$fn = sprintf('%s-%d', $pfx, $mk);
		sectmeta($mv, $ids, $fn);
		$bak = $ids;
	}
	return;
}

function objimg( &$file, $pfx )
{
	$ed = str2int($file, 0, 3) - 4;
	$st = 0;
	$tim = array();
	while ( $st < $ed )
	{
		$pos = str2int($file, $st, 3);
			$st += 4;
		$t = psxtim($file, $pos);
		$tim[] = $t;
	} // while ( $st < $ed )

	$pos  = str2int($file, $ed, 3);
	$meta = substr ($file, $pos);
	return sectmeta($meta, $tim, $pfx);
}

function mana( $fname )
{
	// for ANA/*/*.IMG only
	if ( stripos($fname, ".img") == false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// 08-10 for ANA/*_*OBJ/*.IMG
	// 28    for ANA/*INFO*/*.IMG
	$mgc = str2int($file, 0, 4);
	if ( $mgc === 0x28 )
		infoimg($file, $fname);
	else
		objimg($file, $fname);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
cid 12
	esl_bs00_img
	wal_drm1_img
	end_0001_img
	end_0002_img
	jul_bs32_img
	mhm_gdn2_img
	jgl_e120_img
	rui_e050_img
	lak_bs10_img
	lak_e040_img
	wal_b010_img
	wal_drm0_img
	wal_tmpl_img
	wal_way0_img
cid 13
	mhm_gdn2_img
cid 14
	mhm_gdn2_img
tid ff
	jul_bss4.img
	min_bs00.img
	sea_bs10.img

1-1-3  186,213
m#888888   + i#606040 = r#e8e8c8 (in-game , additive blending)
m#ffffff88 + i#606040 = r#b4b4a5 (test as alpha)
 */
