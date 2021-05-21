<?php
/*
[license]
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
