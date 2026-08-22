<?php
require('/opt/rfslib.php');

function reexs( $fname, $echo = false )
{
	if ( ! $echo )  printf("reexs : {$fname}\n");
	$decp = fopen( "{$fname}", "rb" );
	$exsp = fopen( "{$fname}.exs", "wb" );

	$size = filesize($fname);
	rfs_fputint( $exsp, 0, 0x1535845, 4 );
	rfs_fputint( $exsp, 4, $size, 4 );

	fseek( $decp, 0, SEEK_SET );
	fseek( $exsp, 8, SEEK_SET );
	for ( $i=0; $i < $size; $i += 8 )
	{
		$w1 = rfs_fgetints( $decp, 4 );
		$w2 = rfs_fgetints( $decp, 4 );
		rfs_fputints( $exsp, -1, 1 );
		rfs_fputints( $exsp, $w1, 4 );
		rfs_fputints( $exsp, $w2, 4 );
	}

	fclose($decp);
	fclose($exsp);

	if ( $echo )
		echo file_get_contents( "{$fname}.exs" );
}

if ( $argc == 1 )   exit();

$i = 1;
while ( $i < $argc )
{
	$opt = $argv[$i];
	if ( "-echo" == $opt )
	{
		reexs( $argv[$i+1], true );
		$i += 2;
	}
	else
		reexs( $argv[$i] );
}
