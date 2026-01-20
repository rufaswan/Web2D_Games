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

function copyquad( &$quad, &$json, $type, $id )
{
	if ( $id < 0 )
		return;
	if ( ! isset($json[$type][$id]) )
		return;

	$data = $json[$type][$id];
	if ( empty($data) )
		return;

	if ( ! isset($quad[$type]) )
		$quad[$type] = array();
	list_add($quad[$type], $id, $data);

	$func = __FUNCTION__;
	switch ( $type )
	{
		case 'keyframe':
			if ( ! isset($data['layer']) )
				return;
			foreach( $data['layer'] as $lk => $lv )
			{
				if ( isset($lv['blend_id']) )
					$func($quad, $json, 'blend', $lv['blend_id']);
			} // foreach( $data['layer'] as $lk => $lv )
			return;
		case 'hitbox':
			return;
		case 'slot':
			foreach( $data as $sk => $sv )
				$func($quad, $json, $sv['type'], $sv['id']);
			return;
		case 'animation':
			if ( ! isset($data['timeline']) )
				return;
			$mix = array(
				'matrix_mix_id',
				'color_mix_id',
				'dstquad_mix_id',
				'srcquad_mix_id',
				'fogquad_mix_id',
				'hitquad_mix_id',
			);
			foreach( $data['timeline'] as $tk => $tv )
			{
				foreach ( $mix as $m )
				{
					if ( isset($tv[$m]) )
						$func($quad, $json, 'mix', $tv[$m]);
				} // foreach ( $mix as $m )

				if ( isset($tv['attach']) )
					$func($quad, $json, $tv['attach']['type'], $tv['attach']['id']);
			} // foreach( $data['timeline'] as $tk => $tv )
			return;
		case 'skeleton':
			if ( ! isset($data['bone']) )
				return;
			foreach( $data['bone'] as $bk => $bv )
			{
				if ( isset($bv['attach']) )
					$func($quad, $json, $bv['attach']['type'], $bv['attach']['id']);
			} // foreach( $data['bone'] as $bk => $bv )
			return;
	} // switch ( $tag )
	return;
}

function quadfile( $fname, $typeid )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$json = json_decode($file, true);
	if ( empty($json) )  return;

	$typeid = strtolower($typeid);
	if ( strpos($typeid,'-') === false )
		return;

	list($type,$id) = explode('-', $typeid);
	$quad = array();

	copyquad($quad, $json, $type, (int)$id);
	if ( empty($quad) )  return;

	$file = json_pretty::encode($quad);
	$fn   = sprintf('%s-%s.quad', $fname, $typeid);
	save_file($fn, $file);
	return;
}

echo "${argv[0]}  QUAD  type-id\n";
if ( $argc !== 3 )
	exit();
quadfile( $argv[1] , $argv[2] );
