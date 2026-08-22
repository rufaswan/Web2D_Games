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

function get_range( string $range ) : array
{
	$r = [
		'pos' => 0,
		'len' => 0,
	];
	if ( strpos($range, '-') !== false )
	{
		list($b0,$b1) = explode('-', $range);
		$r['pos'] = hexdec($b0);
		$r['len'] = hexdec($b1) - $pos;
	}
	if ( strpos($range, '=') !== false )
	{
		list($b0,$b1) = explode('=', $range);
		$r['pos'] = hexdec($b0);
		$r['len'] = hexdec($b1);
	}
	if ( $r['pos'] < 0 || $r['len'] < 1 )
		return [];
	return $r;
}

function partcopy( string $fname, string $range, bool $txt ) : void
{
	$r = get_range($range);
	if ( empty($r) )
		return;
	printf("%s( %s , %x , %x ) = %x\n", __FUNCTION__, $fname, $r['pos'], $r['pos']+$r['len'], $r['len']);

	if ( empty($fname) )
		return;
	$fp = fopen($fname, 'rb');
	fseek($fp, $r['pos'], SEEK_SET);
	$str = fread($fp, $r['len']);

	$len = strlen($str);
	if ( $len < 1 )
		return;

	$fn = sprintf('%s.%x', $fname, $r['pos']);
	if ( $txt )
	{
		$b = '';
		for ( $i=0; $i < $len; $i++ )
		{
			if ( $str[$i] === "\x00" )
				$b .= '-- ';
			else
				$b .= sprintf('%2x ', ord($str[$i]));
		}
		file_put_contents($fn, $b);
	}
	else
		file_put_contents($fn, $str);
	return;
}

echo <<<_MSG
{$argv[0]}  [option]  FILE [range...]  [FILE [range...]]...

option:
  -txt  : output in hex
  -bin  : output in binary [default]

range
  10-80 : copy bytes from offset 0x10 to 0x80 , length 0x70
  10=70 : copy 0x70 bytes starting from offset 0x10

_MSG;

$txt   = false;
$fname = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-txt':  $txt = true ; break;
		case '-bin':  $txt = false; break;
		default:
			if ( is_file($argv[$i]) )
				$fname = $argv[$i];
			else
				partcopy( $fname, $argv[$i], $txt );
			break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )

