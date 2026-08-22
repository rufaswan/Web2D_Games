<?php
require 'common.inc';

// rfs_fs_trial.exe
//   sub_5f9210 as rks_unpack
//   sub_598b80 as dict_copy
function undxa( $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$outsz = str2int($file, 0, 4);
	$insz  = str2int($file, 4, 4);
	$byco  = $file[8];

	if ( strlen($file) != $insz )
		return;
	printf("%8x , %8x , %2x , %s\n", $outsz, $insz, ord($byco), $fname);

	$ed = $insz;
	$st = 9;
	$data = '';
	while ( $st < $ed )
	{
		if ( $file[$st] != $byco )
		{
			$data .= $file[$st];
			$st++;
			continue;
		}

		if ( $file[$st+1] == $byco )
		{
			$data .= $byco;
			$st += 2;
			continue;
		}

		$b1 = ord( $file[$st+1] );
		if ( $b1 > ord($byco) )
			$b1--;
		$st += 2;

		$len = $b1 >> 3;
		if ( $b1 & 4 )
		{
			$b2 = str2int($file, $st, 1);
			$st++;
			$len += ($b2 << 5);
		}

		$len += 4;
		$b1a = $b1 & 3;
		switch ( $b1a )
		{
			case 0:
				$pos = str2int($file, $st, 1);
				$st++;
				break;
			case 1:
				$pos = str2int($file, $st, 2);
				$st += 2;
				break;
			case 2:
				$pos = str2int($file, $st, 3);
				$st += 3;
				break;
		}
		$pos++;
		if ( $pos > $len )
		{
			$data .= substr($data, strlen($data)-$pos, $len);
			continue;
		}

		while ( $len > $pos )
		{
			$data .= substr($data, strlen($data)-$pos, $pos);
			$len -= $pos;
		}
		if ( $len != 0 )
			$data .= substr($data, strlen($data)-$pos, $len);

	} // while ( $st < $ed )

	file_put_contents("$fname.dec", $data);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	undxa( $argv[$i] );
