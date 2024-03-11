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

class atlas_tex
{
	private $image  = array();
	private $canvas = array();

	// a < b == 3,2,1  DESC , a > b == 1,2,3  ASC
	// a - b == 1,2,3  ASC  , b - a == 3,2,1  DESC
	static private function sortwh($a, $b)
	{
		// sort height DESC , then width DESC
		$d = $b['h'] - $a['h'];
		if ( $d !== 0 )
			return $d;
		return $b['w'] - $a['w'];
	}

	private function maptree_add( &$id, $x, $y, $w, $h )
	{
		$img = &$this->image[$id];

		// padding 1px
		$iw = $img['w'] + 2;
		$ih = $img['h'] + 2;
		if ( $iw > $w )  return false;
		if ( $ih > $h )  return false;

		$img['x'] = $x + 1;
		$img['y'] = $y + 1;

		$id++;
		if ( isset( $this->image[$id] ) )
		{
			$func = __FUNCTION__;

			// add next image to right direction
			$r = $this->$func($id, $x + $iw, $y, $w - $iw, $ih);
			if ( $r )
				return true;

			// add next image to bottom direction
			$r = $this->$func($id, $x, $y + $ih, $w, $h - $ih);
			return $r;
		}
		return true;
	}

	private function atlasmap()
	{
		$this->canvas = array(
			'w' => 1,
			'h' => 1,
		);

		$is_done = false;
		while ( ! $is_done )
		{
			$is_done = true;
			$id = 0;
			$x  = 0;
			$y  = 0;
			$is_done = $this->maptree_add($id, $x, $y, $this->canvas['w'], $this->canvas['h']);

			if ( ! $is_done )
			{
				// expand canvas size , width then height
				if ( $this->canvas['w'] <= $this->canvas['h'] )
					$this->canvas['w'] <<= 1;
				else
					$this->canvas['h'] <<= 1;
			}
		} // while ( ! $is_done )
		return;
	}

	//////////////////////////////

	public function init()
	{
		$this->image = array();
		return;
	}

	public function putclut( $w, $h, &$pal, &$pix )
	{
		$rgba = '';
		$len = strlen($pix);
		for ( $i=0; $i < $len; $i++ )
		{
			$b = ord($pix[$i]);
			$rgba .= substr($pal, $b<<2, 4);
		}
		return $this->putrgba($w, $h, $rgba);
	}

	public function putrgba( $w, $h, &$pix )
	{
		foreach ( $this->image as $ik => $iv )
		{
			if ( $w === $iv['w'] && $h === $iv['h'] && $pix === $iv['pix'] )
				return $iv['id'];
		}
		$ent = array(
			'id' => count($this->image) ,
			'x' => 1  ,
			'y' => 1  ,
			'w' => $w ,
			'h' => $h ,
			'pix' => $pix  ,
		);
		$this->image[] = $ent;
		return $ent['id'];
	}

	public function sort()
	{
		if ( count($this->image) < 2 )
			return;
		usort($this->image, array(__CLASS__,'sortwh'));
		$this->atlasmap();
		return;
	}

	public function getxywh( $id )
	{
		foreach ( $this->image as $ik => $iv )
		{
			if ( $id === $iv['id'] )
				return array( $iv['x'] , $iv['y'] , $iv['w'] , $iv['h'] );
		}
		return -1;
	}

	public function save( $pfx )
	{
		$canv = array(
			'w' => $this->canvas['w'],
			'h' => $this->canvas['h'],
			'pix' => str_repeat(ZERO, $this->canvas['w']*$this->canvas['h']*4),
		);
		$txt = '';
		foreach ( $this->image as $ik => $iv )
		{
			$txt .= sprintf("%4d , %4d , %4d , %4d , %4d\n", $iv['id'], $iv['x'], $iv['y'], $iv['w'], $iv['h']);
			for ( $y=0; $y < $iv['h']; $y++ )
			{
				$sxx = ($y * $iv['w']);

				$dyy = ($iv['y'] + $y) * $this->canvas['w'];
				$dxx = $dyy + $iv['x'];

				$s = substr(  $iv['pix'], $sxx*4, $iv['w']*4);
				str_update ($canv['pix'], $dxx*4, $s);
			} // for ( $y=0; $y < $iv['h']; $y++ )
		} // foreach ( $this->image as $ik => $iv )

		save_clutfile("$pfx.rgba", $canv);
		save_file("$pfx.txt", $txt);
		return;
	}

} // class atlas_tex
