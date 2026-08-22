<?php
$parts = 256 / 32;
$strips = 2;
$colors = 8;
$blocks = 2;
$i = 0;
for ( $p=0; $p < $parts; $p++ )
{
	for ( $b=0; $b < $blocks; $b++ )
	{
		for ( $s=0; $s < $strips; $s++ )
		{
			for ( $c=0; $c < $colors; $c++ )
			{
				$v = 0;
				$v += $p * $blocks * $strips * $colors;
				$v += $s * $strips * $colors;
				$v += $b * $colors;
				$v += $c;
				printf("%d %d = %x %x\n", $i, $v, $i, $v);
				$i++;
			} // for ( $c=0; $c < $colors; $c++ )
		} // for ( $s=0; $s < $strips; $s++ )
	} // for ( $b=0; $b < $blocks; $b++ )
} // for ( $p=0; $p < $parts; $p++ )

/*
 0- 7  10-17
 8- f  18-20
20-27  30-37
28-2f  38-3f
40-47  50-57
48-4f  58-5f
...
e0-e7  f0-f7
e8-ef  f8-ff
*/
