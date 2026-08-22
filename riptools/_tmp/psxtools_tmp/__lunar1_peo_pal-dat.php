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
require 'lunar1.inc';

define('NO_TRACE', 1);

$gp_clut = '';

function lunar( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_clut;
	if ( stripos($fname, 'peo.pal') !== false )
	{
		$gp_clut = pal555($file);
		return;
	}

	if ( empty($gp_clut) )
		$gp_clut = grayclut(0x100);

	$dir = str_replace('.', '_', $fname);

	$id = 0;
	$st = 0;
	while (1)
	{
		$b1 = str2int($file, $st+0, 3);
		$b2 = str2int($file, $st+4, 3);
			$st += 4;

		if ( $b1 === 0 )
			return;
		if ( $b2 === 0 )
			$b2 = strlen($file);

		$sub = substr($file, $b1, $b2-$b1);
			$sub .= ZERO . ZERO . ZERO . ZERO;
		//$sub = lunar_decode($sub);

		$fn = sprintf('%s/%04d.dec', $dir, $id);
			$id++;
		save_file($fn, $sub);
	} // while (1)
	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar( $argv[$i] );

/*
RAM 80197000
	t1 = v0 + t6[ 644]
	v1 = v0 + t3[ 641]
	80024b8c  lbu  v0[ 4], 0(v1[80196b89])
	80024b98  sb   v0[ 4], 0(t1[80197010])
 */
