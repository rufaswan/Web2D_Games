<?php
require('/opt/rfslib.php');

function tm2exe( $fname )
{
	if ( strpos($fname, "SLPM") === FALSE )
		return;

	$exep = fopen( "{$fname}", "rb" );
	$outp = fopen( "{$fname}.cdpack", "wb+" );

	rfs_fcopy( $outp, $exep, 0x80 );
	$pad = rfs_fgetint( $exep, 0x18, 3 ) - 0x10000;
	$siz = rfs_fgetint( $exep, 0x1c, 4 );

	rfs_fputint( $outp, 0x18, 0x10000, 3 );
	rfs_fputint( $outp, 0x1c, $siz + $pad, 4 );
	fseek( $exep, 0x800, SEEK_SET );
	fseek( $outp, 0x800 + $pad, SEEK_SET );
	rfs_fcopy( $outp, $exep, $siz );

	$cdp = fopen("CDPACK00.BIN", "rb");
	$st = 0;
	$ed = 0x15a4;
	while ( $st < $ed )
	{
		$b = rfs_fgetint( $cdp, 0x801+$st, 1 );
		$b += 0x9c;
		rfs_fputint( $outp, 0x800+$st, $b, 1 );
		$st++;
	}
	fclose($cdp);

	fclose($exep);
	fclose($outp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	tm2exe( $argv[$i] );
