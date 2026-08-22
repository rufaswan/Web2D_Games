<?php
require('/opt/rfslib.php');

function pak0ex( $pakn, $fname, $loc )
{
	$loc = hexdec($loc);

	$pakp = fopen( $pakn, "rb+" );
	$newp = fopen( $pakn.".new", "wb+" );

	$dp = rfs_fgetint( $pakp, 4, 2 ) * 0x800;
	fseek( $pakp, 0, SEEK_SET );
	rfs_fcopy( $newp, $pakp, $dp );

	$st = 4;
	while(1)
	{
		$ps = rfs_fgetint( $pakp, $st + 0, 2 ) * 0x800;
		$sz = rfs_fgetint( $pakp, $st + 2, 2 ) * 0x800;
		if ( 0 == $ps )
			return;

		if ( $loc == $ps )
		{
			$fp = fopen( $fname, "rb" );
			$sz = filesize( $fname );

			fseek( $newp, $dp, SEEK_SET );
			rfs_fcopy( $newp, $fp, $sz );

			fclose($fp);

			$sz = rfs_padd( $sz, 0x800 );
			rfs_fputint( $newp, $st + 0, $dp/0x800, 2 );
			rfs_fputint( $newp, $st + 2, $sz/0x800, 2 );

			$dp += $sz;
		}
		else
		{
			fseek( $pakp, $ps, SEEK_SET );
			fseek( $newp, $dp, SEEK_SET );
			rfs_fcopy( $newp, $pakp, $sz );

			rfs_fputint( $newp, $st + 0, $dp/0x800, 2 );
			rfs_fputint( $newp, $st + 2, $sz/0x800, 2 );
			$dp += $sz;
		}

		$st += 4;
	}

	fclose($pakp);

}

	pak0ex( $argv[1], $argv[2], $argv[3] );
/*
if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	pak0ex( $argv[$i] );
*/
