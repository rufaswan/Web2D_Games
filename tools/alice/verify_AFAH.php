<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
////////////////////////////////////////
function afatxt($txtfn)
{
	$pos = 8;
	$ext = array();
	$dir = array();
	$ent = array();
	foreach ( file($txtfn) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		list($off,$siz,$nam) = explode(',', $line);
		$off = hexdec($off);
		$siz = hexdec($siz);

		if ( isset( $ent[$nam] ) )
			echo "[dup] $nam\n";
		$ent[$nam] = 1;

		# check if file extracted
		if ( stripos($nam, ".dcf") )
			$nam .= ".QNT";

		if ( ! file_exists($nam) )
			echo "[err/del] $nam\n";

		$e = substr($nam, strrpos($nam, '.')+1);
		if ( ! isset( $ext[$e] ) )
			$ext[$e] = 0;
		$ext[$e]++;

		if ( stripos($nam, ".qnt") )
		{
			$png = str_ireplace(".qnt", ".png", $nam);
			if ( ! file_exists($png) )
				echo "[qnt2png] $png\n";
		}

		$sep = strrpos($nam, '/');
		$d = ( $sep ) ? substr($nam, 0, $sep) : '.';
		if ( ! isset( $dir[$d] ) )
			$dir[$d] = 0;
		$dir[$d]++;

		# check if file skipped
		if ( $off != $pos )
			echo "[skipped] $nam\n";
		$pos += $siz;

	}
	ksort($dir);
	foreach ( $dir as $k => $v )
		echo "dir $k , files $v\n";
	ksort($ext);
	foreach ( $ext as $k => $v )
		echo "ext $k , files $v\n";
	return;
}

afatxt( $argv[1] );
