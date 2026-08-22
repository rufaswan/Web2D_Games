<?php
require('/opt/rfslib.php');

function dirbin( $prefix )
{
	if ( is_dir($prefix) )  return;

	$dirp = fopen( "{$prefix}.DIR", "rb" );
	$binp = fopen( "{$prefix}.BIN", "rb" );
		if ( ! $dirp || ! $binp )   return;

	mkdir( $prefix, 0755 );
	$cnt = rfs_fgetint( $dirp, 4, 4 );
	printf("RIP $prefix.DIR/BIN set , cnt=%x\n", $cnt);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$ind = 8 + ($i * 0x14);
		$st = rfs_fgetint( $dirp, $ind+0, 4 );
		$sz = rfs_fgetint( $dirp, $ind+4, 4 );
		$fn = rfs_fgetstr0( $dirp, $ind+8 );

		$path = "{$prefix}/{$fn}";
		printf("%8x , %8x , %8x , $path\n", $ind, $st, $sz);

		$outp = fopen( $path, "wb" );
			if ( ! $outp )  return;

		fseek( $binp, $st, SEEK_SET );
		rfs_fcopy( $outp, $binp, $sz );

		fclose($outp);
	}

	fclose($binp);
	fclose($dirp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dirbin( $argv[$i] );
