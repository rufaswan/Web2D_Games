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
class PSP_Gim
{
	private function gimpix( &$file, $pos )
	{
		$size = str2int($file, $pos+0, 2);
		$type = str2int($file, $pos+4, 2);
		//$swiz = str2int($file, $pos+6, 2);
		$w = str2int($file, $pos+ 8, 2);
		$h = str2int($file, $pos+10, 2);

		$base += ($size + 0x10);
		switch ( $type )
		{
			case 3:
				printf("TYPE %x RGBA32 , %x x %x\n", $type, $w, $h);
				$size = 4 * $w * $h;
				$data['byte'] = 4;
				$data['w'] = $w;
				$data['h'] = $h;
				$data['pix'] = substr($file, $base, $size);
				// all RGBA32 are palette
				break;

			case 4:
				printf("TYPE %x 4-bpp , %x x %x\n", $type, $w, $h);
				$size = $w / 2 * $h;
				$data['byte'] = 1;
				$data['w'] = $w;
				$data['h'] = $h;

				$data['pix'] = substr($file, $base, $size);
				bpp4to8($data['pix']);
				gimpix($data['pix'], $w, $h, 32);
				break;

			case 5:
				printf("TYPE %x 8-bpp , %x x %x\n", $type, $w, $h);
				$size = $w * $h;
				$data['byte'] = 1;
				$data['w'] = $w;
				$data['h'] = $h;
				$data['pix'] = substr($file, $base, $size);
				gimpix($data['pix'], $w, $h, 16);
				break;

			default:
				return php_error("TYPE %x UNKNOWN", $type);
		} // switch ( $type )
		return;
	}
	//////////////////////////////
	public function gim( &$file )
	{
		if ( substr($file, 0, 11) !== "MIG.00.1PSP" )
			return 0;

		$len = strlen($file);
		$pos = 0x10;
		$pix = [];
		$pal = [];
		while ( $pos < $len )
		{
			$blk   = str2int($file, $pos+ 0, 2);
			$bsize = str2int($file, $pos+ 4, 4);
			$bnext = str2int($file, $pos+ 8, 4);
			$bdata = str2int($file, $pos+12, 4);
			switch ( $blk )
			{
				case 2: // root
				case 3: // picture
					$pos += $bnext;
					break;
				case 4: // image
					if ( ! empty($pix) )
						return 0;
					$pix = $this->gimpix($file, $pos+$bdata);
					$pos += $bnext;
					break;
				case 5: // palette
					if ( ! empty($clut) )
						return 0;
					$pal = $this->gimpix($file, $pos+$bdata);
					$pos += $bnext;
					break;
				default:
					break 2;
			}
		} // while (1)

		if ( empty($pix) )
			return 0;
		if ( ! empty($pal) )
		{
			$pix['pal'] = $pal['pix'];
			$pix['cc']  = $pal['w'];
		}
		return $pix;
	}
} // class PSP_Gim
