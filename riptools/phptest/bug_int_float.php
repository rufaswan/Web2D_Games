<?php
declare( strict_types=1 );

// php 32-bit use float64_t
function testfunc( float $int ) : void
{
	printf("%x\n", $int);
	return;
}

for ( $i=0; $i < 0x100; $i++ )
{
	$int = 0;
	for ( $j=0; $j < PHP_INT_SIZE; $j++  )
	{
		// Bitwise Operators Guarantee Integers
		// interpreting it as a negative number under Two's Complement rules.
		//$int <<= 8;
		//$int |=  $i;

		// Mathematical Expression
		// preserve the scale and positive sign of the number.
		// but sacrifices the precision of the lower bits
		$int *= 0x100;
		$int += $i;
	}

	testfunc($int);
	// $int >= 0x80
	// Fatal error: Uncaught TypeError: Argument 1 passed must be of the type integer, float given
} // for ( $i=0; $i < 0x100; $i++ )

// $int > PHP_INT_MAX = convert into float
