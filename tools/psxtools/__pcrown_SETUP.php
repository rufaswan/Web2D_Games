<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";

define("CLR_OFF", 0x154fc); // ALL CLEAR! check
define("PAL_ST_OFF", 0x98a8e); // 0x9ca8e - 0x4000
define("PAL_ED_OFF", 0x9da8e); // 0xa1a8e - 0x4000

//////////////////////////////
/*
	00    dodo.pak   dodoh.prg   10,-1  400
	01    slim.pak   slime.prg   1c,-1
	02    myco.pak    myco.prg   15,-1  6c0 , v2 540 , v3 580
	03    zonb.pak  zombie.prg   40,41
	04    gbln.pak  goblin.prg   24,2e
	05 -
	06    frog.pak    frog.prg   90,91  2400
	07    kage.pak    kage.prg   50,51
	08    bask.pak   basil.prg   a0,a8
	09    drgn.pak  dragon.prg   70,6f  1bc0/1c00
	0a    kumo.pak    kumo.prg   30,31
	0b    nise.pak    nise.prg   66,67
	0c    grif.pak  grifon.prg   36,37  9f40 , wing 9f00
	0d    demn.pak   demon.prg   50,51
	0e    head.pak  knight.prg   64,65
	0f    adri.pak   egrad.prg   3a,3b

	10    gost.pak   ghost.prg   b7,b8
	11    card.pak    card.prg   60,61
	12    barb.pak    barb.prg   be,bf
	13    grdp.pak   dgrad.prg    0,1
	14    maou.pak    vorg.prg   8b,8c
	15    aeri.pak  eeriel.prg   86,87
	16    ryon.pak    ryon.prg   93,94
	17    necr.pak   necro.prg  16f,16e
	18    ediv.pak  -
	19    ning.pak  sirene.prg   9a,9b
	1a    kent.pak    cent.prg  1dc,1dd
	1b    wgod.pak    wgod.prg  1f4,1f5
	1c    pira.pak  -            -1,1d0
	1d    skul.pak    skul.prg   50,51
	1e    slmd.pak  -
	1f    mete.pak  -

	20    polt.pak    polt.prg  1b0,1b1
	21   d_ice.pak    iced.prg   7f,7e
	22    hind.pak    hind.prg   70,6f
	23   blud2.pak    blud.prg   50,51
	24 blud2_4.pak   blud2.prg   50,51
	25    grdp.pak    grad.prg    0,1
	26 -
	27 -
	28 -
	29 -
	2a -
	2b -
	2c    slav.pak  puppet.prg  1b9,1b9
	2d -
	2e    epro.pak    pros.prg  1c8,1c9
	2f    ceye.pak    ceye.prg  1ba,1bb

	30    larv.pak    larv.prg  1f0,1f9
 */
$gp_index = <<<_INDEX
dodo , 10 , -1
slim , 1c , -1
myco , 15 , -1
zonb , 40 , 41
gbln , 24 , 2e
frog , 90 , 91
kage , 50 , 51
bask , a0 , a8
drgn , 70 , 6f
kumo , 30 , 31
nise , 66 , 67
grif , 36 , 37
demn , 50 , 51
head , 64 , 65
adri , 3a , 3b
gost , b7 , b8
card , 60 , 61
barb , be , bf
grdp , 0 , 1
maou , 8b , 8c
aeri , 86 , 87
ryon , 93 , 94
necr , 16f , 16e
ning , 9a , 9b
kent , 1dc , 1dd
wgod , 1f4 , 1f5
pira , -1 , 1d0
skul , 50 , 51
polt , 1b0 , 1b1
d_ice , 7f , 7e
hind , 70 , 6f
blud2 , 50 , 51
blud2_4 , 50 , 51
slav , 1b9 , 1b9
epro , 1c8 , 1c9
ceye , 1ba , 1bb
larv , 1f0 , 1f9

grad ,   0 ,   1
edow , 160 , 161
puro , 1c8 , 1c9
port , 1c0 , 1c1

obaa ,  4d ,  4e
mur1 ,  c9 ,  ca
_INDEX;

function index_init()
{
	global $gp_index;
	$ind = array();
	foreach ( explode("\n", $gp_index) as $line )
	{
		$line = preg_replace("|[\s]+|", '', $line);
		if ( empty($line) )
			continue;
		$id = explode(',', $line);
		$name = array_shift($id);
		arrayhex($id);
		$ind[$name] = $id;
	}
	$gp_index = $ind;
	return;
}

function exp_pal( &$file )
{
	global $gp_index;
	foreach ( $gp_index as $name => $id )
	{
		$pal = "";
		foreach ( $id as $v )
		{
			if ( $v < 0 )
				$pal .= str_repeat(ZERO, 0x40);
			else
				$pal .= substr($file, $v*0x40, 0x40);
		}

		file_put_contents("$name.pal", $pal);
	} // foreach ( $gp_index as $k => $v )
	return;
}
//////////////////////////////
function pcrown( $fname )
{
	// for 0.bin only
	if ( stripos($fname, '0.bin') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, CLR_OFF, 10) != "ALL CLEAR!" )
		return;

	$pal = "";
	for ( $i = PAL_ST_OFF; $i < PAL_ED_OFF; $i += 2 )
		$pal .= $file[$i+1] . $file[$i+0];

	$file = pal555($pal);
	file_put_contents("$fname.pal", $file);

	exp_pal($file);
	return;
}

index_init();
for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
palette asm order
	grad.pak  0 , slash 40 , sword , shield 100/140/180
	edow.pak
	puro.pak  7200 , rod 7240
	port.pak  7000

	dwaf.pak
	dodo2.pak
	jestonly.pak
	ya_a.pak
	card.pak
	eril.pak
	sdol.pak
	jestelfa.pak
	jestelf2.pak
	volg.pak
	goro.pak
	ya_b.pak
	demn.pak
	soldd.pak
	solb.pak
	sens.pak
	maid.pak
	mur1.pak    3240 , 3280
	loco.pak
	hon1.pak
	uma1.pak
	boys.pak
	baba.pak
	ediv_2.pak
	ediv_1.pak
	ediv_3.pak
	obaa.pak    1340 , cat 6040
	chap.pak    6200
	slct.pak    fire 1c80/1ec0/3c40 , fence 9080

select color bank
	21a80  obaa
	21b00  book brown
	21b80  book blue
	21c00  book pink
	21c80  book black
	21d00  book orange
	21e00  fence
	21e80  cat
	21f00  girl
	21f80  circle
	23d80  fire

	VDP1 palette order
		0    20*2
		1  2120*2
		2   9a0*2  obaa
		3  3160*2  book
		4  3180*2  book
		5  31a0*2  book
		6  31c0*2  book
		7  31e0*2  book
		8  e40/f60/1e20  fire
		9  4840*2  fence
		a  3020*2  cat
		b  3100*2  girl

palette
	=> RAM 9ca8e = 0.bin + 98a8e
	=> 9ca8e-9da8e-9daae-9eb8e-a1a90
	z   1000    20  10e0  2f02
	=>  8790

6012b5c - loop
6012e88
6012ed8 - loop
60133a4
	mov.l  [c0b30], r7
	mov.l  [9ca8e], r3
	mov.w  @(r0,r3), r1
	mov.w  r1, @(r0,r7)

select.evn => RAM 19e000

SATURN -> PSP
	bin/  3 = bin/  2 (-0.bin)
	tsk/  1 = tsk/  1
	clb/  1 = clb/  1
	evn/443 = evn/443
	mpb/ 25 = mpb/  2 (-map_*.mpb)
	prg/ 39 = prg/ 40 (+pira.prg from 0.bin)
	pcm/ 17 = at3/ 50
	snd/ 75 = svl/ 60 (k_*.snd -> k_*.at3 , ending.snd->at3)

	chb/393 + mcb/484 = chb/393 + mcb/484 + mvl/390
	chr/237 + pak/236 = chr/ 15 + pak/ 14 + vol/225

	add +tmx/94
	add +dat/ 5+1 (p_*.dat seat3.dat)
	add +png/ 2   (icon0.png pic0.png)
	add +pmf/ 2

	palette
	0.bin + 98a8e -> SYSDIR/BOOT.BIN + 20e328
 */
