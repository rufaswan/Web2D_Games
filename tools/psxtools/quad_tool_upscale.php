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
require 'quad.inc';

function quadscale( &$quad, $scale )
{
	$cnt = count($quad);
	for ( $i=0; $i < $cnt; $i++ )
		$quad[$i] *= $scale;
	return;
}

function quadfile( $fname, $scale )
{
	// for *.quad only
	if ( stripos($fname, '.quad') === false )
		return;

	$json = file_get_contents($fname);
	if ( empty($json) )  return;

	$json = json_decode($json, true);
	if ( empty($json) )  return;

	if ( $scale == 1.0 )
		return;

	$is_mod = false;

	// upscale parts quad to assemble a frame
	if ( isset($json['Frame']) )
	{
		$is_mod = true;
		foreach ( $json['Frame'] as $fk => $fv )
		{
			if ( empty($fv) )
				continue;
			foreach ( $fv as $fvk => $fvv )
			{
				if ( isset($fvv['SrcQuad']) )
					quadscale( $json['Frame'][$fk][$fvk]['SrcQuad'], $scale );
				if ( isset($fvv['DstQuad']) )
					quadscale( $json['Frame'][$fk][$fvk]['DstQuad'], $scale );
			} // foreach ( $fv as $fvk => $fvv )
		} // foreach ( $json['Frame'] as $fk => $fv )
	}

	if ( $is_mod )
	{
		$pfx = substr($fname, 0, strrpos($fname, '.'));
		$fn  = sprintf('%s-%.2f', $pfx, $scale);
		save_quadfile($fn, $json);
	}
	return;
}

printf("%s  float  QUAD_FILE\n", $argv[0]);
$scale = 1.0;
for ( $i=1; $i < $argc; $i++ )
{
	if ( ! is_file($argv[$i]) )
		$scale = 1.0 * $argv[$i];
	else
		quadfile( $argv[$i], $scale );
}
