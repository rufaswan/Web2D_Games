<?php
require('/opt/rfslib.php');

function items( $fname )
{
	$fp = fopen( "{$fname}", "rb" );

	$st = 0x795a8;
	$ed = 0x7991c;
	$id = 0;
	while ( $st < $ed )
	{
		printf("%2x , ", $id);
		$base = rfs_fgetint( $fp, $st, 3 );

		printf("%2x , ", rfs_fgetint( $fp, $base + 0x0, 1 ) ); // Effects
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1, 1 ) ); //
		printf("%4x , ", rfs_fgetint( $fp, $base + 0x2, 2 ) ); // PAD
		printf("%4x , ", rfs_fgetint( $fp, $base + 0xc, 2 ) ); // +Ladies
		printf("%4x , ", rfs_fgetint( $fp, $base + 0xe, 2 ) ); // icons [x-ETC-DAR-EAR  AIR-WAT-FIR-LIG]  [x-AGI-RST-INT  DEF-ATK-EP-HP]
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x10, 1 ) ); // +ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x11, 1 ) ); // +DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x12, 1 ) ); // +INT
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x13, 1 ) ); // +RST
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x14, 1 ) ); // +AGI
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x15, 1 ) ); // +HP
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x16, 1 ) ); // +EP
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x17, 1 ) ); // +HIT
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x18, 1 ) ); // +Light ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x19, 1 ) ); // +Fire  ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1a, 1 ) ); // +Water ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1b, 1 ) ); // +Wind  ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1c, 1 ) ); // +Earth ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1d, 1 ) ); // +Dark  ATK
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1e, 1 ) );
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x1f, 1 ) ); // +Critical
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x20, 1 ) ); // +Light DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x21, 1 ) ); // +Fire  DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x22, 1 ) ); // +Water DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x23, 1 ) ); // +Wind  DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x24, 1 ) ); // +Earth DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x25, 1 ) ); // +Dark  DEF
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x26, 1 ) ); // +Normal Immune
		printf("%2x , ", rfs_fgetint( $fp, $base + 0x27, 1 ) ); // PAD

		// item name
		$fjp = rfs_fgetstr0( $fp, $base + 0x28, TRUE );
		$fjp = exec("printf \"{$fjp}\" | iconv -f sjis -t utf-8");
		echo $fjp . " , ";

		// item desciption
		$ps1 = rfs_fgetint( $fp, $base + 4, 3 );
		$fjp = rfs_fgetstr0( $fp, $ps1, TRUE );
		$fjp = exec("printf \"{$fjp}\" | iconv -f sjis -t utf-8");
		echo $fjp . " , ";

		// item ruby name
		$ps2 = rfs_fgetint( $fp, $base + 8, 3 );
		$fjp = rfs_fgetstr0( $fp, $ps2, TRUE );
		$fjp = exec("printf \"{$fjp}\" | iconv -f sjis -t utf-8");
		echo $fjp . " , ";

		echo "\n";
		$st += 4;
		$id++;
	}

	fclose($fp);
}

	items("slps.ram");
/*
if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dummy( $argv[$i] );
*/
