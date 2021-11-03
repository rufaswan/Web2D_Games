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

function loadpck( $fname )
{
	$file = load_file($fname);
	if ( empty($file) )  return '';

	$cnt = str2int($file, 0, 4);
	$pos = 4;
	$pck = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$siz = str2int($file, $pos+0, 4);
		$sub = substr ($file, $pos+4, $siz);
			$pos += (4 + $siz);
		$pck[] = $sub;
	} // for ( $i=0; $i < $cnt; $i++ )
	return $pck;
}

function save_pck( &$pck, $fmt )
{
	if ( ! METAFILE )
		return;
	foreach ( $pck as $k => $v )
	{
		$fn = sprintf($fmt, $k);
		save_file($fn, $v);
	}
	return;
}

function sectspr( &$json, &$spr )
{
	$json['Frame'] = array();
	$of1 = str2int($spr, 0, 4);
	$of2 = str2int($spr, 4, 4);
	if ( $of1 == 1 )
		return;

	$edh = $of2;
	$sth = $of1;
	$idh = 0;
	for ( $ih = $of1; $ih < $of2; $ih += 4 )
	{
		$num = str2int($spr, $ih+0, 2);
		$off = str2int($spr, $ih+2, 2);

		$pos = $of2 + ($off * 0x16);
		$data = array();
		for ( $ib=0; $ib < $num; $ib++ )
		{
			$sub = substr($spr, $pos, 0x16);
				$pos += 0x16;
			$data[] = $sub;
		} // for ( $ib=0; $ib < $num; $ib++ )

		printf("json Frame[%d] + %d @ %x\n", count($json['Frame']), $num, $off);
		$frame = array();
		foreach ( $data as $dk => $dv )
		{
			echo debug($dv);
			//                    [quad]
			// 0  1   2  3   4 5  6 7  8 9  a b  c d  e f  10 11  12 13  14 15
			// b1 b2  sx sy  w h  x1   y1   x2   y2   x3   y3     x4     y4
			$b1 = ord( $dv[0] );
			$b2 = ord( $dv[1] );
			$sx = ord( $dv[2] );
			$sy = ord( $dv[3] );
			$w  = ord( $dv[4] );
			$h  = ord( $dv[5] );
				$sx += ($b2 * 0x80);

			$quad = array();
			for ( $i=6; $i < 0x16; $i += 2 )
				$quad[] = str2int($dv, $i, 2, true);

			// DstQuad (w=20,18)
			//   0,-18  1f,-18  1f,-1  0,-1
			$data = array(
				'TexID' => 0,
				'SrcQuad' => array(
					$sx+0    , $sy+0    ,
					$sx-1+$w , $sy+0    ,
					$sx-1+$w , $sy-1+$h ,
					$sx+0    , $sy-1+$h ,
				),
				'DstQuad' => array(
					$quad[0] , $quad[1] ,
					$quad[2] , $quad[3] ,
					$quad[4] , $quad[5] ,
					$quad[6] , $quad[7] ,
				),
			);
			quad_ext1px  ($data);
			quad_convexfix($data);

			$frame[] = $data;
		} // foreach ( $frame as $fk => $fv )

		$json['Frame'][] = $frame;
	} // for ( $ih = $of1; $ih < $of2; $ih += 4 )

	return;
}

/*
mnspr049.pck @ 40
	sx = 60,f0  18 x c
		 17, b   0, b   0, 0   17, 0
		 17,-c   0,-c   0,-1   17,-1
		-18, b  -1, b  -1, 0  -18, 0
		-18,-c  -1,-c  -1,-1  -18,-1

	-18, b  -1, b | 0, b  17, b
	-18, 0  -1, 0 | 0, 0  17, 0
	--------------+------------
	-18,-1  -1,-1 | 0,-1  17,-1
	-18,-c  -1,-c | 0,-c  17,-c
 */
