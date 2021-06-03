<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
require "common-guest.inc";

define("SHA1SUM", 'd549facf31c89627d43aa645fa0727411d9c544e');
define("PAL_ST_OFF", 0x98a8e); // 0x9ca8e - 0x4000
define("PAL_ED_OFF", 0x9da8e); // 0xa1a8e - 0x4000

//////////////////////////////
// got these from *.prg files
$gp_prg = <<<_PRG
	00    dodo.pak   dodoh.prg   10  -1
	01    slim.pak   slime.prg   1c  -1
	02    myco.pak    myco.prg   15  -1  -1 f8  -1 fe
	03    zonb.pak  zombie.prg   40  41  -1 46
	04    gbln.pak  goblin.prg   24  2e
	05 -
	06    frog.pak    frog.prg   90  91  102 -1
	07    kage.pak    kage.prg   50  51  -1 54  -1 53  77 77  76 76  50 59  5b 5b  75 5b  58 52  50 5a
	08    bask.pak   basil.prg   a0  a8
	09    drgn.pak  dragon.prg   70  6f  70 77  70 76  -1 73  70 71  -1 71  -1 75  77 76
	0a    kumo.pak    kumo.prg   30  31
	0b    nise.pak    nise.prg   66  67  66 -1
	0c    grif.pak  grifon.prg   36  37  38 39
	0d    demn.pak   demon.prg   50  51  -1 54  -1 53  77 77  76 76
	0e    head.pak  knight.prg   64  65  -1 65  64 6b
	0f    adri.pak   egrad.prg   3a  3b

	10    gost.pak   ghost.prg   b7  b8  b7 b7  b9 b9  ba ba
	11    card.pak    card.prg   60  61  60 60
	12    barb.pak    barb.prg   be  bf  -1 bf
	13    grdp.pak   dgrad.prg    0   1  -1 6  69 -1
	14    maou.pak    vorg.prg   8b  8c  8e 8f  8d 8e  8c 8d
	15    aeri.pak  eeriel.prg   86  87  -1 87  3a 3b  89 89  64 64
	16    ryon.pak    ryon.prg   93  94  95 95  96 96
	17    necr.pak   necro.prg  16f 16e  16f -1  -1 16c  16e -1
	18    ediv.pak  -
	19    ning.pak  sirene.prg   9a  9b  9c 9d
	1a    kent.pak    cent.prg  1dc 1dd
	1b    wgod.pak    wgod.prg  1f4 1f5  -1 65  1f4 1fb  64 65
	1c    pira.pak  -            -1 1d0  -1 1da  -1 1db
	1d    skul.pak    skul.prg   50  51  -1 54  -1 53  77 77  76 76  50 59  5b 5b  75 5b  58 52  50 5a
	1e    slmd.pak  -
	1f    mete.pak  -

	20    polt.pak    polt.prg  1b0 1b1  1b0 1b2  1b1 1b3  1b0 1b5  1b0 1b4
	21   d_ice.pak    iced.prg   7f  7e  7f a3  7f 76  -1 73  7f 4b  -1 71  -1 75  77 76
	22    hind.pak    hind.prg   70  6f  70 77  70 76  -1 73  70 71  -1 71  -1 75  77 76
	23   blud2.pak    blud.prg   50  51  -1 54  -1 53  77 77  76 76  50 59  5b 5b  75 5b  58 52  50 5a
	24 blud2_4.pak   blud2.prg   50  51  -1 54  -1 53  77 77  76 76
	25    grad.pak    grad.prg    0   1
	26 -
	27 -
	28 -
	29 -
	2a -
	2b -
	2c    slav.pak  puppet.prg  1b9 1b9
	2d -
	2e    epro.pak    pros.prg  1c8 1c9
	2f    ceye.pak    ceye.prg  1ba 1bb  1ba -1  69 -1

	30    larv.pak    larv.prg  1f0 1f9  200 -1  -1 1f1  -1 1f6  -1 1f8  -1 1f7  -1 75  -1 f2  41 -1  5b 50  5b -1  71 77  -1 5b  1f1 -1  107 -1

_PRG;
$gp_prg = explode("\n", $gp_prg);
//////////////////////////////
// manual discovered
$gp_pak = <<<_PAK
arel       80  -1
ba_a       -1  e6
baba       e3  -1
comm       -1 106  -1 f2  -1 107  -1 f6  -1 f1  -1 102  -1 114  -1 115  -1 fe
demo      172  -1  170 -1  176 -1  17b -1  174 -1
ee2c       -1 243
e_ex       -1 161
evee       -1 248
evje      1a4 1a5
frogp1     -1  91
g_ex       3a  3b
goda      1f0 1f3
gody      108  -1
gost2p     b7  b7  b7 b8
gradp5     -1   6
hon1       8b  -1
item        7  -1  -1 9  171 -1  5 -1  c -1
jestelf2   -1  ff  cc -1
jestelfa   -1  ff  cc -1  b5 -1  -1 b6
jestonly   -1  ff  cc -1
kdv3      203 203
kent      1dc  -1  -1 1dd
kg2c       -1 14e
kizo       -1  22
lasw2     203 203  247 -1
loco       48  -1
maid        9  -1
mete       3d  3e
mur1       c9  -1  ca -1  cb -1
mur4      185  -1  186 -1  187 -1
mur7      188  -1  189 -1  18a -1
mur8      11e  -1
mur9      182 182  183 183  184 184
mura       d5  -1
murc       23  -1
nelt      203 203
robe       d9  -1
sagn      203 203
seme      203 203
sens       bb  -1  -1 bd
sensonly   bb  -1
skul_4     -1  54  -1 53  77 77  76 76
solb       bf  bf  bd -1  bb -1
soldd      -1  bc  bc -1  bc 0  108 -1
tn1c      21c  -1
vldn      203 203
vlga      203 203
volg       62  63
yousei     80  -1

#OVER#
item        7   9
jestelf2   cc  ff
jestelfa   cc  ff
jestonly   cc  ff
kent      1dc 1dd
sens       bb  bd
soldd      bc  bc
myco       15  f8
frogp1     90  91
skul_4     50  51

edow  160 161
puro  1c8 1c9
port  1c0 1c1

obaa     4d  4e
drek     10  -1
ediv    160 161
ediv_1  160 161
ediv_2  160 161
ediv_3  160 161
gbel     24  2e
port1p  1c0 1c1
port2p  1c0 1c1
port4p  1c0 1c1
prad    1c8 1c9
purop2  1c8 1c9
purop3  1c8 1c9
purop5  1c8 1c9

_PAK;
$gp_pak = explode("\n", $gp_pak);
//////////////////////////////
$gp_index = array();

function index_init()
{
	global $gp_index, $gp_prg, $gp_pak;
	$gp_index = array();

	foreach ( $gp_prg as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$line = preg_split('|[\s]+|', $line);
		if ( count($line) < 5 )
			continue;

		$pak = substr($line[1], 0, strrpos($line[1], '.'));
		$c1 = hexdec($line[3]);
		$c2 = hexdec($line[4]);
		$gp_index[$pak] = array($c1,$c2);
	} // foreach ( $gp_prg as $line )

	foreach ( $gp_pak as $line )
	{
		$line = trim($line);
		if ( empty($line) )
			continue;

		$line = preg_split('|[\s]+|', $line);
		if ( count($line) < 3 )
			continue;

		$pak = $line[0];
		$c1 = hexdec($line[1]);
		$c2 = hexdec($line[2]);
		$gp_index[$pak] = array($c1,$c2);
	} // foreach ( $gp_pak as $line )

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

	if ( sha1($file) !== SHA1SUM )
		return php_error("checksum not matched %s", sha1($file));

	$pal = "";
	for ( $i = PAL_ST_OFF; $i < PAL_ED_OFF; $i += 2 )
		$pal .= $file[$i+1] . $file[$i+0];

	$file = pal555($pal);
	$img = array(
		'w' => 0x10,
		'h' => strlen($file) >> 6,
		'pix' => $file,
	);
	save_clutfile("$fname.pal.rgba", $img);

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

4790-6792 [0 to 2000] ???


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
