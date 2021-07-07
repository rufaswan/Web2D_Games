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
require "common.inc";
require "common-guest.inc";
require "class-merkel.inc";

$mer = new Merkel_Damgard;
$str = array(
	'',
	'The quick brown fox jumps over the lazy dog',
	'Web2D Games is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.',
);

foreach ( $str as $s )
{
	echo "STR = '$s'\n";
	$b1 = md5($s);
	$b2 = $mer->md5sum($s);
		printf("  md5 %s\n", $b1);
		printf("  md5 %s\n", bin2hex($b2));
	echo "\n";

	$b1 = sha1($s);
	$b2 = $mer->sha1sum($s);
		printf("  sha1 %s\n", $b1);
		printf("  sha1 %s\n", bin2hex($b2));
	echo "\n";

	$b2 = $mer->sha256sum($s);
		printf("  sha256 %s\n", bin2hex($b2));
	echo "\n";

	echo "==========\n";
} // foreach ( $str as $s )

/*
 md5  ('') = d41d8cd98f00b204e9800998ecf8427e
sha1  ('') = da39a3ee5e6b4b0d3255bfef95601890afd80709
sha256('') = e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
sha512('') = cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e
 */

$hmac = new HMAC;
$b1 = $hmac->md5sum   ("key", "The quick brown fox jumps over the lazy dog");
$b2 = $hmac->sha1sum  ("key", "The quick brown fox jumps over the lazy dog");
$b3 = $hmac->sha256sum("key", "The quick brown fox jumps over the lazy dog");
	printf("HMAC  md5   %s\n", bin2hex($b1));
	printf("HMAC sha1   %s\n", bin2hex($b2));
	printf("HMAC sha256 %s\n", bin2hex($b3));
/*
HMAC_MD5   ("key", "The quick brown fox jumps over the lazy dog") = 80070713463e7749b90c2dc24911e275
HMAC_SHA1  ("key", "The quick brown fox jumps over the lazy dog") = de7c9b85b8b78aa6bc8a7a36f70a90701c9db4d9
HMAC_SHA256("key", "The quick brown fox jumps over the lazy dog") = f7bc83f430538424b13298e6aa6fb143ef4d59a14946175997479dbc2d1a3cd8
 */
