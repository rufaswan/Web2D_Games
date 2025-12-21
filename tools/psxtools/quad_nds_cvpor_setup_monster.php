<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 *
 * Special Thanks
 *   DSVania Editor
 *   https://github.com/LagoLunatic/DSVEdit/blob/master/docs/formats/Skeleton%20File%20Format.txt
 *     LagoLunatic
 */
require 'common.inc';
require 'common-quad.inc';
require 'common-json.inc';
require 'class-atlas.inc';
require 'quad.inc';
require 'nds_cvpor.inc';

function sect_quad_jnt( &$por, &$quad )
{
	$jnt = cvooe_jntfile($por['jnt'], 0);
	if ( $jnt === -1 )
		return;

	$cnt_key = count($quad['keyframe']);
	$time = array();
	foreach ( $jnt['pose'] as $pk => $pv )
	{
		$key_layer = array();
		$inherit = array();
		$is_done = false;
		while ( ! $is_done )
		{
			$is_done = true;
			foreach ( $pv['body'] as $jk => $jv )
			{
				$joint = $jnt['joint'][$jk];
				$par = $joint['parent'];
				// has parent but data not available yet, try again on next loop
				if ( $par >= 0 && ! isset($inherit[$par]) )
				{
					$is_done = false;
					continue;
				}
				// already done
				if ( isset($inherit[$jk]) )
					continue;

				$protate = ( $par < 0 ) ? 0 : $inherit[$par][0];
				$protate += ($joint['flag'] & 3) * (pi() * 0.5);
				list($dx,$dy) = qdxdy_dist($protate, $jv['dist']);
				if ( $par < 0 )
				{
					$dx += $jnt['head']['cx'];
					$dy += $jnt['head']['cy'];
				}
				else
				{
					$dx += $inherit[$par][1];
					$dy += $inherit[$par][2];
				}

				$radian = ( $jv['rotate'] / 0x8000) * pi();
				if ( $joint['flag'] & 4 )
					$radian += $inherit[$par][0];

				$cur_inherit = array($radian, $dx, $dy);
				$inherit[$jk] = $cur_inherit;

				// if visible keyframe
				$cur_key = 0;
				$kid = $jv['key'];
				if ( $kid < 0 )
					$kid = $joint['key'];
				if ( $kid < 0 )
					goto end_layer;

				$zoomx = ( $joint['flag'] & 0x08 ) ? -1 : 1; // flipx
				$zoomy = ( $joint['flag'] & 0x10 ) ? -1 : 1; // flipy
				$cur_matrix = matrix_scale(3, $zoomx, $zoomy);

				$t = qmat3_dxdy($cur_inherit[0], $cur_inherit[1], $cur_inherit[2]);
				$cur_matrix = matrix_multi33($cur_matrix, $t);

				$cur_key = $quad['keyframe'][$kid]['layer'][0];
				qmat3_mult($cur_matrix, $cur_key['dstquad']);

end_layer:
				list_add($key_layer, $jk, $cur_key);
			} // foreach ( $pv['body'] as $jk => $jv )
		} // while ( ! $is_done )
		//print_r($inherit);

		$kid = $cnt_key + $pk;
		$kent = array(
			'name'  => 'jnt pose ' . $pk,
			'layer' => $key_layer,
			'order' => $jnt['draw'],
		);
		list_add($quad['keyframe'], $kid, $kent);

		$tent = array(
			'time'           => 10,
			'dstquad_mix_id' => 1,
			'attach'         => quad_attach('keyframe', $kid),
		);
		$time[] = $tent;
	} // foreach ( $jnt['pose'] as $pk => $pv )

	$ent = array(
		'name'     => 'ALL KEYPOSES',
		'timeline' => $time,
		'loop_id'  => 0,
	);
	$quad['animation'][] = $ent;

	foreach ( $jnt['anim'] as $ak => $av )
	{
		$time = array();
		foreach ( $av as $tk => $tv )
		{
			$kid = $cnt_key + $tv['pose'];
			$tent = array(
				'time'           => $tv['time'],
				'dstquad_mix_id' => 1,
				'attach'         => quad_attach('keyframe', $kid),
			);
			$time[] = $tent;
		} // foreach ( $av as $tk => $tv )

		$ent = array(
			'name'     => 'jnt anim ' . $ak,
			'timeline' => $time,
			'loop_id'  => 0,
		);
		$quad['animation'][] = $ent;
	} // foreach ( $jnt['anim'] as $ak => $av )
	return;
}
//////////////////////////////
function sect_quad_so( &$por, &$atlas, &$quad )
{
	$so04 = str2int($por['so'], 0x04, 3); // src
	$so08 = str2int($por['so'], 0x08, 3);
	$so0c = str2int($por['so'], 0x0c, 3); // keys
	$so10 = str2int($por['so'], 0x10, 3); // =0 , anim timeline
	$so14 = str2int($por['so'], 0x14, 3); // =0 , anim
	$so20 = str2int($por['so'], 0x20, 3);

	$quad['keyframe'] = array();
	$end = ( $so10 === 0 ) ? $so20 : $so10;
	$kid = -1;
	for ( $i = $so0c; $i < $end; $i += 0xc )
	{
		// 0 1  2   3   4 5 6 7  8 9 a b
		// - -  hc  sc  hoff---  soff---
		$s = substr($por['so'], $i, 0xc);
			$kid++;
		$hcnt = ord( $s[2] );
		$scnt = ord( $s[3] );
		$hoff = str2int($s, 4, 4);
		$soff = str2int($s, 8, 4);
			$hoff >>= 3;
			$soff >>= 4;

		if ( $scnt === 0 )
			continue;

		$klayer = array();
		for ( $j=0; $j < $scnt; $j++ )
		{
			$src = &$por['src'][$soff + $j];
			$src['key'] = $kid;

			list($x,$y,$w,$h) = $atlas->getxywh( $src['atlas'] );
			$src = xywh_quad($w, $h);
			xywh_move($src, $x, $y);

			$dst = xywh_quad($w, $h, $src['bit'] & 2, $src['bit'] & 1);
			xywh_move($dst, $src['dx'], $src['dy']);

			$ent = array(
				'debug'    => sprintf('0x%02x', $src['bit']),
				'dstquad'  => $dst,
				'srcquad'  => $src,
				'tex_id'   => 0,
				'blend_id' => 0,
				'_xywh'    => array($x,$y,$w,$h),
			);
			$klayer[] = $ent;
		} // for ( $j=0; $j < $scnt; $j++ )

		$kent = array(
			'name'  => "keyframe $kid",
			'layer' => $klayer,
		);
		list_add($quad['keyframe'], $kid, $kent);
	} // for ( $i = $b0c; $i < $end; $i += 0xc )

	// add any leftover src as keyframe
	// for jnt file later
	foreach ( $por['src'] as $sk => $sv )
	{
		if ( $sv['key'] !== -1 )
			continue;
		$kid++;
		$por['src'][$sk]['key'] = $kid;

		list($x,$y,$w,$h) = $atlas->getxywh( $sv['atlas'] );
		$src = xywh_quad($w, $h);
		xywh_move($src, $x, $y);

		$dst = xywh_quad($w, $h, $sv['bit'] & 2, $sv['bit'] & 1);
		xywh_move($dst, $sv['dx'], $sv['dy']);

		$kent = array(
			'name'  => "jnt keyframe $kid",
			'layer' => array(
				array(
					'debug'    => sprintf('0x%02x', $sv['bit']),
					'dstquad'  => $dst,
					'srcquad'  => $src,
					'tex_id'   => 0,
					'blend_id' => 0,
				),
			),
		);
		list_add($quad['keyframe'], $kid, $kent);
	} // foreach ( $por['src'] as $sk => $sv )

	$quad['animation'] = array();
	if ( $so14 === 0 )
		return;
	$aid = -1;
	for ( $i = $so14; $i < $so20; $i += 8 )
	{
		// 0 1 2 3  4 5 6 7
		// num----  off----
		$s = substr($por['so'], $i, 8);
			$aid++;
		$num = str2int($s, 0, 3);
		$off = str2int($s, 4, 3);

		$pos = $so10 + $off;
		$time= array();
		for ( $j=0; $j < $num; $j++ )
		{
			// 0 1  2 3  4 5 6 7
			// kid  fps  - - - -
			$s = substr($por['so'], $pos, 8);
				$pos += 8;
			$key = str2int($s, 0, 2);
			$fps = str2int($s, 2, 2);

			$ent = array(
				'time'   => $fps,
				'attach' => quad_attach('keyframe', $key),
			);
			$time[] = $ent;
		} // for ( $j=0; $j < $num; $j++ )

		$aent = array(
			'name'     => "animation $aid",
			'timeline' => $time,
			'loop_id'  => 0,
		);
		list_add($quad['animation'], $aid, $aent);
	} // for ( $i = $so14; $i < $so20; $i +=  )
	return;
}

function sect_soscpal( &$por, &$atlas )
{
	//   0 1 2 |     1-0 2-1 3-2
	// 3 4     | 4-3 5-4
	// 5       | s-5
	// p_imo.dat
	//          40 3c70 3e20 |      3c3*10 36*8 7f*c
	//   4414 48e4           | 9a*8  22*8
	//   49f4                |  2*c
	// s3[+ 0] = 7e => s2
	// s2
	$so04 = str2int($por['so'], 0x04, 3); // src
	$so08 = str2int($por['so'], 0x08, 3);
	$so0c = str2int($por['so'], 0x0c, 3); // keys
	$so10 = str2int($por['so'], 0x10, 3); // =0 , anim timeline
	$so14 = str2int($por['so'], 0x14, 3); // =0 , anim
	$so20 = str2int($por['so'], 0x20, 3);

	$por['src'] = array();
	for ( $i = $so04; $i < $so08; $i += 0x10 )
	{
		// 0 1  2 3  4 5  6 7  8 9  a b  c  d  e  f
		// dx-  dy-  sx-  sy-  w--  h--  t  f  c  -
		$s = substr($por['so'], $i, 0x10);
		$dx = str2int($s,  0, 2, true);
		$dy = str2int($s,  2, 2, true);
		$sx = str2int($s,  4, 2);
		$sy = str2int($s,  6, 2);
		$w  = str2int($s,  8, 2);
		$h  = str2int($s, 10, 2);

		$tid = ord( $s[12] );
		$flg = ord( $s[13] );
		$cid = ord( $s[14] );

		$pal = substr($por['pal'], $cid * 0x40, 0x40);
			$pal[3] = ZERO;

		$sc  = $por['sc'][$tid];
		$pix = rippix8($sc['pix'], $sx, $sy, $w, $h, $sc['w'], $sc['h']);
		$aid = $atlas->putclut($w, $h, $pal, $pix);

		$ent = array(
			'dx'    => $dx,
			'dy'    => $dy,
			'atlas' => $aid,
			'bit'   => $flg,
			'key'   => -1,
		);
		$por['src'][] = $ent;
	} // for ( $i = $so04; $i < $so08; $i += 0x10 )
	return;
}
//////////////////////////////
function por_files( $dir )
{
	$ret = array(
		'so'  => '',
		'sc'  => array(),
		'pal' => '',
		'jnt' => '',
		'tag' => '',
	);
	foreach ( scandir($dir) as $fn )
	{
		if ( $fn[0] === '.' )
			continue;
		$ex = explode('.', $fn);

		$id = (int)$ex[1];
		$dt = file_get_contents("$dir/$fn");
		switch ( $ex[0] )
		{
			case 'sc':
				$len = strlen($dt);
				switch ( $len )
				{
					case 0x2000: // 80x80  4-bit
						bpp4to8($dt);
					case 0x4000: // 80x80  8-bit
						$ret['sc'][$id] = array(
							'w' => 0x80,
							'h' => 0x80,
							'pix' => $dt,
						);
						break;
					case 0x8000: // 100x100  4-bit
						bpp4to8($dt);
						$ret['sc'][$id] = array(
							'w' => 0x100,
							'h' => 0x100,
							'pix' => $dt,
						);
						break;
					default:
						return php_error('%s  sc len = %x', $fn, $len);
				} // switch ( $len )
				break;

			case 'so':
				if ( $id !== 0 )
					return php_error('so id != 0 [%x]', $id);
				$ret['so']  = $dt;
				break;

			case 'pal':
				if ( $id !== 0 )
					return php_error('pal id != 0 [%x]', $id);
				$ret['pal'] = pal555($dt);
				break;

			case 'jnt':
				if ( $id !== 0 )
					return php_error('jnt id != 0 [%x]', $id);
				$ret['jnt'] = $dt;
				break;

			case 'game':
				$ret['tag'] = trim($dt);
				break;
		} // switch ( $ex[0] )
	} // foreach ( scandir($dir) as $fn )

	if ( empty($ret['so']) || empty($ret['sc']) || empty($ret['pal']) )
		return -1;
	return $ret;
}
//////////////////////////////
function cvpor( $dir )
{
	$dir = rtrim($dir, '/\\');
	if ( ! is_dir($dir) )
		return;

	$por = por_files($dir);
	if ( $por === -1 )
		return php_warning('%s not so-sc-pal set', $dir);

	echo "== $dir\n";
	$atlas = new atlas_tex;
	$atlas->init();
	sect_soscpal($por, $atlas);

	$atlas->sort();
	$atlas->save("$dir.0");

	$quad = load_idtagfile($por['tag']);
	$quad['blend'] = array( blend_modes('normal') );
	sect_quad_so($por, $atlas, $quad);

	$quad['mix'] = array(0,'LINEAR');

	if ( ! empty($por['jnt']) )
		sect_quad_jnt($por, $quad);

	save_quadfile($dir, $quad);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvpor( $argv[$i] );
/*
cvdos /so/*  b10=0  b14=0
	p_alast00.dat
	p_bridge.dat
	p_car.dat
	p_crowd0.dat
	p_endch0.dat
	p_endtx0.dat
	p_ent00.dat
	p_fens.dat
	p_final00.dat
	p_giant_00.dat
	p_gorem00.dat
	p_gover.dat
	p_great00.dat
	p_hewp00.dat
	p_iron00.dat
	p_karit.dat
	p_konami.dat
	p_kumo00.dat
	p_maddamon_00.dat
	p_mzghost_00.dat
	p_needle_00.dat
	p_niku0.dat
	p_over.dat
	p_piano0.dat
	p_poison_00.dat
	p_roll.dat
	p_ruwall.dat
	p_tiobj.dat
	p_title.dat
cvpor /so/*  b10=0  b14=0
	p_alast00.dat
	p_ara.dat
	p_art00.dat
	p_art01.dat
	p_art03.dat
	p_artu01.dat
	p_bwall1.dat
	p_bwall2.dat
	p_bwall3.dat
	p_bwall4.dat
	p_bwall5.dat
	p_bwall6.dat
	p_bwall7.dat
	p_bwall8.dat
	p_cpsele0.dat
	p_dark.dat
	p_drabg0.dat
	p_edo00.dat
	p_edr00.dat
	p_edr01.dat
	p_edr20.dat
	p_endch0.dat
	p_endtx0.dat
	p_ent00.dat
	p_fcent0.dat
	p_fcmenu0.dat
	p_fens.dat
	p_final00.dat
	p_frame0.dat
	p_frame1.dat
	p_gear0.dat
	p_gorem00.dat
	p_govera.dat
	p_gover.dat
	p_great00.dat
	p_gsele0.dat
	p_iron00.dat
	p_irong0.dat
	p_konami.dat
	p_laask0.dat
	p_lbbsk0.dat
	p_lbcsk0.dat
	p_legidr.dat
	p_lgear0.dat
	p_maddamon_00.dat
	p_mark.dat
	p_mg06.dat
	p_mg0b.dat
	p_mg10.dat
	p_moonob.dat
	p_mzghost_00.dat
	p_name0.dat
	p_option0.dat
	p_optitle_j.dat
	p_paobj0.dat
	p_pblad0.dat
	p_pclad0.dat
	p_pillr0.dat
	p_prologue.dat
	p_ptuki0.dat
	p_ptuki1.dat
	p_rayzer.dat
	p_retry0.dat
	p_rocket.dat
	p_sand0.dat
	p_sp01.dat
	p_sp03.dat
	p_tbhane.dat
	p_tenjo.dat
	p_tobj.dat
	p_tokei.dat
	p_ub14.dat
	p_wfmenu0.dat
	p_wfsel0.dat
	p_who0.dat
cvooe /so/*  b10=0  b14=0
	p_area02.dat
	p_eabblk.dat
	p_enr00.dat
	p_enr00_p.dat
	p_fens.dat
	p_fsmi.dat
	p_fwindow.dat
	p_gam00.dat
	p_hasira.dat
	p_kafog0.dat
	p_kcask0.dat
	p_konami.dat
	p_mark.dat
	p_needle_00.dat
	p_prolo0.dat
	p_ram0b.dat
	p_raw02.dat
	p_raw04.dat
	p_raw0d.dat
	p_raw0e.dat
	p_spike00.dat
	p_ub14.dat
*/
