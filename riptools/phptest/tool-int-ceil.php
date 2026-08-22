<?php
declare( strict_types=1 );

require 'tool.inc';

//  int   near
//  123 ,  100 =  200 / rem!=0 ceil
//  123 , -100 =  100 / rem!=0 floor
//  123 ,    0 =    0 / invalid
// -123 ,  100 = -100 / rem!=0 ceil
// -123 , -100 = -200 / rem!=0 floor
// -123 ,    0 =    0 / invalid
//    0 ,  100 =    0 / zero
//    0 , -100 =    0 / zero
//    0 ,    0 =    0 / invalid
//  200 ,  100 =  200 / rem=0
//  200 , -100 =  200 / rem=0
//  200 ,    0 =    0 / invalid
// -200 ,  100 = -200 / rem=0
// -200 , -100 = -200 / rem=0
// -200 ,    0 =    0 / invalid
foreach ( [123,-123,0,200,-200] as $v )
{
	foreach ( [100,-100,0] as $n )
	{
		$r = tool::int_ceil($v, $n);
		printf("%4d,%4d = %4d\n", $v, $n, $r);
	} // foreach ( [100,-100,0] as $n )
} // foreach ( [123,-123,0,200,-200] as $v )
