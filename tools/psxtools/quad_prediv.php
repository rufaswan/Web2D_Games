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
require 'quad.inc';

php_req_extension('json_decode', 'json');

function predivsize( &$imgsize, &$file, $fname )
{
	$quad = json_decode($file, true);
	if ( empty($quad) )
		return;
	if ( ! isset($quad['keyframe']) )
		return;

	foreach ( $quad['keyframe'] as $kk => $kv )
	{
		if ( empty($kv) )
			continue;
		if ( ! isset($kv['layer']) )
			continue;
		foreach ( $kv['layer'] as $lk => $lv )
		{
			if ( empty($lv) )
				continue;
			if ( ! isset($lv['srcquad']) )
				continue;
			if ( ! isset($lv['tex_id']) )
				continue;
			$tex_id = $lv['tex_id'];
			if ( ! isset($imgsize[$tex_id]) )
				return php_error('[%s] require tex_id %d not found', $fname, $tex_id);

			list($tw,$th) = $imgsize[$tex_id];
			$srcquad = &$quad['keyframe'][$kk]['layer'][$lk]['srcquad'];
			for ( $i=0 ; $i < 8; $i += 2 )
			{
				$srcquad[$i+0] *= $tw;
				$srcquad[$i+1] *= $th;
			}
		} // foreach ( $kv['layer'] as $lk => $lv )
	} // foreach ( $quad['keyframe'] as $kk => $kv )

	$fn = str_replace('.prediv.quad', '', $fname);
	save_quadfile($fn, $quad);
	return;
}

function get_texid( $base )
{
	$match = array();
	preg_match('|\.([0-9]+)\.|', $base, $match);
	if ( empty($match) )
		return php_error('no tex_id available');
	return $match[1];
}

function predivquad( &$imgsize, $fname )
{
	if ( ! is_file($fname) )
		return;

	$base = str_replace('\\', '/', $fname);
	$pos  = strrpos($base, '/');
	$dir  = '.';
	if ( $pos !== false )
	{
		$dir  = substr($base, 0, $pos);
		$base = substr($base, $pos + 1);
	}
	$base = strtolower($base);

	$file = file_get_contents($fname);
	if ( strpos($base,'.prediv.quad') !== false )
		return predivsize($imgsize, $file, $fname);



	if ( substr($file,0,4) === 'RGBA' )
	{
		$tex_id = get_texid($base);
		$w = str2int($file, 4, 4);
		$h = str2int($file, 8, 4);
		$imgsize[ $tex_id ] = array($w, $h);
		return printf("RGBA[ %d ] %d x %d\n", $tex_id, $w, $h);
	}
	if ( substr($file,0,4) === 'CLUT' )
	{
		$tex_id = get_texid($base);
		$w = str2int($file,  8, 4);
		$h = str2int($file, 12, 4);
		$imgsize[ $tex_id ] = array($w, $h);
		return printf("CLUT[ %d ] %d x %d\n", $tex_id, $w, $h);
	}
	if ( substr($file,0,8) === "\x89PNG\x0d\x0a\x1a\x0a" )
	{
		$tex_id = get_texid($base);
		$w = str2big($file, 0x10, 4);
		$h = str2big($file, 0x14, 4);
		$imgsize[ $tex_id ] = array($w, $h);
		return printf("PNG[ %d ] %d x %d\n", $tex_id, $w, $h);
	}
	return;
}

$imgsize = array();
for ( $i=1; $i < $argc; $i++ )
	predivquad( $imgsize, $argv[$i] );
