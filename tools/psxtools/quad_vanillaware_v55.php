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
require 'quad.inc';

php_req_extension('json_dncode', 'json');

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
function s9sas8_loop( &$quad, &$json )
{
	return;
}
//////////////////////////////
function s6s4_loop( &$quad, &$json )
{
	return;
}
//////////////////////////////
function vanilla( $fname )
{
	$json = file_get_contents($fname);
	if ( empty($json) )  return;

	$json = json_decode($json, true);
	if ( empty($json) )  return;

	$quad = load_idtagfile( $json['tag'] );
	s6s4_loop($quad, $json);
	s9sas8_loop($quad, $json);

	$quad = json_pretty($quad, '');
	save_file("$fname.quad", $quad);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	vanilla( $argv[$i] );
