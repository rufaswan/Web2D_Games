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
require 'common-guest.inc';
require 'common-json.inc';

php_req_extension('json_decode', 'json');

function load_texture( $pfx, $id )
{
	$fname = "$pfx.$id.rgba";
	$img = load_clutfile($fname);
	if ( empty($img) )
		return php_error('load texture failed = %s', $fname);

	if ( isset($img['pal']) )
		return $img;

	$pal = rgba2clut($img['pix'], '');
	if ( $pal === -1 )
		return php_error('cannot convert RGBA texture to CLUT = %s', $fname);

	$img['pal'] = $pal[0];
	$img['pix'] = $pal[1];
	$img['cc']  = strlen($pal[0]) >> 2;
	return $img;
}

function quad2xywh( &$quad )
{
	// 1 ---- 2  2 ----- 1
	// normal |  | flip x
	// 4 ---- 3  3 ----- 4
	//
	// 4 ---- 3  3 ----- 4
	// flip y |  | flip xy
	// 1 ---- 2  2 ----- 1
	if ( $quad[0] !== $quad[2] )  return -1; // x1 == x2
	if ( $quad[6] !== $quad[4] )  return -1; // x4 == x3
	if ( $quad[1] !== $quad[7] )  return -1; // y1 == y4
	if ( $quad[3] !== $quad[5] )  return -1; // y2 == y3

	$w = $quad[4] - $quad[0];
	$h = $quad[5] - $quad[1];
	if ( $w === 0 )  return -1;
	if ( $h === 0 )  return -1;
	return array($quad[0], $quad[1], $w, $h);
}
//////////////////////////////
//////////////////////////////
function quadexport( $engine, $fname )
{
	// for *.quad only
	if ( stripos($fname, '.quad') === false )
		return;

	$json = file_get_contents($fname);
	if ( empty($json) )  return;

	$json = json_decode($json, true);
	if ( empty($json) )  return;

	$pfx = substr($fname, 0, strrpos($fname,'.'));
	return;
}
//////////////////////////////
echo <<<_MSG
{$argv[0]}  engine  QUAD_FILE...
engine
  mugen   : .SFF + .DEF
  openbor : .PNG + .ACT + .TXT

_MSG;

switch ( $argv[1] )
{
	case 'mugen':
	case 'openbor':
		for ( $i=2; $i < $argc; $i++ )
			quadexport( $argv[1], $argv[$i] );
		break;
} // switch ( $argv[1] )
