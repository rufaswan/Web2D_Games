<?php
require "common.inc";

define("CANV_S", 0x300);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function loadtim( &$file, $base )
{
	printf("=== loadtim( %x )\n", $base);
	$dat = substr($file, $base);
	$tim = psxtim($dat);

	global $gp_pix, $gp_clut;
	$gp_clut[] = $tim['clut'];
	$gp_pix[]  = $tim['pix'];
	return;
}

// callback for copypix()
function ana_alp( $fg, $bg )
{
	return alpha_add( $fg, $bg );
}
//////////////////////////////
function secttalk( &$file, $talk, $dir )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	$num = str2int($file, $talk, 4);
	if ( $num == 0 )
		return;
	printf("= talk base %x = %d\n", $talk, $num);

	$st = $talk + 4;
	for ( $i=0; $i < $num; $i++ )
	{
		printf("= $dir/talk/$i.clut , %x\n", $st);
		$clut = "CLUT";
		$clut .= chrint(16,4); // no clut
		$clut .= chrint(48,4); // width
		$clut .= chrint(48,4); // height
		$clut .= clut2str($file, $st, 0x10);
			$st += 0x20;

		$sz = 0x18 * 0x30;
		while ( $sz > 0 )
		{
			$b = ord( $file[$st] );

			$b1 = $b & 0xf;
			$b2 = $b >> 4;
			$clut .= chr($b1);
			$clut .= chr($b2);

			$sz--;
			$st++;
		}
		save_file("$dir/talk/$i.clut", $clut);
	}
	return;
}

function sectparts( &$meta, $off, $fn, $ids, $m, &$big )
{
	$num = ord( $meta[$off] );
		$off++;
	printf("=== sectparts( %x , $fn , $big ) = $num\n", $off);
	if ( $num == 0 )
		return;

	$data = array();
	while ( $num > 0 )
	{
		$num--;
		$p7 = ord( $meta[$off+7] );
		if ( $p7 & 0x20 )
		{
			if ( $m == 0 && $meta[$off+1] == BYTE && $meta[$off+3] == BYTE )
				$big = "BIG";
			$n = ( $big ) ? 17 : 9;
			$off += $n;
		}
		else
		{
			if ( ! isset( $ids[ $p7 & 0xf ] ) )
				return;
			if ( $m == 0 && $meta[$off+9] == BYTE && $meta[$off+10] == BYTE )
				$big = "BIG";
			$n = ( $big ) ? 11 : 9;
			$s = substr($meta, $off, $n);
			array_unshift($data, $s);
			$off += $n;
		}
	} // while ( $num > 0 )
	if ( empty($data) )
		return;

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

	global $gp_pix, $gp_clut;
	foreach ( $data as $v )
	{
		// 0   1   2  3  4 5 6 7 8 9   10
		// dx1 dy1 sx sy w h c f r dx2 dy2
		if ( $big )
		{
			$dx = sint16( $v[0] . $v[ 9] );
			$dy = sint16( $v[1] . $v[10] );
		}
		else
		{
			$dx = sint8( $v[0] );
			$dy = sint8( $v[1] );
		}
		$pix['dx'] = $dx + (CANV_S / 2);
		$pix['dy'] = $dy + (CANV_S / 2);

		$sx = ord( $v[2] );
		$sy = ord( $v[3] );
		$w  = ord( $v[4] );
		$h  = ord( $v[5] );
		$cid = ord( $v[6] );

		$p7 = ord( $v[7] );
		$tid = ($p7 & 0x0f);
			$tid = $ids[$tid];
		$pix['vflip'] = $p7 & 0x80;
		$pix['hflip'] = $p7 & 0x40;
		$pix['alpha'] = "";
		//if ( $tid == 2 && $cid == 1 )
		if ( $cid == 11 ) // mask + image
			$pix['alpha'] = "ana_alp";

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix[$tid], $sx, $sy, $w, $h, 0x100, 0x100);
		$pix['src']['pal'] = $gp_clut[$tid][$cid];

		$pix['rotate'] = array(ord($v[8]), 0, 0);

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , $cid , %02x , %d\n", $p7, $pix['rotate'][0]);
		copypix($pix);
	} // foreach ( $data as $v )

	savpix($fn, $pix, true);
	return;
}

function sectanim( &$meta, $id, $pos, $flg )
{
	if ( defined("DRY_RUN") && DRY_RUN )
		return;

	$num = ord($meta[$pos]);
		$pos++;
	if ( $num == 0 )
		return "";

	$ret = array();
	for ( $i=0; $i < $num; $i++ )
	{
		$p = $pos + ($i * 2);
		$b1 = ord($meta[$p+0]);
		$b2 = ord($meta[$p+1]);
		$ret[] = "$b1-$b2";
	}
	if ( $flg )
		$ret[] = "flag";

	$buf = "anim_{$id} = ";
	$buf .= implode(' , ', $ret);
	return "$buf\n";
}

function sectmeta( &$meta, $dir, $ids )
{
	printf("=== sectmeta( $dir )\n");
	$off = str2int($meta, 2, 2);

	// sprite parts data
	$cnt = str2int($meta, $off, 2);
	$big = "";

	for ( $m=0; $m < $cnt; $m++ )
	{
		$pos = $off + 2 + ($m * 2);
		$pos = str2int($meta, $pos, 2);
		$fn  = sprintf("$dir/%04d", $m);
		sectparts($meta, $pos, $fn, $ids, $m, $big);
	}

	// sprite animation sequence
	$ed = $off;
	$st = 6;
	$buf = "";
	$m = 0;
	while ( $st < $ed )
	{
		$b1 = str2int($meta, $st, 2);
		$pos = $b1 & 0x7fff;
		$flg = $b1 >> 15;

		$buf .= sectanim($meta, $m, $pos, $flg);
		$st += 2;
		$m++;
	}
	save_file("$dir/anim.txt", $buf);
	return;
}
//////////////////////////////
function objimg( &$file, $dir )
{
	echo "=== objimg( $dir )\n";
	$hsz = str2int($file, 0, 4);

	$ed = $hsz - 4;
	$st = 0;
	while ( $st < $ed )
	{
		$off = str2int($file, $st, 4);
		loadtim( $file, $off );
		$st += 4;
	}

	$meta = substr($file, str2int($file, $hsz-4, 4));

	sectmeta($meta, $dir, array(0,1,2,3,4,5,6,7,8,9));
	//save_file("$dir/meta", $meta);
	return;
}

function infoimg( &$file, $dir )
{
	echo "=== infoimg( $dir )\n";
	// talking portraits
	$base = str2int($file, 0x20, 4);
	secttalk($file, $base, $dir);

	// load unassembled sprite images
	$base = str2int($file, 0x1c, 4);
	$ed = str2int($file, $base, 4);
	$st = 0;
	while ( $st < $ed )
	{
		$off = str2int($file, $base+$st, 4);
		loadtim($file, $base+$off);
		$st += 4;
	}

	// load sprite assembly meta
	$meta = array();
	$base = str2int($file, 0x18, 4);
	$ed = str2int($file, $base, 4) - 4;
	$st = 0;
	while ( $st < $ed )
	{
		$b1 = str2int($file, $base+$st+0, 4);
		$b2 = str2int($file, $base+$st+4, 4);
		$sz = $b2 - $b1;

		$meta[] = substr($file, $base+$b1, $sz);
		$st += 4;
	}

	// get info on matching meta to sprite image
	$ed = str2int($file, 0x18, 4);
	$st = str2int($file, 0x14, 4);
	$file = substr($file, $st, $ed-$st);

	$num = str2int($file, 6, 2);
	$b1 = count($meta);
	if ( $num != $b1 )
		printf("ERROR p14 num %d != meta[] %d\n", $num, $b1);

	for ( $i=0; $i < $num; $i++ )
	{
		$p = 8 + ($i * 4);
		$p = str2int($file, $p, 4) + 2;

		echo "= get tids\n";
		$ids = array();
		while ( $file[$p] != BYTE )
		{
			$b1 = ord( $file[$p] );
			printf("i %d , id[] %d\n", $i, $b1);
			$ids[] = $b1;
			$p++;
		}
		if ( empty($ids) )
		{
			printf("ERROR empty ids for $dir/$i\n");
			continue;
		}

		sectmeta($meta[$i], "$dir/$i", $ids);
		//save_file("$dir/$i/meta", $meta[$i]);
	}
	return;
}
//////////////////////////////
function mana( $fname )
{
	// for ANA/*/*.IMG only
	if ( stripos($fname, ".img") == false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	global $gp_pix, $gp_clut;
	$gp_pix  = array();
	$gp_clut = array();

	// 08-10 for ANA/*_*OBJ/*.IMG
	// 28    for ANA/*INFO*/*.IMG
	if ( ord($file[0]) & 0x20 )
		infoimg($file, $dir);
	else
		objimg($file, $dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
cid 12
	esl_bs00_img
	wal_drm1_img
	end_0001_img
	end_0002_img
	jul_bs32_img
	mhm_gdn2_img
	jgl_e120_img
	rui_e050_img
	lak_bs10_img
	lak_e040_img
	wal_b010_img
	wal_drm0_img
	wal_tmpl_img
	wal_way0_img
cid 13
	mhm_gdn2_img
cid 14
	mhm_gdn2_img

1-1-3  186,213
m#888888   + i#606040 = r#e8e8c8 (in-game , additive blending)
m#ffffff88 + i#606040 = r#b4b4a5 (test as alpha)
 */
