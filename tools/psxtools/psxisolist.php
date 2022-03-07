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
require 'common-iso.inc';

$gp_list = array();

function lba2min( $lba )
{
	$frame = lba2frame($lba);
	$f = bin2hex($frame);
	return $f[0].$f[1] . ':' . $f[2].$f[3] . ':' . $f[4].$f[5];
}

function sectent( &$str, $fp, $pos, $par )
{
	$ensz = ord( $str[$pos] );
	$ent  = substr($str, $pos, $ensz);

	// for . and ..
	$ln = str2int($ent, 0x20, 2);
	if ( $ln == 0x001 || $ln == 0x101 )
		return $ensz;

	$ln = ord( $ent[0x20] );
	$fn = substr($ent, 0x21, $ln);
		$fn = strtolower($fn);

	$ln = ( $ln & 1 ) ? $ln - 1 : $ln;
	// has XA flags
	if ( substr($ent, 0x28+$ln, 2) !== 'XA' )
		return $ensz;

	$lba = str2int($ent, 0x02, 4);
	$siz = str2int($ent, 0x0a, 4);
	$xa = ord( $ent[0x26+$ln] );

	//printf("== %6x , %8x , %2x , %s/%s\n", $lba, $siz, $xa, $par, $fn);
	// if dir
	global $gp_list;
	if ( $xa & 0x80 )
		sectdir($fp, $lba, $siz, "$par/$fn");
	else
	{
		$fn = substr($fn, 0, strrpos($fn, ';'));
		$gp_list[$lba] = array('s' => $siz, 'f' => "$par/$fn", 'm' => $xa);
	}

	return $ensz;
}

function sectdir( $fp, $lba, $size, $par )
{
	//printf("== %6x , %s\n", $lba, $par);
	while ( $size > 0 )
	{
		$base = $lba * 0x800;
		$ent  = fp2str($fp, $base, 0x800);
		$p = 0;
		while ( $p < 0x800 )
		{
			if ( ! isset($ent[$p]) || $ent[$p] == ZERO )
				break;
			$ensz = sectent($ent, $fp, $p, $par);
			$p += $ensz;
		}
		$lba++;
		$size -= 0x800;
	}
	return;
}
//////////////////////////////
function isofile( $fname )
{
	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	$cd = fp2str($fp, 0x8000, 0x800);
	if ( substr($cd, 1, 5) !== 'CD001' )
		return printf("%s is not an ISO 2048/sector file\n", $fname);

	global $gp_list;
	$gp_list = array();
	$dir = str_replace('.', '_', $fname);

	$st = str2int($cd, 0x9e, 4);
	$sz = str2int($cd, 0xa6, 4);
	sectdir($fp, $st, $sz, '');

	ksort($gp_list);
	$buf = '';
	foreach ( $gp_list as $k => $v )
	{
		$min = lba2min($k);
		$typ = 'M2  ';
		if ( $v['m'] & 0x40 )  $typ = 'CDDA';
		if ( $v['m'] & 0x30 )  $typ = 'M2F2'; // 0x10 Form 2 + 0x20 Interleaved
		$log = sprintf(
			"%6x , %8s , %8x , %8x , %s , %s\n",
			$k, $min, $k*0x800, $v['s'], $typ, $v['f']
		);
		echo $log;
		$buf .= $log;

		if ( $typ == 'M2  ' )
			$bin = fp2str($fp, $k*0x800, $v['s']);
		else
			$bin = ZERO;
		save_file("$dir/{$v['f']}", $bin);
	}
	save_file("$dir/isolist.txt", $buf);

	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	isofile( $argv[$i] );
