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

class PixLines
{
	private $canvas;
	private $quad;
	private $line;
	private $point;

	private function drawquad( $x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4, $clr )
	{
		$this->drawline($x1, $y1, $x2, $y2, $clr);
		$this->drawline($x2, $y2, $x3, $y3, $clr);
		$this->drawline($x3, $y3, $x4, $y4, $clr);
		$this->drawline($x4, $y4, $x1, $y1, $clr);
		return;
	}

	private function drawline( $x1, $y1, $x2, $y2, $clr )
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

	private function drawpoint( $x1, $y1, $clr )
	{
		// center 0,0
		$x = (int)($this->canvas['hw'] + $x1);
		$y = (int)($this->canvas['hh'] + $y1);

		$dxx = ($y * $this->canvas['w']) + $x;
		$this->canvas['pix'][$dxx] = $clr;
		return;
	}

	private function canvas_grid()
	{
		if ( empty($this->point) )
			return '';

		$sz = 0;
		foreach( $this->point as $pt )
		{
			if ( abs($pt[0]) > $sz )  $sz = abs($pt[0]);
			if ( abs($pt[1]) > $sz )  $sz = abs($pt[1]);
		} // foreach( $this->point as $pt )

		$sz = int_ceil($sz+1, 8);
		$this->canvas = array(
			'w'   => $sz << 1,
			'h'   => $sz << 1,
			'hw'  => $sz,
			'hh'  => $sz,
			'pix' => str_repeat(ZERO, $sz*$sz*4),
			'cc'  => 16,
			'pal' => webpal16(),
		);

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
		return;
	}

	private function normalize()
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
		return;
	}
	//////////////////////////////
	function addquad()
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
		trace("addquad  %d,%d  %d,%d  %d,%d  %d,%d\n", $qd[0], $qd[1], $qd[2], $qd[3], $qd[4], $qd[5], $qd[6], $qd[7]);

		if ( array_search($qd, $this->quad) === false )
			$this->quad[] = $qd;
		return;
	}

	function addline()
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
		trace("addline  %d,%d  %d,%d\n", $ln[0], $ln[1], $ln[2], $ln[3]);

		if ( array_search($ln, $this->line) === false )
			$this->line[] = $ln;
		return;
	}

	function addpoint()
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
		trace("addpoint  %d,%d\n", $pt[0], $pt[1]);

		if ( array_search($pt, $this->point) === false )
			$this->point[] = $pt;
		return;
	}

	function new()
	{
		$this->quad   = array();
		$this->line   = array();
		$this->point  = array();
		$this->canvas = array();
		return;
	}

	function draw()
	{
		$this->normalize();
		$this->canvas_grid();

		foreach( $this->quad as $qd )
			$this->drawquad ($qd[0] , $qd[1] , $qd[2] , $qd[3] , $qd[4] , $qd[5] , $qd[6] , $qd[7], $qd[8]);

		foreach( $this->line as $ln )
			$this->drawline ($ln[0] , $ln[1] , $ln[2] , $ln[3], $ln[4]);

		foreach( $this->point as $pt )
			$this->drawpoint($pt[0] , $pt[1] , $pt[2]);

		return $this->canvas;
	}
} // class PixLines
