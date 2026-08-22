<?php
require('/opt/class_tls2_xor.php');
require('/opt/rfslib.php');

function tls2pict( $pict, $seed )
{
	$imgp = fopen("{$pict}", "rb");
	$decp = fopen("{$pict}.dec", "wb");

	$st = 0;
	$tls2 = new TLS2Key;
		$tls2->set_seed($seed);

	while(1)
	{
		$lh = rfs_fgetint( $imgp, $st, 2 );
		if ( 0 == $lh )
			return;

		$key = $tls2->key();
		$lh ^= $key;
		rfs_fputint( $decp, $st, $lh, 2 );

		$st += 2;
	} // while(1)

	fclose($decp);
	fclose($imgp);
}

tls2pict( "PICT.IMG.5199000", 0x20a33218 ); // sect a332
tls2pict( "PICT.IMG.526e000", 0x20a4dc34 ); // sect a4dc
