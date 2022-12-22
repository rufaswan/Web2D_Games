<?php
require 'common.inc';
require 'quad_vanillaware.inc';

// NONE + 84 -> CMNR + 3c -> FMBP
function odin_CMNR( &$file, $fmbp_off )
{
	$bin  = chrint($fmbp_off, 4);
	$cmnr = strpos_all($file, $bin);
	foreach ( $cmnr as $cmnr_off )
	{
		if ( substr($file, $cmnr_off - 0x3c, 4) === 'CMNR' )
		{
			printf("  %8x  CMNR + 3c -> %8x  FMBP\n", $cmnr_off, $fmbp_off);
			$bin  = chrint($cmnr_off - 0x3c, 4);
			$none = strpos_all($file, $bin);
			foreach( $none as $none_off )
			{
				if ( substr($file, $none_off - 0x84, 4) === 'NONE' )
				{
					printf("    %8x  NONE + 84 -> %8x  CMNR\n", $none_off, $cmnr_off - 0x3c);
					$bin  = chrint($none_off - 0x84, 4);
					$work = strpos_all($file, $bin);
					foreach( $work as $work_off )
						printf("      %8x  -> %8x  NONE\n", $work_off, $none_off - 0x84);
				}

				// not NONE data
				else
					printf("    %8x  -> %8x  CMNR\n", $none_off, $cmnr_off - 0x3c);
			} // foreach( $none as $none_off )
		}

		// not CMNR data
		else
			printf("  %8x  -> %8x  FMBP\n", $cmnr_off, $fmbp_off);
	} // foreach ( $cmnr as $cmnr_off )
	return;
}

function odin_FMBP( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return '';

	global $gp_data;
	$sect = $gp_data['ps2_odin']['sect'];

	$fmbp = strpos_all($file, 'FMBP');
	$ret  = array();
	foreach ( $fmbp as $fmbp_off )
	{
		// not FMBP file, but for strcmp()
		$b1 = str2int($file, $fmbp_off + 8, 4);
		if ( $b1 !== 0xa0 )
			continue;

		// original file from ISO , invalid offset
		$b1 = str2int($file, $fmbp_off + 0x54, 4);
		if ( $b1 < $fmbp_off )
			continue;

		$fn = substr0($file, $fmbp_off + 0x80);
		foreach ( $sect as $sk => $sv )
		{
			$p = str2int($file, $fmbp_off + $sv['p'], 4);
			$c = str2int($file, $fmbp_off + $sv['c'][0], $sv['c'][1]);
			$data = array(
				'pos' => $p,
				'siz' => $c * $sv['k'],
				'mbp' => $fn,
				'sec' => $sk,
				'blk' => $sv['k'],
			);

			printf("%s [ s%x ] = %8x + %8x\n", $data['mbp'], $data['sec'], $data['pos'], $data['siz']);
			$ret[] = $data;
		} // foreach ( $sect as $sk => $sv )

		//odin_CMNR($file, $fmbp_off);
	} // foreach ( $fmbp as $fmbp_off )

	return $ret;
}

function odinoff( $fmbp, $hex )
{
	if ( empty($fmbp) )
		return;
	$hex = hexdec($hex);

	foreach ( $fmbp as $fv )
	{
		$off = $hex - $fv['pos'];
		if ( $off < 0 )
			continue;
		if ( $off >= $fv['siz'] )
			continue;

		$id = 0;
		while ( $off > $fv['blk'] )
		{
			$id++;
			$off -= $fv['blk'];
		}
		printf("%8x = %s [ s%x ][ %x ] + %x\n", $hex, $fv['mbp'], $fv['sec'], $id, $off);
		return;
	} // foreach ( $fmbp as $fv )
	return;
}

printf("%s  eeMemory.bin  HEX...\n", $argv[0]);
if ( $argc < 2 )  exit();

$fmbp = odin_FMBP( $argv[1] );

for ( $i=2; $i < $argc; $i++ )
	odinoff( $fmbp, $argv[$i] );

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
	-      D  C  B  A   (A      - B     ) * C    + D      , fog
	0  44  -1 -- -1 --  (FG.rgb - BG.rgb) * FG.a + BG.rgb , var3
	1  48  -1 -- 1- --  (FG.rgb - 0     ) * FG.a + BG.rgb , 0
	2  42  -1 -- -- 1-  (0      - FG.rgb) * FG.a + BG.rgb , 0
	3  54  -1 -1 -1 --  (FG.rgb - BG.rgb) * BG.a + BG.rgb , var3
	4  58  -1 -1 1- --  (FG.rgb - 0     ) * BG.a + BG.rgb , 0
	5  52  -1 -1 -- 1-  (0      - FG.rgb) * BG.a + BG.rgb , 0
//////////////////////////////
func 16c550
	s6-2 == 1
		TEX1_1 = 60
	s6-4 == 0
	else
		ALPHA_1 = 44
	loop s4
		stack[120] = (stack[150] << 10) | (stack[140] << 8) | stack[130]
		s4-2 == 0
			s4-8 == 1
				fog = 0
			s4-4 == 0
				s6-2 == 0
					s4-1 == 0
						TEX1_1 = 0
					else
						TEX1_1 = 60
			s6-4 == 0
				switch s4.blend
					0  ALPHA_1 = 44 , (fog) FOGCOL = stack[120]
					1  ALPHA_1 = 48 , (fog) FOGCOL = 0
					2  ALPHA_1 = 42 , (fog) FOGCOL = 0
					3  ALPHA_1 = 54 , (fog) FOGCOL = stack[120]
					4  ALPHA_1 = 58 , (fog) FOGCOL = 0
					5  ALPHA_1 = 52 , (fog) FOGCOL = 0
			s6-8 == 0
				s4-10 == 0
					(fog) switch s4.blend
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
ret 16c550
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
	1  ???
	2  skip
	4  disable texture
	8  ???
	10  ???
	20  front->back buffer
	40  *not used*
	80  *not used*
	s4-10 == 1 , pcsx2 crash
s6.flags
	1
	2  disable all texture
	4  disable s4.blend
	8  disable all fog
	10  disable front/back buffer
	20  *not used*
	40  *not used*
	80  *not used*
s8.flags
	1  flip x
	2  flip y
	4  anim loop
	8  anim end + cleared
	10  (odin)
	20  ???
	40  skip
	80  sound effect playback
	100  end all anim (odin)
	200  *not used*
	400  is attachment
	800  (grim)
	1000  (grim)
	2000  (grim)
	4000  *not used*
	8000  *not used*
gs_reg
	14  TEX1_1
	3d  FOGCOL
	42  ALPHA_1

 */
