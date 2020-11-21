<?php
require "common.inc";
require "common-guest.inc";

define("CLR_OFF", 0x154fc); // 0x9ca8e - 0x4000
define("PAL_OFF", 0x98a8e); // 0x9ca8e - 0x4000

$gp_index = <<<_INDEX
grad ,   0 ,   6
obaa ,  4d , 181

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
		list($name,$id1,$id2) = explode(',', $line);
		$ind[$name] = array( hexdec($id1) , hexdec($id2) );
	}
	$gp_index = $ind;
	return;
}

function pcrown( $fname )
{
	// for 0.bin only
	if ( stripos($fname, '0.bin') === false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, CLR_OFF, 10) != "ALL CLEAR!" )
		return;

	global $gp_index;
	foreach ( $gp_index as $k => $v )
	{
		$pal = "";

		$p = PAL_OFF + ($v[0] * 0x20);
		for ( $i=0; $i < 0x20; $i += 2 )
			$pal .= rgb555( $file[$p+$i+1] . $file[$p+$i+0] );
		$p = PAL_OFF + ($v[1] * 0x20);
		for ( $i=0; $i < 0x20; $i += 2 )
			$pal .= rgb555( $file[$p+$i+1] . $file[$p+$i+0] );

		file_put_contents("$k.pal", $pal);
	} // foreach ( $gp_index as $k => $v )

	return;
}

index_init();
for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
palette
	=> RAM 9ca8e = 0.bin + 98a8e
	=> 9ca8e-9da8e-9daae-9eb8e-a1a90
	z   1000    20  10e0  2f02
	=>  8790

	grad             shield
	1  f8f8f0  fbff
	2  f8e0c0  e39f  604880  c12c
	3  f0c0a0  d31e
	4  d89088  c65b
	5  a07880  c1f4  9890b8  de53
	6  804050  a910  b0a8c8  e6b6
	7  a898f0  fa75  c8c0d8  ef19
	8  281860  b065  e0d8e8  f77c
	9  503068  b4ca
	a  d898c0  e27b
	b  9070b8  ddd2
	c  9050a0  d152
	d  684098  cd0d
	e
	f  c8d0f8  ff59  e8e8e8  f7bd
	=> 9ca8e         9cb4e
		-> 9ca8e = 9ca8e[0] = 8790 = 8790[0]
		-> 9cb4e = 9ca8e[6] = 8794 = 8790[2]
	+4000
	=> e0    | eb    | ec ed ee ef de df f0 f1 f2 f3 f4 f5 f6 f7 | f8
	=> spark | sword | slash                                     | shield

	puro             port
	1  680810  882d
	2  881830  9871
	3  a83048  a4d5  482010  8889
	4  c04060  b118
	5  c85870  b979  181020  9043
	6  e08890  ca3c  301830  9866
	7  f0b8a8  d6fe  483048  a4c9
	8  f8e0d0  eb9f  684868  b52d
	9  402000  8088
	a  683018  8ccd  b0a8b0  dab6
	b  a87038  9dd5
	c  c89058  ae59  683830  98ed
	d  804040  a110  885850  a971
	e                c08068  b618
	f                e0b890  cafc
	=> a038e         a028e
	=> +3900[1c8]    +3800[1c0]

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
	=> 9e3ce         9e3ae
	=> 1/+1940[ca] , 2/+1920[c9]

	dodo.pak
	1
	2  784848  a52f
	3  907068  b5d2
	4
	5
	6
	7
	8
	9
	a  885850  a971
	b  683850  a8ed
	c  402040  a088
	d  281820  9065
	e
	f
	=> 9cc8e
	=>

	obaa.pak  cat
	1  -             f8f0e8  f7df
	2  f8d8b0  db7f  -
	3  e0a868  b6bc  986030  9993
	4  a06038  9d94  -
	5  -             -
	6  -             -
	7  683830  98ed  -
	8  -             -
	9  b87078  bdd7  202020  9084
	a  784850  a92f  -
	b  -             483838  9ce9
	c  c0b8b8  def8  -
	d  -             705050  a94e
	e  -             -
	f  907870  b9f2  -
	=> 9d42e         9faae
		-> 9d42e = 9ca8e[ 4d] = 87cc = 8790[1e]
		-> 9faae = 9ca8e[181] = 88be = 8790[97]

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
