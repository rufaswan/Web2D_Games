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
// Accept a list of CLUT/RGBA data and create an atlas map

class AtlasTex
{
	private function MAPTREE_DEF( $x, $y, $w, $h )
	{
		return array(
			'child' => array('',''),
			'x' => $x,
			'y' => $y,
			'w' => $w + 2,
			'h' => $h + 2,
		);
	}

	private function maptree_add( &$tree, &$img, $x, $y, $w, $h )
	{
		// img has 1px padding
		if ( ($img['w']+2) > $w || ($img['h']+2) > $h )
			return array(-1,-1);

		if ( empty($tree) )
		{
			$tree = $this->MAPTREE_DEF($x, $y, $img['w'], $img['h']);
			$img['x'] = $x + 1;
			$img['y'] = $y + 1;
			trace("add map %s [%x x %x] to %x , %x\n", $img['id'], $img['w'], $img['h'], $img['x'], $img['y']);
			return array($x+1, $y+1);
		}
		else
		{
			$func = __FUNCTION__;
			// child 0 is right direction
			list($rx,$ry) = $this->$func($tree['child'][0], $img,
				$x + $tree['w'],
				$y,
				$w - $tree['w'],
				$tree['h']
			);
			// if success added
			if ( $rx !== -1 && $ry !== -1 )
				return array($x,$y);

			// child 1 is bottom direction
			return $this->$func($tree['child'][1], $img,
				$x,
				$y + $tree['h'],
				$w,
				$h - $tree['h']
			);
		}

		return array(-1,-1);
	}

	static private function usort($a, $b)
	{
		// sort height DESC , then width DESC
		if ( $a['h'] !== $b['h'] )
			return ( $a['h'] < $b['h'] ) ? 1 : -1;
		if ( $a['w'] !== $b['w'] )
			return ( $a['w'] < $b['w'] ) ? 1 : -1;
		return 0;
	}
	//////////////////////////////
	public function atlasmap( &$list )
	{
		usort($list, array(__CLASS__,'usort'));
		$cw = 1;
		$ch = 1;

		$tree = '';
		$is_done = false;
		while ( ! $is_done )
		{
			$tree = '';
			$is_done = true;
			foreach ( $list as $k => $v )
			{
				list($rx,$ry) = $this->maptree_add($tree, $list[$k], 0, 0, $cw, $ch);
				if ( $rx === -1 || $ry === -1 )
				{
					$is_done = false;
					break;
				}
			} // foreach ( $list as $k => $v )

			if ( ! $is_done )
			{
				$tree = '';
				if ( $cw > $ch )
					$ch <<= 1;
				else
					$cw <<= 1;
				trace("== atlas resize %x x %x\n", $cw, $ch);
			}
		} // while ( ! $is_done )

		$ind = array();
		foreach ( $list as $k => $v )
			$ind[ $v['id'] ] = $k;

		trace("atlasmap() = %x x %x\n", $cw, $ch);
		return array($ind, $cw, $ch);
	}
} // class AtlasTex
