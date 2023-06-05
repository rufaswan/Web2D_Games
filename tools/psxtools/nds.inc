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
 *   GBATEK v2.8f (no$gba)
 *     Martin Korth
 */

class NDSList
{
	private $fp;

	private function list_add( $name, $pos, $siz, $id, $ram )
	{
		$this->list[$name] = array(
			'id'  => $id  ,
			'pos' => $pos ,
			'siz' => $siz ,
			'ram' => $ram ,
		);
		return;
	}

	private function fread_add( $name, $pos, $siz, $id, $ram )
	{
		$this->list_add($name, $pos, $siz, $id, $ram);
		return $this->fread($pos, $siz);
	}

	private function fread( $pos, $siz )
	{
		fseek($this->fp, $pos, SEEK_SET);
		return fread($this->fp, $siz);
	}

	//////////////////////////////

	private function sect_overlay( $pfx, $off, $siz, &$fat )
	{
		if ( $siz < 1 )
			return;
		$fn = sprintf('%s/overlay.bin', $pfx);
		$over = $this->fread_add($fn, $off, $siz, -1, -1);

		for ( $i=0; $i < $siz; $i += 0x20 )
		{
			$ov_id  = str2int($over, $i+0, 4);
			$ov_ram = str2int($over, $i+4, 4);
			$ov_siz = str2int($over, $i+8, 4);

			$ov_fid = str2int($over, $i+0x18  , 4);
			$ov_off = str2int($fat , $ov_fid*8, 4);

			$fn = sprintf('%s/%06x/%04d_%x.overlay', $pfx, $ov_ram, $ov_id, $ov_id);
			$this->list_add($fn, $ov_off, $ov_siz, $ov_id, $ov_ram);
		} // for ( $i=0; $i < $siz; $i += 0x20 )
		return;
	}

	private function sect_data( $pfx, &$fnt, &$fat, &$fnt_off, $fnt_hd )
	{
		$st_id = str2int($fnt, $fnt_off+4, 2);

		if ( $fnt_off >= $fnt_hd )
			return;

		$off1 = str2int($fnt, $fnt_off+0, 4);
		$off2 = str2int($fnt, $fnt_off+8, 4);
		if ( ($fnt_off+8) >= $fnt_hd )
			$off2 = strlen($fnt);
		$fnt_off += 8;

		$func = __FUNCTION__;
		while ( $off1 < $off2 )
		{
			$b = ord( $fnt[$off1+0] );
			$isd = $b & 0x80;
			$siz = $b & 0x7f;
			if ( $siz === 0 )
				return;

			$nam = substr($fnt, $off1+1, $siz);
				$off1 += (1 + $siz);

			$fn = sprintf('%s/%s', $pfx, $nam);
			if ( $isd )
			{
				$off1 += 2;
				$this->$func($fn, $fnt, $fat, $fnt_off, $fnt_hd);
			}
			else
			{
				$file_off1 = str2int($fat, $st_id*8+0, 4);
				$file_off2 = str2int($fat, $st_id*8+4, 4);
				$file_siz  = $file_off2 - $file_off1;

				$this->list_add($fn, $file_off1, $file_siz, $st_id, -1);
				$st_id++;
			}
		} // while ( $off1 < $off2 )
		return;
	}

	private function sect_head( &$head )
	{
		$arm9_off = str2int($head, 0x20, 4);
		$arm9_ram = str2int($head, 0x28, 4);
		$arm9_siz = str2int($head, 0x2c, 4);
			$this->list_add('arm9/main.bin', $arm9_off, $arm9_siz, -1, $arm9_ram);

		$arm7_off = str2int($head, 0x30, 4);
		$arm7_ram = str2int($head, 0x38, 4);
		$arm7_siz = str2int($head, 0x3c, 4);
			$this->list_add('arm7/main.bin', $arm7_off, $arm7_siz, -1, $arm7_ram);

		$fnt_off = str2int($head, 0x40, 4);
		$fnt_siz = str2int($head, 0x44, 4);
		$fat_off = str2int($head, 0x48, 4);
		$fat_siz = str2int($head, 0x4c, 4);
			$fnt = $this->fread_add('fnt.bin', $fnt_off, $fnt_siz, -1, -1);
			$fat = $this->fread_add('fat.bin', $fat_off, $fat_siz, -1, -1);

		$ov9_off = str2int($head, 0x50, 4);
		$ov9_siz = str2int($head, 0x54, 4);
			$this->sect_overlay('arm9', $ov9_off, $ov9_siz, $fat);

		$ov7_off = str2int($head, 0x58, 4);
		$ov7_siz = str2int($head, 0x5c, 4);
			$this->sect_overlay('arm7', $ov7_off, $ov7_siz, $fat);

		$fnt_hd  = str2int($fnt, 0, 4);
		$fnt_off = 0;
			$this->sect_data('data', $fnt, $fat, $fnt_off, $fnt_hd);
		return;
	}

	//////////////////////////////

	public $list;

	function load( $fname )
	{
		$this->list = array();
		if ( ! is_file($fname) )
			return -1;
		$this->fp = fopen($fname, 'rb');
		if ( ! $this->fp )
			return -1;

		$head = $this->fread_add('head.bin', 0, 0x180, -1, -1);

		// RAM address check
		$mgc = $head[0x27] . $head[0x2b] . $head[0x37] . $head[0x3b];
		if ( $mgc !== "\x02\x02\x02\x02" )
			return -1;

		$this->sect_head($head);

		$name = substr($head,  0, 12);
		$code = substr($head, 12,  4);
		$vers = ord( $head[0x1e] );
			$name = rtrim($name, ZERO.' ');
		return sprintf('%s-%d_%s', $code, $vers, $name);
	}

	function loadfile( $name )
	{
		if ( ! isset($this->list[$name]) )
			return '';
		$t = $this->list[$name];
		return $this->fread($t['pos'], $t['siz']);
	}

	function ndsram_new( $size )
	{
		$ram = str_repeat(ZERO, $size);

		$arm = $this->list['arm9/main.bin'];
		$dat = $this->fread($arm['pos'], $arm['siz']);
		str_update($ram, $arm['ram'] & BIT24, $dat);

		$arm = $this->list['arm7/main.bin'];
		$dat = $this->fread($arm['pos'], $arm['siz']);
		str_update($ram, $arm['ram'] & BIT24, $dat);

		return $ram;
	}

	function ndsram_over( &$ram, $id )
	{
		if ( (int)$id !== $id )
			$id = hexdec($id);

		foreach ( $this->list as $lk => $lv )
		{
			if ( $lv['ram'] < 0 )
				continue;
			if ( $lv['id'] === $id )
			{
				$dat = $this->fread($lv['pos'], $lv['siz']);
				str_update($ram, $lv['ram'] & BIT24, $dat);
				return;
			}
		} // foreach ( $this->list as $lk => $lv )
		return;
	}

} // class NDSList

//////////////////////////////

define('NDS_RAM' ,  4 << 20);
define('NDSI_RAM', 16 << 20);
