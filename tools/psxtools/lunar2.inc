<?php
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

	printf("src | %4d,%4d  %4d,%4d |\n", 0, 0,                  $pix['src']['w']-1, 0);
	printf("    | %4d,%4d  %4d,%4d |\n", 0, $pix['src']['h']-1, $pix['src']['w']-1, $pix['src']['h']-1);

	printf("des | %4d,%4d  %4d,%4d |\n", $qax, $qay, $qbx, $qby);
	printf("    | %4d,%4d  %4d,%4d |\n", $qdx, $qdy, $qcx, $qcy);
	return;
}

