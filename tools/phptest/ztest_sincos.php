<?php
define('BIT16', (1<<16)-1);

function hex16( $f )
{
	$h = (int)($f * (BIT16 + 1));
	if ( $h < 0 )
		return sprintf('-%4x', -$h & BIT16);
	else
		return sprintf('+%4x',  $h & BIT16);
}

for ( $h=0; $h < 0x100; $h++ )
{
	$rad = $h * pi() / 0x80;
	$sin = sin($rad);
	$cos = cos($rad);
	//printf("%2x  rad %.6f  sin %.6f  cos %.6f\n", $h, $rad, $sin, $cos);
	printf("%2x  sin %s  cos %s\n", $h, hex16($sin), hex16($cos));
} // for ( $h=0; $h < 0x100; $h++ )

/*
.       <- sin ->
.
.  ^        0        ^
.  |        |        |
. cos  c0 --+-- 40  cos
.  |        |        |
.  \/      80       \/
.
.       <- sin ->

// cross = 0 parallel      , 1 perpendicular
// dot   = 0 perpendicular , 1 parallel
//   perpendicular = 90 or 270 degree (cos = 0)
//   parallel      =  0 or 180 degree (sin = 0)
//
//   dot(x,y) = |x||y| cos Q
// cross(x,y) = |x||y| sin Q
*/
