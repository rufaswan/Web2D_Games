<?php
require 'common.inc';
require 'common-guest.inc';
require 'common-quad.inc';
require 'common-zlib.inc';

function fmbp_s7_matrix( &$s7_row )
{
	$float = array();
	for ( $i=0; $i < 0x30; $i += 4 )
	{
		$b = str2int($s7_row, $i, 4);
		$float[$i] = float32($b);
	}

	// r g b a  mx my mz  rx ry rz  sx sy
	$move   = array($float[16] , $float[20] , $float[24]);
	$rotate = array($float[28] , $float[32] , $float[36]);
	$scale  = array($float[40] , $float[44]);
	printf("    s7  move   %10.2f,%10.2f,%10.2f\n",   $move[0],   $move[1],   $move[2]);
	printf("    s7  rotate %10.2f,%10.2f,%10.2f\n", $rotate[0], $rotate[1], $rotate[2]);
	printf("    s7  scale  %10.2f,%10.2f\n"       ,  $scale[0],  $scale[1]);

	// in scale - rotate z-y-x - move - flip order
	$m = matrix_scale(4, $scale[0], $scale[1]);

	$t = matrix_rotate_z(4, $rotate[2]);
	if ( $t !== -1 )
		$m = matrix_multi44($m, $t);

	$t = matrix_rotate_y(4, $rotate[1]);
	if ( $t !== -1 )
		$m = matrix_multi44($m, $t);

	$t = matrix_rotate_x(4, $rotate[0]);
	if ( $t !== -1 )
		$m = matrix_multi44($m, $t);

	$m[0+3] += $move[0];
	$m[4+3] += $move[1];
	$m[8+3] += $move[2];

	printf("    s7  matrix 4x4\n");
	printf("      %10.2f,%10.2f,%10.2f,%10.2f,\n", $m[ 0], $m[ 1], $m[ 2], $m[ 3]);
	printf("      %10.2f,%10.2f,%10.2f,%10.2f,\n", $m[ 4], $m[ 5], $m[ 6], $m[ 7]);
	printf("      %10.2f,%10.2f,%10.2f,%10.2f,\n", $m[ 8], $m[ 9], $m[10], $m[11]);
	printf("      %10.2f,%10.2f,%10.2f,%10.2f,\n", $m[12], $m[13], $m[14], $m[15]);
	return;
}

function fmbp_s6_s4rect( &$ram, $sx_off, $s6_row )
{
	$float = array();
	for ( $i=0; $i < 0x10; $i += 4 )
	{
		$b = str2int($s6_row, $i, 4);
		$float[$i] = float32($b);
	}
	printf("    s6  x1,y1 <-> x2,y2\n");
	printf("      %10.2f,%10.2f <-> %10.2f,%10.2f\n", $float[0], $float[4], $float[8], $float[12]);

	$s4_id   = str2int($s6_row, 0x10, 2);
	$s4_cnt  = str2int($s6_row, 0x14, 1);

	$rect = array( BIT24 , BIT24 , -BIT24 , -BIT24 );
	for ( $i4=0; $i4 < $s4_cnt; $i4++ )
	{
		$s4_off = $sx_off[4] + (($s4_id + $i4) * 0x18);
		$s4_row = substr($ram, $s4_off, 0x18);
		printf("      %8x  s4[%4x] = %s\n", $s4_off, $s4_id+$i4, printhex($s4_row));

		$s0_id  = str2int($s4_row, 0x08, 2);
		$s1_id  = str2int($s4_row, 0x04, 2);
		$s2_id  = str2int($s4_row, 0x10, 2);
		$s0_off = $sx_off[0] + ($s0_id * 0x20);
		$s1_off = $sx_off[1] + ($s1_id * 0x20);
		$s2_off = $sx_off[2] + ($s2_id * 0x20);
		$s0_row = substr($ram, $s0_off, 0x20);
		$s1_row = substr($ram, $s1_off, 0x20);
		$s2_row = substr($ram, $s2_off, 0x20);
		printf("        %8x  s0[%4x] = %s\n", $s0_off, $s0_id, printhex($s0_row));
		printf("        %8x  s1[%4x] = %s\n", $s1_off, $s1_id, printhex($s1_row));
		printf("        %8x  s2[%4x] = %s\n", $s2_off, $s2_id, printhex($s2_row));

		for ( $i2 = 8; $i2 < 24; $i2 += 4 )
		{
			$x = str2int($s2_row, $i2 + 0, 2, true) / 0x10;
			$y = str2int($s2_row, $i2 + 2, 2, true) / 0x10;
			if ( $x < $rect[0] )  $rect[0] = $x;
			if ( $y < $rect[1] )  $rect[1] = $y;
			if ( $x > $rect[2] )  $rect[2] = $x;
			if ( $y > $rect[3] )  $rect[3] = $y;
		}
	} // for ( $i4=0; $i4 < $s4_cnt; $i4++ )

	printf("    s4  max x1,y1 <-> x2,y2\n");
	printf("      %10.2f,%10.2f <-> %10.2f,%10.2f\n", $rect[0], $rect[1], $rect[2], $rect[3]);
	return;
}
//////////////////////////////
function sect_CTRL( &$ram, $ctrl_off, &$sx_off )
{
	$float = array();
	for ( $i=0; $i < 0x70; $i += 4 )
	{
		$b = str2int($ram, $ctrl_off + $i, 4);
		$float[$i] = float32($b);
	}

	printf("  CTRL matrix 4x4\n");
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[ 0], $float[ 4], $float[ 8], $float[12]);
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[16], $float[20], $float[24], $float[28]);
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[32], $float[36], $float[40], $float[44]);
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[48], $float[52], $float[56], $float[60]);

	printf("  CTRL rgba float*ff\n");
	printf("    # %2x %2x %2x %2x\n", $float[64]*0xff, $float[ 68]*0xff, $float[ 72]*0xff, $float[ 76]*0xff);
	printf("    # %2x %2x %2x %2x\n", $float[80]*0xff, $float[ 84]*0xff, $float[ 88]*0xff, $float[ 92]*0xff);
	printf("    # %2x %2x %2x %2x\n", $float[96]*0xff, $float[100]*0xff, $float[104]*0xff, $float[108]*0xff);

	//////////////////////////////

	$s9_id  = str2int($ram, $ctrl_off + 0x80, 2);
	$s9_off = $sx_off[9] + ($s9_id * 0x30);
	$s9_row = substr($ram, $s9_off, 0x30);
	printf("  %8x  s9[%4x] = %s\n", $s9_off, $s9_id, printhex($s9_row));

	$float = array();
	for ( $i=0; $i < 0x10; $i += 4 )
	{
		$b = str2int($s9_row, $i, 4);
		$float[$i] = float32($b);
	}

	printf("    s9  name = %s\n", substr0($s9_row, 0x10));
	printf("    s9  x1,y1 <-> x2,y2\n");
	printf("      %10.2f,%10.2f <-> %10.2f,%10.2f\n", $float[0], $float[4], $float[8], $float[12]);

	//////////////////////////////

	$float = array();
	for ( $i=0; $i < 0x10; $i += 4 )
	{
		$b = str2int($ram, $ctrl_off + 0xc0 + $i, 4);
		$float[$i] = float32($b);
	}
	printf("  CTRL  x1,y1 <-> x2,y2\n");
	printf("    %10.2f,%10.2f <-> %10.2f,%10.2f\n", $float[0], $float[4], $float[8], $float[12]);

	return;
}

function sect_WORK( &$ram, $work_off, &$sx_off )
{
	$float = array();
	for ( $i=0; $i < 0x60; $i += 4 )
	{
		$b = str2int($ram, $work_off + $i, 4);
		$float[$i] = float32($b);
	}

	printf("  WORK matrix 4x4\n");
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[ 0], $float[ 4], $float[ 8], $float[12]);
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[16], $float[20], $float[24], $float[28]);
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[32], $float[36], $float[40], $float[44]);
	printf("    %10.2f,%10.2f,%10.2f,%10.2f,\n", $float[48], $float[52], $float[56], $float[60]);

	printf("  WORK x1,y1 <-> x2,y2\n");
	printf("    %10.2f,%10.2f <-> %10.2f,%10.2f\n", $float[64], $float[68], $float[72], $float[76]);
	printf("    %10.2f,%10.2f <-> %10.2f,%10.2f\n", $float[80], $float[84], $float[88], $float[92]);

	//////////////////////////////

	$s8_time_c = str2int($ram, $work_off + 0x6c, 2); // countdown
	$s8_time   = str2int($ram, $work_off + 0x6e, 2);
	$sa_id     = str2int($ram, $work_off + 0x70, 2);
	$s8_id_c   = str2int($ram, $work_off + 0x72, 2);
	$s8_set_st = str2int($ram, $work_off + 0x74, 1);

	$sa_off = $sx_off[10] + ($sa_id * 8);
	$sa_row = substr($ram, $sa_off, 8);
	printf("  %8x  sa[%4x] = %s\n", $sa_off, $sa_id, printhex($sa_row));

	$s8_id  = str2int($sa_row, 0, 2) + $s8_id_c + $s8_set_st;
	$s8_off = $sx_off[8] + ($s8_id * 0x20);
	$s8_row = substr($ram, $s8_off, 0x20);
	printf("  %8x  s8[%4x] = %s\n", $s8_off, $s8_id, printhex($s8_row));

	$s6_id  = str2int($s8_row, 0, 2);
	$s7_id  = str2int($s8_row, 4, 2);
	$s6_off = $sx_off[6] + ($s6_id * 0x18);
	$s7_off = $sx_off[7] + ($s7_id * 0x30);
	$s6_row = substr($ram, $s6_off, 0x18);
	$s7_row = substr($ram, $s7_off, 0x30);
	printf("  %8x  s6[%4x] = %s\n", $s6_off, $s6_id, printhex($s6_row));
	printf("  %8x  s7[%4x] = %s\n", $s7_off, $s7_id, printhex($s7_row));

	//////////////////////////////

	fmbp_s7_matrix($s7_row);
	fmbp_s6_s4rect($ram, $sx_off, $s6_row);
	return;
}

function sect_CMNR( &$ram, $cmnr_off, &$sx_off )
{
	$off = chrint($cmnr_off, 4);

	$len = strlen($ram);
	$pos = 0;
	while ( $pos < $len )
	{
		$pos = strpos($ram, $off, $pos);
		if ( $pos === false )
			return;

		$bak = $pos;
			$pos++;
		if ( $bak & 3 ) // aligned to 4-bytes
			continue;

		// if next is CTEX and control
		$ctex_off = str2int($ram, $bak+4, 4);
		$ctrl_off = str2int($ram, $bak+8, 4);
		if ( substr($ram,$ctex_off,4) !== 'CTEX' )
		{
			printf("%8x\n", $bak);
			continue;
		}

		$ctrl_cmnr_off = str2int($ram, $ctrl_off + 0x78, 4);
		$ctrl_ctex_off = str2int($ram, $ctrl_off + 0x7c, 4);
		if ( $cmnr_off === $ctrl_cmnr_off && $ctex_off === $ctrl_ctex_off )
		{
			// active WORK
			$work_off = $bak - 0x60;
			$name1 = substr0($ram, $cmnr_off + 0x1a);
			$name2 = substr0($ram, $ctex_off + 0x1a);
			printf("%8x  WORK  CMNR %s + CTEX %s [active]\n", $work_off, $name1, $name2);
			sect_WORK($ram, $work_off, $sx_off);

			printf("%8x  CTRL [active]\n", $ctrl_off);
			sect_CTRL($ram, $ctrl_off, $sx_off);
		}
		else
		{
			// deleted WORK
			$work_off = $bak - 0x60;
			$name1 = substr0($ram, $cmnr_off + 0x1a);
			$name2 = substr0($ram, $ctex_off + 0x1a);
			printf("%8x  WORK  CMNR %s + CTEX %s [deleted]\n", $work_off, $name1, $name2);
		}
	} // while ( $pos < $len )
	return;
}

function sect_FMBP( &$ram, $fmbp_off, &$sx_off )
{
	$off = chrint($fmbp_off, 4);

	$len = strlen($ram);
	$pos = 0;
	while ( $pos < $len )
	{
		$pos = strpos($ram, $off, $pos);
		if ( $pos === false )
			return;

		if ( $pos & 3 ) // aligned to 4-bytes
		{
			$pos++;
			continue;
		}

		$cmnr_off = $pos - 0x3c;
		if ( substr($ram,$cmnr_off,4) === 'CMNR' )
		{
			$name = substr0($ram, $cmnr_off + 0x1a);
			printf("%8x  CMNR  %s\n", $cmnr_off, $name);
			sect_CMNR($ram, $cmnr_off, $sx_off);
		}
		else
			printf("%8x\n", $pos);

		$pos++;
	} // while ( $pos < $len )
	return;
}
//////////////////////////////
function p2sram( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return '';

	$list = zipfile_list($fp);
	foreach ( $list as $lv )
	{
		if ( stripos($lv['file'], 'eememory.bin') !== false )
		{
			$ram = fp2str($fp, $lv['pos'], $lv['size1']);
			if ( $lv['size1'] != $lv['size2'] )
				$ram = zlib_decode($ram);
			return $ram;
		}
	} // foreach ( $list as $lv )
	return '';
}

function odinp2s( $fname )
{
	$ram = p2sram($fname);
	if ( empty($ram) )  return;

	save_file("$fname.ram", $ram);

	$len = strlen($ram);
	$pos = 0;
	while ( $pos < $len )
	{
		$pos = strpos($ram, 'FMBP', $pos);
		if ( $pos === false )
			return;

		$mgc = $ram[$pos+8] . $ram[$pos+0x14];
		if ( $mgc === "\xa0\x55" || $mgc === "\xa0\xc9" )
		{
			$name = substr0($ram, $pos + 0x80);
			echo "------------------------------\n";
			printf("%8x  FMBP  %s\n", $pos, $name);

			$sx_off = array();
			for ( $i = 0x54; $i < 0x80; $i += 4 )
				$sx_off[] = str2int($ram, $pos + $i, 4);

			if ( $sx_off[0] < $pos )
				printf("  CDread() copy. skipped.\n");
			else
				sect_FMBP($ram, $pos, $sx_off);
		}

		$pos += 4;
	} // while ( $pos < $len )
	return;
}

printf("%s  P2S_FILE...\n", $argv[0]);
for ( $i=1; $i < $argc; $i++ )
	odinp2s( $argv[$i] );

/*
//////////////////////////////
interpolation
	mod = (s8[f] * ((s8[6] - x) / s8[f])) / s8[6]
		= (s8[6] - x) / s8[6]

	switch ( inter )
	case 2
		mod2 = mod * mod
		mod3 = mod * mod * mod

		pmod  = (mod3 * -0.5) + (mod2 *  1.0) + (mod * -0.5)
		cmod  = (mod3 *  1.5) + (mod2 * -2.5) + 1
		n1mod = (mod3 * -1.5) + (mod2 *  2.0) + (mod *  0.5)
		n2mod = (mod3 *  0.5) + (mod2 * -0.5) + 0
		res = (prev * pmod) + (cur * cmod) + (next * n1mod) + (next2 * n2mod)

	case 1
		cmod = 1.0 - mod
		nmod = mod
		res = (cur * cmod) + (next * nmod)

	case 0
		res = cur
	// switch ( inter )

sample comparison
	if s8[f] = 4 , s8[6] = 4
	case 2
		x    mod   prev   cur   nxt1  nxt2
		x=4  0      0     1.0   0      0    = 1.0
		x=3  0.25  -0.07  0.87  0.23  -0.02 = 1.0
		x=2  0.5   -0.06  0.56  0.56  -0.06 = 1.0
		x=1  0.75  -0.02  0.23  0.87  -0.07 = 1.0
		x=0  1.0    0     0     1.0    0    = 1.0
	case 1
		x    mod   cur   next
		x=4  0     1.0   0    = 1.0
		x=3  0.25  0.75  0.25 = 1.0
		x=2  0.5   0.5   0.5  = 1.0
		x=1  0.75  0.25  0.75 = 1.0
		x=0  1.0   0     1.0  = 1.0
//////////////////////////////
alpha blending
	-      D  C  B  A         ( A.rgb  - B.rgb) *  C.a +  D.rgb , fog
	0  44  -1 -- -1 --  0101  (FG.rgb - BG.rgb) * FG.a + BG.rgb , var3
	1  48  -1 -- 1- --  0201  (FG.rgb -   0   ) * FG.a + BG.rgb , 0
	2  42  -1 -- -- 1-  2001  (  0    - FG.rgb) * FG.a + BG.rgb , 0
	3  54  -1 -1 -1 --  0111  (FG.rgb - BG.rgb) * BG.a + BG.rgb , var3
	4  58  -1 -1 1- --  0211  (FG.rgb -   0   ) * BG.a + BG.rgb , 0
	5  52  -1 -1 -- 1-  2011  (  0    - FG.rgb) * BG.a + BG.rgb , 0
		0=FG  1=BG  2=0  3=unused

	(FG.rgb - BG.rgb) * FG.a + BG.rgb
	= FG.rgb*FG.a + (-BG.rgb*FG.a) + BG.rgb
	= FG.rgb*FG.a + BG.rgb(-1*FG.a + 1)
	= FG.rgb*FG.a + BG.rgb(1 - FG.a)

	{ 0          , OP_ADD          , SRC1_ALPHA , INV_SRC1_ALPHA} , // 0101: (Cs - Cd)*As + Cd ==> Cs*As + Cd*(1 - As)
	{ BLEND_ACCU , OP_ADD          , SRC1_ALPHA , CONST_ONE}      , //?0201: (Cs -  0)*As + Cd ==> Cs*As + Cd
	{ BLEND_ACCU , OP_REV_SUBTRACT , SRC1_ALPHA , CONST_ONE}      , //?2001: (0  - Cs)*As + Cd ==> Cd - Cs*As
	{ 0          , OP_ADD          , DST_ALPHA  , INV_DST_ALPHA}  , // 0111: (Cs - Cd)*Ad + Cd ==> Cs*Ad + Cd*(1 - Ad)
	{ 0          , OP_ADD          , DST_ALPHA  , CONST_ONE}      , // 0211: (Cs -  0)*Ad + Cd ==> Cs*Ad + Cd
	{ 0          , OP_REV_SUBTRACT , DST_ALPHA  , CONST_ONE}      , // 2011: (0  - Cs)*Ad + Cd ==> Cd - Cs*Ad
//////////////////////////////
func 16bc5c
	s6-2 == 1
		TEX1_1 = 60
	s6-4 == 0
	else
		ALPHA_1 = 44
	loop s4
		stack[120] = (stack[150] << 10) | (stack[140] << 8) | stack[130]
		s4-2 == 0
			s4-8 == 1
				fade = 0
			s4-4 == 0
				s6-2 == 0
					s4-1 == 0
						TEX1_1 = 0
					else
						TEX1_1 = 60
			s6-4 == 0
				switch s4.blend
					0  ALPHA_1 = 44 , (fade) FOGCOL = stack[120]
					1  ALPHA_1 = 48 , (fade) FOGCOL = 0
					2  ALPHA_1 = 42 , (fade) FOGCOL = 0
					3  ALPHA_1 = 54 , (fade) FOGCOL = stack[120]
					4  ALPHA_1 = 58 , (fade) FOGCOL = 0
					5  ALPHA_1 = 52 , (fade) FOGCOL = 0
			s6-8 == 0
				s4-10 == 0
					(fade) switch s4.blend
						0  FOGCOL = stack[120]
						1  FOGCOL = 0
						2  FOGCOL = 0
						3  FOGCOL = stack[120]
						4  FOGCOL = 0
						5  FOGCOL = 0
				else
					FOGCOL = (rgb)white
			s6-10 == 0
				s4-20 == 0
					( FB == 1 )  FB = 0
				else
					( FB == 0 )  FB = 1
	endloop
ret 16bc5c
//////////////////////////////
func 171fc0
	w80-2 == 1
		return
	w80-1 == 0
	else
		w80 &= fe or ~01
	w80-2 == 1
		return
	w80-8 == 1
		s8-80 == 0
		else
			callback()
		w80 &= f7 or ~08
	s8-100 == 0
		s8-4 == 0
			s8-8 == 0
				w80[72]++
				w80[6c] = s8+1[6]
				w80[6e] = s8+1[6]
			else
				w80 |= 1
				w80 |= 2
		else
			w80[72] = s8[c]
			s8 = w80[72] + w80[74]
			s8 < sa[2]
				w80[6c] = s8[6]
				w80[6e] = s8[6]
				w80 |= 1
			else
				w80 |= 1
				w80 |= 2
	else
		foreach work80
			w80 |= 1
			w80 |= 2
	w80 |= 8
ret 171fc0
//////////////////////////////
func 1722d0
	w80-4 == 0
		s8-40 == 0
			func 1723f0
				s8[10] == 2  s7 inter 2
				s8[10] == 1  s7 inter 1
				s8[11] == 2  s6 inter 2
				s8[11] == 1  s6 inter 1
				w80 |= 10
				s8[e] == 2  s5s3 inter 2
				s8[e] == 1  s5s3 inter 1
				s7_scale()
				s7_rotate()
				s7_move()
				s8-1 == 0
				else
					flip_x()
				s8-2 == 1
					flip y()
				else
				s6.s4cnt == 0
					w80 &= df or ~20
				else
					s6 rect stuff
					w80 |= 20
				a0 != 0 && s6.s4cnt != 0 && s8-400 == 0
					s7 rgba stuff
					s8-20 == 1
						NONE = 1
					else
						NONE = ???-2 == 1
			ret 1723f0
ret 1722d0
//////////////////////////////
s4.flags
	1  filter , 0=nearest , 1=reduced texture
	2  0=render , 1=skip , hitbox/after effect
	4  1=no texture/vertex color only
	8  1=no fade/alpha
	10  1=white fog , 0=fade fog
	20  0=render to NONE , 1=render to gp
	40  *not used*
	80  *not used*
s5.flags
	1  *6b4-200*  attack
	2  *6b4-2000*  hittable
	4  *6b4-10000*
	8
	10  (item)
	20  (head)
	40  *6b4-10000* (dragon body) block/push
	80  *6b4-10000*
	200  (critical strike)  dust
s6.flags
	1
	2  1=reduced texture , 0=refer s4-1
	4  1=disable s4.blend
	8  1=disable fade
	10  1=disable persistence effect/motion blur
	20  *not used*
	40  *not used*
	80  *not used*
s8.flags
	1  1=flip x
	2  1=flip y
	4  1=anim loop
	8  1=anim end + cleared
	10  (odin)
	20  ???
	40  1=skip
	80  1=sound effect playback
	100  1=end all anim (odin)
	200  *not used*
	400  1=no s4,only s5
	800  (grim)
	1000  (grim)
	2000  (grim)
	4000  *not used*
	8000  *not used*
 */
