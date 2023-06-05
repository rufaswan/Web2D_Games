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

function afsloop( $fp, $base, $pfx )
{
	$head = fp2str($fp, $base, 0x800);
	if ( substr($head,$base+0,4) !== "AFS\x00" )
		return false;

	$pos = 8;
	while (1)
	{
		$s = substr($head,$pos,4);
		if ( $s !== ZERO.ZERO.ZERO.ZERO )
			break;
		$pos += 8;
	} // while (1)

	$len  = str2int($head, $pos, 4);
	$cnt  = str2int($head, 4, 4);
	$head = fp2str($fp, $base, $len);

	$off = array();
	for ( $i=8; $i < $len; $i += 8 )
	{
		$p = str2int($head, $i+0, 4);
		if ( $p === 0 )
			continue;

		$s = str2int($head, $i+4, 4);
		$off[] = array(
			'id'  => ($i >> 3) - 1,
			'pos' => $p,
			'siz' => $s,
		);
	} // for ( $i=8; $i < $len; $i += 8 )

	printf("AFS = %x , has %x\n", $cnt, count($off));
	if ( isset($off[$cnt]) )
	{
		$toc = $off[$cnt];
		$sub = fp2str($fp, $base+$toc['pos'], $toc['siz']);
		foreach ( $off as $ok => $ov )
		{
			$p = $ov['id'] * 0x30;
			$n = substr0($sub, $p);
				$n = substr($n, 0, 0x20);
				$n = str_replace('/', '@', $n);
			$off[$ok]['fn'] = $n;
		} // foreach ( $off as $ok => $ov )
	}

	$func = __FUNCTION__;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$ov = $off[$i];
		if ( isset($ov['fn']) )
			$fn = sprintf('%s/%s', $pfx, $ov['fn']);
		else
			$fn = sprintf('%s/%08d.bin', $pfx, $ov['id']);

		printf("%8x , %8x , %s\n", $ov['pos'], $ov['siz'], $fn);
		$res = $func($fp, $base+$ov['pos'], $fn);
		if ( ! $res )
		{
			$sub = fp2str($fp, $base+$ov['pos'], $ov['siz']);
			save_file($fn, $sub);
		}
	} // for ( $i=0; $i < $cnt; $i++ )
	return true;
}

function afsfile( $fname )
{
	$fp = fopen_file($fname);
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);
	afsloop($fp, 0, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	afsfile( $argv[$i] );
