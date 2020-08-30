<?php
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";

define("CANV_S", 0x200);
define("SCALE", 1.0);
//define("DRY_RUN", true);

define("PCROWN_EXE" , "0.bin");
define("PCROWN_CLOF", 0x98a8e);

$gp_pix  = array();
$gp_clut = array();

function sectquad( &$pix, $dat, &$tid )
{
	//return;
	// 0 1  2  3   4  5   6  7   8  9   a  b
	// tid  x1 y1  x2 y2  x3 y3  x4 y4  -  sign
	$pix['vertex'] = array();
	$pix['vertex'][0][0] = ord( $dat[2] ) * SCALE;
	$pix['vertex'][0][1] = ord( $dat[3] ) * SCALE;
	$pix['vertex'][1][0] = ord( $dat[4] ) * SCALE;
	$pix['vertex'][1][1] = ord( $dat[5] ) * SCALE;
	$pix['vertex'][2][0] = ord( $dat[6] ) * SCALE;
	$pix['vertex'][2][1] = ord( $dat[7] ) * SCALE;
	$pix['vertex'][3][0] = ord( $dat[8] ) * SCALE;
	$pix['vertex'][3][1] = ord( $dat[9] ) * SCALE;

	$sign = ord( $dat[11] );
	if ( $sign & 0x01 )  $pix['vertex'][0][0] *= -1;
	if ( $sign & 0x02 )  $pix['vertex'][0][1] *= -1;
	if ( $sign & 0x04 )  $pix['vertex'][1][0] *= -1;
	if ( $sign & 0x08 )  $pix['vertex'][1][1] *= -1;
	if ( $sign & 0x10 )  $pix['vertex'][2][0] *= -1;
	if ( $sign & 0x20 )  $pix['vertex'][2][1] *= -1;
	if ( $sign & 0x40 )  $pix['vertex'][3][0] *= -1;
	if ( $sign & 0x80 )  $pix['vertex'][3][1] *= -1;
	printf("QUAD : %4d,%4d  %4d,%4d  %4d,%4d  %4d,%4d\n",
		$pix['vertex'][0][0], $pix['vertex'][0][1],
		$pix['vertex'][1][0], $pix['vertex'][1][1],
		$pix['vertex'][2][0], $pix['vertex'][2][1],
		$pix['vertex'][3][0], $pix['vertex'][3][1]
	);

	$tu = 1.0 / $pix['src']['w'];
	$tv = 1.0 / $pix['src']['h'];

	$dx = $pix['vertex'][0][0];
	$dy = $pix['vertex'][0][1];

	//return;

	//    -| 12  43  |- 21  34  || 14  41  -- 23  32
	//    -| 43  12  |- 34  21  -- 23  32  || 14  41
	// flip  -   y      x   xy     xr  l      r   xl

	$pix['dx'] = $dx;
	$pix['dy'] = $dy;
	return;
}

function sectpart( &$pak, $dir, $id, $off, $no )
{
	printf("== sectpart( $dir , $id , %x , $no )\n", $off);

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);
	$pix['bgzero'] = false;

	global $gp_pix, $gp_clut;
	for ( $i=0; $i < $no; $i++ )
	{
		$p = $off + ($i * 12);
		$dat = substr($pak, $p, 12);
		//debug($dat);

		// fedc ba98  7654 3210
		// cctt tttt  tttt tttt
		$b1 = str2big($dat, 0, 2);
		$tid = $b1 & 0x3fff;
		$cid = $b1 >> 14;
		flag_warn ("cid", $b1 >> 12);
		zero_watch("dat", $dat[10] );

		$pix['src']['w'] = $gp_pix[$tid][1];
		$pix['src']['h'] = $gp_pix[$tid][2];
		$pix['src']['pix'] = $gp_pix[$tid][0];
		$pix['src']['pal'] = ( empty($gp_clut) ) ? grayclut(16) : $gp_clut[$cid];

		sectquad($pix, $dat, $tid);
		//copyquad($pix);

		$dx = $pix['dx'];
		$dy = $pix['dy'];
		$pix['dx'] += ($ceil/2);
		$pix['dy'] += ($ceil/2);
		printf("%4d , %4d , 0 , 0 , %4d , %4d", $dx, $dy, $gp_pix[$tid][1], $gp_pix[$tid][2]);
		printf(" , $tid\n");
		copypix($pix);
	}

	$fn = sprintf("$dir/%04d", $id);
	savpix($fn, $pix, true);
	return;
}
//////////////////////////////
function sectanim( &$pak, $off )
{
	// anim def
	// 0 1  2  3  4 5  6  7
	// sid  -  -  ms   -  rep
	$anim = array();
	while (1)
	{
		$bak = $off;
			$off += 8;
		if ( $pak[$bak+3] == BYTE && $pak[$bak+2] == BYTE )
			continue;
		if ( $pak[$bak+7] != ZERO )
			return implode(' , ', $anim);

		$b1 = ordint( $pak[$bak+1] . $pak[$bak+0] );
		$b2 = ordint( $pak[$bak+5] . $pak[$bak+4] );
		$anim[] = sprintf("%d-%d", $b1 & 0x0fff, $b2);
	}
	return implode(' , ', $anim);
}

function load_texx( &$pak, $pfx, $off, $no )
{
	$chr = load_file("$pfx.chr");
	if ( empty($chr) )  return;

	global $gp_pix;
	$gp_pix = array();

	global $gp_clut;
	$pos = 0;
	for ( $i=0; $i < $no; $i++ )
	{
		$p = $off + ($i * 8);

		// 0  1 2 3  4  5  6  7
		// -  chr    w  h  id
		$id = ordint( $pak[$p+7] . $pak[$p+6] );
		$w = ord( $pak[$p+4] );
		$h = ord( $pak[$p+5] );
		$siz = ($w/2 * $h);
		printf("%4x , %6x , %3d x %3d = %4x\n", $i, $pos, $w, $h, $siz);

		$b1 = substr($chr, $pos, $siz);
		$pix = "";
		for ( $s=0; $s < $siz; $s++ )
		{
			$b2 = ord( $b1[$s] );
			$b3 = ($b2 >> 4) & BIT4;
			$b4 = ($b2 >> 0) & BIT4;
			$pix .= chr($b3) . chr($b4);
		}
		$gp_pix[$i] = array($pix, $w, $h);

		$clut = "CLUT";
		$clut .= chrint(16, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= ( empty($gp_clut) ) ? grayclut(16) : $gp_clut[0];
		$clut .= $pix;
		save_file("{$pfx}_tmp/$i.clut", $clut);

		// aligned to 8x8 tile
		$pos = int_ceil($pos + $siz, 0x20);
	} // for ( $i=0; $i < $no; $i++ )
	return;
}
//////////////////////////////
function pakchr( &$pak, $pfx )
{
	echo "== pakchr( $pfx )\n";
	$dir = "{$pfx}_chrpak";

	$num1 = str2big($pak, 0x20, 2); // no1 parts * 8
	$num2 = str2big($pak, 0x22, 2); // no2 distort set def * 12
	$num3 = str2big($pak, 0x24, 2); // no4 anim * 8
	$num4 = str2big($pak, 0x26, 2); // no5 anim set def * 12

	$off1 = str2big($pak, 0x08, 4); // parts def * 8
	$off2 = str2big($pak, 0x0c, 4); // distort def * 12
	$off3 = str2big($pak, 0x10, 4); // distort set def
	$off4 = str2big($pak, 0x14, 4); // anim def * 8
	$off5 = str2big($pak, 0x18, 4); // anim set def * 12
	$off6 = str2big($pak, 0x2c, 4);
	$off7 = str2big($pak, 0x30, 4);

	load_texx($pak, $pfx, $off1, $num1);
	//return;

	for ( $i=0; $i < $num2; $i++ )
	{
		$p = $off3 + ($i * 12);

		// distort set def
		// 0 1 2 3 4 5 6 7  8 9  a b
		// - - - - - - - -  st   no
		$st = ordint( $pak[$p+ 9] . $pak[$p+ 8] );
		$no = ordint( $pak[$p+11] . $pak[$p+10] );

		printf("SPR $i = %x , %x , %x\n", $p, $st, $no);
		sectpart($pak, $dir, $i, $off2 + $st*12, $no);
	}

	$anim = "";
	for ( $i=0; $i < $num4; $i++ )
	{
		$p = $off5 + ($i * 12);
		$st = str2big($pak, $p, 4);

		printf("ANIM %d = %x , %x\n", $i, $p, $st);
		$anim .= "anim_$i = ";
		$anim .= sectanim($pak, $st);
		$anim .= "\n";
	}
	save_file("$dir/anim.txt", $anim);
	return;
}
//////////////////////////////
function loadclut( $fname )
{
	echo "== loadclut( $fname )\n";
	global $gp_clut;
	$gp_clut = array();

	$prg = file_get_contents($fname);
	$exe = load_file( PCROWN_EXE );
	if ( empty($prg) || empty($exe) )
		return;

	$v = substr0($prg, 0x40);
	echo "$fname = $v\n";

	$cl = array();
	$cl[] = str2big($prg, 2, 2);
	$cl[] = str2big($prg, 4, 2);

	foreach ( $cl as $k => $v )
	{
		if ( $v == BIT16 )
			continue;

		$pal = "";
		$pos = PCROWN_CLOF + ($v * 0x20);
		printf("add CLUT %x @ %x\n", $v, $pos);

		for ( $i=0; $i < 0x20; $i += 2 )
		{
			$pal .= rgb555( $exe[$pos+1] . $exe[$pos+0] );
			$pos += 2;
		}
		$gp_clut[$k] = $pal;
	} // foreach ( $cl as $c )

	return;
}

function pcrown( $fname )
{
	if ( stripos($fname, '.prg') !== false )
		return loadclut($fname);

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$pak = load_file("$pfx.pak");
	if ( empty($pak) )  return;

	if ( substr($pak,0,4) != "unkn" )
		return;

	pakchr($pak, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
palette
	grad             puro
	1  f8f8f0  fbff  680810  882d
	2  f8e0c0  e39f  881830  9871
	3  f0c0a0  d31e  a83048  a4d5
	4  d89088  c65b  c04060  b118
	5  a07880  c1f4  c85870  b979
	6  804050  a910  e08890  ca3c
	7  a898f0  fa75  f0b8a8  d6fe
	8  281860  b065  f8e0d0  eb9f
	9  503068  b4ca  402000  8088
	a  d898c0  e27b  683018  8ccd
	b  9070b8  ddd2  a87038  9dd5
	c  9050a0  d152  c89058  ae59
	d  684098  cd0d  804040  a110
	e
	f  c8d0f8  ff59
	=> RAM 9ca8e = 0.bin + 98a8e
		size 5000 (640 set of 16 color palettes)
	=> grad/+0[0] , puro/+3900[1c8]

slct.pak 0 = 32x37
	00   198,184  205,184  205,160  198,160  Normal ( 7x24)
	ff   174,124  167,124  167,148  174,148  Normal ( 7x24)
	flag  01  02   04  08   10  20   40  80
	diff  24  60   38  60   38  12   24  12

	data  12, 30   19, 30   19,  6   12,  6  ( 7x24)
	4b   -12,-30   19,-30   19,  6  -12,  6  (31x36)
	base 186,154

	dir
		 0,0  48, 0  48,48  0,48  == 186,154  234,154  234,202  186,202  Normal
		24,0  48,24  24,48  0,24  == 210,154  234,178  210,202  186,178  Normal (45 degree)

proserpina
	hat 53 = 56x24
		107,111  162,111  162,134  107,134  Normal
		-8,-84  47,-84  47,-61  -8,-61 (eb 55x23)
	face 0 = 24x20
		108,124  131,124  131,143  108,143  Normal
		-7,-71  16,-71  16,-52  -7,-52 (eb 23x19)
arel
	wings 8 = 40x38
		4,99  51,99  51,136  4,136  Normal
		0,48/-32,-85  15,-85  15,-48  -32,-48 (eb 47x37)
		50/-31,-84  15,-84  15,-48  -31,-48 (eb 46,37)
		51/-28,-80  14,-80  13,-47  -24,-48 (eb ~42/41,33/32)
		52/-28,-73  13,-76  12,-48  -18,-48 (eb ~41/40/10,28/25/3)
	body 0 = 32x59
		20,98  51,98  51,156  20,156  Normal
		0,48,50,51/-16,-86  15,-86  15,-28  -16,-28 (eb 31x58)
		52/-16,-85  15,-85  15,-29  -16,-29 (eb 31,56)

	113/71  215/d7  /  199/c7  183/b7  113/71  145/91  131/83  67/43  1/1
	240/f0  239/ef  96/60

book select
	VORE  item.pak
	VORE  comm.pak
	VORE  arel.pak
	VORE  slct.pak
	VORE  chap.pak
	VORE  obaa.pak

prg
	0 dohdoh  10 ghost   20 polt  30 larv
	1 slime   11 card    21 iced
	2 myco    12 barb    22 hind
	3 zombie  13 d/grad  23 blud
	4 goblin  14 vorg    24 blud2
	5         15 eeriel  25
	6 frog    16 ryon    26
	7 kage    17 necro   27
	8 basil   18         28
	9 dragon  19 sirene  29
	a kumo    1a cent    2a
	b nise    1b wgod    2b
	c grifon  1c         2c puppet
	d demon   1d skul    2d
	e knight  1e         2e pros
	f egrad   1f         2f ceye
*/
