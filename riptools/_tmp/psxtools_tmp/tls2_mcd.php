<?php
require('/opt/class_tls2_xor.php');
require('/opt/rfslib.php');

$dirp = fopen("MCD.DIR", "rb");
$imgp = fopen("MCD.IMG", "rb");
$decp = fopen("MCD.IMG.dec", "wb");

$tls2 = new TLS2Key;
$dir_st = 0;
while(1)
{
	$fsz = rfs_fgetint( $dirp, $dir_st + 0xa, 2 );
	$fps = rfs_fgetint( $dirp, $dir_st + 0xc, 2 );
	if ( BIT16 == $fsz )
		exit();

	$fnm = rfs_fgetstr0( $dirp, $dir_st + 0 );
	$tls2->set_str($fnm);
	printf("=== DEC_MCD $fnm ===\n");

	$max = $fsz * 0x800;
	$bas = $fps * 0x800;
	for ( $siz = 0; $siz < $max; $siz += 2 )
	{
		$lh = rfs_fgetint( $imgp, $bas + $siz, 2 );
		if ( 0 == $lh )
			continue;

		$key = $tls2->key();
		$lh ^= $key;
		rfs_fputint( $decp, $bas + $siz, $lh, 2 );

	} // while(1)

	rfs_padd($decp, 0x800);
	$dir_st += 0x10;
} // while(1)

fclose($decp);
fclose($imgp);
fclose($dirp);

/*
$tls2->set_seed( 0x20a33218 );
for ( $i=0; $i < 32; $i++ )
	$tls2->key();
$tls2->set_str( "DAY1.TIM" );
for ( $i=0; $i < 32; $i++ )
	$tls2->key();
//tls2keys( 0x3218, 0x78, 0x1aa1 );
//tls2gen( 0x20a33218 ); // seed ALBUM akane p2 y1
//tls2gen( 0x30cfa811 ); // seed TITLE
//str2key( "DAY1.TIM" );
*/
