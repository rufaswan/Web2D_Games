<?php
function php_check( $func )
{
	printf('check for %s() ... ', $func);
	if ( function_exists($func) )
		echo 'OK';
	else
		echo 'not found';
	echo "\n";
}

php_check('zlib_decode');
php_check('json_decode');

echo "--> FreeBSD/MacOS : iconv() is defined as libiconv()\n";
php_check('iconv');
php_check('libiconv');
