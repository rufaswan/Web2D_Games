<?php
define("ZERO", chr(  0));
define("BYTE", chr(255));

function substr0( &$str, $pos, $end = ZERO )
{
	$len = 0;
	while ( isset( $str[$pos+$len] ) && $str[$pos+$len] != $end )
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

function save_file( $fname, $str )
{
	if ( defined("DRY_RUN") )
		return;
	if ( empty($str) )
		return;
	if ( strpos($fname, '\\') )
		$fname = str_replace('\\', '/', $fname);

	if ( strpos($fname, '/') )
		@mkdir( dirname($fname), 0755, true );
	file_put_contents($fname, $str);
	return;
}

function str2big( &$str, $pos, $byte )
{
	$s = substr($str, $pos, $byte);
	$s = strrev($s);
	return ordint($s);
}

