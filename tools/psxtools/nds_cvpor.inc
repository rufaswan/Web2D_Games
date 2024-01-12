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
 *
 * Special Thanks
 *   DSVania Editor
 *   https://github.com/LagoLunatic/DSVEdit/blob/master/docs/formats/Skeleton%20File%20Format.txt
 *     LagoLunatic
 */

function cvooe_jntfile( &$file, $pos )
{
	$jnt = array();

	$jnt['head'] = array(
		'name' => substr0($file, $pos + 3),
		'cx'   => str2int($file, $pos + 0x22, 2, true),
		'cy'   => str2int($file, $pos + 0x24, 2, true),
	);
	if ( stripos($jnt['head']['name'], '.opd') === false )
		return -1;

	$cjnt = str2int($file, $pos + 0x26, 1);
	$cjnt_inv = str2int($file, $pos + 0x27, 1);
	$cjnt_vis = str2int($file, $pos + 0x28, 1);
	$chit = str2int($file, $pos + 0x29, 1);
	$cpss = str2int($file, $pos + 0x2a, 1);
	$cpnt = str2int($file, $pos + 0x2b, 1);
	$canm = str2int($file, $pos + 0x2c, 1);
		$pos += 0x30;

	$jnt['joint'] = array();
	for ( $i=0; $i < $cjnt; $i++ )
	{
		$ent = array(
			'parent' => str2int($file, $pos + 0, 1, true),
			'key'    => str2int($file, $pos + 1, 1, true),
			'flag'   => str2int($file, $pos + 2, 1),
			'unk'    => str2int($file, $pos + 3, 1),
		);
		$pos += 4;
		$jnt['joint'][] = $ent;
	} // for ( $i=0; $i < $cjnt; $i++ )

	$jnt['pose'] = array();
	for ( $i=0; $i < $cpss; $i++ )
	{
		$head = array(
			str2int($file, $pos + 0, 1),
			str2int($file, $pos + 1, 1),
		);
		$pos += 2;

		$body = array();
		for ( $j=0; $j < $cjnt; $j++ )
		{
			$ent = array(
				'rotate' => str2int($file, $pos + 0, 2, true),
				'dist'   => str2int($file, $pos + 2, 1, true),
				'key'    => str2int($file, $pos + 3, 1, true),
			);
			$pos += 4;
			$body[] = $ent;
		} // for ( $j=0; $j < $cjnt; $j++ )

		$ent = array(
			'head' => $head,
			'body' => $body,
		);
		$jnt['pose'][] = $ent;
	} // for ( $i=0; $i < $cpss; $i++ )

	$jnt['hitbox'] = array();
	for ( $i=0; $i < $chit; $i++ )
	{
		$ent = array(
			'rotate' => str2int($file, $pos + 0, 2, true),
			'dist'   => str2int($file, $pos + 2, 1),
			'w'      => str2int($file, $pos + 3, 1),
			'h'      => str2int($file, $pos + 4, 1),
			'joint'  => str2int($file, $pos + 5, 1),
			'flag'   => str2int($file, $pos + 6, 1),
			'unk'    => str2int($file, $pos + 7, 1),
		);
		$pos += 8;
		$jnt['hitbox'][] = $ent;
	} // for ( $i=0; $i < $chit; $i++ )

	$jnt['point'] = array();
	for ( $i=0; $i < $cpnt; $i++ )
	{
		$ent = array(
			'rotate' => str2int($file, $pos + 0, 2, true),
			'dist'   => str2int($file, $pos + 1, 1),
			'unk'    => str2int($file, $pos + 2, 1),
		);
		$pos += 4;
		$jnt['point'][] = $ent;
	} // for ( $i=0; $i < $cpnt; $i++ )

	$jnt['draw'] = array();
	for ( $i=0; $i < $cjnt_vis; $i++ )
	{
		$jnt['draw'][] = str2int($file, $pos, 1);
		$pos++;
	} // for ( $i=0; $i < $cjnt_vis; $i++ )

	$jnt['anim'] = array();
	for ( $i=0; $i < $canm; $i++ )
	{
		$cnt = str2int($file, $pos, 1);
		$pos++;

		$ent = array();
		for ( $j=0; $j < $cnt; $j++ )
		{
			$time = array(
				'pose' => str2int($file, $pos + 0, 1),
				'time' => str2int($file, $pos + 1, 1),
				'case' => str2int($file, $pos + 2, 1),
			);
			$pos += 3;
			$ent[] = $time;
		} // for ( $j=0; $j < $cnt; $j++ )
		$jnt['anim'][] = $ent;
	} // for ( $i=0; $i < $canm; $i++ )

	return $jnt;
}
