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
declare( strict_types=1 );

require 'tool.inc';

function save_sgfile( string $fn, string &$sub ) : void
{
	$fn = str_replace('.', '_', $fn);
	$fn = strtolower($fn);
	if ( substr($sub,0,4) == 'TEN2' )
		$fn .= '.ten2';

	tool::save($fn, $sub);
}
//////////////////////////////
function z2arc( string &$file, string $dir ) : void
{
	$len = strlen($file);
	$pos = 0;
	while (1)
	{
		$off1 = tool::ordstr($file, $pos+ 0, 3);
		$off2 = tool::ordstr($file, $pos+16, 3);
		if ( $off1 == 0 )
			return;
		if ( $off2 == 0 )
			$off2 = $len;

		$fn = tool::substr0($file, $pos+3);
			$pos += 16;

		$siz = $off2 - $off1;
		$sub = tool::substr($file, $off1, $siz);

		$s = "$dir/$fn";
		tool::trace($off1, $siz, $s);
		save_sgfile($s, $sub);
	} // while (1)
}

function r2arc( string &$file, string $dir ) : void
{
	if ( tool::substr0($file,8) !== 'Copyright by TENKY 1996' )
		return;

	$base = tool::ordstr($file, 0, 3);
	$cnt  = tool::ordstr($file, 4, 3);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x20 + ($i * 0x20);
		$fn = tool::substr0($file, $p+ 0);
		$b1 = tool::ordstr ($file, $p+16, 3);
		$b2 = tool::ordstr ($file, $p+20, 3);
			$ps = ($base + $b1) * 0x800;
			$sz =  $b2 * 0x800;

		$sub = tool::substr($file, $ps, $sz);
		$s = "$dir/$fn";
		tool::trace($ps, $sz, $s);

		if ( stripos($s, '.z2') !== false )
			z2arc($sub, $s);
		else
			save_sgfile($s, $sub);
	} // for ( $i=0; $i < $cnt; $i++ )
}

function suigai( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( stripos($fname, '.r2') !== false )
		r2arc($file, $fname);
	if ( stripos($fname, '.z2') !== false )
		z2arc($file, $fname);
}

for ( $i=1; $i < $argc; $i++ )
	suigai( $argv[$i] );
