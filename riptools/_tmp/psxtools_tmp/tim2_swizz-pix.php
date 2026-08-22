<?php
$w = 0x40;
$h = 0x40;

printf("w %x h %x sz %x\n", $w, $h, $w*$h);
function odd($n) { return (int)$n % 2; }

$inter  = [0x00,0x10 , 0x02,0x12 , 0x11,0x01 , 0x13,0x03];
$matrix = [0,1,-1,0];
$tile   = [4,-4];

$new = [];
$pix = [];
for ( $y=0; $y < $h; $y++ )
{
	$oddy = odd($y);
	for ( $x=0; $x < $w; $x++ )
	{
		$xx = $x + odd($y/4) * $tile[ odd($x/4) ];
		$yy = $y + $matrix[$y%4];
		printf("xx %x yy %x\n", $xx, $yy);

		$b1 = (int)($x/4) % 4 ;
		$b2 = $y * $w ;
		if ( $oddy )
		{
			$b1 += 4;
			$b2 -= $w;
		}
		printf("b1 %x b2 %x\n", $b1, $b2);

		$i = $inter[$b1] + ($x*4)%16 + $x/16*32 + $b2;
		$j = $yy * $w + $xx;

		printf("%x,%x  new[%x] = pix[%x]\n", $x, $y, $j, $i);
		$new[$j] = $i;
		$pix[$i] = $j;
	} // for ( $x=0; $x < $w; $x++ )
} // for ( $y=0; $y < $h; $y++ )

ksort($pix);
	printf("== pix [%d] ==\n", count($pix)-$w*$h);
	foreach ( $pix as $i => $j )
		printf("pix[%x] = new[%x]\n", $i, $j);
ksort($new);
	printf("== new [%d] ==\n", count($new)-$w*$h);
	foreach ( $new as $j => $i )
		printf("new[%x] = pix[%x]\n", $j, $i);

/*
 0 6 c 12 18 1e 24 2a
*/
