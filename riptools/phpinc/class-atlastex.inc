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
declare(strict_types=1);

tool::class('clutfile');

// Accept a list of CLUT/RGBA data and create an atlas map
class atlastex
{
	// a < b == 3,2,1  DESC , a > b == 1,2,3  ASC
	// a - b == 1,2,3  ASC  , b - a == 3,2,1  DESC
	private static function sort_wh( array $a, array $b) : int
	{
		// sort height DESC , then width DESC
		$r = (int)($b->h - $a->h);
		if ( $r === 0 )
			$r = (int)($b->w - $a->w);
		return $r;
	}

	private static function pix_update( string &$dst, string &$src, int $pos ) : void
	{
		$dstlen = strlen($dst);
		$srclen = strlen($src);
		if ( ($pos+$srclen) > $dstlen )
			return;
		for ( $i=0; $i < $srclen; $i++ )
			$dst[$pos+$i] = $src[$i];
	}

	private static function ord4( int $int ) : string
	{
		$bin = '';
		for ( $i=0; $i < 4; $i++ )
		{
			$c = $int & BIT8;
			$int >>= 8;
			$bin .= chr($c);
		}
		return $bin;
	}
	//////////////////////////////
	private $image  = [];
	private $canvas;

	private function maptree_add( int &$id, int $x, int $y, int $w, int $h ) : bool
	{
		$img = &$this->image[$id];

		// padding 1px
		$iw = $img->w + 2;
		$ih = $img->h + 2;
		if ( $iw > $w )  return false;
		if ( $ih > $h )  return false;

		$img->x = $x + 1;
		$img->y = $y + 1;

		$id++;
		if ( isset( $this->image[$id] ) )
		{
			$func = [$this, __FUNCTION__];

			// add next image to right direction
			$r = $func($id, $x + $iw, $y, $w - $iw, $ih);
			if ( $r )
				return true;

			// add next image to bottom direction
			$r = $func($id, $x, $y + $ih, $w, $h - $ih);
			return $r;
		}
		return true;
	}

	private function atlasmap() : void
	{
		$this->canvas = new clutdata;
		$this->canvas->w = 1;
		$this->canvas->h = 1;

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
				if ( $this->canvas->w <= $this->canvas->h )
					$this->canvas->w <<= 1;
				else
					$this->canvas->h <<= 1;
			}
		} // while ( ! $is_done )
		return;
	}
	//////////////////////////////
	public function add( clutdata &$img ) : int
	{
		// a 256 color palette is often not enough
		// convert CLUT to RGBA
		clutfile::clut2rgba($img);

		// return ID if duplicate
		foreach ( $this->image as $ik => $iv )
		{
			if ( $iv->w   !== $img->w   )  continue;
			if ( $iv->h   !== $img->h   )  continue;
			if ( $iv->pix !== $img->pix )  continue;
			return $iv->id;
		}

		// generate a new ID + entry
		// backup id for reference after sorting
		$img->id = count($this->image);
		$this->image[] = $img;
		return $img->id;
	}

	public function sort() : void
	{
		if ( count($this->image) < 2 )
			return;
		usort($this->image, [__CLASS__,'sort_wh']);
		$this->atlasmap();
	}

	public function getxywh( int $id ) : array
	{
		foreach ( $this->image as $ik => $iv )
		{
			if ( $id === $iv['id'] )
				return [ $iv['x'] , $iv['y'] , $iv['w'] , $iv['h'] ];
		}
		return [];
	}

	public function save( string $pfx ) : void
	{
		$canv = new clutdata;
		$canv->w = $this->canvas['w'];
		$canv->h = $this->canvas['h'];
		$canv->pix = str_repeat(ZERO, $canv->w*$canv->h*4);

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
				self::pix_update ($canv['pix'], $s, $dxx*4);
			} // for ( $y=0; $y < $iv['h']; $y++ )
		} // foreach ( $this->image as $ik => $iv )

		clutfile::save($pfx, $canv);
		file_put_contents("$pfx.txt", $txt);
	}

} // class atlastex
