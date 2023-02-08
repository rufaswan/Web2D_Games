<?php
printf("%s  DEC_STR  ENC_HEX\n", $argv[0]);
if ( $argc !== 3 )  exit();

// dec   enc      = xor
// .BMP  d1bdb2af = ffffffff
$dec = $argv[1];
$enc = hex2bin($argv[2]);
if ( strlen($dec) !== strlen($enc) )
	exit();

$xor = '';
$len = strlen($dec);
for ( $i=0; $i < $len; $i++ )
{
	$d = ord($dec[$i]);
	$e = ord($enc[$i]);
	$x = $d ^ $e;
	$xor .= sprintf('%02x ', $x);
} // for ( $i=0; $i < $len; $i++ )

echo "DEC $dec , XOR $xor\n";
