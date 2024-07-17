<?php
require 'class-merkel.inc';

$str = array(
	'',
	'The quick brown fox jumps over the lazy dog',
	'Web2D Games is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version. Web2D Games is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with Web2D Games. If not, see <http://www.gnu.org/licenses/>.',
);

foreach ( $str as $s )
{
	echo "STR = '$s'\n\n";

	$b1 = exec("echo -n '$s' | md5sum");
	$b2 = merkel_damgard::md5($s);
		printf("  md5 %s\n", $b1);
		printf("  md5 %s\n", bin2hex($b2));
	echo "\n";

	$b1 = exec("echo -n '$s' | sha1sum");
	$b2 = merkel_damgard::sha1($s);
		printf("  sha1 %s\n", $b1);
		printf("  sha1 %s\n", bin2hex($b2));
	echo "\n";

	$b1 = exec("echo -n '$s' | sha256sum");
	$b2 = merkel_damgard::sha256($s);
		printf("  sha256 %s\n", $b1);
		printf("  sha256 %s\n", bin2hex($b2));
	echo "\n";

	$b1 = exec("echo -n '$s' | sha224sum");
	$b2 = merkel_damgard::sha224($s);
		printf("  sha224 %s\n", $b1);
		printf("  sha224 %s\n", bin2hex($b2));
	echo "\n";

	if ( PHP_INT_SIZE >= 8 )
	{
		$b1 = exec("echo -n '$s' | sha512sum");
		$b2 = merkel_damgard::sha512($s);
			printf("  sha512 %s\n", $b1);
			printf("  sha512 %s\n", bin2hex($b2));
		echo "\n";

		$b1 = exec("echo -n '$s' | sha384sum");
		$b2 = merkel_damgard::sha384($s);
			printf("  sha384 %s\n", $b1);
			printf("  sha384 %s\n", bin2hex($b2));
		echo "\n";
	}

	echo "==========\n";
} // foreach ( $str as $s )

/*
 md5  ('') = d41d8cd98f00b204e9800998ecf8427e
sha1  ('') = da39a3ee5e6b4b0d3255bfef95601890afd80709
sha256('') = e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
sha224('') = d14a028c2a3a2bc9476102bb288234c415a2b01f828ea62ac5b3e42f
sha512('') = cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e
sha384('') = 38b060a751ac96384cd9327eb1b1e36a21fdb71114be07434c0cc7bf63f6e1da274edebfe76f65fbd51ad2f14898b95b
 */

$md5    = hmac::md5   ("key", "The quick brown fox jumps over the lazy dog");
$sha1   = hmac::sha1  ("key", "The quick brown fox jumps over the lazy dog");
$sha256 = hmac::sha256("key", "The quick brown fox jumps over the lazy dog");
//$sha512 = hmac::sha512("key", "The quick brown fox jumps over the lazy dog");
	printf("HMAC  md5   %s\n", bin2hex($md5   ));
	printf("HMAC sha1   %s\n", bin2hex($sha1  ));
	printf("HMAC sha256 %s\n", bin2hex($sha256));
	//printf("HMAC sha512 %s\n", bin2hex($sha512));
/*
HMAC_MD5   ("key", "The quick brown fox jumps over the lazy dog") = 80070713463e7749b90c2dc24911e275
HMAC_SHA1  ("key", "The quick brown fox jumps over the lazy dog") = de7c9b85b8b78aa6bc8a7a36f70a90701c9db4d9
HMAC_SHA256("key", "The quick brown fox jumps over the lazy dog") = f7bc83f430538424b13298e6aa6fb143ef4d59a14946175997479dbc2d1a3cd8
 */
