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
php_req_extension("zlib_decode", "zlib");
//php_req_extension("zlib_encode", "zlib");

// PHP 7+
//php_req_extension("inflate_init", "zlib"); // for custom dictionary
//php_req_extension("deflate_init", "zlib"); // for custom dictionary

// enum
//   ZLIB_ENCODING_RAW     = -15
//   ZLIB_ENCODING_GZIP    =  31
//   ZLIB_ENCODING_DEFLATE =  15
// level = -1 to 9
//   -1 = auto (same as level 6)
//    0 = store
//    9 = best
// zip file methods
//  unzip -v ZIPFILE
//  zipinfo  ZIPFILE
//  0   = Stored
//  1-2 = Defl:F
//  3-7 = Defl:N
//  8-9 = Defl:X
