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
 */
require 'common.inc';
require 'common-guest.inc';

function save_layers( &$map, &$set, $pfx )
{
	printf("== save_layers( %s )\n", $pfx);
	$id  = 0;
	$pos = 0;
	while ( isset( $map['tile'][2][$pos] ) )
	{
		$pix = COPYPIX_DEF($map['room'][0]*0x10, $map['room'][1]*0x10);
		$pix['src']['w'] = 16;
		$pix['src']['h'] = 16;

		for ( $y=0; $y < $map['room'][1]; $y++ )
		{
			for ( $x=0; $x < $map['room'][0]; $x++ )
			{
				$b1 = ord( $map['tile'][2][$pos+0] );
				$b2 = ord( $map['tile'][2][$pos+1] );
				$tile_id = $b1 | ($b2 << 8);
					$pos += 2;

				$sx = $tile_id & BIT4;
				$sy = $tile_id >> 4;

				$pix['src']['pix'] = riprgba($set['pix'], $sx*16, $sy*16, 16, 16, $set['w'], $set['h']);
				$pix['dx'] = $x << 4;
				$pix['dy'] = $y << 4;
				copypix_fast($pix, 4);
			} // for ( $x=0; $x < $map['room'][0]; $x++ )
		} // for ( $y=0; $y < $map['room'][1]; $y++ )

		$fn = sprintf('%s/l_%04d', $pfx, $id);
			$id++;
		savepix($fn, $pix, false, false);
	} // while ( isset( $map['tile'][2][$pos] ) )

	return;
}

function save_layout( &$map, $pfx )
{
	printf("== save_layout( %s )\n", $pfx);
	$mw = str2int($map['map'][0][0], 0, 2);
	$mh = str2int($map['map'][0][0], 6, 2);

	$a = array();
	$txt = '';
	foreach ( $map['map'][0] as $mk => $mv )
	{
		$l = array();
		$pos = 8;
		for ( $y=0; $y < $mh; $y++ )
		{
			for ( $x=0; $x < $mw; $x++ )
			{
				$id = str2int($mv, $pos, 2);
					$pos += 2;

				$ln = sprintf('%s/l_%04d.rgba', $pfx, $id);
				if ( is_file($ln) )
				{
					$dx = $x * ($map['room'][0] * 0x10);
					$dy = $y * ($map['room'][1] * 0x10);
					$l[] = sprintf('l_%04d+%d+%d', $id, $dx, $dy);
				}
			} // for ( $x=0; $x < $mw; $x++ )
		} // for ( $y=0; $y < $mh; $y++ )

		$an = sprintf('a_%04d', $mk);
		if ( ! empty($l) )
		{
			$txt .= sprintf("%s = %s\n", $an, implode(' , ', $l));
			$a[] = sprintf('%s+0+0', $an);
		}
	} // foreach ( $map['map'][0] as $mk => $mv )

	$txt .= sprintf("main = %s\n", implode(' , ', $a));
	save_file("$pfx/layout.txt", $txt);
	return;
}
//////////////////////////////
function load_fntdata( $pfx )
{
	$fnt = load_file("$pfx.fnt");
	if ( empty($fnt) )
		return '';
	$cnt = ord( $fnt[0x80] );
	printf("== load_fntdata( %s ) = %x\n", $pfx, $cnt);

	$off = 0x84 + ($cnt * 0x40);
	$pix = array();
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = 0x84 + ($i * 0x20);
		$p1 = str2int($fnt, $pos+ 0, 3);
		$sz = str2int($fnt, $pos+ 4, 3);
		$p2 = str2int($fnt, $pos+ 8, 3);
		$w  = str2int($fnt, $pos+16, 2);
		$h  = str2int($fnt, $pos+18, 2);

		$fn = substr0($fnt, 0x80+$p1);
		printf("%x , %3x x %3x , %s\n", $i, $w, $h, $fn);

		$img = array(
			'w' => $w,
			'h' => $h,
			'pix' => '',
		);

		$sub = substr($fnt, $off+$p2, $sz);
		$y = $h;
		while ( $y > 0 )
		{
			$y--;
			$syy = ($y * $w);
			for ( $x=0; $x < $w; $x++ )
			{
				$sxx = ($syy + $x) * 4;

				// in BGRA
				$img['pix'] .= $sub[$sxx+2];
				$img['pix'] .= $sub[$sxx+1];
				$img['pix'] .= $sub[$sxx+0];
				$img['pix'] .= $sub[$sxx+3];
			} // for ( $x=0; $x < $w; $x++ )
		} // while ( $y > 0 )

		save_clutfile("$pfx/meta/fnt.$fn.rgba", $img);
		$pix[] = $img;
	} // for ( $i=0; $i < $cnt; $i++ )
	return $pix;
}

function fnt8pix( &$fnt, &$map, $cnt )
{
	$max = int_ceil($cnt, 0x10);

	$col = 0x10;
	$row = $max >> 4;
	$tw = $fnt['w'] >> 3;
	$th = $fnt['h'] >> 3;

	$adj = array(
		array(0,0), array(8,0),
		array(0,8), array(8,8),
	);

	$pix = COPYPIX_DEF($col*0x10, $row*0x10);
	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$id = 0;
	for ( $y=0; $y < $row; $y++ )
	{
		for ( $x=0; $x < $col; $x++ )
		{
			if ( $id >= $cnt )
				continue;
			$sub = substr($map, $id << 4, 0x10);
				$id++;

			for ( $p=0; $p < 4; $p++ )
			{
				$tile = str2int($sub, $p << 2, 4);

				// max col = 400/8 = 80
				// max row = 400/8 = 80
				// fedcba98 76543210 fedcba98 76543210
				// -------- -------- vrrrrrrr hccccccc
				$sx = ($tile >> 0) & 0x7f;
				$sy = ($tile >> 8) & 0x7f;
				$pix['hflip'] = $tile & 0x80;
				$pix['vflip'] = $tile & 0x8000;
				if ( $sx >= $tw )  continue;
				if ( $sy >= $th )  continue;

				$pix['src']['pix'] = riprgba($fnt['pix'], $sx*8, $sy*8, 8, 8, $fnt['w'], $fnt['h']);
				$pix['dx'] = ($x << 4) + $adj[$p][0];
				$pix['dy'] = ($y << 4) + $adj[$p][1];
				copypix_fast($pix, 4);
			} // for ( $p=0; $p < 4; $p++ )
		} // for ( $x=0; $x < $col; $x++ )
	} // for ( $y=0; $y < $row; $y++ )

	return $pix['rgba'];
}

function fnt16pix( &$fnt, &$map, $cnt )
{
	$max = int_ceil($cnt, 0x10);

	$col = 0x10;
	$row = $max >> 4;

	$pix = COPYPIX_DEF($col*0x10, $row*0x10);
	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	$id = 0;
	for ( $y=0; $y < $row; $y++ )
	{
		for ( $x=0; $x < $col; $x++ )
		{
			if ( $id >= $cnt )
				continue;
			$tile = str2int($map, $id << 2, 4);
				$id++;

			// max col = 400/10 = 40
			// max row =  80/10 =  8
			// fedcba98 76543210 fedcba98 76543210
			// -------- -------- -----rrr hvcccccc
			$sx = ($tile >> 0) & 0x3f;
			$sy = ($tile >> 8) & 7;
			$pix['hflip'] = $tile & 0x80;
			$pix['vflip'] = $tile & 0x40;

			$pix['src']['pix'] = riprgba($fnt['pix'], $sx*0x10, $sy*0x10, 0x10, 0x10, $fnt['w'], $fnt['h']);
			$pix['dx'] = $x << 4;
			$pix['dy'] = $y << 4;
			copypix_fast($pix, 4);
		} // for ( $x=0; $x < $col; $x++ )
	} // for ( $y=0; $y < $row; $y++ )

	return $pix['rgba'];
}

function load_tileset( $pfx, &$map )
{
	$set = load_clutfile("$pfx.tileset");
	if ( ! empty($set) )
		return $set;

	// generate a new tile.set
	$fnt = load_fntdata($pfx);
	if ( empty($fnt) )
		return '';

	$len1 = strlen( $map['tile'][0] );
	$len2 = strlen( $map['tile'][1] );
	if ( ($len1 >> 4) === ($len2 >> 2) )
	{
		// bmz gv1 gv2 laix
		// 1 tile = 16x16 pixel = 4 * 8x8 pixel
		//        = 4 * 4 byte
		// 1 meta = 4 byte
		printf("DETECT %s.fnt = gv1 8x8 tile\n", $pfx);
		if ( count($fnt) > 1 )
		{
			foreach ( $fnt as $fk => $fv )
			{
				$pix = fnt8pix($fv, $map['tile'][0], $len1 >> 4);
				save_clutfile("$pfx/meta/set.$fk.rgba", $pix);
			} // foreach ( $fnt as $fk => $fv )
			return '';
		}
		else
		{
			$pix = fnt8pix($fnt[0], $map['tile'][0], $len1 >> 4);
			save_clutfile("$pfx.tileset", $pix);
			return $pix;
		}
	}

	if ( ($len1 >> 2) === ($len2 >> 2) )
	{
		// gv2
		// 1 tile = 16x16 pixel
		//        = 4 byte
		// 1 meta = 4 byte
		printf("DETECT %s.fnt = gv1 16x16 tile\n", $pfx);
		$pix = fnt16pix($fnt[0], $map['tile'][0], $len1 >> 2);
		save_clutfile("$pfx.tileset", $pix);
		return $pix;
	}

	if ( ($len1 >> 2) === ($len2 >> 1) )
	{
		// mgv
		// 1 tile = 16x16 pixel
		//        = 4 byte
		// 1 meta = 2 byte
		printf("DETECT %s.fnt = mgv 16x16 tile\n", $pfx);
		$pix = fnt16pix($fnt[0], $map['tile'][0], $len1 >> 2);
		save_clutfile("$pfx.tileset", $pix);
		return $pix;
	}

	// no match
	return '';
}
//////////////////////////////
function info_setmapkey( $name, &$data, $id, $key )
{
	if ( $id === 0 )
		return;

	while( ! isset($data[$id]) )
		$data .= '----------------';

	if ( $data[$id] !== '-' && $data[$id] !== $key )
		printf("%s[%x] = %s , set error %s\n", $name, $id, $data[$id], $key);

	$data[$id] = $key;
	return;
}

function info_maplayer( &$map )
{
	if ( defined('NO_TRACE') )
		return;
	$len = strlen( $map['map'][0][0] );
	$room_sz = $map['room'][0] * $map['room'][1] * 2;

	$tile = '';
	$room = '';
	foreach ( $map['map'][0] as $mk => $mv )
	{
		$data = array();
		$pos = 8;
		while ( $pos < $len )
		{
			$b1 = ord( $mv[$pos+0] );
			$b2 = ord( $mv[$pos+1] );
			$room_id = $b1 | ($b2 << 8);
				$pos += 2;
			info_setmapkey('ROOM', $room, $room_id, "$mk");

			if ( isset($data[$room_id]) )
				continue;
			$s = substr($map['tile'][2], $room_id*$room_sz, $room_sz);

			if ( strlen($s) !== $room_sz )
				return php_error('ROOM[%x] = %x [expect %x]', $room_id, strlen($s), $room_sz);
			$data[$room_id] = $s;
		} // while ( $pos < $len )

		$data = implode('', $data);
		$pos = strlen($data);
		while ( $pos > 0 )
		{
			$pos -= 2;
			$b1 = ord( $data[$pos+0] );
			$b2 = ord( $data[$pos+1] );
			$tile_id = $b1 | ($b2 << 8);
			info_setmapkey('TILE', $tile, $tile_id, "$mk");
		} // while ( $pos > 0 )
	} // foreach ( $map['map'][0] as $mk => $mv )

	echo "ROOM/layer\n";
	$len = strlen($room);
	for ( $i=0; $i < $len; $i += 0x10 )
	{
		$s = substr($room, $i, 0x10);
		printf("  %4x  %s\n", $i, $s);
	}

	echo "TILE/layer\n";
	$len = strlen($tile);
	for ( $i=0; $i < $len; $i += 0x10 )
	{
		$s = substr($tile, $i, 0x10);
		printf("  %4x  %s\n", $i, $s);
	}
	return;
}

function mapdata_loop( &$file, $st, $ed, $sz )
{
	$map = array();
	while ( $st < $ed )
	{
		$map[] = substr($file, $st, $sz);
		$st += $sz;
	}
	return $map;
}

function load_mapdata( $pfx )
{
	$bin = load_file("$pfx.bin");
	if ( empty($bin) )
		return '';
	printf("== load_mapdata( %s )\n", $pfx);

	$data = array();

	$mgc = $bin[0] . $bin[4] . $bin[0x18] . $bin[0x1c];
	if ( $mgc === "\x04\x18\x01\x10" )
	{
		printf("DETECT %s.bin = mgv 32-bit\n", $pfx);
		$data['room'] = array(16,15);
		$p1 = str2int($bin,  4, 3);
		$p2 = str2int($bin,  8, 3);
		$p3 = str2int($bin, 12, 3);

		$s1 = substr($bin, $p1, $p2-$p1);
		$s2 = substr($bin, $p2, $p3-$p2);

		// tile data
		$p1 = str2int($s1,  4, 3);
		$p2 = str2int($s1,  8, 3);
		$p3 = str2int($s1, 12, 3);
		$data['tile'][0] = substr($s1, $p1, $p2-$p1);
		$data['tile'][1] = substr($s1, $p2, $p3-$p2);
		$data['tile'][2] = substr($s1, $p3);
	}

	$mgc = $bin[0] . $bin[4] . $bin[0x18];
	if ( $mgc === "\x04\x18\x0c" )
	{
		printf("DETECT %s.bin = gv1 32-bit\n", $pfx);
		$data['room'] = array(25,15);
		$p1 = str2int($bin,  4, 3);
		$p2 = str2int($bin,  8, 3);
		$p3 = str2int($bin, 12, 3);

		$s1 = substr($bin, $p1, $p2-$p1);
		$s2 = substr($bin, $p2, $p3-$p2);

		// tile data
		$p1 = str2int($s1, 0, 3);
		$p2 = str2int($s1, 4, 3);
		$p3 = str2int($s1, 8, 3);
		$data['tile'][0] = substr($s1, $p1, $p2-$p1);
		$data['tile'][1] = substr($s1, $p2, $p3-$p2);
		$data['tile'][2] = substr($s1, $p3);
	}

	$mgc = $bin[0] . $bin[8] . $bin[0x30];
	if ( $mgc === "\x04\x30\x0c" )
	{
		printf("DETECT %s.bin = laix 64-bit\n", $pfx);
		$data['room'] = array(27,15);
		$p1 = str2int($bin,  8, 3);
		$p2 = str2int($bin, 16, 3);
		$p3 = str2int($bin, 24, 3);

		$s1 = substr($bin, $p1, $p2-$p1);
		$s2 = substr($bin, $p2, $p3-$p2);

		// tile data
		$p1 = str2int($s1, 0, 3);
		$p2 = str2int($s1, 4, 3);
		$p3 = str2int($s1, 8, 3);
		$data['tile'][0] = substr($s1, $p1, $p2-$p1);
		$data['tile'][1] = substr($s1, $p2, $p3-$p2);
		$data['tile'][2] = substr($s1, $p3);
	}

	// no match
	if ( empty($data) )
		return '';

	// map data
	$sz1 = str2int($s2,  0, 3);
	$ps1 = str2int($s2,  4, 3);
	$sz2 = str2int($s2,  8, 3);
	$ps2 = str2int($s2, 12, 3);
	$sz3 = str2int($s2, 16, 3);
	$ps3 = str2int($s2, 20, 3);
	$sz4 = str2int($s2, 24, 3);
	$ps4 = str2int($s2, 28, 3);
	$ps5 = strlen ($s2);
	$data['map'][0] = mapdata_loop($s2, $ps1, $ps2, $sz1);
	$data['map'][1] = mapdata_loop($s2, $ps2, $ps3, $sz2);
	$data['map'][2] = mapdata_loop($s2, $ps3, $ps4, $sz3);
	$data['map'][3] = mapdata_loop($s2, $ps4, $ps5, $sz4);

	foreach ( $data['tile'] as $k => $v )
		save_file("$pfx/meta/tile.$k", $v);

	foreach ( $data['map'] as $mk => $mv )
	{
		foreach ( $mv as $k => $v )
			save_file("$pfx/meta/map.$mk.$k", $v);
	}
	return $data;
}
//////////////////////////////
function gunvolt( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname,'.'));
	$map = load_mapdata($pfx);
	if ( empty($map) )
		return php_notice('ERROR load_mapdata()');
	info_maplayer($map);

	$set = load_tileset($pfx, $map);
	if ( empty($set) )
		return php_notice('ERROR load_tileset()');

	save_layers($map, $set, $pfx);
	save_layout($map, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gunvolt( $argv[$i] );

/*
bin
	00  04
	04  tile data/offset
		04/00  tileset data/offset
		08/04  collusion data/offset
		0c/08  room data/offset
			tileset data
			collusion data
			room data
	08  map layer data/offset
		00  map tile size
		04  map tile offset
		08  map ??? size
		0c  map ??? offset
		10  map ??? size
		14  map ??? offset
		18  map ??? size
		1c  map ??? offset
			map tile * n
			map ??? * n
			map ??? * n
			map ??? * n
	0c  PTMT offset
	10  PTMT offset
	14  end/filesize
fnt
	00  01
	04  toc/offset
		00  count
			00  fname offset
			04  fsize
			08  file offset
			0c  ???
			10  w
			12  h
			14  01 02 -- --
			18  -- -- -- --
			1c  mod_time
	08  file size

mgv 16x16  mgv/*
gv1 16x16  gv2/st01_a_00  gv2/st01_a_01
gv1  8x8   bmz/*  gv1/*  gv2/*  gva/*

mgv tile.1/collusion type
	-- --  *none*
	-- 10  ladder
	-- 20  spike
	 8 --  block
	 8 10  ladder top
	 8 20  spike

gv2 tile.1/collusion type
	-- -- -- --  *none*
	 4 -- -- --  slope up 1
	 5 -- -- --  slope up 2
	 6 -- -- --  slope up 3
	 7 -- -- --  slope up 4
	10 -- -- --  block
	10 -- -- 80  jump through block
	10 -- 10 --  ?invisible block?
 */
