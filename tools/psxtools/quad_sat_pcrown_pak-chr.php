<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "common-atlas.inc";
require "quad.inc";

define("METAFILE", false);

$gp_json = array();
$gp_pix  = array();
$gp_clut = array();

function sectquad( &$dqd, $dat )
{
	//return;
	// 0 1  2  3   4  5   6  7   8  9   a  b
	// tid  x1 y1  x2 y2  x3 y3  x4 y4  -  sign
	$b = array();
	for ( $i=0; $i < 12; $i++ )
		$b[] = ord( $dat[$i] );
	$dat = $b;

	$qax = ( $dat[11] & 0x01 ) ? -$dat[2] : $dat[2];
	$qay = ( $dat[11] & 0x02 ) ? -$dat[3] : $dat[3];
	$qbx = ( $dat[11] & 0x04 ) ? -$dat[4] : $dat[4];
	$qby = ( $dat[11] & 0x08 ) ? -$dat[5] : $dat[5];
	$qcx = ( $dat[11] & 0x10 ) ? -$dat[6] : $dat[6];
	$qcy = ( $dat[11] & 0x20 ) ? -$dat[7] : $dat[7];
	$qdx = ( $dat[11] & 0x40 ) ? -$dat[8] : $dat[8];
	$qdy = ( $dat[11] & 0x80 ) ? -$dat[9] : $dat[9];

	// 1-2
	//   |
	// 4-3
	$dqd = array(
		$qax , $qay ,
		$qbx , $qby ,
		$qcx , $qcy ,
		$qdx , $qdy ,
	);
	return;
}

function sectpart( &$pak, $pfx, $k2, $id2, $no2 )
{
	global $gp_json, $gp_pix, $gp_clut;

	$data = array();
	for ( $i=0; $i < $no2; $i++ )
	{
		$p = ($id2 + $i) * $pak[1]['k'];
		$dat = substr($pak[1]['d'], $p, 12);
		//echo debug($dat);

		// fedc ba98  7654 3210
		// cctt tttt  tttt tttt
		$b1 = str2big($dat, 0, 2);
		$tid = $b1 & 0x3fff;
		$cid = $b1 >> 14;

		// obaa.pak has both cat + 4 books having the same value
		// probably the palette is referred by opcode
		//if ( $cid != 0 ) // OR $b1 & 0x4000
			//return;
		$pal = substr($gp_clut, $cid*0x40, 0x40);

		$gp_pix[$tid]['pal'] = $pal;
		$w = $gp_pix[$tid]['w'];
		$h = $gp_pix[$tid]['h'];

		$data[$i] = array();
		$data[$i]['TexID'] = 0;
		$data[$i]['_tmp_'] = $tid;

		$data[$i]['DstQuad'] = array();
		sectquad($data[$i]['DstQuad'], $dat);

	} // for ( $i=0; $i < $no; $i++ )

	$gp_json['Frame'][$k2] = $data;
	return;
}
//////////////////////////////
function save_texx( $pfx )
{
	global $gp_json, $gp_pix;

	// atlas map texture
	list($ind, $cw, $ch) = atlasmap($gp_pix);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $cw;
	$pix['rgba']['h'] = $ch;
	$pix['rgba']['pix'] = canvpix($cw,$ch);

	$gray = grayclut(16);
	foreach ( $gp_pix as $img )
	{
		if ( ! isset( $img['pal'] ) || empty( $img['pal'] ) )
			$img['pal'] = $gray;
		$img['pal'][3] = ZERO;

		$pix['src'] = $img;
		$pix['dx'] = $img['x'];
		$pix['dy'] = $img['y'];

		if ( isset($img['cc']) )
			copypix_fast($pix, 1);
		else
			copypix_fast($pix, 4);
	} // foreach ( $files as $img )

	savepix("$pfx.0", $pix);

	// update gp_json[Frame][][SrcQuad]
	foreach ( $gp_json['Frame'] as $fk => $fv )
	{
		foreach ( $fv as $fvk => $fvv )
		{
			$tmp = $fvv['_tmp_'];
			unset( $gp_json['Frame'][$fk][$fvk]['_tmp_'] );

			$img = $gp_pix[ $ind[$tmp] ];
			$x = $img['x'];
			$y = $img['y'];
			$w = $img['w'] - 1;
			$h = $img['h'] - 1;
			$gp_json['Frame'][$fk][$fvk]['SrcQuad'] = array(
				$x      , $y ,
				$x + $w , $y ,
				$x + $w , $y + $h ,
				$x      , $y + $h ,
			);
		} // foreach ( $fv as $fvk => $fvv )
	} // foreach ( $gp_json['Frame'] as $fk => $fv )
	return;
}

function load_texx( &$pak, $pfx )
{
	$chr = load_file("$pfx.chr");
	if ( empty($chr) )  return;

	global $gp_pix;
	$gp_pix = array();

	$pos = 0;
	$len = strlen($pak['d']);
	for ( $i=0; $i < $len; $i += $pak['k'] )
	{
		$d = $i / $pak['k'];
		// 0  1 2 3  4  5  6  7
		// -  chr    w  h  id
		$id = str2big($pak['d'], $i+6, 2);
		$w  = str2big($pak['d'], $i+4, 1);
		$h  = str2big($pak['d'], $i+5, 1);
		$siz = ($w/2 * $h);
		printf("%4x , %6x , %3d x %3d = %4x\n", $d, $pos, $w, $h, $siz);

		$b1 = substr($chr, $pos, $siz);
		$pix = "";
		for ( $s=0; $s < $siz; $s++ )
		{
			$b2 = ord( $b1[$s] );
			$b3 = ($b2 >> 4) & BIT4;
			$b4 = ($b2 >> 0) & BIT4;
			$pix .= chr($b3) . chr($b4);
		}
		$gp_pix[$d] = array(
			'pix' => $pix,
			'cc' => 16,
			'w'  => $w,
			'h'  => $h,
			'id' => $d,
		);

		// aligned to 8x8 tile
		$pos = int_ceil($pos + $siz, 0x20);
	} // for ( $i=0; $i < $no; $i++ )

	return;
}
//////////////////////////////
function sectspr( &$pak, $pfx )
{
	$len = strlen($pak[2]['d']);
	for ( $i2=0; $i2 < $len; $i2 += $pak[2]['k'] )
	{
		// distort set def
		// 0 1 2 3 4 5 6 7  8 9  a b
		// - - - - - - - -  id   no
		$id2 = str2big($pak[2]['d'], $i2+ 8, 2);
		$no2 = str2big($pak[2]['d'], $i2+10, 2);
		$k2  = $i2 / $pak[2]['k'];

		sectpart($pak, $pfx, $k2, $id2, $no2);
	}
	return;
}

function sectanim( &$pak, $pfx )
{
	global $gp_json;

	$len = strlen($pak[4]['d']);
	for ( $i4=0; $i4 < $len; $i4 += $pak[4]['k'] )
	{
		$k4 = $i4 / $pak[4]['k'];

		$b0 = str2big($pak[4]['d'], $i4+0, 4);
		$st = $b0 - $pak[3]['o'];

		$ent = array(
			'FID' => array(),
			'FPS' => array(),
		);
		$name = sprintf("anim_%d", $k4);
		while (1)
		{
			$bak = $st;
				$st += 8;

			// 0 1  2  3  4 5  6  7
			// sid  -  -  ms   -  rep
			//$b2 = str2big($pak[3]['d'], $bak+2, 2, true);
			//if ( $b2 == -1 )
				//continue;

			$b7 = str2big($pak[3]['d'], $bak+7, 1);
			if ( $b7 === 1 || $b7 === 2 )
				break;
			if ( $b7 !== 0 )
				continue;

			$b0 = str2big($pak[3]['d'], $bak+0, 2, true);
			$b4 = str2big($pak[3]['d'], $bak+4, 2, true);
			#$b6 = str2big($pak[3]['d'], $bak+6, 1);

			$ent['FID'][] = $b0 & 0x0fff;
			$ent['FPS'][] = $b4;
		} // while (1)

		$gp_json['Animation'][$name][0] = $ent;
	} // for ( $i4=0; $i4 < $len; $i4 += $pak[4]['k'] )

	return;
}
//////////////////////////////
function pakchr( &$pak, $pfx )
{
	echo "== pakchr( $pfx )\n";

	//     0 1 |         1-0 2-1
	// 2 3 4   | 3-2 4-3 6-4
	//       5 |             s-5
	// 6       | 5-6
	// grad.pak
	//       -     -    40   958 |     -     - 123*8 2544*c
	//   1c888 1ec10 22a30     - | 2f6*c 7c4*8  b9*c      -
	//       -     -     - 234bc |     -     -     -  2f6*4
	//   232dc                   |  3c*8
	// s4[+ 0] - 1ec10   => s3
	// s3[+ 0] =   2f5   => s2
	// s2[+ 8] =  2541+3 => s1
	// s1[+ 0] =   122   => s0
	// s0[]
	//
	// s4-s3-s2-s1-s0
	$sect = array(
		array('p' => 0x08 , 'k' =>  8), // 0
		array('p' => 0x0c , 'k' => 12), // 1
		array('p' => 0x10 , 'k' => 12), // 2
		array('p' => 0x14 , 'k' =>  8), // 3
		array('p' => 0x18 , 'k' => 12), // 4
		array('p' => 0x2c , 'k' =>  4), // 5
		array('p' => 0x30 , 'k' =>  8), // 6
	);
	file2sect($pak, $sect, $pfx, array('str2big', 4), 0, METAFILE);

	global $gp_json;
	$gp_json = load_idtagfile('sat_pcrown');

	load_texx($pak[0], $pfx);

	sectanim($pak, $pfx);
	sectspr ($pak, $pfx);

	save_texx($pfx);
	save_quadfile($pfx, $gp_json);
	return;
}
//////////////////////////////
function pcrown( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$pak = load_file("$pfx.pak");
	if ( empty($pak) )  return;

	if ( substr($pak,0,4) != "unkn" )
		return;

	global $gp_clut, $gp_pix;
	$gp_pix = array();
	$pal = load_file("$pfx.pal");
	if ( ! empty($pal) )
	{
		echo "added CLUT @ $pfx.pal\n";
		$gp_clut = $pal;
	}

	pakchr($pak, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
book select
	VORE  item.pak
	VORE  comm.pak
	VORE  arel.pak
	VORE  slct.pak
	VORE  chap.pak
	VORE  obaa.pak
*/
