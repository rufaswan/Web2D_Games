<?php
require 'common.inc';

// map file 5.dec
//   current = 800ad0d8
// sbyte
// ubyte
// shalf  800ac274  sub_800ac254
// uhalf  800ac2b0  sub_800ac290
// word

// 800a14e8  lbu   v0, 0(v0)
// 800a14f0  sll   v0, 2
// 800a14f4  addu  v0, s1  // s1=800ad778
// 800a14f8  lw    v0, 0(v0)
// 800a1500  jalr  v0
$gp_evop = [
	["\x00", 'return'], // Akari
	["\x01", 'goto', 'uhalf'], // Akari
	//["\x02", 'if'], // Akari
	["\x03", ''],
	["\x04", ''],
	["\x05", ''], // Akari
	["\x06", ''],
	["\x07", ''], // Akari
	["\x08", ''], // Akari
	["\x09", ''], // Akari
	["\x0a", ''],
	["\x0b", ''], // Akari
	["\x0c", ''], // Akari
	["\x0d", ''],
	["\x0e", ''],
	["\x0f", ''],

	["\x10", ''],
	["\x11", ''],
	["\x12", ''],
	["\x13", 'nop'],
	["\x14", ''],
	["\x15", ''],
	["\x16", ''], // Akari
	["\x17", ''],
	["\x18", ''],
	["\x19", ''], // Akari
	["\x1a", ''],
	["\x1b", ''],
	["\x1c", ''],
	["\x1d", ''],
	["\x1e", ''],
	["\x1f", ''],

	["\x20", ''], // Akari
	["\x21", ''],
	["\x22", ''],
	["\x23", ''], // Akari
	["\x24", ''],
	["\x25", ''],
	["\x26", ''], // Akari
	["\x27", ''],
	["\x28", ''],
	["\x29", ''],
	["\x2a", ''], // Akari
	["\x2b", ''],
	["\x2c", ''],
	["\x2d", ''],
	["\x2e", ''],
	["\x2f", ''],

	["\x30", ''],
	["\x31", ''], // Akari
	["\x32", ''],
	["\x33", ''],
	["\x34", ''],
	["\x35", ''], // Akari
	["\x36", ''],
	["\x37", ''],
	["\x38", ''],
	["\x39", ''],
	["\x3a", ''],
	["\x3b", ''],
	["\x3c", ''],
	["\x3d", ''],
	["\x3e", ''],
	["\x3f", ''],

	["\x40", ''],
	["\x41", ''],
	["\x42", ''],
	["\x43", ''],
	["\x44", ''],
	["\x45", ''],
	["\x46", ''],
	["\x47", ''],
	["\x48", ''],
	["\x49", ''],
	["\x4a", ''],
	["\x4b", ''],
	["\x4c", ''],
	["\x4d", ''],
	["\x4e", ''],
	["\x4f", ''],

	["\x50", ''],
	["\x51", ''],
	["\x52", ''],
	["\x53", ''],
	["\x54", ''],
	["\x55", ''],
	["\x56", ''],
	["\x57", ''],
	["\x58", ''],
	["\x59", ''],
	["\x5a", ''], // Akari
	["\x5b", ''], // Akari
	["\x5c", ''],
	["\x5d", ''],
	["\x5e", ''],
	["\x5f", ''],

	["\x60", ''],
	["\x61", ''],
	["\x62", ''],
	["\x63", ''], // Akari
	["\x64", ''],
	["\x65", ''],
	["\x66", ''],
	["\x67", ''],
	["\x68", ''],
	["\x69", ''],
	["\x6a", ''],
	["\x6b", ''],
	["\x6c", ''],
	["\x6d", ''],
	["\x6e", ''],
	["\x6f", ''],

	["\x70", ''],
	["\x71", ''],
	["\x72", ''],
	["\x73", ''],
	["\x74", ''],
	["\x75", ''], // Akari
	["\x76", ''],
	["\x77", ''],
	["\x78", ''],
	["\x79", ''],
	["\x7a", ''],
	["\x7b", ''],
	["\x7c", ''],
	["\x7d", ''],
	["\x7e", ''],
	["\x7f", ''],

	["\x80", ''],
	["\x81", ''],
	["\x82", ''],
	["\x83", ''],
	["\x84", ''], // Akari
	["\x85", ''],
	["\x86", ''], // Akari
	["\x87", ''], // Akari
	["\x88", ''],
	["\x89", ''],
	["\x8a", ''],
	["\x8b", ''],
	["\x8c", ''],
	["\x8d", ''],
	["\x8e", ''],
	["\x8f", ''],

	["\x90", ''],
	["\x91", ''],
	["\x92", ''],
	["\x93", ''],
	["\x94", ''],
	["\x95", ''],
	["\x96", ''],
	["\x97", ''],
	["\x98", ''], // Akari
	["\x99", ''], // Akari
	["\x9a", ''],
	["\x9b", ''],
	["\x9c", ''], // Akari
	["\x9d", ''],
	["\x9e", ''],
	["\x9f", ''],

	["\xa0", ''], // Akari
	["\xa1", ''],
	["\xa2", ''],
	["\xa3", ''], // Akari
	["\xa4", ''],
	["\xa5", ''],
	["\xa6", ''],
	["\xa7", ''], // Akari
	["\xa8", ''],
	["\xa9", ''],
	["\xaa", ''],
	["\xab", ''],
	["\xac", ''],
	["\xad", ''],
	["\xae", ''],
	["\xaf", ''],

	["\xb0", ''],
	["\xb1", ''],
	["\xb2", ''],
	["\xb3", ''], // Akari
	["\xb4", ''], // Akari
	["\xb5", ''],
	["\xb6", ''],
	["\xb7", ''],
	["\xb8", ''],
	["\xb9", ''],
	["\xba", ''],
	["\xbb", ''],
	["\xbc", ''], // Akari
	["\xbd", ''],
	["\xbe", ''], // Akari
	["\xbf", ''],

	["\xc0", ''],
	["\xc1", ''],
	["\xc2", ''],
	["\xc3", ''],
	["\xc4", ''],
	["\xc5", ''],
	["\xc6", ''], // Akari
	["\xc7", ''],
	["\xc8", ''],
	["\xc9", ''],
	["\xca", ''],
	["\xcb", ''], // Akari
	["\xcc", ''],
	["\xcd", 1, 'setbit   23'], // or   800000
	["\xce", 1, 'clearbit 23'], // and ~800000
	["\xcf", ''],

	["\xd0", ''], // Akari
	["\xd1", 'DUMMY'],
	["\xd2", ''],
	["\xd3", ''],
	["\xd4", ''],
	["\xd5", ''],
	["\xd6", ''],
	["\xd7", ''],
	["\xd8", ''],
	["\xd9", ''],
	["\xda", ''],
	["\xdb", ''],
	["\xdc", ''],
	["\xdd", ''],
	["\xde", ''],
	["\xdf", ''],

	["\xe0", ''],
	["\xe1", ''],
	["\xe2", ''],
	["\xe3", ''],
	["\xe4", 'DUMMY'],
	["\xe5", ''],
	["\xe6", ''],
	["\xe7", ''],
	["\xe8", ''],
	["\xe9", ''],
	["\xea", ''],
	["\xeb", ''],
	["\xec", ''],
	["\xed", ''],
	["\xee", ''],
	["\xef", ''],

	["\xf0", ''],
	["\xf1", ''], // Akari
	["\xf2", ''],
	["\xf3", ''],
	["\xf4", ''], // Akari
	["\xf5", ''], // Akari
	["\xf6", ''],
	["\xf7", ''],
	["\xf8", ''],
	["\xf9", ''],
	["\xfa", ''],
	["\xfb", ''],
	["\xfc", ''],
	["\xfd", 'nop'],
	//["\xfe", 'page2'], // Akari
	["\xff", 'nop'],
];

// 80085ffc  lbu   v0, 0(v1)
// 80086004  sll   v0, 2
// 80086008  lui   at, 800b
// 8008600c  addu  at, v0
// 80086010  lw    v0, -2488(at)  // at=800adb78
// 80086018  jalr  v0
$gp_evop_fe = [
	["\xfe\x00", 'DUMMY'],
	["\xfe\x01", ''],
	["\xfe\x02", ''],
	["\xfe\x03", ''],
	["\xfe\x04", ''],
	["\xfe\x05", ''],
	["\xfe\x06", ''],
	["\xfe\x07", ''],
	["\xfe\x08", ''],
	["\xfe\x09", ''],
	["\xfe\x0a", ''], // Akari
	["\xfe\x0b", ''],
	["\xfe\x0c", ''],
	["\xfe\x0d", ''], // Akari
	["\xfe\x0e", ''], // Akari
	["\xfe\x0f", ''],

	["\xfe\x10", ''],
	["\xfe\x11", ''],
	["\xfe\x12", ''],
	["\xfe\x13", ''],
	["\xfe\x14", ''],
	["\xfe\x15", ''],
	["\xfe\x16", ''],
	["\xfe\x17", ''],
	["\xfe\x18", ''],
	["\xfe\x19", ''],
	["\xfe\x1a", ''],
	["\xfe\x1b", ''],
	["\xfe\x1c", ''],
	["\xfe\x1d", ''],
	["\xfe\x1e", ''],
	["\xfe\x1f", ''],

	["\xfe\x20", ''],
	["\xfe\x21", ''],
	["\xfe\x22", ''],
	["\xfe\x23", ''],
	["\xfe\x24", ''],
	["\xfe\x25", ''],
	["\xfe\x26", ''],
	["\xfe\x27", ''],
	["\xfe\x28", ''],
	["\xfe\x29", ''],
	["\xfe\x2a", ''],
	["\xfe\x2b", ''],
	["\xfe\x2c", ''],
	["\xfe\x2d", ''],
	["\xfe\x2e", ''],
	["\xfe\x2f", ''],

	["\xfe\x30", ''],
	["\xfe\x31", ''],
	["\xfe\x32", ''],
	["\xfe\x33", ''],
	["\xfe\x34", ''],
	["\xfe\x35", ''], // Akari
	["\xfe\x36", ''],
	["\xfe\x37", ''],
	["\xfe\x38", ''],
	["\xfe\x39", ''],
	["\xfe\x3a", ''],
	["\xfe\x3b", ''],
	["\xfe\x3c", ''],
	["\xfe\x3d", ''],
	["\xfe\x3e", ''],
	["\xfe\x3f", ''],

	["\xfe\x40", ''],
	["\xfe\x41", ''],
	["\xfe\x42", ''],
	["\xfe\x43", ''],
	["\xfe\x44", ''],
	["\xfe\x45", ''],
	["\xfe\x46", ''],
	["\xfe\x47", ''],
	["\xfe\x48", ''],
	["\xfe\x49", ''],
	["\xfe\x4a", ''],
	["\xfe\x4b", ''],
	["\xfe\x4c", ''],
	["\xfe\x4d", ''],
	["\xfe\x4e", ''],
	["\xfe\x4f", ''],

	["\xfe\x50", ''],
	["\xfe\x51", ''],
	["\xfe\x52", ''],
	["\xfe\x53", ''],
	["\xfe\x54", ''], // Akari
	["\xfe\x55", ''],
	["\xfe\x56", ''],
	["\xfe\x57", ''],
	["\xfe\x58", ''],
	["\xfe\x59", ''],
	["\xfe\x5a", ''],
	["\xfe\x5b", ''],
	["\xfe\x5c", ''],
	["\xfe\x5d", ''],
	["\xfe\x5e", ''],
	["\xfe\x5f", ''],

	["\xfe\x60", ''],
	["\xfe\x61", ''],
	["\xfe\x62", ''],
	["\xfe\x63", ''], // Akari
	["\xfe\x64", ''],
	["\xfe\x65", ''],
	["\xfe\x66", ''],
	["\xfe\x67", ''],
	["\xfe\x68", ''],
	["\xfe\x69", ''],
	["\xfe\x6a", ''],
	["\xfe\x6b", ''],
	["\xfe\x6c", ''],
	["\xfe\x6d", ''],
	["\xfe\x6e", ''],
	["\xfe\x6f", ''],

	["\xfe\x70", ''],
	["\xfe\x71", ''],
	["\xfe\x72", ''],
	["\xfe\x73", ''],
	["\xfe\x74", ''],
	["\xfe\x75", ''], // Akari
	["\xfe\x76", ''],
	["\xfe\x77", ''],
	["\xfe\x78", 'DUMMY'],
	["\xfe\x79", 'DUMMY'],
	["\xfe\x7a", 'DUMMY'],
	["\xfe\x7b", 'DUMMY'],
	["\xfe\x7c", 'DUMMY'],
	["\xfe\x7d", 'DUMMY'],
	["\xfe\x7e", 'DUMMY'],
	["\xfe\x7f", ''],

	["\xfe\x80", ''],
	["\xfe\x81", ''],
	["\xfe\x82", ''],
	["\xfe\x83", ''],
	["\xfe\x84", ''],
	["\xfe\x85", ''],
	["\xfe\x86", ''],
	["\xfe\x87", ''],
	["\xfe\x88", ''],
	["\xfe\x89", ''],
	["\xfe\x8a", ''],
	["\xfe\x8b", ''],
	["\xfe\x8c", ''],
	["\xfe\x8d", ''],
	["\xfe\x8e", ''],
	["\xfe\x8f", ''], // Akari

	["\xfe\x90", ''], // Akari
	["\xfe\x91", ''], // Akari
	["\xfe\x92", ''], // Akari
	["\xfe\x93", ''], // Akari
	["\xfe\x94", ''], // Akari
	["\xfe\x95", ''], // Akari
	["\xfe\x96", ''], // Akari
	["\xfe\x97", ''], // Akari
	["\xfe\x98", ''],
	["\xfe\x99", ''], // Akari
	["\xfe\x9a", ''],
	["\xfe\x9b", ''],
	["\xfe\x9c", ''],
	["\xfe\x9d", ''],
	["\xfe\x9e", ''],
	["\xfe\x9f", ''],

	["\xfe\xa0", ''], // Akari
	["\xfe\xa1", ''],
	["\xfe\xa2", ''],
	["\xfe\xa3", ''],
	["\xfe\xa4", ''],
	["\xfe\xa5", ''], // Akari
	["\xfe\xa6", ''],
	["\xfe\xa7", ''], // Akari
	["\xfe\xa8", ''],
	["\xfe\xa9", ''],
	["\xfe\xaa", ''],
	["\xfe\xab", ''],
	["\xfe\xac", ''],
	["\xfe\xad", ''],
	["\xfe\xae", ''],
	["\xfe\xaf", ''],

	["\xfe\xb0", ''],
	["\xfe\xb1", ''],
	["\xfe\xb2", ''],
	["\xfe\xb3", ''],
	["\xfe\xb4", ''],
	["\xfe\xb5", ''],
	["\xfe\xb6", ''],
	["\xfe\xb7", ''],
	["\xfe\xb8", ''],
	["\xfe\xb9", ''],
	["\xfe\xba", ''],
	["\xfe\xbb", ''],
	["\xfe\xbc", ''],
	["\xfe\xbd", ''], // Akari
	["\xfe\xbe", ''],
	["\xfe\xbf", ''],

	["\xfe\xc0", ''],
	["\xfe\xc1", ''],
	["\xfe\xc2", ''],
	["\xfe\xc3", ''],
	["\xfe\xc4", ''],
	["\xfe\xc5", ''],
	["\xfe\xc6", ''],
	["\xfe\xc7", ''],
	["\xfe\xc8", ''],
	["\xfe\xc9", ''],
	["\xfe\xca", ''],
	["\xfe\xcb", ''],
	["\xfe\xcc", ''],
	["\xfe\xcd", ''],
	["\xfe\xce", ''],
	["\xfe\xcf", ''],

	["\xfe\xd0", ''], // Akari
	["\xfe\xd1", ''],
	["\xfe\xd2", ''],
	["\xfe\xd3", ''],
	["\xfe\xd4", ''],
	["\xfe\xd5", ''],
	["\xfe\xd6", ''],
	["\xfe\xd7", ''],
	["\xfe\xd8", ''],
	["\xfe\xd9", ''],
	["\xfe\xda", ''],
	["\xfe\xdb", ''],
	["\xfe\xdc", ''],
	["\xfe\xdd", ''],
	["\xfe\xde", ''],
	["\xfe\xdf", ''],

	["\xfe\xe0", ''],
	["\xfe\xe1", ''],
	["\xfe\xe2", 'INVALID'],
	["\xfe\xe3", 'INVALID'],
	["\xfe\xe4", 'INVALID'],
	["\xfe\xe5", 'INVALID'],
	["\xfe\xe6", 'INVALID'],
	["\xfe\xe7", 'INVALID'],
	["\xfe\xe8", 'INVALID'],
	["\xfe\xe9", 'INVALID'],
	["\xfe\xea", 'INVALID'],
	["\xfe\xeb", 'INVALID'],
	["\xfe\xec", 'INVALID'],
	["\xfe\xed", 'INVALID'],
	["\xfe\xee", 'INVALID'],
	["\xfe\xef", 'INVALID'],

	["\xfe\xf0", 'INVALID'],
	["\xfe\xf1", 'INVALID'],
	["\xfe\xf2", 'INVALID'],
	["\xfe\xf3", 'INVALID'],
	["\xfe\xf4", 'INVALID'],
	["\xfe\xf5", 'INVALID'],
	["\xfe\xf6", 'INVALID'],
	["\xfe\xf7", 'INVALID'],
	["\xfe\xf8", 'INVALID'],
	["\xfe\xf9", 'INVALID'],
	["\xfe\xfa", 'INVALID'],
	["\xfe\xfb", 'INVALID'],
	["\xfe\xfc", 'INVALID'],
	["\xfe\xfd", 'INVALID'],
	["\xfe\xfe", 'INVALID'],
	["\xfe\xff", 'INVALID'],
];
//////////////////////////////
function dec5_getlabels( &$label_off, $pos )
{
	$list = [];
	foreach ( $label_off as $k => $v )
	{
		if ( $v === $pos )
			$list[] = $k;
	}
	return $list;
}

function dec5_objlist( &$label_off, &$sub )
{
	$len = strlen($sub);
	for ( $i=0; $i < $len; $i += 0x40 )
	{
		$obj = sprintf('obj_%d::', $i >> 6);
		$p1 = str2int($sub, $i + 0, 2);
		$p2 = str2int($sub, $i + 2, 2);
		$p3 = str2int($sub, $i + 4, 2);
		$p4 = str2int($sub, $i + 6, 2);
		$label_off[ $obj.'on_start'  ] = $p1;
		$label_off[ $obj.'on_update' ] = $p2;
		$label_off[ $obj.'on_talk'   ] = $p3;
		$label_off[ $obj.'on_push'   ] = $p4;

		for ( $j = 8; $j < 0x40; $j += 2 )
		{
			$p = str2int($sub, $j, 2);
			if ( $p === 0 )
				continue;

			$name = sprintf('%sevent_%d', $obj, ($j-8) >> 1);
			$label_off[$name] = $p;
		}
	} // for ( $i=0; $i < $len; $i += 0x40 )
	return;
}

function dec5_evop_disasm( &$bin )
{
	$cnt = str2int($bin, 0x80, 2);
	$label_off = ['default' => 0];

	$len = $cnt * 0x40;
	$sub = substr($bin, 0x84, $len);
	dec5_objlist($label_off, $sub);

	$sub = substr($bin, 0x84 + $len);
	$len = strlen($sub);
	$pos = 0;
	$txt = '';
	while ( $pos < $len )
	{
		$name = dec5_getlabels($label_off, $pos);
		if ( ! empty($name) )
		{
			$txt .= "\n";
			foreach ( $name as $k => $v )
				$txt .= sprintf("%s:\n", $v);
			$txt .= "\t";
		}

		$b = ord( $sub[$pos] );
			$pos++;
		$txt .= sprintf("%2x ", $b);
	}
	$txt .= "\n";
	return $txt;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$txt = dec5_evop_disasm($file);
	save_file("$fname.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*


$gp_cmd = [];

function xeno_opvar( &$file, &$pos, $var )
{
	$ret = [];
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
	$gp_cmd = [];
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
*/
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

fedcba9876543210
aaaaaaaaaasssss-

lw  [800ad0d0] + (a * 4)
1 << s
1 & 2

 */
