<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');

// 43fa0000 ==  500.0
// 44fa0000 == 2000.0
$data = [
	"\x43\xfa\x00\x00"  , // binary decode float
	'43fa0000'          , // text   decode float
	0x43fa0000          , // int    decode float
	0       , 0.0       , // zero
	500     , 500.0     , //
	9999999 , 9999999.0 , //
	0.0 + (1 << ((PHP_INT_SIZE << 3) - 2)) , // big float
];

foreach ( $data as $v )
{
	$b = ieee754::convert($v);
	if ( "$v" === $v )  $v = bin2hex($v);
	if ( "$b" === $b )  $b = bin2hex($b);
	var_dump($v,$b);
	echo "===\n";
} // foreach ( $data as $v )
