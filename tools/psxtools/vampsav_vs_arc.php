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

function subfile( &$file, $id, $fn )
{
	$p = $id * 8;
	$off = str2int($file, $p+0, 3);
	$siz = str2int($file, $p+4, 3);
	if ( $off === 0 || $siz === 0 )
		return;

	$off *= 0x800;
	$siz *= 0x800;
	printf("%6x , %8x , %8x , %s\n", $off >> 11, $off, $siz, $fn);

	$s  = substr($file, $off, $siz);
	save_file($fn, $s);
	return;
}

function char_sets( &$file, $dir )
{
	$chars = array(
		//                        800           61510
		'bulleta'        => array(0x24 , 0x25 , 0x26 , 0x27 , 0x28 , 0x29),
		'demitri'        => array(0x2a , 0x2b , 0x2c , 0x2d , 0x2e , 0x2f),
		'gallon'         => array(0x30 , 0x31 , 0x32 , 0x33 , 0x34 , 0x35),
		'victor'         => array(0x36 , 0x37 , 0x38 , 0x39 , 0x3a , 0x3b),
		'zabel'          => array(0x3c , 0x3d , 0x3e , 0x3f , 0x40 , 0x41),
		'morrigan'       => array(0x42 , 0x43 , 0x44 , 0x45 , 0x46 , 0x47),
		'anakaris'       => array(0x48 , 0x49 , 0x4a , 0x4b , 0x4c , 0x4d),
		'felicia'        => array(0x4e , 0x4f , 0x50 , 0x51 , 0x52 , 0x53),
		'bishamon'       => array(0x54 , 0x55 , 0x56 , 0x57 , 0x58 , 0x59),
		'aulbath'        => array(0x5a , 0x5b , 0x5c , 0x5d , 0x5e , 0x5f),
		'sasquatch'      => array(0x60 , 0x61 , 0x62 , 0x63 , 0x64 , 0x65),
		'q-bee'          => array(0x66 , 0x67 , 0x68 , 0x69 , 0x6a , 0x6b),
		'lei-lei'        => array(0x6c , 0x6d , 0x6e , 0x6f , 0x70 , 0x71),
		'lilith'         => array(0x72 , 0x73 , 0x74 , 0x75 , 0x76 , 0x77),
		'jedah'          => array(0x78 , 0x79 , 0x7a , 0x7b , 0x7c , 0x7d),

		'phobos'         => array(0x7e , 0x7f , 0x81 , 0x82 , 0x83 , 0x84),
		'pyron'          => array(0x87 , 0x88 , 0x89 , 0x8a , 0x8b , 0x8c),
		'dark gallon'    => array(0x8d , 0x8e , 0x8f , 0x90 , 0x91 , 0x92),
		'donovan'        => array(0x93 , 0x94 , 0x96 , 0x97 , 0x98 , 0x99),
		'oboro bishamon' => array(0x9c , 0x9d , 0x9f , 0xa0 , 0xa1 , 0xa2),
		'unk1'           => array(   0 , 0x80 ,    0 ,    0 , 0x85 , 0x86),
		'unk2'           => array(0xef , 0xf0 ,    0 ,    0 , 0x9a , 0x9b),
	);
	foreach ( $chars as $ck => $cv )
	{
		foreach ( $cv as $cvk => $cvv )
		{
			if ( $cvv === 0 )
				continue;
			$fn = sprintf('%s/char/%s/%d.bin', $dir, $ck, $cvk);
			subfile($file, $cvv, $fn);
		} // foreach ( $cv as $cvk => $cvv )
	} // foreach ( $chars as $ck => $cv )
	return;
}

function vampsav( $fname )
{
	// for *.arc only
	if ( stripos($fname, '.arc') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	$cnt = str2int($file, 0, 3) * 0x100;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$fn = sprintf('%s/%04d.bin', $dir, $i);
		subfile($file, $i, $fn);
	} // for ( $i=0; $i < $cnt; $i++ )

	char_sets($file, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	vampsav( $argv[$i] );
