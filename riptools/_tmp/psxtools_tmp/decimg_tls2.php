<?php
require('/opt/rfslib.php');

function decimgfile( $fname )
{
	$imgp = fopen( "{$fname}", "rb" );
	$decp = fopen( "{$fname}.dec", "wb+" );
		if ( ! $imgp || ! $decp )   return;

	printf("=== DEC_IMG : {$fname} ===\n");

$x197d78 = 0x197d78;
$a0 = 0x1985a0; // sb
//$a0 = 0; // sb
$a1 = 0; // lbu
$a3 = 0;
$v0 = 0;
////////////////////////////////////////
pf4ac:
	$t4 = rfs_fgetint( $imgp, $a1, 4 );
		printf("pf4ac  lw  t4(%x), 0(a1(%x))\n", $t4, $a1);
	$t2 = $t4;
	$a1 += 4;
////////////////////////////////////////
// LOOP clear area
pf4bc:
	// lui  $1, 0x19
	// addu $1, $a3
	// sb   $0, 0x7d78($1)
	//$one = $x197d78;
	//rfs_fputint( $decp, $one + $a3, 0, 1 );
		//printf("pf4bc  sb  0, a3(%x)(one(%x))\n", $a3, $one);
	//$a3++;
	$t0 = 0x7de;
	//printf("pf4bc  if ( (a3(%x) < 0x7de) != 0 )  goto pf4bc LOOP\n", $a3);
		//if ( ($a3 < 0x7de) != 0 )  goto pf4bc;
pf4d8:
	$t3 = 0;
	printf("pf4d8  if ( 0 >= t2(%x) )  goto end\n", $t2);
		if ( 0 >= $t2 )  goto end;
pf4e0:
	$t5 = $x197d78;
pf4e8:
	$t3 >>= 1;
	$v0 = $t3 & 0x100;
	$v0tmp = $v0;
	$v0 = $t3 & 0x1;
	printf("pf4e8  if ( 0 != v0tmp(%x) )  goto pf508\n", $v0tmp);
		if ( 0 != $v0tmp )  goto pf508;
pf4f8:
	$v1 = rfs_fgetint( $imgp, $a1, 1 );
		printf("pf4f8  lbu v1(%x), 0(a1(%x))\n", $v1, $a1);
	$a1++;
	$t3 = $v1 | 0xff00;
	$v0 = $t3 & 1;
pf508:
	$v0tmp = $v0;
	$v0 = $t0 + $t5;
	printf("pf508  if ( 0 == v0tmp(%x) )  goto pf534\n", $v0tmp);
		if ( 0 == $v0tmp )  goto pf534;
pf510:
	$v1 = rfs_fgetint( $imgp, $a1, 1 );
		printf("pf510  lbu v1(%x), 0(a1(%x))\n", $v1, $a1);
	$a1++;
	rfs_fputint( $decp, $a0, $v1, 1 );
		printf("pf510  sb  v1(%x), 0(a0(%x))\n", $v1, $a0);
	$a0++;
	$t2--;
	rfs_fputint( $decp, $v0, $v1, 1 );
		printf("pf510  sb  v1(%x), 0(v0(%x))\n", $v1, $v0);
	$t0 = ($t0 + 1) & 0x7ff;
	printf("pf510  goto pf5a8\n");
		goto pf5a8;
pf534:
	$a3 = rfs_fgetint( $imgp, $a1, 1 );
		printf("pf534  lbu a3(%x), 0(a1(%x))\n", $a3, $a1);
	$a1++;
	$t1 = rfs_fgetint( $imgp, $a1, 1 );
		printf("pf534  lbu t1(%x), 0(a1(%x))\n", $t1, $a1);
	$a1++;
	$v0 = $t1 & 0xe0;
	$v0 <<= 3;
	$a3 |= $v0;
	$v0 = $t1 & 0x1f;
	$t1 = $v0 + 2;
	$a2 = 0;
	printf("pf534  if ( (t1(%x) < 0) != 0 )  goto pf5a8\n", $t1);
		if ( ($t1 < 0) != 0 )  goto pf5a8;
pf564:
	$v0 = $a3 + $a2;
	$v0 = $v0 & 0x7ff;
	// lui  $1, 0x19
	// addu $1, $v0
	// lbu  $v1, 0x7d78($1)
	$one = $x197d78;
	$v1 = rfs_fgetint( $decp, $one + $v0, 1 );
		printf("pf564  lbu v1(%x), v0(%x)(one(%x))\n", $v1, $v0, $one);
	rfs_fputint( $decp, $a0, $v1, 1 );
		printf("pf564  sb  v1(%x), 0(a0(%x))\n", $v1, $a0);
	$t2--;
	$a0++;
	printf("pf564  if ( 0 >= t2(%x) )  goto end\n", $t2);
		if ( 0 >= $t2 )  goto end;
pf58c:
	$v0 = $t0 + $t5;
	rfs_fputint( $decp, $v0, $v1, 1 );
		printf("pf58c  sb  v1(%x), 0(v0(%x))\n", $v1, $v0);
	$t0++;
	$a2++;
	$t0 = $t0 & 0x7ff;
	printf("pf58c  if ( (t1(%x) < a2(%x)) == 0 )  goto pf564 LOOP\n", $t1, $a2);
		if ( ($t1 < $a2) == 0 )  goto pf564;
pf5a8:
	printf("pf5a8  if ( 0 < t2(%x) )  goto pf4e8 LOOP\n", $t2);
		if ( 0 < $t2 )  goto pf4e8;
////////////////////////////////////////
end:
	$v0 = $t4;

	fclose($imgp);
	fclose($decp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	decimgfile( $argv[$i] );
