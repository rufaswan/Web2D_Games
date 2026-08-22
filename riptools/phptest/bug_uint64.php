<?php
declare( strict_types=1 );

// first X bits of the fractional parts pf the cube roots
// of the 3rd prime [2,3,5,7,11]
$cube_root = pow( 5 , (1.0/3.0) );
printf('cube root of 5 = %f'."\n", $cube_root);
echo   'frac hex       = ';
$frac = $cube_root;
for ( $i=0; $i < 0x20; $i++ )
{
	$frac = $frac - (int)$frac;
	$frac *= 0x100;
	printf('%02x ', $frac);
}
echo "\n\n";


function uint_test( string $hex ) : int
{
	$int = hexdec($hex);
	return printf("expect %s , get %x\n", $hex, $int);
}
uint_test('b5c0fbcf');
uint_test('b5c0fbcfec4d3b2f');
// expect b5c0fbcfec4d3b2f , get b5c0fbcfec4d3800
