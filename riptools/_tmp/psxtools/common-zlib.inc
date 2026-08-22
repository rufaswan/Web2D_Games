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
// PHP 5.4.0+
php_req_extension('zlib_decode', 'zlib');
//php_req_extension('zlib_encode', 'zlib');

// PHP 7+
//php_req_extension('inflate_init', 'zlib'); // for custom dictionary
//php_req_extension('deflate_init', 'zlib'); // for custom dictionary

// chr(0x89) . "PNG\r\n" . chr(0x1a) . "\n";

//////////////////////////////

function zipfile_list( $fp )
{
	$list = [];
	$pos = 0;
	while (1)
	{
		$pk3 = fp2str($fp, $pos, 0x1e);
			$pos += 0x1e;
		if ( substr($pk3,0,4) !== "PK\x03\x04" )
			break;

		$fsz1 = str2int($pk3, 0x12, 4); // compressed size
		$fsz2 = str2int($pk3, 0x16, 4); // uncompressed size
		$flen = str2int($pk3, 0x1a, 2);
		$fext = str2int($pk3, 0x1c, 2);

		$fnam = fp2str($fp, $pos, $flen);
			$pos += ($flen + $fext);

		$list[] = [
			'file'  => $fnam,
			'pos'   => $pos,
			'size1' => $fsz1,
			'size2' => $fsz2,
		];

		$pos += $fsz1;
	} // while (1)
	return $list;
}
