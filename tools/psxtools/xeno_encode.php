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

function xeno_enc_dict( &$dict )
{
	$flag = 0;
	foreach ( $dict as $k => $v )
	{
		$f = ( isset($v[1]) ) ? 1 : 0;
		$flag |= ($f << $k);
	} // foreach ( $dict as $k => $v )

	$enc = chr($flag);
	foreach ( $dict as $k => $v )
		$enc .= $v;

	$dict = array();
	return $enc;
}

function xeno_encode( &$file )
{
	$len = strlen($file);
	$enc = chrint($len, 4);

	$dict = array();
	$pos = 0;
	while ( $pos < $len )
	{
		if ( count($dict) == 8 )
			$enc .= xeno_enc_dict($dict);

		// min length = 3
		$seek = substr($file, $pos, 3);
		$back = $pos - 0xfff;
		if ( $back < 0 )
			$back = 0;

		// check if decoded text has matching string
		if ( strpos($file, $seek, $back) >= $pos )
		{
			$dict[] = $file[$pos];
			$pos++;
		}
		else
		{
			// look-ahead for maximum length
			$dlen = 3;
			for ( $i=0; $i < 15; $i++ )
			{
				$seek = substr($file, $pos, $dlen+1);
				if ( strpos($file, $seek, $back) >= $pos )
					break;
				$dlen++;
			} // for ( $i=0; $i < 0x10; $i++ )

			// dict seek   = 0xfff;
			// dict length = 3 + 0xf
			$seek = substr($file, $pos, $dlen);
			$dpos = $pos - strpos($file, $seek, $back);

			$b = (($dlen-3) << 12) | $dpos;
			$dict[] = chrint($b, 2);
			$pos += $dlen;
		}
	} // while ( $pos < $len )

	if ( ! empty($dict) )
	{
		while ( count($dict) != 8 )
			$dict[] = ZERO;
		$enc .= xeno_enc_dict($dict);
	}
	return $enc;
}

function xeno( $ent )
{
	if ( is_dir($ent) )
	{
		$list = array();
		lsfile_r($ent, $list);

		$cnt = count($list);
		$st = 4 + ($cnt * 4) + 4;

		$head = str_repeat(ZERO, $st);
		str_update($head, 0, chrint($cnt,4));
		str_update($head, 4, chrint($st ,4));

		$body = '';
		foreach ( $list as $k => $v )
		{
			$file = file_get_contents($v);
			if ( empty($file) )
				continue;

			$lz = xeno_encode($file);
			while ( strlen($lz) % 4 )
				$lz .= ZERO;
			$body .= $lz;

			$sz = strlen($lz);
				$st += $sz;

			$hp = 8 + $k * 4;
			str_update($head, $hp, chrint($st,4));
		} // foreach ( $list as $k => $v )

		$head .= $body;
		save_file("$ent.enc", $lz);
	}
	if ( is_file($ent) )
	{
		$file = file_get_contents($ent);
		if ( empty($file) )  return;

		$lz = xeno_encode($file);
		save_file("$ent.enc", $lz);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
