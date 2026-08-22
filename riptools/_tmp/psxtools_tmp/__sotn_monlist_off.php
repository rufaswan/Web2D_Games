<?php
function str2int( &$str, $pos, $byte )
{
	$sub = substr($str, $pos, $byte);
	return ordint($sub);
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
//////////////////////////////

function sotn( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$file = file_get_contents("$dir/$dir.bin");
	$off = str2int($file, 12, 3);
	$off -= 0x180000;

	$b1 = str2int($file, $off+0x14, 4);
	$b2 = str2int($file, $off+0x1c, 4);
	$b3 = str2int($file, $off+0x20, 4);
	$b4 = str2int($file, $off+0x28, 4);
	printf("%s , %8x , %8x , %8x , %8x\n", $dir, $b1, $b2, $b3, $b4);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );
