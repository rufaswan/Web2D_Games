<?php
require "common.inc";

function sectmap( &$ram, $dir, $off )
{
	printf("=== sectmap( $dir , %x )\n", $off);
	$map_w = ord( $ram[$off+0] ) * 0x100;
	$map_h = ord( $ram[$off+1] ) * 0xc0;
	$off1 = str2int($ram, $off+ 4, 3); //
	$off2 = str2int($ram, $off+ 8, 3); //
	$off3 = str2int($ram, $off+12, 3); // tile layout
		$off += 16;
	echo "map : $map_w x $map_h\n";
	printf("off : %6x , %6x , %6x\n", $off1, $off2, $off3);

	$pos = $off3;
	$map = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$dat = str2int($ram, $pos, 2);
				$pos += 2;
			//$map .= sprintf("%4x ", $dat);

			$tid = $dat & 0xfff;
			//$pix['hflip'] = $dat & 0x4000;
			//$pix['vflip'] = $dat & 0x8000;
			//flag_warn("dat", $dat & 0x3000);

			$tile = str2int($ram, $off2 + ($tid*4), 4);;
			$map .= sprintf("%8x ", $tile);
			//$b1 = ord( $ram[$tile+0] );
			//$b2 = ord( $ram[$tile+1] );
			//$b3 = ord( $ram[$tile+2] );
			//$b4 = ord( $ram[$tile+3] );
			//
			//$tid = $b2 >> 4;
			//$sx = ($b1 & 0x0f) * 0x10;
			//if ( $b1 & 8 )
			//	$sx -= 0x80;
			//$sy = ($b1 >>   4) * 0x20;
			//if ( $b1 & 8 )
			//	$sy += 0x10;
			//$cid = $b4;

		} // for ( $x=0; $x < $map_w; $x += 0x10 )

		$map .= "\n";
	} // for ( $y=0; $y < $map_h; $y += 0x10 )

	echo "$map\n";
	return;
}
//////////////////////////////
function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$pat = nds_patch($dir, 'cvnds');
	if ( empty($pat) )
		return;
	$ram = nds_ram($dir);
	$y9  = file_get_contents("$dir/y9.bin");

	//var_dump( $pat['map'] );
	$ed = strlen($ram);
	$st = 0;
	foreach ( $pat['map'] as $mk => $mv )
	{
		foreach ( $mv as $mvv )
		{
			if ( strpos($mvv, 'ov-') !== false )
			{
				$mvv = str_replace('ov-', '', $mvv);
				$st = nds_overlay( $ram, $y9, $dir, (int)$mvv );
			}
		} // foreach ( $mv as $mvv )

		while ( str2int($ram, $st, 3) == 0 && $st < $ed )
			$st += 4;

		while ( str2int($ram, $st+4, 2) == 0 && $st < $ed )
		{
			$off = str2int($ram, $st+12, 3);
			if ( $off != 0 )
				sectmap($ram, "$dir/$mk", $off);
			$st += 0x10;
		}
	} // foreach ( $pat['map'] as $mk => $mv )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
