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
require 'class-pixlines.inc';
require 'quad.inc';

php_req_extension('json_decode', 'json');

define('S4_0', 1 << 0); // 1
define('S4_DISABLE_DSTQUAD', 1 << 1);
define('S4_DISABLE_SRCQUAD', 1 << 2);
define('S4_DISABLE_FOGQUAD', 1 << 3);
define('S4_4', 1 << 4); // 10
define('S4_5', 1 << 5);
define('S4_6', 1 << 6);
define('S4_7', 1 << 7);

define('S6_0', 1 << 0); // 1
define('S6_1', 1 << 1);
define('S6_DISABLE_BLEND', 1 << 2);
define('S6_3',  1 << 3);
define('S6_4', 1 << 4); // 10
define('S6_5', 1 << 5);
define('S6_6', 1 << 6);
define('S6_7', 1 << 7);

define('S8_FLIP_X', 1 << 0); // 1
define('S8_FLIP_Y', 1 << 1);
define('S8_2', 1 << 2);
define('S8_3', 1 << 3);
define('S8_4', 1 << 4); // 10
define('S8_5', 1 << 5);
define('S8_6', 1 << 6);
define('S8_7', 1 << 7);

define('S8_8', 1 << 8); // 100
define('S8_9', 1 << 9);
define('S8_10', 1 << 10);
define('S8_11', 1 << 11);
define('S8_12', 1 << 12); // 1000
define('S8_13', 1 << 13);
define('S8_14', 1 << 14);
define('S8_15', 1 << 15);

define('S8_16', 1 << 16); // 10000
define('S8_17', 1 << 17);
define('S8_18', 1 << 18);
define('S8_19', 1 << 19);
define('S8_20', 1 << 20); // 100000
define('S8_21', 1 << 21);
define('S8_22', 1 << 22);
define('S8_23', 1 << 23);
//////////////////////////////
function list_pad( &$list, $id )
{
	$id = (int)$id;
	while ( ! isset($list[$id]) )
		$list[] = array();
	return;
}
//////////////////////////////
function s9sas8_loop( &$quad, &$json )
{
	return;
}
//////////////////////////////
function s6s4_loop( &$quad, &$json, $dir, $line )
{
	$grid = new PixLines;
	foreach ( $json['s6'] as $s6k => $s6v )
	{
		if ( empty($s6v) )
			continue;
		printf("s6[%d].flags  %s\n", $s6k, $s6v['bits']);

		$fn = sprintf('%s/%04d.clut', $dir, $s6k);
		$grid->new();

		if ( $s6v['s4'][1] > 0 )
		{
			for ( $i=0; $i < $s6v['s4'][1]; $i++ )
			{
				$s4k = $s6v['s4'][0] + $i;
				$s4v = $json['s4'][$s4k];
				printf("  s4[%d].flags  %s\n", $s4k, $s4v['bits']);

				$s2k = $s4v['s0s1s2'][2];
				$s2v = $json['s2'][$s2k];

				$grid->addquad($s2v, "\x0e");
			} // for ( $i=0; $i < $s6v['s4'][1]; $i++ )
		}

		if ( $s6v['s5'][1] > 0 )
		{
			for ( $i=0; $i < $s6v['s5'][1]; $i++ )
			{
				$s5k = $s6v['s5'][0] + $i;
				$s5v = $json['s5'][$s5k];
				printf("  s5[%d].flags  %s\n", $s5k, $s5v['bits']);

				$s3k = $s5v['s3'];
				$s3v = $json['s3'][$s3k];

				$grid->addquad($s3v['rect'], "\x0d");
			} // for ( $i=0; $i < $s6v['s5'][1]; $i++ )
		}

		if ( $line )
		{
			$img = $grid->draw();
			save_clutfile($fn, $img);
		}
	} // foreach ( $json['s6'] as $s6k => $s6v )
	return;
}
//////////////////////////////
function vanilla( $line, $fname )
{
	$json = file_get_contents($fname);
	if ( empty($json) )  return;

	$json = json_decode($json, true);
	if ( empty($json) )  return;

	$dir = str_replace('.', '_', $fname);

	$quad = load_idtagfile( $json['id3'] );
	s6s4_loop($quad, $json, $dir, $line);
	s9sas8_loop($quad, $json);

	$quad = json_pretty($quad, '');
	save_file("$fname.quad", $quad);
	return;
}

$line = false;
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		vanilla( $line, $argv[$i] );
	else
		$line = $argv[$i];
}
