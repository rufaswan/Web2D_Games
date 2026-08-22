<?php

$txt = str_repeat("\x61", 0x10000);
printf("crc32  %8x\n", crc32($txt));

$enc = [
	'raw'     => ZLIB_ENCODING_RAW     ,
	'gzip'    => ZLIB_ENCODING_GZIP    ,
	'deflate' => ZLIB_ENCODING_DEFLATE ,
];
foreach ( $enc as $ek => $ev )
{
	foreach ( [0,9] as $lv )
	{
		$zip = zlib_encode($txt, $ev, $lv);
		file_put_contents("$ek.$lv", $zip);
	}
}

/*
100 original
	raw 105
		head  01 -- 01 ff fe
	gzip 117
		head  1f 8b 08 --  -- -- -- --  04 03 [01 -- 01 ff fe]
		foot  [59 36 7d b0]  -- 01 -- --
	deflate 10b
		head  78 01 [01 -- 01 ff fe]
		foot  b4 50 61 01
1 original
	raw 6
		head  01 01 -- fe ff
	gzip 18
		head  1f 8b 08 --  -- -- -- --  04 03 [01 01 -- fe ff]
		foot  [43 be b7 e8]  01 -- -- --
	deflate c
		head  78 01 [01 01 -- fe ff]
		foot  -- 62 -- 62
80 original
	raw 85
		head  01 80 -- 7f ff
	gzip 97
		head  1f 8b 08 --  -- -- -- --  04 03 [01 80 -- 7f ff]
		foot  [8c 36 2b f1]  80 -- -- --
	deflate 8b
		head  78 01 [01 80 -- 7f ff]
		foot  39 74 30 81
0 original
	raw 6
		head  01 -- -- ff ff
	gzip 18
		head  1f 8b 08 --  -- -- -- --  04 03 [01 -- -- ff ff]
		foot  [-- -- -- --]  -- -- -- --
	deflate c
		head  78 01 [01 -- -- ff ff]
		foot  -- -- -- 01
10000 original
	raw 1000a
		head  -- ff ff -- --
			data
		head  01 01 -- fe ff
			data
	gzip 1001c
		head  1f 8b 08 --  -- -- -- --  04 03 [-- ff ff -- --]
			data
		head  [01 01 -- fe ff]
			data
		foot  [ff 91 20 c3]  -- -- 01 --
	deflate 10010
		head  78 01 [-- ff ff -- --]
			data
		head  [01 01 -- fe ff]
			data
		foot  2d 87 05 b0



deflate 61
	0    0    1
	1   62   62   +62  +61
	2  125   c3   +c3  +61
	3  249  124  +124  +61
	4  3ce  185  +185  +61

	2a3  c5d8 ffc4
	2a4  c60c   34  +34  +61-fff1

	800  41fe  82e
	801  4a8d  88f  +88f  +61

deflate 10
	ffe   7ff  ffe1
	fff   7ff     0  +0   +10-fff1
	1000  80f    10  +10  +10
//////////////////////////////
raw
	5/head
		0  01=last  00=multi
		1  le16  len
		3  le16  NOT len
gzip
	f/head
		0-9  1f 8b 08 --  -- -- -- --  04 03
		a-e  -> raw/head
	8/foot
		0-3  le32  crc
		4-7  le32  len
deflate
	7/head
		0-1  78 01
		2-6  -> raw/head
	4/foot
		0-1  be16  sum of sum data bytes
		2-3  be16  1 + sum data bytes
 */

echo "deflate 0\n";
$zip = zlib_encode($txt, ZLIB_ENCODING_DEFLATE, 0);
$len = strlen($zip);
	$b1 = ord( $zip[$len-4] );
	$b2 = ord( $zip[$len-3] );
	$sum1 = ($b1 << 8) | $b2;

	$b1 = ord( $zip[$len-2] );
	$b2 = ord( $zip[$len-1] );
	$sum2 = ($b1 << 8) | $b2;
	printf("zlib sum %4x , %4x\n", $sum1, $sum2);

$len = strlen($txt);
	$sum1 = 0;
	$sum2 = 1;
	for ( $i=0; $i < $len; $i++ )
	{
		$b1 = ord( $txt[$i] );
		$sum2 += $b1;
		if ( $sum2 >= 0xfff1 )
			$sum2 -= 0xfff1;
		$sum1 += $sum2;
		if ( $sum1 >= 0xfff1 )
			$sum1 -= 0xfff1;
	}
	printf("calc sum %4x , %4x\n", $sum1, $sum2);
