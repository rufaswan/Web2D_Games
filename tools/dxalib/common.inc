<?php
define("ZERO", "\x00");
define("BYTE", "\xff");
define("BIT8",  (1 <<  8)-1);
define("BIT16", (1 << 16)-1);
define("BIT24", (1 << 24)-1);
define("BIT32", (1 << 32)-1);

function str2int( &$str, $pos, $byte )
{
	$int = substr($str, $pos, $byte);
	return ordint($int);
}

function substr0( &$str, $pos )
{
	$len = 0;
	while ( $str[$pos+$len] != ZERO )
		$len++;
	return substr($str, $pos, $len);
}

function ordint( $str )
{
	if ( (int)$str === $str ) // already $int
		return $str;
	$len = strlen($str);
	$int = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$int += ($b << ($i*8));
	}
	return $int;
}

function chrint( $int, $byte = 0 )
{
	if ( (int)$int !== $int ) // already $str
		return $int;
	$str = "";
	for ( $i=0; $i < $byte; $i++ )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
	}
	while ( strlen($str) < $byte )
		$str .= ZERO;
	return $str;
}

function save_file( $fname, $str )
{
	if ( empty($str) )
		return;
	if ( strpos($fname, '\\') )
		$fname = str_replace('\\', '/', $fname);

	if ( strpos($fname, '/') )
		@mkdir( dirname($fname), 0755, true );
	file_put_contents($fname, $str);
	return;
}
