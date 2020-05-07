<?php
define("ZERO", chr(  0));
define("BYTE", chr(255));
define("BIT8",  0xff);
define("BIT16", 0xffff);
define("BIT24", 0xffffff);
define("BIT32", 0xffffffff);

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

function req_ext( $func, $ext )
{
	if ( ! function_exists($func) )
		exit("REQ $ext extension not found!\n");
}

function intv( &$file, &$st )
{
	$ret = 0;
	while(1)
	{
		$b1 = ord( $file[$st] );
			$st++;

		$ret <<= 7;
		$ret += ($b1 & 0x7f);

		if ( $b1 & 0x80 )
			continue;
		return $ret;
	}
	return $ret;
}
