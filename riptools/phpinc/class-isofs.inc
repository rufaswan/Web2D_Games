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
declare( strict_types=1 );

class isofs
{
	private $fp = 0;
	private function fp_read( int $pos, int $len ) : string
	{
		if ( $pos < 0 )  return '';
		if ( $len < 0 )  return '';
		fseek($this->fp, $pos);

		$bin = fread($this->fp, $len);
		if ( strlen($bin) !== $len )
			return '';

		return $bin;
	}

	private function lba_read( int $pos, int $len ) : string
	{
		// *= 800
		$pos <<= 11;
		$len <<= 11;
		return $this->fp_read($pos, $len);
	}

	//////////////////////////////
	public function entry_read( string &$bin ) : array
	{
		$res = [];
		return $res;
	}

	public function fopen( string $fname ) : bool
	{
		$this->fp = fopen($fname, 'rb');
		if ( ! $this->fp )
			return false;

		$root = $this->lba_read(0x10, 2);
		if ( substr($root,0,6) !== "\x01CD001" )
			return false;
		if ( substr($root,0x801,5) !== 'CD001' )
			return false;

		return true;
	}
} // class isofs
