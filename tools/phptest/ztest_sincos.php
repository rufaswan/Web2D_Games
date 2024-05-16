<?php
define('BIT16', (1<<16)-1);

function hex16( $f )
{
	$h = (int)($f * (1 << 16));
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
