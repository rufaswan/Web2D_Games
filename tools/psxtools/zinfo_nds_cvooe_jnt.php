<?php
require 'common.inc';

function jntfile( &$file, $off )
{
	$txt = '';
	$b1 = ord( $file[2] );
	$fn = substr($file, 3, $b1);

	$head = substr($file, 0x22, 0x2d-0x22);
	$txt .= debug($head, $fn);
	$txt .= "\n";

	$cjnt = ord( $file[0x26] );
	$cjnt_inv = ord( $file[0x27] );
	$cjnt_vis = ord( $file[0x28] );
	$chit = ord( $file[0x29] );
	$cpss = ord( $file[0x2a] );
	$cpnt = ord( $file[0x2b] );
	$canm = ord( $file[0x2c] );

	$pos = 0x30;
	$txt .= sprintf("[%8x] joint = vis %x + inv %x = %x\n", $pos + $off, $cjnt_vis, $cjnt_inv, $cjnt);
	for ( $i=0; $i < $cjnt; $i++ )
	{
		$fn = sprintf('  %2x', $i);
		$b1 = substr($file, $pos, 4);
			$pos += 4;
		$txt .= debug($b1, $fn);
	} // for ( $i=0; $i < $cjnt; $i++ )
	$txt .= "\n";

	$txt .= sprintf("[%8x] pose = %x\n", $pos + $off, $cpss);
	for ( $i=0; $i < $cpss; $i++ )
	{
		$fn = sprintf('  %2x', $i);
		$b1 = substr($file, $pos, 2);
			$pos += 2;
		$txt .= debug($b1, $fn);
		$txt .= sprintf("  [%8x]\n", $pos + $off);

		for ( $j=0; $j < $cjnt; $j++ )
		{
			$fn = sprintf('    %2x,%2x', $i, $j);
			$b1 = substr($file, $pos, 4);
				$pos += 4;
			$txt .= debug($b1, $fn);
		} // for ( $j=0; $j < $cjnt; $j++ )
	} // for ( $i=0; $i < $cjnt; $i++ )
	$txt .= "\n";

	$txt .= sprintf("[%8x] hitbox = %x\n", $pos + $off, $chit);
	for ( $i=0; $i < $chit; $i++ )
	{
		$fn = sprintf('  %2x', $i);
		$b1 = substr($file, $pos, 8);
			$pos += 8;
		$txt .= debug($b1, $fn);
	} // for ( $i=0; $i < $cjnt; $i++ )
	$txt .= "\n";

	$txt .= sprintf("[%8x] point = %x\n", $pos + $off, $cpnt);
	for ( $i=0; $i < $cpnt; $i++ )
	{
		$fn = sprintf('  %2x', $i);
		$b1 = substr($file, $pos, 4);
			$pos += 4;
		$txt .= debug($b1, $fn);
	} // for ( $i=0; $i < $cjnt; $i++ )
	$txt .= "\n";

	$txt .= sprintf("[%8x] draw = %x\n", $pos + $off, $cjnt_vis);
	for ( $i=0; $i < $cjnt_vis; $i++ )
	{
		$txt .= sprintf("  %2x : %2x\n", $i, ord($file[$pos]));
			$pos++;
	} // for ( $i=0; $i < $cjnt_vis; $i++ )
	$txt .= "\n";

	$txt .= sprintf("[%8x] anim = %x\n", $pos + $off, $canm);
	for ( $i=0; $i < $canm; $i++ )
	{
		$cnt = ord( $file[$pos] );
			$pos++;
		$txt .= sprintf("  %2x : %2x\n", $i, $cnt);
		$txt .= sprintf("  [%8x]\n", $pos + $off);

		for ( $j=0; $j < $cnt; $j++ )
		{
			$fn = sprintf('    %2x,%2x', $i, $j);
			$b1 = substr($file, $pos, 3);
				$pos += 3;
			$txt .= debug($b1, $fn);
		}
	} // for ( $i=0; $i < $canm; $i++ )
	$txt .= "\n";

	return $txt;
}

function cvooe( $fname, $off )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$p1 = strpos($file, '.opd');
	if ( $p1 === false )
		return;

	$p1 += 4;
	$p2  = 3 + ord($file[2]);
	if ( $p1 !== $p2 )
		return;

	$txt = jntfile($file, $off);
	save_file("$fname.txt", $txt);
	return;
}

$off = 0;
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		cvooe( $argv[$i], $off );
	else
		$off = hexdec($argv[$i]);
}

/*
joint 3
	00 01 02 03 04 05 06 07

	00 *all*
	01
		j_alessi00.jnt  j_alessibak.jnt
		j_armag2.jnt
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_dhum00.jnt
		j_dino00.jnt
		j_fk.jnt
		j_fran00.jnt  j_fran01.jnt  j_fran03.jnt
		j_geva0.jnt  j_geva_orz.jnt
		j_gk.jnt  j_gk_b.jnt
		j_golem0.jnt
		j_guru00.jnt
		j_hums00.jnt
		j_kani00.jnt
		j_red00.jnt
		j_run.jnt
		j_wpnm00.jnt
	02
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_dino00.jnt
		j_dra00.jnt
		j_fk.jnt
		j_fran02.jnt
		j_geva0.jnt  j_geva_orz.jnt
		j_gk.jnt  j_gk_b.jnt
		j_golem0.jnt
		j_grav00.jnt
	03
		j_armag2.jnt
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_dem.jnt  j_frdm.jnt  j_lddm.jnt  j_sedm.jnt  j_thdm.jnt
		j_dhum00.jnt
		j_grav00.jnt
		j_guru00.jnt
		j_hums00.jnt
		j_kani00.jnt
		j_red00.jnt
		j_wpnm00.jnt
	04 *all*
	05
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_fk.jnt
		j_fran02.jnt
		j_gk.jnt  j_gk_b.jnt
	06
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_fran00.jnt  j_fran01.jnt  j_fran02.jnt  j_fran03.jnt
		j_run.jnt
	07
		j_fran00.jnt  j_fran01.jnt  j_fran02.jnt  j_fran03.jnt
		j_geva0.jnt  j_geva_orz.jnt
		j_golem0.jnt
		j_kani00.jnt
		j_run.jnt
 */
