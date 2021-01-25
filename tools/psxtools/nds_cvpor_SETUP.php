<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "nds_cvpor.inc";

$gp_patch = array();

function sectsosc( &$ram, $game, $dir, $sk, $sv )
{
	global $gp_patch;
	$scid = 0;
	foreach ( $sv as $k => $v )
	{
		$v = explode('-', $v);
		switch ( $v[0] )
		{
			case 'ov':
				$v1 = (int)$v[1];
				nds_overlay( $ram, $dir, $v1 );
				$sk = "ov_{$v1}_{$sk}";
				break;
			case 'so':
				$v1 = hexdec($v[1]);
				if ( $v1 == 0 )
					break;
				$p = $gp_patch['ndsram']['files'][0] + ( $v1 * $gp_patch['ndsram']['files'][2] );
				$s = substr0($ram, $p+6);
				$fn = "$dir/$game/sosc/$sk.so";
				save_file($fn, load_file("$dir/data/$s"));
				break;
			case 'sc':
				$cnt = count($v);
				if ( $cnt == 2 )
				{
					$v1 = hexdec($v[1]);
					$v2 = $v1;
				}
				else
				if ( $cnt == 3 )
				{
					$v1 = hexdec($v[1]);
					$v2 = hexdec($v[2]);
				}
				else
					break;

				while ( $v1 <= $v2 )
				{
					$p = $gp_patch['ndsram']['files'][0] + ( $v1 * $gp_patch['ndsram']['files'][2] );
					$s = substr0($ram, $p+6);
					$fn = "$dir/$game/sosc/$sk.$scid.sc";
					save_file($fn, load_file("$dir/data/$s"));
					$scid++;
					$v1++;
				} // while ( $v1 < $v2 )
				break;
			case 'pal':
				$v1 = hexdec($v[1]);
				if ( $v1 == 0 )
					break;
				$cnt = ord( $ram[$v1+2] );
				$s = substr($ram, $v1+4, $cnt*0x20);
				$s = pal555($s);
				$fn = "$dir/$game/sosc/$sk.pal";
				save_file($fn, $s);
				break;
		} // switch ( $v[0] )
	} // foreach ( $sv as $k => $v )
	return;
}
//////////////////////////////
function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	global $gp_patch;
	$gp_patch = nds_patch($dir, "cvnds");
	if ( empty($gp_patch) )
		return;
	$ram  = nds_ram($dir);
	$game = $gp_patch['ndsram']['game'][0];
	arrayhex( $gp_patch['ndsram']['files'] );

	nds_game( $ram, $dir, $gp_patch['ndsram']['game'] );
	foreach ( $gp_patch['sosc'] as $sk => $sv )
		sectsosc($ram, $game, $dir, $sk, $sv);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
