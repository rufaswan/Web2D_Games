<?php
$err = "{$argv[0]}  pos  hex  hex\n";
if ( $argc != 4 )  exit($err);

$p  = hexdec($argv[1]) % 12;
$h1 = hexdec($argv[2]);
$h2 = hexdec($argv[3]);
$h = $h1 ^ $h2;
printf("[%x] %x ^ %x = %x\n", $p, $h1, $h2, $h);
