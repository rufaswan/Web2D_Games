<?php
// $tid is multiple of 4 patch
// for Portrait of Ruin - Dragon Zombie
require "common.inc";

$file = file_get_contents("0.2.bak");
if ( empty($file) )  exit();

$ed = str2int($file, 8, 3);
$st = str2int($file, 4, 3);
while ( $st < $ed )
{
	$b = ord( $file[$st+12] );
	if ( $b % 4 )
		exit();
	$file[$st+12] = chr($b/4);
	$st += 16;
}
file_put_contents("0.2", $file);
