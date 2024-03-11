<?php

function lower_case()  { return "function lower_case()"; }
function mixed_CASE()  { return "function mixed_CASE()"; }
//function MIXED_case()  { return "function MIXED_case()"; } // ERROR function redeclare
function UPPER_CASE()  { return "function UPPER_CASE()"; }
var_dump( lower_case() );
var_dump( mixed_CASE() );
var_dump( MIXED_case() );
var_dump( UPPER_CASE() );

$lower_var = array('a', 'b', 'c');
$mixed_VAR = array('d', 'e', 'f');
$MIXED_var = array('g', 'h', 'i');
$UPPER_VAR = array('j', 'k', 'l');
var_dump( $lower_var );
var_dump( $mixed_VAR );
var_dump( $MIXED_var );
var_dump( $UPPER_VAR );

$alp = array('a', 'b', 'c');
$num = array(0, 1, 2);
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
/*
//////////////////////////////
Expected Output:

string(22) "function lower_case()"
string(22) "function mixed_CASE()"
string(22) "function mixed_CASE()"
string(22) "function UPPER_CASE()"
array(3) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "c"
}
array(3) {
  [0]=>
  string(1) "d"
  [1]=>
  string(1) "e"
  [2]=>
  string(1) "f"
}
array(3) {
  [0]=>
  string(1) "g"
  [1]=>
  string(1) "h"
  [2]=>
  string(1) "i"
}
array(3) {
  [0]=>
  string(1) "j"
  [1]=>
  string(1) "k"
  [2]=>
  string(1) "l"
}
//////////////////////////////
*/
