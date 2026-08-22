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

class pixlines
{
	private $canvas = [];
	private $quad   = [];
	private $line   = [];
	private $point  = [];

	private function drawquad( float $x1, float $y1, float $x2, float $y2, float $x3, float $y3, float $x4, float $y4, string $clr ) : void
	{
		$this->drawline($x1, $y1, $x2, $y2, $clr);
		$this->drawline($x2, $y2, $x3, $y3, $clr);
		$this->drawline($x3, $y3, $x4, $y4, $clr);
		$this->drawline($x4, $y4, $x1, $y1, $clr);
	}

	private function drawline( float $x1, float $y1, float $x2, float $y2, string $clr ) : void
	{
		if ( abs($x1-$x2) > abs($y1-$y2) )
		{
			// always left-to-right direction
			if ( $x1 > $x2 )
			{
				$tx = $x2;  $ty = $y2;
				$x2 = $x1;  $y2 = $y1;
				$x1 = $tx;  $y1 = $ty;
			}

			$x2 -= $x1;
			$y2 -= $y1;
			$slope = ( $x2 > 0 ) ? $y2 / $x2 : 0;
			for ( $x=0; $x <= $x2; $x++ )
			{
				$y = $x * $slope;
				$this->drawpoint($x1+$x , $y1+$y , $clr);
			} // for ( $x=0; $x <= $p2[0]; $x++ )
		}
		else
		{
			// always top-to-bottom direction
			if ( $y1 > $y2 )
			{
				$tx = $x2;  $ty = $y2;
				$x2 = $x1;  $y2 = $y1;
				$x1 = $tx;  $y1 = $ty;
			}

			$x2 -= $x1;
			$y2 -= $y1;
			$slope = ( $y2 > 0 ) ? $x2 / $y2 : 0;
			for ( $y=0; $y <= $y2; $y++ )
			{
				$x = $y * $slope;
				$this->drawpoint($x1+$x , $y1+$y , $clr);
			} // for ( $y=0; $y <= $p2[1]; $y++ )
		}
		return;
	}

	private function drawpoint( float $x1, float $y1, string $clr ) : void
	{
		// center 0,0
		$canvas = &$this->canvas;
		$x = (int)($canvas->hw + $x1);
		$y = (int)($canvas->hh + $y1);

		$dxx = ($y * $canvas->w) + $x;
		$canvas->pix[$dxx] = $clr;
	}

	private function canvas_grid() : void
	{
		if ( empty($this->point) )
			return;

		$sz = 0;
		foreach( $this->point as $pt )
		{
			if ( abs($pt[0]) > $sz )  $sz = abs($pt[0]);
			if ( abs($pt[1]) > $sz )  $sz = abs($pt[1]);
		} // foreach( $this->point as $pt )

		$sz = tool::int_ceil($sz+1, 8);
		$this->canvas = new clutdata;
		$this->canvas->hw = $sz;
		$this->canvas->hh = $sz;
		$this->canvas->w = $sz << 1;
		$this->canvas->h = $sz << 1;
		$this->canvas->pal = clutfile::webpal16();
		$this->canvas->pix = str_repeat(ZERO, $sz*$sz*4);

		// all coordinates are from -sz to +sz
		// draw 16x16 tile
		$f = $sz << 1;
		for ( $i = 16; $i <= $f; $i += 16 )
		{
			$p = ($i - 1) - $sz;
			$this->drawline(  $p, -$sz,  $p, $sz, "\x01"); // | edge
			$this->drawline(-$sz,   $p, $sz,  $p, "\x01"); // - edge
		} // for ( $i=0; $i < $f; $i += 16 )

		// draw center lines
		$this->drawline(   0, -$sz,   0, $sz, "\x02"); // half | line
		$this->drawline(-$sz,    0, $sz,   0, "\x02"); // half - line
	}

	private function normalize() : void
	{
		foreach ( $this->quad as $qd )
		{
			$this->addpoint($qd[0], $qd[1], "\x0c");
			$this->addpoint($qd[2], $qd[3], "\x0c");
			$this->addpoint($qd[4], $qd[5], "\x0c");
			$this->addpoint($qd[6], $qd[7], "\x0c");
		}

		foreach ( $this->line as $ln )
		{
			$this->addpoint($ln[0], $ln[1], "\x0c");
			$this->addpoint($ln[2], $ln[3], "\x0c");
		}
	}
	//////////////////////////////
	public function addquad() : void
	{
		$qd = '';
		$arg = func_get_args();

		if ( count($arg) === 2 && count($arg[0]) === 8 )
		{
			$qd   = $arg[0];
			$qd[] = $arg[1];
		}
		if ( count($arg) === 9 )
			$qd = $arg;

		if ( empty($qd) )
			return;
		tool::trace('addquad', $qd);

		if ( array_search($qd, $this->quad) === false )
			$this->quad[] = $qd;
	}

	public function addline() : void
	{
		$ln = '';
		$arg = func_get_args();

		if ( count($arg) === 2 && count($arg[0]) === 4 )
		{
			$ln   = $arg[0];
			$ln[] = $arg[1];
		}
		if ( count($arg) === 5 )
			$ln = $arg;

		if ( empty($ln) )
			return;
		tool::trace('addline', $ln);

		if ( array_search($ln, $this->line) === false )
			$this->line[] = $ln;
	}

	public function addpoint() : void
	{
		$pt = '';
		$arg = func_get_args();

		if ( count($arg) === 2 && count($arg[0]) === 2 )
		{
			$pt   = $arg[0];
			$pt[] = $arg[1];
		}
		if ( count($arg) === 3 )
			$pt = $arg;

		if ( empty($pt) )
			return;
		tool::trace('addpoint', $pt);

		if ( array_search($pt, $this->point) === false )
			$this->point[] = $pt;
	}

	public function draw() : clutdata
	{
		$this->normalize();
		$this->canvas_grid();

		foreach( $this->quad as $qd )
			$this->drawquad ($qd[0] , $qd[1] , $qd[2] , $qd[3] , $qd[4] , $qd[5] , $qd[6] , $qd[7], $qd[8]);

		foreach( $this->line as $ln )
			$this->drawline ($ln[0] , $ln[1] , $ln[2] , $ln[3], $ln[4]);

		foreach( $this->point as $pt )
			$this->drawpoint($pt[0] , $pt[1] , $pt[2]);

		$img = clutfile::array2data($this->canvas);
		return $img;
	}
} // class pixlines
