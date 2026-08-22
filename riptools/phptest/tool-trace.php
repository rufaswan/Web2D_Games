<?php
declare( strict_types=1 );

require 'tool.inc';

$list = [1,2,3,4,5];
tool::trace(
	'test trace()' ,
	"\x81\x40\x81\x41" ,
	"\x41\x79\x61\x20" ,
	2000000.0 , -2000000.0 ,
	4000000.5 , -4000000.5 ,
	123456789.123456789  ,
	123456789123456789.0 ,
	0x123456789abcdef    ,
	-19.99999994039536   ,
	19.0000001           ,
	$list ,
	(object)$list ,
	false , true
);

// 0xbf7fffff
$num = -0.99999994039536;
var_dump( sprintf('%.6f',$num)   );
var_dump( number_format($num, 6) );
