<?php
require "common.inc";

$gp_pix  = array();
$gp_clut = array();

function load_clut( &$mcb, $st )
{
	global $gp_clut;
	$gp_clut = array();

	$len = strlen($mcb);
	while ( $st < $len )
	{
		$pal = "";
		for ( $i=0; $i < 0x200; $i += 2 )
			$pal .= rgb555( $mcb[$st+$i+1] . $mcb[$st+$i+0] );
		$gp_clut[] = $pal;
		$st += 0x200;
	}
	return;
}

function sectmap( &$mcb, &$chb, $w, $dir, $st, $id )
{
	$map_w = $w * 8;
	$map_h = 0x20 * 8;

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);
	$pix['bgzero'] = true;

	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	printf("=== sectmap( %x , $dir , %x , $id )\n", $w, $st);
	echo "map : $map_w x $map_h\n";

	global $gp_clut;
	$map = "";
	for ( $y=0; $y < $map_h; $y += 8 )
	{
		for ( $x=0; $x < $map_w; $x += 8 )
		{
			$dat = ordint( $mcb[$st+3] . $mcb[$st+2] . $mcb[$st+1] . $mcb[$st+0] );
				$st += 4;
			$map .= sprintf("%8x ", $dat);

			$tid = ($dat & BIT16) / 2;

			$pix['hflip'] = $dat & 0x40000000;

			$pix['src']['pix'] = substr($chb, $tid*0x40, 0x40);
			$pix['src']['pal'] = $gp_clut[0];
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 8 )

		$map .= "\n";
	} // for ( $y=0; $y < $map_h; $y += 8 )

	echo "$map\n";
	savpix("$dir/$id", $pix);
	return;
}
//////////////////////////////
function pcrown( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$chb = load_file("$pfx.chb");
	$mcb = load_file("$pfx.mcb");
	if ( empty($chb) )  return;
	if ( empty($mcb) )  return;

	$dir = "{$pfx}_chbmcb";
	$len = strlen($mcb);
	switch ( $len )
	{
		case 0x2200:
			load_clut($mcb, 0x2000);
			sectmap($mcb, $chb, 0x40, $dir,      0, "40-0");
			sectmap($mcb, $chb, 0x3d, $dir,      0, "3d-0");
			break;
		case 0x4400:
			load_clut($mcb, 0x4000);
			sectmap($mcb, $chb, 0x40, $dir,      0, "40-0");
			sectmap($mcb, $chb, 0x20, $dir, 0x2000, "20-2");
			sectmap($mcb, $chb, 0x20, $dir, 0x3000, "20-3");
			break;
		case 0x10400:
			load_clut($mcb, 0x10000);
			sectmap($mcb, $chb, 0x20, $dir,      0, "20-00");
			sectmap($mcb, $chb, 0x20, $dir, 0x1000, "20-01");
			sectmap($mcb, $chb, 0x20, $dir, 0x2000, "20-02");
			sectmap($mcb, $chb, 0x20, $dir, 0x3000, "20-03");
			sectmap($mcb, $chb, 0x20, $dir, 0x4000, "20-04");
			sectmap($mcb, $chb, 0x20, $dir, 0x5000, "20-05");
			sectmap($mcb, $chb, 0x20, $dir, 0x6000, "20-06");
			sectmap($mcb, $chb, 0x20, $dir, 0x7000, "20-07");
			sectmap($mcb, $chb, 0x20, $dir, 0x8000, "20-08");
			sectmap($mcb, $chb, 0x20, $dir, 0x9000, "20-09");
			sectmap($mcb, $chb, 0x20, $dir, 0xa000, "20-10");
			sectmap($mcb, $chb, 0x20, $dir, 0xb000, "20-11");
			sectmap($mcb, $chb, 0x20, $dir, 0xc000, "20-12");
			sectmap($mcb, $chb, 0x20, $dir, 0xd000, "20-13");
			sectmap($mcb, $chb, 0x20, $dir, 0xe000, "20-14");
			sectmap($mcb, $chb, 0x20, $dir, 0xf000, "20-15");
			break;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

