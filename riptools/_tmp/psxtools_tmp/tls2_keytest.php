<?php
require('/opt/class_tls2_xor.php');
require('/opt/rfslib.php');

if ( 1 == $argc )  exit();

$tls2 = new TLS2Key;
$i = 1;
while ( $i < $argc )
{
	$opt = $argv[$i];
	switch( $opt )
	{
		case "-s":
			$str = $argv[$i+1];
			printf("=== STR {$str} ===\n");
			$tls2->set_str( $str );
			for ( $r=0; $r < 32; $r++ )
				$tls2->key();
			$i += 2;
			break;
		case "-x":
			$hex = hexdec( $argv[$i+1] );
			printf("=== SEED %x ===\n", $hex);
			$tls2->set_seed( $hex );
			for ( $r=0; $r < 32; $r++ )
				$tls2->key();
			$i += 2;
			break;
		default:
			$i++;
			break;
	}
}

/*
DAY1.TIM
DAY2.TIM
DAY3.TIM
0x20a33218 // seed ALBUM akane p2 y1 [0x3218, 0x78, 0x1aa1]
0x30cfa811 // seed TITLE
*/
