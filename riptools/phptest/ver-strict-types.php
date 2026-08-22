<?php
function php_code_test( $code, $type )
{
	try {
		eval("declare(strict_types=1);\n".$code);
		echo "[OK] '$type'\n";
	} catch (Throwable $e) {
		printf("[FAIL] '%s': %s\n", $type, get_class($e));
		printf("       %s\n", $e->getMessage());
	}
}
//////////////////////////////
// PHP_VERSION_ID >= 70000 // 7.0.0
// Warning: Unsupported declare 'strict_types'
// Parse error: syntax error, unexpected '>'
php_code_test('
	function spaceship( $a , $b ) {
		return $a <=> $b;
	}
	spaceship("abc","xyz");
', 'spaceship');
// PHP_VERSION_ID >= 70000 // 7.0.0
// Parse error: syntax error, unexpected ':', expecting '{'
// Fatal error: Argument 1 passed  must be an instance if int, integer given
php_code_test('
	function strict_int( int $x ) : int {
		return $x;
	}
	strict_int(5);
', 'strict_types');
// PHP_VERSION_ID >= 70000 // 7.0.0
php_code_test('
	function yield_data() : Generator {
		yield "binary data";
	}
	function yield_from() : Generator {
		yield from yield_data();
	}
	foreach ( yield_from() as $v) {
		$v;
	}
', 'yield_from');
//////////////////////////////
// PHP_VERSION_ID >= 70100 // 7.1.0
// TypeError: Return value must be an instance of void, none returned
php_code_test('
	function return_void() : void {
		return;
	}
	return_void();
', 'void');
// PHP_VERSION_ID >= 70100 // 7.1.0
// ParseError: syntax error: unexpected '?'
php_code_test('
	function return_nullarray( ?array $a=null ) : ?array {
		return null;
	}
	return_nullarray();
', 'nullarray');
//////////////////////////////
// PHP_VERSION_ID >= 70200 // 7.2.0
// TypeError: Return value must be an instance of object, instance of stdClass returned
php_code_test('
	function return_object( array $t ) : object {
		return (object)$t;
	}
	return_object( [1,2,3] );
', 'object');
//////////////////////////////
// PHP_VERSION_ID >= 80000 // 8.0.0
// TypeError: Argument 1 passed must be an instance of mixed, none given
php_code_test('
	function mixed_arg( mixed $arg ) : mixed {
		return $arg;
	}
	mixed_arg(5);
', 'mixed_arg');
// PHP_VERSION_ID >= 80000 // 8.0.0
// ParseError: syntax error, unexpected '|', expecting '{'
php_code_test('
	function return_union( int $t ) : int|string {
		return ( $t & 1 ) ? "zero" : 0;
	}
	return_union(5);
', 'union');
//////////////////////////////
