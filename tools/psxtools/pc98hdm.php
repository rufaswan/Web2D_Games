<?php
require "common.inc";

function pc98toc( &$file, $pos, $base )
{
	$toc = array();
	$fn = substr($file, $pos+0, 8);
		$fn = rtrim($fn, ' ');
	if ( preg_match("|[^A-Z0-9_.]|", $fn) )
		return $toc;

	$ex = substr($file, $pos+8, 3);
		$ex = rtrim($ex, ' ');
	if ( preg_match("|[^A-Z0-9_.]|", $ex) )
		return $toc;

	$ty = ord( $file[$pos+11] );
	$of = str2int($file, $pos+0x1a, 2);
	$sz = str2int($file, $pos+0x1c, 3);

	$dir = ( $ty & 0x10 ) ? "DIR " : "FILE";
	$off = $base + $of * 0x400;

	$toc = array($fn, $ex, $dir, $off, $sz);
	return $toc;
}

function scanpc98( &$file, $st, $ed, $base, $par )
{
	$func = __FUNCTION__;
	$txt = "";
	for ( $i=$st; $i < $ed; $i += 0x20 )
	{
		if ( $file[$i] == ZERO )
			continue;

		$toc = pc98toc($file, $i, $base);
		if ( empty($toc) )
			continue;

		list($fn,$ex,$dir,$off,$sz) = $toc;
		if ( $fn[0] == '.' )
			continue;

		if ( $dir == "FILE" )
		{
			$log = sprintf("%6x , %s , %6x , %s/%s.%s\n", $off, $dir, $sz, $par, $fn, $ex);
			save_file("$par/$fn.$ex", substr($file, $off, $sz));
		}
		else
		{
			$log = sprintf("%6x , %s , %6x , %s/%s\n", $off, $dir, $sz, $par, $fn);
			$log .= $func($file, $off, $off+0x400, $base, "$par/$fn");
		}
		echo $log;
		$txt .= $log;

	} // for ( $i=$st; $i < $ed; $i += 0x20 )
	return $txt;
}

function pc98( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// HDM -> 2HD/360rpm
	//     -> 0x400 bytes * 8 sectors * 2 surfaces * 0x4d cycles
	//     -> 0x134000
	// FDI is basically the HDM raw image, with a 0x1000-byte header
	if ( strlen($file) != 0x134000 )
		return;

	$st = 0x1400;
	$ed = 0x2400;
	$txt = scanpc98( $file, 0x1400, 0x2400, 0x2400, '.' );
	save_file("$fname.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pc98( $argv[$i] );
