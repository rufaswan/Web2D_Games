<?php
/*
[license]
[/license]
 */
define("PARTSIZE", 0x20);
$gp_file = array();

function partcmp( $pos )
{
	global $gp_file;
	if ( empty($gp_file) )
		return;

	$cnt = count($gp_file);
	$diff = false;
	for ( $c1=0; $c1 < ($cnt-1); $c1++ )
	{
		for ( $c2=$c1+1; $c2 < $cnt; $c2++ )
		{
			$b1 = substr($gp_file[$c1][1], $pos, PARTSIZE);
			$b2 = substr($gp_file[$c2][1], $pos, PARTSIZE);
			if ( $b1 != $b2 )
				$diff = printf("DIFF %x @ %s != %s\n", $pos, $gp_file[$c1][0], $gp_file[$c2][0]);
		}
	}
	if ( ! $diff )
		printf("ALL SAME %x\n", $pos);
	return;
}

for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( file_exists($opt) )
		$gp_file[] = array($opt, file_get_contents($opt));
	else
	{
		$pos = hexdec($opt);
		partcmp( $pos );
	}
}
