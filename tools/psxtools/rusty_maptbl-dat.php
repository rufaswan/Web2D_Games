<?php
require "common.inc";

$gp_clut = "";

function sectpart( &$dat, $pfx, $pos, $id, $w, $h )
{
	printf("== sectpart( $pfx , %x , $id , $w , $h )\n", $pos);
	$bk = $w * $h / 8;

	global $gp_clut;
	$pal = ( empty($gp_clut) ) ? grayclut(16) : $gp_clut;

	$rgba = "RGBA";
	$rgba .= chrint($w, 4);
	$rgba .= chrint($h*2, 4);
	for ( $y=0; $y < $h; $y++ )
	{
		$line = "";
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b0 = ord( $dat[$pos + 0*$bk] ); // mask
			$b1 = ord( $dat[$pos + 1*$bk] );
			$b2 = ord( $dat[$pos + 2*$bk] );
			$b3 = ord( $dat[$pos + 3*$bk] );
			$b4 = ord( $dat[$pos + 4*$bk] );
				$pos++;

			$j = 8;
			while ( $j > 0 )
			{
				$j--;
				$b01 = ($b0 >> $j) & 1; // mask
				$b11 = ($b1 >> $j) & 1;
				$b21 = ($b2 >> $j) & 1;
				$b31 = ($b3 >> $j) & 1;
				$b41 = ($b4 >> $j) & 1;
				$bj = ($b41 << 3) | ($b31 << 2) | ($b21 << 1) | ($b11 << 0);

				$line .= substr($pal, $bj*4, 3); // for RGB
				$line .= ( $b01 ) ? BYTE : ZERO; // for A
			}
		} // for ( $x=0; $x < $w; $x += 8 )

		$rgba .= $line;
		$rgba .= $line;
	} // for ( $y=0; $y < $h; $y++ )

	$fn = sprintf("$pfx/%04d.rgba", $id);
	save_file($fn, $rgba);
	return;
}

function tbldat( &$tbl, &$dat, $pfx )
{
	printf("== tbldat( $pfx )\n");

	$cnt = str2int($tbl, 0, 2);
	$pos = 0;
	for ( $i=0; $i < $cnt; $i++ )
	{
		// 0  1  2  3     4 5  6 7  8  9  a  b
		// -  -  -  anim  w    h    -  -  -  -
		$p = 8 + ($i * 12);
		$w = str2int($tbl, $p+4, 2);
		$h = str2int($tbl, $p+6, 2);
		debug( substr($tbl, $p, 12) );

		sectpart( $dat, $pfx, $pos, $i, $w, $h );
		$pos += ($w * $h * 5 / 8);
	}
	return;
}
//////////////////////////////
function sectmap( &$map, &$dat, $pfx, $map_w, $map_h )
{
	printf("== sectmap( $map_w , $map_h )\n");

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);
	$pix['bgzero'] = true;

	$pix['src']['w'] = 0x20;
	$pix['src']['h'] = 0x10;

	global $gp_clut;
	$pos = 4;
	$mdt = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x20 )
		{
			$b1 = ord( $map[$pos] );
				$pos++;
			$mdt .= sprintf("%2x ", $b1);

			$pix['src']['pix'] = substr($dat, $b1*0x200, 0x200);
			$pix['src']['pal'] = $gp_clut;
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x20 )
		$mdt .= "\n";

	} // for ( $y=0; $y < $map_h; $y += 0x10 )
	echo "$mdt\n";

	savpix($pfx, $pix);
	return;
}

function loadtexx( &$dat )
{
	if ( strlen($dat) != 0x10000 )
		return "";
	printf("== loadtexx() = 256\n");

	$pix = "";
	for ( $i=0; $i < 0x4000; $i++ )
	{
		$b0 = ord( $dat[$i+0     ] );
		$b1 = ord( $dat[$i+0x4000] );
		$b2 = ord( $dat[$i+0x8000] );
		$b3 = ord( $dat[$i+0xc000] );

		$j = 8;
		while ( $j > 0 )
		{
			$j--;
			$b01 = ($b0 >> $j) & 1;
			$b11 = ($b1 >> $j) & 1;
			$b21 = ($b2 >> $j) & 1;
			$b31 = ($b3 >> $j) & 1;
			$bj = ($b31 << 3) | ($b21 << 2) | ($b11 << 1) | ($b01 << 0);
			$pix .= chr($bj);
		}
	} // for ( $i=0; $i < 0x4000; $i++ )
	return $pix;
}

function mapdat( &$map, &$dat, $pfx )
{
	printf("== mapdat( $pfx )\n");
	$pix = loadtexx($dat);

	$map_w = str2int($map, 0, 2) * 0x20;
	$map_h = str2int($map, 2, 2) * 0x10;
	echo "map : $map_w x $map_h\n";

	sectmap($map, $pix, $pfx, $map_w, $map_h);
	return;
}
//////////////////////////////
function loadclut( $fname )
{
	$rgb = file_get_contents($fname);
	if ( empty($rgb) || strlen($rgb) != 0x30 )
		return;
	printf("== loadclut( $fname ) = 16\n");

	global $gp_clut;
	$gp_clut = "";
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		$r = ord( $rgb[$i+0] );
		$g = ord( $rgb[$i+1] );
		$b = ord( $rgb[$i+2] );
		$r = int_clamp($r * 0x11, 0, BIT8);
		$g = int_clamp($g * 0x11, 0, BIT8);
		$b = int_clamp($b * 0x11, 0, BIT8);
		$gp_clut .=  chr($r) . chr($g) . chr($b) . BYTE;
	}
	return;
}

function magclut( $fname )
{
	$mag = file_get_contents($fname);
	if ( empty($mag) )  return;
	printf("== magclut( $fname ) = 16\n");

	$mgc = substr0($mag, 0, chr(0x1a));
	if ( substr($mgc, 0, 6) != "MAKI02" )
		return;

	global $gp_clut;
	$gp_clut = "";

	$pos = strlen($mgc) + 1 + 0x20;
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		// in GRB order
		$gp_clut .= $mag[$pos+1] . $mag[$pos+0] . $mag[$pos+2] . BYTE;
		$pos += 3;
	}
	return;
}

function rusty( $fname )
{
	if ( stripos($fname, '.rgb') !== false )
		return loadclut( $fname );
	if ( stripos($fname, '.mag') !== false )
		return magclut( $fname );

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$f1 = load_file("$pfx.map");
	$f2 = load_file("$pfx.dat");
	if ( ! empty($f1) && ! empty($f2) )
		return mapdat($f1, $f2, $pfx);

	$f1 = load_file("$pfx.tbl");
	$f2 = load_file("$pfx.dat");
	if ( ! empty($f1) && ! empty($f2) )
		return tbldat($f1, $f2, $pfx);
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );

/*
bg1 be(+2f80)
	0/2          1            3
	ff ff ff fa  -- -- -- --  ff ff ff fa
	ff ff ff f7  -- -- -- 02  ff ff ff f5
	ff ff ff ea  -- -- -- --  ff ff ff ea
	ff ff ff f5  -- -- -- --  ff ff ff f5
	ff ff fa aa  -- -- -- --  ff ff fa aa
	ff ff f7 ff  -- -- 02 aa  ff ff f5 55
	ff ff ea aa  -- -- -- --  ff ff ea aa
	ff ff d5 55  -- -- -- --  ff ff d5 55

	1011 = d  rgb
	1110 = 7

rusty (page0.tbl) palette
	0
	1
	2  238
	3  46b
	4  79d
	5  9bf
	6
	7
	8
	9  b75
	a  c97
	b  9b2
	c  274
	d
	e
	f  cdd

rusty
story1.com
	bg1.rgb bg1.map page3_1r.tbl page3_1l.tbl page3_1m.tbl boss1.tbl kime.tbl clear1.tbl
story2.com
	bg2n.rgb bg2n.map page3_2r.tbl page3_2l.tbl page3_2m.tbl
	bg2.rgb bg2.map boss2_r.tbl boss2_l.tbl boss2_m.tbl kime.tbl clear2.tbl
story3.com
	bg3.rgb bg3.map page3_3r.tbl page3_3l.tbl page3_3m.tbl boss3_r.tbl boss3_l.tbl boss3_m.tbl kime.tbl clear3.tbl
story4.com
	bg4.rgb bg4.map page3_4r.tbl page3_4l.tbl page3_4m.tbl boss4_r.tbl boss4_l.tbl boss4_m.tbl kime.tbl
story5.com
	bg5_1.rgb bg5_1.map page3_5r.tbl page3_5l.tbl page3_5m.tbl bg5_2.map bg5_2spr.tbl
	bg5_3.rgb bg5_3.map bg5_3spr.tbl kime.tbl clear5.tbl
story6.com
	bg6.rgb bg6.map page3_6r.tbl page3_6l.tbl page3_6m.tbl bg6_2.map boss6_r.tbl boss6_l.tbl boss6_m.tbl kime.tbl clear6.tbl
story7.com
	bg7.rgb bg7.map page3_7r.tbl page3_7l.tbl page3_7m.tbl boss7.tbl kime.tbl clear7.tbl
story8.com
	bg8.rgb bg8.map page3_8r.tbl page3_8l.tbl page3_8m.tbl
	bg8b.rgb bg8b.map bg8d.map boss8_r.tbl boss8_l.tbl boss8_m.tbl kime.tbl clear8.tbl
story9.com
	bg9.rgb bg9a.map page3_9r.tbl page3_9l.tbl page3_9m.tbl bg9b.map bg9c.map boss4_r.tbl boss4_l.tbl boss4_m.tbl kime.tbl clear1.tbl
story10.com
	bg10.rgb bg10a.map bg10b.map bg10c.map
	lasbosbg.rgb lasbosbg.map boss10_r.tbl boss10_l.tbl boss10_m.tbl boss11_r.tbl boss11_l.tbl boss11_m.tbl lastboss.tbl kime.tbl clear10.tbl

tmix
story1.com
	kime1.tbl kime2.tbl kime3.tbl
	bg1.rgb bg1a.map bg1b.map bg1c.map page3_1r.tbl page3_1l.tbl page3_1m.tbl boss1_r.tbl boss1_l.tbl boss1_m.tbl
story2.com
	kime1.tbl kime2.tbl kime3.tbl
	bg2.rgb bg2a.map bg2c.map page3_2r.tbl page3_2l.tbl page3_2m.tbl boss2_b.tbl
story3.com
	kime1.tbl kime2.tbl kime3.tbl
	bg3.rgb bg3a.map bg3b.map bg3c.map page3_3r.tbl page3_3l.tbl page3_3m.tbl boss3_r.tbl boss3_l.tbl boss3_m.tbl
story4.com
	kime1.tbl kime2.tbl kime3.tbl
	bg4.rgb bg4a.map bg4b.map bg4c.map page3_4r.tbl page3_4l.tbl page3_4m.tbl boss4.tbl
story5.com
	kime1.tbl kime2.tbl kime3.tbl
	bg5.rgb bg5a.map bg5b.map bg5c.map page3_5r.tbl page3_5l.tbl page3_5m.tbl boss5_r.tbl boss5_l.tbl boss5_m.tbl
story6.com
	kime1.tbl kime2.tbl kime3.tbl
	bg6.rgb bg6a.map bg6b.map bg6c.map bg6d.map bg6e.map bg6f.map page3_6r.tbl page3_6l.tbl page3_6m.tbl boss6_r.tbl boss6_l.tbl boss6_m.tbl
story7.com
	kime1.tbl kime2.tbl kime3.tbl
	bg7.rgb bg7a.map bg7b.map bg7c.map page3_7r.tbl page3_7l.tbl page3_7m.tbl boss7_r.tbl boss7_l.tbl boss7_m.tbl
story8.com
	kime1.tbl kime2.tbl kime3.tbl
	bg8.rgb bg8a.map bg8b.map bg8c.map bg8d.map page3_8r.tbl page3_8l.tbl page3_8m.tbl boss8_1.tbl boss8_2.tbl boss8_3.tbl
story9.com
	kime1.tbl kime2.tbl kime3.tbl
	bg9.rgb bg9a.map bg9b.map bg9c.map page3_9r.tbl page3_9l.tbl page3_9m.tbl boss9_r.tbl boss9_l.tbl boss9_m.tbl
story10.com
	kime1.tbl kime2.tbl kime3.tbl
	bg10.rgb bg10a.map bg10b.map bg10bs_1.map bg10bs_2.map bg10bs_3.map bg10bs_4.map bg10l_bs.map boss7L_r.tbl boss7L_l.tbl boss2_LS.tbl boss5L_r.tbl boss5L_l.tbl boss5L_m.tbl boss4L.tbl boss10_r.tbl boss10_l.tbl boss10_m.tbl
storyed.com
	kime1.tbl kime2.tbl kime3.tbl
	op_3.rgb op_3.map page0_op.tbl
storyop.com
	kime1.tbl kime2.tbl kime3.tbl
	op.rgb op_1.map
	op_2.rgb op_2.map
	op_3.rgb op_3.map op_4.map
	op_4.rgb op_5.map page0_op.tbl page1_op.tbl page12op.tbl page3_op.tbl
*/
