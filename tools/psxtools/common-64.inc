<?php
/*
[license]
[/license]
 */
if ( PHP_INT_SIZE < 8 )
	php_error("required support for 64-bit or higher");

function float64( $int )
{
	// IEEE 754 double precision
	//   signed   bit  = 1
	//   exponent bits = 11
	//   mantissa bits = 52
	//   bias = 2^10-1 = 1023
	return iee754_float($int, 11, 52, 0x3ff);
}

function float128( $int )
{
	// IEEE 754 quadruple precision
	//   signed   bit  = 1
	//   exponent bits = 15
	//   mantissa bits = 112
	//   bias = 2^14-1 = 16383
	return iee754_float($int, 15, 112, 0x3fff);
}

function float256( $int )
{
	// IEEE 754 octuple precision
	//   signed   bit  = 1
	//   exponent bits = 19
	//   mantissa bits = 236
	//   bias = 2^18-1 = 262143
	return iee754_float($int, 19, 236, 0x3ffff);
}
