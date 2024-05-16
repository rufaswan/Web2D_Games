<?php
$txt = str_repeat("\x61", 0x100);

function mycrc( $txt )
{
	// // https://stackoverflow.com/questions/21001659/crc32-algorithm-implementation-in-c-without-a-look-up-table-and-with-a-public-li
	// https://web.archive.org/web/20190108202303/http://www.hackersdelight.org/hdcodetxt/crc.c.txt
	// https://web.archive.org/web/20190716204559/http://www.hackersdelight.org/permissions.htm
	$len = strlen($txt);
	$crc = 0xffffffff;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord($txt[$i]);
		$crc ^= $b;
		$j = 8;
		while ( $j > 0 )
		{
			$j--;
			$mask = -($crc & 1);
			$crc  = ($crc >> 1) ^ (0xedb88320 & $mask);
		} // while ( $j > 0 )
	} // for ( $i=0; $i < $len; $i++ )
	return ($crc ^ 0xffffffff);
}

$crc1 = crc32($txt);
$crc2 = mycrc($txt);
printf("crc %x , %x\n", $crc1, $crc2);
