<?php
require('/opt/rfslib.php');
$reslst = [];

function parse_entry( $fp, $pos, $parent )
{
	global $reslst;
	$t1 = rfs_fgetint( $fp, $pos+0, 4 );
	$t2 = rfs_fgetint( $fp, $pos+4, 4 );
	$t3 = rfs_fgetint( $fp, $pos+8, 4 );

	$str = "{$parent}/" . rfs_fgetstr0( $fp, $t1 );

	$t4 = rfs_fgetint( $fp, $pos+0xb, 1 );
	if ( $t4 == 0xff ) // IS_DIR
	{
		$reslst['DDT'][$t2] = sprintf("DDT , %8x , DIR  , %8x , %s", $t2, ~$t3+1, $str);
		//printf("%8x , DIR  , %8x , %s\n", $t2, ~$t3+1, $str);
		parse_dir( $fp, $t2, ~$t3+1, $str );
	}
	else // IS_FILE
	{
		$reslst['IMG'][$t2] = sprintf("IMG , %8x , FILE , %8x , %s", $t2, $t3, $str);
		//printf("%8x , FILE , %8x , %s\n", $t2, $t3, $str);
	}
}

function parse_dir( $fp, $pos, $cnt, $parent )
{
	for ( $n=0; $n < $cnt; $n++ )
	{
		$p = $pos + ($n * 0xc);
		parse_entry( $fp, $p, $parent );
	}
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
{
	$fp = fopen( $argv[$i], "rb" );
		if ( ! $fp )   exit();

	$reslst = [];
	parse_dir( $fp, 0, 1, "" );

	ksort( $reslst['DDT'] );
	ksort( $reslst['IMG'] );
	foreach( $reslst['DDT'] as $v )   printf("%s\n", $v);
	foreach( $reslst['IMG'] as $v )   printf("%s\n", $v);

	fclose($fp);
}
