<?php
require('/opt/rfslib.php');

function relzs( $fname, $echo = false )
{
	if ( ! $echo )  printf("relzs : {$fname}\n");
	$decp = fopen( "{$fname}", "rb" );
	$lzsp = fopen( "{$fname}.lzs", "wb" );

	$size = filesize($fname);
	rfs_fputstr( $lzsp, 0, "Lzs1" );

	$size = ($size * 5) / 4;
	$size = rfs_padd( $size + 8, 4 );
	rfs_fputint( $lzsp, 4, $size, 4 );

	fseek( $decp, 0, SEEK_SET );
	fseek( $lzsp, 8, SEEK_SET );
	for ( $i=0; $i < $size; $i += 8 )
	{
		$w1 = rfs_fgetints( $decp, 4 );
		$w2 = rfs_fgetints( $decp, 4 );
		rfs_fputints( $lzsp, -1, 1 );
		rfs_fputints( $lzsp, $w1, 4 );
		rfs_fputints( $lzsp, $w2, 4 );
	}

	fclose($decp);
	fclose($lzsp);

	if ( $echo )
		echo file_get_contents( "{$fname}.lzs" );
}

if ( $argc == 1 )   exit();

$i = 1;
while ( $i < $argc )
{
	$opt = $argv[$i];
	if ( "-echo" == $opt )
	{
		relzs( $argv[$i+1], true );
		$i += 2;
	}
	else
		relzs( $argv[$i] );
}
