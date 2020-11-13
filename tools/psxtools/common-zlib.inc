<?php
if ( PHP_VERSION_ID < 050400 )
	trigger_error("zlib extension requires PHP 5.4.0 or later\n", E_USER_ERROR);

if ( ! function_exists("zlib_decode") )
	trigger_error("zlib extension not found!\n", E_USER_ERROR);

// enum
//   ZLIB_ENCODING_RAW     = -15
//   ZLIB_ENCODING_GZIP    =  31
//   ZLIB_ENCODING_DEFLATE =  15
// level = -1 to 9
//   -1 = auto (same as level 6)
//    0 = store
//    9 = best
