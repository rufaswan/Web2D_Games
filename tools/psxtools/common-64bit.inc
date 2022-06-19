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
// PlayStation One is a 32-bit system
// anything 64-bit and above is here
define('BIT64',  (1 <<  64)-1);
define('BIT128', (1 << 128)-1);
define('BIT256', (1 << 256)-1);

function sint64 ( $s )  { return sint_bit($s, 0x40); }
function sint128( $s )  { return sint_bit($s, 0x80); }
function sint256( $s )  { return sint_bit($s, 0x100); }

function float64( $int )
{
	php_req_bytes(8);
	// IEEE 754 double precision
	//   signed   bit  = 1
	//   exponent bits = 11
	//   mantissa bits = 52
	//   bias = 2^10-1 = 1023
	return iee754_float($int, 11, 52, 0x3ff);
}

function float128( $int )
{
	php_req_bytes(16);
	// IEEE 754 quadruple precision
	//   signed   bit  = 1
	//   exponent bits = 15
	//   mantissa bits = 112
	//   bias = 2^14-1 = 16383
	return iee754_float($int, 15, 112, 0x3fff);
}

function float256( $int )
{
	php_req_bytes(32);
	// IEEE 754 octuple precision
	//   signed   bit  = 1
	//   exponent bits = 19
	//   mantissa bits = 236
	//   bias = 2^18-1 = 262143
	return iee754_float($int, 19, 236, 0x3ffff);
}
