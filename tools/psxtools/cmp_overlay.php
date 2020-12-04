<?php
/*
[license]
[/license]
 */
//require "common.inc";
$MSG = "{$argv[0]}  RAM_FILE  OFFSET...  OVERLAY_DIR\n";

$gp_file = "";
$gp_dir  = "";
$gp_ram  = array();
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file( $argv[$i] ) )
		$gp_file = file_get_contents( $argv[$i] );
	else
	if ( is_dir( $argv[$i] ) )
		$gp_dir = rtrim($argv[$i], '/\\');
	else
		$gp_ram[] = hexdec( $argv[$i] );
}

if ( empty($gp_file) || empty($gp_dir) || empty($gp_ram) )
	exit($MSG);

foreach ( scandir($gp_dir) as $f )
{
	if ( $f[0] == '.' )
		continue;
	$over = file_get_contents("$gp_dir/$f");
	foreach ( $gp_ram as $off )
	{
		$sz = strlen($over);
		$b1 = substr($gp_file, $off, (int)($sz/2));
		if ( strpos($over, $b1) !== false )
			printf("%x , %s\n", $off, "$gp_dir/$f");
	}
}
