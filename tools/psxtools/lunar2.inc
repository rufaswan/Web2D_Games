<?php
/*
[license]
[/license]
 */

function sectquad( &$pix, &$meta, $p, $ceil )
{
	$qax = sint16( $meta[$p+ 6] . $meta[$p+ 7] );
	$qay = sint16( $meta[$p+ 8] . $meta[$p+ 9] );
	$qbx = sint16( $meta[$p+10] . $meta[$p+11] );
	$qby = sint16( $meta[$p+12] . $meta[$p+13] );
	$qcx = sint16( $meta[$p+14] . $meta[$p+15] );
	$qcy = sint16( $meta[$p+16] . $meta[$p+17] );
	$qdx = sint16( $meta[$p+18] . $meta[$p+19] );
	$qdy = sint16( $meta[$p+20] . $meta[$p+21] );
		$qax = (int)($qax * SCALE);
		$qay = (int)($qay * SCALE);
		$qbx = (int)($qbx * SCALE);
		$qby = (int)($qby * SCALE);
		$qcx = (int)($qcx * SCALE);
		$qcy = (int)($qcy * SCALE);
		$qdx = (int)($qdx * SCALE);
		$qdy = (int)($qdy * SCALE);

	$pix['vector'] = array(
		array( $qax+$ceil , $qay+$ceil , 1 ),
		array( $qbx+$ceil , $qby+$ceil , 1 ),
		array( $qcx+$ceil , $qcy+$ceil , 1 ),
		array( $qdx+$ceil , $qdy+$ceil , 1 ),
	);

	$pix['src']['vector'] = array(
		array(                 0,                 0, 1),
		array($pix['src']['w']-1,                 0, 1),
		array($pix['src']['w']-1,$pix['src']['h']-1, 1),
		array(                 0,$pix['src']['h']-1, 1),
	);

	$des = array(
		array($qax,$qay,1),
		array($qbx,$qby,1),
		array($qcx,$qcy,1),
		array($qdx,$qdy,1),
	);

	quad_dump($pix['src']['vector'] , "1243", "src quad");
	quad_dump($des                  , "1243", "des quad");
	return;
}

function sectparts( &$meta, $dir )
{
	trace("== sectparts( $dir )\n");

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	global $gp_pix, $gp_clut;
	$len = strlen($meta);
	$sw = strlen($gp_pix) / 0x100;
	$sh = 0x100;
	for ( $i=0; $i < $len; $i += 0x16 )
	{
		$b1 = ord( $meta[$i+0] );
		$b2 = ord( $meta[$i+1] );
		$sx = ord( $meta[$i+2] );
		$sy = ord( $meta[$i+3] );
		$w  = ord( $meta[$i+4] );
		$h  = ord( $meta[$i+5] );
			$sx = $b2 * 0x80 + $sx;

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix, $sx, $sy, $w, $h, $sw, $sh);
		$pix['src']['pal'] = $gp_clut;
		$pix['bgzero'] = 0;

		sectquad($pix, $meta, $i, $ceil/2);
		trace("%4d , %4d , %4d , %4d", $sx, $sy, $w, $h);
		trace(" , %02x\n", $b1);

		copyquad($pix, 1);
	} // for ( $i=0; $i < $len; $i += 0x16 )

	savpix($dir, $pix, true);
	return;
}

function sectmeta( &$meta, $dir )
{
	trace("== sectmeta( $dir )\n");
	//save_file("$dir/meta", $meta);

	$ed = str2int($meta, 4, 4);
	$st = str2int($meta, 0, 4);
	if ( $st < 8 )
		return;
	for ( $i=$st; $i < $ed; $i += 4 )
	{
		$num = str2int($meta, $i+0, 2);
		$off = str2int($meta, $i+2, 2);

		$pos = $ed + ($off * 0x16);
		$sub = substr ($meta, $pos, $num*0x16);
		trace("meta() %x , %x\n", $pos, $num*0x16);

		$fn = sprintf("$dir/%04d", ($i-$st)/4);
		sectparts($sub, $fn);
	} // for ( $i=$st; $i < $ed; $i += 4 )

	// tileset
		global $gp_pix, $gp_clut;
		$w = strlen($gp_pix) / 0x100;
		$h = 0x100;

		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $gp_clut;
		$clut .= $gp_pix;
		save_file("$dir/pix.clut", $clut);
	return;
}
//////////////////////////////
