<?php
require "common.inc";
require "common-guest.inc";
require "common-quad.inc";

define("CANV_S", 0x200);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function sectquad( &$pix, $dat, $ceil )
{
	//return;
	// 0 1  2  3   4  5   6  7   8  9   a  b
	// tid  x1 y1  x2 y2  x3 y3  x4 y4  -  sign
	$b = array();
	for ( $i=0; $i < 12; $i++ )
		$b[] = ord( $dat[$i] );
	$dat = $b;

	$qax = ( $dat[11] & 0x01 ) ? -$dat[2] : $dat[2];
	$qay = ( $dat[11] & 0x02 ) ? -$dat[3] : $dat[3];
	$qbx = ( $dat[11] & 0x04 ) ? -$dat[4] : $dat[4];
	$qby = ( $dat[11] & 0x08 ) ? -$dat[5] : $dat[5];
	$qcx = ( $dat[11] & 0x10 ) ? -$dat[6] : $dat[6];
	$qcy = ( $dat[11] & 0x20 ) ? -$dat[7] : $dat[7];
	$qdx = ( $dat[11] & 0x40 ) ? -$dat[8] : $dat[8];
	$qdy = ( $dat[11] & 0x80 ) ? -$dat[9] : $dat[9];
		$qax = (int)($qax * SCALE);
		$qay = (int)($qay * SCALE);
		$qbx = (int)($qbx * SCALE);
		$qby = (int)($qby * SCALE);
		$qcx = (int)($qcx * SCALE);
		$qcy = (int)($qcy * SCALE);
		$qdx = (int)($qdx * SCALE);
		$qdy = (int)($qdy * SCALE);

	$pix['vector'] = array(
		array( $qax+$ceil , $qay+$ceil , 1 ),
		array( $qbx+$ceil , $qby+$ceil , 1 ),
		array( $qcx+$ceil , $qcy+$ceil , 1 ),
		array( $qdx+$ceil , $qdy+$ceil , 1 ),
	);

	printf("sign : %08b\n", $dat[11]);
	printf("src | %4d,%4d  %4d,%4d |\n", 0, 0,                  $pix['src']['w']-1, 0);
	printf("    | %4d,%4d  %4d,%4d |\n", 0, $pix['src']['h']-1, $pix['src']['w']-1, $pix['src']['h']-1);

	printf("des | %4d,%4d  %4d,%4d |\n", $qax, $qay, $qbx, $qby);
	printf("    | %4d,%4d  %4d,%4d |\n", $qdx, $qdy, $qcx, $qcy);
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

	global $gp_pix, $gp_clut;
	$gray = grayclut(16);
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
		$pix['src']['pal'] = ( empty($gp_clut) ) ? $gray : $gp_clut[$cid];
		$pix['bgzero'] = 0;

		sectquad($pix, $dat, $ceil/2);
		printf("$tid , $cid\n");

		copyquad($pix, 1);
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
function loadclut( $fname, $pos )
{
	printf("== loadclut( $fname , %x )\n", $pos);

	global $gp_clut;
	$gp_clut = array();
	$file = file_get_contents($fname);

	for ( $i=0; $i < 0x5000; $i += 0x20 )
	{
		$pal = substr($file, $pos+$i, 0x20);
		$plt = "";
		for ( $j=0; $j < 0x20; $j += 2 )
			$plt .= rgb555( $pal[$j+1] . $pal[$j+0] );
		$gp_clut[] = $plt;
	} // for ( $i=0; $i < 0x5000; $i += 0x20 )
	return;
}

function pcrown( $fname )
{
	if ( stripos($fname, '0.bin') !== false )
		return loadclut($fname, 0x98a8e);
	if ( stripos($fname, 'pcrown.pal') !== false )
		return loadclut($fname, 0);

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
	grad             puro          port
	1  f8f8f0  fbff  680810  882d
	2  f8e0c0  e39f  881830  9871
	3  f0c0a0  d31e  a83048  a4d5  482010  8889
	4  d89088  c65b  c04060  b118
	5  a07880  c1f4  c85870  b979  181020  9043
	6  804050  a910  e08890  ca3c  301830  9866
	7  a898f0  fa75  f0b8a8  d6fe  483048  a4c9
	8  281860  b065  f8e0d0  eb9f  684868  b52d
	9  503068  b4ca  402000  8088
	a  d898c0  e27b  683018  8ccd  b0a8b0  dab6
	b  9070b8  ddd2  a87038  9dd5
	c  9050a0  d152  c89058  ae59  683830  98ed
	d  684098  cd0d  804040  a110  885850  a971
	e                              c08068  b618
	f  c8d0f8  ff59                e0b890  cafc
	=> RAM 9ca8e = 0.bin + 98a8e
		size 5000 (640 set of 16 color palettes)
	=> grad/+0[0] , puro/+3900[1c8] , port/+3800[1c0]

	mur1
	1  f8f8c8  e7ff  f8e8b8  dfbf
	2  f0d098  cf5e  f0c090  cb1e
	3  d89858  ae7b  d88858  ae3b
	4  a07840  a1f4  a06840  a1b4
	5  705828  956e  704828  952e
	6  887890  c9f1  985068  b553
	7
	8
	9  a8a8b0  dab5  c8a038  9e99
	a
	b  e0e0f0  fb9c  e8d098  cf5d
	c
	d
	e
	f
	=> 1/+1940[ca] , 2/+1920[c9]

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
