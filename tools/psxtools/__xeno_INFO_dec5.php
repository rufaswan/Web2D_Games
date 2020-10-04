<?php
require "common.inc";

$gp_cmd = array();

function xeno_opvar( &$file, &$pos, $var )
{
	$ret = array();
	foreach ( $var as $v )
	{
		if ( $v == 1 )
		{
			$b1 = ord( $file[$pos] );
				$pos++;
			printf("%2x  ", $b1);
			$ret[] = $b1;
		}
		else
		if ( $v == 2 )
		{
			$b1 = str2int($file, $pos, 2);
				$pos += 2;
			printf("%4x  ", $b1);
			$ret[] = $b1;
		}
		else
		if ( $v == 4 )
		{
			$b1 = str2int($file, $pos, 4);
				$pos += 4;
			printf("%8x  ", $b1);
			$ret[] = $b1;
		}
	}
	return $ret;
}

function xeno_opdec5( &$file, $pos )
{
	// sub_800a1458 = "EVENTLOOP ERROR ACT=%d\n"
	global $gp_cmd;
	while (1)
	{
		$op = ord( $file[$pos] );
			$pos++;
		printf("%2x  ", $op);

		$var = xeno_opvar( $file, $pos, $gp_cmd[$op] );
		echo "\n";

		if ( $op == 0 )
			break;

		// 800ad778[$op*4]
		switch ( $op )
		{
			// = "STACKERR ACT=%d\n"
			case 0x05:  break; // jump near?
			case 0x06:  break; // jump long?
			case 0x0d:  break; // return?
		} // switch ( $op )

	} // while (1)
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$cnt  = str2int($file, 0x80, 3);
	$base = 0x84 + ($cnt * 0x40);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 0x84 + ($i * 0x40);
		for ( $j=0; $j < 0x10; $j++ )
		{
			$p = $pos + ($j * 2);
			$p1 = str2int($file, $p, 2);

			printf("== $fname/$i/$j , %x , %x , %x\n", $p, $p1, $base+$p1);
			xeno_opdec5($file, $base+$p1);
		}
	}
	return;
}
//////////////////////////////
function xeno_init()
{
$cmd = <<<_CMD
00,
01,
02,2,2,1,2
03,
04,
05,2
06,2,1,1
07,1,1
08,1,1
09,1,1
0a,1,2
0b,
0c,
0d,
0e,
0f,

10,
11,
12,1,2
13,
14,
15,
16,2
17,2,2,2,2,2,2,2,2,1
18,1,1,1,1
19,2,2,1
1a,1
1b,2,2,1,1
1c,2,1
1d,2,2,2
1e,
1f,1

20,2
21,2
22,
23,
24,1
25,1
26,2
27,1
28,1
29,1
2a,
2b,
2c,1
2d,1,2,2,2
2e,2
2f,2

30,2
31,
32,
33,
34,2,2
35,2,2,1
36,2
37,2
38,2,2,1
39,2,2,1
3a,2,2,1
3b,2,2,1
3c,2
3d,2
3e,2,2,1
3f,2,2,1

40,2,2,1
41,2,2
42,2,2
43,2
44,1,1,2
45,1,1,1,1,2,1
46,
47,
48,2,2,2
49,2,2,2,1
4a,1,1,1,1,1
4b,1,1,1,1,1,2
4c,1,1,1,1,1,1,1
4d,1,1,1,1,1,1,1,2
4e,1,1,1,1,1
4f,1,1,1,1,1,2

50,1,1,1,1,1,1,1
51,1,1,1,1,1,1,1,2
52,1
53,1,2
54,1,1,1,1
55,1,1,1,1,2
56,2,2,2,2,1
57,1,2,2,2,2,1,1,1
58,2,1
59,
5a,
5b,
5c,2
5d,
5e,
5f,1

60,
61,2,2,2,1
62,1
63,2,2,2,1
64,
65,2,2,2,1
66,1
67,
68,
69,
6a,
6b,
6c,
6d,2,2,2,1
6e,2,2,2,1
6f,1

70,1
71,2
72,
73,1,2,2,2
74,2
75,
76,
77,
78,1,2
79,
7a,
7b,2,1
7c,2,1
7d,2,1
7e,2,1
7f,2

80,1,1,2
81,1,1,2
82,1,1,2
83,1,1,2
84,2,2
85,2,2
86,2,2
87,2
88,2
89,1,2,2
8a,1,2
8b,2,2
8c,2
8d,2
8e,1,1,1,1,2
8f,2

90,2
91,1,2
92,
93,2
94,2,2
95,1
96,
97,2
98,2,2
99,
9a,2
9b,2,2
9c,
9d,2,1
9e,
9f,

a0,2,2,2
a1,2
a2,1
a3,2,2,2,1
a4,2,1
a5,2
a6,2
a7,
a8,2,2
a9,1
aa,1
ab,
ac,1,2
ad,2,2,2
ae,2,2,2
af,2,1

b0,2,1
b1,2,1
b2,1
b3,2
b4,2
b5,2,2
b6,2,2
b7,
b8,
b9,1,2
ba,1
bb,1
bc,
bd,2
be,2
bf,2

c0,2
c1,2
c2,2
c3,
c4,1
c5,1
c6,
c7,2
c8,2
c9,
ca,2,2,2,1
cb,
cc,1,2
cd,
ce,
cf,1,1,1,1

d0,2,2,2,2,2
d1,
d2,
d3,
d4,
d5,2
d6,2
d7,2
d8,2
d9,2
da,2,2,2,2,2,2,2,2
db,2,2
dc,2,2
dd,2,2,1
de,2,2,1
df,2,2,1

e0,1,2,2,1
e1,2,2,2,2,2,2,1
e2,
e3,
e4,
e5,2,2,2,2,2,2,2,2
e6,2,2,2,2
e7,2,2,2
e8,2,2,2
e9,2,2,2
ea,
eb,2,2,2,2,2,2,1,2,2,2
ec,1,2,2,2,1,2,2,2
ed,1,2,2,2
ee,1,1
ef,2

f0,2,2,2
f1,2,2,2,2,2
f2,2,2,2,2
f3,2,2,2
f4,1
f5,
f6,1
f7,2,2
f8,1,2
f9,1
fa,1,1,2
fb,2,2
fc,1,1,1,1,1
fd,
fe,
ff,
_CMD;

	global $gp_cmd;
	$gp_cmd = array();
	foreach ( explode("\n", $cmd) as $line )
	{
		$line = preg_replace('[\s]', '', $line);
		if ( empty($line) )
			continue;
		$line = explode(',', $line);
		$op = array_shift($line);
		$op = hexdec($op);
		$gp_cmd[$op] = $line;
	}
	return;
}

xeno_init();
for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
thames captain deck 1234/1235.bin
	RAM 801be8ec -> 80112b44
	1235.bin
		3000   320,  0  176x246
		f000   526,  0  100x250
		16000  464,  0   96x254
		1d000  526,256  100x220
		4   200w  margie
		6   192w  drunk
		10   64w  drunk
		14   32w  margie
		16   24w  margie
	1234.bin
		5.dec
			80  action no
			84  fei action
			c4  elly action
			104
			144
			184
			1c4
			204
			244  emeralda action
			284
			2c4
			304
			344
			384
			3c4
			404  door to evalator
			444  door to canteen
			484
			4c4
			504
			544  from dock
			584  from tower
			5c4  from crane room
			604  captain action
			644  hans action
			684
			6c4  first mate action
			704  communicator action
			744  navigator action
			784  drunk action
			7c4
			804
			actions
				0  init
				2
				4  interact / break @ sub_800a14ec
				6
				8-3e  events
	0.dec  -
	1.dec  80121398
	2.dec  801066f8
	3.dec  80122644
	4.dec  raw
	5.dec  8011d5ec
	6.dec  80064f6c
	7.dec  8011f268
	tex   801e1ea8  801e2ea8
	opcode  800ad778
		0xcc(800af54c) <- data pointer
		800ad0d8 <- data pos
		fe 15 cc 80 pp 80
			cc = char [equal as cnt of 3.dec]
				0 navigator    [340,0]
				1 captain      [380,0]
				2 hanz         [3c0,0]
				3 first mate   [140,100]
				4 communicator [180,100]
				5 drunk        [1c0,100]
				6 margie       [200,100]
			pp = palette
		19 xx xx yy yy
			xx = signed int16 (+east  -west )
			yy = signed int16 (+north -south)
thames
	RAM 801be8ec
	b2f dock   1230/1231
	b1f cargo  1242/1243
	1f  deck   /
		market 1238/1239
	2f  medic  1236/1237
	3f  beer   1236/1237
	4f  bridge 1234/1235
gasper - shevat
	debug event 454
	file 1514/1515.bin
	entry 14 of 24 , 1bd = c0 69 rr
		rr = 0-7 N NE E SE S SW W NW
 */
