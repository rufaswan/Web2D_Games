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

Linux ONLY , required exec() command
	- stty
	- tput
 */
require 'common.inc';

$gp_fps = array();
$gp_opt = array(
	'pos'  => 0,
	'col'  => 0x10,
	'size' => 1,
);

function hexdigit( $int )
{
	$hx = 0;
	while ( $int > 0 )
	{
		$int >>= 4;
		$hx++;
	}
	return $hx;
}
//////////////////////////////
function term_opt( $hx, $cnt_fps )
{
	$tx = exec('tput cols');
	$ty = exec('tput lines');

	$fps_area = (int)(($tx - $hx) / $cnt_fps);
	$byread = (int)(($fps_area - 2) / 3);

	// STDIN on newline
	return array($tx, $ty-1, $byread);
}

function fp_read_hex( $fp, $off, $len )
{
	$txt = ' |';
	fseek($fp, $off, SEEK_SET);
	$sub = fread($fp, $len);

	for ( $i=0; $i < $len; $i++ )
	{
		if ( ! isset( $sub[$i] ) )
		{
			$txt .= '   ';
			continue;
		}

		if ( $sub[$i] === ZERO )
		{
			$txt .= ' --';
			continue;
		}

		$txt .= sprintf(' %2x', ord($sub[$i]));
	} // for ( $i=0; $i < $len; $i++ )
	return $txt;
}

function hexview()
{
	global $gp_fps, $gp_opt;

	$cnt_fps = count($gp_fps);
	if ( $cnt_fps < 1 )
		return;
	system('stty cbreak -echo');

	$hx = hexdigit($gp_opt['size']);
	$is_done = false;

	// xxxx | aa bb cc | aa bb cc | aa bb cc
	// xxxx | aa bb cc | aa bb cc | aa bb cc
	while ( ! $is_done )
	{
		list($tx,$ty,$byread) = term_opt($hx, $cnt_fps);
		if ( $byread > $gp_opt['col'] )
			$byread = $gp_opt['col'];

		ob_start();
		for ( $y=0; $y < $ty; $y++ )
		{
			$sy = $gp_opt['pos'] + ($y * $gp_opt['col']);
			printf("%{$hx}x", $sy);

			foreach ( $gp_fps as $fp )
				echo fp_read_hex($fp[1], $sy, $byread);

			echo "\n";
		} // for ( $y=0; $y < $ty; $y++ )

		echo ob_get_clean();

		// input handling
		// script idle until get input
		$in = fgetc(STDIN);
		switch ( $in )
		{
			case  '':  break;
			case '+':  $gp_opt['col']++; break;
			case '-':  $gp_opt['col']--; break;

			case 'w':  $gp_opt['pos'] -= $gp_opt['col']; break;
			case 's':  $gp_opt['pos'] += $gp_opt['col']; break;

			case '/':  $gp_opt['pos'] -= ($gp_opt['col'] << 8); break;
			case '*':  $gp_opt['pos'] += ($gp_opt['col'] << 8); break;

			case 'a':  $gp_opt['pos']--; break;
			case 'd':  $gp_opt['pos']++; break;

			case 'q':  $is_done = true; break;
		} // switch ( $in )

		// sanity check
		if ( $gp_opt['col'] < 1 )  $gp_opt['col'] = 1;
		if ( $gp_opt['pos'] < 0 )  $gp_opt['pos'] = 0;
		if ( $gp_opt['pos'] >= $gp_opt['size'] )
			$gp_opt['pos'] = $gp_opt['size'] - 1;
	} // while ( ! $is_done )

	system('stty -cbreak echo');
	foreach ( $gp_fps as $fp )
		fclose( $fp[1] );
	return;
}
//////////////////////////////
function parse_options( $argv, $i )
{
	global $gp_fps, $gp_opt;
	switch ( $argv[$i] )
	{
		case '-p':
		case '--pos':
			$gp_opt['pos'] = hexdec($argv[$i+1]);
			return 2;

		case '-c':
		case '--col':
			$gp_opt['col'] = hexdec($argv[$i+1]);
			return 2;

		default:
			if ( ! is_file( $argv[$i] ) )
				break;
			$fsz = filesize($argv[$i]);
			if ( $fsz < 1 )
				break;
			$fp = fopen($argv[$i], 'rb');
			if ( ! $fp )
				break;

			$gp_fps[] = array($argv[$i], $fp);
			if ( $gp_opt['size'] < $fsz )
				$gp_opt['size'] = $fsz;
			return 1;
	} // switch ( $argv[$i] )
	return 1;
}

$i = 1;
while ( $i < $argc )
	$i += parse_options($argv, $i);

hexview();
/*
stty
	 cbreak == -icanon
	-cbreak ==  icanon
 */
