<?php

function lower_case()  { return "function lower_case()"; }
function mixed_CASE()  { return "function mixed_CASE()"; }
//function MIXED_case()  { return "function MIXED_case()"; } // ERROR function redeclare
function UPPER_CASE()  { return "function UPPER_CASE()"; }
var_dump( lower_case() );
var_dump( mixed_CASE() );
var_dump( MIXED_case() );
var_dump( UPPER_CASE() );

$lower_var = ['a', 'b', 'c'];
$mixed_VAR = ['d', 'e', 'f'];
$MIXED_var = ['g', 'h', 'i'];
$UPPER_VAR = ['j', 'k', 'l'];
var_dump( $lower_var );
var_dump( $mixed_VAR );
var_dump( $MIXED_var );
var_dump( $UPPER_VAR );

$alp = ['a', 'b', 'c'];
$num = [0, 1, 2];
function test_func( $lower_arg, $mixed_ARG, $MIXED_arg, $UPPER_ARG )
{
	echo "== test_func()\n";
	var_dump( $lower_arg );
	var_dump( $mixed_ARG );
	var_dump( $MIXED_arg );
	var_dump( $UPPER_ARG );
	return;
}
test_func($alp, $alp, $alp, $alp);
test_func($num, $num, $num, $num);
